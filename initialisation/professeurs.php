<?php
@set_time_limit(0);
/*
 * $Id: professeurs.php 5937 2010-11-21 17:42:55Z crob $
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
//"j_aid_eleves",
"j_aid_utilisateurs",
"j_aid_utilisateurs_gest",
"j_groupes_professeurs",
//"j_eleves_classes",
//"j_eleves_etablissements",
"j_eleves_professeurs",
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

//**************** EN-TETE *****************
$titre_page = "Tool of initialization of the year : Importation of the courses";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************

// On v�rifie si l'extension d_base est active
verif_active_dbase();

?>
<p class=bold>|<a href="index.php">Return home initialization</a>|</p>
<?php
echo "<center><h3 class='gepi'>Fourth phase of initialization<br />Importation of the professors</h3></center>";

if (!isset($step1)) {
    $j=0;
    $flag=0;
    while (($j < count($liste_tables_del)) and ($flag==0)) {
        if (mysql_result(mysql_query("SELECT count(*) FROM $liste_tables_del[$j]"),0)!=0) {
            $flag=1;
        }
        $j++;
    }

    $test = mysql_result(mysql_query("SELECT count(*) FROM utilisateurs WHERE statut='professeur'"),0);
    if ($test != 0) {$flag=1;}

    if ($flag != 0){
        echo "<p><b>CAUTION ...</b><br />";
        echo "Data concerning the professors are currently present in base GEPI<br /></p>";
        echo "<p>If you continue the procedure the data such as notes, appreciations, ... will be erased.</p>";
        echo "<ul><li>Only the table containing the users (professors, admin, ...) and the table connecting the courses and the professors will be preserved.</li>";
        echo "<li>Professors of the last year present in base GEPI and not present in base GEP of this year are not erased from GEPI base but
simply declared \"inactifs\".</li>";
        echo "</ul>";
        echo "<form enctype='multipart/form-data' action='professeurs.php' method=post>";
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
    $del = @mysql_query("DELETE FROM tempo2");

    echo "<form enctype='multipart/form-data' action='professeurs.php' method=post>";
    echo "<p>Importation of the file <b>F_wind.dbf</b> containing the data relating to the professors.";
    echo "<p>Please specify the complete name of the fichier <b>F_wind.dbf</b>.";
    echo "<input type=hidden name='is_posted' value='yes' />";
    echo "<input type=hidden name='step1' value='y' />";
    echo "<p><input type='file' size='80' name='dbf_file' />";
    echo "<br /><br /><p>Which formula to apply for the generation of the login ?</p>";
    echo "<input type='radio' name='login_gen_type' value='name' checked /> name";
    echo "<br /><input type='radio' name='login_gen_type' value='name8' /> name (truncated to 8 characters)";
    echo "<br /><input type='radio' name='login_gen_type' value='fname8' /> pname (truncated to 8 characters)";
    echo "<br /><input type='radio' name='login_gen_type' value='fname19' /> pname (truncated to 19 characters)";
    echo "<br /><input type='radio' name='login_gen_type' value='firstdotname' /> first name.name";
    echo "<br /><input type='radio' name='login_gen_type' value='firstdotname19' /> first name.name (truncated to 19 characters)";
    echo "<br /><input type='radio' name='login_gen_type' value='namef8' /> namep (truncated to 8 characters)";
    echo "<br /><input type='radio' name='login_gen_type' value='lcs' /> pname (way LCS)";
    echo "<br /><br /><p>These accounts will be used in Single Sign-On with CASE or LemonLDAP ? (leave 'no' if you do not know what it is)</p>";
    echo "<br /><input type='radio' name='sso' value='no' checked /> No";
    echo "<br /><input type='radio' name='sso' value='yes' /> Yes (no password will be generated)";
    echo "<p><input type=submit value='Validate' />";
    echo "</form>";

} else {

    $dbf_file = isset($_FILES["dbf_file"]) ? $_FILES["dbf_file"] : NULL;
    // On commence par rendre inactifs tous les professeurs
    $req = mysql_query("UPDATE utilisateurs set etat='inactif' where statut = 'professeur'");

 // on efface la ligne "display_users" dans la table "setting" de fa�on � afficher tous les utilisateurs dans la page  /utilisateurs/index.php
    $req = mysql_query("DELETE from setting where NAME = 'display_users'");

    if(strtoupper($dbf_file['name']) == "F_WIND.DBF") {
        $fp = @dbase_open($dbf_file['tmp_name'], 0);
        if(!$fp) {
            echo "<p>Impossible to open the file dbf !</p>";
            echo "<a href='professeurs.php'>Click here </a> to restart !</center></p>";
        } else {
            // on constitue le tableau des champs � extraire
            $tabchamps = array("AINOMU","AIPREN","AICIVI","NUMIND","FONCCO","INDNNI" );

            $nblignes = dbase_numrecords($fp); //number of rows
            $nbchamps = dbase_numfields($fp); //number of fields

            if (@dbase_get_record_with_names($fp,1)) {
                $temp = @dbase_get_record_with_names($fp,1);
            } else {
                echo "<p>The selected file is not valid !<br />";
                echo "<a href='professeurs.php'>Click here </a> to restart !</center></p>";
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

            echo "<p>In the table below, the identifiers in red correspond to new professors in base GEPI. the identifiers in green correspond to professors detected in files GEP but already present in base GEPI.<br /><br />It is possible that certain professors below, although appearing in file GEP, are not any more in exercise in your school this year. This is why it will be proposed to you at the end of the procedure of initialization, a cleaning of the base in order to remove these useless data.</p>";
            echo "<table border=1 cellpadding=2 cellspacing=2>";
            echo "<tr><td><p class=\"small\">Identifier of the professor</p></td><td><p class=\"small\">Name</p></td><td><p class=\"small\">First name</p></td><td>Password *</td></tr>";
            srand();
            $nb_reg_no = 0;
            for($k = 1; ($k < $nblignes+1); $k++){
                $ligne = dbase_get_record($fp,$k);
                for($i = 0; $i < count($tabchamps); $i++) {
                    $affiche[$i] = dbase_filter(trim($ligne[$tabindice[$i]]));
                }
                //Civilit�
                $civilite = '';
                if ($affiche[2] = "ML") $civilite = "Mlle";
                if ($affiche[2] = "MM") $civilite = "Mme";
                if ($affiche[2] = "M.") $civilite = "M.";


                $prenoms = explode(" ",$affiche[1]);
                $premier_prenom = $prenoms[0];
                $prenom_compose = '';
                if (isset($prenoms[1])) $prenom_compose = $prenoms[0]."-".$prenoms[1];

                $test_exist = mysql_query("select login from utilisateurs where (
                nom='".traitement_magic_quotes($affiche[0])."' and
                prenom = '".traitement_magic_quotes($premier_prenom)."' and
                statut='professeur'
                )");
                $result_test = mysql_num_rows($test_exist);
                if ($result_test == 0) {
                    if ($prenom_compose != '') {
                        $test_exist2 = mysql_query("select login from utilisateurs
                        where (
                        nom='".traitement_magic_quotes($affiche[0])."' and
                        prenom = '".traitement_magic_quotes($prenom_compose)."' and
                        statut='professeur'
                        )");
                        $result_test2 = mysql_num_rows($test_exist2);
                        if ($result_test2 == 0) {
                            $exist = 'no';
                        } else {
                            $exist = 'yes';
                            $login_prof_gepi = mysql_result($test_exist2,0,'login');
                        }
                    } else {
                        $exist = 'no';
                    }
                } else {
                    $exist = 'yes';
                    $login_prof_gepi = mysql_result($test_exist,0,'login');
                }
                if ($exist == 'no') {


                    // Aucun professeur ne porte le m�me nom dans la base GEPI. On va donc rentrer ce professeur dans la base

                    $affiche[1] = traitement_magic_quotes(corriger_caracteres($affiche[1]));

                    if ($_POST['login_gen_type'] == "name") {
                        $temp1 = $affiche[0];
                        $temp1 = strtoupper($temp1);
                        $temp1 = my_ereg_replace(" ","", $temp1);
                        $temp1 = my_ereg_replace("-","_", $temp1);
                        $temp1 = my_ereg_replace("'","", $temp1);
                        //$temp1 = substr($temp1,0,8);

                    } elseif ($_POST['login_gen_type'] == "name8") {
                        $temp1 = $affiche[0];
                        $temp1 = strtoupper($temp1);
                        $temp1 = my_ereg_replace(" ","", $temp1);
                        $temp1 = my_ereg_replace("-","_", $temp1);
                        $temp1 = my_ereg_replace("'","", $temp1);
                        $temp1 = substr($temp1,0,8);
                    } elseif ($_POST['login_gen_type'] == "fname8") {
                        $temp1 = $affiche[1]{0} . $affiche[0];
                        $temp1 = strtoupper($temp1);
                        $temp1 = my_ereg_replace(" ","", $temp1);
                        $temp1 = my_ereg_replace("-","_", $temp1);
                        $temp1 = my_ereg_replace("'","", $temp1);
                        $temp1 = substr($temp1,0,8);
                    } elseif ($_POST['login_gen_type'] == "fname19") {
                        $temp1 = $affiche[1]{0} . $affiche[0];
                        $temp1 = strtoupper($temp1);
                        $temp1 = my_ereg_replace(" ","", $temp1);
                        $temp1 = my_ereg_replace("-","_", $temp1);
                        $temp1 = my_ereg_replace("'","", $temp1);
                        $temp1 = substr($temp1,0,19);
                    } elseif ($_POST['login_gen_type'] == "firstdotname") {
                        if ($prenom_compose != '') {
                            $firstname = $prenom_compose;
                        } else {
                            $firstname = $premier_prenom;
                        }

                        $temp1 = $firstname . "." . $affiche[0];
                        $temp1 = strtoupper($temp1);

                       $temp1 = my_ereg_replace(" ","", $temp1);
                        $temp1 = my_ereg_replace("-","_", $temp1);
                        $temp1 = my_ereg_replace("'","", $temp1);
                        //$temp1 = substr($temp1,0,19);
                    } elseif ($_POST['login_gen_type'] == "firstdotname19") {
                        if ($prenom_compose != '') {
                            $firstname = $prenom_compose;
                        } else {
                            $firstname = $premier_prenom;
                        }

                        $temp1 = $firstname . "." . $affiche[0];
                        $temp1 = strtoupper($temp1);
                        $temp1 = my_ereg_replace(" ","", $temp1);
                        $temp1 = my_ereg_replace("-","_", $temp1);
                        $temp1 = my_ereg_replace("'","", $temp1);
                        $temp1 = substr($temp1,0,19);
                    } elseif ($_POST['login_gen_type'] == "namef8") {
                        $temp1 =  substr($affiche[0],0,7) . $affiche[1]{0};
                        $temp1 = strtoupper($temp1);
                        $temp1 = my_ereg_replace(" ","", $temp1);
                        $temp1 = my_ereg_replace("-","_", $temp1);
                        $temp1 = my_ereg_replace("'","", $temp1);
                        //$temp1 = substr($temp1,0,8);
                    } elseif ($_POST['login_gen_type'] == "lcs") {
                        $nom = $affiche[0];
                       $nom = strtolower($nom);
                       if (preg_match("/\s/",$nom)) {
                           $noms = preg_split("/\s/",$nom);
                           $nom1 = $noms[0];
                           if (strlen($noms[0]) < 4) {
                               $nom1 .= "_". $noms[1];
                               $separator = " ";
                            } else {
                               $separator = "-";
                            }
                        } else {
                           $nom1 = $nom;
                            $sn = ucfirst($nom);
                        }
                        $firstletter_nom = $nom1{0};
                        $firstletter_nom = strtoupper($firstletter_nom);
                        $prenom = $affiche[1];
                        $prenom1 = $affiche[1]{0};
                        $temp1 = $prenom1 . $nom1;
                    }
                        $login_prof = $temp1;
                    // On teste l'unicit� du login que l'on vient de cr�er
                    $m = 2;
                    $test_unicite = 'no';
                    $temp = $login_prof;
                    while ($test_unicite != 'yes') {
                        $test_unicite = test_unique_login($login_prof);
                        if ($test_unicite != 'yes') {
                            $login_prof = $temp.$m;
                            $m++;
                        }
                    }
                    $affiche[0] = traitement_magic_quotes(corriger_caracteres($affiche[0]));
                    // Mot de passe
                    if (strlen($affiche[5])>2 and $affiche[4]=="ENS" and $_POST['sso']== "no") {
                        //
                        $pwd = md5(trim($affiche[5])); //NUMEN
                        $mess_mdp = "NUMEN";
                    } elseif ($_POST['sso']== "no") {
                       $pwd = md5(rand (1,9).rand (1,9).rand (1,9).rand (1,9).rand (1,9).rand (1,9));
                       $mess_mdp = $pwd;
//                       $mess_mdp = "Inconnu (compte bloqu�)";
                    } elseif ($_POST['sso'] == "yes") {
                        $pwd = '';
                        $mess_mdp = "aucun (sso)";
                    }

                    // utilise le pr�nom compos� s'il existe, plut�t que le premier pr�nom

                    //$res = mysql_query("INSERT INTO utilisateurs VALUES ('".$login_prof."', '".$affiche[0]."', '".$premier_prenom."', '".$civilite."', '".$pwd."', '', 'professeur', 'actif', 'y', '')");
					$sql="INSERT INTO utilisateurs SET login='$login_prof', nom='$affiche[0]', prenom='$premier_prenom', civilite='$civilite', password='$pwd', statut='professeur', etat='actif', change_mdp='y'";
					$res = mysql_query($sql);
					// Pour debug:
					//echo "<tr><td colspan='4'>$sql</td></tr>";

                    if(!$res) $nb_reg_no++;
                    $res = mysql_query("INSERT INTO tempo2 VALUES ('".$login_prof."', '".$affiche[3]."')");
                    echo "<tr><td><p><font color='red'>".$login_prof."</font></p></td><td><p>".$affiche[0]."</p></td><td><p>".$premier_prenom."</p></td><td>".$mess_mdp."</td></tr>";
                } else {

                    $res = mysql_query("UPDATE utilisateurs set etat='actif' where login = '".$login_prof_gepi."'");
                    if(!$res) $nb_reg_no++;
                    $res = mysql_query("INSERT INTO tempo2 VALUES ('".$login_prof_gepi."', '".$affiche[3]."')");
                    echo "<tr><td><p><font color='green'>".$login_prof_gepi."</font></p></td><td><p>".$affiche[0]."</p></td><td><p>".$affiche[1]."</p></td><td>Inchang�</td></tr>";
                }
            }
            dbase_close($fp);
            echo "</table>";
            if ($nb_reg_no != 0) {
                echo "<p>During recording of the data there was $nb_reg_no errors. Test find the cause of the error and restart the procedure before passing at the next stage.";
          } else {
                echo "<p>The importation of the professors in base GEPI was carried out successfully !</p>";

                echo "<p><b>* Precision on the passwords (in non-SSO) :</b><br />
                (it is advised to print this page)</p>
                <ul>
                <li>When a new professor is inserted in base GEPI, its password during first
                 connection to GEPI is his NUMEN.</li>
                <li>If the NUMEM is not available in the file F_wind.dbf, GEPI generates a password randomly.</li></ul>";
                echo "<p><b>In all the cases the new user is brought to change his password during the his first connection.</b></p>";
                echo "<br /><p>You can proceed to the fifth phase of assignment of the courses to
each professor, of assignment of the professors in each class and of definition of the options followed by the students.</p>";
            }
            echo "<center><p><b><a href='prof_disc_classe.php'>Proceed to the fifth phase of initialization</a></b></p></center><br /><br />";
        }
    } else if (trim($dbf_file['name'])=='') {
        echo "<p>No file was selected !<br />";
        echo "<a href='professeurs.php'>Click here </a> to restart !</p>";

    } else {
        echo "<p>The selected file is not valid !<br />";
        echo "<a href='professeurs.php'>Click here </a> to restart !</p>";
    }
}
echo "<p><br /></p>\n";
require("../lib/footer.inc.php");
?>
