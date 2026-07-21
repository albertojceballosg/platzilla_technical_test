<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/graficosgenerales/lib/GraphUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200311
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
	// Agregado por EB para integrar BUGSNAG - 20200311

	global $adb, $current_user, $mod_strings;

	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'fld_module');

	if ((empty ($function)) || (empty ($moduleName))) {
		exit ();
	}

	if ($function == 'getColumns') {
		echo json_encode (GraphUtils::getGraphicalColumnsData ($adb, $moduleName));
	} else if ($function == 'getNumericColumns') {
		echo json_encode (GraphUtils::getNumericColumns ($adb, $moduleName));
	} else if ($function == 'getGridNumericColumns') {
		$row = array (
			'fieldname'  => PlatzillaUtils::purify ($_REQUEST, 'fieldname'),
			'fieldlabel' => PlatzillaUtils::purify ($_REQUEST, 'fieldlabel'),
		);
		$columns = array ();
		GraphUtils::getGridFields ($adb, $moduleName, $row, $columns, 'numeric');
		echo json_encode ($columns);
	} else if ($function == 'getGridNumericColumns') {
		$row = array (
			'fieldname'  => PlatzillaUtils::purify ($_REQUEST, 'fieldname'),
			'fieldlabel' => PlatzillaUtils::purify ($_REQUEST, 'fieldlabel'),
		);
		$columns = array ();
		GraphUtils::getGridFields ($adb, $moduleName, $row, $columns, 'numeric');
		echo json_encode ($columns);
	} else if ($function == 'updateFavorite') {
		$idGraphic = intval (PlatzillaUtils::purify ($_REQUEST, 'graphicId'));
		$faClass   = 'fa-star';
		$title     = 'Ya no es mi favorito';
		$gm        = GraphicManager::getInstance ($adb);
		$myGraphic = $gm->fetchAllFavoriteGraphics ($current_user->id);
		if (count ($myGraphic)) {
			if (in_array ($idGraphic, array_column ($myGraphic,'graficoid'))) {
				$gm->delFavoriteGraphic(intval ($current_user->id), $idGraphic);
				$faClass = 'fa-star-o';
				$title   = 'Convertir en mi favorito';
			} else {
				$isFavorite = $gm->saveFavoriteGraphic (intval ($current_user->id), $idGraphic);
				if (!$isFavorite) {
					$faClass = 'fa-star';
					$title   = 'Ya no es mi favorito';
				}
			}
		} else {
			$isFavorite = $gm->saveFavoriteGraphic (intval ($current_user->id), $idGraphic);
			if (!$isFavorite) {
				$faClass = 'fa-star';
				$title   = 'Ya no es mi favorito';
			}
		}
		$faClass = "<span id='fa-{$idGraphic}' class='fa {$faClass}'></span>";
		echo json_encode (array ('title' => $title, 'faclass' => $faClass));
	} else if ($function == 'getChartProperties') {
		$graphicName     = PlatzillaUtils::purify ($_REQUEST, 'graphic');
		$isInstance      = !empty ($_SESSION ['platInstancia']);
		$chartProperties = GraphicManager::getInstance ($adb)->fetchChartOption ($graphicName, $isInstance);
		if (!empty ($chartProperties)) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('PROPERTIES', $chartProperties);
			echo $smarty->fetch ('modules/graficosgenerales/GraphicProperties.tpl');
		}
	} else if ($function == 'getModulerel') {
		$modules = PlatzillaUtils::purify ($_REQUEST, 'relatedModules');
		$modules = array_values (array_unique ($modules));
		$result  = array ();
		if (count ($modules) > 2) {
			$k            = 1;
			$totalModules = count ($modules);
			foreach ($modules as $module) {
				for ($i = $k; $i < $totalModules; $i++) {
					$result [] = GraphUtils::getModulesRel ($adb, array ($module, $modules [$i]));
				}
				$k++;
			}
		} else {
			$result [] = GraphUtils::getModulesRel ($adb, $modules);
		}

		if (empty ($result [0])) {
			echo json_encode (array('error' => 'No hay relación ente los módulos'));
		} else {
			echo json_encode ($result);
		}
	}
	exit ();
