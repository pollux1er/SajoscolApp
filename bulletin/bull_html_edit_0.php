<?php
/*
 * $Id$
 */
///// Ajoute pour le 3eme trimestre par patient
$xloginx = $_POST['tab_selection_ele_0_0'][0];
$xperiodex = $_POST['tab_periode_num'][0];
$xclassex = $_POST['tab_id_classe'][0];
$MoY  = 0; // Moyenne generale de leleve pour le trimestre
$fgd = 0;
$min_max_moyclas=1;
$total_coef = 0;
$passed = 0;
echo "<br />";
echo "<br />";
echo "<br />";
//var_dump($tab_bull['groupe'][$i]["classlist_string"]);
//echo "</pre>";
// On initialise le tableau des notes et appr�ciations :
echo "<table $class_bordure width='$largeurtableau' border='1' cellspacing='".$cellspacing."' cellpadding='".$cellpadding."' summary='Table of the notes and appreciations'>\n";
echo "<thead>\n";
echo "<tr>\n<td style=\"width: ".$col_matiere_largeur."px; vertical-align: top;\">
<span class='bulletin'><font size=\"1\">";
if ($bull_affiche_numero == 'yes'){
	// En attendant de corriger le bug sur $k
	if(isset($k)) {
		echo "Report card N� ".$k."/".$tab_bull['eff_classe'];
	}
	else {
		echo "Report card N� .../".$tab_bull['eff_classe'];
	}
}
else{
	echo "Number of students : ".$tab_bull['eff_classe']." students";
}
echo "</font></span></td>\n";

//if ($test_coef != 0 and $affiche_coef == "y"){
if(isset($tab_bull['affiche_coef'])) {
	echo "<td style=\"width: ".$col_note_largeur."px; text-align: center;\"> <span class='bulletin' style='font-weight:bold'>Coef.</span></td>\n";
}

if($tab_bull['affiche_nbdev']=="y"){
	echo "<td style=\"width: ".$col_note_largeur."px; text-align: center;\"><span class='bulletin'>Nb.dev</span></td>\n";
}

/* if($min_max_moyclas!=1) {
	// Trois colonnes s�par�es pour min/moy/max
	echo "<td style='width: ".$col_note_largeur."px; text-align: center;'><span class='bulletin'>Min</span></td>\n";
	echo "<td style='width: ".$col_note_largeur."px; text-align: center;'><span class='bulletin'>Classe</span></td>\n";
	echo "<td style='width: ".$col_note_largeur."px; text-align: center;'><span class='bulletin'>Max</span></td>\n";
}
else{
	// Min/Classe/Max en une seule colonne
	echo "<td style=\"width: ".$col_note_largeur."px; text-align: center;\"><span class='bulletin'>Classe m/C/M</span></td>\n";
} */
$cls = $tab_bull['groupe'][$i]["classlist_string"];
//var_dump($tab_bull['groupe'][0]['classes']['classes'][11]['classe']);
if(is_null($cls))
	$cls = $tab_bull['groupe'][0]['classes']['classes'][7]['classe'];
if(is_null($cls))
	$cls = $tab_bull['groupe'][0]['classes']['classes'][8]['classe'];
if(is_null($cls))
	$cls = $tab_bull['groupe'][0]['classes']['classes'][9]['classe'];
if(is_null($cls))
	$cls = $tab_bull['groupe'][0]['classes']['classes'][10]['classe'];
if(is_null($cls))
	$cls = $tab_bull['groupe'][0]['classes']['classes'][11]['classe'];
if(is_null($cls))
	$cls = $tab_bull['groupe'][0]['classes']['classes'][12]['classe'];
if(is_null($cls))
	$cls = $tab_bull['groupe'][0]['classes']['classes'][13]['classe'];
if(is_null($cls))
	$cls = $tab_bull['groupe'][0]['classes']['classes'][14]['classe'];
echo "<td style=\"width: ".$col_note_largeur."px; text-align: center;\"><span class='bulletin' style='font-weight:bold'>Marks/20</span></td>\n";

if($cls == 'F4A' or $cls == 'F5A' or $cls == 'F4B' or $cls == 'F5B' or $cls == 'LSS' or $cls == 'LSA' or $cls == 'USS' or $cls == 'USA' or is_null($cls)) {
echo "<td style=\"width: ".$col_note_largeur."px; text-align: center;\"><span class='bulletin' style='font-weight:bold'>Total/100</span></td>\n";
} else {
echo "<td style=\"width: ".$col_note_largeur."px; text-align: center;\"><span class='bulletin' style='font-weight:bold'>Total</span></td>\n";
}

if($cls == 'F4A' or $cls == 'F5A' or $cls == 'F4B' or $cls == 'F5B' or $cls == 'LSS' or $cls == 'LSA' or $cls == 'USS' or $cls == 'USA' or is_null($cls)) {
	echo "<td style=\"width: ".$col_note_largeur."px; text-align: center;\"><span class='bulletin' style='font-weight:bold'>Grade</span></td>\n";
}

if ($tab_bull['affiche_graph'] == 'y')  {
	echo "<td style=\"width: ".$col_note_largeur."px; text-align: center;\"><span class='bulletin'>Niveaux<br />ABC<font size=\"-2\">+</font>C<font size=\"-2\">-</font>DE</span></td>\n";
}

if (isset($tab_bull['affiche_rang'])){
	echo "<td style=\"width: ".$col_note_largeur."px; text-align: center;\"><span class='bulletin' style='font-weight:bold'><i>Position</i></span></td>\n";
}

// Pas d'affichage dans le cas d'un bulletin d'une p�riode "examen blanc"
if ($bull_affiche_appreciations == 'y'){
	echo "<td colspan=\"2\"><span class='bulletin' style='font-weight:bold'>".$bull_intitule_app."</span></td>\n";
}
echo "</tr>\n";

echo "</thead>\n";


