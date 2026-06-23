🚀 Email Validator (iOS UI + PHP API)

"PHP" (https://img.shields.io/badge/PHP-7.4%2B-blue?logo=php)
"JS" (https://img.shields.io/badge/JavaScript-ES6-yellow?logo=javascript)
"UI" (https://img.shields.io/badge/UI-iOS%20Style-black)
"License" (https://img.shields.io/badge/license-MIT-green)
"Status" (https://img.shields.io/badge/status-ready-success)

A sleek iOS-inspired Email Validation Web App with a powerful PHP backend API.
Supports single & bulk email validation, file uploads, and GET/POST API requests — all wrapped in a modern animated UI.

---

✨ Live Demo (Optional)

«Add your deployed link here:»

https://yourdomain.com/ui/

---

📸 Preview

«Add screenshots here after deploying (recommended for GitHub)»

---

⚡ Features

🎨 Frontend (UI)

- iOS-style glassmorphism design
- Smooth AOS animations
- 3D interactive card tilt
- Typing animation (live UX feel)
- Fully responsive (mobile + desktop)
- Font Awesome icons only (no stickers)

📩 Email Validation

- ✅ Single email validation
- 📦 Bulk email validation (comma-separated)
- 📁 Upload support:
   - CSV
   - TXT
   - PDF (basic extraction)

🔗 API Controls

- Switch between GET / POST
- Custom API endpoint input
- Auto request formatting
- Saved API path via localStorage

📊 Results UI

- Clean validation results
- Status tags (Valid / Invalid)
- Animated entry (AOS)
- Email preview chips
- Toast notifications

---

🧠 How It Works

Flow

1. User inputs email(s) or uploads file
2. UI extracts emails automatically
3. User selects:
   - Validation mode (Single / Bulk)
   - Request method (GET / POST)
4. Request is sent to API
5. API validates email via external service
6. Results are displayed beautifully

---

📁 Project Structure

project-root/
│
├── ui/
│   └── index.php
│
├── api/
│   └── validate/
│       ├── index.php
│       └── logs/
│
└── README.md

---

🛠 Installation

1. Clone Repo

git clone https://github.com/your-username/email-validator.git
cd email-validator

---

2. Run Locally

php -S localhost:8000

Open:

http://localhost:8000/ui/

---

🔌 API Usage

Endpoint

/api/validate/index.php

---

✅ GET Example

/api/validate/index.php?email=test@example.com

---

✅ POST Example

{
  "email": "test@example.com"
}

---

📤 API Response

{
  "success": true,
  "valid": true,
  "message": "Email is valid",
  "details": {
    "status": "VALID",
    "score": 95,
    "validations": {
      "syntax": true,
      "domain_exists": true,
      "mx_records": true,
      "is_disposable": false
    }
  }
}

---

⚠️ Important Notes

🔹 Bulk Limitation

Your UI supports multiple emails, but:

👉 API currently validates only ONE email per request

Fix Options:

- Loop requests in frontend
- OR upgrade API to handle arrays

---

🔹 PDF Extraction

- Works only for text-based PDFs
- Scanned PDFs may fail

---

📡 Using API in Your Own Project

JavaScript Example

fetch('/api/validate/index.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ email: 'test@example.com' })
})
.then(res => res.json())
.then(data => console.log(data));

---

🧾 Logging System

Logs stored in:

/api/validate/logs/

Includes:

- Request ID
- IP Address
- API response
- Errors
- Performance timing

---

🔐 Security Notes

Before production, consider adding:

- API keys 🔑
- Rate limiting 🚦
- Request throttling
- Abuse protection
- Better PDF parsing

---

🎯 Recommended Upgrades

- Bulk API support (multi-email processing)
- Export results (CSV download)
- Dashboard analytics
- Email reputation scoring
- Authentication system
- Queue processing for large lists

---

🌍 Deployment Tips

- Use Apache/Nginx with PHP
- Ensure:
   - "curl" is enabled
   - "logs/" folder is writable
- Update API path in UI if hosted separately

---

💡 Troubleshooting

❌ API not working

- Check API path
- Confirm PHP server running
- Check logs folder

❌ No results

- Ensure valid email format
- Check external API availability

❌ Bulk not working fully

- API only supports single email → update logic

---

🧑‍💻 Author

ITH Pgm

---

📜 License

MIT License (recommended)

---

⭐ Support

If you like this project:

- Star ⭐ the repo
- Fork 🍴 it
- Improve it 🚀

---

🔥 Final Note

This project is designed to feel like a premium SaaS email validation tool UI — clean, fast, and developer-friendly.

---
