// Event listener to open/close the dropdown when clicking on the button
document.getElementById("categories-dropdown").addEventListener("click", function(event) {
    event.stopPropagation(); // Prevent the dropdown from closing when the button is clicked
  
    const dropdownContent = document.querySelector(".dropdown-content");
    dropdownContent.classList.toggle("show"); // Toggle the visibility of the dropdown
  });
  
  // Close the dropdown if clicked outside
  document.addEventListener("click", function(event) {
    const dropdownContent = document.querySelector(".dropdown-content");
    if (!dropdownContent.contains(event.target)) {
      dropdownContent.classList.remove("show");
    }
  });
  
  // Optional: Highlight the currently selected category when clicked
  const categoryItems = document.querySelectorAll(".category-item");
  
  categoryItems.forEach(item => {
    item.addEventListener("click", function() {
      // Remove the active class from all category items
      categoryItems.forEach(cat => cat.classList.remove("active"));
      
      // Add the active class to the clicked category item
      this.classList.add("active");
    });
  });
  