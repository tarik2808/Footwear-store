class OrderService {
    constructor() {
        this.baseUrl = 'http://localhost:8080/FootwearStore Tarik Coralic/backend/rest/api';
    }

    async createOrder(orderData) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/orders`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(orderData)
            });
            return await response.json();
        } catch (error) {
            console.error('Error creating order:', error);
            throw error;
        }
    }

    async getOrders() {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/orders`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Error fetching orders:', error);
            throw error;
        }
    }

    async getOrderById(orderId) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/orders/${orderId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Error fetching order:', error);
            throw error;
        }
    }

    async updateOrderStatus(orderId, status) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/orders/${orderId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ status })
            });
            return await response.json();
        } catch (error) {
            console.error('Error updating order status:', error);
            throw error;
        }
    }

    async cancelOrder(orderId) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/orders/${orderId}/cancel`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Error canceling order:', error);
            throw error;
        }
    }
}

const orderService = new OrderService();
export default orderService; 