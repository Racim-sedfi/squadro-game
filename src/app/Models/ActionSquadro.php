<?php

namespace Game\Squadro\Models;
use Game\Squadro\Models\PlateauSquadro;

class ActionSquadro {

    public PlateauSquadro $plateau;

    public PartieSquadro $partie;

    /**
     * Initialisation
     * @param PlateauSquadro $p;
     */
    public function __construct(PlateauSquadro $p){
        $this->plateau = $p;
        $this->partie = PDOSquadro::getPartieSquadroById($_GET['partie']);
    }

    /**
     * 
     */
    public function estJouablePiece(int $x, int $y): bool{
        $destination = $this->plateau->getDestination($x, $y);
        return ($destination->getCouleur() == PieceSquadro::VIDE && $destination->getDirection() == PieceSquadro::VIDE);
    }

    public function jouePiece(int $x, int $y): void{
        $piece = $this->plateau->getPiece($x, $y);
        $newdestination = $this->plateau->getCoordDestination($x, $y);

        $axe = ($x != $newdestination[0]) ? 'x' : 'y';
        $destination = ($x != $newdestination[0]) ? $newdestination[0] : $newdestination[1];

        $check = match($piece->getDirection()){
            PieceSquadro::NORD => ['start' => $destination , 'end' => $y],
            PieceSquadro::SUD => ['start' => $y , 'end' => $destination],
            PieceSquadro::OUEST => ['start' => $destination , 'end' => $x],
            PieceSquadro::EST => ['start' => $x , 'end' => $destination],
            default => false
        };

        if($check){
            for($piece_index = $check['start']+1; $piece_index < $check['end'] ; $piece_index++){
                match($axe){
                    'x' => $this->reculePiece($piece_index, $y),
                    'y' => $this->reculePiece($x, $piece_index)
                };
            }
        }

        if($newdestination[0] == 6 || $newdestination[1] == 0) $piece->inverseDirection();
        $this->plateau->setPiece($piece, $newdestination[0], $newdestination[1]);
        $this->plateau->setPiece(PieceSquadro::initVide(),$x, $y);
        $this->sortPiece($newdestination[0], $newdestination[1]);

        
        $this->partie->plateau = $this->plateau;
        $this->partie->joueurActif = ($this->partie->getJoueurs()[PartieSquadro::PLAYER_ONE] == $this->partie->getJoueurActif()) ? PartieSquadro::PLAYER_TWO : PartieSquadro::PLAYER_ONE;
        PDOSquadro::savePartieSquadro('initialized', $this->partie->toJson(), $_GET['partie']);       
        if($this->partie->scoreJoueur1 == 5 || $this->partie->scoreJoueur2 == 5) header('Location: /?action=winner');
        header('Location: /?partie='.$_GET['partie']);  
    }

    public function reculePiece(int $x, int $y): void{
        
        $piece = $this->plateau->getPiece($x, $y);
        if($piece->getDirection() != PieceSquadro::VIDE){
            $newdestination =  match($piece->getDirection()){
                PieceSquadro::NORD => $this->plateau->setPiece($piece, $x, 6),
                PieceSquadro::SUD => $this->plateau->setPiece($piece, $x, 0),
                PieceSquadro::OUEST => $this->plateau->setPiece($piece, 6, $y),
                PieceSquadro::EST => $this->plateau->setPiece($piece, 0, $y),
            };
            $this->plateau->setPiece(PieceSquadro::initVide(),$x, $y);
        }
    }

    public function sortPiece(int $x, int $y): void{
        $piece = $this->plateau->getPiece($x, $y);
        if($piece->getDirection() == PieceSquadro::SUD && $y == 6){
            $this->partie->scoreJoueur2++;
            $this->plateau->setPiece(PieceSquadro::initNeutre(),$x, $y);
        }
        elseif($piece->getDirection() == PieceSquadro::OUEST && $x == 0){
            $this->partie->scoreJoueur1++;
            $this->plateau->setPiece(PieceSquadro::initNeutre(),$x, $y);
        }
    }

    public function remporteVictoire(int $couleur): void{
        
    }
}
