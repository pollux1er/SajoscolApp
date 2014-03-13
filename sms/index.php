<?php
/*
* $Id: index.php 8549 2011-10-26 16:47:02Z crob $
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

class Sender{
	var $host;
	var $port;
	/*
	* Username that is to be used for submission
	*/
	var $strUserName;
	/*
	* password that is to be used along with username
	*/
	var $strPassword;
	/*
	* Sender Id to be used for submitting the message
	*/
	var $strSender;
	/*
	* Message content that is to be transmitted
	*/
	var $strMessage;
	/*
	* Mobile No is to be transmitted.
	*/
	var $strMobile;
	/*
	* What type of the message that is to be sent
	* <ul>
	* <li>0:means plain text</li>
	* <li>1:means flash</li>
	* <li>2:means Unicode (Message content should be in Hex)</li>
	* <li>6:means Unicode Flash (Message content should be in Hex)</li>
	* </ul>
	*/
	var $strMessageType;
	/*
	* Require DLR or not
	* <ul>
	* <li>0:means DLR is not Required</li>
	* <li>1:means DLR is Required</li>
	* </ul>
	*/
	var $strDlr;
	
	private function sms__unicode($message){
		$hex1 = '';
		if (function_exists('iconv')) {
			$latin = @iconv('UTF-8', 'ISO-8859-1', $message);
			if (strcmp($latin, $message)) { 
				$arr = unpack('H*hex', @iconv('UTF-8', 'UCS2BE', $message));
				$hex1 = strtoupper($arr['hex']);
			}
			if($hex1 == ''){
				$hex2 = '';
				$hex = '';
				for ($i = 0; $i < strlen($message); $i++){
					$hex = dechex(ord($message[$i]));
					$len = strlen($hex);
					$add = 4 - $len;
					if($len < 4){
						for($j = 0; $j < $add; $j++){
							$hex = "0" . $hex;
						}
					}
					$hex2 .= $hex;
				}
				return $hex2;
			}
			else{
				return $hex1;
			}
		}
		else{
			print 'iconv Function Not Exists !';
		}
	}
	
	//Constructor..
	public function Sender ($host, $port, $username, $password, $sender, $message, $mobile, $msgtype, $dlr){
		$this->host=$host;
		$this->port=$port;
		$this->strUserName = $username;
		$this->strPassword = $password;
		$this->strSender= $sender;
		$this->strMessage=$message; //URL Encode The Message..
		$this->strMobile=$mobile;
		$this->strMessageType=$msgtype;
		$this->strDlr=$dlr;
	}
	
	public function Submit(){
		if($this->strMessageType == "2" || $this->strMessageType == "6") {
			//Call The Function Of String To HEX.
			$this->strMessage = $this->sms__unicode($this->strMessage);
			try {
				//Smpp http Url to send sms.
				$live_url="http://".$this->host.":".$this->port."/bulksms/bulksms?username=".$this->strUserName."&password=".$this->strPassword."&type=".$this->strMessageType."&dlr=".$this->strDlr."&destination=".$this->strMobile."&source=".$this->strSender."&message=".$this->strMessage."";
				$parse_url = file($live_url);
				echo $parse_url[0];
			} catch(Exception $e){
				echo 'Message:' .$e->getMessage();
			}
		}
		else
			$this->strMessage = urlencode($this->strMessage);
		try{
			//Smpp http Url to send sms.
			$live_url="http://".$this->host.":".$this->port."/bulksms/bulksms?username=".$this->strUserName."&password=".$this->strPassword."&type=".$this->strMessageType."&dlr=".$this->strDlr."&destination=".$this->strMobile."&source=".$this->strSender."&message=".$this->strMessage."";
			//var_dump($live_url); die;
			$parse_url=file_get_contents($live_url);
			return $parse_url[0];
		}
		catch(Exception $e){
			echo 'Message:' .$e->getMessage();
		}
	}
}

class sms {

