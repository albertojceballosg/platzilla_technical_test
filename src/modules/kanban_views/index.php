<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/KanbanViewUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	/**
	 * Archivo que se encarga de renderizar la vista Kanban configurada
	 * desde el configurador Kanban y que contiene el tablero y las tarjetas (registros del módulo)
	 *
	 * @var PearDatabase $adb
	 * @var string $app_strings
	 * @var string $current_user
	 * @var string $mod_string
	 * @var string $theme
	 */
	// Agregado por EB para integrar BUGSNAG - 2020512
	global $site_URL;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
	$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
	$bugsnag->setAppVersion('1.0.0');
	if ($site_URL == 'https://apphome.platzillatest.com/') {
		$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
	} else if ($site_URL == 'https://app.platzilla.com/') {
		$bugsnag->setReleaseStage('https://app.platzilla.com/');
	} else {
		$bugsnag->setReleaseStage($site_URL);
	}
	$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
	// Agregado por EB para integrar BUGSNAG - 20200512

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$smarty = new vtigerCRM_Smarty ();

	$view        = PlatzillaUtils::purify ($_REQUEST, 'viewKanban');
	$codeApp     = PlatzillaUtils::purify ($_REQUEST, 'codeApp');
	$codeElement = PlatzillaUtils::purify ($_REQUEST, 'codeElement');

	$fieldName  = PlatzillaUtils::purify ($_REQUEST, 'fieldname');
	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'modulename');

	$recordsModule = array ();
	if (!empty($view)) {
		$recordsModule    = KanbanViewUtils::getRecordsModuleView ($adb, $moduleName, $current_user, $view);
		$rulesColors      = KanbanViewUtils::getKanbanViewRules ($adb, $view, $fieldName, $moduleName, $current_user);
		$viewKanban       = KanbanViewUtils::getKanbanViewById ($adb, $view);
		$availableModules = KanbanViewUtils::getAvailableModulesByAppView ($adb, $current_user, $codeApp, $current_user->is_admin);
		$availableKanban  = KanbanViewUtils::getAvailableViews ($adb, $codeElement, $codeApp);
	} else {
		$availableModules = null;
		$availableKanban  = null;
	}

	$smarty->assign ('APPLICATIONS', KanbanViewUtils::getAvailableApplicationsView ($adb, $current_user));
	$smarty->assign ('AVAIABLE_MODULES', $availableModules);
	$smarty->assign ('AVAIABLE_KANBAN', $availableKanban);
	$smarty->assign ('CODE_ELEMENT', $codeElement);
	$smarty->assign ('CODE_APP', $codeApp);
	$smarty->assign ('VIEWID', $view);
	$smarty->assign ('MODULE', 'kanban_views');
	$smarty->assign ('ITEMVIEWS', $recordsModule);
	$smarty->assign ('VIEWNAME', $viewKanban->getLabel ());
	$smarty->assign ('MODULENAME', $moduleName);
	$smarty->assign ('FIELDNAME', $fieldName);
	$smarty->assign ('RULECOLORS', $rulesColors);
	$smarty->assign ('REQUEST_FROM', 'kanban');
	$smarty->display ('modules/kanban_views/index.tpl');
