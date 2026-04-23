# Helping Paws – Non-Profit Animal Rescue Organization

> A PHP/MySQL web application for managing donors, volunteers, animal adoptions, and contact messages for the Helping Paws Animal Rescue shelter.

---

## Table of Contents

1. [Overview](#overview)
2. [Quick Start (Docker)](#quick-start-docker)
3. [Local Development (without Docker)](#local-development-without-docker)
4. [Environment Variables](#environment-variables)
5. [Database Migrations](#database-migrations)
6. [Project Structure](#project-structure)
7. [Security Model](#security-model)
8. [Deployment](#deployment)
9. [Troubleshooting](#troubleshooting)
10. [Contributing](#contributing)

---

## Overview

| Feature | Description |
|---|---|
| Public site | Landing page, animal search, adopt animals, contact form |
| Donor portal | Registration, login, donation, history, profile |
| Admin panel | Manage contact messages |
| Volunteer | Sign-up form and volunteer count dashboard |

**Tech stack:** PHP 8.2 · MySQL 8.0 · Nginx · Docker · GitHub Actions CI

---

## Quick Start (Docker)

### Prerequisites

- Docker ≥ 24 and Docker Compose v2
- `git`

### Steps

```bash
# 1. Clone
git clone https://github.com/neda420/NON-PROFITORGANIZATION.git
cd NON-PROFITORGANIZATION

# 2. Create your local environment file
cp .env.example .env
# Edit .env and set strong passwords for DB_PASSWORD and DB_ROOT_PASSWORD

# 3. Start all services (app + db)
docker compose up --build -d

# 4. Open the site at:
#    http://localhost:8080
```

The MySQL container automatically applies `migrations/001_initial_schema.sql` on first start.

### Default Admin Credentials

> **Change these immediately after first login.**

| Field    | Value |
|----------|-------|
| URL      | `http://localhost:8080/admin_login.php` |
| Username | `admin` |
| Password | `Admin@HelpingPaws2024` |

To generate a new hash: `php -r "echo password_hash('YourNewPassword', PASSWORD_BCRYPT);"`  
Then update the `ADMIN_TABLE` row directly in MySQL.

---

## Local Development (without Docker)

### Requirements

- PHP 8.2 with `mysqli` extension
- MySQL 8.0 (or MariaDB 10.6+)
- Apache or `php -S`

### Steps

```bash
# 1. Create the database
mysql -u root -p < migrations/001_initial_schema.sql

# 2. Configure environment
cp .env.example .env
#   Set APP_ENV=development, SESSION_SECURE=false, and DB credentials

# 3. Start PHP built-in server (development only)
php -S localhost:8000

# 4. Open http://localhost:8000/landingPage.html
```

---

## Environment Variables

| Variable           | Required | Default         | Description |
|--------------------|----------|-----------------|-------------|
| `APP_ENV`          | Yes      | `production`    | `development` or `production` |
| `DB_HOST`          | Yes      | `127.0.0.1`     | MySQL host |
| `DB_PORT`          | Yes      | `3306`          | MySQL port |
| `DB_NAME`          | Yes      | `helping_paws2` | Database name |
| `DB_USERNAME`      | Yes      | `root`          | DB user |
| `DB_PASSWORD`      | Yes      | *(empty)*       | DB password |
| `SESSION_SECURE`   | No       | `true`          | Set `false` for local HTTP dev |
| `SESSION_SAMESITE` | No       | `Lax`           | Cookie SameSite policy |
| `SESSION_LIFETIME` | No       | `3600`          | Session TTL in seconds |
| `SESSION_NAME`     | No       | `HP_SESSION`    | Cookie name |

Never commit `.env` to version control. Use `.env.example` as the canonical template.

---

## Database Migrations

Migrations live in `migrations/`. Apply them in numbered order:

```bash
mysql -u root -p helping_paws2 < migrations/001_initial_schema.sql
```

Future migrations should be named `002_description.sql`, `003_description.sql`, and so on.

---

## Project Structure

```
.
├── src/
│   ├── config/
│   │   ├── app.php           # Bootstrap: session hardening, error config
│   │   └── database.php      # Centralised DB connection (env-driven)
│   ├── helpers/
│   │   ├── csrf.php          # CSRF token generation & verification
│   │   ├── flash.php         # One-time flash messages
│   │   ├── logger.php        # Structured JSON logger → logs/app.log
│   │   └── sanitize.php      # Input sanitization + output escaping (e())
│   └── middleware/
│       ├── require_auth.php  # Redirect unauthenticated donors
│       └── require_admin.php # Redirect unauthenticated admins
├── migrations/
│   └── 001_initial_schema.sql
├── docker/
│   ├── nginx.conf
│   ├── default.conf
│   └── supervisord.conf
├── docs/
│   ├── ARCHITECTURE.md
│   └── RUNBOOK.md
├── .github/
│   └── workflows/
│       └── ci.yml
├── Dockerfile
├── docker-compose.yml
├── .env.example
├── .gitignore
└── .dockerignore
```

### Key PHP pages

| File | Purpose |
|---|---|
| `landingPage.html` | Public home page |
| `login.php` | Donor login handler |
| `logout.php` | Donor session destroy |
| `register_donor.php` | Donor registration handler |
| `donor_profile.php` | Donor profile (auth-gated) |
| `DonorLanding.php` | Donor dashboard (auth-gated) |
| `DonorHistory.php` | Donation history (auth-gated) |
| `Donation.php` | Donation submission handler |
| `admin_login.php` | Admin login page + handler |
| `admin_logout.php` | Admin session destroy |
| `admin_panel.php` | Admin messages panel (auth-gated) |
| `delete.php` | Delete contact message (admin auth-gated) |
| `search_rescued_animals.php` | Animal search results |
| `contact_process.php` | Contact form handler |
| `submit_volunteer_form.php` | Volunteer sign-up handler |
| `get_total_volunteers.php` | JSON API: volunteer count |

---

## Security Model

| Control | Implementation |
|---|---|
| **No hardcoded credentials** | All DB creds read from environment variables via `.env` |
| **Password hashing** | `password_hash(PASSWORD_BCRYPT)` / `password_verify()` – no plaintext storage |
| **SQL injection prevention** | All queries use prepared statements with bound parameters |
| **CSRF protection** | Per-session token in every state-changing form, verified server-side |
| **Output encoding** | All user data escaped via `e()` (`htmlspecialchars`) before rendering |
| **Session hardening** | `strict_mode`, `HttpOnly`, `Secure`, `SameSite=Lax` enforced |
| **Session fixation** | `session_regenerate_id(true)` on every privilege change |
| **Auth guards** | Protected pages redirect immediately on missing/invalid session |
| **Nginx security headers** | `X-Content-Type-Options`, `X-Frame-Options`, CSP, `Referrer-Policy` |
| **Sensitive path blocking** | `src/`, `logs/`, `migrations/`, `.env` blocked at Nginx level |
| **Structured audit logs** | Admin actions logged to `logs/app.log` (JSON, correlation ID) |

---

## Deployment

### Production checklist

- [ ] `APP_ENV=production` in `.env`
- [ ] `SESSION_SECURE=true` (requires HTTPS / TLS termination)
- [ ] Dedicated non-root DB user with only required privileges
- [ ] Application behind HTTPS (Nginx + Let's Encrypt / load balancer)
- [ ] Strong, unique passwords for all DB accounts
- [ ] Change the default admin password immediately after first deploy
- [ ] Automated DB backups configured (see `docs/RUNBOOK.md`)
- [ ] Log rotation configured for `logs/`

### Rollback

1. Identify the previous image tag or git commit SHA.  
2. Re-deploy: `docker compose up -d --no-deps --force-recreate app`  
3. If DB changes need reverting, apply rollback SQL manually.

---

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| 503 on every page | DB unreachable | Check `DB_HOST`/`DB_PORT`, verify MySQL is running |
| "Invalid security token" | CSRF mismatch or expired session | Clear cookies and retry |
| Session not persisting | `SESSION_SECURE=true` over HTTP | Set `SESSION_SECURE=false` for local HTTP dev |
| Blank page / PHP error | `display_errors=0` in prod | Check `logs/php_errors.log` |
| "Connection failed" in logs | Wrong DB credentials | Update `.env` and restart container |

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for branching strategy, commit message conventions, PR process, and code review checklist.
