<?php
@set_time_limit(0);
/*
* @version: $Id: import_absences_gep.php 5971 2010-11-23 20:19:04Z crob $
*
* Copyright 2001, 2010 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
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

// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
	header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
	die();
} else if ($resultat_session == '0') {
	header("Location: ../logout.php?auto=1");
	die();
}

if (!checkAccess()) {
	header("Location: ../logout.php?auto=1");
	die();
}

//Configuration du calendrier
include("../lib/calendrier/calendrier.class.php");
$cal1 = new Calendrier("form_absences", "display_date_debut");
$cal2 = new Calendrier("form_absences", "display_date_fin");


$id_classe = isset($_POST['id_classe']) ? $_POST['id_classe'] : (isset($_GET['id_classe']) ? $_GET['id_classe'] : NULL);

$periode_num = isset($_POST['periode_num']) ? $_POST['periode_num'] : (isset($_GET['periode_num']) ? $_GET['periode_num'] : NULL);

$step = isset($_POST['step']) ? $_POST['step'] : (isset($_GET['step']) ? $_GET['step'] : NULL);

//====================================================
include "../lib/periodes.inc.php";
$acces="n";
if($ver_periode[$periode_num]=="N") {
	$acces="y";
}
elseif(($ver_periode[$periode_num]=="P")&&($_SESSION['statut']=='secours')) {
	$acces="y";
}
if($acces=="n") {
	$msg="La p�riode $periode_num est close pour cette classe.";
	header("Location:index.php?id_classe=$id_classe&msg=$msg");
}
//====================================================

if (!isset($step)) {
	// On verifie que la table absences_gep est remplie
	$test_abs_gep = mysql_query("select id_seq from absences_gep");
	if (mysql_num_rows($test_abs_gep) == 0) {
		$step_suivant = '1';
	} else {
		$step_suivant = '3';
	}

	// On verife que tous les �l�ves ont un num�ro GEP
	$test = mysql_query("select DISTINCT e.login, e.nom, e.prenom from eleves e, j_eleves_classes j where
	(
	e.login = j.login and
	j.id_classe = '".$id_classe."' and
	e.elenoet = ''
	)
	order by 'e.nom, e.prenom'
	");
	$nb_test = mysql_num_rows($test);
	if ($nb_test != '0') {
		$step = "0";
	} else {
		// Tous les �l�ves on un num�ro GEP
		// On passe directement � la suite
		header("Location: ./import_absences_gep.php?step=$step_suivant&id_classe=$id_classe&periode_num=$periode_num");
		die();
	}
}

function affiche_debug($texte) {
	$debug="n";
	if($debug=="y") {
		echo $texte;
	}
}


//include "../lib/periodes.inc.php";
//**************** EN-TETE *****************
$titre_page = "Outil d'importation des absences � partir du fichier F_EABS.DBF de la base GEP";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
?>
<p class="bold">| <a href="../accueil.php">Home</a> | <a href="index.php?id_classe=<?php echo $id_classe; ?>">Return</a> |</p>

<?php
$call_classe = mysql_query("SELECT classe FROM classes WHERE id = '$id_classe'");
$classe = mysql_result($call_classe, "0", "classe");
?>
<p><b>Class of <?php echo "$classe"; ?> -   absences Importation: <?php echo $nom_periode[$periode_num]; ?></b></p>

<?php

// On v�rifie si l'extension d_base est active
verif_active_dbase();

if ($step == 0) {
	echo "<b>CAUTION</b> : the following students do not have a <b>GEP Number</b> in the <b>GEPI</b> base. For these pupils, no data on the absences could thus be imported
	<ul>
	<li>That is to say continue the importation and you choose to fill later on with the hand the headings absences for these pupils,</li>
	<li>either you must, before proceeding to the importation of the absences,informing number GEP of these pupils by modifying their card (see the administrator of GEPI).</li >
	</ul>\n";
	//echo "<table border=\"1\" cellpadding=\"5\" cellspacing=\"1\">
	//<tr><td><b>Identifiant</b></td><td><b>Nom</b></td><td><b>Pr�nom</b></td></tr>\n";
	echo "<table class='boireaus' cellpadding=\"5\" cellspacing=\"1\">
	<tr><th><b>Identifier</b></th><th><b>Name</b></th><th><b>First name</b></th></tr>\n";
	$i = 0;
	$alt=1;
	while ($i < $nb_test) {
		$alt=$alt*(-1);
		$login_eleve = mysql_result($test,$i,'e.login');
		$nom_eleve = mysql_result($test,$i,'e.nom');
		$prenom_eleve = mysql_result($test,$i,'e.prenom');
		//echo "<tr><td>$login_eleve</td><td>$nom_eleve</td><td>$prenom_eleve</td></tr>\n";
		echo "<tr class='lig$alt'><td>$login_eleve</td><td>$nom_eleve</td><td>$prenom_eleve</td></tr>\n";
		$i++;
	}
	echo "</table>\n";

	echo "<form enctype=\"multipart/form-data\" action=\"import_absences_gep.php\" method=\"post\" name=\"form_absences\">\n";
	echo "<input type=hidden name='step' value='$step_suivant' />\n";
	echo "<input type=hidden name='id_classe' value='".$id_classe."' />\n";
	echo "<input type=hidden name='periode_num' value='".$periode_num."' />\n";
	echo "<p align=\"center\"><input type=submit value=\"Continuer l'importation\" /></p>\n";
	echo "</form>\n";
} else if ($step==1) {
	// On demande le fichier F_NOMA.DBF
	echo "<form enctype=\"multipart/form-data\" action=\"import_absences_gep.php\" method=\"post\" name=\"form_absences\">\n";
	echo add_token_field();
	echo "<p class='bold'>Phase of importation of the sequences related to the morning and the sequences related to the afternoon</p>
	<p>Please specify the complete name of the file <b>F_NOMA.DBF</b> :";
	echo "<input type='file' size='80' name='dbf_file' /></p>\n";
	echo "<p>(In general, file F_NOMA.DBF is in the  Folder relative of the Folder containing file F_EABS.DBF.)</p>\n";				
	echo "<p align=\"center\"><input type=submit value='Validate' /></p>\n";
	echo "<input type=hidden name='step' value='2' />\n";
	echo "<input type=hidden name='id_classe' value='".$id_classe."' />\n";
	echo "<input type=hidden name='periode_num' value='".$periode_num."' />\n";
	echo "</form>\n";

	// On verifie que la table absences_gep est remplie
	$test_abs_gep = mysql_query("select id_seq from absences_gep");
	if (mysql_num_rows($test_abs_gep) != 0) {
		echo "<hr /><form enctype=\"multipart/form-data\" action=\"import_absences_gep.php\" method=\"post\" name=\"form_absences\">\n";
		echo add_token_field();
		echo "<p align=\"center\"><input type=submit value=\"continue without proceeding to the importation\" /></p>\n";
		echo "<input type=hidden name='step' value='3' />\n";
		echo "<input type=hidden name='id_classe' value='".$id_classe."' />\n";
		echo "<input type=hidden name='periode_num' value='".$periode_num."' />\n";
		echo "</form>\n";
	}

} else if ($step==2) {
	check_token(false);
	// On enregistre les donn�es du fichier F_NOMA.DBF dans la table absences_gep

	$dbf_file = isset($_FILES["dbf_file"]) ? $_FILES["dbf_file"] : NULL;
	if(strtoupper($dbf_file['name']) == "F_NOMA.DBF") {
			if (is_uploaded_file($dbf_file['tmp_name'])) {
			$fp = dbase_open($dbf_file['tmp_name'], 0);
			if(!$fp) {
				echo "<p>Impossible to open the file dbf</p>\n";
				echo "<p><a href='import_absences_gep.php?id_classe=$id_classe&amp;periode_num=$periode_num&amp;step=1'> click here </a> to retry !</center></p>\n";
			} else {
				// on constitue le tableau des champs � extraire
				$tabchamps = array("TYPE","CODE","CHOIX");
				//TYPE : Type de la donn�e
				//CODE  : Intitul� de la s�quence
				//CHOIX : M pour matin ou S pour soir

				$nblignes = dbase_numrecords($fp); //number of rows
				$nbchamps = dbase_numfields($fp); //number of fields

				if (@dbase_get_record_with_names($fp,1)) {
					$temp = @dbase_get_record_with_names($fp,1);
				} else {
					echo "<p>The selected file is not valid !<br />\n";
					echo "<a href='import_absences_gep.php?id_classe=$id_classe&amp;periode_num=$periode_num&amp;step=1'>click here </a> to retry !</center></p>\n";
					die();
				}

				$nb = 0;
				foreach($temp as $key => $val){
					$en_tete[$nb] = $key;
					$nb++;
				}

				// On range dans tabindice les indices des champs retenus
				for ($k = 0; $k < count($tabchamps); $k++) {
					for ($i = 0; $i < count($en_tete); $i++) {
						if ($en_tete[$i] == $tabchamps[$k]) {
							$tabindice[] = $i;
						}
					}
				}

				// On vide la table absences_gep
				$req = mysql_query("delete from absences_gep");

				$erreur = 'no';
				for($k = 1; ($k < $nblignes+1); $k++){
					$ligne = dbase_get_record($fp,$k);
					for($i = 0; $i < count($tabchamps); $i++) {
						$affiche[$i] = dbase_filter(trim($ligne[$tabindice[$i]]));
					}
					// On rep�re les lignes qui sont en rapport avec les s�quences
					if ($affiche[0] == "S") {
					$reg = mysql_query("insert into absences_gep set id_seq='$affiche[1]', type='$affiche[2]'");
					if (!$reg) $erreur = 'yes';
					}
				}
				dbase_close($fp);
				echo "<p class='bold'>Phase of importation of the sequences related to the morning and the sequences related to the afternoon</p>\n";
				if ($erreur == 'no') {
					echo "The data of files F_NOMA.DBF were recorded.
					<br /><b><a href=\"javascript:centrerpopup('seq_gep_absences.php',600,480,'scrollbars=yes,statusbar=no,resizable=yes')\">visualize the correspondences between sequences and types of half-days</a></b>\n";
					echo "<form enctype=\"multipart/form-data\" action=\"import_absences_gep.php\" method=\"post\" name=\"form_absences\">\n";
					echo add_token_field();
					echo "<p align=\"center\"><input type=submit value=\"continue the importation\" /></p>\n";
					echo "<input type=hidden name='step' value='3' />\n";
					echo "<input type=hidden name='id_classe' value='".$id_classe."' />\n";
					echo "<input type=hidden name='periode_num' value='".$periode_num."' />\n";
					echo "</form>\n";
				} else {
					echo "<b>CAUTION</b> : There were one or more errors during the recording of the data of file F_NOMA.DBF.";
				}
			}
			}
	} else if (trim($dbf_file['name'])=='') {
		echo "<p>No file was selected !<br />\n";
		echo "<a href='import_absences_gep.php?id_classe=$id_classe&amp;periode_num=$periode_num&amp;step=1'>click here </a> to start again !</center></p>\n";
	} else {
		echo "<p>The selected file is not valid !<br />\n";
		echo "<a href='import_absences_gep.php?id_classe=$id_classe&amp;periode_num=$periode_num&amp;step=1'>click here </a> to start again !</center></p>\n";
	}

} else if ($step==3) {
	check_token(false);

	echo "<form enctype=\"multipart/form-data\" action=\"import_absences_gep.php\" method=\"post\" name=\"form_absences\">\n";
	echo add_token_field();
	echo "<p><b>CAUTION !</b> PLEASE READ WHAT FOLLOWS</p>\n";
	echo "<p>The importation of the data relating to the absences from GEP is a delicate task. Indeed, the files of GEP are with format DBF, GEP does not respect this standard strictly.";
	echo "<p>Warning : you MUST follow to the letter the procedure described below in order to obtain a reliable importation.</p>\n";
	echo "<p class='bold'>If you omit a stage, no error message will announce you a bad imported value.</p>\n";
	echo "<p class='bold'>PROCEDURE</p>\n";
	echo "<ul type='1'><li>Take the file F_EABS.DBF from the folder of GEP and copy it in a separate folder.</li>\n";
	echo "<li>Open the file thus copied in a spreadsheet (preferably OpenOffice.org,but Excel also functions).</li>\n";
	echo "<li>Select the whole of the data (Edition->select all), then carry out a sorting (Data->sort) on the columns B (ELENOET) et C (ABSDATD) in the ascending order. Do not forget to mention in the options of sorting that the first line corresponds to the labels of columns.</li>\n";
	echo "<li>Record the file,still keeping the origin format  (File->Enregistrer).</li>\n";
	echo "<li>Charge the file thus modified (only in its structure, you do not have to change any data) in the fields below</li>\n";
	echo "<li>Indicate the temporal limits to respect during the importation, in the two fields dates envisaged below.Use the sign  / as delimitor between the days, month, and year, as the already visible dates in the fields of typing illustrate it.</li>\n";
	echo "<li>If the class for which you carry out the importation has course saturdays morning, notch the box allowing the taking into account of Saturday morning.</li>\n";
	echo "</ul>\n";
	echo "<p><b>CAUTION !</b>The DBF File which you used for this importation is not compatible any more GEP. Once the imports carry out, thus destroy this file, and continue to use the original with GEP.";
	echo "<p><b>Note :</b> stages 1 to 4 are to be carried out only when you set out again of original file GEP. If you carry out the operations of importation to the chain for all the classes, you have to carry out these operations only once.</p>\n";
	echo "<p class='bold'>MAKE THE IMPORTATION</p>\n";
	echo "<ul><li>Importation of the file <b>F_EABS.DBF</b> containing the data relating to the absences : <br />
	please specify the complete path to the file <b>F_EABS.DBF</b> : ";
	echo "<input type='file'  size='80' name='dbf_file' /><br /><br /></li>\n";
	echo "<li><b>Choose the period (format jj/mm/aaaa) : </b>\n";
	$annee = strftime("%Y");
	$mois = strftime("%m");
	$jour = strftime("%d");

	//=========================
	// MODIF: boireaus 20071118
	// Pour �viter de refaire le choix des dates en changeant de classe, on utilise la SESSION...
	/*
	if (!isset($_POST['display_date_debut'])) $display_date_debut = $jour."/".$mois."/".$annee;
	if (!isset($_POST['display_date_fin'])) $display_date_fin = $jour."/".$mois."/".$annee;
	*/
	$display_date_debut=isset($_POST['display_date_debut']) ? $_POST['display_date_debut'] : (isset($_SESSION['display_date_debut']) ? $_SESSION['display_date_debut'] : $jour."/".$mois."/".$annee);
	$display_date_fin=isset($_POST['display_date_fin']) ? $_POST['display_date_fin'] : (isset($_SESSION['display_date_fin']) ? $_SESSION['display_date_fin'] : $jour."/".$mois."/".$annee);
	//=========================

	echo "<a name=\"calend\"></a>de la date : ";
	echo "<input type='text' name = 'display_date_debut' size='10' value = \"".$display_date_debut."\" />\n";
	echo "<a href=\"#calend\" onClick=\"".$cal1->get_strPopup('../lib/calendrier/pop.calendrier.php', 350, 170)."\"><img src=\"../lib/calendrier/petit_calendrier.gif\" border=\"0\" alt=\"Calendrier\" /></a>\n";
	echo "&nbsp;� la date : ";
	echo "<input type='text' name = 'display_date_fin' size='10' value = \"".$display_date_fin."\" />\n";
	echo "<a href=\"#calend\" onClick=\"".$cal2->get_strPopup('../lib/calendrier/pop.calendrier.php', 350, 170)."\"><img src=\"../lib/calendrier/petit_calendrier.gif\" border=\"0\" alt=\"Calendrier\" /></a>\n";


	//=========================
	// Modif: boireaus 20080225
	echo "<li>To include saturdays morning in the calculation of the half-days of absence <input type=checkbox name='samedi_compte' value='yes' ";

	if(isset($_SESSION['samedi_compte'])) {
		if($_SESSION['samedi_compte']=="yes") {
			echo "checked ";
		}
	}

	echo "/></li>\n";
	//=========================


	//=========================
	// Ajout: boireaus 20080225
	echo "<li>Do not  include Wednesday afternoon in the calculation of the half-days of absence <input type=checkbox name='mercredi_apm_compte' value='no' ";

	if(isset($_SESSION['mercredi_apm_compte'])) {
		if($_SESSION['mercredi_apm_compte']=="no") {
			echo "checked ";
		}
	}

	echo "/></li>\n";
	//=========================

	//echo "</li></ul>\n";
	echo "</ul>\n";
	echo "<input type=hidden name='step' value='4' />\n";
	echo "<input type=hidden name='id_classe' value='".$id_classe."' />\n";
	echo "<input type=hidden name='periode_num' value='".$periode_num."' />\n";
	echo "<p align=\"center\"><input type=submit value='Validate' /></p>\n";
	echo "</form>\n";
	echo "<hr /><b>Remarque</b><br /><br />Data, resulting from the file \"F_NOMA.DBF\" (base GEP), concerning
	<b><a href=\"javascript:centrerpopup('seq_gep_absences.php',600,480,'scrollbars=yes,statusbar=no,resizable=yes')\">correspondences enter sequences and type of half-days</a></b>
	are presented in base GEPI. If these data are not exact any more, you can proceed to a <b><a href='import_absences_gep.php?id_classe=$id_classe&amp;periode_num=$periode_num&amp;step=1'>new importation</a></b>.";


} else if ($step==4) {
	check_token(false);

	//=========================
	// AJOUT: boireaus 20071118
	$_SESSION['display_date_debut']=$_POST['display_date_debut'];
	$_SESSION['display_date_fin']=$_POST['display_date_fin'];
	// Pour �viter de refaire le choix des dates en changeant de classe, on utilise la SESSION...
	/*
	echo "\$_SESSION['display_date_debut']=".$_SESSION['display_date_debut']."<br />";
	echo "\$_SESSION['display_date_fin']=".$_SESSION['display_date_fin']."<br />";
	require("../lib/footer.inc.php");
	die();
	*/
	//=========================


	//=========================
	// AJOUT: boireaus 20071202
	$samedi_compte=isset($_POST['samedi_compte']) ? $_POST['samedi_compte'] : "no";
	//=========================

	//=========================
	// AJOUT: boireaus 20080225
	$mercredi_apm_compte=isset($_POST['mercredi_apm_compte']) ? $_POST['mercredi_apm_compte'] : "yes";
	$_SESSION['mercredi_apm_compte']=$_POST['mercredi_apm_compte'];
	$_SESSION['samedi_compte']=$_POST['samedi_compte'];
	//=========================

	// On fait quelques tests quand m�me, histoire de voir si les dates saisies sont coh�rentes

	// Extraction des dates de d�but et de fin
	$sep_date_d = explode("/", $_POST['display_date_debut']);
	$sep_date_f = explode("/", $_POST['display_date_fin']);

	$anneed = $sep_date_d['2'];
	$moisd = $sep_date_d['1'];
	$jourd = $sep_date_d['0'];
	$date_d_timestamp = mktime(0, 0, 0, $moisd, $jourd, $anneed);

	$datedebut = strftime("%Y%m%d", $date_d_timestamp);

	$anneef = $sep_date_f['2'];
	$moisf = $sep_date_f['1'];
	$jourf = $sep_date_f['0'];
	$date_f_timestamp = mktime(0, 0, 0, $moisf, $jourf, $anneef);
	$datefin = strftime("%Y%m%d", $date_f_timestamp);

	if ($date_f_timestamp < $date_d_timestamp) {
		echo "<p>The completion date of the period of importation precedes the beginning date! Please start again the typing of the dates.</p>\n";
		echo "<p><a href='import_absences_gep.php?id_classe=$id_classe&amp;periode_num=$periode_num&amp;step=3'>click here </a> to retry !</center></p>\n";
		die();
	}

	// Test sur les dates.

	// traitement du fichier GEP

	// Conctitution du tableau sequence<->type
	$tab_seq = array();
	$sql = mysql_query("select id_seq, type  from absences_gep order by id_seq");
	$i = 0;
	while ($i < mysql_num_rows($sql)) {
		$id_seq = mysql_result($sql,$i,'id_seq');
		$tab_seq[$id_seq] = mysql_result($sql,$i,'type');
		$i++;
	}
	// Constitution du tableau login<->num�ro gep
	$tab = array();
	$abs = array();
	$abs_nj = array();
	$retard = array();
	$req_eleves = mysql_query("select DISTINCT e.login, e.elenoet from eleves e, j_eleves_classes j where (
	e.login = j.login and
	j.id_classe = '".$id_classe."'
	)
	order by e.nom, e.prenom");
	$i = 0;
	while ($i < mysql_num_rows($req_eleves)) {
		$login_eleve = mysql_result($req_eleves,$i,'e.login');
		$elenoet = mysql_result($req_eleves,$i,'e.elenoet');
		if ($elenoet != '') $tab[$login_eleve] = $elenoet;
		$i++;
	}
	// Initialisation des tableaux retard et absences
	foreach ($tab as $key => $value) {
		$abs[$key] = 0;
		$abs_nj[$key] = 0;
		$retard[$key] = 0;
	}
	$dbf_file = isset($_FILES["dbf_file"]) ? $_FILES["dbf_file"] : NULL;
	if ($dbf_file!= null AND !is_uploaded_file($dbf_file['tmp_name'])) $dbf_file = null;
	if(strtoupper($dbf_file['name']) == "F_EABS.DBF") {
		$fp = dbase_open($dbf_file['tmp_name'], 0);
		if(!$fp) {
			echo "<p>Impossible d'ouvrir le fichier dbf</p>\n";
			echo "<p><a href='import_absences_gep.php?id_classe=$id_classe&amp;periode_num=$periode_num&amp;step=3'>click here </a> to retry !</center></p>\n";
		} else {
			$tab_date = array();
			// on constitue le tableau des champs � extraire
			$tabchamps = array("ABSTYPE","ELENOET","ABSDATD","ABSDATF","ABSSEQD","ABSSEQF","ABSHEUR","ABSJUST","ABSMOTI","ABSACTI");
			//ABSTYPE : Absence ou Retard ou Infirmerie
			//ELENOET : num�ro de l'�l�ve
			//ABSDATD : date de d�but de l'absence
			//ABSDATF : date de fin de l'absence
			//ABSSEQD : num�ro de la s�quence de d�but de l'absence
			//ABSSEQF : num�ro de la s�quence de fin de l'absence
			//ABSHEUR : heure de rentr�e dans la cas d'un retard
			//ABSJUST : justification (Oui ou Non)
			//ABSMOTI : Motif
			//ABSACTI : ???? prend les valeurs suivantes AT, LE, CO, ... ?

			$nblignes = dbase_numrecords($fp); //number of rows
			$nbchamps = dbase_numfields($fp);  //number of fields

			if (@dbase_get_record_with_names($fp,1)) {
				$temp = @dbase_get_record_with_names($fp,1);
			} else {
				echo "<p>The selected file is not valid !<br />\n";
				echo "<a href='import_absences_gep.php?id_classe=$id_classe&amp;periode_num=$periode_num&amp;step=3'>click here </a> to retry !</center></p>\n";
				die();
			}

			$nb = 0;
			foreach($temp as $key => $val){
				$en_tete[$nb] = $key;
				$nb++;
			}

			// On range dans tabindice les indices des champs retenus
			for ($k = 0; $k < count($tabchamps); $k++) {
				for ($i = 0; $i < count($en_tete); $i++) {
					if ($en_tete[$i] == $tabchamps[$k]) {
						$tabindice[] = $i;
					}
				}
			}

			affiche_debug("<table border=\"1\">\n");
			affiche_debug("<tr><td>Num.</td><td>ABSTYPE</td><td>ELENOET</td><td>ABSDATD</td><td>ABSDATF</td><td>ABSSEQD</td><td>ABSSEQF</td><td>ABSHEUR</td><td>ABSJUST</td><td>ABSMOTI</td><td>ABSACTI</td></tr>\n");

			//=========================
			// AJOUT: boireaus 20071202
			$previous_eleve="";
			//=========================

			$nb_reg_no = 0;
			$nb_record = 0;
			for($k = 1; ($k < $nblignes+1); $k++){

//                echo "<tr><td>$k</td>\n";
				$ligne = dbase_get_record($fp,$k);
				for($i = 0; $i < count($tabchamps); $i++) {
					$affiche[$i] = dbase_filter(trim($ligne[$tabindice[$i]]));
				}

				// premier tri sur les dates
				if (($affiche[2] >= $datedebut) and ($affiche[3] <= $datefin)) {

					if ($temp=array_search($affiche[1], $tab)) {


						affiche_debug("<tr>\n");
						affiche_debug("<td>$k</td>\n");
						for($loop=0;$loop<count($affiche);$loop++){affiche_debug("<td>$affiche[$loop]</td>\n");}
						affiche_debug("</tr>\n");


						// Pour que les erreurs s'affichent au bon niveau:
						affiche_debug("<tr>\n");
						affiche_debug("<td colspan='11'>\n");


						// on comptabilise les retards
						if ($affiche[0] == 'R') $retard[$temp]++;
						// on comptabilise les absences

						// Prise en compte du changement d'heure !
						$test_timechange1 = mktime(0, 0, 0, 3, 27, 2005);
						$test_timechange2 = mktime(0, 0, 0, 10, 30, 2005);

						$test_timechange3 = mktime(0, 0, 0, 3, 26, 2006);
						$test_timechange4 = mktime(0, 0, 0, 10, 29, 2006);

						$test_timechange5 = mktime(0, 0, 0, 3, 25, 2007);
						$test_timechange6 = mktime(0, 0, 0, 10, 28, 2007);

						$test_timechange7 = mktime(0, 0, 0, 3, 30, 2008);
						$test_timechange8 = mktime(0, 0, 0, 10, 26, 2008);

						$test_timechange9 = mktime(0, 0, 0, 3, 29, 2009);
						$test_timechange10 = mktime(0, 0, 0, 10, 25, 2009);

						$test_timechange11 = mktime(0, 0, 0, 3, 28, 2010);
						$test_timechange12 = mktime(0, 0, 0, 10, 31, 2010);

						$test_timechange13 = mktime(0, 0, 0, 3, 27, 2011);
						$test_timechange14 = mktime(0, 0, 0, 10, 30, 2011);

						if (($affiche[0] == 'A') and ($affiche[2] != '') and ($affiche[3] != '') and ($affiche[4] != '') and ($affiche[5] != ''))  {
							affiche_debug("\$previous_eleve=$previous_eleve<br />");

							$debut_a = mktime(0, 0, 0, substr($affiche[2],4,2), substr($affiche[2],6,2), substr($affiche[2],0,4));
							$fin_a = mktime(0, 0, 0, substr($affiche[3],4,2), substr($affiche[3],6,2), substr($affiche[3],0,4));

							affiche_debug("\$debut_a=$debut_a<br />");
							affiche_debug("\$fin_a=$fin_a<br />");

							// Prise en compte du changement d'heure
							if (($test_timechange1 > $debut_a AND $test_timechange1 < $fin_a)
								OR ($test_timechange3 > $debut_a AND $test_timechange3 < $fin_a)
								OR ($test_timechange5 > $debut_a AND $test_timechange5 < $fin_a)
								OR ($test_timechange7 > $debut_a AND $test_timechange7 < $fin_a)
								OR ($test_timechange9 > $debut_a AND $test_timechange9 < $fin_a)
								OR ($test_timechange11 > $debut_a AND $test_timechange11 < $fin_a)
								OR ($test_timechange13 > $debut_a AND $test_timechange13 < $fin_a)
							) {
								$modifier = "3600";
							} elseif (($test_timechange2 > $debut_a AND $test_timechange2 < $fin_a)
								OR ($test_timechange4 > $debut_a AND $test_timechange4 < $fin_a)
								OR ($test_timechange6 > $debut_a AND $test_timechange6 < $fin_a)
								OR ($test_timechange8 > $debut_a AND $test_timechange8 < $fin_a)
								OR ($test_timechange10 > $debut_a AND $test_timechange10 < $fin_a)
								OR ($test_timechange12 > $debut_a AND $test_timechange12 < $fin_a)
								OR ($test_timechange14 > $debut_a AND $test_timechange14 < $fin_a)
							) {
								$modifier = "-3600";
							} else {
								$modifier = "0";
							}

							affiche_debug("\$modifier=$modifier<br />");

							//$nb_demi_jour = (($fin_a - $debut_a)/(60*60*24)+1)*2; // Sans prise en compte du changement d'heure
							$nb_demi_jour = (($fin_a - $debut_a + $modifier)/(60*60*24)+1)*2;  // Avec prise en compte du changement d'heure
							affiche_debug("\$nb_demi_jour=$nb_demi_jour<br />");

							affiche_debug("<p>Test : " . $affiche[1] . " " . $debut_a . ":" . $fin_a . ":" . $nb_demi_jour ."</p>\n");// Quelques tests de d�buggage
							if ($tab_seq[$affiche[4]] == "S") $nb_demi_jour--;
							if ($tab_seq[$affiche[5]] == "M") $nb_demi_jour--;
							affiche_debug("Avant d�compte des samedi/dimanche: \$nb_demi_jour=$nb_demi_jour<br />");

							// Question de la prise en compte des demi-journ�es de week-end : on filtre les samedi et dimanche.
							$jour_debut = strftime("%u", $debut_a);
							//$jour_fin = strftime("%u", $fin_a);
							$duree_a = (($fin_a - $debut_a + $modifier)/(60*60*24)+1);
							affiche_debug("\$duree_a=$duree_a<br />");

							// Est-ce qu'on a un week-end dans la p�riode d'absence ?

							$w = 0;
							while (($duree_a + $jour_debut - 1) >= (7 + $w)) {
								$temp_var=$duree_a + $jour_debut - 1;
								affiche_debug("<p>WEEK-END : <br />");
								affiche_debug("\$duree_a + \$jour_debut - 1 = ".$duree_a." + ".$jour_debut." - 1 = ".$temp_var."<br />");
								$temp_var=7 + $w;
								affiche_debug("7 + \$w = 7 + ".$w." = ".$temp_var."<br />");
								//=========================
								// MODIF: boireaus 20071202
								//if ($_POST['samedi_compte'] == "yes") {
								if ($samedi_compte == "yes") {
								//=========================
									$nb_demi_jour -= 3;
									$temp_test = 3;
								} else {
									$nb_demi_jour -= 4;
									$temp_test = 4;
								}
								affiche_debug($temp_test . " withdrawn half-days of calculation (beginning : $jour_debut ; end : $jour_fin)</p>\n");
								$w += 7;
							}
							affiche_debug("After calculation of the samedi/dimanche: \$nb_demi_jour=$nb_demi_jour<br />");

							// D�compte des mercredi apr�s-midi:
							if($mercredi_apm_compte=="no") {
								// On v�rifie s'il y a un mercredi dans la p�riode.

								$j_debut=strftime("%d", $debut_a);
								$m_debut=strftime("%m", $debut_a);
								$y_debut=strftime("%Y", $debut_a);
								$timestamp_debut_test_mercr=mktime(5,0,0,$m_debut,$j_debut,$y_debut);
								$d=0;
								while($timestamp_debut_test_mercr+$d*3600*24<$fin_a) {
									affiche_debug("Test du ".strftime("%a %d/%m/%Y",$timestamp_debut_test_mercr+$d*3600*24)."\n");
									if(strftime("%u",$timestamp_debut_test_mercr+$d*3600*24)==3) {
										affiche_debug(" MERCREDI: -1 demi-journ�e\n");
										$nb_demi_jour--;
									}
									affiche_debug("<br />\n");
									$d++;
								}
							}


							// On regarde si l'on n'a pas d�j� enregistr� une absence pour la demi-journ�e concern�e

							$current_eleve = $affiche[1];
							if ($current_eleve != $previous_eleve) {
								$tab_date = array();
								affiche_debug("<hr width='200'/>\n");
							}
							$previous_eleve = $current_eleve;

							$current_d_date = $affiche[2] . $tab_seq[$affiche[4]];
							$current_f_date = $affiche[3] . $tab_seq[$affiche[5]];
							affiche_debug("<p>" . $affiche[1] . " : $current_d_date :: $current_f_date</p>\n");
							affiche_debug("<p>\$tab_date[$current_d_date]=".$tab_date[$current_d_date]."</p>\n");

							//if ($tab_date[$current_d_date] == "yes") {
							if ((isset($tab_date[$current_d_date]))&&($tab_date[$current_d_date] == "yes")) {
								$nb_demi_jour--;
							} else {
								$tab_date[$current_d_date] = "yes";
								$tab_date[$current_f_date] = "yes";
							}
							affiche_debug("\$nb_demi_jour=$nb_demi_jour<br />");



							$abs[$temp]  += $nb_demi_jour;
							affiche_debug("\$abs[$temp]=$abs[$temp]<br />");
							if ($affiche[7] == 'N') $abs_nj[$temp] += $nb_demi_jour;
							affiche_debug("\$abs_nj[$temp]=$abs_nj[$temp]<br />");


						}
					/*
					for($i = 0; $i < count($tabchamps); $i++) {
							echo "<td bgcolor=\"#FF0000\">".$affiche[$i]."</td>\n";
						}
					} else {
						for($i = 0; $i < count($tabchamps); $i++) {
							echo "<td bgcolor=\"#00FF80\">".$affiche[$i]."</td>\n";
						}
					*/
						affiche_debug("</td>\n");
						affiche_debug("</tr>\n");
					}
				/*
				} else {
					for($i = 0; $i < count($tabchamps); $i++) {
						echo "<td>".$affiche[$i]."</td>\n";
					}
				*/
				}

				//echo "</tr>\n";
			}
			dbase_close($fp);
			//echo "</table>\n";
			// On affiche le tableau de la classe :

			$tab_session=serialize($tab);
			$retard_session =serialize($retard);
			$abs_session =serialize($abs);
			$abs_nj_session =serialize($abs_nj);
			$_SESSION['tab_session']=$tab_session;
			$_SESSION['retard_session']=$retard_session;
			$_SESSION['abs_session']=$abs_session;
			$_SESSION['abs_nj_session']=$abs_nj_session;

			echo "<p>Summary table of the absences for the period of <b>".$jourd."/".$moisd."/".$anneed."</b> au <b>".$jourf."/".$moisf."/".$anneef."</b></p>\n";
			echo "<p><b>Caution </b>: the data are not recorded yet in GEPI base .</p>\n";
			echo "<form enctype=\"multipart/form-data\" action=\"import_absences_gep.php\" method=\"post\" name=\"form_absences\">\n";
			echo add_token_field();
			echo "<p align=\"center\"><input type=submit value=\"Record the data in base GEPI\" /></p>\n";
			echo "<input type=hidden name='step' value='5' />\n";
			echo "<input type=hidden name='id_classe' value='".$id_classe."' />\n";
			echo "<input type=hidden name='periode_num' value='".$periode_num."' />\n";
			echo "</form>\n";
			//echo "<table border=\"1\" cellpadding=\"3\"><tr><td><b>Nom pr�nom</b></td><td><b>Nb. de retards</b></td><td><b>Nb. de 1/2 journ�es d'absence</b></td><td><b>1/2 j. non justifi�es</b></td></tr>\n";
			echo "<table class='boireaus' cellpadding=\"3\"><tr><th><b>Name Surname</b></th><th><b>Nb. of delays</b></th><th><b>Nb. of 1/2 days of absence</b></th><th><b>1/2 D. not justified</b></th></tr>\n";
			$alt=1;
			foreach ($tab as $key => $value) {
				$alt=$alt*(-1);
				$nom_eleve = sql_query1("select nom from eleves where login = '".$key."'");
				$prenom_eleve = sql_query1("select prenom from eleves where login = '".$key."'");
				$num_gep = sql_query1("select elenoet from eleves WHERE login ='".$key."'");
				//echo "<tr><td>$nom_eleve $prenom_eleve</td><td>$retard[$key]</td><td>$abs[$key]</td><td>$abs_nj[$key]</td></tr>\n";
				echo "<tr class='lig$alt'><td>$nom_eleve $prenom_eleve";
				echo " (<i>$num_gep</i>)";
				echo "</td><td>$retard[$key]</td><td>$abs[$key]</td><td>$abs_nj[$key]</td></tr>\n";
			}
			echo "</table>\n";


		}
	} else if (trim($dbf_file['name'])=='') {
		echo "<p>No file was selected !<br />\n";
		echo "<a href='import_absences_gep.php?id_classe=$id_classe&amp;periode_num=$periode_num&amp;step=3'> click here </a> to try again !</center></p>\n";

	} else {
		echo "<p>The selected file is not valid !<br />\n";
		echo "<a href='import_absences_gep.php?id_classe=$id_classe&amp;periode_num=$periode_num&amp;step=3'>click here </a> to try again!</center></p>\n";
	}
} else if ($step = 5) {
	check_token(false);

	$tab=unserialize($_SESSION['tab_session']);
	$retard=unserialize($_SESSION['retard_session']);
	$abs=unserialize($_SESSION['abs_session']);
	$abs_nj=unserialize($_SESSION['abs_nj_session']);
	echo "Absences importation is finished.<br />Reach <a href='./saisie_absences.php?id_classe=$id_classe&amp;periode_num=$periode_num'>page of typing of the absences</a> for checking.";
	echo "<ul>\n";
	foreach ($tab as $key => $value) {
		$nom_eleve = sql_query1("select nom from eleves where login = '".$key."'");
		$prenom_eleve = sql_query1("select prenom from eleves where login = '".$key."'");
		$test_eleve_nb_absences_query = mysql_query("SELECT * FROM absences WHERE (login='$key' AND periode='$periode_num')");
		$test_nb = mysql_num_rows($test_eleve_nb_absences_query);
		if ($test_nb != "0") {
			$register = mysql_query("UPDATE absences
			SET nb_absences='".$abs[$key]."',
			non_justifie='".$abs_nj[$key]."',
			nb_retards='".$retard[$key]."'
			WHERE (login='".$key."' AND periode='".$periode_num."')");
		} else {
			$register = mysql_query("INSERT INTO absences SET
			login='".$key."',
			periode='".$periode_num."',
			nb_absences='".$abs[$key]."',
			non_justifie='".$abs_nj[$key]."',
			nb_retards='".$retard[$key]."',
			appreciation=''");
		}
		if (!$register) {
			echo "<li><font color=\"#FF0000\">Error during the recording of the data of the Student $prenom_eleve $nom_eleve</font></li>\n";
		} else {
			echo "<li>Data concerning the Student $prenom_eleve $nom_eleve were correctly recorded.</li>\n";
		}
	}
	echo "</ul>\n";

}

echo "<p><br /></p>\n";

require("../lib/footer.inc.php");

?>