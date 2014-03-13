<?php
/*
$Id: saisie_sanction.inc.php 7140 2011-06-06 14:57:51Z crob $
*/

// Page incluse dans saisie_sanction.php ou appel�e via ajax depuis saisie_sanction.php->ajout_sanction.php

//Configuration du calendrier

include("../lib/calendrier/calendrier.class.php");

//Variable : $dernier  on afficher le dernier cr�neau si $dernier='o' (param�tre pour une exclusion)
function choix_heure2($champ_heure,$selected,$dernier) {
	$sql="SELECT * FROM edt_creneaux ORDER BY heuredebut_definie_periode;";
	$res_abs_cren=mysql_query($sql);
	$num_row = mysql_num_rows($res_abs_cren); //le nombre de ligne de la requ�te
	if($num_row==0) {
		echo "The table edt_creneaux is not well informed!";
	}
	else {
        $cpt=1;	
		//echo "<select name='$champ_heure' id='$champ_heure' onchange='changement();' >\n";
		echo "<select name='$champ_heure' id='$champ_heure' onchange=\"if(document.getElementById('display_heure_main')) {document.getElementById('display_heure_main').value=document.getElementById('$champ_heure').options[document.getElementById('$champ_heure').selectedIndex].value};changement();\" >\n";
		
		while($lig_ac=mysql_fetch_object($res_abs_cren)) {
			echo "<option value='$lig_ac->nom_definie_periode'";
			if(($lig_ac->nom_definie_periode==$selected)||(($dernier=='o')&&($cpt==$num_row))) {echo " selected='selected'";}
			echo ">$lig_ac->nom_definie_periode&nbsp;: $lig_ac->heuredebut_definie_periode � $lig_ac->heurefin_definie_periode</option>\n";
			$cpt++;
		}
		echo "</select>\n";
	}
}

//if((!isset($cpt))||(!isset($valeur))) {
if(!isset($valeur)) {
	echo "<p><strong>Error&nbsp;:</strong> Parameters were not transmitted.</p>\n";
	die();
}

require_once('sanctions_func_lib.php');
//echo "\$ele_login=$ele_login<br />";
//echo "\$id_incident=$id_incident<br />";
$meme_sanction_pour_autres_protagonistes="";
if(isset($ele_login)) {
	//echo "plop";
	$texte_protagoniste_1="Sanction for <b>".get_nom_prenom_eleve($ele_login)."</b><br />\n";
	if((isset($id_incident))&&(!isset($id_sanction))) {
		$tab_protagonistes=get_protagonistes($id_incident,array('Responsable'),array('eleve'));
		if(count($tab_protagonistes)>1) {
			//echo "plup";
			$meme_sanction_pour_autres_protagonistes.="Even sanction for&nbsp;:<br />\n";
			for($loop=0;$loop<count($tab_protagonistes);$loop++) {
				if($tab_protagonistes[$loop]!=$ele_login) {
					$meme_sanction_pour_autres_protagonistes.="<input type='checkbox' name='autre_protagoniste_meme_sanction[]' id='autre_protagoniste_meme_sanction_$loop' value=\"$tab_protagonistes[$loop]\" /><label for='autre_protagoniste_meme_sanction_$loop'>".get_nom_prenom_eleve($tab_protagonistes[$loop])."</label><br />\n";
				}
			}

			$meme_sanction_pour_autres_protagonistes=$texte_protagoniste_1.$meme_sanction_pour_autres_protagonistes;
		}
	}
	//elseif(isset($id_sanction)) {
	//}
}

