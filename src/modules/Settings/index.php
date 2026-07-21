<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_user, $mod_strings;
	
	$optionUser = PlatzillaUtils::purify ($_GET, 'option', null);

	$smarty = new vtigerCRM_Smarty();
	if (!is_admin ($current_user)) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	} else if (!empty ($_SESSION ['platInstancia'])) {
		if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
			$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta!');
			$smarty->display ('instanciaUnverified.tpl');
			exit ();
		} else if (!empty ($optionUser)) {
			if ($optionUser == 'ADVANCED') {
				SettingsUtils::setAdvancedOptions ($adb, 0);
			} else if ($optionUser == 'SINGLE') {
				SettingsUtils::setAdvancedOptions ($adb, 1);
			}
		}
		$masterAdb   = AdbManager::getInstance ()->getMasterAdb ();
		$subscription = null;
		try {
			$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
			$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
			if ((empty ($subscription)) || ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE)) {
				throw new Exception ('Tu suscripción se encuentra inactiva');
			}
		} catch (Exception $e) {
			$smarty->assign ('LABEL', 'Tu suscripción');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('URL', 'index.php?module=Home&action=ViewSubscriptionDetails&tab=subscription');
			$smarty->display ('Message.tpl');
			exit ();
		}
	}

	$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
	$smarty    = new vtigerCRM_Smarty ();
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('TABS', SettingsUtils::fetchSettingsTabs ($adb));
	$smarty->assign ('TUTORIALS', HelpSettingsHelper::fetchHelpConfigurations ($masterAdb));
	$smarty->display ('Settings.tpl');
