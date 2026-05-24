-- Add resepNo FK column to menu table if not exists
-- Run this in phpMyAdmin SQL tab or MySQL CLI

ALTER TABLE menu ADD COLUMN resepNo INT DEFAULT NULL AFTER MenuNo;

-- If you want to add foreign key constraint:
-- ALTER TABLE menu ADD CONSTRAINT fk_menu_resep FOREIGN KEY (resepNo) REFERENCES resep(ResepNo);
