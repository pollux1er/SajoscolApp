<?php
/*
* @version: $Id: import_absences_sconet.php 8729 2011-12-13 20:04:12Z crob $
*/

@set_time_limit(0);

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

// INSERT INTO droits VALUES ('/absences/import_absences_sconet.php', 'F', 'F', 'V', 'F', 'F', 'F', 'V', 'Saisie des absences', '');
if (!checkAccess()) {
	header("Location: ../logout.php?auto=1");
	die();
}

$temp_table_abs='temp_abs_import';

//**************** EN-TETE *****************
$titre_page = "Import absences SCONET";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************

//debug_var();

function extr_valeur($lig){
	unset($tabtmp);
	$tabtmp=explode(">",my_ereg_replace("<",">",$lig));
	return trim($tabtmp[2]);
}

function get_nom_class_from_id($id){
	$classe=NULL;

	$sql="SELECT classe FROM classes WHERE id='$id';";
	$res_class=mysql_query($sql);

	if(mysql_num_rows($res_class)>0){
		$lig_tmp=mysql_fetch_object($res_class);
		$classe=$lig_tmp->classe;
	}
	return $classe;
}


?>
	<div class="content">
		<?php

			$is_posted=isset($_POST['is_posted']) ? $_POST['is_posted'] : NULL;
			$etape=isset($_POST['etape']) ? $_POST['etape'] : (isset($_GET['etape']) ? $_GET['etape'] : NULL);

			if(isset($_GET['ad_retour'])){
				$_SESSION['ad_retour']=$_GET['ad_retour'];
			}

			// Initialisation du r�pertoire actuel de sauvegarde
			//$dirname = getSettingValue("backup_directory");

			echo "<h2 align='center'>Import des absences de Sconet</h2>\n";

			//echo "<p><a href='index.php'>Retour</a>|\n";
			echo "<p class=bold><a href='";
			if(isset($_SESSION['ad_retour'])){
				echo $_SESSION['ad_retour'];
			}
			else{
				echo "index.php";
			}
			echo "'><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a>";
			//echo "</p>\n";


			// Il faudra pouvoir g�rer id_classe comme un tableau
			$id_classe=isset($_POST['id_classe']) ? $_POST['id_classe'] : (isset($_GET['id_classe']) ? $_GET['id_classe'] : NULL);
			$num_periode=isset($_POST['num_periode']) ? $_POST['num_periode'] : (isset($_GET['num_periode']) ? $_GET['num_periode'] : NULL);
			$max_per=isset($_POST['max_per']) ? $_POST['max_per'] : (isset($_GET['max_per']) ? $_GET['max_per'] : NULL);


			include "../lib/periodes.inc.php";

			if(!isset($num_periode)) {

				$sql="SELECT MAX(num_periode) AS max_per, id_classe FROM periodes GROUP BY id_classe ORDER BY max_per;";
				$res1=mysql_query($sql);

				unset($tab_max_per);
				$tab_max_per=array();
				while($lig1=mysql_fetch_object($res1)){
					if(!in_array($lig1->max_per,$tab_max_per)){
						//echo "$lig1->id_classe: $lig1->max_per<br />\n";
						$tab_max_per[]=$lig1->max_per;
					}
				}
				sort($tab_max_per);

				if(count($tab_max_per)==0){
					echo "<p><span style='color:red;'>ERREUR:</span> It seems that no class has definite period.</p>\n";
					require("../lib/footer.inc.php");
					exit();
				}
				elseif(count($tab_max_per)==1){
					echo "<form enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."' method='post'>\n";

					echo "<p>Choisissez la p�riode � importer:<br />\n";

					/*
					$sql="SELECT DISTINCT num_periode FROM periodes WHERE MAX(num_periode)= ORDER BY num_periode;";
					$res_per=mysql_query($sql);
					while($lig_tmp=mysql_fetch_object($res_per)){
						// Il ne faudrait proposer que les p�riodes ouvertes en saisie, non?
						echo "<input type='radio' name='num_periode' value='$lig_tmp->num_periode' /> P�riode $lig_tmp->num_periode<br />\n";
					}
					*/

					$i=0;
					for($j=1;$j<=$tab_max_per[$i];$j++){
						//echo "<input type='radio' name='num_periode' value='$j' /> P�riode $j<br />\n";
						if($j==1){$checked=" checked";}else{$checked="";}
						echo "<input type='radio' name='num_periode' id='num_periode_$j' value='$j'$checked /><label for='num_periode_$j' style='cursor: pointer;'> Period $j</label><br />\n";
					}
					echo "<input type='hidden' name='max_per' value='$tab_max_per[$i]' />\n";

					echo "<p><input type='submit' value='Validate' /></p>\n";
					echo "</form>\n";
				}
				else{

					echo "<p>Choose the period to import:</p>\n";
					//echo "<ul>\n";
					echo "<table class='boireaus'>\n";
					$alt=1;
					for($i=0;$i<count($tab_max_per);$i++){
						//echo "<li>\n";

						$alt=$alt*(-1);
						echo "<tr class='lig$alt'><td>\n";

							echo "<form enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."' method='post'>\n";
							echo "<table border='0'>\n";
							echo "<tr><td valign='top' style='border:0px;'> $tab_max_per[$i] periods Class:</td>\n";
							echo "<td style='border:0px;'>\n";
							for($j=1;$j<=$tab_max_per[$i];$j++){
								if($j==1){$checked=" checked";}else{$checked="";}
								echo "<input type='radio' name='num_periode' id='num_periode_".$j."_".$tab_max_per[$i]."' value='$j'$checked /><label for='num_periode_".$j."_".$tab_max_per[$i]."' style='cursor: pointer;'> Period $j</label><br />\n";
							}
							echo "</td>\n";

							/*
							$sql="SELECT DISTINCT num_periode FROM periodes WHERE MAX(num_periode)='$tab_max_per[$i]' ORDER BY num_periode;";
							echo "$sql<br />\n";
							$res_per=mysql_query($sql);
							while($lig_tmp=mysql_fetch_object($res_per)){
								// Il ne faudrait proposer que les p�riodes ouvertes en saisie, non?
								echo "<input type='radio' name='num_periode' value='$lig_tmp->num_periode' /> P�riode $lig_tmp->num_periode<br />\n";
							}
							*/

							echo "<td valign='top' style='border:0px;'>\n";
							echo "<input type='hidden' name='max_per' value='$tab_max_per[$i]' />\n";
							echo "<p><input type='submit' value='Validate' /></p>\n";
							echo "</td>\n";
							echo "</tr>\n";
							echo "</table>\n";
							echo "</form>\n";

						//echo "</li>\n";
						echo "</td></tr>\n";

					}
					//echo "</ul>\n";
					echo "</table>\n";

					echo "<p><i>NOTE:</i> It is not possible simultaneously to import absences of classes  with differents number of periods .</p>\n";
				}
			}
			else {

				// =======================================================================

				if(!isset($id_classe)) {
					echo " | <a href='".$_SERVER['PHP_SELF']."'>Choose another period</a>\n";
					echo "</p>\n";

					if ((($_SESSION['statut']=="cpe")&&(getSettingValue('GepiAccesAbsTouteClasseCpe')=='yes'))||($_SESSION['statut']!="cpe")) {
						$sql="SELECT DISTINCT c.* FROM classes c, periodes p WHERE p.id_classe = c.id  AND p.num_periode='$num_periode' ORDER BY classe;";
					} else {
						$sql="SELECT DISTINCT c.* FROM classes c, j_eleves_cpe e, j_eleves_classes jc, periodes p WHERE (e.cpe_login = '".$_SESSION['login']."' AND jc.login = e.e_login AND c.id = jc.id_classe AND p.id_classe = c.id  AND p.num_periode='$num_periode')  ORDER BY classe;";
					}
					//echo "$sql<br />\n";

					$res_classe=mysql_query($sql);

					$nb_classes = mysql_num_rows($res_classe);
					if ($nb_classes==0) {
						echo "<p>No class was found.</p>\n";
					}
					else{
						echo "<form enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."' method='post'>\n";

						echo "<p>Choose the classes to import.</p>\n";

						$nb_class_par_colonne=round($nb_classes/3);
						//echo "<table width='100%' border='1'>\n";
						echo "<table width='100%'>\n";
						echo "<tr valign='top' align='center'>\n";

						echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
						echo "<td align='left'>\n";

							echo "<table border='0'>\n";
						$i=0;
						while ($lig_classe=mysql_fetch_object($res_classe)) {

							$id_classe=$lig_classe->id;
							$classe=$lig_classe->classe;

							if(($i>0)&&(round($i/$nb_class_par_colonne)==$i/$nb_class_par_colonne)){
									echo "</table>\n";
								echo "</td>\n";
								//echo "<td style='padding: 0 10px 0 10px'>\n";
								echo "<td align='left'>\n";
									echo "<table border='0'>\n";
							}

							$sql="SELECT MAX(num_periode) AS max_per FROM periodes WHERE id_classe='$id_classe';";
							$test=mysql_query($sql);

							if(mysql_num_rows($test)==0){
								echo "<tr><td>&nbsp;</td><td>Classe: $classe (<i>pas de p�riode?</i>)</td></tr>\n";
							}
							else{
								$lig_tmp=mysql_fetch_object($test);
								if($lig_tmp->max_per!=$max_per){
									echo "<tr><td>&nbsp;</td><td>Classe: $classe (<i>$lig_tmp->max_per p�riodes</i>)</td></tr>\n";
								}
								else{
									// Un compte secours peut saisir en p�riode partiellement close
									if($_SESSION['statut']=='secours') {
										$sql="SELECT verouiller FROM periodes WHERE (verouiller='N' OR verouiller='P') AND id_classe='$id_classe' AND num_periode='$num_periode';";
									}
									else {
										$sql="SELECT verouiller FROM periodes WHERE verouiller='N' AND id_classe='$id_classe' AND num_periode='$num_periode';";
									}
									$test=mysql_query($sql);
									if(mysql_num_rows($test)==0){
										echo "<tr><td>&nbsp;</td><td>Classe: $classe (<i>closed period</i>)</td></tr>\n";
									}
									else{
										echo "<tr>\n";
										echo "<td>\n";
										echo "<input type='checkbox' name='id_classe[]' id='case_".$i."' value='$id_classe' />";
										echo "</td>\n";
										echo "<td>\n";
										echo "<label for='case_".$i."' style='cursor: pointer;'>Classe : $classe</label><br />\n";
									}
								}
							}


							//echo "<span class = \"norme\"><input type='checkbox' name='$temp' value='yes' onclick=\"verif1()\" />";
							//echo "Classe : $classe </span><br />\n";
							$i++;
						}
							echo "</table>\n";
						echo "</td>\n";
						echo "</tr>\n";
						echo "</table>\n";
						echo "<p><input type='submit' value='Validate' /></p>\n";
						echo "<input type='hidden' name='num_periode' value='$num_periode' />\n";

						echo "<p><a href='#' onClick='Coche_ou_pas(true); return false;'>Check all</a> / <a href='#' onClick='Coche_ou_pas(false); return false;'>Uncheck all</a></p>\n";

						echo "</form>\n";


						echo "<p><i>NOTE:</i> Only the absences of the checked classes will be imported (<i>even if l'ExportSconet contains the absences of all the classes</i>).</p>\n";


						echo "<script type='text/javascript' language='javascript'>

	function Coche_ou_pas(mode) {
		for(i=0;i<$i;i++) {
			if(document.getElementById('case_'+i)){
				document.getElementById('case_'+i).checked = mode;
			}
		}
	}

	</script>\n";

					}
				}
				else {

						if(!isset($_POST['is_posted'])) {
							$etape=1;

							//echo "<p>Cette page permet de remplir des tableaux PHP avec les informations �l�ves, responsables,...<br />\n";
							echo "<p>This page makes it possible to fill of the temporary tables with the information extracted from the XML.<br />\n";
							echo "</p>\n";
							echo "<form enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."' method='post'>\n";
							echo add_token_field();

							echo "<input type='hidden' name='num_periode' value='$num_periode' />\n";

							// Il faudrait ajouter un test ici... on pourrait injecter une classe pour laquelle la p�riode $num_periode est close.
							if(is_array($id_classe)){
								for($i=0;$i<count($id_classe);$i++){
									echo "<input type='hidden' name='id_classe[]' value='$id_classe[$i]' />\n";
								}
							}
							else{
								echo "<input type='hidden' name='id_classe[]' value='$id_classe' />\n";
							}

							echo "<p>Please provide the file exportAbsence.xml:<br />\n";
							echo "<input type=\"file\" size=\"60\" name=\"absences_xml_file\" /><br />\n";
							echo "<input type='hidden' name='etape' value='$etape' />\n";
							echo "<input type='hidden' name='is_posted' value='yes' />\n";
							echo "</p>\n";

							echo "<p><input type='submit' value='Validate' /></p>\n";
							echo "</form>\n";

							echo "<p><b>CAUTION</b>:</p>\n";
							echo "<ul>\n";
							echo "<li><p>Provide an export of only one period .<br />If several periods are checked at the time of your export, imported information is likely to be erroneous.</p></li>\n";
							echo "<li><p>To recover the export of Sconet:<br />\n";
							echo "On the left menu :<br />\n";
							echo "IMPORT/EXPORT -> Export Absences and Delay ->Select the period (T1, T2, T3)<br />\n";
							echo "Then click on the button 'Export the selected periods'.</p>\n";
							echo "</ul>\n";
						}
						else {
							check_token();

							$post_max_size=ini_get('post_max_size');
							$upload_max_filesize=ini_get('upload_max_filesize');
							$max_execution_time=ini_get('max_execution_time');
							$memory_limit=ini_get('memory_limit');


							if($etape==1) {
								$xml_file = isset($_FILES["absences_xml_file"]) ? $_FILES["absences_xml_file"] : NULL;
								$fp=fopen($xml_file['tmp_name'],"r");
								if($fp){
									//echo "<h3>Lecture du fichier absences...</h3>\n";
									//echo "<blockquote>\n";
									while(!feof($fp)){
										$ligne[]=fgets($fp,4096);
									}
									fclose($fp);
									//echo "<p>Termin�.</p>\n";
									flush();


									//echo "<h3>Analyse du fichier pour extraire les informations...</h3>\n";
									//echo "<blockquote>\n";

									$cpt=0;
									$eleves=array();
									$temoin_eleves=0;
									$temoin_ele=0;
									$temoin_options=0;
									$temoin_scol=0;
									//Compteur �l�ve:
									$i=-1;

									$tab_champs_parametres=array("uaj",
									"annee_scolaire",
									"date_export",
									"horodatage");


									// PARTIE <PARAMETRES>
									$temoin_parametres=0;
									$tab_parametres=array();
									while($cpt<count($ligne)){
										//echo "<p>".htmlentities($ligne[$cpt])."<br />\n";
										if(strstr($ligne[$cpt],"<PARAMETRES>")){
											//echo "D�but de la section PARAMETRES � la ligne <span style='color: blue;'>$cpt</span><br />\n";
											$temoin_parametres++;
										}
										if(strstr($ligne[$cpt],"</PARAMETRES>")){
											//echo "Fin de la section PARAMETRES � la ligne <span style='color: blue;'>$cpt</span><br />\n";
											$temoin_parametres++;
											break;
										}
										if($temoin_parametres==1){
											if(strstr($ligne[$cpt],"<UAJ>")){
												$tab_parametres['uaj']=extr_valeur($ligne[$cpt]);
											}

											if(strstr($ligne[$cpt],"<ANNEE_SCOLAIRE>")){
												$tab_parametres['annee_scolaire']=extr_valeur($ligne[$cpt]);
											}

											if(strstr($ligne[$cpt],"<DATE_EXPORT>")){
												$tab_parametres['date_export']=extr_valeur($ligne[$cpt]);
											}

											if(strstr($ligne[$cpt],"<HORODATAGE>")){
												$tab_parametres['horodatage']=extr_valeur($ligne[$cpt]);
											}
										}
										$cpt++;
									}



									$tab_champs_periode=array("libelle",
									"date_debut",
									"date_fin");

									// PARTIE <PERIODE>
									$temoin_periode=0;
									$tab_periode=array();
									while($cpt<count($ligne)){
										//echo "<p>".htmlentities($ligne[$cpt])."<br />\n";
										if(strstr($ligne[$cpt],"<PERIODE>")){
											//echo "D�but de la section PERIODE � la ligne <span style='color: blue;'>$cpt</span><br />\n";
											$temoin_periode++;
										}
										if(strstr($ligne[$cpt],"</PERIODE>")){
											//echo "Fin de la section PERIODE � la ligne <span style='color: blue;'>$cpt</span><br />\n";
											$temoin_periode++;
											break;
										}
										if($temoin_periode==1){
											if(strstr($ligne[$cpt],"<LIBELLE>")){
												$tab_periode['libelle']=extr_valeur($ligne[$cpt]);
											}

											if(strstr($ligne[$cpt],"<DATE_DEBUT>")){
												$tab_periode['date_debut']=extr_valeur($ligne[$cpt]);
											}

											if(strstr($ligne[$cpt],"<DATE_FIN>")){
												$tab_periode['date_fin']=extr_valeur($ligne[$cpt]);
											}
										}
										$cpt++;
									}

									$tab_champs_eleve=array("elenoet",
									"libelle",
									"nbAbs",
									"nbNonJustif",
									"nbRet",
									"nomEleve",
									"prenomEleve"
									);

									// PARTIE <ELEVES>
									$i=-1;
									// Pour les fichiers XML bricoles sans les sections precedentes:
									$cpt=0;
									while($cpt<count($ligne)){
										if(strstr($ligne[$cpt],"<eleve ")){
											$i++;
											$eleves[$i]=array();

											$ligne_courante=$ligne[$cpt];
											while(!my_ereg("/>",$ligne[$cpt])){
												$cpt++;
												$ligne_courante.=" ".trim($ligne[$cpt]);
											}

											//echo "<p>".$ligne_courante."<br />\n";

											unset($tab_tmp);
											# En coupant aux espaces, on a des blagues sur les noms avec espaces...
											$tab_tmp=explode(" ",$ligne_courante);
											for($j=0;$j<count($tab_tmp);$j++){
												//echo "\$tab_tmp[$j]=".$tab_tmp[$j]."<br />";
												if(my_ereg("=",$tab_tmp[$j])) {
													unset($tab_tmp2);
													$tab_tmp2=explode("=",my_ereg_replace('"','',$tab_tmp[$j]));
													//echo "\$tab_tmp2[0]=".$tab_tmp[0]."<br />";
													//echo "\$tab_tmp2[1]=".$tab_tmp[1]."<br />";
													//$eleves[$i][trim($tab_tmp2[0])]=trim($tab_tmp2[1]);
													$eleves[$i][trim($tab_tmp2[0])]=trim(my_ereg_replace("/>$","",$tab_tmp2[1]));
												}
											}
										}
										$cpt++;
									}

									/*
									echo "<pre>";
									print_r($eleves);
									echo "</pre>";
									*/

									/*
									echo "<table border='1'>";
									for($i=0;$i<count($eleves);$i++){
										echo "<tr>";
										echo "<td>$i</td>";
										foreach($eleves[$i] as $cle => $valeur){
											echo "<td>\$eleves[$i][\"$cle\"]=".$eleves[$i][$cle]."</td>\n";
										}
										echo "</tr>";
									}
									echo "</table>";
									*/



									/*
									$num_periode="NaN";
									if(isset($tab_periode['libelle'])) {
										$num_periode=trim(my_ereg_replace("^T","",$tab_periode['libelle']));
									}
									*/


									//$suffixe_table_temp=preg_replace("/[^0-9]/","",time());
									//$temp_table_abs='temp_abs_'.$suffixe_table_temp;
									$sql="CREATE TABLE IF NOT EXISTS $temp_table_abs (
									id INT(11) not null auto_increment,
									login VARCHAR(50) not null,
									cpe_login VARCHAR(50) not null,
									nbret INT(11) not null,
									nbabs INT(11) not null,
									nbnj INT(11) not null,
									primary key (id));";
									$create_table = mysql_query($sql);

									//$sql="TRUNCATE TABLE $temp_table_abs;";
									$sql="DELETE FROM $temp_table_abs WHERE cpe_login='".$_SESSION['login']."';";
									//echo "$sql<br />";
									$res=mysql_query($sql);

									echo "<form enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."' method='post'>\n";
									// On a fait en sorte � l'�tape pr�c�dente, qu'il n'y ait qu'une classe ou plusieurs, que l'on transmette un tableau id_classe[]
									for($i=0;$i<count($id_classe);$i++){
										echo "<input type='hidden' name='id_classe[]' value='$id_classe[$i]' />\n";
									}

									echo "<table class='boireaus'>\n";
									echo "<tr>\n";
									echo "<th>&nbsp;</th>\n";
									echo "<th>Elenoet</th>\n";
									echo "<th>Name</th>\n";
									echo "<th>First name</th>\n";
									echo "<th>Class</th>\n";
									echo "<th>Number of absences</th>\n";
									echo "<th>Not justified</th>\n";
									echo "<th>Numbers of delays</th>\n";
									echo "</tr>\n";

									$chaine_liste_classes="(";
									for($i=0;$i<count($id_classe);$i++){
										if($i>0){$chaine_liste_classes.=" OR ";}
										$chaine_liste_classes.="id_classe='$id_classe[$i]'";
									}
									$chaine_liste_classes.=")";

									$nb_err=0;
									$alt=-1;
									for($i=0;$i<count($eleves);$i++){

										$ligne_tableau="";
										$affiche_ligne="n";

										if(isset($eleves[$i]['elenoet'])){
											// Est-ce que l'�l�ve fait bien partie d'une des classes import�es pour la p�riode import�e?
											//$sql="SELECT 1=1 FROM j_eleves_classes jec, eleves e WHERE jec.login=e.login AND e.no_gep='".$eleves[$i]['elenoet']."' AND periode='$num_periode' AND $chaine_liste_classes;";
											$sql="SELECT 1=1 FROM j_eleves_classes jec, eleves e WHERE jec.login=e.login AND (e.elenoet='".$eleves[$i]['elenoet']."' OR e.elenoet='0".$eleves[$i]['elenoet']."') AND periode='$num_periode' AND $chaine_liste_classes;";
											//echo "$sql<br />";
											//echo "<!--\n$sql\n-->\n";
											$test=mysql_query($sql);

											if(mysql_num_rows($test)>0){

												$alt=$alt*(-1);
												$ligne_tableau.="<tr class='lig$alt'>\n";
												$ligne_tableau.="<td>$i</td>\n";
												$ligne_tableau.="<td>".$eleves[$i]['elenoet']."</td>\n";

												// R�cup�ration des infos sur l'�l�ve (on a au moins besoin du login pour tester si le CPE a cet �l�ve.
												$sql="SELECT e.login,e.nom,e.prenom,e.elenoet
															FROM eleves e
															WHERE (e.elenoet='".$eleves[$i]['elenoet']."' OR e.elenoet='0".$eleves[$i]['elenoet']."')";
												//echo "$sql<br />";
												//echo "<!--\n$sql\n-->\n";
												$res1=mysql_query($sql);
												if(mysql_num_rows($res1)==0){
													$ligne_tableau.="<td style='color:red;' colspan='3'>Student absent from your table 'eleves'???</td>\n";
													$nb_err++;
												}
												elseif(mysql_num_rows($res1)>1){
													$ligne_tableau.="<td style='color:red;' colspan='3'>More than one Student corresponds to this ELENOET ???</td>\n";
													$nb_err++;
												}
												else{

													$lig1=mysql_fetch_object($res1);

													$acces_a_cet_eleve="y";
													if (($_SESSION['statut']=="cpe")&&(getSettingValue('GepiAccesAbsTouteClasseCpe')!='yes')) {
														// Le CPE a-t-il bien cet �l�ve:
														$sql="SELECT 1=1 FROM j_eleves_cpe jec WHERE jec.e_login='$lig1->login' AND jec.cpe_login='".$_SESSION['login']."'";
														//echo "<!--\n$sql\n-->\n";
														$test=mysql_query($sql);
	
														if((mysql_num_rows($test)==0)) {
															$acces_a_cet_eleve="n";
														}
													}
	
													//if((mysql_num_rows($test)>0)||($_SESSION['statut']=='secours')) {
													if($acces_a_cet_eleve=="y") {
														$affiche_ligne="y";

														//$lig1=mysql_fetch_object($res1);
														//$ligne_tableau.="<td>$lig1->elenoet\n";

														//$ligne_tableau.="<input type='hidden' name='log_eleve[$i]' value='$lig1->login' />\n";
														//$ligne_tableau.="</td>\n";
														$ligne_tableau.="<td>";
														//$ligne_tableau.="<input type='hidden' name='log_eleve[$i]' value='$lig1->login' />\n";
														$ligne_tableau.="$lig1->nom</td>\n";
														$ligne_tableau.="<td>$lig1->prenom</td>\n";

														$ligne_tableau.="<td>\n";
														$sql="SELECT c.classe FROM j_eleves_classes jec, classes c
																WHERE jec.login='$lig1->login' AND
																	jec.id_classe=c.id AND periode='$num_periode'";
														$res2=mysql_query($sql);
														if(mysql_num_rows($res2)==0){
															$ligne_tableau.="<span style='color:red;'>NA</span>\n";
														}
														else {
															$cpt=0;
															while($lig2=mysql_fetch_object($res2)){
																if($cpt>0){
																	$ligne_tableau.=", ";
																}
																$ligne_tableau.=$lig2->classe;
															}
														}
														$ligne_tableau.="</td>\n";
													}


													if("$affiche_ligne"=="y"){
														$temoin_erreur="n";
														$sql_tmp_abs="INSERT INTO $temp_table_abs SET cpe_login='".$_SESSION['login']."', login='".$lig1->login."'";

														echo $ligne_tableau;
														echo "<td>\n";
														if(isset($eleves[$i]['nbAbs'])){
															echo $eleves[$i]['nbAbs'];
															//echo "<input type='hidden' name='nbabs_eleve[$i]' value='".$eleves[$i]['nbAbs']."' />\n";
															$sql_tmp_abs.=", nbabs='".$eleves[$i]['nbAbs']."'";
														}
														else{
															//echo "&nbsp;";
															echo "<span style='color:red;'>ERR</span>\n";
															//echo "<input type='hidden' name='nbabs_eleve[$i]' value='0' />\n";
															$nb_err++;
															$temoin_erreur="y";
														}
														echo "</td>\n";

														echo "<td>\n";
														if(isset($eleves[$i]['nbNonJustif'])){
															echo $eleves[$i]['nbNonJustif'];
															//echo "<input type='hidden' name='nbnj_eleve[$i]' value='".$eleves[$i]['nbNonJustif']."' />\n";
															$sql_tmp_abs.=", nbnj='".$eleves[$i]['nbNonJustif']."'";
														}
														else{
															//echo "&nbsp;";
															echo "<span style='color:red;'>ERR</span>\n";
															//echo "<input type='hidden' name='nbnj_eleve[$i]' value='0' />\n";
															$nb_err++;
															$temoin_erreur="y";
														}
														echo "</td>\n";

														echo "<td>\n";
														if(isset($eleves[$i]['nbRet'])){
															echo $eleves[$i]['nbRet'];
															//echo " -&gt; <input type='text' size='4' name='nbret_eleve[$i]' value='".$eleves[$i]['nbRet']."' />\n";
															//echo "<input type='hidden' size='4' name='nbret_eleve[$i]' value='".$eleves[$i]['nbRet']."' />\n";
															$sql_tmp_abs.=", nbret='".$eleves[$i]['nbRet']."'";
														}
														else{
															//echo "&nbsp;";
															echo "<span style='color:red;'>ERR</span>\n";
															//echo "<input type='hidden' name='nbret_eleve[$i]' value='0' />\n";
															$nb_err++;
															$temoin_erreur="y";
														}

														if($temoin_erreur!="y") {
															// Les absences de l'�l�ve ont pu �tre import�es par un autre cpe sans que l'op�ration soit men�e � bout.
															$sql="DELETE FROM $temp_table_abs WHERE login='$lig1->login';";
															$menage=mysql_query($sql);

															//echo "$sql_tmp_abs<br />";
															$insert_tmp=mysql_query($sql_tmp_abs);
														}

														echo "</td>\n";

														echo "</tr>\n";
													}

												}
											}
										}
									}
									echo "</table>\n";
									echo add_token_field();
									echo "<input type='hidden' name='nb_eleves' value='$i' />\n";
									echo "<input type='hidden' name='is_posted' value='y' />\n";
									echo "<input type='hidden' name='etape' value='2' />\n";

									//echo "<input type='hidden' name='suffixe_table_temp' value='$suffixe_table_temp' />\n";

									# A RENSEIGNER D'APRES L'EXTRACTION:
									echo "<input type='hidden' name='num_periode' value='$num_periode' />\n";

									echo "<p align='center'><input type='submit' value='Importer' /></p>\n";
									echo "</form>\n";

									echo "<p><i>NOTE:</i> If lines are marked of one <span style='color:red;'>ERR</span>, the values will not be imported for student pupil.</p>\n";

								}
								else{
									// PB $fp
								}
							}
							if($etape==2) {
								/*
								$log_eleve=isset($_POST['log_eleve']) ? $_POST['log_eleve'] : NULL;
								$nbabs_eleve=isset($_POST['nbabs_eleve']) ? $_POST['nbabs_eleve'] : NULL;
								$nbnj_eleve=isset($_POST['nbnj_eleve']) ? $_POST['nbnj_eleve'] : NULL;
								$nbret_eleve=isset($_POST['nbret_eleve']) ? $_POST['nbret_eleve'] : NULL;
								*/
								/*
								$suffixe_table_temp=isset($_POST['suffixe_table_temp']) ? $_POST['suffixe_table_temp'] : "";
								if((!preg_match('/^[0-9]*$/', $suffixe_table_temp))||($suffixe_table_temp=='')) {
									echo "<p style='color:red'>ERREUR sur le nom de la table temporaire.</p>\n";
									require("../lib/footer.inc.php");
									die();
								}
								*/

								//$sql="SELECT * FROM temp_abs_$suffixe_table_temp;";
								$sql="SELECT * FROM $temp_table_abs WHERE cpe_login='".$_SESSION['login']."';";
								//echo "$sql<br />";
								$res_tmp=mysql_query($sql);
								if(mysql_num_rows($res_tmp)==0) {
									echo "<p style='color:red'>No data to import.</p>\n";
									require("../lib/footer.inc.php");
									die();
								}

								$i=0;
								while($lig_tmp=mysql_fetch_object($res_tmp)) {
									$log_eleve[$i]=$lig_tmp->login;
									$nbabs_eleve[$i]=$lig_tmp->nbabs;
									$nbnj_eleve[$i]=$lig_tmp->nbnj;
									$nbret_eleve[$i]=$lig_tmp->nbret;

									$i++;
								}

								$num_periode=isset($_POST['num_periode']) ? $_POST['num_periode'] : NULL;

								$nb_eleves=isset($_POST['nb_eleves']) ? $_POST['nb_eleves'] : NULL;


								// On initialise � z�ro les absences, retards,... pour tous les �l�ves des classes import�es et les valeurs extraites du XML de Sconet �craseront ces initialisations.
								// Si on ne fait pas cette initialisation, les �l�ves qui n'ont aucune absence ni retard apparaissent avec un '?' au lieu d'un Z�ro/Aucune.
								for($i=0;$i<count($id_classe);$i++){

									// Ajout d'un test sur le caract�re clos de la p�riode pour la classe
									if($_SESSION['statut']=='secours'){
										$sql="SELECT 1=1 FROM periodes WHERE id_classe='$id_classe[$i]' AND num_periode='$num_periode' AND (verouiller='N' OR verouiller='P');";
									}
									else {
										$sql="SELECT 1=1 FROM periodes WHERE id_classe='$id_classe[$i]' AND num_periode='$num_periode' AND verouiller='N';";
									}
									$test_ver=mysql_query($sql);

									if(mysql_num_rows($test_ver)>0) {
										if((($_SESSION['statut']=="cpe")&&(getSettingValue('GepiAccesAbsTouteClasseCpe')=='yes'))||($_SESSION['statut']=='secours')) {
											$sql="SELECT login FROM j_eleves_classes WHERE id_classe='$id_classe[$i]' AND periode='$num_periode';";
										}
										else{
											// Pour ne r�initialiser que les absences des �l�ves associ�s au CPE:
											$sql="SELECT jecl.login FROM j_eleves_classes jecl, j_eleves_cpe jec WHERE jecl.id_classe='$id_classe[$i]' AND jecl.periode='$num_periode' AND jecl.login=jec.e_login AND jec.cpe_login='".$_SESSION['login']."';";
										}
	
										$res_ele=mysql_query($sql);
										if(mysql_num_rows($res_ele)>0){
											while($lig_tmp=mysql_fetch_object($res_ele)){
												$sql="DELETE FROM absences WHERE login='$lig_tmp->login' AND periode='$num_periode';";
												$res_menage=mysql_query($sql);
	
												$sql="INSERT INTO absences SET login='$lig_tmp->login', periode='$num_periode', nb_absences='0', non_justifie='0', nb_retards='0';";
												$res_ini=mysql_query($sql);
											}
										}
									}
								}


								$nb_err=0;
								$nb_ok=0;
								echo "<p>Importation&nbsp;: ";
								//for($i=0;$i<count($log_eleve);$i++){
								for($i=0;$i<$nb_eleves;$i++){
									if((isset($log_eleve[$i]))&&
										(isset($nbabs_eleve[$i]))&&
										(isset($nbnj_eleve[$i]))&&
										(isset($nbret_eleve[$i]))
									) {

										if($_SESSION['statut']=='secours'){
											$test0=true;

											// Requ�te pour tester que la p�riode est bien close ou partiellement close pour cette classe
											$sql="SELECT 1=1 FROM periodes p,j_eleves_classes jec WHERE p.num_periode='$num_periode' AND (p.verouiller='N' OR p.verouiller='P') AND jec.login='$log_eleve[$i]' AND p.id_classe=jec.id_classe AND p.num_periode=jec.periode;";
										}
										else{
											$test0=true;
											if (($_SESSION['statut']=="cpe")&&(getSettingValue('GepiAccesAbsTouteClasseCpe')!='yes')) {
												// L'�l�ve est-il associ� au CPE:
												// Il faudrait vraiment une tentative frauduleuse pour que ce ne soit pas le cas...
												$sql="SELECT 1=1 FROM j_eleves_cpe jec WHERE jec.e_login='".$log_eleve[$i]."' AND jec.cpe_login='".$_SESSION['login']."';";
												$res_test0=mysql_query($sql);
												if(mysql_num_rows($res_test0)!=0){
													$test0=true;
												}
												else{
													$test0=false;
												}
											}

											// Requ�te pour tester que la p�riode est bien close pour cette classe
											$sql="SELECT 1=1 FROM periodes p,j_eleves_classes jec WHERE p.num_periode='$num_periode' AND p.verouiller='N' AND jec.login='$log_eleve[$i]' AND p.id_classe=jec.id_classe AND p.num_periode=jec.periode;";
										}
										//echo "$sql<br />";
										$test2=mysql_query($sql);

										//if($test0==true){
										if(($test0==true)&&(mysql_num_rows($test2)>0)) {
											if(($nb_ok>0)||($nb_err>0)){echo ", ";}

											$sql="SELECT 1=1 FROM absences WHERE periode='$num_periode' AND login='".$log_eleve[$i]."';";
											$test1=mysql_query($sql);
											if(mysql_num_rows($test1)==0){
												$sql="INSERT INTO absences SET periode='$num_periode',
																				login='".$log_eleve[$i]."',
																				nb_absences='".$nbabs_eleve[$i]."',
																				nb_retards='".$nbret_eleve[$i]."',
																				non_justifie='".$nbnj_eleve[$i]."';";
												$insert=mysql_query($sql);
												if($insert){
													$nb_ok++;
													echo "<span style='color:green;'>".$log_eleve[$i]."</span>";
												}
												else{
													$nb_err++;
													echo "<span style='color:red;'>".$log_eleve[$i]."</span>";
												}
											}
											else{
												$sql="UPDATE absences SET nb_absences='".$nbabs_eleve[$i]."',
																			nb_retards='".$nbret_eleve[$i]."',
																			non_justifie='".$nbnj_eleve[$i]."'
																		WHERE periode='$num_periode' AND
																				login='".$log_eleve[$i]."';";
												$update=mysql_query($sql);
												if($update){
													$nb_ok++;
													echo "<span style='color:green;'>".$log_eleve[$i]."</span>";
												}
												else{
													$nb_err++;
													echo "<span style='color:red;'>".$log_eleve[$i]."</span>";
												}
											}
										}
										else {
											if(!isset($liste_non_importes)) {
												$liste_non_importes=$log_eleve[$i];
											}
											else {
												$liste_non_importes.=", $log_eleve[$i]";
											}
										}
									}
								}
								echo "</p>\n";
								echo "<p>Importation carried out";
								if($nb_err==0){
									echo " without error.</p>\n";
								}
								elseif($nb_err==1){
									echo " with an error.</p>\n";
								}
								else{
									echo " with $nb_err errors.</p>\n";
								}
								echo "<p><br /></p>\n";

								if(isset($liste_non_importes)) {
									echo "<p>No importation was carried out for <span style='color:red'>$liste_non_importes</span>.<br />The period was perhaps closed for these pupils.</p>\n";
									echo "<p><br /></p>\n";
								}

								echo "<p>To control the typings for the class of:</p>\n";
								/*
								echo "<ul>\n";
								for($i=0;$i<count($id_classe);$i++){
									echo "<li><a href='saisie_absences.php?id_classe=$id_classe[$i]&amp;periode_num=$num_periode' target='_blank'>".get_nom_class_from_id($id_classe[$i])."</a></li>\n";
								}
								echo "</ul>\n";
								*/

								$nb_classes=count($id_classe);
								$nb_class_par_colonne=round($nb_classes/3);
								echo "<table width='100%'>\n";
								echo "<tr valign='top' align='center'>\n";

								$i = '0';

								echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
								echo "<td align='left'>\n";

								for($i=0;$i<count($id_classe);$i++){

									if(($i>0)&&(round($i/$nb_class_par_colonne)==$i/$nb_class_par_colonne)){
										echo "</td>\n";
										echo "<td align='left'>\n";
									}

									echo "<a href='saisie_absences.php?id_classe=$id_classe[$i]&amp;periode_num=$num_periode' target='_blank'>".get_nom_class_from_id($id_classe[$i])."</a><br />\n";
								}
								echo "</td>\n";
								echo "</tr>\n";
								echo "</table>\n";

								/*
								// Menage
								$sql="DELETE FROM $temp_table_abs WHERE cpe_login='".$_SESSION['login']."';";
								//echo "$sql<br />";
								$res_tmp=mysql_query($sql);
								*/
							}
						} // Fin is_posted
					//}
				}
			}
			echo "<p><br /></p>\n";

		?>
	</div>
<?php
	require("../lib/footer.inc.php");
?>
