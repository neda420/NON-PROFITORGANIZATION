-- =============================================================
-- Migration 003 – Normalize table and column naming
-- Helping Paws Non-Profit Organization
-- =============================================================
-- This migration corrects all mixed-casing naming inconsistencies
-- introduced in 001 and 002:
--
--   donor_t          → donors
--   ADMIN_TABLE      → admins
--   Donation_T       → donations
--   VOLUNTEER_TABLE  → volunteers
--   RESCUED_ANIMALS  → rescued_animals  (+ column snake_case)
--
-- Tables that already follow snake_case are left untouched:
--   contact_messages, admin_audit_log, donor_preferences,
--   data_deletion_requests
--
-- Safe to run against a schema created by 001 + 002.
-- All foreign-key constraints are explicitly dropped and
-- re-created so the migration works on any MySQL version.
-- =============================================================

USE helping_paws2;

SET FOREIGN_KEY_CHECKS = 0;

-- ── Step 1: Drop all FK constraints that reference tables
--            being renamed, and the FKs on those tables. ────
ALTER TABLE contact_messages
    DROP FOREIGN KEY fk_contact_admin;

ALTER TABLE Donation_T
    DROP FOREIGN KEY fk_donation_donor;

ALTER TABLE admin_audit_log
    DROP FOREIGN KEY fk_admin_audit_admin;

ALTER TABLE donor_preferences
    DROP FOREIGN KEY fk_donor_pref_donor;

ALTER TABLE data_deletion_requests
    DROP FOREIGN KEY fk_deletion_request_donor,
    DROP FOREIGN KEY fk_deletion_request_admin;

-- ── Step 2: Rename tables ─────────────────────────────────
RENAME TABLE donor_t         TO donors;
RENAME TABLE ADMIN_TABLE     TO admins;
RENAME TABLE Donation_T      TO donations;
RENAME TABLE VOLUNTEER_TABLE TO volunteers;
RENAME TABLE RESCUED_ANIMALS TO rescued_animals;

-- ── Step 3: Normalise RESCUED_ANIMALS column names ────────
ALTER TABLE rescued_animals
    CHANGE COLUMN AnimalID       animal_id      INT           NOT NULL AUTO_INCREMENT,
    CHANGE COLUMN MedicalRecord  medical_record VARCHAR(100)  DEFAULT NULL,
    CHANGE COLUMN AnimalType     animal_type    VARCHAR(50)   NOT NULL,
    CHANGE COLUMN AnimalGender   animal_gender  VARCHAR(10)   NOT NULL,
    CHANGE COLUMN VetBills       vet_bills      DECIMAL(10,2) DEFAULT 0.00;

-- Rename the index that referenced the old column name.
ALTER TABLE rescued_animals
    DROP INDEX  idx_animal_type,
    ADD  INDEX  idx_animal_type (animal_type);

-- ── Step 4: Re-create FK constraints with new table names ─
ALTER TABLE contact_messages
    ADD CONSTRAINT fk_contact_admin
        FOREIGN KEY (admin_id) REFERENCES admins (id)
        ON DELETE SET NULL;

ALTER TABLE donations
    ADD CONSTRAINT fk_donation_donor
        FOREIGN KEY (donor_id) REFERENCES donors (donor_id)
        ON DELETE CASCADE;

ALTER TABLE admin_audit_log
    ADD CONSTRAINT fk_admin_audit_admin
        FOREIGN KEY (admin_id) REFERENCES admins (id)
        ON DELETE SET NULL;

ALTER TABLE donor_preferences
    ADD CONSTRAINT fk_donor_pref_donor
        FOREIGN KEY (donor_id) REFERENCES donors (donor_id)
        ON DELETE CASCADE;

ALTER TABLE data_deletion_requests
    ADD CONSTRAINT fk_deletion_request_donor
        FOREIGN KEY (donor_id) REFERENCES donors (donor_id)
        ON DELETE CASCADE,
    ADD CONSTRAINT fk_deletion_request_admin
        FOREIGN KEY (resolved_by) REFERENCES admins (id)
        ON DELETE SET NULL;

-- ── Step 5: Update the default admin seed row ─────────────
-- No data change needed; the row survives the rename intact.

SET FOREIGN_KEY_CHECKS = 1;
