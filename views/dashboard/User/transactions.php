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
    <title>Transaction History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .transaction-row {
            transition: all 0.3s ease;
            animation: slideIn 0.5s backwards;
        }
        .transaction-row:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(10px);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 via-indigo-600 to-indigo-800">
    <!-- Include sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="md:ml-64 p-8">
        <!-- Header -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-white">Transaction History</h2>
            <p class="text-blue-200">View all your past transactions.</p>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-white">
                    <thead>
                        <tr class="text-left border-b border-white/10">
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($transactions): ?>
                            <?php foreach ($transactions as $index => $transaction): ?>
                                <tr class="transaction-row border-b border-white/10" style="animation-delay: <?php echo $index * 0.1; ?>s">
                                    <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($transaction->created_at)); ?></td>
                                    <td class="py-3 px-4">
                                        <?php 
                                        $typeClass = match($transaction->transaction_type) {
                                            'deposit' => 'text-green-400',
                                            'withdrawal' => 'text-red-400',
                                            'transfer' => 'text-blue-400',
                                            default => 'text-white'
                                        };
                                        ?>
                                        <span class="<?php echo $typeClass; ?>">
                                            <?php echo ucfirst($transaction->transaction_type); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4"><?php echo $transaction->description ?? 'Transaction'; ?></td>
                                    <td class="py-3 px-4">
                                        <span class="<?php echo $transaction->transaction_type === 'deposit' ? 'text-green-400' : 'text-red-400'; ?>">
                                            <?php echo $transaction->transaction_type === 'deposit' ? '+' : '-'; ?>
                                            $<?php echo number_format($transaction->amount, 2); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-sm bg-green-500/20 text-green-400">
                                            Completed
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-center text-blue-200">No transactions found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 