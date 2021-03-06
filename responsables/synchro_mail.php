<?php
/*
 * $Id: synchro_mail.php 8183 2011-09-10 11:52:36Z crob $
 *
 * Copyright 2001, 2011 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
 *
 * This file is part of GEPI.
 *
 * GEPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GEPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GEPI; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// Initialisations files
require_once("../lib/initialisations.inc.php");
function saveAction($sql) {
	
	$filename = '../responsables/responsable.txt';
	$somecontent = $sql.";\n";

	// Assurons nous que le fichier est accessible en �criture
	if (is_writable($filename)) {

		if (!$handle = fopen($filename, 'a')) {
			 echo "Impossible d'ouvrir le fichier ($filename)";
			 exit;
		}

		// Ecrivons quelque chose dans notre fichier.
		if (fwrite($handle, $somecontent) === FALSE) {
			echo "Impossible d'�crire dans le fichier ($filename)";
			exit;
		}

		//echo "L'�criture de ($somecontent) dans le fichier ($filename) a r�ussi";

		fclose($handle);

	} else {
		echo "Le fichier $filename n'est pas accessible en �criture.";
	}
}

function updateOnline($sql) {
	$hostname = "173.254.25.235";
	$username = "sajoscol_gepi";
	$password = ";?5tvu45l-Lu";
	$databasename = "sajoscol_appli";
	$con = mysql_pconnect("$hostname", "$username", "$password");
	if (!$con) {
		saveAction($sql); //die('Could not connect: ' . mysql_error());
	}
	else { 
		//echo "Connexion reussi!"; 
		if(mysql_select_db($databasename, $con)) { 
			if (mysql_query($sql)) { 
				echo "<script type='text/javascript'>alert('Successly updated online!');</script>"; 
			} else {
				echo mysql_error();
			}
		}
	}
	
}

// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
    header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
}

$sql="SELECT 1=1 FROM droits WHERE id='/responsables/synchro_mail.php';";
$test=mysql_query($sql);
if(mysql_num_rows($test)==0) {
$sql="INSERT INTO droits SET id='/responsables/synchro_mail.php',
administrateur='V',
professeur='F',
cpe='F',
scolarite='V',
eleve='F',
responsable='F',
secours='F',
autre='F',
description='Synchronization of the mall responsible',
statut='';";
$insert=mysql_query($sql);updateOnline($sql);
}

if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
    die();
}


if(!isset($msg)){
	$msg="";
}

$suppr_infos_actions_diff_mail=isset($_GET['suppr_infos_actions_diff_mail']) ? $_GET['suppr_infos_actions_diff_mail'] : "n";

if((isset($_GET['synchroniser']))&&($_GET['synchroniser']=='y')) {
	check_token();

	$sql="SELECT u.*, rp.mel, rp.pers_id FROM utilisateurs u, resp_pers rp WHERE rp.login=u.login AND u.statut='responsable' AND u.email!=rp.mel ORDER BY rp.nom, rp.prenom;";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		$msg="All the responsible addresses mall are already synchronized between the
tables 'resp_pers' and 'utilisateurs'.<br />\n";
	}
	else {
		$cpt=0;
		$erreur=0;
		if(getSettingValue('mode_email_resp')=='sconet') {
			while($lig=mysql_fetch_object($res)) {
				$sql="UPDATE utilisateurs SET email='$lig->mel' WHERE login='$lig->login' AND statut='responsable';";
				$update=mysql_query($sql);updateOnline($sql);
				if($update) {
					$cpt++;
				}
				else {
					$erreur++;
				}
			}
		}
		elseif(getSettingValue('mode_email_resp')=='mon_compte') {
			while($lig=mysql_fetch_object($res)) {
				$sql="UPDATE resp_pers SET mel='$lig->email' WHERE login='$lig->login';";
				$update=mysql_query($sql);updateOnline($sql);
				if($update) {
					$cpt++;
				}
				else {
					$erreur++;
				}
			}
		}

		if($cpt==0) {
			$msg="No address was updated.<br />";
		}
		elseif($cpt==1) {
			$msg="An address was updated.<br />";
			$suppr_infos_actions_diff_mail="y";
		}
		else {
			$msg="$cpt addresses were updated.<br />";
			$suppr_infos_actions_diff_mail="y";
		}

		if($erreur==1) {
			$msg.="An error occurred.<br />";
		}
		elseif($erreur>1) {
			$msg.="$erreur errors occurred.<br />";
		}
	}
}

//if((isset($_GET['suppr_infos_actions_diff_mail']))&&($_GET['suppr_infos_actions_diff_mail']=='y')) {
if($suppr_infos_actions_diff_mail=='y') {
	check_token();

	$sql="select * from infos_actions where titre like 'Adresse mail non synchro pour%' and description like '%adresse email renseign�e par la personne via%';";
	$test_infos_actions=mysql_query($sql);
	if(mysql_num_rows($test_infos_actions)>0) {
		$sql="delete from infos_actions where titre like 'Adresse mail non synchro pour%' and description like '%adresse email renseign�e par la personne via%';";
		$del=mysql_query($sql);updateOnline($sql);
		if(!$del) {
			$msg.="ERROR during the suppression of the descriptions of difference of mall
in banner page.<br />\n";
		}
		else {
			$msg.="Suppression of the descriptions of difference of mall in banner page
carried out.<br />\n";
		}
	}
	else {
		$msg.="No description existed in banner page.<br />\n";
	}
}

//**************** EN-TETE *******************************
$titre_page = "Synchronization of the responsible addresses mail";
require_once("../lib/header.inc");
//**************** FIN EN-TETE ***************************

//debug_var();

if(!getSettingValue('conv_new_resp_table')){
	$sql="SELECT 1=1 FROM responsables";
	$test=mysql_query($sql);
	if(mysql_num_rows($test)>0){
		echo "<p>A conversion of the responsible data is necessary.</p>\n";
		echo "<p>Follow this bond: <a href='conversion.php'>CONVERT</a></p>\n";
		require("../lib/footer.inc.php");
		die();
	}

	$sql="SHOW COLUMNS FROM eleves LIKE 'ele_id'";
	$test=mysql_query($sql);
	if(mysql_num_rows($test)==0){
		echo "<p>A conversion of the data student/responsible is necessary.</p>\n";
		echo "<p>Follow this bond: <a href='conversion.php'>CONVERT</a></p>\n";
		require("../lib/footer.inc.php");
		die();
	}
	else{
		$sql="SELECT 1=1 FROM eleves WHERE ele_id=''";
		$test=mysql_query($sql);
		if(mysql_num_rows($test)>0){
			echo "<p>A conversion of the data student/responsible is necessary.</p>\n";
			echo "<p>Follow this bond: <a href='conversion.php'>CONVERT</a></p>\n";
			require("../lib/footer.inc.php");
			die();
		}
	}
}

?>
<p class='bold'><a href="index.php"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Return</a>

<?php
	$sql="select * from infos_actions where titre like 'Adresse mail non synchro pour%' and description like '%adresse email renseign�e par la personne via%';";
	$test_infos_actions=mysql_query($sql);
	if(mysql_num_rows($test_infos_actions)>0) {
		echo " | <a href='".$_SERVER['PHP_SELF']."?suppr_infos_actions_diff_mail=y".add_token_in_url()."'>Remove the descriptions of differences in banner page</a>";
	}
	echo "</p>\n";

	$sql="SELECT u.*, rp.mel, rp.pers_id FROM utilisateurs u, resp_pers rp WHERE rp.login=u.login AND u.statut='responsable' AND u.email!=rp.mel ORDER BY rp.nom, rp.prenom;";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "<p>All the responsible addresses mall are synchronized between the tables 'resp_pers' and 'utilisateurs'.</p>\n";

		require("../lib/footer.inc.php");
		die();
	}

	echo "<p>".mysql_num_rows($res)." responsible addresses mall differ between the tables 'resp_pers'and 'utilisateurs'.</p>\n";

	echo "<table class='boireaus' summary='Table of the differences'>\n";
	echo "<tr>\n";
	echo "<th>Name</th>\n";
	echo "<th>First name</th>\n";
	echo "<th>Email user<br />(<i>Manage my account</i>)</th>\n";
	echo "<th>Email resp_pers<br />(<i>Sconet,...</i>)</th>\n";
	echo "</tr>\n";
	$alt=1;
	while($lig=mysql_fetch_object($res)) {
		$alt=$alt*(-1);
		echo "<tr class='lig$alt white_hover'>\n";
		echo "<td><a href='modify_resp.php?pers_id=$lig->pers_id'>$lig->nom</a></td>\n";
		echo "<td>$lig->prenom</td>\n";
		echo "<td>$lig->email</td>\n";
		echo "<td>$lig->mel</td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";

	echo "<p>The parameter setting of synchronization is currently&nbsp;:".getSettingValue('mode_email_resp')."</p>\n";

	if(getSettingValue('mode_email_resp')=='sconet') {
		echo "<p>To update the email of the accounts of users according to the Sconet
values, <a href='".$_SERVER['PHP_SELF']."?synchroniser=y".add_token_in_url()."'>click here</a>.</p>\n";
	}
	elseif(getSettingValue('mode_email_resp')=='mon_compte') {
		echo "<p>To update the email of responsible according to the values of the
accounts of users, <a href='".$_SERVER['PHP_SELF']."?synchroniser=y".add_token_in_url()."'>click here</a>.</p>\n";
	}
	elseif(getSettingValue('mode_email_resp')=='sso') {
		echo "<p style='color:red'>Not yet managed situation.</p>\n";
	}

	if($_SESSION['statut']=='administrateur') {
		echo "<p>This parameter setting can be modified in <a href='../gestion/param_gen.php#mode_email_resp'>General configuration</a></p>\n";
	}

	echo "<p><br /></p>\n";
	require("../lib/footer.inc.php");
?>
