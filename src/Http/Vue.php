<?php

declare(strict_types=1);

namespace Squadro\Http;

use RuntimeException;

/**
 * Moteur de gabarits PHP natifs, avec mise en page commune.
 * Toute donnée affichée passe par e() (échappement HTML anti-XSS).
 */
final class Vue
{
    /** @var array<string, mixed> */
    private array $globales = [];

    public function __construct(private readonly string $dossier)
    {
    }

    public function partager(string $nom, mixed $valeur): void
    {
        $this->globales[$nom] = $valeur;
    }

    /** @param array<string, mixed> $donnees */
    public function rendre(string $gabarit, array $donnees = [], string $titre = 'Squadro', int $statut = 200): Reponse
    {
        $contenu = $this->inclure($gabarit, $donnees);
        $page = $this->inclure('layout', ['contenu' => $contenu, 'titre' => $titre] + $donnees);

        return new Reponse($page, $statut);
    }

    /** @param array<string, mixed> $donnees */
    public function inclure(string $gabarit, array $donnees = []): string
    {
        $fichier = $this->dossier . '/' . $gabarit . '.php';
        if (!is_file($fichier)) {
            throw new RuntimeException(sprintf('Gabarit introuvable : %s', $gabarit));
        }

        $vue = $this;
        extract($donnees + $this->globales, EXTR_SKIP);
        ob_start();
        try {
            require $fichier;
        } finally {
            $sortie = (string) ob_get_clean();
        }

        return $sortie;
    }
}
