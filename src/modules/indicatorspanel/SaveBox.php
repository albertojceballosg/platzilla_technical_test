<?php
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('include/platzilla/Data/BoxScoreManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	
	global $currentModule, $adb, $site_URL;
	setBugSnag ($site_URL);
	
	$isInstance   = !empty ($_SESSION ['platInstancia']) ? 1 : 0;
	$app                = PlatzillaUtils::purify ($_REQUEST, 'app');
	$boxScoreId         = PlatzillaUtils::purify ($_REQUEST, 'boxscoreid');
	$boxScoreName       = PlatzillaUtils::purify ($_REQUEST, 'box_score_name');
	$from               = PlatzillaUtils::purify ($_REQUEST, 'date_from');
	$isHome             = PlatzillaUtils::purify ($_REQUEST, 'is_home', null);
	$mode               = PlatzillaUtils::purify ($_REQUEST, 'mode');
	$monthSearch        = PlatzillaUtils::purify ($_REQUEST, 'monthsearch', date ('m'));
	$operator           = PlatzillaUtils::purify ($_REQUEST, 'operator');
	$recordId           = PlatzillaUtils::purify ($_REQUEST, 'record');
	$createIndicator    = PlatzillaUtils::purify ($_REQUEST, 'create_indicator');
	$submit             = PlatzillaUtils::purify ($_REQUEST, 'submit');
	$targetMonth        = PlatzillaUtils::purify ($_REQUEST, 'target_month');
	$to                 = PlatzillaUtils::purify ($_REQUEST, 'date_to');
	$type               = PlatzillaUtils::purify ($_REQUEST, 'type');
	$update             = PlatzillaUtils::purify ($_REQUEST, 'edit_values_indicator');
	$view               = PlatzillaUtils::purify ($_REQUEST, 'viewScale', 'Month');
	
	$boxScore   = IndicatorsPanel::getInstance ($adb, $monthSearch, $boxScoreId, $from, $to);
	if ($createIndicator) {
		$boxScore->add ($_REQUEST, $monthSearch, $mode, $isInstance);
	} else {
		if ($update) {
			$valueBoxScore = $boxScore->update ($_REQUEST, $monthSearch);
			//Updating related indicators
			BoxScoreManager::getInstance ($adb)->setBlockLocked ($type);
			$row = IndicatorsPanelHelper::getBlockIdRel ($adb, $type);
			if ($boxScore->scale == 'Week') {
				$boxScoreRel = IndicatorsPanel::getInstance ($adb, $monthSearch, $row['boxscoreid'], $from, $to);
				$boxScoreRel->loadData ($row['boxscoreid'], $monthSearch, $row['type']);
				$blocks = $boxScoreRel->getBlocks ($row['boxscoreid'], $row['type']);
				$data = IndicatorsPanelHelper::updateDateRel ($blocks, $boxScoreRel, $row);
				$valueBoxScoreRel = $boxScoreRel->update ($data, $monthSearch, true);
			} else {
				$monthSearchNew = $boxScore->dates;
				for ($kk = 0; $kk < 5; $kk++) {
					if (date ('Y') == $monthSearchNew[$kk]['year']) {
						$month = $monthSearchNew[$kk]['month'] < 10 ? '0' . $monthSearchNew[$kk]['month'] : $monthSearchNew[$kk]['month'];
						$boxScoreRel = IndicatorsPanel::getInstance ($adb, $month, $row['boxscoreid'], $from, $to);
						$boxScoreRel->loadData ($row['boxscoreid'], $month, $row['type']);
						$blocks = $boxScoreRel->getBlocks ($row['boxscoreid'], $row['type']);
						$boxScoreRel = IndicatorsPanel::getInstance ($adb, $month, $row['boxscoreid'], $from, $to);
						$boxScoreRel->loadData ($row['boxscoreid'], $month, $row['type']);
						$blocks = $boxScoreRel->getBlocks ($row['boxscoreid'], $row['type']);
						$data = IndicatorsPanelHelper::updateDateRel ($blocks, $boxScoreRel, $row);
						$valuebs = $boxScoreRel->update ($data, $month, true);
					}
					$valueBoxScoreRel[] = $valuebs;
				}
			}
			if ($boxScore->scale == 'Week') {
				IndicatorsPanelHelper::updateValueIndicatorMonth ($adb, $valueBoxScore);
			} else {
				IndicatorsPanelHelper::updateValueIndicatorWeekly ($adb, $valueBoxScore, $valueBoxScoreRel);
			}
		}
	}
	if (($from) && ($to)) {
		$addUrl = "&date_from={$from}&date_to={$to}";
	} else {
		$addUrl = '';
	}

	if (!empty ($isHome)) {
		header ('Location: index.php?module=Home&action=index');
		exit;
	} else if ($app == 'all') {
		header ("Location: index.php?module={$currentModule}&action=allAppDetailView&monthsearch={$monthSearch}&app={$app}&viewScale={$view}");
		exit;
	} else {
		header ("Location: index.php?module={$currentModule}&action=index&monthsearch={$monthSearch}&app={$app}&viewScale={$view}");
		exit;
	}
