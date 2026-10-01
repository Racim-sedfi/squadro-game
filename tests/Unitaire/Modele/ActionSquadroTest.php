<?php

declare(strict_types=1);

namespace Squadro\Tests\Unitaire\Modele;

use PHPUnit\Framework\TestCase;
use Squadro\Modele\ActionSquadro;
use Squadro\Modele\CoupInvalideException;
use Squadro\Modele\Couleur;
use Squadro\Modele\Direction;
use Squadro\Modele\PieceSquadro;
use Squadro\Modele\PlateauSquadro;
use Squadro\Modele\Position;

final class ActionSquadroTest extends TestCase
{
    private PlateauSquadro $plateau;
    private ActionSquadro $action;

    protected function setUp(): void
    {
        $this->plateau = PlateauSquadro::vide();
        $this->action = new ActionSquadro($this->plateau);
    }

    private function poser(int $ligne, int $colonne, Couleur $couleur, Direction $direction): void
    {
        $this->plateau->poser(new Position($ligne, $colonne), new PieceSquadro($couleur, $direction));
    }

    private function assertPiece(int $ligne, int $colonne, Couleur $couleur, Direction $direction): void
    {
        $piece = $this->plateau->piece(new Position($ligne, $colonne));
        self::assertNotNull($piece, "Une pièce était attendue en ($ligne, $colonne).");
        self::assertSame($couleur, $piece->couleur);
        self::assertSame($direction, $piece->direction);
    }

    private function assertLibre(int $ligne, int $colonne): void
    {
        self::assertTrue($this->plateau->estLibre(new Position($ligne, $colonne)), "La case ($ligne, $colonne) devrait être libre.");
    }

    public function testUnBlancAvanceDeSaVitesseALAller(): void
    {
        $this->poser(2, 0, Couleur::Blanc, Direction::Est); // vitesse aller ligne 2 = 3

        $resultat = $this->action->jouer(Couleur::Blanc, new Position(2, 0));

        self::assertTrue($resultat->arrivee->equals(new Position(2, 3)));
        $this->assertPiece(2, 3, Couleur::Blanc, Direction::Est);
        $this->assertLibre(2, 0);
    }

    public function testUnNoirAvanceVersLeNord(): void
    {
        $this->poser(6, 1, Couleur::Noir, Direction::Nord); // vitesse aller colonne 1 = 3

        $this->action->jouer(Couleur::Noir, new Position(6, 1));

        $this->assertPiece(3, 1, Couleur::Noir, Direction::Nord);
    }

    public function testLaPieceSArreteAuBordEtFaitDemiTour(): void
    {
        $this->poser(2, 5, Couleur::Blanc, Direction::Est); // vitesse 3 mais une seule case avant le bord

        $resultat = $this->action->jouer(Couleur::Blanc, new Position(2, 5));

        self::assertTrue($resultat->demiTour);
        $this->assertPiece(2, 6, Couleur::Blanc, Direction::Ouest);
    }

    public function testLaVitesseDeRetourEstUtiliseeApresLeDemiTour(): void
    {
        $this->poser(1, 6, Couleur::Blanc, Direction::Ouest); // vitesse retour ligne 1 = 3

        $this->action->jouer(Couleur::Blanc, new Position(1, 6));

        $this->assertPiece(1, 3, Couleur::Blanc, Direction::Ouest);
    }

    public function testSauterUnePieceAdverseLaRenvoieASonDepart(): void
    {
        $this->poser(3, 0, Couleur::Blanc, Direction::Est);  // vitesse 2
        $this->poser(3, 1, Couleur::Noir, Direction::Nord);  // noir à l'aller sur la colonne 1

        $resultat = $this->action->jouer(Couleur::Blanc, new Position(3, 0));

        $this->assertPiece(3, 2, Couleur::Blanc, Direction::Est);
        $this->assertPiece(6, 1, Couleur::Noir, Direction::Nord);
        $this->assertLibre(3, 1);
        self::assertCount(1, $resultat->piecesSautees);
    }

    public function testLeSautTermineLeDeplacementMemeSIlResteDuMouvement(): void
    {
        $this->poser(2, 0, Couleur::Blanc, Direction::Est);  // vitesse 3
        $this->poser(2, 1, Couleur::Noir, Direction::Nord);

        $this->action->jouer(Couleur::Blanc, new Position(2, 0));

        $this->assertPiece(2, 2, Couleur::Blanc, Direction::Est);
        $this->assertLibre(2, 3);
    }

