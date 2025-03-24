document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById('login-form');
  
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();
  
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
  
            if (!email || !password) {
                alert('Please enter both email and password.');
                return;
            }
  
            // Get registered users from localStorage
            const users = JSON.parse(localStorage.getItem('users')) || [];
  
            // Check if admin is already defined, if not, add it
            if (!users.some(user => user.email === "admin@example.com")) {
                const adminUser = {
                    username: "admin",
                    email: "admin@example.com",
                    password: "admin123",
                    role: "admin"
                };
                users.push(adminUser);
                localStorage.setItem("users", JSON.stringify(users));
            }
  
            // Find the user in the stored data by email
            const user = users.find(user => user.email === email && user.password === password);
  
            if (user) {
                // Store user session data
                localStorage.setItem('loggedInUser', JSON.stringify({
                    email: user.email,
                    username: user.username,
                    role: user.role
                }));
  
                alert(`Welcome, ${user.username || user.email}! Redirecting...`);
                window.location.href = user.role === 'admin' ? 'admin-dashboard.html' : '../index.html';
            } else {
                alert('Invalid email or password. Please try again.');
            }
        });
    }
  });
  