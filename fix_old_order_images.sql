-- Safe UPDATE query to populate EMPTY/NULL product_image in order_items from products table
-- Run this in phpMyAdmin or MySQL CLI after confirming table structure
-- Test first: SELECT COUNT(*) FROM order_items WHERE product_image IS NULL OR product_image = '';

UPDATE order_items oi
JOIN products p ON oi.product_id = p.id
SET oi.product_image = p.image
WHERE (oi.product_image IS NULL OR oi.product_image = '') 
  AND p.image IS NOT NULL 
  AND p.image != '';

-- Verify:
-- SELECT order_id, product_name, product_image FROM order_items WHERE product_image IS NOT NULL AND product_image != '' ORDER BY RAND() LIMIT 10;

-- Check affected rows:
-- SELECT COUNT(*) as fixed FROM order_items WHERE product_image IS NOT NULL AND product_image != '';