if($valeur=='travail') {
	echo "<table class='boireaus' border='1'>\n";

	$cal = new Calendrier("formulaire", "date_retour");

	$annee = strftime("%Y");
	$mois = strftime("%m");
	$jour = strftime("%d");
	$date_retour=$jour."/".$mois."/".$annee;

	$travail="";
	$heure_retour=strftime("%H").":".strftime("%M");
	if(isset($id_sanction)) {
		$sql="SELECT * FROM s_travail WHERE id_sanction='$id_sanction';";
		$res_sanction=mysql_query($sql);
		if(mysql_num_rows($res_sanction)>0) {
			$lig_sanction=mysql_fetch_object($res_sanction);
			$date_retour=formate_date($lig_sanction->date_retour);
			$heure_retour=$lig_sanction->heure_retour;
			$travail=$lig_sanction->travail;
		}
	}

	if(($travail=="")&&(isset($id_incident))&&(isset($ele_login))) {
		$sql="SELECT * FROM s_travail_mesure WHERE id_incident='$id_incident' AND login_ele='".$ele_login."';";
		$res_travail_mesure_demandee=mysql_query($sql);
		if(mysql_num_rows($res_travail_mesure_demandee)>0) {
			$lig_travail_mesure_demandee=mysql_fetch_object($res_travail_mesure_demandee);
			$travail=$lig_travail_mesure_demandee->travail;
		}
	}

	$alt=1;
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Go back to return &nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	//echo "<input type='text' name='date_retour' id='date_retour' size='10' value=\"".$date_retour."\" onchange='changement();' />\n";
	echo "<input type='text' name='date_retour' id='date_retour' size='10' value=\"".$date_retour."\" onchange='changement();' onKeyDown=\"clavier_date_plus_moins(this.id,event);\" />\n";
	echo "<a href=\"#calend\" onclick=\"".$cal->get_strPopup('../lib/calendrier/pop.calendrier.php', 350, 170)."\">\n";
	echo "<img src=\"../lib/calendrier/petit_calendrier.gif\" border=\"0\" alt=\"Petit calendrier\" />\n";
	echo "</a>\n";
	echo "</td>\n";
	echo "</tr>\n";

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Hour of return&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	choix_heure2('heure_retour',$heure_retour,'');
	echo "</td>\n";
	echo "</tr>\n";

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Nature of work&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";

	echo "<div style='float:right;'>";
	if(isset($id_sanction)) {
		echo lien_envoi_mail_rappel($id_sanction, 0);
	}
	elseif(isset($id_incident)) {
		echo lien_envoi_mail_rappel($id_sanction, 0, $id_incident);
	}
	//echo envoi_mail_rappel_js();
	echo "</div>\n";

	echo "<textarea name='no_anti_inject_travail' cols='30' onchange='changement();'>$travail</textarea>\n";

	//echo "<span style='color: red;'>Mettre un champ d'ajout de fichier.</span><br />\n";
	//echo "<span style='color: red;'>Pouvoir aussi choisir un des fichiers joints lors de la d�claration de l'incident.</span><br />\n";

	if((isset($ele_login))&&(isset($id_incident))) {
		sanction_documents_joints($id_incident, $ele_login);
	}

	echo "</td>\n";
	echo "</tr>\n";

	if($meme_sanction_pour_autres_protagonistes!="") {
		$alt=$alt*(-1);
		echo "<tr class='lig$alt'>\n";
		echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Even sanction&nbsp;: </td>\n";
		echo "<td style='text-align:left;'>\n";
		echo $meme_sanction_pour_autres_protagonistes;
		echo "</td>\n";
		echo "</tr>\n";
	}

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td colspan='2'>\n";
	echo "<input type='submit' name='enregistrer_sanction' value='Record' />\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>\n";
}
elseif($valeur=='retenue') {

	$cal = new Calendrier("formulaire", "date_retenue");

	$annee = strftime("%Y");
	$mois = strftime("%m");
	$jour = strftime("%d");
	$date_retenue=$jour."/".$mois."/".$annee;

	//$heure_debut=strftime("%H").":".strftime("%M");
	$heure_debut='00:00';
	$duree_retenue=1;
	$lieu_retenue="";
	$travail="";
	if(isset($id_sanction)) {
		$sql="SELECT * FROM s_retenues WHERE id_sanction='$id_sanction';";
		$res_sanction=mysql_query($sql);
		if(mysql_num_rows($res_sanction)>0) {
			$lig_sanction=mysql_fetch_object($res_sanction);
			$date_retenue=formate_date($lig_sanction->date);
			$heure_debut=$lig_sanction->heure_debut;
			$duree_retenue=$lig_sanction->duree;
			$lieu_retenue=$lig_sanction->lieu;
			$travail=$lig_sanction->travail;
		}
	}

	if(($travail=="")&&(isset($id_incident))&&(isset($ele_login))) {
		$sql="SELECT * FROM s_travail_mesure WHERE id_incident='$id_incident' AND login_ele='".$ele_login."';";
		$res_travail_mesure_demandee=mysql_query($sql);
		if(mysql_num_rows($res_travail_mesure_demandee)>0) {
			$lig_travail_mesure_demandee=mysql_fetch_object($res_travail_mesure_demandee);
			$travail=$lig_travail_mesure_demandee->travail;
		}
	}

	//echo "<div id='div_liste_retenues_jour' style='float:right; border:1px solid black;background-color: honeydew;'>\n";
	echo "<div id='div_liste_retenues_jour' style='float:right; text-align: center; border:1px solid black; margin-top: 2px; min-width: 19px;'>\n";
	echo "<a href='#' onclick=\"maj_div_liste_retenues_jour();return false;\" title='Reserves of the day'><img src='../images/icons/ico_question_petit.png' width='15' height='15' alt='Reserves of the day' /></a>";
	echo "</div>\n";

	echo "<table class='boireaus' border='1' summary='Retenue'>\n";
	$alt=1;
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Date&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	//echo "<input type='text' name='date_retenue' id='date_retenue' value='$date_retenue' size='10' onchange='maj_div_liste_retenues_jour();changement();' onblur='maj_div_liste_retenues_jour();' />\n";
	echo "<input type='text' name='date_retenue' id='date_retenue' value='$date_retenue' size='10' onchange='maj_div_liste_retenues_jour();changement();' onblur='maj_div_liste_retenues_jour();' onKeyDown=\"clavier_date_plus_moins(this.id,event);\" />\n";
	echo "<a href=\"#calend\" onclick=\"$('date_retenue').focus();".$cal->get_strPopup('../lib/calendrier/pop.calendrier.php', 350, 170)."\">\n";
	echo "<img src=\"../lib/calendrier/petit_calendrier.gif\" border=\"0\" alt=\"Petit calendrier\" />\n";
	echo "</a>\n";

	// Si le module EDT est actif et si l'EDT est renseign�
	if(param_edt($_SESSION["statut"]) == 'yes') {
		//echo "<a href='#' onclick=\"edt_eleve('$id_sanction');return false;\" title='EDT �l�ve'><img src='../images/icons/ico_question_petit.png' width='15' height='15' alt='EDT �l�ve' /></a>";
		echo "<a href='#' onclick=\"edt_eleve();return false;\" title='EDT �l�ve'><img src='../images/icons/ico_question_petit.png' width='15' height='15' alt='EDT �l�ve' /></a>";
		//echo "<input type='hidden' name='ele_login' id='ele_login' value='$ele_login' />\n";
	}

	echo "</td>\n";
	echo "</tr>\n";

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Hour of beginning&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	//echo "<input type='text' name='heure_debut' value='' />\n";
	echo "<input type='text' name='heure_debut_main' id='display_heure_main' size='5' value=\"$heure_debut\" onKeyDown=\"clavier_heure(this.id,event);\" AutoComplete=\"off\" /> ou \n";
	choix_heure2('heure_debut',$heure_debut,'');
	
	//pour infobulle
	$texte="- 2 possible choices to register the hour of beginning of reserve<br />First thanks to the drop-down list. You choose a crenel. In this case, it is the hour beginning of
cr�naux HH:MM which will be taken into account for the impression of
reserve.<br/>In the other case, you seize the hour in the place from ' 00:00 '
under this format.";
	
	echo "</td>\n";
	echo "</tr>\n";

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Duration&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	echo "<input type='text' name='duree_retenue' id='duree_retenue' size='2' value='$duree_retenue' onchange='changement();' /> in hours\n";
	echo "</td>\n";
	echo "</tr>\n";

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Place&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	echo "<input type='text' name='lieu_retenue' id='lieu_retenue' value='$lieu_retenue' onchange='changement();' />\n";
	// S�lectionner parmi des lieux d�j� saisis?
	//$sql="SELECT DISTINCT lieu FROM s_retenues WHERE lieu!='' ORDER BY lieu;";
	$sql="(SELECT DISTINCT lieu FROM s_retenues WHERE lieu!='')";
	if(param_edt($_SESSION["statut"]) == 'yes') {
		$sql.=" UNION (SELECT DISTINCT nom_salle AS lieu FROM salle_cours WHERE nom_salle!='')";
	}
	$sql.=" ORDER BY lieu;";
	//echo "$sql<br />";
	$res_lieu=mysql_query($sql);
	//$tab_lieux=array();
	//$chaine_lieux="";
	if(mysql_num_rows($res_lieu)>0) {
		echo "<select name='choix_lieu' id='choix_lieu' onchange=\"maj_lieu('lieu_retenue','choix_lieu');changement();\">\n";
		echo "<option value=''>---</option>\n";
		while($lig_lieu=mysql_fetch_object($res_lieu)) {
			echo "<option value=\"$lig_lieu->lieu\">$lig_lieu->lieu</option>\n";
			//$tab_lieux[]=urlencode($lig_lieu->lieu);
			//$chaine_lieux.=", '".urlencode($lig_lieu->lieu)."'";
		}
		echo "</select>\n";

		echo "<a href='#' onclick=\"occupation_lieu_heure('$id_sanction');return false;\"><img src='../images/icons/ico_question_petit.png' width='15' height='15' alt='Occupation of the place for the selected date/time' /></a>";
	}

	echo "</td>\n";
	echo "</tr>\n";

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Nature of work&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	//echo "plop";
	echo "<div style='float:right;'>";
	if(isset($id_sanction)) {
		//echo "\$id_sanction=$id_sanction";
		echo lien_envoi_mail_rappel($id_sanction, 0);
	}
	elseif(isset($id_incident)) {
		//echo "\$id_incident=$id_incident";
		echo lien_envoi_mail_rappel("", 0, $id_incident);
	}
	//echo envoi_mail_rappel_js();
	echo "</div>\n";

	echo "<textarea name='no_anti_inject_travail' cols='30' onchange='changement();'>$travail</textarea>\n";

	//echo "<span style='color: red;'>Mettre un champ d'ajout de fichier.</span><br />\n";
	//echo "<span style='color: red;'>Pouvoir aussi choisir un des fichiers joints lors de la d�claration de l'incident.</span><br />\n";

	if((isset($ele_login))&&(isset($id_incident))) {
		sanction_documents_joints($id_incident, $ele_login);
	}

	echo "</td>\n";
	echo "</tr>\n";
	
	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Carryforward &nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	
	echo "<b>Management of a carryforward :</b><br/>\n";
	
	echo "<ol>\n";
	echo "<li>Tick this box to treat a carryforward : <input type='checkbox' name='report_demande' id='report_demande' value='OK' onchange=\"changement();\" /></li>\n";
	echo "<li>Seize the reason for the carryforward : <select name='choix_motif_report' id='choix_motif_report' changement();\">\n";
	echo "<option value=''>---</option>\n";
	echo "<option value='absent'>Absent</option>\n";
	echo "<option value='aucun_motif'>No reason</option>\n";
	echo "<option value='report_demande'>Carryforward requested</option>\n";
	echo "<option value='autre'>Other</option>\n";
	echo "</select></li>\n";
	echo "<li>Modify the data (date, heure, ...) for the carryforward</li>\n";
	echo "<li>Record the modifications</li>\n";
	echo "<li>Print the document on the following page</li>\n";
	echo "</ol>\n";
	
	if (isset($id_sanction)) {
		echo "<b>Liste des reports</b><br/>\n";
		echo afficher_tableau_des_reports($id_sanction);
	}
	echo "</td>\n";
	echo "</tr>\n";

	if($meme_sanction_pour_autres_protagonistes!="") {
		$alt=$alt*(-1);
		echo "<tr class='lig$alt'>\n";
		echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Even sanction&nbsp;: </td>\n";
		echo "<td style='text-align:left;'>\n";
		echo $meme_sanction_pour_autres_protagonistes;
		echo "</td>\n";
		echo "</tr>\n";
	}

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td colspan='2'>\n";
	echo "<input type='submit' name='enregistrer_sanction' value='Enregistrer' />\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>\n";

	echo "<script type='text/javascript'>
	// Launching below is not taken into account for the addition of a
reserve, only for the modification of a reserve.
	// I thus put a bond in the DIV div_liste_retenues_jour
	maj_div_liste_retenues_jour();
</script>\n";
}
elseif($valeur=='exclusion') {
	echo "<table class='boireaus' border='1' summary='Exclusion'>\n";

	$cal1 = new Calendrier("formulaire", "date_debut");

	$annee = strftime("%Y");
	$mois = strftime("%m");
	$jour = strftime("%d");
	$date_debut=$jour."/".$mois."/".$annee;
	$date_fin=$date_debut;

	$heure_debut=strftime("%H").":".strftime("%M");
	$heure_fin=$heure_debut;
	$afficher_creneau_final = 'o';

	$lieu_exclusion="";
	$travail="";
	
	$nombre_jours="";
	$qualification_faits="";
	$numero_courrier="";
	$type_exclusion="";
	$fct_autorite="";
	$nom_autorite="";
	$fct_delegation="";
	
	if(isset($id_sanction)) {
		$sql="SELECT * FROM s_exclusions WHERE id_sanction='$id_sanction';";
		$res_sanction=mysql_query($sql);
		if(mysql_num_rows($res_sanction)>0) {
			$lig_sanction=mysql_fetch_object($res_sanction);
			$date_debut=formate_date($lig_sanction->date_debut);
			$date_fin=formate_date($lig_sanction->date_fin);
			$heure_debut=$lig_sanction->heure_debut;
			$heure_fin=$lig_sanction->heure_fin;
			$lieu_exclusion=$lig_sanction->lieu;
			$travail=$lig_sanction->travail;
			$afficher_creneau_final='';
			$nombre_jours=$lig_sanction->nombre_jours;
			$qualification_faits=$lig_sanction->qualification_faits;
			$numero_courrier=$lig_sanction->num_courrier;
			$type_exclusion=$lig_sanction->type_exclusion;
			$signataire=$lig_sanction->id_signataire;
		} 
	}

	if(($travail=="")&&(isset($id_incident))&&(isset($ele_login))) {
		$sql="SELECT * FROM s_travail_mesure WHERE id_incident='$id_incident' AND login_ele='".$ele_login."';";
		$res_travail_mesure_demandee=mysql_query($sql);
		if(mysql_num_rows($res_travail_mesure_demandee)>0) {
			$lig_travail_mesure_demandee=mysql_fetch_object($res_travail_mesure_demandee);
			$travail=$lig_travail_mesure_demandee->travail;
		}
	}

	$alt=1;
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Go back to beginning &nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	//echo "<input type='text' name='date_debut' id='date_debut' value='$date_debut' size='10' onchange='changement();' />\n";
	echo "<input type='text' name='date_debut' id='date_debut' value='$date_debut' size='10' onchange='changement();' onKeyDown=\"clavier_date_plus_moins(this.id,event);\" />\n";
	echo "<a href=\"#calend\" onclick=\"".$cal1->get_strPopup('../lib/calendrier/pop.calendrier.php', 350, 170)."\">\n";
	echo "<img src=\"../lib/calendrier/petit_calendrier.gif\" border=\"0\" alt=\"Small calendar\" />\n";
	echo "</a>\n";
	echo "</td>\n";
	echo "</tr>\n";

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Hour of beginning&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	//echo "<input type='text' name='heure_debut' value='' />\n";
	choix_heure2('heure_debut',$heure_debut,'');
	echo "</td>\n";
	echo "</tr>\n";

	$cal2 = new Calendrier("formulaire", "date_fin");

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Date of end &nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	//echo "<input type='text' name='date_fin' id='date_fin' value='$date_fin' size='10' onchange='changement();' />\n";
	echo "<input type='text' name='date_fin' id='date_fin' value='$date_fin' size='10' onchange='changement();' onKeyDown=\"clavier_date_plus_moins(this.id,event);\" />\n";
	echo "<a href=\"#calend\" onclick=\"".$cal2->get_strPopup('../lib/calendrier/pop.calendrier.php', 350, 170)."\">\n";
	echo "<img src=\"../lib/calendrier/petit_calendrier.gif\" border=\"0\" alt=\"Petit calendrier\" />\n";
	echo "</a>\n";
	echo "</td>\n";
	echo "</tr>\n";

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Hour of end &nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	//echo "<input type='text' name='heure_debut' value='' />\n";
	choix_heure2('heure_fin',$heure_fin,$afficher_creneau_final);
	echo "</td>\n";
	echo "</tr>\n";

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Place&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	echo "<input type='text' name='lieu_exclusion' id='lieu_exclusion' value=\"$lieu_exclusion\" onchange='changement();' />\n";
	// S�lectionner parmi des lieux d�j� saisis?
	$sql="SELECT DISTINCT lieu FROM s_exclusions WHERE lieu!='' ORDER BY lieu;";
	$res_lieu=mysql_query($sql);
	if(mysql_num_rows($res_lieu)>0) {
		echo "<select name='choix_lieu' id='choix_lieu' onchange=\"maj_lieu('lieu_exclusion','choix_lieu');changement();\">\n";
		echo "<option value=''>---</option>\n";
		while($lig_lieu=mysql_fetch_object($res_lieu)) {
			echo "<option value=\"$lig_lieu->lieu\">$lig_lieu->lieu</option>\n";
		}
		echo "</select>\n";
	}
	echo "</td>\n";
	echo "</tr>\n";

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Nature of work &nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";

	echo "<div style='float:right;'>";
	if(isset($id_sanction)) {
		echo lien_envoi_mail_rappel($id_sanction, 0);
	}
	elseif(isset($id_incident)) {
		echo lien_envoi_mail_rappel($id_sanction, 0, $id_incident);
	}
	//echo envoi_mail_rappel_js();
	echo "</div>\n";

	echo "<textarea name='no_anti_inject_travail' cols='30' onchange='changement();'>$travail</textarea>\n";

	//echo "<span style='color: red;'>Mettre un champ d'ajout de fichier.</span><br />\n";
	//echo "<span style='color: red;'>Pouvoir aussi choisir un des fichiers joints lors de la d�claration de l'incident.</span><br />\n";

	if((isset($ele_login))&&(isset($id_incident))) {
		sanction_documents_joints($id_incident, $ele_login);
	}

	echo "</td>\n";
	echo "</tr>\n";

// Ajout Eric g�n�ration Ooo de l'exclusion
	$alt=$alt*(-1);
	echo "<tr>\n";
	echo "<td colspan=2 style='text-align:center;'>\n";
	echo "Data to inform for the Open impression Office of temporary exclusion :</td>\n";
	echo "</tr>\n";

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Number of mail&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	echo "<input type='text' name='numero_courrier' id='numero_courrier' value=\"$numero_courrier\" onchange='changement();' />\n";
	echo "<i>The reference of the mail in the register mail departure. Ex : ADM/SD/012/11</i></td>\n";
	echo "</tr>\n";
	
	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Type of exclusion&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	echo "<input type='text' name='type_exclusion' id='type_exclusion' value=\"$type_exclusion\" onchange='changement();' />\n";
	echo "<select name='type_exclusion' id='type_exclusion_select' onchange=\"maj_lieu('type_exclusion','type_exclusion_select','type_exclusion');changement();\">\n";
	if ($type_exclusion=='exclusion temporaire') {
	    echo "<option value=\"exclusion temporaire\" selected>Temporary exclusion</option>\n";
	} else {
	    echo "<option value=\"exclusion temporaire\">Temporary exclusion</option>\n";
	}
	if ($type_exclusion=='exclusion-inclusion temporaire') {
	    echo "<option value=\"exclusion-inclusion temporaire\" selected>temporary Exclusion-inclusion</option>\n";
	} else {
	    echo "<option value=\"exclusion-inclusion temporaire\">temporary Exclusion-inclusion</option>\n";
	}
	
	
	echo "</select>\n";
	echo "<i>Choose the type in the list.</i></td>\n";
	echo "</tr>\n";	
	
	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Number of days of exclusion&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	echo "<input type='text' name='nombre_jours' id='nombre_jours' value=\"$nombre_jours\" onchange='changement();' />\n";
	echo "<i>in all letters</i></td>\n";
	echo "</tr>\n";
	
	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Qualification des faits&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	echo "<textarea name='no_anti_inject_qualification_faits' cols='100' onchange='changement();'>$qualification_faits</textarea>\n";
	echo "</td>\n";
	echo "</tr>\n";
	
	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Choice of the signatory of exclusion&nbsp;: </td>\n";
	echo "<td style='text-align:left;'>\n";
	// S�lectionner parmi les signataires d�j� saisis?
	$sql="SELECT * FROM s_delegation ORDER BY fct_autorite";
	$res_signataire=mysql_query($sql);
	if(mysql_num_rows($res_signataire)>0) {
		echo "<select name='signataire' id='choix_signataire' onchange=\"changement();\">\n";
		echo "<option value=''>---</option>\n";
		while($lig_signataire=mysql_fetch_object($res_signataire)) {
		    if ($signataire==$lig_signataire->id_delegation) {
			echo "<option value=\"$lig_signataire->id_delegation\" selected >$lig_signataire->fct_autorite</option>\n";
			} else {
			echo "<option value=\"$lig_signataire->id_delegation\">$lig_signataire->fct_autorite</option>\n";
			}
		}
		echo "</select>\n";
	} else {
	    echo "<i>No signatory is seized in the base. Ask your administrator to seize
this list in admin module</i>";
	};
	echo "</td>\n";
	echo "</tr>\n";

	if($meme_sanction_pour_autres_protagonistes!="") {
		$alt=$alt*(-1);
		echo "<tr class='lig$alt'>\n";
		echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Even sanction&nbsp;: </td>\n";
		echo "<td style='text-align:left;'>\n";
		echo $meme_sanction_pour_autres_protagonistes;
		echo "</td>\n";
		echo "</tr>\n";
	}

	$alt=$alt*(-1);
	echo "<tr class='lig$alt'>\n";
	echo "<td colspan='2'>\n";
	echo "<input type='submit' name='enregistrer_sanction' value='Enregistrer' />\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>\n";
}
else {
	$sql="SELECT * FROM s_types_sanctions WHERE id_nature='$valeur';";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)>0) {
		$lig=mysql_fetch_object($res);

		echo "<table class='boireaus' border='1' summary=\"$lig->nature\">\n";

		$description="";

		if(isset($id_sanction)) {
			$sql="SELECT * FROM s_autres_sanctions WHERE id_sanction='$id_sanction';";
			$res_sanction=mysql_query($sql);
			if(mysql_num_rows($res_sanction)>0) {
				$lig_sanction=mysql_fetch_object($res_sanction);
				$description=$lig_sanction->description;
			}
		}

		echo "<tr>\n";
		echo "<th colspan='2'>$lig->nature</th>\n";
		echo "</tr>\n";

		$alt=1;
		echo "<tr class='lig$alt'>\n";
		echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>Description&nbsp;: </td>\n";
		echo "<td style='text-align:left;'>\n";
		echo "<textarea name='no_anti_inject_description' cols='30' onchange='changement();'>$description</textarea>\n";

		if((isset($ele_login))&&(isset($id_incident))) {
			sanction_documents_joints($id_incident, $ele_login);
		}

		echo "</td>\n";
		echo "</tr>\n";

		if($meme_sanction_pour_autres_protagonistes!="") {
			$alt=$alt*(-1);
			echo "<tr class='lig$alt'>\n";
			echo "<td style='font-weight:bold;vertical-align:top;text-align:left;'>M�me sanction&nbsp;: </td>\n";
			echo "<td style='text-align:left;'>\n";
			echo $meme_sanction_pour_autres_protagonistes;
			echo "</td>\n";
			echo "</tr>\n";
		}

		$alt=$alt*(-1);
		echo "<tr class='lig$alt'>\n";
		echo "<td colspan='2'>\n";
		echo "<input type='submit' name='enregistrer_sanction' value='Enregistrer' />\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "</table>\n";
	}
	else {
		echo "<p style='color:red;'>Unknown type of sanction.</p>\n";
	}
}
?>