	var $table = 'sms_init';
	var $id;
	var $id_em;
	var $num_tel_em;
	var $num_tel_recep;
	var $message;
	var $error_status;
	var $adr_ip_em;
	var $max_sms = 5;
	
	
	/**
	 * constructeur de la classe sms
	 */	
	function __construct($num_tel_em/*, $num_tel_recep, $mess , $error */) {
		// Je veux éviter de le surcharger de données qu'on ne peut pas fournir à tous les coup!
		// Pour moi le constructeur veut dire qu'on instancie d'abord l'objet, les manipulations surviennent après
		// Idéalement on sait presque toujours qui veut envoyer le SMS donc c la donnée initiale.
		//$this->setId($id);
		//$this->setId_em($em);
		$this->setNum_tel_em($num_tel_em);
		// $this->setNum_tel_recep($num_tel_recep);
		// $this->setMessage($mess);
		//	$this->setError_status($error);
		$this->setAdr_ip_em($_SERVER['REMOTE_ADDR']);
		//var_dump($this->num_tel_em);
	}
	
	/**
	 * fonctions set de tous les attributs de la classe
	 */
	function setId($id) { $this->id = $id; }
	function setId_em($id_em) { $this->id_em = $id_em; }
	function setNum_tel_em($num) { $this->num_tel_em = $num; }
	function setNum_tel_recep($num) { $this->num_tel_recep = $num; }
	function setMessage($mess) { $this->message = $mess; }
	function setError_status($err) { $this->error_status = $err; }
	function setAdr_ip_em($adr) { $this->adr_ip_em = $adr; }
	
	/**
	 * fonction pour nettoyer toutes les données qui vont dans la BDD
	 */
	function cleanNum_tel_em() { $this->num_tel_em = $this->num_tel_em; }
	function cleanNum_tel_recep() { $this->num_tel_recep = $this->num_tel_recep; }
	function cleanMessage() { $this->message = $this->message; }
	function cleanError_status() { $this->error_status = $this->error_status; }
	function cleanAdr_ip_em() { $this->adr_ip_em = $this->adr_ip_em; }
	
	/**
	 *
	 */
	function cleanAll_data() {
		$this->cleanNum_tel_em();
		$this->cleanNum_tel_recep();
		$this->cleanMessage();
		$this->cleanError_status();
		$this->cleanAdr_ip_em();
	}
	
	/**
	 * fonction qui permet de valider le numéro de téléphone de l'emetteur
	 * @return boolean
	 */
	function validateNum_tel_em() {
		
	}
	
	function validateMessage() {
		
		return true;	
	}
	
