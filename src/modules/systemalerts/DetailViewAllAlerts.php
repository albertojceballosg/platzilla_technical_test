<?php
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('modules/systemalerts/systemalerts.php');
	require_once ('modules/systemalerts/lib/SystemAlertsHelper.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule, $mod_strings, $smarty, $theme, $current_user;

	$first = new DateTime();
	$first->modify ('first day of this month');
	$last = new DateTime();
	$last->modify ('last day of this month');

	$scaleSearch = PlatzillaUtils::purify ($_REQUEST, 'viewPeriod', 'Month');
	$from        = PlatzillaUtils::purify ($_REQUEST, 'date_from', $first->format ('Y-m-d'));
	$to          = PlatzillaUtils::purify ($_REQUEST, 'date_to', $last->format ('Y-m-d'));

	$local_user   = clone $current_user;
	$applications = IndicatorsPanelHelper::getAplicationsInstance ($adb, $_SESSION ['platInstancia'], $local_user, $current_user);
	$app          = PlatzillaUtils::purify ($_REQUEST, 'app');
	$applications = (array ('all' => array ('config_applicationsid' => 0, 'app_code' => 'all', 'app_name' => $mod_strings['ALL_APLICATIONS'])) + $applications);
	
	$n           = count ($applications);
	$countAlerts = 0;
	if ($n > 0 && (!empty($applications))) {
		$aplicationCode = array_keys ($applications);
		for ($i = 0; $i < $n; $i++) {
			$code = $aplicationCode[ $i ];
			if ($code != 'all') {
				$alert = SystemAlerts::getInstance ($adb, $scaleSearch, $code, $from, $to);
				if ($alert->alerts != null) {
					$alerts[ $code ]              = $alert->alerts;
					$alerts[ $code ] ['app_name'] = $applications[ $code ]['app_name'];
					$countAlerts                  = ($countAlerts + $alerts[ $code ]['countAlert']);
				}
			}
		}
	}

	if ($app == '') {
		$app = 'all';
	}

	if ($smarty == null) {
		require_once ('Smarty_setup.php');
		$smarty = new vtigerCRM_Smarty;
	}

	$smarty->assign ('THEME', $theme);
	$smarty->assign ('MODSTRING', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('VIEW_SEARCH', $scaleSearch);
	$smarty->assign ('DATE_FROM', $from);
	$smarty->assign ('DATE_TO', $to);
	$smarty->assign ('APPLICATIONS', $applications);
	$smarty->assign ('TAB_ACTIVE', $app);
	$smarty->assign ('LABEL_ALL_APLICATIONS', $mod_strings['ALL_APLICATIONS']);
	$smarty->assign ('ALL_ALERTS', $alerts);
	$smarty->assign ('LABEL_OPERATOR', SystemAlertsHelper::getOperator ());
	$smarty->assign ('COUNT_ALL_ALERTS', $countAlerts);

	if (PlatzillaUtils::purify ($_REQUEST, 'ajax')) {
		$smarty->display ('modules/systemalerts/DetailViewAllAlerts.tpl');
	} else {
		$smarty->display ('modules/systemalerts/index.tpl');
	}
