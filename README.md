# UniFind | University Lost & Found Web Platform

**A full-stack academic web application that helps King Saud University students report, find, and manage lost-and-found items in one place.**

UniFind was developed collaboratively by a five-member student team as part of an academic software engineering project. The platform addresses a common campus problem: information about missing belongings is often scattered across group chats and social media, making reports difficult to find and follow up on.

> **Project status:** Academic prototype. The repository contains a local-development implementation; UniFind is not presented as an officially deployed King Saud University service.

## Features

- **Account management:** User registration, sign-in, sign-out, and profile editing.
- **Item reports:** Create reports with an item name, category, description, date, location, contact number, and image.
- **Browse and search:** Search report details using keywords, filter by item category, and sort by date.
- **Personal report management:** View, edit, and delete reports submitted by the signed-in user.
- **Saved reports:** Save reports for later reference and remove them from the saved list.
- **Administrative management:** Review, search, edit, and delete reports through a separate admin interface.

## Tech Stack

| Layer | Technologies |
| --- | --- |
| Front end | HTML5, CSS3, JavaScript |
| Application / back end | PHP |
| Database | MySQL |
| Database interface | MySQLi |

## Architecture & Data Design

UniFind was designed around a **three-tier architecture**:

1. **Presentation layer:** Web pages and client-side interactions.
2. **Application layer:** PHP-based authentication, report handling, input checks, and application logic.
3. **Data layer:** MySQL storage for accounts, reports, item categories, and saved reports.

The database schema defines five main tables: `users`, `administrators`, `reports`, `categories`, and `saved_reports`. The linked project brief includes the architecture, selected interface screenshots, and an entity-relationship diagram (ERD). The ERD reflects the academic design stage; the current SQL schema is the reference for implemented database fields.

## Repository Guide

| Files / folders | Purpose |
| --- | --- |
| `index.php` | Public landing page |
| `register.php`, `login.php`, `logout.php` | Registration and authentication workflows |
| `reports.php`, `report-details.php` | Browse, search, filter, and view item reports |
| `add-report.php`, `edit-report.php`, `delete-report.php`, `my-reports.php` | Create and manage personal reports |
| `saved.php`, `toggle-save.php` | Save or unsave reports |
| `admin-reports.php`, `admin-edit-report.php`, `admin-delete-report.php` | Admin report management |
| `profile.php` | User profile management |
| `styles.css`, `app.js` | Styling and client-side behavior |
| `db.php`, `unifind_db.sql` | Database connection and SQL schema |
| `images/` | Static assets used by the application |
| `uploads/` | Uploaded report images (runtime content) |
| `UniFind-Project-Brief-Verified.pdf` | Concise project documentation |

## Run Locally

**Prerequisites:** PHP with MySQLi enabled, a local web server (or PHP's development server), and a MySQL-compatible database.

1. Clone the repository:
   ```bash
   git clone https://github.com/leen686/Unifind.git
   cd Unifind
   ```
2. Create a local database named `unifind_db` and import `unifind_db.sql`. **The SQL dump currently contains sample accounts, including a plaintext demo administrator password. Replace or remove the demo data before running the project, and do not deploy it as-is.**
3. Configure the local database connection in `db.php` for your own MySQL host, port, username, and password. Never reuse sample credentials in a public or production deployment.
4. Make sure the `uploads/` directory exists and the local web-server process can write to it.
5. Serve the project using a local PHP server, for example:
   ```bash
   php -S localhost:8000
   ```
6. Open `http://localhost:8000` in your browser.

This setup is intended for **local development and review**, not production hosting.

## Research, Testing & Evaluation

The team used interviews and questionnaires to understand students' lost-and-found challenges and to shape the product requirements.

- **Requirements research:** Five student interviews and 20 questionnaire responses.
- **Development process:** Documented requirements, product backlog, architectural design, implementation, and testing.
- **Testing:** User-story acceptance testing, integration testing, and user acceptance testing (UAT).
- **UAT participants:** Five university students evaluated core tasks such as registration, report creation, search, editing, and deletion.
- **UAT findings:** All five participants agreed or strongly agreed that the interface was clear and that creating a lost-item report was easy. Four out of five agreed or strongly agreed that UniFind met their needs for campus lost-and-found reporting.

These findings reflect a **small academic evaluation sample** and should not be interpreted as performance or usability guarantees at campus scale.

## Project Documentation

[**View the UniFind Project Brief (PDF)**](UniFind-Project-Brief-Verified.pdf)

The brief summarizes the problem, solution, architecture, design-stage ERD, original interface examples, and testing results.

## Team

This is a **collaborative academic project** developed by:

- Leen Almutairi
- Jana Aldakheel
- Latifah Alsaif
- Sadeem Alnassar
- Badriyah Aljabri

King Saud University — College of Computer and Information Sciences, Department of Information Technology.

## Current Scope & Future Improvements

UniFind currently focuses on a single university and relies on reports submitted by users. The academic report identifies potential future additions such as notifications, automated lost-and-found matching, a mobile application, and support for additional institutions.

---

*Academic project for learning and demonstration purposes. Not an official university lost-and-found service.*
