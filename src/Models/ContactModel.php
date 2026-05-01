<?php

declare(strict_types=1);

namespace HelpingPaws\Models;

/**
 * Data-access layer for the `contact_messages` table.
 *
 * All queries use PDO prepared statements to prevent SQL injection.
 */
class ContactModel
{
    public function __construct(private \PDO $db) {}

    /**
     * Insert a new contact message.
     *
     * @param array{name: string, email: string, message: string} $data
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)'
        );
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['message'],
        ]);
    }

    /**
     * Return all contact messages, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array
    {
        return $this->db
            ->query(
                'SELECT id, name, email, message, created_at, admin_id
                   FROM contact_messages
                  ORDER BY created_at DESC'
            )
            ->fetchAll();
    }

    /**
     * Update a contact message record.
     *
     * @param array{name: string, email: string, message: string, admin_id: int} $data
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE contact_messages
                SET name = ?, email = ?, message = ?, admin_id = ?
              WHERE id = ?'
        );
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['message'],
            $data['admin_id'],
            $id,
        ]);
    }

    /**
     * Delete a contact message by ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM contact_messages WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
