<?php

declare(strict_types=1);

namespace Squadro\Modele;

use InvalidArgumentException;

/**
 * Coordonnées immuables d'une case du plateau (0 à 6 en ligne et en colonne).
 */
final readonly class Position
{
    public const MIN = 0;
    public const MAX = 6;

    public function __construct(
        public int $ligne,
        public int $colonne,
    ) {
        if (!self::estValide($ligne, $colonne)) {
            throw new InvalidArgumentException(sprintf('Position hors plateau : (%d, %d).', $ligne, $colonne));
        }
    }

    public static function estValide(int $ligne, int $colonne): bool
    {
        return $ligne >= self::MIN && $ligne <= self::MAX
            && $colonne >= self::MIN && $colonne <= self::MAX;
    }

    /** Case voisine dans la direction donnée, ou null si l'on sort du plateau. */
    public function voisine(Direction $direction): ?self
    {
        $ligne = $this->ligne + $direction->deltaLigne();
        $colonne = $this->colonne + $direction->deltaColonne();

        return self::estValide($ligne, $colonne) ? new self($ligne, $colonne) : null;
    }

    public function equals(self $autre): bool
    {
        return $this->ligne === $autre->ligne && $this->colonne === $autre->colonne;
    }

    public function cle(): string
    {
        return $this->ligne . ',' . $this->colonne;
    }

    /** @return array{ligne: int, colonne: int} */
    public function versTableau(): array
    {
        return ['ligne' => $this->ligne, 'colonne' => $this->colonne];
    }

    /** @param array{ligne: int, colonne: int} $donnees */
    public static function depuisTableau(array $donnees): self
    {
        return new self((int) $donnees['ligne'], (int) $donnees['colonne']);
    }
}
