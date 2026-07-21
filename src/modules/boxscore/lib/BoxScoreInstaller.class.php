<?php
	class BoxScoreInstaller {
		private static $INSTANCE = null;

		private function buildSqlStatementsToCreateTables () {
			$sqlStatements = $this->buildSqlStatementsToDropTables ();
			return array_merge (
				$sqlStatements,
				array (
					'CREATE TABLE IF NOT EXISTS vtiger_boxscore_blocks (
						tipo int(10) NOT NULL AUTO_INCREMENT,
						colorbase varchar(50) DEFAULT NULL,
						colordegrade varchar(50) DEFAULT NULL,
						user int(10) DEFAULT NULL,
						PRIMARY KEY (tipo)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8',
					'CREATE TABLE IF NOT EXISTS vtiger_boxscore_operacion (
						operacion_id int(10) NOT NULL AUTO_INCREMENT,
						boxscoreid int(10) DEFAULT \'0\',
						calculo varchar(255) DEFAULT NULL,
						elements varchar(255) DEFAULT NULL,
						operators varchar(255) DEFAULT NULL,
						tipo int(10) DEFAULT NULL,
						usuario int(10) DEFAULT NULL,
						KEY operacion_id (operacion_id)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8',
					"CREATE TABLE IF NOT EXISTS vtiger_boxscore_privileges (
						privileges_id int(10) NOT NULL AUTO_INCREMENT,
						userid int(10) DEFAULT '0',
						boxscoreid int(10) DEFAULT '0',
						box_score_dataid int(10) DEFAULT '0',
						visible int(10) DEFAULT '1',
						KEY privileges_id (privileges_id)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8",
					"CREATE TABLE IF NOT EXISTS vtiger_boxsoperation_privileges (
						privileges_id int(10) NOT NULL AUTO_INCREMENT,
						userid int(10) DEFAULT '0',
						operation int(10) DEFAULT '0',
						visible int(10) DEFAULT '1',
						KEY privileges_id (privileges_id)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8",
					"CREATE TABLE IF NOT EXISTS vtiger_box_score_data (
						box_score_dataid int(11) NOT NULL AUTO_INCREMENT,
						box_score varchar(255) DEFAULT NULL,
						objetivo varchar(255) DEFAULT NULL,
						cumplimiento varchar(255) DEFAULT NULL,
						tipo tinyint(2) DEFAULT '1',
						boxscoreid int(11) DEFAULT NULL,
						accountid int(11) DEFAULT NULL,
						description text,
						defaultplatzilla int(1) DEFAULT '0',
						querykpi text,
						querykpisemanal text,
						module varchar(30) NOT NULL,
						PRIMARY KEY (box_score_dataid)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8",
					'CREATE TABLE IF NOT EXISTS vtiger_box_score_data_cump (
						id int(10) NOT NULL AUTO_INCREMENT,
						cumplimiento varchar(50) DEFAULT NULL,
						box_score_dataid int(11) DEFAULT NULL,
						valor_varianza decimal(11,2) DEFAULT NULL,
						tipo_varianza varchar(50) DEFAULT NULL,
						valor_inferior decimal(11,2) DEFAULT NULL,
						valor_superior decimal(11,2) DEFAULT NULL,
						tipo_dato_inf varchar(50) DEFAULT NULL,
						tipo_dato_sup varchar(50) DEFAULT NULL,
						etiqueta varchar(255) DEFAULT NULL,
						box_score_objectiveid int(10) DEFAULT NULL,
						PRIMARY KEY (id)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8',
					'CREATE TABLE IF NOT EXISTS vtiger_box_score_data_semanal (
						semanalid int(11) NOT NULL AUTO_INCREMENT,
						box_score_dataid int(11) NOT NULL,
						boxscoreid int(11) DEFAULT NULL,
						accountid int(11) DEFAULT NULL,
						fecha date DEFAULT NULL,
						valor varchar(255) DEFAULT NULL,
						PRIMARY KEY (semanalid),
						KEY box_score_dataid (box_score_dataid),
						CONSTRAINT vtiger_box_score_data_semanal_ibfk_1 FOREIGN KEY (box_score_dataid) REFERENCES vtiger_box_score_data (box_score_dataid) ON DELETE CASCADE ON UPDATE NO ACTION
					) ENGINE=InnoDB DEFAULT CHARSET=utf8',
					'CREATE TABLE IF NOT EXISTS vtiger_box_score_objective (
						box_score_objectiveid int(10) NOT NULL AUTO_INCREMENT,
						box_score_dataid int(14) NOT NULL,
						objective varchar(255) DEFAULT NULL,
						month_apli varchar(50) DEFAULT NULL,
						date_from date DEFAULT NULL,
						date_end date DEFAULT NULL,
						cumplimiento varchar(255) DEFAULT NULL,
						operator varchar(50) DEFAULT NULL,
						PRIMARY KEY (box_score_objectiveid)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8',
				)
			);
		}

		private function buildSqlStatementsToDropTables () {
			return array (
				'DROP TABLE IF EXISTS vtiger_box_score_objective',
				'DROP TABLE IF EXISTS vtiger_box_score_data_semanal',
				'DROP TABLE IF EXISTS vtiger_box_score_data_cump',
				'DROP TABLE IF EXISTS vtiger_box_score_data',
				'DROP TABLE IF EXISTS vtiger_boxsoperation_privileges',
				'DROP TABLE IF EXISTS vtiger_boxscore_privileges',
				'DROP TABLE IF EXISTS vtiger_boxscore_operacion',
				'DROP TABLE IF EXISTS vtiger_boxscore_blocks',
			);
		}

		public function runPostInstallTasks (PearDatabase $adb) {
			$sqlStatements = $this->buildSqlStatementsToCreateTables ();
			if (empty ($sqlStatements)) {
				return;
			}
			$adb->query ('START TRANSACTION');
			foreach ($sqlStatements as $sqlStatement) {
				$adb->query ($sqlStatement);
			}
			$adb->query ('COMMIT');
		}

		public function runPreUninstallTasks (PearDatabase $adb) {
			$sqlStatements = $this->buildSqlStatementsToDropTables ();
			if (empty ($sqlStatements)) {
				return;
			}
			$adb->query ('START TRANSACTION');
			foreach ($sqlStatements as $sqlStatement) {
				$adb->query ($sqlStatement);
			}
			$adb->query ('COMMIT');
		}

		public static function getInstance () {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new self ();
			}
			return self::$INSTANCE;
		}

	}
