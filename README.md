# Web Engineering Project - Pothen Esxes Monitoring System

## Team Members

- Konstandinos Avramidis - AM: 27779
- Russell Vickramasingam - AM: 27688
- Alexandros Pelekanos - AM: 30713

## Project Description

This project is a web-based Pothen Esxes Monitoring System for managing and monitoring financial declarations of public officials in Cyprus.

The system supports:

- Public pages and declaration browsing
- User authentication and protected user dashboards
- Submission and profile management flows
- Administrative management tools
- API endpoints for declarations and statistics

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
  - Pages (PHP): `project-root/auth/login.php`, `project-root/auth/logout.php`, `project-root/auth/register.php`, `project-root/submit/dashboard.php`, `project-root/submit/my_submissions.php`, `project-root/submit/profile.php`, `project-root/submit/submit.php`
  - Page markup: `project-root/submit/submit.html`
  - Page CSS/JS support (shared where needed): `project-root/assets/css/auth.css`, `project-root/assets/css/submit.css`, `project-root/assets/js/header.js`, `project-root/assets/js/footer.js`
  - Database: `project-root/database/schema.sql`, `project-root/database/seed.sql`, `project-root/includes/db.php`
  - Shared modules: `project-root/modules/dashboard.php`, `project-root/modules/declaration.php`, `project-root/modules/list.php`, `project-root/modules/stats.php`

- Russell Vickramasingam
  - Pages (PHP): `project-root/index.php`, `project-root/public/public.php`
  - Page markup: `project-root/public/public.html`
  - Page CSS/JS: `project-root/assets/css/home.css`, `project-root/assets/css/public.css`, `project-root/assets/css/header.css`, `project-root/assets/css/footer.css`, `project-root/assets/css/list.css`, `project-root/assets/css/declaration.css`, `project-root/assets/css/dashboard.css`, `project-root/assets/js/header.js`, `project-root/assets/js/footer.js`
  - Shared modules: `project-root/modules/dashboard.php`, `project-root/modules/declaration.php`, `project-root/modules/list.php`, `project-root/modules/stats.php`

- Alexandros Pelekanos
  - Pages (PHP): `project-root/admin/admin.php`, `project-root/admin/configure.php`, `project-root/admin/manage_submissions.php`, `project-root/admin/manage_users.php`, `project-root/admin/reports.php`, `project-root/api/index.php`, `project-root/api/declarations.php`, `project-root/api/stats.php`
  - Page markup: `project-root/admin/admin.html`
  - Page CSS/JS support: `project-root/assets/css/admin.css`, `project-root/assets/js/header.js`, `project-root/assets/js/footer.js`
  - Shared modules: `project-root/modules/dashboard.php`, `project-root/modules/declaration.php`, `project-root/modules/list.php`, `project-root/modules/search_dashboard.php`, `project-root/modules/stats.php`

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
  public/
    public.html
    public.php
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

## Features Implemented

- User registration, login, and logout
- User dashboard and personal submission management
- Declaration listing and detailed declaration pages
- Search and statistics modules
- Public-facing pages
- Admin pages for configuration, users, submissions, and reports
- API endpoints for declarations and statistics

## Repository

GitHub: https://github.com/Russellvikra/CSE-326.git

## Moodle Deliverable Checklist

- GitHub repository link
- README with names and AM numbers
- README with work distribution per student/file
- README with LAMP setup steps and DB import instructions
