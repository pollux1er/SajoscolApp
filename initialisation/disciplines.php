<?php

@set_time_limit(0);
/*
 * $Id: disciplines.php 5937 2010-11-21 17:42:55Z crob $
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

// Page bourrin�e... la gestion du token n'est pas faite... et ne sera faite que si quelqu'un utilise encore ce mode d'initialisation et le manifeste sur la liste de diffusion gepi-users
check_token();

$liste_tables_del = array(
//"absences",
//"aid",
//"aid_appreciations",
//"aid_config",
//"avis_conseil_classe",
//"classes",
//"droits",
//"eleves",
//"responsables",
//"etablissements",
"groupes",
//"j_aid_eleves",
//"j_aid_utilisateurs",
//"j_eleves_classes",
//"j_eleves_etablissements",
"j_eleves_groupes",
"j_groupes_matieres",
"j_groupes_professeurs",
"j_groupes_classes",
"j_signalement",
//"j_eleves_professeurs",
//"j_eleves_regime",
//"j_professeurs_matieres",
//"log",
//"matieres",
"matieres_appreciations",
"matieres_notes",
"matieres_appreciations_grp",
"matieres_appreciations_tempo",
//"periodes",
"tempo2",
//"temp_gep_import",
"tempo",
//"utilisateurs",
"cn_cahier_notes",
"cn_conteneurs",
"cn_devoirs",
"cn_notes_conteneurs",
"cn_notes_devoirs",
//"setting"
);


if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
die();
}

//**************** EN-TETE *****************
$titre_page = "Tool of initialization of the year : Importation of the courses";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
?>
<p class=bold>|<a href="index.php">Retour accueil initialisation</a>|</p>

<?php

// On v�rifie si l'extension d_base est active
verif_active_dbase();

echo "<center><h3 class='gepi'>Third phase of initialization<br />Importation of the courses</h3></center>";

if (!isset($step1)) {
    $j=0;
    $flag=0;
    while (($j < count($liste_tables_del)) and ($flag==0)) {
        if (mysql_result(mysql_query("SELECT count(*) FROM $liste_tables_del[$j]"),0)!=0) {
            $flag=1;
        }
        $j++;
    }
    if ($flag != 0){
        echo "<p><b>CAUTION ...</b><br />";
        echo "Data concerning the courses are currently present in base GEPI<br /></p>";
        echo "<p>If you continue the procedure the data such as notes, appreciations, ... will be erased.</p>";
        echo "<p>Only the table containing the courses and the table connecting the courses and the professors will be preserved.</p>";

        echo "<form enctype='multipart/form-data' action='disciplines.php' method=post>";
        echo "<input type=hidden name='step1' value='y' />";
        echo "<input type='submit' name='confirm' value='Continue the procedure' />";
        echo "</form>";
		echo "<p><br /></p>\n";
		require("../lib/footer.inc.php");
        die();
    }
}

if (!isset($is_posted)) {
    $j=0;
    while ($j < count($liste_tables_del)) {
        if (mysql_result(mysql_query("SELECT count(*) FROM $liste_tables_del[$j]"),0)!=0) {
            $del = @mysql_query("DELETE FROM $liste_tables_del[$j]");
        }
        $j++;
    }

    echo "<p><b>CAUTION ...</b><br />You should proceed to this operation only if the constitution ofthe classes were carried out !</p>";
    echo "<p>Importation of the file <b>F_tmt.dbf</b> containing the data relating to the courses : please specify the complete name of the file <b>F_tmt.dbf</b>.";
    echo "<form enctype='multipart/form-data' action='disciplines.php' method=post>";
    echo "<input type=hidden name='is_posted' value='yes' />";
    echo "<input type=hidden name='step1' value='y' />";
    echo "<p><input type='file' size='80' name='dbf_file' />";
    echo "<p><input type=submit value='Validate' />";
    echo "</form>";

} else {
    $dbf_file = isset($_FILES["dbf_file"]) ? $_FILES["dbf_file"] : NULL;
    if(strtoupper($dbf_file['name']) == "F_TMT.DBF") {
        $fp = dbase_open($dbf_file['tmp_name'], 0);
        if(!$fp) {
            echo "<p>Impossible to open the file dbf</p>";
            echo "<p><a href='disciplines.php'>Click here </a> to restart !</p>";
        } else {
            // on constitue le tableau des champs � extraire
            $tabchamps = array("MATIMN","MATILC");

            $nblignes = dbase_numrecords($fp); //number of rows
            $nbchamps = dbase_numfields($fp); //number of fields

            if (@dbase_get_record_with_names($fp,1)) {
                $temp = @dbase_get_record_with_names($fp,1);
            } else {
                echo "<p>The selected file is not valid !<br />";
                echo "<a href='disciplines.php'>Click here </a> to restart !</p>";
                die();
            }

            $nb = 0;
            foreach($temp as $key => $val){
                $en_tete[$nb] = "$key";
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
            echo "<p>In the table below, the identifiers in red correspond to new courses in base GEPI. the identifiers in green correspond to course identifiers detected in file GEP but already present in base GEPI.<br /><br />It is possible that certain courses below, although appearing in file GEP, are not used in your school this year. This is why it will be proposed to you at the end of the procedure of initialsation, a cleaning of the base in order to remove these useless data.</p>";
            echo "<table border=1 cellpadding=2 cellspacing=2>";
            echo "<tr><td><p class=\"small\">Identifier of the course</p></td><td><p class=\"small\">Complete name</p></td></tr>";


            $nb_reg_no = 0;
            for($k = 1; ($k < $nblignes+1); $k++){
                $ligne = dbase_get_record($fp,$k);
                for($i = 0; $i < count($tabchamps); $i++) {
                    $affiche[$i] = traitement_magic_quotes(corriger_caracteres(dbase_filter(trim($ligne[$tabindice[$i]]))));
                }
                $verif = mysql_query("select matiere, nom_complet from matieres where matiere='$affiche[0]'");
                $resverif = mysql_num_rows($verif);
                if($resverif == 0) {
                    $req = mysql_query("insert into matieres set matiere='$affiche[0]', nom_complet='$affiche[1]', priority='0',matiere_aid='n',matiere_atelier='n'");
                    if(!$req) {
                        $nb_reg_no++; echo mysql_error();
                    } else {
                        echo "<tr><td><p><font color='red'>$affiche[0]</font></p></td><td><p>$affiche[1]</p></td></tr>";
                    }
                } else {
                    $nom_complet = mysql_result($verif,0,'nom_complet');
                    echo "<tr><td><p><font color='green'>$affiche[0]</font></p></td><td><p>$nom_complet</p></td></tr>";
                }
            }
            echo "</table>";
            dbase_close($fp);
            if ($nb_reg_no != 0) {
                echo "<p>During recording of the data there was $nb_reg_no errors. Test find the cause of the error and restart procedure before passing to the next stage.";
            } else {
                echo "<p>The importation of the courses in base GEPI was carried out successfully !<br />You can proceed to the fourth phase of importation of the professors.</p>";
            }
            echo "<center><p><a href='professeurs.php'>Importation of the professors</a></p></center>";
        }
    } else if (trim($dbf_file['name'])=='') {
        echo "<p>No file was selected !<br />";
        echo "<a href='disciplines.php'>Click here </a> to restart !</p>";

    } else {
        echo "<p>The selected file is not valid !<br />";
        echo "<a href='disciplines.php'>Click here </a> to restart !</p>";
    }
}
echo "<p><br /></p>\n";
require("../lib/footer.inc.php");
?>
