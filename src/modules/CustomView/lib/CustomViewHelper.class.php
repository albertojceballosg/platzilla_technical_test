<?php
	require_once ('include/fields/DateTimeField.php');
	require_once ('include/platzilla/Data/FieldGridManager.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/utils/CommonUtils.php');

	abstract class CustomViewHelper {

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $fieldName
		 * @param string $fieldLabel
		 * @param array $columnsData
		 *
		 * @throws Exception
		 */
		public static function getGridFields (PearDatabase $adb, $moduleName, $fieldName, $fieldLabel, &$columnsData) {
			$fieldsGrid = FieldGridManager::getInstance ($adb)->fetchFieldGrid ($moduleName, $fieldName);
			if (!$fieldsGrid) {
				return;
			};

			foreach ($fieldsGrid as $field) {
				if ($field->getUiType () != FieldInterface::UI_TYPE_SUMMARY_ROW) {
					continue;
				}
				$summaryConfig = unserialize (base64_decode ($field->getDataField ()));
				$summaryFields = array_column ($summaryConfig, 'field');

				foreach ($summaryFields as $column) {
					if($column != 'false') {
						$dummy = explode ('_', $column);
						array_pop ($dummy);
						$columnName  = ucfirst (join ('_', $dummy));
						$key         = 'vtiger_subfields_values:' . $column . ':' . $fieldName . ':' . $moduleName . '@'. $columnName . ':N';
						$label       = 'Tabla: '. $fieldLabel . '. Columna: ' . $field->getLabel () . ' (' . $columnName . ')';
						$columnsData = array_merge ($columnsData, array ($key => $label));
					}
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param CustomView $cv
		 * @param string $moduleName
		 * @param boolean $isKanbanView
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getAvailableColumnsData ($adb, CustomView $cv, $moduleName, $isKanbanView = false) {
			$columnsList = $cv->getModuleColumnsList ($moduleName);

			$columnsData          = array ();
			$processedFieldLabels = array ();
			foreach ($cv->module_list [ $moduleName ] as $key => $value) {
				if (!isset ($columnsList [ $moduleName ][ $key ])) {
					continue;
				}
				foreach ($columnsList [ $moduleName ][ $key ] as $field => $fieldLabel) {
					$fieldLabel = getTranslatedString ($fieldLabel, $moduleName);
					$dummy = explode (':', $field);
					if ((in_array ('taskstatus', $dummy)) || (in_array ($fieldLabel, $processedFieldLabels))) {
						continue;
					} else if (($dummy [4] == 'X') && !$isKanbanView) {
						self::getGridFields ($adb, $moduleName, $dummy [1], $fieldLabel, $columnsData);
					} else if($dummy [4] != 'X') {
						$columnsData [ $field ]  = $fieldLabel;
					} else {
						continue;
					}

					$processedFieldLabels [] = $fieldLabel;
				}
			}
			uasort ($columnsData, function ($fieldLabelA, $fieldLabelB) {
				return strcmp ($fieldLabelA, $fieldLabelB);
			});
			return $columnsData;
		}

		public static function getAvailableDateColumnsData (CustomView $cv, $moduleName, Users $user) {
			$is_admin   = null;
			$local_user = clone $user;
			require ('user_privileges/user_privileges.php');
			$dateColumnsData = array ();
			$result          = $cv->getStdCriteriaByModule ($moduleName);
			if (!isset ($result)) {
				return null;
			}

			foreach ($result as $key => $value) {
				if (!$is_admin) {
					$keys = explode (":", $key);
					if (getFieldVisibilityPermission ($moduleName, $user->id, $keys[2]) != '0') {
						continue;
					}
				}
				if ($value == 'Start Date & Time') {
					$value = 'Start Date';
				}
				$dateColumnsData [ $key ] = getTranslatedString ($value);
			}
			return $dateColumnsData;
		}

		public static function getAvailablePeriods () {
			return array (
				'custom'      => getTranslatedString ('Custom'),
				'prevfy'      => getTranslatedString ('Previous FY'),
				'thisfy'      => getTranslatedString ('Current FY'),
				'nextfy'      => getTranslatedString ('Next FY'),
				'prevfq'      => getTranslatedString ('Previous FQ'),
				'thisfq'      => getTranslatedString ('Current FQ'),
				'nextfq'      => getTranslatedString ('Next FQ'),
				'yesterday'   => getTranslatedString ('Yesterday'),
				'today'       => getTranslatedString ('Today'),
				'tomorrow'    => getTranslatedString ('Tomorrow'),
				'lastweek'    => getTranslatedString ('Last Week'),
				'thisweek'    => getTranslatedString ('Current Week'),
				'nextweek'    => getTranslatedString ('Next Week'),
				'lastmonth'   => getTranslatedString ('Last Month'),
				'thismonth'   => getTranslatedString ('Current Month'),
				'nextmonth'   => getTranslatedString ('Next Month'),
				'last7days'   => getTranslatedString ('Last 7 Days'),
				'last30days'  => getTranslatedString ('Last 30 Days'),
				'last60days'  => getTranslatedString ('Last 60 Days'),
				'last90days'  => getTranslatedString ('Last 90 Days'),
				'last120days' => getTranslatedString ('Last 120 Days'),
				'next7days'   => getTranslatedString ('Next 7 Days'),
				'next30days'  => getTranslatedString ('Next 30 Days'),
				'next60days'  => getTranslatedString ('Next 60 Days'),
				'next90days'  => getTranslatedString ('Next 90 Days'),
				'next120days' => getTranslatedString ('Next 120 Days'),
			);
		}

		public static function getChildPlatformRelatedModules (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				"SELECT
					p.plat,
					p.name,
					pm.module
				FROM
					vtiger_relationsship_plat_modules pm
					INNER JOIN vtiger_relationsship_plat p ON p.relationsship_platid=pm.relationsship_platid
				WHERE
					pm.module_base=? OR
					(pm.module=? AND pm.module_base='-')",
				array ($moduleName, $moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = array (
					'plat'   => $row ['plat'],
					'name'   => $row ['name'],
					'module' => $row ['module'],
				);
			}
			return $modules;
		}

		public static function renderChildPlatformModuleFiltersHtmlSelectOptions ($platform, $moduleName, $dictionary, $viewName = null) {
			require_once ('modules/Settings/comunesRelaciones.php');
			$adb    = conectaPlataformaHija ($platform);
			$result = $adb->pquery (
				'SELECT
					cv.*,
					u.first_name,
					u.last_name
				FROM
					vtiger_customview cv
					INNER JOIN vtiger_tab t ON t.name=cv.entitytype
					LEFT JOIN vtiger_users u ON u.id=cv.userid
				WHERE
					t.name=? AND
					cv.status=0
				ORDER BY
					viewname',
				array ($moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return '';
			}

			$options = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$options [] = array (
					'text'  => ($row ['viewname'] == 'All') ? $dictionary ['COMBO_ALL'] : $row ['viewname'],
					'value' => "{$row['cvid']}|{$platform}",
				);
			}
			require_once ('include/utils/HtmlGenerator.class.php');
			return HtmlGenerator::renderSelectOptions ($options, $viewName);
		}

	}
