<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/boxscore/boxscore.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/PickList/PickListUtils.php');
	require_once ('user_privileges/default_module_view.php');

	global $adb, $app_strings, $current_user, $currentModule, $mod_strings, $singlepane_view, $theme, $smarty;

	if(isset ($_REQUEST['action'])) {
		$action = vtlib_purify ($_REQUEST['action']);
	} else{
		$action = null;
	}

	if(isset($_REQUEST['box_score_dataid'])) {
		$boxScoreDataId = vtlib_purify($_REQUEST['box_score_dataid']);
	} else{
		$boxScoreDataId = null;
	}

	if(isset($_REQUEST['crear4Q'])) {
		$cuatroqCreate = vtlib_purify($_REQUEST['crear4Q']);
	} else{
		$cuatroqCreate = null;
	}

	if(isset($_REQUEST['fecha4q'])) {
		$cuatroqDate = vtlib_purify($_REQUEST['fecha4q']);
	} else{
		$cuatroqDate = null;
	}

	if(isset($_REQUEST['isDuplicate'])) {
		$isDuplicate = vtlib_purify($_REQUEST['isDuplicate']);
	} else{
		$isDuplicate = null;
	}

	if(isset($_REQUEST['record'])) {
		$record = vtlib_purify($_REQUEST['record']);
	} else{
		$record = null;
	}

	if(isset($_REQUEST['relation_id']) && !empty($_REQUEST['relation_id'])) {
		$relationId = vtlib_purify($_REQUEST['relation_id']);
	} else{
		$relationId = null;
	}

	if(isset($_REQUEST['selected_header']) && !empty($_REQUEST['selected_header'])) {
		$selectedHeader = vtlib_purify($_REQUEST ['selected_header']);
	} else{
		$selectedHeader = null;
	}

	if(isset($_REQUEST['platdb']) && !empty($_REQUEST ['platdb'])) {
		$platformDatabase = vtlib_purify($_REQUEST['platdb']);
	} else{
		$platformDatabase = null;
	}


	/** @var boxscore|stdClass $entity */
	$entity = CRMEntity::getInstance($currentModule);

	if ($cuatroqCreate == 1) {
		$weeklyId = $entity->getWeeklyId($boxScoreDataId, $record, $cuatroqDate);
		$entity->registerCuatroq($boxScoreDataId, $record, $weeklyId, $cuatroqDate, 'no');
	}

	$toolButtons = Button_Check($currentModule);
	$tabId = getTabid($currentModule);
	$category = getParentTab();

	if ($record != '') {
		$entity->id = $record;
		$entity->retrieve_entity_info($record, $currentModule);
	}

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError(false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $focus);
	$adb->setDieOnError($oldDieOnError);

	if ($isDuplicate == 'true') {
		$entity->id = '';
	}

	$recordName = array_values(getEntityName ($currentModule, $entity->id));
	$recordName = $recordName[0];

	// Module Sequence Numbering
	$modSeqField = getModuleSequenceField ($currentModule);

	if($modSeqField != null) {
	$modSeqId = $entity->column_fields[$modSeqField['name']];
	} else{
	$modSeqId = $entity->id;
	}

	$validationArray = split_validationdataArray(getDBValidationData($entity->tab_name, $tabId));

	// Gather the custom link information to display
	$customLinkParams = array (
		'MODULE' => $currentModule,
		'RECORD' => $entity->id,
		'ACTION' => $action,
	);

	$_REQUEST ['fecha_desde'] = '';
	$_REQUEST ['fecha_hasta'] = '';

	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('BLOCKS', getBlocks ($currentModule, 'detail_view', '', $entity->column_fields));
	$smarty->assign ('CAMPOS_TIPO_GRID', escribeDetalleCamposGrid ($currentModule, $entity->id));
	$smarty->assign ('CAMPOS_TIPO_MATRIX', escribeDetalleCamposMatrix ($currentModule, $entity->id));
	$smarty->assign ('CATEGORY', $category);
	$smarty->assign ('CHECK', $toolButtons);
	$smarty->assign ('CUSTOM_LINKS', Vtiger_Link::getAllByType (getTabid ($currentModule), array ('DETAILVIEWBASIC', 'DETAILVIEW', 'DETAILVIEWWIDGET'), $customLinkParams));
	$smarty->assign ('CUSTOM_MODULE', true);
	$smarty->assign ('DETAILVIEW_AJAX_EDIT', PerformancePrefs::getBoolean ('DETAILVIEW_AJAX_EDIT', true));
	$smarty->assign ('EDIT_PERMISSION', isPermitted ($currentModule, 'EditView', $record));
	$smarty->assign ('ID', $entity->id);
	$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign ('IS_REL_LIST', isPresentRelatedLists ($currentModule));
	$smarty->assign ('IS_ADMIN1', is_admin ($current_user) ? '1' : '');
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MOD_SEQ_ID', $modSeqId);
	$smarty->assign ('MODE', $entity->mode);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('NAME', $recordName);
	$smarty->assign ('PLAT', (isset ($_SESSION ['plat'])) && (!empty ($_SESSION ['plat'])) ? vtlib_purify ($_SESSION ['plat']) : '');
	$smarty->assign ('RECORDDUPLICATE', $entity->id);
	$smarty->assign ('SINGLE_MOD', "SINGLE_{$currentModule}");
	$smarty->assign ('SinglePane_View', $singlepane_view);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('UPDATEINFO', updateInfo ($entity->id));
	$smarty->assign ('VALIDATION_DATA_FIELDDATATYPE', $validationArray ['datatype']);
	$smarty->assign ('VALIDATION_DATA_FIELDLABEL', $validationArray ['fieldlabel']);
	$smarty->assign ('VALIDATION_DATA_FIELDNAME', $validationArray ['fieldname']);

	if ($singlepane_view == 'true') {
		$smarty->assign ('RELATEDLISTS', getRelatedLists ($currentModule, $entity));
		require_once ('include/ListView/RelatedListViewSession.php');
		if (($selectedHeader !== null) && ($relationId !== null)) {
			RelatedListViewSession::addRelatedModuleToSession ($relationId, $selectedHeader);
		}
		$smarty->assign ('SELECTEDHEADERS', RelatedListViewSession::getRelatedModulesFromSession ());
	}
	if (isPermitted ($currentModule, 'EditView', $record) == 'yes') {
		$smarty->assign ('EDIT_DUPLICATE', 'permitted');
	}
	if (isPermitted ($currentModule, 'Delete', $record) == 'yes') {
		$smarty->assign ('DELETE', 'permitted');
	}
	if ((PerformancePrefs::getBoolean ('DETAILVIEW_RECORD_NAVIGATION', true)) && (isset ($_SESSION ["{$currentModule}_listquery"]))) {
		$recordNavigationInfo = ListViewSession::getListViewNavigation ($entity->id);
		VT_detailViewNavigation ($smarty, $recordNavigationInfo, $entity->id);
	}
	if ($platformDatabase !== null) {
		$smarty->assign ('PLATDB', $platformDatabase);
	}
	$smarty->display ('DetailView.tpl');

	// Record Change Notification
	$entity->markAsViewed ($current_user->id);

	$bs = new box_score ();
	$bs->loadDefaultData ($record);
	$calculations = $bs->getCalculations ($record);
	$blocks = $bs->getBlocks ();
	$cuatroq = $bs->getCuatroq ($record);

	$year = date ('Y');
	$monthSearch = (isset ($_REQUEST ['monthsearch'])) && (!empty ($_REQUEST ['monthsearch'])) ? vtlib_purify ($_REQUEST ['monthsearch']) : date ('m');
	$day = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
	$from = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
	$to = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));

	$smartyBox = new vtigerCRM_Smarty ();
	$smartyBox->assign ('AVAILABLE_PICKLISTS', getUserFldArray ($currentModule, $current_user->column_fields ['roleid']));
	$smartyBox->assign ('BLOCKS', $blocks);
	$smartyBox->assign ('BOX_SCORE', $bs);
	$smartyBox->assign ('CALCULATIONS', $calculations);
	$smartyBox->assign ('CUATROQ', $cuatroq);
	$smartyBox->assign ('CURRENT_USER', $current_user);
	$smartyBox->assign ('FROM', $from);
	$smartyBox->assign ('MOD', $mod_strings);
	$smartyBox->assign ('MONTH_SEARCH', $monthSearch);
	$smartyBox->assign ('RECORD', $record);
	$smartyBox->assign ('TO', $to);
	$smartyBox->display ('modules/boxscore/DetailView.tpl');

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $focus);
	$adb->setDieOnError ($oldDieOnError);
