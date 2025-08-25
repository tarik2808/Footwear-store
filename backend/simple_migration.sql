-- Simple migration - only adds missing columns
USE shopdb;

-- Add missing fields to products table
ALTER TABLE products 
ADD COLUMN image VARCHAR(500) AFTER category_id;

-- Add missing fields to orders table
ALTER TABLE orders 
ADD COLUMN shipping_name VARCHAR(255) DEFAULT '' AFTER status,
ADD COLUMN shipping_address TEXT DEFAULT '' AFTER shipping_name,
ADD COLUMN shipping_city VARCHAR(100) DEFAULT '' AFTER shipping_address,
ADD COLUMN shipping_zip VARCHAR(20) DEFAULT '' AFTER shipping_city,
ADD COLUMN shipping_phone VARCHAR(20) DEFAULT '' AFTER shipping_zip,
ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cash' AFTER shipping_phone;

SELECT 'Migration completed successfully!' as status;
