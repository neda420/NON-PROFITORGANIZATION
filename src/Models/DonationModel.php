<?php

declare(strict_types=1);

namespace HelpingPaws\Models;

/**
 * Data-access layer for the `donations` table.
 *
 * All queries use PDO prepared statements to prevent SQL injection.
 */
class DonationModel
{
    public function __construct(private \PDO $db) {}

    /**
     * Insert a new donation record.
     *
     * @param array{
     *   donor_id: string,
     *   donor_name: string,
     *   donation_purpose: string,
     *   amount: float
     * } $data
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO donations (donor_id, donor_name, donation_purpose, amount)
             VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([
            $data['donor_id'],
            $data['donor_name'],
            $data['donation_purpose'],
            $data['amount'],
        ]);
    }

    /**
     * Return all donations for a given donor, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findByDonorId(string $donorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT donor_name, donation_purpose, amount, donation_date
               FROM donations
              WHERE donor_id = ?
              ORDER BY donation_date DESC'
        );
        $stmt->execute([$donorId]);
        return $stmt->fetchAll();
    }

    /**
     * Return aggregate donation statistics for a given donor.
     *
     * @return array{total_amount: float, total_donations: int}
     */
    public function getStatsByDonorId(string $donorId): array
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(amount), 0) AS total_amount,
                    COUNT(*)                 AS total_donations
               FROM donations
              WHERE donor_id = ?'
        );
        $stmt->execute([$donorId]);
        $row = $stmt->fetch();
        return [
            'total_amount'     => (float)($row['total_amount']     ?? 0),
            'total_donations'  => (int)($row['total_donations']    ?? 0),
        ];
    }
}
