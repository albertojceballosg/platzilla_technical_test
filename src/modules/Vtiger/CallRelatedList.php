<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/ListView/RelatedListViewSession.php');
	require_once ('include/platzilla/Data/GraphicManager.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DetailViewUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	require_once ('user_privileges/default_module_view.php');

	global $adb, $mod_strings, $app_strings, $currentModule, $current_user, $theme, $singlepane_view;

	$action         = isset ($_REQUEST ['action']) ? vtlib_purify ($_REQUEST ['action']) : null;
	$record         = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : '';
	$editPermission = isset ($_REQUEST ['editpermission']) ? vtlib_purify ($_REQUEST ['editpermission']) : '';
	$isModal        = isset ($_REQUEST ['Ajax']) ? true : false;
	$category = getParentTab ();

	if (($singlepane_view != 'true') || ($action != 'CallRelatedList')) {
		$isDuplicate      = isset ($_REQUEST ['isDuplicate']) ? vtlib_purify ($_REQUEST ['isDuplicate']) : null;
		$relationId       = (isset ($_REQUEST ['relation_id'])) && (!empty ($_REQUEST ['relation_id'])) ? vtlib_purify ($_REQUEST ['relation_id']) : null;
		$selectedHeader   = (isset ($_REQUEST ['selected_header'])) && (!empty ($_REQUEST ['selected_header'])) ? vtlib_purify ($_REQUEST ['selected_header']) : null;
		$mode             = (isset ($_REQUEST ['mode'])) && (!empty ($_REQUEST ['mode'])) ? trim (vtlib_purify ($_REQUEST ['mode'])) : null;

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

		$toolButtons = Button_Check ($currentModule);

		/** @var CRMEntity|stdClass $focus */
		$focus = CRMEntity::getInstance ($currentModule);
		if ($record != '') {
			$focus->retrieve_entity_info ($record, $currentModule);
			$focus->id = $record;
		}

		$blocksData = getBlocks ($currentModule, 'detail_view', '', $entity->column_fields, '', null);

		if ($isDuplicate == 'true') {
			$focus->id = '';
		}

		if (!$_SESSION ['rlvs'][ $currentModule ]) {
			unset ($_SESSION ['rlvs']);
		}

		$userCharts = GraphicManager::getInstance ($adb)->fetchAllFavoriteByModule ($current_user->id, $currentModule);

		// Module Sequence Numbering
		$modSeqField = getModuleSequenceField ($currentModule);
		$modSeqId    = ($modSeqField != null) ? $focus->column_fields [ $modSeqField ['name'] ] : $focus->id;

		if ((!empty ($_REQUEST ['selected_header'])) && ($relationId !== null)) {
			RelatedListViewSession::addRelatedModuleToSession ($relationId, $selectedHeader);
		}

		$moduleHeaders             = ModuleManager::getInstance ($adb)->fetchModule ($currentModule, true);
		$entityIdentifierFieldName = $moduleHeaders->getEntityIdentifier ();

		$smarty = new vtigerCRM_Smarty ();
		if (isPermitted ($currentModule, 'EditView', $record) == 'yes') {
			$smarty->assign ('EDIT_DUPLICATE', 'permitted');
		}
		if (isPermitted ($currentModule, 'Delete', $record) == 'yes') {
			$smarty->assign ('DELETE', 'permitted');
		}
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('BLOCKS', $blocksData);
		$smarty->assign ('CAN_CREATE_RECORDS', $canCreateRecords);
		$smarty->assign ('CATEGORY', $category);
		$smarty->assign ('CHECK', $toolButtons);
		$smarty->assign ('CUSTOM_MODULE', true);
		$smarty->assign ('EDIT_PERMISSION', $editPermission);
		$smarty->assign ('ENTITY_IDENTIFIER_VALUE', $focus->column_fields [ $entityIdentifierFieldName ]);
		$smarty->assign ('GRAPHS', count ($userCharts));
		$smarty->assign ('ID', $focus->id);
		$smarty->assign ('IS_MODAL',$isModal);
		$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MOD_SEQ_ID', $modSeqId);
		$smarty->assign ('MODE', $focus->mode);
		$smarty->assign ('MODULE', $currentModule);
		$smarty->assign ('NAME', $focus->column_fields[ $focus->def_detailview_recname ]);
		$smarty->assign ('RELATEDLISTS', getRelatedLists ($currentModule, $focus));
		$smarty->assign ('SELECTEDHEADERS', RelatedListViewSession::getRelatedModulesFromSession ());
		$smarty->assign ('SINGLE_MOD', getTranslatedString ("SINGLE_{$currentModule}", $currentModule));
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('UPDATEINFO', updateInfo ($focus->id));
		if ($mode != '') {
			$smarty->assign ('OP_MODE', $mode);
		}
		$smarty->display ('RelatedLists.tpl');
	} else {
		header ("Location:index.php?action=DetailView&module=$currentModule&record=$record&parenttab=$category");
	}
