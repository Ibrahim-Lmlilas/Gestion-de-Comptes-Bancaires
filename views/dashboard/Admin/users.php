<?php
session_start();
require_once __DIR__ . '/../../../controllers/UserController.php';

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$error = '';
$success = '';
$users = getUsers();

// Add search functionality
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
if ($searchQuery) {
    $users = array_filter($users, function($user) use ($searchQuery) {
        return stripos($user['username'] ?? '', $searchQuery) !== false || 
               stripos($user['email'] ?? '', $searchQuery) !== false ||
               stripos($user['role'] ?? '', $searchQuery) !== false;
    });
}

// Handle Delete User
if (isset($_POST['delete_user'])) {
    try {
        $userId = $_POST['user_id'];
        deleteUser($userId);
        $success = "User deleted successfully!";
        // Refresh users list
        $users = getUsers();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle Edit User
if (isset($_POST['edit_user'])) {
    try {
        $userId = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        
        // Get account statuses from form
        $accountStatuses = isset($_POST['account_status']) ? $_POST['account_status'] : [];
        
        updateUser($userId, [
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'account_status' => $accountStatuses
        ]);
        
        $success = "User updated successfully!";
        // Refresh users list
        $users = getUsers();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle Add User
if (isset($_POST['add_user'])) {
    try {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $currentBalance = $_POST['current_balance'];
        $savingsBalance = $_POST['savings_balance'];
        $role = $_POST['role'];
        
        addUser($username, $email, $password, $currentBalance, $savingsBalance, $role);
        
        $success = "User added successfully!";
        // Refresh users list
        $users = getUsers();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .user-row {
            transition: all 0.3s ease;
        }
        .user-row:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(10px);
        }
        .toggle-checkbox:checked {
            right: 0;
            border-color: #10B981;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #10B981;
        }
        .toggle-label {
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-rose-950 via-yellow-950 to-stone-600">
    <!-- Include Sidebar -->
    <?php include 'components/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="md:ml-64 p-8">
        <!-- Header -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-white">User Management</h2>
            <p class="text-orange-200">Manage system users and their permissions.</p>
        </div>

        <div class="flex justify-end mb-6">
            <button onclick="openAddClientModal()" 
                    class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Client
            </button>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/20 text-red-300 p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-500/20 text-green-300 p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- Users Table -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6">
            <!-- Search Bar -->
            <form id="searchForm" class="mb-6">
                <div class="flex gap-4">
                    <input type="text" 
                           id="searchInput"
                           name="search" 
                           placeholder="Search by name, email or role..." 
                           value="<?php echo htmlspecialchars($searchQuery); ?>"
                           class="flex-1 px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/50">
                    <button type="button" 
                            onclick="resetSearch()"
                            class="px-6 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors">
                        Reset
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-white">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-200 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-200 uppercase tracking-wider">Account Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-200 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-200 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-200 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-200 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <img class="h-10 w-10 rounded-full" 
                                                 src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username'] ?? ''); ?>" 
                                                 alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium">
                                                <?php echo htmlspecialchars($user['username'] ?? ''); ?>
                                            </div>
                                            <div class="text-sm text-orange-300">
                                                <?php 
                                                if (!empty($user['accounts'])) {
                                                    foreach ($user['accounts'] as $account) {
                                                        if (isset($account['type'])) {
                                                            echo '<span class="mr-2">(' . ($account['type'] === 'current' ? 'C' : 'S') . ')</span>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <div class="text-sm text-orange-300"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">
                                        <?php 
                                        if (!empty($user['accounts'])) {
                                            foreach ($user['accounts'] as $account) {
                                                if (isset($account['account_number'])) {
                                                    echo '<div class="mb-1">' . htmlspecialchars($account['account_number']) . '</div>';
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">
                                        <?php 
                                        if (!empty($user['accounts'])) {
                                            foreach ($user['accounts'] as $account) {
                                                if (isset($account['balance'])) {
                                                    echo '<div class="mb-1">' . number_format($account['balance'], 2) . '</div>';
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo ($user['role'] ?? '') === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst($user['role'] ?? 'user'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (!empty($user['accounts'])): ?>
                                        <?php foreach ($user['accounts'] as $account): ?>
                                            <?php if (isset($account['type']) && isset($account['status'])): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full mb-1 <?php echo $account['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                    <?php echo ucfirst($account['type']) . ': ' . $account['status']; ?>
                                                </span>
                                                <br>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)" 
                                            class="text-orange-400 hover:text-orange-300 mr-3">Edit</button>
                                    <button onclick="openDeleteModal(<?php echo htmlspecialchars(json_encode($user)); ?>)" 
                                            class="text-red-400 hover:text-red-300">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gradient-to-br from-rose-950 to-rose-950 rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-white mb-4">Edit User</h3>
                <form method="POST" action="" id="editUserForm" class="space-y-4">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div>
                        <label class="block text-white mb-2">Username</label>
                        <input type="text" name="username" id="edit_username" required
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                    </div>
                    <div>
                        <label class="block text-white mb-2">Email</label>
                        <input type="email" name="email" id="edit_email" required
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                    </div>
                    <div>
                        <label class="block text-white mb-2">Role</label>
                        <select name="role" id="edit_role" required
                                class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div id="account_statuses">
                        <!-- Account status toggles will be added here dynamically -->
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeEditModal()"
                                class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" name="edit_user"
                                class="px-4 py-2 bg-green-500 hover:bg-green-700 text-white rounded-lg transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .toggle-checkbox:checked {
            right: 0;
            border-color: #10B981;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #10B981;
        }
        .toggle-label {
            transition: all 0.3s ease-in-out;
        }
    </style>

    <script>
        function openEditModal(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;

            // Clear previous account statuses
            const accountStatusesDiv = document.getElementById('account_statuses');
            accountStatusesDiv.innerHTML = '';

            // Add status toggles for each account
            if (user.accounts && user.accounts.length > 0) {
                user.accounts.forEach((account, index) => {
                    const statusToggle = `
                        <div class="mb-4">
                            <label class="block text-white mb-2">${account.type.charAt(0).toUpperCase() + account.type.slice(1)} Account Status</label>
                            <div class="flex items-center justify-between bg-white/10 p-4 rounded-lg">
                                <span class="text-white">${account.type.charAt(0).toUpperCase() + account.type.slice(1)} Account</span>
                                <div class="relative inline-block w-12 mr-2 align-middle select-none">
                                    <input type="hidden" name="account_status[${account.type}]" value="inactive">
                                    <input type="checkbox" name="account_status[${account.type}]" 
                                           value="active"
                                           id="status_${account.type}" 
                                           class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                           ${account.status === 'Active' ? 'checked' : ''} />
                                    <label for="status_${account.type}" 
                                           class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                                <span class="text-white ml-2" id="status_text_${account.type}">${account.status}</span>
                            </div>
                        </div>
                    `;
                    accountStatusesDiv.insertAdjacentHTML('beforeend', statusToggle);

                    // Add event listener for the new toggle
                    document.getElementById(`status_${account.type}`).addEventListener('change', function() {
                        document.getElementById(`status_text_${account.type}`).textContent = 
                            this.checked ? 'Active' : 'Inactive';
                    });
                });
            }

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function openDeleteModal(user) {
            document.getElementById('delete_user_id').value = user.id;
            document.getElementById('delete_username').textContent = user.username;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function openAddClientModal() {
            document.getElementById('addClientModal').classList.remove('hidden');
        }

        function closeAddClientModal() {
            document.getElementById('addClientModal').classList.add('hidden');
        }

        function resetSearch() {
            document.getElementById('searchInput').value = '';
            document.getElementById('searchForm').submit();
        }

        // Auto-submit search form on input change
        document.getElementById('searchInput').addEventListener('input', function() {
            document.getElementById('searchForm').submit();
        });
    </script>

    <!-- Delete User Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gradient-to-br from-rose-950 to-rose-950 rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-white mb-4">Delete User</h3>
                <p class="text-white mb-6">Are you sure you want to delete <span id="delete_username" class="font-semibold"></span>?</p>
                <form method="POST" class="flex justify-end space-x-3">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <button type="button" onclick="closeDeleteModal()"
                            class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" name="delete_user"
                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
                        Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Client Modal -->
    <div id="addClientModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gradient-to-br from-purple-600 to-purple-800 rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-white mb-4">Add New Client</h3>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-white mb-2">Username</label>
                        <input type="text" name="username" required
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                    </div>
                    <div>
                        <label class="block text-white mb-2">Email</label>
                        <input type="email" name="email" required
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                    </div>
                    <div>
                        <label class="block text-white mb-2">Password</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                    </div>
                    <div>
                        <label class="block text-white mb-2">Current Account Initial Balance</label>
                        <input type="number" name="current_balance" required step="0.01" min="0"
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                    </div>
                    <div>
                        <label class="block text-white mb-2">Savings Account Initial Balance</label>
                        <input type="number" name="savings_balance" required step="0.01" min="0"
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                    </div>
                    <div>
                        <label class="block text-white mb-2">Role</label>
                        <select name="role" required
                                class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeAddClientModal()"
                                class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" name="add_user"
                                class="px-4 py-2 bg-green-500 hover:bg-green-700 text-white rounded-lg transition-colors">
                            Add Client
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
