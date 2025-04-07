document.addEventListener('DOMContentLoaded', function() {
    // Fetch users from localStorage
    const users = JSON.parse(localStorage.getItem('users')) || [];
    let editingUserIndex = null;

    // Function to render users in the table
    function renderUsers() {
        const usersTableBody = document.getElementById('usersTableBody');
        usersTableBody.innerHTML = ''; // Clear the table before adding data

        users.forEach((user, index) => {
            const row = document.createElement('tr');

            row.innerHTML = `
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>${user.role}</td>
                <td>${user.status}</td>
                <td>
                    <button class="editBtn" data-index="${index}">Edit</button>
                    <button class="deleteBtn" data-index="${index}">Delete</button>
                    <button class="changeRoleBtn" data-index="${index}">Change Role</button>
                </td>
            `;
            usersTableBody.appendChild(row);
        });
    }

    // Add New User Button Handler
    document.getElementById('addUserBtn').addEventListener('click', function() {
        window.location.href = 'register.html';  // Adjust to your register page URL
    });

    // Edit User Handler
    document.getElementById('usersTableBody').addEventListener('click', function(event) {
        if (event.target.classList.contains('editBtn')) {
            const index = event.target.getAttribute('data-index');
            editingUserIndex = index;
            const userToEdit = users[index];

            document.getElementById('editUsername').value = userToEdit.username;
            document.getElementById('editEmail').value = userToEdit.email;
            document.getElementById('editRole').value = userToEdit.role;
            document.getElementById('editStatus').value = userToEdit.status;

            document.getElementById('editUserModal').style.display = 'flex';
        }
    });

    // Save Changes Handler
    document.getElementById('editUserForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const email = document.getElementById('editEmail').value;
        const role = document.getElementById('editRole').value;
        const status = document.getElementById('editStatus').value;

        if (editingUserIndex !== null) {
            const user = users[editingUserIndex];
            user.email = email;
            user.role = role;
            user.status = status;

            // Update the users in localStorage
            localStorage.setItem('users', JSON.stringify(users));

            // Re-render the users table
            renderUsers();

            // Hide the edit modal
            document.getElementById('editUserModal').style.display = 'none';
            alert('User details updated successfully!');
        }
    });

    // Cancel Edit
    document.getElementById('cancelEditBtn').addEventListener('click', function() {
        document.getElementById('editUserModal').style.display = 'none';
    });

    // Delete User Handler
    document.getElementById('usersTableBody').addEventListener('click', function(event) {
        if (event.target.classList.contains('deleteBtn')) {
            const index = event.target.getAttribute('data-index');

            if (confirm('Are you sure you want to delete this user?')) {
                users.splice(index, 1);

                // Update the users in localStorage
                localStorage.setItem('users', JSON.stringify(users));

                // Re-render the users table
                renderUsers();
            }
        }
    });

    // Change Role Handler
    document.getElementById('usersTableBody').addEventListener('click', function(event) {
        if (event.target.classList.contains('changeRoleBtn')) {
            const index = event.target.getAttribute('data-index');
            const user = users[index];

            // Toggle the user's role between admin and user
            user.role = user.role === 'admin' ? 'user' : 'admin';

            // Update the users in localStorage
            localStorage.setItem('users', JSON.stringify(users));

            // Re-render the users table
            renderUsers();
        }
    });

    // Initial render
    renderUsers();
});
