<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/utils/CalendarViewUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	try {
		$viewId     = PlatzillaUtils::purify ($_GET, 'record');
		$moduleName = PlatzillaUtils::purify ($_GET, 'modulename');

		if (isset ($_SESSION ['flashmessage']['data'])) {
			$view       = $_SESSION ['flashmessage']['data'];
			$moduleName = $_SESSION ['flashmessage']['data']['modulename'];
			unset ($_SESSION ['flashmessage']['data']);
		} else if (!empty ($viewId)) {
			$view = CalendarViewUtils::getCalendarViewById ($adb, $viewId);
			if (isset($view['rules']) && !empty($view['rules'])) {
				$lastRuleId = $view['rules'][0]['ruleid'];
				$indexRule  = 0;
				foreach ($view['rules'] as $rule) {
					if (in_array ($rule['uitype'], array('15'))) {
						$smarty = new vtigerCRM_Smarty ();
						$smarty->assign ('MOD', return_module_language ($current_language, $rule['modulename']));
						$smarty->assign ('PICKLIST_VALUES', PicklistManager::getInstance ($adb)->fetchPicklistByName ($rule['fieldname'], true));
						$smarty->assign ('VALUE', $rule['value']);
						$rule['value'] = $smarty->fetch ('utils/HTMLPickListOptions.tpl');
					} else if (in_array ($rule['uitype'], array('53'))) {
						$rule['value'] = getUserslist ($rule['value']);
					} else if (in_array ($rule['uitype'], array('56'))) {
						$options [] = array ('value' => 1, 'text' => 'Si');
						$options [] = array ('value' => 0, 'text' => 'No');
						
						$smarty = new vtigerCRM_Smarty ();
						$smarty->assign ('OPTIONS', $options);
						$smarty->assign ('SELECTED_VALUE', $rule['value']);
						$rule['value'] = $smarty->fetch ('utils/HTMLSelectOptions.tpl');
					}
					if ($rule['ruleid'] == $lastRuleId) {
						$theRules[$indexRule][] = $rule;
					} else {
						$indexRule++;
						$lastRuleId = $rule ['ruleid'];
						$theRules[$indexRule][] = $rule;
					}
				}
			}
			$moduleName = empty ($moduleName) ? $view ['modulename'] : null;
		} else {
			$view = null;
		}

		$availableFields     = !empty ($moduleName) ? CalendarViewUtils::getAvailableFields ($adb, $moduleName) : null;
		$availableDateFields = CalendarViewUtils::getAvailableDateFields ($availableFields);

		$smarty->assign ('AVAILABLE_APPLICATIONS', CalendarViewUtils::getAvailableApplications ($adb));
		$smarty->assign ('AVAILABLE_DATE_FIELDS', $availableDateFields);
		$smarty->assign ('AVAILABLE_FIELDS', $availableFields);
		$smarty->assign ('AVAILABLE_MODULES', CalendarViewUtils::getAvailableModules ($adb));
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULE_NAME', $moduleName);
		$smarty->assign ('RECORD', $viewId);
		$smarty->assign ('RULES', isset($theRules) ? $theRules : null);
		$smarty->assign ('VIEW', $view);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		} else if ((!empty ($moduleName)) && (empty ($availableDateFields))) {
			$smarty->assign ('IS_ERROR', true);
			$smarty->assign ('MESSAGE', 'El módulo seleccionado no tiene registrados campos de tipo fecha, imprescindibles para crear una vista calendario');
		}
		$smarty->display ('Settings/CalendarViewEditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=Settings&action=CalendarViewListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
