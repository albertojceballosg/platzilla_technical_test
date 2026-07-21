<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/ConfigApplicationsHelper.class.php');

	global $adb, $app_strings, $mod_strings;

	$applicationCode = PlatzillaUtils::purify ($_GET, 'applicationcode');
	$applicationId   = PlatzillaUtils::purify ($_REQUEST, 'record');
	$returnAction    = PlatzillaUtils::purify ($_GET, 'returnaction', 'ConfigApps');
	$returnModule    = PlatzillaUtils::purify ($_GET, 'returnmodule', 'Settings');

	$smarty = new vtigerCRM_Smarty ();
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}

	try {
		if ((empty ($applicationId)) && (!empty ($applicationCode))) {
			$result = $adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_code=?', array ($applicationCode));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row           = $adb->fetchByAssoc ($result, -1, false);
				$applicationId = intval ($row ['config_applicationsid']);
			} else {
				$applicationId = null;
			}
		}

		if (empty ($applicationId)) {
			throw new Exception ('No has suministrado el ID de la aplicación');
		}

		$application = ConfigApplicationsHelper::getApplicationById ($adb, $applicationId);
		if (empty ($application)) {
			throw new Exception ('La aplicación suministrada no está registrada');
		}

		$profileId = $application ['app_profile'];
		if (empty ($profileId)) {
			$profileId = ConfigApplicationsHelper::fixApplicationProfile ($adb, $application);
		}

		$profileData = ConfigApplicationsHelper::getApplicationProfileData ($adb, $profileId);
		if (!$profileData) {
			throw new Exception ('La aplicación suministrada no tiene registrada la información de perfil');
		}

		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('APPLICATION', $application);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('PROFILE_DATA', $profileData);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('RETURN_MODULE', $returnModule);
		$smarty->display ('Settings/EditApplicationProfile.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=Settings&action=ConfigApps&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
