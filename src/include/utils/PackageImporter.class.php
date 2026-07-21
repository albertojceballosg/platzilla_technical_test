<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('vtlib/Vtiger/Module.php');
	require_once ('vtlib/Vtiger/PackageImport.php');

	class PackageImporter extends Vtiger_PackageImport {

		public function __construct ($exportFolderPath = null) {
			parent::Vtiger_PackageImport ();
			$this->_export_tmpdir = $exportFolderPath != null ? $exportFolderPath : sys_get_temp_dir () . '/vtlib';
			if ((!is_dir ($this->_export_tmpdir)) || (!is_writable ($this->_export_tmpdir))) {
				throw new Exception ("Imposible crear el directorio {$this->_export_tmpdir} o no tiene permiso de escritura");
			}
		}

		private function associateMenu (Vtiger_Module $moduleInstance, $parentTab) {
			if (empty ($parentTab)) {
				return;
			}
			$menuInstance = Vtiger_Menu::getInstance ($parentTab);
			if (!$menuInstance) {
				Vtiger_Menu::createInstance ($parentTab);
				$menuInstance = Vtiger_Menu::getInstance ($parentTab);
			}
			$menuInstance->addModule ($moduleInstance);
		}

		private function createModule ($parentTab) {
			$tabName          = $this->_modulexml->name;
			$tabLabel         = $this->_modulexml->label;
			$tabVersion       = $this->_modulexml->version;
			$isExtension      = $this->isExtension ();
			$vtigerMinVersion = $this->_modulexml->dependencies->vtiger_version;
			$vtigerMaxVersion = $this->_modulexml->dependencies->vtiger_max_version;

			$moduleInstance               = new Vtiger_Module ();
			$moduleInstance->name         = $tabName;
			$moduleInstance->label        = $tabLabel;
			$moduleInstance->parent       = $parentTab;
			$moduleInstance->isentitytype = ($isExtension != true);
			$moduleInstance->version      = (!$tabVersion) ? 0 : $tabVersion;
			$moduleInstance->minversion   = (!$vtigerMinVersion) ? false : $vtigerMinVersion;
			$moduleInstance->maxversion   = (!$vtigerMaxVersion) ? false : $vtigerMaxVersion;
			$moduleInstance->save ();
			return $moduleInstance;
		}

		private function importFieldsDependencies ($moduleNode, Vtiger_Module $moduleInstance) {
			global $adb;
			if ((empty ($moduleNode->fielddependencies)) || (empty ($moduleNode->fielddependencies->fielddependency))) {
				return;
			}

			foreach ($moduleNode->fielddependencies->fielddependency as $dependency) {
				$adb->pquery (
					"INSERT INTO vtiger_fielddependencies (modulename, sourcefieldname, sourcefieldvalue, targetfieldname, targetfieldvisibility) VALUES (?, ?, ?, ?, ?)",
					array ($dependency->modulename, $dependency->sourcefieldname, $dependency->sourcefieldvalue, $dependency->targetfieldname, $dependency->targetfieldvisibility)
				);
			}
		}

		private function importGraphs ($moduleNode, Vtiger_Module $moduleInstance) {
			global $adb;
			if ((empty ($moduleNode->graphs)) || (empty ($moduleNode->graphs->graph))) {
				return;
			}

			foreach ($moduleNode->graphs->graph as $graph) {
				$dateGrouping  = !empty ($graph->dategrouping) ? $graph->dategrouping : null;
				$fieldGrouping = !empty ($graph->fieldgrouping) ? $graph->fieldgrouping : null;
				$sql           = !empty ($graph->sql) ? $graph->sql : null;
				$variables     = !empty ($graph->variables) ? $graph->variables : null;
				$roles         = !empty ($graph->roles) ? $graph->roles : null;
				$adb->pquery (
					'INSERT INTO vtiger_graficos (fld_module, fieldoperation, operation, tipografico, title, roles_grafico, sqlprimarioreporte, varreporte, reporteavanzado, comparar, ishome, fieldgrouping, dategrouping) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($moduleInstance->name, $graph->field, $graph->operation, $graph->type, $graph->title, $roles, $sql, $variables, $graph->isadvanced, $graph->compare, $graph->ishome, $fieldGrouping, $dateGrouping)
				);
			}
		}

		private function importReportAvailability ($moduleNode, Vtiger_Module $moduleInstance) {
			global $adb;
			if (!$moduleInstance->isentitytype) {
				return;
			}

			if (empty ($moduleNode->reportavailability)) {
				$isAvailableForReport = 0;
			} else {
				$isAvailableForReport = intval ($moduleNode->reportavailability);
			}

			$adb->pquery ('INSERT INTO vtiger_module_report (tabid, reportavailable) VALUES (?, ?)', array ($moduleInstance->id, $isAvailableForReport));
		}

		private function isExtension () {
			if (empty ($this->_modulexml->type)) {
				return false;
			}
			$isExtension = false;
			$type        = strtolower ($this->_modulexml->type);
			if ($type == 'extension' || $type == 'language') {
				$isExtension = true;
			}
			return $isExtension;
		}

		private function setEntityIdentifier ($fieldNode, Vtiger_Module $moduleInstance, $fieldInstance) {
			if (empty ($fieldNode->entityidentifier)) {
				return;
			}
			$moduleInstance->entityidfield  = $fieldNode->entityidentifier->entityidfield;
			$moduleInstance->entityidcolumn = $fieldNode->entityidentifier->entityidcolumn;
			$moduleInstance->setEntityIdentifier ($fieldInstance);
		}

		private function setPickListValues ($fieldInstance, $withRoles = false) {
			global $adb, $platPrincipal;
			$platformDatabaseName = "pg_crm_$platPrincipal";
			$instanceDatabaseName = $adb->dbName;

			$tableNames = array (
				"vtiger_{$fieldInstance->name}",
				"vtiger_{$fieldInstance->name}_seq",
			);

			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();

			foreach ($tableNames as $tableName) {
				$result = $masterAdb->query ("SELECT 1 FROM $platformDatabaseName.$tableName LIMIT 1", false);
				if (!$result) {
					return;
				}

				$result = $masterAdb->pquery ('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?', array ($instanceDatabaseName, $tableName), false);
				if ($masterAdb->num_rows ($result)) {
					return;
				}

				$result = $masterAdb->query ("SHOW CREATE TABLE $platformDatabaseName.$tableName", true);
				if ($masterAdb->num_rows ($result) == 0) {
					return;
				}
				$row = $masterAdb->fetch_array ($result, false);

				$sql = str_replace ("`$tableName`", "`$instanceDatabaseName`.`$tableName`", $row [1]);
				$masterAdb->query ($sql, true);

				$masterAdb->query ("INSERT INTO $instanceDatabaseName.$tableName SELECT * FROM $platformDatabaseName.$tableName", true);
			}

			$masterAdb->pquery ("INSERT INTO $instanceDatabaseName.vtiger_picklist (name) VALUES (?)", array ($fieldInstance->name), true);

			if (!$withRoles) {
				return;
			}

			$result = $masterAdb->pquery ("SELECT pl.picklistid FROM $instanceDatabaseName.vtiger_picklist pl WHERE pl.name=?", array ($fieldInstance->name), true);
			if ($masterAdb->num_rows ($result) == 0) {
				return;
			}
			$row        = $masterAdb->fetch_array ($result);
			$pickListID = $row ['picklistid'];

			$result = $masterAdb->query ("SHOW KEYS FROM $instanceDatabaseName.{$tableNames [0]} WHERE Key_name='PRIMARY'", true);
			if ((!$result) || ($masterAdb->num_rows ($result) == 0)) {
				return;
			}
			$row                 = $masterAdb->fetch_array ($result);
			$primaryKeyFieldName = $row ['column_name'];

			$masterAdb->query ("INSERT IGNORE INTO $instanceDatabaseName.vtiger_role2picklist (roleid, picklistvalueid, picklistid, sortid) SELECT r.roleid, t.picklist_valueid, $pickListID, t.{$primaryKeyFieldName}  FROM vtiger_role r JOIN {$tableNames [0]} t WHERE r.roleid IN (SELECT roleid FROM $instanceDatabaseName.vtiger_role)", true);
		}

		private function setRelatedModules ($fieldNode, Vtiger_Field $fieldInstance) {
			if ((empty ($fieldNode->relatedmodules)) || (empty ($fieldNode->relatedmodules->relatedmodule))) {
				return;
			}
			$relatedModules = array ();
			foreach ($fieldNode->relatedmodules->relatedmodule as $relatedmodulenode) {
				$relatedModules [] = $relatedmodulenode;
			}
			$fieldInstance->setRelatedModules ($relatedModules);
		}

		public function __parseManifestFile (Vtiger_Unzip $unzip) {
			$manifestfile = $this->__getManifestFilePath ();
			$unzip->unzip ('manifest.xml', $manifestfile);
			$this->_modulexml = simplexml_load_file ($manifestfile, null, LIBXML_NOCDATA);
			unlink ($manifestfile);
		}

		public function import ($zipFile) {
			$unzip = new Vtiger_Unzip ($zipFile);
			// If data is not yet available
			if (empty ($this->_modulexml)) {
				$this->__parseManifestFile ($unzip);
			}
			$this->import_Module ();
		}

		public function import_Block ($moduleNode, Vtiger_Module $moduleInstance, $blockNode) {
			$blockInstance               = new Vtiger_Block ();
			$blockInstance->increateview = $blockNode->createview;
			$blockInstance->indetailview = $blockNode->detailview;
			$blockInstance->ineditview   = $blockNode->editview;
			$blockInstance->label        = $blockNode->label;
			$blockInstance->sequence     = $blockNode->sequence;
			$blockInstance->showtitle    = $blockNode->showtitle;
			$blockInstance->visible      = $blockNode->visible;
			$moduleInstance->addBlock ($blockInstance);
			return $blockInstance;
		}

		public function import_Blocks ($moduleNode, $moduleInstance) {
			if (empty ($moduleNode->blocks) || empty ($moduleNode->blocks->block)) {
				return;
			}
			foreach ($moduleNode->blocks->block as $blockNode) {
				$blockInstance = $this->import_Block ($moduleNode, $moduleInstance, $blockNode);
				$this->import_Fields ($blockNode, $blockInstance, $moduleInstance);
			}
		}

		public function import_CustomView ($moduleNode, Vtiger_Module $moduleInstance, $customViewNode) {
			$viewName   = $customViewNode->viewname;
			$setDefault = $customViewNode->setdefault;
			$setMetrics = $customViewNode->setmetrics;

			$filterInstance            = new Vtiger_Filter();
			$filterInstance->name      = $viewName;
			$filterInstance->isdefault = $setDefault;
			$filterInstance->inmetrics = $setMetrics;

			$moduleInstance->addFilter ($filterInstance);

			foreach ($customViewNode->fields->field as $fieldNode) {
				$fieldInstance = $this->__GetModuleFieldFromCache ($moduleInstance, $fieldNode->fieldname);
				if (!$fieldInstance) {
					continue;
				}

				$filterInstance->addField ($fieldInstance, $fieldNode->columnindex);

				if (!empty ($fieldNode->rules->rule)) {
					foreach ($fieldNode->rules->rule as $rulenode) {
						$filterInstance->addRule ($fieldInstance, $rulenode->comparator, $rulenode->value, $rulenode->columnindex);
					}
				}
			}
		}

		public function import_Field ($blockNode, Vtiger_Block $blockInstance, Vtiger_Module $moduleInstance, $fieldNode) {
			$fieldInstance                = new Vtiger_Field ();
			$fieldInstance->name          = $fieldNode->fieldname;
			$fieldInstance->label         = $fieldNode->fieldlabel;
			$fieldInstance->table         = $fieldNode->tablename;
			$fieldInstance->column        = $fieldNode->columnname;
			$fieldInstance->uitype        = $fieldNode->uitype;
			$fieldInstance->generatedtype = $fieldNode->generatedtype;
			$fieldInstance->readonly      = $fieldNode->readonly;
			$fieldInstance->presence      = $fieldNode->presence;
			$fieldInstance->defaultvalue  = $fieldNode->defaultvalue;
			$fieldInstance->maximumlength = $fieldNode->maximumlength;
			$fieldInstance->sequence      = $fieldNode->sequence;
			$fieldInstance->quickcreate   = $fieldNode->quickcreate;
			$fieldInstance->quicksequence = $fieldNode->quickcreatesequence;
			$fieldInstance->typeofdata    = $fieldNode->typeofdata;
			$fieldInstance->displaytype   = $fieldNode->displaytype;
			$fieldInstance->info_type     = $fieldNode->info_type;

			if (!empty ($fieldNode->helpinfo)) {
				$fieldInstance->helpinfo = $fieldNode->helpinfo;
			}

			if (isset ($fieldNode->masseditable)) {
				$fieldInstance->masseditable = $fieldNode->masseditable;
			}

			if ((isset ($fieldNode->columntype)) && (!empty ($fieldNode->columntype))) {
				$fieldInstance->columntype = $fieldNode->columntype;
			}

			$blockInstance->addField ($fieldInstance);
			$this->setEntityIdentifier ($fieldNode, $moduleInstance, $fieldInstance);

			if (!empty ($fieldNode->picklistvalues)) {
				$this->setPicklistValues ($fieldInstance, ($fieldInstance->uitype == '16') ? false : true);
			}

			$this->setRelatedModules ($fieldNode, $fieldInstance);

			$this->__AddModuleFieldToCache ($moduleInstance, $fieldNode->fieldname, $fieldInstance);
			return $fieldInstance;
		}

		public function import_GridField ($gridnode, $moduleInstance, $fieldNode) {
			global $adb;

			$sql        = 'SELECT fieldid FROM vtiger_field WHERE (fieldname=?) AND (tabid=?) AND (uitype=2202)';
			$parameters = array ($gridnode->name, $moduleInstance->id);
			$result     = $adb->pquery ($sql, $parameters, true);
			if ($adb->num_rows ($result) == 0) {
				return;
			}
			list ($fieldId) = $adb->fetch_row ($result);

			if (!empty ($fieldNode->values)) {
				$values = array ();
				foreach ($fieldNode->values->value as $value) {
					$values [] = html_entity_decode ($value, null, 'UTF-8');
				}
				$values = json_encode ($values);
			} else {
				$values = null;
			}

			$sql        = 'INSERT IGNORE INTO vtiger_subfields (fieldid, name, label, sequence, uitype, length, `precision`, defaultvalue, `values`, relmodule) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
			$parameters = array (
				$fieldId,
				$fieldNode->name,
				$fieldNode->label,
				$fieldNode->sequence,
				$fieldNode->uitype,
				$fieldNode->length,
				$fieldNode->precision,
				$fieldNode->defaultvalue,
				$values,
				$fieldNode->relmodule,
			);
			$adb->pquery ($sql, $parameters, true);
		}

		public function import_GridFields ($gridNode, $moduleInstance) {
			if ((empty ($gridNode->fields)) || (empty ($gridNode->fields->field))) {
				return;
			}

			global $adb;
			$adb->pquery ("INSERT INTO vtiger_field (fieldid, tabid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, readonly, presence, sequence, block, displaytype, typeofdata, quickcreate, info_type, masseditable) VALUES ((SELECT id+1 FROM vtiger_field_seq), ?, ?, ?, 1, 2202, ?, ?, 1, -1, 1, -1, 1, 'D~O', 1, 'BAS', 1)", array ($moduleInstance->id, $gridNode->name, "vtiger_{$this->_modulexml->name}", $gridNode->name, $gridNode->label), true);
			$adb->query ('UPDATE vtiger_field_seq SET id=id+1', true);
			foreach ($gridNode->fields->field as $fieldNode) {
				$this->import_GridField ($gridNode, $moduleInstance, $fieldNode);
			}
		}

		public function import_Module () {
			$parentTab      = (string) $this->_modulexml->parent;
			$moduleInstance = $this->createModule ($parentTab);
			$this->associateMenu ($moduleInstance, $parentTab);
			$this->import_Tables ($this->_modulexml);
			$this->import_Blocks ($this->_modulexml, $moduleInstance);
			$this->importReportAvailability ($this->_modulexml, $moduleInstance);
			$this->importFieldsDependencies ($this->_modulexml, $moduleInstance);
			$this->import_CustomViews ($this->_modulexml, $moduleInstance);
			$this->import_SharingAccess ($this->_modulexml, $moduleInstance);
			$this->import_Events ($this->_modulexml, $moduleInstance);
			$this->import_Actions ($this->_modulexml, $moduleInstance);
			$this->import_CustomLinks ($this->_modulexml, $moduleInstance);
			$this->import_CronTasks ($this->_modulexml);
			$this->import_Grids ($this->_modulexml, $moduleInstance);
			$this->import_ModuleIncrementalCode ($this->_modulexml);
			$this->importGraphs ($this->_modulexml, $moduleInstance);
			Vtiger_Module::fireEvent ($moduleInstance->name, Vtiger_Module::EVENT_MODULE_POSTINSTALL);
			$moduleInstance->initWebservice ();
		}

		public function import_ModuleIncrementalCode ($moduleNode) {
			if (empty ($moduleNode->modulecode)) {
				return;
			}

			global $adb;
			$adb->pquery (
				'INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES ((SELECT id+1 FROM vtiger_modentity_num_seq), ?, ?, ?, ?, ?)',
				array ($moduleNode->modulecode->semodule, $moduleNode->modulecode->prefix, $moduleNode->modulecode->start_id, $moduleNode->modulecode->cur_id, $moduleNode->modulecode->active),
				true
			);
			$adb->query ('UPDATE vtiger_modentity_num_seq SET id=id+1', true);
		}

		public function importRelatedLists ($zipFile) {
			$unzip = new Vtiger_Unzip ($zipFile);
			// If data is not yet available
			if (empty ($this->_modulexml)) {
				$this->__parseManifestFile ($unzip);
			}
			$tabName        = $this->_modulexml->name;
			$moduleInstance = Vtiger_Module::getInstance ($tabName);
			$this->import_RelatedLists ($this->_modulexml, $moduleInstance);
		}

		public function import_Tables ($moduleNode) {
			if ((empty ($moduleNode->tables)) || (empty ($moduleNode->tables->table))) {
				return;
			}

			// Import the table via queries
			foreach ($moduleNode->tables->table as $tableNode) {
				$tableName = $tableNode->name;
				if (Vtiger_Utils::CheckTable ($tableName)) {
					continue;
				}
				$tableSQL = $tableNode->sql;
				Vtiger_Utils::ExecuteQuery ($tableSQL);
			}
		}

	}
