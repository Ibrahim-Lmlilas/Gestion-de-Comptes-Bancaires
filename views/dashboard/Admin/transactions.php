<?php 
session_start(); 
require_once __DIR__ . '/../../../controllers/TransactionController.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$transactions = getAllTransactions();
$totalAmount = getTotalAmountTransactions();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Transactions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .transaction-row {
            transition: all 0.3s ease;
        }
        .transaction-row:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(10px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-rose-950 via-yellow-950 to-stone-600">
    <!-- Sidebar (reuse from admin.php) -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="md:ml-64 p-8">
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-white mb-6">Transaction History</h2>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white/10 backdrop-blur-md rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-2">Total Transactions</h3>
                    <p class="text-3xl text-orange-300"><?php echo count($transactions); ?></p>
                </div>
                <div class="bg-white/10 backdrop-blur-md rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-2">Total Amount</h3>
                    <p class="text-3xl text-orange-300">$<?php echo number_format($totalAmount, 2); ?></p>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-white">
                    <thead>
                        <tr class="text-left border-b border-white/10">
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">From</th>
                            <th class="py-3 px-4">To</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr class="transaction-row border-b border-white/10">
                                <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($transaction->created_at)); ?></td>
                                <td class="py-3 px-4">
                                    <?php 
                                    $typeClass = match($transaction->type) {
                                        'deposit' => 'text-green-400',
                                        'withdrawal' => 'text-red-400',
                                        'transfer' => 'text-blue-400',
                                        default => 'text-white'
                                    };
                                    ?>
                                    <span class="<?php echo $typeClass; ?>"><?php echo ucfirst($transaction->type); ?></span>
                                </td>
                                <td class="py-3 px-4"><?php echo $transaction->sender_name ?? 'N/A'; ?></td>
                                <td class="py-3 px-4"><?php echo $transaction->receiver_name ?? 'N/A'; ?></td>
                                <td class="py-3 px-4">$<?php echo number_format($transaction->amount, 2); ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-sm bg-green-500/20 text-green-400">
                                        Completed
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Add any JavaScript for interactivity here
        document.addEventListener('DOMContentLoaded', () => {
            // Add animation to transaction rows
            const rows = document.querySelectorAll('.transaction-row');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html> 