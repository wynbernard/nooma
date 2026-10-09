ALTER TABLE inventory
    ADD COLUMN department ENUM('Kitchen', 'Bar') NOT NULL DEFAULT 'Bar' AFTER inventory_id;
