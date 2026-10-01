<?php

declare(strict_types=1);

namespace Squadro\Tests\Fonctionnel;

use PDO;
use PHPUnit\Framework\TestCase;
use Squadro\Tests\BaseDeDonneesDeTest;
use Squadro\Application;
use Squadro\Http\Reponse;
use Squadro\Http\Requete;
use Squadro\Http\Session;

/**
 * Tests de bout en bout de l'application HTTP, sans serveur web :
 * chaque « navigateur » a sa propre session, la base SQLite est partagée.
 */
final class ApplicationTest extends TestCase
{
    use BaseDeDonneesDeTest;

    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->baseDeTest();
    }

    /** @return array{0: Application, 1: Session} */
    private function navigateur(): array
    {
        $stockage = [];
        $session = new Session($stockage);

        return [new Application($this->pdo, $session), $session];
    }

    /** @param array<string, string> $donnees */
    private function post(array $navigateur, string $chemin, array $donnees = [], bool $avecCsrf = true): Reponse
    {
        [$app, $session] = $navigateur;
        if ($avecCsrf) {
            $donnees['_csrf'] = $session->jetonCsrf();
        }

        return $app->traiter(new Requete('POST', $chemin, [], $donnees));
    }

    private function get(array $navigateur, string $chemin): Reponse
    {
        return $navigateur[0]->traiter(new Requete('GET', $chemin));
    }

    private function inscrire(string $pseudo): array
    {
        $navigateur = $this->navigateur();
        $reponse = $this->post($navigateur, '/inscription', [
            'pseudo' => $pseudo, 'mot_de_passe' => 'secret123', 'confirmation' => 'secret123',
        ]);
        self::assertSame(303, $reponse->statut);
        self::assertSame('/salon', $reponse->entetes['Location']);

        return $navigateur;
    }

    public function testUnVisiteurEstRedirigeVersLaConnexion(): void
    {
        $reponse = $this->get($this->navigateur(), '/salon');

        self::assertSame(303, $reponse->statut);
        self::assertSame('/connexion', $reponse->entetes['Location']);
    }

    public function testInscriptionPuisAccesAuSalon(): void
    {
        $alice = $this->inscrire('alice');

        $reponse = $this->get($alice, '/salon');

        self::assertSame(200, $reponse->statut);
        self::assertStringContainsString('Bonjour alice', $reponse->corps);
    }

    public function testUnFormulaireSansJetonCsrfEstRefuse(): void
    {
        $reponse = $this->post($this->navigateur(), '/connexion', ['pseudo' => 'x', 'mot_de_passe' => 'y'], avecCsrf: false);

        self::assertSame(419, $reponse->statut);
    }

    public function testUnPseudoContenantDuHtmlEstRefuse(): void
    {
        $reponse = $this->post($this->navigateur(), '/inscription', [
            'pseudo' => '<script>alert(1)</script>', 'mot_de_passe' => 'secret123', 'confirmation' => 'secret123',
        ]);

        self::assertSame(422, $reponse->statut);
        self::assertStringNotContainsString('<script>alert(1)</script>', $reponse->corps);
        self::assertStringContainsString('&lt;script&gt;', $reponse->corps);
    }

    public function testMauvaisMotDePasse(): void
    {
        $this->inscrire('alice');

        $reponse = $this->post($this->navigateur(), '/connexion', ['pseudo' => 'alice', 'mot_de_passe' => 'faux']);

        self::assertSame(422, $reponse->statut);
        self::assertStringContainsString('Pseudo ou mot de passe incorrect', $reponse->corps);
    }

    public function testDeroulementDUnePartieADeuxJoueurs(): void
    {
        $alice = $this->inscrire('alice');
        $bob = $this->inscrire('bob');

        $creation = $this->post($alice, '/parties');
        self::assertMatchesRegularExpression('#^/parties/(\d+)$#', $creation->entetes['Location']);
        $url = $creation->entetes['Location'];

        self::assertStringContainsString('Rejoindre', $this->get($bob, '/salon')->corps);
        $this->post($bob, $url . '/rejoindre');

        // Bob essaie de jouer alors que c'est aux blancs (alice) de commencer.
        $this->post($bob, $url . '/coups', ['coup' => '6,1']);
        self::assertStringContainsString('Ce n&#039;est pas votre tour.', $this->get($bob, $url)->corps);

        // Alice joue sa pièce de la ligne 2 (vitesse 3).
        $this->post($alice, $url . '/coups', ['coup' => '2,0', 'version' => '1']);
        $page = $this->get($bob, $url)->corps;
        self::assertStringContainsString('id="case-2-3"', $page);
        self::assertStringContainsString('À vous de jouer', $page);

        $etat = $this->get($bob, $url . '/etat');
        self::assertSame(['version' => 2], json_decode($etat->corps, true));
    }

    public function testUnCoupEnvoyeDepuisUnePagePerimeeEstRefuse(): void
    {
        $alice = $this->inscrire('alice');
        $bob = $this->inscrire('bob');
        $url = $this->post($alice, '/parties')->entetes['Location'];
        $this->post($bob, $url . '/rejoindre');

        $this->post($alice, $url . '/coups', ['coup' => '2,0', 'version' => '0']);

        self::assertStringContainsString('Le plateau a changé', $this->get($alice, $url)->corps);
    }

    public function testPageInexistante(): void
    {
        self::assertSame(404, $this->get($this->navigateur(), '/nimporte-quoi')->statut);
        self::assertSame(405, $this->get($this->navigateur(), '/deconnexion')->statut);
    }
}
