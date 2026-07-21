<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	if ((!empty ($_SESSION ['platInstancia'])) || ($current_user->is_admin != 'on')) {
		header ('Location: index.php');
		exit ();
	}

	$newMenuLabel   = PlatzillaUtils::purify ($_POST, 'newmenulabel');
	$newModuleLabel = PlatzillaUtils::purify ($_POST, 'newmodulelabel');
	$newModuleName  = PlatzillaUtils::purify ($_POST, 'newmodulename');
	$oldModuleName  = PlatzillaUtils::purify ($_POST, 'oldmodulename');

	$mm = ModuleManager::getInstance ($adb);
	try {
		$result = $adb->pquery ('SELECT * FROM vtiger_parenttab WHERE parenttab_label=? LIMIT 1', array ($newMenuLabel));
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			throw new Exception ("El menú {$newMenuLabel} no está registrado");
		}

		$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE name=? OR tablabel=? LIMIT 1', array ($newModuleName, $newModuleLabel));
		if (($result) && ($adb->num_rows ($result) > 0)) {
			$row = $adb->fetchByAssoc ($result, -1, false);
			if ($row ['name'] == $newModuleName) {
				throw new Exception ("El nombre del módulo {$newModuleName} ya ha sido registrado");
			} else {
				throw new Exception ("El título del módulo {$newModuleLabel} ya ha sido registrado");
			}
		}

		$oldModule = $mm->fetchModule ($oldModuleName);
		if (empty ($oldModule)) {
			throw new Exception ("El módulo {$oldModuleName} no está registrado");
		}

		$mm->duplicateModule ($oldModule, $newModuleName, $newModuleLabel, $newMenuLabel);
		ModuleRelationshipManager::getInstance ($adb)->duplicateRelationships ($oldModuleName, $newModuleName);
		create_tab_data_file ();
		create_parenttab_data_file ();

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => "El módulo {$oldModuleName} ha sido duplicado",
		);
		header ('Location: index.php?module=Settings&action=ModuleManager&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $_POST,
		);
		header ('Location: index.php?module=Settings&action=ModuleDuplicator&parenttab=Settings');
	}
	exit ();
