<?php
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/utils/CommonUtils.php');

	/**
	 * Class FieldValidationsHelper
	 *
	 * Esta clase contiene los métodos que definen y establecen las validaciones para los campos de los módulos
	 */
	abstract class FieldValidationsHelper {

		/**
		 * Obtiene el ID de la entidad del campo a ser validado
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return null
		 */
		public static function getEntityIdField (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT entityidfield FROM vtiger_entityname WHERE modulename=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['entityidfield'];
		}

		/**
		 * Valida el valor único no repetible
		 *
		 * @param \PearDatabase $adb
		 * @param $recordId
		 * @param $moduleName
		 * @param $tableName
		 * @param $fieldLabel
		 * @param $fieldName
		 * @param $fieldValue
		 *
		 * @throws \Exception
		 */
		private static function validateUniqueValue (PearDatabase $adb, $recordId, $moduleName, $tableName, $fieldLabel, $fieldName, $fieldValue) {
			if (empty ($fieldValue)) {
				return;
			}
			$entityFieldId = self::getEntityIdField ($adb, $moduleName);
			$result        = $adb->pquery (
				"SELECT {$fieldName} FROM {$tableName} tn 
				  INNER JOIN vtiger_crmentity crm ON crm.crmid = tn.{$entityFieldId} AND crm.deleted = 0
				  WHERE tn.{$fieldName}=? AND tn.{$entityFieldId}<>?",
				array ($fieldValue, $recordId)
			);
			if ((!$result) || ($adb->num_rows ($result) > 0)) {
				throw new Exception ("El valor del campo {$fieldLabel} ya se encuentra registrado");
			}
		}

		/**
		 * Valida que la fecha de inicio/minima no sea mayor que la decha de culminación/maxima
		 *
		 * @param $fieldLabel
		 * @param $fieldValue
		 * @param $minValue
		 * @param $maxValue
		 *
		 * @throws \Exception
		 */
		private static function validateDateValue ($fieldLabel, $fieldValue, $minValue, $maxValue) {
			if (empty ($fieldValue)) {
				return;
			}

			if (($minValue != null) && ($fieldValue < $minValue)) {
				throw new Exception ("La fecha mínima de {$fieldLabel} es: {$minValue}");
			}

			if (($maxValue != null) && ($fieldValue > $maxValue)) {
				throw new Exception ("La fecha máxima de {$fieldLabel} es: {$maxValue}");
			}
		}

		/**
		 * Valida que el valor introducido en el campo sea numerico y positivo
		 *
		 * @param $subType
		 * @param $fieldLabel
		 * @param $fieldValue
		 * @param $minValue
		 * @param $maxValue
		 *
		 * @throws \Exception
		 */
		private static function validateNumericValue ($subType, $fieldLabel, $fieldValue, $minValue, $maxValue) {
			if (empty ($fieldValue)) {
				return;
			}

			if ((strcmp ($subType, 'NN') != 0) && ($fieldValue < 0)) {
				throw new Exception ("El valor de {$fieldLabel} debe ser positivo");
			}

			if (($minValue != null) && ($fieldValue < $minValue)) {
				throw new Exception ("El valor mínimo de {$fieldLabel} es: {$minValue}");
			}

			if (($maxValue != null) && ($fieldValue > $maxValue)) {
				throw new Exception ("El valor máximo de {$fieldLabel} es: {$maxValue}");
			}
		}

		/**
		 * Obtiene el registro con los campos a ser validados
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 * @param $fieldName
		 *
		 * @return array|string
		 * @throws \Exception
		 */
		public static function getFieldValidationRecords (PearDatabase $adb, $moduleName, $fieldName) {
			$tabId = getTabid ($moduleName);

			$result = $adb->pquery ('SELECT fieldid FROM vtiger_field WHERE tabid=? AND fieldname=?', array ($tabId, $fieldName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				throw new Exception ("El campo {$fieldName} no se encuentra registrado en el módulo {$moduleName}");
			}
			$row     = $adb->fetchByAssoc ($result);
			$fieldId = $row ['fieldid'];

			$result = $adb->pquery ('SELECT validationtype, initialvalue, maximumvalue FROM vtiger_field_validation WHERE tabid=? AND fieldid=?', array ($tabId, $fieldId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return '';
			}

			$records = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$records [] = $row;
			}
			return $records;
		}

		/**
		 * Obtiene el registro del modulo a ser validado
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array
		 * @throws \Exception
		 */
		public static function getModuleValidationRecords (PearDatabase $adb, $moduleName) {
			$tabId  = getTabid ($moduleName);
			$result = $adb->pquery ('SELECT tablename, fieldname, validationtype, initialvalue, maximumvalue FROM vtiger_field_validation WHERE tabid=?', array ($tabId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				throw new Exception ('fields_unconfigured');
			}

			$records = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$records [] = $row;
			}
			return $records;
		}

		/**
		 * Actualiza el registro con las validaciones
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 * @param $fieldName
		 * @param $validationType
		 * @param null $minValue
		 * @param null $maxValue
		 *
		 * @return string
		 */
		public static function updateValidationRecords (PearDatabase $adb, $moduleName, $fieldName, $validationType, $minValue = null, $maxValue = null) {
			$tabId     = getTabid ($moduleName);
			$tableName = "vtiger_{$moduleName}";

			$result = $adb->pquery ('SELECT fieldid FROM vtiger_field WHERE tabid=? AND fieldname=?', array ($tabId, $fieldName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return '';
			}
			$row     = $adb->fetchByAssoc ($result);
			$fieldId = $row ['fieldid'];

			$result = $adb->pquery ('SELECT fieldid FROM vtiger_field_validation WHERE tabid=? AND fieldid=?', array ($tabId, $fieldId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				$adb->pquery (
					'INSERT INTO vtiger_field_validation (tabid, fieldid, tablename, fieldname, validationtype, initialvalue, maximumvalue) VALUES (?, ?, ?, ?, ?, ?, ?)',
					array ($tabId, $fieldId, $tableName, $fieldName, $validationType, $minValue, $maxValue)
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_field_validation SET validationtype=?, initialvalue=?, maximumvalue=? WHERE fieldid=?',
					array ($validationType, $minValue, $maxValue, $fieldId)
				);
			}
			return 'validation_change';
		}

		/**
		 * Valida que se haya dado nombre a los argumentos del campo
		 *
		 * @param \PearDatabase $adb
		 * @param array $arguments
		 *
		 * @throws \Exception
		 */
		public static function validateFields (PearDatabase $adb, array $arguments) {
			$fieldName      = $arguments ['fieldname'];
			$fieldValue     = $arguments ['fieldValue'];
			$maxValue       = $arguments ['maxvalue'];
			$minValue       = $arguments ['minvalue'];
			$moduleName     = $arguments ['modulename'];
			$recordId       = $arguments ['recordid'];
			$tableName      = $arguments ['tablename'];
			$validationType = $arguments ['validationtype'];

			if (!$fieldName) {
				throw new Exception ('No se ha suministrado el nombre del campo');
			}
			if (!$tableName) {
				throw new Exception ('No se ha suministrado el nombre de la tabla');
			}

			$result = $adb->pquery ('SELECT fieldlabel FROM vtiger_field WHERE tablename=? AND fieldname=?', array ($tableName, $fieldName));
			if ((!$result) && ($adb->num_rows ($result) == 0)) {
				throw new Exception ("El campo {$fieldName} no se encuentra registrado en la tabla {$tableName}");
			}
			$row        = $adb->fetchByAssoc ($result);
			$fieldLabel = html_entity_decode ($row ['fieldlabel']);

			if (strlen ($validationType) > 1) {
				$dummy   = explode ('~', $validationType);
				$type    = $dummy [0];
				$subType = $dummy [1];
			} else {
				$type    = $validationType;
				$subType = null;
			}

			if ($type == 'U') {
				self::validateUniqueValue ($adb, $recordId, $moduleName, $tableName, $fieldLabel, $fieldName, $fieldValue);
			} else if ($type == 'D') {
				self::validateDateValue ($fieldLabel, $fieldValue, $minValue, $maxValue);
			} else if ($type == 'N') {
				self::validateNumericValue ($subType, $fieldLabel, $fieldValue, $minValue, $maxValue);
			}
		}

	}
