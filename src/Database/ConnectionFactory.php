<?php

declare(strict_types=1);

namespace App\Database;

use App\Config\Database;
use PDO;

final class ConnectionFactory
{
    public static function shared(): PDO
    {
        return Database::getInstance()->getConnection();
    }
}
