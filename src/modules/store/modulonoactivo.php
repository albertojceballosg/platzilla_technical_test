<?php
	global $app_strings, $mod_strings, $smarty, $theme;
	if ((!isset ($smarty)) || (!$smarty)) {
		require_once ('Smarty_setup.php');
		$smarty = new vtigerCRM_Smarty ();
	}

	$themePath = "themes/$theme";

	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('APPSIMAGE_PATH', 'storage/appsimages/');
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('IMAGE_PATH', "$themePath/images/");
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('THEME_PATH', "{$themePath}/");
	$smarty->display ('modules/store/ModuloNoActivo.tpl');
