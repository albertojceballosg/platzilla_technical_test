<?php
	require_once ('data/CRMEntity.php');
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');

	global $adb, $app_strings, $current_user, $currentModule, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);


	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'formodule');
	$record     = PlatzillaUtils::purify ($_REQUEST, 'record');
	$smarty     = new vtigerCRM_Smarty ();
	if ($function == 'ITERATIONS') {
		try {
			$boxName    = PlatzillaUtils::purify ($_REQUEST, 'boxtype');
			if (empty ($boxName)) {
				throw new Exception ('Cuadricula no identificada!');
			}
			$gridBoxes = GridViewHelper::fetchGridViewByType ($adb, $boxName, $moduleName, $record, $_SESSION ['plat'], $current_user);
			if (!empty($gridBoxes)) {
				$feedbackIds = array ();
				foreach ($gridBoxes->getContent () as $gridBox) {
					if (!method_exists($gridBox,'getFeedbacks') || empty($gridBox->getFeedbacks())) {
						continue;
					}
					foreach ($gridBox->getFeedbacks() as $feedback) {
						$feedbackIds[] = $feedback->getId();
					}
				}
			}
			
			$orphansFeedbacks = ActivityFeedbackManager::getInstance ($adb)->fetchActivityFeedback ($record, $feedbackIds);
			
			$smarty->assign('GRID_BOX', $gridBoxes);
			$smarty->assign ('ID', $record);
			$smarty->assign('IS_INSTANCE', !empty ($_SESSION ['platInstancia']) ? true : false);
			$smarty->assign('MOD', $mod_strings);
			$smarty->assign ('MODULE', $moduleName);
			$smarty->assign ('ORPHAN_FEEDBACKS', isset ($feedbackIds) ? ActivityFeedbackManager::getInstance ($adb)->fetchActivityFeedback ($record, $feedbackIds) : null);
			$smarty->assign ('RECORD', $record);
			$smarty->assign ('NUMBERING_FORMAT', $current_user->column_fields['numbering_format']);
			$smarty->assign ('NUMBERING_HELPER', NumberHelper::getInstance($adb));
		} catch (Exception $e) {
			$smarty->assign('GRID_BOX', null);
			$smarty->assign('MESSAGE', $e->getMessage());
		}
		$smarty->display('modules/grid_view/BoxDetailCardView.tpl');
	} else if ($function == 'JOB_TITLE') {
		    try {
			   if (empty ($record)) {
				   throw new Exception ('No se ha especificado el registro!');
			   } else if (empty ($moduleName)) {
				   throw new Exception ('No se ha especificado el modulo!');
			   }
			   $entity = CRMEntity::getInstance ($moduleName);
			   $entity->id   = $record;
			   $entity->mode = 'edit';
			   $entity->retrieve_entity_info ($record, $moduleName);
			   
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode(array('error' => 'OK', 'html' => '<b>Trabajo: </b>' . $entity->column_fields['titulo']));
			} catch (Exception $e) {
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode(array('error' => $e->getMessage()));
			}
	} else if ($function == 'RELATED_INFO') {
		try {
			$relatedList    = explode ('@', PlatzillaUtils::purify ($_REQUEST, 'relatedlist'));
			if (empty ($relatedList)) {
				throw new Exception ('Lista relacionada no identificada!');
			}
			$relatedArray[ urldecode ($relatedList[0]) ] = array(
				'related_tabid' => $relatedList [1],
				'relationId'    => $relatedList [2],
				'actions'       => $relatedList [3],
			);
			$entity = CRMEntity::getInstance ($moduleName);
			if ($record != '') {
				$entity->id   = $record;
				$entity->mode = 'edit';
				$entity->retrieve_entity_info ($record, $moduleName);
			}
			$currentModule = $moduleName;
			$relatedListCard = GridViewHelper::fetchRelatedList (
				array (
					'adb'           => $adb,
					'relatedList'   => $relatedArray,
					'recordId'      => $record,
					'currentModule' => $moduleName,
					'entity'        => $entity,
					'resetCookie'   => ($_SESSION ['rlvs'][ $moduleName ][ $relatedList[2] ]['currentRecord'] != $record) ? true : false,
					'app_strings'   => $app_strings,
					'mod_strings'   => $mod_strings,
					'theme'         => $theme,
					'isModal'       => true,
				)
			);

			$_SESSION ['rlvs'][ $moduleName ][ $relatedList[2] ]['currentRecord'] = $record;
			$smarty->assign ('RELATED_LIST_CARD', $relatedListCard);
			$smarty->assign ('HiDDEN_BUTTON', 'yes');
			$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']) ? true : false);
			$smarty->assign ('MOD', $mod_strings);
		} catch (Exception $e) {
			$smarty->assign ('RELATED_LIST_CARD', null);
			$smarty->assign('MESSAGE', $e->getMessage());
		}
		$smarty->display('modules/grid_view/RelatedListCardView.tpl');
	}
