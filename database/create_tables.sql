-- Active: 1733930101794@@127.0.0.1@3306@bank
-- Create the Bank database
CREATE DATABASE IF NOT EXISTS Bank;
USE Bank;

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create accounts table
CREATE TABLE accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    account_type ENUM('current', 'savings') NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create transactions table
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    transaction_type ENUM('deposit', 'withdrawal', 'transfer') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    beneficiary_account_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    FOREIGN KEY (beneficiary_account_id) REFERENCES accounts(id)
);


INSERT INTO users (name, email, password, profile_pic) VALUES
('Mohammed Rachidi', 'mohammed@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://randomuser.me/api/portraits/men/11.jpg'),
('Amina Khalil', 'amina@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://randomuser.me/api/portraits/women/12.jpg'),
('Hassan Benjelloun', 'hassan@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://randomuser.me/api/portraits/men/13.jpg'),
('Nadia Sabri', 'nadia@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://randomuser.me/api/portraits/women/14.jpg'),
('Omar Alaoui', 'omar@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://randomuser.me/api/portraits/men/15.jpg');

INSERT INTO users (name, email, password, profile_pic) VALUES
('Omar Lmlilas', 'Omaar@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'https://randomuser.me/api/portraits/men/16.jpg');


-- Essayons d'abord un seul compte
INSERT INTO accounts (user_id, account_type, balance) 
VALUES (12, 'savings', 7000.00);






-- Vérifions si ça a marché
SELECT * FROM accounts;

SELECT * FROM users;


