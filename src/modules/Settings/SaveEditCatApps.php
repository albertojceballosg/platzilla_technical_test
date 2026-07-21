<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$code        = SettingsUtils::purify ($_REQUEST, 'code');
	$description = SettingsUtils::purify ($_REQUEST, 'descripcion');
	$mode        = SettingsUtils::purify ($_REQUEST, 'mode');
	$name        = SettingsUtils::purify ($_REQUEST, 'name');
	$recordId    = SettingsUtils::purify ($_REQUEST, 'record');
	$status      = SettingsUtils::purify ($_REQUEST, 'status');
	$menuLabel   = SettingsUtils::purify ($_REQUEST, 'menuLabel');

	if (($mode == 'edit') && (!$recordId)) {
		$_SESSION ['error_update'] = 'No se ha suministrado el identificador de la categoría';
		header ('Location: index.php?module=Settings&action=CategoryApps&parenttab=Settings');
		exit ();
	} else if ($mode == 'edit') {
		if (!empty ($recordId)) { // Si estoy en modo edición
			$sql        = 'SELECT * FROM vtiger_category_apps WHERE (code=? OR name=?) AND (catappid<>?)';
			$parameters = array ($code, $name, $recordId);
		} else { // Si estoy en modo creación
			$sql        = 'SELECT * FROM vtiger_category_apps WHERE (code=? OR name=?)';
			$parameters = array ($code, $name);
		}
		$result = $adb->pquery ($sql, $parameters);
		if ($adb->num_rows ($result) > 0) {
			$_SESSION ['error_update'] = 'La categoría no puede ser actualizada porque ya existe otra categoría con la misma información';
			header ('Location: index.php?module=Settings&action=CategoryApps&parenttab=Settings');
			exit ();
		}
	}

	try {
		if ($mode == 'edit') {
			$result = $adb->pquery (
				'UPDATE vtiger_category_apps SET code=?, name=?, description=?, status=?, parenttab_label=? WHERE catappid=?',
				array ($code, $name, $description, $status, $menuLabel, $recordId)
			);
			if (!$result) {
				throw new Exception ('Imposible actualizar la categoría. Intente más tarde');
			}
			header ('Location: index.php?module=Settings&action=CategoryApps&parenttab=Settings');
		} else {
			if (!empty ($recordId)) { // Si estoy en modo edición
				$sql        = 'SELECT * FROM vtiger_category_apps WHERE (code=? OR name=?) AND (catappid<>?)';
				$parameters = array ($code, $name, $recordId);
			} else { // Si estoy en modo creación
				$sql        = 'SELECT * FROM vtiger_category_apps WHERE (code=? OR name=?)';
				$parameters = array ($code, $name);
			}
			$result = $adb->pquery ($sql, $parameters);
			if ($adb->num_rows ($result) > 0) {
				echo 'exist';
			} else {
				$result = $adb->pquery (
					'INSERT INTO vtiger_category_apps (code, name, description, status, parenttab_label) VALUES (?, ?, ?, ?, ?)',
					array ($code, $name, $description, $status, $menuLabel)
				);
				echo $result ? 'success' : 'fail';
			}
			return true;
		}
	} catch (Exception $e) {
		$_SESSION ['error_update'] = $e->getMessage ();
		header ("Location: index.php?module=Settings&action=EditCatApps&parenttab=Settings&record={$recordId}");
	}
