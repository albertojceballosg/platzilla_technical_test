<?php
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb;

	$recordId  = PlatzillaUtils::purify ($_GET, 'record');
	$theModule = PlatzillaUtils::purify ($_GET, 'theModule');
	$toId      = PlatzillaUtils::purify ($_GET, 'recordTo');

	$entity = PlatformUtils::getCrmEntity ($adb, $theModule, $recordId);

	if (!empty ($entity)) {
		$details = $entity->column_fields;
		foreach ($details as $fieldName => $value) {
			if (is_array ($value)) {
				$gridValues = GridFieldUtils::getGridValues ($adb, $theModule, $fieldName, $recordId, true);
				if (!empty ($gridValues)) {
					if (array_key_exists ('summary', $gridValues)) {
						$fieldGrid      = FieldGridManager::getInstance($adb)->fetchFieldGrid($theModule, $fieldName);
						$gridColumnName = array_map (
							function ($s, $p) {
								return $s . '_' . $p;
							},
							array_keys ($gridValues ['summary']),
							array_fill (0, count ($gridValues ['summary']), $fieldGrid [0]->getFieldId ())
						);
						$details = array_merge ($details, array_combine ($gridColumnName, array_values ($gridValues['summary'])));
					}
				}
			}
		}
		$codeModuleName = trim("cod_{$theModule}");
		if (!key_exists($codeModuleName, $details)) {
			$codeModuleName = substr ("cod_{$theModule}", 0, 16);
		}

		$data = array (
			'fromid'     => $recordId,
			'codmodule'  => (isset ($details[ $codeModuleName ])) ? $details [ $codeModuleName ] : null,
			'frommodule' => $theModule,
			'fieldsto'   => PlatzillaUtils::purify ($_GET, 'fieldToImport'),
			'fieldsfrom' => PlatzillaUtils::purify ($_GET, 'fieldToExport'),
			'tomodule'   => PlatzillaUtils::purify ($_GET, 'toModule'),
			'toid'       => (!empty($toId)) ? $toId : 1,
			'rowindex'   => PlatzillaUtils::purify ($_GET, 'rowIndex'),
			'recordmask' => PlatzillaUtils::purify ($_GET, 'actionMode'),
		);
		GridFieldUtils::setRelatedImport ($adb, $data);
	} else {
		$details = null;
	}

	header ('HTTP/1.1 200 OK');
	header ('Content-Type: application/json');
	echo json_encode ($details);
	exit ();
