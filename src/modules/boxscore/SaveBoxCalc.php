<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $currentModule;

	if (file_exists ($_SESSION ['plat'] . "/modules/{$currentModule}/{$currentModule}.php")) {
		checkFileAccessForInclusion ($_SESSION ['plat'] . "/modules/{$currentModule}/{$currentModule}.php");
		require_once ($_SESSION ['plat'] . "/modules/{$currentModule}/{$currentModule}.php");
	} else {
		checkFileAccessForInclusion ("modules/{$currentModule}/{$currentModule}.php");
		require_once ("modules/{$currentModule}/{$currentModule}.php");
	}

	$boxScoreId = isset ($_REQUEST ['boxscoreid']) ? vtlib_purify ($_REQUEST ['boxscoreid']) : null;
	$boxScoreArray = isset ($_REQUEST ['boxscoreArray']) ? vtlib_purify ($_REQUEST ['boxscoreArray']) : null;
	$mode = isset ($_REQUEST ['modeView']) ? vtlib_purify ($_REQUEST ['modeView']) : null;
	$operation = isset ($_REQUEST ['operation']) ? vtlib_purify ($_REQUEST ['operation']) : null;
	$operationId = isset ($_REQUEST ['operationid']) ? vtlib_purify ($_REQUEST ['operationid']) : null;

	if (!empty ($boxScoreId)) {
		$cbs = count ($boxScoreArray);
		$cop = count ($operation);

		$bsdata = '';
		$bsop = '';
		$calculo = '';
		$elements = '';
		$operators = '';
		for ($i = 0; $i < $cbs; $i++) {
			$bsdata = $boxScoreArray [ $i ];
			$elements .= "{$bsdata},";
			if ($i < $cop) {
				$bsop = $operation[ $i ];
				$operators .= "{$bsop},";
			} else {
				$bsop = '';
			}

			$bsI = new box_score();
			$namElements = array ();
			$namElements = $bsI->getBasicDataByBoxScoreDataIds ($boxScoreId, $bsdata);
			$calculo .= "{$namElements [ $bsdata ]} {$bsop} ";
		}
		$elements = trim ($elements, ',');
		$operators = trim ($operators, ',');

		$bsI = new box_score();

		if ($mode == 'edit') {
			if (!empty ($operationId)) {
				$bsI->deleteCalculation ($operationId);
			}
		}

		$bscal = $bsI->saveCalculation ($boxScoreId, html_entity_decode ($calculo), $elements, $operators);
	}

	header ("Location: index.php?module=boxscore&action=DetailView&record={$boxScoreId}");
