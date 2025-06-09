import productService from '../../services/productService.js';

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
  addProductForm?.addEventListener("submit", async (e) => {
    e.preventDefault();

    const productName = document.getElementById("product-name").value;
    const productDescription = document.getElementById("product-description").value;
    const productPrice = document.getElementById("product-price").value;
    const productCategory = document.getElementById("product-category").value;
    const productImage = document.getElementById("product-image").files[0];
    const productStock = document.getElementById("product-stock").value;
    const productId = addProductForm.dataset.editId;
    const currentImagePath = document.getElementById("current-image-path").value;

    if (!productCategory) {
      alert("Please select a category for the product");
      return;
    }

    if (!productImage && !productId && !currentImagePath) {
      alert("Please select an image for the product");
      return;
    }

    // Prepare product data for backend
    const productData = {
      name: productName,
      description: productDescription,
      price: parseFloat(productPrice),
      category_id: productCategory,
      stock: parseInt(productStock, 10)
    };
    if (productImage) {
      productData.image = productImage;
    } else if (currentImagePath) {
      productData.image = currentImagePath;
    }
    await submitProduct(productId, productData);
  });

  async function submitProduct(productId, productData) {
    try {
      if (productId) {
        // Editing existing product
        await productService.updateProduct(productId, productData);
        alert('Product updated successfully!');
      } else {
        // Adding new product
        await productService.createProduct(productData);
        alert('Product added successfully!');
      }
      addProductForm.reset();
      delete addProductForm.dataset.editId;
      productModal.style.display = "none";
      displayProducts();
    } catch (error) {
      alert('Failed to save product: ' + error.message);
    }
  }

  // Function to display products
  async function displayProducts() {
    try {
      const result = await productService.getAllProducts();
      const products = result.products || result;
      productsList.innerHTML = "";

      products.forEach(product => {
        const productElement = document.createElement("div");
        productElement.classList.add("product-item");
        productElement.innerHTML = `
          <img src="${product.image ? `http://localhost:8080/FootwearStore Tarik Coralic/backend/${product.image}` : ''}" alt="${product.name}" class="product-image">
          <h3>${product.name}</h3>
          <p>${product.description}</p>
          <p>Price: $${product.price}</p>
          <p>Category: ${product.category_name || product.category_id}</p>
          <p>Stock: ${product.stock}</p>
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
    } catch (error) {
      productsList.innerHTML = '<p>Failed to load products from backend.</p>';
    }
  }

  // Function to delete product
  async function deleteProduct(productId) {
    if (confirm("Are you sure you want to delete this product?")) {
      try {
        await productService.deleteProduct(productId);
        alert('Product deleted successfully!');
        displayProducts();
      } catch (error) {
        alert('Failed to delete product: ' + error.message);
      }
    }
  }

  // Function to edit product
  async function editProduct(productId) {
    try {
      const product = await productService.getProductById(productId);
      if (product) {
        modalTitle.textContent = "Edit Product";
        document.getElementById("product-name").value = product.name;
        document.getElementById("product-description").value = product.description;
        document.getElementById("product-price").value = product.price;
        document.getElementById("product-category").value = product.category_id;
        document.getElementById("product-stock").value = product.stock;
        addProductForm.dataset.editId = productId;
        // Set current image preview and hidden input
        const imagePreview = document.getElementById("current-product-image-preview");
        const currentImagePathInput = document.getElementById("current-image-path");
        if (product.image) {
          imagePreview.src = `http://localhost:8080/FootwearStore Tarik Coralic/backend/${product.image}`;
          imagePreview.style.display = "block";
          currentImagePathInput.value = product.image;
        } else {
          imagePreview.src = "";
          imagePreview.style.display = "none";
          currentImagePathInput.value = "";
        }
        productModal.style.display = "block";
      }
    } catch (error) {
      alert('Failed to fetch product: ' + error.message);
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

  // Initial load
  displayProducts();
});