<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');
	require_once ('modules/emailmanager/lib/EmailManagerUtils.class.php');

	class CustomersInstaller {
		const EMTEMPLATE_CUSTOMER_CREATED     = 'Notificación de cliente creado';
		const EMTEMPLATE_CUSTOMER_DELETED     = 'Notificación de cliente eliminado';
		const EMTEMPLATE_CUSTOMER_MODIFIED    = 'Notificación de cliente modificado';
		const MODULE_NAME                     = 'clientes';

		private static $INSTANCE = null;

		private function registerEmailTemplates (PearDatabase $adb) {
			EmailManagerUtils::saveTemplate (
				$adb,
				array (
					'templatename'     => self::EMTEMPLATE_CUSTOMER_CREATED,
					'language'         => 'es',
					'subject'          => '[Platzilla - Notificación] El cliente <var>NOMBRE_DEL_CLIENTE</var> ha sido creado',
					'body'             => '<p>Hola!</p><p>Te informamos que <var>NOMBRE_DEL_USUARIO</var> creó el registro del cliente <var>NOMBRE_DEL_CLIENTE</var> en fecha <var>FECHA_DE_CREACIÓN</var>.</p><p>Saludos</p><p>El equipo Platzilla</p>',
					'adddefaultheader' => true,
					'adddefaultfooter' => true,
					'attachments'      => null,
				),
				null
			);
			EmailManagerUtils::saveTemplate (
				$adb,
				array (
					'templatename'     => self::EMTEMPLATE_CUSTOMER_DELETED,
					'language'         => 'es',
					'subject'          => '[Platzilla - Notificación] El cliente <var>NOMBRE_DEL_CLIENTE</var> ha sido eliminado',
					'body'             => '<p>Hola!</p><p>Te informamos que <var>NOMBRE_DEL_USUARIO</var> eliminó el registro del cliente <var>NOMBRE_DEL_CLIENTE</var> en fecha <var>FECHA_DE_ELIMINACIÓN</var>.</p><p>Saludos</p><p>El equipo Platzilla</p>',
					'adddefaultheader' => true,
					'adddefaultfooter' => true,
					'attachments'      => null,
				),
				null
			);
			EmailManagerUtils::saveTemplate (
				$adb,
				array (
					'templatename'     => self::EMTEMPLATE_CUSTOMER_MODIFIED,
					'language'         => 'es',
					'subject'          => '[Platzilla - Notificación] El cliente <var>NOMBRE_DEL_CLIENTE</var> ha sido modificado',
					'body'             => '<p>Hola!</p><p>Te informamos que <var>NOMBRE_DEL_USUARIO</var> modificó el registro del cliente <var>NOMBRE_DEL_CLIENTE</var> en fecha <var>FECHA_DE_MODIFICACIÓN</var>.</p><p>Saludos</p><p>El equipo Platzilla</p>',
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

			$templateNames = array (self::EMTEMPLATE_CUSTOMER_CREATED, self::EMTEMPLATE_CUSTOMER_DELETED, self::EMTEMPLATE_CUSTOMER_MODIFIED);
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
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE, 100)->setColumnName ('cod_clientes')->setName ('cod_clientes')->setLabel ('Código')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 255)->setColumnName ('nombre_sociedad')->setName ('nombre_sociedad')->setLabel ('Nombre sociedad')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 255)->setColumnName ('nombre_comercial')->setName ('nombre_comercial')->setLabel ('Nombre comercial')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 255)->setColumnName ('nif')->setName ('nif')->setLabel ('N.I.F.')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_EMAIL)->setColumnName ('email')->setName ('email')->setLabel ('Email')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('direccion')->setName ('direccion')->setLabel ('Dirección')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 50)->setColumnName ('ciudad')->setName ('ciudad')->setLabel ('Ciudad')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 50)->setColumnName ('provincia')->setName ('provincia')->setLabel ('Provincia')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 50)->setColumnName ('pais')->setName ('pais')->setLabel ('País')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('comentarios')->setName ('comentarios')->setLabel ('Comentarios')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
			);

			ModuleManager::getInstance ($adb)->saveModule (
				Module::getInstance (true, 'CLI-', '00001')
					->setEntityIdentifier ('nombre_comercial')
					->setLabel ('Clientes')
					->setMenuLabel ('Entradas')
					->setName (self::MODULE_NAME)
					->setPresence (ModuleInterface::PRESENCE_VISIBLE)
					->setShowInAdminConsole (false)
					->setType (ModuleInterface::TYPE_USER)
					->setBlocks (
						array (
							Block::getInstance ()->setLabel ('Información general')->setFields (array ($fields [0], $fields [1], $fields [2], $fields [3], $fields [4], $fields [5], $fields [6], $fields [7])),
							Block::getInstance ()->setLabel ('Información adicional')->setFields (array ($fields [8])),
						)
					)
					->setCharts (
						array (
							Chart::getInstance ()
								->setDateGrouping (ChartInterface::DATE_GROUPING_DAILY)
								->setFieldName ('createdtime')
								->setOperation (ChartInterface::OPERATION_COUNT)
								->setTitle ('Clientes por Fecha de Creación (Diario)')
								->setType (ChartInterface::TYPE_BARS),
						)
					)
					->setViews (
						array (
							View::getInstance ()
								->setDefault (ViewInterface::DEFAULT_YES)
								->setName ('All')
								->setOwner (1)
								->setStatus (ViewInterface::STATUS_PUBLIC)
								->setColumns (
									array (
										ViewColumn::getInstance ($fields [2])->setSequence (1),
										ViewColumn::getInstance ($fields [3])->setSequence (2),
										ViewColumn::getInstance ($fields [4])->setSequence (3),
									)
								),
						)
					)
			);
			ModuleRelationshipManager::getInstance ($adb)->saveRelationship (
				ModuleRelationship::getInstance ()
					->setActions (array (ModuleRelationshipInterface::ACTION_ADD, ModuleRelationshipInterface::ACTION_SELECT))
					->setFunction ('get_related_list')
					->setLabel ('Contactos')
					->setModuleName (self::MODULE_NAME)
					->setPresence (ModuleRelationshipInterface::PRESENCE_VISIBLE)
					->setRelatedModuleName ('Clientes')
			);
			ModuleRelationshipManager::getInstance ($adb)->saveRelationship (
				ModuleRelationship::getInstance ()
					->setActions (array (ModuleRelationshipInterface::ACTION_ADD, ModuleRelationshipInterface::ACTION_SELECT))
					->setFunction ('get_related_list')
					->setLabel ('Contactos')
					->setModuleName (self::MODULE_NAME)
					->setPresence (ModuleRelationshipInterface::PRESENCE_VISIBLE)
					->setRelatedModuleName ('contactos')
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
