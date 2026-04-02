# Web Engineering Project – Pothen Esxes Monitoring System

## 👥 Team Members

* Konstandinos Avramidis — AM: [ΣΥΜΠΛΗΡΩΣΤΕ]
* Russell Vickramasingam — AM: [ΣΥΜΠΛΗΡΩΣΤΕ]
* Alexandros Pelekanos — AM: [ΣΥΜΠΛΗΡΩΣΤΕ]

## 🧑‍💻 Work Distribution (M2)

* Konstandinos Avramidis: `project-root/database/schema.sql`, `project-root/includes/db.php`
* Russell Vickramasingam: `project-root/auth/register.php`, `project-root/auth/login.php`, `project-root/auth/logout.php`
* Alexandros Pelekanos: `project-root/modules/dashboard.php`, `project-root/modules/list.php`, `project-root/database/seed.sql`

---

## 📌 Project Description

This project is a web-based **Pothen Esxes Monitoring System** developed for tracking and managing the financial declarations of public officials in Cyprus.

The system allows users to access, search, and analyze declaration data, while providing secure access for registered users and administrators.

Users can:

* Register and login securely
* View protected content
* Search financial declarations using keywords
* Access data based on authentication and role

---

## ⚙️ Technologies Used

* PHP (Backend)
* MySQL / MariaDB (Database)
* HTML / CSS (Frontend)
* PDO (Secure Database Connection)

---

## 🧱 Backend Implementation (Milestone 2)

The backend system includes:

* Database design using MySQL (**schema.sql**)
* Demo data (**seed.sql**)
* Secure PDO connection (**db.php**)

### 🔐 Authentication System:

* User Registration (with validation)
* Login (with password verification)
* Logout (session destroy)
* Session Guard (protected pages)

### 🔍 Core Feature:

* Keyword search in **list.php** using GET method

---

## 📁 Project Structure

```
project-root/

database/
  ├── schema.sql
  └── seed.sql

includes/
  └── db.php

auth/
  ├── register.php
  ├── login.php
  └── logout.php

modules/
  ├── dashboard.php
  └── list.php

public/
  └── index.php

assets/

README.md
```

---

## 🗄️ Database Setup

1. Open phpMyAdmin
2. Create a new database:

   ```
  pothen
   ```
3. Import:

   * `database/schema.sql`
   * `database/seed.sql`

4. Verify `project-root/includes/db.php` uses the same DB name (`pothen`).

---

## ▶️ How to Run the Project

1. Install XAMPP / LAMP (Ubuntu)

2. Place the project inside:

   ```
   /var/www/html/   (Linux)
   ```

   or

   ```
   htdocs/          (XAMPP)
   ```

3. Start Apache & MySQL

4. Open in browser:

   ```
  http://localhost/CSE-326/project-root/index.php
   ```

5. Demo login credentials (after seed import):

  * `admin@test.com` / `test123`
  * `nikos@test.com` / `test123`
  * `maria@test.com` / `test123`

---

## 🔐 Security Features

The application follows secure coding practices:

✔ PDO with Prepared Statements (prevents SQL Injection)
✔ password_hash() for secure password storage
✔ password_verify() for authentication
✔ htmlspecialchars() to prevent XSS attacks
✔ Session-based authentication (Session Guard)
✔ No exposure of sensitive database errors

---

## 🔍 Features Implemented

* User Registration with validation
* Secure Login System
* Logout functionality
* Protected Dashboard (authenticated users only)
* Financial declarations listing
* Keyword Search (GET method, bookmarkable)

---

## 📊 Notes

* Each team member has contributed with at least one commit
* Authentication flow is fully functional
* The project follows the required folder structure
* All required security practices have been implemented

---

## 📎 Repository

GitHub Repository:
https://github.com/Russellvikra/CSE-326.git

## 📤 Moodle Deliverable Checklist

* 1 link στο GitHub repository της ομάδας
* README με ονόματα + AM
* README με κατανομή εργασιών ανά φοιτητή/αρχείο
* README με οδηγίες εγκατάστασης LAMP + import `schema.sql` / `seed.sql`
