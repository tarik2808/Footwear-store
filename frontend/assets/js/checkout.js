document.addEventListener("DOMContentLoaded", function () {
    // Check if user is logged in
    if (!isUserLoggedIn()) {
        alert("You must be logged in to access the checkout page.");
        window.location.href = "login.html";
        return;
    }

    // Display checkout total when the page loads
    displayCheckoutTotal();

    // Set up the form submit event listener
    const checkoutForm = document.getElementById("checkout-form");
    checkoutForm.addEventListener("submit", handleCheckoutForm);
});

// Function to check if the user is logged in
function isUserLoggedIn() {
    return localStorage.getItem('loggedInUser') !== null;  // Check if user is logged in
}

// Function to handle the form submission and validate the form data
function handleCheckoutForm(event) {
    event.preventDefault();

    // Check if the user is logged in
    if (!isUserLoggedIn()) {
        alert("You must be logged in to proceed with the checkout.");
        window.location.href = "login.html"; // Redirect to login page if not logged in
        return;
    }

    // Validate form fields
    const fullName = document.getElementById("full-name").value;
    const address = document.getElementById("address").value;
    const phoneNumber = document.getElementById("phone-number").value;
    const city = document.getElementById("city").value;
    const zipCode = document.getElementById("zip-code").value;

    if (!fullName || !address || !phoneNumber || !city || !zipCode) {
        alert("Please fill out all required fields.");
        return;
    }

    // Get the logged-in user
    const loggedInUser = JSON.parse(localStorage.getItem('loggedInUser'));
    const username = loggedInUser ? loggedInUser.username : 'Guest';

    // Retrieve cart items for the user
    const cart = JSON.parse(localStorage.getItem(`cart_${username}`)) || [];

    if (cart.length === 0) {
        alert("Your cart is empty. Add items before proceeding to checkout.");
        return;
    }

    // Order object to be stored in localStorage
    const order = {
        id: Date.now(),  // Unique order ID
        user: username,
        shippingInfo: {
            fullName: fullName,
            address: address,
            phoneNumber: phoneNumber,
            city: city,
            zipCode: zipCode
        },
        cartItems: cart, // Store the entire cart in the order
        status: "pending",  // Order status
        totalAmount: getCartTotal()  // Get the total from the cart
    };

    // Retrieve existing orders from localStorage and add the new order
    const existingOrders = JSON.parse(localStorage.getItem('orders')) || [];
    existingOrders.push(order);

    // Save the updated orders back to localStorage
    localStorage.setItem('orders', JSON.stringify(existingOrders));

    // Clear the user's cart after checkout
    localStorage.removeItem(`cart_${username}`);
    localStorage.removeItem(`cartTotal_${username}`);

    // Show success message
    const successMessage = document.createElement('div');
    successMessage.className = 'success-message';
    successMessage.textContent = "Order placed successfully! Thank you for your purchase.";
    successMessage.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #4CAF50;
        color: white;
        padding: 15px;
        border-radius: 5px;
        z-index: 1000;
        animation: fadeInOut 3s forwards;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    `;
    document.body.appendChild(successMessage);

    // Add CSS for the animation if it doesn't exist yet
    if (!document.getElementById('checkout-animation-style')) {
        const style = document.createElement('style');
        style.id = 'checkout-animation-style';
        style.textContent = `
            @keyframes fadeInOut {
                0% { opacity: 0; transform: translateY(-20px); }
                10% { opacity: 1; transform: translateY(0); }
                90% { opacity: 1; transform: translateY(0); }
                100% { opacity: 0; transform: translateY(-20px); }
            }
        `;
        document.head.appendChild(style);
    }

    // Redirect to home page after a short delay
    setTimeout(() => {
        window.location.href = "../index.html";
    }, 2000);
}


// Function to get the cart total for the logged-in user
function getCartTotal() {
    const storedUser = localStorage.getItem("loggedInUser");
    if (!storedUser) return 0;

    const username = JSON.parse(storedUser).username;
    const total = localStorage.getItem(`cartTotal_${username}`) || "0.00"; // Get total from localStorage
    return parseFloat(total);
}

// Function to display the cart total on checkout page
function displayCheckoutTotal() {
    const storedUser = localStorage.getItem("loggedInUser");
    if (!storedUser) return; // If no user is logged in, exit

    const username = JSON.parse(storedUser).username;
    const total = localStorage.getItem(`cartTotal_${username}`); // Use the correct key with the username

    if (total) {
        // Display the total in the checkout summary
        document.getElementById("checkout-total").textContent = `$${total}`;
    } else {
        // If no total found, display $0.00
        document.getElementById("checkout-total").textContent = "$0.00";
    }
}

// Function to logout
function logout() {
    console.log("User logged out");
    window.location.href = "login.html";
}
