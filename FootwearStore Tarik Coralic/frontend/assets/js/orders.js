// Check if a user is logged in and if they are an admin
document.addEventListener('DOMContentLoaded', function() {
    const loggedInUser = JSON.parse(localStorage.getItem('loggedInUser'));
  
    // If no user is logged in or the user is not an admin, redirect to the login page
    if (!loggedInUser || loggedInUser.role !== 'admin') {
      alert('Access denied. You must be an admin to view this page.');
      window.location.href = 'login.html';  // Redirect to login page
    }
  });
  