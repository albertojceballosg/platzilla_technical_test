<?php
	/**
	 * Archivo utilizado sólo para asignar logos a los medios. Puede ser eliminado
	 */
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/AdbManager.class.php');

	global $adb, $upload_badext;

	$result509 = $adb->query ('SELECT * FROM vtiger_clientes_bdi ORDER BY nombre_de_la_entidad');
	if ((!$result509) || ($adb->num_rows ($result509) == 0)) {
		throw new Exception ('No hay clientes registrados');
	}

	$storageRoot = realpath (__DIR__ . '/../../storage/media');

	while ($row509 = $adb->fetchByAssoc ($result509, -1, false)) {
		$resultTest = $adb->pquery (
			"SELECT DISTINCT
				im.id_nombreimagen
			FROM
				bdi_imagenmedia im
				LEFT JOIN bdi_account a ON a.accountid=im.id_account
			WHERE
				im.id_nombreimagen IS NOT NULL AND
				TRIM(im.id_nombreimagen) <> '' AND
				a.accountname=?",
			array ($row509 ['nombre_de_la_entidad'])
		);

		if ((!$resultTest) || ($adb->num_rows ($resultTest) == 0)) {
			unset ($resultTest);
			continue;
		}

		$selectedFilePath = null;
		while ($rowTest = $adb->fetchByAssoc ($resultTest, -1, false)) {
			if (file_exists ("{$storageRoot}/{$rowTest ['id_nombreimagen']}")) {
				$selectedFilePath = "{$storageRoot}/{$rowTest ['id_nombreimagen']}";
				break;
			}
		}

		unset ($resultTest);

		if (!$selectedFilePath) {
			continue;
		}

		$selectedFileMimeType = mime_content_type ($selectedFilePath);
		switch ($selectedFileMimeType) {
			case 'image/jpg':
			case 'image/jpeg':
				$selectedFileExtension = 'jpg';
				break;
			case 'image/gif':
				$selectedFileExtension = 'gif';
				break;
			case 'image/png':
				$selectedFileExtension = 'png';
				break;
			default:
				$selectedFileExtension = 'bin';
				break;
		}

		$selectedFileName = strtolower ("{$row509 ['nombre_de_la_entidad']}.{$selectedFileExtension}");

		$_FILES ['filename']['name']     = $selectedFileName;
		$_FILES ['filename']['size']     = filesize ($selectedFilePath);
		$_FILES ['filename']['error']    = 0;
		$_FILES ['filename']['type']     = $selectedFileMimeType;
		$_FILES ['filename']['tmp_name'] = $selectedFilePath;

		/** @var Documents|stdClass $document */
		$document                                     = CRMEntity::getInstance ('Documents');
		$document->column_fields ['notes_title']      = 'Logo';
		$document->column_fields ['filename']         = $selectedFileName;
		$document->column_fields ['filesize']         = filesize ($selectedFilePath);
		$document->column_fields ['filestatus']       = 1;
		$document->column_fields ['filelocationtype'] = 'I';
		$document->column_fields ['folderid']         = 1;
		$document->column_fields ['assigned_user_id'] = 1;
		$document->parentid                           = $row509 ['clientes_bdiid'];
		$document->save ('Documents');

		unset ($document);
	}
