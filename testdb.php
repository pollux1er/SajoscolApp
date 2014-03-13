<?php

$hostname = "173.254.25.235";
$username = "sajoscol_gepi";
$password = ";?5tvu45l-Lu";
$databasename = "sajoscol_appli";
$con = mysql_pconnect("$hostname", "$username", "$password");
if (!$con) {
	//saveAction($sql); 
	die('Could not connect: ' . mysql_error());
}
else { 
	echo "Connexion reussi!"; 
	/*if(mysql_select_db($databasename, $con)) { 
		if (mysql_query($sql)) { 
			echo "<script type='text/javascript'>alert('Successly updated online!');</script>"; 
		} else {
			echo mysql_error();
		}
	}*/
}

?>
