<?php

declare(strict_types=1);

namespace Squadro\Controleur;

use Squadro\Http\Reponse;
use Squadro\Http\Requete;
use Squadro\Http\Session;
use Squadro\Http\Vue;
use Squadro\Persistance\DepotJoueurs;
use Squadro\Persistance\PseudoDejaPrisException;
use Squadro\Service\ErreurValidationException;
use Squadro\Service\ServiceAuthentification;

final class AuthControleur extends Controleur
{
    public function __construct(
        Vue $vue,
        Session $session,
        DepotJoueurs $joueurs,
        private readonly ServiceAuthentification $authentification,
    ) {
        parent::__construct($vue, $session, $joueurs);
    }

    public function formulaire(): Reponse
    {
        if ($this->joueurConnecte() !== null) {
            return Reponse::redirection('/salon');
        }

        return $this->vue->rendre('connexion', ['pseudo' => '', 'onglet' => 'connexion'], 'Connexion');
    }

    public function connecter(Requete $requete): Reponse
    {
        $this->exigerCsrf($requete);

        try {
            $joueur = $this->authentification->connecter($requete->post('pseudo'), $requete->post('mot_de_passe'));
        } catch (ErreurValidationException $e) {
            return $this->formulaireAvecErreur($requete, $e->getMessage(), 'connexion');
        }

        $this->session->connecter($joueur->id);
        $this->session->flash('succes', sprintf('Bon retour, %s !', $joueur->pseudo));

        return Reponse::redirection('/salon');
    }

    public function inscrire(Requete $requete): Reponse
    {
        $this->exigerCsrf($requete);

        if ($requete->post('mot_de_passe') !== $requete->post('confirmation')) {
            return $this->formulaireAvecErreur($requete, 'Les deux mots de passe ne correspondent pas.', 'inscription');
        }

        try {
            $joueur = $this->authentification->inscrire($requete->post('pseudo'), $requete->post('mot_de_passe'));
        } catch (ErreurValidationException | PseudoDejaPrisException $e) {
            return $this->formulaireAvecErreur($requete, $e->getMessage(), 'inscription');
        }

        $this->session->connecter($joueur->id);
        $this->session->flash('succes', sprintf('Bienvenue, %s ! Créez une partie ou rejoignez-en une.', $joueur->pseudo));

        return Reponse::redirection('/salon');
    }

    public function deconnecter(Requete $requete): Reponse
    {
        $this->exigerCsrf($requete);
        $this->session->deconnecter();

        return Reponse::redirection('/connexion');
    }

    private function formulaireAvecErreur(Requete $requete, string $erreur, string $onglet): Reponse
    {
        return $this->vue->rendre(
            'connexion',
            ['pseudo' => $requete->post('pseudo'), 'erreur' => $erreur, 'onglet' => $onglet],
            'Connexion',
            422,
        );
    }
}
