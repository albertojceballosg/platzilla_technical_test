<?php
global $smarty;
if (!isset($smarty) || !$smarty instanceof vtigerCRM_Smarty) {
    $smarty = new vtigerCRM_Smarty();
}

    require_once ('data/CRMEntity.php');
    require_once ('include/database/PearDatabase.php');
    require_once ('include/ListView/ListViewSession.php');
    require_once ('include/platzilla/Data/GraphicManager.php');
    require_once ('include/platzilla/Managers/ModuleEditPermissionManager.php');
    require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
    require_once ('include/platzilla/Managers/TableFieldManager.php');
    require_once ('include/platzilla/Objects/FieldInterface.php');
    require_once ('include/platzilla/Objects/NotificationInterface.php');
    require_once ('include/platzilla/Utils/JSDetailViewEditableUtils.php');
    require_once ('include/platzilla/Utils/JSGraphicUtils.php');
    require_once ('include/utils/AttachmentsUtils.class.php');
    require_once ('include/utils/CommonUtils.php');
    require_once ('include/utils/DetailViewUtils.php');
    require_once ('include/utils/EditViewUtils.class.php');
    require_once ('include/utils/EntityCommentsUtils.class.php');
    require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/ProcessCasesUtils.class.php');
    require_once ('include/utils/UserInfoUtil.php');
    require_once ('include/utils/utils.php');
    require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
    require_once ('modules/diagnostic_report/lib/DiagnosticReportHelper.php');
    require_once ('modules/grid_view/lib/GridViewHelper.class.php');
    require_once ('modules/Home/lib/HomeUtils.class.php');
    require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');
    require_once ('modules/notifications/lib/NotificationUtils.class.php');
    require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('modules/orden_de_trabajo/handlers/taskToWork.class.php');
    require_once ('modules/PickList/PickListUtils.php');
    require_once ('modules/Settings/lib/HowToHelper.class.php');
	require_once ('modules/Settings/lib/PanelViewHelper.class.php');
    require_once ('modules/store/lib/StoreUtils.class.php');
    require_once ('modules/webmail/lib/WebmailUtils.class.php');
    require_once ('Smarty_setup.php');
    require_once ('user_privileges/default_module_view.php');
    require_once ('vtlib/Vtiger/Link.php');

	global $adb, $app_strings, $mod_strings, $currentModule, $current_language, $current_user, $singlepane_view, $theme, $relationId, $modalEdit;

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

	$action         = isset ($_REQUEST ['action']) ? vtlib_purify ($_REQUEST ['action']) : null;
	$activeCard     = isset ($_REQUEST ['card_tab']) ? vtlib_purify ($_REQUEST ['card_tab']) : 'ITERATIONS';
	$record         = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : '';
	$isDuplicate    = isset ($_REQUEST ['isDuplicate']) ? vtlib_purify ($_REQUEST ['isDuplicate']) : null;
	$mode           = isset ($_REQUEST ['mode']) ? vtlib_purify ($_REQUEST ['mode']) : null;
	$isModal        = isset ($_REQUEST ['Ajax']) ? true : false;
	$profileIds     = isset ($_REQUEST ['profileids']) ? vtlib_purify ($_REQUEST ['profileids']) : null;
	$relationId     = (isset ($_REQUEST ['relation_id'])) && (!empty ($_REQUEST ['relation_id'])) ? vtlib_purify ($_REQUEST ['relation_id']) : null;
	$selectedHeader = (isset ($_REQUEST ['selected_header'])) && (!empty ($_REQUEST ['selected_header'])) ? vtlib_purify ($_REQUEST ['selected_header']) : null;
	$selectedTab    = (!empty ($_REQUEST ['tab'])) ? vtlib_purify ($_REQUEST ['tab']) : null;
	$caseNumber     = (isset ($_REQUEST ['case_number'])) ? vtlib_purify ($_REQUEST ['case_number']) : null;

	if (!isset ($_REQUEST ['module'])) {
		$_REQUEST ['module'] = $currentModule;
	}

	$profileIds = !empty ($profileIds) ? explode (',', $profileIds) : null;

	/** @var CRMEntity|stdClass $entity */
	$entity           = CRMEntity::getInstance ($currentModule);
	$toolButtons      = Button_Check ($currentModule);
	$tabId            = getTabid ($currentModule);
	$category         = getParentTab ();
	$swDetailViewGrid = true;

	if ($record != '') {
		$entity->id   = $record;
		$entity->mode = 'edit';
		// Only set mode to 'edit' if not already specified in URL (e.g., from cancel action)
		if (empty($mode)) {
			$mode = 'edit';
		}
		$entity->retrieve_entity_info ($record, $currentModule);
		$caseNumber = (empty($caseNumber)) ? $entity->case_number : $caseNumber;
	}
	
	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	// Skip background tasks in preview mode for faster loading
	if (!$isModal) {
		BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $entity);
	}
	$adb->setDieOnError ($oldDieOnError);

	if ($isDuplicate == 'true') {
		$entity->id = '';
	}

	$recordName = array_values (getEntityName ($currentModule, $entity->id));
	$recordName = $recordName [0];

	// Module Sequence Numbering
	$modSeqField = getModuleSequenceField ($currentModule);
	$modSeqId    = ($modSeqField != null) ? $entity->column_fields [ $modSeqField ['name'] ] : $entity->id;

	$validationArray = EditViewUtils::splitValidationData (getDBValidationData ($entity->tab_name, $tabId));

	// Gather the custom link information to display
	$customLinkParams = array (
		'MODULE' => $currentModule,
		'RECORD' => $entity->id,
		'ACTION' => $action,
	);

	$blocksData = getBlocks ($currentModule, 'detail_view', '', $entity->column_fields, '', $profileIds);

	// Eliminar de la información de bloques obtenidos aquellos campos que están condicionados
	if (!empty ($record)) {
		$conditioningFieldNames = array ();
		$result                 = $adb->pquery (
			'SELECT
				f.fieldname,
				f.uitype
			FROM
				vtiger_field f
				INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
			WHERE
				f.uitype IN (?, ?, ?)',
			array ($currentModule, FieldInterface::UI_TYPE_GLOBAL_PICKLIST, FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_PICKLIST)
		);
		if (($result) && ($adb->num_rows ($result) > 0)) {
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$conditioningFieldNames [ $row ['uitype'] ] = $row ['fieldname'];
			}
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		
		$conditionedFieldIds = array ();
		if (!empty ($conditioningFieldNames)) {
			foreach ($conditioningFieldNames as $uiType => $fieldName) {
				if (in_array ($uiType, array (FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_PICKLIST))) {
					$leftJoinClause = "LEFT JOIN vtiger_{$fieldName} pl ON pl.{$fieldName}=fd.sourcefieldvalue";
					if (empty ($entity->column_fields [ $fieldName ])) {
						$whereClause = "AND pl.{$fieldName} IS NULL";
						$arguments   = array ();
					} else {
						$whereClause = "AND pl.{$fieldName}=?";
						$arguments   = array ($entity->column_fields [ $fieldName ]);
					}
				} elseif ($uiType == FieldInterface::UI_TYPE_GLOBAL_PICKLIST) {
					// Los picklists globales usan vtiger_globalpicklists_values, no tabla propia
					// Omitimos el LEFT JOIN y ajustamos el whereClause para no referenciar 'pl'
					$leftJoinClause = '';
					if (empty ($entity->column_fields [ $fieldName ])) {
						// Para valor vacío, buscamos donde sourcefieldvalue esté vacío o sea NULL
						$whereClause = "AND (fd.sourcefieldvalue IS NULL OR fd.sourcefieldvalue='')";
						$arguments   = array ();
					} else {
						$whereClause = "AND fd.sourcefieldvalue=?";
						$arguments   = array ($entity->column_fields [ $fieldName ]);
					}
				} else {
					$leftJoinClause = '';
					$whereClause    = '';
					$arguments      = array ();
				}
				$result = $adb->pquery (
					"SELECT DISTINCT
						tf.fieldid
					FROM
						vtiger_fielddependencies fd
						INNER JOIN vtiger_tab t ON t.name=fd.modulename AND t.name=?
						INNER JOIN vtiger_field tf ON tf.fieldname=fd.targetfieldname AND tf.tabid=t.tabid
						{$leftJoinClause}
					WHERE
						fd.targetfieldvisibility=? AND
						fd.sourcefieldname=?
						{$whereClause}",
					array_merge (array ($currentModule, FieldDependencyInterface::VISIBILITY_HIDDEN, $fieldName), $arguments)
				);
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					continue;
				}
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$conditionedFieldIds [] = $row ['fieldid'];
				}
			}
		}
		if (method_exists ($focus,'hideFields')) {
			$focus->hideFields ($conditionedFieldIds);
		}
		if (!empty ($conditionedFieldIds)) {
			foreach ($blocksData as $blockLabel => $blockData) {
				foreach ($blockData as $row => $fields) {
					foreach ($fields as $fieldLabel => $fieldData) {
						$fieldId = $fieldData ['fldid'];
						if (in_array ($fieldId, $conditionedFieldIds)) {
							unset ($blocksData [ $blockLabel ][ $row ][ $fieldLabel ]);
						}
					}
				}
			}
		}

		$editPermission = isPermitted ($currentModule, 'EditView', $record);
		if ($editPermission == 'yes') {
			$editPermission = ModuleEditPermissionManager::getInstance ($adb)->isEditable ($currentModule, $entity) ? 'yes' : 'no';
		}
	} else {
		$editPermission = 'yes';
	}
	$notificationData = array (
		'module'   => $currentModule,
		'user'     => $current_user,
		'view'     => Notification::DETAIL_VIEW,
		'style'    => Notification::STYLE_NOTIFY,
		'recordId' => $record,
		'mode'     => $mode,
		'platform' => $_SESSION ['plat'],
	);

	// Add ALERT notifications data
	$notificationDataAlert = array (
		'module'   => $currentModule,
		'user'     => $current_user,
		'view'     => Notification::DETAIL_VIEW,
		'style'    => Notification::STYLE_ALERT,
		'recordId' => $record,
		'mode'     => $mode,
		'platform' => $_SESSION ['plat'],
	);

	$notificationDataModal           = $notificationData;
	$notificationDataModal ['style'] = Notification::STYLE_MODAL;
	$notificationDataModal ['mode']  = $mode;
	// Check if pending_modal_id is in GET (fallback if session fails)
	// This ensures the modal ID persists even if session_write_close() doesn't work
	if (isset($_GET['pending_modal_id']) && !empty($_GET['pending_modal_id'])) {
		$notificationDataModal['pending_modal_id'] = intval($_GET['pending_modal_id']);
	}

	$hasModuleConatact = true;
	if (!empty ($_SESSION ['platInstancia'])) {
		$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($_SESSION ['platInstancia']);
		if (!PlatformUtils::isModuleEnabled ($targetAdb, 'contactos')) {
			$hasModuleConatact = false;
		}
	}

	// Skip chat, notifications and emails in preview mode
	if (!$isModal) {
		$searchParameter              = NotificationHelper::getInitialParameters ();
		$searchParameter ['recordId'] = $record;
		$searchParameter ['module']   = $currentModule;
		$parleyArray                  = NotificationHelper::searchParleyByWhere ($adb, $current_user, $searchParameter);
		$activeUserToChat             = NotificationHelper::fetchActiveUserByRecord ($adb, $record);
		$relatedUserToChat            = NotificationHelper::fetchRelatedUserByRecord ($adb, $record, $currentModule);
		$relatedUserToChat []         = $record;
		$totalActiveUser              = count ($activeUserToChat);
		$activeUserToChat             = array_diff ($activeUserToChat, array ($current_user->id));
		$relatedUseresInChat          = array_values (array_unique ($activeUserToChat));
		$keysActiveUsers              = array_keys ($activeUserToChat);
		$lastUserToChat               = (!empty ($activeUserToChat)) ? $activeUserToChat [ $keysActiveUsers [0] ] : 0;
		$activeUserToChat             = array_unique (array_merge ($activeUserToChat, $relatedUserToChat));
		$relatedEmailsData            = WebmailUtils::fetchRelatedEmailsData ($adb, $record);
	} else {
		// Initialize empty values for preview mode
		$parleyArray         = array ();
		$activeUserToChat    = array ();
		$relatedUserToChat   = array ();
		$totalActiveUser     = 0;
		$relatedUseresInChat = array ();
		$lastUserToChat      = 0;
		$relatedEmailsData   = array ();
	}

	// Skip heavy queries in preview mode - only load when needed for full view
	if (!$isModal) {
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
		// Initialize as empty arrays for preview mode
		$relatedModules = array ();
		$availableUsers = array ();
		$availableGroups = array ();
	}
	// related list to DetailView Related info cards - Skip in preview mode (heavy operation)
	if (!$isModal) {
		try {
			$relatedLists = getRelatedLists ($currentModule, $entity);
			
			// Solo generar las tarjetas de listas relacionadas si el módulo tiene listas configuradas
			if (!empty($relatedLists) && is_array($relatedLists) && count($relatedLists) > 0) {
				$relatedListCards = GridViewHelper::fetchRelatedList (
					array (
						'adb'           => $adb,
						'relatedList'   => $relatedLists,
						'recordId'      => $record,
						'currentModule' => $currentModule,
						'entity'        => $entity,
						'resetCookie'   => (isset($_SESSION['rlvs'][$currentModule][$relationId]['currentRecord']) && $_SESSION['rlvs'][$currentModule][$relationId]['currentRecord'] != $record) ? true : false,
						'app_strings'   => $app_strings,
						'mod_strings'   => $mod_strings,
						'theme'         => $theme,
					)
				);
				$_SESSION ['rlvs'][ $currentModule ][ $relationId ]['currentRecord'] = $record;
			} else {
				$relatedListCards = null;
			}
		} catch (Exception $e) {
			$relatedListCards = null;
		}
	} else {
		$relatedListCards = null;
	}
	
	$howToId                   = HowToHelper::hasHowTo ($adb, $currentModule, $record, 'DetailView');
	$moduleHeaders             = ModuleManager::getInstance ($adb)->fetchModule ($currentModule, true);
	$entityIdentifierFieldName = $moduleHeaders->getFieldIdentifier ();
	if (!$smarty instanceof Smarty) {
		$smarty = new vtigerCRM_Smarty();
	}
	$JSDetailViewEditable = JSDetailViewEditableUtils::getInstance ($adb);
	$smarty->register_function('loadEditableFiels', array(&$JSDetailViewEditable, 'fetchEditableJs'));
	$smarty->assign ('ACTION', $action);
	$smarty->assign ('ACTIVE_APPLICATIONS', $applications);
	$smarty->assign ('ACTIVE_USERS_CHATS', $activeUserToChat);
	$smarty->assign ('ACTIVE_CARD', $activeCard);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('APPLICATION_VIEWS_ENABLED', PlatformUtils::areApplicationViewsEnabled ($adb));
	$smarty->assign ('AVAILABLE_GROUPS', $availableGroups);
	$smarty->assign ('AVAILABLE_PICKLISTS', getUserFldArray ($currentModule, $current_user->column_fields ['roleid']));
	$smarty->assign ('AVAILABLE_USERS', $availableUsers);
	$smarty->assign ('BLOCKS', $blocksData);
	// Skip grid fields processing in preview mode (can be heavy)
	if (!$isModal) {
		$gridData = escribeCamposGrid ($currentModule, $entity->id, $swDetailViewGrid);
		$smarty->assign ('CAMPOS_TIPO_GRID', $gridData);
	} else {
		$smarty->assign ('CAMPOS_TIPO_GRID', array ());
	}
	$smarty->assign ('CAN_CREATE_RECORDS', $canCreateRecords);
	$smarty->assign ('CATEGORY', $category);
	$smarty->assign ('CHATS', $parleyArray);
	$smarty->assign ('CHECK', $toolButtons);
	// Skip comments in preview mode
	$smarty->assign ('COMMENTS', $isModal ? '' : EntityCommentsUtils::fetchComments ($adb, $entity->id, $_SESSION ['plat']));
	$smarty->assign ('CURRENT_USER_ID', $current_user->id);
	$smarty->assign ('CURRENT_USER_NAME', "{$current_user->column_fields ['first_name']} {$current_user->column_fields ['last_name']}");
	$smarty->assign ('CUSTOM_BUTTONS', PlatformUtils::getCustomButtons ($adb, $currentModule, 'DetailView', $_REQUEST));
	$smarty->assign ('CUSTOM_LINKS', Vtiger_Link::getAllByType (getTabid ($currentModule), array ('DETAILVIEWBASIC', 'DETAILVIEW', 'DETAILVIEWWIDGET'), $customLinkParams));
	$smarty->assign ('CUSTOM_MODULE', true);
	$smarty->assign ('dateFrom', $searchParameter['dateThMonthFrom']);
	$smarty->assign ('dateTo', $searchParameter['dateTo']);
	$smarty->assign ('DETAILVIEW_AJAX_EDIT', PerformancePrefs::getBoolean ('DETAILVIEW_AJAX_EDIT', true));
	$smarty->assign ('EDIT_PERMISSION', $editPermission);
	$smarty->assign ('ENTITY_ATTACHMENTS', AttachmentsUtils::fetchEntityAttachments ($adb, $record));
	$smarty->assign ('ENTITY_IDENTIFIER_VALUE', $entity->column_fields [ $entityIdentifierFieldName ]);
	$smarty->assign ('FIELD_ATTACHMENTS', AttachmentsUtils::fetchFieldAttachments ($adb, $record, $currentModule));
	// Skip grid view in preview mode
	$smarty->assign ('GRID_VIEW', $isModal ? null : GridViewHelper::fetchGridViewByModule ($adb, $currentModule, $record, $_SESSION ['plat'], $current_user));
	$smarty->assign ('RELATED_LIST_CARD', $relatedListCards);
	$smarty->assign ('hasConatact', $hasModuleConatact);
	// Skip process cases in preview mode
	$smarty->assign ('PROCESS_CASE', $isModal ? null : ProcessCasesUtils::fetchCaseByCode ($adb, $caseNumber, $entity, $current_user));
	$smarty->assign ('HIDDEN_GNL_BUTTON', ($isModal) ? false : true);
	$smarty->assign ('HOW_TO_ID', (!empty ($howToId)) ? $howToId : null);
    $smarty->assign ('ID', $entity->id);
	// Skip on-screen notifications in preview mode
	if (!$isModal) {
		$modalId = NotificationUtils::fetchApplicableOnScreenNotificationsModal ($adb, $notificationDataModal);
	} else {
		$modalId = null;
	}
	$smarty->assign ('ID_NOTIFICATION_MODAL', $modalId);
	$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
