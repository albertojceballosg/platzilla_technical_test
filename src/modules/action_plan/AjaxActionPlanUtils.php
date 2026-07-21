<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Managers/TableFieldManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/action_plan/handlers/KeyObjectiveResult.class.php');
	
	global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;
	
	setBugSnag ($site_URL);
	
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	$masterAdb  = AdbManager::getInstance ()->getMasterAdb ();
	
	if ($function == 'OKR-VIEW') {
		try {
			$record = PlatzillaUtils::purify ($_POST,   'record', null);
			if (empty ($record)) {
				throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
			}
			$entity =  CRMEntity::getInstance ($moduleName);
			$entity->retrieve_entity_info ($record, $moduleName);
			
			$keyObjectiveResult = KeyObjectiveResult::getInstance ($adb)->fetchKeyObjectiveResult ($record);
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('OKR',$keyObjectiveResult);
			$smarty->assign ('PLAN', $entity->column_fields);
			$smarty->assign ('PLAN_ID',$record);
			
			$htmlOutput = $smarty->fetch ("modules/action_plan/OKRTabView.tpl");
			//var_dump ($htmlOutput);exit();
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage()));
		}
	} else if ($function == 'PROGRESS-PLAN-VIEW') {
		try {
			$record = PlatzillaUtils::purify ($_POST,   'record', null);
			if (empty ($record)) {
				throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
			}
			
			$entity =  CRMEntity::getInstance ($moduleName);
			$entity->retrieve_entity_info ($record, $moduleName, null, true);
			if (
				!isset ($entity->column_fields['plan_initiatives']) ||
				empty ($entity->column_fields['plan_initiatives'])
			) {
				throw new Exception ('Este plan no contiene iniciativas');
			} else if (
				isset($entity->column_fields['plan_initiatives']['summaryrow']) &&
				!empty($entity->column_fields['plan_initiatives']['summaryrow'])
			) {
				$summaryRow = json_decode ($entity->column_fields['plan_initiatives']['summaryrow'][0],true);
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('PLAN', $entity->column_fields);
			$smarty->assign ('SUMMARY_ROW', isset ($summaryRow) ? $summaryRow : null);
			$htmlOutput = $smarty->fetch ('modules/action_plan/ProgressPlanView.tpl');
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
	} else if ($function == 'RELATED-KR') {
		try {
			$crmId   = PlatzillaUtils::purify ($_REQUEST, 'record');
			$idTable = PlatzillaUtils::purify ($_REQUEST, 'idtable');
			
			if(empty ($crmId)) {
				throw new Exception ('Obtivo no identificado');
			} else if (empty ($idTable)) {
				throw new Exception ('Referencia de tabla no encontrada');
			}
			$entity =  CRMEntity::getInstance ($moduleName);
			$entity->retrieve_entity_info ($crmId, $moduleName);
			$krResult = $entity->column_fields['kr_achieve_objective'];
			if (empty ($krResult)) {
				throw new Exception ('No se encontraron KR');
			} else if (!count($krResult)) {
				throw new Exception ('No se encontraron KR');
			}
			$summaryRow = json_decode ($krResult['summaryrow'][0],true);
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('KR',$krResult);
			$smarty->assign ('ID_TABLE',$idTable);
			$smarty->assign ('CRM_ID',$crmId);
			$smarty->assign ('TOTAL_ROW', (count($krResult['business_objectivetfid'])) - 1);
			
			$htmlOutput = $smarty->fetch ("modules/action_plan/KeyResultEditView.tpl");
			header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode(array('error' => 'OK', 'goal_progress' => $summaryRow['goal_progress_pc'], 'html' => $htmlOutput));
		} catch (Exception $e) {
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode (array('error' => $e->getMessage()));
		}
	} else if ($function == 'STRATEGIES-INITIATIVES-VIEW') {
		try {
			$record = PlatzillaUtils::purify ($_POST,   'record', null);
			if (empty ($record)) {
				throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
			}
			$columnName     = 'directive';
			$tableFieldName = 'plan_directives';
			$entity         =  CRMEntity::getInstance ($moduleName);
			$entity->retrieve_entity_info ($record, $moduleName);
			
			if (
				(!isset ($entity->column_fields['plan_directives']) ||
				!isset ($entity->column_fields['plan_directives'])) &&
				(empty ($entity->column_fields['plan_initiatives'])  ||
				empty ($entity->column_fields['plan_initiatives']))
			) {
				throw new Exception ('Este plan no contiene iniciativas ni estrategias');
			}
			$guidelineTable = TableFieldManager::getInstance ($adb)->fetchTableFieldConfig ($tableFieldName, $moduleName);
			if (!empty ($guidelineTable)) {
				foreach ($guidelineTable as $guidelineRow) {
					if ($guidelineRow->getFieldName() == $columnName) {
						$actions = $guidelineRow->getActionArray();
						break;
					}
				}
			}
			
			$guideline       = array_merge (array ('Directriz'), $actions['list']['option']);
			$guideline       = array_combine ($guideline, array_fill (0, count ($guideline), 0));
			$totalDirectives = count ($entity->column_fields['plan_directives']['directive']);
			
			for ($k = 0; $k< $totalDirectives; $k++) {
				$directivePercentage = $entity->column_fields['plan_directives']['percentage_directive'][$k];
				$directivePercentage = (!empty($directivePercentage) && is_numeric ($directivePercentage)) ? $directivePercentage : 0.00;
				$guideline[ $entity->column_fields['plan_directives']['directive'][$k] ] += floatval($directivePercentage);
			}
			$guideline ['Directriz'] = '% aplicación';
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('DATA_TABLE', json_encode ($guideline));
			$smarty->assign ('PLAN', $entity->column_fields);
			$htmlOutput = $smarty->fetch ('modules/action_plan/StrategiesInitiativesView.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	} else if ($function == 'SUMMARY-PLAN-VIEW') {
		try {
			$record = PlatzillaUtils::purify ($_POST,   'record', null);
			if (empty ($record)) {
				throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
			}
			$entity =  CRMEntity::getInstance ($moduleName);
			$entity->retrieve_entity_info ($record, $moduleName);
			
			if (!empty ($entity->column_fields['informative_video'])) {
				if (strpos ($entity->column_fields['informative_video'],'vimeo.com') !== false) {
					$entity->column_fields ['video_type'] = 'VIMEO';
				} else if (strpos ($entity->column_fields['informative_video'],'youtube.com') !== false) {
					$entity->column_fields ['video_type'] = 'YOUTUBE';
				} else {
					$entity->column_fields ['video_type'] = null;
				}
			} else {
				$entity->column_fields ['video_type'] = null;
			}
			if (empty($entity->column_fields['video_type']) && !empty($entity->column_fields['image_action_plan'])) {
				
				$imageActionPlan = AttachmentsUtils::fetchEntityAttachment ($adb, $record, $entity->column_fields['image_action_plan']);
			}
			
			$actionPlan = $entity->column_fields;
			if (!empty ($entity->column_fields ['business_destination'])) {
				$idDestination = $entity->column_fields ['business_destination'];
				unset ($entity);
				$entity =  CRMEntity::getInstance ('business_destination');
				$entity->retrieve_entity_info ($idDestination, 'business_destination');
				$businessDestination = $entity->column_fields;
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('DESTINATION', isset ($businessDestination) ? $businessDestination : null);
			$smarty->assign ('IMAGE_ACTION_PLAN', $imageActionPlan);
			$smarty->assign ('PLAN', $actionPlan);
			$htmlOutput = $smarty->fetch ('modules/action_plan/SummaryPlanView.tpl');
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	}
	exit();
