-- Insert test users if they don't exist
INSERT IGNORE INTO users (username, email, password, role) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'), -- password: password
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert test accounts if they don't exist
INSERT IGNORE INTO accounts (user_id, account_type, balance) 
SELECT u.id, 'savings', 5000.00 FROM users u WHERE u.email = 'john@example.com'
UNION ALL
SELECT u.id, 'current', 2500.00 FROM users u WHERE u.email = 'john@example.com'
UNION ALL
SELECT u.id, 'savings', 7500.00 FROM users u WHERE u.email = 'jane@example.com'
UNION ALL
SELECT u.id, 'current', 3000.00 FROM users u WHERE u.email = 'jane@example.com'
UNION ALL
SELECT u.id, 'current', 10000.00 FROM users u WHERE u.email = 'admin@example.com';

-- Insert test transactions if they don't exist (using account IDs from the previous inserts)
INSERT IGNORE INTO transactions (account_id, transaction_type, amount, beneficiary_account_id, created_at)
SELECT 
    a.id,
    'deposit',
    5000.00,
    NULL,
    DATE_SUB(NOW(), INTERVAL 30 DAY)
FROM accounts a
JOIN users u ON a.user_id = u.id
WHERE u.email = 'john@example.com' AND a.account_type = 'savings'
UNION ALL
SELECT 
    a.id,
    'deposit',
    3000.00,
    NULL,
    DATE_SUB(NOW(), INTERVAL 25 DAY)
FROM accounts a
JOIN users u ON a.user_id = u.id
WHERE u.email = 'john@example.com' AND a.account_type = 'current'
UNION ALL
SELECT 
    a.id,
    'withdrawal',
    500.00,
    NULL,
    DATE_SUB(NOW(), INTERVAL 20 DAY)
FROM accounts a
JOIN users u ON a.user_id = u.id
WHERE u.email = 'john@example.com' AND a.account_type = 'current'
UNION ALL
SELECT 
    a1.id,
    'transfer',
    1000.00,
    a2.id,
    DATE_SUB(NOW(), INTERVAL 15 DAY)
FROM accounts a1
JOIN users u1 ON a1.user_id = u1.id
JOIN accounts a2 ON a2.account_type = 'savings'
JOIN users u2 ON a2.user_id = u2.id
WHERE u1.email = 'john@example.com' AND a1.account_type = 'current'
AND u2.email = 'jane@example.com';

-- Jane's transactions
INSERT IGNORE INTO transactions (account_id, transaction_type, amount, beneficiary_account_id, created_at)
SELECT 
    a.id,
    'deposit',
    7500.00,
    NULL,
    DATE_SUB(NOW(), INTERVAL 28 DAY)
FROM accounts a
JOIN users u ON a.user_id = u.id
WHERE u.email = 'jane@example.com' AND a.account_type = 'savings'
UNION ALL
SELECT 
    a.id,
    'deposit',
    4000.00,
    NULL,
    DATE_SUB(NOW(), INTERVAL 23 DAY)
FROM accounts a
JOIN users u ON a.user_id = u.id
WHERE u.email = 'jane@example.com' AND a.account_type = 'current'
UNION ALL
SELECT 
    a.id,
    'withdrawal',
    1000.00,
    NULL,
    DATE_SUB(NOW(), INTERVAL 18 DAY)
FROM accounts a
JOIN users u ON a.user_id = u.id
WHERE u.email = 'jane@example.com' AND a.account_type = 'current'
UNION ALL
SELECT 
    a1.id,
    'transfer',
    500.00,
    a2.id,
    DATE_SUB(NOW(), INTERVAL 13 DAY)
FROM accounts a1
JOIN users u1 ON a1.user_id = u1.id
JOIN accounts a2 ON a2.account_type = 'savings'
JOIN users u2 ON a2.user_id = u2.id
WHERE u1.email = 'jane@example.com' AND a1.account_type = 'savings'
AND u2.email = 'john@example.com';

-- Admin's transactions
INSERT IGNORE INTO transactions (account_id, transaction_type, amount, beneficiary_account_id, created_at)
SELECT 
    a.id,
    'deposit',
    10000.00,
    NULL,
    DATE_SUB(NOW(), INTERVAL 26 DAY)
FROM accounts a
JOIN users u ON a.user_id = u.id
WHERE u.email = 'admin@example.com' AND a.account_type = 'current'
UNION ALL
SELECT 
    a.id,
    'withdrawal',
    2000.00,
    NULL,
    DATE_SUB(NOW(), INTERVAL 21 DAY)
FROM accounts a
JOIN users u ON a.user_id = u.id
WHERE u.email = 'admin@example.com' AND a.account_type = 'current'
UNION ALL
SELECT 
    a1.id,
    'transfer',
    1500.00,
    a2.id,
    DATE_SUB(NOW(), INTERVAL 16 DAY)
FROM accounts a1
JOIN users u1 ON a1.user_id = u1.id
JOIN accounts a2 ON a2.account_type = 'current'
JOIN users u2 ON a2.user_id = u2.id
WHERE u1.email = 'admin@example.com' AND a1.account_type = 'current'
AND u2.email = 'john@example.com';
