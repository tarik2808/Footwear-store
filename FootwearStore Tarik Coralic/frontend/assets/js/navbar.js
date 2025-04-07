document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById("menu-toggle");
    const navbar = document.querySelector("nav");

    menuToggle.addEventListener("click", function () {
        navbar.classList.toggle("nav-active");
    });
});




document.addEventListener("DOMContentLoaded", function () {
    const loginBtn = document.getElementById("login-btn");
    const registerBtn = document.getElementById("register-btn");
    const logoutBtn = document.getElementById("logout-btn");
    const adminDashboardBtn = document.getElementById("admin-dashboard-btn");

    // Get logged-in user info
    const loggedInUser = JSON.parse(localStorage.getItem("loggedInUser"));

    // Call the function to manage button visibility
    updateNavBar(loggedInUser);

    if (logoutBtn) {
        logoutBtn.addEventListener("click", function () {
            localStorage.removeItem("loggedInUser");
            window.location.href = "/frontend/pages/login.html"; 
        });
    }
});




// Helper function to manage navbar visibility
function updateNavBar(loggedInUser) {
    const loginBtn = document.getElementById("login-btn");
    const registerBtn = document.getElementById("register-btn");
    const logoutBtn = document.getElementById("logout-btn");
    const adminDashboardBtn = document.getElementById("admin-dashboard-btn");

    if (loggedInUser) {
        // Hide login & register, show logout
        if (loginBtn) loginBtn.style.display = "none";
        if (registerBtn) registerBtn.style.display = "none";
        if (logoutBtn) logoutBtn.style.display = "inline-block";

        // Show admin dashboard button for admins
        if (loggedInUser.role === "admin" && adminDashboardBtn) {
            adminDashboardBtn.style.display = "inline-block";
        }
    } else {
        // Show login & register, hide logout and admin dashboard
        if (loginBtn) loginBtn.style.display = "inline-block";
        if (registerBtn) registerBtn.style.display = "inline-block";
        if (logoutBtn) logoutBtn.style.display = "none";
        if (adminDashboardBtn) adminDashboardBtn.style.display = "none";
    }
}
