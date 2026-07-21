<?php
	require_once ('include/platzilla/Managers/ViewManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/KanbanViewUtils.class.php');
	require_once ('modules/CustomView/CustomView.php');
	require_once ('modules/CustomView/lib/CustomViewHelper.class.php');
	/**
	 * Archivo que se encarga de obtener los listados y demas parametros solicitados via AJAX
	 * desde el configurador Kanban
	 *
	 * @var PearDatabase $adb
	 * @var string $mod_strings
	 * @var string $current_language
	 * @var User $current_user
	 */
	global $adb, $mod_strings, $current_language, $current_user;

	$function = PlatzillaUtils::purify ($_REQUEST, 'function');

	if ($function == 'paramFieldElements') {
		$appSelect = PlatzillaUtils::purify ($_REQUEST, 'appSelect');
		$element   = KanbanViewUtils::getAvailableModulesByApp ($adb, $current_user, $appSelect, $current_user->is_admin);
		echo json_encode ($element);
	} else if ($function == 'codeElementField') {
		$tabName   = PlatzillaUtils::purify ($_REQUEST, 'tabname');
		$fieldsTab = KanbanViewUtils::getAvailableModuleFields ($adb, $tabName);
		echo json_encode ($fieldsTab);
	} else if ($function == 'cardElementField') {
		$tabName   = PlatzillaUtils::purify ($_REQUEST, 'tabname');
		$fieldsTab = KanbanViewUtils::getAvailableModuleFieldsCards ($adb, $tabName);
		echo json_encode ($fieldsTab);
	} else if ($function == 'codeElementFieldPick') {
		$fieldName     = PlatzillaUtils::purify ($_REQUEST, 'fieldname');
		$fieldId       = PlatzillaUtils::purify ($_REQUEST, 'fieldid');
		$moduleName    = PlatzillaUtils::purify ($_REQUEST, 'modulename');
		$fieldPickList = KanbanViewUtils::getAvailableModuleFieldsPickList ($adb, $fieldName, $moduleName);
		echo json_encode ($fieldPickList);
	} else if ($function == 'paramFieldElementsView') {
		$appSelect = PlatzillaUtils::purify ($_REQUEST, 'appSelect');
		$element   = KanbanViewUtils::getAvailableModulesByAppView ($adb, $current_user, $appSelect, $current_user->is_admin);
		echo json_encode ($element);
	} else if ($function == 'codeElementFieldView') {
		$tabId       = PlatzillaUtils::purify ($_REQUEST, 'tabid');
		$app         = PlatzillaUtils::purify ($_REQUEST, 'app');
		$viewsTabApp = KanbanViewUtils::getAvailableViews ($adb, $tabId, $app);
		echo json_encode ($viewsTabApp);
	} else if ($function == 'updateFieldView') {
		$recordid  = PlatzillaUtils::purify ($_REQUEST, 'recordid');
		$fieldname = PlatzillaUtils::purify ($_REQUEST, 'fieldname');
		$tabname   = PlatzillaUtils::purify ($_REQUEST, 'tabname');
		$valueid   = PlatzillaUtils::purify ($_REQUEST, 'valueid');
		KanbanViewUtils::updateFieldValueView ($adb, $recordid, $fieldname, $tabname, $valueid);
		echo json_encode ('success');
	} else if ($function == 'find_default_view') {
		$modulename = PlatzillaUtils::purify ($_REQUEST, 'modulename');
		$hasDefault = KanbanViewUtils::isDefaultView($adb, $modulename);
		if (empty($hasDefault)) {
			echo null;
		} else {
			echo json_encode ($hasDefault);
		}
	} else if ($function == 'getCustomView') {
		$moduleName  = PlatzillaUtils::purify ($_REQUEST, 'tabname');
		$oCustomView = new CustomView ($moduleName);
		$availableColumns = CustomViewHelper::getAvailableColumnsData ($adb, $oCustomView, $moduleName);
		if (empty ($availableColumns)) {
			echo null;
		} else {
			echo json_encode ($availableColumns);
		}
	} else if ($function == 'getDateColumnList') {
		$moduleName           = PlatzillaUtils::purify ($_REQUEST, 'tabname');
		$oCustomView          = new CustomView ($moduleName);
		$availableDateColumns = CustomViewHelper::getAvailableDateColumnsData ($oCustomView, $moduleName, $current_user);
		if (empty ($availableDateColumns)) {
			echo null;
		} else {
			echo json_encode ($availableDateColumns);
		}
	}
