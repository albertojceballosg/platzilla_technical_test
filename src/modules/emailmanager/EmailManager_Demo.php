<?php
	global $conex;
	
	$conex = mysql_connect('167.114.39.220:3306','timeuser','Eceptu.2011',true) or die("Error al intentar establecer la conexion de login.");
	$db_login = mysql_select_db('time_plat_gestion',$conex);
	
	

	$fromName = 'FROM NAME';
    $fromMail = 'noreply@timemanagement.es';

	$toName='ALDO G';
	$toMail='aguazzini@timemanagement.es';
	
	$idiomaid = 586;   // 585=español, 586=ingles, 587=portuguez   (ver vtiger_cf_807.picklist_valueid )
	
	$eventcode = 'NOTIFICATION_1_TEST';

	$variablesVector = array(
		'CUSTOM_CUSTOM1' => '***VALOR VAR 1***',
		'CUSTOM_CUSTOM2' => '***VALOR VAR 2***',
		'CUSTOM_CUSTOM3' => '***VALOR VAR 3***',
		'CUSTOM_CUSTOM4' => '***VALOR VAR 4***',
		'CUSTOM_CUSTOM5' => '***VALOR VAR 5***',
		'CUSTOM_CUSTOM6' => '***VALOR VAR 6***',
		'CUSTOM_CUSTOM7' => '***VALOR VAR 7***',
		'CUSTOM_CUSTOM8' => '***VALOR VAR 8***',
		'CUSTOM_CUSTOM9' => '***VALOR VAR 9***',
	);

	$IncludeConex='';

	include_once('EmailManagerSender.inc.php');	
	$returncode = EmailManagerSEND($fromName,$fromMail,$toName,$toMail,$idiomaid,$eventcode,$variablesVector,$IncludeConex);

// RETURN CODE
// 0 = smtp acept email
// 1 = smtp NO acept email
// 2 = not template
// 3 = not conexion
// 4 = not FROM email
// 5 = not TO email
// 6 = not valid idiomaid
// 7 = not EventCode
// 8 = email diferido

var_dump($fromName,$fromMail,$toName,$toMail,$idiomaid,$eventcode,$variablesVector,$IncludeConex);
var_dump('--------------',$returncode);

?>
