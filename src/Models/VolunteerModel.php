<?php

declare(strict_types=1);

namespace HelpingPaws\Models;

/**
 * Data-access layer for the `volunteers` table.
 *
 * All queries use PDO prepared statements to prevent SQL injection.
 */
class VolunteerModel
{
    public function __construct(private \PDO $db) {}

    /**
     * Insert a new volunteer sign-up.
     *
     * @param array{
     *   name: string,
     *   email: string,
     *   phone: string,
     *   message: string
     * } $data
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO volunteers (name, email, phone, message)
             VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['message'],
        ]);
    }

    /**
     * Return the total number of registered volunteers.
     */
    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM volunteers')->fetchColumn();
    }
}
