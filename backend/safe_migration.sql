-- Safe migration script - checks if columns exist before adding them
USE shopdb;

-- Check and add missing fields to products table
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'shopdb' AND TABLE_NAME = 'products' AND COLUMN_NAME = 'image') = 0,
    'ALTER TABLE products ADD COLUMN image VARCHAR(500) AFTER category_id;',
    'SELECT "image column already exists" as message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add missing fields to orders table
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'shopdb' AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_name') = 0,
    'ALTER TABLE orders ADD COLUMN shipping_name VARCHAR(255) NOT NULL DEFAULT "" AFTER status;',
    'SELECT "shipping_name column already exists" as message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'shopdb' AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_address') = 0,
    'ALTER TABLE orders ADD COLUMN shipping_address TEXT NOT NULL AFTER shipping_name;',
    'SELECT "shipping_address column already exists" as message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'shopdb' AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'payment_method') = 0,
    'ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT "cash" AFTER shipping_address;',
    'SELECT "payment_method column already exists" as message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add other missing fields quickly
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS shipping_city VARCHAR(100) DEFAULT '' AFTER shipping_address,
ADD COLUMN IF NOT EXISTS shipping_zip VARCHAR(20) DEFAULT '' AFTER shipping_city,
ADD COLUMN IF NOT EXISTS shipping_phone VARCHAR(20) DEFAULT '' AFTER shipping_zip;

-- Add indexes (will fail silently if they already exist)
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);

SELECT 'Migration completed successfully!' as status;
