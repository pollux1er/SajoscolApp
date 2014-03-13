<?php



$link = mysql_connect('localhost', 'root', 'sasse');

if(!$link){
	die('Impossible de se connecter : ' . mysql_error());
}

$db_selected = mysql_select_db('gepi03', $link);

if(!$db_selected) {
	die(' impossible de selectionner la base de donnees : ' . mysql_error());
}

$sql = 'SELECT id FROM `groupes` WHERE id IN (SELECT distinct(id_groupe) FROM `j_eleves_groupes`)';
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
	var_dump($row['id']);
	$r = "DELETE FROM matieres_notes 
		WHERE id_groupe='".$row['id']."' AND periode='1' AND statut!='-' 
		AND LOGIN NOT IN 
		(SELECT login FROM `j_eleves_groupes` 
		WHERE id_groupe = '".$row['id']."' AND periode = '1' AND login IN 
		(SELECT login FROM eleves))";
	$rst = mysql_query($r);
	if(!$rst) {
		echo "Error : " . mysql_error() . "<br />";
	} else {
		echo "success!<br />";	
	}

}

?>
