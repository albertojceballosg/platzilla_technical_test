<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	class PotentialCustomersInstaller {
		const MODULE_NAME                = 'potenciales_clientes';

		private static $INSTANCE = null;

		public function install (PearDatabase $adb) {
			$this->uninstall ($adb);

			$statusPicklist = Picklist::getInstance ()
				->setName ('estado_potencial')
				->setValues (array (
					PicklistValue::getInstance ()->setValue ('Contactado')->setPresence (PicklistValue::PRESENCE_VISIBLE),
					PicklistValue::getInstance ()->setValue ('En negociación')->setPresence (PicklistValue::PRESENCE_VISIBLE),
					PicklistValue::getInstance ()->setValue ('Perdido')->setPresence (PicklistValue::PRESENCE_VISIBLE),
					PicklistValue::getInstance ()->setValue ('Registrado')->setPresence (PicklistValue::PRESENCE_VISIBLE),
				));

			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE, 100)->setColumnName ('cod_potenciales_clientes')->setName ('cod_potenciales_clientes')->setLabel ('Código')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 255)->setColumnName ('nombre_comercial')->setName ('nombre_comercial')->setLabel ('Nombre comercial')->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_EMAIL)->setColumnName ('email')->setName ('email')->setLabel ('Email')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_PICKLIST)->setColumnName ('estado_potencial')->setName ('estado_potencial')->setLabel ('Estado')->setPicklist ($statusPicklist)->setMandatory (true)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('direccion')->setName ('direccion')->setLabel ('Dirección')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 50)->setColumnName ('ciudad')->setName ('ciudad')->setLabel ('Ciudad')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 50)->setColumnName ('provincia')->setName ('provincia')->setLabel ('Provincia')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 50)->setColumnName ('pais')->setName ('pais')->setLabel ('País')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATE)->setColumnName ('fecha_conversion')->setName ('fecha_conversion')->setLabel ('Fecha de conversión')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA)->setColumnName ('comentarios')->setName ('comentarios')->setLabel ('Comentarios')->setMandatory (false)->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE),
			);

			ModuleManager::getInstance ($adb)->saveModule (
				Module::getInstance (true, 'POT-', '00001')
					->setEntityIdentifier ('nombre_comercial')
					->setLabel ('Potenciales clientes')
					->setMenuLabel ('Entradas')
					->setName (self::MODULE_NAME)
					->setPresence (ModuleInterface::PRESENCE_VISIBLE)
					->setShowInAdminConsole (false)
					->setType (ModuleInterface::TYPE_USER)
					->setBlocks (array (
						Block::getInstance ()->setLabel ('Información general')->setFields (array ($fields [0], $fields [1], $fields [2], $fields [3], $fields [4], $fields [5], $fields [6], $fields [7], $fields [8])),
						Block::getInstance ()->setLabel ('Información adicional')->setFields (array ($fields [9])),
					))
					->setButtons (array (
						Button::getInstance ()
							->setAction ('/index.php?module=backgroundtasks&action=RunTask&taskname=Convertir+potencial+cliente+en+cliente&record=[record]&return_module=[module]&return_action=[action]&return_record=[record]&Ajax=true')
							->setDescription ('Convertir a cliente')
							->setIsActive (true)
							->setLabel ('Convertir a cliente')
							->setLocation (ButtonInterface::LOCATION_DETAIL_VIEW)
							->setRunInNewWindow (false)
							->setStyle ('danger')
							->setType (ButtonInterface::TYPE_LINK),
					))
					->setCharts (array (
						Chart::getInstance ()
							->setFieldName ('estado_potencial')
							->setOperation (ChartInterface::OPERATION_COUNT)
							->setTitle ('Potenciales clientes por estado')
							->setType (ChartInterface::TYPE_PIE),
						Chart::getInstance ()
							->setDateGrouping (ChartInterface::DATE_GROUPING_DAILY)
							->setFieldName ('createdtime')
							->setOperation (ChartInterface::OPERATION_COUNT)
							->setTitle ('Potenciales clientes por fecha de creación (Diario)')
							->setType (ChartInterface::TYPE_BARS),
						Chart::getInstance ()
							->setDateGrouping (ChartInterface::DATE_GROUPING_MONTHLY)
							->setFieldName ('fecha_conversion')
							->setOperation (ChartInterface::OPERATION_COUNT)
							->setTitle ('Potenciales clientes por fecha de conversión (Mensual)')
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
							)),
					))
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
