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

// Handle transaction submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction'])) {
    try {
        $account_number = $_POST['account_number'];
        $type = $_POST['type'];
        $amount = floatval($_POST['amount']);
        $beneficiary_account_number = $_POST['beneficiary_account_number'] ?? null;

        createTransaction($account_number, $type, $amount, $beneficiary_account_number);
        $success_message = "Transaction completed successfully!";
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get transactions for all user's accounts
$transactions = [];
foreach ($accounts as $acc) {
    $acc_transactions = getTransactionsByAccountNumber($acc['account_number']);
    $transactions = array_merge($transactions, $acc_transactions);
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
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Your Banking Dashboard</h1>
        
        <!-- Account Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <?php foreach ($accounts as $acc): ?>
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-2"><?= ucfirst($acc['account_type']) ?> Account</h2>
                <p class="text-gray-400 mb-2">Account Number: <?= $acc['account_number'] ?></p>
                <p class="text-2xl font-bold text-green-500">$<?= number_format($acc['balance'], 2) ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Transaction Form -->
        <div class="bg-gray-800 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">New Transaction</h2>
            
            <?php if (isset($error_message)): ?>
            <div class="bg-red-500 text-white p-4 rounded mb-4">
                <?= htmlspecialchars($error_message) ?>
            </div>
            <?php endif; ?>

            <?php if (isset($success_message)): ?>
            <div class="bg-green-500 text-white p-4 rounded mb-4">
                <?= htmlspecialchars($success_message) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="transaction" value="1">
                
                <div>
                    <label class="block text-sm font-medium mb-2">From Account</label>
                    <select name="account_number" required class="w-full bg-gray-700 rounded p-2">
                        <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['account_number'] ?>">
                            <?= ucfirst($acc['account_type']) ?> - <?= $acc['account_number'] ?> 
                            ($<?= number_format($acc['balance'], 2) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Transaction Type</label>
                    <select name="type" required class="w-full bg-gray-700 rounded p-2" id="transactionType">
                        <option value="deposit">Deposit</option>
                        <option value="withdrawal">Withdrawal</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>

                <div id="beneficiaryField" style="display: none;">
                    <label class="block text-sm font-medium mb-2">Beneficiary Account Number</label>
                    <input type="text" name="beneficiary_account_number" 
                           class="w-full bg-gray-700 rounded p-2" 
                           placeholder="Enter beneficiary account number">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Amount</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required 
                           class="w-full bg-gray-700 rounded p-2" 
                           placeholder="Enter amount">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Submit Transaction
                </button>
            </form>
        </div>

        <!-- Transaction History -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Transaction History</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b border-gray-700">
                            <th class="py-2">Date</th>
                            <th class="py-2">Type</th>
                            <th class="py-2">Amount</th>
                            <th class="py-2">From/To</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $trans): ?>
                        <tr class="border-b border-gray-700">
                            <td class="py-2"><?= date('M d, Y H:i', strtotime($trans['created_at'])) ?></td>
                            <td class="py-2"><?= ucfirst($trans['type']) ?></td>
                            <td class="py-2">
                                <span class="<?= $trans['type'] === 'deposit' ? 'text-green-500' : 'text-red-500' ?>">
                                    $<?= number_format($trans['amount'], 2) ?>
                                </span>
                            </td>
                            <td class="py-2">
                                <?php if ($trans['type'] === 'transfer'): ?>
                                    <?= $trans['beneficiary_account'] ?>
                                <?php else: ?>
                                    <?= $trans['source_account'] ?>
                                <?php endif; ?>
                            </td>
                            <td class="py-2">
                                <span class="px-2 py-1 rounded-full text-xs 
                                           <?= $trans['type'] === 'deposit' ? 'bg-green-500/20 text-green-500' : 
                                               ($trans['type'] === 'withdrawal' ? 'bg-red-500/20 text-red-500' : 
                                                'bg-blue-500/20 text-blue-500') ?>">
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
        const transactionType = document.getElementById('transactionType');
        const beneficiaryField = document.getElementById('beneficiaryField');

        transactionType.addEventListener('change', function() {
            beneficiaryField.style.display = this.value === 'transfer' ? 'block' : 'none';
        });
    </script>
</body>
</html>
