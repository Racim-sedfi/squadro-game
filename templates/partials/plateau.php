<?php
/**
 * Plateau affiché sur une grille 9×9 : l'anneau extérieur montre les vitesses,
 * les 7×7 cases intérieures correspondent au PlateauSquadro.
 *
 * @var Squadro\Modele\PartieSquadro $partie
 * @var array<string, Squadro\Modele\Position> $coupsPossibles
 * @var string $csrf
 */

use Squadro\Modele\PlateauSquadro as P;
use Squadro\Modele\Position;

$plateau = $partie->plateau();

// Mise en évidence du dernier coup.
$marques = [];
if ($dernier = $partie->dernierCoup()) {
    $marques[$dernier->depart->cle()] = 'case--depart';
    $marques[$dernier->arrivee->cle()] = 'case--arrivee';
    foreach ($dernier->piecesSautees as $sautee) {
        $marques[$sautee->cle()] = 'case--sautee';
    }
}

/** Vitesse affichée sur l'anneau extérieur pour la cellule (r, c) de la grille 9×9. */
$vitesseAnneau = static function (int $r, int $c): ?array {
    return match (true) {
        $r === 0 && $c >= 2 && $c <= 6 => ['noir', P::VITESSES_NOIR_RETOUR[$c - 1], 'retour'],
        $r === 8 && $c >= 2 && $c <= 6 => ['noir', P::VITESSES_NOIR_ALLER[$c - 1], 'aller'],
        $c === 0 && $r >= 2 && $r <= 6 => ['blanc', P::VITESSES_BLANC_ALLER[$r - 1], 'aller'],
        $c === 8 && $r >= 2 && $r <= 6 => ['blanc', P::VITESSES_BLANC_RETOUR[$r - 1], 'retour'],
        default => null,
    };
};
?>
<form class="plateau-cadre" method="post" action="/parties/<?= e($partie->id()) ?>/coups" data-formulaire-coup>
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="version" value="<?= e($partie->version()) ?>">

    <div class="plateau" role="grid" aria-label="Plateau de Squadro">
        <?php for ($r = 0; $r < 9; $r++): ?>
            <?php for ($c = 0; $c < 9; $c++): ?>
                <?php
                $anneau = $r === 0 || $r === 8 || $c === 0 || $c === 8;
                if ($anneau):
                    $vitesse = $vitesseAnneau($r, $c);
                ?>
                    <div class="vitesse<?= $vitesse ? ' vitesse--' . e($vitesse[0]) . ' vitesse--' . e($vitesse[2]) : '' ?>"
                         <?php if ($vitesse): ?>title="Vitesse <?= e($vitesse[2]) ?> des <?= e($vitesse[0]) ?>s : <?= e($vitesse[1]) ?>"<?php endif ?>>
                        <?php for ($i = 0; $vitesse && $i < $vitesse[1]; $i++): ?><span></span><?php endfor ?>
                    </div>
                <?php
                    continue;
                endif;

                $position = new Position($r - 1, $c - 1);
                $piece = $plateau->piece($position);
                $cle = $position->cle();
                $estCoin = in_array($r - 1, [0, 6], true) && in_array($c - 1, [0, 6], true);
                $estBord = !$estCoin && ($r - 1 === 0 || $r - 1 === 6 || $c - 1 === 0 || $c - 1 === 6);

                $classes = ['case'];
                $classes[] = $estCoin ? 'case--coin' : ($estBord ? 'case--bord' : 'case--croisement');
                if (isset($marques[$cle])) {
                    $classes[] = $marques[$cle];
                }
                ?>
                <div class="<?= e(implode(' ', $classes)) ?>" id="case-<?= e($position->ligne) ?>-<?= e($position->colonne) ?>" role="gridcell">
                    <?php if ($piece !== null):
                        $classePiece = sprintf('piece piece--%s piece--%s', $piece->couleur->value, $piece->direction->value);
                        $etiquette = sprintf('Pièce %s en (%d, %d), direction %s', $piece->couleur->value, $position->ligne, $position->colonne, $piece->direction->value);
                    ?>
                        <?php if (isset($coupsPossibles[$cle])): $cible = $coupsPossibles[$cle]; ?>
                            <button class="<?= e($classePiece) ?> piece--jouable" type="submit" name="coup" value="<?= e($cle) ?>"
                                    data-cible="case-<?= e($cible->ligne) ?>-<?= e($cible->colonne) ?>"
                                    aria-label="<?= e($etiquette) ?> — jouer">
                                <span aria-hidden="true"><?= e($piece->direction->fleche()) ?></span>
                            </button>
                        <?php else: ?>
                            <span class="<?= e($classePiece) ?>" aria-label="<?= e($etiquette) ?>">
                                <span aria-hidden="true"><?= e($piece->direction->fleche()) ?></span>
                            </span>
                        <?php endif ?>
                    <?php endif ?>
                </div>
            <?php endfor ?>
        <?php endfor ?>
    </div>
</form>
