<?php
/**
 * @var Squadro\Http\Vue $vue
 * @var Squadro\Http\Session $session
 * @var Squadro\Modele\JoueurSquadro $joueur
 * @var list<Squadro\Modele\PartieSquadro> $enCours
 * @var list<Squadro\Modele\PartieSquadro> $terminees
 * @var list<Squadro\Modele\PartieSquadro> $ouvertes
 */
$csrf = $session->jetonCsrf();
?>
<section class="salon__entete">
    <div>
        <p class="surtitre">Salon</p>
        <h1>Bonjour <?= e($joueur->pseudo) ?></h1>
    </div>
    <form method="post" action="/parties">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button class="bouton bouton--principal" type="submit">+ Nouvelle partie</button>
    </form>
</section>

<div class="salon">
    <section class="carte">
        <h2>Mes parties</h2>
        <?php if ($enCours === []): ?>
            <p class="vide">Aucune partie en cours. Créez-en une ou rejoignez une partie ouverte.</p>
        <?php else: ?>
            <ul class="liste-parties">
                <?php foreach ($enCours as $partie): ?>
                    <?= $vue->inclure('partials/ligne-partie', ['partie' => $partie, 'joueur' => $joueur]) ?>
                <?php endforeach ?>
            </ul>
        <?php endif ?>
    </section>

    <section class="carte">
        <h2>Parties ouvertes</h2>
        <?php if ($ouvertes === []): ?>
            <p class="vide">Personne n'attend d'adversaire pour le moment.</p>
        <?php else: ?>
            <ul class="liste-parties">
                <?php foreach ($ouvertes as $partie): ?>
                    <li class="liste-parties__element">
                        <div>
                            <span class="liste-parties__titre">Partie n°<?= e($partie->id()) ?></span>
                            <span class="liste-parties__detail">créée par <?= e($partie->joueurBlanc()->pseudo) ?></span>
                        </div>
                        <form method="post" action="/parties/<?= e($partie->id()) ?>/rejoindre">
                            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                            <button class="bouton bouton--secondaire" type="submit">Rejoindre</button>
                        </form>
                    </li>
                <?php endforeach ?>
            </ul>
        <?php endif ?>
    </section>

    <?php if ($terminees !== []): ?>
        <section class="carte salon__historique">
            <h2>Historique</h2>
            <ul class="liste-parties">
                <?php foreach ($terminees as $partie): ?>
                    <?= $vue->inclure('partials/ligne-partie', ['partie' => $partie, 'joueur' => $joueur]) ?>
                <?php endforeach ?>
            </ul>
        </section>
    <?php endif ?>
</div>
