<?php
  require("../../../../vp2018config.php");
  $database = "if18_rinde";
  //echo $serverHost;
  
  //kasutan sessiooni
  session_start();
  
  function addUserPhotoData($fileName){
	$addedId = null;
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
    $stmt = $mysqli->prepare("INSERT INTO vp_user_pictures2 (userid, filename) VALUES (?, ?)");
	echo $mysqli->error;
	$stmt->bind_param("is", $_SESSION["userId"], $fileName);
	if($stmt->execute()){
	  $addedId = $mysqli->insert_id;
	  //echo $addedId;
	} else {
	  echo $stmt->error;
	}
	return $addedId;
	$stmt->close();
	$mysqli->close();
  }
  
  function addPhotoData($filename, $alttext, $privacy){
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
    $stmt = $mysqli->prepare("INSERT INTO vpphotos2 (userid, filename, alttext, privacy) VALUES(?, ?, ?, ?)");
	echo $mysqli->error;
	if(empty($privacy) or $privacy > 3 or $privacy < 1){
		$privacy = 3;
	}
	$stmt->bind_param("issi", $_SESSION["userId"], $filename, $alttext, $privacy);
	if($stmt->execute()){
		echo "Andmebaasiga on kõik korras!";
	} else {
		echo "Andmebaasiga läks midagi viltu!" .$stmt->error;
	}
	$stmt->close();
	$mysqli->close();
  }
  
  function readprofilecolors(){
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
    $stmt = $mysqli->prepare("SELECT bgcolor, txtcolor FROM vpuserprofiles2 WHERE userid=?");
	echo $mysqli->error;
	$stmt->bind_param("i", $_SESSION["userId"]);
	$stmt->bind_result($bgcolor, $txtcolor);
	$stmt->execute();
	$profile = new Stdclass();
	if($stmt->fetch()){
		$_SESSION["bgColor"] = $bgcolor;
		$_SESSION["txtColor"] = $txtcolor;
	} else {
		$_SESSION["bgColor"] = "#FFFFFF";
		$_SESSION["txtColor"] = "#000000";
	}
	$stmt->close();
	$mysqli->close();
  }
  
  //kasutajaprofiili salvestamine
  function storeuserprofile($desc, $bgcol, $txtcol, $picId){
	$notice = "";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
    $stmt = $mysqli->prepare("SELECT description, bgcolor, txtcolor FROM vpuserprofiles2 WHERE userid=?");
	echo $mysqli->error;
	$stmt->bind_param("i", $_SESSION["userId"]);
	$stmt->bind_result($description, $bgcolor, $txtcolor);
	$stmt->execute();
	if($stmt->fetch()){
		//profiil juba olemas, uuendame
		$stmt->close();
		//kui on pilt lisatud
		if(!empty($picId)){
		  $stmt = $mysqli->prepare("UPDATE vpuserprofiles2 SET description=?, bgcolor=?, txtcolor=?, picture=? WHERE userid=?");
		  echo $mysqli->error;
		  $stmt->bind_param("sssii", $desc, $bgcol, $txtcol, $picId, $_SESSION["userId"]);
		} else {
		  $stmt = $mysqli->prepare("UPDATE vpuserprofiles2 SET description=?, bgcolor=?, txtcolor=? WHERE userid=?");
		  echo $mysqli->error;
		  $stmt->bind_param("sssi", $desc, $bgcol, $txtcol, $_SESSION["userId"]);
		}
		
		if($stmt->execute()){
			$notice = "Profiil edukalt uuendatud!";
			$_SESSION["bgColor"] = $bgcol;
		    $_SESSION["txtColor"] = $txtcol;
		} else {
			$notice = "Profiili uuendamisel tekkis tõrge! " .$stmt->error;
		}
	} else {
		//profiili pole, salvestame
		$stmt->close();
		//kui on pilt ka lisatud
		if(!empty($picId)){
		  $stmt = $mysqli->prepare("INSERT INTO vpuserprofiles2 (userid, description, bgcolor, txtcolor, picture) VALUES(?,?,?,?,?)");
		  echo $mysqli->error;
		  $stmt->bind_param("isssi", $_SESSION["userId"], $desc, $bgcol, $txtcol, $picId);
		} else {
		  //INSERT INTO vpusers3 (firstname, lastname, birthdate, gender, email, password) VALUES(?,?,?,?,?,?)"
		  $stmt = $mysqli->prepare("INSERT INTO vpuserprofiles2 (userid, description, bgcolor, txtcolor) VALUES(?,?,?,?)");
		  echo $mysqli->error;
		  $stmt->bind_param("isss", $_SESSION["userId"], $desc, $bgcol, $txtcol);
		}
		if($stmt->execute()){
			$notice = "Profiil edukalt salvestatud!";
			$_SESSION["bgColor"] = $bgcol;
		    $_SESSION["txtColor"] = $txtcol;
		} else {
			$notice = "Profiili salvestamisel tekkis tõrge! " .$stmt->error;
		}
	}
	$stmt->close();
	$mysqli->close();
	return $notice;
  }
  
  //kasutajaprofiili väljastamine
  function showmyprofile(){
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
    $stmt = $mysqli->prepare("SELECT description, bgcolor, txtcolor, picture FROM vpuserprofiles2 WHERE userid=?");
	echo $mysqli->error;
	$stmt->bind_param("i", $_SESSION["userId"]);
	$stmt->bind_result($description, $bgcolor, $txtcolor, $picture);
	$stmt->execute();
	$profile = new Stdclass();
	if($stmt->fetch()){
		$profile->description = $description;
		$profile->bgcolor = $bgcolor;
		$profile->txtcolor = $txtcolor;
		$profile->picture = $picture;
	} else {
		$profile->description = "";
		$profile->bgcolor = "";
		$profile->txtcolor = "";
		$profile->picture = null;
	}
	$stmt->close();
	//kui on pilt olemas
	if(!empty($profile->picture)){
	  $stmt = $mysqli->prepare("SELECT filename FROM vp_user_pictures2 WHERE id=?");
	  echo $mysqli->error;
	  $stmt->bind_param("i", $profile->picture);
	  $stmt->bind_result($pictureFile);
	  $stmt->execute();
	  if($stmt->fetch()){
		$profile->picture = $pictureFile;  
	  }
	  $stmt->close();
	}
	$mysqli->close();
	return $profile;
  }
  
  //väljastame kõik valideeritud sõnumid kasutajate kaupa
  function readallvalidatedmessagesbyuser(){
	$msghtml = "";
	$result = "";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
	$stmt = $mysqli->prepare("SELECT id, firstname, lastname FROM vpusers2");
	echo $mysqli->error;
	$stmt->bind_result($idFromDb, $firstnameFromDb, $lastnameFromDb);
	$stmt2 = $mysqli->prepare("SELECT message, accepted FROM vpamsg2 WHERE acceptedby=?");
	echo $mysqli->error;
	$stmt2->bind_result($msgFromDb, $acceptedFromDb);
	
	$stmt->execute();
	//järgmine käsk hoiab tulemust pikemalt kinni, et saaks ka järgmises käsus kasutada
	$stmt->store_result();
	
	$stmt2->bind_param("i", $idFromDb);
	
	while($stmt->fetch()){
		$msghtml .="<h3>" .$firstnameFromDb ." " .$lastnameFromDb ."</h3> \n";
		$resultcounter = 0;
		$stmt2->execute();
		while($stmt2->fetch()){
		  $msghtml .="<p>";
		  if($acceptedFromDb == 0){
			$msghtml .= "<b>Keelatud: </b>";
		  } else {
			$msghtml .= "<b>Lubatud: </b>";
		  }
		  $msghtml .=  $msgFromDb ."</p>\n";
		  $resultcounter ++;
		}
		if($resultcounter > 0){
		  $result .= $msghtml;
		}
		
		$msghtml = "";
	}
	$stmt->free_result();
	$stmt->close();
	$stmt2->close();
	$mysqli->close();
	return $result;
  }
  
  //kõigi valideeritud sõnumite lugemine valideerija kaupa
  function readallvalidatedmessagesbyuser_vana(){
	$msghtml ="";
	$result = "";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
	$stmt = $mysqli->prepare("SELECT id, firstname, lastname FROM vpusers2");
	echo $mysqli->error;
	$stmt->bind_result($idFromDb, $firstnameFromDb, $lastnameFromDb);
	
	$stmt2 = $mysqli->prepare("SELECT message, accepted FROM vpamsg2 WHERE acceptedby=?");
	$stmt2->bind_param("i", $idFromDb);
	$stmt2->bind_result($msgFromDb, $acceptedFromDb);
	
	$stmt->execute();
	//et saadud tulemus püsiks ja oleks kasutatav ka järgmises päringus ($stmt2)
	$stmt->store_result();
	
	while($stmt->fetch()){
	  $msghtml .= "<h3>" . $firstnameFromDb ." " .$lastnameFromDb ."</h3> \n";
	  $resultcounter = 0;
	  $stmt2->execute();
	  while($stmt2->fetch()){
		$msghtml .= "<p><b>";
		if($acceptedFromDb == 1){
		  $msghtml .= "Lubatud: ";
		} else {
		  $msghtml .= "Keelatud: ";
		}
		$msghtml .= "</b>" .$msgFromDb ."</p> \n";
		$resultcounter ++;
	  }//while $stmt2 fetch
	  if($resultcounter > 0){
		  $result .= $msghtml;
		}
		
		$msghtml = "";
	}//while $stmt fetch
	$stmt->free_result();
	$stmt2->close();
	$stmt->close();
	$mysqli->close();
	return $msghtml;
  }
  
  //kasutajate nimekiri
  function listusers(){
	$notice = "";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
	$stmt = $mysqli->prepare("SELECT firstname, lastname, email FROM vpusers2 WHERE id !=?");
	
	echo $mysqli->error;
	$stmt->bind_param("i", $_SESSION["userId"]);
	$stmt->bind_result($firstname, $lastname, $email);
	if($stmt->execute()){
	  $notice .= "<ol> \n";
	  while($stmt->fetch()){
		  $notice .= "<li>" .$firstname ." " .$lastname .", kasutajatunnus: " .$email ."</li> \n";
	  }
	  $notice .= "</ol> \n";
	} else {
		$notice = "<p>Kasutajate nimekirja lugemisel tekkis tehniline viga! " .$stmt->error;
	}
	
	$stmt->close();
	$mysqli->close();
	return $notice;
  }
  
  function allvalidmessages(){
	$html = "";
	$valid = 1;
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
	$stmt = $mysqli->prepare("SELECT message FROM vpamsg2 WHERE accepted=? ORDER BY accepttime DESC");
	echo $mysqli->error;
	$stmt->bind_param("i", $valid);
	$stmt->bind_result($msg);
	$stmt->execute();
	while($stmt->fetch()){
		$html .= "<p>" .$msg ."</p> \n";
	}
	$stmt->close();
	$mysqli->close();
	if(empty($html)){
		$html = "<p>Kontrollitud sõnumeid pole.</p>";
	}
	return $html;
  }
  
  function validatemsg($editId, $validation){
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
	$stmt = $mysqli->prepare("UPDATE vpamsg2 SET acceptedby=?, accepted=?, accepttime=now() WHERE id=?");
	$stmt->bind_param("iii", $_SESSION["userId"], $validation, $editId);
	if($stmt->execute()){
	  echo "Õnnestus";
	  header("Location: validatemsg.php");
	  exit();
	} else {
	  echo "Tekkis viga: " .$stmt->error;
	}
	$stmt->close();
	$mysqli->close();
  }
  
  //valitud sõnumi lugemine valideerimiseks
  function readmsgforvalidation($editId){
	$notice = "";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
	$stmt = $mysqli->prepare("SELECT message FROM vpamsg2 WHERE id = ?");
	$stmt->bind_param("i", $editId);
	$stmt->bind_result($msg);
	$stmt->execute();
	if($stmt->fetch()){
		$notice = $msg;
	}
	$stmt->close();
	$mysqli->close();
	return $notice;
  }
  
  //valideerimata sõnumite nimekiri
  function readallunvalidatedmessages(){
	$notice = "<ul> \n";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
	$stmt = $mysqli->prepare("SELECT id, message FROM vpamsg2 WHERE accepted IS NULL");
	echo $mysqli->error;
	$stmt->bind_result($msgid, $msg);
	if($stmt->execute()){
	  while($stmt->fetch()){
		$notice .= "<li>" .$msg .'<br><a href="validatemessage.php?id=' .$msgid .'">Valideeri</a></li>' ."\n"; 
	  }
    } else {
	  $notice .= "<li>Sõnumite lugemisel tekkis viga!" .$stmt->error ."</li> \n";
	}
	$notice .= "</ul> \n";
	$stmt->close();
	$mysqli->close();
	return $notice;
  }
  
  //sisselogimine
  function signin($email, $password){
	$notice = "";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
	$stmt = $mysqli->prepare("SELECT id, firstname, lastname, password FROM vpusers2 WHERE email=?");
	echo $mysqli->error;
	$stmt->bind_param("s", $email);
	$stmt->bind_result($idFromDb, $firstnameFromDb, $lastnameFromDb, $passwordFromDb);
	if($stmt->execute()){
	  //andmebaasi päring õnnestus
	  if($stmt->fetch()){
		//kasutaja on olemas
		if(password_verify($password, $passwordFromDb)){
		  //parool õige
		  $notice = "Olete õnnelikult sisse loginud!";
		  //määrame sessioonimuutujad
		  $_SESSION["userId"] = $idFromDb;
		  $_SESSION["lastName"] = $lastnameFromDb;
		  $_SESSION["firstName"] = $firstnameFromDb;
		  readprofilecolors();
		  $stmt->close();
	      $mysqli->close();
		  header("Location: main.php");
		  exit();
		  
		} else {
		  $notice = "Kahjuks vale salasõna!";
		}
	  } else {
		$notice = "Kahjuks sellise kasutajatunnusega (" .$email .") kasutajat ei leitud!";  
	  }
	} else {
	  $notice = "Sisselogimisel tekkis tehniline viga!" .$stmt->error;
	}
	
	$stmt->close();
	$mysqli->close();
	return $notice;
  }
  
  //kasutaja salvestamine
  function signup($name, $surname, $email, $gender, $birthDate, $password){
	$notice = "";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
	//kontrollime, ega kasutajat juba olemas pole
	$stmt = $mysqli->prepare("SELECT id FROM vpusers2 WHERE email=?");
	echo $mysqli->error;
	$stmt->bind_param("s",$email);
	$stmt->execute();
	if($stmt->fetch()){
		//leiti selline, seega ei saa uut salvestada
		$notice = "Sellise kasutajatunnusega (" .$email .") kasutaja on juba olemas! Uut kasutajat ei salvestatud!";
	} else {
		$stmt->close();
		$stmt = $mysqli->prepare("INSERT INTO vpusers2 (firstname, lastname, birthdate, gender, email, password) VALUES(?,?,?,?,?,?)");
    	echo $mysqli->error;
	    $options = ["cost" => 12, "salt" => substr(sha1(rand()), 0, 22)];
	    $pwdhash = password_hash($password, PASSWORD_BCRYPT, $options);
	    $stmt->bind_param("sssiss", $name, $surname, $birthDate, $gender, $email, $pwdhash);
	    if($stmt->execute()){
		  $notice = "ok";
	    } else {
	      $notice = "error" .$stmt->error;	
	    }
	}
	$stmt->close();
	$mysqli->close();
	return $notice;
  }
  
  function saveamsg($msg){
	$notice = "";
    //loome andmebaasiühenduse
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
	//valmistan ette andmebaasikäsu
	$stmt = $mysqli->prepare("INSERT INTO vpamsg2 (message) VALUES(?)");
	echo $mysqli->error;
	//asendan ettevalmistatud käsus küsimärgi(d) päris andmetega
	// esimesena kirja andmetüübid, siis andmed ise
	//s - string; i - integer; d - decimal
	$stmt->bind_param("s", $msg);
	//täidame ettevalmistatud käsu
	if ($stmt->execute()){
	  $notice = 'Sõnum: "' .$msg .'" on edukalt salvestatud!';
	} else {
	  $notice = "Sõnumi salvestamisel tekkis tõrge: " .$stmt->error;
	}
	//sulgeme ettevalmistatud käsu
	$stmt->close();
	//sulgeme ühenduse
	$mysqli->close();
	return $notice;
  }
  
  function readallmessages(){
	$notice = "";
	$mysqli = new mysqli($GLOBALS["serverHost"], $GLOBALS["serverUsername"], $GLOBALS["serverPassword"], $GLOBALS["database"]);
	$stmt = $mysqli->prepare("SELECT message FROM vpamsg2");
	echo $mysqli->error;
	$stmt->bind_result($msg);
	$stmt->execute();
	while($stmt->fetch()){
		$notice .= "<p>" .$msg ."</p> \n";
	}
	$stmt->close();
	$mysqli->close();
	return $notice;
  }
  
  //teksti sisendi kontrollimine
  function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
?>