<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/GanttTaskUtils.class.php');
	require_once ('include/utils/KanbanTaskUtils.class.php');
	require_once ('include/utils/KanbanViewUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	/**
	 * Archivo que se encarga de renderizar la lista de vista Kanban configuradas
	 * desde el configurador Kanban
	 *
	 * @var PearDatabase $adb
	 * @var string $app_strings
	 * @var string $current_user
	 * @var string $mod_string
	 * @var string $theme
	 */

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$selectedTab = PlatzillaUtils::purify ($_GET, 'tab', 'kanban-record');
	$keyword     = PlatzillaUtils::purify ($_GET, 'keyword');
	$page        = PlatzillaUtils::purify ($_GET, 'page');

	$isInstance       = !empty ($_SESSION ['platInstancia']);
	$availableModules = KanbanViewUtils::getAvailableModules ($adb, $current_user, $current_user->is_admin);

	if (($isInstance) && (!empty ($availableModules))) {
		$moduleNames = array ();
		foreach ($availableModules as $availableModule) {
			$moduleNames [] = $availableModule ['name'];
		}
	} else {
		$moduleNames = null;
	}

	$smarty->assign ('DATA', KanbanViewUtils::getKanbanViews ($adb, $current_user, $keyword, $page, $moduleNames));
	$smarty->assign ('GANTT_TASKS', GanttTaskUtils::fetchGantts ($adb));
	$smarty->assign ('KANBAN_TASKS', KanbanTaskUtils::fetchKanbans ($adb));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('SEARCH_KEYWORD', $keyword);
	$smarty->assign ('SELECTED_TAB', $selectedTab);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('Settings/KanbanViewListView.tpl');
