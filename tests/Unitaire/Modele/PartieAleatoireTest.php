<?php

declare(strict_types=1);

namespace Squadro\Tests\Unitaire\Modele;

use PHPUnit\Framework\TestCase;
use Squadro\Modele\Couleur;
use Squadro\Modele\JoueurSquadro;
use Squadro\Modele\PartieSquadro;
use Squadro\Modele\Position;
use Squadro\Modele\StatutPartie;

/**
 * Test de robustesse : des centaines de parties jouées au hasard doivent
 * toujours se terminer par une victoire, sans jamais violer les invariants.
 */
final class PartieAleatoireTest extends TestCase
{
    private const NOMBRE_DE_PARTIES = 300;
    private const COUPS_MAXIMUM = 1000;

    public function testDesPartiesAleatoiresSeTerminentToutesCorrectement(): void
    {
        mt_srand(2024); // reproductible
        $joueurs = [Couleur::Blanc->value => new JoueurSquadro(1, 'a'), Couleur::Noir->value => new JoueurSquadro(2, 'b')];

        for ($n = 0; $n < self::NOMBRE_DE_PARTIES; $n++) {
            $partie = PartieSquadro::nouvelle($joueurs['blanc']);
            $partie->rejoindre($joueurs['noir']);

            $coups = 0;
            while ($partie->statut() === StatutPartie::EnCours) {
                self::assertLessThan(self::COUPS_MAXIMUM, ++$coups, 'La partie ne se termine pas.');

                $joueur = $joueurs[$partie->trait()->value];
                $possibles = array_keys($partie->coupsPossibles($joueur));
                self::assertNotEmpty($possibles, 'Le joueur au trait doit toujours pouvoir jouer.');

                [$ligne, $colonne] = array_map('intval', explode(',', $possibles[array_rand($possibles)]));
                $partie->jouer($joueur, new Position($ligne, $colonne));

                foreach ([Couleur::Blanc, Couleur::Noir] as $couleur) {
                    $surLePlateau = count($partie->plateau()->positionsDe($couleur));
                    self::assertSame(5, $surLePlateau + $partie->score($couleur), 'Une pièce a disparu ou a été dupliquée.');
                }
            }

            self::assertNotNull($partie->gagnant());
            self::assertSame(PartieSquadro::PIECES_POUR_GAGNER, $partie->score($partie->gagnant()));
        }
    }
}
