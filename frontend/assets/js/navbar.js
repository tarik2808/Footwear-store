document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById("menu-toggle");
    const navbar = document.querySelector("nav");

    menuToggle.addEventListener("click", function () {
        navbar.classList.toggle("nav-active");
    });
    
    // Close dropdown when clicking outside
    window.addEventListener("click", function(event) {
        if (!event.target.matches('.dropbtn')) {
            const dropdowns = document.getElementsByClassName("dropdown");
            for (let i = 0; i < dropdowns.length; i++) {
                const openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    });
});

// Function to disable categories dropdown when clicked
function disableCategories() {
    const dropdown = document.getElementById("categories-dropdown");
    if (dropdown) {
        dropdown.classList.toggle("show");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const loginBtn = document.getElementById("login-btn");
    const registerBtn = document.getElementById("register-btn");
    const logoutBtn = document.getElementById("logout-btn");
    const adminDashboardBtn = document.getElementById("admin-dashboard-btn");

    // Get logged-in user info
    const loggedInUser = JSON.parse(localStorage.getItem("user"));

    // Call the function to manage button visibility
    updateNavBar(loggedInUser);

            if (logoutBtn) {
            logoutBtn.addEventListener("click", function () {
                console.log("Logout button clicked - redirecting to login page");
                localStorage.removeItem("user");
                localStorage.removeItem("token");
            
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
            
            console.log("Redirecting to:", loginPath);
            window.location.href = loginPath;
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
