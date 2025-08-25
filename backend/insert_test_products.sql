-- Insert test products for the footwear store
USE shopdb;

-- Insert sample footwear products
INSERT INTO products (name, description, price, stock, category_id, image, brand, sku) VALUES
('Nike Air Max 270', 'Classic Nike Air Max sneakers with air cushioning technology', 129.99, 50, 1, 'uploads/nike-airmax-270.webp', 'Nike', 'NIKE-AM270-001'),
('Adidas Ultraboost 22', 'Premium running shoes with Boost technology and Primeknit upper', 159.99, 30, 1, 'uploads/adidas-ultraboost-22.webp', 'Adidas', 'ADIDAS-UB22-001'),
('Dr. Martens 1460', 'Classic black leather boots with air-cushioned sole', 149.99, 25, 2, 'uploads/dr-martens-1460.webp', 'Dr. Martens', 'DM-1460-001'),
('Gant Chelsea Boots', 'Stylish leather chelsea boots with elastic side panels', 179.99, 20, 2, 'uploads/gant-chelsea.webp', 'Gant', 'GANT-CB-001'),
('Birkenstock Arizona', 'Comfortable leather sandals with contoured footbed', 89.99, 40, 3, 'uploads/birkenstock-arizona.webp', 'Birkenstock', 'BIRK-AZ-001'),
('Teva Original Universal', 'Durable outdoor sandals with adjustable straps', 69.99, 35, 3, 'uploads/teva-universal.webp', 'Teva', 'TEVA-OU-001'),
('Allen Edmonds Park Avenue', 'Classic oxford dress shoes in black leather', 399.99, 15, 4, 'uploads/allen-edmonds-park-ave.webp', 'Allen Edmonds', 'AE-PA-001'),
('Cole Haan Grand Wingtip', 'Modern wingtip dress shoes with lightweight construction', 299.99, 20, 4, 'uploads/cole-haan-wingtip.webp', 'Cole Haan', 'CH-GW-001'),
('Nike Zoom Pegasus 39', 'Professional running shoes with responsive cushioning', 119.99, 45, 5, 'uploads/nike-zoom-pegasus.webp', 'Nike', 'NIKE-ZP39-001'),
('Adidas Predator Edge', 'Football cleats for professional play with control frame', 199.99, 30, 5, 'uploads/adidas-predator-edge.webp', 'Adidas', 'ADIDAS-PE-001'),
('Converse Chuck Taylor', 'Timeless canvas sneakers in classic white', 59.99, 100, 1, 'uploads/converse-chuck-taylor.webp', 'Converse', 'CONV-CT-001'),
('Vans Old Skool', 'Iconic skate shoes with side stripe design', 64.99, 75, 1, 'uploads/vans-old-skool.webp', 'Vans', 'VANS-OS-001'),
('Timberland 6-Inch Boots', 'Premium waterproof boots with premium leather', 189.99, 25, 2, 'uploads/timberland-6inch.webp', 'Timberland', 'TIMB-6IN-001'),
('Clarks Desert Boots', 'Classic suede desert boots with crepe sole', 129.99, 30, 2, 'uploads/clarks-desert-boots.webp', 'Clarks', 'CLARKS-DB-001'),
('Havaianas Flip Flops', 'Comfortable rubber flip flops in various colors', 24.99, 200, 3, 'uploads/havaianas-flip-flops.webp', 'Havaianas', 'HAV-FF-001');

-- Update categories to have proper slugs if they don't exist
UPDATE categories SET slug = 'sneakers' WHERE id = 1 AND name = 'Sneakers';
UPDATE categories SET slug = 'boots' WHERE id = 2 AND name = 'Boots';
UPDATE categories SET slug = 'sandals' WHERE id = 3 AND name = 'Sandals';
UPDATE categories SET slug = 'formal-shoes' WHERE id = 4 AND name = 'Formal Shoes';
UPDATE categories SET slug = 'sports-shoes' WHERE id = 5 AND name = 'Sports Shoes';

SELECT 'Test products inserted successfully!' as message;
SELECT COUNT(*) as total_products FROM products;
