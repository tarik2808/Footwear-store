document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById('login-form');
  
    if (loginForm) {
        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault();
  
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
  
            if (!email || !password) {
                alert('Please enter both email and password.');
                return;
            }
  
            try {
                console.log('Attempting to login with:', { email });
                
                const response = await fetch('http://localhost:8080/FootwearStore%20Tarik%20Coralic/backend/api.php/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                });
  
                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);
  
                if (data.success) {
                    // Store minimal user info in localStorage for admin checks
                    const userObj = {
                        id: data.user.id,
                        email: data.user.email,
                        name: data.user.name,
                        role: data.user.role
                    };
                    localStorage.setItem('loggedInUser', JSON.stringify(userObj));
                    localStorage.setItem('user', JSON.stringify(userObj)); // For cart and other logic
                    // Store JWT token for API authentication if present
                    if (data.token) {
                        localStorage.setItem('token', data.token);
                    }
                    alert(`Welcome, ${data.user.name}! Redirecting...`);
                    window.location.href = data.user.role === 'admin' ? 'admin-dashboard.html' : '../index.html';
                } else {
                    alert(data.message || 'Invalid email or password. Please try again.');
                }
            } catch (error) {
                console.error('Login error details:', {
                    message: error.message,
                    stack: error.stack
                });
                alert('An error occurred during login. Please check the console for details and try again.');
            }
        });
    }
});
  