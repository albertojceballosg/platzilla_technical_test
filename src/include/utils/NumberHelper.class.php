<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	class NumberHelper {
		
		/** @var PearDatabase */
		private $adb;
		
		/** @var integer */
		private $defaultPrecision;
		
		/** @var string */
		private $numberingFormat;
		
		/**
		 * @param string $fieldName
		 *
		 * @return integer
		 * @throws Exception
		 */
		public function _getFieldPrecision ($fieldName) {
			if (empty ($fieldName)) {
				return $this->defaultPrecision;
			}
			
			$result = $this->adb->pquery ('SELECT typeofdata FROM vtiger_field WHERE uitype IN (?,?,?,?) AND fieldname=? LIMIT 1',
				array (7, 9, 41, 71, $fieldName)
			);
			
			if ($this->adb->num_rows ($result) > 0) {
				$typeOfData = $this->adb->fetchByAssoc ($result, -1, false);
				if (isset($typeOfData['typeofdata'])) {
					$dummy = explode (',', $typeOfData ['typeofdata']);
					if (isset($dummy[1])) {
						$precision = intval ($dummy[1]);
					} else {
						$precision = $this->defaultPrecision;
					}
				} else {
					$precision = $this->defaultPrecision;
				}
			} else {
				$precision = $this->defaultPrecision;
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			
			return $precision;
		}
		
		/**
		 * Verifica si un campo es numérico y necesita formato americano para compatibilidad con JavaScript
		 * 
		 * @param string $fieldName
		 * @return boolean
		 */
		private function _isNumericField($fieldName) {
			// Lista de campos numéricos que JavaScript procesará y necesitan formato americano
			$numericFields = array(
				'numero_unidades_planificadas',
				'unidades_consumidas',
				'work_estimated_cost',
				'cost_work_performed',
				'expected_work_progress',
				'overall_progress_perc',
				'estimated_project_progress',
				'porcentaje_de_avance_genera'
			);
			
			return in_array($fieldName, $numericFields);
		}
		
		private function resolveUserId ($user) {
			if (empty ($user)) {
				return null;
			}
			if (!empty ($user->id)) {
				return $user->id;
			}
			if (!empty ($user->column_fields) && !empty ($user->column_fields ['id'])) {
				return $user->column_fields ['id'];
			}
			return null;
		}

		public function _setUserNumberingFormat (&$user) {
			$userId = $this->resolveUserId ($user);
			if (empty ($userId)) {
				$user->numbering_format = 'AMERICAN_FORMAT';
			} else {
				$result = $this->adb->pquery ('SELECT numbering_format FROM vtiger_users WHERE id=? LIMIT 1', array($userId));
				$dummy = null;
				if ($this->adb->num_rows ($result) > 0) {
					$dummy = $this->adb->fetchByAssoc ($result, -1, false);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$user->numbering_format  = (isset ($dummy['numbering_format'])) ? $dummy ['numbering_format'] : 'AMERICAN_FORMAT';
			}
		}
		
		public function __construct ($adb, $user) {
			global $current_user;
			$this->adb              = $adb;
			$this->defaultPrecision = 2;
			$targetUser             = (!empty ($user)) ? $user : $current_user;
			if (!isset ($targetUser->numbering_format) || empty ($targetUser->numbering_format)) {
				$this->_setUserNumberingFormat ($targetUser);
			}
			$this->numberingFormat = !empty ($targetUser->numbering_format)
				? $targetUser->numbering_format
				: 'AMERICAN_FORMAT';
		}
		
		/**
		 * @return string
		 */
		public function getDefaultValue () {
			if ($this->numberingFormat == 'EUROPEAN_FORMAT') {
				return number_format (0.00, $this->defaultPrecision, ',', '.');
			} else {
				return number_format (0.00, $this->defaultPrecision, '.', ',');
			}
		}
		
		/**
		 * @return mixed|string
		 */
		public function getNumberFormat () {
			return $this->numberingFormat;
		}
		
		/**
		 * @param float $value
		 * @param string $field
		 *
		 * @return null|string
		 * @throws Exception
		 */
		public function setNumberFormat ($value, $field = null) {
		
		// MEJOR MANEJO para valores numéricos entre 0 y 1
		// empty() considera "0.51" como vacío si es string, pero no si es numeric
		if ($value === null || $value === '' || $value === false) {
			return '';
		}
		$value     = (is_numeric ($value)) ? floatval ($value) : $value;
		$precision = $this->_getFieldPrecision ($field);
		
		// SOLUCIÓN: Retornar formato del usuario, pero agregar logging para depuración
		$result = ($this->numberingFormat == 'EUROPEAN_FORMAT') 
			? number_format ($value, $precision, ',', '.')
			: number_format ($value, $precision, '.', ',');
		
		return $result;
		}
		
		/**
		 * @param float|string $value
		 *
		 * @return null|float
		 * @throws Exception
		 */
		public function setSaveNumberFormat ($value) {
			// Permitir el valor 0 (empty() retorna true para "0")
			if ($value === null || $value === '') {
				return $value;
			}
			
			if ($this->numberingFormat == 'EUROPEAN_FORMAT') {
				// Formato europeo: 1.234,56 -> 1234.56
				$myNumber = str_replace ('.', '', $value);
				$result = floatval (str_replace (',', '.', $myNumber));
				return $result;
			} else {
				// Formato americano: 1,234.56 -> 1234.56
				$result = floatval (str_replace(',', '', $value));
				return $result;
			}
		}
		
		/**
		 * @param Users $user
		 * @param boolean $defaultFormat
		 *
		 * @return void
		 * @throws Exception
		 */
		public function setUserNumberingFormat (&$user, $defaultFormat = false) {
			$userId = $this->resolveUserId ($user);
			if ($defaultFormat || empty ($userId)) {
				$user->numbering_format = 'AMERICAN_FORMAT';
			} else {
				$result = $this->adb->pquery ('SELECT numbering_format FROM vtiger_users WHERE id=? LIMIT 1', array($userId));
				$dummy = null;
				if ($this->adb->num_rows ($result) > 0) {
					$dummy = $this->adb->fetchByAssoc ($result, -1, false);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$user->numbering_format  = (isset ($dummy['numbering_format'])) ? $dummy ['numbering_format'] : 'AMERICAN_FORMAT';
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param Users $user
		 * @return NumberHelper
		 */
		public static function getInstance ($adb, $user = null) {
			return new self ($adb, $user);
		}
		
	}
