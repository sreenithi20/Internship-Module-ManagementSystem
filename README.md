# Internship Module Management System

A comprehensive web-based Internship Management System designed to handle the end-to-end internship application process. This module manages everything from the initial application forms required by an intern applicant to the final feedback form filled out upon completion of the internship period.

## 🚀 Features

* **User Portal (`user.html`)**: Allows prospective interns to browse information and apply for internships.
* **Admin Portal (`admin.html`)**: Dedicated dashboard for administrators to view, manage, and process intern applications.
* **Application Processing**: Stores and manages new applicant details efficiently (`store_new_details.php`).
* **Feedback Mechanism**: Captures post-internship feedback from completed interns.
* **Data Export (`export.php`)**: Allows administrators to export intern data/records for external use or reporting.

## 💻 Tech Stack

* **Frontend**: HTML5, CSS3 (`styless.css`)
* **Backend**: PHP (Handles form submissions, database connectivity, and backend logic)
* **Database**: MySQL (Typically used with PHP to store user details, assumed via the `.php` store/export handlers)

## 📁 Repository Structure

```text
├── admin.html             # Admin dashboard/login interface
├── user.html              # Intern/User-facing portal
├── web.html               # Main landing/web page
├── styless.css            # Stylesheet for UI design
├── store_new_details.php  # Backend script to save intern application data
├── export.php             # Script to export database records
├── aaan.php / annnn.php   # Backend processing files
├── new 1.html / new 3.html# Additional form/view templates
├── new 4.php              # Additional backend handler
├── ff.html                # Feedback form template
├── LICENSE                # MIT License
└── README.md              # Project documentation
