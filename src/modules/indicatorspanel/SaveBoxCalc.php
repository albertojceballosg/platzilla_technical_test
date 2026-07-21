<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	// Agregado por EB para integrar BUGSNAG - 20200527
	global $site_URL;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
	$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
	$bugsnag->setAppVersion('1.0.0');
	if ($site_URL == 'https://apphome.platzillatest.com/') {
		$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
	} else if ($site_URL == 'https://app.platzilla.com/') {
		$bugsnag->setReleaseStage('https://app.platzilla.com/');
	} else {
		$bugsnag->setReleaseStage($site_URL);
	}
	$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
	// Agregado por EB para integrar BUGSNAG - 20200527

	global $adb, $currentModule;

	$boxScoreId    = PlatzillaUtils::purify ($_REQUEST, 'boxscoreid');
	$boxScoreArray = PlatzillaUtils::purify ($_REQUEST, 'boxscoreArray');
	$mode          = PlatzillaUtils::purify ($_REQUEST, 'modeView');
	$operation     = PlatzillaUtils::purify ($_REQUEST, 'operation');
	$operationId   = PlatzillaUtils::purify ($_REQUEST, 'operationid');
	$app           = PlatzillaUtils::purify ($_REQUEST, 'app');
	$monthSearch   = PlatzillaUtils::purify ($_REQUEST, 'monthsearch', date ('m'));
	$from          = PlatzillaUtils::purify ($_REQUEST, 'date_from');
	$to            = PlatzillaUtils::purify ($_REQUEST, 'date_to');
	$type          = PlatzillaUtils::purify ($_REQUEST, 'type');
	$view          = PlatzillaUtils::purify ($_REQUEST, 'viewScale', 'Month');

	if (!empty ($boxScoreId)) {
		$cbs = count ($boxScoreArray);
		$cop = count ($operation);

		$bsdata    = '';
		$bsop      = '';
		$calcule   = '';
		$elements  = '';
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

			$boxScore    = IndicatorsPanel::getInstance ($adb, $monthSearch, $boxScoreId, $from, $to);
			$namElements = array ();
			$namElements = $boxScore->getBasicDataByBoxScoreDataIds ($boxScoreId, $bsdata, $type);
			$calcule .= "{$namElements [ $bsdata ]} {$bsop}";
		}
		$elements  = trim ($elements, ',');
		$operators = trim ($operators, ',');

		$boxScore = IndicatorsPanel::getInstance ($adb, $monthSearch, $boxScoreId, $from, $to);

		if (($mode == 'edit') && (!empty ($operationId))) {
			$boxScore->deleteCalculation ($operationId);
		}

		$bscal = $boxScore->saveCalculation ($boxScoreId, html_entity_decode ($calcule), $elements, $operators, $type);
	}

	if ($app == 'all') {
		header ("Location: index.php?module={$currentModule}&action=allAppDetailView&monthsearch={$monthSearch}&app={$app}&viewScale={$view}");
	} else {
		header ("Location: index.php?module={$currentModule}&action=index&monthsearch={$monthSearch}&app={$app}&viewScale={$view}");
	}
