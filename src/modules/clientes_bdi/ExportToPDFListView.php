<?php
	if ((!file_exists (__DIR__ . '/../repercusiones_prensa')) || (!is_dir (__DIR__ . '/../repercusiones_prensa'))) {
		echo 'Módulo repercusiones de prensa no está instalado. Notifica al administrador de la aplicación';
		exit ();
	}

	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/clientes_bdi/lib/CustomerUtils.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $adb, $currentModule, $mod_strings;

	if ($_SESSION ['esInstancia'] == true) {
		try {
			StoreUtils::validateInstanceModule ($_SESSION ['platInstancia'], $currentModule);
		} catch (Exception $e) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MENSAJE', $e->getMessage ());
			$smarty->display ('ModuloVencido.tpl');
			exit ();
		}
		if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta!');
			$smarty->display ('instanciaUnverified.tpl');
			exit ();
		}
	}

	$dateField   = PlatzillaUtils::purify ($_GET, 'datefield');
	$customerId  = PlatzillaUtils::purify ($_GET, 'record');
	$from        = PlatzillaUtils::purify ($_GET, 'from');
	$orderBy     = PlatzillaUtils::purify ($_GET, 'orderby');
	$supportType = PlatzillaUtils::purify ($_GET, 'supporttype');
	$to          = PlatzillaUtils::purify ($_GET, 'to');

	$smarty = new vtigerCRM_Smarty ();
	try {
		if (empty ($customerId)) {
			throw new Exception ('No has seleccionado un cliente');
		}

		$customer = CustomerUtils::getCustomerById ($adb, $customerId);
		if (!$customer) {
			throw new Exception ('El ID del cliente suministrado no está registrado');
		}

		$repercussions = null;
		$whereClauses  = array ();
		$arguments     = array ();

		if ((!empty ($from)) && (!empty ($to))) {
			if ($dateField == '1') {
				$whereClauses [] = 'r.fecha BETWEEN ? AND ?';
			} else {
				$whereClauses [] = 'crmer.createdtime BETWEEN ? AND ?';
			}
			$arguments [] = $from;
			$arguments [] = $to;

			if (!empty ($supportType)) {
				$whereClauses [] = 'c.tipo_de_soporte=?';
				$arguments []    = $supportType;
			}
			$whereClauses = count ($whereClauses) > 0 ? join (' AND ', $whereClauses) . ' AND ' : '';

			switch ($orderBy) {
				case 1:
					$orderByClause = 'c.tematica_del_med';
					break;
				case 2:
					$orderByClause = 'c.tematica_del_med, c.tipo_de_soporte';
					break;
				case 3:
					$orderByClause = 'c.tematica_del_med, c.tipo_de_soporte, r.tipo_repercusion';
					break;
				case 4:
					$orderByClause = 'r.fecha DESC';
					break;
				case 5:
					$orderByClause = 'r.fecha ASC';
					break;
				default:
					$orderByClause = null;
					break;
			}
			$orderByClause = !empty ($orderByClause) ? "ORDER BY {$orderByClause}" : '';

			$result = $adb->pquery (
				"SELECT
					r.repercusiones_prensaid,
					r.titular,
					r.fecha,
					r.superficie,
					m.nombre_de_la_entidad AS medio,
					crmer.createdtime
				FROM
					vtiger_repercusiones_prensa r
					INNER JOIN vtiger_crmentity crmer ON crmer.crmid=r.repercusiones_prensaid
					INNER JOIN vtiger_clientes_bdi c ON c.clientes_bdiid=r.relacionado_con
					LEFT JOIN vtiger_medios_bdi m ON m.medios_bdiid=r.medio_donde_apar
				WHERE
					{$whereClauses}
					c.clientes_bdiid=? AND
					crmer.deleted=0
				{$orderByClause}",
				array_merge ($arguments, array ($customerId))
			);
			if (($result) && ($adb->num_rows ($result))) {
				$repercussions = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$repercussions [] = $row;
				}
			}
		}

		$supportTypes = array ();
		$result       = $adb->query ('SELECT tipo_de_soporte FROM vtiger_tipo_de_soporte');
		if (($result) && ($adb->num_rows ($result) > 0)) {
			while ($row = $adb->fetchByAssoc ($result)) {
				$supportTypes [] = $row ['tipo_de_soporte'];
			}
		}

		$smarty->assign ('CURRENT_MODULE', $currentModule);
		$smarty->assign ('CUSTOMER', $customer);
		$smarty->assign ('DATE_FIELD', $dateField);
		$smarty->assign ('FROM', $from);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('ORDER_BY', $orderBy);
		$smarty->assign ('RECORD_ID', $customerId);
		$smarty->assign ('REPERCUSSIONS', $repercussions);
		$smarty->assign ('SUPPORT_TYPE', $supportType);
		$smarty->assign ('SUPPORT_TYPES', $supportTypes);
		$smarty->assign ('TO', $to);
		$smarty->display ('modules/clientes_bdi/ExportToPDFListView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', "index.php?module={$currentModule}&action=index");
		$smarty->display ('Message.tpl');
	}
