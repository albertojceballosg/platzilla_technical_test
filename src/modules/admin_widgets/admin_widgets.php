<?php

	class Widgets {
		public function getModules () {
			global $adb;
			$tabs = array ();
			$query = 'SELECT t.* FROM vtiger_tab t JOIN vtiger_entityname e ON (t.name = e.modulename) WHERE isentitytype = 1 AND presence IN (0, 2) AND customized IN (0,1,2) ORDER BY t.tablabel ASC';
			$result = $adb->query ($query);
			while ($row = $adb->fetchByAssoc ($result)) {
				$tabs[] = $row;
			}
			return $tabs;
		}

		public function getEntityTableName ($moduleName) {
			global $adb;
			$query = 'SELECT tablename FROM vtiger_entityname WHERE modulename = ?';
			$result = $adb->pquery ($query, array ($moduleName));
			if ($result) {
				return $adb->query_result ($result, 0, 'tablename');
			}
			return null;
		}

		public function getDescriptionEntity ($moduleName) {
			global $adb;
			$query = 'SELECT fieldname FROM vtiger_entityname WHERE modulename = ?';
			$result = $adb->pquery ($query, array ($moduleName));
			if ($result) {
				return $adb->query_result ($result, 0, 'fieldname');
			}
			return null;
		}

		public function getUiType ($tablename, $fieldOperation) {
			global $adb;
			$query  = 'SELECT uitype FROM vtiger_field WHERE tablename = ? AND columnname = ?';
			$result = $adb->pquery ($query, array ($tablename, $fieldOperation));
			if ($result) {
				return $adb->query_result ($result, 0, 'uitype');
			}
			return null;
		}

		public function getFieldLabel ($columnName) {
			global $adb;
			$query = 'SELECT fieldlabel FROM vtiger_field WHERE columnname = ?';
			$result = $adb->pquery ($query, array ($columnName));
			if ($result) {
				return html_entity_decode ($adb->query_result ($result, 0, 'fieldlabel'), ENT_QUOTES, 'UTF-8');
			}
			return null;
		}

		public function guardarWidget ($param) {
			global $adb;
			$query = 'INSERT INTO vtiger_widgets (fld_module,fieldoperation,operation,fieldgrouping,texto,icono,color,filterNumber,orderFilter,filterField,sqlprimario,estatus,campofecha,tiempofecha,fechadesde,fechahasta) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
			$adb->pquery ($query, array ($param));
		}

		public function getTabId ($moduleName) {
			global $adb;
			$query = 'SELECT tabid FROM vtiger_entityname WHERE modulename = ?';
			$result = $adb->pquery ($query, array ($moduleName));
			if ($result) {
				return $adb->query_result ($result, 0, 'tabid');
			}
			return null;
		}

		public function obtenerTiposDeCalculo () {
			return array (1 => 'Conteo', 2 => 'Suma', 3 => 'Promedio');
		}

		public function getTableName ($columnName, $tabid) {
			global $adb;
			$query  = 'SELECT tablename FROM vtiger_field WHERE columnname = ? AND tabid = ?';
			$result = $adb->pquery ($query, array ($columnName, $tabid));
			if ($result) {
				return $adb->query_result ($result, 0, 'tablename');
			}
			return null;
		}

		public function getIdField ($tablename) {
			global $adb;
			$sql = "SHOW COLUMNS FROM {$tablename}";
			$result = $adb->query ($sql);
			$fields = array ();
			while ($record = $adb->fetchByAssoc ($result)) {
				$fields[] = $record['field'];
			}
			return $fields[0];
		}

		public function getDateBetween ($valorEntreFechas) {
			$fields = array ();
			switch ($valorEntreFechas) {
				case 2:
					// hoy
					$fields['fechaDesde'] = date ('Y-m-d');
					$fields['fechaHasta'] = date ('Y-m-d');
					break;
				case 3:
					// Ultima semana
					$first = strtotime ('last Sunday -7 days');
					$last  = strtotime ('next Saturday -7 days');
					$fields['fechaDesde'] = date ('Y-m-d', $first);
					$fields['fechaHasta'] = date ('Y-m-d', $last);
					break;
				case 4:
					// Semana Actual
					$first                = strtotime ('last Sunday');
					$last                 = strtotime ('next Saturday');
					$fields['fechaDesde'] = date ('Y-m-d', $first);
					$fields['fechaHasta'] = date ('Y-m-d', $last);
					break;
				case 5:
					// Mes anterior
					$mesActual            = date ('m');
					$year                 = date ('Y');
					$ultimoDia            = date ('d', ((mktime (0, 0, 0, $mesActual, 1, $year) - 1)));
					$fields['fechaDesde'] = date ('d-m-Y', mktime (0, 0, 0, ($mesActual - 1), 1, $year));
					$fields['fechaHasta'] = date ('d-m-Y', mktime (0, 0, 0, ($mesActual - 1), $ultimoDia, $year));
					break;
				case 6:
					// Mes actual
					$mesActual            = date ('m');
					$year                 = date ('Y');
					$ultimoDia            = date ('d', ((mktime (0, 0, 0, ($mesActual + 1), 1, $year) - 1)));
					$fields['fechaDesde'] = date ('d-m-Y', mktime (0, 0, 0, $mesActual, 1, $year));
					$fields['fechaHasta'] = date ('d-m-Y', mktime (0, 0, 0, $mesActual, $ultimoDia, $year));
					break;
				case 7:
					// últimos 7 días
					$hoy                  = date ('d');
					$mesActual            = date ('m');
					$year                 = date ('Y');
					$fields['fechaDesde'] = date ('Y-m-d', mktime (0, 0, 0, $mesActual, ($hoy - 7), $year));
					$fields['fechaHasta'] = date ('Y-m-d');
					break;
				default:
					$fields = self::getDateBetweenAux ($valorEntreFechas);
					break;
			}
			return $fields;
		}

		public function getDateBetweenAux ($valorEntreFechas) {
			$fields = array ();
			switch ($valorEntreFechas) {
				case 8:
					// últimos 30 días
					$hoy                  = date ('d');
					$mesActual            = date ('m');
					$year                 = date ('Y');
					$fields['fechaDesde'] = date ('Y-m-d', mktime (0, 0, 0, $mesActual, ($hoy - 30), $year));
					$fields['fechaHasta'] = date ('Y-m-d');
					break;
				case 9:
					// últimos 60 días
					$hoy                  = date ('d');
					$mesActual            = date ('m');
					$year                 = date ('Y');
					$fields['fechaDesde'] = date ('Y-m-d', mktime (0, 0, 0, $mesActual, ($hoy - 60), $year));
					$fields['fechaHasta'] = date ('Y-m-d');
					break;
				case 10:
					// últimos 90 días
					$hoy                  = date ('d');
					$mesActual            = date ('m');
					$year                 = date ('Y');
					$fields['fechaDesde'] = date ('Y-m-d', mktime (0, 0, 0, $mesActual, ($hoy - 90), $year));
					$fields['fechaHasta'] = date ('Y-m-d');
					break;
				case 11:
					// últimos 120 días
					$hoy                  = date ('d');
					$mesActual            = date ('m');
					$year                 = date ('Y');
					$fields['fechaDesde'] = date ('Y-m-d', mktime (0, 0, 0, $mesActual, ($hoy - 120), $year));
					$fields['fechaHasta'] = date ('Y-m-d');
					break;
				default:
					$fields['fechaDesde'] = 'A';
					$fields['fechaHasta'] = 'B';
					break;
			}
			return $fields;
		}

	}

	function construirSqlPrimario ($datos, $tabId) {
		global $Widget;
		$fieldOperation = $datos['fieldoperation'];
		$opColumn       = $datos['operation'];
		$fieldGrouping  = (!empty($datos['fieldgrouping'])) ? $datos['fieldgrouping'] : null;
		$filterField    = $datos['filterfield'];
		$orderF         = $datos['orderfilter'];
		$filterNumber   = $datos['filternumber'];

		// campos de filtro de fecha
		$filterFieldDate = $datos['campofecha'];
		$fechaDesde      = $datos['fechadesde'];
		$fechaHasta      = $datos['fechahasta'];

		$filtroFecha = '';
		if ($filterFieldDate != '') {
			$filterTableAlias = in_array ($filterFieldDate, array ('createdtime', 'modifiedtime')) ? 'crm' : 'tq';
			$filtroFecha      = " AND DATE_FORMAT({$filterTableAlias}.{$filterFieldDate}, '%Y-%m-%d') BETWEEN '{$fechaDesde}' AND '{$fechaHasta}' ";
		}

		$tableName            = $Widget->getTableName ($fieldOperation, $tabId);
		$tableNameId          = $Widget->getIdField ($tableName);
		$uitypeFieldOperation = $Widget->getUiType ($tableName, $fieldOperation);

		$orderFilter    = ordenFiltro ($orderF);
		$baseOperation  = textSqlBase ($opColumn, $fieldGrouping);
		$subqueryFields = "{$baseOperation}, tq.{$fieldOperation}";
		$subqueryGroup  = "GROUP BY tq.{$fieldOperation}";

		if ($uitypeFieldOperation == '7') {
			$filtro = " AND tq.{$fieldOperation} {$orderFilter} {$filterNumber}";
		} else {
			$filtro = " AND tq.{$fieldOperation}='{$filterField}'";
		}

		$join = '';
		if ($fieldGrouping != '') {
			$fieldGroupingAuxiliar = str_replace ('tq.', '', $fieldGrouping);
			$tableAux              = $Widget->getTableName ($fieldGroupingAuxiliar, $tabId);
			$tableNameIdAux        = $Widget->getIdField ($tableAux);
			if ($tableName != $tableAux) {
				$join = "INNER JOIN {$tableAux} tq2 on tq.{$fieldOperation}=tq2.{$tableNameIdAux}";
			}
		}

		$sql = "SELECT
					{$subqueryFields}
				FROM
					{$tableName} tq
					INNER JOIN vtiger_crmentity crm ON crm.crmid=tq.{$tableNameId}
					{$join}
				WHERE
					crm.deleted=0
					{$filtroFecha}
					{$filtro}
				{$subqueryGroup}";
		return trim ($sql);
	}

	function ordenFiltro ($Orden) {
		switch ($Orden) {
			case '1':
				$orderFilter = '>';
				break;
			case '2':
				$orderFilter = '<';
				break;
			default:
				$orderFilter = '=';
				break;
		}
		return $orderFilter;
	}

	function textSqlBase ($opColumn, $fieldGrouping) {
		switch ($opColumn) {
			case 1:
				$BaseOperation = ' count(*) as variablegraficar ';
				break;
			case 2:
				$BaseOperation = ' ROUND( SUM(' . $fieldGrouping . '),2 ) as variablegraficar ';
				break;
			case 3:
				$BaseOperation = ' ROUND( AVG(' . $fieldGrouping . '),2 ) as variablegraficar ';
				break;
			default:
				$BaseOperation = '';
				break;
		}
		return $BaseOperation;
	}
