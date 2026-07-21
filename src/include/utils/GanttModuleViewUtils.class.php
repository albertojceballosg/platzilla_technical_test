<?php
/**
 * GanttModuleViewUtils - Utilidad para gestionar vistas Gantt de módulos en ListView
 * 
 * Esta clase maneja la configuración y construcción de datos para vistas Gantt
 * aplicables a cualquier módulo del sistema (proyectos, órdenes de trabajo, etc.)
 * 
 * @author Platzilla Development Team
 * @date 2025-11-25
 */

require_once('data/CRMEntity.php');

abstract class GanttModuleViewUtils {
    
    /**
     * Obtener configuración de una vista Gantt específica
     * 
     * @param PearDatabase $adb - Conexión a base de datos
     * @param int $viewId - ID de la vista Gantt
     * @return array|null - Configuración de la vista o null si no existe
     */
    public static function getGanttView($adb, $viewId) {
        $query = "SELECT * FROM vtiger_gantt_module_view WHERE ganttviewid = ? AND status = 'active'";
        $result = $adb->pquery($query, array($viewId));
        
        if ($adb->num_rows($result) == 0) {
            return null;
        }
        
        $view = $adb->fetchByAssoc($result);
        
        $view['hierarchy_config'] = json_decode($view['hierarchy_config'], true);
        $view['display_config'] = json_decode($view['display_config'], true);
        
        // Asegurar que filters esté presente (no decodificar aquí, se hace en AjaxListViewUtils)
        if (empty($view['filters'])) {
            $view['filters'] = null;
        }

        return $view;
    }
    
    /**
     * Obtener todas las vistas Gantt disponibles para un módulo
     * 
     * @param PearDatabase $adb - Conexión a base de datos
     * @param string $moduleName - Nombre del módulo
     * @param int $userId - ID del usuario (null = todas las vistas)
     * @return array - Array de vistas Gantt
     */
    public static function getGanttViews($adb, $moduleName, $userId = null) {
        $params = array($moduleName);
        $query = "SELECT ganttviewid, viewname, is_default, modulename, userid 
                  FROM vtiger_gantt_module_view 
                  WHERE modulename = ? AND status = 'active'";
        
        if ($userId !== null) {
            $query .= " AND (userid = ? OR userid = 0)";  // 0 = vistas globales
            $params[] = $userId;
        }
        
        $query .= " ORDER BY is_default DESC, viewname ASC";
        $result = $adb->pquery($query, $params);
        
        $views = array();
        while ($row = $adb->fetchByAssoc($result)) {
            $views[] = $row;
        }
        
        return $views;
    }
    
    /**
     * Verificar si existe al menos una vista Gantt para el módulo
     * 
     * @param PearDatabase $adb - Conexión a base de datos
     * @param string $moduleName - Nombre del módulo
     * @return bool - true si hay vistas disponibles
     */
    public static function hasGanttViews($adb, $moduleName) {
        $views = self::getGanttViews($adb, $moduleName);
        return !empty($views);
    }
    
    /**
     * Obtener vista por defecto para un módulo
     * 
     * @param PearDatabase $adb - Conexión a base de datos
     * @param string $moduleName - Nombre del módulo
     * @return array|null - Vista por defecto o null
     */
    public static function getDefaultView($adb, $moduleName) {
        $query = "SELECT ganttviewid FROM vtiger_gantt_module_view 
                  WHERE modulename = ? AND is_default = 1 AND status = 'active' 
                  LIMIT 1";
        $result = $adb->pquery($query, array($moduleName));
        
        if ($adb->num_rows($result) == 0) {
            return null;
        }
        
        $row = $adb->fetchByAssoc($result);
        return self::getGanttView($adb, $row['ganttviewid']);
    }
    
    /**
     * Construir datos del Gantt para múltiples registros del módulo
     * 
     * @param PearDatabase $adb - Conexión a base de datos
     * @param string $moduleName - Nombre del módulo
     * @param array $recordIds - IDs de registros a incluir
     * @param array $viewConfig - Configuración de la vista
     * @param Users $currentUser - Usuario actual
     * @return array - Array de tareas para el Gantt
     */
    public static function buildGanttData($adb, $moduleName, $recordIds, $viewConfig, $currentUser) {
        $ganttData = array();
        
        if (empty($recordIds) || empty($viewConfig)) {
            return $ganttData;
        }
        
        // Para orden_de_trabajo, usar el método especializado que maneja el ordenamiento correcto
        if ($moduleName == 'orden_de_trabajo') {
            return self::buildWorkOrdersListViewGantt($adb, $recordIds, $currentUser);
        }
        
        $hierarchyConfig = $viewConfig['hierarchy_config'];
        $displayConfig = $viewConfig['display_config'];
        
        foreach ($recordIds as $recordId) {
            try {
                $recordData = self::buildRecordHierarchy(
                    $adb, 
                    $moduleName, 
                    $recordId, 
                    $hierarchyConfig, 
                    $displayConfig,
                    $currentUser
                );
                
                $ganttData = array_merge($ganttData, $recordData);
            } catch (Exception $e) {
                // Continuar con otros registros si hay error
            }
        }
        
        return $ganttData;
    }
    
