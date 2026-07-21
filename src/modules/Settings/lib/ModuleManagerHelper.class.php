<?php
	require_once ('include/platzilla/Managers/ApplicationManager.php');
	require_once ('include/platzilla/Managers/GlobalPicklistManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/PlatformFreeBillingPlanLimitManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/Translator.class.php');

	abstract class ModuleManagerHelper {

		/**
		 * @param array $moduleData
		 *
		 * @return Block[]
		 */
		private static function getBlocksFromModuleData ($moduleData) {
			$blockSequence = 0;
			$blocks        = array ();
			foreach ($moduleData ['blocks'] as $blockData) {
				$fields    = self::getFieldsFromBlockData ($moduleData ['name'], $blockData, $blockSequence);
				$blocks [] = Block::getInstance ()
					->setFields ($fields)
					->setLabel ($blockData ['label'])
					->setModuleName ($moduleData ['name'])
					->setSequence ($blockSequence)
					->setShowTitle (Block::SHOW_TITLE_YES)
					->setVisibility ($blockData ['visibility']);
				$blockSequence++;
			}
			return $blocks;
		}

		/**
		 * @param string $moduleName
		 * @param array $blockData
		 * @param integer $blockSequence
		 *
		 * @return Field[]
		 */
		private static function getFieldsFromBlockData ($moduleName, $blockData, $blockSequence) {
			$fieldSequence = 0;
			$fields        = array ();
			if ($blockSequence == 0) {
				$fieldData = array (
					'label' => 'Código',
					'name'  => "cod_{$moduleName}",
					'type'  => Field::UI_TYPE_CODE,
				);
				$fields [] = self::getFieldFromFieldData ($moduleName, $fieldData, $fieldSequence);
				$fieldSequence++;
			}
			foreach ($blockData ['fields'] as $fieldData) {
				$fields [] = self::getFieldFromFieldData ($moduleName, $fieldData, $fieldSequence);
				$fieldSequence++;
			}
			return $fields;
		}

		/**
		 * @param string $moduleName
		 * @param array $fieldData
		 * @param integer $fieldSequence
		 *
		 * @return Field
		 */
		private static function getFieldFromFieldData ($moduleName, $fieldData, $fieldSequence) {
			$length    = !empty ($fieldData ['length']) ? $fieldData ['length'] : null;
			$precision = isset ($fieldData ['precision']) ? $fieldData ['precision'] : null;
			if (in_array ($fieldData ['type'], array (Field::UI_TYPE_PICKLIST, Field::UI_TYPE_MULTI_SELECT))) {
				$fieldDataPicklistValues = explode ("\n", $fieldData ['picklistvalues']);
				$picklistValues          = array ();
				foreach ($fieldDataPicklistValues as $fieldDataPicklistValue) {
					$picklistValues [] = PicklistValue::getInstance (true)
						->setPresence (PicklistValue::PRESENCE_VISIBLE)
						->setValue ($fieldDataPicklistValue);
				}
				$picklist   = Picklist::getInstance ()
					->setName ($fieldData ['name'])
					->setValues ($picklistValues);
				$pipeline = null;
				$references = null;
			} else if (in_array ($fieldData ['type'], array (Field::UI_TYPE_MODULE_REFERENCE, Field::UI_TYPE_MODULE_RECORDS))) {
				$picklist   = null;
				$pipeline = null;
				$references = array (
					FieldModuleReference::getInstance ()
						->setFieldName ($fieldData ['name'])
						->setModuleName ($moduleName)
						->setReferencedModuleName ($fieldData ['referencedmodulename'])
						->setSequence (1),
				);
			} else if (in_array ($fieldData ['type'], array (Field::UI_TYPE_PIPELINE))) {
				$picklist   = null;
				$fieldDataPipelineValues = explode ("\n", $fieldData ['picklistvalues']);
				$pipeline = Pipeline::getInstance ()
					->setFieldName ($fieldData ['name'])
					->setModuleName ($moduleName)
					->setValues ($fieldDataPipelineValues);
				$references = null;
			} else {
				$picklist   = null;
				$pipeline = null;
				$references = null;
			}

			return Field::getInstance ()
				->setColumnName ($fieldData ['name'])
				->setDisplayType (Field::DISPLAY_TYPE_ALL)
				->setGeneratedType (Field::GENERATED_TYPE_EXISTING)
				->setLabel ($fieldData ['label'])
				->setMandatory (false)
				->setMassEditable (Field::MASS_EDITABLE_ENABLED)
				->setModuleName ($moduleName)
				->setModuleReferences ($references)
				->setName ($fieldData ['name'])
				->setPresence (Field::PRESENCE_VISIBLE)
				->setPicklist ($picklist)
				->setPipeline ($pipeline)
				->setQuickCreate (Field::QUICK_CREATE_ENABLED)
				->setReadOnly (Field::READ_WRITE)
				->setSequence ($fieldSequence)
				->setUiType ($fieldData ['type'], $length, $precision);
		}

		/**
		 * @param array $moduleData
		 * @param Block[] $blocks
		 * @param Users $currentUser
		 *
		 * @return View[]
		 */
		private static function getViewsFromModuleData ($moduleData, $blocks, $currentUser) {
			$viewColumnSequence = 0;
			$viewColumns        = array ();
			foreach ($moduleData ['viewcolumns'] as $viewColumnName) {
				$expectedFieldName = $viewColumnName != '__code__' ? $viewColumnName : "cod_{$moduleData ['name']}";
				$expectedField     = null;
				foreach ($blocks as $block) {
					$fields = $block->getFields ();
					foreach ($fields as $field) {
						if ($field->getName () == $expectedFieldName) {
							$expectedField = $field;
							break;
						}
					}
					if (!empty ($expectedField)) {
						break;
					}
				}
				$viewColumns [] = ViewColumn::getInstance ($expectedField)->setSequence ($viewColumnSequence);
				$viewColumnSequence++;
			}
			return array (
				View::getInstance ()
					->setColumns ($viewColumns)
					->setDefault (View::DEFAULT_YES)
					->setModuleName ($moduleData ['name'])
					->setName ('All')
					->setOwner ($currentUser->id)
					->setShowCountInMenu (View::SHOW_COUNT_YES)
					->setStatus (View::STATUS_PUBLIC),
			);
		}

		/**
		 * @param array $moduleData
		 *
		 * @throws Exception
		 */
		private static function validateEntityTypeModuleData ($moduleData) {
			if (empty ($moduleData ['entityidentifier'])) {
				throw new Exception ('No se ha suministrado el identificador de la entidad');
			} else if (empty ($moduleData ['blocks'])) {
				throw new Exception ('No se han suministrado los bloques');
			} else if (empty ($moduleData ['viewcolumns'])) {
				throw new Exception ('No se han suministrado las columnas de la vista por defecto');
			}
			foreach ($moduleData ['blocks'] as $blockData) {
				if (empty ($blockData ['fields'])) {
					throw new Exception ("No se han suministrado los campos del bloque {$blockData ['label']}");
				}
			}
		}

		/**
		 * @param array $moduleData
		 *
		 * @throws Exception
		 */
		private static function validateModuleData ($moduleData) {
			if (empty ($moduleData)) {
				throw new Exception ('No se ha suministrado la información del módulo');
			} else if (empty ($moduleData ['name'])) {
				throw new Exception ('No se ha suministrado el nombre del módulo');
			} else if (empty ($moduleData ['label'])) {
				throw new Exception ('No se ha suministrado la etiqueta del módulo');
			} else if (empty ($moduleData ['location'])) {
				throw new Exception ('No se ha suministrado la ubicación del módulo');
			} else if (empty ($moduleData ['type'])) {
				throw new Exception ('No se ha suministrado el tipo de módulo');
			} else if ($moduleData ['type'] == Module::TYPE_USER) {
				self::validateEntityTypeModuleData ($moduleData);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $moduleData
		 * @param Users $currentUser
		 *
		 * @return Module
		 *
		 * @throws Exception
		 */
		public static function createModule (PearDatabase $adb, $moduleData, $currentUser) {
			self::validateModuleData ($moduleData);

			if ($moduleData ['location'] == 'menu') {
				$menuLabel      = $moduleData ['menu'];
				$showInSettings = false;
			} else {
				$menuLabel      = null;
				$showInSettings = true;
			}
			$isEntityType = $moduleData ['type'] == Module::TYPE_USER;
			if ($isEntityType) {
				$entityPrefix     = substr (str_replace ('_', '', strtoupper ($moduleData ['name'])), 0, 3) . '-';
				$entitySequence   = '00001';
				$entityIdentifier = $moduleData ['entityidentifier'] != '__code__' ? $moduleData ['entityidentifier'] : "cod_{$moduleData ['name']}";
				$blocks           = self::getBlocksFromModuleData ($moduleData);
				$views            = self::getViewsFromModuleData ($moduleData, $blocks, $currentUser);
			} else {
				$blocks           = null;
				$entityPrefix     = null;
				$entitySequence   = null;
				$entityIdentifier = null;
				$views            = null;
			}

			$module     = Module::getInstance ($isEntityType, $entityPrefix, $entitySequence)
				->setBlocks ($blocks)
				->setEntityIdentifier ($entityIdentifier)
				->setLabel ($moduleData ['label'])
				->setMenuLabel ($menuLabel)
				->setName ($moduleData ['name'])
				->setPresence (Module::PRESENCE_VISIBLE)
				->setShowInAdminConsole (true)
				->setShowInSettings ($showInSettings)
				->setType (Module::TYPE_USER)
				->setViews ($views);
			$module     = ModuleManager::getInstance ($adb)->saveModule ($module, true);
			$pfplm      = PlatformFreeBillingPlanLimitManager::getInstance ($adb);
			$maxRecords = $pfplm->fetchDefaultMaxRecordsLimit ();
			$limit      = PlatformFreeBillingPlanLimit::getInstance ()
				->setMaxRecords ($maxRecords)
				->setModuleLabel ($module->getLabel ())
				->setModuleName ($module->getName ());
			$pfplm->saveLimit ($limit);
			return $module;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @throws Exception
		 */
		public static function deleteModule (PearDatabase $adb, $moduleName) {
			$mm     = ModuleManager::getInstance ($adb);
			$module = $mm->fetchModule ($moduleName, true);
			if (empty ($module)) {
				throw new Exception ('El módulo solicitado no se encuentra registrado');
			} else if (in_array ($module->getType (), array (Module::TYPE_ADMIN, Module::TYPE_TOOL))) {
				throw new Exception ('El módulo solicitado es un módulo de Platzilla');
			}

			$applications = ApplicationManager::getInstance ($adb)->fetchApplicationHeadersByModuleName ($moduleName);
			if (!empty ($applications)) {
				throw new Exception ('El módulo solicitado está asociado a una o más aplicaciones');
			}

			$mm->deleteModule ($module, true);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @throws Exception
		 * @throws ModuleException
		 */
		public static function disableModule (PearDatabase $adb, $moduleName) {
			$mm     = ModuleManager::getInstance ($adb);
			$module = $mm->fetchModule ($moduleName, true);
			if (empty ($module)) {
				throw new Exception ('El módulo solicitado no se encuentra registrado');
			}

			$module->setPresence (Module::PRESENCE_HIDDEN);
			$mm->updateModuleHeader ($module);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @throws Exception
		 * @throws ModuleException
		 */
		public static function enableModule (PearDatabase $adb, $moduleName) {
			$mm     = ModuleManager::getInstance ($adb);
			$module = $mm->fetchModule ($moduleName, true);
			if (empty ($module)) {
				throw new Exception ('El módulo solicitado no se encuentra registrado');
			}

			$module->setPresence (Module::PRESENCE_VISIBLE);
			$mm->updateModuleHeader ($module);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return Application[]|null
		 */
		public static function fetchApplications (PearDatabase $adb) {
			$applications = ApplicationManager::getInstance ($adb)->fetchApplicationHeaders ();
			if (!empty ($applications)) {
				usort (
					$applications,
					function (Application $applicationA, Application $applicationB) {
						return strcmp ($applicationA->getName (), $applicationB->getName ());
					}
				);
			} else {
				$applications = null;
			}
			return $applications;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return GlobalPicklist[]|null
		 */
		public static function fetchAvailableGlobalPicklists (PearDatabase $adb) {
			return GlobalPicklistManager::getInstance ($adb)->fetchPicklists ();
		}

		/**
		 * @return array
		 */
		public static function fetchAvailableFieldTypes () {
			return array (
				'advanced'  => array (
					array ('icon' => 'fa-subscript', 'text' => Translator::translate ('LBL_NUMERO_LINKENED', 'Settings'), 'value' => FieldInterface::UI_TYPE_CALCULATED_LINK),
					array ('icon' => 'fa-ellipsis-h', 'text' => 'Pipeline', 'value' => FieldInterface::UI_TYPE_PIPELINE),
					array ('icon' => 'fa-code', 'text' => 'Campos de lista especiales', 'value' => FieldInterface::UI_TYPE_GLOBAL_PICKLIST),
				),
				'date'      => array (
					array ('icon' => 'fa-calendar-o', 'text' => Translator::translate ('LBL_FECHA', 'Settings'), 'value' => FieldInterface::UI_TYPE_DATE),
				),
				'media'     => array (
					array ('icon' => 'fa-paperclip', 'text' => Translator::translate ('LBL_ATTACHMENTS', 'Settings'), 'value' => FieldInterface::UI_TYPE_ATTACHMENTS),
					array ('icon' => 'fa-cloud', 'text' => Translator::translate ('LBL_IMAGE_DISPLAY', 'Settings'), 'value' => FieldInterface::UI_TYPE_IMAGE_DISPLAY),
				),
				'number'    => array (
					array ('icon' => 'fa-sort-numeric-asc', 'text' => Translator::translate ('LBL_NUMERO', 'Settings'), 'value' => FieldInterface::UI_TYPE_NUMBER),
					array ('icon' => 'fa-usd', 'text' => Translator::translate ('LBL_MONEDA', 'Settings'), 'value' => FieldInterface::UI_TYPE_CURRENCY),
					array ('icon' => 'fa-adjust', 'text' => Translator::translate ('LBL_PORCENTAJE', 'Settings'), 'value' => FieldInterface::UI_TYPE_PERCENTAGE),
				),
				'selection' => array (
					array ('icon' => 'fa-check-square-o', 'text' => Translator::translate ('LBL_CHECK_BOX', 'Settings'), 'value' => FieldInterface::UI_TYPE_CHECKBOX),
					array ('icon' => 'fa-list-alt', 'text' => Translator::translate ('LBL_LISTA', 'Settings'), 'value' => FieldInterface::UI_TYPE_PICKLIST),
					array ('icon' => 'fa-list', 'text' => Translator::translate ('LBL_LISTA_SELECCION_MULTIPLE', 'Settings'), 'value' => FieldInterface::UI_TYPE_MULTI_SELECT),
					array ('icon' => 'fa-external-link', 'text' => Translator::translate ('LBL_REFERENCIA_MODULO', 'Settings'), 'value' => FieldInterface::UI_TYPE_MODULE_REFERENCE),
				),
				'text'      => array (
					array ('icon' => 'fa-font', 'text' => Translator::translate ('LBL_TEXTO', 'Settings'), 'value' => FieldInterface::UI_TYPE_TEXT),
					array ('icon' => 'fa-align-center', 'text' => Translator::translate ('LBL_AREA_DE_TEXTO', 'Settings'), 'value' => FieldInterface::UI_TYPE_TEXTAREA),
					array ('icon' => 'fa-envelope', 'text' => Translator::translate ('LBL_CORREO_ELECTRONICO', 'Settings'), 'value' => FieldInterface::UI_TYPE_EMAIL),
					array ('icon' => 'fa-phone', 'text' => Translator::translate ('LBL_TELEFONO', 'Settings'), 'value' => FieldInterface::UI_TYPE_PHONE),
					array ('icon' => 'fa-globe', 'text' => Translator::translate ('LBL_URL', 'Settings'), 'value' => FieldInterface::UI_TYPE_URL),
				),
			);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function fetchAvailableMenus (PearDatabase $adb) {
			$result = $adb->query ('SELECT * FROM vtiger_parenttab pt ORDER BY sequence');
			if ($adb->num_rows ($result) > 0) {
				$menus = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$menus [] = $row ['parenttab_label'];
				}
			} else {
				$menus = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $menus;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return Module[]|null
		 */
		public static function fetchAvailableEntityTypeModules (PearDatabase $adb) {
			$modules = ModuleManager::getInstance ($adb)->fetchModules (true, array ('ConfigEditor', 'Events', 'historymanager', 'Import', 'Integration', 'ModTracker', 'panelusuarios', 'RecycleBin', 'Tooltip', 'Users'));
			if (!empty ($modules)) {
				$entityTypeModules = array ();
				foreach ($modules as $module) {
					if ((in_array ($module->getPresence (), array (Module::PRESENCE_ALWAYS_HIDDEN, Module::PRESENCE_HIDDEN))) || (!$module->getIsEntityType ()) || ($module->getType () != Module::TYPE_USER)) {
						continue;
					}
					$label                = $module->getLabel ();
					$name                 = $module->getName ();
					$entityTypeModules [] = $module->setLabel (Translator::translate ($label, $name));
				}
				$sortModulesByLabel = function (Module $moduleA, Module $moduleB) {
					return strcmp ($moduleA->getLabel (), $moduleB->getLabel ());
				};
				usort ($entityTypeModules, $sortModulesByLabel);
			} else {
				$entityTypeModules = null;
			}
			return !empty ($entityTypeModules) ? $entityTypeModules : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Module[] $modules
		 *
		 * @return array
		 */
		public static function fetchModuleApplications (PearDatabase $adb, $modules) {
			if (!empty ($modules)) {
				$moduleApplications = array ();
				$am                 = ApplicationManager::getInstance ($adb);
				foreach ($modules as $module) {
					$applications = $am->fetchApplicationHeadersByModuleName ($module->getName ());
					if (!empty ($applications)) {
						foreach ($applications as $application) {
							$moduleApplications [ $module->getName () ][ $application->getCode () ] = $application->getName ();
						}
					} else {
						$moduleApplications [ $module->getName () ] = null;
					}
				}
			} else {
				$moduleApplications = null;
			}
			return $moduleApplications;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array
		 */
		public static function fetchModules (PearDatabase $adb) {
			$modules = ModuleManager::getInstance ($adb)->fetchModules (true, array ('ConfigEditor', 'Events', 'historymanager', 'Import', 'Integration', 'ModTracker', 'panelusuarios', 'RecycleBin', 'Tooltip', 'Users'));
			if (!empty ($modules)) {
				$adminModules = array ();
				$toolModules  = array ();
				$userModules  = array ();
				foreach ($modules as $module) {
					if ($module->getPresence () == Module::PRESENCE_ALWAYS_HIDDEN) {
						continue;
					}
					$label = $module->getLabel ();
					$name  = $module->getName ();
					$type  = $module->getType ();
					if (($type == Module::TYPE_TOOL) || ($name == 'Documents')) {
						$toolModules [] = $module->setLabel (Translator::translate ($label, $name));
					} else if ($type == Module::TYPE_ADMIN) {
						$adminModules [] = $module->setLabel (Translator::translate ($label, $name));
					} else {
						$userModules [] = $module->setLabel (Translator::translate ($label, $name));
					}
				}
				$sortModulesByLabel = function (Module $moduleA, Module $moduleB) {
					return strcmp ($moduleA->getLabel (), $moduleB->getLabel ());
				};
				usort ($adminModules, $sortModulesByLabel);
				usort ($toolModules, $sortModulesByLabel);
				usort ($userModules, $sortModulesByLabel);
			} else {
				$adminModules = null;
				$toolModules  = null;
				$userModules  = null;
			}
			return array (
				'admin' => $adminModules,
				'tool'  => $toolModules,
				'user'  => $userModules,
			);
		}

	}
