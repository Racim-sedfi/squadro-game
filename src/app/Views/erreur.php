<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-3">
  <div class="mt-4 p-5 bg-danger text-white rounded">
    <h1>Erreur</h1> 
    <p>Déplacement impossible du piece <?= $x ?> ,  <?= $y ?> ?</p> 
    <div class="row align-center "> 
        <a class="btn btn-info col-md-3" type="submit" href="/?partie=<?= $_GET['partie'] ?>"> Rejouer </a>
    </div>  
  </div>
</div>

</body>
</html>

<?php die(); ?>
