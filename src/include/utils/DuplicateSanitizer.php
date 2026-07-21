<?php

/**
 * Sanitiza los column_fields de una entidad cuando se está duplicando,
 * evitando errores de base de datos por cadenas 'NULL', valores nulos en
 * campos NOT NULL, desbordamiento de longitud o formatos numéricos incorrectos.
 */
class DuplicateSanitizer {

	private static $_schemaCache = array();
	private static $_fieldCache = array();

	/**
	 * Limpia los column_fields de $focus para una duplicación segura.
	 *
	 * @param CRMEntity $focus
	 * @param string $module
	 * @param PearDatabase|null $adb
	 */
	public static function sanitizeColumnFields (&$focus, $module, $adb = null) {
		if (empty($adb)) {
			global $adb;
		}
		if (!is_object($focus) || empty($focus->column_fields) || empty($adb)) {
			return;
		}

		require_once ('include/utils/NumberHelper.class.php');
		$numberHelper = NumberHelper::getInstance($adb);

		$fieldMeta = self::getFieldMetadata($adb, $module);
		if (empty($fieldMeta)) {
			return;
		}

		$tables = array();
		foreach ($fieldMeta as $meta) {
			$tables[$meta['tablename']] = true;
		}
		$schemaMeta = self::getSchemaMetadata($adb, array_keys($tables));

		foreach ($focus->column_fields as $fieldname => $value) {
			if (!isset($fieldMeta[$fieldname])) {
				continue;
			}
			$meta = $fieldMeta[$fieldname];
			$uitype = intval($meta['uitype']);
			$typeOfData = $meta['typeofdata'];
			$typeParts = explode('~', $typeOfData);
			$dataType = $typeParts[0];
			$isMandatory = isset($typeParts[1]) && $typeParts[1] === 'M';
			$columnname = $meta['columnname'];
			$tablename = $meta['tablename'];
			$schema = isset($schemaMeta[$tablename][$columnname]) ? $schemaMeta[$tablename][$columnname] : null;

			// Detectar valores que representan nulo
			$isNullLike = ($value === null || $value === '' || (is_string($value) && strtoupper(trim($value)) === 'NULL'));

			if ($isNullLike) {
				$focus->column_fields[$fieldname] = self::resolveNullValue($meta, $schema, $dataType, $isMandatory, $adb);
				continue;
			}

			// Convertir numéricos al formato interno de BD
			if (in_array($dataType, array('I', 'N', 'NN')) || in_array($uitype, array(7, 9, 71))) {
				$focus->column_fields[$fieldname] = self::convertNumberToInternal($value, $numberHelper, $uitype);
			}

			// Truncar cadenas si exceden la longitud máxima de la columna
			if ($schema && !empty($schema['character_maximum_length']) && is_string($focus->column_fields[$fieldname])) {
				$maxLength = intval($schema['character_maximum_length']);
				if ($maxLength > 0 && strlen($focus->column_fields[$fieldname]) > $maxLength) {
					$focus->column_fields[$fieldname] = substr($focus->column_fields[$fieldname], 0, $maxLength);
				}
			}
		}
	}

	private static function getFieldMetadata ($adb, $module) {
		if (isset(self::$_fieldCache[$module])) {
			return self::$_fieldCache[$module];
		}
		$tabid = getTabid($module);
		$result = $adb->pquery(
			'SELECT fieldname, columnname, tablename, uitype, typeofdata, defaultvalue 
			 FROM vtiger_field 
			 WHERE tabid=? AND presence IN (0,2)',
			array($tabid)
		);
		$meta = array();
		while ($row = $adb->fetchByAssoc($result, -1, false)) {
			$meta[$row['fieldname']] = $row;
		}
		self::$_fieldCache[$module] = $meta;
		return $meta;
	}

	private static function getSchemaMetadata ($adb, $tables) {
		$cacheKey = implode('|', $tables);
		if (isset(self::$_schemaCache[$cacheKey])) {
			return self::$_schemaCache[$cacheKey];
		}
		$meta = array();
		foreach ($tables as $table) {
			$result = $adb->pquery(
				'SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_DEFAULT 
				 FROM INFORMATION_SCHEMA.COLUMNS 
				 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
				array($table)
			);
			while ($row = $adb->fetchByAssoc($result, -1, false)) {
				$meta[$table][$row['column_name']] = $row;
			}
		}
		self::$_schemaCache[$cacheKey] = $meta;
		return $meta;
	}

	private static function resolveNullValue ($meta, $schema, $dataType, $isMandatory, $adb) {
		// Priorizar defaultvalue de vtiger_field, salvo que sea la cadena literal 'NULL'
		$defaultValue = isset($meta['defaultvalue']) ? trim($meta['defaultvalue']) : '';
		if ($defaultValue !== '' && strtoupper($defaultValue) !== 'NULL') {
			return $defaultValue;
		}
		// Luego default de la columna en BD, salvo que sea la cadena literal 'NULL'
		if ($schema && $schema['column_default'] !== null && strtoupper(trim($schema['column_default'])) !== 'NULL') {
			return $schema['column_default'];
		}
		// Si es nullable, devolver nulo real
		if (!$isMandatory && ($schema === null || $schema['is_nullable'] === 'YES')) {
			return null;
		}
		// Campos numéricos no nulos: 0
		if (in_array($dataType, array('I', 'N', 'NN')) || in_array(intval($meta['uitype']), array(7, 9, 71))) {
			return 0;
		}
		// Fechas: usar fecha actual como valor seguro
		if (in_array(intval($meta['uitype']), array(5, 6, 23)) || in_array($dataType, array('D', 'DT', 'T'))) {
			return date('Y-m-d');
		}
		// Checkbox
		if (intval($meta['uitype']) === 56) {
			return '0';
		}
		// Cualquier otro caso: cadena vacía
		return '';
	}

	private static function convertNumberToInternal ($value, $numberHelper, $uitype) {
		if (is_numeric($value)) {
			return $value;
		}
		if (is_string($value)) {
			// Currency / percentage / number
			if (in_array($uitype, array(7, 9, 71))) {
				return $numberHelper->setSaveNumberFormat($value);
			}
			// Formato europeo con coma decimal
			if (strpos($value, ',') !== false) {
				return $numberHelper->setSaveNumberFormat($value);
			}
			// Intentar float genérico
			return floatval($value);
		}
		return 0;
	}
}
