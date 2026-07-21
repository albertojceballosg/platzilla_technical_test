<?php
require_once('include/utils/utils.php');
require_once('modules/admin_widgets/admin_widgets.php');

if($_REQUEST['function'] == 'getColumns' && $_REQUEST['fld_module'] != '') {
	$Widgets = new Widgets();

	$tableName = $Widgets->getEntityTableName($_REQUEST['fld_module']);

	$tabId = getTabid($_REQUEST['fld_module']);

	global $adb;

	$fields = array();

	$sql = "SELECT f.columnname,f.tablename,uitype,fieldlabel
		FROM vtiger_field f JOIN vtiger_blocks b ON (block=blockid)
		 
		WHERE presence in (0,2) AND visible = 0 AND display_status = 1
		AND uitype in (7,15)
		AND f.tabid = '".$tabId."' ";

	$result = $adb->query($sql);

	while($row = $adb->fetchByAssoc($result)) {
		$fields[$row['columnname']] = $row['tablename'].'|'.$row['columnname'].'|'.$row['uitype'].'|'.html_entity_decode(getTranslatedString($row['fieldlabel'],$_REQUEST['fld_module']), ENT_QUOTES, 'UTF-8');
	}

	die(json_encode($fields));
}

if($_REQUEST['function'] == 'getNumericColumns' && $_REQUEST['fld_module'] != '') {
	$Widgets = new Widgets();

	$tableName = $Widgets->getEntityTableName($_REQUEST['fld_module']);

	$tabId = getTabid($_REQUEST['fld_module']);

	global $adb;

	$fields = array();

	$sql = "SELECT f.columnname,f.tablename,uitype,fieldlabel
		FROM vtiger_field f JOIN vtiger_blocks b ON (block=blockid)
		 
		WHERE presence in (0,2) AND visible = 0 AND display_status = 1
		AND uitype  in (51,7,71)
		 AND f.tabid = '".$tabId."' ";

	$result = $adb->query($sql);

	while($row = $adb->fetchByAssoc($result)) {
		$fields[$row['columnname']] = $row['tablename'].'|'.$row['columnname'].'|'.$row['uitype'].'|'.html_entity_decode(getTranslatedString($row['fieldlabel'],$_REQUEST['fld_module']), ENT_QUOTES, 'UTF-8');
	}

	die(json_encode($fields));
}

if($_REQUEST['function'] == 'getValues' && $_REQUEST['fld_module'] != '') {
	$Widgets = new Widgets();

	$columnName = $_REQUEST['fld_name'];

	$tabId = $Widgets->getTabId($_REQUEST['fld_module']);
	$tableName = $Widgets->getTableName($columnName,$tabId);

	global $adb;

	$fields = array();

	$uitype = $Widgets->getUiType($tableName,$columnName);

	if ($uitype == '7') {
		$tableId = $Widgets->getIdField($tableName);
		$sql = 'SELECT '.$tableId.', '.$columnName.' FROM '.$tableName;
	} else {
		$sql = 'SELECT * FROM vtiger_'.$columnName;
	}
	
	$result = $adb->query($sql);
	$norows = $adb->num_rows($result);

	if ($norows > 0) {
		while($row = $adb->fetch_array($result)) {
			$fields[] = $uitype.'|'.$row[0].'|'.html_entity_decode(getTranslatedString($row[1],$_REQUEST['fld_module']), ENT_QUOTES, 'UTF-8');
		}
	} else {
		$fields[] = $uitype.'|';
	}

	die(json_encode($fields));
}

if($_REQUEST['function'] == 'VerifyModule' && $_REQUEST['fld_module'] != '') {
	$Widgets = new Widgets();

	global $adb;

	$msj = '';

	$sql = 'SELECT count(widgetid) as cantidad FROM vtiger_widgets WHERE fld_module = ? AND estatus = ?';
	$result = $adb->pquery($sql,array($_REQUEST['fld_module'], 1));

	$row = $adb->fetchByAssoc($result);

	if (intval($row['cantidad']) > 1 && $_REQUEST['estatus'] == '1') {
		$msj = 'error';
	}
		
	die($msj);
}

