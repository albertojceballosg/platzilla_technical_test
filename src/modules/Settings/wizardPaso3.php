<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/Settings/lib/WizardUtils.class.php');

	global $adb, $mod_strings, $theme;

	$protectedVariables = array ('module', 'action', 'Ajax', 'plat');
	$keys               = array_keys ($_POST);
	foreach ($keys as $key) {
		if (!in_array ($key, $protectedVariables)) {
			$_SESSION [ $key ] = SettingsUtils::purify ($_POST, $key);
		}
	}

	if ((isset ($_SESSION ['nombreBloque'])) && (is_array ($_SESSION ['nombreBloque']))) {
		$fieldBlockNumbers  = array ();
		$fieldLabels        = array ();
		$fieldLengths       = array ();
		$fieldModules       = array ();
		$fieldNames         = array ();
		$fieldPrecisions    = array ();
		$fieldPrefixes      = array ();
		$fieldSequences     = array ();
		$fieldTypes         = array ();
		$fieldValues        = array ();
		$fieldBarMinValues  = array ();
		$fieldBarMaxValues  = array ();
		$fieldBarInitValues = array ();
		$fieldBarOrderings  = array ();

		$n = count ($_SESSION ['nombreCampo']);
		for ($i = 0; $i < $n; $i++) {
			if (trim ($_SESSION ['nombreCampo'][ $i ]) !== '') {
				$fieldBlockNumbers []  = $_SESSION ['numeroBloqueCampo'][ $i ];
				$fieldLabels []        = $_SESSION ['etiquetaCampo'][ $i ];
				$fieldLengths []       = $_SESSION ['tamanoCampo'][ $i ];
				$fieldModules []       = $_SESSION ['moduloCampo'][ $i ];
				$fieldNames []         = $_SESSION ['nombreCampo'][ $i ];
				$fieldPrecisions []    = $_SESSION ['precisionCampo'][ $i ];
				$fieldPrefixes []      = $_SESSION ['prefijoCampo'][ $i ];
				$fieldSequences []     = $_SESSION ['secuenciaCampo'][ $i ];
				$fieldTypes []         = $_SESSION ['tipoCampo'][ $i ];
				$fieldValues []        = $_SESSION ['valoresCampo'][ $i ];
				$fieldBarMinValues []  = $_SESSION ['campoBarra']['min'][ $i ];
				$fieldBarMaxValues []  = $_SESSION ['campoBarra']['max'][ $i ];
				$fieldBarInitValues [] = $_SESSION ['campoBarra']['ini'][ $i ];
				$fieldBarOrderings []  = $_SESSION ['campoBarra']['ord'][ $i ];
			}
		}

		$_SESSION ['numeroBloqueCampo'] = $fieldBlockNumbers;
		$_SESSION ['etiquetaCampo']     = $fieldLabels;
		$_SESSION ['tamanoCampo']       = $fieldLengths;
		$_SESSION ['moduloCampo']       = $fieldModules;
		$_SESSION ['nombreCampo']       = $fieldNames;
		$_SESSION ['precisionCampo']    = $fieldPrecisions;
		$_SESSION ['prefijoCampo']      = $fieldPrefixes;
		$_SESSION ['secuenciaCampo']    = $fieldSequences;
		$_SESSION ['tipoCampo']         = $fieldTypes;
		$_SESSION ['valoresCampo']      = $fieldValues;
		$_SESSION ['campoBarra']['min'] = $fieldBarMinValues;
		$_SESSION ['campoBarra']['max'] = $fieldBarMaxValues;
		$_SESSION ['campoBarra']['ini'] = $fieldBarInitValues;
		$_SESSION ['campoBarra']['ord'] = $fieldBarOrderings;
	}

	if ((!isset ($_POST ['reportAvailable'])) && (isset ($_SESSION ['reportAvailable']))) {
		unset ($_SESSION ['reportAvailable']);
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AVAILABLE_GLOBAL_PICKLISTS', WizardUtils::getGlobalPicklists ($adb));
	$smarty->assign ('BLOCK_NAMES', $_SESSION ['nombreBloque']);
	$smarty->assign ('BLOCK_NUMBERS', $_SESSION ['numeroBloque']);
	$smarty->assign ('FIELD_BLOCK_NUMBERS', $_SESSION ['numeroBloqueCampo']);
	$smarty->assign ('FIELD_LABELS', $_SESSION ['etiquetaCampo']);
	$smarty->assign ('FIELD_LENGTHS', $_SESSION ['tamanoCampo']);
	$smarty->assign ('FIELD_MODULES', $_SESSION ['moduloCampo']);
	$smarty->assign ('FIELD_NAMES', $_SESSION ['nombreCampo']);
	$smarty->assign ('FIELD_PRECISIONS', $_SESSION ['precisionCampo']);
	$smarty->assign ('FIELD_PREFIXES', $_SESSION ['prefijoCampo']);
	$smarty->assign ('FIELD_SEQUENCES', $_SESSION ['secuenciaCampo']);
	$smarty->assign ('FIELD_TYPES', $_SESSION ['tipoCampo']);
	$smarty->assign ('FIELD_TYPE_OPTIONS', WizardUtils::getFieldTypesAsOptions ());
	$smarty->assign ('FIELD_VALUES', $_SESSION ['valoresCampo']);
	$smarty->assign ('GLOBAL_PICKLISTS', $_SESSION ['globalpicklists']);
	$smarty->assign ('ID_DLG_CREACION_MODULOS', 'dlgCreaModulos');
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', SettingsUtils::purify ($_REQUEST, 'module'));
	$smarty->assign ('MODULE_NAME', $_SESSION ['nombreCodigo']);
	$smarty->assign ('MODULE_OPTIONS', WizardUtils::getModuleListAsOptions ($adb));
	$smarty->assign ('THEME', $theme);
	$smarty->display ('Settings/ModuleManager/wizardPaso3.tpl');
