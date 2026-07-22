<?php

	/* panelUtils.php
	File containing all functionalities related to the construction of modules panel type or graphic type
	*/

	/** to get the getListPanelGraph for the given module
	 *
	 * @param $tabid :: Type Integer
	 * @returns  $lstPanelGraph Array
	 */
	global $list_fields_name, $list_fields, $sqllist_groupby;

	function getListPanelGraph ($tabid) {
		global $adb;

		$lstPanelGraph = array ();

		$sql = "SELECT * FROM vtiger_panel_graph WHERE tabid = ? ORDER BY sequence";
		$result = $adb->pquery ($sql, array ($tabid));
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return $lstPanelGraph;
		}

		while ($row = $adb->fetchByAssoc ($result)) {
			$lstPanelGraph[] = $row;
		}
		return $lstPanelGraph;
	}

	/** to get the getModuleColumnsList for the given customview
	 *
	 * @param $cvid :: Type Integer
	 * @returns  $columnlist Array in the following format
	 * $columnlist = Array( $columnindex => $columnname,
	 *             $columnindex1 => $columnname1,
	 *                    |
	 *             $columnindexn => $columnnamen)
	 */
	function getColumnsListByPanelId ($panelid, $columnindex = '') {
		global $adb;

		if ($columnindex !== '') {
			$condicionIndex = " and columnindex = " . $columnindex;
		}

		$sSQL = "SELECT vtiger_panel_graph_detail.* FROM vtiger_panel_graph_detail";
		$sSQL .= " inner join vtiger_panel_graph on vtiger_panel_graph.panelid = vtiger_panel_graph_detail.panelid";
		$sSQL .= " where vtiger_panel_graph.panelid = ? " . $condicionIndex . " order by vtiger_panel_graph_detail.columnindex";

		$result = $adb->pquery ($sSQL, array ($panelid));
		while ($columnrow = $adb->fetch_array ($result)) {
			$columnlist[ $columnrow['columnindex'] ] = $columnrow['columnname'];
		}
		return $columnlist;
	}

	/**
	 * Function to check if field is present based on
	 *
	 * @param String $columnname
	 * @param String $tablename
	 */
	function isFieldPresent_ByColumnTable ($columnname, $tablename) {
		global $adb;
		static $_fieldby_tblcol_cache;

		if (!isset($_fieldby_tblcol_cache[ $tablename ])) {
			$query = 'SELECT columnname FROM vtiger_field WHERE tablename = ? AND presence IN (0,2)';

			$result  = $adb->pquery ($query, array ($tablename));
			$numrows = $adb->num_rows ($result);

			if ($numrows) {
				$_fieldby_tblcol_cache[ $tablename ] = array ();
				for ($index = 0; $index < $numrows; ++$index) {
					$_fieldby_tblcol_cache[ $tablename ][] = $adb->query_result ($result, $index, 'columnname');
				}
			}
		}
		// If still the field was not found (might be disabled or deleted?)
		if (!isset($_fieldby_tblcol_cache[ $tablename ])) {
			return false;
		}
		return in_array ($columnname, $_fieldby_tblcol_cache[ $tablename ]);
	}

	/** to get the comparator value for the given comparator and value
	 *
	 * @param $comparator :: type string
	 * @param $value :: type string
	 * @returns  $rtvalue in the format $comparator $value
	 */
	function getAdvComparator ($comparator, $value, $datatype = '') {

		global $adb, $default_charset;
		$value = html_entity_decode (trim ($value), ENT_QUOTES, $default_charset);
		$value = $adb->sql_escape_string ($value);

		if ($comparator == "e") {
			if (trim ($value) == "NULL") {
				$rtvalue = " is NULL";
			} elseif (trim ($value) != "") {
				$rtvalue = " = " . $adb->quote ($value);
			} elseif (trim ($value) == "" && ($datatype == "V" || $datatype == "E")) {
				$rtvalue = " = " . $adb->quote ($value);
			} else {
				$rtvalue = " is NULL";
			}
		}
		if ($comparator == "n") {
			if (trim ($value) == "NULL") {
				$rtvalue = " is NOT NULL";
			} elseif (trim ($value) != "") {
				$rtvalue = " <> " . $adb->quote ($value);
			} elseif (trim ($value) == "" && $datatype == "V") {
				$rtvalue = " <> " . $adb->quote ($value);
			} elseif (trim ($value) == "" && $datatype == "E") {
				$rtvalue = " <> " . $adb->quote ($value);
			} else {
				$rtvalue = " is NOT NULL";
			}
		}
		if ($comparator == "s") {
			if (trim ($value) == "" && ($datatype == "V" || $datatype == "E")) {
				$rtvalue = " like '" . formatForSqlLike ($value, 3) . "'";
			} else {
				$rtvalue = " like '" . formatForSqlLike ($value, 2) . "'";
			}
		}
		if ($comparator == "ew") {
			if (trim ($value) == "" && ($datatype == "V" || $datatype == "E")) {
				$rtvalue = " like '" . formatForSqlLike ($value, 3) . "'";
			} else {
				$rtvalue = " like '" . formatForSqlLike ($value, 1) . "'";
			}
		}
		if ($comparator == "c") {
			if (trim ($value) == "" && ($datatype == "V" || $datatype == "E")) {
				$rtvalue = " like '" . formatForSqlLike ($value, 3) . "'";
			} else {
				$rtvalue = " like '" . formatForSqlLike ($value) . "'";
			}
		}
		if ($comparator == "k") {
			if (trim ($value) == "" && ($datatype == "V" || $datatype == "E")) {
				$rtvalue = " not like ''";
			} else {
				$rtvalue = " not like '" . formatForSqlLike ($value) . "'";
			}
		}
		if ($comparator == "l") {
			$rtvalue = " < " . $adb->quote ($value);
		}
		if ($comparator == "g") {
			$rtvalue = " > " . $adb->quote ($value);
		}
		if ($comparator == "m") {
			$rtvalue = " <= " . $adb->quote ($value);
		}
		if ($comparator == "h") {
			$rtvalue = " >= " . $adb->quote ($value);
		}
		if ($comparator == "b") {
			$rtvalue = " < " . $adb->quote ($value);
		}
		if ($comparator == "a") {
			$rtvalue = " > " . $adb->quote ($value);
		}

		return $rtvalue;
	}

	/** to get the Advanced filter for the given panel Id
	 *
	 * @param $panelid :: Type Integer
	 * @returns  $advfilterlist Array
	 */
	function getAdvFilterByPanelId ($panelid, $module = '', $columnid = '', $bTodos = true) {

		global $adb, $log, $default_charset;

		$advft_criteria = array ();

		$i = 1;
		$j = 0;

		if ($columnid !== '') {
			if ($bTodos) {
				$condicionEje = "-1,";
			}
			$conditionColumnId = " and columnindex IN ($condicionEje$columnid)";
		}

		$ssql = 'SELECT vtiger_panel_graph_conditions.* FROM vtiger_panel_graph
					INNER JOIN vtiger_panel_graph_conditions ON vtiger_panel_graph_conditions.panelid = vtiger_panel_graph.panelid';
		$ssql .= " where vtiger_panel_graph.panelid = ? " . $conditionColumnId . " order by vtiger_panel_graph_conditions.columnindex";

		$result      = $adb->pquery ($ssql, array ($panelid));
		$noOfColumns = $adb->num_rows ($result);
		if ($noOfColumns <= 0) {
			return;
		}

		while ($relcriteriarow = $adb->fetch_array ($result)) {
			$columnIndex            = $relcriteriarow["columnindex"];
			$criteria               = array ();
			$criteria['columnname'] = html_entity_decode ($relcriteriarow["columnname"], ENT_QUOTES, $default_charset);
			$criteria['comparator'] = $relcriteriarow["comparator"];
			$advfilterval           = html_entity_decode ($relcriteriarow["value"], ENT_QUOTES, $default_charset);
			$col                    = explode (":", $relcriteriarow["columnname"]);
			$temp_val               = explode (",", $relcriteriarow["value"]);
			if (($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) && !isset($col[5])) {
				$val = Array ();
				for ($x = 0; $x < count ($temp_val); $x++) {
					if ($col[4] == 'D') {
						$date      = new DateTimeField(trim ($temp_val[ $x ]));
						$val[ $x ] = $date->getDisplayDate ();
					} elseif ($col[4] == 'DT') {
						$date      = new DateTimeField(trim ($temp_val[ $x ]));
						$val[ $x ] = $date->getDisplayDateTimeValue ();
					} else {
						$date      = new DateTimeField(trim ($temp_val[ $x ]));
						$val[ $x ] = $date->getDisplayTime ();
					}
				}
				$advfilterval = implode (",", $val);
			} elseif ($col[4] == 'DT' && isset($col[5])) {
				$val[ $x ]    = CustomView::getDateforStdFilterBytype ($col[5]);
				$advfilterval = implode (",", $val[ $x ]);
			}
			$criteria['value']            = $advfilterval;
			$criteria['column_condition'] = $relcriteriarow["column_condition"];

			$advft_criteria[ $i ]['columns'][ $j ] = $criteria;
			$advft_criteria[ $i ]['condition']     = $groupCondition;
			$j++;
		}
		if (!empty($advft_criteria[ $i ]['columns'][ $j - 1 ]['column_condition'])) {
			$advft_criteria[ $i ]['columns'][ $j - 1 ]['column_condition'] = '';
		}
		// Clear the condition (and/or) for last group, if any.
		if (!empty($advft_criteria[ $i - 1 ]['condition'])) {
			$advft_criteria[ $i - 1 ]['condition'] = '';
		}
		return $advft_criteria;
	}

	function getHeadersPanel ($panelid, $module) {
		global $adb;
		$headers     = array ();
		$columnslist = getColumnsListByPanelId ($panelid);
		if (isset($columnslist)) {
			foreach ($columnslist as $columnname => $value) {
				$tablefield = "";

				if ($value != "") {
					$list = explode (":", $value);

					$fieldlabel = str_replace ($module . '_', '', $list[3]);
					$fieldlabel = str_replace ("_", " ", $fieldlabel);

					$headers[] = getTranslatedString ($fieldlabel); //added to support i18n issue
				}
			}
		}
		$headers[] = getTranslatedString ('LBL_ACTION');
		return $headers;
	}

	function getDataGraph ($listquery) {
		global $adb;
		global $sqllist_groupby;
		$listquerybak = $listquery;

		foreach ($sqllist_groupby as $k => $groupfld) {
			if (strstr ($groupfld, ' as ')) {
				$parts                 = explode (' as ', $groupfld);
				$sqllist_groupby[ $k ] = end ($parts);
			}
		}

		$fields_groupby = implode (",", $sqllist_groupby);

		$listquerybak = $listquery;
		list($sql, $orderby) = explode (' ORDER BY ', $listquerybak);
		if (!empty($fields_groupby)) {
			$sql .= " GROUP BY " . $fields_groupby;
		}

		if (!empty($orderby)) {
			$sql .= ' ORDER BY ' . $orderby;
		}
		return $adb->query ($sql);
	}

	function getDataGroup ($listquery) {
		global $adb;
		global $sqllist_groupby;
		$listquerybak = $listquery;

		foreach ($sqllist_groupby as $k => $groupfld) {
			if (strstr ($groupfld, ' as ')) {
				$parts                 = explode (' as ', $groupfld);
				$sqllist_groupby[ $k ] = end ($parts);
			}
		}

		$fields_groupby = implode (",", $sqllist_groupby);

		$listquerybak = $listquery;
		list($sql, $orderby) = explode (' ORDER BY ', $listquerybak);
		if (!empty($fields_groupby)) {
			$sql .= " GROUP BY " . $fields_groupby;
		}

		if (!empty($orderby)) {
			$sql .= ' ORDER BY ' . $orderby;
		}
		return $sql;
	}

	function getBarGraph ($listquery, $panelid, $title, $legend) {
		global $adb, $sqllist_groupby;
		$result = getDataGraph ($listquery);

		$bufferSalida = '
		<table width="100%" style="border-width: 1px;border-color: #D3E7F4;border-style: solid;">
			<tr>
			<td>';

		if ($result) {
			$i           = 0;
			$series      = '<series>';
			$colores     = Array ("#FA5858", "#FAAC58", "#F4FA58", "#ACFA58", "#58FA58", "#58FAAC", "#58ACFA", "#5858FA", "#AC58FA", "#FA58F4", "#A4A4A4", "#8A0808", "#8A4B08", "#8A4B08", "#088A85", "#08088A");
			$xml_grafico = "<?xml version='1.0' encoding='UTF-8' ?><chart><series><value xid='0'>" . $legend . "</value></series>";  //
			$xml_grafico .= "<labels><label><x>0</x><y>7</y><text_color>000000</text_color><text_size>13</text_size><align>center</align><text> </text></label></labels>";
			$xml_grafico .= "<graphs>";

			if (strstr ($sqllist_groupby[0], '.')) {
				$lstfieldname = explode ('.', $sqllist_groupby[0]);
				$lstfieldname = end ($lstfieldname);
			} else {
				$lstfieldname = $sqllist_groupby[0];
			}

			while ($row = $adb->fetch_array ($result)) {
				$titulo = $row[ $lstfieldname ];
				$titulo = str_replace ('%', '', $titulo);

				if (empty($titulo)) {
					$titulo = 'N/A';
				}
				$series .= '<value xid="' . $i . '">' . $titulo . '</value>';
				$graphsReservas .= '<value xid="' . $i . '">' . $row['data'] . '</value>';
				$i++;
				$xml_grafico .= "<graph gid='$i' title='$titulo' color='" . $colores[ $i ] . "'><value xid='0'>" . number_format ($row['data'], 2, '.', '') . "</value></graph>";
			}
			$xml_grafico .= "</graphs></chart>";

			$bufferSalida .= '
			<script type="text/javascript" src="include/graficos/amcolumn/swfobject.js"></script>
			<div id="flashcontent_' . $panelid . '">
				<strong>You need to upgrade your Flash Player</strong>
			</div>

			<script type="text/javascript">
				// <![CDATA[
				var so = new SWFObject("include/graficos/amcolumn/amcolumn.swf", "amcolumn", "100%", "400", "8", "#FFFFFF");
				so.addVariable("path", "include/graficos/amcolumn/");
				so.addVariable("settings_file", encodeURIComponent("include/graficos/amcolumn/amcolumn_settings.xml"));
				so.addVariable("chart_data","' . $xml_grafico . '");
				so.write("flashcontent_' . $panelid . '");
				// ]]>
			</script>';
		}

		$bufferSalida .= '</td></tr></table>';

		return $bufferSalida;
	}

	function drawPieGraph ($data, $panelid) {
		$xml_grafico = '<pie>';
		foreach ($data as $value) {
			$xml_grafico .= "<slice title='" . mb_convert_encoding($value['label'], 'UTF-8', 'ISO-8859-1') . "'>" . number_format ($value['value'], 2, '.', '') . "</slice>";
		}
		$xml_grafico .= '</pie>';

		$bufferSalida .= '
			<script type="text/javascript" src="include/graficos/ampie/swfobject.js"></script>
			<div id="flashcontent_grafico_' . $panelid . '">
				<strong>You need to upgrade your Flash Player</strong>
			</div>

			<script type="text/javascript">
				// <![CDATA[
				var so = new SWFObject("include/graficos/ampie/ampie.swf", "ampie", "100%", "300", "8", "#FFFFFF");
				so.addVariable("path", "include/graficos/gestion/ampie/");
				so.addVariable("settings_file", encodeURIComponent("include/graficos/ampie/ampie_settings.xml"));
				so.addVariable("chart_data","' . $xml_grafico . '" );
				so.write("flashcontent_grafico_' . $panelid . '");
				// ]]>
			</script>';

		return $bufferSalida;
	}

	function getPieGraph ($listquery, $panelid, $title, $legend) {
		global $adb, $sqllist_groupby;
		$result = getDataGraph ($listquery);

		$bufferSalida = '
		<table width="100%">
			<tr>
			<td>';

		if ($result) {
			$i = 0;

			$data = array ();
			while (list($val, $label) = $adb->fetch_row ($result)) {
				$data[] = array (
					'label' => mb_convert_encoding($label, 'UTF-8', 'ISO-8859-1'),
					'value' => $val,
				);
			}
			$bufferSalida .= drawPieGraph ($data, $panelid);
		}
		$bufferSalida .= '
			</td>
			</tr>
		</table>';

		return $bufferSalida;
	}

	function getPanelActions ($listview_entries, $tabid, $panelid, $relmodule) {
		global $adb;

		//for($i = 0;$i < count($listview_entries);$i++) {
		//var_dump($listview_entries);

		foreach ($listview_entries['records'] as $clave => $valor) {
			$button            = '';
			$customlink_params = Array ('MODULE' => getTabname ($relmodule), 'RECORD' => $clave);
			$panelListLinks    = Vtiger_Link::getAllByType ($tabid, Array ('PANEL_LIST_LINKS'), $customlink_params, $panelid);

			foreach ($panelListLinks as $valor) {
				for ($i = 0; $i < count ($valor); $i++) {
					if (!empty($valor[ $i ]->linkicon)) {
						$button .= '<a href="' . $valor[ $i ]->linkurl . '" target="_blank">
									<img src="themes/images/' . $valor[ $i ]->linkicon . '" style="border: 0px solid #000000;vertical-align: middle;margin-left: 10px;" alt="' . $valor[ $i ]->linklabel . '" title="' . $valor[ $i ]->linklabel . '" onclick="' . $valor[ $i ]->linkurl . '">
									</a>';
					} else {
						$button .= "<input title='" . getTranslatedString ($valor[ $i ]->linklabel) . " " . getTranslatedString ($singular_modname) . "' class='crmbutton small create'" .
						           " onclick='" . $valor[ $i ]->linkurl . "' type='button' name='button'" .
						           " value='" . getTranslatedString ($valor[ $i ]->linklabel) . "'>&nbsp;";
					}
				}
			}
			$listview_entries['records'][ $clave ][ count ($listview_entries[ $clave ]) - 1 ] = $button;
		}
		return $listview_entries;
	}

	function getByModule_ColumnsList ($oCustomView, $module, $columnslist, $selected = "") {
		global $current_language, $theme;
		global $app_list_strings;
		$advfilter   = array ();
		$mod_strings = return_specified_module_language ($current_language, $module);

		$check_dup = Array ();
		foreach ($oCustomView->module_list[ $module ] as $key => $value) {
			$advfilter = array ();
			$label     = $key;
			if (isset($columnslist[ $module ][ $key ])) {
				foreach ($columnslist[ $module ][ $key ] as $field => $fieldlabel) {
					if (!in_array ($fieldlabel, $check_dup)) {
						if (isset($mod_strings[ $fieldlabel ])) {
							if ($selected == $field) {
								$advfilter_option['value']    = $field;
								$advfilter_option['text']     = $mod_strings[ $fieldlabel ];
								$advfilter_option['selected'] = "selected";
							} else {
								$advfilter_option['value']    = $field;
								$advfilter_option['text']     = $mod_strings[ $fieldlabel ];
								$advfilter_option['selected'] = "";
							}
						} else {
							if ($selected == $field) {
								$advfilter_option['value']    = $field;
								$advfilter_option['text']     = $fieldlabel;
								$advfilter_option['selected'] = "selected";
							} else {
								$advfilter_option['value']    = $field;
								$advfilter_option['text']     = $fieldlabel;
								$advfilter_option['selected'] = "";
							}
						}
						$advfilter[]  = $advfilter_option;
						$check_dup [] = $fieldlabel;
					}
				}
				$advfilter_out[ $label ] = $advfilter;
			}
		}
		// Special case handling only for Calendar moudle - Not required for other modules.
		if ($module == 'Calendar') {
			$finalfield  = Array ();
			$finalfield1 = Array ();
			$finalfield2 = Array ();
			$newLabel    = $mod_strings['LBL_CALENDAR_INFORMATION'];

			if (isset($advfilter_out[ $mod_strings['LBL_TASK_INFORMATION'] ])) {
				$finalfield1 = $advfilter_out[ $mod_strings['LBL_TASK_INFORMATION'] ];
			}
			if (isset($advfilter_out[ $mod_strings['LBL_EVENT_INFORMATION'] ])) {
				$finalfield2 = $advfilter_out[ $mod_strings['LBL_EVENT_INFORMATION'] ];
			}
			$finalfield[ $newLabel ] = array_merge ($finalfield1, $finalfield2);
			if (isset ($advfilter_out[ $mod_strings['LBL_CUSTOM_INFORMATION'] ])) {
				$finalfield[ $mod_strings['LBL_CUSTOM_INFORMATION'] ] = $advfilter_out[ $mod_strings['LBL_CUSTOM_INFORMATION'] ];
			}
			$advfilter_out = $finalfield;
		}
		return $advfilter_out;
	}

	function getByModule_ColumnsHTML ($oCustomView, $module, $columnslist, $selected = "") {
		$columnsList = getByModule_ColumnsList ($oCustomView, $module, $columnslist, $selected);
		return generateSelectColumnsHTML ($columnsList, $module);
	}

	function generateSelectColumnsHTML ($columnsList, $module) {
		$shtml = '';

		foreach ($columnsList as $blocklabel => $blockcolumns) {
			$shtml .= "<optgroup label='" . getTranslatedString ($blocklabel, $module) . "' class='select' style='border:none'>";
			foreach ($blockcolumns as $columninfo) {
				$shtml .= "<option " . $columninfo['selected'] . " value='" . $columninfo['value'] . "'>" . $columninfo['text'] . "</option>";
			}
		}
		return $shtml;
	}

	function getFieldSelectedByPanelId ($panelid, $index) {
		global $adb;

		$query = "SELECT columnname FROM vtiger_panel_graph_detail WHERE panelid = ? AND columnindex = ?";

		$result = $adb->pquery ($query, array ($panelid, $index));

		if ($result) {
			return $adb->query_result ($result, 0, 'columnname');
		}

		return;
	}

	function getAdvCriteriaHTML ($selected = "") {
		global $adv_filter_options;

		foreach ($adv_filter_options as $key => $value) {
			if ($selected == $key) {
				$shtml .= "<option selected value=\"" . $key . "\">" . $value . "</option>";
			} else {
				$shtml .= "<option value=\"" . $key . "\">" . $value . "</option>";
			}
		}

		return $shtml;
	}

	function deleteColumnsPanelOrGraph ($panelid, $columnindex = '') {
		global $adb;

		if ($columnindex !== '') {
			$conditionIndex = " and columnindex = " . $columnindex;
		}

		$query = "DELETE FROM vtiger_panel_graph_detail WHERE panelid = ?" . $conditionIndex;

		$result = $adb->pquery ($query, array ($panelid));
	}

	function saveColumnsPanelOrGraph ($panelid, $columnindex, $columnname) {
		global $adb;

		if (!empty($panelid) && $columnindex !== '' && !empty($columnname)) {
			$columnindex--;
			$query  = "INSERT INTO vtiger_panel_graph_detail (id,panelid,columnindex,columnname) VALUES (NULL,?,?,?)";
			$result = $adb->pquery ($query, array ($panelid, $columnindex, $columnname));
		}
	}

	function deleteConditionsPanelOrGraph ($panelid, $columnindex = '') {
		global $adb;

		if ($columnindex !== '') {
			$conditionIndex = " and columnindex = " . $columnindex;
		}
		$query = "DELETE FROM vtiger_panel_graph_conditions WHERE panelid = ?" . $conditionIndex;

		$result = $adb->pquery ($query, array ($panelid));
	}

	function saveConditionsPanelOrGraph ($panelid, $columnindex, $condition, $columnname, $comparator, $value, $groupid, $column_condition) {
		global $adb;

		if (!empty($panelid) && !empty($columnname)) {
			$query = "INSERT INTO vtiger_panel_graph_conditions (panelid,columnindex,conditions,columnname,comparator,value,groupid,column_condition)
						VALUES (?,?,?,?,?,?,?,?)";

			if (empty($column_condition)) {
				$column_condition = "and";
			}
			$result = $adb->pquery ($query, array ($panelid, $columnindex, $condition, $columnname, $comparator, $value, $groupid, $column_condition));
		}
	}

	function createPanelOrGraph ($module, $label, $type, $subtype, $relmodule) {
		global $adb;

		$module    = getTabid ($module);
		$relmodule = getTabid ($relmodule);

		$query  = "SELECT max(sequence)+1 AS sequence FROM vtiger_panel_graph WHERE tabid = ?";
		$result = $adb->pquery ($query, array ($module));

		$sequence = $adb->query_result ($result, 0, 'sequence');
		if (!isset($sequence)) {
			$sequence = 1;
		}
		$query = "INSERT INTO vtiger_panel_graph (panelid,tabid,label,type,subtype,description,reltabid,sequence) VALUES (NULL,?,?,?,?,?,?,?)";

		$adb->pquery ($query, array ($module, $label, $type, $subtype, '', $relmodule, $sequence));
		return $adb->getLastInsertID ();
	}

	function updatePositionPanelOrGraph ($module, $pos, $prevpos) {
		global $adb;
		$module = getTabid ($module);
		$query  = "SELECT panelid FROM vtiger_panel_graph WHERE tabid = ? AND sequence = ?";
		$result = $adb->pquery ($query, array ($module, $pos));

		$panelid = $adb->query_result ($result, 0, 'panelid');

		$query  = "UPDATE vtiger_panel_graph SET sequence = ? WHERE sequence = ? AND tabid = ?";
		$result = $adb->pquery ($query, array ($pos, $prevpos, $module));
		$query  = "UPDATE vtiger_panel_graph SET sequence = ? WHERE panelid = ? AND tabid = ?";
		$result = $adb->pquery ($query, array ($prevpos, $panelid, $module));
	}

	function updatePositionColumnPanel ($panelid, $pos, $prevpos) {
		global $adb;

		$query  = "UPDATE vtiger_panel_graph_detail SET columnindex = 99999 WHERE columnindex = ? AND panelid = ?";
		$result = $adb->pquery ($query, array ($pos, $panelid));

		$query  = "UPDATE vtiger_panel_graph_detail SET columnindex = ? WHERE columnindex = ? AND panelid = ?";
		$result = $adb->pquery ($query, array ($pos, $prevpos, $panelid));

		$query  = "UPDATE vtiger_panel_graph_detail SET columnindex = ? WHERE columnindex = 99999 AND panelid = ?";
		$result = $adb->pquery ($query, array ($prevpos, $panelid));

		$query  = "UPDATE vtiger_panel_graph_conditions SET columnindex = 99999 WHERE columnindex = ? AND panelid = ?";
		$result = $adb->pquery ($query, array ($pos, $panelid));

		$query  = "UPDATE vtiger_panel_graph_conditions SET columnindex = ? WHERE columnindex = ? AND panelid = ?";
		$result = $adb->pquery ($query, array ($pos, $prevpos, $panelid));

		$query  = "UPDATE vtiger_panel_graph_conditions SET columnindex = ? WHERE columnindex = 99999 AND panelid = ?";
		$result = $adb->pquery ($query, array ($prevpos, $panelid));

		$query  = "UPDATE vtiger_panel_graph_parameters SET columnindex = 99999 WHERE columnindex = ? AND panelid = ?";
		$result = $adb->pquery ($query, array ($pos, $panelid));

		$query  = "UPDATE vtiger_panel_graph_parameters SET columnindex = ? WHERE columnindex = ? AND panelid = ?";
		$result = $adb->pquery ($query, array ($pos, $prevpos, $panelid));

		$query  = "UPDATE vtiger_panel_graph_parameters SET columnindex = ? WHERE columnindex = 99999 AND panelid = ?";
		$result = $adb->pquery ($query, array ($prevpos, $panelid));
	}

	function deletePanelOrGraph ($module, $panelid) {
		global $adb;
		$module = getTabid ($module);
		$query  = "SELECT sequence FROM vtiger_panel_graph WHERE tabid = ? AND panelid = ?";
		$result = $adb->pquery ($query, array ($module, $panelid));

		$sequence = $adb->query_result ($result, 0, 'sequence');

		$query  = "DELETE FROM vtiger_panel_graph_parameters WHERE panelid = ?";
		$result = $adb->pquery ($query, array ($panelid));

		$query  = "DELETE FROM vtiger_panel_graph_conditions WHERE panelid = ?";
		$result = $adb->pquery ($query, array ($panelid));

		$query  = "DELETE FROM vtiger_panel_graph_detail WHERE panelid = ?";
		$result = $adb->pquery ($query, array ($panelid));

		$query  = "DELETE FROM vtiger_panel_graph WHERE panelid = ?";
		$result = $adb->pquery ($query, array ($panelid));

		$query  = "UPDATE vtiger_panel_graph SET sequence = sequence - 1 WHERE sequence > ? AND panelid = ?";
		$result = $adb->pquery ($query, array ($sequence, $panelid));
		return;
	}

	function linksPanelOrGraph ($module, $panelid) {
		global $adb;

		$module = getTabid ($module);

		$query  = "SELECT linklabel,linkurl,linkicon FROM vtiger_links WHERE tabid = ? AND relatedlistid = ? AND linktype = 'PANEL_LIST_LINKS' ORDER BY sequence";
		$result = $adb->pquery ($query, array ($module, $panelid));

		$bufferSalida = '
			<tr id="row0" style="display:none">
				<td class="dvtCellLabel" align="left" id="td_label0">
				<input name="label[]" id="label0" class="repBox" type="text" value="' . $row['linklabel'] . '">
				</td>
				<td class="dvtCellLabel" align="left" id="td_url0">
				<input name="url[]" id="url0" class="repBox" type="text" value="' . $row['linkicon'] . '">
				</td>
				<td class="dvtCellLabel" align="left" id="td_icon0">
				<input name="icon[]" id="icon0" class="repBox" type="text" value="' . $row['linkurl'] . '">
				</td>
				<td class="dvtCellLabel" align="left" id="td_action0">
				<a onclick="deleteLink(\'0\');" href="javascript:;"><img src="themes/images/delete.gif" align="absmiddle">
				</a>
				</td>
			</tr>';

		$i = 1;
		while ($row = $adb->fetchByAssoc ($result)) {
			$bufferSalida .= '
			<tr id="row' . $i . '">
				<td class="dvtCellLabel" align="left" id="td_label' . $i . '">
				<input name="label[]" id="label0" class="repBox" type="text" value="' . $row['linklabel'] . '">
				</td>
				<td class="dvtCellLabel" align="left" id="td_url' . $i . '">
				<input name="url[]" id="url" class="repBox" type="text" value="' . $row['linkicon'] . '">
				</td>
				<td class="dvtCellLabel" align="left" id="td_icon' . $i . '">
				<input name="icon[]" id="icon" class="repBox" type="text" value="' . $row['linkurl'] . '">
				</td>
				<td class="dvtCellLabel" align="left" id="td_action' . $i . '">
				<a onclick="deleteLink(\'' . $i . '\');" href="javascript:;"><img src="themes/images/delete.gif" align="absmiddle">
				</a>
				</td>
			</tr>';
			$i++;
		}

		return $bufferSalida;
	}

	function deleteLinksPanelOrGraph ($module, $panelid) {
		global $adb;

		$module = getTabid ($module);

		$query = "DELETE FROM vtiger_links WHERE tabid = ? AND relatedlistid = ? AND linktype = 'PANEL_LIST_LINKS'";

		$result = $adb->pquery ($query, array ($module, $panelid));
	}

	function saveLinksPanelOrGraph ($module, $panelid, $label, $url, $icon) {
		global $adb;

		$query = "SELECT id FROM vtiger_links_seq";

		$result = $adb->query ($query);
		$id     = $adb->query_result ($result, 0, 'id');
		$module = getTabid ($module);

		for ($i = 0; $i < count ($label); $i++) {
			if (!empty($label[ $i ]) || !empty($icon[ $i ])) {

				$query = "INSERT INTO vtiger_links (linkid,tabid,linktype,linklabel,linkurl,linkicon,sequence,handler_path,handler_class,handler,relatedlistid)
							VALUES (?,?,'PANEL_LIST_LINKS',?,?,?,?,NULL,NULL,NULL,?)";

				$result = $adb->pquery ($query, array ($id, $module, $label[ $i ], $url[ $i ], $icon[ $i ], $i, $panelid));
				$id++;
			}
		}

		$query  = "UPDATE vtiger_links_seq SET id = ?";
		$result = $adb->pquery ($query, array ($id));
	}

	function getHTMLOperations ($selectValue) {
		$lstOp = array ('count', 'sum', 'avg');

		$bufferSalida = '';
		for ($i = 0; $i < count ($lstOp); $i++) {
			$selected = '';
			if ($selectValue == $lstOp[ $i ]) {
				$selected = "selected";
			}
			$bufferSalida .= '<option value="' . $lstOp[ $i ] . '" ' . $selected . '>' . getTranslatedString ($lstOp[ $i ]) . '</option>';
		}
		return $bufferSalida;
	}

	function getHTMLSubtype ($selectValue) {
		$lstOp = array ('Bar', 'Pie');

		$bufferSalida = '';
		for ($i = 0; $i < count ($lstOp); $i++) {
			$selected = '';
			if ($selectValue == $lstOp[ $i ]) {
				$selected = "selected";
			}
			$bufferSalida .= '<option value="' . $lstOp[ $i ] . '" ' . $selected . '>' . getTranslatedString ($lstOp[ $i ]) . '</option>';
		}
		return $bufferSalida;
	}

	function getPanelOGraphSettings ($panelid) {
		global $adb;
		$sql = "SELECT subtype,description FROM vtiger_panel_graph WHERE panelid = ?";

		$result = $adb->pquery ($sql, array ($panelid));

		while ($row = $adb->fetchByAssoc ($result)) {
			$lstPanelGraph[] = $row;
		}
		return $lstPanelGraph;
	}

	function saveGraphSettings ($subtype, $description, $panelid) {
		global $adb;
		$sql = "UPDATE vtiger_panel_graph SET subtype = ?, description = ? WHERE panelid = ?";

		$result = $adb->pquery ($sql, array ($subtype, $description, $panelid));
	}

	function getColumnsPanelDash ($panelid, $columnIndex = '') {
		global $adb;
		$lstColumns = array ();
		$condicion  = '';

		if (isset($columnIndex) && $columnIndex !== '') {
			$condicion = " AND columnindex = " . $columnIndex;
		}
		$sql = "SELECT columnindex, parameter FROM vtiger_panel_graph_parameters WHERE panelid = ? " . $condicion . " ORDER BY columnindex";

		$result = $adb->pquery ($sql, array ($panelid));
		while ($row = $adb->fetchByAssoc ($result)) {
			$lstColumns[ $row['columnindex'] ] = $row['parameter'];
		}
		return $lstColumns;
	}

	function getColumnsPanelDashSmarty ($panelid, $fld_module, $relatedModule) {
		global $currentModule;
		$lstColumnsSQL = getColumnsPanelDash ($panelid);

		$i = 1;
		foreach ($lstColumnsSQL as $index => $value) {
			$imgDown = '<img src="themes/softed/images/arrow_down.png" border="0" style="cursor:pointer;" onclick="changeColumnPosition(' . $panelid . ',' . ($index + 1) . ',' . $index . ')">';
			$imgUp   = '<img src="themes/softed/images/arrow_up.png" border="0" style="cursor:pointer;" onclick="changeColumnPosition(' . $panelid . ',' . ($index - 1) . ',' . $index . ')">';
			$imgDel  = '<img src="themes/images/delete.gif" align="absmiddle" title="Borrar..." border="0" onclick="deleteColumn(' . $panelid . ',' . $index . ')">';

			if ($i == 1) {
				$imgUp = '&nbsp;';
			}

			if (($i) == count ($lstColumnsSQL) || $index == -1) {
				$imgDown = '&nbsp;';
			}

			$parameters         = json_decode (html_entity_decode ($value));
			$parameters->action = '
			<table width="350px">
				<tr>
				<td width="200px">
				<input type="button" class="crmButton create small" onclick="jQuery.ajax({type: \'POST\',url: \'index.php\',data: { panelid: \'' . $panelid . '\', columnindex: \'' . $index . '\', module: \'' . $currentModule . '\', action: \'LayoutPanelList\', function: \'panelColumnProperties\', Ajax: \'true\', related_module: \'' . $relatedModule . '\', fld_module: \'' . $fld_module . '\' }}).done(function( html ) { jQuery( \'#textodlgPanelGraphProperties\' ).html( html ); });jQuery(\'#dlgPanelGraphProperties\').slideDown(function(){});" alt="' . getTranslatedString ('LBL_EDIT_PANEL_GRAPH_PROPERTIES') . '" title="' . getTranslatedString ('LBL_EDIT_COLUMN_PROPERTIES') . '" value="' . getTranslatedString ('LBL_EDIT_COLUMN_PROPERTIES') . '"/>
				</td>
				<td width="50px">' . $imgUp . '</td><td width="50px">' . $imgDown . '</td><td width="50px">' . $imgDel . '</td>
				</tr>
			</table>';

			$lstColumns[] = $parameters;
			if ($index != -1) {
				$i++;
			}
		}
		return $lstColumns;
	}

	function getValuesFilas ($panelid) {
		global $adb;

		$sql        = "SELECT columnindex, parameter FROM vtiger_panel_graph_parameters WHERE panelid = ? AND columnindex = -1";
		$result     = $adb->pquery ($sql, array ($panelid));
		$row        = $adb->fetchByAssoc ($result);
		$parameters = json_decode (html_entity_decode ($row['parameter']));

		if ($parameters->groupby == 'month') {
			$values = array (
				array ('label' => getTranslatedString ('Enero'), 'value' => 'MONTH(' . $parameters->field . ') = 1'),
				array ('label' => getTranslatedString ('Febrero'), 'value' => 'MONTH(' . $parameters->field . ') = 2'),
				array ('label' => getTranslatedString ('Marzo'), 'value' => 'MONTH(' . $parameters->field . ') = 3'),
				array ('label' => getTranslatedString ('Abril'), 'value' => 'MONTH(' . $parameters->field . ') = 4'),
				array ('label' => getTranslatedString ('Mayo'), 'value' => 'MONTH(' . $parameters->field . ') = 5'),
				array ('label' => getTranslatedString ('Junio'), 'value' => 'MONTH(' . $parameters->field . ') = 6'),
				array ('label' => getTranslatedString ('Julio'), 'value' => 'MONTH(' . $parameters->field . ') = 7'),
				array ('label' => getTranslatedString ('Agosto'), 'value' => 'MONTH(' . $parameters->field . ') = 8'),
				array ('label' => getTranslatedString ('Septiembre'), 'value' => 'MONTH(' . $parameters->field . ') = 9'),
				array ('label' => getTranslatedString ('Octubre'), 'value' => 'MONTH(' . $parameters->field . ') = 10'),
				array ('label' => getTranslatedString ('Noviembre'), 'value' => 'MONTH(' . $parameters->field . ') = 11'),
				array ('label' => getTranslatedString ('Diciembre'), 'value' => 'MONTH(' . $parameters->field . ') = 12'),
			);
		} elseif ($parameters->groupby == 'value') {
			$sql    = "SELECT distinct $parameters->field as value FROM $parameters->table";
			$result = $adb->query ($sql);
			while ($row2 = $adb->fetchByAssoc ($result)) {
				$values[] = array ('label' => getTranslatedString ($row2['value']), 'value' => $row2['value']);
			}
		}
		return $values;
	}

	function saveColumnParameters ($panelid, $columnindex, $parameters) {
		global $adb;

		$sql    = "DELETE FROM vtiger_panel_graph_parameters WHERE panelid = ? AND columnindex = ?";
		$result = $adb->pquery ($sql, array ($panelid, $columnindex));

		$sql    = "INSERT INTO vtiger_panel_graph_parameters VALUES (NULL,?,?,?)";
		$result = $adb->pquery ($sql, array ($panelid, $columnindex, $parameters));
	}

	function getStatusAxisColumn ($panelid, $columnindex) {
		global $adb;

		if ($columnindex == -1) {
			return "";
		}

		$sql    = "SELECT * FROM vtiger_panel_graph_parameters WHERE panelid = ? AND columnindex = -1";
		$result = $adb->pquery ($sql, array ($panelid));

		if ($adb->num_rows ($result) > 0) {
			return 'disabled="disabled"';
		}

		return "";
	}

	function getLastColumnIndex ($panelid) {
		global $adb;
		$sql = "SELECT max(columnindex) AS columnindex FROM vtiger_panel_graph_parameters WHERE panelid = ?";

		$result = $adb->pquery ($sql, array ($panelid));

		if ($adb->num_rows ($result) == 0) {
			$columnIndex = 0;
		} else {
			$row         = $adb->fetchByAssoc ($result);
			$columnIndex = $row['columnindex'] + 1;
		}

		return $columnIndex;
	}

?>