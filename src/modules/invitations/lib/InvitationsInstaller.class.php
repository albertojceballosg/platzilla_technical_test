<?php
	set_include_path (get_include_path () . ':' . realpath (__DIR__ . '/../../'));
	require_once ('vtlib/Vtiger/Module.php');

	class InvitationsInstaller {
		const MAX_NEW_RECORDS_BY_INVITATION = 5;

		private static $INSTANCE = null;

		private function getAvailableEntities () {
			global $adb;
			$sql    = "SELECT DISTINCT
							t.name
						FROM
							vtiger_tab t
							INNER JOIN vtiger_entityname en ON en.modulename=t.name
						WHERE
							(t.presence=0) AND
							(t.name NOT IN ('Calendar', 'invitations', 'Documents', 'instances', 'Users'))";
			$result = $adb->query ($sql);
			if ($adb->getRowCount ($result) == 0) {
				return null;
			}
			$entities = array ();
			while ($row = $adb->fetch_row ($result)) {
				$entities [] = $row [0];
			}
			return $entities;
		}

		private function copyDirectory ($src, $dst) {
			if ((!file_exists ($src)) || (!is_dir ($src))) {
				return;
			}
			if (!mkdir ($dst, 0775, true)) {
				throw new Exception ("Imposible crear el directorio $dst");
			}
			$dir = opendir ($src);
			while ($file = readdir ($dir)) {
				if (($file == '.') || ($file == '..')) {
					continue;
				}
				if (is_dir ("$src/$file")) {
					$this->copyDirectory ($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
				} else {
					copy ($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
				}
			}
			closedir ($dir);
		}

		private function deleteDirectory ($dir) {
			if (!file_exists ($dir)) {
				return true;
			}
			if (!is_dir ($dir)) {
				return unlink ($dir);
			}
			foreach (scandir ($dir) as $item) {
				if ($item == '.' || $item == '..') {
					continue;
				}
				if (!$this->deleteDirectory ($dir . DIRECTORY_SEPARATOR . $item)) {
					return false;
				}
			}
			return rmdir ($dir);
		}

		private function createModule () {
			$module        = new Vtiger_Module ();
			$module->name  = 'invitations';
			$module->label = 'Invitaciones';
			$module->save ();
			return $module;
		}

		private function createField (array $options) {
			$field             = new Vtiger_Field ();
			$field->name       = $options ['name'];
			$field->label      = $options ['label'];
			$field->table      = $options ['tableName'];
			$field->column     = $options ['columnName'];
			$field->columntype = $options ['columnType'];
			$field->uitype     = $options ['uiType'];
			$field->typeofdata = $options ['typeOfData'];
			return $field;
		}

		private function cleanDatabase () {
			global $adb;
			$adb->query ("DELETE FROM vtiger_crmentity WHERE setype='invitations'");
			$adb->query ('DROP TABLE IF EXISTS vtiger_invitations');
			$adb->query ('DROP TABLE IF EXISTS vtiger_invitationscf');
			$adb->query ('DROP TABLE IF EXISTS vtiger_invitationstatus');
			$adb->query ('DROP TABLE IF EXISTS vtiger_invitationstatus_seq');
			$adb->query ("DELETE FROM vtiger_picklist WHERE name='invitationstatus'");
		}

		private function deleteTemplates (Vtiger_Module $module) {
			global $theme;
			$templatesFolder = __DIR__ . "/../../Smarty/templates/$theme/modules/{$module->name}";
			if (is_dir ($templatesFolder)) {
				$this->deleteDirectory ($templatesFolder);
			}
		}

		private function tearDownFields (Vtiger_Module $module) {
			$fields = $module->getFields ();
			if (($fields) && (is_array ($fields)) && (count ($fields) > 0)) {
				/** @var Vtiger_Field $field */
				foreach ($fields as $field) {
					$field->delete ();
				}
			}
		}

		private function tearDownFilters (Vtiger_Module $module) {
			$filters = Vtiger_Filter::getAllForModule ($module);
			if (($filters) && (is_array ($filters)) && (count ($filters) > 0)) {
				/** @var Vtiger_Filter $filter */
				foreach ($filters as $filter) {
					$filter->delete ();
				}
			}
		}

		private function tearDownRelatedEntities (Vtiger_Module $module) {
			$relatedEntities = $this->getAvailableEntities ();
			if (($relatedEntities) && (is_array ($relatedEntities)) && (count ($relatedEntities) > 0)) {
				foreach ($relatedEntities as $relatedEntity) {
					$relatedModule = Vtiger_Module::getInstance ($relatedEntity);
					$relatedModule->unsetRelatedList (Vtiger_Module::getInstance ($module->name), 'Invitaciones', 'get_dependents_list');
				}
			}
		}

		private function setupRelatedEntities (Vtiger_Module $module, $entities) {
			// Ligar a todas las entidades posibles
			if (!$entities) {
				return;
			}
			foreach ($entities as $entity) {
				$relatedModule = Vtiger_Module::getInstance ($entity);
				if (!$relatedModule) {
					continue;
				}
				$relatedModule->setRelatedList (Vtiger_Module::getInstance ($module->name), 'Invitaciones', array ('ADD'), 'get_dependents_list');
			}
		}

		private function setup () {
			$module = $this->createModule ();
			$module->initTables ('vtiger_invitations', 'invitationid');

			$menu = Vtiger_Menu::getInstance ('Entradas');
			$menu->addModule ($module);

			$informationBlock        = new Vtiger_Block ();
			$informationBlock->label = 'LBL_INVITATIONS_BLOCK_INFORMATION';
			$module->addBlock ($informationBlock);

			$codeField = $this->createField (array ('name' => 'code', 'label' => 'LBL_INVITATIONS_FIELD_CODE', 'tableName' => 'vtiger_invitations', 'columnName' => 'code', 'columnType' => 'VARCHAR(10)', 'uiType' => 4, 'typeOfData' => 'V~M'));
			$codeField->setModuleSeqNumber ('configure', $module->name, 'INV-', '00001');
			$informationBlock->addField ($codeField);

			$guestField = $this->createField (array ('name' => 'guest', 'label' => 'LBL_INVITATIONS_FIELD_GUEST', 'tableName' => 'vtiger_invitations', 'columnName' => 'guest', 'columnType' => 'VARCHAR(50)', 'uiType' => 13, 'typeOfData' => 'E~M'));
			$informationBlock->addField ($guestField);

			$entityField = $this->createField (array ('name' => 'entityid', 'label' => 'LBL_INVITATIONS_FIELD_ENTITYID', 'tableName' => 'vtiger_invitations', 'columnName' => 'entityid', 'columnType' => 'INT(19)', 'uiType' => 10, 'typeOfData' => 'V~M'));
			$informationBlock->addField ($entityField);

			$statusField              = $this->createField (array ('name' => 'invitationstatus', 'label' => 'LBL_INVITATIONS_FIELD_STATUS', 'tableName' => 'vtiger_invitations', 'columnName' => 'invitationstatus', 'columnType' => 'VARCHAR(10)', 'uiType' => 16, 'typeOfData' => 'V~O'));
			$statusField->displaytype = 2;
			$statusField->setNoRolePicklistValues (array ('Enviada', 'Aceptada', 'Procesada'));
			$informationBlock->addField ($statusField);

			// Ligar a todas las entidades posibles
			$entities = $this->getAvailableEntities ();
			if ($entities) {
				$entityField->setRelatedModules ($entities);
			}

			$ownerField              = $this->createField (array ('name' => 'assigned_user_id', 'label' => 'LBL_INVITATIONS_FIELD_ASSIGNED_TO', 'tableName' => 'vtiger_crmentity', 'columnName' => 'smownerid', 'columnType' => 'INT(19)', 'uiType' => 53, 'typeOfData' => 'V~M'));
			$ownerField->displaytype = 3;
			$informationBlock->addField ($ownerField);

			$createdField              = $this->createField (array ('name' => 'CreatedTime', 'label' => 'LBL_INVITATIONS_FIELD_CREATED_TIME', 'tableName' => 'vtiger_crmentity', 'columnName' => 'createdtime', 'columnType' => 'DATETIME', 'uiType' => 70, 'typeOfData' => 'T~O'));
			$createdField->displaytype = 2;
			$informationBlock->addField ($createdField);

			$modifiedField              = $this->createField (array ('name' => 'ModifiedTime', 'label' => 'LBL_INVITATIONS_FIELD_MODIFIED_TIME', 'tableName' => 'vtiger_crmentity', 'columnName' => 'modifiedtime', 'columnType' => 'DATETIME', 'uiType' => 70, 'typeOfData' => 'T~O'));
			$modifiedField->displaytype = 2;
			$informationBlock->addField ($modifiedField);

			$module->setEntityIdentifier ($codeField);

			$this->setupRelatedEntities ($module, $entities);

			$filter            = new Vtiger_Filter();
			$filter->name      = 'All';
			$filter->isdefault = true;
			$module->addFilter ($filter);

			$filter->addField ($codeField);
			$filter->addField ($guestField);
			$filter->addField ($statusField);

			$module->enableTools ('Export');
			$module->disableTools ('Import');

			$module->initWebservice ();

			Vtiger_Utils::AlterTable (
				'vtiger_invitations',
				' ALTER `code` DROP DEFAULT,
				ALTER `guest` DROP DEFAULT,
				ALTER `invitationstatus` DROP DEFAULT,
				ALTER `entityid` DROP DEFAULT'
			);
			Vtiger_Utils::AlterTable (
				'vtiger_invitations',
				' CHANGE COLUMN `code` `code` VARCHAR(10) NOT NULL AFTER `invitationid`,
				CHANGE COLUMN `guest` `guest` VARCHAR(255) NOT NULL AFTER `code`,
				CHANGE COLUMN `entityid` `entityid` INT(19) NOT NULL AFTER `guest`,
				ADD INDEX `code` (`code`),
				ADD INDEX `guest` (`guest`),
				ADD INDEX `invitationstatus` (`invitationstatus`),
				ADD INDEX `entityid` (`entityid`)'
			);

			global $theme;
			$templatesFolder = __DIR__ . "/../../Smarty/templates/$theme/modules/{$module->name}";
			if (is_dir ($templatesFolder)) {
				$this->deleteDirectory ($templatesFolder);
			}
			$this->copyDirectory (__DIR__ . '/templates', $templatesFolder);

			Vtiger_Module::fireEvent ($module->name, Vtiger_Module::EVENT_MODULE_POSTINSTALL);
		}

		private function tearDown () {
			$module = Vtiger_Module::getInstance ('invitations');
			if (!$module) {
				return;
			}
			Vtiger_Module::fireEvent ($module->name, Vtiger_Module::EVENT_MODULE_PREUNINSTALL);
			$module->deinitWebservice ();
			$module->disableTools (array ('Import', 'Export'));
			$this->tearDownFilters ($module);
			$this->tearDownRelatedEntities ($module);
			$module->unsetEntityIdentifier ();
			$this->tearDownFields ($module);
			$module->deleteRelatedLists ();
			$module->delete ();
			$this->cleanDatabase ();
			$this->deleteTemplates ($module);
		}

		public function setupDatabaseVariables () {
			global $adb;

			ob_start ();
			include ('modules/invitations/email-manager-data/template-subject.html.php');
			$subject = ob_get_clean ();

			ob_start ();
			include ('modules/invitations/email-manager-data/template-body.html.php');
			$body = ob_get_clean ();

			$result                    = $adb->query ("SELECT vi.varvalue FROM vtiger_variables_instancias vi WHERE vi.varname='MAX_NEW_RECORDS_BY_INVITATION'", true);
			$isInstanceVariableDefined = $adb->num_rows ($result) > 0 ? true : false;

			$adb->startTransaction ();
			$result  = $adb->query ("SELECT eventid FROM vtiger_emailmanager_events WHERE code='INVITATION_CREATED' LIMIT 1", true);
			$eventId = ($result) && ($adb->num_rows ($result)) ? $adb->query_result ($result, 0, 'eventid') : null;
			if ($eventId == null) {
				$adb->query ("INSERT INTO vtiger_emailmanager_events (code, label, pordefecto) VALUES ('INVITATION_CREATED', 'Se ha creado una invitación a compartir contenido de Platzilla', 1)");
				$adb->pquery (
					"INSERT INTO vtiger_emailmanager_template (idiomaid, eventid, subject, header, footer, body) VALUES (
						(SELECT picklist_valueid FROM vtiger_emails_idiomas WHERE cf_807='Español' LIMIT 1),
						(SELECT eventid FROM vtiger_emailmanager_events WHERE code='INVITATION_CREATED' LIMIT 1),
						?,
						1,
						1,
						?
					)",
					array ($subject, $body)
				);
			}
			if (!$isInstanceVariableDefined) {
				$adb->pquery ('INSERT INTO vtiger_variables_instancias (varname, varvalue) VALUES (?, ?)', array ('MAX_NEW_RECORDS_BY_INVITATION', self::MAX_NEW_RECORDS_BY_INVITATION));
			}
			$adb->completeTransaction ();
		}

		public function tearDownDatabaseVariables () {
			global $adb;

			$adb->query ("DELETE FROM vtiger_emailmanager_template WHERE eventid IN (SELECT eventid FROM vtiger_emailmanager_events WHERE code='INVITATION_CREATED')");
			$adb->query ("DELETE FROM vtiger_emailmanager_events WHERE code='INVITATION_CREATED'");
			$adb->query ("DELETE FROM vtiger_variables_instancias WHERE varname='MAX_NEW_RECORDS_BY_INVITATION'");
		}

		public function install ($adbConnection = null) {
			global $adb;
			if (($adbConnection) && ($adbConnection instanceof PearDatabase)) {
				$adb = $adbConnection;
			}
			$this->tearDown ();
			$this->setup ();
		}

		public function uninstall ($adbConnection = null) {
			global $adb;
			if (($adbConnection) && ($adbConnection instanceof PearDatabase)) {
				$adb = $adbConnection;
			}
			$this->tearDown ();
		}

		public static function getInstance () {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new InvitationsInstaller ();
			}
			return self::$INSTANCE;
		}

	}
