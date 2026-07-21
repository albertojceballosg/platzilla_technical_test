<?php
	// Aumentar límite de memoria para servidor de pruebas
	@ini_set('memory_limit', '1024M');
	@ini_set('max_execution_time', 300);

	if (strstr (getcwd (), 'reportmanager')) {
		chdir ('../../');
	}

	require_once ('include/mpdf/mpdf.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/DetailViewUtils.php');
	require_once ('user_privileges/default_module_view.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('reportmanager.php');

	global $adb, $plat, $theme;
	$module = SettingsUtils::purify ($_REQUEST, 'modulename');
	$record = SettingsUtils::purify ($_REQUEST, 'record');

	define ('DEFAULT_MODULE_FOLDER', 'modules/reportmanager/');

	if (isset($_REQUEST['page']) && ($_REQUEST['page'])) {
		$PAGEACTUAL = (int) $_REQUEST['page'];
	} else {
		$PAGEACTUAL = 1;
	}

	if (isset($_REQUEST['idview']) && ($_REQUEST['idview'])) {
		$idview = $_REQUEST['idview'];
	} else if ($module == 'supplier_part_work') {
		// Módulo especial para parte de trabajo de proveedores
		$idview = 'supplierPartWorkReport';
	} else if (!empty($module)) {
		$sql = $adb->pquery (
			'SELECT code_template
			FROM vtiger_report2module rm
			INNER JOIN vtiger_tab t ON rm.tabid = t.tabid
			WHERE t.name =?
			AND rm.active = 1
			ORDER BY rm.id DESC
			LIMIT 1',
			array ($module)
		);
		if ($adb->num_rows ($sql) == 0) {
			echo 'Error inesperado, intente mas tarde!';
			exit();
		}

		$idview = $adb->query_result ($sql, 0, 'code_template');
		$view   = $idview;
	} else {
		$idview = 0;
	}

	if (file_exists ('./modules/reportmanager/ViewTemplate/' . $idview . '.php')) {
		include ('ViewTemplate/' . $idview . '.php');
	} else {
		$view = $idview;
		include ('ViewTemplate/ordinary_invoice.php');
	}

	$mpdf = new mPDF();
	$mpdf->AddPage ('P');
	$stylesheet = '';
	if ($module == 'part_work') {
		$stylesheet .= file_get_contents ('modules/part_work/part_work.css');
	} else if ($module == 'supplier_part_work') {
		$stylesheet .= file_get_contents ('modules/part_work/part_work.css');
	}
	// CSS reducido para evitar segmentation faults en servidor de pruebas
	$stylesheet .= file_get_contents ('themes/' . $theme . '/css/bootstrap/bootstrap.css');
	$stylesheet .= file_get_contents ('themes/' . $theme . '/css/invoice/invoice.css');
	// Comentados CSS que causan segmentation faults en servidor de pruebas
	//$stylesheet .= file_get_contents ('themes/' . $theme . '/css/libs/font-awesome.css');
	//$stylesheet .= file_get_contents ('themes/' . $theme . '/css/libs/nanoscroller.css');
	//$stylesheet .= file_get_contents ('themes/' . $theme . '/css/libs/nifty-component.css');
	//$stylesheet .= file_get_contents ('themes/' . $theme . '/css/compiled/theme_styles.css');
	//$stylesheet .= file_get_contents ('themes/' . $theme . '/css/libs/fullcalendar.css');
	//$stylesheet .= file_get_contents ('themes/' . $theme . '/css/libs/fullcalendar.print.css');
	//$stylesheet .= file_get_contents ('themes/' . $theme . '/css/compiled/calendar.css');
	//$stylesheet .= file_get_contents ('themes/' . $theme . '/css/libs/morris.css');
	//$stylesheet .= file_get_contents ('themes/' . $theme . '/css/libs/daterangepicker.css');
	//$stylesheet .= file_get_contents ('themes/' . $theme . '/css/libs/jquery-jvectormap-1.2.2.css');
	//$stylesheet .= file_get_contents ('https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400');

	$mpdf->WriteHTML ($stylesheet, 1);
	$mpdf->WriteHTML ($html, 2);

	try {
		if (isset($donwLoadField)) {
			$mpdf->Output ($donwLoadField, 'D');
		} else if (isset($pdfSerial)) {
			$mpdf->Output ($pdfSerial, 'D');
		} else {
			$mpdf->Output ('invoice.pdf', 'D');
		}
	} catch (Exception $e) {
		error_log("[View.php] Error al generar PDF: " . $e->getMessage());
		error_log("[View.php] Stack trace: " . $e->getTraceAsString());
		echo "Error al generar PDF: " . $e->getMessage();
		exit();
	}
