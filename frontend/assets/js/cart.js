document.addEventListener("DOMContentLoaded", function () {
    console.log("Checking login status...");

    if (window.location.pathname.includes("cart.html")) {
        const email = getLoggedInUser();
        if (!email) {
            alert("You must be logged in to access the cart.");
            window.location.href = "login.html"; 
            return;
        }

        // Load cart for the specific user
        displayCart(email);

        // Checkout button click
        document.getElementById("checkout-btn").addEventListener("click", function () {
            saveCartObject(email); // Save the cart object before proceeding
            checkout(email);
        });
    }
});

// Helper function to get logged-in user's email
function getLoggedInUser() {
    const storedUser = localStorage.getItem("user");
    return storedUser ? JSON.parse(storedUser).email : null;
}

function getUserCart(email) {
    return JSON.parse(localStorage.getItem(`cart_${email}`)) || [];
}

function saveUserCart(email, cart) {
    localStorage.setItem(`cart_${email}`, JSON.stringify(cart));
}

function calculateTotal(cart) {
    return cart.reduce((acc, item) => acc + (item.price * item.quantity), 0).toFixed(2);
}

function displayCart(email) {
    const cartContainer = document.getElementById('cart-items');
    const userCart = getUserCart(email);
    
    if (userCart.length === 0) {
        cartContainer.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
        document.getElementById('checkout-btn').disabled = true;
        return;
    }

    cartContainer.innerHTML = '';
    let total = 0;

    userCart.forEach((item, index) => {
        const itemElement = document.createElement('div');
        itemElement.className = 'cart-item';
        
        // Create image element
        const imgElement = document.createElement('img');
        imgElement.className = 'cart-item-image';
        imgElement.src = item.image ? item.image : '../assets/images/placeholder.png';
        imgElement.alt = item.name;
        
        // Create details container
        const detailsElement = document.createElement('div');
        detailsElement.className = 'cart-item-details';
        
        // Add name
        const nameElement = document.createElement('div');
        nameElement.className = 'item-name';
        nameElement.textContent = `${item.name} (Size: ${item.size}) x${item.quantity}`;
        
        // Add price
        const priceElement = document.createElement('div');
        priceElement.className = 'item-price';
        priceElement.textContent = `$${(item.price * item.quantity).toFixed(2)}`;
        
        // Add remove button
        const removeButton = document.createElement('button');
        removeButton.className = 'remove-btn';
        removeButton.textContent = 'Remove One';
        removeButton.onclick = () => removeOneItemFromCart(email, item.id, item.size);
        
        // Assemble the elements
        detailsElement.appendChild(nameElement);
        detailsElement.appendChild(priceElement);
        detailsElement.appendChild(removeButton);
        
        itemElement.appendChild(imgElement);
        itemElement.appendChild(detailsElement);
        
        cartContainer.appendChild(itemElement);
        total += item.price * item.quantity;
    });

    // Update total
    const totalElement = document.getElementById('cart-total');
    if (totalElement) {
        totalElement.textContent = `$${total.toFixed(2)}`;
        localStorage.setItem(`cartTotal_${email}`, total.toFixed(2));
    }

    // Enable checkout button
    document.getElementById('checkout-btn').disabled = false;

    // Save cart object
    saveCartObject(email);
}

function removeOneItemFromCart(email, productId, productSize) {
    let cart = getUserCart(email);
    const itemIndex = cart.findIndex(item => item.id === productId && item.size === productSize);

    if (itemIndex !== -1) {
        const itemName = cart[itemIndex].name;
        if (cart[itemIndex].quantity > 1) {
            cart[itemIndex].quantity -= 1;
        } else {
            cart.splice(itemIndex, 1);
        }
        
        // Show success message
        const successMessage = document.createElement('div');
        successMessage.className = 'success-message';
        successMessage.textContent = `One ${itemName} has been removed from your cart.`;
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
        if (!document.getElementById('cart-remove-animation-style')) {
            const style = document.createElement('style');
            style.id = 'cart-remove-animation-style';
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

        // Remove the message after 3 seconds
        setTimeout(() => {
            successMessage.remove();
        }, 3000);
    }

    saveUserCart(email, cart);
    displayCart(email);
}

// Function to save the cart object to localStorage
function saveCartObject(email) {
    let cart = getUserCart(email);

    const cartObject = {
        id: Date.now(), // Unique ID for this cart session
        user: email,
        items: cart,
        totalAmount: calculateTotal(cart)
    };

    localStorage.setItem(`cartObject_${email}`, JSON.stringify(cartObject));
}

function checkout(email) {
    let cart = getUserCart(email);

    if (cart.length === 0) {
        alert("Your cart is empty. Add items first.");
        return;
    }

    localStorage.setItem(`cartTotal_${email}`, calculateTotal(cart));
    window.location.href = "checkout.html";
}

function logout() {
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
    
    console.log("Cart.js logout - redirecting to:", loginPath);
    window.location.href = loginPath;
}


