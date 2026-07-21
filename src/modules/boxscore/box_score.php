<?php
require_once('data/CRMEntity.php');
require_once('data/Tracker.php');
require_once('include/utils/AdbManager.class.php');
require_once('include/utils/VtlibUtils.php');
require_once('modules/cuatroq/cuatroq.php');
require_once('modules/boxscore/lib/BoxScoreHelper.class.php');
require_once('modules/Settings/lib/SettingsUtils.class.php');

class box_score extends CRMEntity {
	private $months;
	public  $db;
	public  $log;

	public $boxs = array();
	public $boxsdefault = array();
	public $dates = array();
	public $calculate = array();
	public $escala  = null;
	public $sqlPrimarioReporte = null;
	public $varreporte  = null;

	public function __construct() {
		global $adb;
		existeCampoTabla('boxscoreid', 'vtiger_box_score_data', 'ALTER TABLE vtiger_box_score_data ADD COLUMN boxscoreid INT(11) NULL AFTER tipo');
		$monthSearch  = SettingsUtils::purify($_REQUEST, 'monthsearch');
		$recordId = SettingsUtils::purify($_REQUEST, 'record');
		$from  = SettingsUtils::purify($_REQUEST, 'fecha_desde');
		$to = SettingsUtils::purify($_REQUEST, 'fecha_hasta');
		$this->months = BoxScoreHelper::getMonths();
		$this->escala = BoxScoreHelper::getScale($adb, $recordId);
		$this->dates  = BoxScoreHelper::getDates($from, $to, $this->escala, $this->months, $monthSearch);
	}

	private function createBoxScoreData(array $data) {
		global $adb, $current_user;
		$adb->pquery(
			'INSERT INTO vtiger_box_score_data(box_score, objetivo, cumplimiento, tipo, description, defaultplatzilla, querykpi, querykpisemanal, module, boxscoreid) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array($data['box_score'], '', $data['cumplimiento'], $data['tipo'], $data['description'], $data['defaultplatzilla'], $data['querykpi'], $data['querykpisemanal'], $data['module'], $data['boxscoreid'])
		);
		$boxScoreDataId = $adb->getLastInsertID();
		$adb->pquery(
			'INSERT INTO vtiger_boxscore_privileges(userid, boxscoreid, box_score_dataid, visible) VALUES(?, ?, ?, ?)',
			array($current_user->id, $data['boxscoreid'], $boxScoreDataId, '1')
		);
		if($current_user->id != 1) {
			$adb->pquery(
				'INSERT INTO vtiger_boxscore_privileges(userid, boxscoreid, box_score_dataid, visible) VALUES(?, ?, ?, ?)',
				array(1, $data['boxscoreid'], $boxScoreDataId, '1')
			);
		}
		$assignedUserId = $this->getAssignedUserId($data['boxscoreid']);
		if(!in_array($assignedUserId, array(1, $current_user->id))) {
			$adb->pquery(
				'INSERT INTO vtiger_boxscore_privileges(userid, boxscoreid, box_score_dataid, visible) VALUES(?, ?, ?, ?)',
				array($assignedUserId, $data['boxscoreid'], $boxScoreDataId, '1')
			);
		}
		return $boxScoreDataId;
	}

	private function duplicateBoxScoreData($data) {
		global $adb, $current_user;
		$adb->pquery(
			'INSERT INTO vtiger_box_score_data(box_score, objetivo, cumplimiento, tipo, boxscoreid, accountid, description) VALUES(?, ?, ?, ?, ?, ?, ?)',
			array($data['box_score'], $data['objetivo'], $data['cumplimiento'], $data['tipo'], $data['boxscoreid'], $data['accountid'], $data['description'])
		);
		$boxScoreDataId = $adb->getLastInsertID();
		$adb->pquery(
			'INSERT INTO vtiger_boxscore_privileges(userid, boxscoreid, box_score_dataid, visible) VALUES(?, ?, ?, ?)',
			array($current_user->id, $data['boxscoreid'], $boxScoreDataId, '1')
		);
		if($current_user->id != 1) {
			$adb->pquery(
				'INSERT INTO vtiger_boxscore_privileges(userid, boxscoreid, box_score_dataid, visible) VALUES(?, ?, ?, ?)',
				array(1, $data['boxscoreid'], $boxScoreDataId, '1')
			);
		}
		$assignedUserId = $this->getAssignedUserId($data['boxscoreid']);
		if(!in_array($assignedUserId, array(1, $current_user->id))) {
			$adb->pquery(
				'INSERT INTO vtiger_boxscore_privileges(userid, boxscoreid, box_score_dataid, visible) VALUES(?, ?, ?, ?)',
				array($assignedUserId, $data['boxscoreid'], $boxScoreDataId, '1')
			);
		}
		return $boxScoreDataId;
	}

