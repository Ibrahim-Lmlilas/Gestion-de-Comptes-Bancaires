<?php

class Database
{
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $conn = null;

    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    public function construct($dbname, $username = 'root', $password = '20JvAt02', $host = null)
    {
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
        $this->host = $host ?? 'localhost';
        $this->connect();
    }

    public function connect()
    {
        try {
            if ($this->conn === null) {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
                $this->conn = new PDO($dsn, $this->username, $this->password, $this->options);
            }
            return $this->conn;
        } catch (PDOException $e) {
            throw new Exception("Connection error: " . $e->getMessage());
        }
    }
}
