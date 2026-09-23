-- Adds a "contacted" workflow status to Contact Messages.
-- Run once against an already-imported edgetech_crm database:
--   mysql -u root edgetech_crm < database/contact_status_migration.sql

-- Map any legacy 'read' value onto the new set first.
UPDATE contact_messages SET status = 'contacted' WHERE status = 'read';

ALTER TABLE contact_messages
  MODIFY status ENUM('new','contacted','replied') NOT NULL DEFAULT 'new';
