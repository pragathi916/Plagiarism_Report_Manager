

# 📚 Plagiarism Report Managing Web Application

A complete role-based plagiarism report managing portal for educational institutions. Built using **HTML, CSS, JavaScript, PHP, and PHPMailer**, this system supports three types of users with distinct roles: **Admin**, **Librarian**, and **Faculty**. Users can upload, process, view, and download reports, with secure email-based password recovery.

---

## 🚀 Features

### 🔐 Authentication & Security

* Role-based login system
* Forgot Password with token-based email reset
* Email notifications via **PHPMailer**
* Secure password hashing
* Token expiration to prevent misuse

### 👤 User Roles

#### 🛠 Admin

* Add Faculty and Librarian users
* Delete users
* Reset passwords

#### 📗 Librarian

* View reports uploaded by Faculty
* Upload processed plagiarism reports
* Download reports

#### 📘 Faculty

* Upload reports/documents for plagiarism check
* View reports by date
* Download their own uploaded and processed reports

---

## 🛠️ Tech Stack

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Database:** MySQL
* **Mail Service:** PHPMailer

---

## 📦 Installation Instructions

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/pragathi916/Plag_check.git
```

### 2️⃣ Install Composer & PHPMailer

If Composer is not installed, download it from:
👉 [https://getcomposer.org/download/](https://getcomposer.org/download/)

Then navigate to the project directory and run:

```bash
cd Plag_check
composer require phpmailer/phpmailer
```

This creates a `vendor/` directory with PHPMailer classes.

---

## ⚙️ Configuration

### 📁 Database Setup

* Import the provided `.sql` file into your MySQL database.
* Update database credentials in `config.php`:

### 📧 Email Settings

* Configure SMTP inside files like `forgot-password.php`:

 
---

## ✅ Requirements

* PHP 7.x or higher
* MySQL
* Composer
* XAMPP / Localhost Server

