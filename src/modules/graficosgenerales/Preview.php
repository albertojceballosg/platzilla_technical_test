<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/platzilla/Utils/JSGraphicUtils.php');
	require_once ('modules/graficosgenerales/lib/GraphUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200311
	global $site_URL;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
	$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
	$bugsnag->setAppVersion('1.0.0');
	if ($site_URL == 'https://apphome.platzillatest.com/') {
		$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
	} else if ($site_URL == 'https://app.platzilla.com/') {
		$bugsnag->setReleaseStage('https://app.platzilla.com/');
	} else {
		$bugsnag->setReleaseStage($site_URL);
	}
	$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
	// Agregado por EB para integrar BUGSNAG - 20200311

	global $adb, $theme;

	$dateGrouping       = PlatzillaUtils::purify ($_REQUEST, 'dategrouping');
	$fieldGrouping      = PlatzillaUtils::purify ($_REQUEST, 'fieldgrouping');
	$fieldOperation     = PlatzillaUtils::purify ($_REQUEST, 'fieldoperation');
	$graphicType        = PlatzillaUtils::purify ($_REQUEST, 'graphictype');
	$opColumn           = PlatzillaUtils::purify ($_REQUEST, 'opcolumn');
	$wModule            = PlatzillaUtils::purify ($_REQUEST, 'wmodules');
	$title              = PlatzillaUtils::purify ($_REQUEST, 'graphcTitule');
	$fieldToCalculation = PlatzillaUtils::purify ($_REQUEST, 'fieldsOperations');
	$optionsGraphic     = PlatzillaUtils::purify ($_REQUEST, 'options');
	$optionsGraphic     = GraphUtils::getOptionChart ($optionsGraphic, $graphicType);

	if (is_array ($fieldToCalculation)) {
		$calculation = join(';', $fieldToCalculation);
	}

	try {
		if (empty ($wModule)) {
			throw new Exception ('No has suministrado el nombre del módulo donde está la información que quieres graficar');
		}

		if (empty ($fieldOperation)) {
			throw new Exception ('No has suministrado los campos que contienes la información a graficar');
		}

		// Para la previsualización, usar un rango de fechas muy amplio
		// para asegurar que se muestren datos si existen
		$objectDate = new DateTime();
		$objectDate->modify ('+1 year'); // 1 año en el futuro
		$dayUntil = $objectDate->format ('Y-m-d');

		$objectDate = new DateTime();
		$objectDate->modify ('-10 year'); // 10 años en el pasado
		$dayFrom = $objectDate->format ('Y-m-d');

		$dateFrom = $dayFrom;
		$dateTo   = $dayUntil;

		$fieldName = (is_array ($fieldOperation) && (count ($fieldOperation) > 0)) ? $fieldOperation [0] : $fieldOperation;
		$fieldLabel = GraphUtils::getFieldLabel ($adb, $fieldName);

		$arguments = array (
			'fld_module'     => json_encode ($wModule),
			'fieldoperation' => json_encode ($fieldOperation),
			'fieldcompare'   => (isset ($calculation)) ? $calculation : null,
			'gridoperation'  => null,
			'operation'      => json_encode ($opColumn),
			'tipografico'    => $graphicType,
			'title'          => $title,
			'fieldgrouping'  => $fieldGrouping,
			'dategrouping'   => $dateGrouping,
			'graphicoptions' => json_encode ($optionsGraphic, JSON_FORCE_OBJECT),
		);
		$dateFilter = array (
			'dateFrom' => $dateFrom,
			'dateTo'   => $dateTo,
		);

		$data = GraphUtils::getGraphData ($adb, $arguments, $dateFilter);

		// Ajustar opciones para la previsualización con dimensiones más grandes
		// Usar dimensiones más amplias para aprovechar el espacio del modal
		if (empty($optionsGraphic['width'])) {
			$optionsGraphic['width'] = '100%';
		}
		if (empty($optionsGraphic['height'])) {
			// Altura dinámica: se calculará en el cliente para usar todo el espacio disponible
			// Usar un valor alto que será ajustado por JavaScript según el contenedor
			$optionsGraphic['height'] = 'auto';
		}
		
		// NO configurar chartArea aquí - dejar que JSGraphicUtils.php lo configure
		// según el tipo de gráfico (barras: 93%, pie: 90%, etc.)
		
		// Configuración especial de leyenda para preview
		if (empty($optionsGraphic['legend'])) {
			$optionsGraphic['legend'] = array();
		}
		// En preview, usar fuente más grande para mejor legibilidad
		if (empty($optionsGraphic['legend']['textStyle'])) {
			$optionsGraphic['legend']['textStyle'] = array('fontSize' => 13);
		}

		$graph = array (
			'applicationcode' => 'preview',
			'dataGrafico'     => $data,
			'dategrouping'    => $dateGrouping,
			'graficoid'       => 100,
			'fieldoperation'  => null,
			'fieldgrouping'   => null,
			'fld_module'      => null,
			'operation'       => null,
			'tipografico'     => $graphicType,
			'title'           => $title,
			'graphicoptions'  => json_encode ($optionsGraphic, JSON_FORCE_OBJECT),
			'google'          => true,
		);

		$smarty = new vtigerCRM_Smarty ();
		$graphsUtils = JSGraphicUtils::getInstance ($adb);

		$smarty->register_function('loadGraphic', array(&$graphsUtils, 'fetchGoogleChartJs'));
		$smarty->assign ('GRAPH', $graph);
		echo $smarty->fetch ('modules/graficosgenerales/Preview.tpl');
	} catch (Exception $e) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'javascript:;');
		echo $smarty->fetch ('Message.tpl');
	}
	exit ();
