-- SQL script to clear all data from the `resep` table in database `skripsi_daniel`.
-- Run this from your MySQL client (phpMyAdmin / mysql CLI) while connected to the proper server.

USE skripsi_daniel;
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE resep;
SET FOREIGN_KEY_CHECKS = 1;

-- Optional: if you also want to drop the old header table (ONLY if you're sure):
-- DROP TABLE IF EXISTS resep_header;
