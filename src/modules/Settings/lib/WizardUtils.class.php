<?php
	require_once ('include/platzilla/Managers/GlobalPicklistManager.php');
	require_once ('include/platzilla/Objects/Field.php');
	require_once ('include/platzilla/Objects/GridField.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');

	abstract class WizardUtils {
		const MODULE_NOT_REGISTERED               = 0;
		const MODULE_REGISTERED_IN_MASTER         = 1;
		const MODULE_REGISTERED_IN_THIS_INSTANCE  = 2;
		const MODULE_REGISTERED_IN_OTHER_INSTANCE = 3;

		private static function getMasterPlatformModuleLabels () {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->query ('SELECT tablabel FROM vtiger_tab');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row ['tablabel'];
			}
			return $modules;
		}

		private static function getMasterPlatformModuleNames () {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->query ('SELECT name FROM vtiger_tab');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row ['name'];
			}
			return $modules;
		}

		private static function getChildFolders (PearDatabase $adb, $parentTabId) {
			if (!$parentTabId) {
				return null;
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_parenttab WHERE padre=? ORDER BY sequence', array ($parentTabId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			$folders = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$children   = self::getChildFolders ($adb, $row ['parenttabid']);
				$folders [] = array (
					'id'        => $row ['parenttabid'],
					'color'     => $row ['color'],
					'iconclass' => $row ['iconclass'],
					'sequence'  => $row ['sequence'],
					'state'     => 'closed',
					'text'      => $row ['parenttab_label'],
					'visible'   => $row ['visible'],
					'children'  => $children,
				);
			}
			return $folders;
		}

		private static function getChildModules (PearDatabase $adb, $parentTabId) {
			if (!$parentTabId) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					NULL AS parenttab_label,
					t.tabid,
					t.name,
					t.tablabel,
					t.presence,
					p.sequence
				FROM
					vtiger_parenttabrel p
					INNER JOIN vtiger_tab t ON (t.tabid=p.tabid)
				WHERE
					t.presence=0 AND
					p.parenttabid=?
				ORDER BY
					p.sequence',
				array ($parentTabId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = array (
					'checked'   => $row ['presence'] == 0 ? 'true' : 'false',
					'color'     => $row ['color'],
					'iconclass' => $row ['iconclass'],
					'sequence'  => $row ['sequence'],
					'tabid'     => $row ['tabid'],
					'text'      => $row ['tablabel'],
					'visible'   => $row ['visible'],
				);
			}
			return $modules;
		}

		private static function getChildFoldersAndModules (PearDatabase $adb, $parentTabId) {
			if (!$parentTabId) {
				return array ();
			}
			$folders = self::getChildFolders ($adb, $parentTabId);
			$modules = self::getChildModules ($adb, $parentTabId);
			return array_merge (
				is_array ($folders) ? $folders : array (),
				is_array ($modules) ? $modules : array ()
			);
		}

		private static function getOrphanModules (PearDatabase $adb) {
			$result = $adb->query (
				'SELECT
					-1 AS parenttabid,
					NULL AS parenttab_label,
					t.tabid,
					t.name,
					t.tablabel,
					t.presence
				FROM
					vtiger_tab t
				WHERE
					t.presence=0 AND
					t.tabid NOT IN (SELECT tabid FROM vtiger_parenttabrel)
				ORDER BY
					t.tablabel'
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = array (
					'id'        => $row ['tabid'],
					'checked'   => $row ['presence'] == 0 ? 'true' : 'false',
					'color'     => '',
					'iconclass' => '',
					'sequence'  => 0,
					'text'      => $row ['tablabel'],
					'visible'   => $row ['visible'],
				);
			}
			return $modules;
		}

		private static function getRelatedParentModuleIds (PearDatabase $adb, $tabId) {
			if (!$tabId) {
				return null;
			}

			$result = $adb->pquery ('SELECT parenttabid FROM vtiger_parenttabrel WHERE tabid=?', array ($tabId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$moduleIds = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$moduleIds [] = $row ['parenttabid'];
			}
			return $moduleIds;
		}

		private static function getDateTimeColumnType ($type) {
			if (in_array ($type, array (5, 6))) {
				return 'DATE ';
			}
			if (in_array ($type, array (14))) {
				return 'TIME ';
			}
			if (in_array ($type, array (70))) {
				return 'DATETIME ';
			}
			return '';
		}

		private static function getVarcharColumnType ($type, $length) {
			if (in_array ($type, array (1, 4, 10))) {
				if ($length == '') {
					$length = 255;
				}
				return "VARCHAR($length)";
			}
			if ($type == 11) {
				return 'VARCHAR(30)';
			}
			if ($type == 13) {
				return 'VARCHAR(50)';
			}
			if (in_array ($type, array (15, 16, 17, 83, 85, 255, 257))) {
				return 'VARCHAR(255)';
			}
			if ($type == 56) {
				return 'VARCHAR(3)';
			}
			if ($type == 108) {
				return 'VARCHAR(127)';
			}
			if ($type == 404) {
				return 'VARCHAR(100)';
			}
			return $type;
		}

		private static function getNumericTypeOfData ($type, $length, $precision) {
			if ($type == 7) {
				return "NN~O~{$length},{$precision}";
			}
			if (in_array ($type, array (9, 71))) {
				if ($length == '') {
					$length = 10;
				}
				if ($precision == '') {
					$precision = 2;
				}
				return "N~O~{$length},{$precision}";
			}
			return '';
		}

		private static function getVarcharTypeOfData ($type, $length) {
			if (in_array ($type, array (1, 4))) {
				return "V~O~LE~{$length}";
			}
			if (in_array ($type, array (10, 11, 15, 16, 17, 21, 33, 52, 83, 85, 256, 257, 258, 404))) {
				return 'V~O';
			}
			if ($type == 108) {
				return 'V~O~LE~127';
			}
			if ($type == 53) {
				return 'V~M';
			}
			return '';
		}

		private static function rebuildParentTabChildren (PearDatabase $adb, $elements, $order, $color) {
			foreach ($elements as $index => $values) {
				$parentTabName = str_replace ('&aacute;', 'á', str_replace ('&oacute;', 'ó', str_replace ('&amp;', '&', html_entity_decode (htmlspecialchars ($values ['text'])))));
				$parentTabId   = $values ['id'];
				$tabId         = $values ['tabid'];
				$children      = $values ['children'];

				if (count ($children) > 0) {
					if (!$parentTabId) {
						$result      = $adb->query ('SELECT IFNULL(MAX(parenttabid) + 1, 1) AS parenttabid FROM vtiger_parenttab');
						$row         = $adb->fetchByAssoc ($result);
						$parentTabId = $row ['parenttabid'];
						$adb->pquery (
							'INSERT INTO vtiger_parenttab (parenttabid, parenttab_label, sequence, padre, color) VALUES (?, ?, ?, 0, ?)',
							array ($parentTabId, $parentTabName, $order, $parentTabId, $color)
						);
					} else if (($parentTabId) && ($parentTabId != '-1')) {
						$adb->pquery (
							'UPDATE vtiger_parenttab SET parenttab_label=?, sequence=?, visible=0, padre=? WHERE parenttabid=?',
							array ($parentTabName, $index, $parentTabId, $parentTabId)
						);
					}
					self::rebuildParentTabChildren ($adb, $children, $index, $color);
				} else if ((!self::isModuleRegistered ($adb, $tabId)) && (self::isParentModuleRegistered ($adb, $parentTabId)) && ($parentTabId != -1)) {
					$adb->pquery ('INSERT INTO vtiger_parenttabrel (parenttabid, tabid, sequence) VALUES (?, ?, ?)', array ($parentTabId, $tabId, $index));
				}
			}
		}

		public static function getAllActiveApplications (PearDatabase $adb) {
			$result = $adb->query ("SELECT config_applicationsid, app_name FROM vtiger_config_applications WHERE app_status='Activa'");
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$applications = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$applications [] = $row;
			}
			return $applications;
		}

		public static function getAllParentModules (PearDatabase $adb) {
			$result = $adb->query ('SELECT parenttabid, parenttab_label FROM vtiger_parenttab ORDER BY sequence');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$modules [] = $row;
			}
			return $modules;
		}

		public static function getFieldColumnType ($type, $length, $precision = 0) {
			if (in_array ($type, array (1, 4, 10, 11, 13, 15, 17, 56, 83, 85, 108, 255, 257, 404))) {
				return self::getVarcharColumnType ($type, $length);
			}
			if (in_array ($type, array (7, 9, 71))) {
				if ($length == '') {
					$length = 10;
				}
				if ($precision == '') {
					$precision = 2;
				}
				return "NUMERIC($length,$precision)";
			}
			if (in_array ($type, array (5, 6, 14, 70))) {
				return self::getDateTimeColumnType ($type);
			}
			if (in_array ($type, array (21, 33, 52, 256, 258))) {
				return 'TEXT ';
			}
			if (in_array ($type, array (53))) {
				return 'INT(19) ';
			}
			return '';
		}

		public static function getFieldTypeOfData ($type, $length, $precision = 0) {
			if (in_array ($type, array (1, 4, 10, 11, 15, 16, 17, 21, 33, 52, 53, 83, 85, 108, 256, 257, 258, 404))) {
				return self::getVarcharTypeOfData ($type, $length);
			}
			if (in_array ($type, array (5, 6))) {
				return 'D~O';
			}
			if (in_array ($type, array (7, 9, 71))) {
				return self::getNumericTypeOfData ($type, $length, $precision);
			}
			if ($type == 13) {
				return 'E~O';
			}
			if ($type == 56) {
				return 'C~O';
			}
			if ($type == 70) {
				return 'DT~O';
			}
			return $type;
		}

		public static function getFieldTypesAsOptions ($isGrid = false) {
			if ($isGrid) {
				return array (
					array ('icon' => 'fa-font', 'text' => getTranslatedString ('LBL_TEXTO'), 'value' => GridFieldInterface::UI_TYPE_TEXT),
					array ('icon' => 'fa-calendar-o', 'text' => getTranslatedString ('LBL_FECHA'), 'value' => GridFieldInterface::UI_TYPE_DATE),
					array ('icon' => 'fa-sort-numeric-asc', 'text' => getTranslatedString ('LBL_NUMERO'), 'value' => GridFieldInterface::UI_TYPE_NUMBER),
					array ('icon' => 'fa-adjust', 'text' => getTranslatedString ('LBL_PORCENTAJE'), 'value' => GridFieldInterface::UI_TYPE_PERCENTAGE),
					array ('icon' => 'fa-external-link', 'text' => getTranslatedString ('LBL_REFERENCIA_MODULO'), 'value' => GridFieldInterface::UI_TYPE_MODULE_REFERENCE),
					array ('icon' => 'fa-list-alt', 'text' => getTranslatedString ('LBL_LISTA'), 'value' => GridFieldInterface::UI_TYPE_PICKLIST),
					array ('icon' => 'fa-globe', 'text' => getTranslatedString ('LBL_URL'), 'value' => GridFieldInterface::UI_TYPE_URL),
					array ('icon' => 'fa-align-center', 'text' => getTranslatedString ('LBL_AREA_DE_TEXTO'), 'value' => GridFieldInterface::UI_TYPE_TEXTAREA),
					array ('icon' => 'fa-check-square-o', 'text' => getTranslatedString ('LBL_CHECK_BOX'), 'value' => GridFieldInterface::UI_TYPE_CHECKBOX),
					array ('icon' => 'fa-check-square-o', 'text' => getTranslatedString ('LBL_CALCULATED_FIELDS'), 'value' => GridFieldInterface::UI_TYPE_CALCULATED),
					array ('icon' => 'fa-paperclip', 'text' => getTranslatedString ('LBL_ATTACHMENTS'), 'value' => FieldInterface::UI_TYPE_ATTACHMENTS),
				);
			}

			return array (
				array ('icon' => 'fa-font', 'text' => getTranslatedString ('LBL_TEXTO'), 'value' => FieldInterface::UI_TYPE_TEXT),
				array ('icon' => 'fa-code', 'text' => getTranslatedString ('LBL_CODIGO_AUTOMATICO'), 'value' => FieldInterface::UI_TYPE_CODE),
				array ('icon' => 'fa-calendar-o', 'text' => getTranslatedString ('LBL_FECHA'), 'value' => FieldInterface::UI_TYPE_DATE),
				array ('icon' => 'fa-sort-numeric-asc', 'text' => getTranslatedString ('LBL_NUMERO'), 'value' => FieldInterface::UI_TYPE_NUMBER),
				array ('icon' => 'fa-subscript', 'text' => getTranslatedString ('LBL_NUMERO_LINKENED'), 'value' => FieldInterface::UI_TYPE_CALCULATED_LINK),
				array ('icon' => 'fa-adjust', 'text' => getTranslatedString ('LBL_PORCENTAJE'), 'value' => FieldInterface::UI_TYPE_PERCENTAGE),
				array ('icon' => 'fa-external-link', 'text' => getTranslatedString ('LBL_REFERENCIA_MODULO'), 'value' => FieldInterface::UI_TYPE_MODULE_REFERENCE),
				array ('icon' => 'fa-phone', 'text' => getTranslatedString ('LBL_TELEFONO'), 'value' => FieldInterface::UI_TYPE_PHONE),
				array ('icon' => 'fa-envelope', 'text' => getTranslatedString ('LBL_CORREO_ELECTRONICO'), 'value' => FieldInterface::UI_TYPE_EMAIL),
				array ('icon' => 'fa-list-alt', 'text' => getTranslatedString ('LBL_LISTA'), 'value' => FieldInterface::UI_TYPE_PICKLIST),
				array ('icon' => 'fa-globe', 'text' => getTranslatedString ('LBL_URL'), 'value' => FieldInterface::UI_TYPE_URL),
				array ('icon' => 'fa-file-video-o', 'text' => getTranslatedString ('LBL_FIELD_VIDEO'), 'value' => FieldInterface::UI_TYPE_VIDEO),
				array ('icon' => 'fa-align-center', 'text' => getTranslatedString ('LBL_AREA_DE_TEXTO'), 'value' => FieldInterface::UI_TYPE_TEXTAREA),
				array ('icon' => 'fa-list', 'text' => getTranslatedString ('LBL_LISTA_SELECCION_MULTIPLE'), 'value' => FieldInterface::UI_TYPE_MULTI_SELECT),
				array ('icon' => 'fa-check-square-o', 'text' => getTranslatedString ('LBL_CHECK_BOX'), 'value' => FieldInterface::UI_TYPE_CHECKBOX),
				array ('icon' => 'fa-usd', 'text' => getTranslatedString ('LBL_MONEDA'), 'value' => FieldInterface::UI_TYPE_CURRENCY),
				array ('icon' => 'fa-paperclip', 'text' => getTranslatedString ('LBL_ATTACHMENTS'), 'value' => FieldInterface::UI_TYPE_ATTACHMENTS),
				array ('icon' => 'fa-cloud', 'text' => getTranslatedString ('LBL_IMAGE_DISPLAY'), 'value' => FieldInterface::UI_TYPE_IMAGE_DISPLAY),
				array ('icon' => 'fa-ellipsis-h', 'text' => 'Pipeline', 'value' => FieldInterface::UI_TYPE_PIPELINE),
				array ('icon' => 'fa-code', 'text' => 'Campos de lista especiales', 'value' => FieldInterface::UI_TYPE_GLOBAL_PICKLIST),
				array ('icon' => 'fa-code', 'text' => getTranslatedString ('LBL_APP_FIELD'), 'value' => FieldInterface::UI_TYPE_APP),
			);
		}

		public static function getFieldListAsOptions ($labels = null, $names = null, $name = 'campoIdentificador') {
			$options = array ();
			if ($name != 'campoIdentificador') {
				$options [] = array (
					'text'  => getTranslatedString ('LBL_SELECCIONAR'),
					'value' => '',
				);
			}
			if (($names) && (is_array ($names)) && (count ($names) > 0)) {
				$n = count ($names);
				for ($i = 0; $i <= $n; $i++) {
					if (!empty ($names [ $i ])) {
						$options [] = array (
							'text'  => $labels [ $i ],
							'value' => $names [ $i ],
						);
					}
				}
			}
			return $options;
		}

		public static function getFoldersAndModules (PearDatabase $adb) {
			existeCampoTabla ('color', 'vtiger_parenttab', 'ALTER TABLE vtiger_parenttab ADD COLUMN color VARCHAR(50) NULL AFTER padre');
			$result = $adb->query ('SELECT * FROM vtiger_parenttab WHERE padre=0 ORDER BY sequence');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$module     = array (
					'id'        => $row ['parenttabid'],
					'color'     => $row ['color'],
					'iconclass' => $row ['iconclass'],
					'sequence'  => $row ['sequence'],
					'text'      => $row ['parenttab_label'],
					'visible'   => $row ['visible'],
					'children'  => self::getChildFoldersAndModules ($adb, $row ['parenttabid']),
				);
				$modules [] = $module;
			}

			$orphanModules = self::getOrphanModules ($adb);
			$modules []    = array (
				'id'        => -1,
				'color'     => '',
				'iconclass' => '',
				'sequence'  => 5,
				'state'     => 'closed',
				'text'      => 'Modulos Sin Padre',
				'visible'   => '0',
				'children'  => $orphanModules,
			);

			return array (
				array (
					'text'     => 'Menu',
					'children' => $modules,
				),
			);
		}

		public static function getGlobalPicklists (PearDatabase $adb) {
			return GlobalPicklistManager::getInstance ($adb)->fetchPicklists ();
		}

		public static function getModuleListAsOptions (PearDatabase $adb) {
			$result = $adb->query ('SELECT name, tablabel FROM vtiger_tab WHERE presence IN (0, 2) AND isentitytype=1');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$options = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$options [] = array (
					'text'  => getTranslatedString ($row ['tablabel']),
					'value' => $row ['name'],
				);
			}
			usort (
				$options,
				function ($optionA, $optionB) {
					if ($optionA ['text'] === $optionB ['text']) {
						return 0;
					}
					return (($optionA ['text'] < $optionB ['text']) ? -1 : 1);
				}
			);
			return $options;
		}

		public static function isModuleRegistered (PearDatabase $adb, $tabId) {
			$result = $adb->pquery ('SELECT tabid FROM vtiger_tab WHERE tabid=?', array ($tabId));
			return ($adb->num_rows ($result) > 0);
		}

		public static function isParentModuleRegistered (PearDatabase $adb, $parentTabId) {
			$result = $adb->pquery ('SELECT parenttabid FROM vtiger_parenttab WHERE parenttabid=?', array ($parentTabId));
			return ($adb->num_rows ($result) > 0);
		}

		public static function updateParentModulesTable (PearDatabase $adb) {
			$result = $adb->query ("SHOW COLUMNS FROM vtiger_parenttab LIKE 'padre'");
			if (!$result) {
				return false;
			}

			$row = $adb->num_rows ($result) > 0 ? $adb->fetchByAssoc ($result) : null;
			if ((!$row) || (!isset ($row ['field']))) {
				$adb->query ("ALTER TABLE vtiger_parenttab ADD padre INT(11) NOT NULL DEFAULT '0'");
				return true;
			}

			if ($row ['field'] == 'padre') {
				return true;
			}

			return false;
		}

		public static function whereIsModuleLabelRegistered (PearDatabase $adb, $moduleLabel) {
			$masterModuleLabels = self::getMasterPlatformModuleLabels ();
			if (in_array ($moduleLabel, $masterModuleLabels)) {
				return self::MODULE_REGISTERED_IN_MASTER;
			}

			$result = $adb->pquery ('SELECT tabid FROM vtiger_tab WHERE tablabel=?', array ($moduleLabel));
			return ($adb->num_rows ($result) > 0) ? self::MODULE_REGISTERED_IN_THIS_INSTANCE : self::MODULE_NOT_REGISTERED;
		}

		public static function whereIsModuleNameRegistered (PearDatabase $adb, $moduleName) {
			$masterModuleNames = self::getMasterPlatformModuleNames ();
			if (in_array ($moduleName, $masterModuleNames)) {
				return self::MODULE_REGISTERED_IN_MASTER;
			}

			$result = $adb->pquery ('SELECT tabid FROM vtiger_tab WHERE name=?', array ($moduleName));
			return ($adb->num_rows ($result) > 0) ? self::MODULE_REGISTERED_IN_THIS_INSTANCE : self::MODULE_NOT_REGISTERED;
		}

		public static function renderRelatedParentModulesHtmlSelect (PearDatabase $adb, $moduleName) {
			$parentModules = self::getAllParentModules ($adb);
			if (!$parentModules) {
				return '';
			}

			$relatedParentModuleIds = self::getRelatedParentModuleIds ($adb, getTabid ($moduleName));
			$options                = array ();
			foreach ($parentModules as $parentModule) {
				$options [] = array (
					'selected' => ($relatedParentModuleIds) && (in_array ($parentModule ['parenttabid'], $relatedParentModuleIds)) ? true : false,
					'text'     => getTranslatedString ($parentModule ['parenttab_label']),
					'value'    => $parentModule ['parenttabid'],
				);
			}
			require_once ('include/utils/HtmlGenerator.class.php');
			return HtmlGenerator::renderSelectMultiple (null, 'parenttabrel', $options, null, 'small', '');
		}

		public static function rebuildParentTab (PearDatabase $adb, $data) {
			existeCampoTabla ('color', 'vtiger_parenttab', 'ALTER TABLE vtiger_parenttab ADD COLUMN color VARCHAR(50) NULL AFTER padre;');
			$adb->query ('DELETE FROM vtiger_parenttabrel');
			foreach ($data as $index => $values) {
				$children = $values ['children'];
				$color    = $values ['color'];
				self::rebuildParentTabChildren ($adb, $children, $index, $color);
			}
			create_parenttab_data_file ();
		}

	}
