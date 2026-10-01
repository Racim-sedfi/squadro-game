<?php
/**
 * @var int $statut
 * @var string $message
 */
?>
<section class="carte carte--centree">
    <p class="surtitre">Erreur <?= e($statut) ?></p>
    <h1><?= $statut === 404 ? 'Page introuvable' : 'Oups…' ?></h1>
    <p><?= e($message) ?></p>
    <a class="bouton bouton--principal" href="/">Retour à l'accueil</a>
</section>
