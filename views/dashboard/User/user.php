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
        
        // Send JSON response for AJAX requests
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $success_message]);
            exit;
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        
        // Send JSON response for AJAX requests
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error_message]);
            exit;
        }
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
                        <a href="user.php" class="flex items-center space-x-2 text-orange-500">
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
                        <a href="profile.php" class="flex items-center space-x-2 text-gray-400 hover:text-orange-500 transition-colors">
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
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold">Welcome Back, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></h1>
                <div class="text-sm text-gray-400">
                    <?= date('l, F j, Y') ?>
                </div>
            </div>
            
            <!-- Account Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($accounts as $account): ?>
                <div class="bg-gradient-to-br from-gray-800 to-gray-700 rounded-xl p-6 shadow-lg transform hover:scale-105 transition-transform duration-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-orange-500">
                            <?= ucfirst($account['account_type'] ?? 'Unknown') ?> Account
                        </h3>
                        <i class="fas <?= $account['account_type'] === 'savings' ? 'fa-piggy-bank' : 'fa-wallet' ?> text-orange-500"></i>
                    </div>
                    <p class="text-3xl font-bold mb-4" id="balance_<?= $account['account_type'] ?>">
                        $<?= number_format($account['balance'] ?? 0, 2) ?>
                    </p>
                    <p class="text-sm text-gray-400">
                        Account Number: <?= htmlspecialchars($account['account_number'] ?? 'N/A') ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Transaction Forms -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Transaction Form -->
                <div class="bg-gradient-to-br from-gray-800 to-gray-700 rounded-xl p-6 shadow-lg">
                    <h2 class="text-xl font-semibold mb-6 text-orange-500">Make a Transaction</h2>
                    <div id="transactionMessages"></div>
                    <form id="transactionForm" class="space-y-4">
                        <input type="hidden" name="transaction" value="1">
                        
                        <div>
                            <label class="block text-gray-300 mb-2">From Account</label>
                            <select name="account_number" required class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <?php foreach ($accounts as $acc): ?>
                                <option value="<?= htmlspecialchars($acc['account_number'] ?? '') ?>">
                                    <?= ucfirst($acc['account_type'] ?? 'Unknown') ?> - <?= htmlspecialchars($acc['account_number'] ?? '') ?> 
                                    ($<?= number_format($acc['balance'] ?? 0, 2) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-300 mb-2">Transaction Type</label>
                            <select name="type" required class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent" id="transactionType">
                                <option value="deposit">Deposit</option>
                                <option value="withdrawal">Withdrawal</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>

                        <div id="beneficiaryField" style="display: none;">
                            <label class="block text-gray-300 mb-2">Beneficiary Account Number</label>
                            <input type="text" name="beneficiary_account_number" 
                                   class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                                   placeholder="Enter beneficiary account number">
                        </div>

                        <div>
                            <label class="block text-gray-300 mb-2">Amount</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-gray-400">$</span>
                                <input type="number" name="amount" step="0.01" min="0.01" required 
                                       class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 pl-8 text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                                       placeholder="0.00">
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                            Submit Transaction
                        </button>
                    </form>
                </div>

                <!-- Quick Actions -->
                <div class="bg-gradient-to-br from-gray-800 to-gray-700 rounded-xl p-6 shadow-lg">
                    <h2 class="text-xl font-semibold mb-6 text-orange-500">Quick Actions</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <button class="flex flex-col items-center justify-center p-4 bg-gray-900 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-paper-plane text-2xl text-orange-500 mb-2"></i>
                            <span class="text-sm">Send Money</span>
                        </button>
                        <button class="flex flex-col items-center justify-center p-4 bg-gray-900 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-qrcode text-2xl text-orange-500 mb-2"></i>
                            <span class="text-sm">Scan QR</span>
                        </button>
                        <button class="flex flex-col items-center justify-center p-4 bg-gray-900 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-credit-card text-2xl text-orange-500 mb-2"></i>
                            <span class="text-sm">Cards</span>
                        </button>
                        <button class="flex flex-col items-center justify-center p-4 bg-gray-900 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-chart-line text-2xl text-orange-500 mb-2"></i>
                            <span class="text-sm">Analytics</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-700 rounded-xl p-6 shadow-lg">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-orange-500">Recent Transactions</h2>
                    <a href="transactions.php" class="text-gray-400 hover:text-orange-500 transition-colors flex items-center space-x-2">
                        <span>View All</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
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
                        <tbody id="transactionTableBody">
                            <!-- Transaction rows will be dynamically updated -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Call these functions when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateBalances();
            updateTransactions();
            
            // Set up periodic updates
            setInterval(updateBalances, 30000); // Every 30 seconds
            setInterval(updateTransactions, 30000);
        });

        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        // Function to show message
        function showMessage(message, isError = false) {
            const messageDiv = document.getElementById('transactionMessages');
            messageDiv.innerHTML = `
                <div class="p-4 mb-4 rounded-lg ${isError ? 'bg-red-500/20 text-red-500' : 'bg-green-500/20 text-green-500'}">
                    ${message}
                </div>
            `;
            setTimeout(() => {
                messageDiv.innerHTML = '';
            }, 5000);
        }

        // Function to update account balances
        function updateBalances() {
            fetch('../../controllers/get_balances.php')
                .then(response => response.json())
                .then(data => {
                    Object.keys(data).forEach(accountType => {
                        const balanceElement = document.getElementById(`balance_${accountType}`);
                        if (balanceElement) {
                            balanceElement.textContent = '$' + parseFloat(data[accountType]).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    });
                })
                .catch(error => console.error('Error updating balances:', error));
        }

        // Function to update transaction history
        function updateTransactions() {
            fetch('../../controllers/get_transactions.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Server error:', data);
                        throw new Error(data.error);
                    }
                    
                    const tbody = document.getElementById('transactionTableBody');
                    // Only show the 5 most recent transactions
                    const recentTransactions = data.slice(0, 5);
                    
                    tbody.innerHTML = recentTransactions.length ? '' : 
                        '<tr><td colspan="5" class="py-4 px-4 text-center text-gray-400">No transactions found</td></tr>';
                    
                    recentTransactions.forEach(trans => {
                        const isIncoming = trans.direction === 'in';
                        const row = `
                            <tr class="border-b border-gray-700 hover:bg-gray-800/50 transition-colors">
                                <td class="py-4 px-4">
                                    ${new Date(trans.created_at).toLocaleDateString('en-US', {
                                        month: 'short',
                                        day: 'numeric',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    })}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="flex items-center space-x-2">
                                        <i class="fas ${
                                            trans.type === 'deposit' ? 'fa-arrow-down text-green-400' :
                                            trans.type === 'withdrawal' ? 'fa-arrow-up text-red-400' :
                                            isIncoming ? 'fa-arrow-down text-green-400' :
                                            'fa-arrow-up text-red-400'
                                        }"></i>
                                        <span class="${
                                            trans.type === 'deposit' ? 'text-green-400' :
                                            trans.type === 'withdrawal' ? 'text-red-400' :
                                            isIncoming ? 'text-green-400' :
                                            'text-red-400'
                                        }">
                                            ${trans.type.charAt(0).toUpperCase() + trans.type.slice(1)}
                                            ${trans.type === 'transfer' ? (isIncoming ? ' (Received)' : ' (Sent)') : ''}
                                        </span>
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="${isIncoming ? 'text-green-400' : 'text-red-400'}">
                                        ${isIncoming ? '+' : '-'}$${parseFloat(trans.amount).toLocaleString('en-US', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        })}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-gray-300">
                                    ${isIncoming ? trans.source_account : trans.beneficiary_account || 'N/A'}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                               ${isIncoming ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500'}">
                                        Completed
                                    </span>
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error updating transactions:', error);
                    const tbody = document.getElementById('transactionTableBody');
                    tbody.innerHTML = '<tr><td colspan="5" class="py-4 px-4 text-center text-red-400">Error loading transactions</td></tr>';
                });
        }

        // Handle transaction form submission
        document.getElementById('transactionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message);
                    this.reset();
                    updateBalances();
                    updateTransactions();
                } else {
                    showMessage(data.message, true);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while processing your transaction.', true);
            });
        });

        // Handle transaction type change
        document.getElementById('transactionType').addEventListener('change', function() {
            const beneficiaryField = document.getElementById('beneficiaryField');
            beneficiaryField.style.display = this.value === 'transfer' ? 'block' : 'none';
        });
    </script>
</body>
</html>