$smarty->assign ('IS_ADMIN', is_admin ($current_user));
$smarty->assign ('IS_MODAL',$isModal);
$isRelListValue = isPresentRelatedLists ($currentModule);
$smarty->assign ('IS_REL_LIST', $isRelListValue);
	$smarty->assign ('LAST_USERS_CHATS', $lastUserToChat);
	$smarty->assign ('lastMonth', $searchParameter['lastMonth']);
	$smarty->assign ('lastThMonth', $searchParameter['lastThMonth']);
	$smarty->assign ('lastWeek', $searchParameter['lastWeek']);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODCHAT', return_module_language ($current_language, 'notification_center'));
	$smarty->assign ('MOD_SEQ_ID', $modSeqId);
	$smarty->assign ('MODE', $entity->mode);
	$smarty->assign ('MODULE', $currentModule);
	// Skip notifications and alerts in preview mode
	if (!$isModal) {
		$smarty->assign ('NOTIFICATIONS', NotificationUtils::fetchApplicableOnScreenNotifications ($adb, $notificationData));
		$smarty->assign ('ALERTS', NotificationUtils::fetchApplicableOnScreenNotifications ($adb, $notificationDataAlert));
	} else {
		$smarty->assign ('NOTIFICATIONS', array ());
		$smarty->assign ('ALERTS', array ());
	}
	$smarty->assign ('NAME', $recordName);
	$smarty->assign ('ORGANIZATION_CURRENCY', HomeUtils::getOrganizationCurrency ($adb));
	$smarty->assign ('PROFILE_IDS', $profileIds);
	$smarty->assign ('RECORD', $record);
	$smarty->assign ('RELATED_EMAILS_DATA', $relatedEmailsData);
	$smarty->assign ('RELATED_MODULES', $relatedModules);
	$smarty->assign ('RELATED_USERS_CHAT', json_encode ($relatedUseresInChat));
	// Skip user chat search in preview mode
	$smarty->assign ('SEARCH_USERS_CHATS', $isModal ? json_encode (array ()) : json_encode (NotificationHelper::fetchUserToChat ($adb, $_SESSION ['plat'])));
	$smarty->assign ('SELECTED_TAB', $selectedTab);
	$smarty->assign ('SINGLE_MOD', "SINGLE_{$currentModule}");
	$smarty->assign ('SinglePane_View', $singlepane_view);
	$smarty->assign ('TAB_DETAIL', (!isset ($tabDetail)) ? 'StandardDetailView.tpl' : $tabDetail);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('today', $searchParameter['todayTime']);
	// Skip table fields in preview mode
	$smarty->assign ('TABLE_FIELDS', $isModal ? null : TableFieldManager::getInstance ($adb)->fetchTableFieldByModule ($currentModule));
	$smarty->assign ('TOTAL_ACTIVE_USERS_CHATS', $totalActiveUser);
	// Skip sync count in preview mode
	$smarty->assign ('TOTAL_SYNCS', $isModal ? 0 : DataSharingUtils::fetchTotalSyncs ($adb, $_SESSION ['platInstancia'], $currentModule, $record));
	$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
	$smarty->assign ('UPDATEINFO', updateInfo ($entity->id));
	// Skip users chat list in preview mode
	$smarty->assign ('USERS_CHATS', $isModal ? array () : NotificationHelper::fetchUserToChat ($adb, $_SESSION ['plat']));
	$smarty->assign ('VALIDATION_DATA_FIELDDATATYPE', $validationArray ['datatype']);
	$smarty->assign ('VALIDATION_DATA_FIELDLABEL', $validationArray ['fieldlabel']);
	$smarty->assign ('VALIDATION_DATA_FIELDNAME', $validationArray ['fieldname']);
	$smarty->assign ('VALUED_FUNCTIONS', (isset ($tabDetail) && !empty ($record)) ? DiagnosticReportHelper::fetchValuedFunction ($adb, $record) : null);
	$smarty->assign ('VIEW_TASK', PanelViewHelper::getStatusModule ($adb, $currentModule, null));
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
		$isRemovable = ModuleEditPermissionManager::getInstance ($adb)->isRemovable ($currentModule, $entity->id);
		if ($currentModule == 'orden_de_trabajo') {
			if ($entity->column_fields['estado_de_la_orden'] == 'Terminado') {
				$isRemovable = false;
			} else {
				// Un trabajo puede eliminarse si todas sus tareas pueden eliminarse
				// (no tienen reportes de avance ni relaciones con otros registros)
				$isRemovable = taskToWork::getInstance($adb)->areAllTasksRemovable ($entity->id);
			}
		}
		$smarty->assign ('DELETE', ($isRemovable) ? 'permitted' : null);
		
		
	}
	if ((PerformancePrefs::getBoolean ('DETAILVIEW_RECORD_NAVIGATION', true)) && (isset ($_SESSION ["{$currentModule}_listquery"]))) {
		$recordNavigationInfo = ListViewSession::getListViewNavigation ($entity->id);
		VT_detailViewNavigation ($smarty, $recordNavigationInfo, $entity->id);
	}
	if (isset ($_SESSION ['flashmessage'])) {
		$isError = $_SESSION ['flashmessage']['iserror'];
		$message = $_SESSION ['flashmessage']['message'];
		if ($isError && $message === 'EMPTY_RESULT') {
			error_log(sprintf(
				"[DetailView] EMPTY_RESULT module=%s record=%s action=%s mode=%s",
				isset($_REQUEST['module']) ? $_REQUEST['module'] : '',
				isset($_REQUEST['record']) ? $_REQUEST['record'] : '',
				isset($_REQUEST['action']) ? $_REQUEST['action'] : '',
				isset($_REQUEST['mode']) ? $_REQUEST['mode'] : ''
			));
			$message = 'No se pudo completar la operación por un problema interno. Recarga la página e inténtalo de nuevo.';
		}
		$smarty->assign ('IS_ERROR', $isError);
		$smarty->assign ('MESSAGE', $message);
		unset ($_SESSION ['flashmessage']);
	}
	// Control panel's tab $current_user->id
	$userCharts = GraphicManager::getInstance ($adb)->fetchAllFavoriteByModule (null, $currentModule);
	if (count ($userCharts)) {
		$categories = GraphUtils::getCategories ();
		foreach ($categories as $key => $category) {
			$categoryCatalg [ $key ] = array (
				'app_code' => $key,
				'app_name' => $category,
			);
		}

		$objectDate     = new DateTime();
		$dateTo         = $objectDate->format ('Y-m-d');
		$objectDate     = new DateTime();
		$objectDate->modify ('-3 month');
		$dateFrom       = $objectDate->format ('Y-m-d');
		$dateFilter = array (
			'dateFrom' => $dateFrom,
			'dateTo'   => $dateTo,
		);

		// Obtener los gráficos básicos
		$graphs = array (
			'applications'     => array (),
			'boxscoresimple'   => array (),
			'boxscoreadvanced' => array (),
			'others'           => array (),
		);

		GraphicManager::getInstance($adb)->getBasicGraphics ($graphs, $isInstance, $categories, $dateFilter, $userCharts);
		$graphsUtils = JSGraphicUtils::getInstance ($adb);

		$smarty->register_function ('loadGraphic', array(&$graphsUtils, 'fetchGoogleChartJs'));
		$smarty->assign ('ACTIVE_TAB', '');
		$smarty->assign ('APPLICATIONS', $categoryCatalg);
		$smarty->assign ('COLORS', array ('#2ecc71', '#e74c3c', '#f39c12', '#3fcfbb', '#626f70', '#8f44ad'));
		$smarty->assign ('FAVORITES', $favoriteCharts);
		$smarty->assign ('FLMODULE', $currentModule);
		$smarty->assign ('GRAPHS', $graphs);
		$smarty->assign ('IS_ADMIN', is_admin ($current_user));
		$smarty->assign ('IS_FAVORITES', true);
		$smarty->assign ('IS_HOME', false);
		$smarty->assign ('OPERATIONS', GraphUtils::getDefinedOperations ());
	} else {
		$smarty->assign ('GRAPHS', null);
	}
	
	// Variables para el botón de crear informe diario con menú de fechas
	if ($currentModule == 'daily_report') {
		require_once ('modules/daily_report/lib/DailyReportUtils.class.php');
		$reportedDays      = DailyReportUtils::fetchDailyReportDateByUser ($adb, $current_user->id);
		$today             = date ('Y-m-d');
		$yesterday         = date ('Y-m-d', strtotime ('-1 days'));
		$reportToDay       = "{$today}@{$current_user->id}";
		$reportToYesterday = "{$yesterday}@{$current_user->id}";
		$smarty->assign ('HEADER_TODAY', $today);
		$smarty->assign ('HEADER_YESTERDAY', $yesterday);
		$smarty->assign ('REPORTED_DAYS', (is_array ($reportedDays)) ? join (';', $reportedDays) : null);
		$smarty->assign ('REPORT_TODAY', base64_encode ($reportToDay));
		$smarty->assign ('REPORT_YESTERDAY', base64_encode ($reportToYesterday));
		$smarty->assign ('HEADER_DATA_DAILY_REPORT', $headerDataDailyReport);
	}

// Asignar variables de formato para JavaScript (como en EditView)
	$smarty->assign('NUMBERING_FORMAT', $current_user->numbering_format);
	$smarty->assign('USER_DATE_FORMAT', $current_user->date_format ?: 'yyyy-mm-dd');

$smarty->display('DetailView.tpl');

	// Record Change Notification
	$entity->markAsViewed ($current_user->id);

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $entity);
	$adb->setDieOnError ($oldDieOnError);
