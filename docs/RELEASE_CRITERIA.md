# Release Criteria

Production release is allowed only when all checks below are green.

## Must Pass

- CI workflow passes on target commit:
  - PHP syntax + PHPCS
  - smoke tests
  - security scan
  - docker build/start smoke
- Database migrations are reviewed and applied in staging.
- `health.php` returns HTTP 200 in staging.
- Manual authentication flow test completed:
  - donor registration/login/logout
  - admin login/logout
  - donation submit
  - volunteer form submit
- Rollback procedure validated for current release.

## Security Gate

- No hardcoded credentials in source control.
- CSRF checks in all state-changing endpoints.
- Prepared statements used for database queries.
- No debug dumps (`var_dump`, `print_r`, `die` with internals).

## Operational Readiness

- Backups run successfully and latest restore test is documented.
- Alerting channels verified for uptime and error-rate alarms.
- On-call owner assigned for release window.

## Change Approval

- At least one reviewer approval.
- PR includes test evidence and migration/rollback notes.
- Release notes prepared with user-impact summary.