    /**
     * Construir jerarquía para un registro individual
     * 
     * @param PearDatabase $adb
     * @param string $moduleName
     * @param int $recordId
     * @param array $hierarchyConfig
     * @param array $displayConfig
     * @param Users $currentUser
     * @return array - Array de tareas
     */
    private static function buildRecordHierarchy($adb, $moduleName, $recordId, $hierarchyConfig, $displayConfig, $currentUser) {
        $tasks = array();
        
        // Delegar a método específico según el módulo
        switch ($moduleName) {
            case 'proyectos':
                $tasks = self::buildProjectHierarchy($adb, $recordId, $hierarchyConfig, $displayConfig, $currentUser);
                break;
            case 'orden_de_trabajo':
                $tasks = self::buildWorkOrderHierarchy($adb, $recordId, $hierarchyConfig, $displayConfig, $currentUser);
                break;
            default:
                // Implementación genérica basada en configuración
                $tasks = self::buildGenericHierarchy($adb, $moduleName, $recordId, $hierarchyConfig, $displayConfig, $currentUser);
                break;
        }
        
        return $tasks;
    }
    
    /**
     * Construir jerarquía específica para proyectos (4 niveles)
     * 
     * @param PearDatabase $adb
     * @param int $projectId
     * @param array $hierarchyConfig
     * @param array $displayConfig
     * @param Users $currentUser
     * @return array - Array de tareas
     */
    private static function buildProjectHierarchy($adb, $projectId, $hierarchyConfig, $displayConfig, $currentUser) {
        require_once('modules/proyectos/handlers/taskToProject.class.php');
        
        $tasks = array();
        
        // NIVEL 1: Proyecto
        $projectName = self::getRecordName($adb, 'proyectos', $projectId);

        // Obtener fechas estimadas estándar del proyecto (est_start_date / est_end_date) y progreso
        $projectDates = self::getProjectEstimatedDates($adb, $projectId);
        $projectStart = isset($projectDates['start']) ? $projectDates['start'] : null;
        $projectEnd   = isset($projectDates['end'])   ? $projectDates['end']   : null;
        $projectProgress = isset($projectDates['progress']) ? $projectDates['progress'] : 0;

        $projectTask = self::createGanttTask(
            "project-{$projectId}",
            $projectName . ' (PROYECTO)',
            $projectStart,
            $projectEnd,
            $projectProgress,
            '',
            'task-level-1 task-project',
            'proyectos'
        );
        $tasks[] = $projectTask;
        $projectIndex = count($tasks) - 1;
        
        // NIVEL 2-4: Etapas, Trabajos, Tareas
        $jobs = taskToProject::getInstance($adb)->fetchRelatedWork($projectId, $currentUser, null);
        
        if (!empty($jobs)) {
            $ganttGroups = array();
            
            foreach ($jobs as $job) {
                $stageName = $job->getStageName();
                
                // NIVEL 2: Etapa (si no existe)
                if (!isset($ganttGroups[$stageName])) {
                    $stageId = "stage-" . uniqid();
                    $stageTask = self::createGanttTask(
                        $stageId,
                        $stageName,
                        $job->getStartDate(),
                        null,
                        0,
                        "project-{$projectId}",
                        'task-level-2 task-stage',
                        'proyectos'
                    );
                    $tasks[] = $stageTask;
                    $stageIndex = count($tasks) - 1;
                    $ganttGroups[$stageName] = array('id' => $stageId, 'index' => $stageIndex);
                }
                
                $stageId = $ganttGroups[$stageName]['id'];
                $stageIndex = $ganttGroups[$stageName]['index'];
                
                // NIVEL 3: Trabajo
                $jobId = "{$job->getCrmIdJob()}@{$job->getId()}";
                $jobTask = self::createGanttTask(
                    $jobId,
                    $job->getJobName() . ' (TRABAJOS)',
                    $job->getStartDate(),
                    $job->getEstimatedDueDate(),
                    intval($job->getPercentageCompletion()),
                    $stageId,
                    'task-level-3 task-job',
                    'orden_de_trabajo'
                );
                $tasks[] = $jobTask;
                $jobIndex = count($tasks) - 1;
                
                // NIVEL 4: Tareas del trabajo
                $jobTasks = self::getJobTasks($adb, $job->getCrmIdJob());
                foreach ($jobTasks as $taskData) {
                    $taskTask = self::createGanttTask(
                        $taskData['activityid'],
                        $taskData['subject'] . ' (Tarea/Acción)',
                        $taskData['date_start'],
                        $taskData['due_date'],
                        intval($taskData['progress']),
                        $jobId,
                        'task-level-4 task-item',
                        'Calendar'
                    );
                    $tasks[] = $taskTask;
                    
                    // Actualizar fechas del trabajo
                    self::updateParentTaskDates($tasks, $jobIndex, $taskData['date_start'], $taskData['due_date']);
                }
                
                // Actualizar fechas y progreso de la etapa
                self::updateParentTask($tasks, $stageIndex, $job->getStartDate(), $job->getEstimatedDueDate(), intval($job->getProjectProgress()));
            }
        }
        
        return $tasks;
    }

