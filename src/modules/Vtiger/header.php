<?php
	require_once ('Smarty_setup.php');
	require_once ('data/Tracker.php');
	require_once ('include/calculator/Calc.php');
	require_once ('include/devicedetect.class.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Calendar/CalendarCommon.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	require_once ('modules/operating_modes/lib/OperatingModesHelper.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
	require_once ('modules/daily_report/lib/DailyReportUtils.class.php');
	require_once ('modules/systemalerts/systemalerts.php');
	require_once ('vtlib/Vtiger/Link.php');

	// Declarar el objeto global Smarty al inicio del archivo
	global $smarty;
	if (!isset($smarty) || !$smarty instanceof vtigerCRM_Smarty) {
		$smarty = new vtigerCRM_Smarty();
	}
	global $adb, $app_list_strings, $app_strings, $currentModule, $current_user, $default_charset, $plat, $theme, $vtiger_current_version, $site_URL;
	
	$homeTabSelected = isset ($_GET ['tab']) ? vtlib_purify ($_GET ['tab']) : null;
	$urlAction       = isset ($_GET ['action']) ? vtlib_purify ($_GET ['action']) : 'EditView';
	if ((isset($_REQUEST['Ajax']) && ($_REQUEST['Ajax'] == 'true')) ||
			(isset($_REQUEST['Popup']) && ($_REQUEST['Popup'] == 'true'))
		) {
		$skipHeaders = true;
	} else {
		$skipHeaders = false;
		$message = '<div id="Mensajes" class="calAddEvent layerPopup" style="display:none;position: absolute;top: 50%;left: 50%;width: 400px;height: 100px;margin-top: -50px;margin-left: -200px;font-size:10pt;text-align:center;z-index:10000;">
						<div id="TextoMensajes" style="padding:20px">
						</div>
						<br/>
						<input type="button" class="crmbutton small cancel" onclick="cierraidUI(\'Mensajes\');" value="' . getTranslatedString ('LBL_CLOSE') . '">
				</div>';
	}
	
	$isAdmin           = is_admin ($current_user) ? 'Si' : 'No';
	$isInstance        = !empty ($_SESSION ['platInstancia']);
	$hiddenMenu        = ($isInstance) ? array (4) : array ();
	$qcModules         = getQuickCreateModules ();
	$date              = new DateTimeField (null);
	$quickAccess       = getAllParenttabmoduleslist ();
	$tracFocus         = new Tracker ();
	$list              = $tracFocus->get_recently_viewed ($current_user->id);
	$commonHeaderLinks = Vtiger_Link::getAllByType (Vtiger_Link::IGNORE_MODULE, Array ('ONDEMANDLINK', 'HEADERLINK', 'HEADERSCRIPT', 'HEADERCSS'), array ('MODULE' => $currentModule));
	$menuReplace       = array (
		'á'        => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
		'&aacute;' => 'a', '&eacute;' => 'e', '&iacute;' => 'i', '&oacute;' => 'o', '&uacute;' => 'u',
		'&Aacute;' => 'A', '&Eacute;' => 'E', '&Iacute;' => 'I', '&Oacute;' => 'O', '&Uacute;' => 'U',
	);

	if (($_REQUEST['action'] == 'customer') && (in_array ($_REQUEST ['module'], array ('Settings', 'Home')))) {
		$block_notifications = true;
		$headerLinks         = null;
	} else {
		$block_notifications = false;
		$headerLinks         = Vtiger_Link::getAllByType (getTabid ('Home'), array ('HEADER_LINK'), array ('MODULE' => $currentModule, 'ACTION' => vtlib_purify ($_REQUEST['action'])));
	}

	$result = $adb->query ('SELECT * FROM vtiger_tab WHERE presence=0 AND isentitytype=1 ORDER BY tablabel');
	if ((!$result) || ($adb->num_rows ($result) == 0)) {
		$relatedModules = null;
	} else {
		$relatedModules = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$relatedModules [] = $row;
		}
	}
	$result = $adb->query ('SELECT name FROM vtiger_tab WHERE isentitytype!=1 AND name NOT IN ("historymanager") ORDER BY tablabel');
	if ((!$result) || ($adb->num_rows ($result) == 0)) {
		$configdModules = null;
	} else {
		$configModules = array ('Settings', 'Calendar');
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$configModules [] = $row['name'];
		}
	}
	
	if (empty ($_SESSION ['authenticated_user_language'])) {
		$_SESSION ['authenticated_user_language'] = 'es_es';
	}
	$result = $adb->query ('SELECT * FROM vtiger_crmentity WHERE demo=1');
	if ($adb->num_rows ($result) > 0) {
		$hasDemoData = true;
	} else {
		$hasDemoData = false;
	}

	$oph                     = OperatingModesHelper::getInstance ();
	$availableOperatingModes = $oph->fetchAvailableOperatingModes ();
	if (empty ($current_user->defaultOperating)) {
		$operatingModeAndTab            = $oph->getDefaultOperatingModeUser ($adb, $current_user->id);
		$current_user->defaultOperating = $operatingModeAndTab [0];
		$current_user->defaultHomeTab   = $operatingModeAndTab [1];
	}

	foreach ($availableOperatingModes as $operatingMode) {
		if ($operatingMode->getOperatingModeName () == $current_user->defaultOperating) {
			$current_user->defaultOperationLabel = $operatingMode->getLabel ();
			$operatingAttr                       = $operatingMode->getAttributes();
			$operatingTabs                       = $operatingMode->getTabTabs();
			break;
		}
	}
	$quickMenu    = getMenuQuickCreate (array_values ($operatingAttr ['parent-tab']));
	$reportedDays = DailyReportUtils::fetchDailyReportDateByUser ($adb, $current_user->id);
	$today        = date('Y-m-d');
	$yesterday    = date('Y-m-d',strtotime('-1 days'));
	$reportToDay  = "{$today}@{$current_user->id}";
	$reportToYesterday = "{$yesterday}@{$current_user->id}";
	$smarty->assign ('ACTIVITIES', getFullCalendar ());
	$smarty->assign ('AVAIABLE_OPERATING_MODES', $availableOperatingModes);
	$smarty->assign ('DEFAULT_OPERATING', $current_user->defaultOperationLabel);
	$smarty->assign ('OPERATING_MODE_BTN', $operatingAttr);
	$smarty->assign ('OPERATING_TABS', $operatingTabs);
	$smarty->assign ('APD', $ret);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CALC', get_calc ("themes/{$theme}/images/"));
	$smarty->assign ('CATEGORY', getParentTab ());
	$smarty->assign ('CHECK_MOBILE', deviceDetect::mobile_device_detect ());
	$smarty->assign ('CNT', count ($qcModules));
	$smarty->assign ('COLORED_HEADERS', null);
	$smarty->assign ('COLORED_HEADERS_PARENT', getColoredHeadersFromParenttab ());
	$smarty->assign ('CURRENT_USER', $current_user->user_name);
	$smarty->assign ('CURRENT_USER_IMAGE', getUserImageName ($current_user->id));
	$smarty->assign ('CURRENT_MERGE_USER_ID', $mergeCurrentUserId);
	$smarty->assign ('CURRENT_USER_MAIL', $current_user->email1);
	$smarty->assign ('CURRENT_USER_NAME', trim ($current_user->column_fields['first_name'] . ' ' . $current_user->column_fields['last_name']));
	$smarty->assign ('CURRENT_USER_HASHHMAC', $hashHmac);
	$smarty->assign ('INSTANCE_CODE_HELPCRUNCH', $platformName);
	$smarty->assign ('IS_ADMIN_HELPCRUNCH', $isAdmin);
	$smarty->assign ('CUSTOMER_ID', $customerId);
	$smarty->assign ('BILLING_PLAN', $billingPlan);
	$smarty->assign ('CURRENT_USER_DATECREATED', $userDateCreated);
	$smarty->assign ('REMAINING_TRIAL_DAYS', $remainingTrialDays);
	$smarty->assign ('SITE_URL', $site_URL);
	$smarty->assign ('CUSTOM_ICONS', getCustomHeadIcons ());
	$smarty->assign ('DATE', $date->getDisplayDateTimeValue ());
	$smarty->assign ('ES_INSTANCIA', !empty ($_SESSION ['platInstancia']));
	$smarty->assign ('HAS_DEMO_DATA', $hasDemoData);
	$smarty->assign ('HEADERCSS', $commonHeaderLinks ['HEADERCSS']);
	$smarty->assign ('HEADERLINKS', $commonHeaderLinks ['HEADERLINK']);
	$smarty->assign ('HEADERS', getHeaderArray ($hiddenMenu));
	$smarty->assign ('HEADERSCRIPTS', $commonHeaderLinks ['HEADERSCRIPT']);
	$smarty->assign ('HEADER_OPER_MODE', array_values ($operatingAttr ['parent-tab']));
	$smarty->assign ('HELP_AVAILABLE_APPLICATIONS', HelpSettingsHelper::fetchApplications ($adb));
	$smarty->assign ('HIDE_MENU', $operatingAttr ['hide-menu']);
	$smarty->assign ('HEADER_TODAY',$today);
	$smarty->assign ('HEADER_YESTERDAY',$yesterday);
	$smarty->assign ('REPORTED_DAYS', (is_array ($reportedDays)) ? join (';', $reportedDays) : null);
	$smarty->assign ('REPORT_TODAY', base64_encode ($reportToDay));
	$smarty->assign ('REPORT_YESTERDAY',  base64_encode ($reportToYesterday));
	$smarty->assign ('MESSAGE_ERROR', $message);
	$smarty->assign ('SKIP_HEADERS', $skipHeaders);
	$smarty->assign ('IMAGEPATH', "themes/{$theme}/images/");
	$smarty->assign ('INSTANCE_CODE', $_SESSION ['platInstancia']);
	$smarty->assign ('IS_ADMIN', is_admin ($current_user));
	$smarty->assign ('IS_INSTANCE', $isInstance);
	$smarty->assign ('MENU_QUICKCREATE', $quickMenu);
	$smarty->assign ('menu_replace', $menuReplace);
	$smarty->assign ('MENUSTRUCTURE', getMenuStructure ($currentModule));
	$smarty->assign ('MODULE_NAME', $currentModule);
	$smarty->assign ('MODULELISTS', $app_list_strings['moduleList']);
	$smarty->assign ('NAV_SMALL', (!$operatingAttr['hide-tab'] || true) ? ' nav-small' : '');
	$smarty->assign ('ONDEMANDLINKS', $commonHeaderLinks ['ONDEMANDLINK']);
	$smarty->assign ('PENDING_NOTIFICATIONS', array ('total' => 0, 'timemanagement' => 0, 'platform' => 0));
	$smarty->assign ('PLATFORM', $_SESSION ['plat']);
	$smarty->assign ('PLAT_CODE', $plat);
	$smarty->assign ('PRINT_URL', 'phprint.php?jt=' . session_id () . $GLOBALS['request_string']);
	$smarty->assign ('QCMODULE', $qcModules);
	$smarty->assign ('QUICKACCESS', $quickAccess ['menu']);
	$smarty->assign ('RELATED_MODULES', $relatedModules);
	$smarty->assign ('SHOWMAIL', $showMail);
	$smarty->assign ('HOME_TAB',empty($homeTabSelected) ? $operatingAttr['selected-tab'] : $homeTabSelected);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('TRACINFO', $list);
	$smarty->assign ('trans_subm', $quickAccess ['trans_subm']);
	$smarty->assign ('tourlayout', $_REQUEST['tourlayout']);
	$smarty->assign ('TOTAL_ALERTS', SystemAlerts::getTotalAlerts ($adb, $current_user->id));
	$smarty->assign ('USE_ASTERISK', 'false');
	$smarty->assign ('USER', getFullNameFromArray ('Users', $current_user->column_fields));
	$smarty->assign ('USER_DATE_FORMAT', $current_user->date_format ? $current_user->date_format : 'yyyy-mm-dd');
	$smarty->assign ('USER_FIRST_NAME', $current_user->column_fields ['first_name']);
	$smarty->assign ('USER_ID', $current_user->id);
	$smarty->assign ('URL_ACTION', strtolower ($urlAction));
	$smarty->assign ('VERSION', $vtiger_current_version);
	if (!empty ($_REQUEST['query_string'])) {
		$smarty->assign ('QUERY_STRING', htmlspecialchars ($_REQUEST['query_string'], ENT_QUOTES, $default_charset));
	} else {
		$smarty->assign ('QUERY_STRING', $app_strings['LBL_SEARCH_STRING']);
	}
	if ($block_notifications) {//Se deshabilitan los menus
		$smarty->assign ('NOTIFICATIONS_PERMITTED', 'no');
		$smarty->assign ('STYLEMENUS', 'style="display:none"');
		$smarty->assign ('STYLE_DISPLAY_HOME', 'display:inline;"');
		$smarty->assign ('STYLE_DISPLAY_CUSTOMER', 'display:none;"');
	} else {
		$smarty->assign ('NOTIFICATIONS_PERMITTED', 'yes');
		$smarty->assign ('STYLE_DISPLAY_CUSTOMER', 'display:inline;"');
		$smarty->assign ('STYLE_DISPLAY_HOME', 'display:none;"');
		$smarty->assign ('HEADER_LINKS', $headerLinks ['HEADER_LINK']);
	}
	if ($_REQUEST['action'] == 'Logout') {
		$smarty->assign ('ACTION_NAME', 'Logout');
	}
	if ((isset ($_SESSION ['is_authenticated'])) && ($_SESSION ['is_authenticated'] == 1)) {
		if (
			isset($_REQUEST['action'])  && (
			!in_array ($_REQUEST['action'], array ('ListView','EditView','DetailView', 'RecordHistory','index')) ||
			in_array ($currentModule, $configModules)
			)
		) {
			$smarty->display ('platzilla_layout.tpl');
		}
	} else {
		$smarty->display ('boilerplate_out.tpl');
	}
