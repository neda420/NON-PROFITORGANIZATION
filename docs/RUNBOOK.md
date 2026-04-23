# Runbook – Helping Paws Production Operations

## Table of Contents

1. [Health Checks](#health-checks)
2. [Deployment Procedure](#deployment-procedure)
3. [Database Backups](#database-backups)
4. [Incident Response](#incident-response)
5. [Access Control](#access-control)
6. [Log Management](#log-management)
7. [Certificate Renewal](#certificate-renewal)

---

## Health Checks

### Application health

```bash
# Check container status
docker compose ps

# Tail application logs
docker compose logs -f app

# Check PHP-FPM is responding
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/landingPage.html
# Expected: 200
```

### Database health

```bash
# From host
docker compose exec db mysqladmin ping -u root -p

# Check table row counts
docker compose exec db mysql -u root -p helping_paws2 \
  -e "SELECT 'donor_t', COUNT(*) FROM donor_t
      UNION SELECT 'Donation_T', COUNT(*) FROM Donation_T
      UNION SELECT 'VOLUNTEER_TABLE', COUNT(*) FROM VOLUNTEER_TABLE;"
```

---

## Deployment Procedure

### Standard deploy (new image)

```bash
# 1. Pull latest code
git pull origin main

# 2. Build new image
docker compose build app

# 3. Restart app container with zero-downtime (swap)
docker compose up -d --no-deps --force-recreate app

# 4. Verify
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/landingPage.html
docker compose logs --tail=20 app
```

### Apply a new migration

```bash
# 1. Backup first (see Database Backups)
# 2. Apply migration
docker compose exec db mysql -u root -p helping_paws2 \
  < migrations/002_your_migration.sql
# 3. Verify schema
docker compose exec db mysql -u root -p helping_paws2 -e "SHOW TABLES;"
```

### Rollback

```bash
# Roll back to previous image tag (e.g., helping-paws:v1.2)
docker compose stop app
docker tag helping-paws:v1.2 helping-paws:latest
docker compose up -d app
```

---

## Database Backups

### Manual backup

```bash
BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql.gz"
docker compose exec db mysqldump \
  -u root -p helping_paws2 \
  | gzip > "/backups/${BACKUP_FILE}"
echo "Backup saved: /backups/${BACKUP_FILE}"
```

### Automated backup (cron example)

Add to `/etc/cron.d/helping-paws-backup`:

```
0 2 * * * root \
  docker compose -f /opt/helping-paws/docker-compose.yml exec -T db \
    mysqldump -u root --password="${DB_ROOT_PASSWORD}" helping_paws2 \
  | gzip > "/backups/helping_paws2_$(date +\%Y\%m\%d).sql.gz" \
  && find /backups -name "*.sql.gz" -mtime +30 -delete
```

### Restore from backup

```bash
gunzip -c /backups/backup_20240101_020000.sql.gz \
  | docker compose exec -T db mysql -u root -p helping_paws2
```

---

## Incident Response

### P1 – Site completely down

1. `docker compose ps` – identify which container is unhealthy.
2. `docker compose logs --tail=50 app` – check PHP/Nginx errors.
3. `docker compose logs --tail=50 db` – check MySQL errors.
4. If DB is down, check volume/disk space: `df -h`.
5. Restart affected service: `docker compose restart <service>`.
6. If still failing, rollback to the last known-good image.
7. Notify stakeholders within 15 minutes of P1 declaration.

### P2 – Authentication broken

1. Check `logs/php_errors.log` for PHP errors.
2. Check `logs/app.log` for login failure patterns.
3. Verify session storage is writable: `ls -la /var/www/html/logs/`
4. Verify DB is reachable from app container.

### P3 – Suspected data breach

1. Immediately revoke all active sessions: truncate `sessions` table (if using DB sessions) or restart app to invalidate all in-memory sessions.
2. Rotate all DB passwords.
3. Review `logs/app.log` for unusual access patterns.
4. Preserve logs for forensic analysis (do NOT delete).
5. Notify affected users as required by applicable data protection law.
6. File an incident report.

---

## Access Control

### Production server access

- SSH key-based authentication only; password auth disabled.
- Only DevOps team members have server SSH access.
- Admin panel access requires a known username + bcrypt-hashed password stored in `ADMIN_TABLE`.

### Granting admin access

```bash
# Generate a bcrypt hash
php -r "echo password_hash('NewAdminPassword123!', PASSWORD_BCRYPT) . PHP_EOL;"

# Insert new admin
docker compose exec db mysql -u root -p helping_paws2 \
  -e "INSERT INTO ADMIN_TABLE (username, password) VALUES ('newadmin', '<hash>');"
```

### Revoking access

```bash
docker compose exec db mysql -u root -p helping_paws2 \
  -e "DELETE FROM ADMIN_TABLE WHERE username = 'departing_admin';"
```

---

## Log Management

| Log file | Contents | Rotation |
|---|---|---|
| `logs/app.log` | Structured JSON application events (info, audit, error) | Rotate weekly, keep 90 days |
| `logs/php_errors.log` | PHP runtime errors | Rotate weekly, keep 30 days |
| Nginx access log | HTTP requests | Managed by Docker log driver |

### Logrotate config (`/etc/logrotate.d/helping-paws`)

```
/opt/helping-paws/logs/*.log {
    weekly
    rotate 13
    compress
    missingok
    notifempty
    sharedscripts
    postrotate
        docker compose -f /opt/helping-paws/docker-compose.yml kill -s USR1 app
    endscript
}
```

---

## Certificate Renewal

If using Certbot + Nginx on the host:

```bash
# Test renewal
certbot renew --dry-run

# Force renewal
certbot renew --force-renewal

# Reload Nginx inside container
docker compose exec app nginx -s reload
```

Certbot renews automatically via a systemd timer; verify with:

```bash
systemctl status certbot.timer
```
