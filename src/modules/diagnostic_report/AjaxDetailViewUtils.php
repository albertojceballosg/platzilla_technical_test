<?php
    require_once ('Smarty_setup.php');
    require_once ('include/utils/AdbManager.class.php');
    require_once ('include/utils/PlatzillaUtils.class.php');
    require_once ('include/utils/utils.php');
    require_once ('modules/diagnostic_report/lib/DiagnosticReportHelper.php');
    require_once ('modules/grid_view/lib/GridViewHelper.class.php');
    require_once ('modules/model_action_plan/lib/ModelActionPlanHelper.php');
    
    global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;

    setBugSnag ($site_URL);
    $current_module = PlatzillaUtils::purify ($_REQUEST, 'module');
    $function       = PlatzillaUtils::purify ($_REQUEST, 'function');
    $moduleName     = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
    $isInstance     = !empty ($_SESSION ['platInstancia']);
    $masterAdb      = AdbManager::getInstance ()->getMasterAdb ();

    if ($function == 'VIEW-ACTION-PLAN-TAB') {
        try {
            $record = PlatzillaUtils::purify($_POST, 'record', null);
            $idDestination = PlatzillaUtils::purify($_POST, 'idDestination', null);
            if (empty ($record)) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            $entity       = CRMEntity::getInstance ('diagnostic_report');
            $entity->id   = $record;
            $entity->mode = 'edit';
            $entity->retrieve_entity_info ($record, 'diagnostic_report');
            if (count ($entity->column_fields) && !empty ($entity->column_fields['action_plan'])) {
                $idDestination          = (empty($idDestination)) ? $entity->column_fields['destination'] : $idDestination;
                $actionPlanEntity       = CRMEntity::getInstance ('action_plan');
                $actionPlanEntity->id   = intval ($entity->column_fields['destination']);
                $actionPlanEntity->mode = 'edit';
                $actionPlanEntity->retrieve_entity_info (intval ($entity->column_fields['action_plan']), 'action_plan');
            }
            if (empty ($idDestination) && !isset($actionPlanEntity)) {
                throw new Exception ('¡Uooops! No ha seleccionado un destino');
            }
            if (!empty ($idDestination)) {
                $targetAdb       = $adb;
                $GLOBALS ['adb'] = $masterAdb;
                $model = ModelActionPlanHelper::getInstance($masterAdb, $_SESSION['plat'])->fetchModelByDestinationId($idDestination);
                $GLOBALS ['adb'] = $targetAdb;
                if (empty ($model)) {
                    $model = ModelActionPlanHelper::getInstance($adb, $_SESSION['plat'])->fetchModelByDestinationId($idDestination);
                    if (empty ($model)) {
                        throw new Exception('Uoops! el destino a sido eliminado');
                    }
                }
            }
            $smarty = new vtigerCRM_Smarty ();
            $smarty->assign ('APP', $app_strings);
            $smarty->assign ('ACTION_PLAN', (isset($actionPlanEntity)) ? $actionPlanEntity->column_fields : null);
            $smarty->assign ('DESTINATION_ID', $idDestination);
            $smarty->assign ('IS_ADMIN', is_admin ($current_user));
            $smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
            $smarty->assign ('MOD', $mod_strings);
            $smarty->assign ('MODEL', $model);
            $smarty->assign ('RECORD', $record);
            $htmlOutput = $smarty->fetch ('modules/diagnostic_report/DetailViewActionPlan.tpl');
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
    } else if ($function == 'VIEW-DESTINATION-TAB') {
        try {
            $record       = PlatzillaUtils::purify($_POST, 'record', null);
            $idDetailView = PlatzillaUtils::purify ($_POST, 'tabid', null);
            if (empty ($record)) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            $entity       = CRMEntity::getInstance ('diagnostic_report');
            $entity->id   = $record;
            $entity->mode = 'edit';
            $entity->retrieve_entity_info ($record, 'diagnostic_report');
            if (count ($entity->column_fields)) {
                $destinations = DiagnosticReportHelper::fetchAvailableDestinations ($masterAdb, $entity->column_fields);
                if (empty ($destinations)) {
                    $destinations = DiagnosticReportHelper::fetchAvailableDestinations ($adb, $entity->column_fields);
                }
                if (!empty ($entity->column_fields['destination'])) {
                    $businessDestinationEntity       = CRMEntity::getInstance ('business_destination');
                    $businessDestinationEntity->id   = intval ($entity->column_fields['destination']);
                    $businessDestinationEntity->mode = 'edit';
                    $businessDestinationEntity->retrieve_entity_info (intval ($entity->column_fields['destination']), 'business_destination');
                }
            }
            
            $smarty = new vtigerCRM_Smarty ();
            $smarty->assign ('AVAILABLE_DESTINATIONS', isset ($destinations) ? $destinations : null);
            $smarty->assign ('BUSINESS_DESTINATION', (isset ($businessDestinationEntity)) ? $businessDestinationEntity->column_fields : null);
            $smarty->assign ('DIAGNOSTIC_REPORT', $entity->column_fields);
            $smarty->assign ('GRID_VIEW', GridViewHelper::fetchGridViewByModule ($adb, $current_module, $record, $_SESSION ['plat'], $current_user));
            $smarty->assign ('ID_TAB', $idDetailView);
            $smarty->assign ('MODULE', $current_module);
            $smarty->assign ('RECORD', $record);
            $smarty->assign ('TOTAL_DESTINATION', isset ($destinations) ? (count ($destinations) > 1 ? count ($destinations) : 0) : 0);
            $htmlOutput = $smarty->fetch ('modules/diagnostic_report/DetailViewDestinations.tpl');
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
    }