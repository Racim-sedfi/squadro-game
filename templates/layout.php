<?php
/**
 * @var Squadro\Http\Vue $vue
 * @var Squadro\Http\Session $session
 * @var string $contenu
 * @var string $titre
 */
$joueur ??= null;
$flashs = $session->consommerFlashs();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Squadro : jeu de stratégie à deux joueurs, jouable en ligne.">
    <title><?= e($titre) ?> · Squadro</title>
    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/squadro.css">
    <script src="/assets/js/squadro.js" defer></script>
</head>
<body>
<header class="entete">
    <div class="conteneur entete__barre">
        <a class="marque" href="/">
            <span class="marque__logo" aria-hidden="true"></span>
            Squadro
        </a>
        <nav class="nav" aria-label="Navigation principale">
            <?php if ($joueur !== null): ?>
                <a href="/salon">Salon</a>
            <?php endif ?>
            <a href="/regles">Règles</a>
            <?php if ($joueur !== null): ?>
                <span class="nav__joueur" title="Connecté"><?= e($joueur->pseudo) ?></span>
                <form method="post" action="/deconnexion">
                    <input type="hidden" name="_csrf" value="<?= e($session->jetonCsrf()) ?>">
                    <button class="bouton bouton--discret" type="submit">Déconnexion</button>
                </form>
            <?php endif ?>
        </nav>
    </div>
</header>

<main class="conteneur principal">
    <?php foreach ($flashs as $flash): ?>
        <div class="alerte alerte--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
    <?php endforeach ?>

    <?= $contenu ?>
</main>

<footer class="pied">
    <div class="conteneur">
        Projet académique réalisé par <strong>Racim Sedfi</strong> &amp; <strong>Said Abdelli</strong>
        · PHP 8 orienté objet · <a href="https://github.com/Racim-sedfi/squadro-game">Code source</a>
    </div>
</footer>
</body>
</html>
