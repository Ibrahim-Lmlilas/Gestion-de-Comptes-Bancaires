-- Active: 1733930101794@@127.0.0.1@3306@bancaire
CREATE DATABASE bancaire


use bancaire


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_type ENUM('courant', 'epargne') NOT NULL,
    balance DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    transaction_type ENUM('depot', 'retrait', 'transfert') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    beneficiary_account_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (beneficiary_account_id) REFERENCES accounts(id) ON DELETE SET NULL
);


INSERT INTO users (name, email, password, profile_pic)
VALUES 
('Ali Ahmed', 'ali.ahmed@example.com', 'hashed_password_123', 'images/ali.png'),
('Sara Ben', 'sara.ben@example.com', 'hashed_password_456', 'images/sara.png');

INSERT INTO accounts (user_id, account_type, balance)
VALUES 
(1, 'courant', 10000.00),
(1, 'epargne', 15000.50),
(2, 'courant', 5000.00);

INSERT INTO transactions (account_id, transaction_type, amount, beneficiary_account_id)
VALUES 
(1, 'depot', 500.00, NULL),
(2, 'retrait', 200.00, NULL),
(1, 'transfert', 1000.00, 2);

SELECT * FROM users;

SELECT * FROM accounts;

SELECT * FROM transactions;


