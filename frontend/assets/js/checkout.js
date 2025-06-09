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
    return localStorage.getItem('user') !== null;  // Check if user is logged in
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

    // Extra validation and logging
    const missingFields = [];
    if (!fullName) missingFields.push('Full Name');
    if (!address) missingFields.push('Address');
    if (!phoneNumber) missingFields.push('Phone Number');
    if (!city) missingFields.push('City');
    if (!zipCode) missingFields.push('Zip Code');
    if (missingFields.length > 0) {
        alert('Please fill out the following fields: ' + missingFields.join(', '));
        return;
    }

    // Get the logged-in user
    const loggedInUser = JSON.parse(localStorage.getItem('user'));
    const email = loggedInUser ? loggedInUser.email : 'Guest';

    // Retrieve cart items for the user
    const cart = JSON.parse(localStorage.getItem(`cart_${email}`)) || [];

    if (cart.length === 0) {
        alert("Your cart is empty. Add items before proceeding to checkout.");
        return;
    }

    // Map cart items to use product_id instead of id
    const cartItems = cart.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        price: item.price
    }));

    // Order object to be sent to backend
    const order = {
        shipping_name: fullName,
        shipping_address: address,
        shipping_phone: phoneNumber,
        shipping_city: city,
        shipping_zip: zipCode,
        payment_method: 'cash', // or get from form if you have payment method selection
        cart_items: cartItems
    };

    // Log the order payload for debugging
    console.log('Order payload:', order);

    // Send order to backend (replace with your API call)
    fetch('http://localhost:8080/FootwearStore Tarik Coralic/backend/rest/api/orders', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify(order)
    })
    .then(response => response.json())
    .then(data => {
        // Handle success (clear cart, show message, redirect, etc.)
        localStorage.removeItem(`cart_${email}`);
        localStorage.removeItem(`cartTotal_${email}`);
        alert('Order placed successfully!');
        window.location.href = '../index.html';
    })
    .catch(error => {
        alert('Failed to place order. Please try again.');
        console.error(error);
    });
}

// Function to get the cart total for the logged-in user
function getCartTotal() {
    const storedUser = localStorage.getItem("user");
    if (!storedUser) return 0;

    const email = JSON.parse(storedUser).email;
    const total = localStorage.getItem(`cartTotal_${email}`) || "0.00"; // Get total from localStorage
    return parseFloat(total);
}

// Function to display the cart total on checkout page
function displayCheckoutTotal() {
    const storedUser = localStorage.getItem("user");
    if (!storedUser) return; // If no user is logged in, exit

    const email = JSON.parse(storedUser).email;
    const total = localStorage.getItem(`cartTotal_${email}`); // Use the correct key with the email

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
