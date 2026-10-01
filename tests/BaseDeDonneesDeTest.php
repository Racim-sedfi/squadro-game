<?php

declare(strict_types=1);

namespace Squadro\Tests;

use PDO;
use Squadro\Persistance\ConnexionBdd;

/**
 * Fournit une base vide pour chaque test.
 *
 * Par défaut : SQLite en mémoire (aucune installation nécessaire).
 * Si TEST_DB_DSN est défini (ex. dans la CI), les tests tournent contre MySQL.
 */
trait BaseDeDonneesDeTest
{
    protected function baseDeTest(): PDO
    {
        $dsn = getenv('TEST_DB_DSN');
        if ($dsn === false || $dsn === '') {
            return ConnexionBdd::ouvrir('sqlite::memory:');
        }

        $pdo = ConnexionBdd::ouvrir($dsn, getenv('TEST_DB_USER') ?: null, getenv('TEST_DB_PASSWORD') ?: null);
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $pdo->exec('TRUNCATE TABLE parties');
        $pdo->exec('TRUNCATE TABLE joueurs');
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        return $pdo;
    }
}
