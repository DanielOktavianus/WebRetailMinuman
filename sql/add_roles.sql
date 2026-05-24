-- SKRIPSIS8 - Add Role-Based Access Control
-- Run these SQL queries to add role column to data_user table

-- 1. Add role column to data_user table
ALTER TABLE `data_user` ADD COLUMN `role` VARCHAR(20) DEFAULT 'karyawan' AFTER `password`;

-- 2. Set admin role for first user (optional - change usernameNo to your admin user)
UPDATE `data_user` SET `role` = 'admin' WHERE `usernameNo` = 1;

-- 3. Set other users as karyawan (employees)
UPDATE `data_user` SET `role` = 'karyawan' WHERE `role` IS NULL;

-- Verify the changes
SELECT usernameNo, username, role FROM data_user;
