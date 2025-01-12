CREATE DATABASE IF NOT EXISTS Bank;
USE Bank;

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create accounts table
CREATE TABLE accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_type ENUM('current', 'savings') NOT NULL,
    account_number VARCHAR(20) NOT NULL UNIQUE,
    balance DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create transactions table
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    type ENUM('deposit', 'withdrawal', 'transfer') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    beneficiary_account_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    FOREIGN KEY (beneficiary_account_id) REFERENCES accounts(id)
);

-- Insert test users if they don't exist
INSERT IGNORE INTO users (username, email, password, role) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'), -- password: password
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert test accounts if they don't exist
INSERT IGNORE INTO accounts (user_id, account_type, account_number, balance, status) 
SELECT u.id, 'savings', FLOOR(RAND() * 9000000000) + 1000000000, 5000.00, 'Active' FROM users u WHERE u.email = 'john@example.com'
UNION ALL
SELECT u.id, 'current', FLOOR(RAND() * 9000000000) + 1000000000, 2500.00, 'Active' FROM users u WHERE u.email = 'john@example.com'
UNION ALL
SELECT u.id, 'savings', FLOOR(RAND() * 9000000000) + 1000000000, 7500.00, 'Active' FROM users u WHERE u.email = 'jane@example.com'
UNION ALL
SELECT u.id, 'current', FLOOR(RAND() * 9000000000) + 1000000000, 3000.00, 'Active' FROM users u WHERE u.email = 'jane@example.com'
UNION ALL
SELECT u.id, 'current', FLOOR(RAND() * 9000000000) + 1000000000, 10000.00, 'Active' FROM users u WHERE u.email = 'admin@example.com';

-- Insert test transactions if they don't exist (using account IDs from the previous inserts)
INSERT IGNORE INTO transactions (account_id, type, amount, beneficiary_account_id, created_at)
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
INSERT IGNORE INTO transactions (account_id, type, amount, beneficiary_account_id, created_at)
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
INSERT IGNORE INTO transactions (account_id, type, amount, beneficiary_account_id, created_at)
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

-- Insert test transactions if they don't exist
INSERT IGNORE INTO transactions (account_id, type, amount, beneficiary_account_id, created_at)
SELECT a1.id, 'deposit', 1000.00, NULL, DATE_SUB(NOW(), INTERVAL 5 DAY)
FROM accounts a1
INNER JOIN users u ON a1.user_id = u.id
WHERE u.email = 'john@example.com' AND a1.account_type = 'current'
LIMIT 1;

INSERT IGNORE INTO transactions (account_id, type, amount, beneficiary_account_id, created_at)
SELECT a1.id, 'withdrawal', 500.00, NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)
FROM accounts a1
INNER JOIN users u ON a1.user_id = u.id
WHERE u.email = 'john@example.com' AND a1.account_type = 'current'
LIMIT 1;

INSERT IGNORE INTO transactions (account_id, type, amount, beneficiary_account_id, created_at)
SELECT a1.id, 'transfer', 200.00, a2.id, DATE_SUB(NOW(), INTERVAL 1 DAY)
FROM accounts a1
INNER JOIN users u1 ON a1.user_id = u1.id
INNER JOIN accounts a2 ON a2.account_type = 'current'
INNER JOIN users u2 ON a2.user_id = u2.id
WHERE u1.email = 'john@example.com' 
  AND u2.email = 'jane@example.com'
  AND a1.account_type = 'current'
LIMIT 1;