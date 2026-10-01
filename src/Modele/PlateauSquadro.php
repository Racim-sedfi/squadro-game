<?php

declare(strict_types=1);

namespace Squadro\Modele;

use InvalidArgumentException;

/**
 * Plateau 7×7 de Squadro.
 *
 *          col 0   1   2   3   4   5   6
 * ligne 0      .   ↓   ↓   ↓   ↓   ↓   .     ← demi-tour des noirs
 * ligne 1      →   +   +   +   +   +   ←
 *   ...        →   +   +   +   +   +   ←     ← voies des blancs (lignes 1 à 5)
 * ligne 5      →   +   +   +   +   +   ←
 * ligne 6      .   ↑   ↑   ↑   ↑   ↑   .     ← départ des noirs
 *              ↑ départ des blancs     ↑ demi-tour des blancs
 *
 * Chaque pièce circule sur sa propre « voie » : la ligne pour un blanc,
 * la colonne pour un noir. La vitesse dépend de la voie et du sens.
 */
final class PlateauSquadro
{
    public const TAILLE = 7;
    public const VOIES = [1, 2, 3, 4, 5];

    /** Vitesse des blancs à l'aller, indexée par ligne. */
    public const VITESSES_BLANC_ALLER = [0, 1, 3, 2, 3, 1, 0];
    /** Vitesse des blancs au retour, indexée par ligne. */
    public const VITESSES_BLANC_RETOUR = [0, 3, 1, 2, 1, 3, 0];
    /** Vitesse des noirs à l'aller, indexée par colonne. */
    public const VITESSES_NOIR_ALLER = [0, 3, 1, 2, 1, 3, 0];
    /** Vitesse des noirs au retour, indexée par colonne. */
    public const VITESSES_NOIR_RETOUR = [0, 1, 3, 2, 3, 1, 0];

    /** @var array<int, array<int, PieceSquadro|null>> */
    private array $cases = [];

    private function __construct()
    {
        for ($ligne = 0; $ligne < self::TAILLE; $ligne++) {
            $this->cases[$ligne] = array_fill(0, self::TAILLE, null);
        }
    }

    /** Plateau en position de début de partie : 5 pièces par camp sur leur point de départ. */
    public static function initial(): self
    {
        $plateau = new self();
        foreach (self::VOIES as $voie) {
            $plateau->poser(self::pointDeDepart(Couleur::Blanc, $voie), PieceSquadro::blanche());
            $plateau->poser(self::pointDeDepart(Couleur::Noir, $voie), PieceSquadro::noire());
        }

        return $plateau;
    }

    public static function vide(): self
    {
        return new self();
    }

    public function piece(Position $position): ?PieceSquadro
    {
        return $this->cases[$position->ligne][$position->colonne];
    }

    public function estLibre(Position $position): bool
    {
        return $this->piece($position) === null;
    }

    public function poser(Position $position, PieceSquadro $piece): void
    {
        $this->cases[$position->ligne][$position->colonne] = $piece;
    }

    public function retirer(Position $position): void
    {
        $this->cases[$position->ligne][$position->colonne] = null;
    }

    /** @return list<Position> */
    public function positionsDe(Couleur $couleur): array
    {
        $positions = [];
        foreach ($this->cases as $ligne => $colonnes) {
            foreach ($colonnes as $colonne => $piece) {
                if ($piece !== null && $piece->couleur === $couleur) {
                    $positions[] = new Position($ligne, $colonne);
                }
            }
        }

        return $positions;
    }

    /** Vitesse de la pièce située en $position (nombre de cases parcourues par coup). */
    public function vitesse(Position $position): int
    {
        $piece = $this->piece($position) ?? throw new InvalidArgumentException('Aucune pièce sur cette case.');

        return self::vitessePour($piece, self::voie($piece->couleur, $position));
    }

    public static function vitessePour(PieceSquadro $piece, int $voie): int
    {
        return match ([$piece->couleur, $piece->estEnRetour()]) {
            [Couleur::Blanc, false] => self::VITESSES_BLANC_ALLER[$voie],
            [Couleur::Blanc, true] => self::VITESSES_BLANC_RETOUR[$voie],
            [Couleur::Noir, false] => self::VITESSES_NOIR_ALLER[$voie],
            [Couleur::Noir, true] => self::VITESSES_NOIR_RETOUR[$voie],
        };
    }

    /** Numéro de voie d'une pièce : sa ligne pour un blanc, sa colonne pour un noir. */
    public static function voie(Couleur $couleur, Position $position): int
    {
        return $couleur === Couleur::Blanc ? $position->ligne : $position->colonne;
    }

    public static function pointDeDepart(Couleur $couleur, int $voie): Position
    {
        return $couleur === Couleur::Blanc ? new Position($voie, 0) : new Position(6, $voie);
    }

    public static function pointDeDemiTour(Couleur $couleur, int $voie): Position
    {
        return $couleur === Couleur::Blanc ? new Position($voie, 6) : new Position(0, $voie);
    }

    public function copie(): self
    {
        return clone $this; // les pièces sont immuables : une copie superficielle suffit
    }

    /** @return list<array{ligne: int, colonne: int, couleur: string, direction: string}> */
    public function versTableau(): array
    {
        $pieces = [];
        foreach ($this->cases as $ligne => $colonnes) {
            foreach ($colonnes as $colonne => $piece) {
                if ($piece !== null) {
                    $pieces[] = ['ligne' => $ligne, 'colonne' => $colonne] + $piece->versTableau();
                }
            }
        }

        return $pieces;
    }

    /** @param list<array{ligne: int, colonne: int, couleur: string, direction: string}> $pieces */
    public static function depuisTableau(array $pieces): self
    {
        $plateau = new self();
        foreach ($pieces as $donnees) {
            $plateau->poser(Position::depuisTableau($donnees), PieceSquadro::depuisTableau($donnees));
        }

        return $plateau;
    }
}
