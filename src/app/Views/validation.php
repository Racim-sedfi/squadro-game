<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-3">
  <div class="mt-4 p-5 bg-primary text-white rounded">
    <h1>Valider</h1> 
    <p>Valider la mouvement du piéce ?</p> 
    <div class="row align-center "> 
        <form method="post" action="#">
            <input type="hidden" name="x" value="<?= $x ?>">
            <input type="hidden" name="y" value="<?= $y ?>">
            <button class="btn btn-success col-md-3" type="submit" name="confirm" value="true"> Valider </button>
            <a class="btn btn-danger col-md-3" type="submit" href="/?partie=<?= $_GET['partie'] ?>"> Annuler </a>
        </form>
    </div>  
  </div>
</div>

</body>
</html>
