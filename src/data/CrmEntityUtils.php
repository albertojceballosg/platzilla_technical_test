<?php
	require_once ('include/platzilla/Data/EntityHistoryManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');

	abstract class CrmEntityUtils {
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @param string $moduleName
		 * @return array|null
		 */
		private static function fetchNotSelectableFields ($adb, $moduleName) {
			if (empty ($moduleName)) {
				return null;
			}
			$results = $adb->pquery (
				'SELECT
       				fieldname
				FROM
					vtiger_field vf
				INNER JOIN vtiger_tab vt ON vt.tabid = vf.tabid
				WHERE
					vt.name = ? AND
					vf.uitype IN (5010, 2208)',
				array ($moduleName)
			);
			if ($adb->num_rows ($results) > 0) {
				$notSelectableFields = array ();
				while ($row = $adb->fetchByAssoc ($results)) {
					$notSelectableFields [] = $row['fieldname'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($notSelectableFields)) ? $notSelectableFields : null;
		}
		
		private static function getArrayDiff ($key, $main, $other) {
			$theKeys = array_keys ($main [$key]);
			$isDiff  = false;
			foreach ($theKeys as $thisKey) {
				if (!isset ($other[ $key ][ $thisKey ])) {
					continue;
				}
				if (is_array ($main[ $key ][ $thisKey ])) {
					$dummyDirect = array_diff_assoc ($main[ $key ][ $thisKey ], $other[ $key ][ $thisKey ]);
					$dummyRevert = array_diff_assoc ($other[ $key ][ $thisKey ], $main[ $key ][ $thisKey ]);
					if (!empty ($dummyDirect) || !empty ($dummyRevert)) {
						$isDiff = true;
						break;
					}
				} else if (trim ($main[ $key ][ $thisKey ]) != trim ($other[ $key ][ $thisKey ])) {
					$isDiff = true;
					break;
				}
			}
			return $isDiff;
		}
		
		/**
		 * @param string[] $data
		 * @param string $fieldName
		 * @param boolean $decodeHtml
		 *
		 * @return string
		 */
		private static function getFieldValue ($data, $fieldName, $decodeHtml = false) {
			$value = $data [ $fieldName ];
			if (!empty ($value)) {
				if (is_array ($value)  && (is_array (array_values ($value)))) {
					return json_encode ($value);
				} else if (is_array ($value)) {
					return join (', ', $value);
				} else if ($decodeHtml && !is_numeric ($value)) {
					return html_entity_decode ($value, ENT_QUOTES, 'UTF-8');
				} else {
					return $value;
				}
			}
			return '';
		}

		/**
		 * @param array $main
		 * @param array $other
		 *
		 * @return array|null
		 */
		private static function getFieldsNames ($adb, $moduleName, $main, $other) {
			if (empty($main) || empty($other)) {
				return array ();
			}
			
			$notSelectable            = array ('modifiedby', 'createdtime', 'modifiedtime', 'record_id', 'record_module');
			$otherNoTSelectableFields = self::fetchNotSelectableFields ($adb, $moduleName);
			if (!empty ($otherNoTSelectableFields)) {
				$notSelectable = array_merge ($notSelectable, $otherNoTSelectableFields);
			}
			
			foreach ($main as $key => $value) {
				if (is_array ($value)  && !in_array ($key, $notSelectable)) {
					$isDiff = self::getArrayDiff ($key, $main, $other);
					if ($isDiff) {
						$dummy [] = $key;
					}
				} else if (!in_array ($key, $notSelectable)) {
					// Normalizar valores para comparación
					$normalizedMain = self::normalizeValueForComparison($value, $key, $moduleName, $adb);
					$normalizedOther = self::normalizeValueForComparison($other[$key], $key, $moduleName, $adb);
					
					if ($normalizedMain != $normalizedOther) {
						$dummy [] = $key;
					}
				}
			}
			return $dummy;
		}

		/**
		 * Normaliza un valor para comparación, evitando falsos positivos por diferencias de formato
		 * @param mixed $value
		 * @param string $fieldName
		 * @param string $moduleName
		 * @param PearDatabase $adb
		 * @return mixed
		 */
		private static function normalizeValueForComparison($value, $fieldName, $moduleName, $adb) {
			if ($value === null || $value === '') {
				return '';
			}
			
			// Obtener información del campo para determinar su tipo
			$field = FieldManager::getInstance($adb)->fetchFieldByName($moduleName, $fieldName, true);
			if (empty($field)) {
				return trim((string)$value);
			}
			
			$uitype = $field->getUiType();
			
			// Normalizar fechas (uitype 5, 6, 23, 70)
			if (in_array($uitype, array(5, 6, 23, 70))) {
				// Intentar parsear formatos comunes explícitamente
				$formats = array(
					'Y-m-d',      // 2024-12-30
					'd/m/Y',      // 30/12/2024
					'd-m-Y',      // 30-12-2024
					'm/d/Y',      // 12/30/2024
					'Y/m/d',      // 2024/12/30
				);
				
				foreach ($formats as $format) {
					$date = DateTime::createFromFormat($format, $value);
					if ($date !== false && $date->format($format) === $value) {
						return $date->format('Y-m-d');
					}
				}
				
				// Fallback a strtotime si ningún formato explícito funciona
				$timestamp = strtotime($value);
				if ($timestamp !== false) {
					return date('Y-m-d', $timestamp);
				}
			}
			
			// Normalizar números decimales (uitype 7, 9, 71, 72)
			if (in_array($uitype, array(7, 9, 71, 72))) {
				// Reemplazar coma por punto y convertir a float
				$normalized = str_replace(',', '.', $value);
				$floatVal = floatval($normalized);
				// Redondear a 8 decimales para comparación precisa
				$rounded = round($floatVal, 8);
				// Formatear eliminando ceros a la derecha pero manteniendo al menos 2 decimales
				$formatted = rtrim(rtrim(number_format($rounded, 8, '.', ''), '0'), '.');
				// Asegurar al menos 2 decimales para valores con decimales
				if (strpos($formatted, '.') !== false) {
					$parts = explode('.', $formatted);
					if (strlen($parts[1]) < 2) {
						$formatted = number_format($rounded, 2, '.', '');
					}
				}
				return $formatted;
			}
			
			// Para otros campos, usar trim
			return trim((string)$value);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $recordId
		 * @param string $moduleName
		 * @param array $oldData
		 * @param array $newData
		 * @param integer $currentUserId
		 *
		 * @throws Exception EntityHistoryException
		 */
		public static function audit (PearDatabase $adb, $recordId, $moduleName, $oldData, $newData, $currentUserId) {
			$module = ModuleManager::getInstance ($adb)->fetchModule ($moduleName, true);
			if (empty ($module)) {
				return;
			}
			$fieldNames = self::getFieldsNames ($adb, $moduleName, $oldData, $newData);
			
			if (empty ($fieldNames)) {
				return;
			}

			$histories = array ();
			foreach ($fieldNames as $fieldName) {
				$oldValue = self::getFieldValue ($oldData, $fieldName, true);
				$newValue = self::getFieldValue ($newData, $fieldName);
				
				// Normalizar valores para comparación
				$normalizedOld = self::normalizeValueForComparison($oldValue, $fieldName, $moduleName, $adb);
				$normalizedNew = self::normalizeValueForComparison($newValue, $fieldName, $moduleName, $adb);
				
				if ($normalizedOld == $normalizedNew) {
					continue;
				}

				$field = FieldManager::getInstance ($adb)->fetchFieldByName ($moduleName, $fieldName, true);
				if (empty ($field)) {
					continue;
				}
				
				// Guardar valores normalizados para mantener consistencia de formato
				$histories [] = EntityHistory::getInstance ()
					->setId (0)
					->setCreatedDate (date ('Y-m-d h:i:s'))
					->setFieldId ($field->getId ())
					->setModuleId ($module->getId ())
					->setUiType ($field->getUiType ())
					->setModifiedBy ($currentUserId)
					->setModifiedOn (1)
					->setNewValue ($normalizedNew)
					->setOldValue ($normalizedOld)
					->setRegistryId ($recordId);
			}
			try {
				EntityHistoryManager::getInstance ($adb)->saveAllEntityHistory ($histories);
			} catch (Exception $e) {
				$_SESSION ['flashmessage'] = array (
					'iserror' => true,
					'message' => $e->getMessage (),
					'data'    => null,
				);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $recordId
		 * @param string $moduleName
		 * @param array $data
		 * @param integer $currentUserId
		 *
		 * @throws Exception EntityHistoryException
		 */
		public static function entry (PearDatabase $adb, $recordId, $moduleName, $data, $currentUserId) {
			$module = ModuleManager::getInstance ($adb)->fetchModule ($moduleName, true);
			if (empty ($module)) {
				return;
			}

			if (empty ($data)) {
				return;
			} else {
				unset ($data ['modifiedby']);
				unset ($data ['createdtime']);
				unset ($data ['modifiedtime']);
				unset ($data ['record_id']);
				unset ($data ['record_module']);
			}
			$histories  = array ();
			$fieldNames = array_keys ($data);
			foreach ($fieldNames as $fieldName) {
				$value = self::getFieldValue ($data, $fieldName, true);
				if (empty($value)) {
					continue;
				}

				$field = FieldManager::getInstance ($adb)->fetchFieldByName ($moduleName, $fieldName, true);
				if (empty ($field)) {
					continue;
				}

				$histories [] = EntityHistory::getInstance ()
					->setId (0)
					->setCreatedDate (date ('Y-m-d h:i:s'))
					->setFieldId ($field->getId ())
					->setModuleId ($module->getId ())
					->setModifiedBy ($currentUserId)
					->setModifiedOn (0)
					->setNewValue ($value)
					->setOldValue ('')
					->setRegistryId ($recordId);
			}
			try {
				EntityHistoryManager::getInstance ($adb)->saveAllEntityHistory ($histories);
			} catch (Exception $e) {
				$_SESSION ['flashmessage'] = array (
					'iserror' => true,
					'message' => $e->getMessage (),
					'data'    => null,
				);
			}
		}

	}
