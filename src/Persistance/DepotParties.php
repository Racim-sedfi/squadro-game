<?php

declare(strict_types=1);

namespace Squadro\Persistance;

use PDO;
use Squadro\Modele\JoueurSquadro;
use Squadro\Modele\PartieSquadro;
use Squadro\Modele\StatutPartie;

/**
 * Accès aux parties en base (pattern Repository).
 *
 * L'état de jeu est stocké en JSON (et non via serialize()/unserialize(),
 * qui exposerait l'application à l'injection d'objets PHP).
 * Les écritures utilisent un verrouillage optimiste sur la colonne `version`.
 */
final class DepotParties
{
    private const SELECTION = <<<'SQL'
        SELECT p.id, p.statut, p.etat, p.version,
               jb.id AS blanc_id, jb.pseudo AS blanc_pseudo,
               jn.id AS noir_id,  jn.pseudo AS noir_pseudo
        FROM parties p
        JOIN joueurs jb ON jb.id = p.joueur_blanc_id
        LEFT JOIN joueurs jn ON jn.id = p.joueur_noir_id
        SQL;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function ajouter(PartieSquadro $partie): void
    {
        $requete = $this->pdo->prepare(
            'INSERT INTO parties (joueur_blanc_id, joueur_noir_id, statut, etat, version)
             VALUES (:blanc, :noir, :statut, :etat, :version)'
        );
        $requete->execute([
            'blanc' => $partie->joueurBlanc()->id,
            'noir' => $partie->joueurNoir()?->id,
            'statut' => $partie->statut()->value,
            'etat' => json_encode($partie->etatVersTableau(), JSON_THROW_ON_ERROR),
            'version' => $partie->version(),
        ]);

        $partie->attribuerId((int) $this->pdo->lastInsertId());
    }

    /** @throws ConflitDeVersionException si la partie a été modifiée entre-temps */
    public function enregistrer(PartieSquadro $partie): void
    {
        $requete = $this->pdo->prepare(
            'UPDATE parties
             SET joueur_noir_id = :noir, statut = :statut, etat = :etat,
                 version = version + 1, mis_a_jour_le = CURRENT_TIMESTAMP
             WHERE id = :id AND version = :version'
        );
        $requete->execute([
            'noir' => $partie->joueurNoir()?->id,
            'statut' => $partie->statut()->value,
            'etat' => json_encode($partie->etatVersTableau(), JSON_THROW_ON_ERROR),
            'id' => $partie->id(),
            'version' => $partie->version(),
        ]);

        if ($requete->rowCount() !== 1) {
            throw new ConflitDeVersionException('La partie a été modifiée par une autre action. Rechargez la page.');
        }

        $partie->incrementerVersion();
    }

    public function trouver(int $id): ?PartieSquadro
    {
        $requete = $this->pdo->prepare(self::SELECTION . ' WHERE p.id = :id');
        $requete->execute(['id' => $id]);
        $ligne = $requete->fetch();

        return $ligne ? $this->hydrater($ligne) : null;
    }

    /** Version courante d'une partie (utilisée par le rafraîchissement automatique). */
    public function version(int $id): ?int
    {
        $requete = $this->pdo->prepare('SELECT version FROM parties WHERE id = :id');
        $requete->execute(['id' => $id]);
        $version = $requete->fetchColumn();

        return $version === false ? null : (int) $version;
    }

    /** @return list<PartieSquadro> */
    public function listerPourJoueur(JoueurSquadro $joueur): array
    {
        $requete = $this->pdo->prepare(
            self::SELECTION . ' WHERE p.joueur_blanc_id = :id1 OR p.joueur_noir_id = :id2
            ORDER BY p.mis_a_jour_le DESC, p.id DESC'
        );
        $requete->execute(['id1' => $joueur->id, 'id2' => $joueur->id]);

        return array_values(array_map($this->hydrater(...), $requete->fetchAll()));
    }

    /** @return list<PartieSquadro> parties en attente d'adversaire créées par d'autres joueurs */
    public function listerOuvertes(JoueurSquadro $saufJoueur): array
    {
        $requete = $this->pdo->prepare(
            self::SELECTION . ' WHERE p.statut = :statut AND p.joueur_blanc_id <> :id ORDER BY p.cree_le DESC, p.id DESC'
        );
        $requete->execute(['statut' => StatutPartie::EnAttente->value, 'id' => $saufJoueur->id]);

        return array_values(array_map($this->hydrater(...), $requete->fetchAll()));
    }

    /** @param array<string, mixed> $ligne */
    private function hydrater(array $ligne): PartieSquadro
    {
        return PartieSquadro::reconstituer(
            (int) $ligne['id'],
            new JoueurSquadro((int) $ligne['blanc_id'], $ligne['blanc_pseudo']),
            $ligne['noir_id'] !== null ? new JoueurSquadro((int) $ligne['noir_id'], $ligne['noir_pseudo']) : null,
            StatutPartie::from($ligne['statut']),
            (int) $ligne['version'],
            json_decode($ligne['etat'], true, 512, JSON_THROW_ON_ERROR),
        );
    }
}