//===============================================================================
// D�but de la partie AID de d�but de bulletin
// Pas d'affichage dans le cas d'un bulletin d'une p�riode "examen blanc"
if ($bull_affiche_aid == 'y') {
	// On attaque maintenant l'affichage des appr�ciations des Activit�s Interdisciplinaires devant appara�tre en t�te des bulletins :

	if(isset($tab_bull['eleve'][$i]['aid_b'])) {
		for($z=0;$z<count($tab_bull['eleve'][$i]['aid_b']);$z++) {
			echo "<tr>\n";

			echo "<td><span class='bulletin'>".htmlentities($tab_bull['eleve'][$i]['aid_b'][$z]['nom_complet']);
			echo "<br />";
			$cpt=0;
			foreach($tab_bull['eleve'][$i]['aid_b'][$z]['aid_prof_resp_login'] as $current_aid_prof_login) {
				if($cpt>0) {echo ", ";}
				echo "<i>".affiche_utilisateur($current_aid_prof_login,$tab_bull['id_classe'])."</i>";
				$cpt++;
			}
			echo "</span></td>\n";

			//if ($test_coef != 0 and $affiche_coef == "y"){
			if(isset($tab_bull['affiche_coef'])) {
				echo "<td>-</td>\n";
			}

			if($tab_bull['affiche_nbdev']=="y"){
				echo "<td>-</td>\n";
			}

			// Moyenne min/classe/max de la classe
			/* if($min_max_moyclas!=1) {
				// Trois colonnes s�par�es pour min/moy/max
				echo "<td><span class='bulletin'>".$tab_bull['eleve'][$i]['aid_b'][$z]['aid_note_min']."</span></td>\n";
				echo "<td><span class='bulletin'>".$tab_bull['eleve'][$i]['aid_b'][$z]['aid_note_moyenne']."</span></td>\n";
				echo "<td><span class='bulletin'>".$tab_bull['eleve'][$i]['aid_b'][$z]['aid_note_max']."</span></td>\n";
			}
			else{
				// Min/Classe/Max en une seule colonne
				echo "<td><span class='bullminclasmax'>".$tab_bull['eleve'][$i]['aid_b'][$z]['aid_note_min']."</span><br />\n";
				echo "<span class='bullminclasmax'>".$tab_bull['eleve'][$i]['aid_b'][$z]['aid_note_moyenne']."</span><br />\n";
				echo "<span class='bullminclasmax'>".$tab_bull['eleve'][$i]['aid_b'][$z]['aid_note_max']."</span></td>\n";
			} */

			// Moyenne de l'�l�ve
			echo "<td style='font-weight:bold;'><span class='bulletin'>";
			if($tab_bull['eleve'][$i]['aid_b'][$z]['aid_statut']=="") {
				echo $tab_bull['eleve'][$i]['aid_b'][$z]['aid_note'];
			}
			elseif($tab_bull['eleve'][$i]['aid_b'][$z]['aid_statut']=="other") {
				echo "-";
			}
			else {
				echo $tab_bull['eleve'][$i]['aid_b'][$z]['aid_statut'];
			}
			echo "</span></td>\n";
			// Moyenne total


			if ($tab_bull['affiche_graph'] == 'y') {
				echo "<td>";
				if((isset($tab_bull['eleve'][$i]['aid_b'][$z]['place_eleve']))&&($tab_bull['eleve'][$i]['aid_b'][$z]['place_eleve']!="")) {
					echo "<img height='40' width='40' src='../visualisation/draw_artichow4.php?place_eleve=".$tab_bull['eleve'][$i]['aid_b'][$z]['place_eleve'].
						"&amp;temp1=".$tab_bull['eleve'][$i]['aid_b'][$z]['quartile1_classe'].
						"&amp;temp2=".$tab_bull['eleve'][$i]['aid_b'][$z]['quartile2_classe'].
						"&amp;temp3=".$tab_bull['eleve'][$i]['aid_b'][$z]['quartile3_classe'].
						"&amp;temp4=".$tab_bull['eleve'][$i]['aid_b'][$z]['quartile4_classe'].
						"&amp;temp5=".$tab_bull['eleve'][$i]['aid_b'][$z]['quartile5_classe'].
						"&amp;temp6=".$tab_bull['eleve'][$i]['aid_b'][$z]['quartile6_classe'].
						"&amp;nb_data=7' alt='Quartiles' />\n";
				}
				else {
					echo "-";
				}
				echo "</td>\n";
			}

			if(isset($tab_bull['affiche_rang'])) {
				echo "<td>-</td>\n";
			}

			// Pas d'affichage dans le cas d'un bulletin d'une p�riode "examen blanc"
			if ($bull_affiche_appreciations == 'y'){
				echo "<td style='text-align: left;' colspan='2'><span class='bulletin'>";
				//echo "<b>".$tab_bull['eleve'][$i]['aid_b'][$z]['aid_nom']."</b><br />";
				if(($tab_bull['eleve'][$i]['aid_b'][$z]['message']!='')||(($tab_bull['eleve'][$i]['aid_b'][$z]['aid_nom']!='')&&($tab_bull['eleve'][$i]['aid_b'][$z]['display_nom']=='y'))) {
					echo "<b>";
					if($tab_bull['eleve'][$i]['aid_b'][$z]['message']!='') {
						echo $tab_bull['eleve'][$i]['aid_b'][$z]['message']." ";
					}
					echo $tab_bull['eleve'][$i]['aid_b'][$z]['aid_nom']."</b><br />";
				}
				echo texte_html_ou_pas($tab_bull['eleve'][$i]['aid_b'][$z]['aid_appreciation']);
				echo "</span></td>\n";
			}

			echo "</tr>\n";
		}
	}
}
// Fin de la partie AID de d�but de bulletin
//===============================================================================

