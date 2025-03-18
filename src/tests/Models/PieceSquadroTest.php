<?php

require_once './vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Game\Squadro\Models\PieceSquadro;

class PieceSquadroTest extends TestCase
{
    public function testInitializePiece(): void
    {
        $piece = new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::NORD);
        $this->assertInstanceOf(PieceSquadro::class, $piece);
    }


    public function testCanGetColor(): void
    {
        $piece = new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::NORD);
        $this->assertEquals(PieceSquadro::BLANC, $piece->getCouleur());
    }

    public function testCanGetDirection(): void
    {
        $piece = new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::NORD);
        $this->assertEquals(PieceSquadro::NORD, $piece->getDirection());
    }

    public function testCanInverseDirectionsSudToNord(): void
    {
        $piece = new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::SUD);
        $piece->inverseDirection();
        $this->assertEquals(PieceSquadro::NORD, $piece->getDirection());
    }

    public function testCanInverseDirectionsNordToSud(): void
    {
        $piece = new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::NORD);
        $piece->inverseDirection();
        $this->assertEquals(PieceSquadro::SUD, $piece->getDirection());
    }

    public function testCanInverseDirectionsOuestToEst(): void
    {
        $piece = new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::OUEST);
        $piece->inverseDirection();
        $this->assertEquals(PieceSquadro::EST, $piece->getDirection());
    }

    public function testCanInverseDirectionsEstToOuest(): void
    {
        $piece = new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::EST);
        $piece->inverseDirection();
        $this->assertEquals(PieceSquadro::OUEST, $piece->getDirection());
    }

    public function testCanConvertToString(): void
    {
        $piece = new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::EST);
        $str_piece = $piece->__tostring();
        $this->assertIsString($str_piece);
    }

    public function testCanInitVide(): void
    {
        $piece = new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::EST);
        $init = $piece->initVide();
        $this->assertEquals(PieceSquadro::VIDE, $init->getCouleur());
        $this->assertEquals(PieceSquadro::VIDE, $init->getDirection());
    }

    public function testCanInitNeutre(): void
    {
        $init = PieceSquadro::initNeutre();
        $this->assertEquals(PieceSquadro::NEUTRE, $init->getCouleur());
        $this->assertEquals(PieceSquadro::NEUTRE, $init->getDirection());
    }

    public function testCanInitNoirNord(): void
    {
        $init = PieceSquadro::initNoirNord();
        $this->assertEquals(PieceSquadro::NOIR, $init->getCouleur());
        $this->assertEquals(PieceSquadro::NORD, $init->getDirection());
    }

    public function testCanInitNoirSud(): void
    {
        $init = PieceSquadro::initNoirSud();
        $this->assertEquals(PieceSquadro::NOIR, $init->getCouleur());
        $this->assertEquals(PieceSquadro::SUD, $init->getDirection());
    }

    public function testCanInitBlancEst(): void
    {
        $init = PieceSquadro::initBlancEst();
        $this->assertEquals(PieceSquadro::BLANC, $init->getCouleur());
        $this->assertEquals(PieceSquadro::EST, $init->getDirection());
    }


    public function testCanInitBlancOuest(): void
    {
        $init = PieceSquadro::initBlancOuest();
        $this->assertEquals(PieceSquadro::BLANC, $init->getCouleur());
        $this->assertEquals(PieceSquadro::OUEST, $init->getDirection());
    }


    public function testCanReturnJson(): void
    {
        $piece = new PieceSquadro(PieceSquadro::BLANC, PieceSquadro::EST);
        $this->assertJson($piece->toJson());
    }

    public function testCanCreateCreateFromJson(): void
    {
        $json = json_encode([
            'couleur' => PieceSquadro::BLANC,
            'direction' => PieceSquadro::EST,
        ]);

        $piece = PieceSquadro::fromJson($json);

        $this->assertInstanceOf(PieceSquadro::class , $piece);
        $this->assertEquals(PieceSquadro::BLANC , $piece->getCouleur());
        $this->assertEquals(PieceSquadro::EST , $piece->getDirection());
    }

}