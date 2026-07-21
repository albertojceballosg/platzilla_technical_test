<?php
require_once('modules/boxscore/boxscore.php');

abstract class BoxScoreHelper {

	private static function getFulfillmentLimits ($operator, $objective, $inObjectiveVariance, $closeToObjectiveVariance) {
		if (empty ($operator)) {
			return null;
		}
		$result = array();
		if ($operator == 'menor-igual') {
			$result = boxscore::caseMenorIgualLimit($objective, $inObjectiveVariance, $closeToObjectiveVariance);
			$inObjectiveMin = $result['inObjectiveMin'];
			$inObjectiveMax = $result['inObjectiveMax'];
			$closeToObjectiveMin = $result['closeToObjectiveMin'];
			$closeToObjectiveMax = $result['closeToObjectiveMax'];
		} else {
			$result = boxscore::caseElseLimit($objective, $inObjectiveVariance, $closeToObjectiveVariance);

			$inObjectiveMin = $result['inObjectiveMin'];
			$inObjectiveMax = $result['inObjectiveMax'];
			$closeToObjectiveMin = $result['closeToObjectiveMin'];
			$closeToObjectiveMax = $result['closeToObjectiveMax'];
		}
		return array (
			'inobjectivemax'      => $inObjectiveMax,
			'inobjectivemin'      => $inObjectiveMin,
			'closetoobjectivemax' => $closeToObjectiveMax,
			'closetoobjectivemin' => $closeToObjectiveMin,
		);
	}

	public static function checkLastWeek ($w) {
		if ((date ('m', $w) == '12') && (date ('d', $w) > 28)) {
			$w = strtotime (date ('Y-m-28', $w) . ' -0 week');
		}
		return $w;
	}

	public static function formatDecimal ($value) {
		$val = explode ('.', $value);
		if ((isset ($val [1])) && ($val [1] != '')) {
			$n = strlen ($val [1]);
			if ($n > 1) {
				$decimal = substr ($val [1], 0, -($n - 1));
				$val = "{$val [0]}.{$decimal}";
			} else {
				$val = $value;
			}
		} else {
			$val = $value;
		}
		return $val;
	}

	public static function getDates ($from, $to, $escala, $months, $monthSearch) {
		$year = date ('Y');
		$month = !empty ($monthSearch) ? $months [ $monthSearch ] : date ('F');

		$dates = array ();
		if ((!empty ($from)) && (!empty ($to))) {
			$dates = boxscore::getDatesFromTo($from, $escala);
		} else if ($escala == 'Week') {
			$dates = boxscore::getDatesEscalaWeek($month, $year);
		} else {
			// Ajustando función para visualizar tres meses anteriores al mes consultado y el mes siguiente
			$monday = date ('Y-m-d', strtotime ("last monday of {$month} {$year}"));
			for ($i = 2; $i >= 0; $i--) {
				$w = strtotime ("{$monday} -{$i} month");
				$w = self::checkLastWeek ($w);
				$dates [] = array (
					'date'  => date ('Y-m-d', $w),
					'week'  => intval (date ('W', $w)),
					'month' => intval (date ('m', $w)),
					'year'  => date ('Y', $w),
				);
			}

			for ($i = 1; $i <= 1; $i++) {
				$w = strtotime ("{$monday} +{$i} month");
				$w = self::checkLastWeek ($w);
				$dates [] = array (
					'date'  => date ('Y-m-d', $w),
					'week'  => intval (date ('W', $w)),
					'month' => intval (date ('m', $w)),
					'year'  => date ('Y', $w),
				);
			}
		}

		return $dates;
	}

	public static function getMonths () {
		return array (
			'01' => 'January',
			'02' => 'February',
			'03' => 'March',
			'04' => 'April',
			'05' => 'May',
			'06' => 'June',
			'07' => 'July',
			'08' => 'August',
			'09' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December',
		);
	}

	public static function getScale (PearDatabase $adb, $recordId) {
		$result = $adb->pquery ('SELECT escala FROM vtiger_boxscore WHERE boxscoreid=?', array ($recordId));
		if (($result) && ($adb->num_rows ($result))) {
			$row = $adb->fetchByAssoc ($result);
			return $row ['escala'];
		} else {
			return null;
		}
	}

