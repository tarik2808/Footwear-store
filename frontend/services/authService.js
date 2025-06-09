class AuthService {
    constructor() {
        this.baseUrl = 'http://localhost:8080/FootwearStore Tarik Coralic/backend/rest/api';
    }

    async login(email, password) {
        try {
            const response = await fetch(`${this.baseUrl}/users/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            if (!response.ok) {
                throw new Error('Login failed');
            }

            const data = await response.json();
            localStorage.setItem('token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            return data;
        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    }

    async register(name, email, password) {
        try {
            const response = await fetch(`${this.baseUrl}/users/register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name, email, password })
            });

            if (!response.ok) {
                throw new Error('Registration failed');
            }

            const data = await response.json();
            localStorage.setItem('token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            return data;
        } catch (error) {
            console.error('Registration error:', error);
            throw error;
        }
    }

    logout() {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login.html';
    }

    isAuthenticated() {
        return !!localStorage.getItem('token');
    }

    getUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    }

    isAdmin() {
        const user = this.getUser();
        return user && user.role === 'admin';
    }

    async getAllUsers(page = 1, limit = 10) {
        try {
            const response = await fetch(`${this.baseUrl}/users?page=${page}&limit=${limit}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            if (!response.ok) throw new Error('Failed to fetch users');
            return await response.json();
        } catch (error) {
            console.error('Get users error:', error);
            throw error;
        }
    }

    async createUser(userData) {
        try {
            const response = await fetch(`${this.baseUrl}/users/register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify(userData)
            });
            if (!response.ok) throw new Error('Failed to create user');
            return await response.json();
        } catch (error) {
            console.error('Create user error:', error);
            throw error;
        }
    }

    async updateUser(id, userData) {
        try {
            const response = await fetch(`${this.baseUrl}/users/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify(userData)
            });
            if (!response.ok) throw new Error('Failed to update user');
            return await response.json();
        } catch (error) {
            console.error('Update user error:', error);
            throw error;
        }
    }

    async deleteUser(id) {
        try {
            const response = await fetch(`${this.baseUrl}/users/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            if (!response.ok) throw new Error('Failed to delete user');
            return await response.json();
        } catch (error) {
            console.error('Delete user error:', error);
            throw error;
        }
    }

    async updateUserRole(id, role) {
        try {
            const response = await fetch(`${this.baseUrl}/users/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify({ role })
            });
            if (!response.ok) throw new Error('Failed to update user role');
            return await response.json();
        } catch (error) {
            console.error('Update user role error:', error);
            throw error;
        }
    }
}

const authService = new AuthService();
export default authService; 