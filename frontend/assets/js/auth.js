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
            // Automatically log in the user after successful registration
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
        
        // Determine the correct path based on current location
        const currentPath = window.location.pathname;
        let loginPath;
        
        if (currentPath.includes('/pages/')) {
            // We're in a pages subfolder, go up one level then into pages
            loginPath = "../pages/login.html";
        } else {
            // We're in the root frontend folder
            loginPath = "./pages/login.html";
        }
        
        console.log("Auth.js logout - redirecting to:", loginPath);
        window.location.href = loginPath;
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
}

const authService = new AuthService(); 