//===============================================================================
// Partie mati�res/groupes
$categorie_precedente="";
for($j=0;$j<count($tab_bull['groupe']);$j++) {
	// Si l'�l�ve suit l'option, sa note est affect�e (�ventuellement vide)
	if(isset($tab_bull['note'][$j][$i])) {

		if($tab_bull['affiche_categories']) {
			if($categorie_precedente!=$tab_bull['cat_id'][$j]) {
				if($bull_categ_bgcolor!=''){
					echo "<tr bgcolor='".$bull_categ_bgcolor."'>\n";
				}
				else{
					echo "<tr>\n";
				}

				if($tab_bull['affiche_moyenne'][$j]==1) {
					$colspan=1;
					if(isset($tab_bull['affiche_coef'])) {$colspan++;}
					if($tab_bull['affiche_nbdev']=='y') {$colspan++;}

					echo "<td colspan='$colspan'><p style='padding: 0; margin:0; font-size: ".$bull_categ_font_size."px;'>".$tab_bull['nom_cat_complet'][$j]."</p></td>\n";

					/* if($min_max_moyclas!=1) {
						// Min
						//echo "<td style='padding: 0; margin: 0; font-size: ".$bull_categ_font_size."px; text-align: center;'>-</td>\n";
						echo "<td style='padding: 0; margin: 0; font-size: ".$bull_categ_font_size."px; text-align: center;'>".nf($tab_bull['moy_cat_min'][$i][$tab_bull['cat_id'][$j]])."</td>\n";
						// Moyenne cat�gorie classe
						echo "<td style='padding: 0; margin: 0; font-size: ".$bull_categ_font_size."px; text-align: center;'>".nf($tab_bull['moy_cat_classe'][$i][$tab_bull['cat_id'][$j]])."</td>\n";
						// Max
						//echo "<td style='padding: 0; margin: 0; font-size: ".$bull_categ_font_size."px; text-align: center;'>-</td>\n";
						echo "<td style='padding: 0; margin: 0; font-size: ".$bull_categ_font_size."px; text-align: center;'>".nf($tab_bull['moy_cat_max'][$i][$tab_bull['cat_id'][$j]])."</td>\n";
					}
					else {
						// Moyenne cat�gorie classe
						echo "<td style='padding: 0; margin: 0; font-size: ".$bull_categ_font_size."px; text-align: center;'>".nf($tab_bull['moy_cat_classe'][$i][$tab_bull['cat_id'][$j]])."</td>\n";
					} */

					// Moyenne cat�gorie �l�ve
					echo "<td style='padding: 0; margin: 0; font-size: ".$bull_categ_font_size."px; text-align: center;'>".nf($tab_bull['moy_cat_eleve'][$i][$tab_bull['cat_id'][$j]])."</td>\n";

					$colspan=2;
					if($tab_bull['affiche_graph']=='y') {$colspan++;}
					if(isset($tab_bull['affiche_rang'])) {$colspan++;}
					if ($bull_affiche_appreciations != 'y') {$colspan-=2;}
					if($colspan>0) {
						// Appr�ciation
						echo "<td style='padding: 0; margin: 0; font-size: ".$bull_categ_font_size."px; text-align: left;' colspan='$colspan'>-</td>\n";
					}
				}
				else {
					$colspan=7;
					if(isset($tab_bull['affiche_coef'])) {$colspan++;}
					if($tab_bull['affiche_nbdev']=='y') {$colspan++;}
					//if($min_max_moyclas==1){$colspan-=2;}
					if($tab_bull['affiche_graph']=='y') {$colspan++;}
					if(isset($tab_bull['affiche_rang'])) {$colspan++;}
					if ($bull_affiche_appreciations != 'y') {$colspan-=2;}

					echo "<td style='padding: 0; margin: 0; font-size: ".$bull_categ_font_size."px; text-align: center;' colspan='$colspan'>".$tab_bull['nom_cat_complet'][$j]."</td>\n";
				}

				echo "</tr>\n";

				$categorie_precedente=$tab_bull['cat_id'][$j];
			}
		}

		if(isset($tab_bull['groupe'][$j][$i]['cn_note'])) {
			//$rowspan=count($tab_bull['groupe'][$j][$i]['cn_note']);
			$chaine_rowspan=" rowspan='".count($tab_bull['groupe'][$j][$i]['cn_note'])."'";
		}
		else {
			$chaine_rowspan="";
		}

		echo "<tr>\n";
		//echo "<td$chaine_rowspan style='text-align: left;'><span class='bulletin'>".htmlentities($tab_bull['groupe'][$j]['matiere']['nom_complet'])."</span>";
		echo "<td$chaine_rowspan style='text-align: left;'>";
		if(getSettingValue('bul_rel_nom_matieres')=='nom_groupe') {
			$info_nom_matiere=$tab_bull['groupe'][$j]['name'];
		}
		elseif(getSettingValue('bul_rel_nom_matieres')=='description_groupe') {
			$info_nom_matiere=$tab_bull['groupe'][$j]['description'];
		}
		else {
			// Pour parer au bug sur la suppression de mati�re alors que des groupes sont conserv�s:
			if(isset($tab_bull['groupe'][$j]['matiere']['nom_complet'])) {
				$info_nom_matiere=$tab_bull['groupe'][$j]['matiere']['nom_complet'];
			}
			else {
				$info_nom_matiere=$tab_bull['groupe'][$j]['name']." (".$tab_bull['groupe'][$j]['id'].")";
			}
		}
		echo "<span class='bulletin'>".htmlentities($info_nom_matiere)."</span>";

		echo "<br />\n";
		echo "<span class='bulletin'>";
		$cpt=0;
		foreach($tab_bull['groupe'][$j]["profs"]["list"] as $current_prof_login) {
			if($cpt>0) {echo ", ";}
			echo "<i>".affiche_utilisateur($current_prof_login,$tab_bull['id_classe'])."</i>";
			$cpt++;
		}
		echo "</span></td>\n";

		if(isset($tab_bull['affiche_coef'])) {
			echo "<td$chaine_rowspan style='text-align: center;'><span class='bulletin'>".$tab_bull['coef_eleve'][$i][$j]."</span></td>\n";
			$total_coef += $tab_bull['coef_eleve'][$i][$j];
		}

		if($tab_bull['affiche_nbdev']=="y"){
			echo "<td$chaine_rowspan style='text-align: center;'><span class='bulletin'>".$tab_bull['nbct'][$j][$i]."/".$tab_bull['groupe'][$j]['nbct']."</span></td>\n";
		}

		/* if($min_max_moyclas!=1) {
			echo "<td$chaine_rowspan style='text-align: center;'><span class='bulletin'>".nf($tab_bull['moy_min_classe_grp'][$j])."</span></td>\n";
			echo "<td$chaine_rowspan style='text-align: center;'><span class='bulletin'>".nf($tab_bull['moy_classe_grp'][$j])."</span></td>\n";
			echo "<td$chaine_rowspan style='text-align: center;'><span class='bulletin'>".nf($tab_bull['moy_max_classe_grp'][$j])."</span></td>\n";
		}
		else {
			echo "<td$chaine_rowspan style='text-align: center;'><span class='bullminclasmax'>".nf($tab_bull['moy_min_classe_grp'][$j])."</span><br />\n";
			echo "<span class='bullminclasmax'>".nf($tab_bull['moy_classe_grp'][$j])."</span><br />\n";
			echo "<span class='bullminclasmax'>".nf($tab_bull['moy_max_classe_grp'][$j])."</span></td>\n";
		} */

		echo "<td$chaine_rowspan style='font-weight:bold; text-align: center;'><span class='bulletin'>";
		if($tab_bull['statut'][$j][$i]=="") {
			$fgd = $tab_bull['note'][$j][$i];
			if($fgd < 10) {
				echo "<span style='color:red'>" . nf($fgd) . "</span>";
			} else {
				echo nf($tab_bull['note'][$j][$i]);
			}
		}
		else {
			echo $tab_bull['statut'][$j][$i];
		}
		echo "</span></td>\n";
		////////////////////////
		echo "<td$chaine_rowspan style='font-weight:bold; text-align: center;'><span class='bulletin'>";
		if($tab_bull['statut'][$j][$i]=="") {
			echo nf($tab_bull['note'][$j][$i]*$tab_bull['coef_eleve'][$i][$j]);
		}
		else {
			echo $tab_bull['statut'][$j][$i];
		}
		echo "</span></td>\n";
///////////////////// GRADE
	if($cls == 'F4A' or $cls == 'F5A' or $cls == 'F4B' or $cls == 'F5B' or $cls == 'LSS' or $cls == 'LSA' or $cls == 'USS' or $cls == 'USA'  or is_null($cls)) {
		echo "<td$chaine_rowspan style='font-weight:bold; text-align: center;'><span class='bulletin'>";
		if($tab_bull['statut'][$j][$i]=="") {
			$tot = nf($tab_bull['note'][$j][$i]*$tab_bull['coef_eleve'][$i][$j]);
			if($tot >= 50) $passed++; 
			echo getGrade($tot, $cls);
		}
		
		//	echo $tab_bull['statut'][$j][$i];
		
		echo "</span></td>\n";
	}
		if ($tab_bull['affiche_graph'] == 'y') {
			echo "<td$chaine_rowspan style='text-align: center;'>";
			if((isset($tab_bull['place_eleve'][$j][$i]))&&($tab_bull['place_eleve'][$j][$i]!="")) {
				//echo $place_eleve_classe[$i]." ";
				echo "<img height='40' width='40' src='../visualisation/draw_artichow4.php?place_eleve=".$tab_bull['place_eleve'][$j][$i].
					"&amp;temp1=".$tab_bull['quartile1_grp'][$j].
					"&amp;temp2=".$tab_bull['quartile2_grp'][$j].
					"&amp;temp3=".$tab_bull['quartile3_grp'][$j].
					"&amp;temp4=".$tab_bull['quartile4_grp'][$j].
					"&amp;temp5=".$tab_bull['quartile5_grp'][$j].
					"&amp;temp6=".$tab_bull['quartile6_grp'][$j].
					"&amp;nb_data=7' alt='Quartiles' />\n";
			}
			else {
				echo "-";
			}
			echo "</td>\n";
		}

		if(isset($tab_bull['affiche_rang'])) {
			echo "<td$chaine_rowspan style='text-align: center;'><span class='bulletin'>";
			//if(isset($tab_bull['rang'][$i][$j])) {
				//echo $tab_bull['rang'][$i][$j]."/".$tab_bull['groupe'][$j]['effectif'];
			if(isset($tab_bull['rang'][$j][$i])) {
				echo $tab_bull['rang'][$j][$i]."/".$tab_bull['groupe'][$j]['effectif_avec_note'];
			}
			else {
				echo "-";
			}
			echo "</span></td>\n";
		}

		if ($bull_affiche_appreciations == 'y') {
			if(!isset($tab_bull['groupe'][$j][$i]['cn_note'])) {
				// Appr�ciation
				echo "<td colspan='2' style='text-align: left;'><span class='bulletin'>";
				//echo texte_html_ou_pas($tab_bull['app'][$j][$i]);
				if($tab_bull['note'][$j][$i] <= 3) {
					echo "Very poor";
				} elseif($tab_bull['note'][$j][$i] > 3 && $tab_bull['note'][$j][$i] <= 5) {
					echo "Poor";
				} elseif($tab_bull['note'][$j][$i] > 5 && $tab_bull['note'][$j][$i] <= 8) {
					echo "Weak";
				} elseif($tab_bull['note'][$j][$i] > 8 && $tab_bull['note'][$j][$i] <= 9.9) {
					echo "Below Average";
				} elseif($tab_bull['note'][$j][$i] >= 10 && $tab_bull['note'][$j][$i] < 10.9) {
					echo "Average";
				} elseif($tab_bull['note'][$j][$i] >= 11 && $tab_bull['note'][$j][$i] < 14) {
					echo "Fair";
				} elseif($tab_bull['note'][$j][$i] >= 14 && $tab_bull['note'][$j][$i] <= 17) {
					echo "Good";
				} elseif($tab_bull['note'][$j][$i] > 17 && $tab_bull['note'][$j][$i] <= 20) {
					echo "Excellent";
				}
					
				
				echo "</span></td>\n";
			}
			else {
				$n = 0;
				// Premi�re boite
				echo "<td style='text-align: left; height: ".$col_hauteur."px; width: ".$col_boite_largeur."px;'><span class='bulletin'>";
				echo $tab_bull['groupe'][$j][$i]['cn_nom'][$n].":".nf($tab_bull['groupe'][$j][$i]['cn_note'][$n]);
				echo "</span></td>\n";
				$n++;

				// Appr�ciation
				echo "<td$chaine_rowspan style='text-align: center;'><span class='bulletin'>";
				echo texte_html_ou_pas($tab_bull['app'][$j][$i]);
				echo "</span></td>\n";

				// Boites suivantes
				while ($n < count($tab_bull['groupe'][$j][$i]['cn_note'])) {
					echo "</tr>\n";
					echo "<tr>\n";
					echo "<td style='height: ".$col_hauteur."px; text-align: left;'><span class='bulletin'>";
					echo $tab_bull['groupe'][$j][$i]['cn_nom'][$n].":".nf($tab_bull['groupe'][$j][$i]['cn_note'][$n]);
					echo "</span></td>\n";
					$n++;
				}
			}
		}
		echo "</tr>\n";
	}
}
// Fin de la partie Mati�res/groupes
//===============================================================================

