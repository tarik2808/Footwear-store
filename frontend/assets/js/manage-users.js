import authService from '../../services/authService.js';

document.addEventListener('DOMContentLoaded', async function() {
    let users = [];
    let editingUserId = null;
    let currentPage = 1;
    let totalPages = 1;
    const USERS_PER_PAGE = 10;

    // Fetch users from backend
    async function loadUsers(page = 1) {
        try {
            const result = await authService.getAllUsers(page, USERS_PER_PAGE);
            users = result.users || result;
            currentPage = result.page || page;
            totalPages = Math.ceil((result.total || users.length) / USERS_PER_PAGE);
            renderUsers();
            renderPagination();
        } catch (error) {
            alert('Failed to load users: ' + error.message);
        }
    }

    // Function to render users in the table
    function renderUsers() {
        const usersTableBody = document.getElementById('usersTableBody');
        usersTableBody.innerHTML = '';
        users.forEach((user, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${user.name || user.username || ''}</td>
                <td>${user.email}</td>
                <td>${user.role}</td>
                <td>${user.status || 'active'}</td>
                <td>
                    <button class="editBtn" data-id="${user.id}">Edit</button>
                    <button class="deleteBtn" data-id="${user.id}">Delete</button>
                </td>
            `;
            usersTableBody.appendChild(row);
        });
    }

    // Render pagination controls
    function renderPagination() {
        let pagination = document.getElementById('pagination');
        if (!pagination) {
            pagination = document.createElement('div');
            pagination.id = 'pagination';
            pagination.style.margin = '20px 0';
            document.getElementById('manage-users').appendChild(pagination);
        }
        pagination.innerHTML = '';
        if (totalPages <= 1) return;
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = (i === currentPage) ? 'btn active' : 'btn';
            btn.addEventListener('click', () => loadUsers(i));
            pagination.appendChild(btn);
        }
    }

    // Add New User Button Handler
    document.getElementById('addUserBtn').addEventListener('click', function() {
        document.getElementById('editUsername').value = '';
        document.getElementById('editEmail').value = '';
        document.getElementById('editRole').value = 'user';
        document.getElementById('editStatus').value = 'active';
        editingUserId = null;
        document.getElementById('editUserModal').style.display = 'flex';
    });

    // Edit User Handler
    document.getElementById('usersTableBody').addEventListener('click', function(event) {
        if (event.target.classList.contains('editBtn')) {
            const id = event.target.getAttribute('data-id');
            editingUserId = id;
            const userToEdit = users.find(u => u.id == id);
            document.getElementById('editUsername').value = userToEdit.name || userToEdit.username || '';
            document.getElementById('editEmail').value = userToEdit.email;
            document.getElementById('editRole').value = userToEdit.role;
            document.getElementById('editStatus').value = userToEdit.status || 'active';
            document.getElementById('editUserModal').style.display = 'flex';
        }
    });

    // Save Changes Handler (edit or add)
    document.getElementById('editUserForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        const name = document.getElementById('editUsername').value;
        const email = document.getElementById('editEmail').value;
        const role = document.getElementById('editRole').value;
        const status = document.getElementById('editStatus').value;
        const password = document.getElementById('editPassword').value;
        try {
            if (editingUserId) {
                // Update user
                const updateData = { name, email, role, status };
                if (password) updateData.password = password;
                await authService.updateUser(editingUserId, updateData);
                alert('User updated successfully!');
            } else {
                // Create user
                if (!password) return alert('Password is required!');
                await authService.createUser({ name, email, password, role, status });
                alert('User created successfully!');
            }
            document.getElementById('editUserModal').style.display = 'none';
            await loadUsers();
        } catch (error) {
            alert('Error saving user: ' + error.message);
        }
    });

    // Cancel Edit
    document.getElementById('cancelEditBtn').addEventListener('click', function() {
        document.getElementById('editUserModal').style.display = 'none';
    });

    // Delete User Handler
    document.getElementById('usersTableBody').addEventListener('click', async function(event) {
        if (event.target.classList.contains('deleteBtn')) {
            const id = event.target.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this user?')) {
                try {
                    await authService.deleteUser(id);
                    alert('User deleted successfully!');
                    await loadUsers();
                } catch (error) {
                    alert('Error deleting user: ' + error.message);
                }
            }
        }
    });

    // Initial load
    await loadUsers();
});
