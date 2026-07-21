<?php
/**
 * Fix para gráficos apilados con múltiples series
 * Este archivo modifica la generación de datos para soportar gráficos apilados correctamente
 */

class StackedGraphFix {
	
	/**
	 * Genera datos para gráfico apilado con estructura PIVOT
	 * Versión genérica que funciona con cualquier módulo
	 * 
	 * @param PearDatabase $adb
	 * @param array $graphData - Datos del gráfico desde vtiger_graficos
	 * @param array $dateFilter - Filtro de fechas
	 * @return array - Datos en formato Google Charts
	 */
	public static function getStackedGraphData($adb, $graphData, $dateFilter) {
		// Decodificar configuración
		$modules = json_decode(str_replace('&quot;', '"', $graphData['fld_module']));
		$fields = json_decode(str_replace('&quot;', '"', $graphData['fieldoperation']));
		$groupingField = $graphData['fieldgrouping'];
		
		// Verificar que tengamos exactamente 2 campos
		if (count($fields) != 2) {
			return null;
		}
		
		// Obtener el nombre del módulo
		$moduleName = is_array($modules) ? $modules[0] : $modules;
		
		// Obtener información de la tabla principal del módulo
		$entityResult = $adb->pquery(
			"SELECT tablename, entityidfield FROM vtiger_entityname WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)",
			array($moduleName)
		);
		
		if ($adb->num_rows($entityResult) == 0) {
			return null;
		}
		
		$entityData = $adb->fetchByAssoc($entityResult);
		$mainTable = $entityData['tablename'];
		$entityIdField = $entityData['entityidfield'];
		
		// Extraer información de los campos
		$field1Parts = explode('.', $fields[0]);
		$field2Parts = explode('.', $fields[1]);
		$groupingParts = explode('.', $groupingField);
		
		$table1 = $field1Parts[0];
		$column1 = $field1Parts[1];
		$table2 = $field2Parts[0];
		$column2 = $field2Parts[1];
		$groupingTable = $groupingParts[0];
		$groupingColumn = $groupingParts[1];
		
		// Determinar qué campo es el eje X y cuál es para series
		$xAxisField = $groupingColumn;
		$xAxisTable = $groupingTable;
		$seriesField = ($groupingColumn == $column1) ? $column2 : $column1;
		$seriesTable = ($groupingColumn == $column1) ? $table2 : $table1;
		
		// Construir JOINs dinámicamente
		$joins = "INNER JOIN {$mainTable} ON {$mainTable}.{$entityIdField} = crm.crmid";
		
		// Agregar JOIN para tabla de eje X si es diferente de la principal
		if ($xAxisTable != $mainTable) {
			$joins .= " LEFT JOIN {$xAxisTable} ON {$xAxisTable}.{$entityIdField} = crm.crmid";
		}
		
		// Agregar JOIN para tabla de series si es diferente
		if ($seriesTable != $mainTable && $seriesTable != $xAxisTable) {
			$joins .= " LEFT JOIN {$seriesTable} ON {$seriesTable}.{$entityIdField} = crm.crmid";
		}
		
		// Determinar de qué tabla obtener cada campo
		$xAxisTableAlias = ($xAxisTable == $mainTable) ? $mainTable : $xAxisTable;
		$seriesTableAlias = ($seriesTable == $mainTable) ? $mainTable : $seriesTable;
		
		// Obtener valores únicos del campo de series
		$seriesQuery = "
			SELECT DISTINCT {$seriesTableAlias}.{$seriesField} AS series_value
			FROM vtiger_crmentity crm
			{$joins}
			WHERE crm.deleted = 0
			  AND {$seriesTableAlias}.{$seriesField} IS NOT NULL
			  AND {$seriesTableAlias}.{$seriesField} != ''
			ORDER BY series_value
		";
		
		$seriesResult = $adb->query($seriesQuery);
		$seriesValues = array();
		while ($row = $adb->fetchByAssoc($seriesResult)) {
			$seriesValues[] = $row['series_value'];
		}
		
		if (empty($seriesValues)) {
			return array();
		}
		
		// Construir consulta PIVOT
		$caseClauses = array();
		$seriesAliases = array(); // Mapeo de alias seguros a valores originales
		$aliasIndex = 0;
		foreach ($seriesValues as $seriesValue) {
			$escapedValue = $adb->sql_escape_string($seriesValue);
			$safeAlias = "series_" . $aliasIndex;
			$caseClauses[] = "SUM(CASE WHEN {$seriesTableAlias}.{$seriesField} = '{$escapedValue}' THEN 1 ELSE 0 END) AS {$safeAlias}";
			$seriesAliases[$safeAlias] = $seriesValue;
			$aliasIndex++;
		}
		
		$pivotQuery = "
			SELECT 
				{$xAxisTableAlias}.{$xAxisField} AS x_axis,
				" . implode(",\n\t\t\t\t", $caseClauses) . "
			FROM vtiger_crmentity crm
			{$joins}
			WHERE crm.deleted = 0
			  AND {$xAxisTableAlias}.{$xAxisField} IS NOT NULL
			  AND {$xAxisTableAlias}.{$xAxisField} != ''
			GROUP BY {$xAxisTableAlias}.{$xAxisField}
			ORDER BY x_axis
		";
		
		// Ejecutar consulta
		$result = $adb->query($pivotQuery);
		
		if ($adb->num_rows($result) == 0) {
			return array();
		}
		
		// Construir datos para Google Charts
		$data = array();
		
		// Primera fila: encabezados
		$headers = array($xAxisField);
		foreach ($seriesValues as $seriesValue) {
			$headers[] = html_entity_decode($seriesValue, ENT_QUOTES, 'UTF-8');
		}
		$data[] = $headers;
		
		// Filas de datos
		while ($row = $adb->fetchByAssoc($result)) {
			$dataRow = array(html_entity_decode($row['x_axis'], ENT_QUOTES, 'UTF-8'));
			foreach ($seriesAliases as $alias => $originalName) {
				$dataRow[] = (float)$row[$alias];
			}
			$data[] = $dataRow;
		}
		
		return $data;
	}
	
	/**
	 * Verifica si un gráfico debe usar el fix de apilamiento
	 * 
	 * @param array $graphData
	 * @return bool
	 */
	public static function shouldUseFix($graphData) {
		$options = json_decode(str_replace('&quot;', '"', $graphData['graphicoptions']), true);
		$fields = json_decode(str_replace('&quot;', '"', $graphData['fieldoperation']));
		
		// Usar fix si:
		// 1. Es un gráfico apilado
		// 2. Tiene exactamente 2 campos
		// 3. Tiene campo de agrupación
		return !empty($options['isStacked']) 
			&& $options['isStacked'] !== 'false'
			&& count($fields) == 2
			&& !empty($graphData['fieldgrouping']);
	}
}
