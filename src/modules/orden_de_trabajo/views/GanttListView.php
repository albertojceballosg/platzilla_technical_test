<?php
/**
 * GanttListView.php - Controlador para vista Gantt de ListView de orden_de_trabajo
 * 
 * URL de acceso:
 * index.php?module=orden_de_trabajo&action=GanttListView
 * 
 * Parámetros opcionales:
 * - viewname: ID de la vista de lista a usar (default: ALL)
 * - periodtask: Período de fechas (today, thisweek, thismonth, thisyear, custom)
 * - custom_start_date: Fecha inicio personalizada (dd/mm/yyyy)
 * - custom_end_date: Fecha fin personalizada (dd/mm/yyyy)
 */

require_once('Smarty_setup.php');
require_once('include/utils/GanttModuleViewUtils.class.php');
require_once('include/utils/PlatzillaUtils.class.php');
require_once('modules/notifications/lib/NotificationPeriodUtils.class.php');

global $adb, $current_user, $theme;

try {
    // Parámetros de entrada
    $viewName = PlatzillaUtils::purify($_REQUEST, 'viewname', 'ALL');
    $periodTask = PlatzillaUtils::purify($_REQUEST, 'periodtask', 'thisyear');
    $customStartDate = PlatzillaUtils::purify($_REQUEST, 'custom_start_date', null);
    $customEndDate = PlatzillaUtils::purify($_REQUEST, 'custom_end_date', null);
    
    // Determinar rango de fechas
    if (!empty($customStartDate) && !empty($customEndDate)) {
        $startParts = explode('/', $customStartDate);
        $endParts = explode('/', $customEndDate);
        
        if (count($startParts) == 3 && count($endParts) == 3) {
            $periodDates = array(
                'startdate' => $startParts[2] . '-' . $startParts[1] . '-' . $startParts[0],
                'enddate' => $endParts[2] . '-' . $endParts[1] . '-' . $endParts[0]
            );
        } else {
            $periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate($periodTask);
        }
    } else {
        $periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate($periodTask);
    }
    
    // Obtener IDs de órdenes de trabajo con fechas en el período
    $query = "SELECT odt.orden_de_trabajoid
              FROM vtiger_orden_de_trabajo odt
              INNER JOIN vtiger_crmentity ce ON ce.crmid = odt.orden_de_trabajoid AND ce.deleted = 0
              WHERE (
                  (odt.fecha_prevista BETWEEN ? AND ?) OR
                  (odt.fecha_estim_fin BETWEEN ? AND ?) OR
                  (odt.fecha_prevista <= ? AND odt.fecha_estim_fin >= ?)
              )
              ORDER BY odt.fecha_prevista ASC";
    
    $params = array(
        $periodDates['startdate'], $periodDates['enddate'],
        $periodDates['startdate'], $periodDates['enddate'],
        $periodDates['startdate'], $periodDates['enddate']
    );
    
    $result = $adb->pquery($query, $params);
    $workOrderIds = array();
    
    while ($row = $adb->fetchByAssoc($result)) {
        $workOrderIds[] = intval($row['orden_de_trabajoid']);
    }
    
    // Construir datos del Gantt
    $ganttTasks = array();
    if (!empty($workOrderIds)) {
        $ganttTasks = GanttModuleViewUtils::buildWorkOrdersListViewGantt($adb, $workOrderIds, $current_user);
    }
    
    // Preparar Smarty
    $smarty = new vtigerCRM_Smarty();
    $smarty->assign('THEME', $theme);
    $smarty->assign('GANTT_VIEW_NAME', 'Trabajos planificados');
    $smarty->assign('GANTT_TASKS', $ganttTasks);
    $smarty->assign('GANTT_TASKS_JSON', json_encode($ganttTasks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    $smarty->assign('PERIOD_TASK', $periodTask);
    $smarty->assign('PERIOD_START', $periodDates['startdate']);
    $smarty->assign('PERIOD_END', $periodDates['enddate']);
    $smarty->assign('TOTAL_WORK_ORDERS', count($workOrderIds));
    
    // Renderizar
    $smarty->display('centaurus/modules/orden_de_trabajo/GanttListView.tpl');
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">';
    echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}
