<?php
	require_once ('include/utils/PanelUtils.php');
	global $currentModule, $adb, $list_fields_name, $list_fields, $sqllist_groupby, $mod_strings, $app_strings;

	$tabid = getTabid ($currentModule);

	$noofPanelForRow = 2;

	$widthTD    = (100 / $noofPanelForRow);
	$lstPaneles = getListPanelGraph ($tabid);

	$bufferSalida = '<table width="100%"><tr>';

	$i = 0;
	foreach ($lstPaneles as $clave => $valor) {
		$list_fields      = array ();
		$sqllist_groupby  = array ();
		$list_fields_name = array ();
		if ((($i % $noofPanelForRow) == 0) && ($i > 0)) {
			$bufferSalida .= '</tr><tr>';
		}
		$focus      = CRMEntity::getInstance (getTabModuleName ($lstPaneles[ $clave ]['reltabid']));
		$list_query = getListQuery (getTabModuleName ($lstPaneles[ $clave ]['reltabid']));
		$query      = getModifiedPanelListQuery ($lstPaneles[ $clave ]['panelid'], $list_query, getTabModuleName ($lstPaneles[ $clave ]['reltabid']), $lstPaneles[ $clave ]['type']);
		$headers    = getHeadersPanel ($lstPaneles[ $clave ]['panelid'], getTabModuleName ($lstPaneles[ $clave ]['reltabid']));

		$panelOgraph = array ();

		if ($lstPaneles[ $clave ]['type'] == 'Panel') {
			if ($lstPaneles[ $clave ]['subtype'] == 'Dash') {
				//Consulto las columnas de la tabla de datos y sobre cada una consulto
				$listaColumnas = getColumnsPanelDash ($lstPaneles[ $clave ]['panelid']);
				$headers       = array ();
				//Para cada celda de la matriz se realiza la consulta correspondiente
				$listaFilas = getValuesFilas ($lstPaneles[ $clave ]['panelid']);
				$bHeaders   = true;
				$ii         = 0;
				$totales    = array ();
				$graficar   = array ();
				foreach ($listaFilas as $value) {
					$valueFila = $value['label'];
					$j         = 0;
					$row       = array ();
					foreach ($listaColumnas as $index => $parameter) {
						$parameters = json_decode (html_entity_decode ($parameter));
						$query      = getModifiedPanelListQuery (
							$lstPaneles[ $clave ]['panelid'],
							$list_query,
							getTabModuleName ($lstPaneles[ $clave ]['reltabid']),
							$lstPaneles[ $clave ]['type'],
							$lstPaneles[ $clave ]['subtype'],
							$index
						);
						if (!empty($value['value'])) {
							$query .= ' AND ' . $value['value'];
						}

						$list_result = $adb->pquery ($query, array ());
						$rowResult   = $adb->fetchByAssoc ($list_result);

						if ($bHeaders) {
							$headers[] = $parameters->titulo;
							if ($parameters->graficar == 'no' || $parameters->graficar == '0') {
								$graficar[ $j ] = false;
							} else {
								$graficar[ $j ] = true;
							}
						}

						if ($index == -1) {
							$row[]         = $valueFila;
							$totales[ $j ] = getTranslatedString ('LBL_TOTALES');
						} else {
							$row[] = $rowResult['data'];
							if (is_numeric ($rowResult['data'])) {
								$totales[ $j ] += $rowResult['data'];
							}
						}
						$j++;
					}
					$bHeaders                           = false;
					$listview_entries[ $ii ]['records'] = $row;
					$listview_entries[ $ii ]['color']   = '#FFFFFF';
					$ii++;
				}
				$listview_entries[ $ii ]['records'] = $totales;
				$listview_entries[ $ii ]['color']   = '#FFEEAA';

				$smarty = new vtigerCRM_Smarty();

				$smarty->assign ('MOD', $mod_strings);
				$smarty->assign ('APP', $app_strings);
				$smarty->assign ('MODULE', $currentModule);
				$smarty->assign ('LISTHEADER', $headers);
				$smarty->assign ('LISTENTITY', $listview_entries);

				$data = array ();
				$n = count ($headers);
				for ($ii = 1; $ii < $n; $ii++) {
					if ($graficar[ $ii ]) {
						$data[] = array ('label' => $headers[ $ii ], 'value' => $totales[ $ii ]);
					}
				}
				$panelOgraph[] = $smarty->fetch ('PanelList.tpl');
				$panelOgraph[] = drawPieGraph ($data, $panelid);
			} else {
				$navigation_array        = VT_getSimpleNavigationValues (1, 20, 2);
				$list_result             = $adb->pquery ($query, array ());
				$focus->list_fields      = $list_fields;
				$focus->list_fields_name = $list_fields_name;
				$listview_entries = getListViewEntries ($focus, getTabModuleName ($lstPaneles[ $clave ]['reltabid']), $list_result, $navigation_array);
				$listview_entries = getPanelActions ($listview_entries, $tabid, $lstPaneles[ $clave ]['panelid'], $lstPaneles[ $clave ]['reltabid']);

				$smarty = new vtigerCRM_Smarty();
				$smarty->assign ('MOD', $mod_strings);
				$smarty->assign ('APP', $app_strings);
				$smarty->assign ('MODULE', $currentModule);
				$smarty->assign ('LISTHEADER', $headers);
				$smarty->assign ('LISTENTITY', $listview_entries);
				$panelOgraph[] = $smarty->fetch ('PanelList.tpl');
			}
		} else {
			if ($lstPaneles[ $clave ]['subtype'] == 'Bar') {
				$panelOgraph[] = getBarGraph ($query, $lstPaneles[ $clave ]['panelid'], $lstPaneles[ $clave ]['label'], $lstPaneles[ $clave ]['description']);
			} else if ($lstPaneles[ $clave ]['subtype'] == 'Pie') {
				$panelOgraph[] = getPieGraph ($query, $lstPaneles[ $clave ]['panelid'], $lstPaneles[ $clave ]['label'], $lstPaneles[ $clave ]['description']);
			}
		}

		$n = count ($panelOgraph);
		for ($ii = 0; $ii < $n; $ii++) {
			$bufferSalida .= '<td width="' . number_format ($widthTD, 0) . '%" valign="top">
					<div class="dvInnerHeader" align="center">
					<div style="float: center; font-weight: bold;">
					' . $lstPaneles[ $clave ]['label'] . '
					</div></div><br/>' .
			                 $panelOgraph[ $ii ] .
			                 '<br/></td>';
			$i++;
			if ((($i % $noofPanelForRow) == 0) && ($i > 0)) {
				$bufferSalida .= '<td width="' . number_format ($widthTD, 0) . '%">&nbsp;</td>';
			}
		}
	}
	if ((($i % $noofPanelForRow) == 0) && ($i > 0)) {
		$bufferSalida .= '<td width="' . number_format ($widthTD, 0) . '%">&nbsp;</td>';
	}
	$bufferSalida .= '</tr></table>';

	echo $bufferSalida;
