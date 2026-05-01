<?php

declare(strict_types=1);

namespace HelpingPaws\Models;

/**
 * Data-access layer for the `donors` table.
 *
 * All queries use PDO prepared statements to prevent SQL injection.
 */
class DonorModel
{
    public function __construct(private \PDO $db) {}

    /**
     * Find a donor by their primary-key ID.
     *
     * @return array<string, mixed>|null
     */
    public function findById(string $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT donor_id, name, email, occupation, phone, address,
                    contact_method, interest_volunteering, created_at
               FROM donors
              WHERE donor_id = ?
              LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Find a donor together with their hashed password (for authentication).
     *
     * @return array<string, mixed>|null
     */
    public function findForAuth(string $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT donor_id, password FROM donors WHERE donor_id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Return true when a donor with the given ID already exists.
     */
    public function existsById(string $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM donors WHERE donor_id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Insert a new donor record.
     *
     * @param array{
     *   donor_id: string,
     *   name: string,
     *   email: string,
     *   password: string,
     *   address: string,
     *   phone: string,
     *   occupation: string,
     *   contact_method: string,
     *   interest_volunteering: string
     * } $data
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO donors
                (donor_id, name, email, password, address, phone,
                 occupation, contact_method, interest_volunteering)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        return $stmt->execute([
            $data['donor_id'],
            $data['name'],
            $data['email'],
            $data['password'],
            $data['address'],
            $data['phone'],
            $data['occupation'],
            $data['contact_method'],
            $data['interest_volunteering'],
        ]);
    }

    /**
     * Return the total number of registered donors.
     */
    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM donors')->fetchColumn();
    }
}
