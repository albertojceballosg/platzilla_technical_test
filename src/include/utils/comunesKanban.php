<?php
	require_once ('include/utils/comunesTareas.php');

	function getAccountConditions ($id) {
		if ((isset ($_REQUEST ["account{$id}"])) && (!empty ($_REQUEST ["account{$id}"]))) {
			return " AND a.accountid={$_REQUEST ["account{$id}"]}";
		} else {
			return '';
		}
	}

	function getUserConditions ($id) {
		if ((isset ($_REQUEST ["user{$id}"])) && (!empty ($_REQUEST ["user{$id}"]))) {
			return " AND u.id={$_REQUEST ["user{$id}"]}";
		} else {
			return '';
		}
	}

	function getDeveloperConditions ($id) {
		if ((isset ($_REQUEST ["vendor{$id}"])) && (!empty ($_REQUEST ["vendor{$id}"]))) {
			return " AND reldesa.idvendor={$_REQUEST ["vendor{$id}"]}";
		} else {
			return '';
		}
	}

	function getTypeConditions ($id) {
		if ((isset ($_REQUEST ["type{$id}"])) && (!empty ($_REQUEST ["type{$id}"]))) {
			return " AND type='" . mysql_real_escape_string ($_REQUEST ["type{$id}"]) . "'";
		} else {
			return '';
		}
	}

	function getListQueryPanel ($id, $condicionAdicional, $order = 'ASC') {
		global $current_user;

		$customerConditions = '';

		$cuentaQuery = getAccountConditions ($id);
		$userQuery   = getUserConditions ($id);
		$desaQuery   = getDeveloperConditions ($id);
		$tipoQuery   = getTypeConditions ($id);

		if (existeCampoTabla ('razon', 'vtiger_troubletickets')) {
			$razonField = 'razon';
		} else {
			$razonField = 'NULL';
		}

		if (existeCampoTabla ('texto_val_coordinador', 'vtiger_troubletickets')) {
			$textoValCoordinadorField = 'texto_val_coordinador';
		} else {
			$textoValCoordinadorField = 'NULL';
		}

		$sql = "SELECT DISTINCT(vtiger_troubletickets.ticketid), end_estimated_date as estimada, start_date, vtiger_troubletickets.title, accountname,
			user_name, createdtime, vtiger_troubletickets.status, type, vtiger_troubletickets.more_info, vtiger_troubletickets.ticket_no,
			$textoValCoordinadorField,$razonField
			FROM vtiger_troubletickets
				INNER JOIN vtiger_crmentity on (vtiger_crmentity.crmid=vtiger_troubletickets.ticketid AND vtiger_crmentity.deleted = 0)
				LEFT JOIN vtiger_users u on id=smownerid
				LEFT JOIN vtiger_ticketcf cft on cft.ticketid=vtiger_troubletickets.ticketid
				LEFT JOIN vtiger_reldesa reldesa on (vtiger_troubletickets.ticketid=reldesa.idticket)
			WHERE
				vtiger_crmentity.deleted=0
				$cuentaQuery
				$userQuery
				$desaQuery
				$tipoQuery
				$customerConditions
			AND $condicionAdicional
			ORDER BY createdtime $order";

		return $sql;
	}

	function obtieneTiposModulo ($module) {
		global $adb;

		$value  = obtenerValorVariable ('field_type', $module);
		$values = explode (',', $value);

		if (is_array ($values)) {
			return $values;
		} else {
			$tabid     = getTabid ($module);
			$fieldinfo = getFieldInformation ($tabid, $value);

			$sql    = "SELECT DISTINCT " . $value . " FROM vtiger_" . $fieldinfo['tablename'];
			$result = $adb->query ($sql);

			while ($row = $adb->fetch_array ($result)) {
				$lst[] = $row;
			}
		}
		if (isset ($lst)) {
			foreach ($lst as $key => $value) {
				# code...
				echo "TT $key = $value <br>";
			}
			return $lst;
		} else {
			return null;
		}
	}

	function obtieneTiposModuloKanbanTT ($module) {
		global $adb;

		$value  = obtenerValorVariable ('status_todotasks', $module);
		$values = explode (',', $value);

		if (is_array ($values)) {
			return $values;
		} else {
			$tabid     = getTabid ($module);
			$fieldinfo = getFieldInformation ($tabid, $value);

			$sql    = "SELECT DISTINCT " . $value . " FROM vtiger_" . $fieldinfo['tablename'];
			$result = $adb->query ($sql);

			while ($row = $adb->fetch_array ($result)) {
				$lst[] = $row;
			}
		}

		if (isset ($lst)) {
			foreach ($lst as $key => $value) {
				# code...
				echo "TT $key = $value <br>";
			}
			return $lst;
		} else {
			return null;
		}
	}

	function obtieneRegistrosSegunTipo_old ($module, $tipos) {
		global $adb;
		for ($i = 0; $i < count ($tipos); $i++) {
			switch ($module) {
				case "HelpDesk":
					$sql = "SELECT vtiger_troubletickets.title, vtiger_vendor.color, vtiger_ordentrabajo.ordentrabajoid
					FROM vtiger_ordentrabajo INNER JOIN vtiger_crmentity
					ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
					INNER JOIN vtiger_troubletickets
					ON (vtiger_ordentrabajo.ticketid = vtiger_troubletickets.ticketid)
					INNER JOIN vtiger_crmentity AS crm2
					ON (vtiger_troubletickets.ticketid = crm2.crmid AND crm2.deleted = 0)
					INNER JOIN vtiger_users
					ON (vtiger_users.id = vtiger_crmentity.smownerid)
					INNER JOIN vtiger_vendor
					ON (vtiger_vendor.user_id = vtiger_users.id)
					WHERE vtiger_troubletickets.status = ?";

					if (isset($_REQUEST['proyectosid']) && !empty($_REQUEST['proyectosid'])) {
						$sql .= " AND vtiger_troubletickets.proyectoid = " . $_REQUEST['proyectosid'];
					}
					if (isset($_REQUEST['vendor']) && !empty($_REQUEST['vendor'])) {
						$sql .= " AND vtiger_vendor.vendorid = " . $_REQUEST['vendor'];
					}
					if (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) {
						$sql .= " AND vtiger_troubletickets.type = '" . $_REQUEST['type'] . "'";
					}
					if (isset($_REQUEST['account']) && !empty($_REQUEST['account'])) {
						$sql .= " AND vtiger_troubletickets.parent_id = " . $_REQUEST['account'];
					}
					break;
			}
			if (isset ($sql)) {
				$result = $adb->pquery ($sql, array ($tipos[ $i ]));
				while ($row = $adb->fetchByAssoc ($result)) {
					$registros[ $tipos[ $i ] ][] = $row;
				}
			}
		}
		return isset ($registros) ? $registros : null;
	}

	function obtieneRegistrosSegunTareas ($tipos) {
		global $adb;
		foreach ($tipos as $k => $tipo) {
			$addsql = "vtiger_troubletickets.status='" . $tipo . "'";
			if (isset($_REQUEST['proyectosid']) && !empty($_REQUEST['proyectosid'])) {
				$addsql .= " AND vtiger_troubletickets.proyectoid = " . $_REQUEST['proyectosid'];
			}
			if (isset($_REQUEST['vendor']) && !empty($_REQUEST['vendor'])) {
				$addsql .= " AND vtiger_vendor.vendorid = " . $_REQUEST['vendor'];
			}
			if (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) {
				$addsql .= " AND vtiger_troubletickets.type = '" . $_REQUEST['type'] . "'";
			}
			if (isset($_REQUEST['account']) && !empty($_REQUEST['account'])) {
				$addsql .= " AND vtiger_troubletickets.parent_id = " . $_REQUEST['account'];
			}

			$sql    = getListQueryPanel ($k, $addsql);
			$result = $adb->query ($sql);
			while ($row = $adb->fetchByAssoc ($result)) {
				$registros[ $k ][] = $row;
			}
		}
		return isset ($registros) ? $registros : null;
	}

	function obtieneRegistrosSegunTareasTT ($tipos, $filtro) {
		global $adb;
		foreach ($tipos as $k => $tipo) {
			$addsql = " and tt.status_todotasks='" . $tipo . "'";

			$sql = getListQueryPanelTT ($k, $addsql, $filtro);

			$result = $adb->query ($sql);
			while ($row = $adb->fetchByAssoc ($result)) {
				$registros[ $k ][] = $row;
			}
		}
		return isset ($registros) ? $registros : null;
	}

	function getListQueryPanelTT ($id, $condicionAdicional, $filtro) {
		$queryFiltro = "";
		$joinAccount = "";

		if (isset($filtro['fecha_desde']) and $filtro['fecha_desde'] != '') {
			$fecha_desde = explode ('-', $filtro['fecha_desde']);
			$fecha_desde = $fecha_desde[2] . '-' . $fecha_desde[1] . '-' . $fecha_desde[0];
			$queryFiltro .= " AND date_start >= '" . $fecha_desde . "'";
		}

		if (isset($filtro['fecha_hasta']) and $filtro['fecha_hasta'] != '') {
			$fecha_hasta = explode ('-', $filtro['fecha_hasta']);
			$fecha_hasta = $fecha_hasta[2] . '-' . $fecha_hasta[1] . '-' . $fecha_hasta[0];
			$queryFiltro .= " AND date_start <= '" . $fecha_hasta . "'";
		}

		if (isset($filtro['proyectosid']) and $filtro['proyectosid'] != '') {
			$queryFiltro .= " AND h.proyectosid = " . $filtro['proyectosid'];
		}
		if (isset($filtro['account']) and $filtro['account'] != '') {
			$queryFiltro .= " AND p.accountid = " . $filtro['account'];
			$joinAccount = " JOIN vtiger_proyectos p on (p.proyectosid = h.proyectosid) ";
		}

		$sql = "select h.hitoid,h.cod_hito,h.name , tt.*

		from vtiger_hito h join
		`vtiger_crmentityrel` crmer on (crmer.crmid = h.hitoid)
		join vtiger_todotasks tt on (crmer.relcrmid = tt.todotasksid)
		join vtiger_crmentity crmh on (crmh.crmid = h.hitoid)
		join vtiger_crmentity crmtt on (crmtt.crmid = tt.todotasksid)
		$joinAccount
		WHERE crmer.relmodule = 'todotasks' and crmer.module = 'hito'
		and crmh.deleted = 0 and crmtt.deleted = 0
		$condicionAdicional $queryFiltro ";

		return $sql;
	}

	function obtieneRegistrosSegunTareasProyectos ($tipos, $proyectosid) {
		global $adb;
		foreach ($tipos as $k => $tipo) {
			$addsql = "vtiger_troubletickets.status='" . $tipo . "'";
			if (isset($proyectosid)) {
				$addsql .= " AND vtiger_troubletickets.proyectoid = " . $proyectosid;
			}

			$sql    = getListQueryPanel ($k, $addsql);
			$result = $adb->query ($sql);
			while ($row = $adb->fetchByAssoc ($result)) {
				$registros[ $k ][] = $row;
			}
		}
		return isset ($registros) ? $registros : null;
	}

	function obtieneTodosRegistros ($tipos) {
		global $adb;
		foreach ($tipos as $k => $tipo) {
			$addsql = "vtiger_troubletickets.status='" . $tipo . "'";
			if (isset($_REQUEST['proyectosid']) && !empty($_REQUEST['proyectosid'])) {
				$addsql .= " AND vtiger_troubletickets.proyectoid = " . $_REQUEST['proyectosid'];
			}
			if (isset($_REQUEST['vendor']) && !empty($_REQUEST['vendor'])) {
				$addsql .= " AND vtiger_vendor.vendorid = " . $_REQUEST['vendor'];
			}
			if (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) {
				$addsql .= " AND vtiger_troubletickets.type = '" . $_REQUEST['type'] . "'";
			}
			if (isset($_REQUEST['account']) && !empty($_REQUEST['account'])) {
				$addsql .= " AND vtiger_troubletickets.parent_id = " . $_REQUEST['account'];
			}

			$sql    = getListQueryPanel ($k, $addsql, 'desc');
			$result = $adb->query ($sql);
			while ($row = $adb->fetchByAssoc ($result)) {
				$registros[] = $row;
			}
		}
		return isset ($registros) ? $registros : null;
	}

	function obtieneTodosRegistrosTT ($tipos, $filtro) {
		global $adb;
		foreach ($tipos as $k => $tipo) {

			$addsql = " and tt.status_todotasks='" . $tipo . "'";

			$sql    = getListQueryPanelTT ($k, $addsql, $filtro);
			$result = $adb->query ($sql);
			while ($row = $adb->fetchByAssoc ($result)) {
				$registros[] = $row;
			}
		}
		return isset ($registros) ? $registros : null;
	}
