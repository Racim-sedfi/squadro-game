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
        $this->AuthController = new AuthController();
        PDOSquadro::initPDO($_ENV['sgbd'],$_ENV['host'],$_ENV['database'],$_ENV['user'],$_ENV['password']);
    }

    public function start()
    {
        $url = (isset($_GET['action'])) ? $_GET['action'] : 'index'; 
        return match($url){
            'new'    =>  $this->AuthController->new(),
            'rejoindre'    =>  $this->AuthController->rejoidre(),
            'logout'    =>  $this->AuthController->logout(),
            default     => $this->AuthController->dashboard(),
        };
    }
    
}

(new Game)->start();
