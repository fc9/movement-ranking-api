<?php

namespace App\Repositories;

use App\Config\Database;
use Illuminate\Database\Connection;

class PersonalRecordRepository implements PersonalRecordRepositoryInterface
{
    private Connection $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function getRankingByMovementId(int $movementId): array
    {
        // Optimized query to get ranking with personal records
        // This query gets the maximum value for each user and the date of that record
        $sql = "
            SELECT 
                u.id as user_id,
                u.name as user_name,
                max_records.max_value as personal_record,
                pr.date as personal_record_date,
                RANK() OVER (ORDER BY max_records.max_value DESC) as ranking_position
            FROM user u
            INNER JOIN (
                SELECT 
                    user_id,
                    MAX(value) as max_value
                FROM personal_record 
                WHERE movement_id = ?
                GROUP BY user_id
            ) max_records ON u.id = max_records.user_id
            INNER JOIN personal_record pr ON (
                pr.user_id = max_records.user_id 
                AND pr.movement_id = ? 
                AND pr.value = max_records.max_value
            )
            INNER JOIN (
                SELECT 
                    user_id,
                    MAX(date) as latest_date
                FROM personal_record 
                WHERE movement_id = ?
                GROUP BY user_id, value
            ) latest_records ON (
                pr.user_id = latest_records.user_id 
                AND pr.date = latest_records.latest_date
            )
            ORDER BY max_records.max_value DESC, pr.date ASC
        ";


        $results = $this->connection
            ->select($sql, [$movementId, $movementId, $movementId]);

        return array_map(function ($row) {
            // Convert stdClass to array if needed
            $row = (array) $row;

            return [
                'user_id'              => (int)$row['user_id'],
                'user_name'            => $row['user_name'],
                'personal_record'      => (float)$row['personal_record'],
                'personal_record_date' => $row['personal_record_date'],
                'ranking_position'     => (int)$row['ranking_position']
            ];
        }, $results);
    }
}

