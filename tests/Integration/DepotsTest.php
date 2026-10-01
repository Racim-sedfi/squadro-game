<?php

declare(strict_types=1);

namespace Squadro\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use Squadro\Tests\BaseDeDonneesDeTest;
use Squadro\Modele\PartieSquadro;
use Squadro\Modele\Position;
use Squadro\Modele\StatutPartie;
use Squadro\Persistance\ConflitDeVersionException;
use Squadro\Persistance\DepotJoueurs;
use Squadro\Persistance\DepotParties;
use Squadro\Persistance\PseudoDejaPrisException;

/**
 * Tests des dépôts contre une vraie base SQLite en mémoire.
 */
final class DepotsTest extends TestCase
{
    use BaseDeDonneesDeTest;

    private PDO $pdo;
    private DepotJoueurs $joueurs;
    private DepotParties $parties;

    protected function setUp(): void
    {
        $this->pdo = $this->baseDeTest();
        $this->joueurs = new DepotJoueurs($this->pdo);
        $this->parties = new DepotParties($this->pdo);
    }

    public function testCreerEtRetrouverUnJoueur(): void
    {
        $alice = $this->joueurs->creer('alice', password_hash('secret', PASSWORD_DEFAULT));

        self::assertEquals($alice, $this->joueurs->trouverParId($alice->id));
        [$trouve, $hash] = $this->joueurs->trouverIdentifiants('alice') ?? self::fail('Joueur introuvable');
        self::assertTrue($trouve->equals($alice));
        self::assertTrue(password_verify('secret', $hash));
        self::assertNull($this->joueurs->trouverIdentifiants('inconnu'));
    }

    public function testUnPseudoEstUnique(): void
    {
        $this->joueurs->creer('alice', 'x');

        $this->expectException(PseudoDejaPrisException::class);
        $this->joueurs->creer('alice', 'y');
    }

    public function testCycleDeVieDUnePartie(): void
    {
        $alice = $this->joueurs->creer('alice', 'x');
        $bob = $this->joueurs->creer('bob', 'x');

        $partie = PartieSquadro::nouvelle($alice);
        $this->parties->ajouter($partie);
        self::assertNotNull($partie->id());

        self::assertCount(1, $this->parties->listerOuvertes($bob));
        self::assertCount(0, $this->parties->listerOuvertes($alice), 'On ne voit pas ses propres parties dans les parties ouvertes.');

        $partie->rejoindre($bob);
        $this->parties->enregistrer($partie);
        $partie->jouer($alice, new Position(2, 0));
        $this->parties->enregistrer($partie);

        $relue = $this->parties->trouver((int) $partie->id()) ?? self::fail('Partie introuvable');
        self::assertSame(StatutPartie::EnCours, $relue->statut());
        self::assertSame('bob', $relue->joueurNoir()?->pseudo);
        self::assertEquals($partie->plateau(), $relue->plateau());
        self::assertSame(2, $relue->version());
        self::assertSame(2, $this->parties->version((int) $partie->id()));
        self::assertCount(1, $this->parties->listerPourJoueur($bob));
        self::assertCount(0, $this->parties->listerOuvertes($bob));
    }

    public function testLeVerrouillageOptimisteRefuseUneEcritureConcurrente(): void
    {
        $alice = $this->joueurs->creer('alice', 'x');
        $bob = $this->joueurs->creer('bob', 'x');
        $partie = PartieSquadro::nouvelle($alice);
        $this->parties->ajouter($partie);

        // Deux requêtes lisent la même version de la partie…
        $lecture1 = $this->parties->trouver((int) $partie->id()) ?? self::fail();
        $lecture2 = $this->parties->trouver((int) $partie->id()) ?? self::fail();

        $lecture1->rejoindre($bob);
        $this->parties->enregistrer($lecture1);

        // … la seconde écriture, basée sur une version périmée, est refusée.
        $lecture2->rejoindre($this->joueurs->creer('charlie', 'x'));
        $this->expectException(ConflitDeVersionException::class);
        $this->parties->enregistrer($lecture2);
    }

    public function testLEtatEstStockeEnJsonEtNonSerialise(): void
    {
        $partie = PartieSquadro::nouvelle($this->joueurs->creer('alice', 'x'));
        $this->parties->ajouter($partie);

        $etat = $this->pdo->query('SELECT etat FROM parties')->fetchColumn();

        self::assertIsString($etat);
        self::assertJson($etat);
        self::assertStringNotContainsString('O:', $etat);
    }
}
