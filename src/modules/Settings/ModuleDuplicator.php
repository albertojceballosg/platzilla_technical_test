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

	$result = $adb->query ('SELECT t.* FROM vtiger_tab t WHERE t.presence=0 AND t.customized=1 AND t.isentitytype=1');
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$availableModules = array ();
		while ($row = $adb->fetchByAssoc ($result)) {
			$row ['tablabel']    = getTranslatedString ($row ['tablabel'], $row ['name']);
			$availableModules [] = $row;
		}
		usort (
			$availableModules,
			function ($moduleA, $moduleB) {
				if ($moduleA ['tablabel'] < $moduleB ['tablabel']) {
					return -1;
				} else if ($moduleA ['tablabel'] == $moduleB ['tablabel']) {
					return 0;
				} else {
					return 1;
				}
			}
		);
	} else {
		$availableModules = null;
	}

	$result = $adb->query ('SELECT parenttab_label FROM vtiger_parenttab WHERE visible=0 AND avaliable=1 ORDER BY sequence');
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$availableMenuLabels = array ();
		while ($row = $adb->fetchByAssoc ($result)) {
			$availableMenuLabels [] = $row ['parenttab_label'];
		}
	} else {
		$availableMenuLabels = null;
	}

	$smarty->assign ('AVAILABLE_MODULES', $availableModules);
	$smarty->assign ('AVAILABLE_MENU_LABELS', $availableMenuLabels);
	$smarty->assign ('MOD', $mod_strings);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		$smarty->assign ('SELECTED_NEW_MENU_LABEL', $_SESSION ['flashmessage']['data']['newmenulabel']);
		$smarty->assign ('SELECTED_NEW_MODULE_LABEL', $_SESSION ['flashmessage']['data']['newmodulelabel']);
		$smarty->assign ('SELECTED_NEW_MODULE_NAME', $_SESSION ['flashmessage']['data']['newmodulename']);
		$smarty->assign ('SELECTED_OLD_MODULE_NAME', $_SESSION ['flashmessage']['data']['oldmodulename']);
		unset ($_SESSION ['flashmessage']);
	} else {
		$smarty->assign ('SELECTED_NEW_MENU_LABEL', null);
		$smarty->assign ('SELECTED_NEW_MODULE_LABEL', null);
		$smarty->assign ('SELECTED_NEW_MODULE_NAME', null);
		$smarty->assign ('SELECTED_OLD_MODULE_NAME', null);
	}
	$smarty->display ('Settings/ModuleManager/ModuleDuplicator.tpl');
