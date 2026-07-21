<?php
	// Listado de tipos de bases de datos por instancia
	define ('DB_MAIN', 0);  //DB de la plataforma
	define ('DB_DT_SS', 1); //DB del datamart de SixSigma
	define ('DB_WP_EF', 2); //DB del WP de Emprender Facil
	define ('DB_WP_GF', 3); //DB del WP de Gestionar Facil

	function determinarPermisosModuloHijo ($plat, $module, $permiso) {
		global $adb;
		require_once ('include/utils/RelationshipUtils.class.php');
		return RelationshipUtils::canManageChildModule ($adb, $plat, $module, $permiso);
	}

	function conectaPlataformaHija ($name, $bTipo = DB_MAIN, $childUser = '') {
		global $dbconfig;

		if (file_exists ("{$name}/config.inc.php")) {
			require ("{$name}/config.inc.php");
			$databaseName = $dbconfig ['db_name'];
			$userName     = $dbconfig ['db_username'];
			$password     = $dbconfig ['db_password'];
		} else {
			switch ($bTipo) {
				case DB_DT_SS:
					$databaseName = "dt_ss_{$name}";
					break;
				case DB_WP_EF:
					$databaseName = 'testtm_app_wp';
					break;
				case DB_WP_GF:
					$databaseName = 'testtm_app_wp';
					break;
				default:
					$databaseName = "pg_crm_{$name}";
					break;
			}
			$userName = "usr_{$name}";
			$password = md5 ("usr_{$name}");
		}

		$type = $dbconfig ['db_type'];
		$host = $dbconfig ['db_hostname'];

		require_once ('include/database/PearDatabase.php');
		$adb = new PearDatabase ();
		$adb->setDatabaseType ($type);
		$adb->setDatabaseHost ($host);
		$adb->setDatabaseName ($databaseName);
		$adb->setUserName ($userName);
		$adb->setUserPassword ($password);
		$adb->connect (true);

		if (!empty ($childUser)) {
			global $current_user;
			$result = $adb->pquery ('SELECT id FROM vtiger_users WHERE user_name=?', array ($userName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return $adb;
			}

			$row    = $adb->fetchByAssoc ($result, -1, false);
			$userId = $row ['id'];

			$_SESSION ['authenticated_user_id_main'] = $_SESSION ['authenticated_user_id'];
			$_SESSION ['authenticated_user_id']      = $userId;
			$_SESSION ['plat_main']                  = $_SESSION ['plat'];
			$_SESSION ['plat']                       = $name;
			$current_user->id                        = $userId;
		}

		return $adb;
	}
