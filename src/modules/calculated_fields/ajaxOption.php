<?php
	require_once ('include/utils/utils.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('vtlib/Vtiger/Utils.php');

	global $adb;

	$platform            = $_SESSION ['plat'];
	$objCalculatedFields = new CalculatedFieldsUtils ($adb, $platform);
	$method              = SettingsUtils::purify ($_REQUEST, 'method');
	$cod                 = SettingsUtils::purify ($_REQUEST, 'id');
	$moduleName          = SettingsUtils::purify ($_REQUEST, 'modulename');

	try {
		if ($method != null) {
			switch ($method) {
				case 'field':
					$response = $objCalculatedFields->delCalculatedFields ($cod);
					echo json_encode (array('cod' => $cod, 'method' => $method));
					break;
				case 'system':
					$response = $objCalculatedFields->delCalculatedSystem ($cod);
					echo json_encode (array('cod' => $cod, 'method' => $method, 'error' => $response));
					break;
				case 'status_system':
					$response = $objCalculatedFields->setStatusToCalculatedSystem ($cod);
					if (is_numeric ($response)) {
						if ($response == 1) {
							echo json_encode (array ('cod' => $cod, 'error' => $response, 'btnImage' => 'ban'));
						} else {
							echo json_encode (array ('cod' => $cod, 'error' => $response, 'btnImage' => 'check'));
						}
					} else {
						echo json_encode (array ('cod' => $cod, 'error' => $response, 'btnImage' => null));
					}
					break;
				case 'get_fields':
					if ($moduleName) {
						$modules[]      = $moduleName;
						$relatedModules = $objCalculatedFields->getRelatedModulesByName($moduleName);
						foreach ($relatedModules as $relation) {
							$modules [] = $relation ['name'];
						}

						$condition = "f.typeofdata LIKE 'N%' AND";
						echo json_encode ($objCalculatedFields->getColumnsByModule ($modules, $condition));
					} else {
						echo json_encode (array ('error' => 'No ha seleccionado el módulo'));
					}
					break;

				default:
					echo '';
					break;
			}
		} else {
			echo '';
		}
	} catch (Exception $e) {
		echo '';
	}
