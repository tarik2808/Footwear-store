class ProductService {
    constructor() {
        this.baseUrl = 'http://localhost:8080/FootwearStore Tarik Coralic/backend/rest/api';
    }

    async getAllProducts() {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/products`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Error fetching products:', error);
            throw error;
        }
    }

    async getProductById(id) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/products/${id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Error fetching product:', error);
            throw error;
        }
    }

    async createProduct(productData) {
        try {
            const token = localStorage.getItem('token');
            let formData = new FormData();
            for (const key in productData) {
                if (productData[key] !== undefined && productData[key] !== null) {
                    formData.append(key, productData[key]);
                }
            }
            const response = await fetch(`${this.baseUrl}/products`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: formData
            });
            return await response.json();
        } catch (error) {
            console.error('Error creating product:', error);
            throw error;
        }
    }

    async updateProduct(id, productData) {
        try {
            const token = localStorage.getItem('token');
            let formData = new FormData();
            for (const key in productData) {
                if (productData[key] !== undefined && productData[key] !== null) {
                    formData.append(key, productData[key]);
                }
            }
            const response = await fetch(`${this.baseUrl}/products/${id}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: formData
            });
            return await response.json();
        } catch (error) {
            console.error('Error updating product:', error);
            throw error;
        }
    }

    async deleteProduct(id) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Error deleting product:', error);
            throw error;
        }
    }

    async getProductsByCategory(categoryId) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.baseUrl}/categories/${categoryId}/products`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Error fetching products by category:', error);
            throw error;
        }
    }
}

const productService = new ProductService();
export default productService; 