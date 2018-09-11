<?php
  //echo "See on minu esimene php!"; //rumal teade
  $firstName = "Andrus";
  $lastName = "Rinde";
  $dateToday = date("d.m.Y");
  $hourNow = date("G");
  $partOfDay = "";
  if ($hourNow < 8){
	  $partOfDay = "varajane hommik";
  }
  if ($hourNow >= 8 and $hourNow < 16){
	  $partOfDay = "koolipäev";
  }
  if ($hourNow > 16){
	  $partOfDay = "loodetavasti vaba aeg";
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
  <?php
    echo "<p>Tänane kuupäev on: " .$dateToday .".</p> \n";
	echo "<p>Lehe avamise hetkel oli kell " .date("H:i:s") .", käes oli " .$partOfDay .".</p> \n";
  ?>
  <img src="../tlu_terra_600x400_2.jpg" alt="TLÜ Terra õppehoone">
  <p>Minu sõber teeb ka <a href="../../../~jaagala">veebi</a></p>
</body>
</html>