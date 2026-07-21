<?php
	require_once ('Smarty_setup.php');

	global $adb, $current_user, $mod_strings;

	$smarty = new vtigerCRM_Smarty ();

	// Si no es un usuario administrador de la plataforma madre, no puede realizar cambios de Module Manager
	if ((!empty ($_SESSION ['platInstancia'])) || ($current_user->is_admin != 'on')) {
		$smarty->assign ('MENSAJE', 'Sólo un administador puede realizar estos cambios!');
		$smarty->assign ('LINKVOLVER', 'index.php?module=Home&action=index');
		$smarty->display ('Settings/ModuleManager/NoTienePermiso.tpl');
		return;
	}

	$applicationsResult = $adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_status=?', array ('Activa'));
	if (($applicationsResult) && ($adb->num_rows ($applicationsResult) > 0)) {
		$availableApplications = array ();
		while ($availableApplication = $adb->fetchByAssoc ($applicationsResult)) {
			$modulesResult = $adb->pquery (
				'SELECT t.* FROM vtiger_tab t INNER JOIN vtiger_configapps_tab cat ON cat.tabid=t.tabid WHERE cat.config_applicationsid=?',
				array ($availableApplication ['config_applicationsid'])
			);
			if ((!$modulesResult) || ($adb->num_rows ($modulesResult) == 0)) {
				continue;
			}

			$applicationModules = array ();
			while ($applicationModule = $adb->fetchByAssoc ($modulesResult, -1, false)) {
				$applicationModule ['tablabel'] = getTranslatedString ($applicationModule ['tablabel'], $applicationModule ['name']);
				$applicationModules [] = $applicationModule;
			}
			$availableApplication ['modules']                             = $applicationModules;
			$availableApplications [ $availableApplication ['app_code'] ] = $availableApplication;
		}
		uksort (
			$availableApplications,
			function ($applicationA, $applicationB) {
				return strcasecmp ($applicationA, $applicationB);
			}
		);
	} else {
		$availableApplications = null;
	}

	$result = $adb->query ('SELECT parenttab_label FROM vtiger_parenttab WHERE visible=0 AND avaliable=1 ORDER BY sequence');
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$availableMenuLabels = array ();
		while ($row = $adb->fetchByAssoc ($result)) {
			$availableMenuLabels [] = html_entity_decode ($row ['parenttab_label'], ENT_QUOTES, 'UTF-8');
		}
	} else {
		$availableMenuLabels = null;
	}

	$smarty->assign ('AVAILABLE_APPLICATIONS', $availableApplications);
	$smarty->assign ('AVAILABLE_MENU_LABELS', $availableMenuLabels);
	$smarty->assign ('MOD', $mod_strings);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		$smarty->assign ('SELECTED_NEW_APPLICATION_CODE', $_SESSION ['flashmessage']['data']['newapplicationcode']);
		$smarty->assign ('SELECTED_NEW_APPLICATION_DESCRIPTION', $_SESSION ['flashmessage']['data']['newapplicationdescription']);
		$smarty->assign ('SELECTED_NEW_APPLICATION_NAME', $_SESSION ['flashmessage']['data']['newapplicationname']);
		$smarty->assign ('SELECTED_NEW_MODULE_DATA', $_SESSION ['flashmessage']['data']['modules']);
		$smarty->assign ('SELECTED_OLD_APPLICATION_CODE', $_SESSION ['flashmessage']['data']['oldapplicationcode']);
		unset ($_SESSION ['flashmessage']);
	} else {
		$smarty->assign ('SELECTED_NEW_APPLICATION_CODE', null);
		$smarty->assign ('SELECTED_NEW_APPLICATION_DESCRIPTION', null);
		$smarty->assign ('SELECTED_NEW_APPLICATION_NAME', null);
		$smarty->assign ('SELECTED_NEW_MODULE_DATA', null);
		$smarty->assign ('SELECTED_OLD_APPLICATION_CODE', null);
	}
	$smarty->display ('Settings/ModuleManager/AppDuplicator.tpl');