	/*
	 * Fonction qui permet d'envoyer les sms
	 */
	function send_sms($destination, $msg) { 
		$host = "121.241.242.114";
		$port = "8080";
		$user = "dms-annuairecm";
		$pass = "27fevrie";
		$source = $this->num_tel_em;
		$type = "0";
		$dlr = "1";
		// on enleve le + devant le numero de lemetteur pour eviter lerreur 1707
		$source = str_replace("+", "", $source);
		//var_dump($this->num_tel_em);
		if(empty($msg))
			return false;
			
		$this->message = $msg;
		// $this->message .= "\n"."__"."\n";
		// $this->message .= 'SMS4EVER.NET';
		$this->num_tel_recep = $destination = str_replace("+", "", $destination);
		$sender = "SAJOSCOL";
		//
		// if (preg_match("/2377/", $destination)) {
			// $action = "sendsms";
			// $userid = "726e02c0-b0cc-45d2-8e28-928c9fe84d0e";
			// $password = "25fevrier";
			// $url = "http://iyam.mobi/apiv1/?";
			// $urlsend = $url . "action=" . $action . "&userid=" . $userid . "&password=" . $password . "&sender=" . $sender . "&to=" . $destination . "&msg=" . $msg;
			
			// $return = file_get_contents($urlsend);
			// $response = json_decode($return);
			//$sms->keep_sms($_POST['numero'], $_POST['message']);
		// } elseif (preg_match("/2379/", $destination)) {
		
		$sms = new Sender($host, $port, $user, $pass, $source, $this->message, $destination, $type, $dlr);
		// var_dump($sms);
		// die();
		// } elseif (preg_match("/2375/", $destination)) {
			// $sms = new Sender($host, $port, $user, $pass, $source, $this->message, $destination, $type, $dlr);
		// } else {
		
		// }
		
		
		//$this->error_status = 
		//$sms->Submit();
		
		//var_dump($query);
		//parent::__insert($query);
		return $sms->Submit();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function log_debug($texte) {
	$fich=fopen("/tmp/debug.txt","a+");
	fwrite($fich,$texte."\n");
	fclose($fich);
}

//log_debug('Avant initialisations');

// Initialisations files
require_once("../lib/initialisations.inc.php");

//log_debug('Après initialisations');

extract($_GET, EXTR_OVERWRITE);
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

//log_debug('Après $session_gepi->security_check()');

if (!checkAccess()) {
	header("Location: ../logout.php?auto=1");
	die();
}
$_SESSION['chemin_retour'] = $_SERVER['REQUEST_URI'];

if(isset($_SESSION['retour_apres_maj_sconet'])) {
	unset($_SESSION['retour_apres_maj_sconet']);
}

//log_debug('Après checkAccess()');

//log_debug(debug_var());
//debug_var();


 //répertoire des photos

// En multisite, on ajoute le répertoire RNE


$gepi_prof_suivi=getSettingValue('gepi_prof_suivi');
if($_SESSION['statut']=="professeur") {
	if(getSettingValue('GepiAccesGestElevesProfP')!='yes') {
		tentative_intrusion("2", "Tentative d'accès par un prof à des fiches élèves, sans en avoir l'autorisation.");
		echo "Vous ne pouvez pas accéder à cette page car l'accès professeur n'est pas autorisé !";
		require ("../lib/footer.inc.php");
		die();
	}
	else {
		// Le professeur est-il professeur principal dans une classe au moins.
		$sql="SELECT 1=1 FROM j_eleves_professeurs WHERE professeur='".$_SESSION['login']."';";
		$test=mysql_query($sql);
		if (mysql_num_rows($test)==0) {
			tentative_intrusion("2", "Tentative d'accès par un prof qui n'est pas $gepi_prof_suivi à des fiches élèves, sans en avoir l'autorisation.");
			echo "Vous ne pouvez pas accéder à cette page car vous n'êtes pas $gepi_prof_suivi !";
			require ("../lib/footer.inc.php");
			die();
		}
	}
}

// Le statut scolarite ne devrait pas être proposé ici.
// La page confirm_query.php n'est accessible qu'en administrateur
if(($_SESSION['statut']=="administrateur")||($_SESSION['statut']=="scolarite")) {
	if (isset($is_posted) and ($is_posted == '1')) {

		check_token();

		
		//header("Location: ../lib/confirm_query.php?liste_cible=$liste_cible&amp;action=del_eleve");
		if($liste_cible!=''){
			header("Location: ../lib/confirm_query.php?liste_cible=$liste_cible&liste_cible2=$liste_cible2&action=del_eleve".add_token_in_url(false));
		}
	}
}

//**************** EN-TETE *****************
$titre_page = "Gestion des SMS";
require_once("../lib/header.inc");
//************** FIN EN-TETE *****************

if(getSettingValue('eleves_index_debug_var')=='y') {
	debug_var();
}
?>

<script type='text/javascript' language="JavaScript">
	
</script>

<?php
if ($_SESSION['statut'] == 'administrateur') {
	$retour = "../accueil_admin.php";
}
else{
	$retour = "../accueil.php";
}
if (isset($quelles_classes)) {
	$retour = "index.php";
}
echo "<p class=bold><a href=\"".$retour."\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour </a>\n";


if(($_SESSION['statut']=="administrateur")||($_SESSION['statut']=="scolarite")) {
	echo " | <a href='index.php?mode=unique'>Send SMS to a specific parent</a>\n";
	echo " | <a href='index.php?mode=class'>Send SMS to a class parents</a>\n";
	if(filesize('sms.txt') != 0) {
		echo " | <a href='index.php?mode=sendpendingsms'>Send pending SMS</a>\n";
	}

}

// if(($_SESSION['statut']=="administrateur")||($_SESSION['statut']=="scolarite")) {echo " | <a href='synchro_mail.php'>Synchroniser les adresses mail élèves</a>\n";}
if(isset($_GET['mode'])) { 
	if($_GET['mode'] == 'unique') {
		$query = "SELECT resp.resp_legal, rp.civilite, rp.nom, rp.prenom, rp.tel_port FROM responsables2 AS resp LEFT JOIN resp_pers AS rp ON rp.pers_id = resp.pers_id";
		//var_dump($query);
		$result = @mysql_query($query);
		$destinataires = array();
		while($row = mysql_fetch_assoc($result))
			$destinataires[] = $row;
		if($_SERVER['REQUEST_METHOD']=='post') {
			// $action = "sendsms";
			// $userid = "726e02c0-b0cc-45d2-8e28-928c9fe84d0e";
			// $password = "25fevrier";
			$sender = "SAJOSCOL";
			$sms = new sms($sender);
			$to = "237" . $_post['to'];
			$msg = urlencode($_post['message']);
			$response = $sms->send_sms($to, $msg);
			// $url = "http://iyam.mobi/apiv1/?";
			// $urlsend = $url . "action=" . $action . "&userid=" . $userid . "&password=" . $password . "&sender=" . $sender . "&to=" . $to . "&msg=" . $msg;
			
			// $return = file_get_contents($urlsend);
			// $response = json_decode($return);
			//var_dump($response);
		}
		?>
		<center><p class='grand'>Send SMS to one parent</p></center>
<?php if(@$response->status == "success") echo '<h2 style="color:blue;">' . 'Message succesfully sent to ' .  $_POST['to'] . '</h2>'; ?>
<form id="form1" name="form1" method="post" action="">
  <table width="40%" border="0" cellspacing="2" cellpadding="2">
    <tr>
      <th class="p-r-10" scope="row"><div align="right">Sender</div></th>
      <td><input name="sender" type="text" disabled="disabled" id="sender" readonly="readonly"value="SASSE" />
	  <input type="hidden" name="parent" value=""/></td>
    </tr>
    <tr>
      <th class="p-r-10" scope="row"><div align="right">Recipient</div></th>
      <td><select name="to" id="number" >
		<?php foreach($destinataires as $d){ ?>
			<option value="<?php echo $d['tel_port']; ?>"><?php echo $d['nom'] .  " " . $d['prenom'] ?> </option>
			<?php } ?>
		</select>
	  </td>
    </tr>
    <tr>
      <th class="p-r-10" scope="row"><div align="right">Message</div></th>
      <td><textarea name="message" id="textarea" cols="40" rows="3"></textarea></td>
    </tr>
    <tr>
      <th colspan="2" scope="row"><input type="submit" name="button" id="button" value="Send SMS" /></th>
    </tr>
  </table>
</form>
		<?php
	} elseif($_GET['mode'] == 'class') {
		$query = "SELECT * FROM classes";

		//var_dump($query);
		$result = @mysql_query($query);
		$classes = array();
		while($row = mysql_fetch_assoc($result))
			$classes[] = $row;
		
		if($_SERVER['REQUEST_METHOD']=='POST') {
			// $action = "sendsms";
			// $userid = "726e02c0-b0cc-45d2-8e28-928c9fe84d0e";
			// $password = "25fevrier";
			// $sender = "SAJOSCOL";
			// $msg = urlencode($_POST['message']);
			// $url = "http://iYam.mobi/apiv1/?";
			
			$query = "SELECT DISTINCT rsp.pers_id, rp.civilite, rp.nom, rp.tel_port, c.nom_complet, c.classe FROM `responsables2` AS rsp 
			LEFT JOIN resp_pers AS rp ON rp.pers_id = rsp.pers_id 
			INNER JOIN eleves AS e ON e.ele_id = rsp.ele_id 
			INNER JOIN j_eleves_classes AS j ON j.login = e.login 
			LEFT JOIN classes AS c ON c.id = j.id_classe WHERE c.classe = '" . $_POST['toclass'] . "' AND tel_port != ''";
			
			$result = @mysql_query($query);
			$parents = array();
			$sender = "SAJOSCOL";
			$sms = new sms($sender);
			$msg = $_POST['message'];
			while($row = mysql_fetch_assoc($result))
				$parents[] = $row;
			//var_dump($parents); die;
			foreach($parents as $p) {
				$to = "237" . $p['tel_port'];
				// $urlsend = $url . "action=" . $action . "&userid=" . $userid . "&password=" . $password . "&sender=" . $sender . "&to=" . $to . "&msg=" . $msg;
				// $return = file_get_contents($urlsend);
				// $response = json_decode($return);
				
				$response = $sms->send_sms($to, $msg);				
				var_dump($to);
				
			}
			//var_dump($destinataires);
			//var_dump($response);
		}
		
		?>
		<center><p class='grand'>SENS SmS to a class</p></center>

<form id="form1" name="form1" method="post" action="">
  <table width="40%" border="0" cellspacing="2" cellpadding="2">
    <tr>
      <th class="p-r-10" scope="row"><div align="right">Sender</div></th>
      <td><input name="sender" type="text" disabled="disabled" id="sender" readonly="readonly" value="SASSE" /></td>
    </tr>
    <tr>
      <th class="p-r-10" scope="row"><div align="right">Recipient</div></th>
      <td><select name="toclass" id="number" >
		<?php foreach($classes as $d){ ?>
			<option value="<?php echo $d['classe']; ?>"><?php echo $d['nom_complet']; ?> </option>
			<?php } ?>
		</select>
	  </td>
    </tr>
    <tr>
      <th class="p-r-10" scope="row"><div align="right">Message</div></th>
      <td><textarea name="message" id="textarea" cols="40" rows="3"></textarea></td>
    </tr>
    <tr>
      <th colspan="2" scope="row"><input type="submit" name="button" id="button" value="Send SMS" /></th>
    </tr>
  </table>
</form><?php
	
	
	} 
	elseif($_GET['mode'] == 'sendpendingsms') {
		$handle = @fopen("sms.txt", "r");
		echo "<br />";
		$sms = array();
		if ($handle) {
			while (($buffer = fgets($handle, 4096)) !== false) {
				$sms[] = $buffer;
			}
			if (!feof($handle)) {
				echo "Erreur: fgets() a échoué\n";
			}
			fclose($handle);
		}
		
		$url = "http://iYam.mobi/apiv1/?";
		$nbsmssent = 0;
		foreach($sms as $s) {
			$urlsend = $url . $s;
			$return = file_get_contents($urlsend);
			if($return)
				$response = json_decode($return);
			else {
				$req = "";
			}
			$nbsmssent++;
		}
		echo $nbsmssent . " SMS successfully sent!";
	}
	else {
	
	}
}
else {
	if($_SERVER['REQUEST_METHOD']=='POST') { 
		// $action = "sendsms";
		// $userid = "726e02c0-b0cc-45d2-8e28-928c9fe84d0e";
		// $password = "25fevrier";
		// $sender = "SAJOSCOL";
		// $to = "237" . $_POST['to'];
		//$msg = urlencode($_POST['message']);
		//var_dump($msg);
		// $url = "http://iYam.mobi/apiv1/?";
		// $urlsend = $url . "action=" . $action . "&userid=" . $userid . "&password=" . $password . "&sender=" . $sender . "&to=" . $to . "&msg=" . $msg;
		
		// $return = file_get_contents($urlsend);
		// $response = json_decode($return);
		//
		$sender = "SAJOSCOL";
			$sms = new sms($sender);
			$to = "237" . $_POST['to'];
			$msg = $_POST['message'];
			$response = $sms->send_sms($to, $msg);
			
	}
	// echo "<pre>";
	//var_dump($response);
	// var_dump($urlsend);
	// echo "</pre>";// die;
?>

<center><p class='grand'>Send SmS</p></center>
<?php if(preg_match("/1701/", $response)) echo '<h2 style="color:blue;">' . 'Message succesfully sent to ' .  $_POST['to'] . '</h2>';
		else echo '<h2 style="color:red;">Message not sent</h2>';
 ?>
<form id="form1" name="form1" method="post" action="">
  <table width="40%" border="0" cellspacing="2" cellpadding="2">
    <tr>
      <th class="p-r-10" scope="row"><div align="right">Sender</div></th>
      <td><input name="sender" type="text" disabled="disabled" id="sender" readonly="readonly" value="SASSE" /></td>
    </tr>
    <tr>
      <th class="p-r-10" scope="row"><div align="right">Number</div></th>
      <td><input type="text" name="to" id="number" /></td>
    </tr>
    <tr>
      <th class="p-r-10" scope="row"><div align="right">Message</div></th>
      <td><textarea name="message" id="textarea" cols="40" rows="3"></textarea></td>
    </tr>
    <tr>
      <th colspan="2" scope="row"><input type="submit" name="button" id="button" value="Send SMS" /></th>
    </tr>
  </table>
</form>
<?php
}
require("../lib/footer.inc.php");
?>