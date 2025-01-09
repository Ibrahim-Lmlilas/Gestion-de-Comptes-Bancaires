<?php 
session_start(); 
require_once __DIR__ . '/../../../controllers/UserController.php';
require_once __DIR__ . '/../../../controllers/TransactionController.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$users = getUsers();
$transactions = getAllTransactions();
$totalAmountTransactions = getTotalAmountTransactions();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .activity-item {
            transition: all 0.3s ease;
            animation: slideIn 0.5s backwards;
        }
        .activity-item:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.1);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-orange-500 via-purple-600 to-purple-800">
    <!-- Include Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="md:ml-64 p-8">
        <!-- Welcome Section -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-white">Welcome, Admin!</h2>
            <p class="text-orange-200">Here's what's happening with your bank today.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Users Card -->
            <div class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-200">Total Users</p>
                        <h3 class="text-3xl font-bold text-white"><?php echo count($users); ?></h3>
                    </div>
                    <div class="bg-orange-500/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Transactions Card -->
            <div class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-200">Total Transactions</p>
                        <h3 class="text-3xl font-bold text-white"><?php echo count($transactions); ?></h3>
                    </div>
                    <div class="bg-green-500/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Amount Card -->
            <div class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-200">Total Amount</p>
                        <h3 class="text-3xl font-bold text-white">$<?php echo number_format($totalAmountTransactions, 2); ?></h3>
                    </div>
                    <div class="bg-blue-500/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-6">Recent Activity</h3>
            <div class="space-y-4">
                <?php 
                $recentTransactions = array_slice($transactions, 0, 5);
                foreach ($recentTransactions as $transaction): 
                ?>
                    <div class="activity-item flex items-center justify-between border-b border-white/10 pb-4">
                        <div class="flex items-center space-x-4">
                            <div class="bg-<?php echo $transaction->transaction_type === 'deposit' ? 'green' : ($transaction->transaction_type === 'withdrawal' ? 'red' : 'blue'); ?>-500/20 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-<?php echo $transaction->transaction_type === 'deposit' ? 'green' : ($transaction->transaction_type === 'withdrawal' ? 'red' : 'blue'); ?>-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-white"><?php echo ucfirst($transaction->type); ?></p>
                                <p class="text-sm text-orange-200">
                                    <?php 
                                    echo $transaction->sender_name ? 
                                        "From {$transaction->sender_name}" : 
                                        ($transaction->receiver_name ? "To {$transaction->receiver_name}" : "System");
                                    ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-white">$<?php echo number_format($transaction->amount, 2); ?></p>
                            <p class="text-sm text-orange-200"><?php echo date('M d, Y', strtotime($transaction->created_at)); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
