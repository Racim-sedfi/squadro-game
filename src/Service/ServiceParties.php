<?php

declare(strict_types=1);

namespace Squadro\Service;

use Squadro\Modele\JoueurSquadro;
use Squadro\Modele\PartieSquadro;
use Squadro\Modele\Position;
use Squadro\Modele\ResultatCoup;
use Squadro\Persistance\ConflitDeVersionException;
use Squadro\Persistance\DepotParties;

/**
 * Cas d'utilisation autour des parties : chaque méthode charge l'agrégat,
 * délègue la règle métier au modèle puis enregistre le résultat.
 */
final class ServiceParties
{
    public function __construct(private readonly DepotParties $parties)
    {
    }

    public function creer(JoueurSquadro $createur): PartieSquadro
    {
        $partie = PartieSquadro::nouvelle($createur);
        $this->parties->ajouter($partie);

        return $partie;
    }

    public function rejoindre(int $idPartie, JoueurSquadro $joueur): PartieSquadro
    {
        $partie = $this->charger($idPartie);
        if (!$partie->participe($joueur)) {
            $partie->rejoindre($joueur);
            $this->parties->enregistrer($partie);
        }

        return $partie;
    }

    public function jouer(int $idPartie, JoueurSquadro $joueur, Position $depart, ?int $versionAttendue = null): ResultatCoup
    {
        $partie = $this->charger($idPartie);

        // Le formulaire transmet la version affichée : un double clic ou un onglet périmé
        // ne peut pas rejouer un coup sur un plateau qui a changé.
        if ($versionAttendue !== null && $versionAttendue !== $partie->version()) {
            throw new ConflitDeVersionException('Le plateau a changé depuis votre dernier affichage.');
        }

        $resultat = $partie->jouer($joueur, $depart);
        $this->parties->enregistrer($partie);

        return $resultat;
    }

    public function charger(int $idPartie): PartieSquadro
    {
        return $this->parties->trouver($idPartie) ?? throw new PartieIntrouvableException($idPartie);
    }
}
