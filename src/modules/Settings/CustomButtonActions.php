<?php
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb;

	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'fld_module');

	if ((empty ($function)) || (empty ($moduleName))) {
		exit ();
	}

	if ($function == 'getColumns') {
		$result  = $adb->pquery (
			'SELECT
				f.fieldname,
				f.fieldlabel,
				f.tablename,
				f.uitype,
				f.typeofdata
			FROM
				vtiger_field f
				INNER JOIN vtiger_blocks b ON f.block=b.blockid AND b.visible=0 AND b.display_status=1
				INNER JOIN vtiger_tab t ON t.tabid=f.tabid
			WHERE
				f.presence IN (?, ?) AND
				f.uitype NOT IN (?, ?, ?, ?, ?, ?, ?) AND
				t.name=?',
			array (FieldInterface::PRESENCE_USER_DEFINED, FieldInterface::PRESENCE_VISIBLE, FieldInterface::UI_TYPE_EMAIL, FieldInterface::UI_TYPE_GRID, FieldInterface::UI_TYPE_IMAGE_DISPLAY, FieldInterface::UI_TYPE_MODIFIED_BY, FieldInterface::UI_TYPE_MODULE_REFERENCE, FieldInterface::UI_TYPE_PHONE, FieldInterface::UI_TYPE_URL, $moduleName)
		);
		$columns = array ();
		if ($adb->num_rows ($result) != 0) {
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fieldtype  = explode ('~', $row ['typeofdata']);
				$columns [] = array (
					'fieldname'  => $row ['fieldname'],
					'label'      => html_entity_decode (getTranslatedString ($row ['fieldlabel'], $moduleName), ENT_QUOTES, 'UTF-8'),
					'tablename'  => $row ['tablename'],
					'uitype'     => $row ['uitype'],
					'typeofdata' => $fieldtype[0],
				);
			}
			usort (
				$columns,
				function ($columnA, $columnB) {
					return strcmp ($columnA ['label'], $columnB ['label']);
				}
			);
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		echo json_encode ($columns);
	}
	exit ();
