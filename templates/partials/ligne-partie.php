<?php
/**
 * @var Squadro\Modele\PartieSquadro $partie
 * @var Squadro\Modele\JoueurSquadro $joueur
 */

use Squadro\Modele\Couleur;
use Squadro\Modele\StatutPartie;

$maCouleur = $partie->couleurDe($joueur);
$adversaire = $maCouleur !== null ? $partie->joueur($maCouleur->adversaire()) : null;

[$badge, $classe] = match (true) {
    $partie->statut() === StatutPartie::EnAttente => ['En attente', 'neutre'],
    $partie->statut() === StatutPartie::Terminee && $partie->gagnant() === $maCouleur => ['Victoire', 'succes'],
    $partie->statut() === StatutPartie::Terminee => ['Défaite', 'erreur'],
    $partie->estAuTourDe($joueur) => ['À vous de jouer', 'accent'],
    default => ['Tour adverse', 'neutre'],
};
?>
<li>
    <a class="liste-parties__element liste-parties__element--lien" href="/parties/<?= e($partie->id()) ?>">
        <div>
            <span class="liste-parties__titre">Partie n°<?= e($partie->id()) ?></span>
            <span class="liste-parties__detail">
                <?php if ($adversaire !== null): ?>
                    contre <?= e($adversaire->pseudo) ?> ·
                    <?= e($partie->score(Couleur::Blanc)) ?>–<?= e($partie->score(Couleur::Noir)) ?>
                <?php else: ?>
                    en attente d'un adversaire
                <?php endif ?>
                · vous jouez les <?= e(strtolower($maCouleur?->libelle() ?? '')) ?>
            </span>
        </div>
        <span class="badge badge--<?= e($classe) ?>"><?= e($badge) ?></span>
    </a>
</li>