	public static function calculateFulfillment ($crmId, $boxScoreId) {
		global $adb, $current_user;

		$type = SettingsUtils::purify ($_REQUEST, 'tipo');
		$monthSearch = SettingsUtils::purify ($_REQUEST, 'monthsearch', date ('m'));
		$year = date ('Y');
		$day = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
		$from = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
		$to = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));

		$whereClauses = array ();
		$arguments = array ();
		if (!empty ($crmId)) {
			$whereClauses [] = 'vbsd.box_score_dataid=?';
			$arguments [] = $crmId;
		}
		if (!empty ($type)) {
			$whereClauses [] = 'vbsd.tipo=?';
			$arguments [] = $type;
		}
		if ($boxScoreId != '') {
			$whereClauses [] = 'vbsd.boxscoreid=?';
			$arguments [] = $boxScoreId;
		}
		$whereClauses = count ($whereClauses) > 0 ? 'WHERE ' . join (' AND ', $whereClauses) : '';

		$result = $adb->pquery (
			"SELECT
						vbsd.box_score_dataid,
						vbsd.box_score,
						vbsd.tipo,
						vbsd.boxscoreid,
						vbsd.accountid,
						vbsd.description,
						bk.colorbase,
						bk.colordegrade,
						o.objective AS objetivo,
						o.operator,
						o.box_score_objectiveid,
						o.objective,
						o.month_apli,
						o.date_from,
						o.date_end,
						o.cumplimiento
					FROM
						vtiger_box_score_data vbsd
						INNER JOIN vtiger_boxscore_blocks bk on bk.tipo=vbsd.tipo
						INNER JOIN vtiger_boxscore_privileges p ON p.boxscoreid=vbsd.boxscoreid AND p.box_score_dataid=vbsd.box_score_dataid AND p.userid=?
						LEFT OUTER JOIN vtiger_box_score_objective o ON o.box_score_dataid=vbsd.box_score_dataid AND o.month_apli=? AND o.date_from=? AND o.date_end=?
					{$whereClauses}
					ORDER BY
						vbsd.tipo ASC
					LIMIT 1",
			array_merge (array ($current_user->id, $monthSearch, $from, $to), $arguments)
		);
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return array ();
		}

		$row = $adb->fetchByAssoc ($result);
		return $row ['cumplimiento'];
	}

	public static function getFulfillmentsByBoxScoreDataId ($boxScoreDataId) {
		global $adb;

		$whereClause = '';
		$arguments = array ();
		if (!empty ($boxScoreDataId)) {
			$whereClause = 'WHERE vbsd.box_score_dataid=? AND o.box_score_objectiveid IS NOT NULL';
			$arguments [] = $boxScoreDataId;
		}

		$result = $adb->pquery (
			"SELECT
						vbsd.*
					FROM
						vtiger_box_score_data_cump vbsd
						LEFT OUTER JOIN vtiger_box_score_objective o ON o.box_score_dataid=vbsd.box_score_dataid AND o.box_score_objectiveid=vbsd.box_score_objectiveid
					{$whereClause}
					GROUP BY
						vbsd.etiqueta
					ORDER BY
						vbsd.etiqueta DESC",
			$arguments
		);

		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return array ();
		}

		$fulfillment = array ();
		while ($row = $adb->fetchByAssoc ($result)) {
			$fulfillment [] = $row;
		}
		return $fulfillment;
	}

	public static function getFulfillmentByBoxScoreDataId ($boxScoreDataId, $objectiveId, $label) {
		global $adb;
		$result = $adb->pquery (
			'SELECT * FROM vtiger_box_score_data_cump WHERE box_score_dataid=? AND etiqueta=? AND box_score_objectiveid=? LIMIT 1',
			array ($boxScoreDataId, $label, $objectiveId)
		);
		return ($result) && ($adb->num_rows ($result) == 0) ? $adb->fetchByAssoc ($result, -1, false) : null;
	}

	public static function updateFulfillmentValue ($boxScoreDataId, $value) {
		global $adb;

		if ((isset ($_REQUEST ['monthsearch'])) && ($_REQUEST ['monthsearch'] != '')) {
			$monthSearch = vtlib_purify ($_REQUEST ['monthsearch']);
		} else {
			$monthSearch = date ('m');
		}
		$year = date ('Y');
		$day = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
		$from = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
		$to = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));

		$result = $adb->pquery (
			'SELECT
						vbsd.*,
						o.objective,
						o.box_score_objectiveid,
						o.operator
					FROM
						vtiger_box_score_data_cump vbsd
						LEFT OUTER JOIN vtiger_box_score_objective o ON o.box_score_dataid=vbsd.box_score_dataid AND o.box_score_objectiveid=vbsd.box_score_objectiveid AND o.month_apli=? AND o.date_from=? AND o.date_end=?
					WHERE
						vbsd.box_score_dataid=? AND
						o.box_score_objectiveid IS NOT NULL',
			array ($monthSearch, $from, $to, $boxScoreDataId)
		);
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return;
		}

		$inObjectiveRow = $adb->fetchByAssoc ($result);
		$closeToObjectiveRow = $adb->fetchByAssoc ($result);

		$operator = $inObjectiveRow ['operator'];
		$inObjectiveVariance = doubleval ($inObjectiveRow ['valor_varianza']);
		$closeToObjectiveVariance = doubleval ($closeToObjectiveRow ['valor_varianza']);
		$objective = doubleval (trim (str_replace (',', '.', $inObjectiveRow ['objective']), '%'));
		$limits = self::getFulfillmentLimits ($operator, $objective, $inObjectiveVariance, $closeToObjectiveVariance);

		if ($limits) {
			$fulfillment = limitFulfillment($limits, $value, $objective);
		} else {
			$fulfillment = '';
		}

		$adb->pquery (
			'UPDATE vtiger_box_score_objective SET cumplimiento=? WHERE box_score_dataid=? AND box_score_objectiveid=?',
			array ($fulfillment, $boxScoreDataId, $inObjectiveRow ['box_score_objectiveid'])
		);
	}

	public function limitFulfillment($limits, $value, $objective) {
		if (($value >= $limits ['inobjectivemin']) && ($value <= $limits ['inobjectivemax'])) {
			$fulfillment = 'De acuerdo al objetivo';
		} else if (($value >= $limits ['closetoobjectivemin']) && ($value <= $limits ['closetoobjectivemin'])) {
			$fulfillment = 'Cerca del objetivo';
		} else if (($objective != '') && ($value != '')) {
			$fulfillment = 'Lejos del objetivo';
		} else {
			$fulfillment = '';
		}

		return $fulfillment;
	}

	public static function updateInObjectiveFulfillment ($data, $boxScoreDataId, $objectiveId) {
		global $adb;
		if ((isset ($data ['boxscorecump_dao_0'])) && (!empty ($data ['boxscorecump_dao_0']))) {
			$fulfillment = self::getFulfillmentByBoxScoreDataId ($boxScoreDataId, $objectiveId, $data ['cumplimiento_0']);
		} else {
			$fulfillment = null;
		}
		if (!$fulfillment) {
			$adb->pquery (
				"INSERT INTO vtiger_box_score_data_cump (cumplimiento, valor_varianza, box_score_dataid, etiqueta, tipo_varianza, box_score_objectiveid) VALUES ('De acuerdo al objetivo', ?, ?, ?, ?, ?)",
				array (trim (str_replace (',', '.', $data ['dao_inf_0']), '%'), $boxScoreDataId, $data ['cumplimiento_0'], $data ['tipo_dao_inf_0'], $objectiveId)
			);
		} else {
			$adb->pquery (
				"UPDATE vtiger_box_score_data_cump SET cumplimiento='De acuerdo al objetivo', valor_varianza=?, etiqueta=?, tipo_varianza=? WHERE box_score_objectiveid=? AND box_score_dataid=? AND id=?",
				array (trim (str_replace (',', '.', $data ['dao_inf_0']), '%'), $data ['cumplimiento_0'], $data ['tipo_dao_inf_0'], $objectiveId, $boxScoreDataId, $fulfillment ['id'])
			);
		}
	}

	public static function updateCloseToObjectiveFulfillment ($data, $boxScoreDataId, $objectiveId) {
		global $adb;
		if ((isset ($data ['boxscorecump_dao_1'])) && (!empty ($data ['boxscorecump_dao_1']))) {
			$fulfillment = self::getFulfillmentByBoxScoreDataId ($boxScoreDataId, $objectiveId, $data ['cumplimiento_1']);
		} else {
			$fulfillment = null;
		}
		if (!$fulfillment) {
			$adb->pquery (
				"INSERT INTO vtiger_box_score_data_cump (cumplimiento, valor_varianza, box_score_dataid, etiqueta, tipo_varianza, box_score_objectiveid) VALUES ('Cerca del objetivo', ?, ?, ?, ?, ?)",
				array (trim (str_replace (',', '.', $data ['dao_inf_1']), '%'), $boxScoreDataId, $data ['cumplimiento_1'], $data ['tipo_dao_inf_1'], $objectiveId)
			);
		} else {
			$adb->pquery (
				"UPDATE vtiger_box_score_data_cump SET cumplimiento='Cerca del objetivo', valor_varianza=?, etiqueta=?, tipo_varianza=? WHERE box_score_objectiveid=? AND box_score_dataid=? AND id=?",
				array (trim (str_replace (',', '.', $data ['dao_inf_1']), '%'), $data ['cumplimiento_1'], $data ['tipo_dao_inf_1'], $objectiveId, $boxScoreDataId, $fulfillment ['id'])
			);
		}
	}

}
