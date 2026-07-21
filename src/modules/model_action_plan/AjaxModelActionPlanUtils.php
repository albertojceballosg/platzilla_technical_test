<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/model_action_plan/lib/ModelActionPlanHelper.php');
	
	global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;
	
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'flmodule', null);
	$isInstance = !empty ($_SESSION ['platInstancia']);
    $masterAdb  = AdbManager::getInstance ()->getMasterAdb ();
    
	if ($function == 'COPY_ACTION_PLAN') {
		try {
			$destination = PlatzillaUtils::purify ($_POST, 'destination');
			$initiatives = PlatzillaUtils::purify ($_POST, 'initiatives');
			$instance    = PlatzillaUtils::purify ($_POST, 'instance', null);
			if (empty ($destination)) {
				throw new Exception('Destino o plan desconocido');
			} else if (empty ($initiatives)) {
				throw new Exception('Plan de acción incompleto');
			}
            if (empty ($instance)  && $isInstance) {
                $targetAdb       = $adb;
                $GLOBALS ['adb'] = $masterAdb;
			} else if (!empty($instance) && !$isInstance) {
                $targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance [0]);
            } else {
                throw new Exception('Instancia de usuario no identificada');
            }
			
			list ($destinationId, $planId, $diagnosticId) = explode ('@', $destination);
			/** copy the action plan and destination */
			$sourceDestination = ModelActionPlanHelper::getInstance ($adb, $_SESSION['plat'])->copyModelActionPlan ($destinationId, $planId, $initiatives);
			
			/** create the action plan and destination */
            $GLOBALS ['adb'] = $targetAdb;
			$actionPlanId    = ModelActionPlanHelper::getInstance ($targetAdb, $_SESSION['plat'])->createModelActionPlan ($sourceDestination);
			if (isset ($_SESSION ['flashmessage'])) {
				$_SESSION ['flashmessage']['iserror'] = false;
				$_SESSION ['flashmessage']['message'] = '';
				unset ($_SESSION ['flashmessage']);
			}
			$idDestination   = ModelActionPlanHelper::getInstance ($targetAdb, $_SESSION['plat'])->createDestination ($sourceDestination['destination'], $planId, $actionPlanId);
            ModelActionPlanHelper::getInstance ($targetAdb, $_SESSION['plat'])->updateDiagnosticReport ($diagnosticId, $idDestination, $actionPlanId);
            $dummy = explode('_', $adb->dbName);
            $GLOBALS ['adb'] = $masterAdb;
			ModelActionPlanHelper::getInstance ($adb, $_SESSION['plat'])->saveActionPlanExported ($destinationId, $planId, $idDestination, $actionPlanId, $dummy [2]);
            $urlLocation = "{$site_URL}index.php?module=action_plan&parenttab=&action=DetailView&record={$actionPlanId}";
            $GLOBALS ['adb'] = $targetAdb;
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode(array('error' => 'OK', 'url' => $urlLocation));
		} catch (Exception $e) {
			$adb = AdbManager::getInstance ()->getMasterAdb ();
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	} else if ($function = 'GET_INSTANCE_CODE') {
		try {
			$email = PlatzillaUtils::purify ($_POST, 'email', null);
			$email = urldecode ($email);
			if (empty ($tasksData || true)) {
				throw new Exception ('Uoops! correo no encontrado');
			}
			
			$code = ModelActionPlanHelper::getInstance ($adb, $_SESSION['plat'])->getInstanceCode ($email);
			if (empty ($code)) {
				throw new Exception('Instancia no registrada');
			}
			
			$htmlOutput = array ('code' => $code[0], 'name' => $code[1]);
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	}
exit();