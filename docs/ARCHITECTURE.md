# Architecture – Helping Paws

## High-Level Overview

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

In Docker Compose, each component runs as a separate service on an internal bridge network (`internal`). Only Nginx exposes a port to the host.

---

## Application Layers

### Entry Points (web-accessible PHP files)

All top-level `.php` files are entry points.  
They follow this pattern:

```
require src/config/app.php          ← bootstrap (session, error config)
require src/helpers/csrf.php        ← CSRF helpers
require src/helpers/sanitize.php    ← input sanitization + e()
require src/helpers/flash.php       ← flash messages
require src/helpers/logger.php      ← appLog() / logInfo() / logAudit()
require src/middleware/require_*.php ← auth guard (where applicable)

$conn = getDbConnection();          ← singleton mysqli connection
```

### Shared Infrastructure (`src/`)

| File | Responsibility |
|---|---|
| `src/config/database.php` | Parses `.env`, returns singleton `mysqli` connection |
| `src/config/app.php` | PHP error config, session hardening, bootstraps database |
| `src/helpers/csrf.php` | Per-session CSRF token: `csrfToken()`, `csrfTokenField()`, `verifyCsrfToken()` |
| `src/helpers/flash.php` | Session-backed flash messages: `flashSet()`, `flashRender()` |
| `src/helpers/logger.php` | Writes newline-delimited JSON to `logs/app.log` |
| `src/helpers/sanitize.php` | `inputString()`, `inputEmail()`, `inputFloat()`, `e()`, etc. |
| `src/middleware/require_auth.php` | Redirects to `LoginDonor.html` if `$_SESSION['donorId']` absent |
| `src/middleware/require_admin.php` | Redirects to `admin_login.php` if `$_SESSION['admin_id']` absent |

---

## Database Schema (summary)

| Table | Key columns | Notes |
|---|---|---|
| `donor_t` | `donor_id` (PK), `email` (UQ), `password` | bcrypt hash |
| `ADMIN_TABLE` | `id` (PK), `username` (UQ), `password` | bcrypt hash |
| `contact_messages` | `id` (PK), `name`, `email`, `message`, `admin_id` (FK) | |
| `Donation_T` | `id` (PK), `donor_id` (FK), `amount` | DECIMAL(12,2) |
| `VOLUNTEER_TABLE` | `id` (PK), `name`, `email`, `phone` | |
| `RESCUED_ANIMALS` | `AnimalID` (PK), `AnimalType`, `AnimalGender`, `VetBills` | |

Full DDL: `migrations/001_initial_schema.sql`

---

## Authentication Flow

### Donor

1. POST `LoginDonor.html` → `login.php`
2. Prepared statement lookup by `donor_id`
3. `password_verify()` against stored bcrypt hash
4. `session_regenerate_id(true)` → set `$_SESSION['donorId']`
5. Redirect to `DonorLanding.php`
6. Logout: `logout.php` destroys session, redirects to `LoginDonor.html`

### Admin

1. GET/POST `admin_login.php`
2. Prepared statement lookup by `username`
3. `password_verify()` against stored bcrypt hash
4. `session_regenerate_id(true)` → set `$_SESSION['admin_id']`
5. Redirect to `admin_panel.php`
6. Logout: `admin_logout.php` destroys session, redirects to `admin_login.php`

---

## Security Architecture

- CSRF tokens are stored in `$_SESSION` and embedded as hidden form fields.
- All state-changing endpoints call `verifyCsrfToken()` at the top.
- All database queries use MySQLi prepared statements.
- All HTML output calls `e()` (alias for `htmlspecialchars`).
- Nginx blocks direct access to `src/`, `logs/`, `migrations/`, `.env`.
- PHP errors are logged to `logs/php_errors.log`; `display_errors=0` in production.
- Application events are logged as JSON to `logs/app.log` with timestamp, level, IP, and request URI.

---

## CI/CD Pipeline

```
Push / PR
   │
   ├── php-lint      (PHP syntax check + phpcs PSR-12)
   ├── security-scan (grep for SQL interpolation, var_dump, hardcoded creds)
   └── docker-build  (docker build + smoke test)
```

Defined in `.github/workflows/ci.yml`.

---

## Future Considerations

- **Rate limiting:** Add Nginx `limit_req_zone` for login and contact endpoints.
- **Two-factor auth:** TOTP for admin accounts.
- **Email integration:** SMTP for contact confirmation and donation receipts.
- **Password reset:** Token-based self-service reset flow.
- **Full test suite:** PHPUnit for unit + integration tests.
- **Composer autoloading:** Migrate `src/` to PSR-4 autoloaded namespaces.
