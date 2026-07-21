<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');

	class RssNewsInstaller {
		const MODULE_NAME = 'rssnews';

		/** @var RssNewsInstaller */
		private static $INSTANCE = null;

		public function install (PearDatabase $adb) {
			$this->uninstall ($adb);

			$references = array (
				FieldModuleReference::getInstance ()->setReferencedModuleName ('medios_bdi')->setSequence (1),
			);

			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE, 255)->setColumnName ('code')->setName ('code')->setLabel ('LBL_RSSNEWS_FIELD_CODE')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATE)->setColumnName ('publicationdate')->setName ('publicationdate')->setLabel ('LBL_RSSNEWS_FIELD_PUBLICATION_DATE')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_MODULE_REFERENCE)->setColumnName ('media')->setName ('media')->setLabel ('LBL_RSSNEWS_FIELD_MEDIA')->setModuleReferences ($references)->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 255)->setColumnName ('headline')->setName ('headline')->setLabel ('LBL_RSSNEWS_FIELD_HEADLINE')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_URL, 255)->setColumnName ('url')->setName ('url')->setLabel ('LBL_RSSNEWS_FIELD_URL')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('keywords')->setName ('keywords')->setLabel ('LBL_RSSNEWS_FIELD_KEYWORDS')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
			);

			ModuleManager::getInstance ($adb)->saveModule (
				Module::getInstance (true, 'RSS-', '00001')
					->setEntityIdentifier ('code')
					->setLabel ('Noticias RSS')
					->setMenuLabel ('Ejecución')
					->setName (self::MODULE_NAME)
					->setPresence (ModuleInterface::PRESENCE_USER_DEFINED)
					->setShowInAdminConsole (false)
					->setType (ModuleInterface::TYPE_USER)
					->setBlocks (array (
						Block::getInstance ()->setLabel ('LBL_RSSNEWS_BLOCK_GENERAL_INFORMATION')->setFields ($fields),
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
								ViewColumn::getInstance ($fields [2])->setSequence (3),
								ViewColumn::getInstance ($fields [3])->setSequence (4),
								ViewColumn::getInstance ($fields [4])->setSequence (5),
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
				self::$INSTANCE = new self ();
			}
			return self::$INSTANCE;
		}

	}
