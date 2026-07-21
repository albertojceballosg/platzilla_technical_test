<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/okrs/lib/OkrHelperUtils.php');
	
	global $adb, $currentModule, $mod_strings, $site_URL;
	
	setBugSnag ($site_URL);
	
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	
	if ($function == 'OKRS_CARD') {
		try {
			$companyType  = PlatzillaUtils::purify ($_POST, 'companytype');
			$companyPhase = PlatzillaUtils::purify ($_POST, 'companyphase');
			if(empty ($companyType) || empty($companyPhase)) {
				throw new Exception ('información no completa');
			}
			$data = array(
				'companyphase' => $companyPhase,
				'companytype'  => $companyType,
				'onboarding'   => 'YES',
				'status'       => 'ENABLED',
			);
			
			$okrClass   = OkrHelperUtils::getInstance ();
			$objectives = $okrClass->getObjectivesByWhere ($data, 'ORDER BY companyarea ASC');
			
			if (!empty ($objectives)) {
				$arrayObjectives = array ();
				$arrayAreas      = array ();
				$objectivesIds   = array ();
				$arrayKR         = array ();
				foreach ($objectives as $objective) {
					$objectivesIds [] = $objective->getId ();
					$arrayObjectives[ $objective->getId () ] = array (
						'how_to_do' => $objective->getHowToDo (),
						'frequency' => $objective->getFrequency (),
					);
					if (!in_array ($objective->getCompanyArea (), $arrayAreas)) {
						$arrayAreas [] = $objective->getCompanyArea ();
					}
					foreach ($objective->getKeyResults () as $keyResul) {
						$arrayKR[ $keyResul->getId () ] = array (
							'description' => $keyResul->getDescription (),
							'value'       => $keyResul->getGoalValue (),
						);
					}
				}
				
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('COMPANY_AREAS', isset ($arrayAreas) ? $arrayAreas : null);
			$smarty->assign ('DATA_OBJECTIVES', isset ($arrayObjectives) ? json_encode ($arrayObjectives) : null);
			$smarty->assign ('DATA_KEY_RESULTS', isset($arrayKR) ? json_encode ($arrayKR) : null);
			$smarty->assign ('FREQUENCY', OkrsInterface::OKRS_FREQUENCY);
			$smarty->assign ('OBJECTIVE_IDS', isset ($objectivesIds) ? $objectivesIds : null);
			$smarty->assign ('KEY_RESULTS', $okrClass->fetchKeyResults ());
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('OBJECTIVES', $objectives);
			
			
			$htmlOutput = $smarty->fetch ('modules/Okrs/Wizard/WizardOkrsV2.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else {
		try {
			throw new Exception ('Uoops! Acción no identificada');
			
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
	exit();
