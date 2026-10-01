<?php

declare(strict_types=1);

namespace Squadro\Modele;

enum StatutPartie: string
{
    case EnAttente = 'en_attente';
    case EnCours = 'en_cours';
    case Terminee = 'terminee';

    public function libelle(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente d\'un adversaire',
            self::EnCours => 'En cours',
            self::Terminee => 'Terminée',
        };
    }
}
