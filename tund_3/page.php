<?php
  $firstName = "Tundmatu";
  $lastName = "Kodanik";
  
  //püüan POST andmed kinni
  var_dump($_POST);
  if (isset($_POST["firstname"])){
	$firstName = $_POST["firstname"];
  }
  if (isset($_POST["lastname"])){
	$lastName = $_POST["lastname"];
  }
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>
  <?php
    echo $firstName;
	echo " ";
	echo $lastName;
  ?>
  , õppetöö</title>
</head>
<body>
  <h1>
  <?php
    echo $firstName ." " .$lastName;
  ?>
  </h1>
  <p>Siin on minu <a href="http://www.tlu.ee">TLÜ</a> õppetöö raames valminud veebilehed. Need ei oma mingit sügavat sisu ja nende kopeerimine ei oma mõtet.</p>
  <hr>
  
  <form method="POST">
    <label>Eesnimi: </label>
    <input type="text" name="firstname">
    <label>Perekonnanimi: </label>
    <input type="text" name="lastname">
	<label>Sünniaasta: </label>
	<input type="number" min="1914" max="2000" value="1999" name="birthyear">
    <input type="submit" name="submitUserData" value="Saada andmed">
  </form>
  <hr>
  <?php
    if (isset($_POST["birthyear"])){
	  echo "<p>Olete elanud järgnevatel aastatel:</p> \n";
	  echo "<ul> \n";
	    for ($i = $_POST["birthyear"]; $i <= date("Y"); $i++){
		  echo "<li>" .$i ."</li> \n";
		}
	  echo "</ul> \n";
	}
  ?>
  
</body>
</html>







