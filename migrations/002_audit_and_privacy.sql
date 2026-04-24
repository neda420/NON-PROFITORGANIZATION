USE helping_paws2;

-- Track privileged admin actions for security investigations.
CREATE TABLE IF NOT EXISTS admin_audit_log (
    id            BIGINT       NOT NULL AUTO_INCREMENT,
    admin_id      INT          DEFAULT NULL,
    action        VARCHAR(120) NOT NULL,
    entity_type   VARCHAR(80)  DEFAULT NULL,
    entity_id     VARCHAR(80)  DEFAULT NULL,
    before_state  JSON         DEFAULT NULL,
    after_state   JSON         DEFAULT NULL,
    request_id    VARCHAR(80)  DEFAULT NULL,
    ip_address    VARCHAR(45)  DEFAULT NULL,
    user_agent    VARCHAR(255) DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_audit_created_at (created_at),
    KEY idx_admin_audit_admin_id (admin_id),
    CONSTRAINT fk_admin_audit_admin
        FOREIGN KEY (admin_id) REFERENCES ADMIN_TABLE(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Store donor communication and consent preferences.
CREATE TABLE IF NOT EXISTS donor_preferences (
    donor_id                 VARCHAR(20) NOT NULL,
    consent_email_marketing  TINYINT(1)  NOT NULL DEFAULT 0,
    consent_sms_marketing    TINYINT(1)  NOT NULL DEFAULT 0,
    consent_data_processing  TINYINT(1)  NOT NULL DEFAULT 1,
    consent_updated_at       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP
                                           ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (donor_id),
    CONSTRAINT fk_donor_pref_donor
        FOREIGN KEY (donor_id) REFERENCES donor_t(donor_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Track user-initiated deletion/retention requests.
CREATE TABLE IF NOT EXISTS data_deletion_requests (
    id              BIGINT       NOT NULL AUTO_INCREMENT,
    donor_id        VARCHAR(20)  NOT NULL,
    request_reason  VARCHAR(255) DEFAULT NULL,
    status          VARCHAR(20)  NOT NULL DEFAULT 'pending',
    requested_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at     TIMESTAMP    NULL DEFAULT NULL,
    resolved_by     INT          DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_deletion_request_status (status),
    KEY idx_deletion_request_donor (donor_id),
    CONSTRAINT fk_deletion_request_donor
        FOREIGN KEY (donor_id) REFERENCES donor_t(donor_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_deletion_request_admin
        FOREIGN KEY (resolved_by) REFERENCES ADMIN_TABLE(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
