<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/Settings/lib/WizardUtils.class.php');
	require_once ('vtlib/Vtiger/Block.php');
	require_once ('vtlib/Vtiger/Event.php');
	require_once ('vtlib/Vtiger/Field.php');
	require_once ('vtlib/Vtiger/Filter.php');
	require_once ('vtlib/Vtiger/Menu.php');
	require_once ('vtlib/Vtiger/Module.php');

	class ModuleCreator {
		private static $INSTANCE            = null;
		private static $LANGUAGE_FILE_NAMES = array ('de_de.lang.php', 'en_us.lang.php', 'es_es.lang.php', 'pt_br.lang.php');

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

		private function assignParentApplication (PearDatabase $adb, $moduleId, $moduleName, $applicationName) {
			$adb->pquery ('UPDATE vtiger_tab SET in_administration=0 WHERE tabid=?', array ($moduleId));

			$result = $adb->pquery ('SELECT config_applicationsid FROM vtiger_config_applications WHERE app_name=?', array ($applicationName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}

			$row           = $adb->fetchByAssoc ($result);
			$applicationId = $row ['config_applicationsid'];

			$adb->pquery ('INSERT INTO vtiger_configapps_tab (config_applicationsid, tabid) VALUES (?, ?)', array ($applicationId, $moduleId));

			$icons   = array ('fa fa-cube yellow-bg', 'fa fa-cube emerald-bg', 'fa fa-cube red-bg', 'fa fa-cube green-bg');
			$icon    = $icons [ rand (0, 3) ];
			$fieldId = $adb->getUniqueID ('vtiger_settings_field');
			$blockId = getSettingsBlockId ($applicationName);
			$link    = "index.php?module={$moduleName}&action=index&parenttab=Settings";
			$adb->pquery (
				'INSERT INTO vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence) VALUES (?, ?, ?, ?, ?, ?, ?)',
				array ($fieldId, $blockId, $moduleName, $icon, '', $link, 0)
			);
		}

		private function createLanguageFile ($moduleName, $moduleLabel, $sourceFilePath, $targetFilePath) {
			if ((!$sourceFilePath) || (!file_exists ($sourceFilePath)) || (!is_file ($sourceFilePath))) {
				return;
			}

			$contents = file_get_contents ($sourceFilePath);
			$contents = str_replace ("'ModuleName'", "'{$moduleName}'", $contents);
			$contents = str_replace ("'SINGLE_ModuleName'", "'SINGLE_{$moduleName}'", $contents);
			$contents = str_replace ("'Module Name'", "'{$moduleLabel}'", $contents);
			$contents = str_replace ("'ModuleName ID'", "'{$moduleName} ID'", $contents);
			$contents = str_replace ("'Module Name ID'", "'{$moduleLabel} ID'", $contents);

			file_put_contents ($targetFilePath, $contents);
		}

		private function createModuleFile ($moduleName, $fieldNames, $fieldLabels, $sourceFilePath, $targetFilePath) {
			if (!$sourceFilePath) {
				return;
			}

			$fields            = array ();
			$names             = array ();
			$searchField       = '';
			$searchFieldName   = '';
			$popupField        = '';
			$alphabeticalField = '';
			$detailField       = '';
			$requiredField     = '';
			$defaultOrderField = '';
			$mandatoryField    = '';
			$n                 = count ($fieldNames);
			for ($i = 0; $i < $n; $i++) {
				if (empty ($fieldNames [ $i ])) {
					continue;
				}
				$fields [] = "'{$fieldLabels [ $i ]}' => array ('{$moduleName}', '{$fieldNames [ $i ]}')";
				$names []  = "'{$fieldLabels [ $i ]}' => '{$fieldNames [ $i ]}'";

				if (!empty ($searchField)) {
					continue;
				}
				$searchField       = "'{$fieldLabels [ $i ]}' => array ('{$moduleName}', '{$fieldNames [ $i ]}')";
				$searchFieldName   = "'{$fieldLabels [ $i ]}' => '{$fieldNames [ $i ]}'";
				$popupField        = "array ('{$fieldNames [ $i ]}')";
				$alphabeticalField = $fieldNames [ $i ];
				$detailField       = $fieldNames [ $i ];
				$requiredField     = "array ('{$fieldNames [ $i ]}' => 1)";
				$defaultOrderField = $fieldNames [ $i ];
				$mandatoryField    = "array ('{$fieldNames [ $i ]}')";
			}
            
            $defaultListLink = 'cod_'.$moduleName;
			$contents = file_get_contents ($sourceFilePath);
			$contents = str_replace ('{$_MODULE_NAME}', $moduleName, $contents);
			$contents = str_replace ('{$_LST_FIELDS}', join (",\n\t\t\t", $fields), $contents);
			$contents = str_replace ('{$_LST_FIELDS_NAME}', join (",\n\t\t\t", $names), $contents);
			$contents = str_replace ('{$_LINK_NAME}', $fieldNames [0], $contents);
            $contents = str_replace ('{$_DEFAULT_LIST_LINK}', $defaultListLink, $contents);
			$contents = str_replace ('{$_SEARCH_FIELDS}', $searchField, $contents);
			$contents = str_replace ('{$_SEARCH_FIELDS_NAME}', $searchFieldName, $contents);
			$contents = str_replace ('{$_POPUP_FIELDS}', $popupField, $contents);
			$contents = str_replace ('{$_ALPHABETICAL_FIELD}', $alphabeticalField, $contents);
			$contents = str_replace ('{$_DETAIL_FIELD}', $detailField, $contents);
			$contents = str_replace ('{$_REQUIRED_FIELD}', $requiredField, $contents);
			$contents = str_replace ('{$_ORDER_FIELD}', $defaultOrderField, $contents);
			$contents = str_replace ('{$_MANDATORY_FIELDS}', $mandatoryField, $contents);
			file_put_contents ($targetFilePath, $contents);
		}

		private function createModuleFiles ($moduleName, $moduleLabel, $moduleFolderPath, $moduleFileNames) {
			$this->deleteDirectory ($moduleFolderPath);
			if (!is_dir ("{$moduleFolderPath}/language")) {
				$oldumask = umask (0);
				mkdir ("{$moduleFolderPath}/language", 0777, true);
				umask ($oldumask);
			}

			foreach ($moduleFileNames as $sourceFileName => $targetFileName) {
				$sourceFilePath = "vtlib/ModuleDir/5.4.0/{$sourceFileName}";
				if ((!file_exists ($sourceFilePath)) || (!is_file ($sourceFilePath))) {
					continue;
				}
				copy ($sourceFilePath, "{$moduleFolderPath}/{$targetFileName}");
			}

			foreach (self::$LANGUAGE_FILE_NAMES as $languageFileName) {
				$this->createLanguageFile ($moduleName, $moduleLabel, "vtlib/ModuleDir/5.4.0/language/{$languageFileName}", "{$moduleFolderPath}/language/{$languageFileName}");
			}
		}

		private function createModule (PearDatabase $adb, $moduleName, $moduleLabel, $parentModuleName, $isEntityType = 0) {
			$module               = new Vtiger_Module ();
			$module->name         = $moduleName;
			$module->label        = $moduleLabel;
			$module->parent       = $parentModuleName;
			$module->isentitytype = $isEntityType;
			$module->save ();

			if ($parentModuleName) {
				$menu = Vtiger_Menu::getInstance ($parentModuleName);
				$menu->addModule ($module);
			}

			$adb->pquery ('UPDATE vtiger_tab SET isplatzilla=0 WHERE tabid=?', array ($module->id));

			return $module;
		}

		private function createModuleAuditFields (array $blocks) {
			if (empty ($blocks)) {
				return;
			}

			/** @var Vtiger_Block $firstBlock */
			$firstBlock = $blocks [0];
			$fields     = array (
				array ('Assigned To', 'smownerid', 'vtiger_crmentity', 'assigned_user_id', 53),
				array ('Created Time', 'createdtime', 'vtiger_crmentity', 'createdtime', 70),
				array ('Modified Time', 'modifiedtime', 'vtiger_crmentity', 'modifiedtime', 70),
			);

			$n = count ($fields);
			for ($i = 0; $i < $n; $i++) {
				$field             = new Vtiger_Field ();
				$field->column     = $fields [ $i ][1];
				$field->columntype = WizardUtils::getFieldColumnType ($fields [ $i ][4], '', '');
				$field->label      = $fields [ $i ][0];
				$field->name       = $fields [ $i ][3];
				$field->table      = $fields [ $i ][2];
				$field->typeofdata = WizardUtils::getFieldTypeOfData ($fields [ $i ][4], '', '');
				$field->uitype     = $fields [ $i ][4];
				$firstBlock->addField ($field);
			}
		}

		private function createModuleBlocks (Vtiger_Module $module, $blockLabels) {
			$blocks = array ();
			$n      = count ($blockLabels);
			for ($i = 0; $i < $n; $i++) {
				if (empty ($blockLabels [ $i ])) {
					continue;
				}

				$block        = new Vtiger_Block ();
				$block->label = $blockLabels [ $i ];
				$module->addBlock ($block);
				$blocks [] = $block;
			}
			return $blocks;
		}

		private function createModuleFields (Vtiger_Module $module, $blocks, $arguments) {
			$totalBlocks = count ($blocks);
			$totalFields = count ($arguments ['nombreCampo']);
			/** @var Vtiger_Block $block */

			for ($i = 0; $i < $totalBlocks; $i++) {
				$blockNumber = $arguments ['numeroBloque'][ $i ];
				$block       = $blocks [ $i ];
				for ($j = 0; $j < $totalFields; $j++) {
					if ((empty ($arguments ['nombreCampo'][ $j ])) || ($arguments ['numeroBloqueCampo'][ $j ] != $blockNumber)) {
						continue;
					}

					$field             = new Vtiger_Field ();
					$field->column     = $arguments ['nombreCampo'][ $j ];
					$field->columntype = WizardUtils::getFieldColumnType ($arguments ['tipoCampo'][ $j ], $arguments ['tamanoCampo'][ $j ], $arguments ['precisionCampo'][ $j ]);
					$field->label      = $this->getFieldLabel ($arguments ['etiquetaCampo'][ $j ], $arguments ['nombreCampo'][ $j ]);
					$field->name       = strtolower ($arguments ['nombreCampo'][ $j ]);
					$field->table      = $module->basetable;
					$field->typeofdata = $this->getFieldTypeOfData ($arguments ['nombreCampo'][ $j ], $arguments ['tipoCampo'][ $j ], $arguments ['tamanoCampo'][ $j ], $arguments ['precisionCampo'][ $j ], $arguments ['campoIdentificador']);
					$field->uitype     = $arguments ['tipoCampo'][ $j ];
					$block->addField ($field);

					if (in_array ($field->uitype, array (15, 33))) {
						$field->setPicklistValues (explode ("\n", $arguments ['valoresCampo'][ $j ]));
					} else if ($field->uitype == 4) {
						$field->setModuleSeqNumber ('configure', $arguments ['nombreCodigo'], $arguments ['prefijoCampo'][ $j ], $arguments ['secuenciaCampo'][ $j ]);
					} else if (in_array ($field->uitype, array (10, 404))) {
						$field->setRelatedModules (array ($arguments ['moduloCampo'][ $j ]));
					}

					if ($arguments ['nombreCampo'][ $j ] == $arguments ['campoIdentificador']) {
						$module->setEntityIdentifier ($field);
					}
				}
			}
		}

		private function createModuleFilters (Vtiger_Module $module, $arguments) {
			$filter            = new Vtiger_Filter ();
			$filter->name      = 'All';
			$filter->isdefault = true;
			$module->addFilter ($filter);

			if (count ($arguments ['columnasFiltro']) <= 1) {
				return;
			}

			$n = count (array_filter ($arguments ['columnasFiltro']));
			for ($i = 0; $i < $n; $i++) {
				$index                      = array_search ($arguments ['columnasFiltro'][ $i ], array_filter ($arguments ['nombreCampo']));
				$field                      = new Vtiger_Field();
				$field->block->module->name = $module->name;
				$field->column              = $arguments ['columnasFiltro'][ $i ];
				$field->columntype          = WizardUtils::getFieldColumnType ($arguments ['tipoCampo'][ $index ], $arguments ['tamanoCampo'][ $index ], $arguments ['precisionCampo'][ $index ]);
				$field->label               = $arguments ['etiquetaCampo'][ $index ];
				$field->name                = strtolower ($arguments ['columnasFiltro'][ $i ]);
				$field->table               = $module->basetable;
				$field->typeofdata          = WizardUtils::getFieldTypeOfData ($arguments ['tipoCampo'][ $index ], $arguments ['tamanoCampo'][ $index ], $arguments ['precisionCampo'][ $index ]);
				$field->uitype              = $arguments ['tipoCampo'][ $index ];
				$filter->addField ($field);
			}
		}

		private function duplicateLanguageFile ($oldModuleName, $oldModuleLabel, $newModuleName, $newModuleLabel, $sourceFilePath, $targetFilePath) {
			if ((!$sourceFilePath) || (!file_exists ($sourceFilePath)) || (!is_file ($sourceFilePath))) {
				return;
			}

			$contents = file_get_contents ($sourceFilePath);
			$contents = str_replace ("'ModuleName'", "'{$newModuleName}'", $contents);
			$contents = str_replace ("'SINGLE_ModuleName'", "'SINGLE_{$newModuleName}'", $contents);
			$contents = str_replace ("'Module Name'", "'{$newModuleLabel}'", $contents);
			$contents = str_replace ("'ModuleName ID'", "'{$newModuleName} ID'", $contents);
			$contents = str_replace ("'Module Name ID'", "'{$newModuleLabel} ID'", $contents);

			$contents = str_replace ("'{$oldModuleName}'", "'{$newModuleName}'", $contents);
			$contents = str_replace ("'SINGLE_{$oldModuleName}'", "'SINGLE_{$newModuleName}'", $contents);
			$contents = str_replace ("'{$oldModuleLabel}'", "'{$newModuleLabel}'", $contents);
			$contents = str_replace ("'{$oldModuleName} ID'", "'{$newModuleName} ID'", $contents);
			$contents = str_replace ("'{$oldModuleLabel} ID'", "'{$newModuleLabel}'", $contents);

			file_put_contents ($targetFilePath, $contents);
		}

		private function duplicateModuleBlock (Vtiger_Block $oldBlock) {
			$newBlock               = new Vtiger_Block ();
			$newBlock->increateview = $oldBlock->increateview;
			$newBlock->indetailview = $oldBlock->indetailview;
			$newBlock->label        = html_entity_decode ($oldBlock->label, ENT_QUOTES, 'UTF-8');
			$newBlock->sequence     = $oldBlock->sequence;
			$newBlock->showtitle    = $oldBlock->showtitle;
			$newBlock->visible      = $oldBlock->visible;
			return $newBlock;
		}

		private function duplicateModuleBlocks (PearDatabase $adb, Vtiger_Module $oldModule, Vtiger_Module $newModule) {
			$oldBlocks = Vtiger_Block::getAllForModule ($oldModule);
			if (empty ($oldBlocks)) {
				return;
			}
			$oldEntityFieldName = $this->getEntityFieldName ($adb, $oldModule);

			/** @var Vtiger_Block $oldBlock */
			foreach ($oldBlocks as $oldBlock) {
				$newBlock = $this->duplicateModuleBlock ($oldBlock);
				$newModule->addBlock ($newBlock);

				$oldBlockFields = Vtiger_Field::getAllForBlock ($oldBlock, $oldModule);
				/** @var Vtiger_Field $oldBlockField */
				foreach ($oldBlockFields as $oldBlockField) {
					$oldFieldPrefixData    = $this->getFieldPrefix ($adb, $oldModule->name);
					$oldRelatedModuleNames = $this->getRelatedModules ($adb, $oldBlockField->id, $oldModule->name);
					$newBlockField         = $this->duplicateModuleField ($oldBlockField, $oldModule, $newModule);
					$newBlock->addField ($newBlockField);

					if (in_array ($oldBlockField->uitype, array (15, 33))) {
						$newBlockField->setPicklistValues (getAllPickListValues ($oldBlockField->name));
					} else if (($oldBlockField->uitype == 4) && (!empty ($oldFieldPrefixData))) {
						$newBlockField->setModuleSeqNumber ('configure', $newModule->name, $oldFieldPrefixData ['prefix'], $oldFieldPrefixData ['start_id']);
					} else if ((in_array ($oldBlockField->uitype, array (10, 404))) && ($oldRelatedModuleNames)) {
						$newBlockField->setRelatedModules ($oldRelatedModuleNames);
					}
					if (($oldEntityFieldName) && ($oldBlockField->name == $oldEntityFieldName)) {
						$newModule->setEntityIdentifier ($newBlockField);
					}
				}
			}
		}

		private function duplicateModuleField (Vtiger_Field $oldBlockField, Vtiger_Module $oldModule, Vtiger_Module $newModule) {
			$newBlockField                = new Vtiger_Field ();
			$newBlockField->defaultvalue  = $oldBlockField->defaultvalue;
			$newBlockField->displaytype   = $oldBlockField->displaytype;
			$newBlockField->generatedtype = $oldBlockField->generatedtype;
			$newBlockField->helpinfo      = html_entity_decode ($oldBlockField->helpinfo, ENT_QUOTES, 'UTF-8');
			$newBlockField->info_type     = $oldBlockField->info_type;
			$newBlockField->label         = html_entity_decode ($oldBlockField->label, ENT_QUOTES, 'UTF-8');
			$newBlockField->masseditable  = $oldBlockField->masseditable;
			$newBlockField->maximumlength = $oldBlockField->maximumlength;
			$newBlockField->presence      = $oldBlockField->presence;
			$newBlockField->quickcreate   = $oldBlockField->quickcreate;
			$newBlockField->quicksequence = $oldBlockField->quicksequence;
			$newBlockField->readonly      = $oldBlockField->readonly;
			$newBlockField->sequence      = $oldBlockField->sequence;
			$newBlockField->table         = $oldBlockField->table == $oldModule->basetable ? $newModule->basetable : $oldBlockField->table;
			$newBlockField->typeofdata    = $oldBlockField->typeofdata;
			$newBlockField->uitype        = $oldBlockField->uitype;
			$newBlockField->column        = str_replace ($oldModule->name, $newModule->name, $oldBlockField->column);
			$newBlockField->name          = str_replace ($oldModule->name, $newModule->name, $oldBlockField->name);
			return $newBlockField;
		}

		private function duplicateModuleFiles (PearDatabase $adb, $platform, $oldModuleId, $oldModuleName, $oldModuleFolderPath, $newModuleName, $arguments) {
			$newModuleLabel     = $arguments ['nombrePublico'];
			$oldModuleLabel     = PlatformUtils::getModuleLabel ($adb, $oldModuleId);
			$oldModuleFileNames = array_diff (scandir ($oldModuleFolderPath), array ('.', '..'));
			if (empty ($oldModuleFileNames)) {
				return;
			}

			$rootFolder = realpath (__DIR__ . '/../../../');
			if (!empty ($platform)) {
				$newModuleFolderPath = "{$rootFolder}/{$platform}/modules/{$newModuleName}";
			} else {
				$newModuleFolderPath = "{$rootFolder}/modules/{$newModuleName}";
			}

			$this->deleteDirectory ($newModuleFolderPath);
			if (!is_dir ("{$newModuleFolderPath}/language")) {
				$oldumask = umask (0);
				mkdir ("{$newModuleFolderPath}/language", 0777, true);
				umask ($oldumask);
			}

			$oldModuleEntityData    = PlatformUtils::getEntityData ($adb, $oldModuleId);
			$newModuleEntityIdField = str_replace ($oldModuleName, $newModuleName, $oldModuleEntityData ['entityidfield']);

			foreach ($oldModuleFileNames as $oldModuleFileName) {
				if (is_dir ("{$oldModuleFolderPath}/{$oldModuleFileName}")) {
					continue;
				}
				if ($oldModuleFileName == "{$oldModuleName}.php") {
					$newModuleFileName = "{$newModuleName}.php";
				} else if ($oldModuleFileName == "{$oldModuleName}Ajax.php") {
					$newModuleFileName = "{$newModuleName}Ajax.php";
				} else if ($oldModuleFileName == "{$oldModuleName}.js") {
					$newModuleFileName = "{$newModuleName}.js";
				} else {
					$newModuleFileName = $oldModuleFileName;
				}

				$contents = file_get_contents ("{$oldModuleFolderPath}/{$oldModuleFileName}");
				$contents = str_replace ($oldModuleEntityData ['entityidfield'], '{$_ENTITY_ID_FIELD}', $contents);
				$contents = str_replace ($oldModuleName, $newModuleName, $contents);
				$contents = str_replace ('{$_ENTITY_ID_FIELD}', $newModuleEntityIdField, $contents);
				file_put_contents ("{$newModuleFolderPath}/{$newModuleFileName}", $contents);
			}

			foreach (self::$LANGUAGE_FILE_NAMES as $languageFileName) {
				$this->duplicateLanguageFile ($oldModuleName, $oldModuleLabel, $newModuleName, $newModuleLabel, "{$oldModuleFolderPath}/language/{$languageFileName}", "{$newModuleFolderPath}/language/{$languageFileName}");
			}
		}

		private function duplicateModuleFilters (PearDatabase $adb, Vtiger_Module $oldModule, Vtiger_Module $newModule) {
			$oldFilters = Vtiger_Filter::getAllForModule ($oldModule);
			if (empty ($oldFilters)) {
				return;
			}

			/** @var Vtiger_Filter $oldFilter */
			foreach ($oldFilters as $oldFilter) {
				$columnNames          = $this->getViewFilterColumnNames ($adb, $oldFilter->id);
				$newFilter            = new Vtiger_Filter ();
				$newFilter->inmetrics = $oldFilter->inmetrics;
				$newFilter->isdefault = $oldFilter->isdefault;
				$newFilter->name      = $oldFilter->name;
				$newFilter->status    = $oldFilter->status;
				$newModule->addFilter ($newFilter);
				foreach ($columnNames as $columnName) {
					$oldField = Vtiger_Field::getInstance ($columnName, $oldModule);
					if (!$oldField) {
						continue;
					}
					$newField        = $this->duplicateModuleField ($oldField, $oldModule, $newModule);
					$newField->table = $newModule->basetable;
					$newFilter->addField ($newField);
				}
			}
		}

		private function duplicateRelatedLists (PearDatabase $adb, Vtiger_Module $oldModule, Vtiger_Module $newModule) {
			$result = $adb->pquery ('SELECT * FROM vtiger_relatedlists WHERE tabid=?', array ($oldModule->id));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$newModule->setRelatedList (Vtiger_Module::getInstance ($row ['related_tabid']), $row ['label'], $row ['actions'], $row ['relfield']);
			}
		}

		private function duplicateTable (PearDatabase $adb, Vtiger_Module $oldModule, Vtiger_Module $newModule) {
			$newModule->initTables ();
			$adb->query ("DROP TABLE IF EXISTS {$newModule->basetable}", true);
			$result = $adb->query ("SHOW CREATE TABLE {$oldModule->basetable}", true);
			if ($adb->num_rows ($result) == 0) {
				return;
			}
			$row = $adb->fetch_array ($result, false);
			$adb->query (str_replace ($oldModule->name, $newModule->name, $row [1]), true);
		}

		private function getEntityFieldName (PearDatabase $adb, Vtiger_Module $module) {
			$result = $adb->pquery ('SELECT fieldname FROM vtiger_entityname WHERE tabid=?', array ($module->id));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['fieldname'];
		}

		private function getFieldLabel ($fieldLabel, $fieldName) {
			return !empty ($fieldLabel) ? $fieldLabel : $fieldName;
		}

		private function getFieldPrefix (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT * FROM vtiger_modentity_num WHERE semodule=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			return $adb->fetchByAssoc ($result, -1, false);
		}

		private function getFieldTypeOfData ($fieldName, $fieldType, $fieldLength, $fieldPrecision, $identifierFieldName) {
			$typeOfData = WizardUtils::getFieldTypeOfData ($fieldType, $fieldLength, $fieldPrecision);
			if ($fieldName == $identifierFieldName) {
				$typeOfData = str_replace ('~O', '~M', $typeOfData);
			}
			return $typeOfData;
		}

		private function getOldModuleFolderPath ($oldModuleName, $platform) {
			$rootFolder = realpath (__DIR__ . '/../../../');
			if ((!empty ($platform)) && (file_exists ("{$rootFolder}/{$platform}/modules/{$oldModuleName}"))) {
				$oldModuleFolderPath = "{$rootFolder}/{$platform}/modules/{$oldModuleName}";
			} else {
				$oldModuleFolderPath = "{$rootFolder}/modules/{$oldModuleName}";
			}
			return $oldModuleFolderPath;
		}

		private function getRelatedModules (PearDatabase $adb, $fieldId, $moduleName) {
			$result = $adb->pquery ('SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=?', array ($fieldId, $moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$moduleNames = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$moduleNames [] = $row ['relmodule'];
			}
			return $moduleNames;
		}

		private function getViewFilterColumnNames (PearDatabase $adb, $viewId) {
			$result = $adb->pquery (
				'SELECT
					cl.*
				FROM
					vtiger_cvcolumnlist cl
					INNER JOIN vtiger_customview cv ON cv.cvid=cl.cvid
				WHERE
					cv.cvid=?
				ORDER BY
					cl.columnindex',
				array ($viewId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$columnNames = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$columnData     = explode (':', $row ['columnname']);
				$columnNames [] = $columnData [1];
			}
			return $columnNames;
		}

		private function registerEventHandlers (Vtiger_Module $module) {
			if (!Vtiger_Event::hasSupport ()) {
				return;
			}
			Vtiger_Event::register ($module, 'vtiger.entity.aftersave', "{$module->name}Handler", "modules/{$module->name}/{$module->name}Handler.php");
			Vtiger_Event::register ($module, 'vtiger.entity.beforesave', "{$module->name}Handler", "modules/{$module->name}/{$module->name}Handler.php");
		}

		private function registerReportAvailability (PearDatabase $adb, $moduleId, $availability) {
			$adb->pquery ('DELETE FROM vtiger_module_report WHERE tabid=?', array ($moduleId));
			if ($availability) {
				$adb->pquery ('INSERT INTO vtiger_module_report (tabid, reportavailable) VALUES (?, ?)', array ($moduleId, $availability));
			}
		}

		private function setRelatedLists (Vtiger_Module $module, $relatedModuleNames, $relatedLabels, $addActions, $selectActions, $patternActions) {
			$n = count ($relatedModuleNames);
			for ($i = 0; $i < $n; $i++) {
				if (empty ($relatedModuleNames [ $i ])) {
					continue;
				}

				if ((empty ($addActions [ $i ])) && (empty ($selectActions [ $i ])) && (empty ($patternActions [ $i ]))) {
					$actions = false;
				} else {
					$actions = array ();
					if (!empty ($addActions [ $i ])) {
						$actions [] = 'ADD';
					}
					if (!empty ($selectActions [ $i ])) {
						$actions [] = 'SELECT';
					}
					if (!empty ($patternActions [ $i ])) {
						$actions [] = 'PATRON';
					}
				}
				$module->setRelatedList (Vtiger_Module::getInstance ($relatedModuleNames [ $i ]), $relatedLabels [ $i ], $actions);
			}
		}

		public function createSimpleModule (PearDatabase $adb, $platform, $moduleName, $moduleLabel, $parentModule) {
			if (!$moduleName) {
				throw new Exception ('No se ha suministrado el nombre del módulo');
			}
			$moduleName       = strtolower ($moduleName);
			$rootFolder       = realpath (__DIR__ . '/../../../');
			$moduleFolderPath = !empty ($platform) ? "{$rootFolder}/{$platform}/modules/{$moduleName}" : "{$rootFolder}/modules/{$moduleName}";
			$moduleFileNames  = array ('indexSimple.php' => 'index.php');
			$module           = $this->createModule ($adb, $moduleName, $moduleLabel, $parentModule);
			$this->createModuleFiles ($moduleName, $moduleLabel, $moduleFolderPath, $moduleFileNames);
			return $module;
		}

		public function createFieldsModule (PearDatabase $adb, $platform, array $arguments) {
			$moduleName = strtolower ($arguments ['nombreCodigo']);
			if (!$moduleName) {
				throw new Exception ('No se ha suministrado el nombre del módulo');
			}
			$moduleLabel = $arguments ['nombrePublico'];
			$rootFolder  = realpath (__DIR__ . '/../../../');
			if (!empty ($platform)) {
				$moduleFolderPath = "{$rootFolder}/{$platform}/modules/{$moduleName}";
			} else {
				$moduleFolderPath = "{$rootFolder}/modules/{$moduleName}";
			}

			$moduleFileNames = array (
				'AddComment.php'           => 'AddComment.php',
				'CalendarView.php'         => 'CalendarView.php',
				'CallRelatedList.php'      => 'CallRelatedList.php',
				'CustomView.php'           => 'CustomView.php',
				'Delete.php'               => 'Delete.php',
				'DeleteAttachment.php'     => 'DeleteAttachment.php',
				'DetailView.php'           => 'DetailView.php',
				'DetailViewAjax.php'       => 'DetailViewAjax.php',
				'EditView.php'             => 'EditView.php',
				'ExportModule.php'         => 'ExportModule.php',
				'ExportRecords.php'        => 'ExportRecords.php',
				'FindDuplicateRecords.php' => 'FindDuplicateRecords.php',
				'Import.php'               => 'Import.php',
				'index.php'                => 'index.php',
				'ListView.php'             => 'ListView.php',
				'ListViewPagging.php'      => 'ListViewPagging.php',
				'MassEdit.php'             => 'MassEdit.php',
				'MassEditSave.php'         => 'MassEditSave.php',
				'MassMail.php'             => 'MassMail.php',
				'MassMailSend.php'         => 'MassMailSend.php',
				'Modal.php'                => 'Modal.php',
				'ModuleFileAjax.php'       => "{$moduleName}Ajax.php",
				'ModuleFile.js'            => "{$moduleName}.js",
				'popupPatron.php'          => 'popupPatron.php',
				'ProcessDuplicates.php'    => 'ProcessDuplicates.php',
				'QuickCreate.php'          => 'QuickCreate.php',
				'Save.php'                 => 'Save.php',
				'SaveChat.php'             => 'SaveChat.php',
				'Settings.php'             => 'Settings.php',
				'TagCloud.php'             => 'TagCloud.php',
				'UnifiedSearch.php'        => 'UnifiedSearch.php',
				'UpdateRelatedRecords.php' => 'UpdateRelatedRecords.php',
				'UploadAttachment.php'     => 'UploadAttachment.php',
			);
			if (isset ($arguments ['isAdmin'])) {
				$inAdministration = true;
				$parentModule     = false;
			} else {
				$inAdministration = false;
				$parentModule     = $arguments ['moduloPadre'];
			}

			$module     = $this->createModule ($adb, $moduleName, $moduleLabel, $parentModule, 1);
			$moduleId   = $module->id;
			$moduleName = $module->name;
			$module->initTables ();
			$blocks = $this->createModuleBlocks ($module, $arguments ['nombreBloque']);
			$this->createModuleFields ($module, $blocks, $arguments);
			$this->createModuleAuditFields ($blocks);
			$this->createModuleFilters ($module, $arguments);
			$this->createModuleFiles ($moduleName, $moduleLabel, $moduleFolderPath, $moduleFileNames);
			$this->createModuleFile ($moduleName, $arguments ['nombreCampo'], $arguments ['etiquetaCampo'], "{$rootFolder}/vtlib/ModuleDir/5.4.0/ModuleFile.php", "{$moduleFolderPath}/{$moduleName}.php");
			$this->setRelatedLists ($module, $arguments ['listaModulos'], $arguments ['labelModulos'], $arguments ['listaAccionAdd'], $arguments ['listaAccionSelect'], $arguments ['listaAccionPatron']);
			$module->initWebservice ();
			$module->setDefaultSharing ('Private');
			$module->enableTools (array ('Import', 'Export'));
			$module->disableTools ('Merge');
			$this->registerEventHandlers ($module);

			$availableForReport = isset ($arguments ['reportAvailable']) && ($arguments ['reportAvailable'] == 'on') ? true : false;
			$this->registerReportAvailability ($adb, $moduleId, $availableForReport);

			if ($inAdministration) {
				$this->assignParentApplication ($adb, $moduleId, $moduleName, $arguments ['appMadre']);
			}
			return $module;
		}

		public function duplicateModule (PearDatabase $adb, $platform, array $arguments) {
			$newModuleName = strtolower ($arguments ['nombreCodigo']);
			if (!$newModuleName) {
				throw new Exception ('No se ha suministrado el nombre del módulo');
			}

			$oldModuleId         = $arguments ['moduloaduplicar'];
			$oldModule           = Vtiger_Module::getInstance ($oldModuleId);
			$oldModuleName       = PlatformUtils::getModuleName ($adb, $oldModuleId);
			$oldModuleFolderPath = $this->getOldModuleFolderPath ($oldModuleName, $platform);
			if (!file_exists ($oldModuleFolderPath)) {
				throw new Exception ("No se encuentran archivos para el módulo {$oldModuleName}");
			}

			$newModuleLabel = $arguments ['nombrePublico'];
			$parentModule   = $arguments ['moduloPadre'];

			$newModule = $this->createModule ($adb, $newModuleName, $newModuleLabel, $parentModule, $oldModule->isentitytype);
			$this->duplicateTable ($adb, $oldModule, $newModule);
			$this->duplicateModuleBlocks ($adb, $oldModule, $newModule);
			$this->duplicateModuleFilters ($adb, $oldModule, $newModule);
			$this->duplicateModuleFiles ($adb, $platform, $oldModuleId, $oldModuleName, $oldModuleFolderPath, $newModuleName, $arguments);
			$this->duplicateRelatedLists ($adb, $oldModule, $newModule);
			$newModule->initWebservice ();
			$newModule->setDefaultSharing ('Private');
			$newModule->enableTools (array ('Import', 'Export'));
			$newModule->disableTools ('Merge');
			$this->registerEventHandlers ($newModule);
			$this->registerReportAvailability ($adb, $newModule->id, true);
			return $newModule;
		}

		public static function getInstance () {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new ModuleCreator ();
			}
			return self::$INSTANCE;
		}

	}