    public function testSauterPlusieursPiecesConsecutives(): void
    {
        $this->poser(3, 0, Couleur::Blanc, Direction::Est);
        $this->poser(3, 1, Couleur::Noir, Direction::Nord);
        $this->poser(3, 2, Couleur::Noir, Direction::Nord);

        $resultat = $this->action->jouer(Couleur::Blanc, new Position(3, 0));

        $this->assertPiece(3, 3, Couleur::Blanc, Direction::Est);
        $this->assertPiece(6, 1, Couleur::Noir, Direction::Nord);
        $this->assertPiece(6, 2, Couleur::Noir, Direction::Nord);
        self::assertCount(2, $resultat->piecesSautees);
    }

    public function testAvancerPuisSauter(): void
    {
        $this->poser(2, 0, Couleur::Blanc, Direction::Est);  // vitesse 3
        $this->poser(2, 2, Couleur::Noir, Direction::Nord);

        $this->action->jouer(Couleur::Blanc, new Position(2, 0));

        $this->assertPiece(2, 3, Couleur::Blanc, Direction::Est);
        $this->assertPiece(6, 2, Couleur::Noir, Direction::Nord);
    }

    public function testUnePieceSauteeAuRetourRepartDuPointDeDemiTour(): void
    {
        $this->poser(3, 0, Couleur::Blanc, Direction::Est);
        $this->poser(3, 1, Couleur::Noir, Direction::Sud);   // noir au retour

        $this->action->jouer(Couleur::Blanc, new Position(3, 0));

        $this->assertPiece(0, 1, Couleur::Noir, Direction::Sud);
    }

    public function testUnSautPeutAmenerAuPointDeDemiTour(): void
    {
        $this->poser(1, 4, Couleur::Blanc, Direction::Est);
        $this->poser(1, 5, Couleur::Noir, Direction::Nord);

        $resultat = $this->action->jouer(Couleur::Blanc, new Position(1, 4));

        self::assertTrue($resultat->demiTour);
        $this->assertPiece(1, 6, Couleur::Blanc, Direction::Ouest);
    }

    public function testUnePieceRevenueASonDepartSortDuPlateau(): void
    {
        $this->poser(1, 2, Couleur::Blanc, Direction::Ouest); // vitesse retour 3, s'arrête au bord après 2 cases

        $resultat = $this->action->jouer(Couleur::Blanc, new Position(1, 2));

        self::assertTrue($resultat->pieceArrivee);
        self::assertTrue($resultat->arrivee->equals(new Position(1, 0)));
        $this->assertLibre(1, 0);
        self::assertSame([], $this->plateau->positionsDe(Couleur::Blanc));
    }

    public function testUnNoirSauteUnBlancQuiCroiseSaColonne(): void
    {
        // Une pièce noire sur la voie d'un noir ne peut pas exister ; on vérifie
        // qu'un noir ne saute que les blancs qui croisent sa colonne.
        $this->poser(6, 3, Couleur::Noir, Direction::Nord);  // vitesse 2
        $this->poser(5, 3, Couleur::Blanc, Direction::Est);

        $this->action->jouer(Couleur::Noir, new Position(6, 3));

        $this->assertPiece(4, 3, Couleur::Noir, Direction::Nord);
        $this->assertPiece(5, 0, Couleur::Blanc, Direction::Est);
    }

    public function testSimulerNeModifiePasLePlateau(): void
    {
        $this->poser(3, 0, Couleur::Blanc, Direction::Est);
        $this->poser(3, 1, Couleur::Noir, Direction::Nord);

        $simulation = $this->action->simuler(new Position(3, 0));

        self::assertTrue($simulation['arrivee']->equals(new Position(3, 2)));
        $this->assertPiece(3, 0, Couleur::Blanc, Direction::Est);
        $this->assertPiece(3, 1, Couleur::Noir, Direction::Nord);
    }

    public function testImpossibleDeJouerUneCaseVide(): void
    {
        $this->expectException(CoupInvalideException::class);
        $this->action->jouer(Couleur::Blanc, new Position(3, 3));
    }

    public function testImpossibleDeJouerUnePieceAdverse(): void
    {
        $this->poser(6, 1, Couleur::Noir, Direction::Nord);

        $this->expectException(CoupInvalideException::class);
        $this->action->jouer(Couleur::Blanc, new Position(6, 1));
    }
}
