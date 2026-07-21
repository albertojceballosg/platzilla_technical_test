<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');
	require_once ('modules/emailmanager/lib/EmailManagerUtils.class.php');

	class NegotiationInstaller {
		const EMTEMPLATE_NEGOTIATION_CREATED                    = 'Notificación de negociación creada';
		const EMTEMPLATE_NEGOTIATION_DELETED                    = 'Notificación de negociación eliminada';
		const EMTEMPLATE_NEGOTIATION_MODIFIED                   = 'Notificación de negociación modificada';
		const MODULE_NAME                                       = 'negociaciones';

		private static $INSTANCE = null;

		private function registerEmailTemplates (PearDatabase $adb) {
			EmailManagerUtils::saveTemplate (
				$adb,
				array (
					'templatename'     => self::EMTEMPLATE_NEGOTIATION_CREATED,
					'language'         => 'es',
					'subject'          => '[Platzilla - Notificación] Se ha creado la negociación con el cliente <var>NOMBRE_DEL_CLIENTE</var>',
					'body'             => '<p>Hola!</p><p>Te informamos que <var>NOMBRE_DEL_USUARIO</var> creó la negociación con el cliente <var>NOMBRE_DEL_CLIENTE</var> en fecha <var>FECHA_DE_CREACIÓN</var>.</p><p>Saludos</p><p>El equipo Platzilla</p>',
					'adddefaultheader' => true,
					'adddefaultfooter' => true,
					'attachments'      => null,
				),
				null
			);
			EmailManagerUtils::saveTemplate (
				$adb,
				array (
					'templatename'     => self::EMTEMPLATE_NEGOTIATION_DELETED,
					'language'         => 'es',
					'subject'          => '[Platzilla - Notificación] Se ha eliminado la negociación con el cliente <var>NOMBRE_DEL_CLIENTE</var>',
					'body'             => '<p>Hola!</p><p>Te informamos que <var>NOMBRE_DEL_USUARIO</var> eliminó la negociación con el cliente <var>NOMBRE_DEL_CLIENTE</var> en fecha <var>FECHA_DE_ELIMINACIÓN</var>.</p><p>Saludos</p><p>El equipo Platzilla</p>',
					'adddefaultheader' => true,
					'adddefaultfooter' => true,
					'attachments'      => null,
				),
				null
			);
			EmailManagerUtils::saveTemplate (
				$adb,
				array (
					'templatename'     => self::EMTEMPLATE_NEGOTIATION_MODIFIED,
					'language'         => 'es',
					'subject'          => '[Platzilla - Notificación] Se ha modificado la negociación con el cliente <var>NOMBRE_DEL_CLIENTE</var>',
					'body'             => '<p>Hola!</p><p>Te informamos que <var>NOMBRE_DEL_USUARIO</var> modificó la negociación con el cliente <var>NOMBRE_DEL_CLIENTE</var> en fecha <var>FECHA_DE_MODIFICACIÓN</var>.</p><p>Saludos</p><p>El equipo Platzilla</p>',
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

			$templateNames = array (self::EMTEMPLATE_NEGOTIATION_CREATED, self::EMTEMPLATE_NEGOTIATION_DELETED, self::EMTEMPLATE_NEGOTIATION_MODIFIED);
			foreach ($templateNames as $templateName) {
				$template = EmailManagerUtils::getTemplateByNameAndLanguage ($adb, $templateName, 'es', null);
				if (!empty ($template)) {
					EmailManagerUtils::deleteTemplate ($adb, $template ['templateid']);
				}
			}
		}

		public function install (PearDatabase $adb) {
			$this->uninstall ($adb);

			$reference      = FieldModuleReference::getInstance ()->setFieldName ('cliente')->setModuleName (self::MODULE_NAME)->setReferencedModuleName ('potenciales_clientes')->setSequence (1);
			$statusPicklist = Picklist::getInstance ()
				->setName ('estado_negociacion')
				->setValues (array (
					PicklistValue::getInstance ()->setValue ('Avanzada')->setPresence (PicklistValue::PRESENCE_VISIBLE),
					PicklistValue::getInstance ()->setValue ('No iniciada')->setPresence (PicklistValue::PRESENCE_VISIBLE),
					PicklistValue::getInstance ()->setValue ('Primeros contactos')->setPresence (PicklistValue::PRESENCE_VISIBLE),
					PicklistValue::getInstance ()->setValue ('Terminada')->setPresence (PicklistValue::PRESENCE_VISIBLE),
				));

			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE, 100)->setColumnName ('cod_negociacione')->setName ('cod_negociacione')->setLabel ('Código')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_MODULE_REFERENCE)->setColumnName ('cliente')->setName ('cliente')->setLabel ('Cliente')->setModuleReferences (array ($reference))->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_PICKLIST, 255)->setColumnName ('estado_negociacion')->setName ('estado_negociacion')->setLabel ('Estado')->setPicklist ($statusPicklist)->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATE)->setColumnName ('inicio')->setName ('inicio')->setLabel ('Fecha de inicio')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATE)->setColumnName ('fin')->setName ('fin')->setLabel ('Fecha de fin')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('comentarios')->setName ('comentarios')->setLabel ('Comentarios')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
			);

			ModuleManager::getInstance ($adb)->saveModule (
				Module::getInstance (true, 'NEG-', '00001')
					->setEntityIdentifier ('cliente')
					->setLabel ('Negociaciones')
					->setMenuLabel ('Planificación')
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
							->setFieldName ('estado_negociacion')
							->setOperation (ChartInterface::OPERATION_COUNT)
							->setTitle ('Negociaciones por Estado')
							->setType (ChartInterface::TYPE_PIE),
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
								ViewColumn::getInstance ($fields [4])->setSequence (4),
								ViewColumn::getInstance ($fields [2])->setSequence (5),
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
