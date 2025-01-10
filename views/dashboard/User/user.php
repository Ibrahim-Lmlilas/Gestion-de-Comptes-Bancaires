<?php
session_start();
require_once __DIR__ . '/../../../controllers/TransactionController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Get user's transactions
$transactions = getTransaction($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center;
            animation: cardAppear 0.6s backwards;
        }
        .card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .transaction-row {
            transition: all 0.3s ease;
        }
        .transaction-row:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(10px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 via-indigo-600 to-indigo-800">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-white/10 backdrop-blur-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-[9999]">
        <div class="flex items-center justify-center h-20 border-b border-white/10">
            <h1 class="text-2xl font-bold text-white">My Banking</h1>
        </div>
        <nav class="mt-6">
            <div class="px-6 py-4">
                <span class="text-blue-200 text-sm">Menu</span>
            </div>
            <a href="user.php" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
                Dashboard
            </a>
            <a href="transfer.php" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
                Transfer Money
            </a>
            <a href="transactions.php" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
                Transactions
            </a>
            <a href="profile.php" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
                Profile
            </a>
            <a href="../../auth/logout.php" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
                Logout
            </a>
        </nav>
    </div>

    <!-- Mobile Menu Button -->
    <button id="mobile-menu-button" class="fixed top-4 left-4 z-[9999] p-2 rounded-lg bg-white/10 backdrop-blur-lg md:hidden">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path id="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Main Content -->
    <div class="md:ml-64 p-8">
        <!-- Welcome Section -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-white">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p class="text-blue-200">Here's your account overview.</p>
        </div>

        <!-- Account Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Current Account Card -->
            <div class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">Current Account</h3>
                    <div class="bg-blue-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-white mb-2">$<?php echo number_format(0, 2); // Replace with actual balance ?></p>
                <p class="text-blue-200">Available Balance</p>
            </div>

            <!-- Quick Actions Card -->
            <div class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="transfer.php" class="flex items-center justify-center p-3 bg-blue-500/20 rounded-lg text-white hover:bg-blue-500/30 transition-colors">
                        <span>Transfer Money</span>
                    </a>
                    <a href="transactions.php" class="flex items-center justify-center p-3 bg-blue-500/20 rounded-lg text-white hover:bg-blue-500/30 transition-colors">
                        <span>View Transactions</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Recent Transactions</h3>
                <a href="transactions.php" class="text-blue-300 hover:text-blue-200">View All</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-white">
                    <thead>
                        <tr class="text-left border-b border-white/10">
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($transactions): ?>
                            <?php foreach (array_slice($transactions, 0, 5) as $transaction): ?>
                                <tr class="transaction-row border-b border-white/10">
                                    <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($transaction->created_at)); ?></td>
                                    <td class="py-3 px-4">
                                        <span class="<?php echo $transaction->type === 'deposit' ? 'text-green-400' : 'text-red-400'; ?>">
                                            <?php echo ucfirst($transaction->type); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4"><?php echo $transaction->description ?? 'Transaction'; ?></td>
                                    <td class="py-3 px-4"><?php echo $transaction->type === 'deposit' ? '+' : '-'; ?>$<?php echo number_format($transaction->amount, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-4 px-4 text-center text-blue-200">No transactions found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const sidebar = document.getElementById('sidebar');
            const menuIcon = document.getElementById('menu-icon');
            
            mobileMenuButton.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                if (sidebar.classList.contains('-translate-x-full')) {
                    menuIcon.setAttribute('d', 'M4 6h16M4 12h16M4 18h16');
                } else {
                    menuIcon.setAttribute('d', 'M6 18L18 6M6 6l12 12');
                }
            });

            document.addEventListener('click', (e) => {
                if (window.innerWidth < 768) {
                    if (!sidebar.contains(e.target) && !mobileMenuButton.contains(e.target)) {
                        sidebar.classList.add('-translate-x-full');
                        menuIcon.setAttribute('d', 'M4 6h16M4 12h16M4 18h16');
                    }
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('-translate-x-full');
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            });
        });
    </script>
</body>
</html>
