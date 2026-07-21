<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/EditViewUtils.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/PickList/DependentPickListUtils.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $currentModule, $theme;

	try {
		$profileIds = !empty ($profileIds) ? explode (',', $profileIds) : null;
		/** @var CRMEntity|stdClass $focus */
		$focus           = CRMEntity::getInstance ($currentModule);
		$dispView        = getView ($focus->mode);
		$tabId           = getTabid ($currentModule);
		$validationData  = getDBValidationData ($focus->tab_name, $tabId);
		$validationArray = EditViewUtils::splitValidationData ($validationData);
		$modSeqField     = getModuleSequenceField ($currentModule);
		$recordName      = null;

		$blocks = getBlocks ($currentModule, $dispView, 'mass_edit', $focus->column_fields, '', $profileIds);
		if (empty ($blocks)) {
			throw new Exception ("El módulo {$currentModule} no tiene campos habilitados para edición masiva");
		}

		if (!empty ($_SESSION ['platInstancia'])) {
			$applications = PlatformUtils::getApplicationsByUserRole ($adb, $current_user->column_fields ['roleid'], $currentModule);
		} else {
			$applications = PlatformUtils::getApplicationsByModuleName ($adb, $currentModule);
		}

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('ACTIVE_APPLICATIONS', $applications);
		$smarty->assign ('ADVBLOCKS', getBlocks ($currentModule, $dispView, 'mass_edit', $focus->column_fields, 'ADV', $profileIds));
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('BASBLOCKS', getBlocks ($currentModule, $dispView, 'mass_edit', $focus->column_fields, 'BAS', $profileIds));
		$smarty->assign ('BLOCKS', $blocks);
		$smarty->assign ('CALENDAR_DATEFORMAT', parse_calendardate ($app_strings ['NTC_DATE_FORMAT']));
		$smarty->assign ('CALENDAR_LANG', $app_strings ['LBL_JSCALENDAR_LANG']);
		$smarty->assign ('CATEGORY', getParentTab ());
		$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULE', $currentModule);
		$smarty->assign ('OP_MODE', $dispView);
		$smarty->assign ('PICKIST_DEPENDENCY_DATASOURCE', Zend_Json::encode (Vtiger_DependencyPicklist::getPicklistDependencyDatasource ($currentModule)));
		$smarty->assign ('PROFILE_IDS', $profileIds);
		$smarty->assign ('SINGLE_MOD', 'SINGLE_' . $currentModule);
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('VALIDATION_DATA_FIELDNAME', $validationArray ['fieldname']);
		$smarty->assign ('VALIDATION_DATA_FIELDDATATYPE', $validationArray ['datatype']);
		$smarty->assign ('VALIDATION_DATA_FIELDLABEL', $validationArray ['fieldlabel']);
		$smarty->assign ('MOD_SEQ_ID', $focus->column_fields [ $modSeqField ['name'] ]);
		$smarty->display ('MassEditView.tpl');
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: text/plain');
		echo $e->getMessage ();
	}
	exit ();
