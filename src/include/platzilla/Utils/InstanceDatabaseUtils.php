<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	abstract class InstanceDatabaseUtils extends DatabaseUtils {

		/**
		 * @param PearDatabase $adb
		 * @param string $platformDatabaseName
		 * @param string $instanceDatabaseName
		 * @param string $tableName
		 *
		 * @return boolean
		 */
		private static function createInstanceTable (PearDatabase $adb, $platformDatabaseName, $instanceDatabaseName, $tableName) {
			$sql    = "SHOW CREATE TABLE {$platformDatabaseName}.{$tableName}";
			$result = $adb->query ($sql, true);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$sql = str_replace ("`$tableName`", "`$instanceDatabaseName`.`$tableName`", $row ['create table']);
				$adb->query ($sql, true);
				$created = true;
			} else {
				$created = false;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $created;
		}

		/**
		 * @param mysqli_result $result
		 * @param string $onErrorMessage
		 *
		 * @throws DatabaseException
		 */
		private static function validateResult ($result, $onErrorMessage) {
			if (!$result) {
				throw new DatabaseException ($onErrorMessage);
			} else if ($result instanceof mysqli_result) {
				$result->close ();
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param array $tableNames
		 */
		private static function createInstanceApplicationTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName);
				$adb->query ("ALTER TABLE {$instanceDatabaseName}.{$tableName} AUTO_INCREMENT=1", true);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param array $tableNames
		 */
		private static function createInstanceBaseTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName);
			}
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_org_share_action_mapping SELECT * FROM {$adb->dbName}.vtiger_org_share_action_mapping", true);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param array $tableNames
		 */
		private static function createInstanceCatalogTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				if (self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName)) {
					$adb->query ("INSERT INTO {$instanceDatabaseName}.$tableName SELECT * FROM {$adb->dbName}.$tableName", true);
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param array $tableNames
		 */
		private static function createInstanceCoreTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName);
			}
			$adb->query ("ALTER TABLE {$instanceDatabaseName}.vtiger_field AUTO_INCREMENT=1");
			$adb->query ("ALTER TABLE {$instanceDatabaseName}.vtiger_fieldmodulerel AUTO_INCREMENT=1");
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_parenttab SELECT * FROM {$adb->dbName}.vtiger_parenttab", true);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param array $tableNames
		 */
		private static function createInstanceProfileTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName);
			}
			$adb->query ("ALTER TABLE {$instanceDatabaseName}.vtiger_profile AUTO_INCREMENT=1");
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param array $tableNames
		 */
		private static function createInstanceSecurityTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName);
			}
			$adb->query ("ALTER TABLE {$instanceDatabaseName}.vtiger_users AUTO_INCREMENT=1", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_users_seq (id) VALUES (1)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_role_seq (id) VALUES (0)", true);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param array $tableNames
		 */
		private static function createInstanceSequenceTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				if (!self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName)) {
					continue;
				}
				$adb->query ("INSERT INTO {$instanceDatabaseName}.$tableName (id) VALUES (0)", true);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param array $tableNames
		 */
		private static function createInstanceSettingsTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName);
			}

			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_blocks (blockid, label, sequence) VALUES (1, 'LBL_GENERAL_SETTINGS', 1)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_blocks (blockid, label, sequence) VALUES (2, 'LBL_APPLICATIONS_SETTINGS', 2)", true);
			$adb->query ("UPDATE {$instanceDatabaseName}.vtiger_settings_blocks_seq SET id=4", true);

			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (1, 1, 'Accesos y permisos', 'LBL_PROFILES', 'fa fa-key emerald-bg', 'LBL_PROFILE_DESCRIPTION', 'index.php?module=Settings&action=ProfileListView&parenttab=Settings', 1, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (2, 1, 'Accesos y permisos', 'LBL_ROLES', 'fa fa-sort-amount-asc green-bg', 'LBL_ROLE_DESCRIPTION', 'index.php?module=Settings&action=listroles&parenttab=Settings', 2, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (3, 1, 'Accesos y permisos', 'LBL_USERS', 'fa fa-user red-bg', 'LBL_USER_DESCRIPTION', 'index.php?module=panelusuarios&action=index&parenttab=Settings', 3, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (4, 1, 'Accesos y permisos', 'USERGROUPLIST', 'fa fa-users purple-bg', 'LBL_GROUP_DESCRIPTION', 'index.php?module=Settings&action=listgroups&parenttab=Settings', 4, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (5, 1, 'Accesos y permisos', 'LBL_SHARING_ACCESS', 'fa fa-lock emerald-bg', 'LBL_SHARING_ACCESS_DESCRIPTION', 'index.php?module=Settings&action=OrgSharingDetailView&parenttab=Settings', 5, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (6, 1, 'Accesos y permisos', 'LBL_INSTANCES_DATA_SHARING_NAME', 'fa fa-exchange green-bg', 'LBL_INSTANCES_DATA_SHARING_DESCRIPTION', 'index.php?module=instancesdatasharing&action=index&parenttab=Settings', 6, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (11, 1, 'Outputs', 'LBL_CONFIG_FORMAT_REPORT', 'fa  fa-file-pdf-o purple-bg', 'LBL_CONFIG_FORMAT_REPORT_DESCRIPTION', 'index.php?module=reportmanager&action=index&parenttab=Settings', 1, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (12, 1, 'Outputs', 'LBL_WIDGETS', 'fa fa-cube green-bg', 'LBL_WIDGETS_DESCRIPTION', 'index.php?module=admin_widgets&action=index', 2, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (13, 1, 'Outputs', 'LBL_CALENDAR_VIEW', 'fa fa-calendar red-bg', 'LBL_CALENDAR_VIEW_DESCRIPTION', 'index.php?module=Settings&action=CalendarViewListView&parenttab=Settings', 3, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (14, 1, 'Outputs', 'LBL_KANBAN_VIEW', 'fa fa-th yellow-bg', 'LBL_KANBAN_VIEW_DESCRIPTION', 'index.php?module=Settings&action=KanbanViewListView&parenttab=Settings', 4, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (21, 1, 'Motores y gestores', 'LBL_EMAIL_MANAGER', 'fa fa-envelope-o emerald-bg', 'LBL_EMAIL_MANAGER_DESCRIPTION', 'index.php?module=emailmanager&action=index&parenttab=Settings', 1, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (22, 1, 'Motores y gestores', 'LBL_BACKGROUND_TASKS_NAME', 'fa fa-cogs purple-bg', 'LBL_BACKGROUND_TASKS_DESCRIPTION', 'index.php?module=backgroundtasks&action=index&parenttab=Settings', 2, 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (23, 1, 'Motores y gestores', 'LBL_SCREEN_NOTIFICATIONS', 'fa fa-bell emerald-bg', 'LBL_SCREEN_NOTIFICATIONS_DESCRIPTION', 'index.php?module=notifications&action=index', 3, 0);", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active) VALUES (24, 1, 'Motores y gestores', 'LBL_CONFIG_CALCULATED_FIELDS', 'fa fa-sun-o green-bg', 'LBL_CONFIG_CALCULATED_FIELDS_DESCRIPTION', 'index.php?module=calculated_fields&action=index&parenttab=Settings', 4, 0);", true);
			$adb->query ("UPDATE {$instanceDatabaseName}.vtiger_settings_field_seq SET id=31", true);

			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_currency_info (currency_name, currency_code, currency_symbol, conversion_rate, currency_status, defaultid, deleted) VALUES ('Euro', 'EUR', '€', 1.000, 'Active', '-11', 0)", true);
			$adb->query ("INSERT INTO {$instanceDatabaseName}.vtiger_currency_info_seq (id) VALUES (1)", true);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param array $tableNames
		 */
		private static function createInstanceSpecialModuleTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param string[] $tableNames
		 */
		private static function updateInstanceApplicationTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				if (!self::checkIfTableExists ($adb, $tableName, $instanceDatabaseName)) {
					self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName);
					$adb->query ("ALTER TABLE {$instanceDatabaseName}.{$tableName} AUTO_INCREMENT=1", true);
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param string[] $tableNames
		 */
		private static function updateInstanceCatalogTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				if (!self::checkIfTableExists ($adb, $tableName)) {
					self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName);
					$adb->query ("INSERT INTO {$instanceDatabaseName}.$tableName SELECT * FROM {$adb->dbName}.$tableName", true);
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param string[] $tableNames
		 */
		private static function updateInstanceSequenceTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				if (!self::checkIfTableExists ($adb, $tableName, $instanceDatabaseName)) {
					self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName);
					$adb->query ("INSERT INTO {$instanceDatabaseName}.$tableName (id) VALUES (0)", true);
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param string[] $tableNames
		 */
		private static function updateInstanceTables (PearDatabase $adb, $instanceCode, $tableNames) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			foreach ($tableNames as $tableName) {
				if (!self::checkIfTableExists ($adb, $tableName, $instanceDatabaseName)) {
					self::createInstanceTable ($adb, $adb->dbName, $instanceDatabaseName, $tableName);
				}
			}
		}

		/**
		 * @param string $databasesHostName
		 * @param string $userName
		 * @param string $password
		 * @param string $instanceCode
		 *
		 * @throws DatabaseException
		 * @throws Exception
		 */
		public static function createInstanceDatabase ($databasesHostName, $userName, $password, $instanceCode) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";

			try {
				$mysqli = new mysqli ();
				$mysqli->connect ($databasesHostName, $userName, $password);
				if ($mysqli->connect_errno) {
					throw new DatabaseException (DatabaseException::ERROR_UNABLE_TO_CONNECT);
				}

				// Eliminar base de datos
				$sql    = "DROP DATABASE IF EXISTS `{$instanceDatabaseName}`";
				$result = $mysqli->query ($sql);
				self::validateResult ($result, DatabaseException::ERROR_UNABLE_TO_DELETE_DATABASE);

				// Crear base de datos
				$sql    = "CREATE DATABASE IF NOT EXISTS {$instanceDatabaseName} DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci";
				$result = $mysqli->query ($sql);
				self::validateResult ($result, DatabaseException::ERROR_UNABLE_TO_CREATE_DATABASE);
			} catch (Exception $ie) {
				$e = $ie;
			}
			if (isset ($mysqli)) {
				$mysqli->close ();
			}
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param string[] $tableNames
		 */
		public static function createInstanceDatabaseTables (PearDatabase $adb, $instanceCode, $tableNames) {
			self::createInstanceSequenceTables ($adb, $instanceCode, $tableNames ['sequence']);
			self::createInstanceSecurityTables ($adb, $instanceCode, $tableNames ['security']);
			self::createInstanceCoreTables ($adb, $instanceCode, $tableNames ['core']);
			self::createInstanceProfileTables ($adb, $instanceCode, $tableNames ['profile']);
			self::createInstanceSettingsTables ($adb, $instanceCode, $tableNames ['settings']);
			self::createInstanceCatalogTables ($adb, $instanceCode, $tableNames ['catalog']);
			self::createInstanceBaseTables ($adb, $instanceCode, $tableNames ['base']);
			self::createInstanceApplicationTables ($adb, $instanceCode, $tableNames ['application']);
			self::createInstanceSpecialModuleTables ($adb, $instanceCode, $tableNames ['specialmodules']);
		}

		/**
		 * @param string $hostName
		 * @param string $userName
		 * @param string $password
		 * @param string $httpHostName
		 * @param string $instanceCode
		 *
		 * @throws DatabaseException
		 * @throws Exception
		 */
		public static function createInstanceDatabaseUser ($hostName, $userName, $password, $httpHostName, $instanceCode) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";
			$instanceUserName     = "usr_{$instanceCode}";
			$instanceUserPassword = md5 ($instanceUserName);

			try {
				$mysqli = new mysqli ();
				$mysqli->connect ($hostName, $userName, $password);
				if ($mysqli->connect_errno) {
					throw new DatabaseException (DatabaseException::ERROR_UNABLE_TO_CONNECT);
				}

				// Eliminar usuario
				$sql    = "SELECT u.User FROM mysql.user u WHERE u.User='{$instanceUserName}' AND u.Host='{$httpHostName}'";
				$result = $mysqli->query ($sql);
				if (($result) && ($result->num_rows > 0)) {
					$result->close ();
					$sql    = "DROP USER '{$instanceUserName}'@'{$httpHostName}'";
					$result = $mysqli->query ($sql);
					self::validateResult ($result, DatabaseException::ERROR_UNABLE_TO_DELETE_USER);
				}

				// Crear usuario
				$sql    = "CREATE USER '{$instanceUserName}'@'{$httpHostName}' IDENTIFIED BY '{$instanceUserPassword}'";
				$result = $mysqli->query ($sql);
				self::validateResult ($result, DatabaseException::ERROR_UNABLE_TO_CREATE_USER);

				// Asignar permisos
				$sql    = "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, CREATE VIEW, EVENT, TRIGGER, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON {$instanceDatabaseName}.* TO  '{$instanceUserName}'@'{$httpHostName}'";
				$result = $mysqli->query ($sql);
				self::validateResult ($result, DatabaseException::ERROR_UNABLE_TO_GRANT_PERMISSIONS);
			} catch (Exception $ie) {
				$e = $ie;
			}
			if (isset ($mysqli)) {
				$mysqli->close ();
			}
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param string $hostName
		 * @param string $userName
		 * @param string $password
		 * @param string $instanceCode
		 *
		 * @throws DatabaseException
		 * @throws Exception
		 */
		public static function deleteInstanceDatabase ($hostName, $userName, $password, $instanceCode) {
			$instanceDatabaseName = "pg_crm_{$instanceCode}";

			try {
				$mysqli = new mysqli ();
				$mysqli->connect ($hostName, $userName, $password);
				if ($mysqli->connect_errno) {
					throw new DatabaseException (DatabaseException::ERROR_UNABLE_TO_CONNECT);
				}

				// Eliminar base de datos
				$sql    = "DROP DATABASE IF EXISTS `{$instanceDatabaseName}`";
				$result = $mysqli->query ($sql);
				self::validateResult ($result, DatabaseException::ERROR_UNABLE_TO_DELETE_DATABASE);
			} catch (Exception $ie) {
				$e = $ie;
			}
			if (isset ($mysqli)) {
				$mysqli->close ();
			}
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param string $hostName
		 * @param string $userName
		 * @param string $password
		 * @param string $httpHostName
		 * @param string $instanceCode
		 *
		 * @throws DatabaseException
		 * @throws Exception
		 */
		public static function deleteInstanceUser ($hostName, $userName, $password, $httpHostName, $instanceCode) {
			$instanceUserName = "usr_{$instanceCode}";

			try {
				$mysqli = new mysqli ();
				$mysqli->connect ($hostName, $userName, $password);
				if ($mysqli->connect_errno) {
					throw new DatabaseException (DatabaseException::ERROR_UNABLE_TO_CONNECT);
				}

				// Eliminar usuario
				$sql    = "SELECT u.User FROM mysql.user u WHERE u.User='{$instanceUserName}' AND u.Host='{$httpHostName}'";
				$result = $mysqli->query ($sql);
				if (($result) && ($result->num_rows > 0)) {
					$result->close ();
					$sql    = "DROP USER '{$instanceUserName}'@'{$httpHostName}'";
					$result = $mysqli->query ($sql);
					self::validateResult ($result, DatabaseException::ERROR_UNABLE_TO_DELETE_USER);
				}
			} catch (Exception $ie) {
				$e = $ie;
			}
			if (isset ($mysqli)) {
				$mysqli->close ();
			}
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param array $tableNames
		 */
		public static function updateInstanceDatabaseTables (PearDatabase $adb, $instanceCode, $tableNames) {
			self::updateInstanceSequenceTables ($adb, $instanceCode, $tableNames ['sequence']);
			self::updateInstanceTables ($adb, $instanceCode, $tableNames ['security']);
			self::updateInstanceTables ($adb, $instanceCode, $tableNames ['core']);
			self::updateInstanceTables ($adb, $instanceCode, $tableNames ['profile']);
			self::updateInstanceTables ($adb, $instanceCode, $tableNames ['settings']);
			self::updateInstanceCatalogTables ($adb, $instanceCode, $tableNames ['catalog']);
			self::updateInstanceTables ($adb, $instanceCode, $tableNames ['base']);
			self::updateInstanceApplicationTables ($adb, $instanceCode, $tableNames ['application']);
			self::updateInstanceTables ($adb, $instanceCode, $tableNames ['specialmodules']);
		}

	}
