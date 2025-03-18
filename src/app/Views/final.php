<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-3">
  <div class="mt-4 p-5 bg-success text-white rounded">
    <h1 class="text-center">
        Joueur
        <?php if($this->partie->scoreJoueur1 == 5) echo 'blanc'  ?>
        <?php if($this->partie->scoreJoueur2 == 5) echo 'Noir'  ?>
        a gagné
    </h1> 
    <div class="row align-center text-center"> 
        <form method="get" action="#">
            <button class="btn btn-dark col-md-3" type="submit" name="action" value="replay"> Rejouer </button>
        </form>
    </div>  
  </div>
</div>

</body>
</html>

<?php die(); ?>
