<?php

require_once './vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Game\Squadro\Models\ArrayPieceSquadro;
use Game\Squadro\Models\PieceSquadro;

class ArrayPieceSquadroTest extends TestCase
{

    /**
     * Summary of testAddPiece
     * @return void
     */
    public function testAddPiece(): void{
        $array_pieces = new ArrayPieceSquadro();
        for ($i=0; $i < 10 ; $i++){
            $piece = PieceSquadro::initBlancEst();
            $array_pieces->add($piece);
        }

        $this->assertEquals($i, $array_pieces->count());
    }

    /**
     * Summary of testRemovePiece
     * @return void
     */
    public function testRemovePiece(): void{
        $array_pieces = new ArrayPieceSquadro();
        for ($i=0; $i < 10 ; $i++){
            $piece = PieceSquadro::initBlancEst();
            $array_pieces->add($piece);
        }

        $array_pieces->remove(2);

        $this->assertEquals($i-1, $array_pieces->count());
    }

    /**
     * Summary of testOffsetSetTrue
     * @return void
     */
    public function testOffsetSetTrue(): void{
        $array_pieces = new ArrayPieceSquadro();
        $piece = PieceSquadro::initBlancEst();
        $array_pieces->add($piece);

        $this->assertTrue($array_pieces->offsetExists(0));
    }

    /**
     * Summary of testOffsetSetFalse
     * @return void
     */
    public function testOffsetSetFalse(): void{
        $array_pieces = new ArrayPieceSquadro();
        $piece = PieceSquadro::initBlancEst();
        $array_pieces->add($piece);

        $this->assertFalse($array_pieces->offsetExists(1));
    }

    /**
     * Summary of testOffsetUnset
     * @return void
     */
    public function testOffsetUnset(): void{
        $array_pieces = new ArrayPieceSquadro();
        $array_pieces->offsetUnset(0);

        $this->assertFalse(false);
    }

    /**
     * Summary of testOffsetGet
     * @return void
     */
    public function testOffsetGet(): void{
        $array_pieces = new ArrayPieceSquadro();
        $pieceBlancEst = PieceSquadro::initBlancEst();
        $pieceNoirNord = PieceSquadro::initNoirNord();

        $array_pieces->add($pieceBlancEst);
        $array_pieces->add($pieceNoirNord);

        $this->assertSame($pieceBlancEst, $array_pieces->offsetGet(0));
        $this->assertSame($pieceNoirNord, $array_pieces->offsetGet(1));
    }

    public function testOffsetSet(): void{
        $array_pieces = new ArrayPieceSquadro();
        $pieceBlancEst = PieceSquadro::initBlancEst();
        $pieceNoirNord = PieceSquadro::initNoirNord();

        $array_pieces->offsetSet(0,$pieceBlancEst);
        $array_pieces->offsetSet(null, $pieceNoirNord);
        $this->assertEquals($pieceBlancEst, $array_pieces->offsetGet(0));
        $this->assertEquals($pieceNoirNord, $array_pieces->offsetGet(1));
    }
}