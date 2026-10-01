<?php

declare(strict_types=1);

namespace Squadro;

use PDO;
use Squadro\Controleur\AuthControleur;
use Squadro\Controleur\PartieControleur;
use Squadro\Controleur\SalonControleur;
use Squadro\Http\ErreurHttpException;
use Squadro\Http\Reponse;
use Squadro\Http\Requete;
use Squadro\Http\Routeur;
use Squadro\Http\Session;
use Squadro\Http\Vue;
use Squadro\Persistance\ConnexionBdd;
use Squadro\Persistance\DepotJoueurs;
use Squadro\Persistance\DepotParties;
use Squadro\Service\ServiceAuthentification;
use Squadro\Service\ServiceParties;
use Throwable;

/**
 * Point d'assemblage de l'application (« composition root ») :
 * instancie les dépendances, déclare les routes et convertit les erreurs en réponses.
 */
final class Application
{
    private readonly Routeur $routeur;
    private readonly Vue $vue;
    private readonly DepotJoueurs $joueurs;

    public function __construct(
        PDO $pdo,
        private readonly Session $session,
        private readonly bool $debug = false,
    ) {
        $this->vue = new Vue(dirname(__DIR__) . '/templates');
        $this->vue->partager('session', $session);

        $this->joueurs = $joueurs = new DepotJoueurs($pdo);
        $parties = new DepotParties($pdo);

        $auth = new AuthControleur($this->vue, $session, $joueurs, new ServiceAuthentification($joueurs));
        $salon = new SalonControleur($this->vue, $session, $joueurs, $parties);
        $partie = new PartieControleur($this->vue, $session, $joueurs, new ServiceParties($parties), $parties);

        $this->routeur = (new Routeur())
            ->get('/', fn () => $salon->accueil())
            ->get('/regles', fn () => $salon->regles())
            ->get('/connexion', fn () => $auth->formulaire())
            ->post('/connexion', fn (Requete $r) => $auth->connecter($r))
            ->post('/inscription', fn (Requete $r) => $auth->inscrire($r))
            ->post('/deconnexion', fn (Requete $r) => $auth->deconnecter($r))
            ->get('/salon', fn () => $salon->salon())
            ->post('/parties', fn (Requete $r) => $partie->creer($r))
            ->get('/parties/{id}', fn (Requete $r, int $id) => $partie->afficher($id))
            ->get('/parties/{id}/etat', fn (Requete $r, int $id) => $partie->etat($id))
            ->post('/parties/{id}/rejoindre', fn (Requete $r, int $id) => $partie->rejoindre($r, $id))
            ->post('/parties/{id}/coups', fn (Requete $r, int $id) => $partie->jouer($r, $id));
    }

    /** Construit l'application à partir des variables d'environnement. */
    public static function depuisEnvironnement(): self
    {
        $racine = dirname(__DIR__);
        $dsn = getenv('DB_DSN') ?: 'sqlite:' . $racine . '/var/squadro.sqlite';

        if (str_starts_with($dsn, 'sqlite:') && !is_dir($racine . '/var')) {
            mkdir($racine . '/var', 0775, true);
        }

        $pdo = ConnexionBdd::ouvrir($dsn, getenv('DB_USER') ?: null, getenv('DB_PASSWORD') ?: null);

        return new self($pdo, Session::demarrer(), filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOL));
    }

    public function traiter(Requete $requete): Reponse
    {
        try {
            $idJoueur = $this->session->joueurId();
            $this->vue->partager('joueur', $idJoueur !== null ? $this->joueurs->trouverParId($idJoueur) : null);

            [$action, $parametres] = $this->routeur->resoudre($requete);

            return $action($requete, ...array_values($parametres));
        } catch (ErreurHttpException $e) {
            if ($e->statut === 401) {
                $this->session->flash('info', $e->getMessage());

                return Reponse::redirection('/connexion');
            }

            return $this->pageErreur($e->statut, $e->getMessage());
        } catch (Throwable $e) {
            error_log((string) $e);

            return $this->pageErreur(500, $this->debug ? $e->getMessage() : 'Une erreur inattendue est survenue.');
        }
    }

    private function pageErreur(int $statut, string $message): Reponse
    {
        return $this->vue->rendre('erreur', ['statut' => $statut, 'message' => $message], 'Erreur ' . $statut, $statut);
    }
}
