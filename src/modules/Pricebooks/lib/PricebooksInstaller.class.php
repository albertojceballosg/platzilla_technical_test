<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');

	class PricebooksInstaller {
		const MODULE_NAME = 'Pricebooks';

		private static $INSTANCE = null;

		private function buildSqlStatementsToCreateTables () {
			$sqlStatements = $this->buildSqlStatementsToDropTables ();
			return array_merge (
				$sqlStatements,
				array (
					'CREATE TABLE vtiger_pricebooks_conditiongroups (
						pricebookid INT(11) NOT NULL,
						groupid INT(11) NOT NULL,
						glue VARCHAR(3) NULL DEFAULT NULL,
						PRIMARY KEY (pricebookid, groupid),
						CONSTRAINT FK_vtiger_pricebooks_conditiongroups_pricebooks FOREIGN KEY (pricebookid) REFERENCES vtiger_pricebooks (pricebookid) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE=InnoDB',
					'CREATE TABLE vtiger_pricebooks_conditions (
						pricebookid INT(11) NOT NULL,
						groupid INT(11) NOT NULL,
						conditionid INT(11) NOT NULL,
						variabletype VARCHAR(25) NOT NULL,
						variablename VARCHAR(255) NOT NULL,
						operator VARCHAR(15) NOT NULL,
						value VARCHAR(255) NULL DEFAULT NULL,
						glue VARCHAR(3) NULL DEFAULT NULL,
						PRIMARY KEY (pricebookid, groupid, conditionid),
						UNIQUE INDEX pricebooks_conditions_unique (pricebookid, groupid, conditionid, variabletype, variablename, operator, value),
						CONSTRAINT FK_vtiger_pricebooks_conditions_conditiongroups FOREIGN KEY (pricebookid, groupid) REFERENCES vtiger_pricebooks_conditiongroups (pricebookid, groupid) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE=InnoDB',
				)
			);
		}

		private function buildSqlStatementsToDropTables () {
			return array (
				'DROP TABLE IF EXISTS vtiger_pricebooks_conditions',
				'DROP TABLE IF EXISTS vtiger_pricebooks_conditiongroups',
			);
		}

		public function install (PearDatabase $adb) {
			$this->uninstall ($adb);

			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE, 100)->setColumnName ('cod_pricebook')->setName ('cod_pricebook')->setLabel ('Código')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 255)->setColumnName ('name')->setName ('name')->setLabel ('Nombre')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('description')->setName ('description')->setLabel ('Descripción')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER, 18, 2)->setColumnName ('multiplier')->setName ('multiplier')->setLabel ('Multiplicador')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
			);

			ModuleManager::getInstance ($adb)->saveModule (
				Module::getInstance (true, 'TAR-', '00001')
					->setEntityIdentifier ('name')
					->setLabel ('Tarifas')
					->setMenuLabel ('Entradas')
					->setName (self::MODULE_NAME)
					->setPresence (ModuleInterface::PRESENCE_USER_DEFINED)
					->setShowInAdminConsole (true)
					->setType (ModuleInterface::TYPE_ADMIN)
					->setBlocks (array (
						Block::getInstance ()->setLabel ('Información general')->setFields ($fields),
					))
					->setViews (array (
						View::getInstance ()
							->setDefault (ViewInterface::DEFAULT_YES)
							->setName ('All')
							->setOwner (1)
							->setStatus (ViewInterface::STATUS_PUBLIC)
							->setColumns (array (
								ViewColumn::getInstance ($fields [0])->setSequence (1),
								ViewColumn::getInstance ($fields [1])->setSequence (2),
								ViewColumn::getInstance ($fields [3])->setSequence (3),
							)),
					))
			);
			ModuleRelationshipManager::getInstance ($adb)->saveRelationship (
				ModuleRelationship::getInstance ()
					->setActions (array (ModuleRelationshipInterface::ACTION_SELECT))
					->setFunction ('get_related_list')
					->setLabel ('Tarifas')
					->setModuleName ('articulos')
					->setPresence (ModuleRelationshipInterface::PRESENCE_VISIBLE)
					->setRelatedModuleName (self::MODULE_NAME)
			);
		}

		public function runPostInstallTasks (PearDatabase $adb) {
			return;
		}

		public function runPreUninstallTasks (PearDatabase $adb) {
			return;
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
