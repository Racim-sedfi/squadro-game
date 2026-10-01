<?php

declare(strict_types=1);

namespace Squadro\Modele;

/**
 * Direction de déplacement d'une pièce sur le plateau.
 */
enum Direction: string
{
    case Nord = 'nord';
    case Est = 'est';
    case Sud = 'sud';
    case Ouest = 'ouest';

    public function inverse(): self
    {
        return match ($this) {
            self::Nord => self::Sud,
            self::Sud => self::Nord,
            self::Est => self::Ouest,
            self::Ouest => self::Est,
        };
    }

    /** Variation de ligne pour un pas dans cette direction. */
    public function deltaLigne(): int
    {
        return match ($this) {
            self::Nord => -1,
            self::Sud => 1,
            default => 0,
        };
    }

    /** Variation de colonne pour un pas dans cette direction. */
    public function deltaColonne(): int
    {
        return match ($this) {
            self::Est => 1,
            self::Ouest => -1,
            default => 0,
        };
    }

    public function fleche(): string
    {
        return match ($this) {
            self::Nord => '↑',
            self::Est => '→',
            self::Sud => '↓',
            self::Ouest => '←',
        };
    }
}
