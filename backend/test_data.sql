-- Insert test categories
INSERT INTO categories (name, description) VALUES
('Sneakers', 'Casual and athletic sneakers for everyday wear'),
('Boots', 'Stylish boots for various occasions'),
('Sandals', 'Comfortable sandals for warm weather'),
('Formal Shoes', 'Elegant shoes for formal occasions'),
('Sports Shoes', 'Specialized shoes for sports activities');

-- Insert test users
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert test products
INSERT INTO products (name, description, price, stock, category_id) VALUES
('Nike Air Max', 'Classic Nike Air Max sneakers with air cushioning', 129.99, 50, 1),
('Adidas Ultraboost', 'Premium running shoes with Boost technology', 159.99, 30, 1),
('Dr. Martens Casual Black', 'Classic black leather boots', 149.99, 25, 2),
('Gant Chelsea Leather Boots', 'Stylish leather chelsea boots', 179.99, 20, 2),
('Birkenstock Arizona', 'Comfortable leather sandals', 89.99, 40, 3),
('Teva Original Universal', 'Durable outdoor sandals', 69.99, 35, 3),
('Allen Edmonds Park Avenue', 'Classic oxford dress shoes', 399.99, 15, 4),
('Cole Haan Grand Wingtip', 'Modern wingtip dress shoes', 299.99, 20, 4),
('Nike Zoom Pegasus', 'Professional running shoes', 119.99, 45, 5),
('Adidas Predator', 'Football cleats for professional play', 199.99, 30, 5);

-- Insert test orders
INSERT INTO orders (user_id, total_price, status) VALUES
(2, 299.98, 'delivered'),
(3, 179.99, 'processing');

-- Insert test order items
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 129.99),
(1, 5, 1, 89.99),
(2, 4, 1, 179.99); 