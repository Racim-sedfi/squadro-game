<?php

declare(strict_types=1);

namespace Squadro\Modele;

/**
 * Ce qui s'est passé lors d'un coup : utile pour l'affichage (dernier coup joué)
 * et pour les tests.
 */
final readonly class ResultatCoup
{
    /**
     * @param list<Position> $piecesSautees positions des pièces adverses sautées (renvoyées en arrière)
     */
    public function __construct(
        public Couleur $couleur,
        public Position $depart,
        public Position $arrivee,
        public array $piecesSautees = [],
        public bool $demiTour = false,
        public bool $pieceArrivee = false,
    ) {
    }

    /** @return array<string, mixed> */
    public function versTableau(): array
    {
        return [
            'couleur' => $this->couleur->value,
            'depart' => $this->depart->versTableau(),
            'arrivee' => $this->arrivee->versTableau(),
            'piecesSautees' => array_map(static fn (Position $p) => $p->versTableau(), $this->piecesSautees),
            'demiTour' => $this->demiTour,
            'pieceArrivee' => $this->pieceArrivee,
        ];
    }

    /** @param array<string, mixed> $donnees */
    public static function depuisTableau(array $donnees): self
    {
        return new self(
            Couleur::from($donnees['couleur']),
            Position::depuisTableau($donnees['depart']),
            Position::depuisTableau($donnees['arrivee']),
            array_values(array_map(Position::depuisTableau(...), $donnees['piecesSautees'] ?? [])),
            (bool) ($donnees['demiTour'] ?? false),
            (bool) ($donnees['pieceArrivee'] ?? false),
        );
    }
}
