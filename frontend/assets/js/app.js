const API_BASE_URL = 'http://localhost/footwear-store/api';

class ApiClient {
    constructor() {
        this.token = localStorage.getItem('token');
    }

    async request(endpoint, options = {}) {
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            ...options,
            headers
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'An error occurred');
        }

        return response.json();
    }

    setToken(token) {
        this.token = token;
        localStorage.setItem('token', token);
    }

    clearToken() {
        this.token = null;
        localStorage.removeItem('token');
    }
}

class App {
    constructor() {
        this.api = new ApiClient();
        this.currentUser = null;
        this.setupEventListeners();
        this.checkAuth();
    }

    setupEventListeners() {
        // Navigation
        document.querySelectorAll('[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadPage(e.target.dataset.page);
            });
        });

        // Auth forms
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin(e.target);
        });

        document.getElementById('registerForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleRegister(e.target);
        });

        document.getElementById('logoutBtn').addEventListener('click', () => {
            this.handleLogout();
        });
    }

    async checkAuth() {
        try {
            const user = await this.api.request('/users/profile');
            this.currentUser = user;
            this.updateUI();
        } catch (error) {
            this.currentUser = null;
            this.updateUI();
        }
    }

    updateUI() {
        const authButtons = document.getElementById('authButtons');
        const userMenu = document.getElementById('userMenu');

        if (this.currentUser) {
            authButtons.classList.add('d-none');
            userMenu.classList.remove('d-none');
        } else {
            authButtons.classList.remove('d-none');
            userMenu.classList.add('d-none');
        }
    }

    async handleLogin(form) {
        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const response = await this.api.request('/users/login', {
                method: 'POST',
                body: JSON.stringify(data)
            });

            this.api.setToken(response.token);
            this.currentUser = response.user;
            this.updateUI();
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('loginModal')).hide();
            form.reset();
            
            // Load products page
            this.loadPage('products');
        } catch (error) {
            alert(error.message);
        }
    }

    async handleRegister(form) {
        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const response = await this.api.request('/users/register', {
                method: 'POST',
                body: JSON.stringify(data)
            });

            this.api.setToken(response.token);
            this.currentUser = response.user;
            this.updateUI();
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('registerModal')).hide();
            form.reset();
            
            // Load products page
            this.loadPage('products');
        } catch (error) {
            alert(error.message);
        }
    }

    handleLogout() {
        this.api.clearToken();
        this.currentUser = null;
        this.updateUI();
        this.loadPage('products');
    }

    async loadPage(page) {
        const content = document.getElementById('content');
        
        switch (page) {
            case 'products':
                await this.loadProducts();
                break;
            case 'categories':
                await this.loadCategories();
                break;
            case 'cart':
                await this.loadCart();
                break;
            case 'orders':
                await this.loadOrders();
                break;
            default:
                await this.loadProducts();
        }
    }

    async loadProducts() {
        try {
            const response = await this.api.request('/products');
            const content = document.getElementById('content');
            
            content.innerHTML = `
                <h2>Products</h2>
                <div class="row">
                    ${response.products.map(product => `
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">${product.name}</h5>
                                    <p class="card-text">${product.description}</p>
                                    <p class="card-text">$${product.price}</p>
                                    <button class="btn btn-primary" onclick="app.addToCart(${product.id})">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        } catch (error) {
            alert(error.message);
        }
    }

    async loadCategories() {
        try {
            const response = await this.api.request('/categories');
            const content = document.getElementById('content');
            
            content.innerHTML = `
                <h2>Categories</h2>
                <div class="row">
                    ${response.categories.map(category => `
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">${category.name}</h5>
                                    <p class="card-text">${category.description}</p>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        } catch (error) {
            alert(error.message);
        }
    }

    async loadCart() {
        try {
            const response = await this.api.request('/cart');
            const content = document.getElementById('content');
            
            content.innerHTML = `
                <h2>Cart</h2>
                <div class="row">
                    ${response.items.map(item => `
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">${item.product_id}</h5>
                                    <p class="card-text">Quantity: ${item.quantity}</p>
                                    <p class="card-text">Price: $${item.price}</p>
                                    <button class="btn btn-danger" onclick="app.removeFromCart(${item.id})">Remove</button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="mt-4">
                    <h3>Total: $${response.total}</h3>
                    <button class="btn btn-primary" onclick="app.checkout()">Checkout</button>
                </div>
            `;
        } catch (error) {
            alert(error.message);
        }
    }

    async loadOrders() {
        try {
            const response = await this.api.request('/orders');
            const content = document.getElementById('content');
            
            content.innerHTML = `
                <h2>Orders</h2>
                <div class="row">
                    ${response.orders.map(order => `
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Order #${order.id}</h5>
                                    <p class="card-text">Status: ${order.status}</p>
                                    <p class="card-text">Total: $${order.total_amount}</p>
                                    <p class="card-text">Date: ${new Date(order.created_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        } catch (error) {
            alert(error.message);
        }
    }

    async addToCart(productId) {
        try {
            await this.api.request('/cart', {
                method: 'POST',
                body: JSON.stringify({ product_id: productId, quantity: 1 })
            });
            alert('Product added to cart');
            this.loadCart();
        } catch (error) {
            alert(error.message);
        }
    }

    async removeFromCart(itemId) {
        try {
            await this.api.request(`/cart/items/${itemId}`, {
                method: 'DELETE'
            });
            this.loadCart();
        } catch (error) {
            alert(error.message);
        }
    }

    async checkout() {
        try {
            const shippingAddress = prompt('Enter shipping address:');
            const paymentMethod = prompt('Enter payment method:');
            
            if (!shippingAddress || !paymentMethod) {
                throw new Error('Shipping address and payment method are required');
            }

            await this.api.request('/orders', {
                method: 'POST',
                body: JSON.stringify({
                    shipping_address: shippingAddress,
                    payment_method: paymentMethod
                })
            });

            alert('Order placed successfully');
            this.loadOrders();
        } catch (error) {
            alert(error.message);
        }
    }
}

// Initialize app
const app = new App(); 