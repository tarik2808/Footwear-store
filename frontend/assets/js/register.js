document.getElementById("registerForm").addEventListener("submit", async function(event) {
    event.preventDefault(); // Prevent form submission

    // Get input values
    let fullname = document.getElementById("fullname").value;
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

    try {
        // Register the user using authService
        const result = await authService.register(fullname, email, password);
        
        // Show success message
        alert("Registration successful! You are now logged in.");
        
        // Redirect to the appropriate page based on user role
        const user = result.user;
        window.location.href = user.role === "admin" ? "../admin-dashboard.html" : "../index.html";
    } catch (error) {
        alert(error.message || "Registration failed. Please try again.");
    }
});
