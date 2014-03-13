<?php
/*
 * $Id: ajouter_periode.php 7271 2011-06-20 11:46:28Z crob $
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

$sql="SELECT 1=1 FROM droits WHERE id='/classes/ajouter_periode.php';";
$test=mysql_query($sql);
if(mysql_num_rows($test)==0) {
$sql="INSERT INTO droits SET id='/classes/ajouter_periode.php',
administrateur='V',
professeur='F',
cpe='F',
scolarite='F',
eleve='F',
responsable='F',
secours='F',
autre='F',
description='Classes: Ajouter des p�riodes',
statut='';";
$insert=mysql_query($sql);
}

if (!checkAccess()) {
	header("Location: ../logout.php?auto=1");
	die();
}

$id_classe=isset($_POST['id_classe']) ? $_POST['id_classe'] : (isset($_GET['id_classe']) ? $_GET['id_classe'] : NULL);
$nb_ajout_periodes=isset($_POST['nb_ajout_periodes']) ? $_POST['nb_ajout_periodes'] : NULL;
$nb_periodes_initial=isset($_POST['nb_periodes_initial']) ? $_POST['nb_periodes_initial'] : NULL;

if(!isset($id_classe)) {
	header("Location: index.php?msg=No identifier of class was proposed");
	die();
}

if((isset($nb_ajout_periodes))&&(!preg_match('/^[1-9]$/',$nb_ajout_periodes))) {
	unset($nb_ajout_periodes);
	$msg="Nombre de p�riodes � ajouter invalide.";
}

if((isset($nb_periodes_initial))&&(!preg_match('/^[1-9]$/',$nb_periodes_initial))) {
	unset($nb_periodes_initial);
	$msg="Nombre initial de p�riodes invalide.";
}

$call_data = mysql_query("SELECT classe FROM classes WHERE id = '$id_classe'");
$classe = mysql_result($call_data, 0, "classe");
$periode_query = mysql_query("SELECT * FROM periodes WHERE id_classe = '$id_classe'");
$test_periode = mysql_num_rows($periode_query) ;
include "../lib/periodes.inc.php";

// =================================
// AJOUT: boireaus
$chaine_options_classes="";
$sql="SELECT id, classe FROM classes ORDER BY classe";
$res_class_tmp=mysql_query($sql);
if(mysql_num_rows($res_class_tmp)>0){
	$id_class_prec=0;
	$id_class_suiv=0;
	$temoin_tmp=0;

    $cpt_classe=0;
	$num_classe=-1;

	while($lig_class_tmp=mysql_fetch_object($res_class_tmp)){
		if($lig_class_tmp->id==$id_classe){
			// Index de la classe dans les <option>
			$num_classe=$cpt_classe;

			$chaine_options_classes.="<option value='$lig_class_tmp->id' selected='true'>$lig_class_tmp->classe</option>\n";
			$temoin_tmp=1;
			if($lig_class_tmp=mysql_fetch_object($res_class_tmp)){
				$chaine_options_classes.="<option value='$lig_class_tmp->id'>$lig_class_tmp->classe</option>\n";
				$id_class_suiv=$lig_class_tmp->id;
			}
			else{
				$id_class_suiv=0;
			}
		}
		else {
			$chaine_options_classes.="<option value='$lig_class_tmp->id'>$lig_class_tmp->classe</option>\n";
		}

		if($temoin_tmp==0){
			$id_class_prec=$lig_class_tmp->id;
		}

		$cpt_classe++;
	}
}
// =================================

$themessage  = 'Information was modified. Do you really want to leave without recording ?';
//**************** EN-TETE *****************
$titre_page = "Management of classes - Addition of periods";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************

echo "<form action='".$_SERVER['PHP_SELF']."' name='form1' method='post'>\n";

echo "<p class='bold'><a href='periodes.php?id_classe=$id_classe' onclick=\"return confirm_abandon (this, change, '$themessage')\"><img src='../images/icons/back.png' alt='Return' class='back_link'/> Return </a>\n";

if($id_class_prec!=0){echo " | <a href='".$_SERVER['PHP_SELF']."?id_classe=$id_class_prec' onclick=\"return confirm_abandon (this, change, '$themessage')\">Previous class</a>\n";}
if($chaine_options_classes!="") {

	echo "<script type='text/javascript'>
	// Initialisation
	change='no';

	function confirm_changement_classe(thechange, themessage)
	{
		if (!(thechange)) thechange='no';
		if (thechange != 'yes') {
			document.form1.submit();
		}
		else{
			var is_confirmed = confirm(themessage);
			if(is_confirmed){
				document.form1.submit();
			}
			else{
				document.getElementById('id_classe').selectedIndex=$num_classe;
			}
		}
	}
</script>\n";


	echo " | <select name='id_classe' id='id_classe' onchange=\"confirm_changement_classe(change, '$themessage');\">\n";
	echo $chaine_options_classes;
	echo "</select>\n";
}
if($id_class_suiv!=0){echo " | <a href='".$_SERVER['PHP_SELF']."?id_classe=$id_class_suiv' onclick=\"return confirm_abandon (this, change, '$themessage')\">next class</a>\n";}

//=========================
// AJOUT: boireaus 20081224
$titre="Navigation";
$texte="";

//$texte.="<img src='../images/icons/date.png' alt='' /> <a href='periodes.php?id_classe=$id_classe' onclick=\"return confirm_abandon (this, change, '$themessage')\">P�riodes</a><br />";
$texte.="<img src='../images/icons/edit_user.png' alt='' /> <a href='classes_const.php?id_classe=$id_classe' onclick=\"return confirm_abandon (this, change, '$themessage')\">Students</a><br />";
$texte.="<img src='../images/icons/document.png' alt='' /> <a href='../groupes/edit_class.php?id_classe=$id_classe' onclick=\"return confirm_abandon (this, change, '$themessage')\">Courses</a><br />";
$texte.="<img src='../images/icons/document.png' alt='' /> <a href='../groupes/edit_class_grp_lot.php?id_classe=$id_classe' onclick=\"return confirm_abandon (this, change, '$themessage')\">config.simplified</a><br />";
$texte.="<img src='../images/icons/configure.png' alt='' /> <a href='modify_nom_class.php?id_classe=$id_classe' onclick=\"return confirm_abandon (this, change, '$themessage')\">Parameters</a>";

$ouvrir_infobulle_nav=getSettingValue("ouvrir_infobulle_nav");

if($ouvrir_infobulle_nav=="y") {
	$texte.="<div id='save_mode_nav' style='float:right; width:20px; height:20px;'><a href='#' onclick='modif_mode_infobulle_nav();return false;'><img src='../images/vert.png' width='16' height='16' /></a></div>\n";
}
else {
	$texte.="<div id='save_mode_nav' style='float:right; width:20px; height:20px;'><a href='#' onclick='modif_mode_infobulle_nav();return false;'><img src='../images/rouge.png' width='16' height='16' /></a></div>\n";
}

$texte.="<script type='text/javascript'>
	// <![CDATA[
	function modif_mode_infobulle_nav() {
		new Ajax.Updater($('save_mode_nav'),'classes_ajax_lib.php?mode=ouvrir_infobulle_nav',{method: 'get'});
	}
	//]]>
</script>\n";

$tabdiv_infobulle[]=creer_div_infobulle('navigation_classe',$titre,"",$texte,"",14,0,'y','y','n','n');

echo " | <a href='#' onclick=\"afficher_div('navigation_classe','y',-100,20);\"";
echo ">";
echo "Navigation";
echo "</a>";
//=========================

echo "</p>\n";
echo "</form>\n";

//=========================================================================
function search_liaisons_classes_via_groupes($id_classe) {
	global $tab_liaisons_classes;

	$sql="SELECT jgc.id_groupe FROM j_groupes_classes jgc WHERE jgc.id_classe='$id_classe';";
	//echo "$sql<br />\n";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)>0) {
		while($lig=mysql_fetch_object($res)) {
			$sql="SELECT c.classe, jgc.id_classe, g.* FROM j_groupes_classes jgc, groupes g, classes c WHERE jgc.id_classe!='$id_classe' AND g.id=jgc.id_groupe AND c.id=jgc.id_classe AND jgc.id_groupe='$lig->id_groupe' ORDER BY c.classe;";
			//echo "$sql<br />\n";
			$test=mysql_query($sql);
			if(mysql_num_rows($test)>0) {
				while($lig2=mysql_fetch_object($test)) {
					if(!in_array($lig2->id_classe,$tab_liaisons_classes)) {
						$tab_liaisons_classes[]=$lig2->id_classe;
						search_liaisons_classes_via_groupes($lig2->id_classe);
					}
				}
			}
		}
	}
}
//=========================================================================
if(!isset($nb_ajout_periodes)) {

	$sql="SELECT num_periode FROM periodes WHERE id_classe='".$id_classe."' ORDER BY num_periode DESC LIMIT 1;";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "<p style='color:red'>ANOMALIE&nbsp;: La classe ".$classe." n'a actuellement aucune p�riode.</p>\n";
		require("../lib/footer.inc.php");
		die();
	}
	else {
		$lig=mysql_fetch_object($res);
		$max_per=$lig->num_periode;
	}

	echo "<p class='bold'>Search of direct connections&nbsp;:</p>\n";
	echo "<blockquote>\n";
	echo "<p>";
	
	$tab_liaisons_classes=array();
	$tab_liaisons_classes[]=$id_classe;
	
	$sql="SELECT jgc.id_groupe FROM j_groupes_classes jgc WHERE jgc.id_classe='$id_classe';";
	//echo "$sql<br />\n";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "No connection was found.<br />The addition of period thus does not present a difficulty.</p>\n";
	}
	else {
		while($lig=mysql_fetch_object($res)) {
			$sql="SELECT c.classe, jgc.id_classe, g.* FROM j_groupes_classes jgc, groupes g, classes c WHERE jgc.id_classe!='$id_classe' AND g.id=jgc.id_groupe AND c.id=jgc.id_classe AND jgc.id_groupe='$lig->id_groupe' ORDER BY c.classe;";
			//echo "$sql<br />\n";
			$test=mysql_query($sql);
			if(mysql_num_rows($test)>0) {
				$cpt=0;
				while($lig2=mysql_fetch_object($test)) {
					if($cpt==0) {
						echo "<b>$lig2->name (<i>$lig2->description</i>)&nbsp;:</b> ";
					}
					echo " $lig2->classe";
					$cpt++;
				}
				echo "<br />\n";
			}
		}
	}
	echo "</blockquote>\n";
	
	search_liaisons_classes_via_groupes($id_classe);
	
	if(count($tab_liaisons_classes)>0) {
		echo "<p>The class <b>$classe</b> is dependent (<i>in a direct or indirect way (via another class)</i>) to the following classes&nbsp;: ";
		$cpt=0;
		for($i=0;$i<count($tab_liaisons_classes);$i++) {
			if($tab_liaisons_classes[$i]!=$id_classe) {
				if($cpt>0) {echo ", ";}
				echo get_class_from_id($tab_liaisons_classes[$i]);
				$cpt++;
			}
		}

		echo "<p>The class of <b>$classe</b> currently has <b>$max_per</b> periods.</p>\n";
		echo "<p>How many periods do you want to add for <b>$classe</b> and dependent classes?</p>\n";
	}
	else {
		echo "<p>The class of <b>$classe</b> currently has <b>$max_per</b> periods.</p>\n";
		echo "<p>How many periods do you want to add for <b>$classe</b>?</p>\n";
	}
	
	echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>\n";
	echo add_token_field();
	
	echo "<p>Number of periods to be added&nbsp;: <select name='nb_ajout_periodes'>\n";
	for($i=1;$i<10;$i++) {
		echo "<option value='$i'>$i</option>\n";
	}
	echo "</select>\n";
	//echo "<br />\n";

	echo " <input type='hidden' name='id_classe' value='$id_classe' />\n";
	echo " <input type='hidden' name='nb_periodes_initial' value='$max_per' />\n";
	echo " <input type='submit' name='Ajouter' value='Ajouter' />\n";
	echo "</p>\n";
	echo "</form>\n";
	
	echo "<p><br /></p>\n";
	
	echo "<p class='bold'>Remarks&nbsp;:</p>\n";
	echo "<div style='margin-left: 3em;'>\n";
		echo "<p>The addition of period presents a difficulty when there are
courses/groups on several classes.<br />Two classes sharing a course must have the same number of periods.<br />If you add periods to the class ".$classe.", it will be necessary&nbsp:</p>\n";
		echo "<ul>\n";
			echo "<li>to add the same number of periods to the classes related to $classe</li>\n";
			echo "<li>to break the connections&nbsp;:<br />That would mean that you would have two distinct courses then for $classe and a class sharing the course.<br />For the professor the consequences are as follows&nbsp;:<br />\n";
				echo "<ul>\n";
					echo "<li>to type the results of an exam, it will be necessary to create an exam in each of the two courses and type the notes there</li>\n";
					echo "<li>the average of the group of student will not be calculated; there will be two averages&nbsp: those of the two courses<br />Same things for the  min and max averages.</li>\n";
					echo "<li>For the existing notes, a new group should be created, a new report card, cloner exams and box to transfer the notes to it and cause the recalculation of the averages of containers.<br />Les saisies de cahier de textes, d'emploi du temps doivent �tre dupliqu�es, les saisies ant�rieures d'absences peuvent-elles �tre perturb�es (?) ou l'association n'est-elle que �l�ve/jour_heures_absence (?),...</li>";
				echo "</ul>\n";
				echo "<span style='color:red'>The second solution is not implemented for the moment</span>\n";
			echo "</li>\n";
		echo "</ul>\n";
	echo "</div>\n";
}
//=========================================================================
else {
	check_token(false);

	$tab_liaisons_classes=array();
	$tab_liaisons_classes[]=$id_classe;
	search_liaisons_classes_via_groupes($id_classe);

	for($i=0;$i<count($tab_liaisons_classes);$i++) {
		// Classe par classe
			// Ajouter une p�riode dans la table 'periodes'... � nommer...
			// Ins�rer des enregistrements pour
				// j_eleves_classes (v�rifier qu'un �l�ve n'est pas d�j� dans une autre classe pour le m�me num�ro de p�riode... un �l�ve peut-il passer d'une classe � 2 p�riodes � une classe � 3 p�riodes... pb de chevauchement pour les absences...)
				// j_eleves_groupes en bouclant sur les groupes de la classe
		$id_classe_courant=$tab_liaisons_classes[$i];
		$classe_courante=get_class_from_id($tab_liaisons_classes[$i]);

		echo "<p class='bold'>Treatment of the class $classe_courante&nbsp;:</p>\n";
		echo "<blockquote>\n";

		$sql="SELECT num_periode FROM periodes WHERE id_classe='".$id_classe_courant."' ORDER BY num_periode DESC LIMIT 1;";
		$res=mysql_query($sql);
		if(mysql_num_rows($res)==0) {
			echo "<p style='color:red'>ANOMALIE&nbsp;: The class ".$classe_courante." currently has no period .</p>\n";
		}
		else {
			//$lig=mysql_fetch_object($res);
			//$num_periode=$lig->num_periode;

			$num_periode=$nb_periodes_initial;

			// R�cup�ration de la liste des �l�ves de la classe pour la derni�re p�riode
			$tab_ele=array();
			$sql="SELECT DISTINCT login FROM j_eleves_classes WHERE id_classe='".$id_classe_courant."' AND periode='$num_periode';";
			$res=mysql_query($sql);
			if(mysql_num_rows($res)==0) {
				echo "<p>No student is registered in the class ".$classe_courante." over the period $num_periode.</p>\n";
			}
			else {
				while($lig=mysql_fetch_object($res)) {
					$tab_ele[]=$lig->login;
				}
			}

			$tab_group=array();
			$sql="SELECT id_groupe FROM j_groupes_classes WHERE id_classe='$id_classe_courant'";
			$res_liste_grp_classe=mysql_query($sql);
			if(mysql_num_rows($res_liste_grp_classe)>0){
				while($lig_tmp=mysql_fetch_object($res_liste_grp_classe)){
					$tab_group[$lig_tmp->id_groupe]=array();
					$sql="SELECT DISTINCT login FROM j_eleves_groupes WHERE id_groupe='$lig_tmp->id_groupe' AND periode='$num_periode'";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)>0){
						while($lig_tmp2=mysql_fetch_object($test)) {
							$tab_group[$lig_tmp->id_groupe][]=$lig_tmp2->login;
						}
					}
				}
			}

			// Boucle sur le nombre de p�riodes � ajouter
			for($loop=0;$loop<$nb_ajout_periodes;$loop++) {
				$num_periode++;

				echo "Cr�ation de la p�riode $num_periode&nbsp;: ";
				$sql="INSERT INTO periodes SET nom_periode='P�riode $num_periode', num_periode='$num_periode', verouiller='O', id_classe='".$id_classe_courant."', date_verrouillage='0000-00-00 00:00:00';";
				//echo "$sql<br />\n";
				$res=mysql_query($sql);
				updateOnline($sql);

				if(!$res) {
					echo "<span style='color:red'>FAILURE</span>";
					echo "<br />\n";
				}
				else {
					echo "<span style='color:green'>SUCCESSES</span>";
					echo "<br />\n";
					echo "<blockquote>\n";

					if(count($tab_ele)>0) {
						echo "Ajout des �l�ves dans la classe&nbsp;:";
						for($j=0;$j<count($tab_ele);$j++) {
							if($j>0) {echo ", ";}
							$sql="INSERT INTO j_eleves_classes SET login='$tab_ele[$j]', id_classe='$id_classe_courant', periode='$num_periode';";
							//echo "$sql<br />\n";
							$res=mysql_query($sql);
							updateOnline($sql);
							if(!$res) {
								echo "<span style='color:red'>$tab_ele[$j]</span> ";
							}
							else {
								echo "<span style='color:green'>$tab_ele[$j]</span> ";
							}
						}
						echo "<br />\n";
					}

					foreach($tab_group as $id_groupe => $tab_ele_groupe) {
						$tab_champs=array();
						$tmp_group=get_group($id_groupe,$tab_champs);

						echo "Inscription dans l'enseignement ".$tmp_group['name']." (<i>".$tmp_group['description']."</i>) (<i>n�$id_groupe</i>)&nbsp;: ";
						$kk=0;
						for($k=0;$k<count($tab_ele_groupe);$k++) {
							$sql="SELECT 1=1 FROM j_eleves_groupes WHERE login='$tab_ele_groupe[$k]' AND id_groupe='$id_groupe' AND periode='$num_periode';";
							//echo "$sql<br />\n";
							$res=mysql_query($sql);
							if(mysql_num_rows($res)>0) {
								echo "<span style='color:blue'>$tab_ele_groupe[$k]</span> ";
							}
							else {
								if($kk>0) {echo ", ";}
								$sql="INSERT INTO j_eleves_groupes SET login='$tab_ele_groupe[$k]', id_groupe='$id_groupe', periode='$num_periode';";
								$res=mysql_query($sql);
								updateOnline($sql);
								if(!$res) {
									echo "<span style='color:red'>$tab_ele_groupe[$k]</span> ";
								}
								else {
									echo "<span style='color:green'>$tab_ele_groupe[$k]</span> ";
								}
								$kk++;
							}
						}
						echo "<br />\n";
					}
					echo "</blockquote>\n";
				}
			}
		}
		echo "</blockquote>\n";
	}

	echo "<p class='bold'>Finished.</p>\n";

	if((substr(getSettingValue('autorise_edt_tous'),0,1)=='y')||(substr(getSettingValue('autorise_edt_admin'),0,1)=='y')||(substr(getSettingValue('autorise_edt_eleve'),0,1)=='y')) {
		echo "<p><br /></p>\n";
		echo "<p>Remind of controlling that you defined the dates of periods in <a href='../edt_organisation/edt_calendrier.php'>calendar</a>.</p>\n";
		echo "<p><br /></p>\n";
	}
}
//=========================================================================

require("../lib/footer.inc.php");

?>