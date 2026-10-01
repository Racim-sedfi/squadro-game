<?php

declare(strict_types=1);

namespace Squadro\Service;

use Squadro\Modele\JoueurSquadro;
use Squadro\Persistance\DepotJoueurs;

/**
 * Inscription et connexion des joueurs.
 * Les mots de passe sont hachés avec password_hash() (algorithme par défaut de PHP).
 */
final class ServiceAuthentification
{
    public const LONGUEUR_MIN_MOT_DE_PASSE = 6;
    private const FORMAT_PSEUDO = '/^[A-Za-z0-9_-]{3,20}$/';

    public function __construct(private readonly DepotJoueurs $joueurs)
    {
    }

    /** @throws ErreurValidationException */
    public function inscrire(string $pseudo, string $motDePasse): JoueurSquadro
    {
        $pseudo = trim($pseudo);
        if (preg_match(self::FORMAT_PSEUDO, $pseudo) !== 1) {
            throw new ErreurValidationException('Le pseudo doit contenir 3 à 20 caractères (lettres, chiffres, - ou _).');
        }
        if (mb_strlen($motDePasse) < self::LONGUEUR_MIN_MOT_DE_PASSE) {
            throw new ErreurValidationException(sprintf(
                'Le mot de passe doit contenir au moins %d caractères.',
                self::LONGUEUR_MIN_MOT_DE_PASSE,
            ));
        }

        return $this->joueurs->creer($pseudo, password_hash($motDePasse, PASSWORD_DEFAULT));
    }

    /** @throws ErreurValidationException */
    public function connecter(string $pseudo, string $motDePasse): JoueurSquadro
    {
        $identifiants = $this->joueurs->trouverIdentifiants(trim($pseudo));

        // Message volontairement identique dans les deux cas pour ne pas révéler les pseudos existants.
        if ($identifiants === null || !password_verify($motDePasse, $identifiants[1])) {
            throw new ErreurValidationException('Pseudo ou mot de passe incorrect.');
        }

        [$joueur, $hash] = $identifiants;
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $this->joueurs->changerHash($joueur, password_hash($motDePasse, PASSWORD_DEFAULT));
        }

        return $joueur;
    }
}
