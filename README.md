# 🎓 Internship Module Management System

A web-based internship webforms module built during an internship at **PSG Institutions — Central Human Resource**, designed to manage the complete internship lifecycle for students — from registration to feedback and certificate submission.

> ⚠️ *This project was developed as part of an internship assignment. Deployment at the institution is subject to their discretion.*

---

## 📌 Overview

The system provides a set of structured, validated web forms that guide students through every stage of the internship process. Administrators can approve or reject applications and access all submitted data through a backend dashboard.

---

## 🔄 Workflow

```
Student fills Registration Form (web.html)
        ↓
Registration Success Page → WhatsApp invite link + QR code
        ↓
Admin reviews on admin.html → Approves / Rejects
        ↓
Automated email sent to student
        ↓
Student fills Feedback Form (ff.html)
        ↓
Certificate Upload Page
        ↓
Thank You Page
```

---

## ✨ Features

### 👩‍🎓 Student Modules

| Page | File | Description |
|------|------|-------------|
| Registration Form | `web.html` | Collects student personal, academic and internship details with full validation |
| Registration Success | `new 1.html` | Confirmation page with WhatsApp group invite link and QR code |
| Approval Status | `new 3.html` | Shows whether the application was approved or rejected |
| Feedback Form | `ff.html` | Post-internship survey covering experience, learning and satisfaction |
| Certificate Upload | `new 4.php` | Students upload their internship completion certificate |
| Thank You Page | `new 1.html` | Final confirmation after certificate upload |
| User Dashboard | `user.html` | Student view of their internship details and current status |

### 🛠️ Admin Modules

| Page | File | Description |
|------|------|-------------|
| Admin Approval Panel | `admin.html` | Approve or reject internship applications; triggers automated email to student |
| Database Dashboard | `aaan.php` / `annnn.php` | Backend PHP pages for processing and storing form data |
| Data Export | `export.php` | Export all internship records for reporting or archiving |

---

## 🖥️ Screenshots

### Registration Form 
<img width="621" height="812" alt="image" src="https://github.com/user-attachments/assets/50c0f429-ed48-48f7-afdf-4be68ce5156f" />

### Form Data Preview Page
<img width="607" height="812" alt="image" src="https://github.com/user-attachments/assets/f35134ae-7e2d-4848-a216-6ffc6fcd5329" />

### Approval Page
<img width="1740" height="250" alt="image" src="https://github.com/user-attachments/assets/86d6b2ee-066a-4e85-b020-e0249fe0f997" />

### Feedback Form
<img width="663" height="840" alt="image" src="https://github.com/user-attachments/assets/64e0c5d9-3045-4ac8-9cf2-bd8e71840d42" />

### Certificate Uplaoding Page
<img width="699" height="655" alt="image" src="https://github.com/user-attachments/assets/7e757dac-3e65-491b-9a75-3458c6b704ee" />







---

## 🧾 Form Fields

### Registration Form (`web.html`)
- First Name, Last Name
- Register Number *(institution-issued)*
- Name of College
- Year of Study
- Qualification *(dropdown)*
- Specialization *(dynamic dropdown based on qualification)*
- Field of Interest
- Email ID *(validated — must include `@` and `.`)*
- Mobile Number *(10 digits only)*
- Alternate Contact Details *(Staff / Parents / Guardian)*
- Alternate Contact Name & Number
- Address for Communication
- Purpose of Internship
- Internship Period *(From / To date picker)*
- Upload File *(College Permission Letter / Resume — PDF, DOC, DOCX, max 5MB)*
- Upload Photo *(JPG, PNG, GIF, max 2MB)*

### Feedback Form (`ff.html`)
- Internship experience rating
- Learning outcomes
- Overall satisfaction
- Open-ended comments

---

## 🗄️ Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP |
| Database | MySQL (phpMyAdmin) |
| Server | Apache (XAMPP / WAMP) |

---

## 📂 Project Structure

```
Internship-Module-ManagementSystem/
│
├── web.html                  # Main registration form
├── admin.html                # Admin approval panel
├── user.html                 # Student dashboard
├── ff.html                   # Feedback form
├── new 1.html                # Success / Thank you page
├── new 3.html                # Approval status page
├── new 4.php                 # Certificate upload handler
│
├── aaan.php                  # Backend data processor
├── annnn.php                 # Backend data processor
├── store_new_details.php     # Stores registration data to DB
├── export.php                # Exports data from DB
│
├── styless.css               # Stylesheet
├── .gitignore
├── LICENSE
└── README.md
```

---

## 🚀 Getting Started

### Prerequisites
- PHP >= 7.4 with `mysqli` extension
- MySQL >= 5.7
- Apache server — XAMPP or WAMP recommended

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/sreenithi20/Internship-Module-ManagementSystem.git

# 2. Move the folder to your server root
# XAMPP → htdocs/
# WAMP  → www/

# 3. Create a MySQL database (e.g. internship_db) in phpMyAdmin

# 4. Update DB credentials in these PHP files:
#    aaan.php, annnn.php, new 4.php, store_new_details.php, export.php
#
#    Look for and update:
#    $servername = "localhost";
#    $username   = "your_db_username";
#    $password   = "your_db_password";
#    $dbname     = "internship_db";

# 5. Start Apache + MySQL from XAMPP/WAMP control panel

# 6. Open in browser
http://localhost/Internship-Module-ManagementSystem/web.html
```

---

## 🙋‍♀️ Author

**Sreenithi**
- GitHub: [@sreenithi20](https://github.com/sreenithi20)
- Built during an internship at **PSG Institutions — Central Human Resource**

---

## 📄 License

This project was developed during an internship at PSG Institutions. All rights reserved.
