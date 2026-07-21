<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $adb, $currentModule;

	checkFileAccessForInclusion ("modules/$currentModule/$currentModule.php");
	require_once ("modules/$currentModule/$currentModule.php");

	$mode            = isset ($_REQUEST ['mode']) ? vtlib_purify ($_REQUEST ['mode']) : null;
	$record          = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : null;
	$assignType      = isset ($_REQUEST ['assigntype']) ? vtlib_purify ($_REQUEST ['assigntype']) : null;
	$assignedUserId  = isset ($_REQUEST ['assigned_user_id']) ? vtlib_purify ($_REQUEST ['assigned_user_id']) : null;
	$assignedGroupId = isset ($_REQUEST ['assigned_group_id']) ? vtlib_purify ($_REQUEST ['assigned_group_id']) : null;
	$search          = isset ($_REQUEST ['search_url']) ? vtlib_purify ($_REQUEST ['search_url']) : '';
	$returnAction    = (isset ($_REQUEST ['return_action'])) && ($_REQUEST ['return_action'] != '') ? vtlib_purify ($_REQUEST ['return_action']) : 'DetailView';
	$returnModule    = (isset ($_REQUEST ['return_module'])) && ($_REQUEST ['return_module'] != '') ? vtlib_purify ($_REQUEST ['return_module']) : $currentModule;
	$returnId        = (isset ($_REQUEST ['return_id'])) && ($_REQUEST ['return_id'] != '') ? vtlib_purify ($_REQUEST ['return_id']) : '';
	$urlPlatDb       = (isset ($_REQUEST ['platdb'])) && (!empty ($_REQUEST ['platdb'])) ? '&platdb=' . vtlib_purify ($_REQUEST ['platdb']) : '';
	$pageNumber      = isset ($_REQUEST ['pagenumber']) ? vtlib_purify ($_REQUEST ['pagenumber']) : 1;
	$parentTab       = getParentTab ();

	/** @var CRMEntity|stdClass $focus */
	$focus     = new $currentModule ();
	$modeEvent = '';
	setObjectValuesFromRequest ($focus);
	if ($mode) {
		$focus->mode = $mode;
	}
	if ($record) {
		$focus->id = $record;
		$modeEvent = 'save';
	}
	if ($assignType == 'U') {
		$focus->column_fields ['assigned_user_id'] = $assignedUserId;
	} else if ($assignType == 'T') {
		$focus->column_fields ['assigned_user_id'] = $assignedGroupId;
	}
	$focus->save ($currentModule);

	if (is_array ($_REQUEST ['respuesta'])) {
		$adb->pquery ('DELETE FROM vtiger_formacion_preguntas_respuestas WHERE formacion_preguntasid=?', array ($focus->id));
	}
	if ((($_REQUEST ['tipo_de_pregunta'] == 'Multiple Choice') || ($_REQUEST ['tipo_de_pregunta'] == 'Respuesta Múltiple')) && (is_array ($_REQUEST ['respuesta']))) {
		$arguments = array ();
		foreach ($_REQUEST ['respuesta'] as $k => $v) {
			if ($v != '') {
				$corr         = $_REQUEST ['es_correcta'][ $k ] == 'on' ? 1 : 0;
				$arguments [] = array ($focus->id, $k, trim ($v), $corr, $_REQUEST['valor_correcta'][ $k ], $_REQUEST['seleccion'][ $k ]);
			}
		}
		if (!empty ($arguments)) {
			foreach ($arguments as $argument) {
				$adb->pquery (
					'INSERT INTO vtiger_formacion_preguntas_respuestas (formacion_preguntasid, orden, respuesta, correcta, porciento_valor, seleccion) VALUES (?, ?, ?, ?, ?, ?)',
					$argument
				);
			}
		}
	} else if (($_REQUEST['tipo_de_pregunta'] == 'Lista') && (is_array ($_REQUEST ['respuesta']))) {
		$arguments = array ();
		foreach ($_REQUEST ['respuesta'] as $k => $v) {
			if ($v != '') {
				$corr         = $_REQUEST ['es_correctaLista'][ $k ] == 'on' ? 1 : 0;
				$arguments [] = array ($focus->id, $k, trim ($v), $corr, $_REQUEST ['valor_correctaLista'][ $k ], $_REQUEST ['seleccion'][ $k ]);
			}
		}
		if (!empty ($arguments)) {
			foreach ($arguments as $argument) {
				$adb->pquery (
					'INSERT INTO vtiger_formacion_preguntas_respuestas (formacion_preguntasid, orden, respuesta, correcta, porciento_valor, seleccion) VALUES (?, ?, ?, ?, ?, ?)',
					$argument
				);
			}
		}
	} else if (($_REQUEST ['tipo_de_pregunta'] == 'Verdadero/Falso') && (is_array ($_REQUEST['vf_respuesta']))) {
		$adb->pquery ('DELETE FROM vtiger_formacion_preguntas_respuestas WHERE formacion_preguntasid=?', array ($focus->id));
		$arguments = array ();
		foreach ($_REQUEST ['vf_respuesta'] as $k => $v) {
			if ($v != '') {
				$corr         = $_REQUEST ['vf_es_correcta'][ $k ] == 'on' ? 1 : 0;
				$arguments [] = array ($focus->id, $k, trim ($v), $corr, $_REQUEST ['valor_correcta'][ $k ]);
			}
		}
		if (!empty ($arguments)) {
			foreach ($arguments as $argument) {
				$adb->pquery (
					'INSERT INTO vtiger_formacion_preguntas_respuestas (formacion_preguntasid, orden, respuesta, correcta, porciento_valor) VALUES (?, ?, ?, ?, ?)',
					$argument
				);
			}
		}
	}

	if ((!$returnId) && ($mode == 'create')) {
		$returnId  = $focus->id;
		$modeEvent = 'save';
	}

	header ("Location: index.php?module=$returnModule&action=$returnAction&record=$returnId&parenttab=$parentTab&mode={$modeEvent}&start={$pageNumber}{$search}{$urlPlatDb}");
