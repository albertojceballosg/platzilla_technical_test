<?php
/**
* Crea Cliente SOAP con vtiger
*
* @author Ultima modificacion por ...
* @copyright Copyright (c) 2013, Timemanagement_
* @version 1.0 17/05/2013 09:41:32
* @filesource
*/

global $Server_Path;
/**
* Variable global. Path donde esta el portal.
* @global integer $Portal_Path
*/
global $Portal_Path;

$plat='time';

$nameplat = explode('.',$_SERVER['HTTP_HOST']);
if ((count($nameplat) > 1) && $nameplat[0] != 'madre') // Para el caso de la madre no se asigna instancia
	$_REQUEST['plat'] = $nameplat[0];
if (isset($_REQUEST['plat'])) {
	$plat = $_REQUEST['plat'];
	
	session_name($plat);
}

//This is the vtiger server path ie., the url to access the vtiger server in browser
//Ex. i access my vtiger as http://mickie:90/vtiger/index.php so i will give as http://mickie:90/vtiger
$Server_Path =  'http://'.$plat.'.timemanagement.es';
// $Server_Path =  'http://'.$plat.'.gen.timelocal.es';

//This is the customer portal path ie., url to access the customer portal in browser 
//Ex. i access my portal as http://mickie:90/customerportal/login.php so i will give as http://mickie:90/customerportal
$Authenticate_Path =  'http://'.$plat.'.timemanagement.es';
// $Authenticate_Path =  'http://'.$plat.'.gen.timelocal.es';

//Give a temporary directory path which is used when we upload attachment
$upload_dir = '/tmp';

//These are the Proxy Settings parameters
$proxy_host = ''; //Host Name of the Proxy
$proxy_port = ''; //Port Number of the Proxy
$proxy_username = ''; //User Name of the Proxy
$proxy_password = ''; //Password of the Proxy

//The character set to be used as character encoding for all soap requests
$default_charset = 'UTF-8';  //'ISO-8859-1';

$default_language = 'es_es';

$languages = Array('es_es'=>'Espa�ol','en_us'=>'English','pt_pt'=>'Portugu�s');
/**
* Incluye Libreria NuSoap
*/
// require_once 'include/nusoap/lib/nusoap.php';
require_once('customerportal/nusoap/lib/nusoap.php');

/**
* Variable global. Path del servidor al que conecta
* @global integer $Server_Path
*/
global $Server_Path;
/**
* Variable global. Objeto cliente SOAP
* @global integer $client
*/
global $client;
// $client = new nusoap_client($Server_Path."/vtigerservice.php?service=customerportal&plat_customer=".$plat, false, $proxy_host, $proxy_port, $proxy_username, $proxy_password);
$client = new soapclient2($Server_Path."/vtigerservice.php?service=customerportal&plat_customer=".$plat, false, $proxy_host, $proxy_port, $proxy_username, $proxy_password);
$client->soap_defencoding = $default_charset;

define('_WEBSERVICE_USER','dpolo@timemanagement.es');
define('_WEBSERVICE_PASS','vvvcjad3');

class soapService {

	public $_soapLogin; 

	public function soapLogin(){
		global $plat,$client;
		if(!$this->_soapLogin->id && !$this->_soapLogin->sessionid){
			$params = array('user_name' =>	 	_WEBSERVICE_USER,
							'user_password'=>	_WEBSERVICE_PASS,
							'version' => 		"5.1.0");
			$result = $client->call('authenticate_user', $params,'','');
			if(is_array($result[0]) && !empty($result[0])){
				$this->_soapLogin->id=$result[0]['id'];
				$this->_soapLogin->sessionid=$result[0]['sessionid'];
			}
		}
		// echo "<pre>".print_r($result,true)."</pre>";
	}
	
	public function soapRequest($function='',$data){
		global $plat,$client;
		$this->soapLogin();
		$params = array('data'			=> $data,
						'customerid'	=> $this->_soapLogin->id,
						'sessionid'		=> $this->_soapLogin->sessionid,
					);
		$ret=$client->call($function, $params, $Server_Path, $Server_Path);
		return $ret;
	}
	
	public function getPlatStatus(){
		global $plat,$client;
		$data=array('plat'=>$plat);
		$return=$this->soapRequest('getPlatStatus',$data);
		$return[0]['periodo_prueba']=(array)json_decode(html_entity_decode($return[0]['periodo_prueba']));
		// echo "<pre>".print_r($return,true)."</pre>";
		return $return[0];
	}
	
	function email($email,$html,$asunto='Registro de Lead'){
		$mail_host=$_SERVER["HTTP_HOST"];
		$mail="info@platzilla.com";
		$de_mail="Platzilla <".$mail.">";
		
		$cuerpo="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
					<html xmlns=\"http://www.w3.org/1999/xhtml\">
					<head>
					<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
					<link href=\"https://(fonts.)?googleapis.com/css?family=Questrial\" rel=\"stylesheet\" type=\"text/css\">
					</head>
						<body>
						".$html."
						</body>
					</html>";

		$header="From: ".$de_mail." \r\n";
		$header.="Reply-To: ".$de_mail."\r\n";
		$header.="Mime-Version: 1.0\r\n";
		$header.="Content-Type: text/html; charset=utf-8\r\n";
		$headers.="Return-Path:<".$mail.">\r\n";
		$header.="Content-Transfer-Encoding: 7bit\r\n";
		$header.="X-Mailer: PHP/".phpversion()."\r\n";
				
		//$cuerpo=wordwrap($cuerpo);
		foreach($email as $to){
			mail($to,$asunto,$cuerpo,$header, "-f$mail");
		}
		
	}
}

?>

