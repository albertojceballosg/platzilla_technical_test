<?php
	require_once ('include/utils/VtlibUtils.php');

	global $adb;

	if(isset($_REQUEST['monthsearch']) && !empty($_REQUEST['monthsearch'])) {
		$monthSearch = vtlib_purify($_REQUEST['boxscoreselect']);
	} else{
		$monthSearch = date ('m');
	}

	if(isset($_REQUEST['record'])) {
		$record = vtlib_purify($_REQUEST['record']);
	} else{
		$record = null;
	}

	$year = date ('Y');
	$day = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
	$from = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
	$to = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));

	$result = $adb->pquery (
		'SELECT
			vbsd.*,
			o.objective,
			o.operator
		FROM
			vtiger_box_score_data_cump vbsd
			LEFT OUTER JOIN vtiger_box_score_objective o ON o.box_score_dataid=vbsd.box_score_dataid AND o.box_score_objectiveid=vbsd.box_score_objectiveid AND o.month_apli=? AND o.date_from=? AND o.date_end=?
		WHERE
			o.box_score_objectiveid IS NOT NULL AND
			vbsd.box_score_dataid=?',
		array ($monthSearch, $from, $to, $record)
	);

	$i = 1;
	$data = '';
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result)) {
			$data .= "{$i}@@";
			$data .= "{$row ['objective']}@@";
			$data .= "{$row ['id']}@@";
			$data .= str_replace ('.', ',', $row ['valor_varianza']) . $row ['tipo_varianza'] . '@@';
			$data .= "{$row ['valor_varianza']}@@";
			$data .= "{$row ['tipo_varianza']}@@";
			$data .= "{$row ['operator']}@@";
			$data .= '---';
			$i++;
		}
	}
	echo $data;
