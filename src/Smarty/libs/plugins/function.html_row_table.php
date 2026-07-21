<?php
	/**
	 * Smarty plugin
	 *
	 * @package    Smarty
	 * @subpackage PluginsFunction
	 */
	
	/**
	 * Smarty {html_row_table} function plugin
	 * Type:     function
	 * Name:     html_row_table<br>
	 * Purpose:  Prints the table row  from
	 *           the passed parameters<br>
	 * Params:
	 * <pre>
	 * - fields      (required) - array of specific fields and properties to display
	 *             column type: linkToDetailView, description, showAvatar
	 * - row_data   (required) - array of data fronm sql query response
	 * - url_avatar (required) - url to avatar images
	 * </pre>
	 *
	 * @link      app.platzilla.com
	 * @author   ING. Wilfredo Araujo
	 *
	 * @param array $params parameters
	 *
	 * @return string
	 * @uses   smarty_function_escape_special_chars()
	 */
	function smarty_function_html_row_table ($params) {
	    require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
		$htmlOutput = "";
		$fields     = $params ['fields'];
		$urlAvatar  = $params ['url_avatar'];
		$listRows   = $params ['list_data'];
		if (!count ($params ['row_data'])) {
			$colspan = count ($fields);
			return '<td colspan="'.$colspan.'" style="text-align: center;color: red";>No hay datos para mostrar </td>';
		}
		
		foreach ($fields as $fieldLabel => $column) {
			$rowData = $params ['row_data'];
			if (!count ($column) || empty ($column[0])) {
				$htmlOutput .= "<td>&nbsp;</td>";
			} else if ($column[0] == 'linkToDetailView') {
				$tdData  = $column [1];
				$moduleData = ($tdData [4] == 'string') ? $tdData[3] : $rowData[$tdData[3]];
				if (!empty ($tdData[0])) {
					$rowData = $rowData[$tdData[0]];
				}
				$htmlOutput .= "<td style='vertical-align: top'>
				<a href='index.php?module={$moduleData}&action=DetailView&record={$rowData[$tdData[2]]}' target='_blank'>{$rowData[$tdData[1]]}</a>
				</td>";
			} else if ($column[0] == 'description') {
				$tdData  = $column [1];
				if (!empty ($tdData[0])) {
					$rowData = $rowData[$tdData[0]];
				}
				// Lógica especial para combined_condition con colores (multi-idioma)
				if ($tdData[1] == 'combined_condition') {
					// Incluir clase de configuración
					require_once('modules/Home/lib/SituacionConfig.class.php');
					
					$value = $rowData[$tdData[1]];
					$cleanValue = trim($value);
					
					// Si el valor es una clave de traducción (LBL_ON_TIME_* o PICK_ACTIVITY_*), traducirla
					if (strpos($cleanValue, 'LBL_') === 0 || strpos($cleanValue, 'PICK_ACTIVITY_') === 0) {
						global $current_language;
						// Usar el módulo Calendar para traducciones de combined_condition
						$translatedValue = getTranslatedString($cleanValue, 'Calendar');
						$displayValue = $translatedValue;
					} else {
						$displayValue = $cleanValue;
					}
					
					// Obtener color usando sistema de traducción
					$color = SituacionConfig::getColorByValue($cleanValue);
					
					// Aplicar estilo según color encontrado
					if (!empty($color)) {
						$style = "background-color: {$color}; color: #FFFFFF; padding: 4px 1px; border-radius: 4px; font-weight: 400; text-align:center;";
					} else {
						// Valor no reconocido, mostrar con estilo por defecto
						$style = "background-color: #ffffff; color: #000000; padding: 4px 1px; border-radius: 0px; font-weight: 400;text-align:center;";
					}
					
					$htmlOutput .= "<td style='vertical-align: top'><span style='{$style}'>" . trim($displayValue) . "</span></td>";
				} else {
					$htmlOutput .= "<td style='vertical-align: top'>{$rowData[$tdData[1]]}</td>";
				}
			} else if ($column[0] == 'showAvatar') {
				$tdData  = $column [1];
				if (!empty ($tdData[0])) {
					$rowData = $rowData[$tdData[0]];
				}
				$htmlOutput .= "<td style='vertical-align: top'>
				<figure class='center-block' style='border-radius: 50%; height: 40px; overflow: hidden; width: 40px;'>
					<img class='img-responsive img-circle' alt='{$rowData[$tdData[2]]}' title='{$rowData[$tdData[2]]}' src='{$urlAvatar}/{$rowData[$tdData[1]]}'>
				</figure>
				</td>";
			} else if ($column[0] == 'HelpOnRecord') {
				$tdData  = $column [1];
				if (!empty ($tdData[0])) {
					$rowData = $rowData[$tdData[0]];
				}
				if (!empty($rowData[$tdData[1]])) {
					$htmlOutput .= "<td style='vertical-align: top;text-align: center'>
				    <a class='btn btn-link'
				    data-width='950'
				    data-toggle='lightbox'
				    data-parent=''
				    data-gallery='remoteload'
				    data-title='¡Aprende como!'
				    href='index.php?module={$tdData[3]}&action=AjaxDetailViewUtils&record={$rowData[$tdData[1]]}&function=GET-HOW-TO&Ajax=true' title='¡Aprende como!'>
				    <i class='bi bi-question-square'></i></a></td>";
				} else {
					$htmlOutput .= "<td style='vertical-align: top;text-align: center'>&nbsp;</td>";
				}
			} else if ($column[0] == 'doReportAndFeedback') {
				$tdData  = $column [1];
				if (!empty ($tdData[0]) && $tdData[0] != 'module_name') {
					$rowData = $rowData[$tdData[0]];
				} else if (!empty ($tdData[0]) && $tdData[0] == 'module_name') {
					$moduleName = $rowData[$tdData[0]];
				} else {
					$moduleName = $tdData[3];
				}
				if (!empty ($rowData[$tdData[1]])) {
					$activityId = (!empty($rowData[$tdData[2]])) ? $rowData[$tdData[2]] : '';
					$htmlOutput .= "<td style='vertical-align: top;text-align: center'>
					<a data-width='950' data-toggle='lightbox' data-parent='' data-gallery='remoteload' data-title=''
					href='index.php?module=grid_view&amp;action=EditActivityReport&record={$rowData[$tdData[1]]}&formodule={$moduleName}&activityid={$activityId}&Ajax=true' title='Reportes y feedbacks'>
					<span class='icon icon-02-iconos-chat'></span>
					</a></td>";
				}  else {
					$htmlOutput .= "<td style='vertical-align: top;text-align: center'>&nbsp;&nbsp;</td>";
				}
			} else if (($column[0] == 'totalReport') || ($column[0] == 'totalFeedback')) {
				$tdData  = $column [1];
				if (!empty ($tdData[0])) {
					$rowData = $rowData[$tdData[0]];
				}
				if (!empty($rowData[$tdData[1]])) {
					$htmlOutput .= "<td style='vertical-align: top;text-align: center'>
					<a data-width='950' data-toggle='lightbox' data-parent='' data-gallery='remoteload' data-title='Reportes sobre actividad:'
					title='Reportes y feedbacks'
					href='index.php?module=grid_view&action=GridViewAjaxUtils&record={$rowData[$tdData[2]]}&formodule={$rowData[$tdData[3]]}&boxtype=REPORT_ACTIVITY&function=ITERATIONS&Ajax=true' >{$rowData[$tdData[1]]}
					</a></td>";
				} else {
					$htmlOutput .= "<td style='vertical-align: top;text-align: center'>&nbsp;0&nbsp;</td>";
				}
			} else if ($column[0] == 'showList') {
				$tdData      = $column [2];
				$workId 	= $rowData[$column[1]];
				if (isset ($listRows[$workId])) {
					$htmlOutput .= "<td style='vertical-align: top;text-align: left'><ul>";
					foreach ($listRows[$workId] as $row) {
						$subject    = (!empty($row[$tdData[2]])) ? $row[$tdData[2]] : '<i>Enunciado de la tarea</i>';
						$subjectExt = (!isset($tdData[3])) ? '' : '&nbsp;|&nbsp;' . $row[$tdData[3]];
						$htmlOutput .= "<li>
						<a href='index.php?module={$tdData[0]}&action=DetailView&record={$row[$tdData[1]]}' target='_blank'>
						{$subject}{$subjectExt}
						</a></li>";
					}
					$htmlOutput .= "</ul></td>";
				} else {
					$htmlOutput .= "<td style='vertical-align: top;text-align: center'>&nbsp;&nbsp;</td>";
				}
			} else if ($column[0] == 'linkToShowModal') {
				$tdData  = $column [1];
				if (!empty ($tdData[0])) {
					$rowData = $rowData[$tdData[0]];
				}
				
				if ($rowData[$tdData[4]] == $tdData[3]) {
					$htmlOutput .= "<td style='vertical-align: top;text-align: center'>
				    <a data-modal='modal-detail-row' class='md-trigger' data-target='#modal-detail-row' modal-title='{$rowData[$tdData[1]]}'
				    href='index.php?module={$tdData[3]}&parenttab=&action=DetailView&tab=detail&record={$rowData[$tdData[2]]}' title='Trabajos'>
				    <span style='display: none'></span><i class='fa fa-eye'></i>
				    </a></td>";
				} else {
					$htmlOutput .= "<td style='vertical-align: top;text-align: center'>&nbsp;&nbsp;</td>";
				}
			} else if ($column[0] == 'listOfFields') {
				$tdData  = $column [1];
				if (count ($tdData)) {
					$htmlOutput .= "<td style='vertical-align: top;text-align: left'>";
					foreach ($tdData as $field) {
						if (!isset ($rowData[$field])) {
							continue;
						}
						$htmlOutput .= "<div style='display: block'>{$rowData[$field]}</div>";
					}
					$htmlOutput .= "</td>";
				} else {
					$htmlOutput .= "<td style='vertical-align: top;text-align: center'>&nbsp;&nbsp;</td>";
				}
			}
		
		}
		return $htmlOutput;
	}
