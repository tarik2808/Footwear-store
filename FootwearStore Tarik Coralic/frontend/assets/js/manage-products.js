document.addEventListener("DOMContentLoaded", function () {
  const loggedInUser = JSON.parse(localStorage.getItem("loggedInUser"));

  // Redirect non-admin users to the home page
  if (!loggedInUser || loggedInUser.role !== "admin") {
      alert("Access Denied! Admins Only.");
      window.location.href = "../index.html";
      return;
  }

  console.log("Admin dashboard loaded for:", loggedInUser.username);

  // Delete duplicate Nike Air Max Tuned 1 product
  let products = JSON.parse(localStorage.getItem("products")) || [];
  let seenNames = new Set();
  products = products.filter(product => {
    if (product.name === "Nike Air Max Tuned 1") {
      if (seenNames.has(product.name)) {
        return false; // Remove duplicate
      }
      seenNames.add(product.name);
    }
    return true;
  });
  localStorage.setItem("products", JSON.stringify(products));

  // Get DOM elements
  const addProductBtn = document.getElementById("add-product-btn");
  const productModal = document.getElementById("product-modal");
  const addProductForm = document.getElementById("add-product-form");
  const cancelBtn = document.getElementById("cancel-btn");
  const productsList = document.getElementById("products-list");
  const modalTitle = document.getElementById("modal-title");

  // Show modal when Add Product button is clicked
  addProductBtn?.addEventListener("click", () => {
    modalTitle.textContent = "Add New Product";
    addProductForm.reset();
    delete addProductForm.dataset.editId;
    productModal.style.display = "block";
  });

  // Hide modal when Cancel button is clicked
  cancelBtn?.addEventListener("click", () => {
    productModal.style.display = "none";
    addProductForm.reset();
    delete addProductForm.dataset.editId;
  });

  // Close modal when clicking outside
  window.addEventListener("click", (event) => {
    if (event.target === productModal) {
      productModal.style.display = "none";
      addProductForm.reset();
      delete addProductForm.dataset.editId;
    }
  });

  // Handle form submission
  addProductForm?.addEventListener("submit", (e) => {
    e.preventDefault();

    const productName = document.getElementById("product-name").value;
    const productDescription = document.getElementById("product-description").value;
    const productPrice = document.getElementById("product-price").value;
    const productCategory = document.getElementById("product-category").value;
    const productImage = document.getElementById("product-image").files[0];
    const productId = addProductForm.dataset.editId || Date.now().toString();

    if (!productCategory) {
      alert("Please select a category for the product");
      return;
    }

    // If editing and no new image is selected, keep the existing image
    if (!productImage && addProductForm.dataset.editId) {
      const products = JSON.parse(localStorage.getItem("products")) || [];
      const existingProduct = products.find(p => p.id === productId);
      if (existingProduct) {
        updateProduct(productId, {
          name: productName,
          description: productDescription,
          price: parseFloat(productPrice),
          category: productCategory,
          image: existingProduct.image
        });
      }
      return;
    }

    if (!productImage && !addProductForm.dataset.editId) {
      alert("Please select an image for the product");
      return;
    }

    console.log("Adding product with category:", productCategory);

    // Convert image to base64
    const reader = new FileReader();
    reader.onload = function(event) {
      const imageData = event.target.result;
      
      // Create product object
      const product = {
        id: productId,
        name: productName,
        description: productDescription,
        price: parseFloat(productPrice),
        category: productCategory,
        image: imageData
      };

      console.log("Created product object:", product);

      // Get existing products or initialize empty array
      let products = JSON.parse(localStorage.getItem("products")) || [];
      
      if (addProductForm.dataset.editId) {
        // Update existing product
        const index = products.findIndex(p => p.id === productId);
        if (index !== -1) {
          products[index] = product;
        }
      } else {
        // Add new product
        products.push(product);
      }
      
      // Save to localStorage
      localStorage.setItem("products", JSON.stringify(products));

      // Reset form and close modal
      addProductForm.reset();
      delete addProductForm.dataset.editId;
      productModal.style.display = "none";

      // Refresh products list
      displayProducts();
    };

    if (productImage) {
      reader.readAsDataURL(productImage);
    }
  });

  // Function to display products
  function displayProducts() {
    const products = JSON.parse(localStorage.getItem("products")) || [];
    productsList.innerHTML = "";

    products.forEach(product => {
      const productElement = document.createElement("div");
      productElement.classList.add("product-item");
      productElement.innerHTML = `
        <img src="${product.image}" alt="${product.name}" class="product-image">
        <h3>${product.name}</h3>
        <p>${product.description}</p>
        <p>Price: $${product.price}</p>
        <p>Category: ${product.category}</p>
        <div class="product-actions">
          <button class="btn edit-btn" data-id="${product.id}">Edit</button>
          <button class="btn delete-btn" data-id="${product.id}">Delete</button>
        </div>
      `;
      productsList.appendChild(productElement);
    });

    // Add delete functionality
    document.querySelectorAll(".delete-btn").forEach(btn => {
      btn.addEventListener("click", (e) => {
        const productId = e.target.dataset.id;
        console.log("Delete button clicked for product ID:", productId);
        deleteProduct(productId);
      });
    });

    // Add edit functionality
    document.querySelectorAll(".edit-btn").forEach(btn => {
      btn.addEventListener("click", (e) => {
        const productId = e.target.dataset.id;
        editProduct(productId);
      });
    });
  }

  // Function to delete product
  function deleteProduct(productId) {
    if (confirm("Are you sure you want to delete this product?")) {
      let products = JSON.parse(localStorage.getItem("products")) || [];
      console.log("Deleting product with ID:", productId);
      console.log("Current products:", products);
      
      // Find the product to delete
      const productToDelete = products.find(p => p.id === productId);
      if (!productToDelete) {
        console.error("Product not found:", productId);
        return;
      }
      
      // Filter out the product to delete
      products = products.filter(product => String(product.id) !== String(productId));
      console.log("Products after deletion:", products);
      
      // Save to localStorage
      localStorage.setItem("products", JSON.stringify(products));
      
      // Refresh the display
      displayProducts();
    }
  }

  // Function to edit product
  function editProduct(productId) {
    const products = JSON.parse(localStorage.getItem("products")) || [];
    const product = products.find(p => p.id === productId);

    if (product) {
      modalTitle.textContent = "Edit Product";
      document.getElementById("product-name").value = product.name;
      document.getElementById("product-description").value = product.description;
      document.getElementById("product-price").value = product.price;
      document.getElementById("product-category").value = product.category;
      
      // Store the product ID for the form submission
      addProductForm.dataset.editId = productId;
      
      productModal.style.display = "block";
    }
  }

  // Function to update product
  function updateProduct(productId, updatedProduct) {
    let products = JSON.parse(localStorage.getItem("products")) || [];
    const index = products.findIndex(p => p.id === productId);
    
    if (index !== -1) {
      products[index] = { ...products[index], ...updatedProduct };
      localStorage.setItem("products", JSON.stringify(products));
      displayProducts();
      productModal.style.display = "none";
      delete addProductForm.dataset.editId;
    }
  }

  // Initial display of products
  displayProducts();
});