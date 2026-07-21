<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Reports/lib/ReportUtils.class.php');

	global $adb, $current_user;

	$columns              = PlatzillaUtils::purify ($_POST, 'columns');
	$folderId             = PlatzillaUtils::purify ($_POST, 'folderid');
	$isScheduled          = PlatzillaUtils::purify ($_POST, 'isscheduled', false);
	$locked                = PlatzillaUtils::purify ($_POST, 'locked', 0);
	$moduleName           = PlatzillaUtils::purify ($_POST, 'modulename');
	$name                 = PlatzillaUtils::purify ($_POST, 'name');
	$reportId             = PlatzillaUtils::purify ($_POST, 'record');
	$standardFilterColumn = PlatzillaUtils::purify ($_POST, 'standardfiltercolumn');
	$type                 = PlatzillaUtils::purify ($_POST, 'type');
	
	// Procesar campos de agrupación dinámicamente (hasta 10)
	$groupings = array();
	$sortings = array();
	for($i = 1; $i <= 10; $i++) {
		$groupField = PlatzillaUtils::purify($_POST, 'Group' . $i);
		$sortField = PlatzillaUtils::purify($_POST, 'Sort' . $i);
		if(!empty($groupField) && $groupField != 'none') {
			$groupings[$i] = $groupField;
			$sortings[$i] = !empty($sortField) ? $sortField : 'Ascending';
		}
	}
	
	// Mantener compatibilidad con código legacy
	$firstGrouping = isset($groupings[1]) ? $groupings[1] : null;
	$secondGrouping = isset($groupings[2]) ? $groupings[2] : null;
	$thirdGrouping = isset($groupings[3]) ? $groupings[3] : null;
	$visibility           = PlatzillaUtils::purify ($_POST, 'visibility');

	$isInstance = !empty ($_SESSION ['platInstancia']);

	try {
		if (empty ($columns)) {
			throw new Exception ('No se han suministrado las columnas del reporte');
		}
		if (empty ($folderId)) {
			throw new Exception ('No se ha suministrado la carpeta del reporte');
		}
		if (empty ($moduleName)) {
			throw new Exception ('No se ha suministrado el nombre del módulo principal');
		}
		if (empty ($name)) {
			throw new Exception ('No se ha suministrado el nombre del reporte');
		}
		if (empty ($type)) {
			throw new Exception ('No se ha suministrado el tipo del reporte');
		}

		// Agregar columnas de agrupación a las columnas del reporte
		foreach($groupings as $grouping) {
			if(!empty($grouping) && !in_array($grouping, $columns)) {
				$columns[] = $grouping;
			}
		}

		$arguments = array (
			'advancedfilter'     => PlatzillaUtils::purify ($_POST, 'advancedfilter'),
			'applicationcodes'   => PlatzillaUtils::purify ($_POST, 'applicationcodes'),
			'columns'            => $columns,
			'description'        => PlatzillaUtils::purify ($_POST, 'description'),
			'firstgrouping'      => $firstGrouping,
			'firstsorting'       => isset($sortings[1]) ? $sortings[1] : 'Ascending',
			'folderid'           => $folderId,
			'locked'             => $isInstance,
			'modulename'         => $moduleName,
			'name'               => $name,
			'relatedmodulenames' => PlatzillaUtils::purify ($_POST, 'relatedmodulenames', array ()),
			'secondgrouping'     => $secondGrouping,
			'secondsorting'      => isset($sortings[2]) ? $sortings[2] : 'Ascending',
			'thirdgrouping'      => $thirdGrouping,
			'thirdsorting'       => isset($sortings[3]) ? $sortings[3] : 'Ascending',
			'groupings'          => $groupings,
			'sortings'           => $sortings,
			'totalcolumns'       => PlatzillaUtils::purify ($_POST, 'totalcolumns'),
			'type'               => $type,
			'visibility'         => $visibility,
		);

		if ($isScheduled) {
			$arguments ['schedule'] = array (
				'day'        => PlatzillaUtils::purify ($_POST, 'scheduleday'),
				'format'     => PlatzillaUtils::purify ($_POST, 'scheduleformat'),
				'frequency'  => PlatzillaUtils::purify ($_POST, 'schedulefrequency'),
				'month'      => PlatzillaUtils::purify ($_POST, 'schedulemonth'),
				'recipients' => PlatzillaUtils::purify ($_POST, 'schedulerecipients'),
				'time'       => PlatzillaUtils::purify ($_POST, 'scheduletime'),
				'weekday'    => PlatzillaUtils::purify ($_POST, 'scheduleweekday'),
			);
		} else {
			$arguments ['schedule'] = null;
		}

		if (!empty ($standardFilterColumn)) {
			$arguments ['standardfilter'] = array (
				'column' => $standardFilterColumn,
				'from'   => PlatzillaUtils::purify ($_POST, 'standardfilterfrom', '0000-00-00'),
				'period' => PlatzillaUtils::purify ($_POST, 'standardfilterperiod'),
				'to'     => PlatzillaUtils::purify ($_POST, 'standardfilterto', '0000-00-00'),
			);
		} else {
			$arguments ['standardfilter'] = null;
		}

		if ($visibility == 'Shared') {
			$arguments ['sharewith'] = PlatzillaUtils::purify ($_POST, 'sharewith');
		}

		if (empty ($reportId) || ($isInstance && intval ($locked) === 0)) {
			ReportUtils::createReport ($adb, $arguments, $current_user);
		} else {
			ReportUtils::updateReport ($adb, $reportId, $arguments, $current_user);
		}

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
