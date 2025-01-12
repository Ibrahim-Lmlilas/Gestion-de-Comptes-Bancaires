<?php
require_once __DIR__ . '/../../../controllers/TransactionController.php';
require_once __DIR__ . '/../../../models/UserModel.php';
require_once __DIR__ . '/../../../models/Account.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /views/auth/login.php');
    exit();
}

$db = new Database('bank');
$pdo = $db->getConnection();

// Get user's accounts
$account = new Account($pdo);
$accounts = $account->getAccountsByUserId($_SESSION['user_id']);

// Get filter parameters
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$account_filter = $_GET['account'] ?? '';
$type_filter = $_GET['type'] ?? '';

// Get transactions for all user's accounts
$transactions = [];
foreach ($accounts as $acc) {
    if (!$account_filter || $account_filter === $acc['account_number']) {
        $acc_transactions = getTransactionsByAccountNumber($acc['account_number']);
        $transactions = array_merge($transactions, $acc_transactions);
    }
}

// Apply filters
if ($start_date) {
    $transactions = array_filter($transactions, function($trans) use ($start_date) {
        return strtotime($trans['created_at']) >= strtotime($start_date);
    });
}
if ($end_date) {
    $transactions = array_filter($transactions, function($trans) use ($end_date) {
        return strtotime($trans['created_at']) <= strtotime($end_date . ' 23:59:59');
    });
}
if ($type_filter) {
    $transactions = array_filter($transactions, function($trans) use ($type_filter) {
        return $trans['type'] === $type_filter;
    });
}

// Sort transactions by date (newest first)
usort($transactions, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
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
                        <a href="transactions.php" class="flex items-center space-x-2 text-orange-500">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Transactions</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center space-x-2 text-gray-400 hover:text-orange-500 transition-colors">
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
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Transaction History</h1>
                    <p class="text-gray-400">View and filter all your transactions</p>
                </div>
                <div class="text-sm text-gray-400">
                    <?= date('l, F j, Y') ?>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-700 rounded-xl p-6 mb-8">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-gray-300 mb-2">Start Date</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"
                               class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-white">
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2">End Date</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"
                               class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-white">
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2">Account</label>
                        <select name="account" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-white">
                            <option value="">All Accounts</option>
                            <?php foreach ($accounts as $acc): ?>
                            <option value="<?= htmlspecialchars($acc['account_number']) ?>" 
                                    <?= $account_filter === $acc['account_number'] ? 'selected' : '' ?>>
                                <?= ucfirst($acc['account_type']) ?> - <?= htmlspecialchars($acc['account_number']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2">Type</label>
                        <select name="type" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-white">
                            <option value="">All Types</option>
                            <option value="deposit" <?= $type_filter === 'deposit' ? 'selected' : '' ?>>Deposit</option>
                            <option value="withdrawal" <?= $type_filter === 'withdrawal' ? 'selected' : '' ?>>Withdrawal</option>
                            <option value="transfer" <?= $type_filter === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-700 rounded-xl p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-3 px-4 text-gray-400 font-medium">Date</th>
                                <th class="text-left py-3 px-4 text-gray-400 font-medium">Type</th>
                                <th class="text-left py-3 px-4 text-gray-400 font-medium">Amount</th>
                                <th class="text-left py-3 px-4 text-gray-400 font-medium">Account</th>
                                <th class="text-left py-3 px-4 text-gray-400 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-center text-gray-400">No transactions found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($transactions as $trans): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-800/50 transition-colors">
                                <td class="py-4 px-4">
                                    <?= date('M d, Y H:i', strtotime($trans['created_at'])) ?>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="flex items-center space-x-2">
                                        <i class="fas <?= 
                                            $trans['type'] === 'deposit' ? 'fa-arrow-down text-green-400' :
                                            ($trans['type'] === 'withdrawal' ? 'fa-arrow-up text-red-400' : 
                                             'fa-exchange-alt text-blue-400') 
                                        ?>"></i>
                                        <span class="<?= 
                                            $trans['type'] === 'deposit' ? 'text-green-400' :
                                            ($trans['type'] === 'withdrawal' ? 'text-red-400' : 
                                             'text-blue-400') 
                                        ?>">
                                            <?= ucfirst($trans['type']) ?>
                                        </span>
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="<?= 
                                        $trans['type'] === 'deposit' ? 'text-green-400' :
                                        ($trans['type'] === 'withdrawal' ? 'text-red-400' : 
                                         'text-blue-400') 
                                    ?>">
                                        <?= $trans['type'] === 'deposit' ? '+' : '-' ?>
                                        $<?= number_format($trans['amount'], 2) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-gray-300">
                                    <?php if ($trans['type'] === 'transfer'): ?>
                                        To: <?= htmlspecialchars($trans['beneficiary_account'] ?? 'N/A') ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($trans['source_account'] ?? 'N/A') ?>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                               <?= $trans['type'] === 'deposit' ? 'bg-green-500/20 text-green-500' : 
                                                   ($trans['type'] === 'withdrawal' ? 'bg-red-500/20 text-red-500' : 
                                                    'bg-blue-500/20 text-blue-500') ?>">
                                        Completed
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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