//===============================================================================
// D�but de la partie AID de fin de bulletin
// Pas d'affichage dans le cas d'un bulletin d'une p�riode "examen blanc"
if ($bull_affiche_aid == 'y') {
	// On attaque maintenant l'affichage des appr�ciations des Activit�s Interdisciplinaires devant appara�tre en fin des bulletins :

	if(isset($tab_bull['eleve'][$i]['aid_e'])) {
		for($z=0;$z<count($tab_bull['eleve'][$i]['aid_e']);$z++) {
			echo "<tr>\n";

			echo "<td style='text-align: center;'><span class='bulletin'>".htmlentities($tab_bull['eleve'][$i]['aid_e'][$z]['nom_complet']);
			echo "<br />";
			$cpt=0;
			foreach($tab_bull['eleve'][$i]['aid_e'][$z]['aid_prof_resp_login'] as $current_aid_prof_login) {
				if($cpt>0) {echo ", ";}
				echo "<i>".affiche_utilisateur($current_aid_prof_login,$tab_bull['id_classe'])."</i>";
				$cpt++;
			}
			echo "</span></td>\n";

			//if ($test_coef != 0 and $affiche_coef == "y"){
			if(isset($tab_bull['affiche_coef'])) {
				echo "<td style='text-align: center;'>-</td>\n";
			}

			if($tab_bull['affiche_nbdev']=="y"){
				echo "<td style='text-align: center;'>-</td>\n";
			}

			// Moyenne min/classe/max de la classe
			/* if($min_max_moyclas!=1) {
				// Trois colonnes s�par�es pour min/moy/max
				echo "<td style='text-align: center;'><span class='bulletin'>".$tab_bull['eleve'][$i]['aid_e'][$z]['aid_note_min']."</span></td>\n";
				echo "<td style='text-align: center;'><span class='bulletin'>".$tab_bull['eleve'][$i]['aid_e'][$z]['aid_note_moyenne']."</span></td>\n";
				echo "<td style='text-align: center;'><span class='bulletin'>".$tab_bull['eleve'][$i]['aid_e'][$z]['aid_note_max']."</span></td>\n";
			}
			else{
				// Min/Classe/Max en une seule colonne
				echo "<td style='text-align: center;'><span class='bullminclasmax'>".$tab_bull['eleve'][$i]['aid_e'][$z]['aid_note_min']."</span><br />\n";
				echo "<span class='bullminclasmax'>".$tab_bull['eleve'][$i]['aid_e'][$z]['aid_note_moyenne']."</span><br />\n";
				echo "<span class='bullminclasmax'>".$tab_bull['eleve'][$i]['aid_e'][$z]['aid_note_max']."</span></td>\n";
			} */

			// Moyenne de l'�l�ve
			echo "<td style='text-align: center; font-weight:bold;'><span class='bulletin'>";
			if($tab_bull['eleve'][$i]['aid_e'][$z]['aid_statut']=="") {
				echo $tab_bull['eleve'][$i]['aid_e'][$z]['aid_note'];
			}
			elseif($tab_bull['eleve'][$i]['aid_e'][$z]['aid_statut']=="other") {
				echo "-";
			}
			else {
				echo $tab_bull['eleve'][$i]['aid_e'][$z]['aid_statut'];
			}
			echo "</span></td>\n";
			

			if ($tab_bull['affiche_graph'] == 'y') {
				echo "<td style='text-align: center;'>";
				if((isset($tab_bull['eleve'][$i]['aid_e'][$z]['place_eleve']))&&($tab_bull['eleve'][$i]['aid_e'][$z]['place_eleve']!="")) {
					echo "<img height='40' width='40' src='../visualisation/draw_artichow4.php?place_eleve=".$tab_bull['eleve'][$i]['aid_e'][$z]['place_eleve'].
						"&amp;temp1=".$tab_bull['eleve'][$i]['aid_e'][$z]['quartile1_classe'].
						"&amp;temp2=".$tab_bull['eleve'][$i]['aid_e'][$z]['quartile2_classe'].
						"&amp;temp3=".$tab_bull['eleve'][$i]['aid_e'][$z]['quartile3_classe'].
						"&amp;temp4=".$tab_bull['eleve'][$i]['aid_e'][$z]['quartile4_classe'].
						"&amp;temp5=".$tab_bull['eleve'][$i]['aid_e'][$z]['quartile5_classe'].
						"&amp;temp6=".$tab_bull['eleve'][$i]['aid_e'][$z]['quartile6_classe'].
						"&amp;nb_data=7' alt='Quartiles' />\n";
				}
				else {
					echo "-";
				}
				echo "</td>\n";
			}

			if(isset($tab_bull['affiche_rang'])) {
				echo "<td style='text-align: center;'>-</td>\n";
			}

			// Pas d'affichage dans le cas d'un bulletin d'une p�riode "examen blanc"
			if ($bull_affiche_appreciations == 'y'){
				echo "<td style='text-align: left;' colspan='2'><span class='bulletin'>";
				//echo "<b>".$tab_bull['eleve'][$i]['aid_e'][$z]['aid_nom']."</b><br />";
				if(($tab_bull['eleve'][$i]['aid_e'][$z]['message']!='')||(($tab_bull['eleve'][$i]['aid_e'][$z]['aid_nom']!='')&&($tab_bull['eleve'][$i]['aid_e'][$z]['display_nom']=='y'))) {
					echo "<b>";
					if($tab_bull['eleve'][$i]['aid_e'][$z]['message']!='') {
						echo $tab_bull['eleve'][$i]['aid_e'][$z]['message']." ";
					}
					echo $tab_bull['eleve'][$i]['aid_e'][$z]['aid_nom']."</b><br />";
				}
				echo texte_html_ou_pas($tab_bull['eleve'][$i]['aid_e'][$z]['aid_appreciation']);
				echo "</span></td>\n";
			}

			echo "</tr>\n";
		}
	}
}
// Fin de la partie AID de fin de bulletin
//===============================================================================

