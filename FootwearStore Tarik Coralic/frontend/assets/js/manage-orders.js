document.addEventListener("DOMContentLoaded", function () {
    // Check if the user is an admin
    const loggedInUser = JSON.parse(localStorage.getItem('loggedInUser'));
    if (!loggedInUser || loggedInUser.role !== 'admin') {
        alert("You must be logged in as an admin to view this page.");
        window.location.href = "login.html"; // Redirect to login page if not admin
        return;
    }

    // Load orders when the page loads
    loadOrders();
});

// Function to load orders from localStorage and display them in cards
function loadOrders() {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const ordersList = document.getElementById("orders-list");

    // Clear existing orders
    ordersList.innerHTML = "";

    // If there are no orders, show a message
    if (orders.length === 0) {
        ordersList.innerHTML = "<p>No orders available.</p>";
        return;
    }

    // Loop through orders and create order cards
    orders.forEach(order => {
        const card = document.createElement("div");
        card.classList.add("order-card");

        // Order Header
        const orderHeader = document.createElement("div");
        orderHeader.classList.add("order-header");
        orderHeader.innerHTML = `
            <h3>Order #${order.id} (${order.status})</h3>
            <p><strong>User:</strong> ${order.user}</p>
            <p><strong>Full Name:</strong> ${order.shippingInfo.fullName}</p>
        `;
        card.appendChild(orderHeader);

        // Order Details
        const orderDetails = document.createElement("div");
        orderDetails.classList.add("order-details");
        orderDetails.innerHTML = `
            <p><strong>Address:</strong> ${order.shippingInfo.address}</p>
            <p><strong>Phone:</strong> ${order.shippingInfo.phoneNumber}</p>
            <p><strong>City:</strong> ${order.shippingInfo.city}</p>
            <p><strong>Zip Code:</strong> ${order.shippingInfo.zipCode}</p>
            <p><strong>Total Amount:</strong> $${order.totalAmount.toFixed(2)}</p>
        `;
        card.appendChild(orderDetails);

        // Order Products
        const productsSection = document.createElement("div");
        productsSection.classList.add("order-products");
        if (order.cartItems && order.cartItems.length > 0) {
            const productList = document.createElement("ul");
            productList.classList.add("product-list");
            order.cartItems.forEach(item => {
                const listItem = document.createElement("li");
                listItem.classList.add("product-item");
                
                // Create product image
                const imgElement = document.createElement("img");
                imgElement.src = item.image;
                imgElement.alt = item.name;
                imgElement.classList.add("product-image");
                
                // Create product details
                const detailsElement = document.createElement("div");
                detailsElement.classList.add("product-details");
                detailsElement.innerHTML = `
                    <p class="product-name">${item.name}</p>
                    <p class="product-info">Size: ${item.size}, Quantity: ${item.quantity}</p>
                    <p class="product-price">$${(item.price * item.quantity).toFixed(2)}</p>
                `;
                
                listItem.appendChild(imgElement);
                listItem.appendChild(detailsElement);
                productList.appendChild(listItem);
            });
            productsSection.appendChild(productList);
        } else {
            productsSection.innerHTML = "<p>No products</p>";
        }
        card.appendChild(productsSection);

        // Action Buttons
        const actions = document.createElement("div");
        actions.classList.add("actions");
        const shippedButton = document.createElement("button");
        shippedButton.textContent = "Mark as Shipped";
        shippedButton.classList.add("mark-shipped");
        shippedButton.onclick = () => markAsShipped(order.id);

        const deleteButton = document.createElement("button");
        deleteButton.textContent = "Delete";
        deleteButton.classList.add("delete");
        deleteButton.onclick = () => deleteOrder(order.id);

        actions.appendChild(shippedButton);
        actions.appendChild(deleteButton);
        card.appendChild(actions);

        // Append card to orders list
        ordersList.appendChild(card);
    });
}

// Function to mark an order as "Shipped"
function markAsShipped(orderId) {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const orderIndex = orders.findIndex(order => order.id === orderId);

    if (orderIndex !== -1) {
        orders[orderIndex].status = "Shipped";
        localStorage.setItem('orders', JSON.stringify(orders));
        loadOrders();  // Reload orders to update the display
    }
}

// Function to delete an order
function deleteOrder(orderId) {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const updatedOrders = orders.filter(order => order.id !== orderId);

    localStorage.setItem('orders', JSON.stringify(updatedOrders));
    loadOrders();  // Reload orders to update the display
}
