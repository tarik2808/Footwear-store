-- Insert products that match the existing images in uploads folder
USE shopdb;

-- First, let's make sure we have the right categories
INSERT IGNORE INTO categories (id, name, description, slug) VALUES
(1, 'Sneakers', 'Casual and athletic sneakers for everyday wear', 'sneakers'),
(2, 'Boots', 'Stylish boots for various occasions', 'boots'),
(3, 'Sandals', 'Comfortable sandals for warm weather', 'sandals'),
(4, 'Formal Shoes', 'Elegant shoes for formal occasions', 'formal-shoes'),
(5, 'Sports Shoes', 'Specialized shoes for sports activities', 'sports-shoes');

-- Insert products that match your existing images exactly
INSERT INTO products (name, description, price, stock, category_id, image, brand, sku) VALUES
-- Nike Products (Category 1 - Sneakers)
('Nike Air Max 97', 'Classic Nike Air Max 97 with iconic wave design and Air-Sole unit', 149.99, 45, 1, 'uploads/air97.webp', 'Nike', 'NIKE-AM97-001'),
('Nike Zoom Fly', 'Professional running shoes with responsive Zoom Air technology', 129.99, 35, 1, 'uploads/nike zoom.webp', 'Nike', 'NIKE-ZF-001'),

-- Adidas Products (Category 1 - Sneakers)
('Adidas Ultraboost 22', 'Premium running shoes with Boost technology and Primeknit upper', 159.99, 30, 1, 'uploads/Ultraboost.webp', 'Adidas', 'ADIDAS-UB22-001'),
('Adidas Predator Pro 25', 'Professional football cleats with control frame technology', 199.99, 25, 5, 'uploads/adidas-predator-pro-25-turf-off-white-black.webp', 'Adidas', 'ADIDAS-PP25-001'),

-- Boots (Category 2)
('Dr. Martens 1460', 'Classic black leather boots with air-cushioned sole and yellow stitching', 149.99, 25, 2, 'uploads/Dr Martens.webp', 'Dr. Martens', 'DM-1460-001'),
('Gant Chelsea Boots', 'Stylish leather chelsea boots with elastic side panels', 179.99, 20, 2, 'uploads/Gant.webp', 'Gant', 'GANT-CB-001'),

-- Sandals (Category 3)
('Birkenstock Arizona', 'Comfortable leather sandals with contoured footbed', 89.99, 40, 3, 'uploads/birkenstock.webp', 'Birkenstock', 'BIRK-AZ-001'),
('Teva Men\'s Sandals', 'Durable outdoor sandals with adjustable straps for men', 69.99, 35, 3, 'uploads/teva men.webp', 'Teva', 'TEVA-MEN-001'),

-- Formal Shoes (Category 4)
('Allen Edmonds Park Avenue', 'Classic oxford dress shoes in black leather', 399.99, 15, 4, 'uploads/Allen-Edmonds-Park-Avenue-Oxfords-In-Black-1030x579.webp', 'Allen Edmonds', 'AE-PA-001'),
('Cole Haan Grand Wingtip', 'Modern wingtip dress shoes with lightweight construction', 299.99, 20, 4, 'uploads/Cole Haan.webp', 'Cole Haan', 'CH-GW-001'),

-- Luxury/Designer (Category 1 - Sneakers)
('Red Bottom Sneakers', 'Premium designer sneakers with signature red sole', 899.99, 10, 1, 'uploads/red bottoms.webp', 'Designer', 'DESIGNER-RB-001');

-- Update any existing products to avoid duplicates
UPDATE products SET 
    description = 'Classic Nike Air Max 97 with iconic wave design and Air-Sole unit',
    price = 149.99,
    stock = 45,
    image = 'uploads/air97.webp',
    brand = 'Nike',
    sku = 'NIKE-AM97-001'
WHERE name LIKE '%Nike Air Max%' OR name LIKE '%Air Max%';

-- Show results
SELECT 'Products inserted successfully!' as message;
SELECT COUNT(*) as total_products FROM products;
SELECT name, brand, price, category_id, image FROM products ORDER BY category_id, name;
