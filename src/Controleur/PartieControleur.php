<?php

declare(strict_types=1);

namespace Squadro\Controleur;

use InvalidArgumentException;
use Squadro\Http\ErreurHttpException;
use Squadro\Http\Reponse;
use Squadro\Http\Requete;
use Squadro\Http\Session;
use Squadro\Http\Vue;
use Squadro\Modele\CoupInvalideException;
use Squadro\Modele\PartieSquadro;
use Squadro\Modele\Position;
use Squadro\Persistance\ConflitDeVersionException;
use Squadro\Persistance\DepotJoueurs;
use Squadro\Persistance\DepotParties;
use Squadro\Service\PartieIntrouvableException;
use Squadro\Service\ServiceParties;

final class PartieControleur extends Controleur
{
    public function __construct(
        Vue $vue,
        Session $session,
        DepotJoueurs $joueurs,
        private readonly ServiceParties $service,
        private readonly DepotParties $parties,
    ) {
        parent::__construct($vue, $session, $joueurs);
    }

    public function creer(Requete $requete): Reponse
    {
        $this->exigerCsrf($requete);
        $partie = $this->service->creer($this->exigerJoueur());
        $this->session->flash('info', 'Partie créée : elle apparaît dans le salon des autres joueurs.');

        return Reponse::redirection('/parties/' . $partie->id());
    }

    public function rejoindre(Requete $requete, int $id): Reponse
    {
        $this->exigerCsrf($requete);

        try {
            $this->service->rejoindre($id, $this->exigerJoueur());
        } catch (CoupInvalideException | ConflitDeVersionException $e) {
            $this->session->flash('erreur', $e->getMessage());

            return Reponse::redirection('/salon');
        } catch (PartieIntrouvableException $e) {
            throw new ErreurHttpException(404, $e->getMessage());
        }

        return Reponse::redirection('/parties/' . $id);
    }

    public function afficher(int $id): Reponse
    {
        $joueur = $this->exigerJoueur();
        $partie = $this->chargerOu404($id);

        return $this->vue->rendre('partie', [
            'partie' => $partie,
            'joueur' => $joueur,
            'maCouleur' => $partie->couleurDe($joueur),
            'coupsPossibles' => $partie->coupsPossibles($joueur),
        ], 'Partie n°' . $id);
    }

    public function jouer(Requete $requete, int $id): Reponse
    {
        $this->exigerCsrf($requete);
        $joueur = $this->exigerJoueur();

        try {
            // Le bouton cliqué transmet la case de la pièce sous la forme « ligne,colonne ».
            if (preg_match('/^(\d),(\d)$/', $requete->post('coup'), $case) !== 1) {
                throw new CoupInvalideException('Coup invalide.');
            }
            $depart = new Position((int) $case[1], (int) $case[2]);
            $this->service->jouer($id, $joueur, $depart, $requete->postEntier('version'));
        } catch (CoupInvalideException | ConflitDeVersionException | InvalidArgumentException $e) {
            $this->session->flash('erreur', $e->getMessage());
        } catch (PartieIntrouvableException $e) {
            throw new ErreurHttpException(404, $e->getMessage());
        }

        return Reponse::redirection('/parties/' . $id);
    }

    /** Point d'API léger interrogé par le navigateur pour détecter le coup de l'adversaire. */
    public function etat(int $id): Reponse
    {
        $this->exigerJoueur();
        $version = $this->parties->version($id) ?? throw new ErreurHttpException(404, 'Partie introuvable.');

        return Reponse::json(['version' => $version]);
    }

    private function chargerOu404(int $id): PartieSquadro
    {
        try {
            return $this->service->charger($id);
        } catch (PartieIntrouvableException $e) {
            throw new ErreurHttpException(404, $e->getMessage());
        }
    }
}
