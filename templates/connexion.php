<?php
/**
 * @var Squadro\Http\Session $session
 * @var string $pseudo
 * @var string $onglet  'connexion' | 'inscription'
 * @var string|null $erreur
 */
$erreur ??= null;
$csrf = $session->jetonCsrf();
?>
<section class="accueil">
    <div class="accueil__texte">
        <p class="surtitre">Jeu de stratégie · 2 joueurs</p>
        <h1>Traversez le plateau,<br>revenez avant votre adversaire.</h1>
        <p class="accueil__intro">
            Chaque pièce fait un aller-retour à sa propre vitesse. Sautez les pièces adverses
            pour les renvoyer en arrière : le premier joueur qui ramène <strong>4 pièces sur 5</strong> gagne.
        </p>
        <a class="lien-fleche" href="/regles">Lire les règles complètes</a>
    </div>

    <div class="carte carte--auth">
        <div class="onglets" role="tablist">
            <input type="radio" name="onglet" id="onglet-connexion" <?= $onglet === 'connexion' ? 'checked' : '' ?>>
            <label for="onglet-connexion" role="tab">Connexion</label>
            <input type="radio" name="onglet" id="onglet-inscription" <?= $onglet === 'inscription' ? 'checked' : '' ?>>
            <label for="onglet-inscription" role="tab">Créer un compte</label>

            <?php if ($erreur !== null): ?>
                <div class="alerte alerte--erreur onglets__alerte" role="alert"><?= e($erreur) ?></div>
            <?php endif ?>

            <form class="formulaire onglets__panneau onglets__panneau--connexion" method="post" action="/connexion">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <label>Pseudo
                    <input type="text" name="pseudo" value="<?= e($pseudo) ?>" required autocomplete="username" maxlength="20">
                </label>
                <label>Mot de passe
                    <input type="password" name="mot_de_passe" required autocomplete="current-password">
                </label>
                <button class="bouton bouton--principal" type="submit">Se connecter</button>
            </form>

            <form class="formulaire onglets__panneau onglets__panneau--inscription" method="post" action="/inscription">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <label>Pseudo
                    <input type="text" name="pseudo" value="<?= e($pseudo) ?>" required autocomplete="username"
                           pattern="[A-Za-z0-9_\-]{3,20}" maxlength="20" title="3 à 20 caractères : lettres, chiffres, - ou _">
                </label>
                <label>Mot de passe
                    <input type="password" name="mot_de_passe" required minlength="6" autocomplete="new-password">
                </label>
                <label>Confirmation
                    <input type="password" name="confirmation" required minlength="6" autocomplete="new-password">
                </label>
                <button class="bouton bouton--principal" type="submit">Créer mon compte</button>
            </form>
        </div>
    </div>
</section>
