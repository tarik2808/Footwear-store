import productService from '../../services/productService.js';

document.addEventListener("DOMContentLoaded", function () {
    // Load all boots products when the page loads
    loadBoots();
});

// Helper function to load boots and display them in the category section
async function loadBoots() {
    const bootsSection = document.getElementById("boots-list");
    bootsSection.innerHTML = "";
    try {
        const result = await productService.getAllProducts();
        const products = result.products || result;
        const boots = products.filter(product => product.category_id == 2);
        boots.forEach(product => {
            const productItem = document.createElement("div");
            productItem.classList.add("product-item");

            const productImage = document.createElement("img");
            productImage.classList.add("product-image");
            productImage.src = product.image ? `http://localhost:8080/FootwearStore Tarik Coralic/backend/${product.image}` : 'default-image.png';
            productImage.alt = product.name;

            const productTitle = document.createElement("h3");
            productTitle.classList.add("product-title");
            productTitle.textContent = product.name;

            const productDescription = document.createElement("p");
            productDescription.classList.add("product-description");
            productDescription.textContent = product.description;

            const productPrice = document.createElement("p");
            productPrice.classList.add("product-price");
            productPrice.textContent = `$${product.price}`;

            const sizeSelection = document.createElement("div");
            sizeSelection.classList.add("product-size-selection");

            const sizeLabel = document.createElement("label");
            sizeLabel.setAttribute("for", `${product.name}-size`);
            sizeLabel.textContent = "Select Size:";

            const sizeSelect = document.createElement("select");
            sizeSelect.id = `${product.name}-size`;
            sizeSelect.name = "size";

            // Example sizes for boots (adjust according to your products)
            const sizes = ["EU 40", "EU 42", "EU 44", "EU 46"];
            sizes.forEach(size => {
                const option = document.createElement("option");
                option.value = size.toLowerCase().replace(" ", "-");
                option.textContent = size;
                sizeSelect.appendChild(option);
            });

            const addToCartButton = document.createElement("button");
            addToCartButton.classList.add("product-btn");
            addToCartButton.textContent = "Add to Cart";

            addToCartButton.onclick = function () {
                const selectedSize = sizeSelect.value;
                if (!selectedSize) {
                    alert("Please select a size");
                    return;
                }
                addToCart(product.id, product.name, selectedSize, product.price, product.image);
            };

            // Append all elements
            sizeSelection.appendChild(sizeLabel);
            sizeSelection.appendChild(sizeSelect);
            productItem.appendChild(productImage);
            productItem.appendChild(productTitle);
            productItem.appendChild(productDescription);
            productItem.appendChild(productPrice);
            productItem.appendChild(sizeSelection);
            productItem.appendChild(addToCartButton);

            bootsSection.appendChild(productItem);
        });
    } catch (error) {
        bootsSection.innerHTML = '<p>Failed to load boots from backend.</p>';
    }
}

// Remove the local addToCart function
