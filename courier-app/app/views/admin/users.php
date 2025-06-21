<?php
$pageTitle = 'Manage Users';
$currentPage = 'users';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Manage Users</h1>
                <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                    Add User
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm border-b">
        <div class="px-6 py-4">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-64">
                    <input type="text" id="searchInput" placeholder="Search by name, email, or phone..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <select id="roleFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <button onclick="applyFilters()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="p-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contact
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Joined
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Last Login
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="usersTable">
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" value="<?= $user['id'] ?>" class="user-checkbox rounded">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        <?= strtoupper(substr($user['name'], 0, 2)) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($user['name']) ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ID: <?= $user['id'] ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?= htmlspecialchars($user['email']) ?>
                                        </div>
                                        <?php if ($user['phone']): ?>
                                            <div class="text-sm text-gray-500">
                                                <?= htmlspecialchars($user['phone']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select onchange="updateRole(<?= $user['id'] ?>, this.value)" 
                                                class="text-xs font-semibold rounded-full px-2 py-1 border-0
                                                <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button onclick="toggleStatus(<?= $user['id'] ?>, <?= $user['is_active'] ? 0 : 1 ?>)"
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                <?= $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="viewUser(<?= $user['id'] ?>)" 
                                                    class="text-blue-600 hover:text-blue-900">View</button>
                                            <button onclick="editUser(<?= $user['id'] ?>)" 
                                                    class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <button onclick="deleteUser(<?= $user['id'] ?>)" 
                                                        class="text-red-600 hover:text-red-900">Delete</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if (!empty($pagination)): ?>
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <?= $pagination['start'] ?> to <?= $pagination['end'] ?> of <?= $pagination['total'] ?> results
                </div>
                <div class="flex space-x-2">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?>" 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                        <a href="?page=<?= $i ?>" 
                           class="px-3 py-2 border rounded-lg text-sm <?= $i === $pagination['current_page'] ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?>" 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create/Edit User Modal -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add User</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="userForm" class="p-6 space-y-4">
                <input type="hidden" id="userId" name="id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="tel" name="phone" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1" id="passwordHelp">Leave blank to keep current password</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" id="isActive" class="rounded">
                    <label for="isActive" class="ml-2 text-sm text-gray-700">Active</label>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div id="viewUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">User Details</h3>
                    <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="p-6" id="userDetails">
                <!-- User details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passwordHelp').style.display = 'none';
    document.getElementById('isActive').checked = true;
    document.getElementById('userModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
}

function closeViewModal() {
    document.getElementById('viewUserModal').classList.add('hidden');
}

function editUser(id) {
    fetch(`/api/users/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('userId').value = data.id;
            document.getElementById('password').required = false;
            document.getElementById('passwordHelp').style.display = 'block';
            
            // Populate form fields
            document.querySelector('[name="name"]').value = data.name;
            document.querySelector('[name="email"]').value = data.email;
            document.querySelector('[name="phone"]').value = data.phone || '';
            document.querySelector('[name="role"]').value = data.role;
            document.querySelector('[name="is_active"]').checked = data.is_active == 1;
            
            document.getElementById('userModal').classList.remove('hidden');
        });
}

function viewUser(id) {
    fetch(`/api/users/${id}`)
        .then(response => response.json())
        .then(data => {
            const detailsHtml = `
                <div class="space-y-6">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-xl font-medium text-gray-700">
                                ${data.name.substr(0, 2).toUpperCase()}
                            </span>
                        </div>
                        <div>
                            <h4 class="text-xl font-medium text-gray-900">${data.name}</h4>
                            <p class="text-sm text-gray-500">ID: ${data.id}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h5 class="text-sm font-medium text-gray-900 mb-2">Contact Information</h5>
                            <div class="space-y-2 text-sm">
                                <div><strong>Email:</strong> ${data.email}</div>
                                <div><strong>Phone:</strong> ${data.phone || 'Not provided'}</div>
                            </div>
                        </div>
                        
                        <div>
                            <h5 class="text-sm font-medium text-gray-900 mb-2">Account Details</h5>
                            <div class="space-y-2 text-sm">
                                <div><strong>Role:</strong> <span class="capitalize">${data.role}</span></div>
                                <div><strong>Status:</strong> 
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${data.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                        ${data.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </div>
                                <div><strong>Joined:</strong> ${new Date(data.created_at).toLocaleDateString()}</div>
                                <div><strong>Last Login:</strong> ${data.last_login ? new Date(data.last_login).toLocaleString() : 'Never'}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h5 class="text-sm font-medium text-gray-900 mb-2">Recent Activity</h5>
                        <div id="userActivity" class="text-sm text-gray-500">
                            Loading activity...
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('userDetails').innerHTML = detailsHtml;
            document.getElementById('viewUserModal').classList.remove('hidden');
            
            // Load user activity
            loadUserActivity(id);
        });
}

function loadUserActivity(userId) {
    fetch(`/api/users/${userId}/activity`)
        .then(response => response.json())
        .then(data => {
            const activityHtml = data.length > 0 
                ? data.map(activity => `
                    <div class="border-l-2 border-gray-200 pl-4 pb-4">
                        <div class="font-medium">${activity.description}</div>
                        <div class="text-xs text-gray-400">${new Date(activity.created_at).toLocaleString()}</div>
                    </div>
                `).join('')
                : '<div>No recent activity found.</div>';
            
            document.getElementById('userActivity').innerHTML = activityHtml;
        });
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        fetch(`/api/users/${id}`, { method: 'DELETE' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete user: ' + (data.message || 'Unknown error'));
                }
            });
    }
}

function updateRole(id, role) {
    fetch(`/api/users/${id}/role`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ role })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to update role');
            location.reload();
        }
    });
}

function toggleStatus(id, status) {
    fetch(`/api/users/${id}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to update status');
        }
    });
}

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const role = document.getElementById('roleFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (role) params.append('role', role);
    if (status) params.append('status', status);
    
    window.location.href = '?' + params.toString();
}

// Form submission
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Handle checkbox
    data.is_active = formData.has('is_active') ? 1 : 0;
    
    const url = data.id ? `/api/users/${data.id}` : '/api/users';
    const method = data.id ? 'PUT' : 'POST';
    
    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            location.reload();
        } else {
            alert('Failed to save user: ' + (data.message || 'Unknown error'));
        }
    });
});

// Select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>
