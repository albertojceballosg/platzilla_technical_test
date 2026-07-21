<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Objects/ApplicationInterface.php');
	require_once ('include/platzilla/Objects/ApplicationSubscriptionInterface.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/GetParentGroups.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/Reports/lib/ReportUtils.class.php');

	global $adb, $app_strings, $currentModule, $current_user, $mod_strings, $theme;

	$mode = PlatzillaUtils::purify ($_REQUEST, 'mode');

	$platform         = $_SESSION ['plat'];
	$CalculatedFields = new CalculatedFieldsUtils ($adb, $platform);
	$CalculatedFields->getAllCalculateSystem ();

	// Constructing the Role Array
	$roleDetails = getAllRoleDetails ();
	unset ($roleDetails ['H1']);
	$roles = array ();
	foreach ($roleDetails as $roleId => $roleInfo) {
		$roles [ $roleId ] = $roleInfo [0];
	}

	// Constructing the User Array
	$usersDetails = getAllUserName ();
	$users        = array ();
	foreach ($usersDetails as $userId => $userInfo) {
		$users [ $userId ] = $userInfo;
	}

	// Constructing the Group Array
	$groupsDetails = getAllGroupName ();
	$groups        = array ();
	foreach ($groupsDetails as $id => $groupInfo) {
		$groups [ $id ] = $groupInfo;
	}

	// Obtener el catálogo de aplicaciones
	if (!empty ($_SESSION ['platInstancia'])) {
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$instanceDatabaseName = "pg_crm_{$_SESSION ['platInstancia']}";
		$result               = $masterAdb->pquery (
			"SELECT
				ica.config_applicationsid,
				ica.app_code,
				ica.app_name
			FROM
				vtiger_instanceapplications ia
				INNER JOIN vtiger_instances i ON i.code=ia.instancecode
				INNER JOIN vtiger_config_applications mca ON mca.app_code=ia.applicationcode
				INNER JOIN {$instanceDatabaseName}.vtiger_config_applications ica ON ica.app_code=mca.app_code AND ica.app_status='Activa'
			WHERE
				ia.status IN (?, ?) AND
				i.code=?",
			array (ApplicationSubscriptionInterface::STATUS_ACTIVE, ApplicationSubscriptionInterface::STATUS_SUBSCRIBED, $_SESSION ['platInstancia'])
		);
	} else {
		$result = $adb->pquery ('SELECT config_applicationsid, app_code, app_name FROM vtiger_config_applications WHERE app_status=?', array (ApplicationInterface::STATUS_ACTIVE));
	}
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$applications     = array ();
		$applicationCodes = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$row ['modules'] = ReportUtils::getApplicationModules ($adb, $row ['config_applicationsid']);
			$applications []     = $row;
			$applicationCodes [] = $row ['app_code'];
		}
	} else {
		$applications     = null;
		$applicationCodes = null;
	}

	// Obtener las carpetas
	$folders = ReportUtils::getAvailableFolders ($adb, $current_user);
	if (!empty ($folders)) {
		foreach ($folders as $folderIndex => $folder) {
			$reports = $folder ['reports'];
			if (empty ($reports)) {
				continue;
			}
			foreach ($reports as $reportIndex => $report) {
				$reportApplicationCodes = !empty ($report ['applicationcodes']) ? json_decode ($report ['applicationcodes']) : null;
				if (empty ($reportApplicationCodes)) {
					continue;
				} else if ((empty ($applicationCodes)) || (empty (array_intersect ($applicationCodes, $reportApplicationCodes)))) {
					unset ($folders [ $folderIndex ]['reports'][ $reportIndex ]);
				}
			}
		}
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('AVAILABLE_APPLICATIONS', $applications);
	$smarty->assign ('AVAILABLE_FOLDERS', $folders);
	$smarty->assign ('AVAILABLE_GROUPS', $groups);
	$smarty->assign ('AVAILABLE_MODULES', ReportUtils::getAvailableModules ($adb));
	$smarty->assign ('AVAILABLE_ROLES', $roles);
	$smarty->assign ('AVAILABLE_STANDARD_FILTER_PERIODS', ReportUtils::getAvailableStandardFilterPeriods ());
	$smarty->assign ('AVAILABLE_USERS', $users);
	$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('THEME', $theme);
	if (in_array ($mode, array ('ajax', 'ajaxdelete'))) {
		$smarty->display ('ReportContents.tpl');
	} else {
		$smarty->display ('Reports.tpl');
	}
