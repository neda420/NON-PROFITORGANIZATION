# Architecture – Helping Paws

> **Document status:** Step 1 of project rebuild — Audit & Architecture Planning.  
> This document covers the **current-state inventory**, **identified gaps**, and the **target architecture** for the full rebuild.

---

## Table of Contents

1. [Current-State Overview](#1-current-state-overview)
2. [File Inventory](#2-file-inventory)
3. [Feature Inventory](#3-feature-inventory)
4. [Gaps, Code-Quality Issues & Security Concerns](#4-gaps-code-quality-issues--security-concerns)
5. [Target Architecture](#5-target-architecture)
6. [Target Folder Layout](#6-target-folder-layout)
7. [Target Database Schema](#7-target-database-schema)
8. [Target Authentication Flow](#8-target-authentication-flow)
9. [Target Security Architecture](#9-target-security-architecture)
10. [Target CI/CD Pipeline](#10-target-cicd-pipeline)

---

## 1. Current-State Overview

```
                          ┌──────────────────────┐
  Browser / Client ──────►│  Nginx (port 80/443) │
                          │  TLS termination      │
                          │  Static file serving  │
                          │  Security headers     │
                          └──────────┬───────────┘
                                     │ FastCGI
                          ┌──────────▼───────────┐
                          │  PHP-FPM 8.2          │
                          │  Application code     │
                          └──────────┬───────────┘
                                     │ mysqli (TCP)
                          ┌──────────▼───────────┐
                          │  MySQL 8.0            │
                          │  helping_paws2 DB     │
                          └──────────────────────┘
```

All PHP entry points live directly at the project root alongside HTML, CSS, and configuration files.  
No front controller, no autoloading, and no Model or Controller layer exists today.

---

## 2. File Inventory

### PHP Entry Points (root-level, web-accessible)

| File | Role | Auth guard |
|---|---|---|
| `health.php` | JSON health-check endpoint (DB ping) | None |
| `login.php` | Donor login POST handler | None |
| `logout.php` | Donor session destroy | Donor |
| `register_donor.php` | Donor registration POST handler | None |
| `donor_profile.php` | Donor profile page | Donor |
| `DonorLanding.php` | Donor dashboard | Donor |
| `DonorHistory.php` | Donation history list | Donor |
| `Donation.php` | Donation submission POST handler | Donor |
| `admin_login.php` | Admin login form + POST handler | None |
| `admin_logout.php` | Admin session destroy | Admin |
| `admin_panel.php` | Admin contact-message panel | Admin |
| `delete.php` | Delete a contact message | Admin |
| `search_rescued_animals.php` | Animal search results page | None |
| `contact_process.php` | Contact form POST handler | None |
| `submit_volunteer_form.php` | Volunteer sign-up POST handler | None |
| `get_total_volunteers.php` | JSON API — volunteer count | None |
| `public_metrics.php` | JSON public aggregate metrics | None |
| `csrf_token.php` | CSRF token fetch endpoint | None |

### HTML Pages (static)

| File | Purpose |
|---|---|
| `index.html` | Site root redirect / splash |
| `landingPage.html` | Primary public landing page |
| `LoginDonor.html` | Donor login form |
| `DonorLanding.html` | Static version of donor dashboard |
| `Donation.html` | Donation submission form |
| `Donor.html` | Donor information page |
| `DonorCredentials.html` | Donor credentials form |
| `admin_login.html` | Static admin login form (duplicate of `admin_login.php` form) |

### CSS Files

| Location | Files | Notes |
|---|---|---|
| Root | `style.css`, `landingPage.css`, `adminStyles.css`, `admin_panel_styles.css`, `admin_styles.css`, `chart.css` | 6 files, inconsistently named |
| `css/` | `DonorcredentialsStyles.css`, `donation.css`, `donor_history.css`, `donorprofile.css`, `styles.css` | 5 files in subdirectory |

### Shared Infrastructure (`src/`)

| File | Responsibility |
|---|---|
| `src/config/app.php` | PHP error config, session hardening; does **not** call `session_start()` |
| `src/config/database.php` | Parses `.env`, returns singleton `mysqli` connection |
| `src/helpers/csrf.php` | `csrfToken()`, `csrfTokenField()`, `verifyCsrfToken()` |
| `src/helpers/flash.php` | Session-backed flash messages: `flashSet()`, `flashRender()` |
| `src/helpers/http.php` | `redirectTo()`, `jsonResponse()` |
| `src/helpers/logger.php` | Newline-delimited JSON to `logs/app.log` |
| `src/helpers/rate_limit.php` | In-session rate limiting: `enforceRateLimit()` |
| `src/helpers/sanitize.php` | `inputString()`, `inputEmail()`, `inputFloat()`, `e()`, etc. |
| `src/middleware/require_auth.php` | Redirects to `LoginDonor.html` if `$_SESSION['donorId']` absent |
| `src/middleware/require_admin.php` | Redirects to `admin_login.php` if `$_SESSION['admin_id']` absent |

### Image Directories

| Directory | Contents |
|---|---|
| `img/` | `Thank-YOu-Pets.jpg`, `back.jpeg`, `catdogs.jpeg`, `helpingcatdogs.jpeg`, `techfest.jpg`, `th.jpeg` |
| `Images/` | Additional images (second image directory) |
| `assets/` | `favicon.ico` |

### Database Migrations

| File | Description |
|---|---|
| `migrations/001_initial_schema.sql` | Full initial schema: 6 tables + default admin seed |
| `migrations/002_audit_and_privacy.sql` | `admin_audit_log`, `donor_preferences`, `data_deletion_requests` |

### Docker / Infrastructure

| File | Purpose |
|---|---|
| `Dockerfile` | Multi-stage-less Alpine image: PHP-FPM 8.2 + Nginx + Supervisor |
| `docker-compose.yml` | App + MySQL 8.0 services; internal bridge network |
| `docker/nginx.conf` | Global Nginx config; security headers |
| `docker/default.conf` | Server block; PHP-FPM pass-through; sensitive-path blocking |
| `docker/supervisord.conf` | Supervises Nginx + PHP-FPM in a single container |

### GitHub Actions Workflows

| File | Trigger | What it does |
|---|---|---|
| `.github/workflows/ci.yml` | Push / PR on any branch | HTML validation only (`html-validate`) |
| `.github/workflows/cd.yml` | Push to `main` / manual | Deploys whole repository to GitHub Pages |

### Tests

| File | Type |
|---|---|
| `tests/smoke.php` | Hand-rolled assertions (not PHPUnit); tests `sanitize`, `csrf`, `rate_limit` helpers |

### Other Notable Files

| File | Purpose |
|---|---|
| `landingPage.js` | Front-end JS for the landing page |
| `.env.example` | Canonical environment variable template |
| `db_helping_paws2.sql` | Ad-hoc SQL dump at project root (legacy artifact) |
| `CONTRIBUTING.md` | Branching, commit conventions, PR process |

---

## 3. Feature Inventory

| Area | Feature | Entry point(s) |
|---|---|---|
| **Public** | Landing page | `landingPage.html` |
| **Public** | Animal search | `search_rescued_animals.php` |
| **Public** | Contact form | `contact_process.php` |
| **Public** | Volunteer sign-up | `submit_volunteer_form.php` |
| **Public** | Volunteer count API | `get_total_volunteers.php` |
| **Public** | Public aggregate metrics | `public_metrics.php` |
| **Donor** | Registration | `register_donor.php` |
| **Donor** | Login / logout | `login.php`, `logout.php` |
| **Donor** | Dashboard | `DonorLanding.php` |
| **Donor** | Make a donation | `Donation.php` |
| **Donor** | Donation history | `DonorHistory.php` |
| **Donor** | Profile view | `donor_profile.php` |
| **Admin** | Login / logout | `admin_login.php`, `admin_logout.php` |
| **Admin** | Contact-message panel | `admin_panel.php` |
| **Admin** | Delete contact message | `delete.php` |
| **Infra** | Application health check | `health.php` |

---

## 4. Gaps, Code-Quality Issues & Security Concerns

### Structural / Code-Quality

| # | Issue | Impact |
|---|---|---|
| Q1 | **No Composer / PSR-4 autoloading** — every file manually `require_once`s its dependencies. | High maintenance burden; brittle include chains; blocks unit testing. |
| Q2 | **No MVC separation** — business logic (queries, validation, redirects) mixed into HTML-output PHP files. | Hard to test, maintain, and reuse logic. |
| Q3 | **No Model layer** — SQL queries are scattered across 16+ entry-point files. | Duplication; no single source of truth per entity. |
| Q4 | **No front controller / router** — every page is a direct URL; no centralized request dispatch. | URL structure is tied to file names; no clean URL support. |
| Q5 | **Flat root directory** — PHP, HTML, CSS, SQL, and Docker files all co-exist at the project root. | Difficult to navigate; tight coupling between concerns. |
| Q6 | **Duplicate HTML/PHP pairs** — `Donation.html` + `Donation.php`, `DonorLanding.html` + `DonorLanding.php`, `admin_login.html` + `admin_login.php`. | Confusion about which file is canonical; risk of divergence. |
| Q7 | **Duplicate image directories** — `img/` and `Images/` both hold site images. | Inconsistent asset paths across pages. |
| Q8 | **Inconsistent naming conventions** — PHP files mix PascalCase (`DonorLanding.php`), snake_case (`donor_profile.php`), and lowercase (`login.php`); DB tables mix `UPPER_CASE`, `PascalCase`, and `snake_case`. | Cognitive overhead; typo-prone references. |
| Q9 | **Scattered CSS** — 11 CSS files split across the root and `css/` with overlapping scopes. | Style conflicts; no single source of truth for design tokens. |
| Q10 | **`session_start()` absent from `app.php`** — each entry point must call it independently. | Any page that forgets `session_start()` before an auth guard breaks silently. |
| Q11 | **MySQLi instead of PDO** — database layer uses MySQLi. | Limits abstraction; different API to PDO makes future DB-driver changes harder. |
| Q12 | **No input validation layer** — field-level validation (required, max-length, format) is ad-hoc per entry point with no shared ruleset. | Inconsistent validation; fields can be silently skipped. |
| Q13 | **`db_helping_paws2.sql` at project root** — stale SQL dump checked into source. | May contain sensitive or out-of-date schema state. |

### Testing

| # | Issue |
|---|---|
| T1 | **No PHPUnit** — `tests/smoke.php` is a stand-alone assertion script, not an auto-discoverable test suite. |
| T2 | **No integration tests** — no tests exercise full request/response cycles. |
| T3 | **No model or controller unit tests** — because neither layer exists yet. |

### CI/CD

| # | Issue |
|---|---|
| C1 | **`ci.yml` only validates HTML** — no PHP syntax check, no PHPCS/PSR-12 lint, no security scan, no Docker build job. |
| C2 | **`cd.yml` deploys to GitHub Pages** — GitHub Pages serves only static files; this is a PHP/MySQL app. The CD workflow should deploy to a PHP-capable host, not GitHub Pages. |
| C3 | **No automated smoke test in CI** — `tests/smoke.php` exists but is not wired into the workflow. |

### Security

| # | Concern |
|---|---|
| S1 | **In-session rate limiting only** — `enforceRateLimit()` is tied to the PHP session. A new session (e.g., fresh browser) resets the counter; distributed attackers are unaffected. Supplement with Nginx `limit_req_zone`. |
| S2 | **No password-reset flow** — there is no self-service mechanism to recover a locked/forgotten account. |
| S3 | **Admin login combines form rendering and POST handling** in one file but `admin_login.html` also exists as a separate static form, creating two paths to the same action. |
| S4 | **`csrf_token.php` is a public endpoint** that returns a CSRF token over GET; token-fetch endpoints must be evaluated for cross-origin abuse when a relaxed CORS policy is in use. |

---

## 5. Target Architecture

### Design principles

- **PHP 8.2 + Composer PSR-4 autoloading** — no more manual `require_once` chains.
- **MVC-lite** — thin controllers, models per entity, PHP template views.
- **Single entry point** — `public/index.php` dispatches all requests through a lightweight router.
- **PDO** replaces MySQLi for a consistent, driver-agnostic database layer.
- **All web-accessible assets under `public/`** — PHP source, tests, migrations, and config remain outside the web root.
- **Consolidated assets** — one CSS directory (`public/css/`), one image directory (`public/img/`).
- **PHPUnit test suite** — unit tests for Models and Helpers; integration smoke tests.
- **Corrected CI** — lint + security scan + PHPUnit + Docker build, all in one pipeline.

### High-Level Diagram (target)

```
                          ┌──────────────────────┐
  Browser / Client ──────►│  Nginx (port 80/443) │
                          │  TLS termination      │
                          │  Static file serving  │
                          │  Security headers     │
                          │  Rate limiting        │
                          └──────────┬───────────┘
                                     │ FastCGI → public/index.php
                          ┌──────────▼───────────┐
                          │  PHP-FPM 8.2          │
                          │  Router               │
                          │  Controllers          │
                          │  Models (PDO)         │
                          │  Views (PHP templates)│
                          └──────────┬───────────┘
                                     │ PDO (TCP)
                          ┌──────────▼───────────┐
                          │  MySQL 8.0            │
                          │  helping_paws2 DB     │
                          └──────────────────────┘
```

### Entry-Point Pattern (target)

Every request hits `public/index.php`, which:

1. Requires `src/bootstrap.php` (autoloader, session, env, error config).
2. Instantiates the `Router` and registers routes.
3. Dispatches to the matched `Controller::action()`.
4. The controller calls Model methods, builds a data array, and renders a View template.

```
public/index.php
    └── src/bootstrap.php       ← autoload, session, config, DB
          └── Router::dispatch()
                └── XxxController::action()
                      ├── XxxModel::method()   ← PDO queries
                      └── view('path/to/template.php', $data)
```

### Shared Infrastructure (target `src/`)

| Namespace / Path | Responsibility |
|---|---|
| `HelpingPaws\Config\App` | PHP error config, session hardening, calls `session_start()` |
| `HelpingPaws\Config\Database` | PDO singleton, env-driven credentials |
| `HelpingPaws\Core\Router` | Lightweight route registration and dispatch |
| `HelpingPaws\Helpers\Csrf` | CSRF token generation, form field embedding, verification |
| `HelpingPaws\Helpers\Flash` | Session-backed flash messages |
| `HelpingPaws\Helpers\Http` | `redirectTo()`, `jsonResponse()` |
| `HelpingPaws\Helpers\Logger` | Structured JSON logger |
| `HelpingPaws\Helpers\RateLimit` | In-session rate limiting (supplemented by Nginx) |
| `HelpingPaws\Helpers\Sanitize` | Input sanitization and output escaping |
| `HelpingPaws\Middleware\AuthMiddleware` | Donor auth guard |
| `HelpingPaws\Middleware\AdminMiddleware` | Admin auth guard |
| `HelpingPaws\Models\DonorModel` | CRUD for `donors` table |
| `HelpingPaws\Models\AdminModel` | CRUD for `admins` table |
| `HelpingPaws\Models\DonationModel` | CRUD for `donations` table |
| `HelpingPaws\Models\VolunteerModel` | CRUD for `volunteers` table |
| `HelpingPaws\Models\AnimalModel` | CRUD for `rescued_animals` table |
| `HelpingPaws\Models\ContactModel` | CRUD for `contact_messages` table |
| `HelpingPaws\Controllers\PublicController` | Landing, animal search, contact, volunteer |
| `HelpingPaws\Controllers\DonorController` | Register, login, logout, dashboard, donate, history, profile |
| `HelpingPaws\Controllers\AdminController` | Login, logout, messages panel, delete message |

---

## 6. Target Folder Layout

```
NON-PROFITORGANIZATION/
├── public/                         ← web root (Nginx points here)
│   ├── index.php                   ← single entry point
│   ├── css/
│   │   ├── main.css                ← consolidated public styles
│   │   ├── donor.css               ← donor portal styles
│   │   └── admin.css               ← admin panel styles
│   ├── img/                        ← all site images
│   └── favicon.ico
│
├── src/
│   ├── bootstrap.php               ← autoload + session + env + DB
│   ├── Config/
│   │   ├── App.php                 ← error config, session hardening
│   │   └── Database.php            ← PDO singleton
│   ├── Core/
│   │   └── Router.php              ← route registration & dispatch
│   ├── Helpers/
│   │   ├── Csrf.php
│   │   ├── Flash.php
│   │   ├── Http.php
│   │   ├── Logger.php
│   │   ├── RateLimit.php
│   │   └── Sanitize.php
│   ├── Middleware/
│   │   ├── AuthMiddleware.php      ← donor auth guard
│   │   └── AdminMiddleware.php     ← admin auth guard
│   ├── Models/
│   │   ├── DonorModel.php
│   │   ├── AdminModel.php
│   │   ├── DonationModel.php
│   │   ├── VolunteerModel.php
│   │   ├── AnimalModel.php
│   │   └── ContactModel.php
│   ├── Controllers/
│   │   ├── PublicController.php
│   │   ├── DonorController.php
│   │   └── AdminController.php
│   └── Views/
│       ├── layouts/
│       │   ├── public.php          ← base layout for public pages
│       │   ├── donor.php           ← base layout for donor portal
│       │   └── admin.php           ← base layout for admin panel
│       ├── public/
│       │   ├── landing.php
│       │   ├── animals.php
│       │   ├── contact.php
│       │   └── volunteer.php
│       ├── donor/
│       │   ├── login.php
│       │   ├── register.php
│       │   ├── dashboard.php
│       │   ├── donate.php
│       │   ├── history.php
│       │   └── profile.php
│       └── admin/
│           ├── login.php
│           ├── panel.php
│           └── messages.php
│
├── migrations/
│   ├── 001_initial_schema.sql
│   ├── 002_audit_and_privacy.sql
│   └── 003_rename_tables.sql       ← normalise table naming conventions
│
├── tests/
│   ├── Unit/
│   │   ├── Helpers/
│   │   └── Models/
│   └── Integration/
│       └── SmokeTest.php
│
├── docker/
│   ├── nginx.conf
│   ├── default.conf                ← updated: limit_req_zone on login & contact
│   └── supervisord.conf
│
├── docs/
│   ├── ARCHITECTURE.md             ← this file
│   ├── DATA_GOVERNANCE.md
│   ├── RELEASE_CRITERIA.md
│   └── RUNBOOK.md
│
├── logs/                           ← runtime only; excluded from VCS
├── Dockerfile
├── docker-compose.yml
├── composer.json
├── phpunit.xml
├── .env.example
├── .gitignore
├── .dockerignore
├── README.md
└── CONTRIBUTING.md
```

---

## 7. Target Database Schema

Table names will be normalised to `snake_case` in migration `003_rename_tables.sql`.

| Old name | Target name | Notes |
|---|---|---|
| `donor_t` | `donors` | Remove `_t` suffix |
| `ADMIN_TABLE` | `admins` | Lowercase, remove `_TABLE` suffix |
| `contact_messages` | `contact_messages` | No change |
| `Donation_T` | `donations` | Lowercase, remove `_T` suffix |
| `VOLUNTEER_TABLE` | `volunteers` | Lowercase, remove `_TABLE` suffix |
| `RESCUED_ANIMALS` | `rescued_animals` | Lowercase |
| `admin_audit_log` | `admin_audit_log` | No change |
| `donor_preferences` | `donor_preferences` | No change |
| `data_deletion_requests` | `data_deletion_requests` | No change |

### Schema summary (target names)

| Table | Key columns | Notes |
|---|---|---|
| `donors` | `donor_id` (PK), `email` (UQ), `password` | bcrypt/argon2 hash |
| `admins` | `id` (PK, AUTO), `username` (UQ), `password` | bcrypt/argon2 hash |
| `contact_messages` | `id` (PK), `name`, `email`, `message`, `admin_id` (FK→admins) | |
| `donations` | `id` (PK), `donor_id` (FK→donors), `amount` DECIMAL(12,2) | |
| `volunteers` | `id` (PK), `name`, `email`, `phone` | |
| `rescued_animals` | `animal_id` (PK), `animal_type`, `animal_gender`, `vet_bills` | |
| `admin_audit_log` | `id` (PK), `admin_id` (FK), `action`, `before_state` JSON, `after_state` JSON | |
| `donor_preferences` | `donor_id` (PK/FK), consent flags | |
| `data_deletion_requests` | `id` (PK), `donor_id` (FK), `status`, `resolved_by` (FK→admins) | |

Full DDL: `migrations/001_initial_schema.sql` (original), `migrations/003_rename_tables.sql` (target rename).

---

## 8. Target Authentication Flow

### Donor

1. GET `POST /donor/login` → `DonorController::login()`
2. `DonorModel::findByUsername()` — PDO prepared statement
3. `password_verify()` against bcrypt hash
4. `session_regenerate_id(true)` → set `$_SESSION['donor_id']`
5. Flash success → redirect to `/donor/dashboard`
6. Logout: `GET /donor/logout` → destroys session → redirect to `/donor/login`

### Admin

1. POST `/admin/login` → `AdminController::login()`
2. `AdminModel::findByUsername()` — PDO prepared statement
3. `password_verify()` against bcrypt hash
4. `session_regenerate_id(true)` → set `$_SESSION['admin_id']`
5. Flash success → redirect to `/admin/panel`
6. Logout: `GET /admin/logout` → destroys session → redirect to `/admin/login`

---

## 9. Target Security Architecture

| Control | Implementation |
|---|---|
| **No hardcoded credentials** | All DB creds read from environment variables via `.env` |
| **Password hashing** | `password_hash(PASSWORD_BCRYPT)` / `password_verify()` |
| **SQL injection prevention** | All queries use PDO prepared statements |
| **CSRF protection** | Per-session token in every state-changing form; `Csrf::verify()` at top of every POST handler |
| **Output encoding** | All user data escaped via `e()` (`htmlspecialchars`) |
| **Session hardening** | `strict_mode`, `HttpOnly`, `Secure`, `SameSite=Lax`; `session_start()` called in bootstrap |
| **Session fixation** | `session_regenerate_id(true)` on every privilege change |
| **Auth guards** | `AuthMiddleware` / `AdminMiddleware` redirect immediately on missing/invalid session |
| **Rate limiting** | Nginx `limit_req_zone` on `/donor/login`, `/admin/login`, `/contact`; supplemented by in-session `RateLimit` helper |
| **Nginx security headers** | `X-Content-Type-Options`, `X-Frame-Options`, CSP, `Referrer-Policy` |
| **Sensitive path blocking** | `src/`, `logs/`, `migrations/`, `.env` blocked at Nginx level; web root is `public/` only |
| **Structured audit logs** | Admin actions logged to `logs/app.log` (JSON, correlation ID) |
| **Password reset** | Token-based self-service reset (token stored in DB with expiry) |

---

## 10. Target CI/CD Pipeline

```
Push / PR
   │
   ├── php-lint        PHP syntax check (php -l) on all .php files
   ├── phpcs           PSR-12 code style (phpcs)
   ├── security-scan   grep for SQL interpolation, var_dump, hardcoded creds
   ├── phpunit         Full PHPUnit test suite (Unit + Integration)
   └── docker-build    docker build + health.php smoke test

Push to main (after CI passes)
   │
   └── deploy          Pull on server → DB backup → apply migrations
                        → docker compose up --build → verify health.php
```

Defined in `.github/workflows/ci.yml` (CI) and `.github/workflows/cd.yml` (CD to PHP host, not GitHub Pages).

---

*Last updated: Step 1 — Audit & Architecture Planning (rebuild project).*
