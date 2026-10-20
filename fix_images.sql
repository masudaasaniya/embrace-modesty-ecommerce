-- Fix Product Images SQL Script
-- Run this in phpMyAdmin or MySQL to fix image paths

-- Fix scarf -> skaff naming issue
UPDATE products SET image = 'skaff1.jpg' WHERE image = 'scarf1.jpg';
UPDATE products SET image = 'skaff2.jpg' WHERE image = 'scarf2.jpg';
UPDATE products SET image = 'skaff3.jpg' WHERE image = 'scarf3.jpg';
UPDATE products SET image = 'skaff4.jpg' WHERE image = 'scarf4.jpg';
UPDATE products SET image = 'skaff5.jpg' WHERE image = 'scarf5.jpg';
UPDATE products SET image = 'skaff6.jpg' WHERE image = 'scarf6.jpg';
UPDATE products SET image = 'skaff7.jpg' WHERE image = 'scarf7.jpg';
UPDATE products SET image = 'skaff8.jpg' WHERE image = 'scarf8.jpg';
UPDATE products SET image = 'skaff9.jpg' WHERE image = 'scarf9.jpg';
UPDATE products SET image = 'skaff10.jpg' WHERE image = 'scarf10.jpg';
UPDATE products SET image = 'skaff11.jpg' WHERE image = 'scarf11.jpg';
UPDATE products SET image = 'skaff12.jpg' WHERE image = 'scarf12.jpg';
UPDATE products SET image = 'skaff13.jpg' WHERE image = 'scarf13.jpg';
UPDATE products SET image = 'skaff14.jpg' WHERE image = 'scarf14.jpg';
UPDATE products SET image = 'skaff15.jpg' WHERE image = 'scarf15.jpg';

-- View current image status (SELECT only, won't modify data)
SELECT id, name, category, image FROM products;

