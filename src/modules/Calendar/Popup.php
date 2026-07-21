<?php
	require_once ('Smarty_setup.php');
	require_once ('include/ListView/ListView.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Calendar/Activity.php');

	global $adb, $app_strings, $currentModule, $mod_strings, $theme;

	global $app_strings, $default_charset;
	global $currentModule, $current_user;

	$ajax = PlatzillaUtils::purify ($_REQUEST, 'ajax');
	$callback     = PlatzillaUtils::purify ($_REQUEST, 'callback');
	$ctrlId       = PlatzillaUtils::purify ($_REQUEST, 'ctrlid');
	$ctrlName     = PlatzillaUtils::purify ($_REQUEST, 'ctrlname');
	$esPatron     = PlatzillaUtils::purify ($_REQUEST, 'es_patron');
	$forField     = PlatzillaUtils::purify ($_REQUEST, 'forfield');
	$form         = PlatzillaUtils::purify ($_REQUEST, 'form');
	$mainTab      = PlatzillaUtils::purify ($_REQUEST, 'maintab');
	$parentModule = PlatzillaUtils::purify ($_REQUEST, 'parent_module');
	$popupMode    = PlatzillaUtils::purify ($_REQUEST, 'popupmode');
	$popupType    = PlatzillaUtils::purify ($_REQUEST, 'popuptype');
	$query        = PlatzillaUtils::purify ($_REQUEST, 'query');
	$recordId     = PlatzillaUtils::purify ($_REQUEST, 'recordid');
	$returnAction = PlatzillaUtils::purify ($_REQUEST, 'return_action');
	$returnModule = PlatzillaUtils::purify ($_REQUEST, 'return_module');
	$select       = PlatzillaUtils::purify ($_REQUEST, 'select');
	$srcModule    = PlatzillaUtils::purify ($_REQUEST, 'srcmodule');
	$start        = PlatzillaUtils::purify ($_REQUEST, 'start');

	$maxEntriesPerPage = 25;

	$focus             = new Activity ();
	$focus->search_fields = array (
		'Subject'   => array ('activity', 'subject'),
	);
	$focus->list_mode  = 'search';
	$focus->popup_type = $popupType;
	if ($form == 'vtlibPopupView') {
		vtlib_setup_modulevars ($currentModule, $focus);
	}
	$focus->initSortbyField ($currentModule);

	$url = '';
	if (!empty ($mainTab)) {
		$url = "&maintab={$mainTab}";
	}
	if ((!empty ($popupMode)) && (!empty ($callback))) {
		$url = "&popupmode={$popupMode}&callback={$callback}";
	}
	$alphabetical = AlphabeticalSearch ($currentModule, 'Popup', $focus->def_basicsearch_col, 'true', 'basic', $popupType, '', '', $url);

	$sql       = getListQuery ($currentModule);
	$urlString = '';
	$where     = null;
	if ($query == 'true') {
		list ($where, $ustring) = explode ('#@@#', getWhereCondition ($currentModule));
		$urlString .= "&query=true{$ustring}";
	}
	if ((isset ($where)) && (!empty ($where))) {
		$sql .= " AND {$where}";
	}
	$sortOrder = $focus->getSortOrder ();
	$orderBy   = $focus->getOrderBy ();
	if (!empty ($orderBy)) {
		$sql .= " ORDER BY {$orderBy} {$sortOrder}";
	}
	if (PerformancePrefs::getBoolean ('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true) {
		$noofrows = $adb->query_result ($adb->query (mkCountQuery ($sql)), 0, 'count');
	} else {
		$noofrows = null;
	}
	if (!empty ($start)) {
		if ($start == 'last') {
			$noofrows = $adb->query_result ($adb->query (mkCountQuery ($sql)), 0, 'count');
			if ($noofrows > 0) {
				$start = ceil ($noofrows / $maxEntriesPerPage);
			}
		}
		if (!is_numeric ($start)) {
			$start = 1;
		} else if ($start < 1) {
			$start = 1;
		}
		$start = ceil ($start);
	} else {
		$start = 1;
	}
	$startRecord = (($start - 1) * $maxEntriesPerPage);
	$sql .= " LIMIT {$startRecord}, {$maxEntriesPerPage}";
	$result = $adb->query ($sql);

	$urlString .= "&popuptype={$popupType}";
	if ($select == 'enable') {
		$urlString .= '&select=enable';
	}
	if (!empty ($returnModule)) {
		$urlString .= "&return_module={$returnModule}";
	}
	$navigation           = VT_getSimpleNavigationValues ($start, $maxEntriesPerPage, $noofrows);
	$listViewHeaderSearch = getSearchListHeaderValues ($focus, $currentModule, '', $sortOrder, $orderBy);
	$listViewHeader       = getSearchListViewHeader ($focus, $currentModule, $urlString, $sortOrder, $orderBy);
	$listViewEntries      = getSearchListViewEntries ($focus, $currentModule, $result, $navigation, $form);
	$navigationOutput     = getTableHeaderSimpleNavigation ($navigation, $urlString, $currentModule, 'Popup');

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('ALPHABETICAL', $alphabetical);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CATEGORY', getParentTab ());
	$smarty->assign ('CTRLID', $ctrlId);
	$smarty->assign ('CTRLNAME', $ctrlName);
	$smarty->assign ('HEADERCOUNT', (count ($listViewHeader) + 1));
	$smarty->assign ('LISTENTITY', $listViewEntries);
	$smarty->assign ('LISTHEADER', $listViewHeader);
	$smarty->assign ('MAINTAB', $mainTab);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('NAVIGATION', $navigationOutput);
	$smarty->assign ('PARENT_MODULE', $parentModule);
	$smarty->assign ('POPUPTYPE', $popupType);
	$smarty->assign ('SEARCHLISTHEADER', $listViewHeaderSearch);
	$smarty->assign ('SINGLE_MOD', $currentModule);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('THEME_PATH', "themes/{$theme}/");
	if ((!empty ($popupMode)) && (!empty ($callback))) {
		$smarty->assign ('CALLBACK', $callback);
		$smarty->assign ('POPUPMODE', $popupMode);
	}
	if (!empty ($returnAction)) {
		$smarty->assign ('RETURN_ACTION', $returnAction);
	}
	if (!empty ($returnModule)) {
		$smarty->assign ('RETURN_MODULE', $returnModule);
	}
	if (!empty ($select)) {
		$smarty->assign ('SELECT', 'enable');
	}
	if (!empty ($ajax)) {
		$smarty->display ('PopupContents.tpl');
	} else {
		$smarty->display ('Popup.tpl');
	}