//===============================================================================
// D�but de la partie moyenne g�n�rale
if(isset($tab_bull['display_moy_gen'])) {
	// Affichage des moyennes g�n�rales
//	if ($tab_bull['test_coef']!=0) {

		$total_coeff_eleve=0;
		for($j=0;$j<count($tab_bull['groupe']);$j++) {
			// Si l'�l�ve suit l'option
			if(isset($tab_bull['coef_eleve'][$i][$j])) {
				$total_coeff_eleve+=$tab_bull['coef_eleve'][$i][$j];
			}
		}

		if ($total_coeff_eleve) {
			echo "<tr>\n";
			if($xperiodex == 3)
				echo "<td style='text-align: left;'><span class='bulletin'><b>Terminal Average</b></span></td>\n";
			else
				echo "<td style='text-align: left;'><span class='bulletin'><b>General Average</b></span></td>\n";

			// Coef
			if(isset($tab_bull['affiche_coef'])) {
				echo "<td style='text-align: center;'><span class='bulletin'>".$total_coef."</span></td>\n";
			}

			// Nb dev
			if($tab_bull['affiche_nbdev']=='y') {
				echo "<td style='text-align: center;'><span class='bulletin'>-</span></td>\n";
			}

			/* if($min_max_moyclas!=1) {
				echo "<td style='text-align: center;'><span class='bulletin'>".nf($tab_bull['moy_min_classe'])."</span></td>\n";
				//$tab_bull['moy_gen_classe'][$i]
				echo "<td style='text-align: center;'><span class='bulletin'><b>".nf($tab_bull['moy_generale_classe'])."</b></span></td>\n";
				echo "<td style='text-align: center;'><span class='bulletin'>".nf($tab_bull['moy_max_classe'])."</span></td>\n";
			}
			else {
				echo "<td style='text-align: center;'><span class='bullminclasmax'>".nf($tab_bull['moy_min_classe'])."</span><br />\n";
				//$tab_bull['moy_gen_classe'][$i]
				echo "<span class='bullminclasmax'><b>".nf($tab_bull['moy_generale_classe'])."</b></span><br />\n";
				echo "<span class='bullminclasmax'>".nf($tab_bull['moy_max_classe'])."</span></td>\n";
			} */

			echo "<td style='text-align: center; font-weight:bold;'><span class='bulletin'>".nf($tab_bull['moy_gen_eleve'][$i])."</span></td>\n";
			$MoY = $tab_bull['moy_gen_eleve'][$i];
			$moygenagarder = $MoY;
			// Total
			echo "<td style='text-align: center; font-weight:bold;'><span class='bulletin'>".nf($tab_bull['moy_gen_eleve'][$i]*$total_coef)."</span></td>\n";

			if ($tab_bull['affiche_graph']=='y') {
				echo "<td style='text-align: center;'>";

				if((isset($tab_bull['place_eleve_classe'][$i]))&&($tab_bull['place_eleve_classe'][$i]!="")) {
					//echo $place_eleve_classe[$i]." ";
					echo "<img height='40' width='40' src='../visualisation/draw_artichow4.php?place_eleve=".$tab_bull['place_eleve_classe'][$i].
						"&amp;temp1=".$tab_bull['quartile1_classe_gen'].
						"&amp;temp2=".$tab_bull['quartile2_classe_gen'].
						"&amp;temp3=".$tab_bull['quartile3_classe_gen'].
						"&amp;temp4=".$tab_bull['quartile4_classe_gen'].
						"&amp;temp5=".$tab_bull['quartile5_classe_gen'].
						"&amp;temp6=".$tab_bull['quartile6_classe_gen'].
						"&amp;nb_data=7' alt='Quartiles' />\n";
				}
				else {
					echo "-";
				}

				echo "</td>\n";
			}
			if($cls == 'F4A' or $cls == 'F5A' or $cls == 'F4B' or $cls == 'F5B' or $cls == 'LSS' or $cls == 'LSA' or $cls == 'USS' or $cls == 'USA'  or is_null($cls)) {
			echo "<td style='text-align: left;'><span class='bulletin'>\n";
			//echo $tab_bull['avis'][$i];
			echo "-";
			echo "</span></td>\n";
}
			// Rang
			if(isset($tab_bull['affiche_rang'])) {
				echo "<td style='text-align: center;'><span class='bulletin'>";
				if(isset($tab_bull['rang_classe'][$i])) {
					$xrangx = $tab_bull['rang_classe'][$i]; // Ajouter par patient, prendre le rang pour l'afficher plus bas
					echo $tab_bull['rang_classe'][$i]."/".$tab_bull['eff_classe'];
				}
				else {
					echo "-";
				}
				echo "</span></td>\n";
			}

			
			echo "<td style='text-align: left;' colspan='2'><span class='bulletin'>\n";
			//echo $tab_bull['avis'][$i];
			echo "-";
			echo "</span></td>\n";
			echo "</tr>\n";
		}
	//}
}
// Fin de la partie moyenne g�n�rale
//===============================================================================


