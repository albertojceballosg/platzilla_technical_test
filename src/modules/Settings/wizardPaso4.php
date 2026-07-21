<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/Settings/lib/WizardUtils.class.php');

	global $adb, $mod_strings, $app_strings, $theme;

	$protectedVariables = array ('module', 'action', 'Ajax', 'plat');
	$keys               = array_keys ($_POST);
	foreach ($keys as $key) {
		if (!in_array ($key, $protectedVariables)) {
			$_SESSION [ $key ] = SettingsUtils::purify ($_POST, $key);
		}
	}

	// Validar que no exista una tabla con el nombre de campo picklist seleccionado
	if (!empty ($_SESSION ['tipoCampo'])) {
		foreach ($_SESSION ['tipoCampo'] as $index => $uiType) {
			if (!in_array ($uiType, array (15, 33))) {
				continue;
			}
			$result = $adb->pquery ('SHOW TABLES LIKE ?', array ("vtiger_{$_SESSION ['nombreCampo'][$index]}"));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				echo "<div class=\"alert alert-danger\"><strong>Error:</strong> El nombre de campo '{$_SESSION ['nombreCampo'][$index]}' ya está en uso. Elige otro nombre</div>";
				require_once ('modules/Settings/wizardPaso3.php');
				exit ();
			}
		}
	}

	$relatedModuleNames          = SettingsUtils::purify ($_SESSION, 'listaModulos');
	$relatedModuleLabels         = SettingsUtils::purify ($_SESSION, 'labelModulos');
	$relatedModuleAddActions     = SettingsUtils::purify ($_SESSION, 'listaAccionAdd');
	$relatedModuleSelectActions  = SettingsUtils::purify ($_SESSION, 'listaAccionSelect');
	$relatedModulePatternActions = SettingsUtils::purify ($_SESSION, 'listaAccionPatron');

	if ((isset ($relatedModuleNames)) && (is_array ($relatedModuleNames))) {
		$selectedModuleNames          = array ();
		$selectedModuleLabels         = array ();
		$selectedModuleAddActions     = array ();
		$selectedModuleSelectActions  = array ();
		$selectedModulePatternActions = array ();

		$n = count ($relatedModuleNames);
		for ($i = 0; $i < $n; $i++) {
			if (trim ($relatedModuleNames [ $i ]) !== '') {
				$selectedModuleNames []          = $relatedModuleNames [ $i ];
				$selectedModuleLabels []         = $relatedModuleLabels [ $i ];
				$selectedModuleAddActions []     = $relatedModuleAddActions [ $i ];
				$selectedModuleSelectActions []  = $relatedModuleSelectActions [ $i ];
				$selectedModulePatternActions [] = $relatedModulePatternActions [ $i ];
			}
		}

		$_SESSION ['listaModulos']      = $selectedModuleNames;
		$_SESSION ['labelModulos']      = $selectedModuleLabels;
		$_SESSION ['listaAccionAdd']    = $selectedModuleAddActions;
		$_SESSION ['listaAccionSelect'] = $selectedModuleSelectActions;
		$_SESSION ['listaAccionPatron'] = $selectedModulePatternActions;
	}

	$availableFields = WizardUtils::getFieldListAsOptions (SettingsUtils::purify ($_SESSION, 'etiquetaCampo'), SettingsUtils::purify ($_SESSION, 'nombreCampo'));
	$selectedFilters = SettingsUtils::purify ($_SESSION, 'columnasFiltro');

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AVAILABLE_FIELDS', $availableFields);
	$smarty->assign ('MODULES', WizardUtils::getModuleListAsOptions ($adb));
	$smarty->assign ('ID_DLG_CREACION_MODULOS', 'dlgCreaModulos');
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', SettingsUtils::purify ($_REQUEST, 'module'));
	$smarty->assign ('SELECTED_FILTERS', $selectedFilters);
	$smarty->assign ('SELECTED_IDENTIFIER', SettingsUtils::purify ($_SESSION, 'campoIdentificador', ''));
	$smarty->assign ('SELECTED_REPORT_AVAILABILITY', SettingsUtils::purify ($_SESSION, 'reportAvailable'));
	$smarty->assign ('SELECTED_LABELS', $_SESSION ['labelModulos']);
	$smarty->assign ('SELECTED_MODULES', $_SESSION ['listaModulos']);
	$smarty->assign ('SELECTED_INSERTS', $_SESSION ['listaAccionAdd']);
	$smarty->assign ('SELECTED_SELECTS', $_SESSION ['listaAccionSelect']);
	$smarty->assign ('SELECTED_PATTERNS', $_SESSION ['listaAccionPatron']);
	$smarty->assign ('THEME', $theme);
	echo $smarty->fetch ('Settings/ModuleManager/wizardPaso4.tpl');
