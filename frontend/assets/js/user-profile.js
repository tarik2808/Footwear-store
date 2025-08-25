document.addEventListener("DOMContentLoaded", function () {
    checkLoginStatus();
    document.getElementById("logout-btn").addEventListener("click", logout);
    
    // Add refresh button for order history
    const refreshBtn = document.createElement("button");
    refreshBtn.textContent = "🔄 Refresh Orders";
    refreshBtn.classList.add("refresh-btn");
    refreshBtn.addEventListener("click", () => loadOrderHistory());
    
    const orderList = document.getElementById("order-list");
    if (orderList) {
        orderList.parentNode.insertBefore(refreshBtn, orderList);
    }
});

// Function to check if the user is logged in
function checkLoginStatus() {
    console.log('checkLoginStatus called');
    const user = getLoggedInUser();
    console.log('User from localStorage:', user);
    
    if (!user) {
        console.log('No user found, redirecting to login');
        redirectToLogin();
        return;
    }

    console.log('User found, displaying info and loading orders');
    displayUserInfo(user);
    loadOrderHistory();
}

// Function to get the logged-in user from localStorage
function getLoggedInUser() {
    const storedUser = localStorage.getItem("user");
    return storedUser ? JSON.parse(storedUser) : null;
}

// Function to redirect to the login page
function redirectToLogin() {
    alert("You must be logged in to access the profile page.");
    
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
    
    console.log("User-profile.js redirectToLogin - redirecting to:", loginPath);
    window.location.href = loginPath;
}

// Function to display user information on the profile page
function displayUserInfo(user) {
    document.getElementById("username").textContent = user.name;
    document.getElementById("email").textContent = user.email;
}

// Function to load and display the user's order history
async function loadOrderHistory(username) {
    console.log('loadOrderHistory called');
    try {
        const user = getLoggedInUser();
        const token = localStorage.getItem('token');
        console.log('User:', user);
        console.log('Token:', token ? 'Token exists' : 'No token');
        
        if (!user || !token) {
            console.error('No user or token found');
            return;
        }

        const response = await fetch(`../../backend/rest/api/orders/user`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('API Response:', result);
        const orders = result.orders || [];
        console.log('Orders array:', orders);
        const orderList = document.getElementById("order-list");

        orderList.innerHTML = ""; // Clear previous orders

        if (orders.length === 0) {
            orderList.innerHTML = "<p>No orders found.</p>";
            return;
        }

        orders.forEach(order => {
            orderList.appendChild(createOrderCard(order));
        });
    } catch (error) {
        console.error('Error loading order history:', error);
        document.getElementById("order-list").innerHTML = "<p>Error loading orders. Please try again.</p>";
    }
}



// Function removed - now using API calls instead of localStorage

// Function to create an HTML card for an order
function createOrderCard(order) {
    const orderCard = document.createElement("div");
    orderCard.classList.add("order-card");

    // Format the date
    const createdDate = new Date(order.created_at).toLocaleDateString();
    
    orderCard.innerHTML = `
        <h4>Order #${order.id || order.order_id}</h4>
        <div class="status ${order.status.toLowerCase()}">${order.status}</div>
        <p><strong>Total Amount:</strong> $${(order.total_price || 0).toFixed(2)}</p>
        <p><strong>Order Date:</strong> ${createdDate}</p>
        <div class="shipping-info">
            <p><strong>Shipping to:</strong> ${order.shipping_name || 'N/A'}</p>
            <p>${order.shipping_address || 'N/A'}, ${order.shipping_city || 'N/A'}, ${order.shipping_zip || 'N/A'}</p>
            <p><strong>Phone:</strong> ${order.shipping_phone || 'N/A'}</p>
        </div>
        <div class="order-items">
            <h5>Items:</h5>
            ${order.items && order.items.length > 0 ? 
                order.items.map(item => `
                    <div class="order-item">
                        <span class="item-name">${item.product_name || 'Unknown Product'}</span>
                        <span class="item-size">Size: ${item.selected_size || 'N/A'}</span>
                        <span class="item-quantity">Qty: ${item.quantity}</span>
                        <span class="item-price">$${(item.product_price || 0).toFixed(2)}</span>
                    </div>
                `).join('') : 
                '<p>No items found</p>'
            }
        </div>
    `;
    
    return orderCard;
}

// Function to log out the user
function logout() {
    localStorage.removeItem("user");
    
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
    
    console.log("User-profile.js logout - redirecting to:", loginPath);
    window.location.href = loginPath;
}
