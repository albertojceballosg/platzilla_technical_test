<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Data/GraphicManager.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('modules/Settings/lib/PanelViewHelper.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');
	require_once ('modules/historymanager/lib/RecordHistoryHelper.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	
	// Asegura que el objeto global $smarty esté correctamente instanciado
	global $smarty;
	if (!isset($smarty) || !$smarty instanceof vtigerCRM_Smarty) {
		$smarty = new vtigerCRM_Smarty();
	}

	global $adb, $app_strings, $current_language, $currentModule, $current_user, $singlepane_view, $theme, $mod_strings;

	$action         = PlatzillaUtils::purify ($_GET, 'action');
	$formodule      = PlatzillaUtils::purify ($_GET, 'formodule');
	$record         = PlatzillaUtils::purify ($_GET, 'record');
	$editPermission = PlatzillaUtils::purify ($_GET,'editpermission');
	$isModal        = PlatzillaUtils::purify ($_GET,'Ajax', false);

	$category        = getParentTab ();
	$dateTo          = date_create ()->format ('Y-m-d');
	$historyThMonth  = date_create ()->modify ('-3 month')->format ('Y-m-d');
	$historyToday    = date_create ()->modify ('-1 day')->format ('Y-m-d');
	$historyWeek     = date_create ()->modify ('-7 day')->format ('Y-m-d');
	$dateMonth       = date_create ()->modify ('-1 month')->format ('Y-m-d');
	$historySixMonth = date_create ()->modify ('-6 month')->format ('Y-m-d');
	$historyYear     = date_create ()->modify ('-12 month')->format ('Y-m-d');

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

			$moduleSubscription = $psm->fetchModuleSubscription ($_SESSION ['platInstancia'], $formodule);
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

		$applications     = PlatformUtils::getApplicationsByUserRole ($adb, $current_user->column_fields ['roleid'], $formodule);
		$canCreateRecords = ($moduleSubscription->getMaxRecords () == -1) || ($moduleSubscription->getMaxRecords () > $moduleSubscription->getTotalRecords ());
	} else {
		$applications     = PlatformUtils::getApplicationsByModuleName ($adb, $formodule);
		$canCreateRecords = true;
	}
	/** @var CRMEntity|stdClass $entity */
	$entity = CRMEntity::getInstance ($formodule);

	if ($record != '') {
		$entity->id   = $record;
		$entity->mode = 'edit';
		$mode         = 'edit';
		$entity->retrieve_entity_info ($record, $currentModule);
	}

	$blocksData = getBlocks ($formodule, 'detail_view', '', $entity->column_fields, '', null);

	$arguments = array (
		'module'   => $formodule,
		'dayFrom'  => '',
		'dayTo'    => '',
		'record'   => $record,
		'sql'      => 1,
		'fieldIds' => '',
		'language' => $current_language,
	);

	$fieldsList      = RecordHistoryHelper::getColumnsByModule ($adb, $formodule);
	$hasNumericField = 'NO';
	foreach ($fieldsList as $field) {
		if (in_array($field ['uitype'], array(FieldInterface::UI_TYPE_NUMBER, FieldInterface::UI_TYPE_PERCENTAGE, FieldInterface::UI_TYPE_CALCULATED_LINK))) {
			$hasNumericField = 'YES';
			break;
		}
	}

	if (($singlepane_view != 'true') || ($action == 'RecordHistory')) {
		/** @var  $userCharts, $current_user->id */
		$userCharts = GraphicManager::getInstance ($adb)->fetchAllFavoriteByModule (null, $formodule);

		try {
			if ($isModal) {
				throw new Exception ('is modal');
			}
			$relatedListCards = GridViewHelper::fetchRelatedList (
				array (
					'adb'           => $adb,
					'relatedList'   => getRelatedLists ($formodule, $entity),
					'recordId'      => $record,
					'currentModule' => $formodule,
					'entity'        => $entity,
					'resetCookie'   => ($_SESSION ['rlvs'][ $formodule ][ $relationId ]['currentRecord'] != $record) ? true : false,
					'app_strings'   => $app_strings,
					'mod_strings'   => $mod_strings,
					'theme'         => $theme,
				)
			);
			$_SESSION ['rlvs'][$formodule ][ $relationId ]['currentRecord'] = $record;
		} catch (Exception $e) {
			$relatedListCards = null;
		}

		$entityIdentifierFieldValue = '';
		if (($record != '') && (!empty ($formodule))) {
			$focus = CRMEntity::getInstance ($formodule);
			$focus->retrieve_entity_info ($record, $formodule);
			$focus->id                  = $record;
			$moduleHeaders              = ModuleManager::getInstance ($adb)->fetchModule ($formodule, true);
			$entityIdentifierFieldName  = $moduleHeaders->getFieldIdentifier ();
			$entityIdentifierFieldValue = $focus->column_fields [ $entityIdentifierFieldName ];
		}

		//$smarty = new vtigerCRM_Smarty ();
		if (isPermitted ($currentModule, 'EditView', $record) == 'yes') {
			$smarty->assign ('EDIT_DUPLICATE', 'permitted');
		}
		if (isPermitted ($currentModule, 'Delete', $record) == 'yes') {
			$smarty->assign ('DELETE', 'permitted');
		}
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('AVAILABLE_TYPES', RecordHistoryHelper::getDefinedGraphTypes ());
		$smarty->assign ('BLOCKS', $blocksData);
		$smarty->assign ('CATEGORY', $category);
		$smarty->assign ('CAN_CREATE_RECORDS', $canCreateRecords);
		$smarty->assign ('CHECK', Button_Check ($currentModule));
		$smarty->assign ('COLORS', array ('#2ecc71', '#e74c3c', '#f39c12', '#3fcfbb', '#626f70', '#8f44ad'));
		$smarty->assign ('CURRENT_USER_NAME', $current_user->column_fields['first_name'] . ' ' . $current_user->column_fields['last_name']);
		$smarty->assign ('CUSTOM_MODULE', true);
		$smarty->assign ('EDIT_PERMISSION', $editPermission);
		$smarty->assign ('ENTITY_IDENTIFIER_VALUE', $entityIdentifierFieldValue);
		$smarty->assign ('FIELD_LIST', $fieldsList);
		$smarty->assign ('GRAPHS', count ($userCharts));
		$smarty->assign ('HAS_NUM_FIELD', $hasNumericField);
		$smarty->assign ('HISTORICALRECORDS', RecordHistoryHelper::getHistoryDataFromModule ($adb, $arguments));
		$smarty->assign ('HISTORY_DATE_FROM', $historyThMonth);
		$smarty->assign ('HISTORY_DATE_TO', $historyToday);
		$smarty->assign ('HISTORY_MONTH', $dateMonth);
		$smarty->assign ('HISTORY_SIX_MONTH', $historySixMonth);
		$smarty->assign ('HISTORY_TH_MONTH', $historyThMonth);
		$smarty->assign ('HISTORY_TODAY', $historyToday);
		$smarty->assign ('HISTORY_WEEK', $historyWeek);
		$smarty->assign ('HISTORY_YEAR', $historyYear);
		$smarty->assign ('ID', $record);
		$smarty->assign ('IS_ADMIN', is_admin ($current_user));
		$smarty->assign ('IS_MODAL',($isModal) ? true : false);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULE', $formodule);
		$smarty->assign ('PROFILE_IDS', null);
		$smarty->assign ('RELHISTORY', RecordHistoryHelper::getHistoricalRelatedEvents($adb, $arguments));
		$smarty->assign ('RELATED_LIST_CARD', $relatedListCards);
		$smarty->assign ('SEARCH_FORM', '');
		$smarty->assign ('SINGLE_MOD', $formodule);
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('VIEW_TASK', PanelViewHelper::getStatusModule ($adb, $formodule, null));
		$smarty->display ('RecordHistory.tpl');
	} else {
		header ("Location:index.php?action=DetailView&module=$currentModule&record=$record&parenttab=$category");
	}
