<?php
session_start();
require_once __DIR__ . '/../../../controllers/vusers.php';

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
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        
        // Add your profile update logic here
        // You'll need to create a method in your User model to handle this

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
    <style>
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .input-field {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(59, 130, 246, 0.1);
            border: 2px solid rgba(96, 165, 250, 0.2);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .input-field:focus {
            background: rgba(59, 130, 246, 0.2);
            border-color: #3B82F6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .input-label {
            position: absolute;
            left: 1rem;
            top: 1rem;
            padding: 0 0.25rem;
            color: #93C5FD;
            font-size: 1rem;
            transition: all 0.3s;
            pointer-events: none;
        }
        .input-field:focus + .input-label,
        .input-field:not(:placeholder-shown) + .input-label {
            top: -0.5rem;
            left: 0.8rem;
            font-size: 0.875rem;
            background: linear-gradient(to bottom right, #3B82F6, #1D4ED8);
            padding: 0 0.5rem;
            color: white;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 via-indigo-600 to-indigo-800">
    <!-- Include sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="md:ml-64 p-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 mb-8">
                <h2 class="text-2xl font-bold text-white">Profile Settings</h2>
                <p class="text-blue-200">Manage your account information.</p>
            </div>

            <!-- Profile Form -->
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-6">
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

                <form method="POST" class="space-y-6">
                    <div class="input-group">
                        <input type="text" name="username" class="input-field" 
                               placeholder=" " value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                        <label class="input-label">Username</label>
                    </div>

                    <div class="input-group">
                        <input type="email" name="email" class="input-field" 
                               placeholder=" " value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                        <label class="input-label">Email Address</label>
                    </div>

                    <div class="border-t border-white/10 my-6 pt-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Change Password</h3>
                        
                        <div class="input-group">
                            <input type="password" name="current_password" class="input-field" placeholder=" ">
                            <label class="input-label">Current Password</label>
                        </div>

                        <div class="input-group">
                            <input type="password" name="new_password" class="input-field" placeholder=" ">
                            <label class="input-label">New Password</label>
                        </div>
                    </div>

                    <button type="submit" name="update_profile" 
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                        Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 