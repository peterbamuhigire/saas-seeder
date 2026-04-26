<?php

declare(strict_types=1);

namespace App\Http\RateLimit;

use PDO;

final class DatabaseRateLimitStore implements RateLimitStoreInterface
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function hit(string $bucket, int $windowSeconds): int
    {
        $this->purgeExpired($windowSeconds);

        $stmt = $this->db->prepare('INSERT INTO tbl_rate_limit_hits (bucket_key, hit_at) VALUES (?, CURRENT_TIMESTAMP)');
        $stmt->execute([$bucket]);

        return $this->count($bucket, $windowSeconds);
    }

    public function count(string $bucket, int $windowSeconds): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM tbl_rate_limit_hits WHERE bucket_key = ? AND hit_at >= ?'
        );
        $stmt->execute([$bucket, gmdate('Y-m-d H:i:s', time() - $windowSeconds)]);

        return (int) $stmt->fetchColumn();
    }

    private function purgeExpired(int $windowSeconds): void
    {
        $stmt = $this->db->prepare('DELETE FROM tbl_rate_limit_hits WHERE hit_at < ?');
        $stmt->execute([gmdate('Y-m-d H:i:s', time() - max($windowSeconds, 86400))]);
    }
}
