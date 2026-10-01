<?php

declare(strict_types=1);

namespace Squadro\Controleur;

use Squadro\Http\ErreurHttpException;
use Squadro\Http\Requete;
use Squadro\Http\Session;
use Squadro\Http\Vue;
use Squadro\Modele\JoueurSquadro;
use Squadro\Persistance\DepotJoueurs;

/**
 * Comportements communs aux contrôleurs : authentification et protection CSRF.
 */
abstract class Controleur
{
    private ?JoueurSquadro $joueur = null;

    public function __construct(
        protected readonly Vue $vue,
        protected readonly Session $session,
        protected readonly DepotJoueurs $joueurs,
    ) {
    }

    protected function joueurConnecte(): ?JoueurSquadro
    {
        $id = $this->session->joueurId();
        if ($id === null) {
            return null;
        }

        return $this->joueur ??= $this->joueurs->trouverParId($id);
    }

    /** @throws ErreurHttpException 401 si personne n'est connecté */
    protected function exigerJoueur(): JoueurSquadro
    {
        return $this->joueurConnecte() ?? throw new ErreurHttpException(401, 'Connectez-vous pour continuer.');
    }

    /** @throws ErreurHttpException 419 si le jeton CSRF est absent ou invalide */
    protected function exigerCsrf(Requete $requete): void
    {
        if (!$this->session->verifierCsrf($requete->post('_csrf'))) {
            throw new ErreurHttpException(419, 'Votre session a expiré, merci de réessayer.');
        }
    }
}
