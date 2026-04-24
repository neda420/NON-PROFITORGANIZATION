# Data Governance & Compliance

## Scope

This document defines how Helping Paws handles personally identifiable information (PII), consent, retention, and deletion across donor and volunteer workflows.

## Data Classification

- Public: static website content, non-identifiable aggregate metrics.
- Internal: operational logs without direct PII where possible.
- Confidential (PII): donor names, emails, phone numbers, addresses.
- Sensitive operational: admin actions, audit records, security incidents.

## Minimum Data Collection

- Collect only fields required for donations, communication, and receipts.
- Avoid collecting payment card data directly in this service.
- Use aggregated metrics for dashboards whenever user-level detail is not required.

## Consent & Communication Preferences

- Consent flags are stored in `donor_preferences`.
- Required processing consent defaults to enabled for account operation.
- Marketing consent is opt-in only and independently tracked per channel.
- Preference updates should be timestamped and auditable.

## Retention Policy

- `donor_t`: retained while account is active; anonymize after approved deletion request.
- `Donation_T`: retained for statutory/financial reporting obligations.
- `VOLUNTEER_TABLE`: retain for 24 months of inactivity unless earlier deletion requested.
- `contact_messages`: retain for 12 months.
- `admin_audit_log`: retain for at least 24 months.
- application logs: rotate weekly, retain 90 days unless incident hold applies.

## Deletion Request Flow

1. User submits deletion request.
2. System records request in `data_deletion_requests` with `pending` status.
3. Admin reviews request, validates identity, and approves/rejects.
4. On approval, account is anonymized/deleted based on legal retention constraints.
5. Request row is marked resolved with resolver and resolution timestamp.

## Encryption & Transport

- Enforce HTTPS/TLS for all production traffic.
- Never store plaintext passwords (bcrypt/argon2 hashes only).
- Store secrets in environment variables or platform secret manager.
- Restrict database user privileges to least privilege.

## Incident & Breach Handling

- Preserve relevant logs and audit records.
- Rotate credentials immediately.
- Document affected data categories and impacted users.
- Notify stakeholders/users according to applicable regulation and timelines.
