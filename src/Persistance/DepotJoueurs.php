<?php

declare(strict_types=1);

namespace Squadro\Persistance;

use PDO;
use PDOException;
use Squadro\Modele\JoueurSquadro;

/**
 * Accès aux joueurs en base (pattern Repository).
 */
final class DepotJoueurs
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @throws PseudoDejaPrisException */
    public function creer(string $pseudo, string $hashMotDePasse): JoueurSquadro
    {
        $requete = $this->pdo->prepare('INSERT INTO joueurs (pseudo, mot_de_passe) VALUES (:pseudo, :mot_de_passe)');

        try {
            $requete->execute(['pseudo' => $pseudo, 'mot_de_passe' => $hashMotDePasse]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { // violation de contrainte d'unicité
                throw new PseudoDejaPrisException($pseudo, $e);
            }
            throw $e;
        }

        return new JoueurSquadro((int) $this->pdo->lastInsertId(), $pseudo);
    }

    public function trouverParId(int $id): ?JoueurSquadro
    {
        $requete = $this->pdo->prepare('SELECT id, pseudo FROM joueurs WHERE id = :id');
        $requete->execute(['id' => $id]);
        $ligne = $requete->fetch();

        return $ligne ? new JoueurSquadro((int) $ligne['id'], $ligne['pseudo']) : null;
    }

    /**
     * Retourne le joueur et le hash de son mot de passe, pour l'authentification.
     *
     * @return array{0: JoueurSquadro, 1: string}|null
     */
    public function trouverIdentifiants(string $pseudo): ?array
    {
        $requete = $this->pdo->prepare('SELECT id, pseudo, mot_de_passe FROM joueurs WHERE pseudo = :pseudo');
        $requete->execute(['pseudo' => $pseudo]);
        $ligne = $requete->fetch();

        return $ligne ? [new JoueurSquadro((int) $ligne['id'], $ligne['pseudo']), $ligne['mot_de_passe']] : null;
    }

    public function changerHash(JoueurSquadro $joueur, string $hash): void
    {
        $this->pdo->prepare('UPDATE joueurs SET mot_de_passe = :hash WHERE id = :id')
            ->execute(['hash' => $hash, 'id' => $joueur->id]);
    }
}
