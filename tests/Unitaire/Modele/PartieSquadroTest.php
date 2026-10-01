<?php

declare(strict_types=1);

namespace Squadro\Tests\Unitaire\Modele;

use PHPUnit\Framework\TestCase;
use Squadro\Modele\CoupInvalideException;
use Squadro\Modele\Couleur;
use Squadro\Modele\Direction;
use Squadro\Modele\JoueurSquadro;
use Squadro\Modele\PartieSquadro;
use Squadro\Modele\PieceSquadro;
use Squadro\Modele\PlateauSquadro;
use Squadro\Modele\Position;
use Squadro\Modele\StatutPartie;

final class PartieSquadroTest extends TestCase
{
    private JoueurSquadro $alice;
    private JoueurSquadro $bob;

    protected function setUp(): void
    {
        $this->alice = new JoueurSquadro(1, 'alice');
        $this->bob = new JoueurSquadro(2, 'bob');
    }

    private function partieEnCours(): PartieSquadro
    {
        $partie = PartieSquadro::nouvelle($this->alice);
        $partie->rejoindre($this->bob);

        return $partie;
    }

    public function testUneNouvellePartieAttendUnAdversaire(): void
    {
        $partie = PartieSquadro::nouvelle($this->alice);

        self::assertSame(StatutPartie::EnAttente, $partie->statut());
        self::assertSame(Couleur::Blanc, $partie->couleurDe($this->alice));
        self::assertNull($partie->joueurNoir());
    }

    public function testRejoindreDemarreLaPartie(): void
    {
        $partie = $this->partieEnCours();

        self::assertSame(StatutPartie::EnCours, $partie->statut());
        self::assertSame(Couleur::Noir, $partie->couleurDe($this->bob));
        self::assertTrue($partie->estAuTourDe($this->alice));
    }

    public function testOnNePeutPasRejoindreSaProprePartie(): void
    {
        $this->expectException(CoupInvalideException::class);
        PartieSquadro::nouvelle($this->alice)->rejoindre($this->alice);
    }

    public function testUnePartiePleineRefuseUnTroisiemeJoueur(): void
    {
        $this->expectException(CoupInvalideException::class);
        $this->partieEnCours()->rejoindre(new JoueurSquadro(3, 'charlie'));
    }

    public function testLesJoueursJouentChacunLeurTour(): void
    {
        $partie = $this->partieEnCours();

        $partie->jouer($this->alice, new Position(1, 0));

        self::assertSame(Couleur::Noir, $partie->trait());
        self::assertSame(1, $partie->nombreCoups());
        self::assertTrue($partie->estAuTourDe($this->bob));
    }

    public function testJouerHorsDeSonTourEstRefuse(): void
    {
        $this->expectException(CoupInvalideException::class);
        $this->expectExceptionMessage('Ce n\'est pas votre tour.');
        $this->partieEnCours()->jouer($this->bob, new Position(6, 1));
    }

    public function testUnSpectateurNePeutPasJouer(): void
    {
        $this->expectException(CoupInvalideException::class);
        $this->partieEnCours()->jouer(new JoueurSquadro(3, 'charlie'), new Position(1, 0));
    }

    public function testOnNePeutPasJouerAvantLArriveeDeLAdversaire(): void
    {
        $this->expectException(CoupInvalideException::class);
        PartieSquadro::nouvelle($this->alice)->jouer($this->alice, new Position(1, 0));
    }

    public function testLaQuatriemePieceRameneeDonneLaVictoire(): void
    {
        $plateau = PlateauSquadro::vide();
        $plateau->poser(new Position(1, 1), new PieceSquadro(Couleur::Blanc, Direction::Ouest));
        $plateau->poser(new Position(6, 1), PieceSquadro::noire());

        $partie = PartieSquadro::reconstituer(10, $this->alice, $this->bob, StatutPartie::EnCours, 5, [
            'trait' => 'blanc',
            'plateau' => $plateau->versTableau(),
            'scores' => ['blanc' => 3, 'noir' => 2],
        ]);

        $resultat = $partie->jouer($this->alice, new Position(1, 1));

        self::assertTrue($resultat->pieceArrivee);
        self::assertSame(4, $partie->score(Couleur::Blanc));
        self::assertSame(StatutPartie::Terminee, $partie->statut());
        self::assertSame(Couleur::Blanc, $partie->gagnant());
        self::assertFalse($partie->estAuTourDe($this->bob));
    }

    public function testCoupsPossiblesDonneLaDestinationDeChaquePiece(): void
    {
        $partie = $this->partieEnCours();

        $coups = $partie->coupsPossibles($this->alice);

        self::assertCount(5, $coups);
        self::assertTrue($coups['2,0']->equals(new Position(2, 3)));
        self::assertSame([], $partie->coupsPossibles($this->bob));
    }

    public function testEtatSerialiseEtReconstitue(): void
    {
        $partie = $this->partieEnCours();
        $partie->jouer($this->alice, new Position(2, 0));
        $partie->jouer($this->bob, new Position(6, 3));

        $json = json_encode($partie->etatVersTableau(), JSON_THROW_ON_ERROR);
        $copie = PartieSquadro::reconstituer(1, $this->alice, $this->bob, StatutPartie::EnCours, 2, json_decode($json, true));

        self::assertEquals($partie->plateau(), $copie->plateau());
        self::assertSame($partie->trait(), $copie->trait());
        self::assertSame(2, $copie->nombreCoups());
        self::assertEquals($partie->dernierCoup(), $copie->dernierCoup());
    }
}
