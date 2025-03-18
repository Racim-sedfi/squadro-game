<?php

namespace Game\Squadro\Models;
use Game\Squadro\Models\PlateauSquadro;
use Game\Squadro\Models\JoueurSquadro;

class PartieSquadro{
    /**
     * Summary of PLAYER_ONE
     * @var int
     */
    public const PLAYER_ONE = 0;

    /**
     * Summary of PLAYER_TWO
     * @var int
     */
    public const PLAYER_TWO = 1;

    /**
     * Summary of partieId
     * @var 
     */
    private ?int $partieId = 0;

    /**
     * Summary of joueurs
     * @var array
     */
    private array $joueurs = [];

    /**
     * Summary of joueurActif
     * @var int
     */
    public int $joueurActif = self::PLAYER_ONE;

    public int $scoreJoueur1 = 0;

    public int $scoreJoueur2 = 0;

    /**
     * Summary of gameStatus
     * @var string
     */
    public string $gameStatus;

    /**
     * Summary of plateau
     * @var PlateauSquadro
     */
    public PlateauSquadro $plateau;

    /**
     * Summary of __construct
     * @param \Game\Squadro\Models\JoueurSquadro $playerOne
     */
    public function __construct(JoueurSquadro $playerOne, $gameStatus = 'initialized'){
        $this->joueurs[self::PLAYER_ONE] = $playerOne;
        $this->gameStatus = $gameStatus;
    }


    public function addJoueur(JoueurSquadro $player): void{
        $this->joueurs[self::PLAYER_TWO] = $player;
    }

    public function getJoueurActif(): JoueurSquadro{
        return $this->joueurs[$this->joueurActif];
    }

    public function getNomJoueurActif(): string{
        return $this->joueurActif->getNomJoueur();
    }

    public function __tostring(): string{
        return serialize($this); 
    }

    public function getPartieId(): int{
        return $this->partieId; 
    }

    public function setPartieId(int $id): void{
        $this->partieId = $id; 
    }

    public function getJoueurs(): array{
        return $this->joueurs;
    }

    public function toJson(): string
    {
        return serialize($this);
    }

    public static function fromJson(string $json): PartieSquadro
    {
        return unserialize($json);
    }

}