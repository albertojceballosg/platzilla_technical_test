<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/jQueryUtils.php');
	require_once ('include/utils/PanelUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/LayoutPanelHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $app_strings, $currentModule, $mod_strings, $theme;

	$ajaxRequest      = SettingsUtils::purify ($_REQUEST, 'Ajax');
	$fieldModuleName  = SettingsUtils::purify ($_REQUEST, 'fld_module');
	$forModuleName    = SettingsUtils::purify ($_REQUEST, 'formodule');
	$function         = SettingsUtils::purify ($_REQUEST, 'function');
	$label            = SettingsUtils::purify ($_REQUEST, 'label');
	$panelId          = SettingsUtils::purify ($_REQUEST, 'panelid');
	$position         = SettingsUtils::purify ($_REQUEST, 'pos');
	$previousPosition = SettingsUtils::purify ($_REQUEST, 'prevpos');
	$recordId         = SettingsUtils::purify ($_REQUEST, 'record');
	$relatedModule    = SettingsUtils::purify ($_REQUEST, 'related_module');
	$subType          = SettingsUtils::purify ($_REQUEST, 'subtype');
	$type             = SettingsUtils::purify ($_REQUEST, 'type');

	$moduleName = !empty ($forModuleName) ? $forModuleName : $fieldModuleName;

	if ($ajaxRequest != 'true') {
		if ($function == 'SavePanelOrGraph') {
			LayoutPanelHelper::savePanelOrGraph ($moduleName, $_REQUEST);
		} else if ($function == 'newPanelOrGraph') {
			$relModule = SettingsUtils::purify ($_REQUEST, 'relmodule');
			createPanelOrGraph ($fieldModuleName, $label, $type, $subType, is_array ($relModule) ? $relModule [0] : null);
		} else if ($function == 'SavePanelColumnProperties') {
			LayoutPanelHelper::savePanelColumnProperties ($recordId, $_REQUEST);
		}
	} else if ($function == 'changeColumnPosition') {
		updatePositionColumnPanel ($panelId, $position, $previousPosition);
	} else if ($function == 'changePosition') {
		updatePositionPanelOrGraph ($moduleName, $position, $previousPosition);
	} else if ($function == 'deletePanelOrGraph') {
		deletePanelOrGraph ($moduleName, $position);
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('THEME', $theme);
	if ($ajaxRequest != 'true') {
		$entries  = LayoutPanelHelper::getPanelEntries ($moduleName);
		$dialogId = 'dlgPanelGraphProperties';
		$smarty->assign ('CFENTRIES', $entries);
		$smarty->assign ('DLG_PANEL_GRAPH_PROPERTIES', escribeDlgModal ($dialogId, ''));
		$smarty->assign ('ID_DLG_PANEL_GRAPH_PROPERTIES', $dialogId);
		$smarty->assign ('MODULE', $moduleName);
		$smarty->display ('Settings/LayoutPanelList.tpl');
	} else if ($function == 'panelProperties') {
		$panelSettings = getPanelOGraphSettings ($panelId);

		if (($type == 'Panel') && ($panelSettings [0]['subtype'] == 'Dash')) {
			$lstColumnas = getColumnsPanelDashSmarty ($panelId, $fieldModuleName, $relatedModule);
			$smarty->assign ('_LIST_COLUMNS', $lstColumnas);
			$smarty->assign ('CURRENT_MODULE', $currentModule);
			$smarty->assign ('MODULE', $moduleName);
			$smarty->assign ('MODULELABEL', getModuleTitleFromDB ($relatedModule));
			$smarty->assign ('MODULEPANELLABEL', getTranslatedString ($moduleName, $moduleName));
			$smarty->assign ('PANELID', $panelId);
			$smarty->assign ('RELATED_MODULE', $relatedModule);
			$smarty->display ('Settings/PanelDashProperties.tpl');
		} else {
			$customView    = new CustomView ();
			$moduleColumns = $customView->getModuleColumnsList ($relatedModule);

			$smarty->assign ('CHOOSE_COLUMNS', LayoutPanelHelper::getColumns ($panelId, $customView, $relatedModule, $moduleColumns));
			$smarty->assign ('COLUMNS_BLOCK', getByModule_ColumnsHTML ($customView, $relatedModule, $moduleColumns));
			$smarty->assign ('FOPTION', getAdvCriteriaHTML ()); //Ojo con esta funcion se debe obtener los valores de las tablas paneles
			$smarty->assign ('FUNCTION', $function);
			$smarty->assign ('MODULE', $moduleName);
			$smarty->assign ('MODULELABEL', getModuleTitleFromDB ($relatedModule));
			$smarty->assign ('MODULEPANELLABEL', getTranslatedString ($moduleName, $moduleName));
			$smarty->assign ('PANELID', $panelId);
			if ($type == 'Panel') {
				$smarty->assign ('CRITERIA_GROUPS', getAdvFilterByPanelid ($panelId));
				$smarty->assign ('CURRENT_LINKS', linksPanelOrGraph ($moduleName, $panelId));
				$smarty->display ('Settings/PanelProperties.tpl');
			} else {
				$graphSettings = getPanelOGraphSettings ($panelId);

				$smarty->assign ('LEGEND', $graphSettings [0]['description']);
				$smarty->assign ('OPERATIONS', getHTMLOperations ('count'));
				$smarty->assign ('SUBTYPE', getHTMLSubtype ($graphSettings [0]['subtype']));
				$smarty->display ('Settings/GraphProperties.tpl');
			}
		}
	} else if ($function == 'panelColumnProperties') {
		$columnIndex     = SettingsUtils::purify ($_REQUEST, 'columnindex');
		$customView      = new CustomView();
		$columns         = $customView->getModuleColumnsList ($relatedModule, true);
		$parameters      = LayoutPanelHelper::getPanelDashColumnParameters ($columnIndex, $panelId);
		$advancedFilters = $columnIndex ? getAdvFilterByPanelid ($panelId, '', $columnIndex, false) : null;

		$operation = 'count';
		if (isset ($columns)) {
			$selected = getFieldSelectedByPanelId ($panelId, $columnIndex);
			$list     = explode (':', $selected);
			if (isset ($list [5])) {
				$operation = $list [5];
				unset ($list [5]);
				$selected = implode (':', $list);
			}
		} else {
			$selected = null;
		}

		$smarty->assign ('COLUMNINDEX', $columnIndex);
		$smarty->assign ('COLUMNS_BLOCK', getByModule_ColumnsHTML ($customView, $relatedModule, $columns));
		$smarty->assign ('CRITERIA_GROUPS', $advancedFilters);
		$smarty->assign ('DISABLED_AXIS_COLUMN', getStatusAxisColumn ($panelId, $columnIndex));
		$smarty->assign ('FOPTION', getAdvCriteriaHTML ()); //Ojo con esta funcion se debe obtener los valores de las tablas paneles
		$smarty->assign ('GRAFICAR', $parameters->graficar);
		$smarty->assign ('IMAGE_PATH', "themes/{$theme}/images/");
		$smarty->assign ('LABEL', $parameters->titulo);
		$smarty->assign ('MODULE', $moduleName);
		$smarty->assign ('MODULELABEL', getModuleTitleFromDB ($fieldModuleName));
		$smarty->assign ('MODULEPANELLABEL', getTranslatedString ($fieldModuleName, $fieldModuleName));
		$smarty->assign ('OPERATIONS', getHTMLOperations ($operation));
		$smarty->assign ('PANELID', $panelId);
		$smarty->assign ('SELECTED_VALUE', $selected);
		$smarty->display ('Settings/PanelColumnProperties.tpl');
	} else if ($function == 'newPanel') {
		$smarty->assign ('LISTAMODULOS', escribeListadoModulos ('relmodule', 'relmodule', '', 'inline'));
		$smarty->assign ('MODULE', $moduleName);
		$smarty->assign ('MODULELABEL', getModuleTitleFromDB ($moduleName));
		$smarty->assign ('PANELID', $panelId);
		$smarty->display ('Settings/newPanel.tpl');
	} else if ($function == 'changeColumnPosition') {
		$columns = getColumnsPanelDashSmarty ($panelId, $fieldModuleName, $relatedModule);
		$smarty->assign ('_LIST_COLUMNS', $columns);
		$smarty->assign ('CURRENT_MODULE', $currentModule);
		$smarty->assign ('MODULE', $moduleName);
		$smarty->assign ('MODULELABEL', getModuleTitleFromDB ($relatedModule));
		$smarty->assign ('MODULEPANELLABEL', getTranslatedString ($fieldModuleName, $fieldModuleName));
		$smarty->assign ('PANELID', $panelId);
		$smarty->assign ('RELATED_MODULE', $relatedModule);
		$smarty->display ('Settings/PanelDashProperties.tpl');
	} else {
		$smarty->assign ('CFENTRIES', LayoutPanelHelper::getPanelEntries ($moduleName));
		$smarty->assign ('DLG_PANEL_GRAPH_PROPERTIES', null);
		$smarty->display ('Settings/LayoutPanelEntries.tpl');
	}
