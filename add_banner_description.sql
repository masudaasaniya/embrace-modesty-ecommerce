-- Add description column to banners table
-- Safe: TEXT NULL, won't affect existing rows

ALTER TABLE banners ADD COLUMN description TEXT NULL AFTER subtitle;

-- Verify:
-- DESCRIBE banners;
