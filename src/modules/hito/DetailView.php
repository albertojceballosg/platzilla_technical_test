<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('user_privileges/default_module_view.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/PickList/PickListUtils.php');

	global $mod_strings, $app_strings, $currentModule, $current_user, $theme, $singlepane_view;

	$focus = CRMEntity::getInstance ($currentModule);

	$singlepane_view = 'true';
	$tool_buttons    = Button_Check ($currentModule);
	$smarty          = new vtigerCRM_Smarty();

	$record           = $_REQUEST['record'];
	$isduplicate      = vtlib_purify ($_REQUEST['isDuplicate']);
	$tabid            = getTabid ($currentModule);
	$category         = getParentTab ($currentModule);
	$swDetailViewGrid = true;

	if ($record != '') {
		$focus->id = $record;
		$focus->retrieve_entity_info ($record, $currentModule);
	}

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $focus);
	$adb->setDieOnError ($oldDieOnError);

	if ($isduplicate == 'true') {
		$focus->id = '';
	}

// Identify this module as custom module.
	$smarty->assign ('CUSTOM_MODULE', true);

	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
// TODO: Update Single Module Instance name here.
	$smarty->assign ('SINGLE_MOD', 'SINGLE_' . $currentModule);
	$smarty->assign ('CATEGORY', $category);
	$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('ID', $focus->id);
	$smarty->assign ('MODE', $focus->mode);

	$recordName = array_values (getEntityName ($currentModule, $focus->id));
	$recordName = $recordName[0];
	$smarty->assign ('NAME', $recordName);
	$smarty->assign ('UPDATEINFO', updateInfo ($focus->id));

// Module Sequence Numbering
	$mod_seq_field = getModuleSequenceField ($currentModule);
	if ($mod_seq_field != null) {
		$mod_seq_id = $focus->column_fields[ $mod_seq_field['name'] ];
	} else {
		$mod_seq_id = $focus->id;
	}
	$smarty->assign ('MOD_SEQ_ID', $mod_seq_id);
// END

	$validationArray = split_validationdataArray (getDBValidationData ($focus->tab_name, $tabid));
	$smarty->assign ('VALIDATION_DATA_FIELDNAME', $validationArray['fieldname']);
	$smarty->assign ('VALIDATION_DATA_FIELDDATATYPE', $validationArray['datatype']);
	$smarty->assign ('VALIDATION_DATA_FIELDLABEL', $validationArray['fieldlabel']);

	$smarty->assign ('EDIT_PERMISSION', isPermitted ($currentModule, 'EditView', $record));
	$smarty->assign ('CHECK', $tool_buttons);

	if (PerformancePrefs::getBoolean ('DETAILVIEW_RECORD_NAVIGATION', true) && isset($_SESSION[ $currentModule . '_listquery' ])) {
		$recordNavigationInfo = ListViewSession::getListViewNavigation ($focus->id);
		VT_detailViewNavigation ($smarty, $recordNavigationInfo, $focus->id);
	}

	$smarty->assign ('IS_REL_LIST', isPresentRelatedLists ($currentModule));
	$smarty->assign ('SinglePane_View', $singlepane_view);

	if ($singlepane_view == 'true') {
		$related_array = getRelatedLists ($currentModule, $focus);
		unset($related_array['Recursos humanos']);

		$smarty->assign ("RELATEDLISTS", $related_array);

		require_once ('include/ListView/RelatedListViewSession.php');
		if (!empty($_REQUEST['selected_header']) && !empty($_REQUEST['relation_id'])) {
			RelatedListViewSession::addRelatedModuleToSession (vtlib_purify ($_REQUEST['relation_id']),
				vtlib_purify ($_REQUEST['selected_header']));
		}
		$open_related_modules = RelatedListViewSession::getRelatedModulesFromSession ();
		$smarty->assign ("SELECTEDHEADERS", $open_related_modules);
	}

	if (isPermitted ($currentModule, 'EditView', $record) == 'yes') {
		$smarty->assign ('EDIT_DUPLICATE', 'permitted');
	}
	if (isPermitted ($currentModule, 'Delete', $record) == 'yes') {
		$smarty->assign ('DELETE', 'permitted');
	}

	$blocks = getBlocks ($currentModule, 'detail_view', '', $focus->column_fields);
	if (!empty($focus->column_fields['proyectosid'])) {
		$focusProject     = CRMEntity::getInstance ('proyectos');
		$focusProject->id = $focus->column_fields['proyectosid'];
		$focusProject->retrieve_entity_info ($focus->column_fields['proyectosid'], 'proyectos');
		if ($focusProject->column_fields['template'] == '1') {
			unset($blocks['Datos b&aacute;sicos'][1]);//Ese es el bloque de fechas.
		}
	}
	$smarty->assign ('BLOCKS', $blocks);

// Gather the custom link information to display
	include_once ('vtlib/Vtiger/Link.php');
	$customlink_params = Array ('MODULE' => $currentModule, 'RECORD' => $focus->id, 'ACTION' => vtlib_purify ($_REQUEST['action']));
	$smarty->assign ('CUSTOM_LINKS', Vtiger_Link::getAllByType (getTabid ($currentModule), Array ('DETAILVIEWBASIC', 'DETAILVIEW', 'DETAILVIEWWIDGET'), $customlink_params));
// END

// Botones Personalizados
	require_once ('include/utils/PlatformUtils.class.php');
	$smarty->assign ('CUSTOM_BUTTONS', PlatformUtils::getCustomButtons ($adb, $currentModule, 'DetailView', $_REQUEST));

// Record Change Notification
	$focus->markAsViewed ($current_user->id);
// END
	$smarty->assign ('CAMPOS_TIPO_GRID', escribeCamposGrid ($currentModule, $entity->id, $swDetailViewGrid));
	$smarty->assign ('CAMPOS_TIPO_MATRIX', escribeDetalleCamposMatrix ($currentModule, $focus->id));

	$smarty->assign ('DETAILVIEW_AJAX_EDIT', PerformancePrefs::getBoolean ('DETAILVIEW_AJAX_EDIT', true));

	if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
		$smarty->assign ("PLATDB", vtlib_purify ($_REQUEST['platdb']));
	}

	$smarty->assign ('AVAILABLE_PICKLISTS', getUserFldArray ($currentModule, $current_user->column_fields ['roleid']));
	$smarty->assign ('ATTACHMENTS', AttachmentsUtils::fetchAttachments ($adb, $record, $currentModule));
	$smarty->display ('DetailView.tpl');

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $focus);
	$adb->setDieOnError ($oldDieOnError);

?>