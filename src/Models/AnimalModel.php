<?php

declare(strict_types=1);

namespace HelpingPaws\Models;

/**
 * Data-access layer for the `rescued_animals` table.
 *
 * All queries use PDO prepared statements to prevent SQL injection.
 */
class AnimalModel
{
    public function __construct(private \PDO $db) {}

    /**
     * Search animals by type or gender (case-insensitive partial match).
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $term): array
    {
        $like = '%' . $term . '%';
        $stmt = $this->db->prepare(
            'SELECT medical_record, animal_type, animal_gender, vet_bills
               FROM rescued_animals
              WHERE animal_type LIKE ? OR animal_gender LIKE ?'
        );
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    /**
     * Return the total number of rescued animals.
     */
    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM rescued_animals')->fetchColumn();
    }

    /**
     * Return the percentage (0–100) of rescued animals matching a given type.
     */
    public function getTypePercentage(string $type): int
    {
        $stmt = $this->db->prepare(
            'SELECT ROUND(
                (SUM(CASE WHEN animal_type = ? THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) * 100
             ) AS pct
               FROM rescued_animals'
        );
        $stmt->execute([$type]);
        return (int) ($stmt->fetchColumn() ?? 0);
    }
}
