<?php

declare(strict_types=1);

namespace Squadro\Http;

final readonly class Reponse
{
    /** @param array<string, string> $entetes */
    public function __construct(
        public string $corps = '',
        public int $statut = 200,
        public array $entetes = ['Content-Type' => 'text/html; charset=UTF-8'],
    ) {
    }

    /** Redirection après un POST (pattern Post/Redirect/Get). */
    public static function redirection(string $url): self
    {
        return new self('', 303, ['Location' => $url]);
    }

    /** @param array<string, mixed> $donnees */
    public static function json(array $donnees, int $statut = 200): self
    {
        return new self(
            json_encode($donnees, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            $statut,
            ['Content-Type' => 'application/json; charset=UTF-8', 'Cache-Control' => 'no-store'],
        );
    }

    public function envoyer(): void
    {
        http_response_code($this->statut);
        foreach ($this->entetes as $nom => $valeur) {
            header($nom . ': ' . $valeur);
        }
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: same-origin');
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; frame-ancestors 'none'; form-action 'self'");

        echo $this->corps;
    }
}
