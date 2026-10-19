ALTER TABLE inventory
    MODIFY COLUMN department ENUM('Kitchen', 'Bar', 'Promo') NOT NULL DEFAULT 'Bar';
