document.getElementById("registerForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent form submission

    // Get input values
    let username = document.getElementById("username").value;
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;
    let confirmPassword = document.getElementById("confirmPassword").value;
    let passwordError = document.getElementById("passwordError");

    // Validate email format using regex
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return;
    }

    // Validate password confirmation
    if (password !== confirmPassword) {
        passwordError.textContent = "Passwords do not match!";
        passwordError.style.color = "red";
        return;
    } else {
        passwordError.textContent = "";
    }

    // Get existing users from localStorage or initialize an empty array
    let users = JSON.parse(localStorage.getItem("users")) || [];

    // Check if username or email already exists
    let userExists = users.some(user => user.username === username || user.email === email);
    if (userExists) {
        alert("Username or Email already exists. Please use a different one.");
        return;
    }

    // Assign "admin" role if the username or email matches predefined criteria
    let role = (username === "admin" || email === "admin@example.com") ? "admin" : "user";

    // Create new user object
    let newUser = {
        username: username,
        email: email,
        password: password,
        role: role
    };

    // Store user in localStorage
    users.push(newUser);
    localStorage.setItem("users", JSON.stringify(users));

    // Automatically log in the user
    localStorage.setItem("loggedInUser", JSON.stringify(newUser));

    alert(`Registration successful! You are now logged in as ${role}.`);

    // Redirect to the appropriate page
    window.location.href = role === "admin" ? "../admin-dashboard.html" : "../index.html";
});
