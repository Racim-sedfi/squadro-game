<?php

declare(strict_types=1);

namespace Squadro\Modele;

use InvalidArgumentException;

/**
 * Une pièce de Squadro : une couleur et une direction de déplacement.
 *
 * Objet valeur immuable : faire demi-tour renvoie une nouvelle pièce.
 */
final readonly class PieceSquadro
{
    public function __construct(
        public Couleur $couleur,
        public Direction $direction,
    ) {
        if (!in_array($direction, [self::directionAller($couleur), self::directionAller($couleur)->inverse()], true)) {
            throw new InvalidArgumentException(sprintf(
                'Une pièce %s ne peut pas se déplacer vers le %s.',
                $couleur->value,
                $direction->value,
            ));
        }
    }

    /** Pièce blanche sur son point de départ, prête pour l'aller. */
    public static function blanche(): self
    {
        return new self(Couleur::Blanc, Direction::Est);
    }

    /** Pièce noire sur son point de départ, prête pour l'aller. */
    public static function noire(): self
    {
        return new self(Couleur::Noir, Direction::Nord);
    }

    /** Direction du trajet aller pour une couleur donnée. */
    public static function directionAller(Couleur $couleur): Direction
    {
        return $couleur === Couleur::Blanc ? Direction::Est : Direction::Nord;
    }

    public function estEnRetour(): bool
    {
        return $this->direction !== self::directionAller($this->couleur);
    }

    public function faireDemiTour(): self
    {
        return new self($this->couleur, $this->direction->inverse());
    }

    /** @return array{couleur: string, direction: string} */
    public function versTableau(): array
    {
        return ['couleur' => $this->couleur->value, 'direction' => $this->direction->value];
    }

    /** @param array{couleur: string, direction: string} $donnees */
    public static function depuisTableau(array $donnees): self
    {
        return new self(Couleur::from($donnees['couleur']), Direction::from($donnees['direction']));
    }
}
