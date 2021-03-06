<?php
/**
 * Initialisation des couleurs
 * @version $Id: maj_coul_global.inc 7849 2011-08-20 18:19:12Z regis $
 * @license GNU/GPL 
 * @package Initialisation
 * @subpackage initialisation
*/

function getCouleurGlobal($couleur,$coul_origine) {
   $sql = "select VALUE from setting where NAME='".$couleur."'";
   $coul = sql_query($sql);
	if (!$coul) return ($coul_origine);
   if (count($coul) == 0) {
        return ($coul_origine);
	} else {
		$row = sql_row($coul,0);
		if ($row == "") {
			return ($coul_origine);
		} else {
        return ("#".$row[0]);
		}
	}
}


// Notices de type compte-rendu
$color_fond_notices["c"] = getCouleurGlobal("fond_notices_c",$color_fond_notices["c"]);
$couleur_entete_fond["c"] = getCouleurGlobal("entete_fond_c",$couleur_entete_fond["c"]);
$couleur_cellule["c"]=getCouleurGlobal("cellule_c",$couleur_cellule["c"]);
$couleur_cellule_alt["c"] = getCouleurGlobal("cellule_alt_c",$couleur_cellule_alt["c"]);

// Notices de type  travail � faire)
$color_fond_notices["t"] = getCouleurGlobal("fond_notices_t",$color_fond_notices["t"]);
$couleur_entete_fond["t"] = getCouleurGlobal("entete_fond_t",$couleur_entete_fond["t"]);
$couleur_cellule["t"] =getCouleurGlobal("cellule_t",$couleur_cellule["t"]);
$couleur_cellule_alt["t"] = getCouleurGlobal("cellule_alt_t",$couleur_cellule_alt["t"]);

// Notice informations g�n�rales
$color_fond_notices["i"] = getCouleurGlobal("fond_notices_i",$color_fond_notices["i"]);
$couleur_entete_fond["i"] =getCouleurGlobal("entete_fond_i",$couleur_entete_fond["i"]) ;
$couleur_cellule["i"]=getCouleurGlobal("cellule_i",$couleur_cellule["i"]);
$couleur_cellule_alt["i"] = getCouleurGlobal("cellule_alt",$couleur_cellule_alt["i"]);

// Travaux � faire "futurs"
$color_fond_notices["f"] = getCouleurGlobal("fond_notices_f",$color_fond_notices["f"]);
$couleur_cellule["f"] = getCouleurGlobal("cellule_f",$couleur_cellule["f"]);

$color_police_travaux = getCouleurGlobal("police_travaux",$color_police_travaux);
$color_police_matieres = getCouleurGlobal("police_matieres",$color_police_matieres);
$couleur_bord_tableau_notice = getCouleurGlobal("bord_tableau_notice",$couleur_bord_tableau_notice);
$couleur_cellule_gen = getCouleurGlobal("cellule_gen",$couleur_cellule_gen);


?>
