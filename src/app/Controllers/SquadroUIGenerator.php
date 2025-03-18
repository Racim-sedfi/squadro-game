<?php

namespace Game\Squadro\Controllers;
use Game\Squadro\Models\PartieSquadro;
use Game\Squadro\Models\PDOSquadro;
use Game\Squadro\Models\PlateauSquadro;
use Game\Squadro\Models\ActionSquadro;
require_once './env/db.php';


class SquadroUIGenerator {

    private $plateau;
    private $PieceSquadroUi;
    private $ActionSquadro;
    
    private $partie;
    /**
     * 
     */
    public function __construct() {
        PDOSquadro::initPDO($_ENV['sgbd'],$_ENV['host'],$_ENV['database'],$_ENV['user'],$_ENV['password']);
        $this->partie = isset($_GET['partie']) ?  PDOSquadro::getPartieSquadroById($_GET['partie']) : new PartieSquadro($_SESSION['player']);
        $this->plateau = $this->partie->plateau;
        $this->ActionSquadro = new ActionSquadro($this->plateau);
        $this->PieceSquadroUi = new PieceSquadroUi();
    }

    public function index(){
        if($this->partie->scoreJoueur1 == 5 || $this->partie->scoreJoueur2 == 5){
        include __DIR__.'/../Views/final.php';

        }
        include __DIR__.'/../Views/index.php';
    }

    public function validate($x ,$y){

        $this->partie = PDOSquadro::getPartieSquadroById(gameId: $_GET['partie']);
        if(isset($_POST['confirm']))
        {
            if( $this->ActionSquadro->estJouablePiece($x, $y) ){
                $this->ActionSquadro->jouePiece($x, $y);     
            }
            else {
                include __DIR__.'/../Views/erreur.php';
            }
        }        
        
        include __DIR__.'/../Views/validation.php';
    }

    public function gagnant(){
        include __DIR__.'/../Views/final.php';
    }
    public function replay(){
        session_destroy();
        header('Location: /');
    }
}