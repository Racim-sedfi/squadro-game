<?php

declare(strict_types=1);

namespace Squadro\Persistance;

use PDO;

/**
 * Fabrique de connexion PDO configurée de façon sûre
 * (exceptions, requêtes préparées natives, tableaux associatifs).
 */
final class ConnexionBdd
{
    public static function ouvrir(string $dsn, ?string $utilisateur = null, ?string $motDePasse = null): PDO
    {
        $pdo = new PDO($dsn, $utilisateur, $motDePasse, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = ON');
            self::creerSchemaSqlite($pdo);
        }

        return $pdo;
    }

    /** En SQLite, le schéma est créé à la volée : l'application démarre sans aucune installation. */
    private static function creerSchemaSqlite(PDO $pdo): void
    {
        $schema = file_get_contents(dirname(__DIR__, 2) . '/database/schema.sqlite.sql');
        if ($schema !== false) {
            $pdo->exec($schema);
        }
    }
}
