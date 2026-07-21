<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');
	require_once ('modules/emailmanager/lib/EmailManagerUtils.class.php');

	class ContactsInstaller {
		const EMTEMPLATE_CONTACT_CREATED     = 'Notificación de contacto creado';
		const EMTEMPLATE_CONTACT_DELETED     = 'Notificación de contacto eliminado';
		const EMTEMPLATE_CONTACT_MODIFIED    = 'Notificación de contacto modificado';
		const MODULE_NAME                    = 'contactos';

		private static $INSTANCE = null;

		private function registerEmailTemplates (PearDatabase $adb) {
			EmailManagerUtils::saveTemplate (
				$adb,
				array (
					'templatename'     => self::EMTEMPLATE_CONTACT_CREATED,
					'language'         => 'es',
					'subject'          => '[Platzilla - Notificación] El contacto <var>NOMBRE_DEL_CONTACTO</var> ha sido creado',
					'body'             => '<p>Hola!</p><p>Te informamos que <var>NOMBRE_DEL_USUARIO</var> creó el registro del contacto <var>NOMBRE_DEL_CONTACTO</var> en fecha <var>FECHA_DE_CREACIÓN</var>.</p><p>Saludos</p><p>El equipo Platzilla</p>',
					'adddefaultheader' => true,
					'adddefaultfooter' => true,
					'attachments'      => null,
				),
				null
			);
			EmailManagerUtils::saveTemplate (
				$adb,
				array (
					'templatename'     => self::EMTEMPLATE_CONTACT_DELETED,
					'language'         => 'es',
					'subject'          => '[Platzilla - Notificación] El contacto <var>NOMBRE_DEL_CONTACTO</var> ha sido eliminado',
					'body'             => '<p>Hola!</p><p>Te informamos que <var>NOMBRE_DEL_USUARIO</var> eliminó el registro del contacto <var>NOMBRE_DEL_CONTACTO</var> en fecha <var>FECHA_DE_ELIMINACIÓN</var>.</p><p>Saludos</p><p>El equipo Platzilla</p>',
					'adddefaultheader' => true,
					'adddefaultfooter' => true,
					'attachments'      => null,
				),
				null
			);
			EmailManagerUtils::saveTemplate (
				$adb,
				array (
					'templatename'     => self::EMTEMPLATE_CONTACT_MODIFIED,
					'language'         => 'es',
					'subject'          => '[Platzilla - Notificación] El contacto <var>NOMBRE_DEL_CONTACTO</var> ha sido modificado',
					'body'             => '<p>Hola!</p><p>Te informamos que <var>NOMBRE_DEL_USUARIO</var> modificó el registro del contacto <var>NOMBRE_DEL_CONTACTO</var> en fecha <var>FECHA_DE_MODIFICACIÓN</var>.</p><p>Saludos</p><p>El equipo Platzilla</p>',
					'adddefaultheader' => true,
					'adddefaultfooter' => true,
					'attachments'      => null,
				),
				null
			);
		}

		private function unregisterEmailTemplates (PearDatabase $adb) {
			$emailManager = ModuleManager::getInstance ($adb)->fetchModule ('emailmanager', true);
			if (empty ($emailManager)) {
				return;
			}

			$templateNames = array (self::EMTEMPLATE_CONTACT_CREATED, self::EMTEMPLATE_CONTACT_DELETED, self::EMTEMPLATE_CONTACT_MODIFIED);
			foreach ($templateNames as $templateName) {
				$template = EmailManagerUtils::getTemplateByNameAndLanguage ($adb, $templateName, 'es', null);
				if (!empty ($template)) {
					EmailManagerUtils::deleteTemplate ($adb, $template ['templateid']);
				}
			}
		}

		public function install (PearDatabase $adb) {
			$this->uninstall ($adb);

			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE, 100)->setColumnName ('cod_contactos')->setName ('cod_contactos')->setLabel ('Código')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 255)->setColumnName ('nombres')->setName ('nombres')->setLabel ('Nombres')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 255)->setColumnName ('apellidos')->setName ('apellidos')->setLabel ('Apellidos')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_EMAIL)->setColumnName ('email')->setName ('email')->setLabel ('Email')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_PHONE)->setColumnName ('telefono')->setName ('telefono')->setLabel ('Teléfono')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('comentarios')->setName ('comentarios')->setLabel ('Comentarios')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
			);

			ModuleManager::getInstance ($adb)->saveModule (
				Module::getInstance (true, 'CON-', '00001')
					->setEntityIdentifier ('apellidos')
					->setLabel ('Contactos')
					->setMenuLabel ('Entradas')
					->setName (self::MODULE_NAME)
					->setPresence (ModuleInterface::PRESENCE_VISIBLE)
					->setShowInAdminConsole (false)
					->setType (ModuleInterface::TYPE_USER)
					->setBlocks (array (
						Block::getInstance ()->setLabel ('Información general')->setFields (array ($fields [0], $fields [1], $fields [2], $fields [3], $fields [4])),
						Block::getInstance ()->setLabel ('Información adicional')->setFields (array ($fields [5])),
					))
					->setCharts (array (
						Chart::getInstance ()
							->setDateGrouping (ChartInterface::DATE_GROUPING_DAILY)
							->setFieldName ('createdtime')
							->setOperation (ChartInterface::OPERATION_COUNT)
							->setTitle ('Contactos por Fecha de Creación (Diario)')
							->setType (ChartInterface::TYPE_BARS),
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
								ViewColumn::getInstance ($fields [3])->setSequence (3),
								ViewColumn::getInstance ($fields [4])->setSequence (4),
							)),
					))
			);
			ModuleRelationshipManager::getInstance ($adb)->saveRelationship (
				ModuleRelationship::getInstance ()
					->setActions (array (ModuleRelationshipInterface::ACTION_ADD, ModuleRelationshipInterface::ACTION_SELECT))
					->setFunction ('get_related_list')
					->setLabel ('Actividades')
					->setModuleName (self::MODULE_NAME)
					->setPresence (ModuleRelationshipInterface::PRESENCE_VISIBLE)
					->setRelatedModuleName ('Calendar')
			);
		}

		public function runPostInstallTasks (PearDatabase $adb) {
			$this->registerEmailTemplates ($adb);
		}

		public function runPreUninstallTasks (PearDatabase $adb) {
			$this->unregisterEmailTemplates ($adb);
		}

		public function uninstall (PearDatabase $adb) {
			$mm     = ModuleManager::getInstance ($adb);
			$module = $mm->fetchModule (self::MODULE_NAME);
			if (empty ($module)) {
				return;
			}

			ModuleRelationshipManager::getInstance ($adb)->deleteRelationships (self::MODULE_NAME);
			$mm->deleteModule ($module);
		}

		public static function getInstance () {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new self ();
			}
			return self::$INSTANCE;
		}

	}
