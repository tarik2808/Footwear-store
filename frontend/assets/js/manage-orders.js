import orderService from '../../services/orderService.js';

document.addEventListener("DOMContentLoaded", function () {
    // Check if the user is an admin
    const user = JSON.parse(localStorage.getItem('user'));
    if (!user || user.role !== 'admin') {
        alert("You must be logged in as an admin to view this page.");
        window.location.href = "login.html"; // Redirect to login page if not admin
        return;
    }

    // Load orders when the page loads
    loadOrders();
});

// Function to load orders from localStorage and display them in cards
async function loadOrders() {
    console.log('loadOrders called');
    const ordersList = document.getElementById("orders-list");
    ordersList.innerHTML = "";
    try {
        console.log('Calling orderService.getOrders()');
        const result = await orderService.getOrders();
        console.log('orderService result:', result);
        const orders = result.orders || [];
        if (orders.length === 0) {
            ordersList.innerHTML = "<p>No orders available.</p>";
            return;
        }
        // Loop through orders and create order cards
        orders.forEach(order => {
            console.log('Order object:', order); // Log the order object for debugging
            const card = document.createElement("div");
            card.classList.add("order-card");

            // Order Header
            const orderHeader = document.createElement("div");
            orderHeader.classList.add("order-header");
            orderHeader.innerHTML = `
                <h3>Order #${order.id || order.order_id} (${order.status})</h3>
                <p><strong>User:</strong> ${order.user || order.user_name || order.user_id}</p>
                <p><strong>Shipping Address:</strong> ${order.shipping_address || ''}</p>
            `;
            card.appendChild(orderHeader);

            // Order Details
            const orderDetails = document.createElement("div");
            orderDetails.classList.add("order-details");
            orderDetails.innerHTML = `
                <p><strong>User Name:</strong> ${order.user_name || ''}</p>
                <p><strong>Status:</strong> ${order.status || ''}</p>
                <p><strong>Total Price:</strong> $${order.total_price || ''}</p>
                <p><strong>Created At:</strong> ${order.created_at || ''}</p>
                <p><strong>Shipping Name:</strong> ${order.shipping_name || ''}</p>
                <p><strong>Shipping Address:</strong> ${order.shipping_address || ''}</p>
                <p><strong>Phone:</strong> ${order.shipping_phone || ''}</p>
                <p><strong>City:</strong> ${order.shipping_city || ''}</p>
                <p><strong>Zip Code:</strong> ${order.shipping_zip || ''}</p>
            `;
            card.appendChild(orderDetails);

            // Order Products
            const productsSection = document.createElement("div");
            productsSection.classList.add("order-products");
            if (order.items && order.items.length > 0) {
                console.log('Order items found:', order.items);
                const productList = document.createElement("ul");
                productList.classList.add("product-list");
                order.items.forEach(item => {
                    console.log('Processing item:', item);
                    const listItem = document.createElement("li");
                    listItem.classList.add("product-item");
                    
                    // Create product image
                    const imgElement = document.createElement("img");
                    const imagePath = item.image ? `../../backend/${item.image}` : "../assets/images/placeholder.png";
                    console.log('Image path:', imagePath);
                    imgElement.src = imagePath;
                    imgElement.alt = item.product_name;
                    imgElement.classList.add("product-image");
                    
                    // Create product details
                    const detailsElement = document.createElement("div");
                    detailsElement.classList.add("product-details");
                    detailsElement.innerHTML = `
                        <p class="product-name">${item.product_name}</p>
                        <p class="product-info">Size: ${item.selected_size || 'N/A'}, Quantity: ${item.quantity}</p>
                        <p class="product-price">$${(item.product_price * item.quantity).toFixed(2)}</p>
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
            if (order.status.toLowerCase() !== "shipped") {
                const shippedButton = document.createElement("button");
                shippedButton.textContent = "Mark as Shipped";
                shippedButton.classList.add("mark-shipped");
                shippedButton.onclick = () => markAsShipped(order.id);
                actions.appendChild(shippedButton);
            }
            const deleteButton = document.createElement("button");
            deleteButton.textContent = "Delete";
            deleteButton.classList.add("delete");
            deleteButton.onclick = () => deleteOrder(order.id);
            actions.appendChild(deleteButton);
            card.appendChild(actions);

            // Append card to orders list
            ordersList.appendChild(card);
        });
    } catch (error) {
        ordersList.innerHTML = "<p>Failed to load orders from backend.</p>";
        console.error(error);
    }
}

// Function to mark an order as "Shipped"
async function markAsShipped(orderId) {
    try {
        await orderService.updateOrderStatus(orderId, "shipped");
        await loadOrders(); // Reload orders from backend
    } catch (error) {
        alert("Failed to mark as shipped. Please try again.");
        console.error(error);
    }
}

// Function to delete an order
function deleteOrder(orderId) {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const updatedOrders = orders.filter(order => order.id !== orderId);

    localStorage.setItem('orders', JSON.stringify(updatedOrders));
    loadOrders();  // Reload orders to update the display
}