//===============================================================================
if($affiche_deux_moy_gen==1) {
	// D�but de la partie moyenne g�n�rale avec coef 1
	if($tab_bull['display_moy_gen']=='y') {
		// Affichage des moyennes g�n�rales
		if ($tab_bull['test_coef']!=0) {
	
			$total_coeff_eleve=0;
			for($j=0;$j<count($tab_bull['groupe']);$j++) {
				// Si l'�l�ve suit l'option
				if(isset($tab_bull['coef_eleve'][$i][$j])) {
					$total_coeff_eleve+=$tab_bull['coef_eleve'][$i][$j];
				}
			}
	
			if ($total_coeff_eleve) {
				echo "<tr>\n";
				echo "<td style='text-align: left;'><span class='bulletin'><b>Moy.g�n.non coef</b></span></td>\n";
	
				// Coef
				if(isset($tab_bull['affiche_coef'])) {
					echo "<td style='text-align: center;'><span class='bulletin'>-</span></td>\n";
				}
	
				// Nb dev
				if($tab_bull['affiche_nbdev']=='y') {
					echo "<td style='text-align: center;'><span class='bulletin'>-</span></td>\n";
				}
	
				/* if($min_max_moyclas!=1) {
					echo "<td style='text-align: center;'><span class='bulletin'>".nf($tab_bull['moy_min_classe_noncoef'])."</span></td>\n";
					//$tab_bull['moy_gen_classe'][$i]
					echo "<td style='text-align: center;'><span class='bulletin'><b>".nf($tab_bull['moy_generale_classe_noncoef'])."</b></span></td>\n";
					echo "<td style='text-align: center;'><span class='bulletin'>".nf($tab_bull['moy_max_classe_noncoef'])."</span></td>\n";
				}
				else {
					echo "<td style='text-align: center;'><span class='bullminclasmax'>".nf($tab_bull['moy_min_classe_noncoef'])."</span><br />\n";
					//$tab_bull['moy_gen_classe'][$i]
					echo "<span class='bullminclasmax'><b>".nf($tab_bull['moy_generale_classe_noncoef'])."</b></span><br />\n";
					echo "<span class='bullminclasmax'>".nf($tab_bull['moy_max_classe_noncoef'])."</span></td>\n";
				} */
				
				echo "<td style='text-align: center; font-weight:bold;'><span class='bulletin'>".nf($tab_bull['moy_gen_eleve_noncoef'][$i])."</span></td>\n";
	
				if ($tab_bull['affiche_graph']=='y') {
					echo "<td style='text-align: center;'>";
					/*
					if((isset($tab_bull['place_eleve_classe'][$i]))&&($tab_bull['place_eleve_classe'][$i]!="")) {
						//echo $place_eleve_classe[$i]." ";
						echo "<img height='40' width='40' src='../visualisation/draw_artichow4.php?place_eleve=".$tab_bull['place_eleve_classe'][$i].
							"&amp;temp1=".$tab_bull['quartile1_classe_gen'].
							"&amp;temp2=".$tab_bull['quartile2_classe_gen'].
							"&amp;temp3=".$tab_bull['quartile3_classe_gen'].
							"&amp;temp4=".$tab_bull['quartile4_classe_gen'].
							"&amp;temp5=".$tab_bull['quartile5_classe_gen'].
							"&amp;temp6=".$tab_bull['quartile6_classe_gen'].
							"&amp;nb_data=7' alt='Quartiles' />\n";
					}
					else {
					*/
						echo "-";
					//}
	
					echo "</td>\n";
				}
	
				// Rang
				if(isset($tab_bull['affiche_rang'])) {
					echo "<td style='text-align: center;'><span class='bulletin'>";
					/*
					if(isset($tab_bull['rang_classe'][$i])) {
						echo $tab_bull['rang_classe'][$i]."/".$tab_bull['eff_classe'];
					}
					else {
					*/
						echo "-";
					//}
					echo "</span></td>\n";
				}
	
				echo "<td style='text-align: left;' colspan='2'><span class='bulletin'>\n";
				//echo $tab_bull['avis'][$i];
				echo "-";
				echo "</span></td>\n";
				echo "</tr>\n";
			}
		}
	}
	// Fin de la partie moyenne g�n�rale avec coef 1
}
//===============================================================================

