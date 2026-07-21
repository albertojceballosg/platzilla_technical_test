<?php
// ==========================
// 1. Inicialización y dependencias
// ==========================
// Se incluyen todas las clases y utilidades necesarias para la vista de lista (ListView), manejo de base de datos, controladores, permisos, etc.
	require_once ('Smarty_setup.php');
	require_once ('include/DatabaseUtil.php');
	require_once ('include/ListView/ListView.php');
	require_once ('include/platzilla/Data/GraphicManager.php');
	require_once ('include/platzilla/Managers/ModuleEditPermissionManager.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/platzilla/Managers/ViewManager.php');
	require_once ('include/platzilla/Objects/NotificationInterface.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CalendarViewUtils.class.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/KanbanTaskUtils.class.php');
	require_once ('include/utils/KanbanViewUtils.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/admin_widgets/admin_widgets.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/CustomView/CustomView.php');
	require_once ('modules/how_use/lib/HowToUseHelper.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/PickList/PickListUtils.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	require_once ('vtlib/Vtiger/Link.php');

	global $adb, $app_strings, $clientView, $current_language, $current_user, $currentModule, $list_max_entries_per_page, $log, $mod_strings, $theme, $site_URL;
	// Asegura que el objeto global $smarty esté correctamente instanciado
	global $smarty;
	if (!isset($smarty) || !$smarty instanceof vtigerCRM_Smarty) {
		$smarty = new vtigerCRM_Smarty();
	}
	setBugSnag ($site_URL);

	$homeViewId = (!isset($homeViewId)) ? null : $homeViewId;
	if (isset($isHomeTab) && $isHomeTab) {
		$currentModule = $moduleTab;
		$viewId        = $homeViewId;
	}

	$action         = isset ($_REQUEST ['action']) ? vtlib_purify ($_REQUEST ['action']) : null;
	$mode           = isset ($_REQUEST ['mode']) ? vtlib_purify ($_REQUEST ['mode']) : null;
	$profileIds     = isset ($_REQUEST ['profileids']) ? vtlib_purify ($_REQUEST ['profileids']) : null;
	$kanbanViewId   = isset ($_REQUEST ['kview']) ? vtlib_purify ($_REQUEST ['kview']) : null;
	$kanbanField    = isset ($_REQUEST ['kfieldname']) ? vtlib_purify ($_REQUEST ['kfieldname']) : null;
	$listViewTab    = isset ($_REQUEST ['tab']) ? vtlib_purify ($_REQUEST ['tab']) : null;
	$profileIds     = !empty ($profileIds) ? explode (',', $profileIds) : null;
	$idModeSelected = isset ($_REQUEST ['howusename']) ? vtlib_purify ($_REQUEST ['howusename']) : null;
	$homeViewId     = isset ($_REQUEST ['homeViewId']) ? vtlib_purify ($_REQUEST ['homeViewId']) : $homeViewId;
	$isHomeTabAjax  = isset ($_REQUEST ['isHomeTab']) ? vtlib_purify ($_REQUEST ['isHomeTab']) : null;
	$tabHomeId      = isset ($_REQUEST ['idTab']) ? vtlib_purify ($_REQUEST ['idTab']) : null;
	
	$isInstance = !empty ($_SESSION ['platInstancia']);

	// ==========================
	// 2. Manejo de subscripciones y acceso a módulos
	// ==========================
	// Si la plataforma es multi-instancia, se verifica que la suscripción esté activa y 
	// que el módulo esté instalado y habilitado.	
	if ($isInstance) {
		if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta!');
			$smarty->display ('instanciaUnverified.tpl');
			exit ();
		}

		$masterAdb          = AdbManager::getInstance ()->getMasterAdb ();
		$subscription       = null;
		$moduleSubscription = null;
		try {
			$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
			$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
			if ((empty ($subscription)) || ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE)) {
				throw new Exception ('Tu suscripción se encuentra inactiva');
			}

			$moduleSubscription = $psm->fetchModuleSubscription ($_SESSION ['platInstancia'], $currentModule);
			if (empty ($moduleSubscription)) {
				throw new Exception ('El módulo no se encuentra instalado. Te invitamos a instalar una aplicación que lo contenga');
			} else if ($moduleSubscription->getStatus () == ModuleSubscription::STATUS_INACTIVE) {
				throw new Exception ('El módulo se encuentra vencido. Te invitamos a renovar el servicio');
			}
		} catch (Exception $e) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('LABEL', 'Tu suscripción');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('URL', 'index.php?module=Home&action=ViewSubscriptionDetails&tab=subscription');
			$smarty->display ('Message.tpl');
			exit ();
		}

		$applications     = PlatformUtils::getApplicationsByUserRole ($adb, $current_user->column_fields ['roleid'], $currentModule);
		$canCreateRecords = ($moduleSubscription->getMaxRecords () == -1) || ($moduleSubscription->getMaxRecords () > $moduleSubscription->getTotalRecords ());
	} else {
		$applications     = PlatformUtils::getApplicationsByModuleName ($adb, $currentModule);
		$canCreateRecords = true;
	}

	if (file_exists ("{$_SESSION ['plat']}/modules/{$currentModule}/{$currentModule}.php")) {
		checkFileAccessForInclusion ("{$_SESSION ['plat']}/modules/{$currentModule}/{$currentModule}.php");
		require_once ("{$_SESSION ['plat']}/modules/{$currentModule}/{$currentModule}.php");
	} else {
		checkFileAccessForInclusion ("modules/{$currentModule}/{$currentModule}.php");
		require_once ("modules/{$currentModule}/{$currentModule}.php");
	}

	$category    = getParentTab ();
	$toolButtons = isset ($tool_buttons) ? $tool_buttons : Button_Check ($currentModule);

	// How to Use Platzilla
	$isHowUseDiscontinued = true;
	try {
		if ($isHowUseDiscontinued) {
			throw new Exception ('HOW TO USE IS DISCONTINUED');
		}
		if (!empty($homeViewId)) {
			throw new Exception ('COMES_FROM_HOME');
		}
		// getting mode from profile
		if (empty ($idModeSelected)) {
			$instanceProfile = ProfilesHowToUseManager::getInstance ($adb)->fetchProfilesHowToUseByCode ('', $currentModule);
			if (!empty ($instanceProfile) && count ($instanceProfile->getHowToUse ())) {
				$idModeSelected = $instanceProfile->getHowToUse()[0]->getId();
			}
		}

		$howToUse = HowToUseHelper::getDefaultMode ($adb, $currentModule, $idModeSelected,'LIST_VIEW');
		if (empty ($howToUse['howUseId'])) {
			throw new Exception ('THERE ARE NO USE MODES TO THIS MODULE');
		}
		$listViewTab    = (empty ($listViewTab)) ? $howToUse['tab'] : $listViewTab;
		$viewId         = (empty ($viewId) && (!isset ($_REQUEST ['viewname']))) ? $howToUse ['viewId'] : $viewId;
		$statusButtons  = $howToUse ['statusButtons'];
		$modesViews     = $howToUse ['relatedView'];
		$idModeSelected = $howToUse ['howUseId'];
		$availableModes = HowToUseHelper::fetchAllHowToUse ($adb, $currentModule, true);
	} catch (Exception $e) {
		$statusButtons  = null;
		$modesViews     = null;
		$availableModes = null;
		if ($e->getMessage () == 'COMES_FROM_HOME') {
			$viewId = $homeViewId;
		}
	}
	// Custom View
	$customView = new CustomView ($currentModule);

	/** @var integer $viewId */
	$viewId = (empty($viewId)) ? $customView->getViewId ($currentModule, $profileIds) : $viewId;

	if ($viewId == 0) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$isAdmin   = is_admin ($current_user);
	$view      = ViewManager::getInstance ($adb)->fetchViewById ($currentModule, $viewId, true);
	$canEdit   = (($isAdmin) || (!empty($view) && $view->getOwner () == $current_user->id)) ? 'yes' : 'no';
	$canDelete = (!empty ($view)) && ($view->getName () != 'All') && ($view->getDefault () != View::DEFAULT_YES) && (($isAdmin) || ($view->getOwner () == $current_user->id));

	$customViewHtml = $customView->getCustomViewCombo ($viewId, true, $profileIds, $modesViews );
	$viewInfo       = $customView->getCustomViewByCvid ($viewId);
	$statusDetails  = method_exists ($customView, 'isPermittedChangeStatus') ? $customView->isPermittedChangeStatus ($viewInfo ['status']) : null;

	/** @var CRMEntity $focus */
	$focus = new $currentModule ();
	$focus->initSortbyField ($currentModule);

	$listButtons = $focus->getListButtons ($app_strings, $mod_strings);
	if (($clientView) && (isset ($listButtons ['mass_edit']))) {
		unset ($listButtons ['mass_edit']);
	}
	// ==========================
	// 3. Construcción y ejecución de la consulta SQL principal
	// ==========================
	// Aquí se arma la consulta principal ($listQuery) y una auxiliar ($listQueryAll) para obtener los registros a mostrar.	
	if (ListViewSession::hasViewChanged ($currentModule)) {
		$_SESSION ["{$currentModule}_Order_By"] = '';
	}
	$sortOrder                                = $focus->getSortOrder ();
	$orderBy                                  = $focus->getOrderBy ();
	$_SESSION ["{$currentModule}_Order_By"]   = $orderBy;
	$_SESSION ["{$currentModule}_Sort_Order"] = $sortOrder;

	$queryGenerator = new QueryGenerator ($currentModule, $current_user);
	$queryGenerator->initForCustomViewById ($viewId);
	
	// Validación: verificar que el viewId pertenece al módulo correcto
	$viewValidation = $adb->pquery("SELECT entitytype FROM vtiger_customview WHERE cvid = ?", array($viewId));
	if ($viewValidation && $adb->num_rows($viewValidation) > 0) {
		$viewEntityType = $adb->query_result($viewValidation, 0, 'entitytype');
		if ($viewEntityType !== $currentModule) {
			// viewId incorrecto detectado, corrigiendo silenciosamente
			// Obtener el viewId correcto directamente de la BD (ignorando sesión y request)
			$correctViewResult = $adb->pquery(
				"SELECT cvid FROM vtiger_customview WHERE entitytype = ? ORDER BY setdefault DESC, viewname ASC LIMIT 1",
				array($currentModule)
			);
			if ($correctViewResult && $adb->num_rows($correctViewResult) > 0) {
				$viewId = $adb->query_result($correctViewResult, 0, 'cvid');
								// Limpiar la sesión contaminada
				$_SESSION['lvs'][$currentModule]['viewname'] = $viewId;
				// Reinicializar el QueryGenerator con el viewId correcto
				$queryGenerator = new QueryGenerator($currentModule, $current_user);
				$queryGenerator->initForCustomViewById($viewId);
			} else {
							}
		}
	}
	
	// Enabling Module Search
	$urlString = '';
	if ($_REQUEST ['query'] == 'true') {
		$queryGenerator->addUserSearchConditions ($_REQUEST);
		$ustring = getSearchURL ($_REQUEST);
		$urlString .= "&query=true{$ustring}";
	}
	$listQuery  = $queryGenerator->getQuery ();
	$where      = $queryGenerator->getConditionalWhere ();
	$whereColor = $customView->getColorFilterByCvid ($viewId);

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
			// BUG DETECTADO: Variable incorrecta $tablename vs $tableName
			$tablePrefix = !empty ($tableName) ? "{$tableName}." : '';
			$orderByClause = "ORDER BY {$tablePrefix}{$orderBy} {$sortOrder}";
			$listQuery .= " {$orderByClause}";
		}
	}

	$listQueryAll = 'SELECT * ' . strstr ($listQuery, strtoupper ('FROM'));
	$resultAll    = $adb->query ($listQueryAll);

	// Postgres 8 fixes
	if ($adb->dbType == 'pgsql') {
		$listQuery    = fixPostgresQuery ($listQuery, $log, 0);
		$listQueryAll = fixPostgresQuery ($listQueryAll, $log, 0);
	}

	// Execute query
	// ==========================
	// 4. Lógica de paginación y construcción del paginador
	// ==========================
	// Se calcula la página actual, el offset/limit y se ejecuta la consulta paginada.
	// También se construye el mensaje de rango y el HTML del paginador.
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
		$finalQuery = $listQuery . " OFFSET {$limit} LIMIT {$list_max_entries_per_page}";
		//error_log("[ListView.php] Consulta REAL ejecutada (PostgreSQL): " . $finalQuery);
		$result = $adb->query ($finalQuery);
	} else {
		$finalQuery = $listQuery . " LIMIT {$limit}, {$list_max_entries_per_page}";
		//error_log("[ListView.php] Consulta REAL ejecutada (MySQL): " . $finalQuery);
		$result = $adb->query ($finalQuery);
	}
	
	// Corrección: Si estamos en una página > 1 y no hay registros, calcular la última página válida
	$numRowsCurrentPage = $result ? $adb->num_rows($result) : 0;
	if ($numRowsCurrentPage == 0 && $start > 1) {
		// Calcular el total real de registros
		$countResult = $adb->query(mkCountQuery($listQuery));
		$totalRecords = $countResult ? $adb->query_result($countResult, 0, 'count') : 0;
		
		if ($totalRecords > 0) {
			// Calcular la última página válida
			$lastValidPage = ceil($totalRecords / $list_max_entries_per_page);
			if ($lastValidPage < $start) {
								// Recalcular con la página correcta
				$start = $lastValidPage;
				$limit = (($start - 1) * $list_max_entries_per_page);
				$noofrows = $totalRecords;
				$navigation = VT_getSimpleNavigationValues($start, $list_max_entries_per_page, $noofrows);
				
				// Actualizar la sesión con la página correcta
				$_SESSION['lvs'][$currentModule]['start'] = $start;
				
				// Re-ejecutar la consulta con el nuevo offset
				if ($adb->dbType == 'pgsql') {
					$finalQuery = $listQuery . " OFFSET {$limit} LIMIT {$list_max_entries_per_page}";
				} else {
					$finalQuery = $listQuery . " LIMIT {$limit}, {$list_max_entries_per_page}";
				}
				$result = $adb->query($finalQuery);
			}
		} else {
			// No hay registros en absoluto, ir a página 1
			$start = 1;
			$limit = 0;
			$noofrows = 0;
			$navigation = VT_getSimpleNavigationValues($start, $list_max_entries_per_page, $noofrows);
			$_SESSION['lvs'][$currentModule]['start'] = $start;
		}
	}
	
	$recordListRangeMsg = getRecordRangeMessage ($result, $limit, $noofrows);

	// Navigation
	// ==========================
	// 5. Construcción del arreglo de registros a desplegar
	// ==========================
	// Aquí se crea el controlador de la ListView y se obtienen:
	// - $header: cabecera de la tabla
	// - $entries: arreglo principal con los registros a mostrar en la UI
	// - $search: campos de búsqueda	
	$navigationOutput = getTableHeaderSimpleNavigation ($navigation, $urlString, $currentModule, 'index', $viewId);

	$controller = new ListViewController ($adb, $current_user, $queryGenerator);
	$skipAction = isset ($skipAction) ? $skipAction : false;
	$header     = $controller->getListViewHeader ($focus, $currentModule, $urlString, $sortOrder, $orderBy, $skipAction);
	$entries    = $controller->getListViewEntries ($focus, $currentModule, $result, $navigation, $skipAction, $whereColor, $resultAll);
	$search     = $controller->getBasicSearchFieldInfoList ();
	

	$defaultGralView = $controller->getDefaultListViewByUser ($adb, $currentModule, $current_user->id);
	$listViewTab     = (empty ($listViewTab)) ? $defaultGralView : $listViewTab;

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
	$result = $adb->pquery (
		'SELECT
			rl.*
		FROM
			vtiger_relatedlists rl
			INNER JOIN vtiger_tab rm ON rm.tabid=rl.tabid AND rm.name=?
			INNER JOIN vtiger_tab rrm ON rrm.tabid=rl.related_tabid AND rrm.name=?
		WHERE
			rl.presence=0
		LIMIT 1',
		array ($currentModule, 'Calendar')
	);
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$activities = '1';
		$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE presence=0 AND isentitytype=1 ORDER BY tablabel', array ());
		if ($adb->num_rows ($result) == 0) {
			$relatedModules = null;
		} else {
			$relatedModules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$relatedModules [] = $row;
			}
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}

		$result = $adb->query ('SELECT * FROM vtiger_users ORDER BY id');
		if ($adb->num_rows ($result) > 0) {
			$availableUsers = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$availableUsers [ $row ['id'] ] = trim ("{$row ['first_name']} {$row ['last_name']}");
			}
		} else {
			$availableUsers = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}

		$result = $adb->query ('SELECT * FROM vtiger_groups ORDER BY groupid');
		if ($adb->num_rows ($result) > 0) {
			$availableGroups = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$availableGroups [ $row ['groupid'] ] = $row ['groupname'];
			}
		} else {
			$availableGroups = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
	} else {
		$activities = '0';
		$availableUsers = null;
		$availableGroups = null;
		$relatedModules = null;
	}
	
	$result = $adb->query ('SELECT id,  CONCAT(first_name, " ", last_name) AS username, imagename FROM vtiger_users ORDER BY id');
	if ($adb->num_rows ($result) > 0) {
		$availableUsersFilter = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$row['imagename'] = (empty($row['imagename'])) ? '/Image/avatar/png/man.png' : "{$_SESSION ['plat']}/user_images/{$row['imagename']}";
			$availableUsersFilter [ $row ['id'] ] = array('name' => trim ($row ['username']),
					'avatar' => $row['imagename'],
			);
		}
	}
	// [ TT11468 ] Motor Widget  - Johana Romero - 26/01/2017
	$Widget      = new Widgets ();
	$widgetsData = array ();
	$result      = $adb->pquery ('SELECT * FROM vtiger_widgets WHERE fld_module=? AND estatus=?', array ($currentModule, 1));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$sql = null;
			if ((!empty ($row ['campofecha'])) && ($row ['tiempofecha'])) {
				$moduleId  = $Widget->getTabId ($row ['fld_module']);
				$dates     = $Widget->getDateBetween ($row ['tiempofecha']);
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
	$notificationData = array (
		'module'   => $currentModule,
		'user'     => $current_user,
		'view'     => Notification::LIST_VIEW,
		'style'    => Notification::STYLE_NOTIFY,
		'recordId' => 0,
		'mode'     => $mode,
		'platform' => $_SESSION ['plat'],
	);
	/** se elimina la declaración local para usar la declaración global declarada en el header */
	//$smarty        = new vtigerCRM_Smarty ();

	try {
		$defaultKanban = (!isset ($_REQUEST ['ajax'])) ? KanbanViewUtils::isDefaultView($adb, $currentModule, $current_user->id) : null;
		//Kanban setting
		if (!empty($kanbanField) && (!empty($kanbanViewId))) {
			$smarty->assign('KANBAN_LIST', KanbanViewUtils::getAvailableViewsByModule($adb, $currentModule));
		} else if (!empty($defaultKanban)) {
			$smarty->assign('KANBAN_LIST', KanbanViewUtils::getAvailableViewsByModule($adb, $currentModule));
		} else {
			$smarty->assign('KANBAN_LIST', KanbanViewUtils::getAvailableViewsByModule($adb, $currentModule));
		}
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', 'Error');
		$smarty->assign ('MESSAGE', $e->getMessage ());
	}

	$result = $adb->pquery ('SELECT * FROM vtiger_crmentity WHERE demo=1 AND setype=?', array ($currentModule));
	if ($adb->num_rows ($result) > 0) {
		$hasDemoData = true;
	} else {
		$hasDemoData = false;
	}
	// are there Graphics tab on listView?
	try {
		$categories = GraphUtils::getCategories ();
		foreach ($categories as $key => $category) {
			$categoryCatalg [ $key ] = array (
				'app_code' => $key,
				'app_name' => $category,
			);
		}

		GraphicManager::getInstance($adb)->getBasicGraphics ($graphs, $isInstance, $categories, null, null, $currentModule);
		if(empty ($graphs)) {
			throw new Exception ('THERE ARE NOT GRAPHICS IN THE CURRENT MODULE');
		}
		$smarty->assign ('GRAPHS', 1);
	} catch (Exception $e) {
		$smarty->assign ('GRAPHS', null);
	}

	// are there Calendar tab on listView?
	$calendarView     = false;
	$calendarViewData = CalendarViewUtils::getCalendarViews ($adb, null, null, array($currentModule));
	if (is_array ($calendarViewData)) {
		$calendarView = (!empty ($calendarViewData ['records']));
	}
	$boxScore      = IndicatorsPanelHelper::hasboxScoreData($adb, $currentModule);
	$result = $adb->pquery(
		'SELECT 
				r.folderid 
			  FROM 
			  	vtiger_report r	
			  INNER JOIN vtiger_reportfolder rf ON rf.folderid=r.folderid 
			  INNER JOIN vtiger_reportmodules rm ON rm.reportmodulesid=r.reportid
			  WHERE
			  	r.sharingtype=? AND
                rm.primarymodule=?',
			array ('Public', $currentModule)
		);
	$report         = $adb->getRowCount ($result);
	$hasActivity    = DataViewUtils::hasRelatedActivities ($adb, $currentModule);
	if ($hasActivity) {
		$kanbanTaskConf = KanbanTaskUtils::fetchKanbanByModule ($adb, $currentModule);
		$hasActivity    = (!empty ($kanbanTaskConf) && intval ($kanbanTaskConf ['list_view']));
	}
	
	// Verificar si hay vistas Gantt disponibles para el módulo
	$ganttModuleView = false;
	$ganttViews = array();
	if (file_exists('include/utils/GanttModuleViewUtils.class.php')) {
		try {
			require_once('include/utils/GanttModuleViewUtils.class.php');
			if (class_exists('GanttModuleViewUtils')) {
				$ganttModuleView = GanttModuleViewUtils::hasGanttViews($adb, $currentModule);
				if ($ganttModuleView) {
					$ganttViews = GanttModuleViewUtils::getGanttViews($adb, $currentModule, $current_user->id);
					$smarty->assign('GANTT_MODULE_VIEWS', $ganttViews);
				}
			}
		} catch (Exception $e) {
			$ganttModuleView = false;
		}
	}
	
	if (empty ($statusButtons)) {
		$statusButtons = array(
			'list' => 1,
			'kanban'   => ($smarty->get_template_vars('KANBAN_LIST')) ? 1 : 0,
			'boxscore' => ($boxScore) ? 1 : 0,
			'graphic'  => ($smarty->get_template_vars('GRAPHS')) ? 1 : 0,
			'report'   => ($report) ? 1 : 0,
			'calendar' => ($calendarView) ? 1 : 0,
			'task'     => ($hasActivity) ? 1 : 0,
			'gantt'    => ($ganttModuleView) ? 1 : 0,
		);
	}
	$sumButtons   = array_sum (array_values ($statusButtons));
	$totalButtons = ($sumButtons == 1) ? 2 : ($sumButtons + 1);
	// Fin widgets
	$smarty->assign ('ACTION', $action);
	$smarty->assign ('ACTIVE_APPLICATIONS', $applications);
	$smarty->assign ('ALLSELECTEDIDS', vtlib_purify (isset ($_REQUEST ['allselobjs']) ? $_REQUEST ['allselobjs'] : ''));
	$smarty->assign ('ALPHABETICAL', $alphabetical);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('APPLICATION_VIEWS_ENABLED', PlatformUtils::areApplicationViewsEnabled ($adb));
	$smarty->assign ('AVALABLE_FIELDS', getMergeFields ($currentModule, 'available_fields'));
	$smarty->assign ('AVAILABLE_GROUPS', $availableGroups);
	$smarty->assign ('AVAILABLE_MODES', $availableModes);
	$smarty->assign ('AVAILABLE_PICKLISTS', getUserFldArray ($currentModule, $current_user->column_fields ['roleid']));
	$smarty->assign ('AVAILABLE_USERS', $availableUsers);
	$smarty->assign ('AVAILABLE_USERS_FILTER', $availableUsersFilter);
	$smarty->assign ('BOXSCORE', IndicatorsPanelHelper::hasboxScoreData($adb, $currentModule));
	$smarty->assign ('ALLIDS', is_array ($entries) ? implode (array_keys ($entries), ';') : '');
	$smarty->assign ('BUTTONS', $listButtons);
	$smarty->assign ('CAN_CREATE_RECORDS', $canCreateRecords);
	$smarty->assign ('CALENDAR_VIEW', $calendarView);
	$smarty->assign ('CATEGORY', $category);
	$smarty->assign ('CHANGE_GROUP_OWNER', getGroupslist ());
	$smarty->assign ('CHANGE_OWNER', getUserslist ());
	$smarty->assign ('CHECK', $toolButtons);
	$smarty->assign ('CRITERIA', $criteria);
	$smarty->assign ('CURRENT_PAGE_BOXES', is_array ($entries) ? implode (array_keys ($entries), ';') : '');
	$smarty->assign ('CUSTOM_BUTTONS', PlatformUtils::getCustomButtons ($adb, $currentModule, 'ListView', $_REQUEST));
	$smarty->assign ('CUSTOM_LINKS', Vtiger_Link::getAllByType (getTabid ($currentModule), array ('LISTVIEWBASIC', 'LISTVIEW'), $customLinksArguments));
	$smarty->assign ('CUSTOM_MODULE', $focus->IsCustomModule);
	$smarty->assign ('CUSTOMVIEW_OPTION', $customViewHtml);
	$smarty->assign ('CUSTOMVIEW_PERMISSION', $statusDetails);
	$smarty->assign ('CV_DELETE_PERMIT', $canDelete);
	$smarty->assign ('CV_EDIT_PERMIT', $canEdit);
	$smarty->assign ('DETAILWIDGET', $widgetsData);
	$smarty->assign ('FIELDNAMES', $fieldnames);
	$smarty->assign ('FIELDS_TO_MERGE', getMergeFields ($currentModule, 'fileds_to_merge'));
	$smarty->assign ('HOW_USE_ID', $idModeSelected);
	$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign ('IS_ADMIN', is_admin ($current_user));
	$smarty->assign ('IS_FIRST_CONNECTION', !empty ($_SESSION ['firstConnection']));
	$smarty->assign ('IS_REL_ACTIVITIES', $activities);
	$smarty->assign ('IS_LISTVIEW', 'yes');
	$smarty->assign ('LISTENTITY', $entries);
	$smarty->assign ('LISTHEADER', $header);
	$smarty->assign ('LIST_VIEW_TAB', $listViewTab);
	$smarty->assign ('MAX_RECORDS', $list_max_entries_per_page);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('NAVIGATION', $navigationOutput);
	$smarty->assign ('NOTIFICATIONS', NotificationUtils::fetchApplicableOnScreenNotifications ($adb, $notificationData));
	$smarty->assign ('PROFILE_IDS', $profileIds);
	$smarty->assign ('recordListRange', $recordListRangeMsg);
	$smarty->assign ('RELATED_MODULES', $relatedModules);
	$smarty->assign ('SEARCHLISTHEADER', $search);
	$smarty->assign ('SELECTEDIDS', vtlib_purify (isset ($_REQUEST ['selobjs']) ? $_REQUEST ['selobjs'] : ''));
	$smarty->assign ('MODULELABEL', getModuleTitleFromDB($currentModule));
	$smarty->assign ('SINGLE_MOD', getTranslatedString ('SINGLE_' . $currentModule));
	$smarty->assign ('STATUS_BUTTONS', $statusButtons);
	$smarty->assign ('STATUS_TOTAL_BUTTONS', $totalButtons);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('TOTAL_MAX_FREE_RECORDS', (isset ($moduleSubscription)) && ($moduleSubscription->getMaxRecords () != -1) ? $moduleSubscription->getMaxRecords () : null);
	$smarty->assign ('TOTAL_USED_FREE_RECORDS', (isset ($moduleSubscription)) && ($moduleSubscription->getMaxRecords () != -1) ? $moduleSubscription->getTotalRecords () : null);
	$smarty->assign ('TOTALRECORD', $noofrows);
	$smarty->assign ('TOTAL_SYNCS', DataSharingUtils::fetchTotalSyncs ($adb, $_SESSION ['platInstancia'], $currentModule));
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
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	if (!empty ($urlString)) {
		$smarty->assign ('SEARCH_URL', $urlString);
	}
	if (((isset ($isHomeTab) && $isHomeTab)) || (!empty ($isHomeTabAjax))) {
		if ($current_user->defaultOperating == 'FORMATIVE_MODE') {
			$smarty->assign ('IS_HOME_TAB', true);
		}
		$smarty->assign ('TAB_GROUP', 'record');
		$smarty->assign ('IS_HOME_TAB', false);
		$smarty->assign ('TAB_HOME_ID', (empty($tabHomeId)) ? rand () : $tabHomeId);
		if (isset ($_REQUEST ['ajax']) && $_REQUEST ['ajax'] != '') {
			$smarty->display ('Home/TabsContents/ListViewHomeEntries.tpl');
		} else {
			$proyectTab = $smarty->fetch ('Home/TabsContents/HomeListView.tpl');
		}
		
	} else if (isset ($_REQUEST ['ajax']) && $_REQUEST ['ajax'] != '') {
		$smarty->display('ListViewEntries.tpl');
	} else if (isset ($_REQUEST ['modeview']) && $_REQUEST ['modeview'] != '' && $_REQUEST ['modeview'] == 'viewkanban') {
		$smarty->display ('ListViewKanban.tpl');
	} else {
		$smarty->display ('ListView.tpl');
	}
