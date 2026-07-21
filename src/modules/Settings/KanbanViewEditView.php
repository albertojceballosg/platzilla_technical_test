<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/KanbanViewUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/CustomView/lib/CustomViewHelper.class.php');
	/**
	 * Archivo que se encarga de renderizar la vista para edición del Kanban
	 * desde el configurador Kanban
	 *
	 * @var PearDatabase $adb
	 * @var string $app_strings
	 * @var User $current_user
	 * @var string $mod_string
	 * @var string $theme
	 */
	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$mod_strings = return_module_language ($current_language, 'CustomView');
	$isInstance  = !empty ($_SESSION ['platInstancia']);

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	try {
		$viewId         = PlatzillaUtils::purify ($_GET, 'record');
		$aplicationcode = PlatzillaUtils::purify ($_GET, 'codeApp');
		$moduleName     = PlatzillaUtils::purify ($_GET, 'modulename');
		$fieldName      = PlatzillaUtils::purify ($_GET, 'fieldname');
		$returnModule   = PlatzillaUtils::purify ($_GET, 'return_module', 'Settings');

		if (isset ($_SESSION ['flashmessage']['data'])) {
			$view       = $_SESSION ['flashmessage']['data'];
			$moduleName = $_SESSION ['flashmessage']['data']['modulename'];
			unset ($_SESSION ['flashmessage']['data']);
		} else if (!empty ($viewId)) {
			$view       = KanbanViewUtils::getKanbanViewById ($adb, $viewId);
			$moduleName = empty ($moduleName) ? $view->getModuleName () : null;
			$fieldName  = empty ($fieldName) ? $view->getFieldName () : null;
			$mode = 'edit';
		} else {
			$view = null;
			$mode = '';
		}

		if (($mode == 'edit') && !empty ($moduleName) && !empty ($fieldName)) {
			$oCustomView = new CustomView ($moduleName);
			$smarty->assign ('AVAILABLE_COLUMNS', CustomViewHelper::getAvailableColumnsData ($adb, $oCustomView, $moduleName));
			$smarty->assign ('AVAILABLE_DATE_COLUMNS', CustomViewHelper::getAvailableDateColumnsData ($oCustomView, $moduleName, $current_user));
			$smarty->assign ('AVAILABLE_MODULES', KanbanViewUtils::getAvailableModulesByApp ($adb, $current_user, $view->getCodeApplication (), $current_user->is_admin));
			$smarty->assign ('AVAILABLE_MODULE_FIELDS_CARDS', KanbanViewUtils::getAvailableModuleFieldsCards ($adb, $moduleName));
			$smarty->assign ('AVAILABLE_MODULE_FIELDS', KanbanViewUtils::getAvailableModuleFields ($adb, $moduleName));
			$smarty->assign ('CALCULATION_OPERATORS', KanbanViewUtils::getCalculationOperators ());
			// Valores actualmente disponibles para el campo base (picklist clasico o pipeline).
			// Se usa para detectar valores nuevos/cambiados respecto de las reglas ya guardadas.
			if (!empty ($fieldName)) {
				$smarty->assign ('AVAILABLE_FIELD_VALUES', KanbanViewUtils::getAvailableModuleFieldsPickList ($adb, $fieldName, $moduleName));
			}
		} else if (!empty($returnModule) && empty($mode) && empty($view) && $isInstance) {
			$oCustomView = new CustomView ($returnModule);
			$tabId       = getTabid($returnModule);
			$tabLabel    = getTabname($tabId);
			$moduleName  = $returnModule;
			$smarty->assign ('AVAILABLE_COLUMNS', CustomViewHelper::getAvailableColumnsData ($adb, $oCustomView, $moduleName));
			$smarty->assign ('AVAILABLE_DATE_COLUMNS', CustomViewHelper::getAvailableDateColumnsData ($oCustomView, $moduleName, $current_user));
			$smarty->assign ('AVAILABLE_MODULES', array (array ('name' => $returnModule, 'tabid' => $tabId, 'tablabel' => $tabLabel)));
			$smarty->assign ('AVAILABLE_MODULE_FIELDS_CARDS', KanbanViewUtils::getAvailableModuleFieldsCards ($adb, $moduleName));
			$smarty->assign ('AVAILABLE_MODULE_FIELDS', KanbanViewUtils::getAvailableModuleFields ($adb, $moduleName));
			$smarty->assign ('CALCULATION_OPERATORS', KanbanViewUtils::getCalculationOperators ());
		}

		$smarty->assign ('AVAILABLE_PERIODS', CustomViewHelper::getAvailablePeriods ());
		$smarty->assign ('APPLICATIONS', KanbanViewUtils::getAvailableApplications ($adb, $current_user));
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULENAME', $moduleName);
		$smarty->assign ('FIELDNAME', $fieldName);
		$smarty->assign ('IS_INSTANCE', $isInstance);
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('RECORD', $viewId);
		$smarty->assign ('RTN_MODULE', $returnModule);
		$smarty->assign ('VIEW', $view);
		$smarty->assign ('MODE', $mode);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('Settings/KanbanViewEditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=Settings&action=KanbanViewListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
