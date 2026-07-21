<?php
	require_once ('include/utils/FieldCalculate.php');
	require_once ('include/platzilla/Data/FieldGridManager.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('include/fields/DateTimeField.php');
	require_once ('include/utils/NumberHelper.class.php');

	abstract class GridFieldUtils {

	/**
	 * Caché estático para almacenar resultados de getAvailableGridFields
	 * Evita queries repetidas a la base de datos durante la misma ejecución
	 * @var array
	 */
	private static $gridFieldsCache = array();

	/**
	 * Caché estático para almacenar resultados de getCalculatedDataField
	 * Evita queries repetidas para campos calculados del mismo grid
	 * @var array
	 */
	private static $calculatedFieldsCache = array();

	/** cleanEquationForPhp
	 * Limpia la ecuación de residuos JS/jQuery para que pueda evaluarse en PHP.
	 * Convierte: Number(jQuery("input[name='campo[]']").map(function(){return jQuery(this).val()/100;}).get( x )) en: campo
	 * @param string $equation
	 * @return string
	 */
	private static function cleanEquationForPhp($equation) {
			// Decodifica entidades HTML primero
			$equation = html_entity_decode($equation, ENT_QUOTES | ENT_HTML5);
			// Elimina patrones JS/jQuery con variantes codificadas o no, y conserva /100 si corresponde
			$pattern = '/([a-zA-Z0-9_]+)\[\](?:\'|&#039;)?\](?:\"|&quot;)?\)\.map\(function\(\)\{return jQuery\(this\)\.val\(\)(\/100)?;\}\)\.get\( x \)\)/';
			$equation = preg_replace_callback($pattern, function($matches) {
				// Si hay división por 100, conservarla
				return isset($matches[2]) && $matches[2] === '/100' ? $matches[1] . $matches[2] : $matches[1];
			}, $equation);
			// Elimina [] si quedaron
			$equation = str_replace(["[]"], "", $equation);
			return $equation;
		}

		/**
		 * Llena la tabla temporal de totales para un grid, recalculando los valores summary de cada registro.
		 *
		 * @param PearDatabase $adb Conexión a la base de datos
		 * @param array $gridColumnNames Nombres de las columnas del grid
		 * @param string $moduleName Nombre del módulo (por ejemplo, 'Accounts')
		 * @param string $gridName Nombre del grid
		 * @param string $temporaryTable Nombre de la tabla temporal a llenar
		 * @param bool $lockAlreadyHeld Indica si el lock ya fue adquirido antes de llamar a esta función
		 * @param string|null $lockedBy Identificador del proceso que tiene el lock (opcional)
		 */
		public static function fillTemporaryTable($adb, $gridColumnNames, $moduleName, $gridName, $temporaryTable, $lockAlreadyHeld = false, $lockedBy = null) {
			// 1. Si no se tiene el lock, intentar obtenerlo para evitar que dos procesos trabajen al mismo tiempo sobre la misma tabla temporal.
			// Esto es importante para evitar conflictos y asegurar la integridad de los datos.
			if (!$lockAlreadyHeld) {
				// Intentar obtener el lock (bloqueo) para este proceso. El lock previene que dos procesos hagan cambios simultáneamente y causen errores o datos inconsistentes.
				// El tiempo de espera para adquirir el lock es de 120 segundos.
				$lockAcquired = self::acquireGridSummaryLock($adb, $moduleName, $gridName, $temporaryTable, 120, $lockedBy);
				// Si no se pudo obtener el lock, salir de la función sin hacer nada.
				// Esto significa que otro proceso ya tiene el lock y está trabajando en la tabla temporal.
				if (!$lockAcquired) {
					return;
				}
			}
			// 2. Consultar si hay registros pendientes para procesar en la tabla temporal.
			// Busca registros del módulo que aún no han sido procesados ni insertados en la tabla temporal.
			$aux = "SELECT crmid FROM vtiger_crmentity WHERE setype= '".$moduleName."' AND deleted = 0 AND crmid not in (select recordid FROM ".$temporaryTable.")";
			// Ejecuta la consulta para obtener los registros pendientes.
			$result = $adb->pquery($aux);
			// Cuenta cuántos registros faltan por procesar.
			$numRegistros = $adb->num_rows($result);
			// Si no hay registros pendientes, libera el lock y sale de la función.
			if ((!$result) || ($numRegistros == 0 )) {
				// Libera el lock para que otros procesos puedan usar la tabla temporal.
				self::releaseGridSummaryLock($adb, $moduleName, $gridName, $temporaryTable);
				return;
			}
			
			// 3. OPTIMIZACIÓN: Pre-cargar todos los subfieldsid de una vez para evitar consultas repetidas
			$subfieldCache = array();
			if (!empty($gridColumnNames)) {
				$columnNamesArray = array_keys($gridColumnNames);
				$placeholders = implode(',', array_fill(0, count($columnNamesArray), '?'));
				$params = $columnNamesArray;
				$params[] = 2204; // uitype
				
				$cacheResult = $adb->pquery(
					"SELECT subfieldsid, name FROM vtiger_subfields_special WHERE name IN ($placeholders) AND uitype=?",
					$params
				);
				
				if ($adb->num_rows($cacheResult) > 0) {
					while ($cacheRow = $adb->fetch_array($cacheResult)) {
						$subfieldCache[$cacheRow['name']] = intval($cacheRow['subfieldsid']);
					}
				}
			}
			
			// Procesamiento masivo de los registros pendientes.
			while($row = $adb->fetch_array($result)) {
				$data    = array ();
				$gridRow = self::getGridValues ($adb, $moduleName, $gridName, $row['crmid'], true);
				$data    = array_merge($data, array ('recordid' => intval($row['crmid'])));
				foreach ($gridColumnNames as $columnname => $fieldName) {
					// Usar el cache en lugar de consultar la BD
					if (!isset($subfieldCache[$columnname])) {
						continue;
					}

					$subfieldsid = $subfieldCache[$columnname];

					// Usar el nuevo método para asegurar integridad y obtener el total actualizado

					$valorReal = GridFieldUtils::upsertGridValuesAndGetTotal(
						$adb,
						$moduleName,
						$columnname, // nombre del subcampo summary
						$row['crmid'],
						$gridRow, // array de filas del grid para este registro
						$subfieldsid
					);

					$dummy = explode ('_', $columnname);
					array_pop ($dummy);
					$gridFieldName = join ('_', $dummy);
					$gridRowValue  = round($valorReal,2);
					$data          = array_merge($data, array ($columnname => floatval($gridRowValue)));
				}
				
				//Preparar la instrucción para insertar el registro usando INSERT IGNORE, 
				// por lo cual no se usa $adb->run_insert_data
				$sql_fields1 = '';
				$sql_data1   = '';
				foreach ($data as $walk1 => $cur) {
					$sql_fields1 .= ($sql_fields1 ? ',' : '') . $walk1;
					$sql_data1 .= ($sql_data1 ? ',' : '') . $adb->sql_quote ($cur);
				}
				$sqlinstruction = 'INSERT IGNORE INTO ' . $temporaryTable . ' (' . $sql_fields1 . ') VALUES (' . $sql_data1 . ')';
				$adb->query ($sqlinstruction);
				$adb->query ("commit;");
				//
				$current++;
				$percent = intval(($current/$total)*100);
			}
		}

		/**
     * Extrae y retorna un array plano con los valores de un campo específico de cada registro en un arreglo de grilla.
		 *
		 * @author Equipo Platzilla
		 * @copyright Platzilla (c) 2025
		 * @version 1.0
		 * @date Última modificación: 2025-05-23
		 *
		 * @param array $values Arreglo de registros (cada uno como array asociativo de campos)
		 * @param string $gridFieldName Nombre del campo a extraer de cada registro
		 * @access private
		 * @return array|null Array plano de valores del campo solicitado, o null si el arreglo está vacío
		 *
		 * @example
		 *   $values = [
		 *     ['campoA' => 10, 'campoB' => 20],
		 *     ['campoA' => 30, 'campoB' => 40]
		 *   ];
		 *   $result = self::getGridFieldValues($values, 'campoA'); // [10, 30]
		 */
		private static function getGridFieldValues ($values, $gridFieldName) {
			if (!empty ($values)) {
				$fieldValues = array ();
				foreach ($values as $value) {
					$fieldValues [] = $value [ $gridFieldName ];
				}
			} else {
				$fieldValues = null;
			}
			return $fieldValues;
		}

		/**getRelatedGridField
		 * Obtiene la metadata y, opcionalmente, los valores summary de subcampos relacionados a un grid.
		 *
		 * Consulta la base de datos para obtener información estructurada sobre los subcampos (subfields) de un grid, incluyendo identificadores, nombres y,
		 * si corresponde, el valor summary asociado según la configuración serializada del campo. El resultado es útil para construir vistas, validaciones o cálculos sobre los subcampos de un grid.
		 *
		 * @author Equipo Platzilla
		 * @copyright Platzilla (c) 2025
		 * @version 1.0
		 * @date Última modificación: 2025-05-23
		 *
		 * @param PearDatabase $adb Conexión a la base de datos
		 * @param array $subfields Lista de nombres de subcampos a buscar
		 * @param array|null $dataValues (opcional) Valores asociados a los subcampos, usado para obtener valores summary
		 * @param bool $keySummay (opcional) Si es true, busca subcampos tipo summary y obtiene el valor correspondiente
		 * @access private
		 * @return array Array de arrays asociativos con la metadata y, si corresponde, el valor summary de cada subcampo
		 *
		 * @example
		 *   $subfields = ['fieldA_123', 'fieldB_456'];
		 *   $result = self::getRelatedGridField($adb, $subfields);
		 *   // Devuelve: [
		 *   //   ['gridName' => 'fieldA', 'fieldid' => 123, ...],
		 *   //   ['gridName' => 'fieldB', 'fieldid' => 456, ...]
		 *   // ]
		 */
		 private static function getRelatedGridField ($adb, $subfields, $dataValues = null, $keySummay = false) {
			$subfieldsData = array ();
			if (!count ($subfields)) {
				return $subfieldsData;
			}
			foreach ($subfields as $subfieldName) {
				if ($keySummay) {
					$dummy        = explode('_', $subfieldName);
					$fieldid      = array_pop ($dummy);
					$searchFieldName = 'summary_' . $fieldid;
				} else {
					$searchFieldName = $subfieldName;
				}
				$result = $adb->pquery(
					'SELECT 
							ss.subfieldsid,
							ss.fieldid,
							ss.data_field,
							f.fieldname
						  FROM 
							vtiger_subfields_special ss
						  INNER JOIN vtiger_field f ON f.fieldid = ss.fieldid
						  WHERE 
						  	ss.name=?',
					array ($searchFieldName)
				);
				if ($adb->num_rows ($result)) {
					while ($row = $adb->fetch_array($result)) {
						if ($keySummay) {
							$summay      = array_column (unserialize(base64_decode($row ['data_field'])), 'field');
							$theKey      = array_search($subfieldName, $summay);
							$summayValue = (!empty($dataValues)) ? $dataValues[ $searchFieldName ] [ $theKey ] : null;
						}

						$subfieldsData = array_merge(
							$subfieldsData,
							array (
								'gridName'     => $row ['fieldname'],
								'fieldid'      => $row ['fieldid'],
								'subfieldsid'  => $row  ['subfieldsid'],
								'subfieldName' => $subfieldName,
								'summaryValue' => (!empty ($row ['data_field']) && $theKey) ? $summayValue : null,
							)
						);
					}
					unset($row, $summay, $theKey, $summayValue);
					$result->free();
				}
			}
			unset($result);
			return $subfieldsData;
		}

		/**
		 * Retorna el índice de un código de módulo relacionado en los valores de un subcampo grid, o false si no existe.
		 *
		 * @author Equipo Platzilla
		 * @copyright Platzilla (c) 2025
		 * @version 1.0
		 * @date Última modificación: 2025-05-23
		 *
		 * @param PearDatabase $adb Conexión a la base de datos
		 * @param integer $gridFieldId ID del campo grid
		 * @param string $relModule Nombre del módulo relacionado
		 * @param string $codModule Código del módulo a buscar
		 * @param integer $recordId ID del registro principal
		 * @access private
		 * @return integer|false Índice del código en los valores relacionados, o false si no está presente
		 */
		private static function getRowRelatedGridField ($adb, $gridFieldId, $relModule, $codModule, $recordId) {
			$result = $adb->pquery(
				'SELECT 
						sfv.field_values,
						ss.subfieldsid
					  FROM 
					  	vtiger_subfields_values sfv
					  INNER JOIN vtiger_subfields_special ss ON sfv.subfieldsid = ss.subfieldsid
					  INNER JOIN vtiger_crmentity crme ON crme.crmid = sfv.modulecfid AND crme.deleted = 0
					  WHERE
					  ss.fieldid=? AND 
					  ss.uitype=? AND 
					  ss.relmodule=? AND 
					  sfv.modulecfid=?',
				array ($gridFieldId, 10, $relModule, $recordId)
			);
			if ($adb->num_rows ($result)) {
				$row = $adb->fetch_array($result);
				$listsCode = unserialize(base64_decode($row ['field_values']));
				$keyIndex = array_search($codModule, $listsCode);
				$result->free();
				unset($row, $listsCode);
				return $keyIndex;
			}
			$result->free();
			return false;
		}

		/**
		 * Construye el array de valores summary para una fila de grid, asignando cada valor a su columna summary según la configuración.
		 *
		 * @author Equipo Platzilla
		 * @copyright Platzilla (c) 2025
		 * @version 1.0
		 * @date Última modificación: 2025-05-23
		 *
		 * @param PearDatabase $adb Conexión a la base de datos
		 * @param array $rows Datos de la fila, incluyendo summaryId y values
		 * @param array &$values Referencia al array donde se agregan los valores summary
		 * @access private
		 * @return void
		 */
		private static function getRowSummary (PearDatabase $adb, $rows, &$values) {
			$values['summary'] = array ();
			$dataFields = $adb->run_query_allrecords("SELECT data_field FROM vtiger_subfields_special WHERE subfieldsid={$rows['summaryId']}");
			if (!empty ($dataFields[0]['data_field'])) {
				$summaryFields = unserialize (base64_decode($dataFields[0]['data_field']));
				$totalFields = count($summaryFields);
				for ($k = 0; $k < $totalFields; $k++) {
					if ($summaryFields[$k]['field'] == 'false') {
						continue;
					}
					$dummy = explode ('_', $summaryFields[$k]['field']);
					array_pop ($dummy);
					$columnName  = (join ('_', $dummy));
					$values['summary'] = array_merge($values['summary'], array ($columnName => $rows['values'][$k]));
				}
			}
		}

		/**getSummaryGridFields
		 * Devuelve un array asociativo de columnas summary con metadata completa para validación y generación de queries.
		 * Cada elemento contiene: column (nombre técnico), columnName (amigable), fieldId, action, expression, fieldName.
		 * Si $extended es false, mantiene compatibilidad y retorna solo nombres de columna.
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $gridName
		 * @param bool $extended
		 * @return array|null
		 * @throws Exception
		 */
		public static function getSummaryGridFields (PearDatabase $adb, $moduleName, $gridName) {
			$fieldsGrid = FieldGridManager::getInstance($adb)->fetchFieldGrid($moduleName, $gridName);
			if (!$fieldsGrid) {
				return null;
			};
			$gridColumns = array();
			foreach ($fieldsGrid as $field) {
					if ($field->getUiType () != FieldInterface::UI_TYPE_SUMMARY_ROW) {
					continue;
				}
				$summaryConfig = unserialize(base64_decode($field->getDataField()));
				$summaryFields = array_column ($summaryConfig, 'field');

					foreach ($summaryFields as $column) {
						if($column != 'false') {
							$dummy = explode ('_', $column);
						array_pop($dummy);
						$columnName = ucfirst(join('_', $dummy));
							$gridColumns = array_merge ($gridColumns, array ($column => $columnName));
					}
				}
			}
			return $gridColumns;
		}

		/**
		 * Estima el tiempo restante (en segundos) para completar el llenado de la tabla temporal de totales.
		 * Devuelve un entero con los segundos estimados, o -1 si no se puede calcular.
		 */
		private static function getTemporaryTableETA($adb, $temporaryTable, $moduleName, $startTime = null) {
			// Total de registros esperados
			$sqlTotal = "SELECT COUNT(*) as total FROM vtiger_crmentity WHERE setype=? AND deleted=0";
			$resTotal = $adb->pquery($sqlTotal, [$moduleName]);
			$total = ($resTotal && $adb->num_rows($resTotal)) ? intval($adb->query_result($resTotal, 0, 'total')) : 0;
			if ($resTotal) { $resTotal->free(); unset($resTotal); }

			// Registros ya procesados
			$sqlDone = "SELECT COUNT(*) as done FROM $temporaryTable";
			$resDone = $adb->pquery($sqlDone, []);
			$done = ($resDone && $adb->num_rows($resDone)) ? intval($adb->query_result($resDone, 0, 'done')) : 0;
			if ($resDone) { $resDone->free(); unset($resDone); }

			if ($total == 0 || $done == 0) return -1;

			// Si no se pasa tiempo de inicio, usar tiempo actual (menos preciso)
			if ($startTime === null) {
				$startTime = isset($_SESSION['grid_temp_table_start']) ? $_SESSION['grid_temp_table_start'] : microtime(true);
				$_SESSION['grid_temp_table_start'] = $startTime;
			}
			$elapsed = microtime(true) - $startTime;
			$rate = $done / $elapsed;
			if ($rate <= 0) return -1;
			$remaining = $total - $done;
			$eta = intval($remaining / $rate);
			return $eta;
		}
		/**
		 * Calcula el porcentaje de avance de la tabla temporal de totales.
		 * Devuelve un entero entre 0 y 100.
		 */
		private static function getTemporaryTableProgress($adb, $temporaryTable, $moduleName) {
			// Total de registros esperados
			$sqlTotal = "SELECT COUNT(*) as total FROM vtiger_crmentity WHERE setype=? AND deleted=0";
			$resTotal = $adb->pquery($sqlTotal, [$moduleName]);
			$total = ($resTotal && $adb->num_rows($resTotal)) ? intval($adb->query_result($resTotal, 0, 'total')) : 0;
			if ($resTotal) { $resTotal->free(); unset($resTotal); }

			// Registros ya procesados
			$sqlDone = "SELECT COUNT(*) as done FROM $temporaryTable";
			$resDone = $adb->pquery($sqlDone, []);
			$done = ($resDone && $adb->num_rows($resDone)) ? intval($adb->query_result($resDone, 0, 'done')) : 0;
			if ($resDone) { $resDone->free(); unset($resDone); }

			if ($total == 0) return 100;
			$percent = intval(($done / $total) * 100);
			if ($percent > 100) $percent = 100;
			return $percent;
		}


		/**
		 * Organiza y limpia un array de campos relacionados, eliminando elementos no válidos para mantener la estructura esperada.
		 *
		 * @author Equipo Platzilla
		 * @copyright Platzilla (c) 2025
		 * @version 1.0
		 * @date Última modificación: 2025-05-23
		 *
		 * @param array &$dataField Referencia al array de campos a organizar
		 * @access private
		 * @return void
		 */
		private static function organizeRelatedList (&$dataField) {
			$totalDataField = count ($dataField);
			for ($k = 1; $k < $totalDataField; $k++) {
				if (!is_array ($dataField[ $k ]) && ($k < count ($dataField))) {
					array_splice ($dataField, ($k - 1), 1);
				}
			}
			$totalDataField = count ($dataField);
			$dataGroup      = $dataField[0];
			$numGrupo       = 0;
			for ($k = 1; $k < $totalDataField; $k++) {
				if (is_array ($dataField[ $k ])) {
					$listValue = array_values ($dataField[ $k ]);
					$dataGroup .= '@' . $listValue[0];
				} else {
					$dataField[ $numGrupo ] = $dataGroup;
					$dataGroup              = $dataField[ $k ];
					$numGrupo++;
				}
			}
			$dataField[ $numGrupo ] = $dataGroup;
			$dataField              = array_slice ($dataField, 0, ($numGrupo + 1));
		}

		/**
		 * @param PearDatabase$adb
		 * @param integer$gridFieldId
		 * @param string $equation
		 *
		 * @return null|array
		 */
		private static function searchFieldInEquation ($adb, $gridFieldId, $equation) {
			$calculationFileds = array ();
			$result = $adb->pquery('SELECT subfieldsid, name FROM vtiger_subfields_special WHERE fieldid=? AND INSTR(?, name) > 0', array ($gridFieldId, $equation));
			if ($adb->num_rows ($result)) {
				while ($row = $adb->fetch_array ($result)) {
					$calculationFileds = array_merge ($calculationFileds, array ($row ['name'] => intval ($row ['subfieldsid'])));
				}
			}
			return (count ($calculationFileds)) ? $calculationFileds : null;
		}

		/**
		 * Obtiene la metadata de los subcampos calculados (uitype 2204) asociados a un campo grid.
		 * Esta función consulta la tabla vtiger_subfields_special para encontrar todos los subfields de tipo calculado
		 * (uitype 2204) relacionados con el grid especificado por $gridFieldId. Devuelve un array con la metadata de cada
		 * subfield calculado, incluyendo su identificador (subfieldsid), la ecuación asociada 
		 * (equation) y los campos involucrados en dicha ecuación (fields).
		 *
		 * Uso típico: Se utiliza como paso previo para identificar los subfields calculados de un grid, obtener la ecuación
		 * asociada y los campos dependientes, y posteriormente calcular o consultar el valor real almacenado en la tabla
		 * vtiger_subfields_values usando el subfieldsid retornado.
		 *
		 * @param PearDatabase $adb         Conexión activa a la base de datos
		 * @param integer      $gridFieldId Identificador del campo grid principal
		 *
		 * @return array|null Un array de arrays con las claves:
		 *                   - subfieldsid: int, identificador del subfield calculado
		 *                   - equation: string, ecuación matemática asociada (limpia de sintaxis JS)
		 *                   - fields: array|null, campos dependientes presentes en la ecuación
		 *                   Devuelve null si no existen subfields calculados asociados.
		 *
		 * @example
		 * $metadata = GridFieldUtils::getCalculatedDataField($adb, $fieldId);
		 * foreach ($metadata as $calc) {
		 *     // $calc['subfieldsid'], $calc['equation'], $calc['fields']
		 * }
		 *
		 * @author Equipo Platzilla
		 * @copyright Platzilla (c) 2025
		 */
		private static function getCalculatedDataField ($adb, $gridFieldId) {
			// OPTIMIZACIÓN: Verificar caché primero para evitar consultas repetidas
			$cacheKey = 'grid_' . $gridFieldId;
			if (isset(self::$calculatedFieldsCache[$cacheKey])) {
				return self::$calculatedFieldsCache[$cacheKey];
			}

			$calcualtedData = array ();
			$result = $adb->pquery(
				'SELECT 
						subfieldsid,
						data_field
					  FROM 
					  	vtiger_subfields_special
					  WHERE 
					  uitype=? AND 
					  fieldid=?',
				array (2204, $gridFieldId)
			);
			if ($adb->num_rows ($result)) {
				// OPTIMIZACIÓN: Obtener todos los subfields de una vez para evitar consultas repetidas
				$allSubfields = array();
				$subfieldResult = $adb->pquery(
					'SELECT subfieldsid, name FROM vtiger_subfields_special WHERE fieldid=?',
					array($gridFieldId)
				);
				if ($adb->num_rows($subfieldResult)) {
					while ($subfieldRow = $adb->fetch_array($subfieldResult)) {
						$allSubfields[$subfieldRow['name']] = intval($subfieldRow['subfieldsid']);
					}
				}
				
				// Pre-compilar patrones de reemplazo para evitar repetición
				$replacements = array(
					'Number(jQuery(&quot;input[name=&#039;' => '',
					'[]&#039;]&quot;).map(function(){return jQuery(this).val();}).get( x ))' => '',
					'[]&#039;]&quot;).map(function(){return jQuery(this).val()/100;}).get( x ))' => '',
					'[]&#039;]&quot;).map(function(){return jQuery(this).val()/100;}).get( x ))' => '/100',
					'[]&#039;]&quot;).map(function(){return jQuery(this).val()/100;}).get( x )))' => '/100',
				);
				
				while ($row = $adb->fetch_array ($result)) {
					// Limpiar la ecuación de todo el código JavaScript
					$equation = $row['data_field'];
					// Aplicar todos los reemplazos de una vez
					$equation = str_replace(array_keys($replacements), array_values($replacements), $equation);
					// Limpiar entidades HTML
					$equation = html_entity_decode($equation, ENT_QUOTES, 'UTF-8');
					
					// Buscar campos en la ecuación usando el cache en memoria
					$fieldsInEquation = array();
					if (!empty($equation) && !empty($allSubfields)) {
						foreach ($allSubfields as $fieldName => $subfieldId) {
							if (strpos($equation, $fieldName) !== false) {
								$fieldsInEquation[$fieldName] = $subfieldId;
							}
						}
					}
					
					$calcualtedData [] = array (
						'subfieldsid' => $row ['subfieldsid'],
						'equation'    => $equation,
						'fields'      => !empty($fieldsInEquation) ? $fieldsInEquation : null,
					);
				}
			}
			// Guardar resultado en caché para evitar consultas repetidas
			$result = (count ($calcualtedData)) ? $calcualtedData : null;
			self::$calculatedFieldsCache[$cacheKey] = $result;
			return $result;
		}

		/**
		 * Asegura que los valores de la columna objetivo estén actualizados si es calculada.
		 * Si la columna es calculada (uitype 2204), recalcula cada celda usando la ecuación y
		 * sustituyendo nulos/vacíos por cero SOLO para efectos del cálculo del total summary.
		 * No modifica los campos no calculados en la base de datos.
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $gridFieldName Nombre de la columna objetivo
		 * @param int $entityId
		 * @param array $values Array de filas del grid (cada una asociativa)
		 * @param int $subfieldsid ID del subcampo grid (summary)
		 * @return array Array de filas del grid con la columna objetivo actualizada si es calculada
		 */
		private static function ensureCalculatedColumnValuesUpdated($adb, $moduleName, $gridFieldName, $entityId, $values, $subfieldsid, $calculatedFieldId) {

			if (empty($values) || empty($gridFieldName) || empty($moduleName) || empty($subfieldsid)) {
				return $values;
			}
			// Buscar metadata del grid principal (fieldid)
			$fieldId = null;
			$meta = self::getAvailableGridFields($adb, $moduleName);
			if ($meta && is_array($meta)) {
				foreach ($meta as $gridSubfields) {
					foreach ($gridSubfields as $subfield) {
						if (isset($subfield['subfieldsid']) && $subfield['subfieldsid'] == $subfieldsid) {
							$fieldId = $subfield['fieldid'];
							break 2; // Rompe ambos foreach
						}
					}
				}
			}
			if (!$fieldId) {
				return $values;
			}
			// Buscar ecuación del subcampo calculado exacto
			$calculatedFields = self::getCalculatedDataField($adb, $fieldId);
			$equation = null;
			$fields = array();
			if ($calculatedFields && is_array($calculatedFields)) {
				foreach ($calculatedFields as $calc) {
					if (isset($calc['subfieldsid']) && $calc['subfieldsid'] == $subfieldsid) {
						$equation = $calc['equation'];
						$fields = $calc['fields'];
						// Limpiar la ecuación para uso PHP
						$equation = self::cleanEquationForPhp($equation);
						break;
					}
				}
			}
			if (!$equation || empty($fields)) {
				return $values;
			}
			// Pre-cargar valores de campos dependientes desde la BD si faltan en alguna fila
			$dependentsCache = array(); // [campo][rowIndex] => valor
			if (!empty($fields)) {
				$subfieldIds = array_values($fields);
				$placeholders = implode(',', array_fill(0, count($subfieldIds), '?'));
				$params = array_merge($subfieldIds, array($entityId));
				
				$gridSubfields = $adb->pquery(
					"SELECT subfieldsid, field_values FROM vtiger_subfields_values WHERE subfieldsid IN ($placeholders) AND modulecfid = ?",
					$params
				);
				
				if ($adb->num_rows($gridSubfields) > 0) {
					// Crear un mapa inverso de fid => campo
					$fidToCampo = array_flip($fields);
					
					while ($row = $adb->fetch_array($gridSubfields)) {
						$fid = $row['subfieldsid'];
						$campo = isset($fidToCampo[$fid]) ? $fidToCampo[$fid] : null;
						
						if ($campo && !empty($row['field_values'])) {
							$fieldValues = unserialize(base64_decode($row['field_values']));
							if (is_array($fieldValues)) {
								foreach ($fieldValues as $rowIdx => $val) {
									$dependentsCache[$campo][$rowIdx] = $val;
								}
							}
						}
					}
				}
			}
			// Recalcular solo filas válidas (ignorar summary y vacías)
			foreach ($values as $idx => $fila) {
				if ($idx === 'summary') {
					continue; // No calcular para la fila summary
				}
				if (!is_array($fila) || isset($fila['summary'])) {
					continue;
				}
				$tieneAlMenosUnCampo = false;
				foreach ($fields as $campo => $fid) {
					$valor = isset($fila[$campo]) ? $fila[$campo] : (isset($dependentsCache[$campo][$idx]) ? $dependentsCache[$campo][$idx] : 0);
					if ($valor !== '' && $valor !== null) {
						$tieneAlMenosUnCampo = true;
						break;
					}
				}
				if (!$tieneAlMenosUnCampo) {
					continue;
				}
				// Construir contexto de cálculo
				$contexto = array();
				foreach ($fields as $campo => $fid) {
					$valor = isset($fila[$campo]) ? $fila[$campo] : (isset($dependentsCache[$campo][$idx]) ? $dependentsCache[$campo][$idx] : 0.00);
					if ($valor === null || $valor === '' || !is_numeric($valor)) {
						$valor = 0.00;
					}
					$contexto[$campo] = $valor;
				}
							// Evaluar directamente la ecuación limpia, usando los valores de la fila
				$ecuacionEval = preg_replace('/\s+/', '', $equation); // Elimina todos los espacios
				// Sustituir variables de la ecuación por valores del contexto (solo si hay coincidencias exactas)
				foreach ($contexto as $campo => $valor) {
					$valorNum = is_numeric($valor) ? (float)$valor : 0.0;
					$antes = $ecuacionEval;
					$ecuacionEval = preg_replace('/\\b' . preg_quote($campo, '/') . '\\b/', $valorNum, $ecuacionEval);
				}
				// Limpieza final de residuos JS/jQuery o HTML antes de evaluar
				$ecuacionEval = self::cleanEquationForPhp($ecuacionEval);
				$valorCalculado = FieldCalculate::calculate($ecuacionEval);
				$valorCalculado = round($valorCalculado, 2);
				$values[$idx][$gridFieldName] = $valorCalculado;
			}
			return $values;
		}


		/**
		 * Recupera la metadata y los valores summary de una fila summary (uitype 2203) para un grid y registro dado.
		 *
		 * Devuelve un array con el subfieldsid, la metadata (summaryData) y los valores summary (summaryValues) ya decodificados,
		 * o null si no existe la fila summary para los parámetros dados.
		 *
		 * @param PearDatabase $adb        Conexión a la base de datos
		 * @param integer      $gridFieldId ID del campo grid principal
		 * @param integer      $modulecfId  ID del registro relacionado (módulo personalizado)
		 * @return array|null  Array con claves 'subfieldsid', 'summaryData', 'summaryValues' o null si no hay datos
		 */
		private static function getSummaryRowData ($adb, $gridFieldId, $modulecfId) {
			$result = $adb->pquery(
				'SELECT 
				  	ss.subfieldsid,
				  	ss.data_field,
				  	sfv.field_values
				FROM 
				  	vtiger_subfields_special ss
				INNER JOIN vtiger_subfields_values sfv ON sfv.subfieldsid = ss.subfieldsid
				WHERE 
				  	ss.uitype=? AND 
				  	ss.fieldid=? AND 
				  	sfv.modulecfid=?',
				array (2203, $gridFieldId, $modulecfId)
			);
			if ($adb->num_rows ($result)) {
				$row = $adb->fetch_array ($result);
				return array (
					'subfieldsid'   => $row ['subfieldsid'],
					'summaryData'   => unserialize (base64_decode ($row ['data_field'])),
					'summaryValues' => unserialize (base64_decode ($row ['field_values'])),
				);
			}
			return null;
		}

		/**
		 * @param PearDatabase$adb
		 * @param array $requestData
		 */
		private static function updateIdToCreateRelated ($adb, $requestData) {
			$module        = $requestData ['module'];
			$recordId      = $requestData ['recordId'];
			$adb->query("UPDATE vtiger_grid_related_grid SET toid={$recordId} WHERE tomodule='{$module}' AND recordmask='create' AND toid=1");
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $requestData
		 */
		private static function updateRelatedImport ($adb, $requestData) {
			$module        = $requestData ['module'];
			$recordId      = $requestData ['recordId'];
			$relatedFields = array ();

			$result = $adb->pquery('SELECT * FROM vtiger_grid_related_grid WHERE fromid=? AND frommodule=?', array($recordId, $module));
			if ($adb->num_rows ($result)) {
				while ($row = $adb->fetch_array ($result)) {
					$fieldToData      = self::getRelatedGridField ($adb, explode (',', $row ['fieldsto']));
					$relatedFields [] = array (
						'fieldsFrom'     => self::getRelatedGridField ($adb, explode (',', str_replace ($module.'@', '', $row ['fieldsfrom'])), $requestData ['requestData'], true),
						'fieldsTo'       => $fieldToData,
						'rowId'          => self::getRowRelatedGridField ($adb, $fieldToData['fieldid'], $module, $row ['codmodule'], $row ['toid']),
						'calculatedData' => self::getCalculatedDataField ($adb, $fieldToData['fieldid']),
						'summaryRow'     => self::getSummaryRowData ($adb, $fieldToData['fieldid'], $row ['toid']),
						'moduleTo'       => $row ['tomodule'],
						'cod'            => $row ['codmodule'],
						'idTo'           => $row ['toid'],
					);
				}
			}
			$totalRelatedFields = count ($relatedFields);
			for ($k = 0; $k < $totalRelatedFields; $k++) {
				$subFieldId   = $relatedFields [$k] ['fieldsTo']['subfieldsid'];
				if (empty($subFieldId)) {
					continue;
				}
				$modulecfId   = $relatedFields [$k] ['idTo'];
				$gridSubfield = $adb->run_query_allrecords("SELECT field_values FROM vtiger_subfields_values WHERE subfieldsid={$subFieldId} AND modulecfid={$modulecfId}");
				$fieldValue   = unserialize(base64_decode($gridSubfield[0]['field_values']));
				if ($relatedFields [$k]['rowId'] !== false) {
					$fieldValue [ $relatedFields[$k]['rowId'] ] = $relatedFields [$k] ['fieldsFrom'] ['summaryValue'];
					$fieldStringValue                          = base64_encode(serialize($fieldValue));
					$adb->query("UPDATE vtiger_subfields_values SET field_values='{$fieldStringValue}' WHERE subfieldsid={$subFieldId} AND modulecfid={$modulecfId}");
					if (! empty($relatedFields [$k]['calculatedData'])) {
						self::updateCalculatedRow ($adb, $relatedFields [$k]['calculatedData'], $relatedFields [$k]['rowId'], $modulecfId);
					}
					if (!empty ($relatedFields [$k]['summaryRow'])) {
						self::updateSummaryRow ($adb, $relatedFields [$k]['summaryRow'], $modulecfId);
					}
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $calculatedData
		 * @param integer $rowIndex
		 * @param integer $recordId
		 */
		private static function updateCalculatedRow ($adb, $calculatedData, $rowIndex, $recordId) {
			foreach ($calculatedData as $dummy) {
				$equation = $dummy ['equation'];
				foreach ($dummy ['fields'] as $fieldName => $fieldId) {
					$pattern = "/\b{$fieldName}/";
					if (! preg_match ($pattern, $equation)) {
						continue;
					}
					$gridSubfield = $adb->run_query_allrecords("SELECT field_values FROM vtiger_subfields_values WHERE subfieldsid={$fieldId} AND modulecfid={$recordId}");
					$fieldValue   = unserialize (base64_decode ($gridSubfield[0]['field_values']));
					$equation     = str_replace ($fieldName, $fieldValue [$rowIndex], $equation);
				}
				preg_match_all('/[\+\-\*\/]/', $equation, $matches);
				if (count ($matches [0]) < 2) {
					$equation = str_replace (array('(', ')'), '', $equation);
				}
				$result                  = FieldCalculate::calculate ($equation);
				$calculatedRowValue      = $adb->run_query_allrecords ("SELECT field_values FROM vtiger_subfields_values WHERE subfieldsid={$dummy ['subfieldsid']} AND modulecfid={$recordId}");
				$fieldValue              = unserialize(base64_decode($calculatedRowValue[0]['field_values']));
				$fieldValue[ $rowIndex ] = $result;
				$fieldStringValue        = base64_encode (serialize ($fieldValue));
				$adb->query("UPDATE vtiger_subfields_values SET field_values='{$fieldStringValue}' WHERE subfieldsid={$dummy ['subfieldsid']} AND modulecfid={$recordId}");
			}
		}

		/** updateSummaryRow
		 * Recalcula y actualiza los valores summary (totales) de una fila summary para un registro de grid.
		 *
		 * Este método recorre la configuración summary de la fila indicada, calcula los totales (por ejemplo, suma) de los subcampos correspondientes
		 * y actualiza el valor serializado en la tabla vtiger_subfields_values para el registro y subcampo summary indicados.
		 *
		 * @param PearDatabase $adb            Conexión a la base de datos
		 * @param array        $summaryRowData Metadata y configuración de la fila summary (incluye summaryData y subfieldsid)
		 * @param integer      $modulecfId     ID del registro principal (entidad) al que pertenece la fila summary
		 *
		 * @return void
		 */
		private static function updateSummaryRow ($adb, $summaryRowData, $modulecfId) {
			$summaryRowValues = array();
			foreach ($summaryRowData ['summaryData'] as $rowCell) {
				if (($rowCell ['field'] != 'false') && ($rowCell ['action'] == 'sum')) {
					$rowCellValues = $adb->run_query_allrecords (
						"SELECT 
								  sfv.field_values 
								FROM 
								  vtiger_subfields_values sfv 
								INNER JOIN vtiger_subfields_special ss ON ss.subfieldsid = sfv.subfieldsid
								WHERE 
								  ss.name='{$rowCell ['field']}' AND 
								  sfv.modulecfid={$modulecfId}"
					);
					$fieldValue          = unserialize (base64_decode ($rowCellValues [0]['field_values']));
					$summaryRowValues [] = array_sum ($fieldValue);
				} else {
					$summaryRowValues [] = 0;
				}
			}
			if (count ($summaryRowValues)) {
				$fieldStringValue = base64_encode (serialize ($summaryRowValues));
				$adb->query("UPDATE vtiger_subfields_values SET field_values='{$fieldStringValue}' WHERE subfieldsid={$summaryRowData ['subfieldsid']} AND modulecfid={$modulecfId}");
			}
		}

		/** createTempGridValues
		 * Crea y llena una tabla temporal con los valores summary de un grid para un módulo específico.
		 *
		 * Este método elimina (DROP) y recrea la tabla temporal indicada para almacenar los valores summary (totales, sumas, etc.)
		 * de los subcampos de un grid, según la configuración del módulo y grid especificados. Luego, llena la tabla con los datos actuales.
		 *
		 * Advertencia: Esta operación elimina la tabla temporal si existe, por lo que debe usarse con precaución para evitar pérdida de datos temporales.
		 *
		 * @param PearDatabase $adb         Conexión a la base de datos
		 * @param string       $moduleName  Nombre del módulo
		 * @param string       $gridName    Nombre del grid
		 * @param string       $temporaryTable Nombre de la tabla temporal a crear y llenar
		 *
		 * @return array|null  Arreglo con los nombres de columnas summary creadas, o null si no existen columnas
		 * @throws Exception   Si ocurre un error durante la creación o llenado de la tabla temporal
		 */
		public static function createTempGridValues ($adb, $moduleName, $gridName, $temporaryTable, $isIntact = true) {
			$gridColumnNames = self::getSummaryGridFields ($adb, $moduleName, $gridName);
			if (empty ($gridColumnNames)) {
				return null;
			}

			// --- LOCKING GLOBAL PARA RECONSTRUCCIÓN DE TABLA TEMPORAL ---
			// --- Cálculo dinámico del timeout para el lock zombie ---
			$sqlCount = "SELECT COUNT(*) as total FROM vtiger_crmentity WHERE setype= ? AND deleted = 0";
			$resCount = $adb->pquery($sqlCount, [$moduleName]);
			$numRegistros = ($resCount && $adb->num_rows($resCount)) ? intval($adb->query_result($resCount, 0, 'total')) : 0;
			$tiempoPorRegistro = 0.011; // segundos
			$margenSeguridad = 15; // segundos extra
			$timeoutZombie = intval($numRegistros * $tiempoPorRegistro + $margenSeguridad);
			if ($timeoutZombie < 60) $timeoutZombie = 60; // mínimo 60 segundos
			
			$maxWaitSeconds = $tiempoPorRegistro; // Tiempo máximo de espera total para el proceso que espera
			$sleepInterval = 2;  // Intervalo entre reintentos
			$waited = 0;
			$lockedBy = getmypid() . '-' . uniqid();
			$lockAcquired = false;
			
			while (!$lockAcquired && $waited < $maxWaitSeconds) {
				$lockAcquired = self::acquireGridSummaryLock($adb, $moduleName, $gridName, $temporaryTable, $timeoutZombie, $lockedBy);
				if (!$lockAcquired) {
					// Mostrar mensaje de espera al usuario (solo la primera vez)
					if ($waited == 0) {
						echo "<div id='progress-message' style='margin:10px 0;font-weight:bold;color:#c25c00;'>La tabla de totales está siendo actualizada por otro usuario. Por favor, espere unos segundos...</div>\n";
						flush();
					}
					sleep($sleepInterval);
					$waited += $sleepInterval;
				}
			}
			if (!$lockAcquired) {
				echo "<div style='color:#c20000;font-weight:bold;'>No se pudo actualizar la tabla de totales. Intente nuevamente más tarde.</div>\n";
				return null;
			}

			try {
				$columns    = $adb->getColumnNames ($temporaryTable);
				$newColumns = array ();
				$shouldRecreate = false;
				
				// Si $isIntact es false, forzar recreación completa (DROP + CREATE)
				if (!$isIntact && !empty($columns)) {
					$adb->query ("DROP TABLE `{$temporaryTable}`;");
					$columns = array(); // Marcar como si no existiera para forzar CREATE
					$shouldRecreate = true;
				} elseif (!empty ($columns)) {
					// Lógica original: verificar si hay cambios en la estructura
					$currentColumnNames = array_keys($gridColumnNames);
					$newColumns = array_diff($currentColumnNames, $columns);
					$removedColumns = array_diff($columns, array_merge(['recordid'], $currentColumnNames));
					
					// Recrear si hay columnas nuevas O columnas eliminadas
					if (count($newColumns) > 0 || count($removedColumns) > 0) {
						$adb->query ("DROP TABLE `{$temporaryTable}`;");
						$columns = array();
						$shouldRecreate = true;
					} else {
						// Si no hay cambios estructurales, verificar integridad de datos
						$integrityCheck = QueryGenerator::checkGridSummaryTableIntegrity($adb, $moduleName, $gridName, $temporaryTable);
						if (!$integrityCheck) {
							$adb->query("TRUNCATE `{$temporaryTable}`;");
						}
					}
				}

				if (empty ($columns) || $shouldRecreate) {
					$query = "CREATE TABLE IF NOT EXISTS `{$temporaryTable}` ( `recordid` INT(19) NOT NULL,";
					foreach ($gridColumnNames as $columnname => $fieldName) {
						$query .= "`{$columnname}` FLOAT(18,2) NOT NULL DEFAULT '0.00', ";
					}
					$query .= 'UNIQUE KEY recordid (recordid)) ENGINE=InnoDB DEFAULT CHARSET=utf8';
					$adb->query($query);
				}

				self::fillTemporaryTable($adb, $gridColumnNames, $moduleName, $gridName, $temporaryTable, true, $lockedBy);
			} finally {
				self::releaseGridSummaryLock($adb, $moduleName, $gridName, $temporaryTable, $lockedBy);
			}
			return $gridColumnNames;
		}

		/**
		 * Intenta adquirir un lock global para la reconstrucción de la tabla de totales.
		 * Devuelve el locked_by si el lock es adquirido, false si ya existe un lock vigente y no expirado.
		 */
		private static function acquireGridSummaryLock($adb, $moduleName, $gridName, $temporaryTable, $timeout = 60, $lockedBy = null) {
			if ($lockedBy === null) {
				$lockedBy = getmypid() . '-' . uniqid();
			}
			$now = date('Y-m-d H:i:s');
			// Buscar si ya existe lock
			$sql = "SELECT locked_by, lock_time FROM vtiger_grid_summary_locks WHERE modulename=? AND gridname=? AND tablename=?";
			$res = $adb->pquery($sql, [$moduleName, $gridName, $temporaryTable]);
			if ($adb->num_rows($res) > 0) {
				$row = $adb->fetch_array($res);
				$lock_time = strtotime($row['lock_time']);
				$now_ts = strtotime($now);
				if (($now_ts - $lock_time) < $timeout) {
					// --- NUEVO: Si el lock está vigente pero los totales YA están listos, eliminar lock y permitir adquisición ---
					if (self::isGridTotalsUpToDate($adb, $moduleName, $temporaryTable)) {
						$adb->pquery("DELETE FROM vtiger_grid_summary_locks WHERE modulename=? AND gridname=? AND tablename=?", [$moduleName, $gridName, $temporaryTable]);
					} else {
						// Lock vigente, no se puede adquirir
						return false;
					}
				} else {
					// Lock zombie: eliminarlo y permitir adquisición
					$adb->pquery("DELETE FROM vtiger_grid_summary_locks WHERE modulename=? AND gridname=? AND tablename=?", [$moduleName, $gridName, $temporaryTable]);
				}
			}
			// Insertar nuevo lock
			$adb->pquery(
				"INSERT INTO vtiger_grid_summary_locks (modulename, gridname, tablename, locked_by, lock_time) VALUES (?, ?, ?, ?, ?)",
				[$moduleName, $gridName, $temporaryTable, $lockedBy, $now]
			);
			return $lockedBy;
		}

		/**
		 * Verifica si la tabla temporal de totales ya está completamente actualizada (sin registros pendientes).
		 * Devuelve true si ya está lista.
		 */
		private static function isGridTotalsUpToDate($adb, $moduleName, $temporaryTable) {
			$sql = "SELECT crmid FROM vtiger_crmentity WHERE setype=? AND deleted=0 AND crmid NOT IN (SELECT recordid FROM $temporaryTable)";
			$res = $adb->pquery($sql, [$moduleName]);
			if ($res && $adb->num_rows($res) == 0) {
				return true;
			}
			return false;
		}
		/**
		 * Libera el lock global de la tabla de totales.
		 */
		private static function releaseGridSummaryLock($adb, $moduleName, $gridName, $temporaryTable, $lockedBy = null) {
			if ($lockedBy !== null) {
				// Solo libera si el lock pertenece al proceso actual
				$adb->pquery("DELETE FROM vtiger_grid_summary_locks WHERE modulename=? AND gridname=? AND tablename=? AND locked_by=?", [$moduleName, $gridName, $temporaryTable, $lockedBy]);
			} else {
				// Compatibilidad retro, libera sin validar dueño
				$adb->pquery("DELETE FROM vtiger_grid_summary_locks WHERE modulename=? AND gridname=? AND tablename=?", [$moduleName, $gridName, $temporaryTable]);
			}
		}


		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 */
		public static function deleteGridValues (PearDatabase $adb, $entityId) {
			FieldGridManager::getInstance ($adb)->deleteFieldGridValues ($entityId);
		}

		/** getAvailableGridFields
		 * Obtiene la metadata de los campos grid disponibles para un módulo.
		 *
		 * Este método consulta y retorna un arreglo con la información de todos los subcampos (grids) definidos y disponibles para el módulo indicado.
		 * Cada subcampo incluye su identificador, etiquetas, nombre, secuencia y otros metadatos útiles para construir vistas o realizar operaciones sobre grids.
		 *
		 * @param PearDatabase $adb      Conexión a la base de datos
		 * @param string       $moduleName Nombre del módulo
		 *
		 * @return array|null  Arreglo asociativo con la metadata de los subcampos grid disponibles, o null si no existen grids
		 * @throws Exception   Si ocurre un error al consultar los campos grid
		 */
		public static function getAvailableGridFields (PearDatabase $adb, $moduleName) {
			// Verificar si ya existe en caché
			if (isset(self::$gridFieldsCache[$moduleName])) {
				return self::$gridFieldsCache[$moduleName];
			}

			if (!$fieldsGrid = FieldGridManager::getInstance ($adb)->fetchAvailableFieldsGrid ($moduleName)) {
				// Guardar null en caché para evitar consultas repetidas
				self::$gridFieldsCache[$moduleName] = null;
				return null;
			};

			foreach ($fieldsGrid as $field) {
				$dummy = explode ('_', $field->getName ());
				if (count ($dummy) > 1) {
					array_pop ($dummy);
					$fieldName = join ('_', $dummy);
				} else {
					$fieldName = $field->getName ();
				}
				$fields [ $field->getFieldName () ][] = array (
					'subfieldsid' => $field->getSubFieldId (),
					'fieldid'     => $field->getFieldId (),
					'gridlabel'   => $field->getLabel (),
					'label'       => $field->getFieldLabel (),
					'name'        => $fieldName,
					'sequence'    => $field->getSequence (),
				);
			}
			// Guardar resultado en caché
			self::$gridFieldsCache[$moduleName] = $fields;
			return $fields;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $gridName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getGridFields (PearDatabase $adb, $moduleName, $gridName) {
			if (!$fieldsGrid = FieldGridManager::getInstance ($adb)->fetchFieldGrid ($moduleName, $gridName)) {
				return null;
			};

			foreach ($fieldsGrid as $field) {
				$dummy = explode ('_',$field->getName ());
				array_pop ($dummy);
				$fieldName             = join ('_', $dummy);
				$fields [ $fieldName ] = array (
					'subfieldsid'  => $field->getSubFieldId (),
					'fieldid'      => $field->getFieldId (),
					'name'         => $field->getName (),
					'label'        => $field->getLabel (),
					'sequence'     => $field->getSequence (),
					'uitype'       => $field->getUiType (),
					'length'       => $field->getLength (),
					'precision'    => $field->getPrecision (),
					'defaultvalue' => $field->getDefaultValue (),
					'values'       => $field->getValues (),
					'action_field' => $field->getActionField (),
					'filter_field' => $field->getFilterField (),
					'relmodule'    => $field->getModuleReferences (),
					'data_field'   => $field->getDataField (),
					'locked'       => $field->isLocked (),
					'gridlabel'    => $field->getFieldLabel (),
					'gridname'     => $field->getFieldName (),
				);
			}
			return $fields;
		}

		/** getGridValues
		 * Obtiene los valores de los subcampos de un grid para un registro específico y módulo.
		 *
		 * Este método recupera los valores almacenados de los subcampos de un grid para un registro dado, devolviendo un arreglo indexado por fila y campo.
		 * Si $summaryRow es true, también incluye los valores summary (totales) asociados a la fila summary del grid.
		 *
		 * @param PearDatabase $adb      Conexión a la base de datos
		 * @param string       $moduleName Nombre del módulo
		 * @param string       $gridName   Nombre del grid
		 * @param integer      $entityId   ID del registro principal
		 * @param boolean      $summaryRow (opcional) Si es true, incluye los valores summary de la fila summary
		 *
		 * @return array|null  Arreglo indexado por fila y campo con los valores del grid, o null si no hay datos
		 * @throws Exception   Si ocurre un error al recuperar los valores
		 */
		public static function getGridValues (PearDatabase $adb, $moduleName, $gridName, $entityId, $summaryRow = false) {
			if (!$fieldGridValues = FieldGridManager::getInstance($adb)->fetchFieldGridValues ($moduleName, $gridName, $entityId, $summaryRow)) {
				return null;
			}
			$values       = array ();
			$summaryValues = array ();
			foreach ($fieldGridValues as $value) {
				if (empty ($value->getGridFieldArrayValue ())) {
					continue;
				}
				$dummy = explode ('_', $value->getFieldName ());
				array_pop ($dummy);
				$gridFieldName = join ('_', $dummy);

				if (($summaryRow) && ($gridFieldName == 'summary')) {
					$summaryValues ['values']    = $value->getGridFieldArrayValue ();
					$summaryValues ['summaryId'] = $value->getSubFieldId ();
					continue;
				}
				foreach ($value->getGridFieldArrayValue () as $index => $row) {
					// Solo agregar si el valor no está vacío, no es 0.00 y no es null
					if (!empty($row) && $row !== '0.00' && $row !== '0' && trim($row) !== '') {
						$values [ $index ][ $gridFieldName ] = $row;
					}
				}
			}

			if ($summaryRow && count ($summaryValues)) {
				self::getRowSummary ($adb, $summaryValues, $values);
			}
			return $values;
		}

		/**
		 * Guarda y actualiza los valores de todos los grids definidos para un módulo y registro específico.
		 *
		 * Este método recorre todos los campos grid disponibles del módulo indicado, delegando la actualización de cada uno
		 * al método updateFieldGridValues. Si el modo es 'edit', sincroniza campos relacionados mediante updateRelatedImport;
		 * si es creación, actualiza la relación usando updateIdToCreateRelated.
		 *
		 * @param PearDatabase $adb     Conexión a la base de datos
		 * @param array        $arguments Argumentos con la estructura:
		 *                                - module: nombre del módulo
		 *                                - requestData: datos recibidos (incluye modo y valores)
		 *                                - recordId: ID del registro principal
		 *                                - upLoadBadext: extensiones prohibidas para adjuntos
		 *                                - userId: ID del usuario actual
		 *
		 * @return boolean    True si algún grid fue actualizado, false si no hubo cambios
		 * @throws Exception  Si ocurre un error durante la actualización de los grids
		 */
		public static function saveFieldGrid ($adb, $arguments) {
			$gridFields = self::getAvailableGridFields ($adb, $arguments ['module']);
			if (empty ($gridFields)) {
				return false;
			} else if (!$listFieldsGrid = array_keys ($gridFields)) {
				return false;
			}
			$gridStatus = false;
			foreach ($listFieldsGrid as $gridName) {
				$updateGrid = self::updateFieldGridValues ($adb, $gridName, $arguments);
				if ($updateGrid) {
					$gridStatus = true;
				}
			}
			if ($arguments ['requestData'] ['mode'] == 'edit') {
				self::updateRelatedImport ($adb, $arguments);
			} else {
				self::updateIdToCreateRelated ($adb, $arguments);
			}

			return $gridStatus;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $gridName
		 * @param integer $entityId
		 * @param array $values
		 *
		 * @throws Exception
		 */
		public static function setGridValues (PearDatabase $adb, $moduleName, $gridName, $entityId, $values) {
			if ((empty ($moduleName)) || (empty ($gridName)) || (empty ($entityId))) {
				return;
			}

			$gridFields = self::getGridFields ($adb, $moduleName, $gridName);
			if (empty ($gridFields)) {
				return;
			}

			$nonShittyFieldNames = array_keys ($gridFields);
			foreach ($nonShittyFieldNames as $nonShittyFieldName) {
				$shittyFieldName = isset ($gridFields [ $nonShittyFieldName ]) ? $gridFields [ $nonShittyFieldName ]['name'] : null;
				if (empty ($shittyFieldName)) {
					continue;
				}

				$fieldValues = self::getGridFieldValues ($values, $nonShittyFieldName);
				$adb->pquery (
					'DELETE
						sfv
					FROM
						vtiger_subfields_values sfv
						INNER JOIN vtiger_subfields_special sfs ON sfs.subfieldsid=sfv.subfieldsid AND sfs.name=?
						INNER JOIN vtiger_field f ON f.fieldid=sfs.fieldid AND f.fieldname=? AND f.uitype=?
						INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
					WHERE
						sfv.modulecfid=?',
					array ($shittyFieldName, $gridName, FieldInterface::UI_TYPE_GRID, $moduleName, $entityId)
				);
				if (!empty ($fieldValues)) {
					$adb->pquery (
						'INSERT INTO vtiger_subfields_values (modulecfid, subfieldsid, field_values)
 						SELECT
 							?,
 							sfs.subfieldsid,
 							?
						FROM
							vtiger_subfields_special sfs
							INNER JOIN vtiger_field f ON f.fieldid=sfs.fieldid AND f.fieldname=? AND f.uitype=?
							INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
						WHERE
							sfs.name=?',
						array ($entityId, base64_encode (serialize ($fieldValues)), $gridName, FieldInterface::UI_TYPE_GRID, $moduleName, $shittyFieldName)
					);
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $gridName
		 * @param array $arguments
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public static function updateFieldGridValues ($adb, $gridName, $arguments) {
			$dataFields   = $arguments ['requestData'];
			$module       = $arguments ['module'];
			$recordid     = $arguments ['recordId'];
			$uploadBadext = $arguments ['upLoadBadext'];
			$userId       = $arguments ['userId'];
			$fgm          = FieldGridManager::getInstance ($adb);
			$fieldsGrid   = $fgm->fetchFieldGrid ($module, $gridName);
			$gridStatus   = false;

			foreach ($fieldsGrid as $field) {
				if (!in_array ($field->getName (), array_keys ($dataFields))) {
					continue;
				}
				$gridValue = $fgm->fetchFieldGridValuesByEntityId($recordid, $field->getSubFieldId ());

				if (($field->getSubFieldId ()) && ($field->getName () != 'Acciones')) {
					$dataField = vtlib_purify ($dataFields [ $field->getName () ]);
					if ((is_array ($dataField)) && ($field->getUiType () != FieldInterface::UI_TYPE_SUMMARY_ROW)) {
						array_shift ($dataField);
					}
					if ($field->getUiType () == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
						$postDelimiter = strpos ($field->getModuleReferences (), '@');
						if ($postDelimiter !== false) {
							if (is_array ($dataField)) {
								array_shift ($dataField);
								array_pop ($dataField);
							}
							self::organizeRelatedList ($dataField);
						}
					} else if ($field->getUiType () == FieldInterface::UI_TYPE_ATTACHMENTS) {
						array_shift ($dataField);
						$dataFieldToUpload = array ();
						$dataFieldToSave   = array ();
						$totalAttachment   = count ($_REQUEST [ $field->getName () ]);
						$registredDocs     = AttachmentsUtils::getAttachmentsNames ($adb, $recordid, $field->getSubFieldId ());
						for ($k = 0; $k < $totalAttachment; $k += 2) {
							$uploadDocs = true;
							list($tableRow, $fieleName) = explode ('@', $_REQUEST [ $field->getName () ][ ($k + 1) ]['filename']);
							$dataFieldToSave [] = array (
								'data'     => $_REQUEST [ $field->getName () ][ $k ]['data'],
								'filename' => $fieleName,
								'tableRow' => $tableRow,
							);
							if (is_array ($registredDocs) && in_array ($fieleName, $registredDocs)) {
								$uploadDocs = false;
							}

							if ($uploadDocs) {
								$dataFieldToUpload[] = array (
									'data'     => $_REQUEST [ $field->getName () ][ $k ]['data'],
									'filename' => $fieleName,
									'tableRow' => $tableRow,
									'isGrid'   => true,
								);
							}
						}

						AttachmentsUtils::saveAttachments ($adb, $recordid, $field->getModuleReferences (), $field->getSubFieldId (), $userId, $dataFieldToUpload, $uploadBadext);
						unset($dataField);
						$dataField = $dataFieldToSave;
					} else if ($field->getUiType () == FieldInterface::UI_TYPE_DATE) {
						// Campo fecha - convertir del formato del usuario al formato BD
						if (is_array($dataField)) {
							foreach ($dataField as $idx => $dateVal) {
								if (!empty($dateVal) && $dateVal !== '0000-00-00') {
									$dataField[$idx] = DateTimeField::convertToDBFormat($dateVal);
								}
							}
						}
					} else if (in_array($field->getUiType(), array(FieldInterface::UI_TYPE_NUMBER, FieldInterface::UI_TYPE_PERCENTAGE, FieldInterface::UI_TYPE_CURRENCY, 72, FieldInterface::UI_TYPE_CALCULATED))) {
						// Campos numéricos (incluyendo calculados) - convertir del formato del usuario al formato BD
						global $current_user;
						if (is_array($dataField)) {
							$numberHelper = NumberHelper::getInstance($adb, $current_user);
							foreach ($dataField as $idx => $numVal) {
								// Permitir el valor 0 (empty() retorna true para "0")
								if ($numVal !== null && $numVal !== '') {
									$dataField[$idx] = $numberHelper->setSaveNumberFormat($numVal);
								}
							}
						}
					}

					if (
						!empty (array_diff ($dataField, $gridValue->getGridFieldArrayValue ())) ||
						!empty (array_diff ($gridValue->getGridFieldArrayValue (), $dataField)) ||
						(count ($dataField) != count ($gridValue->getGridFieldArrayValue ()))
					) {
						$gridFieldValues [] = FieldGridValues::getInstance()
							->setModulecfId ($recordid)
							->setSubFieldId (intval($field->getSubFieldId()))
							->setGridFieldValue ($dataField);
					}
				}
			}

			if (isset ($gridFieldValues) && !empty($gridFieldValues)) {
				$gridStatus = true;
				try {
					$fgm->saveFieldGridValues ($gridFieldValues);
					// Actualizar tabla vtiger_grid_summary_* si existe, o crearla si tiene configuración summary
					// Esto mantiene coherencia entre los valores del grid y los totales optimizados
					try {
						// Obtener el fieldid del grid
						$gridFieldQuery = "SELECT f.fieldid 
						                   FROM vtiger_field f 
						                   INNER JOIN vtiger_tab t ON t.tabid = f.tabid 
						                   WHERE f.fieldname = ? AND t.name = ? AND f.uitype = 2202";
						$gridFieldResult = $adb->pquery($gridFieldQuery, array($gridName, $module));
						
						if ($adb->num_rows($gridFieldResult) > 0) {
							$fieldId = $adb->query_result($gridFieldResult, 0, 'fieldid');
							$summaryTableName = 'vtiger_grid_summary_' . $gridName;
							
							// Verificar si el grid tiene configuración de fila summary (uitype 2203)
							$hasSummaryQuery = "SELECT COUNT(*) as count FROM vtiger_subfields_special 
							                    WHERE fieldid = ? AND uitype = 2203";
							$hasSummaryResult = $adb->pquery($hasSummaryQuery, array($fieldId));
							$hasSummaryRow = ($adb->query_result($hasSummaryResult, 0, 'count') > 0);
							
							if ($hasSummaryRow) {
								// Verificar si existe la tabla summary
								$tableExistsQuery = "SHOW TABLES LIKE ?";
								$tableExistsResult = $adb->pquery($tableExistsQuery, array($summaryTableName));
								$tableExists = ($adb->num_rows($tableExistsResult) > 0);
								
								// Si no existe la tabla, crearla
								if (!$tableExists) {
									$summaryColumnNames = self::createTempGridValues($adb, $module, $gridName, $summaryTableName, false);
								} else {
									// Si ya existe, obtener sus columnas
									$summaryColumnNames = self::getSummaryGridFields($adb, $module, $gridName);
								}
								
								if (!empty($summaryColumnNames)) {
									// Obtener valores actuales del grid
									$gridValues = self::getGridValues($adb, $module, $gridName, $recordid, false);
									
									// Actualizar/Insertar en tabla summary
									self::updateGridSummaryTable(
										$adb, 
										$summaryTableName, 
										$summaryColumnNames, 
										$module, 
										$gridName, 
										$recordid, 
										$gridValues
									);
								}
							}
						}
					} catch (Exception $summaryException) {
						// Si falla la actualización de la tabla summary, solo registrar el error
						// No interrumpir el guardado principal del grid
						error_log("[GridFieldUtils::updateFieldGridValues] Error actualizando tabla summary: " . $summaryException->getMessage());
					}
					
				} catch (Exception $e) {
					$_SESSION ['flashmessage'] = array (
						'iserror' => true,
						'message' => $e->getMessage (),
						'data'    => null,
					);
				}
			}
			return $gridStatus;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 */
		public static function setRelatedImport ($adb, $arguments) {
			$result = $adb->pquery (
				'SELECT 
						gridrelatedgridid 
					  FROM 
					  	vtiger_grid_related_grid 
					  WHERE 
					  	fieldsto=? AND 
					  	fieldsfrom=? AND
					  	tomodule=? AND
					  	toid=? AND 
					  	rowindex=?',
				array ($arguments ['fieldsto'], $arguments ['fieldsfrom'], $arguments ['tomodule'], $arguments ['toid'], $arguments ['rowindex'])
			);
			if ($adb->num_rows ($result)) {
				$row = $adb->fetch_array($result);
				$adb->query("UPDATE vtiger_grid_related_grid SET fromid={$arguments ['fromid']}, codmodule='{$arguments ['codmodule']}' WHERE gridrelatedgridid={$row ['gridrelatedgridid']}");
			} else {
				$adb->run_insert_data('vtiger_grid_related_grid', $arguments);
			}
		}
		/**
		 * upsertGridValuesAndGetTotal
		 * 
		 * Verifica, inserta o actualiza los valores de subcampos grid en vtiger_subfields_values para un registro específico,
		 * garantizando la integridad y sincronización de los totales summary asociados.
		 *
		 * Este método asegura que para el registro ($entityId) y subcampo grid ($subfieldsid),
		 * exista un registro actualizado en vtiger_subfields_values. Si no existe, lo inserta;
		 * si existe pero está desactualizado (total diferente), lo actualiza. Devuelve siempre el valor summary final.
		 *
		 * Uso típico: procesos masivos como fillTemporaryTable, sincronización global de grids,
		 * o validaciones puntuales tras edición o migración de datos.
		 *
		 * @param PearDatabase $adb Conexión activa a la base de datos
		 * @param string $moduleName Nombre del módulo CRM (ej: 'Accounts')
		 * @param string $gridFieldName Nombre del campo grid
		 * @param int $entityId ID del registro principal (crmid)
		 * @param array $values Array de valores actuales del grid para el registro
		 * @param int $subfieldsid ID del subcampo grid (summary) a sincronizar
		 * @return float Valor summary (total) actualizado para el registro y subcampo
		 *
		 * @throws Exception Si ocurre un error de base de datos
		 */
		public static function upsertGridValuesAndGetTotal($adb, $moduleName, $gridFieldName, $entityId, $values, $subfieldsid) {
			// 1. Salida rápida si no existen registros en vtiger_subfields_values para este entityId y subfieldsid
			// (esto es un chequeo inicial, pero la función luego puede insertar si hace falta)
			$resVal = $adb->pquery("SELECT field_values FROM vtiger_subfields_values WHERE subfieldsid = ? AND modulecfid = ?", array($subfieldsid, $entityId));
			if (!$resVal || $adb->num_rows($resVal) == 0) {
				if ($resVal) { $resVal->free(); unset($resVal); }
				return 0.00;
			}
			if ($resVal) { $resVal->free(); unset($resVal); }

			// 2. Obtener metadata del subcampo para asegurar que los valores calculados estén actualizados
			$fieldId = null;
			$meta = self::getAvailableGridFields($adb, $moduleName);
			if ($meta && is_array($meta)) {
				foreach ($meta as $gridSubfields) {
					foreach ($gridSubfields as $subfield) {
						if (isset($subfield['subfieldsid']) && $subfield['subfieldsid'] == $subfieldsid) {
							$fieldId = $subfield['fieldid'];
							break 2;
						}
					}
				}
			}

			// 3. Asegura que los valores calculados del grid estén actualizados (por si hay columnas calculadas)
			$values = self::ensureCalculatedColumnValuesUpdated($adb, $moduleName, $gridFieldName, $entityId, $values, $subfieldsid, $fieldId);

			// 4. Validación básica de parámetros
			if (empty($moduleName) || empty($gridFieldName) || empty($entityId) || empty($subfieldsid)) {
				return 0.00;
			}

			// 5. Obtener y formatear los valores actuales del subcampo en el grid
			$fieldValues = self::getGridFieldValues($values, $gridFieldName);
			if (is_array($fieldValues)) {
				foreach ($fieldValues as $k => $v) {
					if (is_numeric($v)) {
						$fieldValues[$k] = number_format((float)$v, 2, '.', '');
					}
				}
			}
			$encodedValues = base64_encode(serialize($fieldValues));

			// 6. Calcular el total (summary) sumando todos los valores del subcampo
			$valorReal = 0.00;
			if (is_array($fieldValues)) {
				foreach ($fieldValues as $val) {
					$valorReal += floatval($val);
				}
				$valorReal = round($valorReal, 2);
			}

			// 7. Verificar si ya existe el registro en la tabla de totales
			$resVal = $adb->pquery("SELECT field_values FROM vtiger_subfields_values WHERE subfieldsid = ? AND modulecfid = ?", array($subfieldsid, $entityId));
			$needsInsert = false;
			if (!$resVal || $adb->num_rows($resVal) == 0) {
				// Si no existe, marcar para insertar
				$needsInsert = true;
			} else {
				// Si existe, comparar el total actual con el almacenado
				$currentValue = $adb->query_result($resVal, 0, 'field_values');
				$decoded = unserialize(base64_decode($currentValue));
				$currentTotal = 0.00;
				if (is_array($decoded)) {
					foreach ($decoded as $val) {
						$currentTotal += floatval($val);
					}
					$currentTotal = round($currentTotal, 2);
				}
				// Si el total almacenado es diferente al calculado, actualizar el registro
				if (abs($currentTotal - $valorReal) > 0.001) {
					$adb->pquery("UPDATE vtiger_subfields_values SET field_values=? WHERE subfieldsid=? AND modulecfid=?", array($encodedValues, $subfieldsid, $entityId));
				}
				if ($resVal) { $resVal->free(); unset($resVal); }
				return $valorReal;
			}

			// 8. Si no existía, insertar el registro
			if ($needsInsert) {
				$adb->pquery(
					'INSERT INTO vtiger_subfields_values (modulecfid, subfieldsid, field_values) VALUES (?, ?, ?)',
					array($entityId, $subfieldsid, $encodedValues)
				);
				if ($resVal) { $resVal->free(); unset($resVal); }
				return $valorReal;
			}
			if ($resVal) { $resVal->free(); unset($resVal); }
			return $valorReal;
		}

		/**
		 * Recalcula todos los valores de campos calculados y fila summary de un campo grid
		 * 
		 * @param PearDatabase $adb Conexión a la base de datos
		 * @param int $fieldId ID del campo grid
		 * @param int $batchSize Tamaño del lote para procesamiento (default: 100)
		 * @return array Resultado detallado del proceso
		 */
		public static function recalculateGridField($adb, $fieldId, $batchSize = 100) {
			$result = array(
				'success' => false,
				'processed' => 0,
				'errors' => 0,
				'skipped' => 0,
				'total' => 0,
				'messages' => array(),
				'start_time' => microtime(true),
				'field_info' => array(),
				'summary_updated' => 0
			);
			
			try {
				// 1. Validar que el campo existe y es tipo grid
				$fieldQuery = "SELECT f.fieldid, f.fieldname, f.fieldlabel, f.uitype, t.name as module
				               FROM vtiger_field f
				               INNER JOIN vtiger_tab t ON t.tabid = f.tabid
				               WHERE f.fieldid = ?";
				$fieldResult = $adb->pquery($fieldQuery, array($fieldId));
				
				if (!$fieldResult || $adb->num_rows($fieldResult) == 0) {
					throw new Exception("Campo no encontrado");
				}
				
				$fieldData = $adb->fetchByAssoc($fieldResult);
				
				if ($fieldData['uitype'] != 2202) {
					throw new Exception("El campo no es de tipo Grid (uitype 2202)");
				}
				
				$moduleName = $fieldData['module'];
				$gridName = $fieldData['fieldname'];
				
				$result['field_info'] = array(
					'fieldname' => $gridName,
					'fieldlabel' => $fieldData['fieldlabel'],
					'module' => $moduleName
				);
				
				// 2. Obtener metadata de campos calculados y summary
				$calculatedFields = self::getCalculatedDataField($adb, $fieldId);
				$summaryConfig = self::getSummaryRowConfig($adb, $fieldId);
				
				if (empty($calculatedFields) && empty($summaryConfig)) {
					$result['messages'][] = "No hay campos calculados ni fila summary para recalcular";
					$result['success'] = true;
					return $result;
				}
				
				// 3. Obtener información de la tabla summary y recrearla si existe
				$summaryTableName = null;
				$summaryColumnNames = null;
				if (!empty($summaryConfig)) {
					$summaryTableName = 'vtiger_grid_summary_' . $gridName;
					
					// Eliminar y recrear la tabla summary desde cero para asegurar estructura actualizada
					$result['messages'][] = "Recreando tabla summary {$summaryTableName}...";
					
					try {
						// Usar createTempGridValues con $isIntact = false para forzar DROP y recreación
						$summaryColumnNames = self::createTempGridValues($adb, $moduleName, $gridName, $summaryTableName, false);
						
						if ($summaryColumnNames) {
							$result['messages'][] = "Tabla summary recreada con " . count($summaryColumnNames) . " columnas";
						} else {
							$result['messages'][] = "No se encontraron columnas summary para recrear";
						}
					} catch (Exception $e) {
						$result['messages'][] = "Advertencia al recrear tabla summary: " . $e->getMessage();
						// Continuar con el proceso aunque falle la recreación
						$summaryColumnNames = self::getSummaryGridFields($adb, $moduleName, $gridName);
					}
				}
				
				// 4. Contar total de registros
				$countQuery = "SELECT COUNT(*) as total 
				               FROM vtiger_crmentity 
				               WHERE setype = ? AND deleted = 0";
				$countResult = $adb->pquery($countQuery, array($moduleName));
				$totalRecords = $adb->query_result($countResult, 0, 'total');
				
				$result['total'] = $totalRecords;
				
				if ($totalRecords == 0) {
					$result['messages'][] = "No hay registros para procesar";
					$result['success'] = true;
					return $result;
				}
				
				$result['messages'][] = "Iniciando recálculo de {$totalRecords} registros...";
				
				// 5. Procesar por lotes
				$offset = 0;
				$batchNumber = 0;
				
				while ($offset < $totalRecords) {
					$batchNumber++;
					
					$batchQuery = "SELECT crmid 
					              FROM vtiger_crmentity 
					              WHERE setype = ? AND deleted = 0 
					              LIMIT {$batchSize} OFFSET {$offset}";
					$batchResult = $adb->pquery($batchQuery, array($moduleName));
					
					if (!$batchResult || $adb->num_rows($batchResult) == 0) {
						break;
					}
					
					// Procesar cada registro del lote
					while ($row = $adb->fetchByAssoc($batchResult)) {
						$recordId = $row['crmid'];
						
						try {
							// Obtener valores del grid
							$gridValues = self::getGridValues($adb, $moduleName, $gridName, $recordId, false);
							
							if (empty($gridValues)) {
								$result['skipped']++;
								continue;
							}
							
							// IMPORTANTE: Guardar valores de campos numéricos (uitype 7) que pueden estar vacíos en vtiger_subfields_values
							// Estos campos se llenan por importación automática pero no se guardan durante el guardado normal
							$allSubfields = $adb->pquery(
								"SELECT subfieldsid, name, uitype FROM vtiger_subfields_special WHERE fieldid = ?",
								array($fieldId)
							);
							
							while ($subfieldRow = $adb->fetchByAssoc($allSubfields)) {
								$subfieldId = $subfieldRow['subfieldsid'];
								$subfieldName = $subfieldRow['name'];
								$subfieldUitype = $subfieldRow['uitype'];
								
								// Extraer el nombre del campo sin el sufijo del fieldid
								$dummy = explode('_', $subfieldName);
								array_pop($dummy);
								$cleanFieldName = join('_', $dummy);
								
								// Verificar si hay valores en el grid para este campo
								$fieldValuesArray = array();
								foreach ($gridValues as $rowIndex => $rowData) {
									if (isset($rowData[$cleanFieldName])) {
										$fieldValuesArray[$rowIndex] = $rowData[$cleanFieldName];
									}
								}
								
								// Si hay valores, guardarlos en vtiger_subfields_values
								if (!empty($fieldValuesArray)) {
									$serializedValues = base64_encode(serialize($fieldValuesArray));
									
									// Verificar si ya existe
									$checkQuery = "SELECT field_values FROM vtiger_subfields_values 
									               WHERE subfieldsid = ? AND modulecfid = ?";
									$checkResult = $adb->pquery($checkQuery, array($subfieldId, $recordId));
									
									if ($adb->num_rows($checkResult) > 0) {
										// UPDATE
										$updateQuery = "UPDATE vtiger_subfields_values 
										                SET field_values = ? 
										                WHERE subfieldsid = ? AND modulecfid = ?";
										$adb->pquery($updateQuery, array($serializedValues, $subfieldId, $recordId));
									} else {
										// INSERT
										$insertQuery = "INSERT INTO vtiger_subfields_values (modulecfid, subfieldsid, field_values) 
										                VALUES (?, ?, ?)";
										$adb->pquery($insertQuery, array($recordId, $subfieldId, $serializedValues));
									}
								}
							}
							
							// Recalcular campos calculados con INSERT/UPDATE
							if (!empty($calculatedFields)) {
								self::recalculateAndUpsertCalculatedFields($adb, $calculatedFields, $gridValues, $recordId);
							}
							
							// Recalcular fila summary con INSERT/UPDATE
							if (!empty($summaryConfig)) {
								self::recalculateAndUpsertSummaryRow($adb, $summaryConfig, $recordId);
							}
							
							// Actualizar tabla vtiger_grid_summary_* si existe
							if ($summaryTableName && $summaryColumnNames) {
								self::updateGridSummaryTable($adb, $summaryTableName, $summaryColumnNames, $moduleName, $gridName, $recordId, $gridValues);
								$result['summary_updated']++;
							}
							
							$result['processed']++;
							
						} catch (Exception $e) {
							$result['errors']++;
							$result['messages'][] = "Error en registro {$recordId}: " . $e->getMessage();
						}
					}
					
					// Liberar memoria
					if ($batchResult instanceof ADORecordSet) {
						$batchResult->Close();
					}
					unset($batchResult);
					
					$offset += $batchSize;
					
					// Log de progreso cada 5 lotes
					if ($batchNumber % 5 == 0) {
						$progress = min(100, round(($offset / $totalRecords) * 100, 1));
						$result['messages'][] = "Progreso: {$progress}% ({$offset}/{$totalRecords})";
					}
				}
				
				// 6. Resultado final
				$elapsed = round(microtime(true) - $result['start_time'], 2);
				$result['messages'][] = "Recalculo completado exitosamente";
				$result['messages'][] = "Registros procesados: {$result['processed']}";
				$result['messages'][] = "Registros omitidos: {$result['skipped']}";
				$result['messages'][] = "Total de registros: {$result['total']}";
				
				if ($result['summary_updated'] > 0) {
					$result['messages'][] = "Tabla summary actualizada: {$result['summary_updated']} registros";
				}
				
				$result['success'] = true;
				
			} catch (Exception $e) {
				$result['success'] = false;
				$result['messages'][] = "Error: " . $e->getMessage();
			}
			
			return $result;
		}
		
		/**
	 * Recalcula y hace UPSERT de campos calculados (INSERT si no existe, UPDATE si existe)
	 * 
	 * @param PearDatabase $adb
	 * @param array $calculatedData Metadata de campos calculados
	 * @param array $gridValues Valores actuales del grid
	 * @param int $recordId ID del registro
	 */
	private static function recalculateAndUpsertCalculatedFields($adb, $calculatedData, $gridValues, $recordId) {
		require_once('include/utils/FieldCalculate.php');
		
		foreach ($calculatedData as $calcField) {
			$subfieldsid = $calcField['subfieldsid'];
			$equation = $calcField['equation'];
			$fields = $calcField['fields'];
			
			// Verificar si ya existe el registro para este campo calculado
			$checkQuery = "SELECT field_values FROM vtiger_subfields_values 
			               WHERE subfieldsid = ? AND modulecfid = ?";
			$checkResult = $adb->pquery($checkQuery, array($subfieldsid, $recordId));
			
			$existingValues = array();
			$recordExists = ($adb->num_rows($checkResult) > 0);
			
			if ($recordExists) {
				$existingValues = unserialize(base64_decode($adb->query_result($checkResult, 0, 'field_values')));
				if (!is_array($existingValues)) {
					$existingValues = array();
				}
			}
			
			// Recalcular cada fila del grid
			foreach ($gridValues as $rowIndex => $rowData) {
				$currentEquation = $equation;

				// Sustituir cada variable en la ecuación con su valor real
				if (!empty($fields) && is_array($fields)) {
					foreach ($fields as $fieldName => $fieldId) {
						$pattern = "/\b{$fieldName}\b/";
						if (!preg_match($pattern, $currentEquation)) {
							continue;
						}
						
						// Obtener el valor del campo desde vtiger_subfields_values
						$fieldQuery = "SELECT field_values FROM vtiger_subfields_values 
						               WHERE subfieldsid = ? AND modulecfid = ?";
						$fieldResult = $adb->pquery($fieldQuery, array($fieldId, $recordId));
						
						$fieldValue = 0;
						if ($adb->num_rows($fieldResult) > 0) {
							$fieldValues = unserialize(base64_decode($adb->query_result($fieldResult, 0, 'field_values')));
							if (is_array($fieldValues) && isset($fieldValues[$rowIndex])) {
								$fieldValue = $fieldValues[$rowIndex];
								if (!is_numeric($fieldValue)) {
									$fieldValue = 0;
								}
							}
						}
						
						$currentEquation = preg_replace($pattern, $fieldValue, $currentEquation);
					}
				}
				
				// Limpiar paréntesis si no hay operadores múltiples
				preg_match_all('/[\+\-\*\/]/', $currentEquation, $matches);
				if (count($matches[0]) < 2) {
					$currentEquation = str_replace(array('(', ')'), '', $currentEquation);
				}
				
				// Calcular el resultado
				try {
					$calculatedValue = FieldCalculate::calculate($currentEquation);
					if (!is_numeric($calculatedValue)) {
						$calculatedValue = 0;
					}
				} catch (Exception $e) {
					$calculatedValue = 0;
				}
				
				$existingValues[$rowIndex] = $calculatedValue;
			}
			
			// Serializar los valores
			$serializedValues = base64_encode(serialize($existingValues));
			
			// UPSERT
			if ($recordExists) {
				$updateQuery = "UPDATE vtiger_subfields_values 
				                SET field_values = ? 
				                WHERE subfieldsid = ? AND modulecfid = ?";
				$adb->pquery($updateQuery, array($serializedValues, $subfieldsid, $recordId));
			} else {
				$insertQuery = "INSERT INTO vtiger_subfields_values (modulecfid, subfieldsid, field_values) 
				                VALUES (?, ?, ?)";
				$adb->pquery($insertQuery, array($recordId, $subfieldsid, $serializedValues));
			}
		}
	}
	
	/**
	 * Obtiene la configuración de la fila summary sin requerir un modulecfId
	 * 
	 * @param PearDatabase $adb
	 * @param int $gridFieldId ID del campo grid
	 * @return array|null Array con 'subfieldsid' y 'summaryData' o null si no existe
	 */
	private static function getSummaryRowConfig($adb, $gridFieldId) {
		$result = $adb->pquery(
			"SELECT subfieldsid, data_field 
			 FROM vtiger_subfields_special 
			 WHERE uitype = 2203 AND fieldid = ?",
			array($gridFieldId)
		);
		
		if ($adb->num_rows($result) > 0) {
			$row = $adb->fetch_array($result);
			return array(
				'subfieldsid' => $row['subfieldsid'],
				'summaryData' => unserialize(base64_decode($row['data_field']))
			);
		}
		
		return null;
	}
	
	/**
	 * Recalcula y hace UPSERT de la fila summary (INSERT si no existe, UPDATE si existe)
	 * 
	 * @param PearDatabase $adb
	 * @param array $summaryRowData Configuración de la fila summary
	 * @param int $recordId ID del registro
	 */
	private static function recalculateAndUpsertSummaryRow($adb, $summaryRowData, $recordId) {
		$summaryRowValues = array();
		
		foreach ($summaryRowData['summaryData'] as $rowCell) {
			if (($rowCell['field'] != 'false') && ($rowCell['action'] == 'sum')) {
				// Obtener valores del campo para sumar
				$fieldQuery = "SELECT sfv.field_values 
				               FROM vtiger_subfields_values sfv 
				               INNER JOIN vtiger_subfields_special ss ON ss.subfieldsid = sfv.subfieldsid
				               WHERE ss.name = ? AND sfv.modulecfid = ?";
				$fieldResult = $adb->pquery($fieldQuery, array($rowCell['field'], $recordId));
				
				if ($adb->num_rows($fieldResult) > 0) {
					$fieldValues = unserialize(base64_decode($adb->query_result($fieldResult, 0, 'field_values')));
					$sum = array_sum($fieldValues);
					$summaryRowValues[] = $sum;
				} else {
					$summaryRowValues[] = 0;
				}
			} else {
				$summaryRowValues[] = 0;
			}
		}
		
		if (count($summaryRowValues) > 0) {
			$serializedValues = base64_encode(serialize($summaryRowValues));
			$subfieldsid = $summaryRowData['subfieldsid'];

			$updateQuery = "UPDATE vtiger_subfields_values 
			                SET field_values = ? 
			                WHERE subfieldsid = ? AND modulecfid = ?";
			$adb->pquery($updateQuery, array($serializedValues, $subfieldsid, $recordId));
			
			if ($adb->getAffectedRowCount() == 0) {
				// No existía, hacer INSERT
				$insertQuery = "INSERT INTO vtiger_subfields_values (modulecfid, subfieldsid, field_values) 
				                VALUES (?, ?, ?)";
				$adb->pquery($insertQuery, array($recordId, $subfieldsid, $serializedValues));
			} else {
			}
		}
	}
		
		/**
		 * Actualiza la tabla vtiger_grid_summary_* con los valores recalculados
		 * 
		 * @param PearDatabase $adb
		 * @param string $tableName Nombre de la tabla summary
		 * @param array $columnNames Nombres de las columnas summary
		 * @param string $moduleName Nombre del módulo
		 * @param string $gridName Nombre del grid
		 * @param int $recordId ID del registro
		 * @param array $gridValues Valores del grid
		 */
		private static function updateGridSummaryTable($adb, $tableName, $columnNames, $moduleName, $gridName, $recordId, $gridValues) {
			if (empty($columnNames)) {
				return;
			}
			
			// Verificar si la tabla existe
			$tableExistsQuery = "SHOW TABLES LIKE ?";
			$tableExistsResult = $adb->pquery($tableExistsQuery, array($tableName));
			
			if ($adb->num_rows($tableExistsResult) == 0) {
				return;
			}
			
			$data = array('recordid' => intval($recordId));
		
		foreach ($columnNames as $columnName => $fieldName) {
			// Obtener subfieldsid - buscar en TODOS los uitypes, no solo 2204
			// Esto incluye campos numéricos (uitype 7) que también necesitan totales
			$subfieldQuery = "SELECT subfieldsid, uitype FROM vtiger_subfields_special 
			                  WHERE name = ? LIMIT 1";
			$subfieldResult = $adb->pquery($subfieldQuery, array($columnName));
			
			if ($adb->num_rows($subfieldResult) == 0) {
				continue;
			}
			
			$subfieldsid = $adb->query_result($subfieldResult, 0, 'subfieldsid');
			$uitype = $adb->query_result($subfieldResult, 0, 'uitype');
			
			// Leer valores directamente desde vtiger_subfields_values
			// NO usar getGridFieldValues porque filtra valores vacíos/cero que son válidos
			$valuesQuery = "SELECT field_values FROM vtiger_subfields_values 
			                WHERE subfieldsid = ? AND modulecfid = ?";
			$valuesResult = $adb->pquery($valuesQuery, array($subfieldsid, $recordId));
			
			$valorReal = 0.00;
			if ($adb->num_rows($valuesResult) > 0) {
				$encodedValues = $adb->query_result($valuesResult, 0, 'field_values');
				$fieldValues = unserialize(base64_decode($encodedValues));
				
				if (is_array($fieldValues)) {
					foreach ($fieldValues as $val) {
						// Permitir valores vacíos, NULL y cero - son válidos para campos numéricos
						if ($val !== null && $val !== '') {
							$valorReal += floatval($val);
						}
					}
					$valorReal = round($valorReal, 2);
				}
			}
			
			$data[$columnName] = $valorReal;
		}
		
			// Verificar si ya existe el registro en la tabla summary
			$checkQuery = "SELECT recordid FROM {$tableName} WHERE recordid = ?";
			$checkResult = $adb->pquery($checkQuery, array($recordId));
			
			try {
			if ($adb->num_rows($checkResult) > 0) {
				// UPDATE
				$updateParts = array();
				$updateParams = array();
				foreach ($data as $column => $value) {
					if ($column != 'recordid') {
						$updateParts[] = "{$column} = ?";
						$updateParams[] = $value;
					}
				}
				$updateParams[] = $recordId;
				
				if (!empty($updateParts)) {
					$updateQuery = "UPDATE {$tableName} SET " . implode(', ', $updateParts) . " WHERE recordid = ?";
					$adb->pquery($updateQuery, $updateParams);
				}
			} else {
				// INSERT
				$columns = array_keys($data);
				$placeholders = array_fill(0, count($data), '?');
				$insertQuery = "INSERT INTO {$tableName} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
				$adb->pquery($insertQuery, array_values($data));
			}
			} catch (Exception $e) {
				// Si falla la actualización de la tabla summary, registrar el error pero continuar
				error_log("[GridFieldUtils.class.php::updateGridSummaryTable] Error updating table {$tableName}: " . $e->getMessage());
				error_log("[GridFieldUtils.class.php::updateGridSummaryTable] This is not critical, summary values are stored in vtiger_subfields_values");
			}
		}

} 