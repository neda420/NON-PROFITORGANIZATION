<?php

declare(strict_types=1);

namespace HelpingPaws\Models;

/**
 * Data-access layer for the `admins` table.
 *
 * All queries use PDO prepared statements to prevent SQL injection.
 */
class AdminModel
{
    public function __construct(private \PDO $db) {}

    /**
     * Find an admin by username together with their hashed password
     * (for authentication).
     *
     * @return array<string, mixed>|null
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, password FROM admins WHERE username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Find an admin by their primary-key ID.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, created_at FROM admins WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }
}
