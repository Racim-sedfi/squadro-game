<?php

declare(strict_types=1);

/*
 * Contrôleur frontal : toutes les requêtes passent par ce fichier.
 * Avec le serveur intégré de PHP, les fichiers statiques (CSS, JS) sont servis directement.
 */

if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require dirname(__DIR__) . '/vendor/autoload.php';

Squadro\Application::depuisEnvironnement()
    ->traiter(Squadro\Http\Requete::depuisGlobales())
    ->envoyer();
