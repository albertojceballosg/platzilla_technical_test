<?php
	error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
	ini_set ('display_errors', 1);
	set_time_limit (0);

	require_once ('data/CRMEntity.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/InstanceCreatorTableNamesProvider.class.php');
	require_once ('include/utils/InstanceUtils.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('log4php/LoggerManager.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/Settings/lib/ConfigApplicationsHelper.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	require_once ('vtlib/Vtiger/Module.php');

	/**
	 * Class InstanceCreator
	 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
	 */
	class InstanceCreator {
		const REGISTRATION_SOURCE_PRICING    = 1;
		const REGISTRATION_SOURCE_INVITATION = 2;
		private static $ACTION_CREATE_STOCK_INSTANCE = 1;
		private static $ACTION_ASSIGN_STOCK_INSTANCE = 2;
		private static $CREATOR                      = null;
		private        $action                       = null;
		private        $actionFullfilled             = false;
		private        $instanceCode                 = null;
		private        $log                          = null;
		private        $temporaryFolderPath          = null;

		public function __construct (Logger $log = null) {
			$this->log = $log;
			global $current_user;
			require ('config.inc.php');
			if ($current_user) {
				return;
			}
			/** @var Users $current_user */
			$current_user = CRMEntity::getInstance ('Users');
			$current_user->retrieve_entity_info (1, 'Users');
		}

		public function __destruct () {
			if (($this->temporaryFolderPath) && (is_dir ($this->temporaryFolderPath)) && (is_writable ($this->temporaryFolderPath))) {
				PlatzillaUtils::deleteFolder ($this->temporaryFolderPath);
			}
			if ($this->actionFullfilled) {
				return;
			}
			if ($this->action == self::$ACTION_CREATE_STOCK_INSTANCE) {
				$this->cleanOnStockInstanceCreationError ();
			} else if ($this->action == self::$ACTION_ASSIGN_STOCK_INSTANCE) {
				$this->cleanOnStockInstanceAssignmentError ();
			}
		}

		private function validateQueryResult ($result, $onErrorMessage) {
			if (!$result) {
				throw new Exception ($onErrorMessage);
			}
		}

		private function startSession () {
			global $platPrincipal;
			if (!session_id ()) {
				session_start ();
			}
			if (!isset ($_SESSION ['plat'])) {
				$_SESSION ['plat'] = $platPrincipal;
			}
		}

		private function generateRandomCode () {
			$code    = '';
			$pattern = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$max     = (strlen ($pattern) - 1);
			for ($i = 0; $i < 6; $i++) {
				$code .= $pattern[mt_rand (0, $max)];
			}
			return $code;
		}

		// Stock instance creation methods

		/**
		 * @throws Exception
		 */
		private function createInstanceDatabase () {
			global $dbconfig;

			$instanceDatabaseName = "pg_crm_{$this->instanceCode}";
			$instanceUserHostName = $dbconfig ['db_serverForNewUsers'];
			$instanceUserName     = "usr_{$this->instanceCode}";
			$instanceUserPassword = md5 ($instanceUserName);

			$e      = null;
			$mysqli = null;
			try {
				$mysqli = new mysqli ();
				$mysqli->connect ($dbconfig ['db_serverForNewDB'], $dbconfig ['db_username'], $dbconfig ['db_password']);
				if ($mysqli->connect_errno) {
					throw new Exception ("Imposible conectarse al servidor {$dbconfig ['db_serverForNewDB']}");
				}

				// Eliminar base de datos
				$sql    = "DROP DATABASE IF EXISTS $instanceDatabaseName";
				$result = $mysqli->query ($sql);
				$this->validateQueryResult (
					$result,
					"Imposible eliminar la base de datos {$instanceDatabaseName}. Valida que el usuario {$dbconfig ['db_username']} tiene derechos de eliminar bases de datos en el servidor {$dbconfig ['db_serverForNewDB']}"
				);

				// Eliminar usuario
				$sql    = "SELECT u.User FROM mysql.user u WHERE u.User='$instanceUserName' AND u.Host='$instanceUserHostName'";
				$result = $mysqli->query ($sql);
				if (($result) && ($result->num_rows > 0)) {
					$result->close ();
					$sql    = "DROP USER '$instanceUserName'@'$instanceUserHostName'";
					$result = $mysqli->query ($sql);
					$this->validateQueryResult (
						$result,
						"Imposible eliminar el usuario '{$instanceUserName}'@'{$instanceUserHostName}'. Valida que el usuario {$dbconfig ['db_username']} tiene derechos de eliminar usuarios en el servidor {$dbconfig ['db_serverForNewDB']}"
					);
				}

				// Crear base de datos
				$sql    = "CREATE DATABASE IF NOT EXISTS {$instanceDatabaseName} DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci";
				$result = $mysqli->query ($sql);
				$this->validateQueryResult (
					$result,
					"Imposible crear la base de datos {$instanceDatabaseName}. Valida que el usuario {$dbconfig ['db_username']} tiene derechos de crear bases de datos en el servidor {$dbconfig ['db_serverForNewDB']}"
				);

				// Crear usuario
				$sql    = "CREATE USER '$instanceUserName'@'$instanceUserHostName' IDENTIFIED BY '$instanceUserPassword'";
				$result = $mysqli->query ($sql);
				$this->validateQueryResult (
					$result,
					"Imposible crear el usuario '{$instanceUserName}'@'{$instanceUserHostName}'. Valida que el usuario {$dbconfig ['db_username']} tiene derechos de crear usuarios en el servidor {$dbconfig ['db_serverForNewDB']}"
				);
				$sql    = "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, CREATE VIEW, EVENT, TRIGGER, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON {$instanceDatabaseName}.* TO  '$instanceUserName'@'$instanceUserHostName'";
				$result = $mysqli->query ($sql);
				$this->validateQueryResult (
					$result,
					"Imposible asignar permisos al usuario '{$instanceUserName}'@'{$instanceUserHostName}'. Valida que el usuario {$dbconfig ['db_username']} tiene derechos de asignar permisos en el servidor {$dbconfig ['db_serverForNewDB']}"
				);
			} catch (Exception $ie) {
				$e = $ie;
			}
			if ($mysqli) {
				$mysqli->close ();
			}
			if ($e) {
				throw $e;
			}
		}

		private function createInstanceTable (PearDatabase $adb, $platformDatabaseName, $instanceDatabaseName, $tableName) {
			$sql    = "SHOW CREATE TABLE {$platformDatabaseName}.$tableName";
			$result = $adb->query ($sql, true);
			if ($adb->num_rows ($result) == 0) {
				return false;
			}
			$row = $adb->fetch_array ($result, false);

			$sql = str_replace ("`$tableName`", "`$instanceDatabaseName`.`$tableName`", $row [1]);
			$adb->query ($sql, true);
			return true;
		}

		private function createInstanceBaseTables () {
			global $platPrincipal;
			$tableNames           = InstanceCreatorTableNamesProvider::getBaseTableNames ();
			$adb                  = AdbManager::getInstance ()->getMasterAdb ();
			$platformDatabaseName = "pg_crm_$platPrincipal";
			$instanceDatabaseName = "pg_crm_{$this->instanceCode}";
			foreach ($tableNames as $tableName) {
				$this->createInstanceTable ($adb, $platformDatabaseName, $instanceDatabaseName, $tableName);
			}
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_org_share_action_mapping SELECT * FROM {$platformDatabaseName}.vtiger_org_share_action_mapping", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_datashare_module_rel_seq (id) VALUES (0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_datashare_relatedmodules_seq (id) VALUES (0)", true);
		}

		private function createInstanceCatalogTables () {
			$tableNames = InstanceCreatorTableNamesProvider::getCatalogTableNames ();

			global $platPrincipal;
			$adb                  = AdbManager::getInstance ()->getMasterAdb ();
			$platformDatabaseName = "pg_crm_$platPrincipal";
			$instanceDatabaseName = "pg_crm_{$this->instanceCode}";
			foreach ($tableNames as $tableName) {
				if ($this->createInstanceTable ($adb, $platformDatabaseName, $instanceDatabaseName, $tableName)) {
					$adb->query ("INSERT INTO {$instanceDatabaseName}.$tableName SELECT * FROM {$platformDatabaseName}.$tableName", true);
				}
			}
		}

		private function createInstanceCoreTables () {
			global $platPrincipal;
			$tableNames           = InstanceCreatorTableNamesProvider::getCoreTableNames ();
			$adb                  = AdbManager::getInstance ()->getMasterAdb ();
			$platformDatabaseName = "pg_crm_$platPrincipal";
			$instanceDatabaseName = "pg_crm_{$this->instanceCode}";
			foreach ($tableNames as $tableName) {
				$this->createInstanceTable ($adb, $platformDatabaseName, $instanceDatabaseName, $tableName);
			}
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_parenttab SELECT * FROM {$platformDatabaseName}.vtiger_parenttab", true);
		}

		private function createInstanceProfileTables () {
			global $platPrincipal;
			$tableNames           = InstanceCreatorTableNamesProvider::getProfileTableNames ();
			$adb                  = AdbManager::getInstance ()->getMasterAdb ();
			$platformDatabaseName = "pg_crm_$platPrincipal";
			$instanceDatabaseName = "pg_crm_{$this->instanceCode}";
			foreach ($tableNames as $tableName) {
				$this->createInstanceTable ($adb, $platformDatabaseName, $instanceDatabaseName, $tableName);
			}
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_profile (profileid, profilename, description) VALUES (1, 'Administrator', 'Admin Profile')", true);
		}

		private function createInstanceSecurityTables () {
			global $platPrincipal;
			$tableNames           = InstanceCreatorTableNamesProvider::getSecurityTableNames ();
			$adb                  = AdbManager::getInstance ()->getMasterAdb ();
			$platformDatabaseName = "pg_crm_$platPrincipal";
			$instanceDatabaseName = "pg_crm_{$this->instanceCode}";
			foreach ($tableNames as $tableName) {
				$this->createInstanceTable ($adb, $platformDatabaseName, $instanceDatabaseName, $tableName);
			}
			$adb->query ("ALTER TABLE {$instanceDatabaseName}.vtiger_users AUTO_INCREMENT=1", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_role (roleid, rolename, parentrole, depth, iscustomer, ispartner, default_module) VALUES ('H1', 'Organización', 'H1', 0, 0, NULL, NULL)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_role (roleid, rolename, parentrole, depth, iscustomer, ispartner, default_module) VALUES ('H2', 'Director', 'H1::H2', 1, 0, NULL, NULL)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_role (roleid, rolename, parentrole, depth, iscustomer, ispartner, default_module) VALUES ('H3', 'Responsable', 'H1::H2::H3', 2, 0, NULL, NULL)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_role (roleid, rolename, parentrole, depth, iscustomer, ispartner, default_module) VALUES ('H4', 'Técnico', 'H1::H2::H3::H4', 3, 0, NULL, NULL)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_role_seq (id) VALUES (4)", true);
		}

		private function createInstanceSequenceTables () {
			global $platPrincipal;
			$tableNames           = InstanceCreatorTableNamesProvider::getSequenceTableNames ();
			$adb                  = AdbManager::getInstance ()->getMasterAdb ();
			$platformDatabaseName = "pg_crm_$platPrincipal";
			$instanceDatabaseName = "pg_crm_{$this->instanceCode}";
			foreach ($tableNames as $tableName) {
				if (!$this->createInstanceTable ($adb, $platformDatabaseName, $instanceDatabaseName, $tableName)) {
					continue;
				}
				$adb->query ("INSERT INTO {$instanceDatabaseName}.$tableName (id) VALUES (1)", true);
			}
		}

		private function createInstanceSettingsTables () {
			global $platPrincipal;
			$tableNames           = InstanceCreatorTableNamesProvider::getSettingsTableNames ();
			$adb                  = AdbManager::getInstance ()->getMasterAdb ();
			$platformDatabaseName = "pg_crm_$platPrincipal";
			$instanceDatabaseName = "pg_crm_{$this->instanceCode}";
			foreach ($tableNames as $tableName) {
				$this->createInstanceTable ($adb, $platformDatabaseName, $instanceDatabaseName, $tableName);
			}

			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_blocks (blockid, label, sequence) VALUES (1, 'LBL_GENERAL_SETTINGS', 1)", true);
			$adb->query ("UPDATE {$instanceDatabaseName}.vtiger_settings_blocks_seq SET id=1", true);

			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (1, 1, 'LBL_PROFILES', 'fa fa-key yellow-bg', 'LBL_PROFILE_DESCRIPTION', 'index.php?module=Settings&action=ListProfiles&parenttab=Settings', 1, 1)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (2, 1, 'LBL_ROLES', 'fa fa-sort-amount-asc green-bg', 'LBL_ROLE_DESCRIPTION', 'index.php?module=Settings&action=listroles&parenttab=Settings', 2, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (3, 1, 'LBL_USERS', 'fa fa-user red-bg', 'LBL_USER_DESCRIPTION', 'index.php?module=panelusuarios&action=index&parenttab=Settings', 3, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (4, 1, 'USERGROUPLIST', 'fa fa-users purple-bg', 'LBL_GROUP_DESCRIPTION', 'index.php?module=Settings&action=listgroups&parenttab=Settings', 4, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (5, 1, 'LBL_TAX_SETTINGS', 'fa fa-legal emerald-bg', 'LBL_TAX_DESCRIPTION', 'index.php?module=Settings&action=TaxConfig&parenttab=Settings', 5, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (6, 1, 'LBL_CURRENCY_SETTINGS', 'fa fa-money green-bg', 'LBL_CURRENCY_DESCRIPTION', 'index.php?module=Settings&action=CurrencyListView&parenttab=Settings', 6, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (7, 1, 'LBL_CUSTOMIZE_MODENT_NUMBER', 'fa fa-sort-numeric-asc red-bg', 'LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION', 'index.php?module=Settings&action=CustomModEntityNo&parenttab=Settings', 7, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (8, 1, 'LBL_CONFIG_FORMAT_REPORT', 'fa  fa-file-pdf-o purple-bg', 'LBL_CONFIG_FORMAT_REPORT_DESCRIPTION', 'index.php?module=reportmanager&action=index&parenttab=Settings', 8, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (9, 1, 'LBL_EMAIL_MANAGER', 'fa fa-envelope-o emerald-bg', 'LBL_EMAIL_MANAGER_DESCRIPTION', 'index.php?module=emailmanager&action=index&parenttab=Settings', 9, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (10, 1, 'LBL_FIELDS_ACCESS', 'fa fa-tasks yellow-bg', 'LBL_SHARING_FIELDS_DESCRIPTION', 'index.php?module=Settings&action=DefaultFieldPermissions&parenttab=Settings', 10, 1)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (11, 1, 'LBL_EDIT_LABELS', 'fa fa-language yellow-bg', 'LBL_EDIT_LABELS_DESCRIPTION', 'index.php?module=Settings&action=editLabels&parenttab=Settings', 11, 1)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (12, 1, 'LBL_WIDGETS', 'fa fa-cube green-bg', 'LBL_WIDGETS_DESCRIPTION', 'index.php?module=admin_widgets&action=index', 12, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (13, 1, 'LBL_CALENDAR_VIEW', 'fa fa-calendar red-bg', 'LBL_CALENDAR_VIEW_DESCRIPTION', 'index.php?module=Settings&action=CalendarViewListView&parenttab=Settings', 13, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (14, 1, 'LBL_LOGIN_HISTORY_DETAILS', 'fa fa-history emerald-bg', 'LBL_LOGIN_HISTORY_DESCRIPTION', 'index.php?module=Settings&action=ListLoginHistory&parenttab=Settings', 14, 1)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (15, 1, 'LBL_AUDIT_TRAIL', 'fa fa-eye red-bg', 'LBL_AUDIT_DESCRIPTION', 'index.php?module=Settings&action=AuditTrailList&parenttab=Settings', 15, 1)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence, active) VALUES (16, 1, 'LBL_SHARING_ACCESS', 'fa fa-lock emerald-bg', 'LBL_SHARING_ACCESS_DESCRIPTION', 'index.php?module=Settings&action=OrgSharingDetailView&parenttab=Settings', 16, 0)", true);
			$adb->query ("UPDATE {$instanceDatabaseName}.vtiger_settings_field_seq SET id=16", true);

			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_currency_info (currency_name, currency_code, currency_symbol, conversion_rate, currency_status, defaultid, deleted) VALUES ('Euro', 'EUR', '€', 1.000, 'Active', '-11', 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_currency_info_seq (id) VALUES (1)", true);

			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_inventorytaxinfo (taxid, taxname, taxlabel, percentage, deleted) VALUES (1, 'tax1', 'IVA', 21.000, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_inventorytaxinfo_seq (id) VALUES (1)", true);

			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_shippingtaxinfo (taxid, taxname, taxlabel, percentage, deleted) VALUES (1, 'shtax1', 'IVA', 21.000, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_shippingtaxinfo_seq (id) VALUES (1)", true);
		}

		private function getInstanceGraphApplicationIds ($instanceCode, $roles) {
			if (empty ($roles)) {
				return null;
			}

			$adb              = AdbManager::getInstance ()->getMasterAdb ();
			$roles            = explode ('#', $roles);
			$applicationCodes = array ();
			foreach ($roles as $applicationId) {
				$result = $adb->pquery ('SELECT app_code FROM vtiger_config_applications WHERE config_applicationsid=?', array ($applicationId));
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					$applicationCodes [] = $applicationId;
				} else {
					$row                 = $adb->fetchByAssoc ($result, -1, false);
					$applicationCodes [] = $row ['app_code'];
				}
			}

			if (empty ($applicationCodes)) {
				return null;
			}

			$adb            = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
			$applicationIds = array ();
			foreach ($applicationCodes as $applicationCode) {
				$result = $adb->pquery ('SELECT config_applicationsid FROM vtiger_config_applications WHERE app_code=?', array ($applicationCode));
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					$applicationIds [] = $applicationCode;
				} else {
					$row               = $adb->fetchByAssoc ($result, -1, false);
					$applicationIds [] = $row ['config_applicationsid'];
				}
			}

			return $applicationIds;
		}

		private function getNewInstanceCode () {
			$adb = AdbManager::getInstance ()->getMasterAdb ();
			$adb->startTransaction ();
			$result = $adb->pquery ('SELECT * FROM vtiger_variables_instancias WHERE varname IN (?, ?)', array ('prefixinstances', 'codeseq'), true);
			if ($adb->num_rows ($result) != 2) {
				throw new Exception ('Se ha presentado un error interno. Contacta al administrador de la aplicación');
			}
			$prefix   = null;
			$sequence = null;
			while ($row = $adb->fetch_array ($result)) {
				if ($row ['varname'] == 'prefixinstances') {
					$prefix = $row ['varvalue'];
				} else {
					$sequence = $row ['varvalue'];
					continue;
				}
			}
			$adb->pquery ('UPDATE vtiger_variables_instancias SET varvalue=varvalue+1 WHERE varname=?', array ('codeseq'), true);
			$adb->completeTransaction ();
			return "{$prefix}{$sequence}";
		}

		private function getSourceModuleNames () {
			$unnecesaryModuleNames = array ('backgroundtasks', 'graficosgenerales', 'ConfigEditor', 'Integration', 'instancias', 'instancias_admin', 'store');
			$questionMarks         = str_repeat ('?, ', count ($unnecesaryModuleNames) - 1) . '?';
			$adb                   = AdbManager::getInstance ()->getMasterAdb ();
			$result                = $adb->pquery (
				"SELECT t.name FROM vtiger_tab t WHERE (t.name IS NOT NULL) AND (TRIM(t.name)<>'') AND t.name NOT IN ({$questionMarks}) ORDER BY t.tabid",
				$unnecesaryModuleNames,
				true
			);
			if ($adb->num_rows ($result) == 0) {
				return null;
			}
			$sourceModuleNames = array ('backgroundtasks', 'graficosgenerales');
			while ($row = $adb->fetch_array ($result)) {
				$sourceModuleNames [] = $row ['name'];
			}
			return $sourceModuleNames;
		}

		private function fixStockInstanceConfiguration () {
			global $platPrincipal;
			$adb                  = AdbManager::getInstance ()->getMasterAdb ();
			$platformDatabaseName = "pg_crm_$platPrincipal";
			$instanceDatabaseName = "pg_crm_{$this->instanceCode}";
			$adb->query ("UPDATE {$instanceDatabaseName}.vtiger_tab it INNER JOIN {$platformDatabaseName}.vtiger_tab mt ON mt.name=it.name SET it.customized=mt.customized, it.parent=mt.parent, it.tabsequence=mt.tabsequence, it.presence=-1");
			$adb->query ("UPDATE {$instanceDatabaseName}.vtiger_tab it INNER JOIN {$platformDatabaseName}.vtiger_tab mt ON mt.name=it.name SET it.presence=mt.presence WHERE mt.customized IN (0, 2) AND mt.name NOT IN ('Accounts', 'Contacts', 'Services', 'Pricebooks', 'myinvoice')");
			$adb->query ("ALTER TABLE {$instanceDatabaseName}.vtiger_config_applications AUTO_INCREMENT=1", true);
			$adb->query ("ALTER TABLE {$instanceDatabaseName}.vtiger_configapps_tab AUTO_INCREMENT=1", true);

			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($this->instanceCode);

			$adb->query ("DELETE FROM vtiger_settings_field WHERE name IN ('ModTracker', 'LBL_TOOLTIP_MANAGEMENT')");
			$adb->query ('UPDATE vtiger_settings_field_seq SET id=(SELECT MAX(fieldid) FROM vtiger_settings_field)');

			$adb->query ("INSERT INTO vtiger_entityname SELECT t.tabid, t.name, 'vtiger_contactdetails', 'firstname,lastname', 'contactid', 'contact_id' FROM vtiger_tab t WHERE t.name='Contacts'", true);
			$adb->query ("INSERT INTO vtiger_entityname SELECT t.tabid, t.name, 'vtiger_leaddetails', 'firstname,lastname', 'leadid', 'leadid' FROM vtiger_tab t WHERE t.name='Leads'", true);
			$adb->query ("INSERT INTO vtiger_entityname SELECT t.tabid, t.name, 'vtiger_notes', 'title', 'notesid', 'notesid' FROM vtiger_tab t WHERE t.name='Documents'", true);
			$adb->query ("INSERT INTO vtiger_entityname SELECT t.tabid, t.name, 'vtiger_troubletickets', 'title', 'ticketid', 'ticketid' FROM vtiger_tab t WHERE t.name='HelpDesk'", true);
			$adb->query ("INSERT INTO vtiger_entityname SELECT t.tabid, t.name, 'vtiger_users', 'first_name,last_name', 'id', 'id' FROM vtiger_tab t WHERE t.name='Users'", true);

			$adb->pquery ('UPDATE vtiger_customview SET clientview=1 WHERE viewname=? AND entitytype=?', array ('Contacto', 'Contacts'), true);
			$adb->pquery ('UPDATE vtiger_customview SET setdefault=0 WHERE viewname=? AND entitytype=? AND setdefault=1', array ('Cuentas', 'Accounts'), true);
			$adb->pquery ('UPDATE vtiger_customview SET clientview=1, setdefault=1 WHERE viewname=? AND entitytype=?', array ('Cuentas', 'Accounts'), true);

			$adb->pquery ('UPDATE vtiger_tab SET presence=-1 WHERE name IN (?, ?)', array ('alertas', 'mod_kanboard'), true);

			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_role2picklist (roleid, picklistvalueid, picklistid, sortid) SELECT 'H3', picklistvalueid, picklistid, sortid FROM {$instanceDatabaseName}.vtiger_role2picklist WHERE roleid='H2'", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_role2picklist (roleid, picklistvalueid, picklistid, sortid) SELECT 'H4', picklistvalueid, picklistid, sortid FROM {$instanceDatabaseName}.vtiger_role2picklist WHERE roleid='H2'", true);
		}

		private function logMessage ($type, $message) {
			if (empty ($this->log)) {
				echo $message . PHP_EOL;
			} else {
				$this->log->emit ($type, $message);
			}
		}

		private function registerStockInstance () {
			global $currentModule;
			$oldCurrentModule = $currentModule;

			$currentModule = 'instancias';
			/** @var instancias|stdClass $instance */
			$instance                                     = CRMEntity::getInstance ('instancias');
			$instance->column_fields ['code']             = $this->instanceCode;
			$instance->column_fields ['domain']           = $this->instanceCode;
			$instance->column_fields ['status']           = 'unassigned';
			$instance->column_fields ['verificationcode'] = $this->generateRandomCode ();
			$instance->column_fields ['assigned_user_id'] = '1';
			$instance->save ('instancias');

			$currentModule = $oldCurrentModule;
			return $instance->id;
		}

		// Instance assignment methods

		/**
		 * @param $instanceCode
		 * @param $arguments
		 * @param $accountID
		 * @param $registrationSource
		 */
		private function registerInstanceApplications ($instanceCode, $arguments, $accountID, $registrationSource) {
			$adb = AdbManager::getInstance ()->getMasterAdb ();
			if ($registrationSource == self::REGISTRATION_SOURCE_INVITATION) {
				$result       = $adb->query ('SELECT ca.* FROM vtiger_config_applications ca ORDER BY ca.config_applicationsid LIMIT 1', true);
				$row          = $adb->fetch_array ($result);
				$applications = array ($row);
				$totalUsers   = 1;
				$isDemo       = 2;
			} else {
				$applications = $arguments ['cart']['applications'];
				$totalUsers   = !empty ($arguments ['usersCounterHidden']) ? $arguments ['usersCounterHidden'] : 20;
				$isDemo       = 0;
			}
			$companyName  = PlatzillaUtils::purify ($arguments, 'company', trim ("{$arguments ['name']} {$arguments ['lastname']}"));
			$userPassword = vtlib_purify ($arguments ['clave']);
			$userEmail    = vtlib_purify ($arguments ['usuarioEmail']);

			$result     = $adb->pquery ('SELECT instanciasid FROM vtiger_instancias WHERE code=?', array ($instanceCode), true);
			$row        = $adb->fetch_array ($result);
			$instanceID = $row ['instanciasid'];
			$adb->pquery (
				'UPDATE vtiger_instancias SET name=?, usuario=?, clave=?, accounts=?, isdemo=?, inidatedemo=?, inidateservices=?, numusuarios=?, numusuariosactivos=?, status=?, inihourinstance=? WHERE instanciasid=?',
				array ($companyName, $userEmail, $userPassword, $accountID, $isDemo, date ('Y-m-d'), null, $totalUsers, 1, 'unverified', date ('H:i:s'), $instanceID),
				true
			);

			$adb->pquery ('INSERT INTO vtiger_instanciaslogins (instanciasid, login) VALUES (?, ?)', array ($instanceID, $userEmail), true);
			foreach ($applications as $application) {
				$application = StoreUtils::getInstanceApplicationByCode ($instanceCode, $application ['app_code']);
				StoreUtils::addInstanceApplication ($instanceCode, $application);
			}
		}

		private function enableInstanceGraphs ($instanceCode) {
			$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
			$result    = $targetAdb->query ('SELECT * FROM vtiger_graficos');
			if ((!$result) || ($targetAdb->num_rows ($result) == 0)) {
				return;
			}

			while ($graph = $targetAdb->fetchByAssoc ($result, -1, false)) {
				if (empty ($graph ['roles_grafico'])) {
					continue;
				}
				$applicationIds = $this->getInstanceGraphApplicationIds ($instanceCode, $graph ['roles_grafico']);
				if (!empty ($applicationIds)) {
					$targetAdb->pquery ('UPDATE vtiger_graficos SET roles_grafico=? WHERE graficoid=?', array (join ('#', $applicationIds), $graph ['graficoid']));
				}
			}
		}

		private function getFirstAvailableStockInstance () {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$sql    = 'SELECT instanciasid, code FROM vtiger_instancias WHERE status=? ORDER BY instanciasid ASC LIMIT 1';
			$result = $adb->pquery ($sql, array ('unassigned'), true);
			if ($adb->num_rows ($result) == 1) {
				$row = $adb->fetch_array ($result);
				return $row;
			} else {
				throw new Exception ('No se encuentran instancias en stock');
			}
		}

		private function populateInstanceDefaultData ($instanceCode) {
			global $adb;
			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);

			/** @var Activity $activity */
			$activity                                   = CRMEntity::getInstance ('Calendar');
			$activity->column_fields ['subject']        = 'Tarea 1. Agrega prospectos, oportunidades y clientes';
			$activity->column_fields ['activitytype']   = 'Task';
			$activity->column_fields ['date_start']     = date ('Y-m-d');
			$activity->column_fields ['due_date']       = date ('Y-m-d');
			$activity->column_fields ['time_start']     = date ('H:i');
			$activity->column_fields ['time_end']       = date ('H:i');
			$activity->column_fields ['duration_hours'] = '0';
			$activity->column_fields ['eventstatus']    = 'Planned';
			$activity->column_fields ['priority']       = 'High';
			$activity->column_fields ['visibility']     = 'Public';
			$activity->column_fields ['recurringtype']  = '--None--';
			$activity->save ('Calendar');

			$activity                                   = CRMEntity::getInstance ('Calendar');
			$activity->column_fields ['subject']        = 'Tarea 2. Configura la información clave para vender y facturar';
			$activity->column_fields ['activitytype']   = 'Task';
			$activity->column_fields ['date_start']     = date ('Y-m-d');
			$activity->column_fields ['due_date']       = date ('Y-m-d');
			$activity->column_fields ['time_start']     = date ('H:i');
			$activity->column_fields ['time_end']       = date ('H:i');
			$activity->column_fields ['duration_hours'] = '0';
			$activity->column_fields ['eventstatus']    = 'Planned';
			$activity->column_fields ['priority']       = 'High';
			$activity->column_fields ['visibility']     = 'Public';
			$activity->column_fields ['recurringtype']  = '--None--';
			$activity->save ('Calendar');

			$activity                                   = CRMEntity::getInstance ('Calendar');
			$activity->column_fields ['subject']        = 'Tarea 3: Invita a tu equipo';
			$activity->column_fields ['activitytype']   = 'Task';
			$activity->column_fields ['date_start']     = date ('Y-m-d');
			$activity->column_fields ['due_date']       = date ('Y-m-d');
			$activity->column_fields ['time_start']     = date ('H:i');
			$activity->column_fields ['time_end']       = date ('H:i');
			$activity->column_fields ['duration_hours'] = '0';
			$activity->column_fields ['eventstatus']    = 'Planned';
			$activity->column_fields ['priority']       = 'High';
			$activity->column_fields ['visibility']     = 'Public';
			$activity->column_fields ['recurringtype']  = '--None--';
			$activity->save ('Calendar');

			$activity                                   = CRMEntity::getInstance ('Calendar');
			$activity->column_fields ['subject']        = 'Tarea 4: Organiza las tareas de atención al cliente';
			$activity->column_fields ['activitytype']   = 'Task';
			$activity->column_fields ['date_start']     = date ('Y-m-d');
			$activity->column_fields ['due_date']       = date ('Y-m-d');
			$activity->column_fields ['time_start']     = date ('H:i');
			$activity->column_fields ['time_end']       = date ('H:i');
			$activity->column_fields ['duration_hours'] = '0';
			$activity->column_fields ['eventstatus']    = 'Planned';
			$activity->column_fields ['priority']       = 'High';
			$activity->column_fields ['visibility']     = 'Public';
			$activity->column_fields ['recurringtype']  = '--None--';
			$activity->save ('Calendar');
		}

		private function registerPlatformAccount ($arguments) {
			global $adb;
			$adb = AdbManager::getInstance ()->getMasterAdb ();

			$userFirstName = vtlib_purify ($arguments ['name']);
			$userLastName  = vtlib_purify ($arguments ['lastname']);
			$companyName   = vtlib_purify (isset ($arguments ['company'])) ? $arguments ['company'] : trim ("{$arguments ['name']} {$arguments ['lastname']}");
			$userEmail     = vtlib_purify ($arguments ['usuarioEmail']);

			require_once ('modules/Accounts/Accounts.php');
			/** @var Accounts|stdClass $account */
			$account                                      = new Accounts ();
			$account->column_fields ['accountname']       = $companyName;
			$account->column_fields ['email1']            = $userEmail;
			$account->column_fields ['assigned_user_id']  = '1';
			$account->column_fields ['trial']             = '1';
			$account->column_fields ['periodo_prueba']    = '{"min":"0","max":"15","ini":"15","ord":"desc"}';
			$account->column_fields ['estado_plataforma'] = 'Activa';
			$account->column_fields ['codename']          = $this->instanceCode;
			$account->save ('Accounts');

			require_once ('modules/Contacts/Contacts.php');
			$contact                                       = new Contacts ();
			$contact->column_fields ['email']              = $userEmail;
			$contact->column_fields ['firstname']          = $userFirstName;
			$contact->column_fields ['lastname']           = $userLastName;
			$contact->column_fields ['account_id']         = $account->id;
			$contact->column_fields ['assigned_user_id']   = '1';
			$contact->column_fields ['portal']             = '1';
			$contact->column_fields ['support_start_date'] = date ('Y-m-d');
			$contact->column_fields ['support_end_date']   = strtotime ('+1 year', strtotime (date ('Y-m-d')));
			$contact->column_fields ['customer_language']  = 'es_es';
			$contact->column_fields ['roleid']             = 'H8';
			$contact->save ('Contacts');

			return $account->id;
		}

		private function registerInstanceOwnershipDetails ($arguments) {
			$adb         = AdbManager::getInstance ()->getTargetInstanceAdb ($this->instanceCode);
			$companyName = vtlib_purify (isset ($arguments ['company'])) ? $arguments ['company'] : trim ("{$arguments ['name']} {$arguments ['lastname']}");
			$adb->pquery ('INSERT INTO vtiger_role2profile (roleid, profileid) VALUES (?, ?)', array ('H2', 1), true);
			$adb->pquery ("INSERT INTO vtiger_organizationdetails (organization_id, organizationname, logoname) VALUES (1, ?, NULL)", array ($companyName), true);
			$adb->query ("INSERT INTO vtiger_organizationdetails_seq (id) VALUES (1)", true);
		}

		private function registerInstanceUser (array $arguments, $accountId) {
			global $adb;
			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($this->instanceCode);

			$userFirstName = vtlib_purify ($arguments ['name']);
			$userLastName  = vtlib_purify ($arguments ['lastname']);
			$userPassword  = vtlib_purify ($arguments ['clave']);
			$userEmail     = vtlib_purify ($arguments ['usuarioEmail']);

			require_once ('modules/Users/Users.php');
			/** @var Users|stdClass $user */
			$user                                                = new Users ();
			$user->column_fields ['user_name']                   = $userEmail;
			$user->column_fields ['crypt_type']                  = 'PHP5.3MD5';
			$user->column_fields ['user_password']               = $userPassword;
			$user->column_fields ['confirm_password']            = $userPassword;
			$user->column_fields ['email1']                      = $userEmail;
			$user->column_fields ['first_name']                  = $userFirstName;
			$user->column_fields ['last_name']                   = $userLastName;
			$user->column_fields ['is_admin']                    = 'on';
			$user->column_fields ['status']                      = 'Active';
			$user->column_fields ['theme']                       = 'centaurus';
			$user->column_fields ['language']                    = 'es_es';
			$user->column_fields ['hour_format']                 = 'am/pm';
			$user->column_fields ['start_hour']                  = '08:00';
			$user->column_fields ['activity_view']               = 'This Week';
			$user->column_fields ['lead_view']                   = 'Today';
			$user->column_fields ['internal_mailer']             = '1';
			$user->column_fields ['reminder_interval']           = '1 Minute';
			$user->column_fields ['currency_grouping_pattern']   = '123,456,789';
			$user->column_fields ['currency_decimal_separator']  = '.';
			$user->column_fields ['currency_grouping_separator'] = ',';
			$user->column_fields ['currency_symbol_placement']   = '$1.0';
			$user->column_fields ['record_id']                   = 1;
			$user->save ('Users');

			$adb->pquery ('UPDATE vtiger_users SET user_hash=?, customerid=?, customerpass=?', array (strtolower (md5 ($userPassword)), $accountId, $userPassword), true);
			$adb->query ('INSERT INTO vtiger_users_seq (id) VALUES (1)', true);
			$adb->pquery ('UPDATE vtiger_user2role SET roleid=? WHERE userid=?', array ('H2', $user->id), true);
		}

		private function cleanOnStockInstanceAssignmentError () {
			/** @var array $dbconfig */
			require (__DIR__ . '/../../config.inc.php');
			global $platPrincipal;
			$platformDatabaseName = "pg_crm_$platPrincipal";
			$instanceDatabaseName = "pg_crm_{$this->instanceCode}";

			$mysqli = null;
			try {
				$mysqli = new mysqli ($dbconfig ['db_server'], $dbconfig ['db_username'], $dbconfig ['db_password'], $dbconfig ['db_name']);
				if ($mysqli->connect_errno) {
					throw new Exception ("Imposible conectarse al servidor {$dbconfig ['db_serverForNewDB']}");
				}
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_crmentity");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_profile WHERE profileid IN (SELECT app_profile FROM {$platformDatabaseName}.vtiger_config_applications)");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_profile2tab WHERE tabid NOT IN (SELECT tabid FROM {$instanceDatabaseName}.vtiger_tab)");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_profile2field WHERE tabid NOT IN (SELECT tabid FROM {$instanceDatabaseName}.vtiger_tab)");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_profile2standardpermissions WHERE tabid NOT IN (SELECT tabid FROM {$instanceDatabaseName}.vtiger_tab)");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_profile2utility WHERE tabid NOT IN (SELECT tabid FROM {$instanceDatabaseName}.vtiger_tab)");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_profile2field WHERE profileid NOT IN (SELECT profileid FROM {$instanceDatabaseName}.vtiger_profile)");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_profile2folders WHERE profileid NOT IN (SELECT profileid FROM {$instanceDatabaseName}.vtiger_profile)");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_profile2globalpermissions WHERE profileid NOT IN (SELECT profileid FROM {$instanceDatabaseName}.vtiger_profile)");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_profile2standardpermissions WHERE profileid NOT IN (SELECT profileid FROM {$instanceDatabaseName}.vtiger_profile)");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_profile2tab WHERE profileid NOT IN (SELECT profileid FROM {$instanceDatabaseName}.vtiger_profile)");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_profile2utility WHERE profileid NOT IN (SELECT profileid FROM {$instanceDatabaseName}.vtiger_profile)");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_role2profile");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_configapps_tab");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_config_applications");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_organizationdetails");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_organizationdetails_seq");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_user2role");
				$mysqli->query ("DELETE FROM {$instanceDatabaseName}.vtiger_users");

				$mysqli->query ("DELETE FROM vtiger_instancemodules WHERE instancecode='{$this->instanceCode}'");
				$mysqli->query ("DELETE FROM vtiger_instanceapplications WHERE instancecode='{$this->instanceCode}'");
				$mysqli->query ("DELETE FROM vtiger_instanciaslogins WHERE instanciasid IN (SELECT instanciasid FROM vtiger_instancias WHERE code='{$this->instanceCode}')");
				$mysqli->query ("DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT c.contactid FROM vtiger_contactdetails c INNER JOIN vtiger_instancias i ON i.accounts=c.accountid WHERE i.code='{$this->instanceCode}')");
				$mysqli->query ("DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT accounts FROM vtiger_instancias WHERE code='{$this->instanceCode}')");
				$mysqli->query ("UPDATE vtiger_instancias SET name=NULL, usuario=NULL, clave=NULL, accounts=NULL, isdemo=NULL, inidatedemo=NULL, appidtrial=NULL, numusuarios=1, numusuariosactivos=1, status='unassigned', inihourinstance=NULL WHERE code='{$this->instanceCode}'");
				$mysqli->close ();
			} catch (Exception $ignored) {
				// Si no se puede conectar, no hacer nada
			}
		}

		private function cleanOnStockInstanceCreationError () {
			/** @var array $dbconfig */
			require (__DIR__ . '/../../config.inc.php');
			$mysqli = null;
			try {
				$mysqli = new mysqli ($dbconfig ['db_server'], $dbconfig ['db_username'], $dbconfig ['db_password'], $dbconfig ['db_name']);
				if ($mysqli->connect_errno) {
					throw new Exception ("Imposible conectarse al servidor {$dbconfig ['db_serverForNewDB']}");
				}
				$instanceUserHostName = $dbconfig ['db_serverForNewUsers'];
				$instanceDatabaseName = "pg_crm_{$this->instanceCode}";
				$instanceUserName     = "usr_{$this->instanceCode}";

				$mysqli->query ("DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT instanciasid FROM vtiger_instancias WHERE code='{$this->instanceCode}')");

				$mysqli->query ("DROP DATABASE IF EXISTS {$instanceDatabaseName}");
				$mysqli->query ("DROP USER '$instanceUserName'@'$instanceUserHostName'");
			} catch (Exception $ignored) {
				// Si no se puede conectar, no hacer nada
			}
			if ($mysqli) {
				$mysqli->close ();
			}
		}

		// Public methods

		/**
		 * @return string
		 * @throws Exception
		 */
		public function createStockInstance () {
			global $adb;
			$this->startSession ();
			$this->action       = self::$ACTION_CREATE_STOCK_INSTANCE;
			$instanceID         = null;
			$this->instanceCode = $this->getNewInstanceCode ();
			$sourceModuleNames  = $this->getSourceModuleNames ();
			if (!$sourceModuleNames) {
				throw new Exception ('No hay módulos registrados en la plataforma');
			}
			$this->createInstanceDatabase ();
			$this->createInstanceSequenceTables ();
			$this->createInstanceSecurityTables ();
			$this->createInstanceCoreTables ();
			$this->createInstanceProfileTables ();
			$this->createInstanceSettingsTables ();
			$this->createInstanceCatalogTables ();
			$this->createInstanceBaseTables ();
			$adb        = AdbManager::getInstance ()->getMasterAdb ();
			$instanceID = $this->registerStockInstance ();
			/** @var PackageExporter $exporter */
			$exporter = null;
			foreach ($sourceModuleNames as $sourceModuleName) {
				$this->logMessage ('INFO', "Exportando {$sourceModuleName}");
				$exporter = InstanceUtils::exportModule ($sourceModuleName);
			}
			$adb                       = AdbManager::getInstance ()->getTargetInstanceAdb ($this->instanceCode);
			$this->temporaryFolderPath = $exporter != null ? $exporter->getExportFolder () : sys_get_temp_dir () . '/vtlib';
			foreach ($sourceModuleNames as $sourceModuleName) {
				$this->logMessage ('INFO', "Importando {$sourceModuleName}");
				InstanceUtils::importModule ("{$this->temporaryFolderPath}/$sourceModuleName.zip");
			}
			foreach ($sourceModuleNames as $sourceModuleName) {
				$this->logMessage ('INFO', "Listas relacionadas  {$sourceModuleName}");
				InstanceUtils::importRelatedLists ("{$this->temporaryFolderPath}/$sourceModuleName.zip");
			}
			$this->fixStockInstanceConfiguration ();
			$this->actionFullfilled = true;
			return $instanceID;
		}

		public function assignStockInstance (array $arguments, $registrationSource) {
			global $platPrincipal;
			require ('config.inc.php');
			$this->action = self::$ACTION_ASSIGN_STOCK_INSTANCE;
			list ($instanceId, $this->instanceCode) = $this->getFirstAvailableStockInstance ();
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			BackgroundTasksRunner::getInstance ($masterAdb, $platPrincipal)->runEventTriggeredTasks (
				'INSTANCE ASSIGNMENT',
				BackgroundTaskInterface::EVENT_INSTANT_BEFORE,
				PlatformUtils::getCrmEntity ($masterAdb, 'instancias', $instanceId)
			);
			$this->cleanOnStockInstanceAssignmentError ();
			$accountId = $this->registerPlatformAccount ($arguments);
			$this->registerInstanceOwnershipDetails ($arguments);
			$this->registerInstanceUser ($arguments, $accountId);
			$this->registerInstanceApplications ($this->instanceCode, $arguments, $accountId, $registrationSource);
			$this->enableInstanceGraphs ($this->instanceCode);
			$this->populateInstanceDefaultData ($this->instanceCode);
			$this->actionFullfilled = true;
			BackgroundTasksRunner::getInstance ($masterAdb, $platPrincipal)->runEventTriggeredTasks (
				'INSTANCE ASSIGNMENT',
				BackgroundTaskInterface::EVENT_INSTANT_AFTER,
				PlatformUtils::getCrmEntity ($masterAdb, 'instancias', $instanceId)
			);
			return $this->instanceCode;
		}

		public function userHasInstance ($userEmail) {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery (
				"SELECT
					1
				FROM
					vtiger_instancias i
					INNER JOIN vtiger_instanciaslogins il ON il.instanciasid=i.instanciasid
				WHERE
					(i.usuario=? OR il.login=?)",
				array ($userEmail, $userEmail)
			);
			return $adb->num_rows ($result) > 0;
		}

		public static function getCreator (Logger $log = null) {
			if (self::$CREATOR == null) {
				self::$CREATOR = new InstanceCreator ($log);
			}
			return self::$CREATOR;
		}

	}
