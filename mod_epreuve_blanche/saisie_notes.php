<?php
/* $Id: saisie_notes.php 6755 2011-04-08 17:46:27Z crob $ */
/*
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

$variables_non_protegees = 'yes';

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



$sql="SELECT 1=1 FROM droits WHERE id='/mod_epreuve_blanche/saisie_notes.php';";
$test=mysql_query($sql);
if(mysql_num_rows($test)==0) {
$sql="INSERT INTO droits SET id='/mod_epreuve_blanche/saisie_notes.php',
administrateur='V',
professeur='V',
cpe='F',
scolarite='V',
eleve='F',
responsable='F',
secours='F',
autre='F',
description='White test: Seizure of the notes',
statut='';";
$insert=mysql_query($sql);
}

//======================================================================================
// Section checkAccess() � d�commenter en prenant soin d'ajouter le droit correspondant:
if (!checkAccess()) {
	header("Location: ../logout.php?auto=1");
	die();
}
//======================================================================================

$id_epreuve=isset($_POST['id_epreuve']) ? $_POST['id_epreuve'] : (isset($_GET['id_epreuve']) ? $_GET['id_epreuve'] : NULL);
$mode=isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : NULL);

if(isset($_POST['saisie_notes'])) {
	check_token();

	$sql="SELECT * FROM eb_epreuves WHERE id='$id_epreuve';";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		$msg="The selected test (<i>$id_epreuve</i>) do not exist.\n";
	}
	else {
		$lig=mysql_fetch_object($res);
		$etat=$lig->etat;
		$note_sur=$lig->note_sur;
	
		if($etat!='clos') {
		
			$n_anonymat=isset($_POST['n_anonymat']) ? $_POST['n_anonymat'] : (isset($_GET['n_anonymat']) ? $_GET['n_anonymat'] : array());
			$note=isset($_POST['note']) ? $_POST['note'] : (isset($_GET['note']) ? $_GET['note'] : array());
		
			$msg="";
		
			for($i=0;$i<count($n_anonymat);$i++) {
				$saisie="y";
				if($_SESSION['statut']=='professeur') {
					$sql="SELECT 1=1 FROM eb_copies WHERE id_epreuve='$id_epreuve' AND login_prof='".$_SESSION['login']."' AND n_anonymat='$n_anonymat[$i]';";
					$test=mysql_query($sql);
		
					if(mysql_num_rows($test)==0) {
						$saisie="n";
						// AJOUTER UNE ALERTE INTRUSION
					}
				}
		
				if($saisie=="y") {
					$elev_statut='';
					if(($note[$i]=='disp')){
						$elev_note='0';
						$elev_statut='disp';
					}
					elseif(($note[$i]=='abs')){
						$elev_note='0';
						$elev_statut='abs';
					}
					elseif(($note[$i]=='-')){
						$elev_note='0';
						$elev_statut='-';
					}
					elseif(preg_match("/^[0-9\.\,]{1,}$/",$note[$i])) {
						$elev_note=str_replace(",", ".", "$note[$i]");
						if(($elev_note<0)||($elev_note>$note_sur)){
							$elev_note='';
							$elev_statut='';
						}
					}
					else{
						$elev_note='';
						//$elev_statut='';
						$elev_statut='v';
					}
					if(($elev_note!='')or($elev_statut!='')){
						$sql="UPDATE eb_copies SET note='$elev_note', statut='$elev_statut' WHERE id_epreuve='$id_epreuve' AND n_anonymat='$n_anonymat[$i]';";
						$res=mysql_query($sql);
						if(!$res) {
							$msg.="Error: $sql<br />";
						}
					}
				}
			}
		
			if(($msg=='')&&(count($n_anonymat)>0)) {
				$msg="Recording carried out.";
			}
		}
		else {
			$msg="The selected test (<i>$id_epreuve</i>) is closed.\n";
		}
	}
}
elseif((isset($mode))&&($mode=='export_csv')) {
	check_token();

	$export="y";

	// V�rifier que l'acc�s est autoris�
	if($_SESSION['statut']=='professeur') {
		$sql="SELECT 1=1 FROM eb_copies WHERE id_epreuve='$id_epreuve' AND login_prof='".$_SESSION['login']."';";
		$test=mysql_query($sql);
	
		if(mysql_num_rows($test)==0) {
			$export="n";
			// AJOUTER UNE ALERTE INTRUSION
		}

		$sql="SELECT n_anonymat, note, statut FROM eb_copies WHERE id_epreuve='$id_epreuve' AND login_prof='".$_SESSION['login']."' ORDER BY n_anonymat;";
	}
	else {
		$sql="SELECT * FROM eb_copies WHERE id_epreuve='$id_epreuve' ORDER BY n_anonymat;";
	}

	if($export=="y") {
		$res=mysql_query($sql);

		if($_SESSION['statut']=='professeur') {
			$csv="N_ANONYMAT;NOTE;\n";
			while($lig=mysql_fetch_object($res)) {
				$note="";
				if($lig->statut=='v') {
					$note="";
				}
				elseif($lig->statut!='') {
					$note=$lig->statut;
				}
				else {
					$note=$lig->note;
				}
				$csv.=$lig->n_anonymat.";".$note.";\n";
			}
		}
		else {
			// Pouvoir choisir les champs?
			//$csv="N_ANONYMAT;LOGIN_ELE;NOTE;LOGIN_PROF;\n";
			$csv="N_ANONYMAT;LOGIN_ELE;NOM_PRENOM_ELE;CLASSE;NOTE;LOGIN_PROF;NOM_PROF\n";
			while($lig=mysql_fetch_object($res)) {
				$note="";
				if($lig->statut=='v') {
					$note="";
				}
				elseif($lig->statut!='') {
					$note=$lig->statut;
				}
				else {
					$note=$lig->note;
				}
				$tmp_tab=get_class_from_ele_login($lig->login_ele);
				$csv.=$lig->n_anonymat.";".$lig->login_ele.";".get_nom_prenom_eleve($lig->login_ele).";".$tmp_tab['liste'].";".$note.";".$lig->login_prof.";".affiche_utilisateur($lig->login_prof,$tmp_tab['id0']).";\n";
			}
		}

		$nom_fic="export_saisie_notes_".$_SESSION['login']."_$id_epreuve.csv";
	
		$now = gmdate('D, d M Y H:i:s') . ' GMT';
		send_file_download_headers('text/x-csv',$nom_fic);
		echo $csv;
		die();
	}
}

include('lib_eb.php');

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
$themessage  = 'Information was modified. Want you to really leave without recording ?';
//**************** EN-TETE *****************
$titre_page = "White test: Seizure of the notes";
//echo "<div class='noprint'>\n";
require_once("../lib/header.inc");
//echo "</div>\n";
//**************** FIN EN-TETE *****************

//debug_var();

//echo "<div class='noprint'>\n";
//echo "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\" name='form1'>\n";

if(isset($id_epreuve)) {
	echo "<p class='bold'><a href='index.php?id_epreuve=$id_epreuve&amp;mode=modif_epreuve'";
	echo " onclick=\"return confirm_abandon (this, change, '$themessage')\"";
	echo ">White test n�$id_epreuve</a>";
}
else {
	echo "<p class='bold'><a href='index.php'";
	echo " onclick=\"return confirm_abandon (this, change, '$themessage')\"";
	echo ">Small white Test</a>";
}

//echo "</p>\n";
//echo "</div>\n";

//==================================================================

if(!isset($id_epreuve)) {
	echo "</p>\n";
	// Acc�der aux �preuves blanches: non closes
	if(($_SESSION['statut']=='administrateur')||($_SESSION['statut']=='scolarite')) {
		$sql="SELECT * FROM eb_epreuves WHERE etat!='clos' ORDER BY date, intitule;";
	}
	elseif($_SESSION['statut']=='professeur') {
		$sql="SELECT ee.* FROM eb_epreuves ee, eb_profs ep WHERE ee.etat!='clos' AND ee.id=ep.id_epreuve AND ep.login_prof='".$_SESSION['login']."' ORDER BY ee.date, ee.intitule;";
	}
	else {
		echo "<p>Unauthorized access.</p>\n";

		// Mettre un tentative_intrusion()
		// Envisager une saisie par le compte secours

		require("../lib/footer.inc.php");
		die();
	}

	//echo "$sql<br />\n";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "<p>No nonclosed test.</p>\n";
	}
	else {
		echo "<p><b>Tests in progress&nbsp;:</b></p>\n";
		echo "<ul>\n";
		while($lig=mysql_fetch_object($res)) {
			echo "<li>\n";
			//echo "Modifier <a href='".$_SERVER['PHP_SELF']."?id_epreuve=$lig->id&amp;modif_epreuve=y'";
			echo "Saisir <a href='".$_SERVER['PHP_SELF']."?id_epreuve=$lig->id'";
			if($lig->description!='') {
				echo " onmouseover=\"delais_afficher_div('div_epreuve_".$lig->id."','y',-100,20,1000,20,20)\" onmouseout=\"cacher_div('div_epreuve_".$lig->id."')\"";

				$titre="Test n�$lig->id";
				$texte="<p><b>".$lig->intitule."</b><br />";
				$texte.=$lig->description;
				$tabdiv_infobulle[]=creer_div_infobulle('div_epreuve_'.$lig->id,$titre,"",$texte,"",30,0,'y','y','n','n');

			}
			echo ">$lig->intitule</a> (<i>".formate_date($lig->date)."</i>)<br />\n";
			echo "</li>\n";
		}
		echo "</ul>\n";
	}

	require("../lib/footer.inc.php");
	die();
}

echo " | <a href='".$_SERVER['PHP_SELF']."'";
echo " onclick=\"return confirm_abandon (this, change, '$themessage')\"";
echo ">Choice of the test</a>";

echo " | <a href='".$_SERVER['PHP_SELF']."?id_epreuve=$id_epreuve&amp;mode=export_csv".add_token_in_url()."'";
echo " onclick=\"return confirm_abandon (this, change, '$themessage')\"";
echo ">To export with the format CSV</a>";

echo "</p>\n";

//========================================================
// Si prof, tester si id_epreuve est bien associ� au prof
if($_SESSION['statut']=='professeur') {
	$sql="SELECT * FROM eb_epreuves ee, eb_profs ep WHERE ee.etat!='clos' AND ee.id=ep.id_epreuve AND ep.login_prof='".$_SESSION['login']."' AND ep.id_epreuve='$id_epreuve' ORDER BY ee.date, ee.intitule;";
	//echo "$sql<br />\n";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "<p>Unauthorized access.</p>\n";

		// Mettre un tentative_intrusion()
		// Envisager une saisie par le compte secours

		require("../lib/footer.inc.php");
		die();
	}
}

//========================================================
echo "<p class='bold'>Test n�$id_epreuve</p>\n";

$sql="SELECT * FROM eb_epreuves WHERE id='$id_epreuve';";
$res=mysql_query($sql);
if(mysql_num_rows($res)==0) {
	echo "<p>Selected test (<i>$id_epreuve</i>) do not exist.</p>\n";
	require("../lib/footer.inc.php");
	die();
}

$lig=mysql_fetch_object($res);
$etat=$lig->etat;
$note_sur=$lig->note_sur;

echo "<blockquote>\n";
echo "<p><b>".$lig->intitule."</b> (<i>".formate_date($lig->date)."</i>)<br />\n";
if($lig->description!='') {
	echo nl2br(trim($lig->description))."<br />\n";
}
else {
	echo "No seized description.<br />\n";
}
echo "</blockquote>\n";


//========================================================
if(($_SESSION['statut']=='administrateur')||($_SESSION['statut']=='scolarite')) {
	$sql="SELECT * FROM eb_copies WHERE id_epreuve='$id_epreuve' ORDER BY n_anonymat";
	$res=mysql_query($sql);
	
	if(mysql_num_rows($res)==0) {
		echo "<p style='color:red;'>No copy was found.<br />You associated groups/lesson with the test?</p>\n";
		require("../lib/footer.inc.php");
		die();
	}
}
elseif($_SESSION['statut']=='professeur') {
	$sql="SELECT * FROM eb_copies WHERE id_epreuve='$id_epreuve' AND login_prof='".$_SESSION['login']."' ORDER BY n_anonymat;";
	$res=mysql_query($sql);
	
	if(mysql_num_rows($res)==0) {
		echo "<p style='color:red;'>No copy was allotted to you.<br />For a little, you would have corrected the copies for nothing;o)</p>\n";
		require("../lib/footer.inc.php");
		die();
	}
}
else {
	echo "<p>Unauthorized access.</p>\n";
	// Mettre un tentative_intrusion()
	// Envisager une saisie par le compte secours
	require("../lib/footer.inc.php");
	die();
}


//========================================================
$sql="SELECT 1=1 FROM eb_copies WHERE id_epreuve='$id_epreuve';";
$test1=mysql_query($sql);

$sql="SELECT DISTINCT n_anonymat FROM eb_copies WHERE id_epreuve='$id_epreuve';";
$test2=mysql_query($sql);
if(mysql_num_rows($test1)!=mysql_num_rows($test2)) {
	echo "<p style='color:red;'>The numbers anonymities are not single on the test (<i>that should not arrive</i>).<br />The seizure is not possible.</p>\n";
	require("../lib/footer.inc.php");
	die();
}

$sql="SELECT login_ele FROM eb_copies WHERE n_anonymat='' AND id_epreuve='$id_epreuve';";
$test3=mysql_query($sql);
if(mysql_num_rows($test3)>0) {
	echo "<p style='color:red;'>Numbers anonymities are not valid on the test&nbsp;: ";
	$cpt=0;
	while($lig=mysql_fetch_object($test3)) {
		if($cpt>0) {echo ", ";}
		echo get_nom_prenom_eleve($lig->login_ele);
		$cpt++;
	}
	echo "<br />That should not arrive.<br />The seizure is not possible.</p>\n";
	require("../lib/footer.inc.php");
	die();
}

$sql="SELECT 1=1 FROM eb_groupes WHERE transfert='y' AND id_epreuve='$id_epreuve';";
$test4=mysql_query($sql);
if(mysql_num_rows($test4)>0) {
	echo "<p style='color:red;'><b>Anomaly&nbsp;:</b> The test is not closed and the transfer of the notes towards the
report cards was already carried out for a teaching/groupe at least.<br />Thank you to contact the administrator or with the responsible one of
the test (<i>in theory titular of an account ' schooling'</i>) so that it carries out the transfer again once the modified notes/corrected.";
}

//========================================================


// Couleurs utilis�es
$couleur_devoirs = '#AAE6AA';
$couleur_fond = '#AAE6AA';
$couleur_moy_cn = '#96C8F0';

if($etat!='clos') {
	echo "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\" name='form1'>\n";
	echo add_token_field();
}

echo "<table border='1' cellspacing='2' cellpadding='1' class='boireaus' summary='Saisie'>\n";
echo "<tr>\n";
echo "<th>Number anonymity</th>\n";
if(($_SESSION['statut']=='administrateur')||($_SESSION['statut']=='scolarite')) {
	$title_col_sp=" title='Column not display for a professor'";
	echo "<th$title_col_sp>Name First name</th>\n";
}
//echo "<th width='100px'>Note</th>\n";
echo "<th style='width:5em;'>Note on $note_sur</th>\n";
echo "</tr>\n";

$cpt=0;
$alt=1;
while($lig=mysql_fetch_object($res)) {
	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td>\n";
	echo "<input type='hidden' name=\"n_anonymat[$cpt]\" value=\"$lig->n_anonymat\" />\n";
	echo "$lig->n_anonymat\n";
	echo "</td>\n";

	if(($_SESSION['statut']=='administrateur')||($_SESSION['statut']=='scolarite')) {
		echo "<td style='background-color:gray;'$title_col_sp>\n";
		echo get_nom_prenom_eleve($lig->login_ele)."\n";
		echo "</td>\n";
	}

	echo "<td id=\"td_".$cpt."\">\n";
	if($etat!='clos') {
		echo "<input id=\"n".$cpt."\" onKeyDown=\"clavier(this.id,event);\" type=\"text\" size=\"4\" ";
		echo "autocomplete=\"off\" ";
		//echo "onfocus=\"javascript:this.select()\" onchange=\"verifcol($cpt);changement()\" ";
		echo "onfocus=\"javascript:this.select()\" onchange=\"verifcol($cpt);calcul_moy_med();changement()\" ";
		echo "name=\"note[$cpt]\" value='";
		if(($lig->statut=='v')) {echo "";}
		elseif($lig->statut!='') {echo "$lig->statut";}
		else {echo "$lig->note";}
		echo "' />\n";
	}
	else {
		if(($lig->statut=='v')) {echo "";}
		elseif($lig->statut!='') {echo "$lig->statut";}
		else {
			echo "$lig->note";
			// Pour le calcul javascript des moyennes,...
			echo "<input type='hidden' id=\"n".$cpt."\" name=\"note[$cpt]\" value=\"$lig->note\" />\n";
		}
	}
	echo "</td>\n";
	echo "</tr>\n";
	$cpt++;
}
echo "</table>\n";

echo "<div style='position: fixed; top: 200px; right: 200px;'>\n";
javascript_tab_stat('tab_stat_',$cpt);
echo "</div>\n";


if($etat!='clos') {
	echo "<input type='hidden' name='id_epreuve' value='$id_epreuve' />\n";
	echo "<p><input type='submit' name='saisie_notes' value='Valider' /></p>\n";
	echo "</form>\n";

	echo "
<script type='text/javascript' language='JavaScript'>

function verifcol(num_id){
	document.getElementById('n'+num_id).value=document.getElementById('n'+num_id).value.toLowerCase();
	if(document.getElementById('n'+num_id).value=='a'){
		document.getElementById('n'+num_id).value='abs';
	}
	if(document.getElementById('n'+num_id).value=='d'){
		document.getElementById('n'+num_id).value='disp';
	}
	if(document.getElementById('n'+num_id).value=='n'){
		document.getElementById('n'+num_id).value='-';
	}
	note=document.getElementById('n'+num_id).value;

	if((note!='-')&&(note!='disp')&&(note!='abs')&&(note!='')){
		//if((note.search(/^[0-9.]+$/)!=-1)&&(note.lastIndexOf('.')==note.indexOf('.',0))){
		if(((note.search(/^[0-9.]+$/)!=-1)&&(note.lastIndexOf('.')==note.indexOf('.',0)))||
	((note.search(/^[0-9,]+$/)!=-1)&&(note.lastIndexOf(',')==note.indexOf(',',0)))){
			if((note>$note_sur)||(note<0)){
				couleur='red';
			}
			else{
				couleur='$couleur_devoirs';
			}
		}
		else{
			couleur='red';
		}
	}
	else{
		couleur='$couleur_devoirs';
	}
	eval('document.getElementById(\'td_'+num_id+'\').style.background=couleur');
}
</script>
";
}

//echo "<p style='color:red;'>Ajouter des confirm_abandon() sur les liens.</p>\n";
echo "<p><br /></p>\n";
echo "<p style='color:red;'>TO MAKE:</p>\n";
echo "<ul>\n";
//echo "<li><p style='color:red;'>Permettre de saisir des notes sur autre chose que 20.</p>\n";
//echo "<li><p style='color:red;'>Calculer la moyenne, m�diane,...</p></li>\n";
echo "<li><p style='color:red;'>To allow to import/to export its seizures with the format CSV</p></li>\n";
echo "</ul>\n";
//echo "<p style='color:red;'>V�rifier que l'on n'a pas deux num�ros anonymat identiques sur l'�preuve... pour verrouiller les saisies si n�cessaire.</p>\n";

require("../lib/footer.inc.php");
?>
