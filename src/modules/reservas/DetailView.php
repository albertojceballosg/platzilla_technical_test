<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/database/PearDatabase.php');
	require_once ('include/ListView/ListViewSession.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DetailViewUtils.php');
	require_once ('include/utils/EditViewUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/PickList/PickListUtils.php');
	require_once ('user_privileges/default_module_view.php');
	require_once ('vtlib/Vtiger/Link.php');

	global $adb, $app_strings, $mod_strings, $currentModule, $current_user, $singlepane_view, $theme;

	$action           = isset ($_REQUEST ['action']) ? vtlib_purify ($_REQUEST ['action']) : null;
	$record           = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : '';
	$isDuplicate      = isset ($_REQUEST ['isDuplicate']) ? vtlib_purify ($_REQUEST ['isDuplicate']) : null;
	$relationId       = (isset ($_REQUEST ['relation_id'])) && (!empty ($_REQUEST ['relation_id'])) ? vtlib_purify ($_REQUEST ['relation_id']) : null;
	$profileIds       = isset ($_REQUEST ['profileids']) ? vtlib_purify ($_REQUEST ['profileids']) : null;
	$selectedHeader   = (isset ($_REQUEST ['selected_header'])) && (!empty ($_REQUEST ['selected_header'])) ? vtlib_purify ($_REQUEST ['selected_header']) : null;
	$platformDatabase = (isset ($_REQUEST ['platdb'])) && (!empty ($_REQUEST ['platdb'])) ? vtlib_purify ($_REQUEST ['platdb']) : null;

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
		$entity->id = $record;
		$entity->retrieve_entity_info ($record, $currentModule);
	}

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $entity);
	$adb->setDieOnError ($oldDieOnError);

	if ($isDuplicate == 'true') {
		$entity->id = '';
	}

	$recordName = array_values (getEntityName ($currentModule, $entity->id));
	$recordName = $recordName [0];

	// Module Sequence Numbering
	$modSeqField = getModuleSequenceField ($currentModule);
	$modSeqId    = ($modSeqField != null) ? $entity->column_fields [ $modSeqField ['name'] ] : $entity->id;

	$validationArray = split_validationdataArray (getDBValidationData ($entity->tab_name, $tabId));

	// Gather the custom link information to display
	$customLinkParams = array (
		'MODULE' => $currentModule,
		'RECORD' => $entity->id,
		'ACTION' => $action,
	);

	$result = $adb->query ($entity->get_related_list ($entity->id, getTabid ('reservas'), getTabid ('espacios'), array ('SELECT'), true));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$locations = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$locations [] = $row;
		}
	} else {
		$locations = null;
	}

	$result = $adb->query ($entity->get_related_list ($entity->id, getTabid ('reservas'), getTabid ('usuarios_colladito'), array ('SELECT'), true));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$users = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$users [] = $row;
		}
	} else {
		$users = null;
	}

	if (!empty ($_SESSION ['platInstancia'])) {
		$applications = PlatformUtils::getApplicationsByUserRole ($adb, $current_user->column_fields ['roleid'], $currentModule);
	} else {
		$applications = PlatformUtils::getApplicationsByModuleName ($adb, $currentModule);
	}

	$blocksData = getBlocks ($currentModule, 'detail_view', '', $entity->column_fields, '', $profileIds);
	// Eliminar de la información de bloques obtenidos aquellos campos que están condicionados
	if (!empty ($record)) {
		$moduleId = getTabid ($currentModule);

		$conditioningFieldNames = array ();
		$result                 = $adb->pquery ('SELECT f.fieldname FROM vtiger_field f WHERE f.uitype=15 AND f.tabid=?', array ($moduleId));
		if (($result) && ($adb->num_rows ($result) > 0)) {
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$conditioningFieldNames [] = $row ['fieldname'];
			}
		}

		$conditionedFieldIds = array ();
		if (!empty ($conditioningFieldNames)) {
			foreach ($conditioningFieldNames as $fieldName) {
				if (empty ($entity->column_fields [ $fieldName ])) {
					$whereClause = "pl.{$fieldName} IS NULL";
					$arguments   = array ();
				} else {
					$whereClause = "pl.{$fieldName}=?";
					$arguments   = array ($entity->column_fields [ $fieldName ]);
				}
				$result = $adb->pquery (
					"SELECT DISTINCT
						fd.field
					FROM
						vtiger_field_dependency fd
						LEFT JOIN vtiger_{$fieldName} pl ON pl.{$fieldName}id=fd.parentfield
					WHERE
						fd.visible=0 AND
						fd.nameparent=? AND
						fd.field IN (SELECT f.fieldid FROM vtiger_field f WHERE f.tabid=?) AND
						{$whereClause}",
					array_merge (array ($fieldName, $moduleId), $arguments)
				);
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					continue;
				}
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$conditionedFieldIds [] = $row ['field'];
				}
			}
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

		$result = $adb->pquery ('SELECT * FROM vtiger_field WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?) AND uitype=4096', array ($currentModule));
		if (($result) && ($adb->num_rows ($result) > 0)) {
			$result = $adb->pquery (
				'SELECT
					a.attachmentsid,
					a.name,
					a.type,
					CONCAT(a.path, a.attachmentsid, \'_\', a.name) AS uri
				FROM
					vtiger_attachments a
					INNER JOIN vtiger_crmentity crmea ON crmea.crmid=a.attachmentsid AND crmea.deleted=0
					INNER JOIN vtiger_seattachmentsrel sear ON sear.attachmentsid=a.attachmentsid
					INNER JOIN vtiger_crmentity crme ON crme.crmid=sear.crmid AND crme.deleted=0 AND crme.crmid=?',
				array ($record)
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$attachments    = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!file_exists ("{$rootFolderPath}/{$row ['uri']}")) {
						continue;
					}
					$row ['size']   = filesize ("{$rootFolderPath}/{$row ['uri']}") / 1024;
					$attachments [] = $row;
				}
			} else {
				$attachments = null;
			}
		} else {
			$attachments = null;
		}
	} else {
		$attachments = null;
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('ACTIVE_APPLICATIONS', $applications);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('ATTACHMENTS', $attachments);
	$smarty->assign ('AVAILABLE_PICKLISTS', getUserFldArray ($currentModule, $current_user->column_fields ['roleid']));
	$smarty->assign ('BLOCKS', $blocksData);
	$smarty->assign ('CAMPOS_TIPO_GRID', escribeCamposGrid ($currentModule, $entity->id, $swDetailViewGrid));
	$smarty->assign ('CAMPOS_TIPO_MATRIX', escribeDetalleCamposMatrix ($currentModule, $entity->id));
	$smarty->assign ('CATEGORY', $category);
	$smarty->assign ('CHECK', $toolButtons);
	$smarty->assign ('CUSTOM_BUTTONS', PlatformUtils::getCustomButtons ($adb, $currentModule, 'DetailView', $_REQUEST));
	$smarty->assign ('CUSTOM_LINKS', Vtiger_Link::getAllByType (getTabid ($currentModule), array ('DETAILVIEWBASIC', 'DETAILVIEW', 'DETAILVIEWWIDGET'), $customLinkParams));
	$smarty->assign ('CUSTOM_MODULE', true);
	$smarty->assign ('DETAILVIEW_AJAX_EDIT', PerformancePrefs::getBoolean ('DETAILVIEW_AJAX_EDIT', true));
	$smarty->assign ('EDIT_PERMISSION', isPermitted ($currentModule, 'EditView', $record));
	$smarty->assign ('ID', $entity->id);
	$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign ('IS_REL_LIST', isPresentRelatedLists ($currentModule));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MOD_SEQ_ID', $modSeqId);
	$smarty->assign ('MODE', $entity->mode);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('NAME', $recordName);
	$smarty->assign ('PROFILE_IDS', $profileIds);
	$smarty->assign ('RELATED_LOCATIONS', $locations);
	$smarty->assign ('RELATED_USERS', $users);
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
	$smarty->display ('modules/reservas/DetailView.tpl');

	// Record Change Notification
	$entity->markAsViewed ($current_user->id);

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $entity);
	$adb->setDieOnError ($oldDieOnError);
