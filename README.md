Below is a ready to paste README.md for your project. I read through both files and included the important details, features, how it works, setup, integration notes, limitations, and how other people can use it in their own website or code.

# Email Validator UI and API

A modern email validation project built with PHP, JavaScript, and a custom iOS style user interface.

This repository includes:

- A polished frontend UI for validating email addresses
- A PHP API endpoint that validates input and forwards requests to an external validation service
- Single email validation
- Bulk email validation
- Comma separated input support
- File upload support for CSV, TXT, and PDF
- GET and POST request support from the UI
- Detailed logging on the API side
- A clean, responsive, mobile friendly user experience

---

## Project Structure


/ui
  index.php

/api/validate
  index.php
  /logs

> Important: the UI code starts with a PHP tag, so it should be saved as index.php, not index.html, unless you remove the PHP opening line. If you want to keep the file name as index.html, delete the first PHP line from the UI file.




---

Features

Frontend UI

iOS inspired glassmorphism design

Animated background glow

AOS scroll animations

Typing effect

Font Awesome icons throughout

Single email validator mode

Bulk email validator mode

GET and POST method selector

API endpoint path selector

File upload support

Drag and drop support

Local storage for saving API path

Live preview of extracted bulk emails

Result cards with clear status tags

Toast notifications

Info icons with tooltips for every major function


API Endpoint

Accepts GET and POST requests

Accepts JSON body, form data, and query parameters

Validates that email input exists

Validates email format

Calls an external email validation service

Returns structured JSON responses

Logs requests, errors, API calls, and results

Generates a request ID for tracing

Supports CORS for frontend usage



---

How It Works

UI flow

1. The user opens the frontend in /ui


2. The user chooses:

Single validation or bulk validation

GET or POST request

API endpoint path



3. The user enters emails manually or uploads a file


4. The frontend extracts the email addresses


5. The frontend sends the data to the API


6. The API validates the emails using the external service


7. The UI displays the results in a clean result panel



API flow

1. The API receives input from GET, POST, or JSON body


2. The API checks if an email was provided


3. The API validates the email format


4. The API sends the email to the external validation service


5. The API processes the returned data


6. The API returns a JSON response


7. The API writes logs to the logs folder




---

Requirements

PHP 7.4 or newer

cURL enabled

Internet access on the server

A browser with JavaScript enabled


Optional but recommended:

Apache or Nginx hosting

PHP server with rewrite support

A writable logs directory



---

Installation

1. Clone the repository

git clone https://github.com/your-username/your-repo-name.git
cd your-repo-name

2. Place the files in the correct folders

Make sure the structure looks like this:

/ui/index.php
/api/validate/index.php

3. Run the project locally

If you are testing locally, start a PHP server from the project root:

php -S localhost:8000

Then open:

http://localhost:8000/ui/

The UI will load and call the API in the same project.


---

API Endpoint

Endpoint

/api/validate/index.php

Supported request methods

GET

POST


Accepted input formats

The API accepts email input from:

Query string

Form data

JSON body


Example GET request

/api/validate/index.php?email=user@example.com

Example POST request

POST /api/validate/index.php
Content-Type: application/json

{
  "email": "user@example.com"
}


---

API Response Format

The API returns JSON in this format:

{
  "success": true,
  "valid": true,
  "message": "Email is valid",
  "details": {
    "status": "VALID",
    "score": 98,
    "validations": {
      "syntax": true,
      "domain_exists": true,
      "mx_records": true,
      "is_disposable": false,
      "is_role_based": false
    },
    "email_hash": "sha256_hash_here",
    "masked_email": "us***@example.com"
  }
}

Response fields

success: tells you whether the API call itself succeeded

valid: tells you whether the email passed validation

message: human readable result message

details: extra validation data



---

Frontend Features Explained

Single validation

The user types one email address and clicks validate.

Bulk validation

The user can enter multiple emails separated by commas.

Example:

