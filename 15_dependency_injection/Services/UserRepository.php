<?php

namespace App\Services;

use App\Contracts\LoggerInterface;

class UserRepository
{
    private Database $db;
    private LoggerInterface $logger;

    public function __construct(Database $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function findAll(): array
    {
        $this->logger->info('Fetching all users');
        $result = $this->db->query('SELECT * FROM users');
        return [
            ['id' => 1, 'name' => 'Budi'],
            ['id' => 2, 'name' => 'Ani'],
            'query' => $result,
        ];
    }
}
