<?php

use Squadro\Modele\PartieSquadro;

?>
<article class="carte regles">
    <p class="surtitre">Règles du jeu</p>
    <h1>Comment jouer à Squadro</h1>

    <h2>But du jeu</h2>
    <p>
        Chaque joueur possède 5 pièces qui doivent traverser le plateau puis revenir à leur point de départ.
        Le premier joueur qui ramène <strong><?= PartieSquadro::PIECES_POUR_GAGNER ?> pièces</strong> remporte la partie.
    </p>

    <h2>Déroulement</h2>
    <ol>
        <li>Les <strong>blancs</strong> partent de la gauche vers la droite, les <strong>noirs</strong> du bas vers le haut. Les blancs commencent.</li>
        <li>À son tour, un joueur déplace <strong>une seule</strong> de ses pièces.</li>
        <li>
            La pièce avance d'autant de cases que de points dessinés au bord de sa voie (1, 2 ou 3).
            La vitesse au retour est différente de la vitesse à l'aller : leur somme vaut toujours 4.
        </li>
        <li>Une pièce qui atteint le bord opposé s'y arrête (même s'il lui restait du mouvement) et fait demi-tour.</li>
        <li>Une pièce qui revient à son point de départ sort du plateau et rapporte un point.</li>
    </ol>

    <h2>Sauter l'adversaire</h2>
    <p>
        Si une pièce rencontre une ou plusieurs pièces adverses collées les unes aux autres, elle les saute,
        se pose sur la première case libre derrière elles et <strong>son déplacement s'arrête</strong>.
        Chaque pièce sautée repart au début de son trajet en cours : son point de départ si elle était à l'aller,
        le bord opposé si elle était déjà sur le retour.
    </p>

    <h2>Dans cette version en ligne</h2>
    <ul>
        <li>Survolez une de vos pièces pour voir où elle arrivera avant de jouer.</li>
        <li>Le plateau se met à jour automatiquement quand votre adversaire joue.</li>
        <li>Le dernier coup est surligné, ainsi que les pièces qui ont été sautées.</li>
    </ul>
</article>
