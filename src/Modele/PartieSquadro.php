<?php

declare(strict_types=1);

namespace Squadro\Modele;

/**
 * Agrégat « partie » : joueurs, plateau, tour de jeu, scores et vainqueur.
 *
 * Toutes les règles de déroulement (qui peut jouer, quand la partie se termine)
 * sont vérifiées ici, côté serveur : l'interface ne fait qu'afficher.
 */
final class PartieSquadro
{
    /** Nombre de pièces à ramener pour gagner (règle officielle : 4 sur 5). */
    public const PIECES_POUR_GAGNER = 4;

    /** @var array{blanc: int, noir: int} */
    private array $scores = ['blanc' => 0, 'noir' => 0];

    private function __construct(
        private ?int $id,
        private JoueurSquadro $joueurBlanc,
        private ?JoueurSquadro $joueurNoir,
        private StatutPartie $statut,
        private Couleur $trait,
        private PlateauSquadro $plateau,
        private ?Couleur $gagnant = null,
        private ?ResultatCoup $dernierCoup = null,
        private int $nombreCoups = 0,
        private int $version = 0,
    ) {
    }

    /** Crée une partie : son créateur joue les blancs, qui commencent. */
    public static function nouvelle(JoueurSquadro $createur): self
    {
        return new self(null, $createur, null, StatutPartie::EnAttente, Couleur::Blanc, PlateauSquadro::initial());
    }

    public function rejoindre(JoueurSquadro $joueur): void
    {
        if ($this->statut !== StatutPartie::EnAttente) {
            throw new CoupInvalideException('Cette partie n\'accepte plus de nouveaux joueurs.');
        }
        if ($joueur->equals($this->joueurBlanc)) {
            throw new CoupInvalideException('Vous ne pouvez pas rejoindre votre propre partie.');
        }

        $this->joueurNoir = $joueur;
        $this->statut = StatutPartie::EnCours;
    }

    public function jouer(JoueurSquadro $joueur, Position $depart): ResultatCoup
    {
        if ($this->statut !== StatutPartie::EnCours) {
            throw new CoupInvalideException('La partie n\'est pas en cours.');
        }
        $couleur = $this->couleurDe($joueur) ?? throw new CoupInvalideException('Vous ne participez pas à cette partie.');
        if ($couleur !== $this->trait) {
            throw new CoupInvalideException('Ce n\'est pas votre tour.');
        }

        $resultat = (new ActionSquadro($this->plateau))->jouer($couleur, $depart);

        if ($resultat->pieceArrivee) {
            $this->scores[$couleur->value]++;
        }

        $this->dernierCoup = $resultat;
        $this->nombreCoups++;

        if ($this->scores[$couleur->value] >= self::PIECES_POUR_GAGNER) {
            $this->gagnant = $couleur;
            $this->statut = StatutPartie::Terminee;
        } else {
            $this->trait = $couleur->adversaire();
        }

        return $resultat;
    }

    /**
     * Pour chaque pièce que $joueur peut jouer maintenant, la case où elle arriverait.
     *
     * @return array<string, Position> clé = Position::cle() de la pièce
     */
    public function coupsPossibles(JoueurSquadro $joueur): array
    {
        if (!$this->estAuTourDe($joueur)) {
            return [];
        }

        $action = new ActionSquadro($this->plateau);
        $coups = [];
        foreach ($this->plateau->positionsDe($this->trait) as $position) {
            $coups[$position->cle()] = $action->simuler($position)['arrivee'];
        }

        return $coups;
    }

    public function estAuTourDe(JoueurSquadro $joueur): bool
    {
        return $this->statut === StatutPartie::EnCours && $this->couleurDe($joueur) === $this->trait;
    }

    public function couleurDe(JoueurSquadro $joueur): ?Couleur
    {
        return match (true) {
            $joueur->equals($this->joueurBlanc) => Couleur::Blanc,
            $this->joueurNoir !== null && $joueur->equals($this->joueurNoir) => Couleur::Noir,
            default => null,
        };
    }

    public function joueur(Couleur $couleur): ?JoueurSquadro
    {
        return $couleur === Couleur::Blanc ? $this->joueurBlanc : $this->joueurNoir;
    }

    public function participe(JoueurSquadro $joueur): bool
    {
        return $this->couleurDe($joueur) !== null;
    }

    public function score(Couleur $couleur): int
    {
        return $this->scores[$couleur->value];
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function attribuerId(int $id): void
    {
        $this->id ??= $id;
    }

    public function joueurBlanc(): JoueurSquadro
    {
        return $this->joueurBlanc;
    }

    public function joueurNoir(): ?JoueurSquadro
    {
        return $this->joueurNoir;
    }

    public function statut(): StatutPartie
    {
        return $this->statut;
    }

    public function trait(): Couleur
    {
        return $this->trait;
    }

    public function plateau(): PlateauSquadro
    {
        return $this->plateau;
    }

    public function gagnant(): ?Couleur
    {
        return $this->gagnant;
    }

    public function dernierCoup(): ?ResultatCoup
    {
        return $this->dernierCoup;
    }

    public function nombreCoups(): int
    {
        return $this->nombreCoups;
    }

    /** Numéro de version, utilisé pour le verrouillage optimiste en base. */
    public function version(): int
    {
        return $this->version;
    }

    public function incrementerVersion(): void
    {
        $this->version++;
    }

    /**
     * État de jeu sérialisable en JSON (les joueurs, le statut et la version
     * sont stockés dans des colonnes dédiées).
     *
     * @return array<string, mixed>
     */
    public function etatVersTableau(): array
    {
        return [
            'trait' => $this->trait->value,
            'plateau' => $this->plateau->versTableau(),
            'scores' => $this->scores,
            'gagnant' => $this->gagnant?->value,
            'dernierCoup' => $this->dernierCoup?->versTableau(),
            'nombreCoups' => $this->nombreCoups,
        ];
    }

    /** @param array<string, mixed> $etat */
    public static function reconstituer(
        int $id,
        JoueurSquadro $joueurBlanc,
        ?JoueurSquadro $joueurNoir,
        StatutPartie $statut,
        int $version,
        array $etat,
    ): self {
        $partie = new self(
            $id,
            $joueurBlanc,
            $joueurNoir,
            $statut,
            Couleur::from($etat['trait']),
            PlateauSquadro::depuisTableau($etat['plateau']),
            isset($etat['gagnant']) ? Couleur::from($etat['gagnant']) : null,
            isset($etat['dernierCoup']) ? ResultatCoup::depuisTableau($etat['dernierCoup']) : null,
            (int) ($etat['nombreCoups'] ?? 0),
            $version,
        );
        $partie->scores = [
            'blanc' => (int) ($etat['scores']['blanc'] ?? 0),
            'noir' => (int) ($etat['scores']['noir'] ?? 0),
        ];

        return $partie;
    }
}
