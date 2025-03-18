<?php

require_once './vendor/autoload.php';
require_once './env/db.php';

use Game\Squadro\Controllers\AuthController;
use Game\Squadro\Controllers\SquadroUIGenerator;
use Game\Squadro\Models\PieceSquadro;
use Game\Squadro\Models\PDOSquadro;

class game {

    private SquadroUIGenerator $SquadroUIGenerator;
    private AuthController $AuthController;

    public function __construct() {
        session_start();
        if(!isset($_SESSION['player']))  header('Location: /login.php'); 
        if(!isset($_GET['partie']))  header('Location: /parties.php'); 
        if(!isset($_SESSION['NOIRS_FINI'])) $_SESSION['NOIRS_FINI'] = 0;
        if(!isset($_SESSION['BLANCS_FINI'])) $_SESSION['BLANCS_FINI'] = 0;
        if(!isset($_SESSION['ACTIVE_PLAYER'])) $_SESSION['ACTIVE_PLAYER'] = PieceSquadro::BLANC;
        $this->SquadroUIGenerator = new SquadroUIGenerator();
        PDOSquadro::initPDO($_ENV['sgbd'],$_ENV['host'],$_ENV['database'],$_ENV['user'],$_ENV['password']);
    }

    public function start()
    {
        $url = (isset($_GET['action'])) ? $_GET['action'] : 'index'; 
        return match($url){
            'validate'  => $this->SquadroUIGenerator->validate($_GET['x'],$_GET['y']),
            'winner'    =>  $this->SquadroUIGenerator->gagnant(),
            'replay'    =>  $this->SquadroUIGenerator->replay(),
            default     => $this->SquadroUIGenerator->index()
        };
    }
    
}

(new Game)->start();
