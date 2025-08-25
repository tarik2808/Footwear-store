-- Check which fields are missing from your database
USE shopdb;

-- Check products table
SELECT 'products' as table_name, 'image' as column_name, 
       CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'shopdb' AND TABLE_NAME = 'products' AND COLUMN_NAME = 'image'

UNION ALL

-- Check orders table fields
SELECT 'orders' as table_name, 'shipping_name' as column_name,
       CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'shopdb' AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_name'

UNION ALL

SELECT 'orders' as table_name, 'shipping_address' as column_name,
       CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'shopdb' AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_address'

UNION ALL

SELECT 'orders' as table_name, 'payment_method' as column_name,
       CASE WHEN COUNT(*) > 0 THEN 'EXISTS' ELSE 'MISSING' END as status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'shopdb' AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'payment_method';
