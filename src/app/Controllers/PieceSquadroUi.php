<?php

namespace Game\Squadro\Controllers;

use Game\Squadro\Models\PieceSquadro;
class PieceSquadroUi {
    
    public function render($piece, $active, $players): void{

        $direction = match ($piece->getDirection()) {
             PieceSquadro::NORD=> 'direction_nord',
             PieceSquadro::SUD=> 'direction_sud',
             PieceSquadro::EST=> 'direction_est',
             PieceSquadro::OUEST=> 'direction_ouest',
             default => ""
        };

        $piece_details = match($piece->getCouleur()) {
            PieceSquadro::BLANC => 
                [
                    'color-code' => '#fff',
                    'name'       => 'BE',
                    'class'       => 'white_button',
                    'direction' => $direction
                ],
            PieceSquadro::NOIR => 
                [
                    'color-code' => '#000',
                    'name'       => 'NN',
                    'class'       => 'black_button',
                    'direction' => $direction
                ],
            PieceSquadro::VIDE => 
                [
                    'color-code' => '#eee',
                    'name'       => '',
                    'class'       => 'empty_button',
                    'direction' => $direction

                ], 
            PieceSquadro::NEUTRE    =>
                [
                    'color-code' => '#999',
                    'name'       => '',
                    'class'       => 'neutre_button',
                    'direction' => $direction
                ]
        };

        include __DIR__.'/../Views/templates/piece.php';
    }
}