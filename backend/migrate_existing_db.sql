-- Migration script to add missing fields to existing tables
-- Run this after your existing shopdb.sql tables are created

USE shopdb;

-- Add missing fields to products table
ALTER TABLE products 
ADD COLUMN image VARCHAR(500) AFTER category_id,
ADD COLUMN sku VARCHAR(100) UNIQUE AFTER image,
ADD COLUMN brand VARCHAR(100) AFTER sku,
ADD COLUMN is_featured BOOLEAN DEFAULT FALSE AFTER brand,
ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER is_featured,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add missing fields to orders table
ALTER TABLE orders 
ADD COLUMN order_number VARCHAR(50) UNIQUE NOT NULL AFTER user_id,
ADD COLUMN subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER total_price,
ADD COLUMN tax_amount DECIMAL(10, 2) DEFAULT 0.00 AFTER subtotal,
ADD COLUMN shipping_amount DECIMAL(10, 2) DEFAULT 0.00 AFTER tax_amount,
ADD COLUMN discount_amount DECIMAL(10, 2) DEFAULT 0.00 AFTER shipping_amount,
ADD COLUMN payment_method ENUM('credit_card', 'debit_card', 'paypal', 'cash', 'bank_transfer') NOT NULL DEFAULT 'cash' AFTER discount_amount,
ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' AFTER payment_method,
ADD COLUMN shipping_name VARCHAR(255) NOT NULL DEFAULT '' AFTER payment_status,
ADD COLUMN shipping_address TEXT NOT NULL AFTER shipping_name,
ADD COLUMN shipping_city VARCHAR(100) NOT NULL DEFAULT '' AFTER shipping_address,
ADD COLUMN shipping_state VARCHAR(100) AFTER shipping_city,
ADD COLUMN shipping_zip VARCHAR(20) NOT NULL DEFAULT '' AFTER shipping_state,
ADD COLUMN shipping_country VARCHAR(100) DEFAULT 'United States' AFTER shipping_zip,
ADD COLUMN shipping_phone VARCHAR(20) NOT NULL DEFAULT '' AFTER shipping_country,
ADD COLUMN tracking_number VARCHAR(100) AFTER shipping_phone,
ADD COLUMN notes TEXT AFTER tracking_number,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Update orders table to use ENUM for status
ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending';

-- Add missing fields to order_items table
ALTER TABLE order_items 
ADD COLUMN product_name VARCHAR(255) NOT NULL DEFAULT '' AFTER product_id,
ADD COLUMN selected_size VARCHAR(20) AFTER quantity,
ADD COLUMN selected_color VARCHAR(50) AFTER selected_size,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER selected_color;

-- Add missing fields to users table
ALTER TABLE users 
ADD COLUMN phone VARCHAR(20) AFTER role,
ADD COLUMN address TEXT AFTER phone,
ADD COLUMN city VARCHAR(100) AFTER address,
ADD COLUMN zip_code VARCHAR(20) AFTER city,
ADD COLUMN country VARCHAR(100) DEFAULT 'United States' AFTER zip_code,
ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER country,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Update users table to use ENUM for role
ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin') DEFAULT 'user';

-- Add missing fields to categories table
ALTER TABLE categories 
ADD COLUMN slug VARCHAR(255) UNIQUE AFTER description,
ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER slug,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add missing fields to cart table
ALTER TABLE cart 
ADD COLUMN selected_size VARCHAR(20) AFTER quantity,
ADD COLUMN selected_color VARCHAR(50) AFTER selected_size,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER selected_color,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_price ON products(price);
CREATE INDEX idx_products_stock ON products(stock);
CREATE INDEX idx_products_featured ON products(is_featured);
CREATE INDEX idx_products_active ON products(is_active);

CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_payment_status ON orders(payment_status);
CREATE INDEX idx_orders_created ON orders(created_at);

CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);

CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_cart_product ON cart(product_id);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_active ON users(is_active);

CREATE INDEX idx_categories_slug ON categories(slug);
CREATE INDEX idx_categories_active ON categories(is_active);

-- Insert default admin user if not exists
INSERT IGNORE INTO users (name, email, password, role) VALUES 
('Admin', 'admin@footwearstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default categories if not exist
INSERT IGNORE INTO categories (name, description, slug) VALUES
('Sneakers', 'Casual and athletic sneakers for everyday wear', 'sneakers'),
('Boots', 'Stylish boots for various occasions', 'boots'),
('Sandals', 'Comfortable sandals for warm weather', 'sandals'),
('Formal Shoes', 'Elegant shoes for formal occasions', 'formal-shoes'),
('Sports Shoes', 'Specialized shoes for sports activities', 'sports-shoes'),
('Casual Shoes', 'Comfortable everyday casual shoes', 'casual-shoes'),
('Loafers', 'Slip-on casual shoes', 'loafers'),
('Oxfords', 'Classic lace-up formal shoes', 'oxfords'); 