    /**
     * Obtener fechas estimadas de inicio y fin de un proyecto, y su progreso
     * usando los campos estándar est_start_date, est_end_date y progreso.
     *
     * @param PearDatabase $adb
     * @param int $projectId
     * @return array ['start' => string|null, 'end' => string|null, 'progress' => int]
     */
    private static function getProjectEstimatedDates($adb, $projectId) {
        $dates = array('start' => null, 'end' => null, 'progress' => 0);

        try {
            $focus = CRMEntity::getInstance('proyectos');
            $tableName = $focus->table_name;
            $idColumn  = $focus->table_index;

            $query  = "SELECT est_start_date, est_end_date, porcentaje_de_avance_genera FROM {$tableName} WHERE {$idColumn} = ?";
            $result = $adb->pquery($query, array($projectId));

            if ($result && $adb->num_rows($result) > 0) {
                $row = $adb->fetchByAssoc($result);
                if (!empty($row['est_start_date'])) {
                    $dates['start'] = $row['est_start_date'];
                }
                if (!empty($row['est_end_date'])) {
                    $dates['end'] = $row['est_end_date'];
                }
                if (isset($row['porcentaje_de_avance_genera'])) {
                    // El valor en BD ya está multiplicado por 100 (16.16% = 16.16), solo convertir a entero
                    $dates['progress'] = intval(floatval($row['porcentaje_de_avance_genera']));
                }
            }
        } catch (Exception $e) {
            // Error obteniendo fechas del proyecto
        }

        return $dates;
    }
    
    /**
     * Construir jerarquía para órdenes de trabajo (4 niveles si tiene proyecto)
     * 
     * Jerarquía:
     * - Nivel 1: Proyecto (si existe) o "Trabajos independientes de proyectos"
     * - Nivel 2: Etapa del proyecto (si existe) o "Trabajos independientes"
     * - Nivel 3: Trabajo (orden_de_trabajo)
     * - Nivel 4: Tareas del trabajo
     * 
     * @param PearDatabase $adb
     * @param int $workOrderId
     * @param array $hierarchyConfig
     * @param array $displayConfig
     * @param Users $currentUser
     * @param array &$sharedCache - Cache compartido para proyectos y etapas (opcional)
     * @return array - Array de tareas
     */
    private static function buildWorkOrderHierarchy($adb, $workOrderId, $hierarchyConfig, $displayConfig, $currentUser, &$sharedCache = null) {
        $tasks = array();
        
        // Inicializar cache si no existe
        static $projectsAdded = array();
        static $stagesAdded = array();
        
        // Constantes para trabajos sin proyecto
        $INDEPENDENT_PROJECT_ID = 'independent';
        $INDEPENDENT_PROJECT_NAME = 'Trabajos independientes de proyectos';
        $INDEPENDENT_STAGE_NAME = 'Trabajos independientes';
        
        // Obtener datos del trabajo incluyendo relación con proyecto
        // Nota: Usamos pw.stageid para obtener el nombre de la etapa desde vtiger_etapas_proyecto
        $query = "SELECT 
                    odt.orden_de_trabajoid,
                    odt.titulo,
                    odt.fecha_prevista,
                    odt.fecha_estim_fin,
                    odt.overall_progress_perc,
                    pw.crmid AS proyecto_id,
                    pw.stageid,
                    p.nombre AS proyecto_titulo,
                    p.est_start_date AS proyecto_start,
                    p.est_end_date AS proyecto_end,
                    ep.titulo AS stage_name
                  FROM vtiger_orden_de_trabajo odt
                  INNER JOIN vtiger_crmentity ce ON ce.crmid = odt.orden_de_trabajoid AND ce.deleted = 0
                  LEFT JOIN vtiger_project_works pw ON pw.crmid_job = odt.orden_de_trabajoid
                  LEFT JOIN vtiger_proyectos p ON p.proyectosid = pw.crmid
                  LEFT JOIN vtiger_crmentity ce_p ON ce_p.crmid = p.proyectosid AND ce_p.deleted = 0
                  LEFT JOIN vtiger_etapas_proyecto ep ON ep.etapas_proyectoid = pw.stageid
                  WHERE odt.orden_de_trabajoid = ?";
        
        $result = $adb->pquery($query, array($workOrderId));
        
        if (!$result || $adb->num_rows($result) == 0) {
            return $tasks;
        }
        
        $row = $adb->fetchByAssoc($result);
        $projectId = $row['proyecto_id'];
        $stageName = $row['stage_name'];
        $stageId = $row['stageid'];
        
        $hasProject = !empty($projectId) && !empty($row['proyecto_titulo']);
        
        // ============================================
        // NIVEL 1: Proyecto (real o virtual)
        // ============================================
        $projectTaskId = null;
        
        if ($hasProject) {
            // Proyecto real
            if (!isset($projectsAdded[$projectId])) {
                $projectTaskId = "project-{$projectId}";
                $projectTask = self::createGanttTask(
                    $projectTaskId,
                    $row['proyecto_titulo'] . ' (PROYECTO)',
                    $row['proyecto_start'],
                    $row['proyecto_end'],
                    0,
                    '',
                    'task-level-1 task-project',
                    'proyectos'
                );
                $tasks[] = $projectTask;
                $projectsAdded[$projectId] = $projectTaskId;
            } else {
                $projectTaskId = $projectsAdded[$projectId];
            }
        } else {
            // Trabajo sin proyecto -> usar proyecto virtual
            if (!isset($projectsAdded[$INDEPENDENT_PROJECT_ID])) {
                $projectTaskId = "project-{$INDEPENDENT_PROJECT_ID}";
                $projectTask = self::createGanttTask(
                    $projectTaskId,
                    $INDEPENDENT_PROJECT_NAME,
                    null,
                    null,
                    0,
                    '',
                    'task-level-1 task-project task-independent',
                    'orden_de_trabajo'
                );
                $tasks[] = $projectTask;
                $projectsAdded[$INDEPENDENT_PROJECT_ID] = $projectTaskId;
            } else {
                $projectTaskId = $projectsAdded[$INDEPENDENT_PROJECT_ID];
            }
            // Forzar etapa virtual
            $stageName = $INDEPENDENT_STAGE_NAME;
            $projectId = $INDEPENDENT_PROJECT_ID;
        }
        
        // ============================================
        // NIVEL 2: Etapa (real o virtual)
        // ============================================
        $stageTaskId = null;
        
        if (empty($stageName)) {
            $stageName = $hasProject ? 'Sin etapa' : $INDEPENDENT_STAGE_NAME;
        }
        
        $stageKey = "{$projectId}-{$stageName}";
        
        if (!isset($stagesAdded[$stageKey])) {
            $stageTaskId = "stage-{$projectId}-" . md5($stageName);
            $stageTask = self::createGanttTask(
                $stageTaskId,
                $stageName,
                null,
                null,
                0,
                $projectTaskId,
                'task-level-2 task-stage',
                $hasProject ? 'proyectos' : 'orden_de_trabajo'
            );
            $tasks[] = $stageTask;
            $stagesAdded[$stageKey] = $stageTaskId;
        } else {
            $stageTaskId = $stagesAdded[$stageKey];
        }
        
        // ============================================
        // NIVEL 3: Trabajo (orden_de_trabajo)
        // ============================================
        $jobTaskId = !empty($stageId) ? "{$workOrderId}@{$stageId}" : "{$workOrderId}@0";
        $jobTask = self::createGanttTask(
            $jobTaskId,
            $row['titulo'] . ' (TRABAJO)',
            $row['fecha_prevista'],
            $row['fecha_estim_fin'],
            intval($row['overall_progress_perc']),
            $stageTaskId,
            'task-level-3 task-job',
            'orden_de_trabajo'
        );
        $tasks[] = $jobTask;
        $jobIndex = count($tasks) - 1;
        
        // ============================================
        // NIVEL 4: Tareas del trabajo
        // ============================================
        $workOrderTasks = self::getJobTasks($adb, $workOrderId);
        foreach ($workOrderTasks as $taskData) {
            $taskTask = self::createGanttTask(
                $taskData['activityid'],
                $taskData['subject'] . ' (Tarea/Acción)',
                $taskData['date_start'],
                $taskData['due_date'],
                intval($taskData['progress']),
                $jobTaskId,
                'task-level-4 task-item',
                'Calendar'
            );
            $tasks[] = $taskTask;
        }
        
        return $tasks;
    }
    
