<?php

declare(strict_types=1);

namespace Squadro\Http;

/**
 * Requête HTTP immuable, construite à partir des superglobales
 * (ou à la main dans les tests).
 */
final readonly class Requete
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $post
     * @param array<string, string> $entetes
     */
    public function __construct(
        public string $methode,
        public string $chemin,
        public array $query = [],
        public array $post = [],
        public array $entetes = [],
    ) {
    }

    public static function depuisGlobales(): self
    {
        $chemin = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            '/' . trim(is_string($chemin) ? $chemin : '/', '/'),
            $_GET,
            $_POST,
            ['accept' => (string) ($_SERVER['HTTP_ACCEPT'] ?? '')],
        );
    }

    public function post(string $cle, string $defaut = ''): string
    {
        $valeur = $this->post[$cle] ?? $defaut;

        return is_string($valeur) ? $valeur : $defaut;
    }

    public function postEntier(string $cle): ?int
    {
        $valeur = filter_var($this->post[$cle] ?? null, FILTER_VALIDATE_INT);

        return $valeur === false ? null : $valeur;
    }
}
