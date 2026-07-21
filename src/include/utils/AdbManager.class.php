<?php
	require_once ('include/database/PearDatabase.php');

	class AdbManager {
		private static $ADBMANAGER = null;
		/** @var PearDatabase */
		private static $MASTER_ADB = null;
		/** @var PearDatabase */
		private static $SOURCE_INSTANCE_ADB = null;
		/** @var PearDatabase */
		private static $TARGET_INSTANCE_ADB = null;
		protected      $masterInstanceName  = null;

		public function __construct () {
			require ('config.inc.php');
			global $platPrincipal;
			$this->masterInstanceName = $platPrincipal;
		}

		/**
		 * @param $instanceName
		 *
		 * @return PearDatabase
		 */
		final public function getSourceInstanceAdb ($instanceName) {
			global $dbconfig, $adb;
			$databaseName = "pg_crm_$instanceName";
			if (self::$SOURCE_INSTANCE_ADB == null) {
				if (($adb) && ($adb->dbName == $databaseName)) {
					self::$SOURCE_INSTANCE_ADB = $adb;
				} else if ($instanceName == $this->masterInstanceName) {
					self::$SOURCE_INSTANCE_ADB = $this->getMasterAdb ();
				} else if ((self::$TARGET_INSTANCE_ADB !== null) && (self::$TARGET_INSTANCE_ADB->dbName == $databaseName)) {
					self::$SOURCE_INSTANCE_ADB = self::$TARGET_INSTANCE_ADB;
				} else {
					$databaseUserName          = "usr_$instanceName";
					$databaseUserPassword      = md5 ($databaseUserName);
					self::$SOURCE_INSTANCE_ADB = new PearDatabase ($dbconfig ['db_type'], $dbconfig ['db_serverForNewDB'], $databaseName, $databaseUserName, $databaseUserPassword);
				}
			} else if (self::$SOURCE_INSTANCE_ADB->dbName != $databaseName) {
				$databaseUserName          = "usr_$instanceName";
				$databaseUserPassword      = md5 ($databaseUserName);
				self::$SOURCE_INSTANCE_ADB = new PearDatabase ($dbconfig ['db_type'], $dbconfig ['db_serverForNewDB'], $databaseName, $databaseUserName, $databaseUserPassword);
			}
			return self::$SOURCE_INSTANCE_ADB;
		}

		/**
		 * @param $instanceName
		 *
		 * @return PearDatabase
		 */
		final public function getTargetInstanceAdb ($instanceName) {
			global $dbconfig, $adb;
			$databaseName = "pg_crm_$instanceName";
			if (self::$TARGET_INSTANCE_ADB == null) {
				if (($adb) && ($adb->dbName == $databaseName)) {
					self::$TARGET_INSTANCE_ADB = $adb;
				} else if ($instanceName == $this->masterInstanceName) {
					self::$TARGET_INSTANCE_ADB = $this->getMasterAdb ();
				} else if ((self::$SOURCE_INSTANCE_ADB !== null) && (self::$SOURCE_INSTANCE_ADB->dbName == $databaseName)) {
					self::$TARGET_INSTANCE_ADB = self::$SOURCE_INSTANCE_ADB;
				} else {
					$databaseUserName          = "usr_$instanceName";
					$databaseUserPassword      = md5 ($databaseUserName);
					self::$TARGET_INSTANCE_ADB = new PearDatabase ($dbconfig ['db_type'], $dbconfig ['db_serverForNewDB'], $databaseName, $databaseUserName, $databaseUserPassword);
				}
			} else if (self::$TARGET_INSTANCE_ADB->dbName != $databaseName) {
				$databaseUserName          = "usr_$instanceName";
				$databaseUserPassword      = md5 ($databaseUserName);
				self::$TARGET_INSTANCE_ADB = new PearDatabase ($dbconfig ['db_type'], $dbconfig ['db_serverForNewDB'], $databaseName, $databaseUserName, $databaseUserPassword);
			}
			return self::$TARGET_INSTANCE_ADB;
		}

		/**
		 * @return PearDatabase
		 */
		final public function getMasterAdb () {
			global $dbconfig;
			include ('config.inc.php');
			if (self::$MASTER_ADB == null) {
				self::$MASTER_ADB = new PearDatabase ($dbconfig ['db_type'], $dbconfig ['db_server'], $dbconfig ['db_name'], $dbconfig ['db_username'], $dbconfig ['db_password']);
			}
			return self::$MASTER_ADB;
		}

		/**
		 * @return AdbManager
		 */
		public static function getInstance () {
			if (self::$ADBMANAGER == null) {
				self::$ADBMANAGER = new AdbManager ();
			}
			return self::$ADBMANAGER;
		}

	}
