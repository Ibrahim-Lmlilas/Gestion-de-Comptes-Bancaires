<?php
session_start();
require_once __DIR__ . '/../../../controllers/TransactionController.php';
require_once __DIR__ . '/../../../models/UserModel.php';
require_once __DIR__ . '/../../../models/Account.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$error = '';
$success = '';

// Get user's accounts
$db = new Database('bank');
$pdo = $db->getConnection();
$account = new Account($pdo);
$userAccounts = $account->getAccountsByUserId($_SESSION['user_id']);

// Separate accounts by type
$currentAccount = null;
$savingsAccount = null;
foreach ($userAccounts as $acc) {
    if ($acc['account_type'] === 'current') {
        $currentAccount = $acc;
    } elseif ($acc['account_type'] === 'savings') {
        $savingsAccount = $acc;
    }
}

// Handle transfer form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer'])) {
    try {
        $amount = floatval($_POST['amount']);
        $source_account = $_POST['source_account'];
        $beneficiary_account = $_POST['beneficiary_account'];
        
        if ($amount <= 0) {
            throw new Exception("Amount must be greater than zero");
        }

        // Create the transaction
        createTransaction($source_account, 'transfer', $amount, $beneficiary_account);
        $success = "Transfer completed successfully!";
        
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
    <title>Transfer Money</title>
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
                <h2 class="text-2xl font-bold text-white">Transfer Money</h2>
                <p class="text-blue-200">Send money to other accounts securely.</p>
            </div>

            <!-- Account Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <?php if ($currentAccount): ?>
                <div class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-2">Current Account</h3>
                    <p class="text-sm text-blue-200 mb-1">Account: <?= htmlspecialchars($currentAccount['account_number']) ?></p>
                    <p class="text-2xl font-bold text-white">$<?= number_format($currentAccount['balance'], 2) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($savingsAccount): ?>
                <div class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-2">Savings Account</h3>
                    <p class="text-sm text-blue-200 mb-1">Account: <?= htmlspecialchars($savingsAccount['account_number']) ?></p>
                    <p class="text-2xl font-bold text-white">$<?= number_format($savingsAccount['balance'], 2) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Transfer Form -->
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
                        <select name="source_account" class="input-field" required>
                            <?php if ($currentAccount): ?>
                            <option value="<?= htmlspecialchars($currentAccount['account_number']) ?>">
                                Current Account - $<?= number_format($currentAccount['balance'], 2) ?>
                            </option>
                            <?php endif; ?>
                            <?php if ($savingsAccount): ?>
                            <option value="<?= htmlspecialchars($savingsAccount['account_number']) ?>">
                                Savings Account - $<?= number_format($savingsAccount['balance'], 2) ?>
                            </option>
                            <?php endif; ?>
                        </select>
                        <label class="input-label">From Account</label>
                    </div>

                    <div class="input-group">
                        <select name="transfer_type" id="transfer_type" class="input-field" required>
                            <option value="external">External Account</option>
                            <?php if ($currentAccount && $savingsAccount): ?>
                            <option value="internal">My Savings Account</option>
                            <?php endif; ?>
                        </select>
                        <label class="input-label">Transfer Type</label>
                    </div>

                    <div class="input-group" id="external_account_group">
                        <input type="text" name="beneficiary_account" id="beneficiary_account"
                               class="input-field" placeholder=" ">
                        <label class="input-label">Beneficiary Account Number</label>
                    </div>

                    <div class="input-group">
                        <input type="number" name="amount" step="0.01" min="0.01" 
                               class="input-field" placeholder=" " required>
                        <label class="input-label">Amount ($)</label>
                    </div>

                    <button type="submit" name="transfer" 
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                        Send Money
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const transferType = document.getElementById('transfer_type');
            const externalAccountGroup = document.getElementById('external_account_group');
            const beneficiaryAccount = document.getElementById('beneficiary_account');
            
            <?php if ($savingsAccount): ?>
            const savingsAccountNumber = '<?= htmlspecialchars($savingsAccount['account_number']) ?>';
            <?php endif; ?>

            transferType.addEventListener('change', function() {
                if (this.value === 'internal') {
                    externalAccountGroup.style.display = 'none';
                    beneficiaryAccount.value = savingsAccountNumber;
                    beneficiaryAccount.required = false;
                } else {
                    externalAccountGroup.style.display = 'block';
                    beneficiaryAccount.value = '';
                    beneficiaryAccount.required = true;
                }
            });
        });
    </script>
</body>
</html>