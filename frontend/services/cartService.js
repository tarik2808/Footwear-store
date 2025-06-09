class CartService {
    constructor() {
        this.baseUrl = 'http://localhost:8080/FootwearStore Tarik Coralic/backend/rest/api';
    }

    async getCart() {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/cart`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Error fetching cart:', error);
            throw error;
        }
    }

    async addToCart(productId, quantity) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/cart`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ productId, quantity })
            });
            return await response.json();
        } catch (error) {
            console.error('Error adding to cart:', error);
            throw error;
        }
    }

    async updateCartItem(cartItemId, quantity) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/cart/${cartItemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ quantity })
            });
            return await response.json();
        } catch (error) {
            console.error('Error updating cart item:', error);
            throw error;
        }
    }

    async removeFromCart(cartItemId) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/cart/${cartItemId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Error removing from cart:', error);
            throw error;
        }
    }

    async clearCart() {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/cart/clear`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Error clearing cart:', error);
            throw error;
        }
    }
}

const cartService = new CartService();
export default cartService; 