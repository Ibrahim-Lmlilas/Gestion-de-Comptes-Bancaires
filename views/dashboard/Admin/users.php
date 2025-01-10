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
        return stripos($user['username'], $searchQuery) !== false || 
               stripos($user['email'], $searchQuery) !== false ||
               stripos($user['role'], $searchQuery) !== false;
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
        
        updateUser($userId, [
            'username' => $username,
            'email' => $email,
            'role' => $role
        ]);
        
        $success = "User updated successfully!";
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
            <form id="searchForm" class="mb-6">
                <div class="flex gap-4">
                    <input type="text" 
                           id="searchInput"
                           name="search" 
                           placeholder="Rechercher par nom, email ou rôle..." 
                           value="<?php echo htmlspecialchars($searchQuery); ?>"
                           class="flex-1 px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/50">
                    <button type="button" 
                            onclick="resetSearch()"
                            class="px-6 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors">
                        Réinitialiser
                    </button>
                </div>
            </form>
            <div id="usersTableContainer" class="overflow-x-auto">
                <!-- Table content will be updated dynamically -->
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-gradient-to-br from-rose-950 to-rose-950 rounded-xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-white mb-4">Edit User</h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div>
                        <label class="block text-white mb-2">Username</label>
                        <input type="text" name="username" id="edit_username" 
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                    </div>
                    <div>
                        <label class="block text-white mb-2">Email</label>
                        <input type="email" name="email" id="edit_email" 
                               class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                    </div>
                    <div>
                        <label class="block text-white mb-2">Role</label>
                        <select name="role" id="edit_role" 
                                class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
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

    <script>
        function openEditModal(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function openDeleteModal(userId, username) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('delete_username').textContent = username;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const usersTableContainer = document.getElementById('usersTableContainer');

        // Live search with debounce
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });

        function performSearch(query) {
            fetch(`search_users.php?search=${encodeURIComponent(query)}`)
                .then(response => response.text())
                .then(html => {
                    usersTableContainer.innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }

        function resetSearch() {
            searchInput.value = '';
            performSearch('');
        }

        // Initial load
        performSearch('');
    </script>
</body>
</html>
