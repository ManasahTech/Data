<?php

declare(strict_types=1);

namespace ManasahTech\Data;

use ManasahTech\Contracts\Data\Database as DatabaseContract;
use PDO;

final class Database implements DatabaseContract
{
    private PDO $pdo;

    public function __construct(
        private string $driver, 
        private string $host, 
        private int $port, 
        private string $database, 
        private string $user, 
        private string $password, 
        private array $options)
    {
        $dsn = "$driver:host=$host;dbname=$database";
        $this->pdo = new PDO($dsn, $user, $password, $options);
    }

    public function getDriver(): string
    {
        return $this->driver;
    }
    public function getHost(): string
    {
        return $this->host;
    }
    public function getPort(): int
    {
        return $this->port;
    }
    public function getDatabase(): string
    {
        return $this->database;
    }
    public function getUser(): string
    {
        return $this->user;
    }
    public function getEncode(): array
    {
        return $this->options;
    }

    public function query()
    {
        
    }
}