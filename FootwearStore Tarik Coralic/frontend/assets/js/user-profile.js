document.addEventListener("DOMContentLoaded", function () {
    checkLoginStatus();
    document.getElementById("logout-btn").addEventListener("click", logout);
});

// Function to check if the user is logged in
function checkLoginStatus() {
    const user = getLoggedInUser();
    
    if (!user) {
        redirectToLogin();
        return;
    }

    displayUserInfo(user);
    loadOrderHistory(user.username);
}

// Function to get the logged-in user from localStorage
function getLoggedInUser() {
    const storedUser = localStorage.getItem("loggedInUser");
    return storedUser ? JSON.parse(storedUser) : null;
}

// Function to redirect to the login page
function redirectToLogin() {
    alert("You must be logged in to access the profile page.");
    window.location.href = "login.html"; // Redirect to login
}

// Function to display user information on the profile page
function displayUserInfo(user) {
    document.getElementById("username").textContent = user.username;
    document.getElementById("email").textContent = user.email;
}

// Function to load and display the user's order history
function loadOrderHistory(username) {
    const orders = getUserOrders(username);
    const orderList = document.getElementById("order-list");

    orderList.innerHTML = ""; // Clear previous orders

    if (orders.length === 0) {
        orderList.innerHTML = "<p>No orders found.</p>";
        return;
    }

    orders.forEach(order => {
        orderList.appendChild(createOrderCard(order));
    });
}



// Function to get orders for a specific user
function getUserOrders(username) {
    const allOrders = JSON.parse(localStorage.getItem("orders")) || [];
    return allOrders.filter(order => order.user === username);
}

// Function to create an HTML card for an order
function createOrderCard(order) {
    const orderCard = document.createElement("div");
    orderCard.classList.add("order-card");

    orderCard.innerHTML = `
        <h4>Order #${order.id}</h4>
        <div class="status ${order.status.toLowerCase()}">${order.status}</div>
        <p><strong>Total Amount:</strong> $${order.totalAmount.toFixed(2)}</p>
        <div class="shipping-info">
            <p><strong>Shipping to:</strong> ${order.shippingInfo.fullName}</p>
            <p>${order.shippingInfo.address}, ${order.shippingInfo.city}, ${order.shippingInfo.zipCode}</p>
            <p><strong>Phone:</strong> ${order.shippingInfo.phoneNumber}</p>
        </div>
    `;
    
    return orderCard;
}

// Function to log out the user
function logout() {
    localStorage.removeItem("loggedInUser");
    window.location.href = "login.html"; // Redirect to login page
}
