<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $mod_strings, $app_strings, $currentModule, $theme, $adb;

	$module = isset ($_REQUEST ['module']) ? vtlib_purify ($_REQUEST ['module']) : '';
	$mode   = isset ($_REQUEST ['mergemode']) ? vtlib_purify ($_REQUEST ['mergemode']) : null;

	$imagePath = "themes/$theme/images/";

	/** @var CRMEntity|stdClass $focus */
	$focus = CRMEntity::getInstance ($module);

	if ($mode == 'mergesave') {
		$mergeId   = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : null;
		$recordIds = isset ($_REQUEST ['pass_rec']) ? vtlib_purify ($_REQUEST ['pass_rec']) : null;
		$result    = $adb->pquery ('SELECT COUNT(*) AS count FROM vtiger_crmentity WHERE crmid=? AND deleted=0', array ($mergeId));
		$count     = $adb->query_result ($result, 0, 'count');
		if ($count > 0) {
			// First, save the primary record
			$focus->mode = 'edit';
			setObjectValuesFromRequest ($focus);
			$focus->save ($module);

			// Remove the id of primary record from the list of records to be deleted.
			$delValue = explode (',', $recordIds, -1);
			$offset   = array_search ($mergeId, $delValue);
			unset ($delValue [ $offset ]);

			// Transfer the related lists of the records to be deleted, to the primary record's related list
			if (method_exists ($focus, 'transferRelatedRecords')) {
				$focus->transferRelatedRecords ($module, $delValue, $mergeId);
			} else if (function_exists ('transferRelatedRecords')) {
				transferRelatedRecords ($module, $delValue, $mergeId);
			}

			// Delete the records by id specified in the list
			foreach ($delValue as $value) {
				$returnModule = isset ($_REQUEST ['return_module']) ? vtlib_purify ($_REQUEST ['return_module']) : null;
				DeleteEntity ($module, $returnModule, $focus, $value, '');
			}
		}
		$smarty = new vtigerCRM_Smarty ();
		$smarty->display ('JavascriptCloseAndReload.tpl');
	} else if ($mode == 'mergefields') {
		$idString    = isset ($_REQUEST ['passurl']) ? vtlib_purify ($_REQUEST ['passurl']) : null;
		$parentTab   = getParentTab ();
		$explodedId  = explode (',', $idString, -1);
		$recordCount = count ($explodedId);

		$allValuesArray = getRecordValues ($explodedId, $module);
		$allValues      = $allValuesArray [0];
		$jsArrVal       = $allValuesArray [1];
		$fldArray       = $allValuesArray [2];
		$jsArr          = implode (',', $jsArrVal);

		$importedRecords = array ();
		$result          = $adb->pquery ('SELECT bean_id FROM vtiger_users_last_import WHERE bean_type=? AND deleted=0', array ($module));
		$numRows         = $adb->num_rows ($result);
		$count           = 0;
		for ($i = 0; $i < $numRows; $i++) {
			foreach ($explodedId as $value) {
				if ($value == $adb->query_result ($result, $i, 'bean_id')) {
					$count++;
				}
			}
			array_push ($importedRecords, $adb->query_result ($result, $i, 'bean_id'));
		}

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('RECORD_COUNT', $recordCount);
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('IMAGE_PATH', $imagePath);
		$smarty->assign ('MODULENAME', $module);
		$smarty->assign ('PARENT_TAB', $parentTab);
		$smarty->assign ('JS_ARRAY', $jsArr);
		$smarty->assign ('ID_ARRAY', $explodedId);
		$smarty->assign ('IDSTRING', $idString);
		$smarty->assign ('ALLVALUES', $allValues);
		$smarty->assign ('FIELD_ARRAY', $fldArray);
		$smarty->assign ('IMPORTED_RECORDS', $importedRecords);
		$smarty->assign ('NO_EXISTING', ($recordCount == $count) ? 1 : 0);
		if ($recordCount == 2) {
			if (
				(isPermitted ($currentModule, 'EditView', $explodedId [0]) == 'yes') &&
				(isPermitted ($currentModule, 'EditView', $explodedId [1]) == 'yes') &&
				(isPermitted ($currentModule, 'Delete', $explodedId [0]) == 'yes') &&
				(isPermitted ($currentModule, 'Delete', $explodedId [1]) == 'yes')
			) {
				$smarty->assign ('EDIT_DUPLICATE', 'permitted');
			}
		} else if (
			(isPermitted ($currentModule, 'EditView', $explodedId [0]) == 'yes') &&
			(isPermitted ($currentModule, 'EditView', $explodedId [1]) == 'yes') &&
			(isPermitted ($currentModule, 'EditView', $explodedId [2]) == 'yes') &&
			(isPermitted ($currentModule, 'Delete', $explodedId [0]) == 'yes') &&
			(isPermitted ($currentModule, 'Delete', $explodedId [1]) == 'yes') &&
			(isPermitted ($currentModule, 'Delete', $explodedId [2]) == 'yes')
		) {
			$smarty->assign ('EDIT_DUPLICATE', 'permitted');
		} else {
			$smarty->assign ('EDIT_DUPLICATE', '');
		}
		$smarty->display ('MergeFields.tpl');
	}
