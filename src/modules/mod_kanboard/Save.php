<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('Smarty_setup.php');
require_once('user_privileges/default_module_view.php');

global $mod_strings, $palt, $app_strings, $currentModule, $current_user, $theme, $singlepane_view;

if($_REQUEST['save']=="estadotarea" && $_REQUEST['ticketid']!=''){
	/*
	$sql="UPDATE `vtiger_troubletickets` SET 
				`status` = '".$_REQUEST['tipo']."'
				where `ticketid` = '".$_REQUEST['ticketid']."'";
	*/
	$sql="UPDATE `vtiger_todotasks` SET 
				`status_todotasks` = '".$_REQUEST['tipo']."'
				where `todotasksid` = '".$_REQUEST['ticketid']."'";

	$adb->query($sql);
	exit;
}
if($_REQUEST['save']=="columnas"){

	global $platPrincipal;
	if(is_array($_REQUEST['label']) && !empty($_REQUEST['label'])){
		$labels=implode(',',$_REQUEST['label']);
		$textos=implode(',',$_REQUEST['text']);
		/*
		$sql="UPDATE `vtiger_variables` SET 
				`value` = '".$labels."'
				where `tabid` = '13' and `varname`='field_type';";
		$adb->query($sql);
		$sql="UPDATE `vtiger_variables` SET 
				`value` = '".$textos."'
				where `tabid` = '13' and `varname`='field_label';";
				*/
		$sql="UPDATE `vtiger_variables` SET 
				`value` = '".$labels."'
				where `tabid` = '78' and `varname`='status_todotasks';";
		$adb->query($sql);
		$sql="UPDATE `vtiger_variables` SET 
				`value` = '".$textos."'
				where `tabid` = '78' and `varname`='label_status_todotasks';";
		$adb->query($sql);

		// Seleccionamos la ruta del archivo de lenguaje
		if ($plat != $platPrincipal){
			$LanguageFile = $plat."/modules/mod_kanboard/language/es_es.lang.php";
		}else{
			$LanguageFile = "modules/mod_kanboard/language/es_es.lang.php";
		}

		// Incluimos el archivo de lenguaje para tomar el array $mod_strings
		require_once("$LanguageFile");


		// Substituimos etiquetas dentro de $mod_strings
		$etiquetaBackend = $_REQUEST['label'];
		$etiquetaEspanol = $_REQUEST['text'];
		foreach ($etiquetaBackend as $keyE => $valE) {
			$mod_strings[$valE] = $etiquetaEspanol[$keyE];
		}

		//Abro el archivo en modo escritura y reemplazamos los labels con los nuevos valores
		$file = fopen($LanguageFile,'w');
		if ($file) {
			fwrite($file,'<?php'."\r\n".'$mod_strings = Array('."\r\n");
			foreach($mod_strings as $keyME => $valME) {
				$line = "'".$keyME."'=>'".$valME."',\r\n";
				fwrite($file,$line);
			}
			fwrite($file,");\r\n?>");
			fclose($file);
		}
		
		/*
		//Abro el archivo
		$file = fopen($LanguageFile,'w');
		if ($file) {
			fwrite($file,'<?php'."\r\n".'$mod_strings = Array('."\r\n");
			fwrite($file,"'kanban' => 'Kanban', \r\n");
			fwrite($file,"'LBL_DEVELOPMENT' => 'Desarrollador', \r\n");
			fwrite($file,"'LBL_PROJECT' => 'Proyecto', \r\n");
			foreach($_REQUEST['label'] as $key => $value) {
				$line = "'".$value."'=>'".$_REQUEST['text'][$key]."',\r\n";
				fwrite($file,$line);
			}
			fwrite($file,");\r\n?>");
			fclose($file);
		}
		*/
		
		header('location: index.php?module=mod_kanboard&action=index');
	}
	exit;
}
//TICKET_OPEN,TICKET_ASSIGNED,TICKET_TO_VALIDATE,TICKET_PENDING_CONFIRMATION_OF_CUSTOMER,TICKET_VERIFIED,TICKET_ACCEPTED
?>