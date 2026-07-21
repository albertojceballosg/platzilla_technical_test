<?php

/**
 * Gestor de actualización automática del progreso esperado del proyecto
 * Reemplaza los triggers para evitar conflictos de recursión en MySQL
 */

require_once('include/database/PearDatabase_Fix.php');

class ProjectEstimatedProgressManager
{
    private $adb;
    
    public function __construct(PearDatabase $adb)
    {
        $this->adb = $adb;
    }
    
    /**
     * Actualiza el progreso esperado de un proyecto específico
     * 
     * @param int $projectId ID del proyecto
     * @return bool True si se actualizó correctamente
     */
    public function updateProjectEstimatedProgress($projectId)
    {
        if (empty($projectId)) {
            return false;
        }
        
        try {
            // FIX: Limpiar resultados pendientes antes de llamar al procedure
            pearDatabase_FlushResults($this->adb);
            
            $result = $this->adb->pquery('CALL sp_update_project_estimated_progress(?)', array($projectId));
            
            // FIX: Consumir cualquier resultado residual del procedure
            pearDatabase_FlushResults($this->adb);
            
            return $result !== false;
        } catch (Exception $e) {
            error_log("Error updating project estimated progress: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza todos los proyectos con su progreso esperado
     * 
     * @return bool True si se actualizó correctamente
     */
    public function updateAllProjectsEstimatedProgress()
    {
        try {
            // FIX: Limpiar resultados pendientes antes de llamar al procedure
            pearDatabase_FlushResults($this->adb);
            
            $result = $this->adb->pquery('CALL sp_update_all_projects_estimated_progress()');
            
            // FIX: Consumir cualquier resultado residual del procedure
            pearDatabase_FlushResults($this->adb);
            
            return $result !== false;
        } catch (Exception $e) {
            error_log("Error updating all projects estimated progress: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza los proyectos asociados a un trabajo específico
     * Este método debe llamarse cuando se modifica un trabajo
     * 
     * @param int $workId ID del trabajo
     * @return bool True si se actualizó correctamente
     */
    public function updateProjectsByWorkId($workId)
    {
        if (empty($workId)) {
            return false;
        }
        
        try {
            // FIX: Limpiar resultados pendientes antes de llamar al procedure
            pearDatabase_FlushResults($this->adb);
            
            $result = $this->adb->pquery('CALL sp_update_projects_by_work_id(?)', array($workId));
            
            // FIX: Consumir cualquier resultado residual del procedure
            pearDatabase_FlushResults($this->adb);
            
            return $result !== false;
        } catch (Exception $e) {
            error_log("Error updating projects by work ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene el progreso esperado actual de un proyecto
     * 
     * @param int $projectId ID del proyecto
     * @return float Progreso esperado
     */
    public function getProjectEstimatedProgress($projectId)
    {
        if (empty($projectId)) {
            return 0;
        }
        
        try {
            // FIX: Limpiar resultados pendientes antes de llamar la función
            pearDatabase_FlushResults($this->adb);
            
            $result = $this->adb->pquery('SELECT fn_calculate_project_estimated_progress(?) as progress', array($projectId));
            
            // FIX: Consumir cualquier resultado residual
            pearDatabase_FlushResults($this->adb);
            
            if ($result && $this->adb->num_rows($result) > 0) {
                $row = $this->adb->fetchByAssoc($result);
                return floatval($row['progress']);
            }
        } catch (Exception $e) {
            error_log("Error getting project estimated progress: " . $e->getMessage());
        }
        
        return 0;
    }
    
    /**
     * Actualiza el progreso esperado cuando se guarda un trabajo
     * Método para ser llamado desde taskToProject.class.php
     * 
     * @param int $workId ID del trabajo
     * @return bool True si se actualizó correctamente
     */
    public function onWorkSave($workId)
    {
        return $this->updateProjectsByWorkId($workId);
    }
    
    /**
     * Actualiza el progreso esperado cuando se elimina un trabajo
     * 
     * @param int $workId ID del trabajo
     * @return bool True si se actualizó correctamente
     */
    public function onWorkDelete($workId)
    {
        return $this->updateProjectsByWorkId($workId);
    }
    
    /**
     * Actualiza el progreso esperado cuando se modifica la relación trabajo-proyecto
     * 
     * @param int $projectId ID del proyecto
     * @return bool True si se actualizó correctamente
     */
    public function onProjectWorkChange($projectId)
    {
        return $this->updateProjectEstimatedProgress($projectId);
    }
    
    /**
     * Realiza recálculo completo para mantenimiento
     * 
     * @return array Estadísticas del recálculo
     */
    public function recalculateAll()
    {
        try {
            $result = $this->adb->pquery('CALL sp_recalculate_all_project_estimated_progress()');
            
            if ($result) {
                $stats = array();
                while ($row = $this->adb->fetchByAssoc($result)) {
                    $stats[] = $row;
                }
                return $stats;
            }
        } catch (Exception $e) {
            error_log("Error in recalculate all: " . $e->getMessage());
        }
        
        return array();
    }
    
    /**
     * Obtiene proyectos que necesitan actualización
     * Basado en trabajos modificados recientemente
     * 
     * @param int $hours Horas hacia atrás para buscar cambios
     * @return array IDs de proyectos a actualizar
     */
    public function getProjectsNeedingUpdate($hours = 1)
    {
        $projects = array();
        
        try {
            // Buscar proyectos con trabajos modificados recientemente
            $query = 'SELECT DISTINCT pw.crmid
                      FROM vtiger_project_works pw
                      INNER JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job
                      INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid AND ce.deleted = 0
                      WHERE ot.modifiedtime > DATE_SUB(NOW(), INTERVAL ? HOUR)';
            
            $result = $this->adb->pquery($query, array($hours));
            
            if ($result) {
                while ($row = $this->adb->fetchByAssoc($result)) {
                    $projects[] = intval($row['crmid']);
                }
            }
        } catch (Exception $e) {
            error_log("Error getting projects needing update: " . $e->getMessage());
        }
        
        return $projects;
    }
    
    /**
     * Actualiza proyectos que necesitan actualización basado en tiempo
     * Método para ejecución periódica (cron job)
     * 
     * @param int $hours Horas hacia atrás para buscar cambios
     * @return int Número de proyectos actualizados
     */
    public function updateStaleProjects($hours = 1)
    {
        $projects = $this->getProjectsNeedingUpdate($hours);
        $updated = 0;
        
        foreach ($projects as $projectId) {
            if ($this->updateProjectEstimatedProgress($projectId)) {
                $updated++;
            }
        }
        
        return $updated;
    }
    
    /**
     * Instancia singleton para uso global
     * 
     * @param PearDatabase $adb
     * @return ProjectEstimatedProgressManager
     */
    public static function getInstance(PearDatabase $adb = null)
    {
        static $instance = null;
        
        if ($instance === null) {
            if ($adb === null) {
                global $adb;
            }
            $instance = new self($adb);
        }
        
        return $instance;
    }
}

?>
