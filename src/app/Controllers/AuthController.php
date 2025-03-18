<?php
namespace Game\Squadro\Controllers;
use Game\Squadro\Models\PDOSquadro;
use Game\Squadro\Models\PlateauSquadro;
use Game\Squadro\Models\PartieSquadro;
class AuthController {

    public function dashboard(){
        $parties = PDOSquadro::getAllPartieSquadroByPlayerName($_SESSION['player']->getNomJoueur());
        $Allparties = PDOSquadro::getAllPartieSquadro();
        include __DIR__.'/../Views/openrooms.php';
    }

    public function new():void {
        $partie = new PartieSquadro($_SESSION['player']);
        $partie->plateau = new PlateauSquadro();
        $partie->gameStatus = 'waitingForPlayer';
        PDOSquadro::createPartieSquadro($_SESSION['player']->getNomJoueur(), $partie->toJson());
        header('Location: /parties.php');
    }

    public function rejoidre():void{
        $partie = PDOSquadro::getPartieSquadroById($_GET['partie']);
        if(sizeof($partie->getJoueurs()) == 1 && $partie->getJoueurs()[0]->getId() != $_SESSION['player']->getId()){
            $partie->addJoueur($_SESSION['player']);
            PDOSquadro::addPlayerToPartieSquadro($_SESSION['player']->getNomJoueur(), $partie->toJson(), $_GET['partie']);
        } 

        //PDOSquadro::addPlayerToPartieSquadro();
        $partie = PDOSquadro::getPartieSquadroById($_GET['partie']);
        //PDOSquadro::createPartieSquadro($_SESSION['player']->getNomJoueur(), $partie->toJson());
        header('Location: /?partie='.$_GET['partie']);
    }


    public function logout():void{
        session_destroy();
        header('Location: /login.php');
    }
}