    /**
     * Construir Gantt completo para ListView de orden_de_trabajo
     * Incluye trabajos con y sin proyecto padre
     * 
     * Jerarquía:
     * - Nivel 1: Proyecto (si existe)
     * - Nivel 2: Etapa del proyecto (si existe)
     * - Nivel 3: Trabajo (orden_de_trabajo)
     * - Nivel 4: Tareas del trabajo
     * 
     * @param PearDatabase $adb
     * @param array $workOrderIds - IDs de órdenes de trabajo a incluir
     * @param Users $currentUser
     * @return array - Array de tareas para el Gantt
     */
    public static function buildWorkOrdersListViewGantt($adb, $workOrderIds, $currentUser) {
        require_once('modules/proyectos/handlers/taskToProject.class.php');
        
        $tasks = array();
        $projectsAdded = array();  // Cache de proyectos ya agregados
        $stagesAdded = array();    // Cache de etapas ya agregadas
        
        if (empty($workOrderIds)) {
            return $tasks;
        }
        
        // Obtener datos de todos los trabajos
        // IMPORTANTE: La relación trabajo-proyecto está en vtiger_project_works (crmid=proyecto, crmid_job=trabajo)
        $placeholders = implode(',', array_fill(0, count($workOrderIds), '?'));
        $query = "SELECT 
                    odt.orden_de_trabajoid,
                    odt.titulo,
                    odt.fecha_prevista,
                    odt.fecha_estim_fin,
                    odt.overall_progress_perc,
                    pw.crmid AS proyecto_id,
                    pw.stageid,
                    p.nombre AS proyecto_titulo,
                    p.est_start_date AS proyecto_start,
                    p.est_end_date AS proyecto_end,
                    p.porcentaje_de_avance_genera AS proyecto_progreso,
                    ep.titulo AS stage_name,
                    (SELECT MIN(a.date_start) 
                     FROM vtiger_activity a 
                     INNER JOIN vtiger_seactivityrel sar ON sar.activityid = a.activityid 
                     WHERE sar.crmid = odt.orden_de_trabajoid) AS min_task_date
                  FROM vtiger_orden_de_trabajo odt
                  INNER JOIN vtiger_crmentity ce ON ce.crmid = odt.orden_de_trabajoid AND ce.deleted = 0
                  LEFT JOIN vtiger_project_works pw ON pw.crmid_job = odt.orden_de_trabajoid
                  LEFT JOIN vtiger_proyectos p ON p.proyectosid = pw.crmid
                  LEFT JOIN vtiger_crmentity ce_p ON ce_p.crmid = p.proyectosid AND ce_p.deleted = 0
                  LEFT JOIN vtiger_etapas_proyecto ep ON ep.etapas_proyectoid = pw.stageid
                  WHERE odt.orden_de_trabajoid IN ({$placeholders})
                  ORDER BY pw.crmid ASC, ep.titulo ASC, min_task_date ASC, odt.titulo ASC";
        
        $result = $adb->pquery($query, $workOrderIds);
        
        // Constantes para trabajos sin proyecto
        $INDEPENDENT_PROJECT_ID = 'independent';
        $INDEPENDENT_PROJECT_NAME = 'Trabajos independientes de proyectos';
        $INDEPENDENT_STAGE_NAME = 'Trabajos independientes';
        
        while ($row = $adb->fetchByAssoc($result)) {
            $workOrderId = $row['orden_de_trabajoid'];
            $projectId = $row['proyecto_id'];
            $stageName = $row['stage_name'];
            $stageId = $row['stageid'];
            
            $hasProject = !empty($projectId) && !empty($row['proyecto_titulo']);
            
            // ============================================
            // NIVEL 1: Proyecto (real o virtual para independientes)
            // ============================================
            $projectTaskId = null;
            
            if ($hasProject) {
                // Proyecto real
                if (!isset($projectsAdded[$projectId])) {
                    $projectTaskId = "project-{$projectId}";
                    $projectProgress = isset($row['proyecto_progreso']) ? intval(floatval($row['proyecto_progreso'])) : 0;
                    $projectTask = self::createGanttTask(
                        $projectTaskId,
                        $row['proyecto_titulo'] . ' (PROYECTO)',
                        $row['proyecto_start'],
                        $row['proyecto_end'],
                        $projectProgress,
                        '',
                        'task-level-1 task-project',
                        'proyectos'
                    );
                    $tasks[] = $projectTask;
                    $projectsAdded[$projectId] = array(
                        'id' => $projectTaskId,
                        'index' => count($tasks) - 1
                    );
                } else {
                    $projectTaskId = $projectsAdded[$projectId]['id'];
                }
            } else {
                // Trabajo sin proyecto -> usar proyecto virtual "Trabajos independientes"
                if (!isset($projectsAdded[$INDEPENDENT_PROJECT_ID])) {
                    $projectTaskId = "project-{$INDEPENDENT_PROJECT_ID}";
                    $projectTask = self::createGanttTask(
                        $projectTaskId,
                        $INDEPENDENT_PROJECT_NAME,
                        null,
                        null,
                        0,
                        '',
                        'task-level-1 task-project task-independent',
                        'orden_de_trabajo'
                    );
                    $tasks[] = $projectTask;
                    $projectsAdded[$INDEPENDENT_PROJECT_ID] = array(
                        'id' => $projectTaskId,
                        'index' => count($tasks) - 1
                    );
                } else {
                    $projectTaskId = $projectsAdded[$INDEPENDENT_PROJECT_ID]['id'];
                }
                // Forzar etapa virtual para independientes
                $stageName = $INDEPENDENT_STAGE_NAME;
                $projectId = $INDEPENDENT_PROJECT_ID;
            }
            
            // ============================================
            // NIVEL 2: Etapa (real o virtual)
            // ============================================
            $stageTaskId = null;
            $stageKey = "{$projectId}-{$stageName}";
            
            if (!empty($stageName)) {
                if (!isset($stagesAdded[$stageKey])) {
                    $stageTaskId = "stage-{$projectId}-" . md5($stageName);
                    $stageTask = self::createGanttTask(
                        $stageTaskId,
                        $stageName,
                        null,
                        null,
                        0,
                        $projectTaskId,
                        'task-level-2 task-stage',
                        $hasProject ? 'proyectos' : 'orden_de_trabajo'
                    );
                    $tasks[] = $stageTask;
                    $stagesAdded[$stageKey] = array(
                        'id' => $stageTaskId,
                        'index' => count($tasks) - 1
                    );
                } else {
                    $stageTaskId = $stagesAdded[$stageKey]['id'];
                }
            }
            
            // ============================================
            // NIVEL 3: Trabajo (orden_de_trabajo)
            // ============================================
            // Determinar dependencia del trabajo (siempre tendrá al menos etapa)
            $jobDependency = !empty($stageTaskId) ? $stageTaskId : $projectTaskId;
            
            $jobTaskId = !empty($stageId) ? "{$workOrderId}@{$stageId}" : "{$workOrderId}@0";
            $jobTask = self::createGanttTask(
                $jobTaskId,
                $row['titulo'] . ' (TRABAJO)',
                $row['fecha_prevista'],
                $row['fecha_estim_fin'],
                intval($row['overall_progress_perc']),
                $jobDependency,
                'task-level-3 task-job',
                'orden_de_trabajo'
            );
            $tasks[] = $jobTask;
            $jobIndex = count($tasks) - 1;
            
            // ============================================
            // NIVEL 4: Tareas del trabajo
            // ============================================
            $jobTasks = self::getJobTasks($adb, $workOrderId);
            foreach ($jobTasks as $taskData) {
                $taskTask = self::createGanttTask(
                    $taskData['activityid'],
                    $taskData['subject'] . ' (Tarea)',
                    $taskData['date_start'],
                    $taskData['due_date'],
                    intval($taskData['progress']),
                    $jobTaskId,
                    'task-level-4 task-item',
                    'Calendar'
                );
                $tasks[] = $taskTask;
                
                // Actualizar fechas del trabajo
                self::updateParentTaskDates($tasks, $jobIndex, $taskData['date_start'], $taskData['due_date']);
            }
            
            // Actualizar fechas de etapa y proyecto
            if (!empty($stageTaskId) && isset($stagesAdded["{$projectId}-{$stageName}"])) {
                $stageIndex = $stagesAdded["{$projectId}-{$stageName}"]['index'];
                self::updateParentTaskDates($tasks, $stageIndex, $row['fecha_prevista'], $row['fecha_estim_fin']);
            }
            if (!empty($projectTaskId) && isset($projectsAdded[$projectId])) {
                $projectIndex = $projectsAdded[$projectId]['index'];
                self::updateParentTaskDates($tasks, $projectIndex, $row['fecha_prevista'], $row['fecha_estim_fin']);
            }
        }
        
        return $tasks;
    }
    
