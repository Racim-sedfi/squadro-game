<?php

declare(strict_types=1);

namespace Squadro\Modele;

/**
 * Un joueur inscrit. Le mot de passe n'est jamais porté par l'objet métier.
 */
final readonly class JoueurSquadro
{
    public function __construct(
        public int $id,
        public string $pseudo,
    ) {
    }

    public function equals(self $autre): bool
    {
        return $this->id === $autre->id;
    }
}
