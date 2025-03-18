<?php

namespace Game\Squadro\Models;

class PieceSquadro {
    
    /**
     * Constants
     */
     public const BLANC = 0;
     public const NOIR = 1;
     public const VIDE = -1;
     public const NEUTRE = -2;
     public const NORD = 0;
     public const EST = 1;
     public const SUD = 2;
     public const OUEST = 3;

     private int $couleur;
     private int $direction;

    /**
     * Constructeur du piéce
     * @param int $couleur
     * @param int $direction
     */
    public function __construct(int $couleur, int $direction){
        $this->couleur = $couleur;
        $this->direction = $direction;
    }


    /**
     * Getter couleur
     * @return int
     */
    public function getCouleur(): int{
        return $this->couleur;
    }

    /**
     * Getter direction
     * @return int
     */
    public function getDirection(): int{
        return $this->direction;
    }

    /**
     * Invérser la direction
     * @return void
     */
    public function inverseDirection(): void{
        $this->direction = match ($this->direction) {
             SELF::SUD =>  SELF::NORD,
             SELF::NORD =>  SELF::SUD,
             SELF::OUEST =>  SELF::EST,
             SELF::EST =>  SELF::OUEST,
        };
    }

    /**
     * Convertir l'objet en string
     * @return string
     */
    public function __tostring(): string{
        return (string) 'Direction: '.$this->direction.' | Couleur: '.$this->couleur;
    }

    /**
     * Initialiser Vide
     * @return PieceSquadro
     */
    public static function initVide(): PieceSquadro{
       return new self(SELF::VIDE, SELF::VIDE);
    }

    /**
     * Initialiser Neutre
     * @return PieceSquadro
     */
    public static function initNeutre(): PieceSquadro{
        return new self(SELF::NEUTRE, SELF::NEUTRE);
    }

    /**
     * Initialiser NoirNord
     * @return PieceSquadro
     */
    public static function initNoirNord(): PieceSquadro{
        return new self(SELF::NOIR, SELF::NORD);
    }

    /**
     * Initialiser NoirSud
     * @return PieceSquadro
     */
    public static function initNoirSud(): PieceSquadro{
        return new self(SELF::NOIR, SELF::SUD);
    }

    /**
     * Initialiser BlancEst
     * @return PieceSquadro
     */
    public static function initBlancEst(): PieceSquadro{
        return new self(SELF::BLANC, SELF::EST);
    }

    /**
     * Initialiser BlancOuest
     * @return PieceSquadro
     */
    public static function initBlancOuest(): PieceSquadro{
        return new self(SELF::BLANC, SELF::OUEST);
    }


    /**
     * Retourner json
     * @return string
     */
    public function toJson(): string{

        return json_encode([
            'couleur' => $this->couleur,
            'direction' => $this->direction
        ]);
    }

    /**
     * Object from json
     * @param string $json
     * @return string
     */
    public static function fromJson(string $json): PieceSquadro{
        $params = json_decode($json);
        return new self($params->couleur, $params->direction);
    }
}
