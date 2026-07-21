<?php
/**
 * SupplierGanttHelper - Utilidad para construir datos Gantt de tareas asignadas a proveedores
 * 
 * Genera una estructura jerárquica:
 * - Nivel 1: Proyecto (o "SIN PROYECTO RELACIONADO")
 * - Nivel 2: Trabajo (o "SIN TRABAJO RELACIONADO")  
 * - Nivel 3: Tarea
 * 
 * @author Platzilla Development Team
 * @date 2025-12-04
 */

require_once('include/platzilla/Utils/DatabaseUtils.php');

class SupplierGanttHelper {
    
    /**
     * Construye los datos del Gantt para las tareas asignadas a un proveedor
     * 
     * @param PearDatabase $adb - Conexión a base de datos
     * @param int $supplierId - ID del proveedor
     * @param object $currentUser - Usuario actual
     * @return array - Array de tareas para el Gantt
     */
    public static function buildSupplierTasksGantt($adb, $supplierId, $currentUser) {
        $ganttTasks = array();
        $processedProjects = array();
        $processedWorks = array();
        
        // Obtener todas las tareas asignadas al proveedor con información de trabajo y proyecto
        $query = "SELECT 
                    act.activityid,
                    act.subject,
                    act.activitytype,
                    act.date_start,
                    act.due_date,
                    act.progress,
                    act.eventstatus,
                    crm.crmid,
                    crm.smownerid,
                    -- Información del trabajo relacionado
                    odt.orden_de_trabajoid AS work_id,
                    odt.titulo AS work_title,
                    odt.fecha_prevista AS work_start,
                    odt.fecha_estim_fin AS work_end,
                    odt.overall_progress_perc AS work_progress,
                    -- Información del proyecto relacionado
                    proy.proyectosid AS project_id,
                    proy.nombre AS project_name,
                    proy.fecha_de_inicio AS project_start,
                    proy.fecha_de_terminacion AS project_end,
                    proy.porcentaje_de_avance_genera AS project_progress
                  FROM vtiger_activity act
                  INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
                  INNER JOIN vtiger_supplieractivityrel srel ON srel.activityid = act.activityid
                  LEFT JOIN vtiger_seactivityrel serel ON serel.activityid = act.activityid
                  LEFT JOIN vtiger_orden_de_trabajo odt ON odt.orden_de_trabajoid = serel.crmid
                  LEFT JOIN vtiger_crmentity crm_odt ON crm_odt.crmid = odt.orden_de_trabajoid AND crm_odt.deleted = 0
                  LEFT JOIN vtiger_proyectos proy ON proy.proyectosid = odt.proyecto
                  LEFT JOIN vtiger_crmentity crm_proy ON crm_proy.crmid = proy.proyectosid AND crm_proy.deleted = 0
                  WHERE srel.proveedoresid = ?
                  ORDER BY proy.nombre, odt.titulo, act.date_start";
        
        $result = $adb->pquery($query, array($supplierId));
        
        if ($adb->num_rows($result) == 0) {
            return $ganttTasks;
        }
        
        $taskIdCounter = 1000000; // Para IDs únicos de elementos virtuales
        
        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            // Excluir tareas tipo "Job" del Gantt
            if ($row['activitytype'] == 'Job') {
                continue;
            }
            
            $projectId = $row['project_id'];
            $workId = $row['work_id'];
            $taskId = $row['activityid'];
            
            // Determinar IDs y nombres para proyecto y trabajo
            $projectGanttId = !empty($projectId) ? 'proj_' . $projectId : 'proj_none';
            $projectName = !empty($row['project_name']) ? $row['project_name'] : 'SIN PROYECTO RELACIONADO';
            $projectStart = !empty($row['project_start']) ? $row['project_start'] : $row['date_start'];
            $projectEnd = !empty($row['project_end']) ? $row['project_end'] : $row['due_date'];
            $projectProgress = !empty($row['project_progress']) ? intval($row['project_progress']) : 0;
            
            $workGanttId = !empty($workId) ? 'work_' . $workId : 'work_none_' . $projectGanttId;
            $workName = !empty($row['work_title']) ? $row['work_title'] : 'SIN TRABAJO RELACIONADO';
            $workStart = !empty($row['work_start']) ? $row['work_start'] : $row['date_start'];
            $workEnd = !empty($row['work_end']) ? $row['work_end'] : $row['due_date'];
            $workProgress = !empty($row['work_progress']) ? intval($row['work_progress']) : 0;
            
            // Nivel 1: Proyecto (si no se ha agregado)
            if (!in_array($projectGanttId, $processedProjects)) {
                $project = new stdClass();
                $project->id = $projectGanttId;
                $project->name = $projectName;
                $project->start = $projectStart;
                $project->end = $projectEnd;
                $project->progress = $projectProgress;
                $project->dependencies = '';
                $project->custom_class = 'task-level-1 task-project';
                $project->level = 1;
                $project->totalGroup = 0;
                $project->relModule = 'proyectos';
                $project->actProgress = $projectProgress;
                $ganttTasks[] = $project;
                $processedProjects[] = $projectGanttId;
            }
            
            // Nivel 2: Trabajo (si no se ha agregado)
            $workUniqueId = $workGanttId . '_' . $projectGanttId;
            if (!in_array($workUniqueId, $processedWorks)) {
                $work = new stdClass();
                $work->id = $workGanttId;
                $work->name = $workName;
                $work->start = $workStart;
                $work->end = $workEnd;
                $work->progress = $workProgress;
                $work->dependencies = $projectGanttId;
                $work->custom_class = 'task-level-3 task-job';
                $work->level = 2;
                $work->totalGroup = 0;
                $work->relModule = 'orden_de_trabajo';
                $work->actProgress = $workProgress;
                $ganttTasks[] = $work;
                $processedWorks[] = $workUniqueId;
            }
            
            // Nivel 3: Tarea (ID numérico para que funcione el guardado)
            $task = new stdClass();
            $task->id = $taskId;
            $task->name = $row['subject'];
            $task->start = $row['date_start'];
            $task->end = $row['due_date'];
            $task->progress = intval($row['progress']);
            $task->dependencies = $workGanttId;
            $task->custom_class = 'task-level-4 task-item';
            $task->level = 3;
            $task->totalGroup = 0;
            $task->relModule = 'Calendar';
            $task->actProgress = intval($row['progress']);
            $task->eventstatus = $row['eventstatus'];
            $ganttTasks[] = $task;
        }
        
        DatabaseUtils::closeResult($result);
        
        return $ganttTasks;
    }
    
    /**
     * Obtiene estadísticas de las tareas del proveedor
     * 
     * @param PearDatabase $adb - Conexión a base de datos
     * @param int $supplierId - ID del proveedor
     * @return array - Estadísticas
     */
    public static function getSupplierTasksStats($adb, $supplierId) {
        $query = "SELECT 
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN act.eventstatus = 'Held' THEN 1 ELSE 0 END) as completed_tasks,
                    SUM(CASE WHEN act.eventstatus = 'Planned' THEN 1 ELSE 0 END) as pending_tasks,
                    MIN(act.date_start) as min_date,
                    MAX(act.due_date) as max_date
                  FROM vtiger_activity act
                  INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
                  INNER JOIN vtiger_supplieractivityrel srel ON srel.activityid = act.activityid
                  WHERE srel.proveedoresid = ?";
        
        $result = $adb->pquery($query, array($supplierId));
        $stats = $adb->fetchByAssoc($result, -1, false);
        DatabaseUtils::closeResult($result);
        
        return $stats;
    }
}
