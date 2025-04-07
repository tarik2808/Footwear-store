document.addEventListener("DOMContentLoaded", function () {
    const loggedInUser = JSON.parse(localStorage.getItem("loggedInUser"));

    // Redirect non-admin users to the home page
    if (!loggedInUser || loggedInUser.role !== "admin") {
        alert("Access Denied! Admins Only.");
        window.location.href = "../index.html";
        return;
    }

    console.log("Admin dashboard loaded for:", loggedInUser.username);

    function manageProducts() { console.log("Managing Products"); }
    function manageUsers() { console.log("Managing Users"); }
    function manageOrders() { console.log("Managing Orders"); }
    function manageCategories() { console.log("Managing Categories"); }
    function manageCart() { console.log("Managing Cart"); }

    document.querySelector("#manage-products")?.addEventListener("click", manageProducts);
    document.querySelector("#manage-users")?.addEventListener("click", manageUsers);
    document.querySelector("#manage-orders")?.addEventListener("click", manageOrders);
    document.querySelector("#manage-categories")?.addEventListener("click", manageCategories);
    document.querySelector("#manage-cart")?.addEventListener("click", manageCart);
});
