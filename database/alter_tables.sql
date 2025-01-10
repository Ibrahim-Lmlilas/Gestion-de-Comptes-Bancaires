-- Add account_number column to accounts table
ALTER TABLE accounts 
ADD COLUMN account_number VARCHAR(10) UNIQUE NOT NULL;

-- Update existing accounts with random account numbers
UPDATE accounts 
SET account_number = CONCAT(
    FLOOR(RAND() * 9 + 1), -- First digit non-zero
    FLOOR(RAND() * 10),
    FLOOR(RAND() * 10),
    FLOOR(RAND() * 10),
    FLOOR(RAND() * 10),
    FLOOR(RAND() * 10),
    FLOOR(RAND() * 10),
    FLOOR(RAND() * 10),
    FLOOR(RAND() * 10),
    FLOOR(RAND() * 10)
); 