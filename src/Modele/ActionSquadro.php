<?php

declare(strict_types=1);

namespace Squadro\Modele;

/**
 * Moteur de règles : calcule et applique le déplacement d'une pièce.
 *
 * Règles implémentées (règles officielles de Squadro) :
 *  1. Une pièce avance du nombre de cases indiqué par sa vitesse (1, 2 ou 3),
 *     qui dépend de sa voie et du sens (aller / retour).
 *  2. Elle s'arrête au bord du plateau même s'il lui reste du mouvement :
 *     au point de demi-tour elle fait demi-tour, à son point de départ elle a terminé.
 *  3. Si elle rencontre une ou plusieurs pièces adverses consécutives, elle les saute,
 *     se pose sur la première case libre derrière et son déplacement s'arrête.
 *  4. Chaque pièce sautée repart au début de son trajet en cours :
 *     point de départ si elle était à l'aller, point de demi-tour si elle était au retour.
 *  5. Une pièce revenue à son point de départ sort du plateau et rapporte un point.
 */
final class ActionSquadro
{
    public function __construct(private readonly PlateauSquadro $plateau)
    {
    }

    /** Indique si la pièce en $position peut être jouée par $joueur. */
    public function estJouable(Couleur $joueur, Position $position): bool
    {
        return $this->plateau->piece($position)?->couleur === $joueur;
    }

    /**
     * Calcule la case d'arrivée et les pièces sautées, sans modifier le plateau.
     *
     * @return array{arrivee: Position, sautees: list<Position>}
     */
    public function simuler(Position $depart): array
    {
        $piece = $this->plateau->piece($depart) ?? throw new CoupInvalideException('Il n\'y a aucune pièce sur cette case.');
        $restant = $this->plateau->vitesse($depart);
        $position = $depart;
        $sautees = [];

        while ($restant > 0) {
            $suivante = $position->voisine($piece->direction);
            if ($suivante === null) {
                break;
            }

            if ($this->estAdverse($suivante, $piece->couleur)) {
                // Saut d'une ou plusieurs pièces adverses consécutives, puis arrêt.
                while ($suivante !== null && $this->estAdverse($suivante, $piece->couleur)) {
                    $sautees[] = $suivante;
                    $suivante = $suivante->voisine($piece->direction);
                }
                // Les pièces adverses n'occupent jamais les bords d'une voie : il reste toujours une case.
                $position = $suivante ?? $position;
                break;
            }

            $position = $suivante;
            $restant--;

            if ($this->estBordDeVoie($piece, $position)) {
                break;
            }
        }

        return ['arrivee' => $position, 'sautees' => $sautees];
    }

    /** Joue la pièce située en $depart pour le camp $joueur et modifie le plateau. */
    public function jouer(Couleur $joueur, Position $depart): ResultatCoup
    {
        $piece = $this->plateau->piece($depart);
        if ($piece === null) {
            throw new CoupInvalideException('Il n\'y a aucune pièce sur cette case.');
        }
        if ($piece->couleur !== $joueur) {
            throw new CoupInvalideException('Cette pièce appartient à votre adversaire.');
        }

        ['arrivee' => $arrivee, 'sautees' => $sautees] = $this->simuler($depart);

        foreach ($sautees as $positionSautee) {
            $this->renvoyer($positionSautee);
        }

        $this->plateau->retirer($depart);
        $demiTour = false;
        $terminee = false;

        if (!$piece->estEnRetour() && $this->estPointDeDemiTour($piece, $arrivee)) {
            $piece = $piece->faireDemiTour();
            $demiTour = true;
        } elseif ($piece->estEnRetour() && $this->estPointDeDepart($piece, $arrivee)) {
            $terminee = true;
        }

        if (!$terminee) {
            $this->plateau->poser($arrivee, $piece);
        }

        return new ResultatCoup($joueur, $depart, $arrivee, $sautees, $demiTour, $terminee);
    }

    /** Renvoie une pièce sautée au début de son trajet en cours. */
    private function renvoyer(Position $position): void
    {
        $piece = $this->plateau->piece($position);
        if ($piece === null) {
            return;
        }

        $voie = PlateauSquadro::voie($piece->couleur, $position);
        $destination = $piece->estEnRetour()
            ? PlateauSquadro::pointDeDemiTour($piece->couleur, $voie)
            : PlateauSquadro::pointDeDepart($piece->couleur, $voie);

        $this->plateau->retirer($position);
        $this->plateau->poser($destination, $piece);
    }

    private function estAdverse(Position $position, Couleur $couleur): bool
    {
        $occupant = $this->plateau->piece($position);

        return $occupant !== null && $occupant->couleur !== $couleur;
    }

    private function estBordDeVoie(PieceSquadro $piece, Position $position): bool
    {
        return $this->estPointDeDemiTour($piece, $position) || $this->estPointDeDepart($piece, $position);
    }

    private function estPointDeDemiTour(PieceSquadro $piece, Position $position): bool
    {
        $voie = PlateauSquadro::voie($piece->couleur, $position);

        return $position->equals(PlateauSquadro::pointDeDemiTour($piece->couleur, $voie));
    }

    private function estPointDeDepart(PieceSquadro $piece, Position $position): bool
    {
        $voie = PlateauSquadro::voie($piece->couleur, $position);

        return $position->equals(PlateauSquadro::pointDeDepart($piece->couleur, $voie));
    }
}
