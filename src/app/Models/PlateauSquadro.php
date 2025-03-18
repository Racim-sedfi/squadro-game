<?php

namespace Game\Squadro\Models;
use Game\Squadro\Models\PieceSquadro;
class PlateauSquadro
{

    public const BLANC_V_ALLER = [0, 1, 3, 2, 3, 1, 0];
    public const BLANC_V_RETOUR = [0, 3, 1, 2, 1, 3, 0];
    public const NOIR_V_ALLER = [0, 3, 1, 2, 1, 3, 0];
    public const NOIR_V_RETOUR = [0, 1, 3, 2, 3, 1, 0];

    private array $plateau;
    private array $lignesJouables = [1, 2, 3, 4, 5];
    private array $colonnesJouables = [1, 2, 3, 4, 5];

    /**
     * Summary of __construct
     */
    public function __construct()
    {
        $this->initCasesVides();
        $this->initCasesNeutres();
        $this->initCasesBlanches();
        $this->initCasesNoires();
    }

    /**
     * Summary of initCasesVides
     * @return void
     */
    private function initCasesVides(): void
    {
        for ($i = 0; $i <= 6; $i++) {
            $array = new ArrayPieceSquadro;
            for($j= 0; $j <= 6 ; $j++){
                $array->offsetSet($j, PieceSquadro::initVide());
            }
            $this->plateau[$i] = $array;
        }
    }

    /**
     * Summary of initCasesNeutres
     * @return void
     */
    private function initCasesNeutres(): void
    {
        $this->plateau[0]->offsetSet(0, PieceSquadro::initNeutre()); // Ligne supérieure neutre
        $this->plateau[0]->offsetSet(6, PieceSquadro::initNeutre()); // Ligne supérieure neutre
        $this->plateau[6]->offsetSet(0, PieceSquadro::initNeutre()); // Ligne supérieure neutre
        $this->plateau[6]->offsetSet(6, PieceSquadro::initNeutre()); // Ligne supérieure neutre
       
    }

    /**
     * Summary of initCasesBlanches
     * @return void
     */
    private function initCasesBlanches(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->plateau[$i]->offsetSet(0, PieceSquadro::initBlancEST());
        }
    }

    /**
     * Summary of initCasesNoires
     * @return void
     */
    private function initCasesNoires(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->plateau[6]->offsetSet($i, PieceSquadro::initNoirNord());
        }
    }

    /**
     * Summary of getPlateu
     * @return array
     */
    public function getPlateu(): array
    {
        return $this->plateau;
    }

    /**
     * Summary of getPiece
     * @param int $x
     * @param int $y
     * @return PieceSquadro
     */
    public function getPiece(int $x, int $y): PieceSquadro
    {
        return $this->plateau[$y]->offsetGet($x);
    }

    /**
     * Summary of setPiece
     * @param \Game\Squadro\Models\PieceSquadro $piece
     * @param int $x
     * @param int $y
     * @return void
     */
    public function setPiece(PieceSquadro $piece, int $x, int $y): void
    {
        $this->plateau[$y]->offsetSet($x, $piece);
    }

    /**
     * Summary of getLignesJouables
     * @return array
     */
    public function getLignesJouables(): array
    {
        return $this->lignesJouables;
    }

    /**
     * Summary of getColonnesJouables
     * @return array
     */
    public function getColonnesJouables(): array
    {
        return $this->colonnesJouables;
    }

    /**
     * Summary of retireLigneJouable
     * @return void
     */
    public function retireLigneJouable(int $index): void
    {
        unset($this->lignesJouables[$index]);
    }

    /**
     * Summary of retireColonneJouable
     * @param int $index
     * @return void
     */
    public function retireColonneJouable(int $index): void
    {
        unset($this->colonnesJouables[$index]);
    }

    /**
     * Summary of getCoordDestination
     * @param int $x
     * @param int $y
     * @return array
     */
    public function getCoordDestination(int $x, int $y): array
    {
        $piece = $this->getPiece($x, $y);
        
       // var_dump($piece); die();
        $destination = match($piece->getDirection()){
            PieceSquadro::OUEST => [$x - self::BLANC_V_RETOUR[$y], $y], 
            PieceSquadro::EST => [$x + self::BLANC_V_ALLER[$y], $y] ,
            PieceSquadro::SUD => [$x , $y + self::NOIR_V_RETOUR[$x]],
            PieceSquadro::NORD => [$x , $y - self::NOIR_V_ALLER[$x]],
            default => [$x , $y]
        };

        return $destination;
    }

    public function getDestination(int $x, int $y): PieceSquadro
    {
        $newdestination = $this->getCoordDestination($x, $y);
        return $this->getPiece($newdestination[0], $newdestination[1]);
    }

    /**
     * Summary of toJson
     * @return bool|string
     */
    public function toJson(): string
    {
        return json_encode($this);
    }

    public function fromJson(): PlateauSquadro
    {
        return json_decode($this);
    }

    public function __tostring(): string
    {
        return serialize($this);
    }
}