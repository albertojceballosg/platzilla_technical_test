<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $app_strings, $current_language, $mod_strings, $theme;

	$themePath    = "themes/$theme";
	$applications = StoreUtils::getCatalogApplications ();
	unset ($_SESSION ['cart']);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('ACTION_NAME', 'pricing');
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('APPLICATIONS', $applications);
	$smarty->assign ('APPSIMAGE_PATH', 'storage/appsimages');
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('IMAGE_PATH', "$themePath/images/");
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('THEME_PATH', "{$themePath}/");
	$smarty->display ('modules/store/SignUp.tpl');
