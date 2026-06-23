<?php
/**
 * email-validate.php
 * Email validation endpoint using Rapid Email Verifier API
 * Accepts GET or POST
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET'], true)) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only GET and POST allowed']);
    exit();
}

define('EMAIL_VALIDATION_API_URL', 'https://rapid-email-verifier.fly.dev/api/validate?email=');
define('LOG_DIR', __DIR__ . '/logs');
define('MAX_LOG_STRING_LEN', 500);

$requestStart = microtime(true);
$requestId = bin2hex(random_bytes(8));

header('X-Request-ID: ' . $requestId);

// ============================================================
// Logging helpers
// ============================================================
function ensure_log_dir(): string
{
    if (!is_dir(LOG_DIR)) {
        @mkdir(LOG_DIR, 0755, true);
    }
    return LOG_DIR;
}

function limit_log_string($value, int $maxLen = MAX_LOG_STRING_LEN): string
{
    if (is_array($value) || is_object($value)) {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $value = (string) $value;
    if (strlen($value) > $maxLen) {
        $value = substr($value, 0, $maxLen) . '...';
    }
    return $value;
}

function mask_email(string $email): string
{
    $email = trim($email);
    if ($email === '' || !str_contains($email, '@')) {
        return 'unknown';
    }

    [$local, $domain] = explode('@', $email, 2);
    $localMasked = strlen($local) > 2 ? substr($local, 0, 2) . '***' : '***';
    return $localMasked . '@' . $domain;
}

function current_memory_mb(): float
{
    return round(memory_get_peak_usage(true) / 1048576, 2);
}

function log_event(string $event, string $message = '', array $extra = [], string $level = 'info'): void
{
    global $requestId, $requestStart;

    $logFile = ensure_log_dir() . '/site_log_' . date('Y-m-d') . '.log';

    $record = [
        'time' => date('Y-m-d H:i:s'),
        'request_id' => $requestId,
        'level' => $level,
        'event' => $event,
        'message' => limit_log_string($message),
        'duration_ms' => round((microtime(true) - $requestStart) * 1000, 2),
        'memory_mb' => current_memory_mb(),
        'request' => [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => limit_log_string($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'),
            'referer' => limit_log_string($_SERVER['HTTP_REFERER'] ?? ''),
            'origin' => limit_log_string($_SERVER['HTTP_ORIGIN'] ?? ''),
        ],
        'extra' => $extra,
    ];

    @file_put_contents(
        $logFile,
        json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

function respond_json(bool $success, bool $valid, string $message, array $details = null, int $httpCode = 200, array $extra = [], string $level = 'info'): void
{
    log_event(
        $success ? 'response_success' : 'response_error',
        $message,
        array_merge($extra, [
            'success' => $success,
            'valid' => $valid,
            'details' => $details,
            'http_code' => $httpCode,
        ]),
        $level
    );

    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'valid' => $valid,
        'message' => $message,
        'details' => $details
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

function send_json_error(string $message, int $code = 400, array $extra = []): void
{
    respond_json(false, false, $message, null, $code, $extra, 'error');
}

function send_json_success(bool $valid, string $message, ?array $details = null, array $extra = []): void
{
    respond_json(true, $valid, $message, $details, 200, $extra, $valid ? 'info' : 'warning');
}

// ============================================================
// Start request logging
// ============================================================
log_event('request_received', 'Validation request received');

// ============================================================
// Get email from POST, GET, or JSON body
// ============================================================
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
if (!is_array($input)) {
    $input = [];
}

$email = trim(
    $input['email'] ??
    $_POST['email'] ??
    $_GET['email'] ??
    ''
);

$emailHash = $email !== '' ? hash('sha256', strtolower($email)) : null;

if (empty($email)) {
    log_event('input_missing', 'No email address provided', [
        'email_hash' => $emailHash,
    ], 'warning');
    send_json_error('No email address provided', 400, [
        'email_hash' => $emailHash,
    ]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    log_event('input_invalid', 'Invalid email format', [
        'email_hash' => $emailHash,
        'masked_email' => mask_email($email),
    ], 'warning');
    send_json_error('Invalid email format', 400, [
        'email_hash' => $emailHash,
        'masked_email' => mask_email($email),
    ]);
}

// ============================================================
// Optional: basic rate signal in logs
// ============================================================
log_event('api_prepare', 'Preparing external validation request', [
    'email_hash' => $emailHash,
    'masked_email' => mask_email($email),
]);

// ============================================================
// Call external validation API
// ============================================================
$url = EMAIL_VALIDATION_API_URL . urlencode($email);
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'IBK-EmailValidation/1.0',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
    ],
]);

$curlStart = microtime(true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_errno = curl_errno($ch);
$curl_error = curl_error($ch);
$curl_total_time = round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000, 2);
curl_close($ch);

log_event('api_call_completed', 'External validation call completed', [
    'email_hash' => $emailHash,
    'masked_email' => mask_email($email),
    'http_code' => $http_code,
    'curl_errno' => $curl_errno,
    'curl_error' => $curl_error,
    'external_api_duration_ms' => $curl_total_time,
]);

if ($curl_errno) {
    send_json_error('Validation service connection failed: ' . $curl_error, 503, [
        'email_hash' => $emailHash,
        'masked_email' => mask_email($email),
        'curl_errno' => $curl_errno,
    ]);
}

if ($http_code !== 200) {
    send_json_error('Validation service returned HTTP ' . $http_code, 503, [
        'email_hash' => $emailHash,
        'masked_email' => mask_email($email),
        'external_http_code' => $http_code,
    ]);
}

// ============================================================
// Process API response
// ============================================================
$result = json_decode($response, true);
if (!is_array($result) || !isset($result['status'])) {
    send_json_error('Invalid response from validation service', 502, [
        'email_hash' => $emailHash,
        'masked_email' => mask_email($email),
        'raw_response_preview' => limit_log_string($response, 250),
    ]);
}

$is_valid = ($result['status'] === 'VALID' && (int)($result['score'] ?? 0) >= 70);
$validations = $result['validations'] ?? [];

$reason = 'Email validation failed';

if (!($validations['syntax'] ?? false)) {
    $reason = 'Invalid email format';
} elseif (!($validations['domain_exists'] ?? false)) {
    $reason = 'Email domain does not exist';
} elseif (!($validations['mx_records'] ?? false)) {
    $reason = 'Invalid mail server configuration';
} elseif ($validations['is_disposable'] ?? false) {
    $reason = 'Disposable email addresses are not allowed';
} elseif ($validations['is_role_based'] ?? false) {
    $reason = 'Role-based emails are not allowed';
} elseif ((int)($result['score'] ?? 0) < 70) {
    $reason = 'Low confidence score (' . (int)($result['score'] ?? 0) . '%)';
} elseif ($is_valid) {
    $reason = 'Email is valid';
}

$details = [
    'status' => $result['status'] ?? 'UNKNOWN',
    'score' => (int)($result['score'] ?? 0),
    'validations' => $validations,
    'email_hash' => $emailHash,
    'masked_email' => mask_email($email),
];

log_event($is_valid ? 'email_validated' : 'email_rejected', $reason, [
    'email_hash' => $emailHash,
    'masked_email' => mask_email($email),
    'status' => $result['status'] ?? 'UNKNOWN',
    'score' => (int)($result['score'] ?? 0),
    'validations' => $validations,
], $is_valid ? 'info' : 'warning');

send_json_success($is_valid, $reason, $details, [
    'email_hash' => $emailHash,
    'masked_email' => mask_email($email),
    'external_http_code' => $http_code,
]);
?>
