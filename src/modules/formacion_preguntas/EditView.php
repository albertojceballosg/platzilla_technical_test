<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/EditViewUtils.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/PickList/DependentPickListUtils.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $currentModule, $theme;

	if (!empty ($_SESSION ['platInstancia'])) {
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

	$createMode     = PlatzillaUtils::purify ($_REQUEST, 'createmode');
	$isduplicate    = PlatzillaUtils::purify ($_REQUEST, 'isDuplicate');
	$profileIds     = PlatzillaUtils::purify ($_REQUEST, 'profileids');
	$record         = PlatzillaUtils::purify ($_REQUEST, 'record');
	$returnAction   = PlatzillaUtils::purify ($_REQUEST, 'return_action');
	$returnId       = PlatzillaUtils::purify ($_REQUEST, 'return_id');
	$returnModule   = PlatzillaUtils::purify ($_REQUEST, 'return_module');
	$returnViewName = PlatzillaUtils::purify ($_REQUEST, 'return_viewname');

	$profileIds = !empty ($profileIds) ? explode (',', $profileIds) : null;

	if (!$_REQUEST ['frontendsid']) {
		$_REQUEST ['frontendsid'] = $_SESSION ['frontendsid'];
	}

	/** @var CRMEntity|stdClass $focus */
	$focus = CRMEntity::getInstance ($currentModule);
	if ($record) {
		$focus->id   = $record;
		$focus->mode = 'edit';
		$focus->retrieve_entity_info ($record, $currentModule);

		$oldDieOnError = $adb->dieOnError;
		$adb->setDieOnError (false);
		BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('EDIT', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $focus);
		$adb->setDieOnError ($oldDieOnError);
	}
	if ($isduplicate == 'true') {
		$focus->id   = '';
		$focus->mode = '';
	}
	if ((empty ($record)) && ($focus->mode != 'edit')) {
		setObjectValuesFromRequest ($focus);
	}

	$disp_view        = getView ($focus->mode);
	$tabid            = getTabid ($currentModule);
	$validationData   = getDBValidationData ($focus->tab_name, $tabid);
	$validationArray  = EditViewUtils::splitValidationData ($validationData);
	$mod_seq_field    = getModuleSequenceField ($currentModule);
	$swDetailViewGrid = false;

	if (($focus->mode == 'edit') || ($isduplicate)) {
		$recordName = array_values (getEntityName ($currentModule, $record));
		$recordName = $recordName [0];
	} else {
		$recordName = null;
	}

	if (($focus->mode != 'edit') && ($mod_seq_field != null)) {
		$autostr        = getTranslatedString ('MSG_AUTO_GEN_ON_SAVE');
		$mod_seq_string = $adb->pquery ('SELECT prefix, cur_id FROM vtiger_modentity_num WHERE semodule=? AND active=1', array ($currentModule));
		$mod_seq_prefix = $adb->query_result ($mod_seq_string, 0, 'prefix');
		$mod_seq_no     = $adb->query_result ($mod_seq_string, 0, 'cur_id');
	} else {
		$autostr        = null;
		$mod_seq_string = null;
		$mod_seq_prefix = null;
		$mod_seq_no     = null;
	}

	if (!empty ($_SESSION ['platInstancia'])) {
		$applications = PlatformUtils::getApplicationsByUserRole ($adb, $current_user->column_fields ['roleid'], $currentModule);
	} else {
		$applications = PlatformUtils::getApplicationsByModuleName ($adb, $currentModule);
	}

	$smarty = new vtigerCRM_Smarty();
	$smarty->assign ('ACTIVE_APPLICATIONS', $applications);
	$smarty->assign ('ADVBLOCKS', getBlocks ($currentModule, $disp_view, $focus->mode, $focus->column_fields, 'ADV', $profileIds));
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('BASBLOCKS', getBlocks ($currentModule, $disp_view, $focus->mode, $focus->column_fields, 'BAS', $profileIds));
	$smarty->assign ('BLOCKS', getBlocks ($currentModule, $disp_view, $focus->mode, $focus->column_fields, '', $profileIds));
	$smarty->assign ('CALENDAR_DATEFORMAT', parse_calendardate ($app_strings ['NTC_DATE_FORMAT']));
	$smarty->assign ('CALENDAR_LANG', $app_strings ['LBL_JSCALENDAR_LANG']);
	$smarty->assign ('CAMPOS_TIPO_GRID', escribeCamposGrid ($currentModule, $focus->id, $swDetailViewGrid));
	$smarty->assign ('CATEGORY', getParentTab ());
	$smarty->assign ('CHECK', Button_Check ($currentModule));
	$smarty->assign ('CREATEMODE', $createMode);
	$smarty->assign ('DUPLICATE', $isduplicate);
	$smarty->assign ('FIELD_ATTACHMENTS', AttachmentsUtils::fetchFieldAttachments ($adb, $record, $currentModule));
	$smarty->assign ('HELP_ITEMS', HelpSettingsHelper::fetchFieldHelpItems ($applications, $currentModule));
	$smarty->assign ('ID', $focus->id);
	$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODE', $focus->mode);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('OP_MODE', $disp_view);
	$smarty->assign ('PICKIST_DEPENDENCY_DATASOURCE', Zend_Json::encode (Vtiger_DependencyPicklist::getPicklistDependencyDatasource ($currentModule)));
	$smarty->assign ('PROFILE_IDS', $profileIds);
	$smarty->assign ('SEARCH', getBasic_Advance_SearchURL ());
	$smarty->assign ('SINGLE_MOD', 'SINGLE_' . $currentModule);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('VALIDATION_DATA_FIELDNAME', $validationArray ['fieldname']);
	$smarty->assign ('VALIDATION_DATA_FIELDDATATYPE', $validationArray ['datatype']);
	$smarty->assign ('VALIDATION_DATA_FIELDLABEL', $validationArray ['fieldlabel']);
	if (($focus->mode == 'edit') || ($isduplicate)) {
		$smarty->assign ('NAME', $recordName);
		$smarty->assign ('UPDATEINFO', updateInfo ($record));
	}
	if (!empty ($returnAction)) {
		$smarty->assign ('RETURN_ACTION', $returnAction);
	}
	if (!empty ($returnId)) {
		$smarty->assign ('RETURN_ID', $returnId);
	}
	if (!empty ($returnModule)) {
		$smarty->assign ('RETURN_MODULE', $returnModule);
	}
	if (!empty ($returnViewName)) {
		$smarty->assign ('RETURN_VIEWNAME', $returnViewName);
	}

	if (($focus->mode != 'edit') && ($mod_seq_field != null)) {
		if (($adb->num_rows ($mod_seq_string) == 0) || ($focus->checkModuleSeqNumber ($focus->table_name, $mod_seq_field ['column'], $mod_seq_prefix . $mod_seq_no))) {
			echo '<br><font color="#FF0000"><b>' . getTranslatedString ('LBL_DUPLICATE') . ' ' . getTranslatedString ($mod_seq_field ['label'])
				 . ' - ' . getTranslatedString ('LBL_CLICK') . ' <a href="index.php?module=Settings&action=CustomModEntityNo&parenttab=Settings&selmodule=' . $currentModule . '">' . getTranslatedString ('LBL_HERE') . '</a> '
				 . getTranslatedString ('LBL_TO_CONFIGURE') . ' ' . getTranslatedString ($mod_seq_field ['label']) . '</b></font>';
		} else {
			$smarty->assign ('MOD_SEQ_ID', $autostr);
		}
	} else {
		$smarty->assign ('MOD_SEQ_ID', $focus->column_fields [ $mod_seq_field ['name'] ]);
	}

	if ($record) {
		$oldDieOnError = $adb->dieOnError;
		$adb->setDieOnError (false);
		BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('EDIT', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $focus);
		$adb->setDieOnError ($oldDieOnError);
	}

	if ($focus->mode == 'edit') {
		$smarty->display ('modules/formacion_preguntas/EditView.tpl');
	} else {
		$smarty->display ('modules/formacion_preguntas/CreateView.tpl');
	}