echo "</table>\n";
$speech = "Average of term :";
$speechan = "Annual average :";
if($xperiodex != 3)
	echo "<br />"; 
if($cls == 'F4A' or $cls == 'F5A' or $cls == 'F4B' or $cls == 'F5B' or $cls == 'LSS' or $cls == 'LSA' or $cls == 'USS' or $cls == 'USA') {
		$speech = "Number of papers for term";
		$speechan = "Annual number of papers :";
		echo "<span style='float:left;color:black;text-align: left;margin-left:0'>Number of subjects passed = " . $passed ."</span>";
		$moygenagarder = $passed;
	if($xperiodex == 3)
		echo "<br />"; 
}
else {
	
}
if($cls == 'F1A' or $cls == 'F2A' or $cls == 'F1B' or $cls == 'F2B' or $cls == 'F3A' or $cls == 'F3B')
	echo "<span style='float: left;text-align: left;width: 250px;color:black;'>Position in the class : " . $xrangx . "/" . $tab_bull['eff_classe'] . "<br />";
else
	echo "<span style='float: left;text-align: left;width: 250px;color:black;'>";
$updatemoyennetrimestrielle = "UPDATE `j_eleves_classes` SET `moyenne` = '$moygenagarder' WHERE `j_eleves_classes`.`login` = '$xloginx' AND `j_eleves_classes`.`id_classe` = $xclassex AND `j_eleves_classes`.`periode` = $xperiodex;";
//var_dump($updatemoyennetrimestrielle);
$updatemoyenneeleve = mysql_query($updatemoyennetrimestrielle);
if($xperiodex == 3) {
	// On recupere les moyennes des trimestres precedents
	$sql = "SELECT * FROM `j_eleves_classes` WHERE `j_eleves_classes`.`login` = '$xloginx' AND `j_eleves_classes`.`id_classe` = $xclassex;";
	$moyennesprecedentes = mysql_query($sql);
	$sumaverage = 0;
	$MoYaN = 0;
	$nbterm = 0;
	while ($row = mysql_fetch_array($moyennesprecedentes, MYSQL_NUM)) {
		if($row[2] != 3) {
			echo "<span style='font-size:12px'>$speech " . $row[2] . " : " . $row[4] . "</span><br />";
			if(!(empty($row[4])) && $row[4] != 0) {
				$sumaverage = $sumaverage + $row[4];
				$nbterm++;
			}
		}
		 
	  // printf("login : %s  classe : %s periode : %s rang : %s moyenne : %s", $row[0], $row[1], $row[2], $row[3], $row[4]);
	}
	$sumaverage = $sumaverage + $moygenagarder;
	if($cls == 'F4A' or $cls == 'F5A' or $cls == 'F4B' or $cls == 'F5B' or $cls == 'LSS' or $cls == 'LSA' or $cls == 'USS' or $cls == 'USA')
		$MoYaN =  $sumaverage;
	else
		$MoYaN = $sumaverage / 3;
	echo "<span style='font-size:13px;font-weight:bold'>$speechan " . $MoYaN . "</span>";
}
echo "</span>"; 
if($xperiodex == 3)
	if($cls == 'F4A' or $cls == 'F5A' or $cls == 'F4B' or $cls == 'F5B' or $cls == 'LSS' or $cls == 'LSA' or $cls == 'USS' or $cls == 'USA')
		echo "<span style='float: left;text-align: right;width: 500px;color:black;height:20px;padding-top:40px'>Principal's remark : ";
	else
		echo "<span style='float: left;text-align: right;width: 500px;color:black;height:20px;padding-top:60px'>Principal's remark : "; 
