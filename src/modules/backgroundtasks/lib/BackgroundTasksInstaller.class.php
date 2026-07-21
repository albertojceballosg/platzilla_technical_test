<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');
	require_once ('modules/emailmanager/lib/EmailManagerUtils.class.php');

	class BackgroundTasksInstaller {
		const EMTEMPLATE_SEND_USER_CREDENTIALS                = 'Credenciales de acceso';
		const EMTEMPLATE_WELCOME                              = 'Bienvenida a Platzilla';
		const BGTASK_FETCH_USERS_EMAILS                       = '[CRON] - Obtener correos de los usuarios';
		const BGTASK_SEND_USER_CREDENTIALS                    = '[SYS] - Enviar credenciales de acceso';
		const BGTASK_SEND_VERIFICATION_CODE                   = '[SYS] - Enviar código de verificación';
		const BGTASK_SEND_WELCOME_NOTIFICATION                = '[SYS] - Enviar notificación de bienvenida';
		const BGTASK_UPDATE_INSTANCE_AFTER_STORE              = '[SYS] - Actualizar instancia por acción en tienda';
		const BGTASK_UPDATE_INSTANCE_ON_MANUAL_PAYMENT        = '[SYS] - Actualizar instancia por pago manual';
		const BGTASK_UPDATE_INSTANCE_ON_PAYMENT_METHOD_UPDATE = '[SYS] - Actualizar instancia por métodos de pago';
		const BGTASK_UPDATE_INSTANCE_ON_SCHEDULED_PAYMENTS    = '[CRON] - Ejecutar cobros pendientes';
		const BGTASK_UPDATE_INSTANCES                         = '[CRON] - Actualizar instancias';
		const CRON_TASK_NAME                                  = 'BackgroundTasksRunner';
		const MODULE_NAME                                     = 'backgroundtasks';

		private static $INSTANCE = null;

		private function buildSqlStatementsToCreateConfigurationTables () {
			$sqlStatements = $this->buildSqlStatementsToDropConfigurationTables ();
			return array_merge (
				$sqlStatements,
				array (
					'CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_cfg_actions` (
						`actiontype` varchar(25) NOT NULL,
						`scope` varchar(10) NOT NULL DEFAULT \'USER\',
						`handlerclass` varchar(255) NOT NULL,
						`handlermethod` varchar(255) NOT NULL,
						PRIMARY KEY (`actiontype`)
					) ENGINE=InnoDB',
					'CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_cfg_categories` (
						`categoryname` varchar(255) NOT NULL,
						`description` text NOT NULL,
						PRIMARY KEY (`categoryname`)
					) ENGINE=InnoDB',
					'CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_cfg_events` (
						`eventname` varchar(25) NOT NULL,
						`description` varchar(255) NOT NULL,
						`scope` varchar(10) NOT NULL,
						PRIMARY KEY (`eventname`)
					) ENGINE=InnoDB',
					'CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_cfg_parameters` (
						`actiontype` varchar(25) NOT NULL,
						`parametername` varchar(50) NOT NULL,
						`parameterorder` int(11) unsigned NOT NULL,
						`ismultivalued` tinyint(4) NOT NULL DEFAULT \'0\',
						`ismandatory` tinyint(4) NOT NULL DEFAULT \'0\',
						`refreshonchanges` tinyint(4) NOT NULL DEFAULT \'0\',
						`showexpanded` tinyint(4) NOT NULL DEFAULT \'0\',
						`defaultoptionstype` varchar(25) DEFAULT NULL,
						`defaultoptionsformula` text,
						`translationmodule` varchar(50) DEFAULT NULL,
						PRIMARY KEY (`actiontype`,`parametername`),
						UNIQUE KEY `actiontype_parameterorder` (`actiontype`,`parameterorder`),
						CONSTRAINT `FK_vtiger_bgtasks_cfg_parameters_vtiger_bgtasks_cfg_actions` FOREIGN KEY (`actiontype`) REFERENCES `vtiger_bgtasks_cfg_actions` (`actiontype`) ON DELETE CASCADE ON UPDATE CASCADE
					) ENGINE=InnoDB',
					'CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_cfg_parameteroptions` (
						`actiontype` varchar(25) NOT NULL,
						`parametername` varchar(50) NOT NULL,
						`parametertype` varchar(25) NOT NULL,
						PRIMARY KEY (`actiontype`,`parametername`,`parametertype`),
						CONSTRAINT `FK_bgtasks_cfg_parameteroptions_cfg_parameters` FOREIGN KEY (`actiontype`, `parametername`) REFERENCES `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`) ON DELETE CASCADE ON UPDATE CASCADE
					) ENGINE=InnoDB',
				)
			);
		}

		private function buildSqlStatementsToCreateDataTables () {
			$sqlStatements = $this->buildSqlStatementsToDropDataTables ();
			return array_merge (
				$sqlStatements,
				array (
					"CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_data` (
						`taskid` int(11) NOT NULL AUTO_INCREMENT,
						`taskname` varchar(50) NOT NULL,
						`description` text,
						`category` varchar(255) DEFAULT NULL,
						`scope` varchar(10) NOT NULL DEFAULT 'USER',
						`modulename` varchar(25) DEFAULT NULL,
						`trigger` varchar(15) NOT NULL,
						`event` varchar(25) DEFAULT NULL,
						`eventinstant` varchar(15) DEFAULT NULL,
						`taskstatus` varchar(15) NOT NULL,
						`frequency` bigint(20) DEFAULT NULL,
						`lastexecutedon` datetime DEFAULT NULL,
						`locked` TINYINT(1) NOT NULL DEFAULT '0',
						PRIMARY KEY (`taskid`),
						UNIQUE KEY `taskname` (`taskname`),
						KEY `trigger` (`trigger`),
						KEY `taskstatus` (`taskstatus`),
						KEY `trigger_event_eventinstant_taskstatus` (`taskstatus`,`trigger`,`event`,`eventinstant`),
						KEY `trigger_frequency_lastexecutedon` (`taskstatus`,`trigger`,`lastexecutedon`),
						KEY `modulename_locked_taskid` (`modulename`, `locked`, `taskid`),
						KEY `FK_vtiger_bgtasks_data_vtiger_bgtasks_cfg_events` (`event`),
						KEY `FK_vtiger_bgtasks_data_vtiger_tab` (`modulename`),
						KEY `FK_vtiger_bgtasks_data_vtiger_bgtasks_cfg_categories` (`category`),
						CONSTRAINT `FK_vtiger_bgtasks_data_vtiger_bgtasks_cfg_categories` FOREIGN KEY (`category`) REFERENCES `vtiger_bgtasks_cfg_categories` (`categoryname`) ON DELETE CASCADE ON UPDATE CASCADE,
						CONSTRAINT `FK_vtiger_bgtasks_data_vtiger_bgtasks_cfg_events` FOREIGN KEY (`event`) REFERENCES `vtiger_bgtasks_cfg_events` (`eventname`) ON DELETE CASCADE ON UPDATE CASCADE,
						CONSTRAINT `FK_vtiger_bgtasks_data_vtiger_tab` FOREIGN KEY (`modulename`) REFERENCES `vtiger_tab` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
					) ENGINE=InnoDB",
					'CREATE TABLE `vtiger_bgtasks_data_filtergroups` (
						`taskid` INT(11) NOT NULL,
						`groupid` INT(11) NOT NULL,
						`operator` VARCHAR(15) NULL DEFAULT NULL,
						PRIMARY KEY (`taskid`, `groupid`),
						CONSTRAINT `FK_vtiger_bgtasks_filtergroups_vtiger_bgtasks_data` FOREIGN KEY (`taskid`) REFERENCES `vtiger_bgtasks_data` (`taskid`) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE=InnoDB',
					'CREATE TABLE `vtiger_bgtasks_data_filters` (
						`taskid` INT(11) NOT NULL,
						`groupid` INT(11) NOT NULL,
						`sequence` INT(11) NOT NULL,
						`modulename` VARCHAR(25) NOT NULL,
						`fieldname` VARCHAR(50) NOT NULL,
						`label` VARCHAR(255) NOT NULL,
						`comparator` VARCHAR(25) NOT NULL,
						`value` VARCHAR(255) NULL DEFAULT NULL,
						`operator` VARCHAR(3) NULL DEFAULT NULL,
						PRIMARY KEY (`taskid`, `groupid`, `sequence`),
						INDEX `modulename_fieldname` (`modulename`, `fieldname`),
						CONSTRAINT `FK_vtiger_bgtasks_data_filters_vtiger_bgtasks_data_filtergroups` FOREIGN KEY (`taskid`, `groupid`) REFERENCES `vtiger_bgtasks_data_filtergroups` (`taskid`, `groupid`) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE=InnoDB',
					'CREATE TABLE `vtiger_bgtasks_data_actions` (
						`taskid` INT(11) NOT NULL,
						`actionname` VARCHAR(50) NOT NULL,
						`actiontype` VARCHAR(25) NOT NULL,
						`actionorder` INT(11) UNSIGNED NOT NULL,
						PRIMARY KEY (`taskid`, `actionname`),
						INDEX `FK_vtiger_bgtasks_data_actions_vtiger_bgtasks_cfg_actions` (`actiontype`),
						CONSTRAINT `FK_vtiger_bgtasks_data_actions_vtiger_bgtasks_cfg_actions` FOREIGN KEY (`actiontype`) REFERENCES `vtiger_bgtasks_cfg_actions` (`actiontype`) ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT `FK_vtiger_bgtasks_data_actions_vtiger_bgtasks_data` FOREIGN KEY (`taskid`) REFERENCES `vtiger_bgtasks_data` (`taskid`) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE=InnoDB',
					'CREATE TABLE `vtiger_bgtasks_data_parameters` (
						`taskid` INT(11) NOT NULL,
						`actionname` VARCHAR(50) NOT NULL,
						`parametername` VARCHAR(50) NOT NULL,
						`expandedkey` VARCHAR(255) NOT NULL,
						`actiontype` VARCHAR(25) NOT NULL,
						`parametertype` VARCHAR(255) NULL DEFAULT NULL,
						`parameterformula` TEXT NULL,
						PRIMARY KEY (`taskid`, `actionname`, `parametername`, `expandedkey`),
						INDEX `FK_vtiger_bgtasks_data_parameters_vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`),
						CONSTRAINT `FK_vtiger_bgtasks_data_parameters_vtiger_bgtasks_cfg_parameters` FOREIGN KEY (`actiontype`, `parametername`) REFERENCES `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`) ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT `FK_vtiger_bgtasks_data_parameters_vtiger_bgtasks_data_actions` FOREIGN KEY (`taskid`, `actionname`) REFERENCES `vtiger_bgtasks_data_actions` (`taskid`, `actionname`) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE=InnoDB',
				)
			);
		}

		private function buildSqlStatementsToDropConfigurationTables () {
			return array (
				'DROP TABLE IF EXISTS vtiger_bgtasks_cfg_parameteroptions',
				'DROP TABLE IF EXISTS vtiger_bgtasks_cfg_parameters',
				'DROP TABLE IF EXISTS vtiger_bgtasks_cfg_actions',
				'DROP TABLE IF EXISTS vtiger_bgtasks_cfg_events',
			);
		}

		private function buildSqlStatementsToDropDataTables () {
			return array (
				'DROP TABLE IF EXISTS vtiger_bgtasks_data_parameters',
				'DROP TABLE IF EXISTS vtiger_bgtasks_data_actions',
				'DROP TABLE IF EXISTS vtiger_bgtasks_data_filters',
				'DROP TABLE IF EXISTS vtiger_bgtasks_data_filtergroups',
				'DROP TABLE IF EXISTS vtiger_bgtasks_data',
			);
		}

		private function buildSqlStatementsToPopulateConfigurationTables ($isMasterPlatform) {
			if ($isMasterPlatform) {
				$sqlStatements = array (
					"INSERT INTO `vtiger_bgtasks_cfg_events` (`eventname`, `description`, `scope`) VALUES ('INSTANCE ASSIGNMENT', 'Al asignar instancia a un usuario que da de alta', 'SYSTEM')",
					"INSERT INTO `vtiger_bgtasks_cfg_events` (`eventname`, `description`, `scope`) VALUES ('MANUAL PAYMENT', 'Al realizar un pago manual', 'SYSTEM')",
					"INSERT INTO `vtiger_bgtasks_cfg_events` (`eventname`, `description`, `scope`) VALUES ('STORE OPERATION', 'Al realizar una operación en tienda (agregar/eliminar aplicaciones', 'SYSTEM')",
					"INSERT INTO `vtiger_bgtasks_cfg_events` (`eventname`, `description`, `scope`) VALUES ('UPDATE PAYMENT METHODS', 'Al realizar una actualización de los métodos de pago afiliados', 'SYSTEM')",
				);
			} else {
				$sqlStatements = array ();
			}
			$sqlStatements = array_merge (
				$sqlStatements,
				array (
					"INSERT INTO `vtiger_bgtasks_cfg_events` (`eventname`, `description`, `scope`) VALUES ('CREATE', 'Al guardar un registro nuevo', 'USER')",
					"INSERT INTO `vtiger_bgtasks_cfg_events` (`eventname`, `description`, `scope`) VALUES ('DELETE', 'Al eliminar un registro', 'USER')",
					"INSERT INTO `vtiger_bgtasks_cfg_events` (`eventname`, `description`, `scope`) VALUES ('EDIT', 'Al editar un registro (clic en el botón editar)', 'USER')",
					"INSERT INTO `vtiger_bgtasks_cfg_events` (`eventname`, `description`, `scope`) VALUES ('MODIFY', 'Al guardar un registro existente', 'USER')",
					"INSERT INTO `vtiger_bgtasks_cfg_events` (`eventname`, `description`, `scope`) VALUES ('READ', 'Al acceder a un registro en modo sólo lectura', 'USER')",
					"INSERT INTO `vtiger_bgtasks_cfg_events` (`eventname`, `description`, `scope`) VALUES ('SAVE', 'Al guardar un registro nuevo o existente', 'USER')",
				)
			);

			$sqlStatements = array_merge (
				$sqlStatements,
				array (
					"INSERT INTO `vtiger_bgtasks_cfg_categories` (`categoryname`, `description`) VALUES ('Actualizaciones', 'Actualiza registros existentes en Platzilla')",
					"INSERT INTO `vtiger_bgtasks_cfg_categories` (`categoryname`, `description`) VALUES ('Asignaciones de registros', 'Asigna registros de Platzilla a miembros de tu equipo')",
					"INSERT INTO `vtiger_bgtasks_cfg_categories` (`categoryname`, `description`) VALUES ('Creación de registros', 'Crea nuevos registros en Platzilla')",
					"INSERT INTO `vtiger_bgtasks_cfg_categories` (`categoryname`, `description`) VALUES ('Envío de notificaciones', 'Envía notificaciones a otros miembros del equipo o a cualquier persona')",
					"INSERT INTO `vtiger_bgtasks_cfg_categories` (`categoryname`, `description`) VALUES ('Extraer datos', 'Extrae datos')",
					"INSERT INTO `vtiger_bgtasks_cfg_categories` (`categoryname`, `description`) VALUES ('Verificaciones', 'Ejecuta acciones en caso de que se cumpla alguna condición')",
				)
			);

			if ($isMasterPlatform) {
				$sqlStatements = array_merge (
					$sqlStatements,
					array (
						"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('SYNCHRONIZE ENTITIES', 'SYSTEM', 'SynchronizeEntitiesAction', 'run')",
						"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('SYNCHRONIZE PAYMENT DATA', 'SYSTEM', 'SynchronizePaymentGatewayDataAction', 'run')",
						"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('UPDATE CUSTOMER CHARGES', 'SYSTEM', 'UpdateCustomerChargesAction', 'run')",
						"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('UPDATE CUSTOMER INVOICES', 'SYSTEM', 'UpdateCustomerInvoicesAction', 'run')",
						"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('UPDATE CUSTOMER SERVICES', 'SYSTEM', 'UpdateCustomerServicesAction', 'run')",
						"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('UPDATE INSTANCES', 'SYSTEM', 'UpdateInstancesAction', 'run')",
						"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('UPDATE SUBSCRIPTION', 'SYSTEM', 'UpdateCustomerSubscriptionAction', 'run')",
					)
				);
			}
			$sqlStatements = array_merge (
				$sqlStatements,
				array (
					"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('CREATE ENTITY', 'USER', 'CreateEntityAction', 'run')",
					"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('FETCH USERS EMAILS', 'USER', 'FetchUsersEmailsAction', 'run')",
					"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('MODIFY ENTITY', 'USER', 'ModifyEntityAction', 'run')",
					"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('SEND EMAIL', 'USER', 'SendEmailAction', 'run')",
					"INSERT INTO `vtiger_bgtasks_cfg_actions` (`actiontype`, `scope`, `handlerclass`, `handlermethod`) VALUES ('CREATE SCHEDULED ENTITIES', 'USER', 'CreateScheduledEntitiesAction', 'run')",
				)
			);

			if ($isMasterPlatform) {
				$sqlStatements = array_merge (
					$sqlStatements,
					array (
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('SYNCHRONIZE ENTITIES', 'sourceentityid', 3, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('SYNCHRONIZE ENTITIES', 'sourceinstancename', 1, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('SYNCHRONIZE ENTITIES', 'sourcemodulename', 2, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('SYNCHRONIZE ENTITIES', 'targetentityid', 6, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('SYNCHRONIZE ENTITIES', 'targetinstancename', 4, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('SYNCHRONIZE ENTITIES', 'targetmodulename', 5, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('SYNCHRONIZE PAYMENT DATA', 'instancecode', 1, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('UPDATE CUSTOMER CHARGES', 'instancecode', 1, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('UPDATE CUSTOMER INVOICES', 'daysbeforeduedate', 2, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('UPDATE CUSTOMER INVOICES', 'instancecode', 1, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('UPDATE CUSTOMER SERVICES', 'demostartdate', 3, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('UPDATE CUSTOMER SERVICES', 'gracedays', 4, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('UPDATE CUSTOMER SERVICES', 'instancecode', 1, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('UPDATE CUSTOMER SERVICES', 'servicesstartdate', 2, 0, 1, 0, 0, NULL, NULL, NULL)",
						"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('UPDATE SUBSCRIPTION', 'instancecode', 1, 0, 1, 0, 0, NULL, NULL, NULL)",
					)
				);
			}
			$sqlStatements = array_merge (
				$sqlStatements,
				array (
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE ENTITY', 'fieldnames', 2, 0, 0, 0, 1, 'SQL', 'SELECT f.fieldname, f.fieldlabel FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid WHERE f.uitype NOT IN (4, 52, 70, 404, 2202) AND t.name=\'[modulename]\'', NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE ENTITY', 'modulename', 1, 0, 1, 1, 0, 'SQL', 'SELECT name, CONCAT(tablabel, \' (\', name, \')\') AS tablabel FROM vtiger_tab WHERE presence=0 AND isentitytype=1 ORDER BY tablabel', NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('FETCH USERS EMAILS', 'instancecode', 1, 0, 1, 0, 0, NULL, NULL, NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('MODIFY ENTITY', 'entityid', 2, 0, 1, 0, 0, NULL, NULL, NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('MODIFY ENTITY', 'fieldnames', 3, 0, 0, 0, 1, 'SQL', 'SELECT f.fieldname AS optionvalue, f.fieldlabel AS optionlabel FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid WHERE f.uitype NOT IN (4, 52, 70, 404, 2202) AND t.name=\'[modulename]\'', NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('MODIFY ENTITY', 'modulename', 1, 0, 1, 1, 0, 'SQL', 'SELECT name, CONCAT(tablabel, \' (\', name, \')\') AS tablabel FROM vtiger_tab WHERE presence=0 AND isentitytype=1 ORDER BY tablabel', NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('SEND EMAIL', 'language', 1, 0, 1, 1, 0, 'JSON', '{\"es\":\"Espa\\u00f1ol\",\"en\":\"Ingl\\u00e9s\",\"pt\":\"Portugu\\u00e9s\"}', NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('SEND EMAIL', 'recipients', 3, 1, 0, 0, 0, NULL, NULL, NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('SEND EMAIL', 'templatename', 2, 0, 1, 1, 0, 'SQL', 'SELECT templatename AS optionvalue, templatename AS optionlabel FROM vtiger_emailmanager_templates WHERE language=\'[language]\'', NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('SEND EMAIL', 'variables', 4, 0, 0, 0, 1, 'HANDLER', NULL, NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'enddatefieldname', 10, 0, 1, 0, 0, NULL, NULL, 'backgroundtask')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'fieldnames', 11, 0, 0, 0, 1, 'SQL', 'SELECT f.fieldname, f.fieldlabel FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid WHERE f.uitype NOT IN (4, 52, 70, 404, 2202) AND t.name=\'[modulename]\'', NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'frequencyfieldname', 2, 1, 1, 0, 0, NULL, NULL, 'backgroundtask')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'modulename', 1, 0, 1, 1, 0, 'SQL', 'SELECT name, CONCAT(tablabel, \' (\', name, \')\') AS tablabel FROM vtiger_tab WHERE presence=0 AND isentitytype=1 ORDER BY tablabel', NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'startdatefieldname', 9, 0, 1, 0, 0, NULL, NULL, 'backgroundtask')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'weekdaysfieldname', 4, 0, 1, 0, 0, NULL, NULL, 'backgroundtask')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'monthdaysfieldname', 7, 0, 1, 0, 0, NULL, NULL, 'backgroundtask')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'lfortnightlyfieldname', 5, 0, 0, 0, 0, NULL, NULL, 'backgroundtask')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'llfortnightlyfieldname', 6, 0, 0, 0, 0, NULL, NULL, 'backgroundtask')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'MesAnualidadfieldname', 8, 0, 0, 0, 0, NULL, NULL, 'backgroundtask')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'CadaCuantofieldname', 3, 0, 0, 0, 0, NULL, NULL, 'backgroundtask')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'gridfieldnames', 12, 0, 0, 0, 0, NULL, NULL, NULL)",
					"INSERT INTO `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`, `parameterorder`, `ismultivalued`, `ismandatory`, `refreshonchanges`, `showexpanded`, `defaultoptionstype`, `defaultoptionsformula`, `translationmodule`) VALUES ('CREATE SCHEDULED ENTITIES', 'relateto', 13, 0, 0, 0, 0, NULL, NULL, NULL)"
				)
			);

			if ($isMasterPlatform) {
				$sqlStatements = array_merge (
					$sqlStatements,
					array (
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SYNCHRONIZE PAYMENT DATA', 'instancecode', 'CUSTOM SQL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SYNCHRONIZE PAYMENT DATA', 'instancecode', 'LITERAL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SYNCHRONIZE PAYMENT DATA', 'instancecode', 'SOURCE FIELD')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SYNCHRONIZE PAYMENT DATA', 'instancecode', 'VARIABLE')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER CHARGES', 'instancecode', 'CUSTOM SQL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER CHARGES', 'instancecode', 'LITERAL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER CHARGES', 'instancecode', 'SOURCE FIELD')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER CHARGES', 'instancecode', 'VARIABLE')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER INVOICES', 'instancecode', 'CUSTOM SQL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER INVOICES', 'instancecode', 'LITERAL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER INVOICES', 'instancecode', 'SOURCE FIELD')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER INVOICES', 'instancecode', 'VARIABLE')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'demostartdate', 'CUSTOM SQL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'demostartdate', 'LITERAL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'demostartdate', 'SOURCE FIELD')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'demostartdate', 'VARIABLE')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'instancecode', 'CUSTOM SQL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'instancecode', 'LITERAL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'instancecode', 'SOURCE FIELD')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'instancecode', 'VARIABLE')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'servicesstartdate', 'CUSTOM SQL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'servicesstartdate', 'LITERAL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'servicesstartdate', 'SOURCE FIELD')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE CUSTOMER SERVICES', 'servicesstartdate', 'VARIABLE')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE SUBSCRIPTION', 'instancecode', 'CUSTOM SQL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE SUBSCRIPTION', 'instancecode', 'LITERAL')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE SUBSCRIPTION', 'instancecode', 'SOURCE FIELD')",
						"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('UPDATE SUBSCRIPTION', 'instancecode', 'VARIABLE')",
					)
				);
			}
			$sqlStatements = array_merge (
				$sqlStatements,
				array (
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('CREATE ENTITY', 'fieldnames', 'CUSTOM SQL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('CREATE ENTITY', 'fieldnames', 'LITERAL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('CREATE ENTITY', 'fieldnames', 'RELATED SOURCE FIELD')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('CREATE ENTITY', 'fieldnames', 'SOURCE FIELD')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('CREATE ENTITY', 'fieldnames', 'VARIABLE')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('FETCH USERS EMAILS', 'instancecode', 'CUSTOM SQL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('FETCH USERS EMAILS', 'instancecode', 'LITERAL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('FETCH USERS EMAILS', 'instancecode', 'SOURCE FIELD')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('FETCH USERS EMAILS', 'instancecode', 'VARIABLE')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('MODIFY ENTITY', 'entityid', 'CUSTOM SQL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('MODIFY ENTITY', 'entityid', 'LITERAL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('MODIFY ENTITY', 'entityid', 'SOURCE FIELD')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('MODIFY ENTITY', 'entityid', 'VARIABLE')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('MODIFY ENTITY', 'fieldnames', 'CUSTOM SQL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('MODIFY ENTITY', 'fieldnames', 'LITERAL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('MODIFY ENTITY', 'fieldnames', 'VARIABLE')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SEND EMAIL', 'recipients', 'CUSTOM SQL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SEND EMAIL', 'recipients', 'LITERAL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SEND EMAIL', 'recipients', 'SOURCE FIELD')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SEND EMAIL', 'recipients', 'VARIABLE')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SEND EMAIL', 'variables', 'CUSTOM SQL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SEND EMAIL', 'variables', 'LITERAL')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SEND EMAIL', 'variables', 'RELATED SOURCE FIELD')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SEND EMAIL', 'variables', 'SOURCE FIELD')",
					"INSERT INTO `vtiger_bgtasks_cfg_parameteroptions` (`actiontype`, `parametername`, `parametertype`) VALUES ('SEND EMAIL', 'variables', 'VARIABLE')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'enddatefieldname', 'SOURCE FIELD')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'fieldnames', 'CALCULATED DATE')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'fieldnames', 'CUSTOM SQL')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'fieldnames', 'LITERAL')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'fieldnames', 'SOURCE FIELD')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'fieldnames', 'VARIABLE')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'frequencyfieldname', 'SOURCE FIELD')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'startdatefieldname', 'SOURCE FIELD')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'weekdaysfieldname', 'SOURCE FIELD')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'monthdaysfieldname', 'SOURCE FIELD')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'lfortnightlyfieldname', 'SOURCE FIELD')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'llfortnightlyfieldname', 'SOURCE FIELD')",
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'MesAnualidadfieldname', 'SOURCE FIELD')",	
					"INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES ('CREATE SCHEDULED ENTITIES', 'CadaCuantofieldname', 'SOURCE FIELD')",
				)
			);

			return $sqlStatements;
		}

		private function buildSqlStatementsToRegisterAsSettingsModule () {
			$sqlStatements = array (
				'SET @BlockID := NULL',
				'SET @FieldID := NULL',
				"SELECT @BlockID:=blockid FROM vtiger_settings_blocks WHERE label='LBL_ADMINISTRATION'",
				"INSERT INTO vtiger_settings_blocks (blockid, label, sequence)
					SELECT IFNULL(MAX(blockid), 0) + 1, 'LBL_ADMINISTRATION', IFNULL(MAX(sequence), 0) + 1 FROM vtiger_settings_blocks HAVING @BlockID IS NULL",
				'SELECT @BlockId:=IFNULL(@BlockId, LAST_INSERT_ID())',
				'UPDATE vtiger_settings_blocks_seq SET id=(SELECT MAX(blockid) FROM vtiger_settings_blocks)',

				"SELECT @FieldID:=fieldid FROM vtiger_settings_field WHERE blockid=@BlockId AND name='LBL_BACKGROUND_TASKS_NAME'",
				"INSERT INTO vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence)
					SELECT IFNULL(GREATEST(MAX(fs.id), MAX(f.fieldid)), 0) + 1, @BlockID, 'LBL_BACKGROUND_TASKS_NAME', 'fa fa-cogs purple-bg', 'LBL_BACKGROUND_TASKS_DESCRIPTION', 'index.php?module=backgroundtasks&action=index&parenttab=Settings', IFNULL(MAX(f.sequence), 0) + 1 FROM vtiger_settings_field_seq fs, vtiger_settings_field f WHERE f.blockid=@BlockID HAVING @FieldID IS NULL",
				"UPDATE vtiger_settings_field SET name='LBL_BACKGROUND_TASKS_NAME', iconpath='fa fa-cogs purple-bg', description='LBL_BACKGROUND_TASKS_DESCRIPTION', linkto='index.php?module=backgroundtasks&action=index&parenttab=Settings' WHERE @FieldId IS NOT NULL AND fieldid=@FieldId AND blockid=@BlockId",
				'UPDATE vtiger_settings_field_seq SET id=(SELECT MAX(fieldid) FROM vtiger_settings_field)',
			);
			return $sqlStatements;
		}

		private function buildSqlStatementsToRegisterCronTask () {
			$cronTaskName = self::CRON_TASK_NAME;
			$moduleName   = self::MODULE_NAME;
			return array (
				"DELETE FROM vtiger_cron_task WHERE name='{$cronTaskName}'",
				"INSERT INTO vtiger_cron_task (name, handler_file, frequency, status, module, description) VALUES ('{$cronTaskName}', 'cron/modules/backgroundtasks/RunScheduledTasks.service.php', 0, 1, '{$moduleName}', 'Ejecuta las tareas en segundo plano configuradas en el módulo backgroundtasks')",
			);
		}

		private function buildSqlStatementsToUnregisterAsSettingsModule () {
			$sqlStatements = array (
				"DELETE FROM vtiger_settings_field WHERE name='LBL_BACKGROUND_TASKS_NAME'",
				'UPDATE vtiger_settings_field_seq SET id=(SELECT MAX(fieldid) FROM vtiger_settings_field)',
			);
			return $sqlStatements;
		}

		private function buildSqlStatementsToUnregisterCronTask () {
			$cronTaskName = self::CRON_TASK_NAME;
			return array ("DELETE FROM vtiger_cron_task WHERE name='{$cronTaskName}'");
		}

		/**
		 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
		 * @param PearDatabase $adb
		 */
		private function registerBackgroundTasks (PearDatabase $adb) {
			BackgroundTasksUtils::saveTask (
				$adb,
				array (
					'event'        => null,
					'eventinstant' => null,
					'frequency'    => 0,
					'filtergroups' => null,
					'modulename'   => 'instances',
					'scope'        => BackgroundTaskInterface::SCOPE_SYSTEM,
					'taskname'     => self::BGTASK_SEND_USER_CREDENTIALS,
					'taskstatus'   => BackgroundTaskInterface::STATUS_ENABLED,
					'trigger'      => BackgroundTaskInterface::TRIGGER_MANUAL,
					'actions'      => array (
						array (
							'actionname' => 'Enviar credenciales de acceso',
							'actiontype' => 'SEND EMAIL',
							'parameters' => array (
								'language'     => 'es',
								'recipients'   => array ('type' => BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD, 'value' => 'usuario'),
								'templatename' => self::EMTEMPLATE_SEND_USER_CREDENTIALS,
								'variables'    => array (
									'NOMBRE_DEL_USUARIO'          => array ('type' => BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL, 'value' => "SELECT TRIM(CONCAT(c.nombre, ' ', c.apellidos)) AS fullname FROM vtiger_instances i INNER JOIN vtiger_contactos c ON c.email=i.administrator WHERE i.code='[code]' LIMIT 1"),
									'URL_RECUPERACION_CONTRASEÑA' => array ('type' => BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL, 'value' => "SELECT CONCAT('{PLATZILLA_ROOT_URI}/reset-password.php?token=', MD5(i.code)) AS url FROM vtiger_instances i WHERE i.code='[code]' LIMIT 1"),
								),
							),
						),
					),
				)
			);
			BackgroundTasksUtils::saveTask (
				$adb,
				array (
					'event'        => null,
					'eventinstant' => null,
					'frequency'    => 0,
					'filtergroups' => null,
					'modulename'   => 'instances',
					'scope'        => BackgroundTaskInterface::SCOPE_SYSTEM,
					'taskname'     => self::BGTASK_SEND_VERIFICATION_CODE,
					'taskstatus'   => BackgroundTaskInterface::STATUS_ENABLED,
					'trigger'      => BackgroundTaskInterface::TRIGGER_MANUAL,
					'actions'      => array (
						array (
							'actionname' => 'Enviar código de verificación',
							'actiontype' => 'SEND EMAIL',
							'parameters' => array (
								'language'     => 'es',
								'recipients'   => array ('type' => BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD, 'value' => 'usuario'),
								'templatename' => self::EMTEMPLATE_WELCOME,
								'variables'    => array (
									'CÓDIGO_DE_VERIFICACIÓN' => array ('type' => BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD, 'value' => 'verificationcode'),
									'NOMBRE_DEL_USUARIO'     => array ('type' => BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL, 'value' => "SELECT TRIM(CONCAT(c.nombre, ' ', c.apellidos)) AS fullname FROM vtiger_instances i INNER JOIN vtiger_contactos c ON c.email=i.administrator WHERE i.code='[code]' LIMIT 1"),
									'URL_DE_VERIFICACIÓN'    => array ('type' => BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE, 'value' => '{PLATZILLA_ROOT_URI}/index.php?module=Settings&action=codeverification&mode=geturl&codigo=|verificationcode|'),
								),
							),
						),
					),
				)
			);
			BackgroundTasksUtils::saveTask (
				$adb,
				array (
					'event'        => 'INSTANCE ASSIGNMENT',
					'eventinstant' => BackgroundTaskInterface::EVENT_INSTANT_AFTER,
					'frequency'    => 0,
					'filtergroups' => null,
					'modulename'   => 'instances',
					'scope'        => BackgroundTaskInterface::SCOPE_SYSTEM,
					'taskname'     => self::BGTASK_SEND_WELCOME_NOTIFICATION,
					'taskstatus'   => BackgroundTaskInterface::STATUS_ENABLED,
					'trigger'      => BackgroundTaskInterface::TRIGGER_EVENT,
					'actions'      => array (
						array (
							'actionname' => 'Enviar notificación de bienvenida',
							'actiontype' => 'SEND EMAIL',
							'parameters' => array (
								'language'     => 'es',
								'recipients'   => array ('type' => BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD, 'value' => 'usuario'),
								'templatename' => self::EMTEMPLATE_WELCOME,
								'variables'    => array (
									'CÓDIGO_DE_VERIFICACIÓN' => array ('type' => BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD, 'value' => 'verificationcode'),
									'NOMBRE_DEL_USUARIO'     => array ('type' => BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL, 'value' => "SELECT TRIM(CONCAT(c.nombre, ' ', c.apellidos)) AS fullname FROM vtiger_instances i INNER JOIN vtiger_contactos c ON c.email=i.administrator WHERE i.code='[code]' LIMIT 1"),
									'URL_DE_VERIFICACIÓN'    => array ('type' => BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE, 'value' => '{PLATZILLA_ROOT_URI}/index.php?module=Settings&action=codeverification&mode=geturl&codigo=|verificationcode|'),
								),
							),
						),
					),
				)
			);
			BackgroundTasksUtils::saveTask (
				$adb,
				array (
					'event'        => 'STORE OPERATION',
					'eventinstant' => BackgroundTaskInterface::EVENT_INSTANT_AFTER,
					'frequency'    => 0,
					'filtergroups' => null,
					'modulename'   => 'instances',
					'scope'        => BackgroundTaskInterface::SCOPE_SYSTEM,
					'taskname'     => self::BGTASK_UPDATE_INSTANCE_AFTER_STORE,
					'taskstatus'   => BackgroundTaskInterface::STATUS_ENABLED,
					'trigger'      => BackgroundTaskInterface::TRIGGER_EVENT,
					'actions'      => array (
						array (
							'actionname' => 'Actualizar suscripción en la pasarela de pagos',
							'actiontype' => 'UPDATE SUBSCRIPTION',
						),
						array (
							'actionname' => 'Actualizar cobros pendientes',
							'actiontype' => 'UPDATE CUSTOMER CHARGES',
						),
						array (
							'actionname' => 'Sincronizar pagos con la pasarela de pagos',
							'actiontype' => 'SYNCHRONIZE PAYMENT DATA',
						),
						array (
							'actionname' => 'Actualizar disponibilidad de servicios del cliente',
							'actiontype' => 'UPDATE CUSTOMER SERVICES',
							'parameters' => array ('gracedays' => '2'),
						),
						array (
							'actionname' => 'Actualizar facturas del cliente',
							'actiontype' => 'UPDATE CUSTOMER INVOICES',
							'parameters' => array ('daysbeforeduedate' => '3'),
						),
					),
				)
			);
			BackgroundTasksUtils::saveTask (
				$adb,
				array (
					'event'        => 'MANUAL PAYMENT',
					'eventinstant' => BackgroundTaskInterface::EVENT_INSTANT_AFTER,
					'frequency'    => 0,
					'filtergroups' => null,
					'modulename'   => 'instances',
					'scope'        => BackgroundTaskInterface::SCOPE_SYSTEM,
					'taskname'     => self::BGTASK_UPDATE_INSTANCE_ON_MANUAL_PAYMENT,
					'taskstatus'   => BackgroundTaskInterface::STATUS_ENABLED,
					'trigger'      => BackgroundTaskInterface::TRIGGER_EVENT,
					'actions'      => array (
						array (
							'actionname' => 'Sincronizar pagos con la pasarela de pagos',
							'actiontype' => 'SYNCHRONIZE PAYMENT DATA',
						),
						array (
							'actionname' => 'Actualizar disponibilidad de servicios del cliente',
							'actiontype' => 'UPDATE CUSTOMER SERVICES',
							'parameters' => array ('gracedays' => '0'),
						),
						array (
							'actionname' => 'Actualizar facturas del cliente',
							'actiontype' => 'UPDATE CUSTOMER INVOICES',
							'parameters' => array ('daysbeforeduedate' => '0'),
						),
					),
				)
			);
			BackgroundTasksUtils::saveTask (
				$adb,
				array (
					'event'        => 'UPDATE PAYMENT METHODS',
					'eventinstant' => BackgroundTaskInterface::EVENT_INSTANT_AFTER,
					'frequency'    => 0,
					'filtergroups' => null,
					'modulename'   => 'instances',
					'scope'        => BackgroundTaskInterface::SCOPE_SYSTEM,
					'taskname'     => self::BGTASK_UPDATE_INSTANCE_ON_PAYMENT_METHOD_UPDATE,
					'taskstatus'   => BackgroundTaskInterface::STATUS_ENABLED,
					'trigger'      => BackgroundTaskInterface::TRIGGER_EVENT,
					'actions'      => array (
						array (
							'actionname' => 'Actualizar suscripción en la pasarela de pagos',
							'actiontype' => 'UPDATE SUBSCRIPTION',
						),
						array (
							'actionname' => 'Actualizar cobros pendientes',
							'actiontype' => 'UPDATE CUSTOMER CHARGES',
						),
						array (
							'actionname' => 'Actualizar facturas del cliente',
							'actiontype' => 'UPDATE CUSTOMER INVOICES',
							'parameters' => array ('daysbeforeduedate' => '3'),
						),
					),
				)
			);
			BackgroundTasksUtils::saveTask (
				$adb,
				array (
					'event'        => null,
					'eventinstant' => null,
					'frequency'    => 0,
					'filtergroups' => null,
					'modulename'   => 'instances',
					'scope'        => BackgroundTaskInterface::SCOPE_SYSTEM,
					'taskname'     => self::BGTASK_UPDATE_INSTANCES,
					'taskstatus'   => BackgroundTaskInterface::STATUS_ENABLED,
					'trigger'      => BackgroundTaskInterface::TRIGGER_SCHEDULE,
					'actions'      => array (
						array (
							'actionname' => 'Actualizar instancias',
							'actiontype' => 'UPDATE INSTANCES',
						),
					),
				)
			);
			BackgroundTasksUtils::saveTask (
				$adb,
				array (
					'event'        => null,
					'eventinstant' => null,
					'frequency'    => 0,
					'filtergroups' => null,
					'modulename'   => 'instances',
					'scope'        => BackgroundTaskInterface::SCOPE_SYSTEM,
					'taskname'     => self::BGTASK_UPDATE_INSTANCE_ON_SCHEDULED_PAYMENTS,
					'taskstatus'   => BackgroundTaskInterface::STATUS_ENABLED,
					'trigger'      => BackgroundTaskInterface::TRIGGER_SCHEDULE,
					'actions'      => array (
						array (
							'actionname' => 'Actualizar cobros pendientes',
							'actiontype' => 'UPDATE CUSTOMER CHARGES',
						),
						array (
							'actionname' => 'Actualizar suscripción en la pasarela de pagos',
							'actiontype' => 'UPDATE SUBSCRIPTION',
						),
						array (
							'actionname' => 'Sincronizar pagos con la pasarela de pagos',
							'actiontype' => 'SYNCHRONIZE PAYMENT DATA',
						),
						array (
							'actionname' => 'Actualizar disponibilidad de servicios del cliente',
							'actiontype' => 'UPDATE CUSTOMER SERVICES',
							'parameters' => array ('gracedays' => '2'),
						),
						array (
							'actionname' => 'Actualizar facturas del cliente',
							'actiontype' => 'UPDATE CUSTOMER INVOICES',
							'parameters' => array ('daysbeforeduedate' => '3'),
						),
					),
				)
			);
			BackgroundTasksUtils::saveTask (
				$adb,
				array (
					'event'        => null,
					'eventinstant' => null,
					'frequency'    => 0,
					'filtergroups' => null,
					'modulename'   => 'instances',
					'scope'        => BackgroundTaskInterface::SCOPE_SYSTEM,
					'taskname'     => self::BGTASK_FETCH_USERS_EMAILS,
					'taskstatus'   => BackgroundTaskInterface::STATUS_ENABLED,
					'trigger'      => BackgroundTaskInterface::TRIGGER_SCHEDULE,
					'actions'      => array (
						array (
							'actionname' => 'Obtener correos',
							'actiontype' => 'FETCH USERS EMAILS',
						),
					),
				)
			);
		}

		private function registerEmailTemplates (PearDatabase $adb) {
			EmailManagerUtils::saveTemplate (
				$adb,
				array (
					'templatename'     => self::EMTEMPLATE_WELCOME,
					'language'         => 'es',
					'subject'          => '[Platzilla] Bienvenido a Platzilla, <var>NOMBRE_DEL_USUARIO</var>',
					'body'             => '<p>Hola <var>NOMBRE_DEL_USUARIO</var>:</p><p>Ya el manejo de tu empresa no será un lío, utiliza todas las herramientas que te ofrecemos y notarás el cambio.</p><p>Tu código de verificación es <var>CÓDIGO_DE_VERIFICACIÓN</var>. Para validar tu cuenta, ingresa en tu navegador el siguiente URL <var>URL_DE_VERIFICACIÓN</var> ó haz clic directamente <a href="<var>URL_DE_VERIFICACIÓN</var>">aquí</a>.</p><p>Saludos.</p><p>El equipo Platzilla</p>',
					'adddefaultheader' => true,
					'adddefaultfooter' => true,
					'attachments'      => null,
				),
				null
			);
			EmailManagerUtils::saveTemplate (
				$adb,
				array (
					'templatename'     => self::EMTEMPLATE_SEND_USER_CREDENTIALS,
					'language'         => 'es',
					'subject'          => '[Platzilla] Información de acceso a tu cuenta',
					'body'             => '<p>Hola <var>NOMBRE_DEL_USUARIO</var>!</p><p>¿Olvidaste tu contraseña? No hay problema, ingresa <a href="<var>URL_RECUPERACION_CONTRASEÑA</var>">aquí</a>, suministra la información solicitada y reestableceremos tu contraseña:</p><p>Si lo prefieres, copia el siguiente enlace en tu navegador (<var>URL_RECUPERACION_CONTRASEÑA</var>), y sigue los mismos pasos</p><p>¿Tú no has pedido la informacion de tu contraseña? No te preocupes, solo ignora este email. Disculpa la interrupción.</p><p>¡Nos vemos!</p><p>El equipo de Plazilla</p>',
					'adddefaultheader' => true,
					'adddefaultfooter' => true,
					'attachments'      => null,
				),
				null
			);
		}

		private function unregisterEmailTemplates (PearDatabase $adb) {
			$templateNames = array (self::EMTEMPLATE_SEND_USER_CREDENTIALS, self::EMTEMPLATE_WELCOME);
			foreach ($templateNames as $templateName) {
				$template = EmailManagerUtils::getTemplateByNameAndLanguage ($adb, $templateName, 'es', null);
				if (!empty ($template)) {
					EmailManagerUtils::deleteTemplate ($adb, $template ['templateid']);
				}
			}
		}

		public function install (PearDatabase $adb) {
			$this->uninstall ($adb);

			ModuleManager::getInstance ($adb)->saveModule (
				Module::getInstance ()
					->setLabel ('Tareas en segundo plano')
					->setName (self::MODULE_NAME)
					->setPresence (ModuleInterface::PRESENCE_VISIBLE)
					->setShowInAdminConsole (false)
					->setType (ModuleInterface::TYPE_TOOL)
			);
		}

		public function runPostInstallTasks (PearDatabase $adb, $isMasterPlatform) {
			$sqlStatements = array_merge (
				$this->buildSqlStatementsToCreateConfigurationTables (),
				$this->buildSqlStatementsToCreateDataTables (),
				$this->buildSqlStatementsToPopulateConfigurationTables ($isMasterPlatform)
			);
			if ($isMasterPlatform) {
				$sqlStatements = array_merge (
					$sqlStatements,
					$this->buildSqlStatementsToRegisterAsSettingsModule (),
					$this->buildSqlStatementsToRegisterCronTask ()
				);
			}
			if (empty ($sqlStatements)) {
				return;
			}
			$adb->query ('START TRANSACTION');
			foreach ($sqlStatements as $sqlStatement) {
				$adb->query ($sqlStatement);
			}
			$adb->query ('COMMIT');
			if ($isMasterPlatform) {
				$this->registerEmailTemplates ($adb);
				$this->registerBackgroundTasks ($adb);
			}
		}

		public function runPreUninstallTasks (PearDatabase $adb, $isMasterPlatform) {
			if ($isMasterPlatform) {
				$this->unregisterEmailTemplates ($adb);
				$sqlStatements = array_merge (
					$this->buildSqlStatementsToUnregisterCronTask (),
					$this->buildSqlStatementsToUnregisterAsSettingsModule ()
				);
			} else {
				$sqlStatements = array ();
			}
			$sqlStatements = array_merge (
				$sqlStatements,
				$this->buildSqlStatementsToDropDataTables (),
				$this->buildSqlStatementsToDropConfigurationTables ()
			);
			if (empty ($sqlStatements)) {
				return;
			}
			$adb->query ('START TRANSACTION');
			foreach ($sqlStatements as $sqlStatement) {
				$adb->query ($sqlStatement);
			}
			$adb->query ('COMMIT');
		}

		public function uninstall (PearDatabase $adb) {
			$mm     = ModuleManager::getInstance ($adb);
			$module = $mm->fetchModule (self::MODULE_NAME);
			if (empty ($module)) {
				return;
			}
			$mm->deleteModule ($module);
		}

		public static function getInstance () {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new self ();
			}
			return self::$INSTANCE;
		}

	}
