<?php

declare(strict_types=1);

namespace Squadro\Tests\Unitaire\Modele;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Squadro\Modele\Couleur;
use Squadro\Modele\Direction;
use Squadro\Modele\PieceSquadro;
use Squadro\Modele\PlateauSquadro;
use Squadro\Modele\Position;

final class PlateauSquadroTest extends TestCase
{
    public function testLePlateauInitialPlaceCinqPiecesParCamp(): void
    {
        $plateau = PlateauSquadro::initial();

        self::assertCount(5, $plateau->positionsDe(Couleur::Blanc));
        self::assertCount(5, $plateau->positionsDe(Couleur::Noir));

        foreach (PlateauSquadro::VOIES as $voie) {
            self::assertSame(Couleur::Blanc, $plateau->piece(new Position($voie, 0))?->couleur);
            self::assertSame(Couleur::Noir, $plateau->piece(new Position(6, $voie))?->couleur);
        }
    }

    public function testLesCoinsSontVides(): void
    {
        $plateau = PlateauSquadro::initial();

        foreach ([[0, 0], [0, 6], [6, 0], [6, 6]] as [$ligne, $colonne]) {
            self::assertTrue($plateau->estLibre(new Position($ligne, $colonne)));
        }
    }

    public function testLaVitesseDependDeLaVoieEtDuSens(): void
    {
        $plateau = PlateauSquadro::vide();
        $plateau->poser(new Position(1, 0), PieceSquadro::blanche());
        $plateau->poser(new Position(1, 6), new PieceSquadro(Couleur::Blanc, Direction::Ouest));
        $plateau->poser(new Position(6, 2), PieceSquadro::noire());

        self::assertSame(1, $plateau->vitesse(new Position(1, 0)));
        self::assertSame(3, $plateau->vitesse(new Position(1, 6)));
        self::assertSame(1, $plateau->vitesse(new Position(6, 2)));
    }

    public function testAllerPlusRetourValentToujoursQuatre(): void
    {
        foreach (PlateauSquadro::VOIES as $voie) {
            self::assertSame(4, PlateauSquadro::VITESSES_BLANC_ALLER[$voie] + PlateauSquadro::VITESSES_BLANC_RETOUR[$voie]);
            self::assertSame(4, PlateauSquadro::VITESSES_NOIR_ALLER[$voie] + PlateauSquadro::VITESSES_NOIR_RETOUR[$voie]);
        }
    }

    public function testSerialisationAllerRetour(): void
    {
        $plateau = PlateauSquadro::initial();
        $plateau->retirer(new Position(3, 0));
        $plateau->poser(new Position(3, 6), new PieceSquadro(Couleur::Blanc, Direction::Ouest));

        $copie = PlateauSquadro::depuisTableau(json_decode(json_encode($plateau->versTableau()), true));

        self::assertEquals($plateau, $copie);
    }

    public function testUnePositionHorsPlateauEstRefusee(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Position(7, 0);
    }

    public function testUnePieceNePeutPasAllerDansUneDirectionEtrangereASaVoie(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PieceSquadro(Couleur::Blanc, Direction::Nord);
    }
}