	private function duplicateObjectives($oldDataId, $newDataId) {
		global $adb;
		$result = $adb->pquery('SELECT * FROM vtiger_box_score_objective o WHERE o.box_score_dataid=?', array($oldDataId));
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return;
		}
		while($row = $adb->fetchByAssoc($result, -1, false)) {
			$adb->pquery(
				'INSERT INTO vtiger_box_score_objective(box_score_dataid, objective, month_apli, date_from, date_end, cumplimiento, operator) VALUES(?, ?, ?, ?, ?, ?, ?)',
				array($newDataId, $row['objective'], $row['month_apli'], $row['date_from'], $row['date_end'], $row['cumplimiento'], $row['operator'])
			);
			$objectiveId = $adb->getLastInsertID();
			$adb->query(
				"INSERT INTO vtiger_box_score_data_cump(box_score_dataid, valor_varianza, tipo_varianza, etiqueta, box_score_objectiveid)
						SELECT $newDataId, dc.valor_varianza, dc.tipo_varianza, dc.etiqueta, $objectiveId  FROM vtiger_box_score_data_cump dc WHERE dc.box_score_dataid=? AND dc.box_score_objectiveid=?"
			);
		}
	}

	private function exists($boxScoreId) {
		global $adb;
		$result = $adb->pquery(
			'SELECT
						vbsd.boxscoreid
					FROM
						vtiger_box_score_data vbsd
						INNER JOIN vtiger_boxscore_blocks bk ON bk.tipo=vbsd.tipo
						INNER JOIN vtiger_boxscore_privileges p ON p.boxscoreid=vbsd.boxscoreid AND p.box_score_dataid=vbsd.box_score_dataid
					WHERE
						vbsd.boxscoreid=?',
			array($boxScoreId)
		);
		return($result) && ($adb->num_rows($result) > 0) ? true : false;
	}

	private function getAssignedUserId($recordId) {
		global $adb;
		$result = $adb->pquery('SELECT smownerid FROM vtiger_crmentity WHERE crmid=?', array($recordId));
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return null;
		}
		$row = $adb->fetchByAssoc($result);
		return intval($row['smownerid']);
	}

	private function getBasicDataByBoxScoreDataIdsWhereClausesAndArguments($boxScoreId, $boxScoreDataIds, $type) {
		$whereClauses = array();
		$arguments = array();
		if(!empty($boxScoreDataIds) != '') {
			$whereClauses[] = "vbsd.box_score_dataid IN({$boxScoreDataIds})";
		}
		if($type != '') {
			$whereClauses[] = 'vbsd.tipo=?';
			$arguments[] = $type;
		}
		if($boxScoreId != '') {
			$whereClauses[] = 'vbsd.boxscoreid=?';
			$arguments[] = $boxScoreId;
		}
		return array(
			'whereclauses'=>count($whereClauses) > 0 ? 'WHERE ' . join(' AND ', $whereClauses) : '',
			'arguments'=>$arguments,
		);
	}

	private function getBasicDataByBoxScoreIdWhereClausesAndArguments($boxScoreId, $crmId, $type) {
		$whereClauses = array();
		$arguments = array();
		if(!empty($crmId)) {
			$whereClauses[] = 'vbsd.box_score_dataid=?';
			$arguments[] = $crmId;
		}
		if($type != '') {
			$whereClauses[] = 'vbsd.tipo=?';
			$arguments[] = $type;
		}
		if($boxScoreId != '') {
			$whereClauses[] = 'vbsd.boxscoreid=?';
			$arguments[] = $boxScoreId;
		}
		return array(
			'whereclauses'=>count($whereClauses) > 0 ? 'WHERE ' . join(' AND ', $whereClauses) : '',
			'arguments'=>$arguments,
		);
	}

	private function getDataWhereClausesAndArguments($boxScoreId, $crmId, $type) {
		$whereClauses = array();
		$arguments = array();
		if(!empty($crmId)) {
			$whereClauses[] = 'vbsd.box_score_dataid=?';
			$arguments[] = $crmId;
		}
		if($type != '') {
			$whereClauses[] = 'vbsd.tipo=?';
			$arguments[] = $type;
		}
		if($boxScoreId != '') {
			$whereClauses[] = 'vbsd.boxscoreid=?';
			$arguments[] = $boxScoreId;
		}
		return array(
			'whereclauses'=>count($whereClauses) > 0 ? 'WHERE ' . join(' AND ', $whereClauses) : '',
			'arguments'=>$arguments,
		);
	}

	private function getObjectiveByBoxScoreDataId($boxScoreDataId, $month, $from, $to) {
		global $adb;
		if(!$boxScoreDataId) {
			return null;
		}
		$result = $adb->pquery(
			'SELECT
						*
					FROM
						vtiger_box_score_objective
					WHERE
						box_score_dataid=? AND
						month_apli=? AND
						date_from=? AND
						date_end=?
					LIMIT 1',
			array($boxScoreDataId, $month, $from, $to)
		);
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return null;
		}
		return $adb->fetchByAssoc($result, -1, false);
	}

	private function getObjectives($boxScoreDataId, $year) {
		global $adb;
		$whereClause = '';
		$arguments = array();
		if(!empty($boxScoreDataId)) {
			$whereClause  = 'AND o.box_score_dataid=? AND o.box_score_objectiveid IS NOT NULL';
			$arguments[] = $boxScoreDataId;
		}
		$result = $adb->pquery("SELECT * FROM vtiger_box_score_objective o WHERE YEAR(CURDATE())=? {$whereClause}", array_merge(array($year), $arguments));
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return array();
		}
		$objectives = array();
		while($row = $adb->fetchByAssoc($result)) {
			$objectives[] = $row;
		}
		return $objectives;
	}

	private function getWeeklyCalculationsSubSelectClauses($boxScoreDataIdList, $operationList, $boxScoreId) {
		$boxScoreDataIds  = explode(',', $boxScoreDataIdList);
		$operations = explode(',', $operationList);
		$subSelectClauses = '';
		$totalBoxScoreDataIds = count($boxScoreDataIds);
		$totalOperations  = count($operations);
		for($i = 0; $i < $totalBoxScoreDataIds; $i++) {
			$weeklyData = $this->getWeeklyData($boxScoreDataIds[ $i ], $boxScoreId);
			if(count($weeklyData) > 0) {
				$subSelectClauses .= "(SELECT(REPLACE(REPLACE(bds1.valor, ',', '.'), '%', '')) FROM vtiger_box_score_data_semanal bds1 WHERE bds1.boxscoreid={$boxScoreId} AND bds1.fecha=bds.fecha AND bds1.box_score_dataid={$boxScoreDataIds[$i]})";
			} else {
				$subSelectClauses .= '( 0 )';
			}
			if($i < $totalOperations) {
				$subSelectClauses .= " {$operations[$i]}";
			}
		}
		return $subSelectClauses;
	}

	private function getWeeklyCalculationsWhereClausesAndArguments($boxScoreId) {
		$whereClauses = array();
		$arguments = array();
		if(existeCampoTabla('boxscoreid', 'vtiger_box_score_data_semanal', 'ALTER TABLE vtiger_box_score_data_semanal ADD COLUMN boxscoreid INT(11) NULL AFTER box_score_dataid')) {
			$whereClauses[] = 'bds.boxscoreid=?';
			$arguments[] = $boxScoreId;
		}
		if((isset($_REQUEST['monthsearch'])) && ($_REQUEST['monthsearch'] != '')) {
			$monthSearch = vtlib_purify($_REQUEST['monthsearch']);
			$month = $this->months[ $monthSearch ];
		} else {
			$monthSearch = date('m');
			$month = date('F');
		}
		$year = date('Y');
		$day  = date('d', mktime(0, 0, 0,($monthSearch + 1), 0, date('Y')));
		if($this->escala == 'Week') {
			$from = date('Y-m-d', strtotime("first monday of {$month} {$year}"));
			$to = date('Y-m-d', strtotime("last monday of {$month} {$year}"));
			$whereClauses[] = 'fecha>=?';
			$whereClauses[] = 'fecha<=?';
			$arguments[] = $from;
			$arguments[] = $to;
		} else {
			$from = date('Y-m-d', mktime(0, 0, 0, $monthSearch, 1, date('Y')));
			$to = date('Y-m-d', mktime(0, 0, 0, $monthSearch, $day, $year));
			$whereClauses[] = "fecha>=('{$from}' - INTERVAL 2 MONTH)";
			$whereClauses[] = "fecha<=('{$to}' + INTERVAL 1 MONTH)";
		}
		return array(
			'whereclauses'=>count($whereClauses) > 0 ? 'AND ' . join(' AND ', $whereClauses) : '',
			'arguments'=>$arguments,
		);
	}

	private function getWeeklyCalculations($boxScoreDataIdList, $operationList, $boxScoreId) {
		global $adb;
		$subSelectClauses  = $this->getWeeklyCalculationsSubSelectClauses($boxScoreDataIdList, $operationList, $boxScoreId);
		$whereClausesAndArguments = $this->getWeeklyCalculationsWhereClausesAndArguments($boxScoreId);
		$whereClauses  = $whereClausesAndArguments['whereclauses'];
		$arguments  = $whereClausesAndArguments['arguments'];
		$result = $adb->pquery(
			"SELECT
						REPLACE(({$subSelectClauses}), '.', ',') AS cal,
						WEEK(fecha,1) AS semana
					FROM
						vtiger_box_score_data bd
						INNER JOIN vtiger_box_score_data_semanal bds ON bd.box_score_dataid=bds.box_score_dataid
						INNER JOIN vtiger_boxscore_privileges p ON p.box_score_dataid=bd.box_score_dataid AND p.visible=1
					WHERE
						bd.box_score_dataid IN({$boxScoreDataIdList})
						{$whereClauses}
					GROUP BY
						WEEK(fecha, 1)
					ORDER BY
						fecha ASC",
			$arguments
		);
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return array();
		}
		$weeklyCalculations = array();
		while($row = $adb->fetchByAssoc($result)) {
			$weeklyCalculations[ $row['semana'] ] = $row;
		}
		return $weeklyCalculations;
	}

	private function getWeeklyDataWhereClausesAndArguments($boxScoreId, $from, $to, $month, $year) {
		$whereClauses = array();
		$arguments = array();
		if(
			(existeCampoTabla('boxscoreid', 'vtiger_box_score_data_semanal', 'ALTER TABLE vtiger_box_score_data_semanal ADD COLUMN boxscoreid INT(11) NULL AFTER box_score_dataid')) &&
			($boxScoreId != '')
		) {
			$whereClauses[] = 's.boxscoreid=?';
			$arguments[] = $boxScoreId;
		}
		if($this->escala == 'Week') {
			$whereClauses[] = 's.fecha>=?';
			$whereClauses[] = 's.fecha<=?';
			$arguments[] = date('Y-m-d', strtotime("first monday of {$month} {$year}"));
			$arguments[] = date('Y-m-d', strtotime("last monday of {$month} {$year}"));
		} else {
			$whereClauses[] = "s.fecha>=('{$from}' - INTERVAL 2 MONTH)";
			$whereClauses[] = "s.fecha<=('{$to}' + INTERVAL 1 MONTH)";
		}
		return array(
			'whereclauses'=>count($whereClauses) > 0 ? 'AND ' . join(' AND ', $whereClauses) : '',
			'arguments'=>$arguments,
		);
	}

	private function getWeeklyData($boxScoreDataId, $boxScoreId) {
		global $adb;
		if((isset($_REQUEST['monthsearch'])) && ($_REQUEST['monthsearch'] != '')) {
			$monthSearch = vtlib_purify($_REQUEST['monthsearch']);
			$month = $this->months[ $monthSearch ];
		} else {
			$monthSearch = date('m');
			$month = date('F');
		}
		$year = date('Y');
		$day = date('d', mktime(0, 0, 0,($monthSearch + 1), 0, date('Y')));
		$from = date('Y-m-d', mktime(0, 0, 0, $monthSearch, 1, date('Y')));
		$to  = date('Y-m-d', mktime(0, 0, 0, $monthSearch, $day, $year));
		$whereClausesAndArguments = $this->getWeeklyDataWhereClausesAndArguments($boxScoreId, $from, $to, $month, $year);
		$whereClause = $whereClausesAndArguments['whereclauses'];
		$arguments  = $whereClausesAndArguments['arguments'];
		$result = $adb->pquery(
			"SELECT *, WEEK(fecha,1) AS semana FROM vtiger_box_score_data_semanal s WHERE s.box_score_dataid=? $whereClause ORDER BY s.fecha ASC",
			array_merge(array($boxScoreDataId), $arguments)
		);
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return array();
		}
		$values = $this->getWeeklyValueByBoxScoreId($boxScoreDataId);
		$weeklyData = array();
		while($row = $adb->fetchByAssoc($result)) {
			$weeklyData[ $row['semana'] ] = $row;
			$normalizedValue  = $values ? $values['normalizedvalue'] : '';
			BoxScoreHelper::updateFulfillmentValue($row['box_score_dataid'], $normalizedValue);
		}
		return $weeklyData;
	}

	private function getWeeklyValueByBoxScoreId($boxScoreDataId, $includeWeeks = false) {
		global $adb;
		if((isset($_REQUEST['monthsearch'])) && ($_REQUEST['monthsearch'] != '')) {
			$monthSearch = vtlib_purify($_REQUEST['monthsearch']);
			$month = $this->months[ $monthSearch ];
		} else {
			$monthSearch = date('m');
			$month = date('F');
		}
		$year  = date('Y');
		$day = date('d', mktime(0, 0, 0,($monthSearch + 1), 0, date('Y')));
		$whereClauses = array();
		$arguments = array();
		if($this->escala == 'Week') {
			$from = date('Y-m-d', strtotime("first monday of {$month} {$year}"));
			$to = date('Y-m-d', strtotime("last monday of {$month} {$year}"));
			$whereClauses[] = 's.fecha>=?';
			$whereClauses[] = 's.fecha<=?';
			$whereClauses[] = "(CASE WHEN MONTH(s.fecha)<10 THEN CONCAT('0', MONTH(s.fecha)) ELSE MONTH(s.fecha) END)={$monthSearch}";
			if($includeWeeks) {
				$whereClauses[] = "WEEK(fecha,1) IN({$this->getWeeksWhereClause($from, $to)})";
			}
			$arguments[] = $from;
			$arguments[] = $to;
		} else {
			$from = date('Y-m-d', mktime(0, 0, 0, $monthSearch, 1, date('Y')));
			$to = date('Y-m-d', mktime(0, 0, 0, $monthSearch, $day, $year));
			$whereClauses[] = "s.fecha>=('{$from}' - INTERVAL 2 MONTH)";
			$whereClauses[] = "s.fecha<=('{$to}' + INTERVAL 1 MONTH)";
			$whereClauses[] = "(CASE WHEN MONTH(s.fecha)<10 THEN CONCAT('0', MONTH(s.fecha)) ELSE MONTH(s.fecha) END)={$monthSearch}";
			if($includeWeeks) {
				$whereClauses[] = "WEEK(fecha,1) IN({$this->getWeeksWhereClause($from, $to)})";
			}
		}
		$whereClauses = count($whereClauses) > 0 ? 'AND ' . join(' AND ', $whereClauses) : '';
		$result = $adb->pquery(
			"SELECT
						(REPLACE(REPLACE(valor, ',', '.'), '%', '')) AS normalizedvalue,
						valor AS realvalue,
						WEEK(fecha,1) AS semana
					FROM
						vtiger_box_score_data_semanal s
					WHERE
						s.box_score_dataid=? AND
						(REPLACE(REPLACE(valor, ',', '.'), '%', ''))<>''
						{$whereClauses}
					ORDER BY
						fecha DESC
					LIMIT 1",
			array_merge(array($boxScoreDataId), $arguments)
		);
		if(($result) && ($adb->num_rows($result) > 0)) {
			$row = $adb->fetchByAssoc($result);
			return array(
				'normalizedvalue'=>BoxScoreHelper::formatDecimal($row['normalizedvalue']),
				'realvalue'=>$row['realvalue'],
			);
		} else {
			return null;
		}
	}

	private function getWeeksWhereClause($from, $to) {
		$startDate = new DateTime($from);
		$endDate = new DateTime($to);
		$interval  = $startDate->diff($endDate);
		$week  = intval(($interval->days) / 7);
		$weeks = array();
		for($i = $week; $i >= 0; $i--) {
			$week = BoxScoreHelper::checkLastWeek(strtotime("{$to} -{$i} week"));
			$weeks[] = date('W', $week);
		}
		return join(', ', $weeks);
	}

	private function isModuleActiveForKpi($moduleName) {
		global $adb;
		$result = $adb->pquery('SELECT * FROM vtiger_tab WHERE name=? AND presence=0', array($moduleName));
		return($result) && ($adb->num_rows($result) > 0) ? true : false;
	}

	private function getDefaultDuplicatedDataWhereClausesAndArguments($oldBoxScoreId, $type, $crmId) {
		$whereClauses = array();
		$arguments = array();
		if(!empty($crmId)) {
			$whereClauses[] = 'vbsd.box_score_dataid=?';
			$arguments[] = $crmId;
		}
		if($type != '') {
			$whereClauses[] = 'vbsd.tipo=?';
			$arguments[] = $type;
		}
		if($oldBoxScoreId != '') {
			$whereClauses[] = 'vbsd.boxscoreid=?';
			$arguments[] = $oldBoxScoreId;
		}
		return array(
			'whereclauses'=>count($whereClauses) > 0 ? 'WHERE ' . join(' AND ', $whereClauses) : '',
			'arguments'=>$arguments,
		);
	}

	private function loadDefaultDuplicatedData($newBoxScoreId, $oldBoxScoreId, $crmId = 0) {
		global $adb;
		$type =(isset($_REQUEST['tipo'])) ? vtlib_purify($_REQUEST['tipo']) : null;
		$whereClausesAndArguments = $this->getDefaultDuplicatedDataWhereClausesAndArguments($oldBoxScoreId, $type, $crmId);
		$whereClause = $whereClausesAndArguments['whereclauses'];
		$arguments  = $whereClausesAndArguments['arguments'];
		$result = $adb->pquery(
			"SELECT
						vbsd.box_score_dataid,
						vbsd.box_score,
						vbsd.objetivo,
						vbsd.cumplimiento,
						vbsd.tipo,
						vbsd.description,
						NULL AS boxscoreid,
						NULL AS accountid
					FROM
						vtiger_box_score_data vbsd
						INNER JOIN vtiger_boxscore_privileges p ON p.boxscoreid=vbsd.boxscoreid AND p.box_score_dataid=vbsd.box_score_dataid AND p.userid=?
					{$whereClause}",
			$arguments
		);
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return;
		}
		while($row = $adb->fetchByAssoc($result, -1, false)) {
			$oldDataId = $row['box_score_dataid'];
			$newDataId = $this->duplicateBoxScoreData($row);
			$this->duplicateObjectives($oldDataId, $newDataId);
			$adb->pquery(
				"INSERT INTO vtiger_box_score_data_semanal(box_score_dataid, boxscoreid, accountid, fecha, valor)
							SELECT $newDataId, $newBoxScoreId, NULL, fecha, valor FROM vtiger_box_score_data_semanal WHERE boxscoreid=? AND box_score_dataid=?",
				array($oldBoxScoreId, $oldDataId)
			);
			$row['boxscoreid'] = $oldBoxScoreId;
			$row['box_score_dataid'] = $newDataId;
			$row['semanal'] = $this->getWeeklyData($newDataId, $oldBoxScoreId);
			$this->boxs[] = $row;
		}
	}

	private function loadDefaultPlatzillaData($boxScoreId) {
		$adb = AdbManager::getInstance()->getMasterAdb();
		$result = $adb->query('SELECT * FROM vtiger_kpisboxscore WHERE active=1');
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return;
		}
		while($row = $adb->fetchByAssoc($result, -1, false)) {
			$row['box_score'] = $row['name'];
			$row['cumplimiento'] = null;
			$row['tipo']  = 1;
			$row['defaultplatzilla'] = 1;
			$row['box_score_dataid'] = $this->add($row);
			$row['semanal'] = $this->getWeeklyData($row['box_score_dataid'], $boxScoreId);
			$this->boxs[] = $row;
		}
	}

	private function setWeeklyDefaultData() {
		global $adb;
		foreach($this->boxs as $key => $values) {
			if(!$this->isModuleActiveForKpi($values['module'])) {
				continue;
			}
			foreach($this->dates as $date) {
				if($this->escala == 'Month') {
					$sql = str_replace('{{ANIO}}', $date['year'], str_replace('{{MES}}', $date['month'], $values['querykpi']));
				} else {
					$sql = str_replace('{{SEMANA}}', $date['week'], str_replace('{{ANIO}}', $date['year'], str_replace('{{MES}}', $date['month'], $values['querykpisemanal'])));
				}
				$result = $adb->query($sql);
				if(($result) && ($adb->num_rows($result) > 0)) {
					$row = $adb->fetchByAssoc($result);
					$value = $row['valorkpi'];
				} else {
					$value = 0;
				}
				$result = $adb->pquery(
					'SELECT * FROM vtiger_box_score_data_semanal WHERE box_score_dataid=? AND boxscoreid=? AND fecha=?',
					array($values['box_score_dataid'], $values['boxscoreid'], $date['date'])
				);
				if($adb->num_rows($result) > 0) {
			$adb->pquery(
				'UPDATE vtiger_box_score_data_semanal SET valor=? WHERE box_score_dataid=? AND boxscoreid=? AND fecha=?',
				array($value, $values['box_score_dataid'], $values['boxscoreid'], $date['date'])
			);
				} else {
			$adb->pquery(
				'INSERT INTO vtiger_box_score_data_semanal(box_score_dataid,boxscoreid,fecha,valor) VALUES(?, ?, ?, ?)',
				array($values['box_score_dataid'], $values['boxscoreid'], $date['date'], $value)
			);
				}
			}
			$this->boxs[ $key ]['semanal'] = $this->getWeeklyData($values['box_score_dataid'], $values['boxscoreid']);
		}
	}

	private function updateBoxScoreData(array $data) {
		global $adb;
		$adb->pquery(
			'UPDATE vtiger_box_score_data SET box_score=?, objetivo=?, cumplimiento=?, tipo=?, description=?, defaultplatzilla=?, querykpi=?, querykpisemanal=?, module=?, boxscoreid=? WHERE box_score_dataid=?',
			array($data['box_score'], '', $data['cumplimiento'], $data['tipo'], $data['description'], $data['defaultplatzilla'], $data['querykpi'], $data['querykpisemanal'], $data['module'], $data['boxscoreid'], $data['record'])
		);
		return $data['record'];
	}

	public function add(array $data) {
		global $adb;
		if((isset($data['boxscoreid'])) && (!empty($data['boxscoreid']))) {
			$boxScoreDataId = $this->updateBoxScoreData($data);
		} else {
			$boxScoreDataId = $this->createBoxScoreData($data);
		}
		$n = count($data['mesobjetivo']);
		for($i = 0; $i < $n; $i++) {
			$year  = date('Y');
			$day = date('d', mktime(0, 0, 0,($data['mesobjetivo'][ $i ] + 1), 0, date('Y')));
			$from  = date('Y-m-d', mktime(0, 0, 0, $data['mesobjetivo'][ $i ], 1, date('Y')));
			$to = date('Y-m-d', mktime(0, 0, 0, $data['mesobjetivo'][ $i ], $day, $year));
			$objective = $this->getObjectiveByBoxScoreDataId($boxScoreDataId, $data['mesobjetivo'][ $i ], $from, $to);
			if(!$objective) {
				$adb->pquery(
					'INSERT INTO vtiger_box_score_objective(box_score_dataid, objective, operator, month_apli, date_from, date_end) VALUES(?, ?, ?, ?, ?, ?)',
					array($boxScoreDataId, $data['objetivo'][ $i ], $data['operador'][ $i ], $data['mesobjetivo'][ $i ], date('Y-m-d', mktime(0, 0, 0, $data['mesobjetivo'][ $i ], 1, date('Y'))), date('Y-m-d', mktime(0, 0, 0, $data['mesobjetivo'][ $i ], $day, $year)))
				);
				$objectiveId = $adb->getLastInsertID();
			} else {
				$objectiveId = $objective['box_score_objectiveid'];
				$adb->pquery(
					'UPDATE vtiger_box_score_objective SET box_score_dataid=?, objective=?, operator=?, month_apli=?, date_from=?, date_end=? WHERE box_score_objectiveid=?',
					array($boxScoreDataId, $data['objetivo'][ $i ], $data['operador'][ $i ], $data['mesobjetivo'][ $i ], date('Y-m-d', mktime(0, 0, 0, $data['mesobjetivo'][ $i ], 1, date('Y'))), date('Y-m-d', mktime(0, 0, 0, $data['mesobjetivo'][ $i ], $day, $year)), $objectiveId)
				);
			}
			BoxScoreHelper::updateInObjectiveFulfillment($data, $boxScoreDataId, $objectiveId);
			BoxScoreHelper::updateCloseToObjectiveFulfillment($data, $boxScoreDataId, $objectiveId);
			$values = $this->getWeeklyValueByBoxScoreId($boxScoreDataId);
			BoxScoreHelper::updateFulfillmentValue($boxScoreDataId, $values ? $values['normalizedvalue'] : '');
		}
		if(isset($data['record']) && !empty($data['record'])) {
			$processedMonths = join(', ', $data['mesobjetivo']);
			$adb->pquery(
				"DELETE FROM vtiger_box_score_data_cump WHERE box_score_dataid=? AND box_score_objectiveid NOT IN(
							SELECT box_score_objectiveid FROM vtiger_box_score_objective WHERE box_score_dataid=? AND month_apli NOT IN({$processedMonths})
						)",
				array($data['record'], $data['record'])
			);
			$adb->pquery(
				"DELETE FROM vtiger_box_score_objective WHERE box_score_dataid=? AND month_apli NOT IN({$processedMonths})",
				array($data['record'])
			);
		}
		return $boxScoreDataId;
	}

	public function deleteCalculation($operationId) {
		global $adb;
		$adb->pquery('DELETE FROM vtiger_boxsoperation_privileges WHERE operation=?', array($operationId));
		$adb->pquery('DELETE FROM vtiger_boxscore_operacion WHERE operacion_id=?', array($operationId));
	}

	public function getBasicDataByBoxScoreDataIds($boxScoreId, $boxScoreDataIds) {
		global $adb, $current_user;
		$type =(isset($_REQUEST['tipo'])) ? vtlib_purify($_REQUEST['tipo']) : null;
		$whereClausesAndArguments = $this->getBasicDataByBoxScoreDataIdsWhereClausesAndArguments($boxScoreId, $boxScoreDataIds, $type);
		$whereClause = $whereClausesAndArguments['whereclauses'];
		$arguments  = $whereClausesAndArguments['arguments'];
		$result = $adb->pquery(
			"SELECT
						vbsd.*
					FROM
						vtiger_box_score_data vbsd
						INNER JOIN vtiger_boxscore_privileges p ON p.boxscoreid=vbsd.boxscoreid AND p.box_score_dataid=vbsd.box_score_dataid AND p.visible=1 AND p.userid=?
					{$whereClause}",
			array_merge(array($current_user->id), $arguments)
		);
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return array();
		}
		$data = array();
		while($row = $adb->fetchByAssoc($result)) {
			$data[ $row['box_score_dataid'] ] = $row['box_score'];
		}
		return $data;
	}

	public function getBlocks() {
		global $adb;
		$type =(isset($_REQUEST['tipo'])) ? vtlib_purify($_REQUEST['tipo']) : null;
		$whereClause = '';
		$arguments = array();
		if($type != '') {
			$whereClause  = 'WHERE vbsd.tipo=?';
			$arguments[] = $type;
		}
		$result = $adb->pquery("SELECT vbsd.* FROM vtiger_boxscore_blocks vbsd {$whereClause} ORDER BY vbsd.tipo ASC", $arguments);
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return array();
		}
		$blocks = array();
		while($row = $adb->fetchByAssoc($result)) {
			$blocks[] = $row;
		}
		return $blocks;
	}

	public function getCalculations($boxScoreId) {
		global $adb, $current_user;
		$whereClause = '';
		$arguments = array();
		if($boxScoreId != '') {
			$whereClause  = 'vbsd.boxscoreid=?';
			$arguments[] = $boxScoreId;
		}
		$whereClause = count($whereClause) > 0 ? "WHERE {$whereClause}" : '';
		$result  = $adb->pquery(
			"SELECT
						vbsd.*,
						bk.colorbase,
						bk.colordegrade
					FROM
						vtiger_boxscore_operacion vbsd
						INNER JOIN vtiger_boxsoperation_privileges p ON p.operation=vbsd.operacion_id AND p.visible=1 AND p.userid=?
						INNER JOIN vtiger_boxscore_blocks bk ON bk.tipo=vbsd.tipo
					{$whereClause}
					ORDER BY
						operacion_id, tipo ASC",
			array_merge(array($current_user->id), $arguments)
		);
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return array();
		}
		$calculations = array();
		while($row = $adb->fetchByAssoc($result)) {
			$calculations[] = array(
				'operacion_id'=>$row['operacion_id'],
				'boxscoreid'=>$row['boxscoreid'],
				'calculo'=>$row['calculo'],
				'totalsemanal'=>$this->getWeeklyCalculations($row['elements'], $row['operators'], $boxScoreId),
				'tipo'=>$row['tipo'],
				'usuario'=>$row['usuario'],
				'colorbase'=>$row['colorbase'],
				'colordegrade'=>$row['colordegrade'],
			);
		}
		return $calculations;
	}

	public function getCalculation($calculationId) {
		global $adb, $current_user;
		$result = $adb->pquery(
			'SELECT
						vbsd.*
					FROM
						vtiger_boxscore_operacion vbsd
						INNER JOIN vtiger_boxsoperation_privileges p ON p.operation=vbsd.operacion_id AND p.visible=1 AND p.userid=?
					WHERE
						vbsd.operacion_id=?',
			array($current_user->id, $calculationId)
		);
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return array();
		}
		$row = $adb->fetchByAssoc($result);
		$row['boxscore_data_id'] = explode(',', $row['elements']);
		$row['operators_list'] = explode(',', $row['operators']);
		return $row;
	}

	public function getCuatroq($boxScoreId) {
		global $adb;
		$result = $adb->pquery('SELECT * FROM vtiger_cuatroq WHERE boxscore=?', array($boxScoreId));
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return array();
		}
		$cuatroq = array();
		while($row = $adb->fetchByAssoc($result)) {
			$cuatroq[ $row['box_score_dataid'] ][ $row['fecha'] ] = $row['automatico'];
		}
		return $cuatroq;
	}

	public function getWeeklyId($boxScoreDataId, $boxScoreId, $date) {
		global $adb;
		$result = $adb->pquery(
			'SELECT semanalid FROM vtiger_box_score_data_semanal WHERE box_score_dataid=? AND boxscoreid=? AND fecha=?',
			array($boxScoreDataId, $boxScoreId, $date)
		);
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return null;
		}
		$row = $adb->fetchByAssoc($result);
		return $row['semanalid'];
	}

	public function loadBasicDataByBoxScoreId($boxScoreId, $crmId = 0) {
		global $adb, $current_user;
		$type =(isset($_REQUEST['tipo'])) ? vtlib_purify($_REQUEST['tipo']) : null;
		$whereClausesAndArguments = $this->getBasicDataByBoxScoreIdWhereClausesAndArguments($boxScoreId, $crmId, $type);
		$whereClause = $whereClausesAndArguments['whereclauses'];
		$arguments  = $whereClausesAndArguments['arguments'];
		$result = $adb->pquery(
			"SELECT
						vbsd.*
					FROM
						vtiger_box_score_data vbsd
						INNER JOIN vtiger_boxscore_privileges p ON p.boxscoreid=vbsd.boxscoreid AND p.box_score_dataid=vbsd.box_score_dataid AND p.visible=1 AND p.userid=?
					{$whereClause}",
			array_merge(array($current_user->id), $arguments)
		);
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return array();
		}
		while($row = $adb->fetchByAssoc($result)) {
			$this->boxs[] = $row;
		}
		return $this->boxs;
	}

	public function loadData($boxScoreId, $crmId = 0) {
		global $adb, $current_user;
		$monthSearch =(isset($_REQUEST['monthsearch'])) && (!empty($_REQUEST['monthsearch'])) ? vtlib_purify($_REQUEST['monthsearch']) : date('m');
		$type =(isset($_REQUEST['tipo'])) ? vtlib_purify($_REQUEST['tipo']) : null;
		$year = date('Y');
		$day = date('d', mktime(0, 0, 0,($monthSearch + 1), 0, date('Y')));
		$from = date('Y-m-d', mktime(0, 0, 0, $monthSearch, 1, date('Y')));
		$to  = date('Y-m-d', mktime(0, 0, 0, $monthSearch, $day, $year));
		$whereClausesAndArguments = $this->getDataWhereClausesAndArguments($boxScoreId, $crmId, $type);
		$whereClause = $whereClausesAndArguments['whereclauses'];
		$arguments  = $whereClausesAndArguments['arguments'];
		$result = $adb->pquery(
			"SELECT
						vbsd.box_score_dataid,
						vbsd.box_score,
						vbsd.tipo,
						vbsd.boxscoreid,
						vbsd.accountid,
						vbsd.description,
						bk.colorbase,
						bk.colordegrade,
						vbsd.defaultplatzilla,
						vbsd.querykpi,
						vbsd.querykpisemanal,
						vbsd.module,
						o.objective AS objetivo,
						o.operator,
						o.box_score_objectiveid,
						o.objective,
						o.month_apli,
						o.date_from,
						o.date_end
					FROM
						vtiger_box_score_data vbsd
						INNER JOIN vtiger_boxscore_blocks bk ON bk.tipo=vbsd.tipo
						INNER JOIN vtiger_boxscore_privileges p ON p.boxscoreid=vbsd.boxscoreid AND p.box_score_dataid=vbsd.box_score_dataid AND p.userid=?
						LEFT OUTER JOIN vtiger_box_score_objective o ON o.box_score_dataid=vbsd.box_score_dataid AND o.month_apli=? AND o.date_from=? AND o.date_end=?
					{$whereClause}
					ORDER BY vbsd.tipo ASC",
			array_merge(array($current_user->id, $monthSearch, $from, $to), $arguments)
		);
		if((!$result) || ($adb->num_rows($result) == 0)) {
			return;
		}
		while($row = $adb->fetchByAssoc($result)) {
			$row['mesobjetivo']  = $row['month_apli'];
			$row['all_objetivo'] = $this->getObjectives($row['box_score_dataid'], $year);
			$row['semanal']  = $this->getWeeklyData($row['box_score_dataid'], $boxScoreId);
			$row['cump_array'] = BoxScoreHelper::getFulfillmentsByBoxScoreDataId($row['box_score_dataid']);
			$row['cumplimiento'] = BoxScoreHelper::calculateFulfillment($row['box_score_dataid'], $boxScoreId);
			$row['escala'] = $this->escala;
			$this->boxs[] = $row;
		}
	}

	public function loadDefaultData($boxScoreId, $crmId = 0) {
		$duplicateId =(isset($_REQUEST['recordDuplicate'])) ? vtlib_purify($_REQUEST['recordDuplicate']) : null;
		$recordMode  =(isset($_REQUEST['recordMode'])) ? vtlib_purify($_REQUEST['recordMode']) : null;
		if(!$this->exists($boxScoreId)) {
			if($recordMode == 'DUPLICATE') {
				$this->loadDefaultDuplicatedData($duplicateId, $boxScoreId, $crmId);
			} else {
				$this->loadDefaultPlatzillaData($boxScoreId);
			}
		} else {
			$this->loadData($boxScoreId, $crmId);
		}
		$this->setWeeklyDefaultData();
	}

	private function getReportDataWhereClausesAndArguments($boxScoreId, $kpis) {
		$whereClauses = array();
		$arguments = array();
		if($boxScoreId != '') {
			$whereClauses[] = 'vbsd.boxscoreid=?';
			$arguments[] = $boxScoreId;
		}
		if((is_array($kpis)) && (!empty($kpis))) {
			$whereClauses[] = 'vbsd.box_score_dataid in(' . str_repeat('?, ',(count($kpis) - 1)) . '?)';
			$arguments[] = $kpis;
		}
		return array(
			'whereclauses'=>count($whereClauses) > 0 ? 'WHERE ' . join(' AND ', $whereClauses) : '',
			'arguments'=>$arguments,
		);
	}

	public function loadReportData($boxScoreId, $kpis) {
		global $adb;
		$from = SettingsUtils::purify($_REQUEST, 'fecha_desde');
		$to  = SettingsUtils::purify($_REQUEST, 'fecha_hasta');
		$whereClausesAndArguments = $this->getReportDataWhereClausesAndArguments($boxScoreId, $kpis);
		$whereClause = $whereClausesAndArguments['whereclauses'];
		$arguments  = $whereClausesAndArguments['arguments'];
		$sql = "SELECT vbsd.* FROM vtiger_box_score_data vbsd {$whereClause}";
		$result = $adb->pquery($sql, $arguments);
		if(($result) && ($adb->num_rows($result) > 0)) {
			while($row = $adb->fetchByAssoc($result)) {
				$row['boxscoreid'] = $boxScoreId;
				$row['semanal'] = $this->getWeeklyData($row['box_score_dataid'], $boxScoreId);
				$this->boxs[] = $row;
			}
		}
		$this->sqlPrimarioReporte = $sql;
		if(($from) && ($to)) {
			$this->varreporte = array(
				'fecha_desde'=>$from,
				'fecha_hasta'=>$to,
			);
		} else {
			$this->varreporte = array();
		}
	}

	public function registerCuatroq($boxScoreDataId, $boxScoreId, $weeklyId, $date, $auto) {
		global $adb, $current_user;
		$result = $adb->pquery(
			'SELECT cuatroqid FROM vtiger_cuatroq WHERE semanalid=? AND boxscore=? AND box_score_dataid=?',
			array($weeklyId, $boxScoreId, $boxScoreDataId)
		);
		/** @var cuatroq|stdClass $entity */
		$entity = new cuatroq();
		$entity->column_fields['boxscore'] = $boxScoreId;
		$entity->column_fields['box_score_dataid'] = $boxScoreDataId;
		$entity->column_fields['semanalid'] = $weeklyId;
		$entity->column_fields['fecha'] = $date;
		$entity->column_fields['automatico'] = $auto;
		$entity->column_fields['assigned_user_id'] = $current_user->id;
		if(($result) && ($adb->num_rows($result) == 1)) {
			$row = $adb->fetchByAssoc($result);
			$entity->mode = 'edit';
			$entity->id = $row['cuatroqid'];
		}
		$entity->save('cuatroq');
	}

	public function saveBlock($colorbase, $colordegrade) {
		global $adb;
		$adb->pquery('INSERT INTO vtiger_boxscore_blocks(colorbase, colordegrade) VALUES(?, ?)', array($colorbase, $colordegrade));
		return $adb->getLastInsertID();
	}

	public function saveCalculation($boxScoreId, $calculation, $elements, $operators) {
		global $adb, $current_user;
		$type =(isset($_REQUEST['tipo'])) ? vtlib_purify($_REQUEST['tipo']) : null;
		$adb->pquery(
			'INSERT INTO vtiger_boxscore_operacion(boxscoreid, calculo, elements, operators, tipo, usuario) VALUES(?, ?, ?, ?, ?, ?)',
			array($boxScoreId, $calculation, $elements, $operators, $type, $current_user->id)
		);
		$operationId = $adb->getLastInsertID();
		$adb->pquery(
			'INSERT INTO vtiger_boxsoperation_privileges(userid, operation, visible) VALUES(?, ?, ?)',
			array($current_user->id, $operationId, '1')
		);
		return $adb->getLastInsertID();
	}

	public function update(array $data) {
		global $adb;
		if((isset($_REQUEST['monthsearch'])) && ($_REQUEST['monthsearch'] != '')) {
			$monthSearch = vtlib_purify($_REQUEST['monthsearch']);
		} else {
			$monthSearch = date('m');
		}
		$year = date('Y');
		foreach($data['value'] as $boxScoreDataId => $weeks) {
			foreach($weeks as $week => $value) {
				if($data['semanalid'][ $boxScoreDataId ][ $week ]) {
					$adb->pquery(
						'UPDATE vtiger_box_score_data_semanal SET box_score_dataid=?, boxscoreid=?, fecha=?, valor=? WHERE semanalid=?',
						array($boxScoreDataId, $data['boxscoreid'], $data['date'][ $boxScoreDataId ][ $week ], $value, $data['semanalid'][ $boxScoreDataId ][ $week ])
					);
				} else {
					$adb->pquery(
						'INSERT INTO vtiger_box_score_data_semanal(box_score_dataid, boxscoreid, fecha, valor) VALUES(?, ?, ?, ?)',
						array($boxScoreDataId, $data['boxscoreid'], $data['date'][ $boxScoreDataId ][ $week ], $value)
					);
				}
				$values = $this->getWeeklyValueByBoxScoreId($boxScoreDataId, true);
				$normalizedValue = $values ? $values['normalizedvalue'] : '';
				BoxScoreHelper::updateFulfillmentValue($boxScoreDataId, $normalizedValue);
				if($this->escala != 'Week') {
					$adb->pquery(
						'UPDATE vtiger_box_score_data_semanal SET valor=? WHERE MONTH(fecha)=? AND YEAR(fecha)=? AND box_score_dataid=? AND boxscoreid=?',
						array($values['realvalue'], $monthSearch, $year, $boxScoreDataId, $data['boxscoreid'])
					);
				}
			}
		}
	}

}