    /**
     * Obtener nombre de un registro usando vtiger_entityname
     * 
     * @param PearDatabase $adb
     * @param string $moduleName
     * @param int $recordId
     * @return string - Nombre del registro
     */
    private static function getRecordName($adb, $moduleName, $recordId) {
        // Obtener campo identificador desde vtiger_entityname
        $query = "SELECT fieldname, fieldidentifier FROM vtiger_entityname 
                  WHERE modulename = ? AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)";
        $result = $adb->pquery($query, array($moduleName, $moduleName));
        
        $identifierField = null;
        if ($adb->num_rows($result) > 0) {
            $data = $adb->fetchByAssoc($result);
            $identifierField = !empty($data['fieldidentifier']) ? $data['fieldidentifier'] : $data['fieldname'];
        }
        
        // Obtener el valor del campo
        try {
            $entity = CRMEntity::getInstance($moduleName);
            $entity->retrieve_entity_info($recordId, $moduleName);
            
            if (!empty($identifierField) && isset($entity->column_fields[$identifierField])) {
                return $entity->column_fields[$identifierField];
            }
        } catch (Exception $e) {
            // Error obteniendo nombre del registro
        }
        
        // Fallback: usar etiqueta del módulo + ID
        $moduleLabel = getTabIdLabelByName($moduleName);
        return $moduleLabel . ' #' . $recordId;
    }
    
    /**
     * Obtener tareas relacionadas a un trabajo/registro
     * 
     * @param PearDatabase $adb
     * @param int $crmId - ID del registro relacionado
     * @return array - Array de tareas
     */
    private static function getJobTasks($adb, $crmId) {
        $query = "SELECT a.activityid, a.subject, a.date_start, a.due_date, a.progress, a.activitytype
                  FROM vtiger_activity a
                  INNER JOIN vtiger_seactivityrel r ON r.activityid = a.activityid
                  INNER JOIN vtiger_crmentity c ON c.crmid = a.activityid
                  WHERE r.crmid = ? 
                    AND c.deleted = 0
                    AND a.activitytype != 'Job'
                  ORDER BY a.date_start ASC";
        $result = $adb->pquery($query, array($crmId));
        
        $tasks = array();
        while ($row = $adb->fetchByAssoc($result)) {
            $tasks[] = $row;
        }
        
        return $tasks;
    }
    
    /**
     * Crear objeto de tarea para Frappe Gantt
     * 
     * @param string $id - ID único de la tarea
     * @param string $name - Nombre de la tarea
     * @param string $start - Fecha de inicio
     * @param string $end - Fecha de fin
     * @param int $progress - Porcentaje de progreso (0-100)
     * @param string $dependencies - IDs de tareas dependientes
     * @param string $customClass - Clases CSS personalizadas
     * @param string $relModule - Módulo relacionado
     * @return stdClass - Objeto de tarea
     */
    private static function createGanttTask($id, $name, $start, $end, $progress, $dependencies, $customClass, $relModule) {
		if (!empty($start) && !empty($end)) {
			$startTs = strtotime($start);
			$endTs = strtotime($end);
			if ($startTs !== false && $endTs !== false && $endTs < $startTs) {
				$tmp = $start;
				$start = $end;
				$end = $tmp;
			}
		}
		$task = new stdClass();
        $task->id = $id;
        $task->name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
        $task->start = $start;
        $task->end = $end;
        $task->progress = $progress;
        $task->dependencies = $dependencies;
        $task->custom_class = $customClass;
        $task->relModule = $relModule;
        $task->actProgress = $progress;
        $task->totalGroup = 0;
        
        return $task;
    }
    
    /**
     * Actualizar fechas y progreso de tarea padre
     * 
     * @param array $tasks - Array de tareas (por referencia)
     * @param int $index - Índice de la tarea padre
     * @param string $startDate - Fecha de inicio
     * @param string $endDate - Fecha de fin
     * @param int $progress - Progreso a acumular
     */
    private static function updateParentTask(&$tasks, $index, $startDate, $endDate, $progress) {
        if (!isset($tasks[$index])) return;

        // Normalizar rango de fechas recibido: si ambas fechas existen y endDate < startDate, intercambiarlas
        if (!empty($startDate) && !empty($endDate)) {
            $startTs = strtotime($startDate);
            $endTs = strtotime($endDate);
            if ($startTs !== false && $endTs !== false && $endTs < $startTs) {
                $tmp = $startDate;
                $startDate = $endDate;
                $endDate = $tmp;
            }
        }
        
        $parent = &$tasks[$index];
        
        // Actualizar fechas
        if (!empty($startDate) && (empty($parent->start) || strtotime($startDate) < strtotime($parent->start))) {
            $parent->start = $startDate;
        }
        if (!empty($endDate) && (empty($parent->end) || strtotime($endDate) > strtotime($parent->end))) {
            $parent->end = $endDate;
        }
        
        // Actualizar progreso
        $parent->actProgress += $progress;
        $parent->totalGroup++;
        
        if ($parent->totalGroup > 0) {
            $parent->progress = intval($parent->actProgress / $parent->totalGroup);
        }
    }
    
    /**
     * Actualizar solo fechas de tarea padre (sin progreso)
     * 
     * @param array $tasks - Array de tareas (por referencia)
     * @param int $index - Índice de la tarea padre
     * @param string $startDate - Fecha de inicio
     * @param string $endDate - Fecha de fin
     */
    private static function updateParentTaskDates(&$tasks, $index, $startDate, $endDate) {
        if (!isset($tasks[$index])) return;

        // Normalizar rango de fechas recibido: si ambas fechas existen y endDate < startDate, intercambiarlas
        if (!empty($startDate) && !empty($endDate)) {
            $startTs = strtotime($startDate);
            $endTs = strtotime($endDate);
            if ($startTs !== false && $endTs !== false && $endTs < $startTs) {
                $tmp = $startDate;
                $startDate = $endDate;
                $endDate = $tmp;
            }
        }
        
        $parent = &$tasks[$index];
        
        // Actualizar fechas
        if (!empty($startDate) && (empty($parent->start) || strtotime($startDate) < strtotime($parent->start))) {
            $parent->start = $startDate;
        }
        if (!empty($endDate) && (empty($parent->end) || strtotime($endDate) > strtotime($parent->end))) {
            $parent->end = $endDate;
        }
    }
    
    /**
     * Construir jerarquía genérica basada en configuración JSON
     * (Implementación futura para otros módulos)
     * 
     * @param PearDatabase $adb
     * @param string $moduleName
     * @param int $recordId
     * @param array $hierarchyConfig
     * @param array $displayConfig
     * @param Users $currentUser
     * @return array - Array de tareas
     */
    private static function buildGenericHierarchy($adb, $moduleName, $recordId, $hierarchyConfig, $displayConfig, $currentUser) {
        // Implementación futura: usar la configuración JSON para construir dinámicamente la jerarquía
        // Por ahora retornar vacío
        return array();
    }
    
    /**
     * Guardar una nueva vista Gantt
     * 
     * @param PearDatabase $adb
     * @param array $viewData - Datos de la vista
     * @return int - ID de la vista creada
     */
    public static function saveGanttView($adb, $viewData) {
        $query = "INSERT INTO vtiger_gantt_module_view 
                  (viewname, modulename, userid, is_default, hierarchy_config, display_config, filters, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = array(
            $viewData['viewname'],
            $viewData['modulename'],
            $viewData['userid'],
            isset($viewData['is_default']) ? $viewData['is_default'] : 0,
            json_encode($viewData['hierarchy_config']),
            json_encode($viewData['display_config']),
            isset($viewData['filters']) ? json_encode($viewData['filters']) : null,
            'active'
        );
        
        $result = $adb->pquery($query, $params);
        return $adb->getLastInsertID();
    }
    
    /**
     * Actualizar vista Gantt existente
     * 
     * @param PearDatabase $adb
     * @param int $viewId
     * @param array $viewData
     * @return bool - true si se actualizó correctamente
     */
    public static function updateGanttView($adb, $viewId, $viewData) {
        $query = "UPDATE vtiger_gantt_module_view 
                  SET viewname = ?, hierarchy_config = ?, display_config = ?, filters = ?, updated_at = NOW() 
                  WHERE ganttviewid = ?";
        
        $params = array(
            $viewData['viewname'],
            json_encode($viewData['hierarchy_config']),
            json_encode($viewData['display_config']),
            isset($viewData['filters']) ? json_encode($viewData['filters']) : null,
            $viewId
        );
        
        $result = $adb->pquery($query, $params);
        return ($result !== false);
    }
    
    /**
     * Eliminar (marcar como inactiva) una vista Gantt
     * 
     * @param PearDatabase $adb
     * @param int $viewId
     * @return bool - true si se eliminó correctamente
     */
    public static function deleteGanttView($adb, $viewId) {
        $query = "UPDATE vtiger_gantt_module_view 
                  SET status = 'deleted', updated_at = NOW() 
                  WHERE ganttviewid = ?";
        
        $result = $adb->pquery($query, array($viewId));
        return ($result !== false);
    }
    
    /**
     * Aplicar filtros de vista Gantt al QueryGenerator
     * 
     * Estructura de filtros esperada:
     * {
     *   "conditions": [
     *     {"field": "estado", "operator": "equals", "value": "En progreso"},
     *     {"field": "fecha_prevista", "operator": "between", "value": ["2025-01-01", "2025-12-31"]},
     *     {"field": "responsable", "operator": "current_user"}
     *   ],
     *   "logic": "AND"
     * }
     * 
     * Operadores soportados:
     * - equals, not_equals: Igualdad/desigualdad
     * - contains, not_contains: Contiene/no contiene (LIKE)
     * - starts_with, ends_with: Empieza/termina con
     * - greater_than, less_than, greater_equal, less_equal: Comparaciones
     * - between: Entre dos valores
     * - in, not_in: En lista de valores
     * - is_empty, is_not_empty: Vacío/no vacío
     * - current_user: Usuario actual (para campos de usuario)
     * - current_date: Fecha actual
     * - this_week, this_month, this_year: Rangos de fecha relativos
     * 
     * @param QueryGenerator $queryGenerator
     * @param array $filters - Configuración de filtros
     * @param Users $currentUser
     * @return void
     */
    public static function applyGanttFilters($queryGenerator, $filters, $currentUser) {
        if (empty($filters['conditions'])) {
            return;
        }
        
        $glue = isset($filters['logic']) && strtoupper($filters['logic']) === 'OR' 
            ? QueryGenerator::$OR 
            : QueryGenerator::$AND;
        
        foreach ($filters['conditions'] as $condition) {
            $field = $condition['field'];
            $operator = $condition['operator'];
            $value = isset($condition['value']) ? $condition['value'] : null;
            
            // Procesar operadores especiales
            switch ($operator) {
                case 'current_user':
                    $queryGenerator->addCondition($field, $currentUser->id, 'e', $glue);
                    break;
                    
                case 'current_date':
                    $queryGenerator->addCondition($field, date('Y-m-d'), 'e', $glue);
                    break;
                    
                case 'this_week':
                    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
                    $queryGenerator->addCondition($field, $startOfWeek, 'h', $glue);
                    $queryGenerator->addConditionGlue(QueryGenerator::$AND);
                    $queryGenerator->addCondition($field, $endOfWeek, 'l');
                    break;
                    
                case 'this_month':
                    $startOfMonth = date('Y-m-01');
                    $endOfMonth = date('Y-m-t');
                    $queryGenerator->addCondition($field, $startOfMonth, 'h', $glue);
                    $queryGenerator->addConditionGlue(QueryGenerator::$AND);
                    $queryGenerator->addCondition($field, $endOfMonth, 'l');
                    break;
                    
                case 'this_year':
                    $startOfYear = date('Y-01-01');
                    $endOfYear = date('Y-12-31');
                    $queryGenerator->addCondition($field, $startOfYear, 'h', $glue);
                    $queryGenerator->addConditionGlue(QueryGenerator::$AND);
                    $queryGenerator->addCondition($field, $endOfYear, 'l');
                    break;
                    
                case 'equals':
                    $queryGenerator->addCondition($field, $value, 'e', $glue);
                    break;
                    
                case 'not_equals':
                    $queryGenerator->addCondition($field, $value, 'n', $glue);
                    break;
                    
                case 'contains':
                    $queryGenerator->addCondition($field, $value, 'c', $glue);
                    break;
                    
                case 'not_contains':
                    $queryGenerator->addCondition($field, $value, 'k', $glue);
                    break;
                    
                case 'starts_with':
                    $queryGenerator->addCondition($field, $value, 's', $glue);
                    break;
                    
                case 'ends_with':
                    $queryGenerator->addCondition($field, $value, 'ew', $glue);
                    break;
                    
                case 'greater_than':
                    $queryGenerator->addCondition($field, $value, 'g', $glue);
                    break;
                    
                case 'less_than':
                    $queryGenerator->addCondition($field, $value, 'l', $glue);
                    break;
                    
                case 'greater_equal':
                    $queryGenerator->addCondition($field, $value, 'h', $glue);
                    break;
                    
                case 'less_equal':
                    $queryGenerator->addCondition($field, $value, 'l', $glue);
                    break;
                    
                case 'between':
                    if (is_array($value) && count($value) >= 2) {
                        $queryGenerator->addCondition($field, $value[0], 'h', $glue);
                        $queryGenerator->addConditionGlue(QueryGenerator::$AND);
                        $queryGenerator->addCondition($field, $value[1], 'l');
                    }
                    break;
                    
                case 'in':
                    if (is_array($value)) {
                        $queryGenerator->addCondition($field, implode(',', $value), 'e', $glue);
                    } else {
                        $queryGenerator->addCondition($field, $value, 'e', $glue);
                    }
                    break;
                    
                case 'not_in':
                    if (is_array($value)) {
                        $queryGenerator->addCondition($field, implode(',', $value), 'n', $glue);
                    } else {
                        $queryGenerator->addCondition($field, $value, 'n', $glue);
                    }
                    break;
                    
                case 'is_empty':
                    $queryGenerator->addCondition($field, '', 'e', $glue);
                    break;
                    
                case 'is_not_empty':
                    $queryGenerator->addCondition($field, '', 'n', $glue);
                    break;
                    
                default:
                    // Operador no reconocido, intentar usar como operador directo
                    $queryGenerator->addCondition($field, $value, $operator, $glue);
                    break;
            }
        }
    }
}
