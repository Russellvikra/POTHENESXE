# Web Engineering Project - Pothen Esxes Monitoring System

## Team Members

- Konstandinos Avramidis - AM: 27779
- Russell Vickramasingam - AM: 30713
- Alexandros Pelekanos - AM: 27688

## Project Description

This project is a web-based Pothen Esxes Monitoring System for managing and monitoring financial declarations of public officials in Cyprus.

The system supports:

- Mandatory authentication before accessing application modules
- Role-based protected dashboards and modules
- Submission and profile management flows
- Administrative management tools
- API endpoints with access control

## Technologies Used

- PHP
- MySQL / MariaDB
- HTML, CSS, JavaScript
- PDO (prepared statements)

## Work Distribution (Per Person)

Each member is assigned pages and works on the page stack (PHP, CSS, and JavaScript where available).
All module files are shared by all members.
Database files (`schema.sql`, `seed.sql`, `db.php`) are assigned to Konstandinos Avramidis.

- Konstandinos Avramidis
  - Authentication flow (login/register/logout) and session handling
  - Submit flow (dashboard, profile, my submissions, submit declaration)
  - Database schema/seed setup and DB connection integration

- Russell Vickramasingam
  - Main authenticated app entry and navigation structure
  - Declaration discovery flow (search/list/declaration views)
  - Shared UI integration (header/footer and page styling)

- Alexandros Pelekanos
  - Admin flow (users, submissions, configuration, reports)
  - API flow (index/declarations/stats) with access control
  - Admin reporting and statistics features

## Project Structure

```
project-root/
  index.php
  setup.php
  admin/
    admin.html
    admin.php
    configure.php
    manage_submissions.php
    manage_users.php
    reports.php
  api/
    declarations.php
    index.php
    stats.php
  assets/
    css/
      admin.css
      auth.css
      dashboard.css
      declaration.css
      footer.css
      header.css
      home.css
      list.css
      public.css
      submit.css
    images/
    include/
      footer.html
      header.html
    js/
      footer.js
      header.js
  auth/
    login.php
    logout.php
    register.php
  database/
    schema.sql
    seed.sql
  includes/
    db.php
  modules/
    dashboard.php
    declaration.php
    list.php
    search_dashboard.php
    stats.php
  submit/
    dashboard.php
    my_submissions.php
    profile.php
    submit.html
    submit.php
README.md
```

## Database Setup

1. Open phpMyAdmin.
2. Create a new database named `pothen`.
3. Import:
   - `project-root/database/schema.sql`
   - `project-root/database/seed.sql`
4. Verify `project-root/includes/db.php` uses the same database credentials.

## How to Run the Project

1. Install and start Apache + MySQL (XAMPP/LAMPP/LAMP).
2. Place the repository in your web root (for example `htdocs` in XAMPP).
3. Open:
   - `http://localhost/POTHENESXE/project-root/index.php`
4. Optional setup endpoint (if needed by your flow):
   - `http://localhost/POTHENESXE/project-root/setup.php`

## Security Features

- PDO prepared statements to reduce SQL injection risk
- `password_hash()` for password storage
- `password_verify()` during authentication
- Output escaping (for example `htmlspecialchars()`) where needed
- Session-based authentication and access control

## Secure Coding Rules (Mandatory)

- Always use prepared statements (`$pdo->prepare(...)` + `execute(...)`) and never SQL string concatenation.
- Always use `password_hash()` for stored passwords and never plain-text passwords.
- Always use `htmlspecialchars()` when echoing user-controlled data.
- Always call `exit;` immediately after every `header('Location: ...')` redirect.
- Never use `die($e->getMessage())` because it can expose sensitive backend/database details.

## Features Implemented

- User registration, login, and logout
- Login-required access control across modules
- User dashboard and personal submission management
- Declaration listing and detailed declaration pages
- Search and statistics modules
- Admin pages for configuration, users, submissions, and reports
- API endpoints for declarations and statistics (authenticated access)

## Repository

GitHub: https://github.com/Russellvikra/POTHENESXE

## Moodle Deliverable Checklist

- GitHub repository link
- README with names and AM numbers
- README with work distribution per student/file
- README with LAMP setup steps and DB import instructions
