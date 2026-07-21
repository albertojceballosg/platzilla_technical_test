<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');

	class CustomersInstaller {
		const MODULE_NAME = 'clientes_colladito';

		private static $INSTANCE = null;

		public function install (PearDatabase $adb) {
			$this->uninstall ($adb);
			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE, 100)->setColumnName ('cod_clientes')->setName ('cod_clientes')->setLabel ('Código')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 40)->setColumnName ('nombre_social')->setName ('nombre_social')->setLabel ('Nombre Social')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 11)->setColumnName ('cif')->setName ('cif')->setLabel ('CIF')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_PHONE)->setColumnName ('telefono')->setName ('telefono')->setLabel ('Teléfono')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_EMAIL)->setColumnName ('email')->setName ('email')->setLabel ('Email')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_OWNER)->setColumnName ('smownerid')->setName ('assigned_user_id')->setLabel ('Assigned To')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setTableName ('vtiger_crmentity'),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 50)->setColumnName ('direccion')->setName ('direccion')->setLabel ('Dirección')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 25)->setColumnName ('ciudad')->setName ('ciudad')->setLabel ('Ciudad')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 30)->setColumnName ('provincia')->setName ('provincia')->setLabel ('Provincia')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 20)->setColumnName ('codigo_postal')->setName ('codigo_postal')->setLabel ('Código Postal')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 20)->setColumnName ('pais')->setName ('pais')->setLabel ('País')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
			);

			ModuleManager::getInstance ($adb)->saveModule (
				Module::getInstance (true, 'CLI-', '00001')
					->setEntityIdentifier ('nombre_social')
					->setLabel ('Clientes')
					->setMenuLabel ('Entradas')
					->setName (self::MODULE_NAME)
					->setPresence (ModuleInterface::PRESENCE_USER_DEFINED)
					->setShowInAdminConsole (false)
					->setType (ModuleInterface::TYPE_USER)
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
								ViewColumn::getInstance ($fields [1])->setSequence (1),
								ViewColumn::getInstance ($fields [2])->setSequence (2),
								ViewColumn::getInstance ($fields [4])->setSequence (3),
								ViewColumn::getInstance ($fields [3])->setSequence (4),
								ViewColumn::getInstance ($fields [6])->setSequence (5),
								ViewColumn::getInstance ($fields [9])->setSequence (5),
							)),
					))
			);
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
				self::$INSTANCE = new CustomersInstaller ();
			}
			return self::$INSTANCE;
		}

	}
