<?php

declare(strict_types=1);

namespace Squadro\Modele;

/**
 * Les deux camps d'une partie de Squadro.
 *
 * Les blancs partent de la colonne de gauche et avancent vers l'est,
 * les noirs partent de la ligne du bas et avancent vers le nord.
 */
enum Couleur: string
{
    case Blanc = 'blanc';
    case Noir = 'noir';

    public function adversaire(): self
    {
        return $this === self::Blanc ? self::Noir : self::Blanc;
    }

    public function libelle(): string
    {
        return $this === self::Blanc ? 'Blancs' : 'Noirs';
    }
}
