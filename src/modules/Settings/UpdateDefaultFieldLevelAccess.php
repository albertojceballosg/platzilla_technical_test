<?php
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$fieldModuleName = SettingsUtils::purify ($_REQUEST, 'fld_module');

	$fieldModuleAccess = getFieldModuleAccessArray ();
	foreach ($fieldModuleAccess as $moduleName => $fieldName) {
		$result = getDefOrgFieldList ($moduleName);
		while ($row = $adb->fetchByAssoc ($result)) {
			$moduleId    = $row ['tabid'];
			$fieldId     = $row ['fieldid'];
			$displayType = $row ['displaytype'];
			$visible     = SettingsUtils::purify ($_REQUEST, $fieldId) == 'on' ? 0 : 1;

			// Updating the Mandatory vtiger_fields
			$uiType     = $row ['uitype'];
			$fieldName  = $row ['fieldname'];
			$typeOfData = $row ['typeofdata'];
			$fieldType  = explode ('~', $typeOfData);
			if (
				(($fieldName == 'salutationtype') && ($uiType == 55)) ||
				($fieldtype [1] == 'M') ||
				($uiType == 111) ||
				($displayType == 3) ||
				($fieldName == 'activitytype')
			) {
				$visible = 0;
			}

			//Updating the database
			$adb->pquery ('UPDATE vtiger_def_org_field SET visible=? WHERE fieldid=? AND tabid=?', array ($visible, $fieldId, $moduleId));
			$adb->pquery ('UPDATE vtiger_field SET presence=? WHERE fieldid=? AND tabid=?', array ($visible, $fieldId, $moduleId));
		}
	}
	header ("Location: index.php?module=Settings&action=DefaultFieldPermissions&parenttab=Settings&fld_module={$fieldModuleName}");
