<?php
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule, $current_user;

	try {
		$result = $adb->pquery ('SELECT * FROM vtiger_entityname WHERE modulename=?', array ($currentModule));
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			throw new Exception ("El módulo {$currentModule} no tiene identificador de entidad configurado");
		}

		$row                       = $adb->fetchByAssoc ($result, -1, false);
		$entityIdentifierFieldName = $row ['fieldname'];
		$platzillaRootFolderPath   = PlatzillaUtils::getPlatzillaRootFolderPath ();
		$filePath                  = "{$platzillaRootFolderPath}/modules/{$currentModule}/{$currentModule}.php";
		if (!file_exists ($filePath)) {
			throw new Exception ("No se encuentra el archivo principal de la clase {$currentModule}");
		}
		if ($currentModule == 'Calendar') {
			require_once ("modules/{$currentModule}/Activity.php");
			/** @var CRMEntity|stdClass $entity */
			$entity = new Activity ();
		} else {
			require_once ("modules/{$currentModule}/{$currentModule}.php");
			/** @var CRMEntity|stdClass $entity */
			$entity       = new $currentModule ();
		}

		$entity->mode = 'create';
		foreach ($_POST as $fieldName => $fieldValue) {
			if (in_array ($fieldName, array ('action', 'Ajax', 'module'))) {
				continue;
			}
			$entity->column_fields [ $fieldName ] = PlatzillaUtils::purify ($_POST, $fieldName);
		}
		$entity->column_fields ['assigned_user_id'] = $current_user->id;
		$entity->save ($currentModule);

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode (array_merge (array ('crmid' => $entity->id, 'entityidentifiervalue' => $entity->column_fields [ $entityIdentifierFieldName ]), $entity->column_fields));
	} catch (Exception $e) {
		header ('HTTP/1.1 500 Internal server error');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
