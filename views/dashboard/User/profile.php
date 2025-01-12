<?php
session_start();
require_once __DIR__ . '/../../../controllers/UserController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $current_password = $_POST['current_password'] ?? null;
        $new_password = $_POST['new_password'] ?? null;
        
        // Validate input
        if (empty($username) || empty($email)) {
            throw new Exception("Username and email are required");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        // Only update password if both current and new passwords are provided
        if ((!empty($current_password) && empty($new_password)) || (empty($current_password) && !empty($new_password))) {
            throw new Exception("Both current and new passwords are required to change password");
        }
        
        // Update profile
        updateProfile(
            $_SESSION['user_id'],
            $username,
            $email,
            $current_password ?: null,
            $new_password ?: null
        );

        $success = "Profile updated successfully!";
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
    <title>Profile Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 text-white min-h-screen">
    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 h-full w-64 bg-gray-900 text-white shadow-lg transform transition-transform duration-150 ease-in-out" id="sidebar">
        <div class="p-6">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-orange-500">MyBank</h2>
                <button class="md:hidden" onclick="toggleSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav>
                <ul class="space-y-4">
                    <li>
                        <a href="user.php" class="flex items-center space-x-2 text-gray-400 hover:text-orange-500 transition-colors">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="transactions.php" class="flex items-center space-x-2 text-gray-400 hover:text-orange-500 transition-colors">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Transactions</span>
                        </a>
                    </li>
                    <li>
                        <a href="profile.php" class="flex items-center space-x-2 text-orange-500">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="/views/auth/logout.php" class="flex items-center space-x-2 text-gray-400 hover:text-orange-500 transition-colors">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Mobile menu button -->
    <button class="fixed top-4 left-4 z-20 md:hidden bg-gray-800 p-2 rounded-lg" onclick="toggleSidebar()">
        <i class="fas fa-bars text-white"></i>
    </button>

    <!-- Main Content -->
    <div class="md:ml-64 p-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Profile Settings</h1>
                    <p class="text-gray-400">Manage your account information</p>
                </div>
                <div class="text-sm text-gray-400">
                    <?= date('l, F j, Y') ?>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-700 rounded-xl p-6 shadow-lg">
                <?php if ($error): ?>
                    <div class="bg-red-500/20 text-red-500 p-4 rounded-lg mb-6">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-500/20 text-green-500 p-4 rounded-lg mb-6">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-gray-300 mb-2">Username</label>
                        <input type="text" name="username" 
                               class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                               value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-2">Email Address</label>
                        <input type="email" name="email" 
                               class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                               value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                    </div>

                    <div class="border-t border-gray-700 my-6 pt-6">
                        <h3 class="text-xl font-semibold text-orange-500 mb-4">Change Password</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-300 mb-2">Current Password</label>
                                <input type="password" name="current_password" 
                                       class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-gray-300 mb-2">New Password</label>
                                <input type="password" name="new_password" 
                                       class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="update_profile" 
                            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                        Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }
    </script>
</body>
</html>