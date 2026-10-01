<?php

declare(strict_types=1);

namespace Squadro\Controleur;

use Squadro\Http\Reponse;
use Squadro\Http\Session;
use Squadro\Http\Vue;
use Squadro\Modele\StatutPartie;
use Squadro\Persistance\DepotJoueurs;
use Squadro\Persistance\DepotParties;

final class SalonControleur extends Controleur
{
    public function __construct(
        Vue $vue,
        Session $session,
        DepotJoueurs $joueurs,
        private readonly DepotParties $parties,
    ) {
        parent::__construct($vue, $session, $joueurs);
    }

    public function accueil(): Reponse
    {
        return Reponse::redirection($this->joueurConnecte() !== null ? '/salon' : '/connexion');
    }

    public function salon(): Reponse
    {
        $joueur = $this->exigerJoueur();
        $mesParties = $this->parties->listerPourJoueur($joueur);

        $enCours = array_values(array_filter($mesParties, static fn ($p) => $p->statut() !== StatutPartie::Terminee));
        $terminees = array_values(array_filter($mesParties, static fn ($p) => $p->statut() === StatutPartie::Terminee));

        return $this->vue->rendre('salon', [
            'joueur' => $joueur,
            'enCours' => $enCours,
            'terminees' => $terminees,
            'ouvertes' => $this->parties->listerOuvertes($joueur),
        ], 'Salon');
    }

    public function regles(): Reponse
    {
        return $this->vue->rendre('regles', [], 'Règles du jeu');
    }
}
