<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Squadro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&family=Great+Vibes&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <style>
        body{
            text-align: center;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        h1{
            font-family: "Figtree", serif;
            font-optical-sizing: auto;
            font-weight: 600;
            font-style: normal;
        }
        table {
            margin: auto auto;
        }

        .indicator button{
            background-color: #fa0;
        }
        
        .direction_nord{
            border-top: 6px solid red;
        }

        .direction_est{
            border-right: 6px solid blue;
        }

        .direction_sud{
            border-bottom: 6px solid red;
        }

        .direction_ouest{
            border-left: 6px solid blue;
        }

        .black_button {
            color: #fff;
        }

        .indicator,
        .piece {
            height: 50px;
            width: 50px;
        }

        .game_score {
            height: 70px;
            font-size: 17px;
        }

        .game_score .white_player .visual_ind{
            display: inline-block;
            height: 17px;
            width: 17px;
            border-radius: 50%;
            color: white;
            border: 1px solid black;
        }

        .game_score .black_player{
            text-align: right;
        }

        .game_score .white_player{
            text-align: left;
        }
        .game_score .black_player .visual_ind{
            display: inline-block;
            height: 17px;
            width: 17px;
            border-radius: 50%;
            background: black;
            border: 1px solid black;
        }

        .active_player{
            background: green;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td colspan="9">     
                <h1> Squadro Game </h1>
            </td>
            </td>
        </tr>
        <tr class="game_score">
            <td colspan="4" class="white_player"> <div class="visual_ind"> </div> <?= $this->partie->getJoueurs()[0]->getNomJoueur() ?> = <?= $this->partie->scoreJoueur1 ?> </td>
            <td colspan="1"> <a href="#" onclick="location.reload();"> refresh </a> </td>
            <td colspan="4" class="black_player"> <div class="visual_ind"> </div> <?= $this->partie->getJoueurs()[1]->getNomJoueur() ?> =  <?= $this->partie->scoreJoueur2 ?> </td>
        </tr>
        <tr>
            <td class="indicator"><button class="piece indicator"> </button></td>
            <?php foreach ($this->plateau::NOIR_V_RETOUR as $ind) { ?>
                <td class="indicator">
                    <button class="piece indicator"> <?php if($ind != 0) echo $ind; ?>  </button>
                </td>
            <?php } ?>
            <td class="indicator"><button class="piece indicator"> </button></td>
        </tr>
        <?php for ($i = 0; $i < 7; $i++) { ?>
            <tr>
                <td class="indicator">
                    <button class="piece indicator"> <?php if($this->plateau::BLANC_V_ALLER[$i] != 0) echo $this->plateau::BLANC_V_ALLER[$i]; ?>  </button>
                </td>
                <?php for ($j = 0; $j < 7; $j++) { ?>
                    <td>
                        <form method="get">
                            <input type="hidden" name="partie" value="<?= $_GET['partie'] ?>">
                            <input type="hidden" name="x" value="<?= $j ?>">
                            <input type="hidden" name="y" value="<?= $i ?>">
                            <?php $this->PieceSquadroUi->render($this->plateau->getPiece($j, $i) , $this->partie->joueurActif, $this->partie->getJoueurs()) ?>
                        </form>
                    </td>
                <?php } ?>
                <td class="indicator">
                    <button class="piece indicator"> <?php if($this->plateau::BLANC_V_RETOUR[$i] != 0) echo $this->plateau::BLANC_V_RETOUR[$i]; ?>  </button>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td class="indicator"><button class="piece indicator"> </button></td>
            <?php foreach ($this->plateau::NOIR_V_ALLER as $ind) { ?>
                <td class="indicator">
                    <button class="piece indicator"> <?php if($ind != 0) echo $ind; ?>  </button>
                </td>
            <?php } ?>
            <td class="indicator"><button class="piece indicator"> </button></td>

        </tr>
    </table>
</body>

</html>