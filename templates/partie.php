<?php
/**
 * @var Squadro\Http\Vue $vue
 * @var Squadro\Http\Session $session
 * @var Squadro\Modele\PartieSquadro $partie
 * @var Squadro\Modele\JoueurSquadro $joueur
 * @var Squadro\Modele\Couleur|null $maCouleur
 * @var array<string, Squadro\Modele\Position> $coupsPossibles
 */

use Squadro\Modele\Couleur;
use Squadro\Modele\PartieSquadro;
use Squadro\Modele\StatutPartie;

$statut = $partie->statut();
$aMonTour = $partie->estAuTourDe($joueur);
$nomTrait = $partie->joueur($partie->trait())?->pseudo ?? '';

$message = match (true) {
    $statut === StatutPartie::EnAttente && $maCouleur !== null => 'En attente d\'un adversaire : la partie est visible dans le salon des autres joueurs.',
    $statut === StatutPartie::EnAttente => 'Cette partie attend un second joueur.',
    $statut === StatutPartie::Terminee => sprintf('Victoire de %s !', $partie->joueur($partie->gagnant() ?? Couleur::Blanc)?->pseudo),
    $aMonTour => 'À vous de jouer : cliquez sur l\'une de vos pièces. Survolez-la pour voir où elle arrivera.',
    $maCouleur === null => sprintf('Vous regardez cette partie. Au tour de %s.', $nomTrait),
    default => sprintf('Au tour de %s…', $nomTrait),
};

// Le navigateur interroge le serveur tant qu'il attend une action de l'adversaire.
$surveiller = $statut !== StatutPartie::Terminee && !$aMonTour;
?>
<section class="partie"
         data-version="<?= e($partie->version()) ?>"
         <?php if ($surveiller): ?>data-etat-url="/parties/<?= e($partie->id()) ?>/etat"<?php endif ?>>

    <div class="partie__joueurs">
        <?php foreach ([Couleur::Blanc, Couleur::Noir] as $couleur):
            $j = $partie->joueur($couleur);
            $actif = $statut === StatutPartie::EnCours && $partie->trait() === $couleur;
            $gagnant = $partie->gagnant() === $couleur;
        ?>
            <div class="joueur joueur--<?= e($couleur->value) ?><?= $actif ? ' joueur--actif' : '' ?><?= $gagnant ? ' joueur--gagnant' : '' ?>">
                <span class="joueur__pion" aria-hidden="true"></span>
                <div class="joueur__infos">
                    <span class="joueur__nom">
                        <?= e($j?->pseudo ?? 'En attente…') ?>
                        <?php if ($couleur === $maCouleur): ?><small>(vous)</small><?php endif ?>
                    </span>
                    <span class="joueur__score" aria-label="<?= e($partie->score($couleur)) ?> pièces ramenées sur <?= PartieSquadro::PIECES_POUR_GAGNER ?>">
                        <?php for ($i = 0; $i < PartieSquadro::PIECES_POUR_GAGNER; $i++): ?>
                            <span class="pastille<?= $i < $partie->score($couleur) ? ' pastille--pleine' : '' ?>"></span>
                        <?php endfor ?>
                    </span>
                </div>
            </div>
        <?php endforeach ?>
    </div>

    <p class="partie__message partie__message--<?= $aMonTour ? 'accent' : e($statut->value) ?>" role="status"><?= e($message) ?></p>

    <div class="partie__corps">
        <?= $vue->inclure('partials/plateau', [
            'partie' => $partie,
            'coupsPossibles' => $coupsPossibles,
            'csrf' => $session->jetonCsrf(),
        ]) ?>

        <aside class="partie__aside">
            <div class="carte carte--compacte">
                <h2>Partie n°<?= e($partie->id()) ?></h2>
                <dl class="infos">
                    <dt>Statut</dt><dd><?= e($statut->libelle()) ?></dd>
                    <dt>Coups joués</dt><dd><?= e($partie->nombreCoups()) ?></dd>
                    <?php if ($dernier = $partie->dernierCoup()): ?>
                        <dt>Dernier coup</dt>
                        <dd>
                            <?= e($dernier->couleur->libelle()) ?> :
                            (<?= e($dernier->depart->ligne) ?>,<?= e($dernier->depart->colonne) ?>) →
                            (<?= e($dernier->arrivee->ligne) ?>,<?= e($dernier->arrivee->colonne) ?>)
                            <?php if ($dernier->piecesSautees !== []): ?>
                                · <?= count($dernier->piecesSautees) ?> pièce(s) sautée(s)
                            <?php endif ?>
                            <?php if ($dernier->pieceArrivee): ?>· pièce rentrée !<?php endif ?>
                        </dd>
                    <?php endif ?>
                </dl>
            </div>
            <div class="carte carte--compacte legende">
                <h2>Rappel</h2>
                <ul>
                    <li>Les points au bord indiquent la vitesse de chaque voie.</li>
                    <li>Sauter une pièce adverse la renvoie au début de son trajet.</li>
                    <li>Premier à ramener <?= PartieSquadro::PIECES_POUR_GAGNER ?> pièces : gagné.</li>
                </ul>
                <a class="lien-fleche" href="/regles">Règles détaillées</a>
            </div>
            <?php if ($statut === StatutPartie::EnAttente && $maCouleur === null): ?>
                <form method="post" action="/parties/<?= e($partie->id()) ?>/rejoindre">
                    <input type="hidden" name="_csrf" value="<?= e($session->jetonCsrf()) ?>">
                    <button class="bouton bouton--principal bouton--large" type="submit">Rejoindre cette partie</button>
                </form>
            <?php endif ?>
            <a class="bouton bouton--secondaire bouton--large" href="/salon">Retour au salon</a>
        </aside>
    </div>
</section>