john@example.com, jane@example.com, support@example.com

File upload

The UI supports uploading:

CSV

TXT

PDF


The file contents are scanned and email addresses are extracted automatically.

Important note about PDF uploads

The current frontend reads PDFs as plain text in the browser. That works best for text based PDFs. Scanned PDFs or image based PDFs may not extract properly. For accurate PDF extraction, a PDF parsing library should be used.

Request method switch

The user can choose:

GET

POST


The UI automatically sends the data using the selected method.

API path selector

The user can change the API endpoint path in the UI and save it in local storage.


---

Important Integration Note

There is one important detail to know when using the files together:

The UI supports bulk emails

The UI can collect multiple emails and send them as an array, or as a comma separated query string.

The current API accepts a single email

The current API file processes one email at a time from:

email in GET

email in POST

email in JSON body


It does not currently loop through an emails array.

What this means

If you want bulk validation to work fully end to end, you have two choices:

Option 1

Update the API to accept an array of emails and return results for each email.

Option 2

Keep the API as is and make the UI send one request per email.

This README documents the current code exactly as provided so users understand how it works and what needs to be adjusted for bulk validation.


---

How to Use This in Your Own Website

You can reuse the API in your own project by making a fetch request from JavaScript.

Single email example using GET

fetch('/api/validate/index.php?email=' + encodeURIComponent(email))
  .then(response => response.json())
  .then(data => {
    console.log(data);
  });

Single email example using POST

fetch('/api/validate/index.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    email: email
  })
})
.then(response => response.json())
.then(data => {
  console.log(data);
});

Using the API in another site

If your frontend is on another domain, update the API URL inside your frontend code to the live path, for example:

const apiPath = 'https://yourdomain.com/api/validate/index.php';

Make sure CORS settings on the API allow that origin.


---

Logging

The API writes logs into:

/api/validate/logs

Each log entry includes:

Timestamp

Request ID

Request method

Request URI

IP address

User agent

Referer

Origin

Validation result

External API response metadata


This is useful for debugging, request tracing, and monitoring.


---

UI Design Notes

The frontend includes:

iOS inspired layout

Glassmorphism cards

Animated glow background

Hover effects

3D style motion

Responsive mobile friendly layout

Clean result cards

Icon based tooltips


It is designed to feel modern, premium, and app like.


---

Browser and Compatibility Notes

The frontend uses:

Google Fonts

Font Awesome

AOS animation library

JavaScript fetch

FileReader API

Local storage


For best results, use a modern browser such as:

Chrome

Edge

Firefox

Safari



---

Security Notes

This project is suitable for a portfolio, internal tool, or lightweight public use.

A few things to keep in mind:

The API is open to requests because CORS is set to *

The API does not currently require authentication

The UI can be embedded into another site if the API path is updated

If you expose this publicly, consider adding:

Rate limiting

API keys

Request throttling

Abuse protection

Better PDF parsing




---

Common Troubleshooting

The UI loads but validation does not work

Check that:

The API path is correct

The API file exists in /api/validate/index.php

The server supports PHP

cURL is enabled

Your server has internet access


The bulk mode shows no results

The current API accepts a single email at a time. You may need to update the API to support arrays of emails or send requests one by one.

PDF upload does not extract emails well

That is expected with plain text browser extraction. Use CSV or TXT for best results, or integrate a real PDF parser.

The API returns an error from the external service

Check:

Internet access

External service availability

Request format

Server logs in /api/validate/logs



---

Recommended Improvements

If you want to upgrade this project later, good next steps are:

Add true bulk API support

Add API key authentication

Add rate limiting

Add CSV export of validation results

Add server side PDF parsing

Add request caching

Add user accounts and history

Add dashboard charts and analytics



---

Example Deployment Layout

project-root/
├── ui/
│   └── index.php
├── api/
│   └── validate/
│       ├── index.php
│       └── logs/
└── README.md


---

Author

ITH PGM 
