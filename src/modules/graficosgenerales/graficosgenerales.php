<?php

	class Graficos {

		public function getNameBs ($bsid) {
			global $adb;
			$sql    = 'SELECT titulo FROM vtiger_boxscore WHERE boxscoreid = ?';
			$result = $adb->pquery ($sql, array ($bsid));
			return $adb->query_result ($result, 0, 'titulo');
		}

		// @codingStandardsIgnoreStart
		// La complejidad inherente al módulo de boxscore impide que se pueda refactorizar adecuadamente esta función. Básicamente me resulta incomprensible
		/**
		 * @param $dataGrafico
		 */
		public function getDataGraficoBoxScoreTipoTwo ($dataGrafico) {
			global $adb;

			$queryPrimarioReporte = $dataGrafico['sqlprimarioreporte'];
			$decodedText          = html_entity_decode ($dataGrafico['varreporte']);
			$myVars               = json_decode ($decodedText, true);

			if ($myVars['boxscoreselect'] != '') {
				$auxBsSelect = $myVars['boxscoreselect'];
				foreach ($auxBsSelect as $clave => $valor) {
					if (empty($valor)) {
						unset($auxBsSelect[ $clave ]);
					}
				}
				$boxscoreForQuery = implode (',', $auxBsSelect);
			}

			$boxGraficar = array ();

			foreach ($myVars['boxscoreselect'] as $key => $value) {
				if ($value != '') {
					$boxscoreBase[ $value ]         = array ('id' => $value);
					$boxscoreselect[]               = $value;
					$boxscoreselectTitulo[ $value ] = html_entity_decode ($this->getNameBs ($value), ENT_QUOTES, 'UTF-8');
				}
			}

			foreach ($myVars['idsBS'] as $key => $value) {
				if ($value != '') {
					$boxGraficar[ $value ] = array ('id' => $value, 'BSid' => $boxscoreBase);
				}
			}

			$fechaDesde = $myVars['fecha_desde'];
			$fechaHasta = $myVars['fecha_hasta'];

			$semanasData = array ();

			$resultPrimarioReporte = $adb->pquery ($queryPrimarioReporte, array ());
			while ($rowPrimarioReporte = $adb->fetchByAssoc ($resultPrimarioReporte)) {
				$boxGraficar[ $rowPrimarioReporte['box_score_dataid'] ]['titulo'] = html_entity_decode ($rowPrimarioReporte['box_score'], ENT_QUOTES, 'UTF-8');

				$queryBoxScoreData  = "SELECT * FROM vtiger_box_score_data vbsd join vtiger_box_score_data_semanal bsds on (bsds.box_score_dataid = vbsd.box_score_dataid) WHERE vbsd.boxscoreid in ($boxscoreForQuery) and box_score = '" . html_entity_decode ($rowPrimarioReporte['box_score'], ENT_QUOTES, 'UTF-8') . "' and bsds.boxscoreid = vbsd.boxscoreid and fecha >= '$fechaDesde' and fecha <= '$fechaHasta' ";
				$resultBoxScoreData = $adb->pquery ($queryBoxScoreData, array ());
				while ($rowBoxScoreData = $adb->fetchByAssoc ($resultBoxScoreData)) {
					$boxGraficar[ $rowPrimarioReporte['box_score_dataid'] ]['BSid'][ $rowBoxScoreData['boxscoreid'] ]['dataSemanal'][ $rowBoxScoreData['fecha'] ] = $rowBoxScoreData['valor'];
					if (!in_array ($rowBoxScoreData['fecha'], $semanasData)) {
						array_push ($semanasData, $rowBoxScoreData['fecha']);
					}
				}
			}
			//grafico comparativo
			$keysGraph    = array_keys ($boxGraficar);
			$jsGraficoInd = '';
			foreach ($keysGraph as $keyGraph) {
				$jsGraficoInd .= "
			graphBar = Morris.Bar({
			element: 'graph-bar-" . $dataGrafico['graficoid'] . '-' . $keyGraph . "',
			data: [";

				foreach ($semanasData as $keySemana => $valueSemana) {
					$jsGraficoInd .= "{x: '" . date ('d-M', strtotime ($valueSemana)) . "', y: " . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[0] ]['dataSemanal'][ $valueSemana ]) ? $boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[0] ]['dataSemanal'][ $valueSemana ] : 0) . ', z: ' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[1] ]['dataSemanal'][ $valueSemana ]) ? $boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[1] ]['dataSemanal'][ $valueSemana ] : 0) . ', a: ' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[2] ]['dataSemanal'][ $valueSemana ]) ? $boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[2] ]['dataSemanal'][ $valueSemana ] : 0);
					if (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[3] ]['dataSemanal'][ $valueSemana ])) {
						$jsGraficoInd .= ', b: ' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[3] ]['dataSemanal'][ $valueSemana ]) ? $boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[3] ]['dataSemanal'][ $valueSemana ] : 0);
					}
					if (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[4] ]['dataSemanal'][ $valueSemana ])) {
						$jsGraficoInd .= ', c: ' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[4] ]['dataSemanal'][ $valueSemana ]) ? $boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[4] ]['dataSemanal'][ $valueSemana ] : 0);
					}
					if (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[5] ]['dataSemanal'][ $valueSemana ])) {
						$jsGraficoInd .= ', d: ' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[5] ]['dataSemanal'][ $valueSemana ]) ? $boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[5] ]['dataSemanal'][ $valueSemana ] : 0);
					}
					if (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[6] ]['dataSemanal'][ $valueSemana ])) {
						$jsGraficoInd .= ', e: ' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[6] ]['dataSemanal'][ $valueSemana ]) ? $boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[6] ]['dataSemanal'][ $valueSemana ] : 0);
					}
					if (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[7] ]['dataSemanal'][ $valueSemana ])) {
						$jsGraficoInd .= ', f: ' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[7] ]['dataSemanal'][ $valueSemana ]) ? $boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[7] ]['dataSemanal'][ $valueSemana ] : 0);
					}
					$jsGraficoInd .= '},';
				}
				$jsGraficoInd .= "

			],
			barColors: ['#339933', '#990000', '#006699', '#FFCC00', '#9b59b6', '#95a5a6'],
			xkey: 'x',
			ykeys: ['y', 'z', 'a'
			" . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[3] ]['dataSemanal'][ $valueSemana ]) ? ",'b'" : '') . '
			' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[4] ]['dataSemanal'][ $valueSemana ]) ? ",'c'" : '') . '
			' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[5] ]['dataSemanal'][ $valueSemana ]) ? ",'d'" : '') . '
			' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[6] ]['dataSemanal'][ $valueSemana ]) ? ",'e'" : '') . '
			' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[7] ]['dataSemanal'][ $valueSemana ]) ? ",'f'" : '') . "
			],
			labels: ['" . $boxscoreselectTitulo[ $boxscoreselect[0] ] . "', '" . $boxscoreselectTitulo[ $boxscoreselect[1] ] . "', '" . $boxscoreselectTitulo[ $boxscoreselect[2] ] . "'
			" . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[3] ]['dataSemanal'][ $valueSemana ]) ? ",'" . $boxscoreselectTitulo[ $boxscoreselect[3] ] . "'" : '') . '
			' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[4] ]['dataSemanal'][ $valueSemana ]) ? ",'" . $boxscoreselectTitulo[ $boxscoreselect[4] ] . "'" : '') . '
			' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[5] ]['dataSemanal'][ $valueSemana ]) ? ",'" . $boxscoreselectTitulo[ $boxscoreselect[5] ] . "'" : '') . '
			' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[6] ]['dataSemanal'][ $valueSemana ]) ? ",'" . $boxscoreselectTitulo[ $boxscoreselect[6] ] . "'" : '') . '
			' . (isset($boxGraficar[ $keyGraph ]['BSid'][ $boxscoreselect[7] ]['dataSemanal'][ $valueSemana ]) ? ",'" . $boxscoreselectTitulo[ $boxscoreselect[7] ] . "'" : '') . '
			],
			resize: true
			});';

				$tablaHTML = '';
				$tablaHTML .= "	<thead>
								<tr>
			<th class='text-center'>Semanas</th>";

				foreach ($auxBsSelect as $key => $value) {
					$tablaHTML .= " 		<th class='text-center'> " . $boxscoreselectTitulo[ $value ] . '</th>';
				}

				$tablaHTML .= '		</tr>
							</thead>';
				$tablaHTML .= '		<tbody>';

				foreach ($semanasData as $keySemana => $valueSemana) {
					$tablaHTML .= ' 	<tr>';
					$tablaHTML .= '		<td>' . $valueSemana . '</td>';
					foreach ($auxBsSelect as $valueBS) {
						$tablaHTML .= " 	<td class='text-center'>" . $boxGraficar[ $keyGraph ]['BSid'][ $valueBS ]['dataSemanal'][ $valueSemana ] . '</td>';
					}
					$tablaHTML .= '	</tr>';
				}

				$tablaHTML .= '		</tbody>';

				$boxGraficar[ $keyGraph ]['tablaHTML'] = $tablaHTML;
			}

			$this->semanasData  = $semanasData;
			$this->boxGraficar  = $boxGraficar;
			$this->tablaHTML    = $tablaHTML;
			$this->jsGraficoInd = $jsGraficoInd;
		}
		// @codingStandardsIgnoreEnd

	}
