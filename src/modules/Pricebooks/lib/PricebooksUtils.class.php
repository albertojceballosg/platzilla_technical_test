<?php
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/SystemVariables.class.php');

	abstract class PricebooksUtils {
		const VARIABLE_TYPE_SERVICE_FIELD   = 'SERVICE_FIELD';
		const VARIABLE_TYPE_SYSTEM_VARIABLE = 'SYSTEM_VARIABLE';

		/**
		 * Compara el valor de la variable con el valor asignado a la condición, según el operador seleccionado
		 *
		 * @param string $variableValue
		 * @param string $operator
		 * @param string $value
		 *
		 * @return boolean
		 *
		 * @codingStandardsIgnoreStart
		 */
		private static function compare ($variableValue, $operator, $value) {
			switch ($operator) {
				case '=':
					$result = ($value === null) ? (($variableValue === null) || ($variableValue === '')) : ($variableValue == $value);
					break;
				case '>':
					$result = ($variableValue > $value);
					break;
				case '>=':
				case '=>':
					$result = ($variableValue >= $value);
					break;
				case '<':
					$result = ($variableValue < $value);
					break;
				case '<=':
				case '=<':
					$result = ($variableValue <= $value);
					break;
				case '!=':
					$result = ($value === null) ? (($variableValue !== null) && ($variableValue !== '')) : ($variableValue != $value);
					break;
				case 'like':
					$result = (preg_match ("/{$value}/", $variableValue) == 1);
					break;
				default:
					$result = false;
					break;
			}
			return !!$result;
		}
		// @codingStandardsIgnoreEnd

		/**
		 * Evalúa la condición
		 *
		 * @param $condition
		 * @param articulos $article
		 * @param array $systemVariables
		 *
		 * @return boolean
		 */
		private static function evaluateCondition ($condition, articulos $article, array $systemVariables) {
			if (empty ($condition)) {
				return true;
			}

			$variableType = $condition ['variabletype'];
			$variableName = $condition ['variablename'];
			$operator     = $condition ['operator'];
			$value        = $condition ['value'];

			if (($variableType == self::VARIABLE_TYPE_SERVICE_FIELD) && (isset ($article->column_fields [ $variableName ]))) {
				$variableValue = $article->column_fields [ $variableName ];
			} else if (($variableType == self::VARIABLE_TYPE_SYSTEM_VARIABLE) && (isset ($systemVariables [ $variableName ]))) {
				$variableValue = $systemVariables [ $variableName ];
			} else {
				return false;
			}

			return self::compare ($variableValue, $operator, $value);
		}

		private static function evaluateConditionGroup ($conditionGroup, articulos $article, array $systemVariables) {
			if ((empty ($conditionGroup)) || (empty ($conditionGroup ['conditions']))) {
				return true;
			}

			$result = false;
			$glue   = 'or';
			foreach ($conditionGroup ['conditions'] as $condition) {
				if ($glue == 'and') {
					$result = !!($result && self::evaluateCondition ($condition, $article, $systemVariables));
				} else if ($glue == 'or') {
					$result = !!($result || self::evaluateCondition ($condition, $article, $systemVariables));
				}
				$glue = isset ($condition ['glue']) ? $condition ['glue'] : 'or';
			}

			return $result;
		}

		private static function evaluateConditionGroups ($conditionGroups, articulos $article, $systemVariables) {
			$result = false;
			$glue   = 'or';
			foreach ($conditionGroups as $conditionGroup) {
				if ($glue == 'and') {
					$result = !!($result && self::evaluateConditionGroup ($conditionGroup, $article, $systemVariables));
				} else if ($glue == 'or') {
					$result = !!($result || self::evaluateConditionGroup ($conditionGroup, $article, $systemVariables));
				}
				$glue = isset ($condition ['glue']) ? $condition ['glue'] : 'or';
			}
			return $result;
		}

		private static function validateCondition ($condition, $conditionIteration, $totalConditions) {
			if (empty ($condition ['variablename'])) {
				throw new Exception ('No se ha suministrado el tipo de variable');
			}
			if (empty ($condition ['variabletype'])) {
				throw new Exception ('No se ha suministrado el tipo de variable');
			}
			if (empty ($condition ['operator'])) {
				throw new Exception ('No se ha suministrado el operador de comparación');
			}
			if ((!in_array ($condition ['operator'], array ('=', '!='))) && (empty ($condition ['value']))) {
				throw new Exception ('No se ha suministrado el valor a comparar');
			}
			if (($conditionIteration < $totalConditions) && (empty ($condition ['glue']))) {
				throw new Exception ('No se ha suministrado el operador de unión entre condiciones');
			}
		}

		private static function validateConditionGroups ($conditionGroups) {
			if (empty ($conditionGroups)) {
				return;
			}

			$conditionGroupIteration = 1;
			foreach ($conditionGroups as $conditionGroup) {
				$conditionIteration = 1;
				foreach ($conditionGroup ['conditions'] as $condition) {
					self::validateCondition ($condition, $conditionIteration, count ($conditionGroup ['conditions']));
					$conditionIteration++;
				}
				if (($conditionGroupIteration < count ($conditionGroups)) && (empty ($conditionGroup ['glue']))) {
					throw new Exception ('No se ha suministrado el operador de unión entre grupos de condiciones');
				}
				$conditionGroupIteration++;
			}
		}

		private static function getConditions (PearDatabase $adb, $pricebookId, $groupId) {
			$result = $adb->pquery (
				'SELECT
					pbc.*,
					IFNULL(f.fieldlabel, pbc.variablename) AS variablelabel
				FROM
					vtiger_pricebooks_conditions pbc
					LEFT JOIN vtiger_field f ON f.fieldname=pbc.variablename AND f.tabid IN (SELECT tabid FROM vtiger_tab t WHERE t.name=?)
				WHERE
					pbc.pricebookid=? AND
					pbc.groupid=?
				ORDER BY
					pbc.conditionid',
				array ('articulos', $pricebookId, $groupId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$conditions = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$conditions [ $row ['conditionid'] ] = array (
					'glue'          => $row ['glue'],
					'operator'      => $row ['operator'],
					'variablename'  => $row ['variablename'],
					'variablelabel' => $row ['variabletype'] == self::VARIABLE_TYPE_SERVICE_FIELD ? getTranslatedString ($row ['variablelabel'], 'articulos') : SystemVariables::getLabel ($row ['variablelabel']),
					'variabletype'  => $row ['variabletype'],
					'value'         => $row ['value'],
				);
			}
			return $conditions;
		}

		/**
		 * Calcula el precio del artículo suministrado como parámetro, agregando impuestos (si se indica)
		 *
		 * @param PearDatabase $adb
		 * @param integer $articleId
		 * @param bool $includeTaxes
		 *
		 * @return float|null
		 */
		public static function calculateArticlePrice (PearDatabase $adb, $articleId, $includeTaxes = false) {
			$result = $adb->pquery (
				'SELECT
					s.*
				FROM
					vtiger_articulos s
				WHERE
					s.articulosid=?',
				array ($articleId)
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$pricebook = self::getApplicablePricebook ($adb, $articleId);
				$article = $adb->fetchByAssoc ($result, -1, false);
				$price   = (doubleval ($article ['precio_sin_impue']) * doubleval ($pricebook ['multiplier']));
				if ($includeTaxes) {
					$tax = ($price * doubleval ($article ['valor_impuesto']) / 100);
				} else {
					$tax = 0.0;
				}
			} else {
				$price = 0.0;
				$tax   = 0.0;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				unset ($result);
			}
			return $price + $tax;
		}

		public static function getApplicablePricebook (PearDatabase $adb, $articleId) {
			/** @var articulos $article */
			$article = PlatformUtils::getCrmEntity ($adb, 'articulos', $articleId);
			if (empty ($article)) {
				return null;
			}

			$pricebook = null;
			$result = $adb->query ('SELECT * FROM vtiger_pricebooks ORDER BY pricebookid');
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$systemVariables     = SystemVariables::getAvailableVariableValues ();
				$defaultPricebook    = null;
				$applicablePricebook = null;
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$conditionGroups = self::getConditionGroups ($adb, $row ['pricebookid']);
					if (empty ($conditionGroups)) {
						$defaultPricebook = $row;
					} else if (self::evaluateConditionGroups ($conditionGroups, $article, $systemVariables)) {
						$applicablePricebook = $row;
						break;
					}
				}

				if ($applicablePricebook !== null) {
					$pricebook = $applicablePricebook;
				} else if ($defaultPricebook !== null) {
					$pricebook = $defaultPricebook;
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				unset ($result);
			}
			return $pricebook;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function getDefaultPricebook (PearDatabase $adb) {
			$defaultPricebook = array (
				'pricebookid'   => null,
				'cod_pricebook' => null,
				'name'          => 'Default pricebook',
				'description'   => null,
				'multiplier'    => 1,
			);
			$pricebook        = null;
			$result           = $adb->query ('SELECT * FROM vtiger_pricebooks ORDER BY pricebookid');
			if (($result) && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$conditionGroups = self::getConditionGroups ($adb, $row ['pricebookid']);
					if (empty ($conditionGroups)) {
						$pricebook = $row;
						break;
					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				unset ($result);
			}
			return !empty ($pricebook) ? $pricebook : $defaultPricebook;
		}

		public static function getConditionGroups (PearDatabase $adb, $pricebookId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_pricebooks_conditiongroups WHERE pricebookid=? ORDER BY groupid', array ($pricebookId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$conditionGroups = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$conditionGroups [ $row ['groupid'] ] = array (
					'conditions' => self::getConditions ($adb, $pricebookId, $row ['groupid']),
					'glue'       => $row ['glue'],
				);
			}
			return $conditionGroups;
		}

		public static function getServiceModuleFields (PearDatabase $adb) {
			$result = $adb->pquery ('SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?', array ('articulos'));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$fields = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fields [ $row ['fieldname'] ] = $row ['fieldlabel'];
			}
			return $fields;
		}

		public static function saveConditionGroups (PearDatabase $adb, $pricebookId, $conditionGroups) {
			self::validateConditionGroups ($conditionGroups);
			$adb->pquery ('DELETE FROM vtiger_pricebooks_conditions WHERE pricebookid=?', array ($pricebookId));
			$adb->pquery ('DELETE FROM vtiger_pricebooks_conditiongroups WHERE pricebookid=?', array ($pricebookId));
			if (empty ($conditionGroups)) {
				return;
			}
			$groupId = 0;
			foreach ($conditionGroups as $conditionGroup) {
				$glue = !empty ($conditionGroup ['glue']) ? $conditionGroup ['glue'] : null;
				$adb->pquery ('INSERT INTO vtiger_pricebooks_conditiongroups (pricebookid, groupid, glue) VALUES (?, ?, ?)', array ($pricebookId, $groupId, $glue));
				$conditionId = 0;
				foreach ($conditionGroup ['conditions'] as $condition) {
					$glue  = !empty ($condition ['glue']) ? $condition ['glue'] : null;
					$value = trim ($condition ['value']) !== '' ? $condition ['value'] : null;
					$adb->pquery (
						'INSERT INTO vtiger_pricebooks_conditions (pricebookid, groupid, conditionid, variabletype, variablename, operator, value, glue) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
						array ($pricebookId, $groupId, $conditionId, $condition ['variabletype'], $condition ['variablename'], $condition ['operator'], $value, $glue)
					);
					$conditionId++;
				}
				$groupId++;
			}
		}

	}
