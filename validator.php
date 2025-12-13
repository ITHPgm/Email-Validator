<?php
// Check if form submitted
$responseMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // API endpoint
        $apiUrl = "https://rapid-email-verifier.fly.dev/api/validate?email=" . urlencode($email);

        // Initialize cURL
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // optional, if SSL issues occur

        // Execute request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            $responseMessage = "cURL error: " . curl_error($ch);
        } else {
            // Decode JSON response
            $data = json_decode($response, true);

            if (isset($data['status'])) {
                $responseMessage = "Email status: <strong>" . htmlspecialchars($data['status']) . "</strong>";
            } else {
                $responseMessage = "Invalid response from API.";
            }
        }

        curl_close($ch);
    } else {
        $responseMessage = "Please enter a valid email address.";
    }
}
?>
