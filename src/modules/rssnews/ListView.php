<?php
	require_once ('Smarty_setup.php');
	require_once ('include/DatabaseUtil.php');
	require_once ('include/ListView/ListView.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/admin_widgets/admin_widgets.php');
	require_once ('modules/CustomView/CustomView.php');
	require_once ('modules/notifymanager/lib/NotifyManagerUtils.class.php');
	require_once ('modules/PickList/PickListUtils.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	require_once ('vtlib/Vtiger/Link.php');

	global $adb, $app_strings, $clientView, $current_language, $current_user, $currentModule, $list_max_entries_per_page, $log, $mod_strings, $theme;

	$profileIds = isset ($_REQUEST ['profileids']) ? vtlib_purify ($_REQUEST ['profileids']) : null;

	$profileIds = !empty ($profileIds) ? explode (',', $profileIds) : null;

	if ($_SESSION ['esInstancia'] == true) {
		try {
			StoreUtils::validateInstanceModule ($_SESSION ['platInstancia'], $currentModule);
		} catch (Exception $e) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MENSAJE', $e->getMessage ());
			$smarty->display ('ModuloVencido.tpl');
			exit ();
		}
		if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta!');
			$smarty->display ('instanciaUnverified.tpl');
			exit ();
		}
	}

	if (file_exists ("{$_SESSION ['plat']}/modules/{$currentModule}/{$currentModule}.php")) {
		checkFileAccessForInclusion ("{$_SESSION ['plat']}/modules/{$currentModule}/{$currentModule}.php");
		require_once ("{$_SESSION ['plat']}/modules/{$currentModule}/{$currentModule}.php");
	} else {
		checkFileAccessForInclusion ("modules/{$currentModule}/{$currentModule}.php");
		require_once ("modules/{$currentModule}/{$currentModule}.php");
	}

	$adbBak = clone $adb;

	$category    = getParentTab ();
	$toolButtons = isset ($tool_buttons) ? $tool_buttons : Button_Check ($currentModule);

	// Se determina si la lista requerida es de una plataforma hija
	list ($viewname, $_REQUEST ['platdb']) = explode ('|', $_REQUEST ['viewname']);
	if ((isset ($_REQUEST ['platdb'])) && (!empty ($_REQUEST ['platdb'])) && (determinarPermisosModuloHijo ($_REQUEST ['platdb'], $_REQUEST ['module'], 'view'))) {
		unset ($adb);
		$adb = conectaPlataformaHija ($_REQUEST ['platdb']);
	}

	// Custom View
	$customView = new CustomView ($currentModule);

	/** @var integer $viewId */
	$viewId = $customView->getViewId ($currentModule, $profileIds);
	if ($viewId == 0) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$customViewHtml = $customView->getCustomViewCombo ($viewId);
	$viewInfo       = $customView->getCustomViewByCvid ($viewId);
	if (method_exists ($customView, 'isPermittedChangeStatus')) {
		$statusDetails = $customView->isPermittedChangeStatus ($viewInfo ['status']);
		$canDelete     = $customView->isPermittedCustomView ($viewId, 'Delete', $currentModule);
		$canEdit       = $customView->isPermittedCustomView ($viewId, 'EditView', $currentModule);
	} else {
		$statusDetails = null;
		$canDelete     = false;
		$canEdit       = false;
	}

	/** @var CRMEntity $focus */
	$focus = new $currentModule ();
	$focus->initSortbyField ($currentModule);

	$listButtons = $focus->getListButtons ($app_strings, $mod_strings);
	if (($clientView) && (isset ($listButtons ['mass_edit']))) {
		unset ($listButtons ['mass_edit']);
	}

	if (ListViewSession::hasViewChanged ($currentModule)) {
		$_SESSION ["{$currentModule}_Order_By"] = '';
	}
	$sortOrder                                = $focus->getSortOrder ();
	$orderBy                                  = $focus->getOrderBy ();
	$_SESSION ["{$currentModule}_Order_By"]   = $orderBy;
	$_SESSION ["{$currentModule}_Sort_Order"] = $sortOrder;

	$queryGenerator = new QueryGenerator ($currentModule, $current_user);
	$queryGenerator->initForCustomViewById ($viewId);
	// Enabling Module Search
	$urlString = '';
	if ($_REQUEST ['query'] == 'true') {
		$queryGenerator->addUserSearchConditions ($_REQUEST);
		$ustring = getSearchURL ($_REQUEST);
		$urlString .= "&query=true{$ustring}";
	}
	$listQuery = $queryGenerator->getQuery ();
	$where     = $queryGenerator->getConditionalWhere ();
	if ((isset ($where)) && (!empty ($where))) {
		$_SESSION ['export_where'] = $where;
	} else {
		unset ($_SESSION ['export_where']);
	}
	// Sorting
	if (!empty ($orderBy)) {
		if ($orderBy == 'smownerid') {
			$listQuery .= " ORDER BY user_name {$sortOrder}";
		} else if ((isset ($focus->force_column_order)) && ($focus->force_column_order) && (isset ($focus->special_order))) {
			$listQuery .= " ORDER BY {$focus->special_order} {$sortOrder}";
		} else {
			$tableName = getTableNameForField ($currentModule, $orderBy);
			$tableName = !empty ($tablename) ? "{$tablename}." : '';
			$listQuery .= " ORDER BY {$tableName}{$orderBy} {$sortOrder}";
		}
	}
	// Postgres 8 fixes
	if ($adb->dbType == 'pgsql') {
		$listQuery = fixPostgresQuery ($listQuery, $log, 0);
	}

	// Execute query
	if (PerformancePrefs::getBoolean ('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true) {
		$result   = $adb->query (mkCountQuery ($listQuery));
		$noofrows = $adb->query_result ($result, 0, 'count');
	} else {
		$noofrows = null;
	}
	$start      = ListViewSession::getRequestCurrentPage ($currentModule, $listQuery, $viewId, (isset ($_REQUEST ['query']) && $_REQUEST ['query'] == 'true'));
	$navigation = VT_getSimpleNavigationValues ($start, $list_max_entries_per_page, $noofrows);
	$limit      = (($start - 1) * $list_max_entries_per_page);
	if ($adb->dbType == 'pgsql') {
		$result = $adb->query ($listQuery . " OFFSET {$limit} LIMIT {$list_max_entries_per_page}");
	} else {
		$result = $adb->query ($listQuery . " LIMIT {$limit}, {$list_max_entries_per_page}");
	}
	$recordListRangeMsg = getRecordRangeMessage ($result, $limit, $noofrows);

	// Navigation
	$navigationOutput = getTableHeaderSimpleNavigation ($navigation, $urlString, $currentModule, 'index', $viewId);

	$controller = new ListViewController ($adb, $current_user, $queryGenerator);
	$skipAction = isset ($skipAction) ? $skipAction : false;
	$header     = $controller->getListViewHeader ($focus, $currentModule, $urlString, $sortOrder, $orderBy, $skipAction);
	$entries    = $controller->getListViewEntries ($focus, $currentModule, $result, $navigation, $skipAction);
	$search     = $controller->getBasicSearchFieldInfoList ();

	// Filters
	$allowFilters = determinarFiltroListasModulo ($currentModule);
	if ($allowFilters) {
		$customView->getCvColumnListSQL ($viewId);
		$columnslist = $customView->getColumnsListByCvid ($viewId);
		$campos      = array_keys ($customView->list_fields);
		$filters     = array ();
		$n           = count ($customView->list_fields);
		for ($i = 0; $i < $n; $i++) {
			$filtro = $customView->list_fields [ $campos [ $i ] ];
			foreach ($filtro as $clave => $valor) {
				$filters [] = getFiltersValues ($valor, $currentModule);
			}
		}
	}

	// Module Search
	$alphabetical = AlphabeticalSearch ($currentModule, 'index', $focus->def_basicsearch_col, 'true', 'basic', '', '', '', '', $viewId);
	$fieldnames   = $controller->getAdvancedSearchOptionString ();
	$criteria     = getcriteria_options ();
	ListViewSession::setSessionQuery ($currentModule, $listQuery, $viewId);

	// Gather the custom link information to display
	$customLinksArguments = array ('MODULE' => $currentModule, 'ACTION' => vtlib_purify ($_REQUEST ['action']), 'CATEGORY' => $category);

	// Valida que exista la relación con el módulo Activities
	$rel = isPresentRelatedLists ($currentModule);
	if (($rel) && (is_array ($rel)) && (in_array ('Activities', $rel))) {
		$activities = '1';
	} else {
		$activities = '0';
	}

	// [ TT11468 ] Motor Widget  - Johana Romero - 26/01/2017
	$widget      = new Widgets ();
	$widgetsData = array ();
	$result      = $adb->pquery ('SELECT * FROM vtiger_widgets WHERE fld_module=? AND estatus=?', array ($currentModule, 1));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$sql = null;
			if ((!empty ($row ['campofecha'])) && ($row ['tiempofecha'])) {
				$moduleId  = $widget->getTabId ($row ['fld_module']);
				$dates     = $widget->getDateBetween ($row ['tiempofecha']);
				$arguments = array (
					'campofecha'     => $row ['campofecha'],
					'fechadesde'     => $dates ['fechaDesde'],
					'fechahasta'     => $dates ['fechaHasta'],
					'fieldgrouping'  => $row ['fieldgrouping'],
					'filterfield'    => $row ['filterfield'],
					'filternumber'   => $row ['filternumber'],
					'fieldoperation' => $row ['fieldoperation'],
					'operation'      => $row ['operation'],
					'orderfilter'    => $row ['orderfilter'],
				);
				$sql       = construirSqlPrimario ($arguments, $moduleId);
			}
			$sql         = html_entity_decode ((!empty ($sql) ? $sql : $row ['sqlprimario']), ENT_QUOTES, 'UTF-8');
			$valueResult = $adb->query ($sql);
			$value       = ($valueResult) && ($adb->num_rows ($valueResult) > 0) ? $adb->fetchByAssoc ($valueResult, -1, false) : null;
			$colorData   = explode ('-', $row ['color']);

			$widgetsData [] = array (
				'color'      => $row ['color'],
				'colorValue' => $colorData [0],
				'icono'      => $row ['icono'],
				'texto'      => $row ['texto'],
				'valor'      => $value,
				'widgetid'   => $row ['widgetid'],
			);
		}
	}
	// Fin widgets

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('ACTIVE_APPLICATIONS', $applications);
	$smarty->assign ('ALLSELECTEDIDS', vtlib_purify ($_REQUEST ['allselobjs']));
	$smarty->assign ('ALPHABETICAL', $alphabetical);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('AVALABLE_FIELDS', getMergeFields ($currentModule, 'available_fields'));
	$smarty->assign ('AVAILABLE_PICKLISTS', getUserFldArray ($currentModule, $current_user->column_fields ['roleid']));
	$smarty->assign ('BUTTONS', $listButtons);
	$smarty->assign ('CATEGORY', $category);
	$smarty->assign ('CHANGE_GROUP_OWNER', getGroupslist ());
	$smarty->assign ('CHANGE_OWNER', getUserslist ());
	$smarty->assign ('CHECK', $toolButtons);
	$smarty->assign ('CRITERIA', $criteria);
	$smarty->assign ('CURRENT_PAGE_BOXES', implode (array_keys ($entries), ';'));
	$smarty->assign ('CUSTOM_BUTTONS', PlatformUtils::getCustomButtons ($adb, $currentModule, 'DetailView', $_REQUEST));
	$smarty->assign ('CUSTOM_LINKS', Vtiger_Link::getAllByType (getTabid ($currentModule), array ('LISTVIEWBASIC', 'LISTVIEW'), $customLinksArguments));
	$smarty->assign ('CUSTOM_MODULE', $focus->IsCustomModule);
	$smarty->assign ('CUSTOMVIEW_OPTION', $customViewHtml);
	$smarty->assign ('CUSTOMVIEW_PERMISSION', $statusDetails);
	$smarty->assign ('CV_DELETE_PERMIT', $canDelete);
	$smarty->assign ('CV_EDIT_PERMIT', $canEdit);
	$smarty->assign ('DETAILWIDGET', $widgetsData);
	$smarty->assign ('FIELDNAMES', $fieldnames);
	$smarty->assign ('FIELDS_TO_MERGE', getMergeFields ($currentModule, 'fileds_to_merge'));
	$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign ('IS_ADMIN', is_admin ($current_user));
	$smarty->assign ('IS_REL_ACTIVITIES', $activities);
	$smarty->assign ('LISTENTITY', $entries);
	$smarty->assign ('LISTHEADER', $header);
	$smarty->assign ('MAX_RECORDS', $list_max_entries_per_page);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('NAVIGATION', $navigationOutput);
	$smarty->assign ('NOTIFICATIONS', NotifyManagerUtils::getNotifications ($_SESSION ['platInstancia'], $currentModule, 'ListView'));
	$smarty->assign ('PROFILE_IDS', $profileIds);
	$smarty->assign ('recordListRange', $recordListRangeMsg);
	$smarty->assign ('SEARCHLISTHEADER', $search);
	$smarty->assign ('SELECTEDIDS', vtlib_purify ($_REQUEST ['selobjs']));
	$smarty->assign ('SINGLE_MOD', getTranslatedString ('SINGLE_' . $currentModule));
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('TOTALRECORD', $noofrows);
	$smarty->assign ('VIEWID', $viewId);

	if ($viewInfo ['viewname'] == 'All') {
		$smarty->assign ('ALL', 'All');
	}
	if ($allowFilters) {
		$smarty->assign ('BUILD_SEARCH', buildFilterSearch ($viewId, $currentModule));
		$smarty->assign ('FILTERS', 1);
		$smarty->assign ('LISTFILTERS', $filters);
	}
	if ($clientView) {
		$smarty->assign ('CALCULATOR_DISPLAY', 'false');
		$smarty->assign ('CALENDAR_DISPLAY', 'false');
		$smarty->assign ('CLIENT_VIEW', 'true');
		$smarty->assign ('CHAT_DISPLAY', 'false');
		$smarty->assign ('LAST_VIEWED', 'false');
		$smarty->assign ('WORLD_CLOCK_DISPLAY', 'false');
	}
	if (isset ($_REQUEST ['MENSAJE'])) {
		$smarty->assign ('MENSAJE', $_REQUEST ['MENSAJE']);
		$smarty->assign ('TIPO_MENSAJE', $_REQUEST ['TIPO_MENSAJE']);
	}
	if (!empty ($urlString)) {
		$smarty->assign ('SEARCH_URL', $urlString);
	}
	if (isset ($_REQUEST ['ajax']) && $_REQUEST ['ajax'] != '') {
		$smarty->display ('ListViewEntries.tpl');
	} else if (isset ($_REQUEST ['modeview']) && $_REQUEST ['modeview'] != '' && $_REQUEST ['modeview'] == 'viewkanban') {
		$smarty->display ('ListViewKanban.tpl');
	} else {
		$smarty->display ("modules/{$currentModule}/ListView.tpl");
	}

	unset ($adb);
	$adb = clone $adbBak;
