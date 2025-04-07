// Helper function to get logged-in user's username
function getLoggedInUser() {
    const storedUser = localStorage.getItem("loggedInUser");
    return storedUser ? JSON.parse(storedUser).username : null;
}

// Global addToCart function to be used across all product pages
function addToCart(productId, productName, productSize, productPrice, productImage) {
    const username = getLoggedInUser();
    if (!username) {
        alert("You must be logged in to add items to the cart.");
        window.location.href = "login.html"; 
        return;
    }

    let cart = getUserCart(username);
    const existingItemIndex = cart.findIndex(item => item.id === productId && item.size === productSize);

    if (existingItemIndex !== -1) {
        cart[existingItemIndex].quantity += 1;
    } else {
        if (!productImage) {
            console.warn("Missing image for product:", productName);
            productImage = "../assets/images/placeholder.png"; // Use a default image
        }
        cart.push({ 
            id: productId, 
            name: productName, 
            size: productSize, 
            price: productPrice, 
            quantity: 1, 
            image: productImage
        });
    }

    saveUserCart(username, cart);
    saveCartObject(username);
    
    // Create and show success message
    showSuccessMessage(`${productName} has been added to your cart!`);
}

// Function to show success message
function showSuccessMessage(message) {
    // Remove any existing success messages first
    const existingMessages = document.querySelectorAll('.success-message');
    existingMessages.forEach(msg => msg.remove());

    // Create success message element with inline styles
    const successMessage = document.createElement('div');
    successMessage.className = 'success-message';
    successMessage.textContent = message;
    successMessage.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #4CAF50;
        color: white;
        padding: 15px;
        border-radius: 5px;
        z-index: 9999;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        animation: fadeInOut 3s forwards;
    `;
    
    // Add CSS for the animation if it doesn't exist yet
    if (!document.getElementById('cart-animation-style')) {
        const style = document.createElement('style');
        style.id = 'cart-animation-style';
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
    
    // Append to body
    document.body.appendChild(successMessage);
    
    // Remove the message after animation completes
    setTimeout(() => {
        if (successMessage.parentNode) {
            successMessage.remove();
        }
    }, 3000);
}

// Helper functions for cart operations
function getUserCart(username) {
    return JSON.parse(localStorage.getItem(`cart_${username}`)) || [];
}

function saveUserCart(username, cart) {
    localStorage.setItem(`cart_${username}`, JSON.stringify(cart));
}

function saveCartObject(username) {
    const cart = getUserCart(username);
    const cartObject = {
        id: Date.now(),
        user: username,
        items: cart,
        totalAmount: calculateTotal(cart)
    };
    localStorage.setItem(`cartObject_${username}`, JSON.stringify(cartObject));
}

function calculateTotal(cart) {
    return cart.reduce((acc, item) => acc + (item.price * item.quantity), 0).toFixed(2);
} 