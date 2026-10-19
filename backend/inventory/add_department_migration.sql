ALTER TABLE inventory
    ADD COLUMN department ENUM('Kitchen', 'Bar', 'Promo') NOT NULL DEFAULT 'Bar' AFTER inventory_id;
