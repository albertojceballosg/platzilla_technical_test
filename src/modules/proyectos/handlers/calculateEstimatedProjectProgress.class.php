<?php

/**
 * Calcula el progreso esperado del proyecto basado en el progreso estimado de las tareas
 * usando el mismo algoritmo que se usa para el avance real del proyecto
 */

class CalculateEstimatedProjectProgress
{
    private $adb;
    
    public function __construct(PearDatabase $adb)
    {
        $this->adb = $adb;
    }
    
    /**
     * Calcula el progreso esperado de un trabajo basado en el progreso estimado de sus tareas
     * 
     * @param int $workId ID del trabajo
     * @return float Progreso estimado del trabajo
     */
    public function calculateWorkEstimatedProgress($workId)
    {
        if (empty($workId)) {
            return 0;
        }
        
        // Consultar el progreso estimado de las tareas del trabajo
        $query = 'SELECT 
                    act.estimated_progress,
                    COUNT(*) as task_count,
                    SUM(CASE WHEN act.estimated_progress IS NOT NULL AND act.estimated_progress > 0 THEN 1 ELSE 0 END) as valid_tasks
                  FROM vtiger_activity act
                  INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
                  WHERE act.orden_de_trabajoid = ? 
                  AND act.activitytype IN (?, ?)'; // Task y Event
        
        $params = array(
            $workId,
            'Task',
            'Event'
        );
        
        $result = $this->adb->pquery($query, $params);
        
        if (!$result || $this->adb->num_rows($result) == 0) {
            return 0;
        }
        
        $row = $this->adb->fetchByAssoc($result, -1, false);
        DatabaseUtils::closeResult($result);
        
        $totalTasks = intval($row['task_count']);
        $validTasks = intval($row['valid_tasks']);
        
        if ($validTasks == 0) {
            return 0;
        }
        
        // Calcular el promedio del progreso estimado de las tareas
        $query = 'SELECT AVG(act.estimated_progress) as avg_progress
                  FROM vtiger_activity act
                  INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
                  WHERE act.orden_de_trabajoid = ? 
                  AND act.activitytype IN (?, ?)
                  AND act.estimated_progress IS NOT NULL 
                  AND act.estimated_progress > 0';
        
        $result = $this->adb->pquery($query, $params);
        
        if (!$result || $this->adb->num_rows($result) == 0) {
            return 0;
        }
        
        $row = $this->adb->fetchByAssoc($result, -1, false);
        DatabaseUtils::closeResult($result);
        
        $avgProgress = floatval($row['avg_progress']);
        
        return round($avgProgress, 2);
    }
    
    /**
     * Calcula el progreso esperado del proyecto basado en el progreso esperado de sus trabajos
     * 
     * @param int $projectId ID del proyecto
     * @return float Progreso esperado del proyecto
     */
    public function calculateProjectEstimatedProgress($projectId)
    {
        if (empty($projectId)) {
            return 0;
        }
        
        // Consultar los trabajos del proyecto con su progreso esperado y factor de contribución
        $query = 'SELECT 
                    pw.job_contribution_factor,
                    ot.expected_work_progress
                  FROM vtiger_project_works pw
                  INNER JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job
                  INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid AND ce.deleted = 0
                  WHERE pw.crmid = ?
                  ORDER BY pw.stageid';
        
        $result = $this->adb->pquery($query, array($projectId));
        
        if (!$result || $this->adb->num_rows($result) == 0) {
            return 0;
        }
        
        $totalProgress = 0;
        
        while ($row = $this->adb->fetchByAssoc($result)) {
            $contributionFactor = floatval($row['job_contribution_factor']);
            $expectedWorkProgress = floatval($row['expected_work_progress']);
            
            // Aplicar el factor de contribución del trabajo
            // Fórmula: (factor_contribución × progreso_esperado_trabajo) / 100
            $contribution = ($contributionFactor * $expectedWorkProgress) / 100;
            $totalProgress += $contribution;
        }
        
        DatabaseUtils::closeResult($result);
        
        return round($totalProgress, 2);
    }
    
    /**
     * Actualiza el campo estimated_project_progress en vtiger_proyectos
     * 
     * @param int $projectId ID del proyecto
     * @return bool True si se actualizó correctamente
     */
    public function updateProjectEstimatedProgress($projectId)
    {
        if (empty($projectId)) {
            return false;
        }
        
        $estimatedProgress = $this->calculateProjectEstimatedProgress($projectId);
        
        // Actualizar el campo en vtiger_proyectos
        $updateQuery = 'UPDATE vtiger_proyectos 
                        SET estimated_project_progress = ? 
                        WHERE proyectosid = ?';
        
        $result = $this->adb->pquery($updateQuery, array($estimatedProgress, $projectId));
        
        if ($result) {
            $this->logUpdate($projectId, $estimatedProgress);
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualiza todos los proyectos con su progreso esperado
     * 
     * @return array Estadísticas de la actualización
     */
    public function updateAllProjectsEstimatedProgress()
    {
        $stats = array(
            'total_projects' => 0,
            'updated_projects' => 0,
            'failed_projects' => 0,
            'errors' => array()
        );
        
        // Obtener todos los proyectos activos
        $query = 'SELECT proyectosid, projectname 
                  FROM vtiger_proyectos 
                  INNER JOIN vtiger_crmentity ce ON ce.crmid = proyectosid AND ce.deleted = 0';
        
        $result = $this->adb->pquery($query);
        
        if (!$result) {
            $stats['errors'][] = 'Error al consultar proyectos';
            return $stats;
        }
        
        $stats['total_projects'] = $this->adb->num_rows($result);
        
        while ($row = $this->adb->fetchByAssoc($result)) {
            $projectId = intval($row['proyectosid']);
            $projectName = $row['projectname'];
            
            try {
                if ($this->updateProjectEstimatedProgress($projectId)) {
                    $stats['updated_projects']++;
                } else {
                    $stats['failed_projects']++;
                    $stats['errors'][] = "No se pudo actualizar el proyecto: {$projectName} (ID: {$projectId})";
                }
            } catch (Exception $e) {
                $stats['failed_projects']++;
                $stats['errors'][] = "Error en proyecto {$projectName}: " . $e->getMessage();
            }
        }
        
        DatabaseUtils::closeResult($result);
        
        return $stats;
    }
    
    /**
     * Registra la actualización en el log
     * 
     * @param int $projectId
     * @param float $progress
     */
    private function logUpdate($projectId, $progress)
    {
        // Aquí se puede agregar logging si se necesita
        // Por ahora, solo actualizamos el campo
    }
    
    /**
     * Obtiene el progreso esperado actual de un proyecto
     * 
     * @param int $projectId
     * @return float
     */
    public function getProjectEstimatedProgress($projectId)
    {
        if (empty($projectId)) {
            return 0;
        }
        
        $query = 'SELECT estimated_project_progress 
                  FROM vtiger_proyectos 
                  WHERE proyectosid = ?';
        
        $result = $this->adb->pquery($query, array($projectId));
        
        if (!$result || $this->adb->num_rows($result) == 0) {
            return 0;
        }
        
        $progress = floatval($this->adb->query_result($result, 0, 'estimated_project_progress'));
        DatabaseUtils::closeResult($result);
        
        return $progress;
    }
}

?>