else
echo "<span style='float: left;text-align: right;width: 500px;color:black;'>Principal's remark : "; 
if($cls == 'F4A' or $cls == 'F5A' or $cls == 'F4B' or $cls == 'F5B') {
	if($xperiodex == 3) {
		if($MoYaN >= 18)
			echo "<span style='color:blue;'>PROMOTED</span>";
		else
			echo "<span style='color:red;'>REPEAT</span>";
	} else {
		if($passed >= 6)
			echo "<span style='color:blue;'>PASSED</span>";
		else
			echo "<span style='color:red;'>FAILED</span>";
	}
} elseif($cls == 'LSS' or $cls == 'LSA' or $cls == 'USS' or $cls == 'USA') {
	if($xperiodex == ) {
		if($MoYaN >= 6)
			echo "<span style='color:blue;'>PROMOTED</span>";
		else
			echo "<span style='color:red;'>REPEAT</span>";
	} else {
		if($passed >= 2)
			echo "<span style='color:blue;'>PASSED</span>";
		else
			echo "<span style='color:red;'>FAILED</span>";
	}
} else {
	if($xperiodex == 3) {
		if($MoYaN >= 10 && $MoYaN <= 20) { echo "<span style='color:blue;'>PROMOTED</span>"; } elseif($MoY < 10 && $MoY >= 0) { echo "<span style='color:red;'>REPEAT</span>"; } elseif($MoY == 10.0) { echo "<span style='color:blue;'>PROMOTED</span>"; }
	} else {
		if($MoY >= 10 && $MoY <= 20) { echo "<span style='color:blue;'>PASSED</span>"; } elseif($MoY < 10 && $MoY >= 0) { echo "<span style='color:red;'>FAILED</span>"; } elseif($MoY == 10.0) { echo "<span style='color:blue;'>PASSED</span>"; }
	}
}
echo "</span><br />";
if($cls == 'LSS' or $cls == 'LSA' or $cls == 'USS' or $cls == 'USA')
	include('signature2.php');
else
	include('signature.php');
//echo "<br />";
/// ajouter pour calculer la moyenne de leleve
/*
$updatemoyennetrimestrielle = "UPDATE `j_eleves_classes` SET `moyenne` = '$moygenagarder' WHERE `j_eleves_classes`.`login` = '$xloginx' AND `j_eleves_classes`.`id_classe` = $xclassex AND `j_eleves_classes`.`periode` = $xperiodex;";
//var_dump($updatemoyennetrimestrielle);
$updatemoyenneeleve = mysql_query($updatemoyennetrimestrielle);
if($xperiodex == 2) {
	// On recupere les moyennes des trimestres precedents
	$sql = "SELECT * FROM `j_eleves_classes` WHERE `j_eleves_classes`.`login` = '$xloginx' AND `j_eleves_classes`.`id_classe` = $xclassex;";
	$moyennesprecedentes = mysql_query($sql);
	$sumaverage = 0;
	$MoYaN = 0;
	$nbterm = 0;
	while ($row = mysql_fetch_array($moyennesprecedentes, MYSQL_NUM)) {
		if($row[2] != 3) {
			echo "<span style='font-size:13px'>Average of term " . $row[2] . " : " . $row[4] . "</span><br />";
			if(!(empty($row[4])) && $row[4] != 0) {
				$sumaverage = $sumaverage + $row[4];
				$nbterm++;
			}
		}
	  // printf("login : %s  classe : %s periode : %s rang : %s moyenne : %s", $row[0], $row[1], $row[2], $row[3], $row[4]);
	}
	$MoYaN = $sumaverage / $nbterm;
	echo "Annual average : " . $MoYaN;
}*/
?>