if($_REQUEST['function'] == 'getColumnsDate' && $_REQUEST['fld_module'] != '') {
	$Widgets = new Widgets();

	$tableName = $Widgets->getEntityTableName($_REQUEST['fld_module']);

	$tabId = getTabid($_REQUEST['fld_module']);

	global $adb;

	$fields = array();

	$sql = "SELECT f.columnname,f.tablename,uitype,fieldlabel
		FROM vtiger_field f JOIN vtiger_blocks b ON (block=blockid)
		 
		WHERE presence in (0,2) AND visible = 0 AND display_status = 1
		AND uitype in (5,6,16,23)
		AND f.tabid = '".$tabId."' ";

	$result = $adb->query($sql);

	while($row = $adb->fetchByAssoc($result)) {
		$fields[$row['columnname']] = $row['tablename'].'|'.$row['columnname'].'|'.$row['uitype'].'|'.html_entity_decode(getTranslatedString($row['fieldlabel'],$_REQUEST['fld_module']), ENT_QUOTES, 'UTF-8');
	}

	die(json_encode($fields));
}

if ($_REQUEST['function'] == 'getDateValue' && $_REQUEST['valorEntreFechas'] != '') {
	$valorEntreFechas = $_REQUEST['valorEntreFechas'];

	$fields = array();
	switch ($valorEntreFechas) {
		case 2:
			// hoy
			$fields['fechaDesde'] = date('Y-m-d');
			$fields['fechaHasta'] = date('Y-m-d');
			break;
		case 3:
			// Ultima semana
			$first = strtotime('last Sunday -7 days');
			$last = strtotime('next Saturday -7 days');

			$fields['fechaDesde']=date('Y-m-d',$first);
			$fields['fechaHasta']=date('Y-m-d',$last);
			break;
		case 4:
			// Semana Actual
			$first = strtotime('last Sunday');
			$last = strtotime('next Saturday');
			$fields['fechaDesde']=date('Y-m-d',$first);
			$fields['fechaHasta']=date('Y-m-d',$last);
			break;
		case 5:
			// Mes anterior
			$mesActual = date('m');
			$year = date('Y');
			$ultimoDia = date('d', ((mktime(0,0,0, $mesActual,1, $year)-1)));
			$fields['fechaDesde']=date('d-m-Y',mktime(0,0,0, ($mesActual-1),1, $year));
			$fields['fechaHasta']=date('d-m-Y',mktime(0,0,0, ($mesActual-1),$ultimoDia, $year));
			break;
		case 6:
			// Mes actual
			$mesActual = date('m');
			$year = date('Y');
			$ultimoDia = date('d', ((mktime(0,0,0, ($mesActual+1),1, $year)-1)));
			$fields['fechaDesde']=date('d-m-Y',mktime(0,0,0, $mesActual,1, $year));
			$fields['fechaHasta']=date('d-m-Y',mktime(0,0,0, $mesActual,$ultimoDia, $year));
			break;
		case 7:
			// últimos 7 días
			$hoy = date('d');
			$mesActual = date('m');
			$year = date('Y');
			$fields['fechaDesde']=date('Y-m-d', mktime(0,0,0, $mesActual,($hoy-7), $year));
			$fields['fechaHasta']=date('Y-m-d');
			break;
		case 8:
			// últimos 30 días
			$hoy = date('d');
			$mesActual = date('m');
			$year = date('Y');
			$fields['fechaDesde']=date('Y-m-d', mktime(0,0,0, $mesActual,($hoy-30), $year));
			$fields['fechaHasta']=date('Y-m-d');
			break;
		case 9:
			// últimos 60 días
			$hoy = date('d');
			$mesActual = date('m');
			$year = date('Y');
			$fields['fechaDesde']=date('Y-m-d', mktime(0,0,0, $mesActual,($hoy-60), $year));
			$fields['fechaHasta']=date('Y-m-d');
			break;
		case 10:
			// últimos 90 días
			$hoy = date('d');
			$mesActual = date('m');
			$year = date('Y');
			$fields['fechaDesde']=date('Y-m-d', mktime(0,0,0, $mesActual,($hoy-90), $year));
			$fields['fechaHasta']=date('Y-m-d');
			break;
		case 11:
			// últimos 120 días
			$hoy = date('d');
			$mesActual = date('m');
			$year = date('Y');
			$fields['fechaDesde']=date('Y-m-d', mktime(0,0,0, $mesActual,($hoy-120), $year));
			$fields['fechaHasta']=date('Y-m-d');
			break;
		default:
			$fields['fechaDesde'] = 'A';
			$fields['fechaHasta'] = 'B';
			break;
	}

	die(json_encode($fields));
}

?>
