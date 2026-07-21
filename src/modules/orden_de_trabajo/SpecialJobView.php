<?php
/**
 * SpecialJobView - Vista especial de expediente de trabajo
 * 
 * Muestra toda la información del trabajo en una sola pantalla:
 * - Información del trabajo (incluyendo situación destacada)
 * - Archivos adjuntos del trabajo
 * - Tareas con sus reportes de avance y feedbacks
 * 
 * @author Platzilla
 * @package orden_de_trabajo
 */


require_once('data/CRMEntity.php');
require_once('include/fields/DateTimeField.php');
require_once('include/fields/CurrencyField.php');
require_once('include/utils/NumberHelper.class.php');
require_once('include/platzilla/Data/ActivityReportManager.php');
require_once('include/platzilla/Data/ActivityFeedbackManager.php');
require_once('include/utils/AttachmentsUtils.class.php');
require_once('include/platzilla/Utils/DatabaseUtils.php');
require_once('modules/grid_view/lib/GridViewHelper.class.php');
require_once('include/utils/utils.php');

class SpecialJobView {
    
    /** @var PearDatabase */
    private $adb;
    
    /** @var Users */
    private $currentUser;
    
    /** @var NumberHelper */
    private $numberingHelper;
    
    public function __construct($adb, $currentUser) {
        $this->adb = $adb;
        $this->currentUser = $currentUser;
        $this->numberingHelper = NumberHelper::getInstance($adb, $currentUser);
    }
    
    /**
     * Obtener la información completa del trabajo
     * @param int $workId ID del trabajo (orden_de_trabajoid)
     * @return array Datos del trabajo
     */
    
