<?php
session_start();
require_once __DIR__ . '/../../../controllers/TransactionController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$error = '';
$success = '';

// Get user's account number
$db = new Database('bank');
$pdo = $db->getConnection();
$stmt = $pdo->prepare("SELECT account_number FROM accounts WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userAccount = $stmt->fetch();

// Handle transfer form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer'])) {
    try {
        $amount = floatval($_POST['amount']);
        $beneficiary_account = $_POST['beneficiary_account'];
        
        if ($amount <= 0) {
            throw new Exception("Amount must be greater than zero");
        }

        // Verify beneficiary account exists
        $stmt = $pdo->prepare("SELECT id, user_id FROM accounts WHERE account_number = ?");
        $stmt->execute([$beneficiary_account]);
        $beneficiaryAccount = $stmt->fetch();

        if (!$beneficiaryAccount) {
            throw new Exception("Invalid beneficiary account number");
        }

        if ($beneficiaryAccount['user_id'] == $_SESSION['user_id']) {
            throw new Exception("Cannot transfer to your own account");
        }

        $result = createTransaction(
            $_SESSION['user_id'],
            'transfer',
            $amount,
            $beneficiaryAccount['id']
        );

        if ($result) {
            $success = "Transfer completed successfully!";
        }
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
                <p class="text-blue-200">Send money to other users securely.</p>
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
                        <input type="text" value="<?php echo htmlspecialchars($userAccount['account_number']); ?>" 
                               class="input-field" readonly>
                        <label class="input-label">Your Account Number</label>
                    </div>

                    <div class="input-group">
                        <input type="text" name="beneficiary_account" pattern="[0-9]{10}" 
                               class="input-field" placeholder=" " required
                               title="Please enter a valid 10-digit account number">
                        <label class="input-label">Beneficiary Account Number</label>
                    </div>

                    <div class="input-group">
                        <input type="number" name="amount" step="0.01" min="0.01" 
                               class="input-field" placeholder=" " required>
                        <label class="input-label">Amount ($)</label>
                    </div>

                    <div class="input-group">
                        <textarea name="description" class="input-field" 
                                placeholder=" " rows="3"></textarea>
                        <label class="input-label">Description (Optional)</label>
                    </div>

                    <button type="submit" name="transfer" 
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                        Send Money
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 