<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('modules/Settings/lib/PanelViewHelper.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/Calendar/Calendar.php');

	global $adb, $app_strings, $current_user, $currentModule, $mod_strings, $theme;

	$activityMode     = PlatzillaUtils::purify ($_REQUEST, 'activity_mode');
	$relatedEntityIds = PlatzillaUtils::purify ($_REQUEST, 'idlist');
	$isDuplicate      = PlatzillaUtils::purify ($_REQUEST, 'isDuplicate');
	$param            = PlatzillaUtils::purify ($_REQUEST, 'param');
	$record           = PlatzillaUtils::purify ($_REQUEST, 'record');
	$returnAction     = PlatzillaUtils::purify ($_REQUEST, 'return_action');
	$returnId         = PlatzillaUtils::purify ($_REQUEST, 'return_id');
	$returnModule     = PlatzillaUtils::purify ($_REQUEST, 'return_module');
	$returnViewName   = PlatzillaUtils::purify ($_REQUEST, 'return_viewname');
	$returnTab        = PlatzillaUtils::purify ($_REQUEST, 'tab', null);
	$fromWork         = PlatzillaUtils::purify ($_REQUEST, 'isWork', null);
	$parentId         = PlatzillaUtils::purify ($_REQUEST, 'parent_id');
	$contactId        = PlatzillaUtils::purify ($_REQUEST, 'contact_id');

	/** @var CRMEntity|stdClass $focus */
	$focus    = CRMEntity::getInstance ($currentModule);
	$category = getParentTab ();
	if (!empty ($record)) {
		$focus->id   = $record;
		$focus->mode = 'edit';
		$focus->retrieve_entity_info ($record, 'Calendar');
		$focus->name = $focus->column_fields ['subject'];

		// Ejecutar tareas en segundo plano antes de editar
		$oldDieOnError = $adb->dieOnError;
		$adb->setDieOnError (false);
		BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('EDIT', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $focus);
		$adb->setDieOnError ($oldDieOnError);

		// Obtener la lista de usuarios invitados
		$result = $adb->pquery (
			'SELECT
				vtiger_users.*,
				vtiger_invitees.*
			FROM
				vtiger_invitees
				LEFT JOIN vtiger_users ON vtiger_invitees.inviteeid=vtiger_users.id
			WHERE
				vtiger_invitees.activityid=?',
			array ($record)
		);
		if ($adb->num_rows ($result) > 0) {
			$i            = 0;
			$invitedUsers = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$userId                   = $row ['inviteeid'];
				$userName                 = getFullNameFromQResult ($result, $i, 'Users');
				$invitedUsers [ $userId ] = $userName;
				$i++;
			}
		} else {
			$invitedUsers = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
	}
	if ($isDuplicate == 'true') {
		$focus->id   = '';
		$focus->mode = '';
	}
	if ((empty ($record)) && ($focus->mode != 'edit')) {
		setObjectValuesFromRequest ($focus);
	}

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

	$relatedEntities = Calendar::getRelatedEntities ($adb, $focus->id);
	if (!empty ($relatedEntityIds)) {
		$relatedEntityIds = explode (';', $relatedEntityIds);
		foreach ($relatedEntityIds as $relatedEntityId) {
			$result = $adb->pquery (
				'SELECT
					en.modulename,
					en.tablename,
					en.entityidcolumn,
					en.fieldname
				FROM
					vtiger_crmentity crme
					INNER JOIN vtiger_entityname en ON en.modulename=crme.setype
				WHERE
					crme.crmid=?',
				array ($relatedEntityId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row         = $adb->fetchByAssoc ($result, -1, false);
				$moduleName  = $row ['modulename'];
				$moduleLabel = getTranslatedString ($row ['modulename'], $row ['modulename']);
				$fieldName   = $row ['fieldname'];
				$result      = $adb->pquery (
					"SELECT {$row ['fieldname']} FROM {$row ['tablename']} WHERE {$row ['entityidcolumn']}=?",
					array ($relatedEntityId)
				);
				if (($result) && ($adb->num_rows ($result) > 0)) {
					$row                = $adb->fetchByAssoc ($result, -1, false);
					$relatedEntities [] = array (
						'activityid'   => $record,
						'crmid'        => $relatedEntityId,
						'label_entity' => "<a title=\"{$moduleLabel}\" href=\"index.php?module={$moduleName}&parenttab=&action=DetailView&record={$relatedEntityId}\">{$row [$fieldName]}</a>",
						'modulename'   => $moduleName,
						'name'         => $moduleLabel,
					);
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
		}
	}

	$result = $adb->query ('SELECT * FROM vtiger_activitytype ORDER BY activitytype');
	if ($adb->num_rows ($result) > 0) {
		$availableActivityTypes = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$activityType                             = $row ['activitytype'];
			$availableActivityTypes [ $activityType ] = $mod_strings [ $activityType ];
		}
	} else {
		$availableActivityTypes = null;
	}
	if ($result instanceof ADORecordSet) {
		$result->Close ();
		$result = null;
	}

	$result = $adb->query ('SELECT * FROM vtiger_eventstatus ORDER BY eventstatus');
	if ($adb->num_rows ($result) > 0) {
		$availableEventStatuses = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$eventStatus                             = $row ['eventstatus'];
			$availableEventStatuses [ $eventStatus ] = $mod_strings [ $eventStatus ];
		}
	} else {
		$availableEventStatuses = null;
	}
	if ($result instanceof ADORecordSet) {
		$result->Close ();
		$result = null;
	}

	$result = $adb->query ('SELECT * FROM vtiger_taskpriority ORDER BY taskpriority');
	if ($adb->num_rows ($result) > 0) {
		$availableTaskPriorities = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$availableTaskPriorities [] = $row ['taskpriority'];
		}
	} else {
		$availableTaskPriorities = null;
	}
	if ($result instanceof ADORecordSet) {
		$result->Close ();
		$result = null;
	}

	$selectedOwnerType = 'U';
	$result            = $adb->query ('SELECT * FROM vtiger_users ORDER BY id');
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
			if ($focus->column_fields ['assigned_user_id'] == $row ['groupid']) {
				$selectedOwnerType = 'T';
			}
		}
	} else {
		$availableGroups = null;
	}
	if ($result instanceof ADORecordSet) {
		$result->Close ();
		$result = null;
	}

	$smarty = new vtigerCRM_Smarty();
	$smarty->assign ('ACTIVITYDATA', $focus->column_fields);
	$smarty->assign ('ACTIVITY_MODE', $activityMode);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('AVAILABLE_ACTIVITY_TYPES', $availableActivityTypes);
	$smarty->assign ('AVAILABLE_EVENT_STATUSES', $availableEventStatuses);
	$smarty->assign ('AVAILABLE_GROUPS', $availableGroups);
	$smarty->assign ('AVAILABLE_TASK_PRIORITIES', $availableTaskPriorities);
	$smarty->assign ('AVAILABLE_USERS', $availableUsers);
	$smarty->assign ('CATEGORY', $category);
	$smarty->assign ('CREATEMODE', $createMode);
	$smarty->assign ('CURRENTUSERID', $current_user->id);
	$smarty->assign ('FROM_WORK', $fromWork);
	$smarty->assign ('ID', $focus->id);
	$smarty->assign ('INVITEDUSERS', $invitedUsers);
	$smarty->assign ('LABEL', $fldLabel);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODE', $focus->mode);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('RELATED', $relatedEntities);
	$smarty->assign ('RELATED_MODULES', $relatedModules);
	$smarty->assign ('SELECTED_OWNER_TYPE', $selectedOwnerType);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('USERSLIST', $userDetails);
	$smarty->assign ('AVAILABLE_MODULES', PanelViewHelper::fetchAvailableModules ($adb, $current_user->id));
	$smarty->assign ('CATEGORIES', DataViewUtils::getAvailableTaskCategories ($adb, $current_user->id));
	$smarty->assign ('AVAILABLE_IMPORTANCE', DataViewUtils::getAvailableImportanceOfTasks ());
	$smarty->assign ('AVAILABLE_ESTIMATED_TIME_UNITS', getAvailableEstimatedTimeUnits ());
	$smarty->assign ('DEFAULT_ESTIMATED_TIME_UNIT', 'Hora');
	if (!empty ($returnAction)) {
		$returnAction .= !empty ($returnTab) ? "&tab={$returnTab}" : '';
		$smarty->assign ('RETURN_TAB', $returnTab);
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
	
	// Pasar parent_id y contact_id al template para que se incluyan como campos hidden
	// (necesario para poblar related_id y related_to al guardar)
	if (!empty ($parentId)) {
		$smarty->assign ('PARENT_ID', $parentId);
	}
	if (!empty ($contactId)) {
		$smarty->assign ('CONTACT_ID', $contactId);
	}
	
	if (!empty($fromWork)) {
		$workId = null;
		if (!empty($relatedEntities) && is_array($relatedEntities)) {
			foreach ($relatedEntities as $rel) {
				if (isset($rel['crmid'])) {
					$workId = $rel['crmid'];
					break;
				}
			}
		}
		$smarty->assign ('WORK_ID', $workId);
	}
	// Usar siempre el template estándar ActivityEditView.tpl
	// (WorkTaskActivityEditView.tpl no existe y no es necesario)
	$smarty->display ("ActivityEditView.tpl");

	if (!empty ($record)) {
		$oldDieOnError = $adb->dieOnError;
		$adb->setDieOnError (false);
		BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('EDIT', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $focus);
		$adb->setDieOnError ($oldDieOnError);
	}
