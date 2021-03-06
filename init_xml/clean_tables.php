<?php

@set_time_limit(0);
/*
* $Id: clean_tables.php 5936 2010-11-21 17:32:17Z crob $
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

extract($_POST, EXTR_OVERWRITE);


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

check_token();

$liste_tables_del = array(
"classes",
"eleves",
"groupes",
//"responsables",
"responsables2",
"resp_pers",
"resp_adr",
"j_eleves_groupes",
"j_groupes_classes",
"j_groupes_professeurs",
"j_groupes_matieres",
"j_eleves_classes",
"j_professeurs_matieres",
"matieres",
"periodes",
"utilisateurs"
);

//**************** EN-TETE *****************
$titre_page = "Tool of initialization of the year : Cleaning of the tables";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
?>
<p class="bold"><a href="index.php"><img src='../images/icons/back.png' alt='Return' class='back_link'/> Return home initialization</a></p>
<?php
echo "<center><h3 class='gepi'>Seventh phase of initialization<br />Cleaning of the tables</h3></center>";
if (!isset($is_posted)) {
echo "<p><b>CAUTION ...</b> : you should proceed to this operation only if all the data (students, classes, professors, disciplines, options) were defined!</p>";
echo "<p>Useless data imported from GEP files during the various phases of initialization will be erased !</p>";
echo "<form enctype='multipart/form-data' action='clean_tables.php' method='post'>";
echo add_token_field();
echo "<input type=hidden name='is_posted' value='yes' />";
echo "<p><input type=submit value='Make the cleaning' />";
echo "</form>";
} else {
	$j=0;
	$flag=0;
	while (($j < count($liste_tables_del)) and ($flag==0)) {
		if (mysql_result(mysql_query("SELECT count(*) FROM $liste_tables_del[$j]"),0)==0) {
			$flag=1;
		}
		$j++;
	}
	if ($flag != 0){
		echo "<p><b>CAUTION ...</b><br />";
		echo "The initialization of the data of the year is not finished, certain data concerning the students, the classes, the groups, the professors or the courses are missing. The procedure cannot continue !</p></body></html>";
		die();
	}
	//Suppression des donn�es inutiles dans la tables utilisateurs
	echo "<h3 class='gepi'>Checking of the data concerning the professors</h3>";
	$req = mysql_query("select login from utilisateurs where (statut = 'professeur' and etat='actif')");
	$sup = 'no';
	$nb_prof = mysql_num_rows($req);
	$i = 0;
	while ($i < $nb_prof) {
		$login_prof = mysql_result($req, $i, 'login');
		$test = mysql_query("select id_professeur from j_professeurs_matieres where id_professeur = '$login_prof'");
		if (mysql_num_rows($test)==0) {
			$del = @mysql_query("delete from utilisateurs where login = '$login_prof'");
			echo "The professor $login_prof was removed from the base .<br />";
			$sup = 'yes';
		} else {
			$test = mysql_query("select login from j_groupes_professeurs where login = '$login_prof'");
			if (mysql_num_rows($test)==0) {
				$del = @mysql_query("delete from utilisateurs where login = '$login_prof'");
				echo "The professor $login_prof was removed from the base.<br />";
				$sup = 'yes';
			}
		}
		$i++;
	}
	if ($sup == 'no') {
		echo "<p>No professor was removed !</p>";
	}
	//Suppression des donn�es inutiles dans la tables des mati�res
	echo "<h3 class='gepi'>Checking of the data concerning the courses</h3>";
	$req = mysql_query("select matiere from matieres");
	$sup = 'no';
	$nb_mat = mysql_num_rows($req);
	$i = 0;
	while ($i < $nb_mat) {
		$mat = mysql_result($req, $i, 'matiere');
		$test1 = mysql_query("select id_matiere from j_professeurs_matieres where id_matiere = '$mat'");
		if (mysql_num_rows($test1)==0) {
			$test2 = mysql_query("select id_matiere from j_groupes_matieres where id_matiere = '$mat'");
			if (mysql_num_rows($test2)==0) {
				$del = @mysql_query("delete from matieres where matiere = '$mat'");
				echo "Course $mat was removed from the base.<br />";
				$sup = 'yes';
			}
		}
		$i++;
	}
	if ($sup == 'no') {
		echo "<p>No course was removed !</p>";
	}
	//Suppression des donn�es inutiles dans la tables des responsables
	echo "<h3 class='gepi'>Checking of the data concerning the responsibles of the students</h3>";
	//$req = mysql_query("select ereno, nom1, prenom1 from responsables");
/*
	$req = mysql_query("select ele_id, pers_id from responsables2");
	$sup = 'no';
	$nb_resp = mysql_num_rows($req);
	$i = 0;
	while ($i < $nb_resp) {
		//$resp = mysql_result($req, $i, 'ereno');
		$ele_id=mysql_result($req, $i, 'ele_id');
		$test1 = mysql_query("select ele_id from eleves where ele_id='$ele_id'");
		if (mysql_num_rows($test1)==0) {
			$pers_id=mysql_result($req, $i, 'pers_id');
			$sql="SELECT nom, prenom FROM resp_pers WHERE ele_id='$ele_id'";
			$res_resp=mysql_query($sql);
			while($lig_resp=mysql_fetch_object($res_resp)){
				$nom_resp=$lig_resp->nom;
				$prenom_resp=$lig_resp->prenom;
				$del=@mysql_query("delete from responsables2 where ele_id='$ele_id'");
				//echo "Le responsable ".$prenom_resp." ".$nom_resp." a �t� supprim� de la base pour l'�l�ve n�$ele_id.<br />";
				$sup = 'yes';
			}
		}
		$i++;
	}
*/
	$req = mysql_query("select pers_id,nom,prenom,adr_id from resp_pers order by nom,prenom");
	$sup = 'no';
	$nb_resp = mysql_num_rows($req);
	$i = 0;
	while ($i < $nb_resp) {
		$pers_id=mysql_result($req, $i, 'pers_id');
		$nom_resp=mysql_result($req, $i, 'nom');
		$prenom_resp=mysql_result($req, $i, 'prenom');
		$adr_id=mysql_result($req, $i, 'adr_id');

		$test1 = mysql_query("select r.ele_id from responsables2 r, eleves e where r.pers_id='$pers_id' AND e.ele_id=r.ele_id");
		//$test1 = mysql_query("select ele_id from eleves where ele_id='$ele_id'");
		if (mysql_num_rows($test1)==0) {
			$del=@mysql_query("delete from responsables2 where pers_id='$pers_id'");
			$del=@mysql_query("delete from resp_pers where pers_id='$pers_id'");
			echo "The responsible ".$prenom_resp." ".$nom_resp." was removed from the base .<br />";

			// L'adresse h�berge-t-elle encore un repr�sentant d'�l�ve de l'�tablissement?
			$sql="SELECT * FROM resp_adr ra, eleves e, responsables2 r, resp_pers rp WHERE
					ra.adr_id=rp.adr_id AND
					r.pers_id=rp.pers_id AND
					r.ele_id=e.ele_id AND
					adr_id='$adr_id'";
			$test2=mysql_query($sql);
			if (mysql_num_rows($test1)==0) {
				$sql="delete from resp_adr where adr_id='$adr_id'";
				$del=mysql_query($sql);
			}

			$sup = 'yes';
		}
		$i++;
	}
	if ($sup == 'no') {
		echo "<p>No responsible was removed !</p>";
	}



	//Suppression des donn�es inutiles dans la table j_eleves_etablissements
	echo "<h3 class='gepi'>Checking of the data concerning the school of origin of the students</h3>\n";

	//SELECT e.* FROM eleves e LEFT JOIN j_eleves_etablissements jec ON jec.id_eleve=e.elenoet WHERE jec.id_eleve is NULL;
	//SELECT jec.* FROM j_eleves_etablissements jec LEFT JOIN eleves e ON jec.id_eleve=e.elenoet WHERE e.elenoet IS NULL;
	$sql="SELECT jec.* FROM j_eleves_etablissements jec
			LEFT JOIN eleves e ON jec.id_eleve=e.elenoet
			WHERE e.elenoet IS NULL;";
	$res_jee=mysql_query($sql);
	if(mysql_num_rows($res_jee)==0) {
		echo "<p>No association student/school was removed.</p>\n";
	}
	else {
		$cpt_suppr_jee=0;
		while($lig_jee=mysql_fetch_object($res_jee)) {
			$sql="DELETE FROM j_eleves_etablissements WHERE id_eleve='".$lig_jee->id_eleve."' AND id_etablissement='".$lig_jee->id_etablissement."';";
			$suppr=mysql_query($sql);
			if($suppr) {
				$cpt_suppr_jee++;
			}
		}
		echo "<p>$cpt_suppr_jee association(s) student/school was removed .<br />(<i>for students which are not any more in the school</i>).</p>\n";
	}


	echo "<p><br /></p>\n";

	//echo "<p>Fin de la proc�dure !</p>";
	echo "<p>End of the procedure of importation!</p>";
	//echo "<p><b>Etape ajout�e:</b> Si vous disposez du F_DIV.CSV, vous pouvez <a href='init_pp.php'>initialiser les professeurs principaux</a>.</p>";
}
require("../lib/footer.inc.php");
?>