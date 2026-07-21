<?php

require_once('database/DatabaseConnection.php');
require_once('Smarty_setup.php');
require_once('include/utils/utils.php');
require_once('data/Tracker.php');
require_once('include/utils/PlatformUtils.class.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/CustomFieldUtil.php');
require_once('modules/notifymanager/notifymanagerutils.php');


global $app_strings;
global $mod_strings;
global $current_language,$default_charset,$currentModule,$adb;
	if ((!isset ($adb)) || (!$adb)) {
		require_once ('include/database/PearDatabase.php');
	}

global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$modulosDeCampos 	= PlatformUtils::getVisibleEntityModulesData ($adb);
$vistasDisponibles 	= getVistasDisponibles();

//To get Email Template variables -- Pavani
$smarty = new vtigerCRM_smarty;

$smarty->assign("APP", $app_strings);
$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("THEME", $theme);
$smarty->assign("THEME_PATH", $theme_path);

$smarty->assign("MOD", $mod_strings);
$smarty->assign("MODULE", $currentModule);
$smarty->assign("MODULOSDECAMPOS", $modulosDeCampos);
$smarty->assign("VISTASDISPONIBLES", $vistasDisponibles);


$smarty->display("modules/notifymanager/crearNotificacion.tpl");

?>