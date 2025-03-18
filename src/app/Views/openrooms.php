<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .w-4 {
            width: 2rem;
        }

        .d-zoom-1,
        .d-zoom-2,
        .d-zoom-3,
        .dh-zoom-1,
        .dh-zoom-2,
        .dh-zoom-3 {
            transition: -webkit-transform 180ms;
            transition: transform 180ms;
            transition: transform 180ms, -webkit-transform 180ms;
        }

        .mr-25,
        .mx-25 {
            margin-right: .75rem !important;
        }

        .p-25 {
            padding: .75rem !important;
        }

        .radius-1 {
            border-radius: .25rem !important;
        }

        [class*=bgc-h-] {
            transition: background-color .15s;
        }

        .text-default-d3 {
            color: #416578 !important;
        }

        .font-bolder,
        .text-600 {
            font-weight: 600 !important;
        }

        .text-90 {
            font-size: .9em !important;
        }


        .bgc-h-secondary-l4:hover,
        .bgc-secondary-l4 {
            background-color: #f2f4f6 !important;
        }

        .text-danger-m1 {
            color: #da3636 !important;
        }

        .text-green-m1 {
            color: #2c8d6a !important;
        }

        .text-95 {
            font-size: .95em !important;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row">
            <h1 class="pb-5"> Welcome <?= $_SESSION['player']->getNomJoueur() ?> </h1>

            <div class="col-md-6">
                <div class="card ccard radius-t-0 h-100">
                    <div class="position-tl w-102 border-t-3 brc-primary-tp3 ml-n1px mt-n1px"></div>
                    <!-- the blue line on top -->

                    <div class="card-header pb-3 brc-secondary-l3">
                        <h5 class="card-title mb-2 mb-md-0 text-dark-m3">
                            Mes parties
                        </h5>
                    </div>

                    <div class="card-body pt-2 pb-1">
                        <?php foreach ($parties as $key => $partie) { ?>
                            <a <?php if($partie->gameStatus != "waitingForPlayer") { ?>
                                href="/?partie=<?= $partie->getPartieId() ?>"
                                <?php } ?>

                            >
                                <div role="button"
                                    class="d-flex gap-3 flex-wrap align-items-center my-2 bgc-secondary-l4 bgc-h-secondary-l3 radius-1 p-25 d-style">
                                    <span class="text-default-d3 text-90 text-600">
                                        Game <?= $partie->getPartieId() ?>
                                    </span>

                                    <span class="ml-auto text-dark-l2 text-nowrap">
                                        Partie de
                                        <span class="text-80">
                                            <?= $partie->getJoueurs()[0]->getNomJoueur() ?>
                                        </span>
                                    </span>

                                    <span class="ml-2">
                                        <span class="text-80">
                                                <span class="badge bg-info"> <?= $partie->gameStatus ?>  </span>
                                        </span>
                                    </span>
                                </div>
                            </a>

                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card ccard radius-t-0 h-100">
                    <div class="position-tl w-102 border-t-3 brc-primary-tp3 ml-n1px mt-n1px"></div>
                    <!-- the blue line on top -->

                    <div class="card-header pb-3 brc-secondary-l3">
                        <h5 class="card-title mb-2 mb-md-0 text-dark-m3">
                            Rejoindre partie
                        </h5>
                    </div>

                    <div class="card-body pt-2 pb-1">
                        <?php foreach ($Allparties as $key => $partie) { ?>
                            <a href="/parties.php/?action=rejoindre&partie=<?= $partie->getPartieId() ?>">
                                <div role="button"
                                    class="d-flex gap-3 flex-wrap align-items-center my-2 bgc-secondary-l4 bgc-h-secondary-l3 radius-1 p-25 d-style">
                                    <span class="text-default-d3 text-90 text-600">
                                        Game <?= $partie->getPartieId() ?>
                                    </span>

                                    <span class="ml-auto text-dark-l2 text-nowrap">
                                        Partie de
                                        <span class="text-80">
                                            <?= $partie->getJoueurs()[0]->getNomJoueur() ?>
                                        </span>
                                    </span>

                                    <span class="ml-2">
                                        <span class="text-80">
                                                <span class="badge bg-info"> <?= $partie->gameStatus ?>  </span>
                                        </span>
                                    </span>
                                </div>
                            </a>

                        <?php } ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-12 pt-5">
            <a class="btn btn-primary pull-right" href="/parties.php?action=new"> Créer une nouvelle partie </a>
            <a class="btn btn-warning pull-right" href="/parties.php?action=logout"> Déconnexion </a>
        </div>
    </div>
</body>

</html>