    public function getWorkInfo($workId) {
        if (empty($workId)) {
            return null;
        }
        // Detectar columnas opcionales que pueden no existir en todas las instalaciones
        $optionalCols = array('contrato_', 'plan_de_servicios', 'proyecto', 'importance_work',
                              'work_priority', 'priority_index', 'fecha_prevista', 'fecha_estim_fin');
        $includedCols = array();
        foreach ($optionalCols as $col) {
            $tr = $this->adb->pquery("SELECT `{$col}` FROM vtiger_orden_de_trabajo LIMIT 1", array());
            if ($tr !== false && $tr !== null) {
                $includedCols[$col] = true;
            }
        }
        
        $optSelect = '';
        foreach ($includedCols as $col => $exists) {
            $optSelect .= ",\n            trab.`{$col}`";
        }
        
        $sql = "SELECT 
            trab.orden_de_trabajoid,
            trab.cod_orden_de_tra,
            trab.titulo,
            trab.descripcion,
            trab.cliente,
            trab.comentarios_resultado,
            trab.unidades_de_medida,
            trab.numero_unidades_planificadas,
            trab.unidades_consumidas,
            trab.overall_progress_perc,
            trab.expected_work_progress,
            trab.work_estimated_cost,
            trab.cost_work_performed,
            trab.work_situation,
            trab.fecha_de_emision,
            trab.fecha_de_inicio,
            trab.fecha_real_de_ci,
            trab.estado_de_la_orden,
            trab.tipo_dactividad,
            crm.smownerid,
            crm.createdtime,
            crm.modifiedtime,
            CONCAT(u.first_name, ' ', u.last_name) as assigned_user_name"
            . $optSelect . "
        FROM vtiger_orden_de_trabajo as trab 
        INNER JOIN vtiger_crmentity as crm ON crm.deleted = 0 AND trab.orden_de_trabajoid = crm.crmid
        LEFT JOIN vtiger_users u ON u.id = crm.smownerid
        WHERE trab.orden_de_trabajoid = ?";
        
        $result = $this->adb->pquery($sql, array($workId));
        
        if ($result === false || $result === null) {
            $dbErr = (isset($this->adb->database) && is_object($this->adb->database))
                ? $this->adb->database->ErrorMsg() : 'no db object';
            return '<div class="alert alert-danger" style="margin:20px"><b>SQL falló:</b> ' . htmlspecialchars($dbErr) . '</div>';
        }
        
        $numRows = $this->adb->num_rows($result);
        
        if ($numRows == 0) {
            DatabaseUtils::closeResult($result);
            $diagResult = $this->adb->pquery(
                "SELECT trab.orden_de_trabajoid, crm.deleted 
                 FROM vtiger_orden_de_trabajo trab
                 INNER JOIN vtiger_crmentity crm ON crm.crmid = trab.orden_de_trabajoid
                 WHERE trab.orden_de_trabajoid = ?",
                array($workId)
            );
            if ($diagResult && $this->adb->num_rows($diagResult) > 0) {
                $diagRow = $this->adb->fetchByAssoc($diagResult);
                DatabaseUtils::closeResult($diagResult);
                return '<div class="alert alert-warning" style="margin:20px">Registro ID ' . intval($workId) . ' existe pero crm.deleted=' . $diagRow['deleted'] . '. El INNER JOIN con deleted=0 lo excluye.</div>';
            }
            DatabaseUtils::closeResult($diagResult);
            return '<div class="alert alert-warning" style="margin:20px">Registro ID ' . intval($workId) . ' no existe en vtiger_orden_de_trabajo o vtiger_crmentity.</div>';
        }
        
        $workData = $this->adb->fetchByAssoc($result, -1, false);
        DatabaseUtils::closeResult($result);
        
        // Calcular ratios en PHP (no son columnas BD)
        $unidadesConsumidas     = floatval($workData['unidades_consumidas']);
        $unidadesPlanificadas   = floatval($workData['numero_unidades_planificadas']);
        $costPerformed          = floatval($workData['cost_work_performed']);
        $costEstimated          = floatval($workData['work_estimated_cost']);
        $workData['unit_ratio'] = ($unidadesPlanificadas > 0) ? ($unidadesConsumidas / $unidadesPlanificadas) * 100 : 0;
        $workData['cost_ratio'] = ($costEstimated > 0) ? ($costPerformed / $costEstimated) * 100 : 0;
        
        // Formatear valores según preferencias del usuario
        $workData['overall_progress_perc_formatted'] = $this->numberingHelper->setNumberFormat($workData['overall_progress_perc'], 'overall_progress_perc');
        $workData['expected_work_progress_formatted'] = $this->numberingHelper->setNumberFormat($workData['expected_work_progress'], 'expected_work_progress');
        $workData['work_estimated_cost_formatted'] = $this->numberingHelper->setNumberFormat($workData['work_estimated_cost'], 'work_estimated_cost');
        $workData['cost_work_performed_formatted'] = $this->numberingHelper->setNumberFormat($workData['cost_work_performed'], 'cost_work_performed');
        $workData['unit_ratio_formatted'] = $this->numberingHelper->setNumberFormat($workData['unit_ratio'], null);
        $workData['cost_ratio_formatted'] = $this->numberingHelper->setNumberFormat($workData['cost_ratio'], null);
        $workData['numero_unidades_planificadas_formatted'] = $this->numberingHelper->setNumberFormat($workData['numero_unidades_planificadas'], 'numero_unidades_planificadas');
        $workData['unidades_consumidas_formatted'] = $this->numberingHelper->setNumberFormat($workData['unidades_consumidas'], 'unidades_consumidas');
        
        // Formatear fechas al formato del usuario
        $dateFields = array('fecha_de_emision', 'fecha_de_inicio', 'fecha_real_de_ci', 'fecha_prevista', 'fecha_estim_fin');
        foreach ($dateFields as $field) {
            if (!empty($workData[$field]) && $workData[$field] !== '0000-00-00') {
                $workData[$field . '_formatted'] = DateTimeField::convertToUserFormat($workData[$field], $this->currentUser);
            }
        }
        if (!empty($workData['createdtime'])) {
            $workData['createdtime_formatted'] = DateTimeField::convertToUserFormat(substr($workData['createdtime'], 0, 10), $this->currentUser);
        }
        if (!empty($workData['modifiedtime'])) {
            $workData['modifiedtime_formatted'] = DateTimeField::convertToUserFormat(substr($workData['modifiedtime'], 0, 10), $this->currentUser);
        }
        
        // Traducir valores de picklist
        if (!empty($workData['importance_work'])) {
            $workData['importance_work_translated'] = getTranslatedString($workData['importance_work'], 'orden_de_trabajo');
        }
        if (!empty($workData['work_priority'])) {
            $workData['work_priority_translated'] = getTranslatedString($workData['work_priority'], 'orden_de_trabajo');
        }
        if (!empty($workData['estado_de_la_orden'])) {
            $workData['estado_de_la_orden_translated'] = getTranslatedString($workData['estado_de_la_orden'], 'orden_de_trabajo');
        }
        if (!empty($workData['tipo_dactividad'])) {
            $workData['tipo_dactividad_translated'] = getTranslatedString($workData['tipo_dactividad'], 'orden_de_trabajo');
        }
        
        // Obtener nombre del cliente si existe
        if (!empty($workData['cliente'])) {
            $tbl = $this->adb->pquery("SHOW TABLES LIKE 'vtiger_account'", array());
            if ($tbl && $this->adb->num_rows($tbl) > 0) {
                $clientResult = $this->adb->pquery(
                    "SELECT accountname FROM vtiger_account WHERE accountid = ?",
                    array($workData['cliente'])
                );
                if ($clientResult && $this->adb->num_rows($clientResult) > 0) {
                    $workData['cliente_name'] = $this->adb->query_result($clientResult, 0, 'accountname');
                }
                if ($clientResult) { DatabaseUtils::closeResult($clientResult); }
            }
        }
        
        // Obtener nombre del contrato si existe (columna BD: contrato_)
        $contratoId = !empty($workData['contrato_']) ? $workData['contrato_'] : null;
        $workData['contrato'] = $contratoId;
        if (!empty($contratoId)) {
            $tbl = $this->adb->pquery("SHOW TABLES LIKE 'vtiger_contratos'", array());
            if ($tbl && $this->adb->num_rows($tbl) > 0) {
                $contractResult = $this->adb->pquery(
                    "SELECT contratosid, codigo_de_contrato, nombre_del_contrato 
                     FROM vtiger_contratos WHERE contratosid = ?",
                    array($contratoId)
                );
                if ($contractResult && $this->adb->num_rows($contractResult) > 0) {
                    $workData['contrato_code'] = $this->adb->query_result($contractResult, 0, 'codigo_de_contrato');
                    $workData['contrato_name'] = $this->adb->query_result($contractResult, 0, 'nombre_del_contrato');
                }
                if ($contractResult) { DatabaseUtils::closeResult($contractResult); }
            }
        }
        
        // Obtener nombre del plan de servicios si existe
        if (!empty($workData['plan_de_servicios'])) {
            $tbl = $this->adb->pquery("SHOW TABLES LIKE 'vtiger_plan_de_servicios'", array());
            if ($tbl && $this->adb->num_rows($tbl) > 0) {
                $planResult = $this->adb->pquery(
                    "SELECT plan_de_servicioid, codigo, nombre_del_plan 
                     FROM vtiger_plan_de_servicios WHERE plan_de_servicioid = ?",
                    array($workData['plan_de_servicios'])
                );
                if ($planResult && $this->adb->num_rows($planResult) > 0) {
                    $workData['plan_code'] = $this->adb->query_result($planResult, 0, 'codigo');
                    $workData['plan_name'] = $this->adb->query_result($planResult, 0, 'nombre_del_plan');
                }
                if ($planResult) { DatabaseUtils::closeResult($planResult); }
            }
        }
        
        // Obtener nombre del proyecto si existe
        if (!empty($workData['proyecto'])) {
            $tbl = $this->adb->pquery("SHOW TABLES LIKE 'vtiger_proyectos'", array());
            if ($tbl && $this->adb->num_rows($tbl) > 0) {
                $proyectoResult = $this->adb->pquery(
                    "SELECT proyectosid, nombre as projectname FROM vtiger_proyectos WHERE proyectosid = ?",
                    array($workData['proyecto'])
                );
                if ($proyectoResult && $this->adb->num_rows($proyectoResult) > 0) {
                    $workData['proyecto_name'] = $this->adb->query_result($proyectoResult, 0, 'projectname');
                }
                if ($proyectoResult) { DatabaseUtils::closeResult($proyectoResult); }
            }
        }
        
        return $workData;
    }
    
