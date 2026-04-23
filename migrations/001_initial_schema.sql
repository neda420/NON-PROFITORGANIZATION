-- =============================================================
-- Migration 001 – Initial production schema
-- Helping Paws Non-Profit Organization
-- =============================================================
-- Run once against a clean database, or use your migration
-- runner to track which files have been applied.
-- =============================================================

CREATE DATABASE IF NOT EXISTS helping_paws2
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE helping_paws2;

-- ── donor_t ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS donor_t (
    donor_id              VARCHAR(20)  NOT NULL,
    name                  VARCHAR(100) NOT NULL,
    email                 VARCHAR(150) NOT NULL,
    -- Stores a bcrypt/argon2 hash produced by PHP password_hash().
    -- Column is wide enough for future algorithm upgrades.
    password              VARCHAR(255) NOT NULL,
    address               VARCHAR(200) DEFAULT NULL,
    phone                 VARCHAR(30)  DEFAULT NULL,
    occupation            VARCHAR(50)  DEFAULT NULL,
    contact_method        VARCHAR(20)  DEFAULT NULL,
    interest_volunteering VARCHAR(10)  DEFAULT NULL,
    created_at            TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                          ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (donor_id),
    UNIQUE KEY uq_donor_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── ADMIN_TABLE ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ADMIN_TABLE (
    id         INT          NOT NULL AUTO_INCREMENT,
    username   VARCHAR(50)  NOT NULL,
    -- Stores a bcrypt/argon2 hash produced by PHP password_hash().
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── contact_messages ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT          NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    message    TEXT         NOT NULL,
    admin_id   INT          DEFAULT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contact_created_at (created_at),
    CONSTRAINT fk_contact_admin
        FOREIGN KEY (admin_id) REFERENCES ADMIN_TABLE (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Donation_T ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Donation_T (
    id               INT           NOT NULL AUTO_INCREMENT,
    donor_id         VARCHAR(20)   NOT NULL,
    donor_name       VARCHAR(100)  NOT NULL,
    donation_purpose VARCHAR(50)   NOT NULL,
    amount           DECIMAL(12,2) NOT NULL,
    donation_date    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_donation_donor    (donor_id),
    KEY idx_donation_date     (donation_date),
    CONSTRAINT fk_donation_donor
        FOREIGN KEY (donor_id) REFERENCES donor_t (donor_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── VOLUNTEER_TABLE ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS VOLUNTEER_TABLE (
    id         INT          NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    phone      VARCHAR(30)  DEFAULT NULL,
    message    TEXT         DEFAULT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_volunteer_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── RESCUED_ANIMALS ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS RESCUED_ANIMALS (
    AnimalID      INT           NOT NULL AUTO_INCREMENT,
    MedicalRecord VARCHAR(100)  DEFAULT NULL,
    AnimalType    VARCHAR(50)   NOT NULL,
    AnimalGender  VARCHAR(10)   NOT NULL,
    VetBills      DECIMAL(10,2) DEFAULT 0.00,
    rescued_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (AnimalID),
    KEY idx_animal_type (AnimalType)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- Seed: default admin account
-- Password: Admin@HelpingPaws2024
-- IMPORTANT: Change this password immediately after first login.
-- =============================================================
INSERT IGNORE INTO ADMIN_TABLE (username, password)
VALUES (
    'admin',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    -- The above hash is bcrypt of "Admin@HelpingPaws2024" (cost=12).
    -- Replace with: php -r "echo password_hash('YourPassword', PASSWORD_BCRYPT);"
);
