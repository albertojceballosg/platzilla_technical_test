<?php

	global $app_strings, $mod_strings, $current_language, $currentModule, $theme, $adb;
	global $list_max_entries_per_page, $clientView, $bDlgModales;

	$bDlgModales=true;
	$adbBak = clone $adb;

	require_once('Smarty_setup.php');
	require_once('include/ListView/ListView.php');
	require_once('modules/CustomView/CustomView.php');
	require_once('modules/PickList/PickListUtils.php');
	require_once('include/DatabaseUtil.php');

	checkFileAccessForInclusion("modules/$currentModule/$currentModule.php");
	require_once("modules/$currentModule/$currentModule.php");

	$category = getParentTab();
	$url_string = '';

	if(isset($tool_buttons)==false) {
		$tool_buttons = Button_Check($currentModule);
	}

	//Se determina si la lista requerida es de una plataforma hija
	list($viewname,$_REQUEST['platdb']) = explode('|',$_REQUEST['viewname']);
	if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
		if (determinarPermisosModuloHijo($_REQUEST['platdb'],$_REQUEST['module'],'view')) {
			unset($adb);
			$adb = conectaPlataformaHija($_REQUEST['platdb']);
		}
	}

	//se verifica si tiene soporte habilitado
	$verificar_soporte = obtenerValorVariable('HELPDESK_SUPPORT_VALIDATE','HelpDesk');
	if ($clientView && $verificar_soporte=='true' && $currentModule=='HelpDesk') {
		$acc=getContactAccountfromUser();
		$support='false';
		$helpdesk_msg=getTranslatedString('NO_DISPONE_HORAS_DE_SOPORTE');
		if ($acc['horas_soporte']>0 && $acc['estado_plataforma']=='Activa') {
			$support='true';
			$helpdesk_msg=sprintf(getTranslatedString('HORAS_DE_SOPORTE_DISPONIBLES'),$acc['horas_soporte']);
		}
	}

	$focus = new $currentModule();
	/** @noinspection PhpUndefinedMethodInspection */
	$focus->initSortbyField($currentModule);
	/** @noinspection PhpUndefinedMethodInspection */
	$list_buttons=$focus->getListButtons($app_strings,$mod_strings);

	if ($clientView && isset($list_buttons['mass_edit'])) {
		unset($list_buttons['mass_edit']);
	}

	if (ListViewSession::hasViewChanged($currentModule)) {
		$_SESSION[$currentModule.'_Order_By'] = '';
	}
	/** @noinspection PhpUndefinedMethodInspection */
	$sorder = $focus->getSortOrder();
	/** @noinspection PhpUndefinedMethodInspection */
	$order_by = $focus->getOrderBy();

	$_SESSION[$currentModule.'_Order_By'] = $order_by;
	$_SESSION[$currentModule.'_Sort_Order'] = $sorder;

	$smarty = new vtigerCRM_Smarty();

	if (isset($_REQUEST['vista']) && $_REQUEST['vista'] != '') {
		$start = vtlib_purify($_REQUEST['vista']);
	} else {
		$start = 2;
	}

	$smarty->assign('VISTAORIGEN', $start);
	// Identify this module as custom module.
	$smarty->assign('CUSTOM_MODULE', $focus->IsCustomModule);
	$smarty->assign('MAX_RECORDS', $list_max_entries_per_page);
	$smarty->assign('MOD', $mod_strings);
	$smarty->assign('APP', $app_strings);
	$smarty->assign('MODULE', $currentModule);
	$smarty->assign('SINGLE_MOD', getTranslatedString('SINGLE_'.$currentModule));
	$smarty->assign('CATEGORY', $category);
	$smarty->assign('BUTTONS', $list_buttons);
	$smarty->assign('CHECK', $tool_buttons);
	$smarty->assign('THEME', $theme);
	$smarty->assign('IMAGE_PATH', "themes/$theme/images/");

	$smarty->assign('CHANGE_OWNER', getUserslist());
	$smarty->assign('CHANGE_GROUP_OWNER', getGroupslist());

	// Custom View
	$profileIds = isset ($_REQUEST ['profileids']) ? vtlib_purify ($_REQUEST ['profileids']) : null;
	$profileIds = !empty ($profileIds) ? explode (',', $profileIds) : null;
	$customView = new CustomView($currentModule);
	if (isset($_REQUEST['viewname']) && !empty($_REQUEST['viewname'])) {
		$viewid = $_REQUEST['viewname'];
	} else {
		$viewid = $customView->getViewId($currentModule, $profileIds);
	}
	if ($viewid == 0) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	$smarty->assign ('ACTIVE_APPLICATIONS', $applications);
	$smarty->assign ('PROFILE_IDS', $profileIds);
	$customview_html = $customView->getCustomViewCombo($viewid);
	$customviewplats_html = getListPlats($currentModule,$_REQUEST['platdb']);
	list($_REQUEST['viewname'],$_REQUEST['platdb']) = explode('|',$_REQUEST['viewname']);
	$viewinfo = $customView->getCustomViewByCvid($viewid);
	// Feature available from 5.1
	if (method_exists($customView, 'isPermittedChangeStatus')) {
		// Approving or Denying status-public by the admin in CustomView
		$statusdetails = $customView->isPermittedChangeStatus($viewinfo['status']);
		// To check if a user is able to edit/delete a CustomView
		$edit_permit = $customView->isPermittedCustomView($viewid,'EditView',$currentModule);
		$delete_permit = $customView->isPermittedCustomView($viewid,'Delete',$currentModule);
		$smarty->assign('CUSTOMVIEW_PERMISSION',$statusdetails);
		$smarty->assign('CV_EDIT_PERMIT',$edit_permit);
		$smarty->assign('CV_DELETE_PERMIT',$delete_permit);
	}
	// END
	$smarty->assign('VIEWID', $viewid);

	if ($viewinfo['viewname'] == 'All') {
		$smarty->assign('ALL', 'All');
	}

	global $current_user;

	$queryGenerator = new QueryGenerator($currentModule, $current_user);
	if ($viewid != '0') {
		$queryGenerator->initForCustomViewById($viewid);
	} else {
		$queryGenerator->initForDefaultCustomView();
	}

	// Enabling Module Search
	$url_string = '';
	if ($_REQUEST['query'] == 'true') {
		$queryGenerator->addUserSearchConditions($_REQUEST);
		$ustring = getSearchURL($_REQUEST);
		$url_string .= "&query=true$ustring";
		$smarty->assign('SEARCH_URL', $url_string);
	}

	$lstRoles = explode(',',obtenerValorVariable('ROLES_ADM_CURSOS', 'formacion_cursos'));

	$users_related='';
	if (!is_admin($current_user) && !in_array($current_user->column_fields['roleid'],$lstRoles)) {
		$users_related="INNER JOIN vtiger_crmentityrel crmrel on crmrel.crmid=vtiger_formacion_cursos.`formacion_cursosid` and crmrel.relcrmid='".$current_user->id."' and crmrel.relmodule='Users'";
	}

	$add_categories = '';
	if (checkDBTableExist('vtiger_formacion_cur_ca')) {
		$add_table=' ,vfcc.* ';
		$add_categories="LEFT JOIN vtiger_crmentityrel crmrelcat ON crmrelcat.`relcrmid`=vtiger_crmentity.crmid AND crmrelcat.module='formacion_cur_ca'
						 LEFT JOIN vtiger_formacion_cur_ca vfcc ON vfcc.formacion_cur_caid=crmrelcat.`crmid`";
	}

	$list_query = "SELECT CONCAT(vtiger_attachments.`path`,vtiger_attachments.`attachmentsid`,'_',vtiger_attachments.`name`) AS image,
					vtiger_formacion_cursos.*$add_table
					FROM vtiger_formacion_cursos
					INNER JOIN vtiger_crmentity ON vtiger_formacion_cursos.formacion_cursosid = vtiger_crmentity.crmid  AND vtiger_crmentity.deleted=0 
					$users_related 
					$add_categories
					LEFT JOIN `vtiger_attachments` ON `vtiger_attachments`.`attachmentsid`=vtiger_formacion_cursos.img_curso
					WHERE  vtiger_formacion_cursos.formacion_cursosid > 0 ";
	if (isset($_REQUEST['search_field']) && $_REQUEST['search_field']!='') {
		if ($_REQUEST['type']!='alpbt') {
			$list_query.=' AND vtiger_formacion_cursos.'.$_REQUEST['search_field'].' LIKE \'%'.$_REQUEST['search_text'].'%\' ';
		} else {
			$list_query.=' AND vtiger_formacion_cursos.'.$_REQUEST['search_field'].' LIKE \''.$_REQUEST['search_text'].'%\' ';
		}
	}

	$where = $queryGenerator->getConditionalWhere();
	if (isset($where) && $where != '') {
		$_SESSION['export_where'] = $where;
	} else {
		unset($_SESSION['export_where']);
	}

	// Sorting
	if (!empty($order_by)) {
		if ($order_by == 'smownerid') {
			$list_query .= ' ORDER BY user_name ' . $sorder;
		} else {
			$tablename = getTableNameForField($currentModule, $order_by);
			$tablename = ($tablename != '') ? ($tablename . '.') : '';
			$list_query .= ' ORDER BY ' . $tablename . $order_by . ' ' . $sorder;
		}
	}

	//Postgres 8 fixes
	if ($adb->dbType == 'pgsql') {
		$list_query = fixPostgresQuery($list_query, $log, 0);
	}
	if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true) {
		$count_result = $adb->query(mkCountQuery($list_query));
		$noofrows = $adb->query_result($count_result,0,'count');
	} else {
		$noofrows = null;
	}

	$queryMode = (isset($_REQUEST['query']) && $_REQUEST['query'] == 'true');
	$start = ListViewSession::getRequestCurrentPage($currentModule, $list_query, $viewid, $queryMode);
	$navigation_array = VT_getSimpleNavigationValues($start,$list_max_entries_per_page,$noofrows);
	$limit_start_rec = ($start-1) * $list_max_entries_per_page;

	if ($adb->dbType == "pgsql") {
		$list_result = $adb->pquery($list_query, array());
	} else {
		$list_result = $adb->pquery($list_query, array());
	}
	$colors=array('primary','info','danger','success','warning');
	while ($r=$adb->fetchByAssoc($list_result)) {
		$key=array_rand($colors,1);
		$r['color']=$colors[$key];
		if ($r['image']=='') {
			$r['image'] = 'themes/images/no_picture.jpg';
		}
		if ($r['formacion_cur_caid']!='') {
			$cursos[$r['formacion_cur_caid']][] = $r;
		} else {
			$cursos[] = $r;
		}
	}

	$smarty->assign('CURSOS',$cursos);

	$recordListRangeMsg = getRecordRangeMessage($list_result, $limit_start_rec,$noofrows);
	$smarty->assign('recordListRange',$recordListRangeMsg);
	$smarty->assign('CUSTOMVIEW_OPTION',$customview_html);
	$smarty->assign('CUSTOMVIEWPLATS_OPTION',$customviewplats_html);

	// Navigation
	$navigationOutput = getTableHeaderSimpleNavigation($navigation_array, $url_string, $currentModule, 'index', $viewid);
	$smarty->assign('NAVIGATION', $navigationOutput);

	$controller = new ListViewController($adb, $current_user, $queryGenerator);

	if (isset($skipAction)==false) {
		$skipAction==false;
	}

	$listview_header = $controller->getListViewHeader($focus,$currentModule,$url_string,$sorder,$order_by,$skipAction);
	$listview_entries = $controller->getListViewEntries($focus,$currentModule,$list_result,$navigation_array,$skipAction);
	$listview_header_search = $controller->getBasicSearchFieldInfoList();

	$smarty->assign('LISTHEADER', $listview_header);
	$smarty->assign('LISTENTITY', $listview_entries);
	$smarty->assign('SEARCHLISTHEADER',$listview_header_search);

	if (determinarFiltroListasModulo($currentModule)) {
		$customView->getCvColumnListSQL($viewid);
		$columnslist = $customView->getColumnsListByCvid($viewid);
		$listfilter_header = array();
		$campos = array_keys($customView->list_fields);
		$contador = count($customView->list_fields);
		for ($i=0; $i < $contador; $i++) {
			$filtro = $customView->list_fields[$campos[$i]];
			foreach ($filtro as $clave => $valor) {
				array_push($listfilter_header,getFiltersValues($valor,$currentModule,$columnslist[$i]));
			}
		}
		$smarty->assign('FILTERS',1);
		$smarty->assign('LISTFILTERS', $listfilter_header);
		$smarty->assign('BUILD_SEARCH', buildFilterSearch($viewid,$currentModule));
	}
	// Module Search
	$alphabetical = AlphabeticalSearch($currentModule,'index',$focus->def_basicsearch_col,'true','basic','','','','',$viewid);
	$fieldnames = $controller->getAdvancedSearchOptionString();
	$criteria = getcriteria_options();
	$smarty->assign('ALPHABETICAL', $alphabetical);
	$smarty->assign('FIELDNAMES', $fieldnames);
	$smarty->assign('CRITERIA', $criteria);

	$smarty->assign('AVALABLE_FIELDS', getMergeFields($currentModule,'available_fields'));
	$smarty->assign('FIELDS_TO_MERGE', getMergeFields($currentModule,'fileds_to_merge'));

	//Added to select Multiple records in multiple pages
	$smarty->assign('SELECTEDIDS', vtlib_purify($_REQUEST['selobjs']));
	$smarty->assign('ALLSELECTEDIDS', vtlib_purify($_REQUEST['allselobjs']));
	$smarty->assign('CURRENT_PAGE_BOXES', implode(array_keys($listview_entries),';'));
	ListViewSession::setSessionQuery($currentModule,$list_query,$viewid);

	if ($clientView) {
		$smarty->assign('CALENDAR_DISPLAY', 'false');
		$smarty->assign('WORLD_CLOCK_DISPLAY', 'false');
		$smarty->assign('CALCULATOR_DISPLAY', 'false');
		$smarty->assign('CHAT_DISPLAY', 'false');
		$smarty->assign('LAST_VIEWED', 'false');
		$smarty->assign('CLIENT_VIEW', 'true');
		$pago = obtenerValorVariable('CONSULTA_PAGO','PurchaseOrder');
		if ($pago != 'false' && !$clientWeb) {
			$smarty->assign('E_PAYMENTMETHOD_FORM', $enable_paymentmethod_form);
			$smarty->assign('PAYMENTMETHOD_FORM', getPaymentMethodForm());
		}
		$smarty->assign('HELPDESK_BUTTON', $support);
		$smarty->assign('HELPDESK_MSG', $helpdesk_msg);
	}

	// Gather the custom link information to display
	require_once('vtlib/Vtiger/Link.php');
	$customlink_params = array('MODULE' => $currentModule, 'ACTION' => vtlib_purify($_REQUEST['action']), 'CATEGORY' => $category);
	$smarty->assign('CUSTOM_LINKS', Vtiger_Link::getAllByType(getTabid($currentModule), array('LISTVIEWBASIC', 'LISTVIEW'), $customlink_params));
	// END
	$smarty->assign('CUSTOM_BUTTONS', determinaBotonesListaRegistros($currentModule));
	$smarty->assign('IS_ADMIN', is_admin($current_user));
	$permitted = isPermitted('instancias', 'index', '');

	if ($permitted == 'yes') {
		list($presence) = $adb->fetch_row($adb->query("select presence from vtiger_tab where name='instancias'"));
		if ($presence == '1') {
			$permitted = 'no';
		}
	}

	$smarty->assign('ISP_INST', $permitted);
	$smarty->assign ('AVAILABLE_PICKLISTS', getUserFldArray ($currentModule, $current_user->column_fields ['roleid']));

	if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] != '') {
		$smarty->display('modules/formacion_cursos/ListViewCursos.tpl');
	} else {
		$smarty->display('ListView.tpl');
	}

	unset($adb);
	$adb = clone $adbBak;

?>