    /**
     * Obtener archivos adjuntos del trabajo
     * @param int $workId ID del trabajo
     * @return array Archivos adjuntos
     */
    public function getWorkAttachments($workId) {
        if (empty($workId)) {
            return array();
        }
        
        // Buscar attachments relacionados con el trabajo
        $result = $this->adb->pquery(
            "SELECT 
                a.attachmentsid,
                a.name,
                a.type,
                CONCAT(a.path, a.attachmentsid, '_', a.name) as uri,
                a.description
            FROM vtiger_attachments a
            INNER JOIN vtiger_seattachmentsrel sar ON sar.attachmentsid = a.attachmentsid
            INNER JOIN vtiger_crmentity crm ON crm.crmid = sar.crmid AND crm.deleted = 0
            WHERE sar.crmid = ? AND crm.setype = ?
            ORDER BY a.attachmentsid DESC",
            array($workId, 'orden_de_trabajo Attachment')
        );
        
        $attachments = array();
        if ($this->adb->num_rows($result) > 0) {
            while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
                $attachments[] = $row;
            }
        }
        DatabaseUtils::closeResult($result);
        
        return $attachments;
    }
    
    /**
     * Obtener documentos relacionados con el trabajo (módulo Documents / vtiger_senotesrel)
     * @param int $workId ID del trabajo
     * @return array Documentos relacionados
     */
    public function getWorkDocuments($workId) {
        if (empty($workId)) {
            return array();
        }
        $sql = "SELECT
                a.attachmentsid,
                a.name,
                a.type,
                a.path,
                CONCAT(a.path, a.attachmentsid, '_', a.name) AS uri
            FROM vtiger_seattachmentsrel sear
            INNER JOIN vtiger_attachments a ON a.attachmentsid = sear.attachmentsid
            INNER JOIN vtiger_crmentity crm ON crm.crmid = a.attachmentsid AND crm.deleted = 0
            WHERE sear.crmid = ?
            ORDER BY a.attachmentsid DESC";
        $result = $this->adb->pquery($sql, array($workId));
        $numRows = ($result) ? $this->adb->num_rows($result) : 0;
        global $PORTAL_URL;
        $baseUrl = rtrim($PORTAL_URL, '/');
        $documents = array();
        if ($result && $numRows > 0) {
            while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
                $row['download_url'] = $baseUrl . '/' . $row['uri'];
                $documents[] = $row;
            }
        }
        if ($result) {
            DatabaseUtils::closeResult($result);
        }
        return $documents;
    }

    /**
     * Obtener tareas relacionadas con el trabajo
     * @param int $workId ID del trabajo
     * @return array Tareas con reportes y feedbacks
     */
    public function getWorkTasks($workId) {
        if (empty($workId)) {
            return array();
        }
        // Detectar campos opcionales de vtiger_activity
        $actOptFields = array(
            'time_start', 'estimated_progress', 'progress_weighting_factor',
            'progress_ratio', 'combined_condition', 'importance', 'priority',
            'location', 'show_in_matrix', 'related_id', 'related_to'
        );
        $actExtraSelect = '';
        foreach ($actOptFields as $f) {
            $tr = $this->adb->pquery("SELECT `{$f}` FROM vtiger_activity LIMIT 1", array());
            if ($tr !== false && $tr !== null) {
                $actExtraSelect .= ",\n                act.{$f}";
            }
        }

        // Detectar si existen las tablas de proveedor
        $hasSupplierRel = false;
        $trSup = $this->adb->pquery("SELECT activityid FROM vtiger_supplieractivityrel LIMIT 1", array());
        if ($trSup !== false && $trSup !== null) { $hasSupplierRel = true; }

        $supplierSelect = $hasSupplierRel ? ",\n                srel.proveedoresid,\n                prov.alias AS supplier_name" : '';
        $supplierJoin   = $hasSupplierRel
            ? "\n            LEFT JOIN vtiger_supplieractivityrel srel ON srel.activityid = act.activityid\n            LEFT JOIN vtiger_proveedores prov ON prov.proveedoresid = srel.proveedoresid"
            : '';

        $sqlTasks = "SELECT 
                act.activityid,
                act.subject,
                act.activitytype,
                act.date_start,
                act.due_date,
                act.duration_hours,
                act.eventstatus,
                act.estimated_time,
                act.estimated_time_unit,
                act.estimated_cost,
                act.progress,
                crm.smownerid,
                crm.description,
                CONCAT(u.first_name, ' ', u.last_name) as assigned_user_name"
            . $actExtraSelect
            . $supplierSelect . "
            FROM vtiger_activity act
            INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
            INNER JOIN vtiger_seactivityrel sar ON sar.activityid = act.activityid
            LEFT JOIN vtiger_users u ON u.id = crm.smownerid"
            . $supplierJoin . "
            WHERE sar.crmid = ? AND act.activitytype != 'Job'
            ORDER BY act.date_start ASC, act.activityid ASC";

        $result = $this->adb->pquery($sqlTasks, array($workId));
        
        if ($result === false || $result === null) {
            $dbErr = (isset($this->adb->database) && is_object($this->adb->database)) ? $this->adb->database->ErrorMsg() : 'no db';
        }
        
        $numTasks = ($result) ? $this->adb->num_rows($result) : 0;
        
        $tasks = array();
        $reportManager = ActivityReportManager::getInstance($this->adb);
        $feedbackManager = ActivityFeedbackManager::getInstance($this->adb);
        
        if ($numTasks > 0) {
            while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
                // Formatear fechas
                if (!empty($row['date_start']) && $row['date_start'] !== '0000-00-00') {
                    $timeStart = !empty($row['time_start']) ? $row['time_start'] : '00:00:00';
                    $dateTimeField = new DateTimeField($row['date_start'] . ' ' . $timeStart);
                    $row['date_start_formatted'] = $dateTimeField->getDisplayDateTimeValue();
                }
                if (!empty($row['due_date']) && $row['due_date'] !== '0000-00-00') {
                    $dueDateField = new DateTimeField($row['due_date']);
                    $row['due_date_formatted'] = $dueDateField->getDisplayDate();
                }
                
                // Formatear números
                $row['estimated_time_formatted'] = $this->numberingHelper->setNumberFormat($row['estimated_time'], 'estimated_time');
                $row['estimated_cost_formatted'] = $this->numberingHelper->setNumberFormat($row['estimated_cost'], 'estimated_cost');
                $row['progress_formatted'] = $this->numberingHelper->setNumberFormat($row['progress'], 'progress');
                $row['progress_weighting_factor_formatted'] = $this->numberingHelper->setNumberFormat($row['progress_weighting_factor'], 'progress_weighting_factor');
                $row['estimated_progress_formatted'] = $this->numberingHelper->setNumberFormat($row['estimated_progress'], 'estimated_progress');
                
                // Obtener módulo relacionado para los links de edición de reportes
                $row['related_module'] = '';
                if (!empty($row['related_id'])) {
                    $setypeResult = $this->adb->pquery(
                        "SELECT setype FROM vtiger_crmentity WHERE crmid = ?",
                        array($row['related_id'])
                    );
                    if ($this->adb->num_rows($setypeResult) > 0) {
                        $row['related_module'] = $this->adb->query_result($setypeResult, 0, 'setype');
                    }
                    DatabaseUtils::closeResult($setypeResult);
                }
                
                // Traducir valores de picklist
                $row['eventstatus_translated'] = !empty($row['eventstatus']) ? getTranslatedString($row['eventstatus'], 'Calendar') : '';
                $row['activitytype_translated'] = !empty($row['activitytype']) ? getTranslatedString($row['activitytype'], 'Calendar') : '';
                $row['importance_translated'] = !empty($row['importance']) ? getTranslatedString($row['importance'], 'Calendar') : '';
                $row['priority_translated'] = !empty($row['priority']) ? getTranslatedString($row['priority'], 'Calendar') : '';
                
                // Calcular actual_data e indicators (igual que CalendarAjax VIEW-TASK-MODAL)
                $row['actual_data'] = $this->calculateTaskActualData($row['activityid'], $row['related_id'], $row['estimated_time_unit']);
                $row['indicators']  = $this->calculateTaskIndicators($row['actual_data'], floatval($row['estimated_time']), floatval($row['estimated_cost']));
                
                // Obtener reportes de avance de esta tarea
                $row['reports'] = $this->getTaskReports($row['activityid'], $reportManager);
                
                // Obtener feedbacks de esta tarea
                $row['feedbacks'] = $this->getTaskFeedbacks($row['activityid'], $feedbackManager);
                
                $tasks[] = $row;
            }
        }
        DatabaseUtils::closeResult($result);
        
        return $tasks;
    }
    
    /**
     * Obtener la tarea Job del trabajo y sus reportes de avance globales
     * @param int $workId ID del trabajo
     * @return array|null ['task_title' => string, 'reports' => array] o null si no hay
     */
    private function getJobReports($workId) {
        if (empty($workId)) {
            return null;
        }
        
        // Buscar la tarea de tipo Job asociada a este trabajo
        $jobResult = $this->adb->pquery(
            "SELECT act.activityid, act.subject
             FROM vtiger_activity act
             INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
             INNER JOIN vtiger_seactivityrel sar ON sar.activityid = act.activityid
             WHERE sar.crmid = ? AND act.activitytype = 'Job'
             ORDER BY act.activityid ASC
             LIMIT 1",
            array($workId)
        );
        
        if (!$jobResult || $this->adb->num_rows($jobResult) === 0) {
            return null;
        }
        
        $jobRow = $this->adb->fetchByAssoc($jobResult, -1, false);
        DatabaseUtils::closeResult($jobResult);
        
        $jobTaskId    = $jobRow['activityid'];
        $jobTaskTitle = $jobRow['subject'];
        
        // Obtener reportes con report_on = 'JOB'
        $reportManager = ActivityReportManager::getInstance($this->adb);
        $reports = $reportManager->fetchActivityReportByActivityId($jobTaskId, null, true);
        
        if (empty($reports)) {
            return null;
        }
        
        $formattedReports = array();
        foreach ($reports as $report) {
            $evidences = AttachmentsUtils::fetchActivityReportAttachments($this->adb, $report->getId());
            
            $rawDateResult = $this->adb->pquery(
                "SELECT reportdate FROM vtiger_activity_report WHERE activityreportid = ?",
                array($report->getId())
            );
            $formattedDatetime = null;
            if ($this->adb->num_rows($rawDateResult) > 0) {
                $rawDate = $this->adb->query_result($rawDateResult, 0, 'reportdate');
                if (!empty($rawDate)) {
                    $reportDateField = new DateTimeField($rawDate);
                    $formattedDatetime = $reportDateField->getDisplayDateTimeValue();
                }
            }
            DatabaseUtils::closeResult($rawDateResult);
            
            // Formatear activity_report_date (fecha del avance)
            $formattedActivityReportDate = null;
            $activityReportDateRaw = $report->getActivityReportDate();
            if (!empty($activityReportDateRaw)) {
                $ardField = new DateTimeField($activityReportDateRaw);
                $formattedActivityReportDate = $ardField->getDisplayDate();
            }
            
            $formattedReports[] = array(
                'activityreportid' => $report->getId(),
                'title'            => $report->getTitle(),
                'report'           => $report->getReport(),
                'progress_formatted' => $this->numberingHelper->setNumberFormat($report->getProgress(), null),
                'formatted_datetime' => $formattedDatetime,
                'activity_report_date' => $formattedActivityReportDate,
                'duration'         => $this->numberingHelper->setNumberFormat($report->getTimeDuration(), null),
                'cost'             => $this->numberingHelper->setNumberFormat($report->getActualCost(), null),
                'user_name'        => $report->getUserName(),
                'evidences'        => $evidences,
            );
        }
        
        return array(
            'task_title' => $jobTaskTitle,
            'reports'    => $formattedReports,
        );
    }
    
    /**
     * Calcular datos reales acumulados de una tarea (sumando sus reportes)
     * Lógica idéntica a CalendarAjax VIEW-TASK-MODAL
     * @param int $taskId
     * @param int $relatedId ID del trabajo relacionado
     * @param string $estimatedTimeUnit Unidad de medida de la tarea
     * @return array actual_data
     */
    private function calculateTaskActualData($taskId, $relatedId, $estimatedTimeUnit) {
        $actualData = array(
            'total_duration'        => 0,
            'total_cost'            => 0,
            'min_date'              => '',
            'max_date'              => '',
            'has_reports'           => false,
            'total_duration_display'=> '0',
            'total_cost_display'    => '0'
        );
        
        $reportsResult = $this->adb->pquery(
            'SELECT 
                SUM(
                    CASE 
                        WHEN ar.activityid = ? THEN ar.duration_time
                        WHEN act.activitytype = ? AND act.related_id = ? AND act.estimated_time_unit = ? THEN ar.duration_time
                        ELSE 0
                    END
                ) as total_duration_time,
                SUM(
                    CASE 
                        WHEN ar.activityid = ? THEN ar.actual_cost
                        WHEN act.activitytype = ? AND act.related_id = ? THEN ar.actual_cost
                        ELSE 0
                    END
                ) as total_actual_cost,
                MIN(ar.reportdate) as min_date,
                MAX(ar.reportdate) as max_date,
                COUNT(*) as report_count
            FROM vtiger_activity_report ar
            INNER JOIN vtiger_activity act ON act.activityid = ar.activityid
            WHERE ar.deleted = 0 AND (ar.activityid = ? 
               OR (act.activitytype = ? AND act.related_id = ?))',
            array(
                $taskId, 'Job', $relatedId, $estimatedTimeUnit,
                $taskId, 'Job', $relatedId,
                $taskId, 'Job', $relatedId
            )
        );
        
        if ($this->adb->num_rows($reportsResult) > 0) {
            $reportRow = $this->adb->fetchByAssoc($reportsResult, -1, false);
            if ($reportRow['report_count'] > 0) {
                $actualData['has_reports']    = true;
                $actualData['total_duration'] = floatval($reportRow['total_duration_time']);
                $actualData['total_cost']     = floatval($reportRow['total_actual_cost']);
                
                if (!empty($reportRow['min_date']) && $reportRow['min_date'] !== '0000-00-00') {
                    $minDateField = new DateTimeField($reportRow['min_date']);
                    $actualData['min_date'] = $minDateField->getDisplayDate();
                }
                if (!empty($reportRow['max_date']) && $reportRow['max_date'] !== '0000-00-00') {
                    $maxDateField = new DateTimeField($reportRow['max_date']);
                    $actualData['max_date'] = $maxDateField->getDisplayDate();
                }
                
                $actualData['total_duration_display'] = $this->numberingHelper->setNumberFormat($actualData['total_duration'], 'duration_time');
                $actualData['total_cost_display']     = $this->numberingHelper->setNumberFormat($actualData['total_cost'], 'actual_cost');
            }
        }
        DatabaseUtils::closeResult($reportsResult);
        
        return $actualData;
    }
    
    /**
     * Calcular indicadores de proporción real/estimado
     * Lógica idéntica a CalendarAjax VIEW-TASK-MODAL
     * @param array  $actualData Resultado de calculateTaskActualData()
     * @param float  $estimatedTime
     * @param float  $estimatedCost
     * @return array indicators
     */
    private function calculateTaskIndicators($actualData, $estimatedTime, $estimatedCost) {
        $indicators = array(
            'duration_ratio'         => 0,
            'cost_ratio'             => 0,
            'duration_ratio_display' => '',
            'cost_ratio_display'     => '',
            'duration_over_budget'   => false,
            'cost_over_budget'       => false
        );
        
        if ($actualData['has_reports']) {
            if ($estimatedTime > 0) {
                $indicators['duration_ratio']         = ($actualData['total_duration'] / $estimatedTime) * 100;
                $indicators['duration_ratio_display'] = $this->numberingHelper->setNumberFormat($indicators['duration_ratio'], null) . '%';
                $indicators['duration_over_budget']   = $indicators['duration_ratio'] > 100;
            }
            if ($estimatedCost > 0) {
                $indicators['cost_ratio']         = ($actualData['total_cost'] / $estimatedCost) * 100;
                $indicators['cost_ratio_display'] = $this->numberingHelper->setNumberFormat($indicators['cost_ratio'], null) . '%';
                $indicators['cost_over_budget']   = $indicators['cost_ratio'] > 100;
            }
        }
        
        return $indicators;
    }
    
    /**
     * Obtener reportes de avance de una tarea
     * @param int $taskId ID de la tarea
     * @param ActivityReportManager $reportManager Instancia del manager
     * @return array Reportes
     */
    private function getTaskReports($taskId, $reportManager) {
        $reports = $reportManager->fetchActivityReportByActivityId($taskId);
        $formattedReports = array();
        
        if (!empty($reports)) {
            foreach ($reports as $report) {
                // Obtener attachments del reporte
                $evidences = AttachmentsUtils::fetchActivityReportAttachments($this->adb, $report->getId());
                
                // Obtener fecha datetime del reporte formateada
                $rawDateResult = $this->adb->pquery(
                    "SELECT reportdate FROM vtiger_activity_report WHERE activityreportid = ?",
                    array($report->getId())
                );
                $formattedDatetime = null;
                if ($this->adb->num_rows($rawDateResult) > 0) {
                    $rawDate = $this->adb->query_result($rawDateResult, 0, 'reportdate');
                    if (!empty($rawDate)) {
                        $reportDateField = new DateTimeField($rawDate);
                        $formattedDatetime = $reportDateField->getDisplayDateTimeValue();
                    }
                }
                DatabaseUtils::closeResult($rawDateResult);
                
                // Formatear activity_report_date (fecha del avance)
                $formattedActivityReportDate = null;
                $activityReportDateRaw = $report->getActivityReportDate();
                if (!empty($activityReportDateRaw)) {
                    $ardField = new DateTimeField($activityReportDateRaw);
                    $formattedActivityReportDate = $ardField->getDisplayDate();
                }
                
                // Verificar permiso de edición
                $canEdit = false;
                if ($this->currentUser) {
                    if ($report->getUserId() !== null && $this->currentUser->id == $report->getUserId()) {
                        $canEdit = true;
                    }
                    if (isset($this->currentUser->is_admin) && $this->currentUser->is_admin == 'on') {
                        $canEdit = true;
                    }
                }
                
                $formattedReports[] = array(
                    'id'               => $report->getId(),
                    'activityreportid' => $report->getId(),
                    'activityid'       => $report->getActivityId(),
                    'title'            => $report->getTitle(),
                    'report'           => $report->getReport(),
                    'progress'         => $report->getProgress(),
                    'progress_formatted' => $this->numberingHelper->setNumberFormat($report->getProgress(), null),
                    'formatted_datetime' => $formattedDatetime,
                    'activity_report_date' => $formattedActivityReportDate,
                    'duration_time'    => $report->getTimeDuration(),
                    'duration'         => $this->numberingHelper->setNumberFormat($report->getTimeDuration(), null),
                    'actual_cost'      => $report->getActualCost(),
                    'cost'             => $this->numberingHelper->setNumberFormat($report->getActualCost(), null),
                    'user_name'        => $report->getUserName(),
                    'user_avatar'      => $report->getUserAvatar(),
                    'userid'           => $report->getUserId(),
                    'evidences'        => $evidences,
                    'can_edit'         => $canEdit
                );
            }
        }
        
        return $formattedReports;
    }
    
    /**
     * Obtener feedbacks de una tarea
     * @param int $taskId ID de la tarea
     * @param ActivityFeedbackManager $feedbackManager Instancia del manager
     * @return array Feedbacks
     */
    private function getTaskFeedbacks($taskId, $feedbackManager) {
        $feedbacks = $feedbackManager->fetchActivityFeedbackByActivity($taskId);
        $formattedFeedbacks = array();
        
        if (!empty($feedbacks)) {
            foreach ($feedbacks as $feedback) {
                // Obtener título del reporte asociado al feedback
                $reportTitle = '';
                $reportResult = $this->adb->pquery(
                    "SELECT ar.title 
                     FROM vtiger_activity_report ar
                     INNER JOIN vtiger_activity_report2feedback ar2f ON ar2f.activityreportid = ar.activityreportid
                     WHERE ar2f.activityfeedbackid = ?",
                    array($feedback->getId())
                );
                if ($this->adb->num_rows($reportResult) > 0) {
                    $reportTitle = $this->adb->query_result($reportResult, 0, 'title');
                }
                DatabaseUtils::closeResult($reportResult);
                
                // Verificar permiso de edición
                $canEdit = false;
                if ($this->currentUser) {
                    if ($feedback->getUserId() !== null && $this->currentUser->id == $feedback->getUserId()) {
                        $canEdit = true;
                    }
                    if (isset($this->currentUser->is_admin) && $this->currentUser->is_admin == 'on') {
                        $canEdit = true;
                    }
                }
                
                $formattedFeedbacks[] = array(
                    'id'            => $feedback->getId(),
                    'activity_id'   => $feedback->getActivityId(),
                    'title'         => $feedback->getTitle(),
                    'feedback'      => $feedback->getFeedback(),
                    'feedback_date' => $feedback->getFeedbackDate(),
                    'user_name'     => $feedback->getUserName(),
                    'user_avatar'   => $feedback->getUserAvatar(),
                    'user_id'       => $feedback->getUserId(),
                    'report_title'  => $reportTitle,
                    'can_edit'      => $canEdit
                );
            }
        }
        
        return $formattedFeedbacks;
    }
    
    /**
     * Obtener tarjetas de relaciones para el trabajo
     * @param int $workId ID del trabajo
     * @return array|null Tarjetas de relaciones formateadas
     */
    public function getRelatedListCards($workId) {
        if (empty($workId)) {
            return null;
        }
        
        // Obtener definiciones de relaciones
        $entity = CRMEntity::getInstance('orden_de_trabajo');
        $entity->id = $workId;
        
        // Inicializar variables del módulo (importante para las funciones de relación)
        global $currentModule;
        $currentModule = 'orden_de_trabajo';
        vtlib_setup_modulevars('orden_de_trabajo', $entity);

        $relatedLists = getRelatedLists('orden_de_trabajo', $entity);

        if (empty($relatedLists) || !is_array($relatedLists) || count($relatedLists) == 0) {
            return null;
        }
        
        global $app_strings, $mod_strings, $theme, $currentModule;
        
        $currentModule = 'orden_de_trabajo';
        $currentTabId  = getTabid($currentModule);
        $recordId      = $workId;
        $relatedCards  = array();
        
        foreach ($relatedLists as $header => $relatedList) {
            if ((!empty($relatedList['relationId'])) && ($relatedList['relationId'] > 0)) {
                try {
                    $relationInfo    = getRelatedListInfoById($relatedList['relationId']);
                    $relatedModule   = $relatedList['tabName'];
                    $function_name   = $relationInfo['functionName'];
                    
                    // Verificar que el método existe en la entidad
                    if (empty($function_name) || !method_exists($entity, $function_name)) {
                        // Intentar usar get_related_list o get_dependents_list como fallback
                        if (method_exists($entity, 'get_related_list')) {
                            $function_name = 'get_related_list';
                        } elseif (method_exists($entity, 'get_dependents_list')) {
                            $function_name = 'get_dependents_list';
                        } else {
                            continue;
                        }
                    }
                    
                    
                    // Obtener datos de la relación según el tipo de método
                    // get_dependents_list tiene solo 4 parámetros, get_related_list tiene 7
                    if ($function_name === 'get_dependents_list') {
                        $relatedListData = $entity->$function_name(
                            $recordId,
                            $currentTabId,
                            $relatedList['related_tabid'],
                            (!empty($relatedList['actions'])) ? $relatedList['actions'] : false
                        );
                    } else {
                        $relatedListData = $entity->$function_name(
                            $recordId,
                            $currentTabId,
                            $relatedList['related_tabid'],
                            (!empty($relatedList['actions'])) ? $relatedList['actions'] : $relatedList['fieldName'],
                            false,
                            $relatedList['relationId'],
                            true
                        );
                    }
                    
                    // Si entries está vacío con get_related_list, intentar con get_dependents_list como fallback
                    if (isset($relatedListData['entries']) && is_array($relatedListData['entries']) && count($relatedListData['entries']) == 0 && method_exists($entity, 'get_dependents_list')) {
                        $relatedListData = $entity->get_dependents_list(
                            $recordId,
                            $currentTabId,
                            $relatedList['related_tabid'],
                            (!empty($relatedList['actions'])) ? $relatedList['actions'] : false
                        );
                    }
                    
                    // Generar botón de ver relación (inline del método privado GridViewHelper::getRelatedListCustomButtons)
                    $actionOption = (!empty($relatedList['actions'])) ? $relatedList['actions'] : $relatedList['fieldName'];
                    $urlHeader    = urlencode($header);
                    $customButton = "<a href='index.php?module=grid_view&action=GridViewAjaxUtils&record={$recordId}&formodule={$currentModule}&relatedlist={$urlHeader}@{$relatedList['related_tabid']}@{$relatedList['relationId']}@{$actionOption}&function=RELATED_INFO&Ajax=true' " .
                                    "class='btn btn-success btn-circle btn-xs' data-title='{$header}' data-width='950' data-toggle='lightbox'>" .
                                    "<i class='fa fa-eye'></i></a>&nbsp;";
                    $customButton .= $relatedListData['CUSTOM_BUTTON'];
                    $navi         = $relatedListData['navigation'];
                    
                    // Limpiar datos para el template
                    unset($relatedListData['CUSTOM_BUTTON']);
                    unset($relatedListData['navigation']);
                    
                    // Verificar estructura de datos - asegurar que existe 'entries'
                    if (!isset($relatedListData['entries'])) {
                        $relatedListData['entries'] = array();
                    }
                    if (!isset($relatedListData['header'])) {
                        $relatedListData['header'] = array();
                    }
                    
                    
                    // Procesar entries para agregar target="_blank" a los links
                    if (!empty($relatedListData['entries']) && is_array($relatedListData['entries'])) {
                        foreach ($relatedListData['entries'] as $entryRecordId => &$recordData) {
                            if (!empty($recordData['records']) && is_array($recordData['records'])) {
                                foreach ($recordData['records'] as $fieldName => &$fieldValue) {
                                    // Agregar target="_blank" a los links HTML
                                    if (strpos($fieldValue, '<a href=') !== false) {
                                        $fieldValue = str_replace('<a href=', '<a target="_blank" href=', $fieldValue);
                                    }
                                }
                            }
                        }
                        unset($recordData, $fieldValue);
                    }
                    
                    $relatedCards[] = array(
                        'currentModule' => $currentModule,
                        'header'        => $header,
                        'customButton'  => $customButton,
                        'navi'          => $navi,
                        'cardData'      => $relatedListData,
                        'relationId'    => $relatedList['relationId'],
                        'relatedTabId'  => $relatedList['related_tabid'],
                        'relatedModule' => $relatedModule
                    );
                } catch (Exception $e) {
                    continue;
                }
            }
        }
        
        $processedCount = count($relatedCards);
        
        return ($processedCount > 0) ? $relatedCards : null;
    }
    
    /**
     * Renderizar la vista especial
     * @param int $workId ID del trabajo
     * @return string HTML renderizado
     */
    public function render($workId, $viewSource = '') {
        global $app_strings, $theme;
        
        // Determinar si debe ocultarse el botón de imprimir (viene desde listview)
        $hidePrintButton = ($viewSource === 'listview');
        
        // Verificar permisos
        if (!isPermitted('orden_de_trabajo', 'DetailView', $workId)) {
            return '<div class="alert alert-danger">' . $app_strings['LBL_PERMISSION_DENIED'] . '</div>';
        }
        
        $workInfo = $this->getWorkInfo($workId);
        
        if (!is_array($workInfo)) {
            return $workInfo
                ? (string)$workInfo
                : '<div class="alert alert-danger" style="margin:20px">Trabajo no encontrado (ID: ' . intval($workId) . ')</div>';
        }
        
        $workAttachments = $this->getWorkAttachments($workId);
        $workDocuments   = $this->getWorkDocuments($workId);
        $tasks           = $this->getWorkTasks($workId);
        $jobReports      = $this->getJobReports($workId);
        $relatedListCards = $this->getRelatedListCards($workId);
        
        
        $userLanguage = (!empty($this->currentUser) && property_exists($this->currentUser, 'language'))
            ? $this->currentUser->language
            : 'es_es';
        $modStrings = return_module_language($userLanguage, 'Calendar');
        
        $isAdmin = isset($this->currentUser->is_admin) && $this->currentUser->is_admin == 'on';
        
        // Preparar Smarty
        $smarty = new vtigerCRM_Smarty();
        $smarty->assign('WORK_INFO',       $workInfo);
        $smarty->assign('WORK_ATTACHMENTS',$workAttachments);
        $smarty->assign('WORK_DOCUMENTS',  $workDocuments);
        $smarty->assign('TASKS',           $tasks);
        $smarty->assign('JOB_REPORTS',     $jobReports);
        $smarty->assign('RELATED_LIST_CARDS', $relatedListCards);
        $smarty->assign('WORK_ID',         $workId);
        $smarty->assign('APP',             $app_strings);
        $smarty->assign('MOD',             $modStrings);
        $smarty->assign('MODULE',          'orden_de_trabajo');
        $smarty->assign('THEME',           $theme);
        $smarty->assign('CURRENT_USER',    $this->currentUser);
        $smarty->assign('IS_ADMIN',        $isAdmin);
        $smarty->assign('NUMBERING_FORMAT', isset($this->currentUser->column_fields['numbering_format'])
            ? $this->currentUser->column_fields['numbering_format']
            : 'AMERICAN_FORMAT');
        $smarty->assign('HIDE_PRINT_BUTTON', $hidePrintButton);
        
        $html = $smarty->fetch('modules/orden_de_trabajo/SpecialJobView.tpl');
        return $html;
    }
    
    /**
     * Punto de entrada estático
     */
    public static function display() {
        global $adb, $current_user;
        $workId = isset($_REQUEST['record']) ? intval($_REQUEST['record']) : 0;
        $viewSource = isset($_REQUEST['view_source']) ? $_REQUEST['view_source'] : '';
        
        if (empty($workId)) {
            echo '<div class="alert alert-danger">ID de trabajo no especificado</div>';
            return;
        }
        
        $view   = new self($adb, $current_user);
        $output = $view->render($workId, $viewSource);
        echo $output;
    }
}

SpecialJobView::display();
