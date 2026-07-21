<?php
	require_once ('include/platzilla/Exceptions/DatabaseException.php');

	abstract class DatabaseUtils {

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $columnName
		 * @param string $sqlDataType
		 * @param boolean $allowNulls
		 * @param string $defaultValue
		 *
		 * @throws DatabaseException
		 */
		public static function addColumnIfNotExists (PearDatabase $adb, $tableName, $columnName, $sqlDataType, $allowNulls = true, $defaultValue = null) {
			if (
				(empty ($tableName)) ||
				(strtolower ($tableName) == 'vtiger_crmentity') ||
				(empty ($columnName)) ||
				(empty ($sqlDataType)) ||
				(!self::checkIfTableExists ($adb, $tableName)) ||
				(self::checkIfColumnExists ($adb, $tableName, $columnName))
			) {
				return;
			}

			$tableName  = $adb->sql_escape_string ($tableName);
			$columnName = $adb->sql_escape_string ($columnName);
			$sqlClause  = "ADD COLUMN `{$columnName}` {$sqlDataType}";
			if ($allowNulls) {
				$sqlClause .= ' NULL';
			} else {
				$sqlClause .= ' NOT NULL';
			}
			if (!empty ($defaultValue)) {
				$sqlClause .= " DEFAULT {$defaultValue}";
			}

			$oldDieOnError = $adb->dieOnError;
			$adb->setDieOnError (false);
			if (!$adb->query ("ALTER TABLE `{$tableName}` {$sqlClause}")) {
				/** @noinspection PhpUndefinedMethodInspection */
				$e = new DatabaseException ($adb->database->ErrorMsg ());
			}
			$adb->setDieOnError ($oldDieOnError);
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $columnName
		 *
		 * @return boolean
		 */
		public static function checkIfColumnExists (PearDatabase $adb, $tableName, $columnName) {
			if ((empty ($tableName)) || (empty ($columnName)) || (!self::checkIfTableExists ($adb, $tableName))) {
				return false;
			}

			$tableName = $adb->sql_escape_string ($tableName);
			$result    = $adb->pquery ("SHOW COLUMNS FROM `{$tableName}` WHERE Field=?", array ($columnName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				$exists = false;
			} else {
				$exists = true;
			}
			self::closeResult ($result);
			$result = null;
			return $exists;
		}

		/**
		 * @param string $databaseName
		 *
		 * @return boolean
		 */
		public static function checkIfDatabaseExists ($databaseName) {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery ('SHOW DATABASES LIKE ?', array ($databaseName));
			$exists = ($adb->num_rows ($result) > 0);
			self::closeResult ($result);
			$result = null;
			return $exists;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $databaseName
		 *
		 * @return boolean
		 */
		public static function checkIfTableExists (PearDatabase $adb, $tableName, $databaseName = null) {
			if (empty ($tableName)) {
				return false;
			}

			$databaseName = !empty ($databaseName) ? $databaseName : $adb->dbName;
			$result       = $adb->pquery ("SHOW TABLES IN {$databaseName} LIKE ?", array ($tableName));
			$exists       = ($result) && ($adb->num_rows ($result) > 0);
			self::closeResult ($result);
			$result = null;
			return $exists;
		}

		/**
		 * @param ADORecordSet $result
		 */
		public static function closeResult (&$result) {
			if (!($result instanceof ADORecordSet)) {
				return;
			}

			$result->Close ();
			unset ($result);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $idColumnName
		 *
		 * @throws DatabaseException
		 */
		public static function createModuleTableIfNotExists (PearDatabase $adb, $tableName, $idColumnName) {
			if (self::checkIfTableExists ($adb, $tableName)) {
				return;
			}

			$tableName     = $adb->sql_escape_string ($tableName);
			$idColumnName  = $adb->sql_escape_string ($idColumnName);
			$oldDieOnError = $adb->dieOnError;
			$adb->setDieOnError (false);
			$result = $adb->query (
				"CREATE TABLE `{$tableName}` (
					`{$idColumnName}` INT(11) NOT NULL,
					PRIMARY KEY (`{$idColumnName}`),
					CONSTRAINT `vtiger_{$tableName}_crmentity` FOREIGN KEY (`{$idColumnName}`) REFERENCES `vtiger_crmentity` (`crmid`) ON UPDATE CASCADE ON DELETE CASCADE
				) ENGINE=InnoDB"
			);

			if (!$result) {
				/** @noinspection PhpUndefinedMethodInspection */
				$e = new DatabaseException ($adb->database->ErrorMsg ());
			}
			$adb->setDieOnError ($oldDieOnError);
			self::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $columnName
		 *
		 * @throws DatabaseException
		 */
		public static function deleteColumnIfExists (PearDatabase $adb, $tableName, $columnName) {
			if (
				(empty ($tableName)) ||
				(strtolower ($tableName) == 'vtiger_crmentity') ||
				(empty ($columnName)) ||
				(!self::checkIfTableExists ($adb, $tableName)) ||
				(!self::checkIfColumnExists ($adb, $tableName, $columnName))
			) {
				return;
			}

			$tableName     = $adb->sql_escape_string ($tableName);
			$columnName    = $adb->sql_escape_string ($columnName);
			$oldDieOnError = $adb->dieOnError;
			$adb->setDieOnError (false);
			/** @noinspection SqlResolve */
			if (!$adb->query ("ALTER TABLE `{$tableName}` DROP COLUMN `{$columnName}`")) {
				/** @noinspection PhpUndefinedMethodInspection */
				$e = new DatabaseException ($adb->database->ErrorMsg ());
			}
			$adb->setDieOnError ($oldDieOnError);
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $columnsToKeep
		 *
		 * @throws DatabaseException
		 */
		public static function deleteColumns (PearDatabase $adb, $tableName, $columnsToKeep) {
			if (
				(empty ($tableName)) ||
				(strtolower ($tableName) == 'vtiger_crmentity') ||
				(!self::checkIfTableExists ($adb, $tableName))
			) {
				return;
			}

			if (!empty ($columnsToKeep)) {
				$questionMarks = str_repeat ('?, ', (count ($columnsToKeep) - 1)) . '?';
				$whereClause   = "WHERE Field NOT IN ({$questionMarks})";
				$arguments     = $columnsToKeep;
			} else {
				$whereClause = '';
				$arguments   = array ();
			}

			$tableName = $adb->sql_escape_string ($tableName);
			$result    = $adb->pquery ("SHOW COLUMNS FROM {$tableName} {$whereClause}", $arguments);
			if ($adb->num_rows ($result) > 0) {
				$oldDieOnError = $adb->dieOnError;
				$adb->setDieOnError (false);
				$adb->startTransaction ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					/** @noinspection SqlResolve */
					if (!$adb->query ("ALTER TABLE `{$tableName}` DROP COLUMN `{$row ['field']}`")) {
						/** @noinspection PhpUndefinedMethodInspection */
						$e = new DatabaseException ($adb->database->ErrorMsg ());
						break;
					}
				}
				$adb->setDieOnError ($oldDieOnError);
				if (isset ($e)) {
					throw $e;
				}
				$adb->completeTransaction ();
			}
			self::closeResult ($result);
			$result = null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 *
		 * @throws DatabaseException
		 */
		public static function deleteTableIfExists (PearDatabase $adb, $tableName) {
			$oldDieOnError = $adb->dieOnError;
			$adb->setDieOnError (false);
			$result = $adb->query ("DROP TABLE IF EXISTS `{$tableName}`");
			if (!$result) {
				/** @noinspection PhpUndefinedMethodInspection */
				$e = new DatabaseException ($adb->database->ErrorMsg ());
			}
			$adb->setDieOnError ($oldDieOnError);
			self::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * Destruye todos los procesos asociados a la conexión suministrada
		 *
		 * @param PearDatabase $adb
		 */
		public static function fullyDisconnect (PearDatabase $adb) {
			$processIds = array ();
			$result     = $adb->query ('SHOW PROCESSLIST');
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['user'] == $adb->userName) {
						$processIds [] = $row ['id'];
					}
				}
			}
			self::closeResult ($result);
			$result = null;
			$adb->disconnect ();

			if (!empty ($processIds)) {
				$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
				foreach ($processIds as $processId) {
					$oldDieOnError = $masterAdb->dieOnError;
					$masterAdb->setDieOnError (false);
					try {
						$masterAdb->pquery ('KILL ?', array ($processId));
					} catch (Exception $ignored) {
						// Do nothing
					}
					$masterAdb->setDieOnError ($oldDieOnError);
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $columnName
		 * @param string $sqlDataType
		 * @param boolean $allowNulls
		 * @param string $defaultValue
		 *
		 * @throws DatabaseException
		 */
		public static function updateColumnIfExists (PearDatabase $adb, $tableName, $columnName, $sqlDataType, $allowNulls = true, $defaultValue = null) {
			if (
				(empty ($tableName)) ||
				(in_array (strtolower ($tableName), array ('vtiger_crmentity', 'vtiger_users'))) ||
				(empty ($columnName)) ||
				(empty ($sqlDataType)) ||
				(!self::checkIfTableExists ($adb, $tableName)) ||
				(!self::checkIfColumnExists ($adb, $tableName, $columnName))
			) {
				return;
			}

			$tableName  = $adb->sql_escape_string ($tableName);
			$columnName = $adb->sql_escape_string ($columnName);
			$sqlClause  = "CHANGE COLUMN `{$columnName}` `{$columnName}` {$sqlDataType}";
			if ($allowNulls) {
				$sqlClause .= ' NULL';
			} else {
				$sqlClause .= ' NOT NULL';
			}
			if (!empty ($defaultValue)) {
				$sqlClause .= " DEFAULT {$defaultValue}";
			}

			$oldExceptionInsteadOfDying   = $adb->exceptionInsteadOfDying;
			$oldDieOnError                = $adb->dieOnError;
			$adb->exceptionInsteadOfDying = false;
			$adb->setDieOnError (false);
			$adb->startTransaction ();
			/** @noinspection SqlResolve */
			$adb->query ("ALTER TABLE `{$tableName}` ALTER `{$columnName}` DROP DEFAULT");
			if (!$adb->query ("ALTER TABLE `{$tableName}` {$sqlClause}")) {
				/** @noinspection PhpUndefinedMethodInspection */
				$e = new DatabaseException ($adb->database->ErrorMsg ());
			}
			$adb->exceptionInsteadOfDying = $oldExceptionInsteadOfDying;
			$adb->setDieOnError ($oldDieOnError);
			if (isset ($e)) {
				throw $e;
			}
			$adb->completeTransaction ();
		}

	}
