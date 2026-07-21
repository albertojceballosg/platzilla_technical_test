<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Vtiger/layout_utils.php');
	require_once ('modules/admin_widgets/admin_widgets.php');

	if (!function_exists ('arrayColumn')) {

		function arrayColumn ($array, $column_name) {
			return array_map (
				function ($element) use ($column_name) {
					return $element[ $column_name ];
				},
				$array
			);
		}
	}

	global $mod_strings, $app_strings, $log, $theme, $currentModule, $adb;
	$theme_path = 'themes/' . $theme . '/';
	$image_path = $theme_path . 'images/';

	$smarty = new vtigerCRM_Smarty;

	$Widgets = new Widgets();

	$record = $_REQUEST['record'];
	$query  = 'SELECT * FROM vtiger_widgets WHERE widgetid = ?';
	$result = $adb->pquery ($query, array ($record));

	$tiposCalculo = $Widgets->obtenerTiposDeCalculo ();

	$fieldOp = array ();

	$out = array ();
	$out = $adb->fetchByAssoc ($result);

	// Agregamos la fecha real de acuerdo al valor del campo de la BD para calcular las fechas desde y hasta
	if ($out['tiempofecha'] != 1) {
		$fechas            = $Widgets->getDateBetween ($out['tiempofecha']);
		$out['fechadesde'] = $fechas['fechaDesde'];
		$out['fechahasta'] = $fechas['fechaHasta'];
	}

	$out['tiposCalculo'] = $tiposCalculo;

	$tabId     = $Widgets->getTabId ($out['fld_module']);
	$tableName = $Widgets->getTableName ($out['fieldoperation'], $tabId);

	$fields = array ();
	$sql    = "SELECT f.tabid,f.columnname,f.tablename,uitype,fieldlabel
	FROM vtiger_field f JOIN vtiger_blocks b ON (block=blockid)
	WHERE presence IN (0,2) AND visible = 0 AND display_status = 1
	AND uitype IN (7,15)
	AND f.tabid = '" . $tabId . "' ";

	$resultSql = $adb->query ($sql);
	while ($row = $adb->fetchByAssoc ($resultSql)) {
		$fields[ $row['columnname'] ] = $row['tablename'] . '|' . $row['columnname'] . '|' . $row['uitype'] . '|' . html_entity_decode (getTranslatedString ($row['fieldlabel'], $out['fld_module']), ENT_QUOTES, 'UTF-8');
	}

	foreach ($fields as $key => $value) {
		$item[ $key ] = explode ('|', $value);
		array_push ($fieldOp, array ('label' => getTranslatedString ($item[ $key ][3], $out['fld_module']), 'value' => $item[ $key ][1]));
	}

	$out['moduleFields'] = $fieldOp;
	$indexOp             = array_search ($adb->query_result ($result, 0, 'fieldoperation'), arrayColumn ($fieldOp, 'value'));

	$fields = array ();
	$sql    = "SELECT f.columnname,f.tablename,uitype,fieldlabel
	FROM vtiger_field f JOIN vtiger_blocks b ON (block=blockid)
	WHERE presence IN (0,2) AND visible = 0 AND display_status = 1
	AND uitype IN (51,7,71)
	AND f.tabid = '" . $tabId . "' ";

	$result = $adb->query ($sql);

	while ($row = $adb->fetchByAssoc ($result)) {
		$fields[ $row['columnname'] ] = $row['tablename'] . '|' . $row['columnname'] . '|' . $row['uitype'] . '|' . html_entity_decode (getTranslatedString ($row['fieldlabel'], $out['fld_module']), ENT_QUOTES, 'UTF-8');
	}

	$numericFieldOp = array ();
	foreach ($fields as $key => $value) {
		$item[ $key ] = explode ('|', $value);
		array_push ($numericFieldOp, array ('label' => html_entity_decode (getTranslatedString ($item[ $key ][3], $out['fld_module']), ENT_QUOTES, 'UTF-8'), 'value' => $item[ $key ][1]));
	}

	$out['moduleNumericFields'] = $numericFieldOp;

	$indexOp              = array_search ($out['fieldgrouping'], arrayColumn ($numericFieldOp, 'value'));
	$out['fieldgrouping'] = getTranslatedString (html_entity_decode ($numericFieldOp[ $indexOp ], ENT_QUOTES, 'UTF-8'), $out['fld_module']);

	// carga campos tipo fecha del modulo
	$fields = array ();
	$result = $adb->pquery (
		'SELECT
			f.columnname,
			f.fieldlabel,
			f.tablename,
			f.uitype
		FROM
			vtiger_field f
			INNER JOIN vtiger_blocks b ON b.blockid=f.block AND b.visible=0 AND b.display_status=1
		WHERE
			f.presence IN (0, 2) AND
			f.uitype IN (5, 6, 70) AND
			f.tabid = ?',
		array ($tabId)
	);
	while ($row = $adb->fetchByAssoc ($result)) {
		$fields[ $row['columnname'] ] = $row['tablename'] . '|' . $row['columnname'] . '|' . $row['uitype'] . '|' . html_entity_decode (getTranslatedString ($row['fieldlabel'], $out['fld_module']), ENT_QUOTES, 'UTF-8');
	}

	$campoFechas = array ();
	foreach ($fields as $key => $value) {
		$item[ $key ] = explode ('|', $value);
		array_push ($campoFechas, array ('label' => html_entity_decode (getTranslatedString ($item[ $key ][3], $out['fld_module']), ENT_QUOTES, 'UTF-8'), 'value' => $item[ $key ][1]));
	}
	$out['campoFechas'] = $campoFechas;

	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('CONFIGWIDGET', $out);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('OPERATIONS', $tiposCalculo);
	$smarty->assign ('LISTAMODULOS', $Widgets->getModules ());
	$smarty->assign ('RECORD', $record);
	$smarty->display ('modules/admin_widgets/EditWidget.tpl');
