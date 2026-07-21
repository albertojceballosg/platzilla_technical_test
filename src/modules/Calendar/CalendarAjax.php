<?php
/*********************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
  * ("License"); You may not use this file except in compliance with the License
  * The Original Code is:  vtiger CRM Open Source
  * The Initial Developer of the Original Code is vtiger.
  * Portions created by vtiger are Copyright (C) vtiger.
  * All Rights Reserved.
 *
  ********************************************************************************/

// Verificar si es una petición para el modal de tareas
$function = isset($_REQUEST['function']) ? $_REQUEST['function'] : '';
if ($function == 'VIEW-TASK-MODAL') {
    // Capturar cualquier output no deseado
    ob_start();
    
    global $adb, $current_user, $app_strings;
    
    require_once('include/fields/DateTimeField.php');
    require_once('include/platzilla/Utils/DatabaseUtils.php');
    require_once('include/platzilla/Data/ActivityReportManager.php');
    require_once('Smarty_setup.php');
    
    // Limpiar buffer antes de enviar JSON
    ob_clean();
    
    // Establecer header JSON
    header('Content-Type: application/json; charset=utf-8');
    
    $activityId = isset($_REQUEST['activityid']) ? intval($_REQUEST['activityid']) : 0;
    
    if (empty($activityId)) {
        echo json_encode(array('success' => false, 'error' => 'MISSING_ACTIVITY_ID'));
        exit;
    }
    
    // Verificar que la tarea existe
    $checkResult = $adb->pquery(
        'SELECT act.activityid, act.subject, act.activitytype, act.categoryid, act.date_start, 
                act.due_date, act.time_start, act.duration_hours, act.duration_minutes,
                act.estimated_time, act.estimated_time_unit, act.estimated_cost, act.progress, act.eventstatus,
                act.priority, act.location, act.importance, act.show_in_matrix,
                act.related_to, act.related_id, act.combined_condition,
                act.estimated_progress, act.progress_ratio,
                crm.smownerid, crm.description, crm.deleted,
                srel.proveedoresid,
                prov.alias AS supplier_name
        FROM vtiger_activity act
        INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid
        LEFT JOIN vtiger_supplieractivityrel srel ON srel.activityid = act.activityid
        LEFT JOIN vtiger_proveedores prov ON prov.proveedoresid = srel.proveedoresid
        WHERE act.activityid = ?',
        array($activityId)
    );
    
    if ($adb->num_rows($checkResult) == 0) {
        DatabaseUtils::closeResult($checkResult);
        echo json_encode(array('success' => false, 'error' => 'TASK_NOT_FOUND'));
        exit;
    }
    
    $taskData = $adb->fetchByAssoc($checkResult);
    DatabaseUtils::closeResult($checkResult);
    
    // Verificar si está eliminada
    if ($taskData['deleted'] == 1) {
        echo json_encode(array('success' => false, 'error' => 'TASK_DELETED'));
        exit;
    }
    
    // Si related_id (columna custom) no está poblado, intentar obtenerlo desde
    // vtiger_seactivityrel, tabla estándar usada para relacionar actividades
    // creadas desde la pestaña Acciones de otros módulos
    if (empty($taskData['related_id'])) {
        $seActivityRelResult = $adb->pquery(
            "SELECT crmid FROM vtiger_seactivityrel WHERE activityid = ?",
            array($activityId)
        );
        if ($adb->num_rows($seActivityRelResult) > 0) {
            $taskData['related_id'] = $adb->query_result($seActivityRelResult, 0, 'crmid');
        }
        DatabaseUtils::closeResult($seActivityRelResult);
    }
    
    // Obtener el módulo relacionado a partir de related_id
    $relatedModule = '';
    if (!empty($taskData['related_id'])) {
        $setypeResult = $adb->pquery(
            "SELECT setype FROM vtiger_crmentity WHERE crmid = ?",
            array($taskData['related_id'])
        );
        if ($adb->num_rows($setypeResult) > 0) {
            $relatedModule = $adb->query_result($setypeResult, 0, 'setype');
        }
        DatabaseUtils::closeResult($setypeResult);
    }
    $taskData['related_module'] = $relatedModule;
    
    // Formatear fechas y horas según idioma del usuario
    $dateTimeField = new DateTimeField($taskData['date_start'] . ' ' . $taskData['time_start']);
    $taskData['formatted_start_date'] = $dateTimeField->getDisplayDate();
    $taskData['formatted_start_time'] = $dateTimeField->getDisplayTime();
    // Combinar fecha y hora en formato completo según idioma
    $taskData['formatted_start_datetime'] = $dateTimeField->getDisplayDateTimeValue();
    
    if (!empty($taskData['due_date'])) {
        $dueDateField = new DateTimeField($taskData['due_date']);
        $taskData['formatted_due_date'] = $dueDateField->getDisplayDate();
        // Para due_date, solo mostrar fecha si no hay hora específica
        $taskData['formatted_due_datetime'] = $dueDateField->getDisplayDate();
    }
    
    // Obtener nombre del usuario asignado
    $ownerResult = $adb->pquery(
        "SELECT CONCAT(first_name, ' ', last_name) as owner_name FROM vtiger_users WHERE id = ?",
        array($taskData['smownerid'])
    );
    if ($adb->num_rows($ownerResult) > 0) {
        $taskData['owner_name'] = $adb->query_result($ownerResult, 0, 'owner_name');
    }
    DatabaseUtils::closeResult($ownerResult);
    
    // Formatear números usando NumberHelper para respetar preferencias del usuario
    require_once('include/utils/NumberHelper.class.php');
    $numberingHelper = NumberHelper::getInstance($adb, $current_user);
    
    $taskData['formatted_estimated_time'] = $numberingHelper->setNumberFormat($taskData['estimated_time'], 'estimated_time');
    $taskData['formatted_estimated_cost'] = $numberingHelper->setNumberFormat($taskData['estimated_cost'], 'estimated_cost');
    $taskData['formatted_progress'] = $numberingHelper->setNumberFormat($taskData['progress'], 'progress');
    
    // Obtener datos de informes de avance (activity reports)
    $actualData = array(
        'total_duration' => 0,
        'total_cost' => 0,
        'min_date' => '',
        'max_date' => '',
        'has_reports' => false
    );
    
    // Incluir reportes de la tarea actual Y de la tarea tipo 'Job' (reporte global de trabajo)
    // Para UNIDADES: solo sumar si la unidad de medida coincide
    // Para COSTO: siempre sumar, sin importar la unidad
    $reportsResult = $adb->pquery(
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
            $activityId, 'Job', $taskData['related_id'], $taskData['estimated_time_unit'],
            $activityId, 'Job', $taskData['related_id'],
            $activityId, 'Job', $taskData['related_id']
        )
    );
    
    if ($adb->num_rows($reportsResult) > 0) {
        $reportRow = $adb->fetchByAssoc($reportsResult);
        if ($reportRow['report_count'] > 0) {
            $actualData['has_reports'] = true;
            
            // Valores numéricos
            $actualData['total_duration'] = floatval($reportRow['total_duration_time']);
            $actualData['total_cost'] = floatval($reportRow['total_actual_cost']);
            
            // Valores fecha - formatear para visualización
            if (!empty($reportRow['min_date']) && $reportRow['min_date'] !== '0000-00-00') {
                $minDateField = new DateTimeField($reportRow['min_date']);
                $actualData['min_date'] = $minDateField->getDisplayDate();
            }
            if (!empty($reportRow['max_date']) && $reportRow['max_date'] !== '0000-00-00') {
                $maxDateField = new DateTimeField($reportRow['max_date']);
                $actualData['max_date'] = $maxDateField->getDisplayDate();
            }
            
            // Formatear números para visualización
            $actualData['total_duration_display'] = $numberingHelper->setNumberFormat($actualData['total_duration'], 'duration_time');
            $actualData['total_cost_display'] = $numberingHelper->setNumberFormat($actualData['total_cost'], 'actual_cost');
        }
    }
    DatabaseUtils::closeResult($reportsResult);
    
    // Calcular indicadores (proporciones)
    $indicators = array(
        'duration_ratio' => 0,
        'cost_ratio' => 0,
        'duration_ratio_display' => '',
        'cost_ratio_display' => '',
        'duration_over_budget' => false,
        'cost_over_budget' => false
    );
    
    if ($actualData['has_reports']) {
        // Calcular proporción de duración
        $estimatedDuration = floatval($taskData['estimated_time']);
        if ($estimatedDuration > 0) {
            $indicators['duration_ratio'] = ($actualData['total_duration'] / $estimatedDuration) * 100;
            // Formatear con NumberHelper para respetar preferencias del usuario
            $formattedRatio = $numberingHelper->setNumberFormat($indicators['duration_ratio'], null);
            $indicators['duration_ratio_display'] = $formattedRatio . '%';
            $indicators['duration_over_budget'] = $indicators['duration_ratio'] > 100;
        }
        
        // Calcular proporción de costo
        $estimatedCost = floatval($taskData['estimated_cost']);
        if ($estimatedCost > 0) {
            $indicators['cost_ratio'] = ($actualData['total_cost'] / $estimatedCost) * 100;
            // Formatear con NumberHelper para respetar preferencias del usuario
            $formattedCostRatio = $numberingHelper->setNumberFormat($indicators['cost_ratio'], null);
            $indicators['cost_ratio_display'] = $formattedCostRatio . '%';
            $indicators['cost_over_budget'] = $indicators['cost_ratio'] > 100;
        }
    }
    
    $taskData['actual_data'] = $actualData;
    $taskData['indicators'] = $indicators;
    
    // Obtener reportes de avance de la tarea
    $activityReports = array();
    $reportManager = ActivityReportManager::getInstance($adb);
    // Verificar si la tarea es de tipo Job para incluir reportes JOB en la vista
    $activityTypeResult = $adb->pquery("SELECT activitytype FROM vtiger_activity WHERE activityid = ?", array($activityId));
    $isJobActivity = ($adb->num_rows($activityTypeResult) > 0 && $adb->query_result($activityTypeResult, 0, 'activitytype') === 'Task')
        ? false
        : false; // fallback seguro
    if ($adb->num_rows($activityTypeResult) > 0) {
        $actSubject = $adb->pquery("SELECT subject FROM vtiger_activity WHERE activityid = ?", array($activityId));
        // Detectar Job: verificar si tiene reportes con report_on='JOB'
        $jobCheckResult = $adb->pquery(
            "SELECT COUNT(*) as cnt FROM vtiger_activity_report WHERE activityid = ? AND report_on = 'JOB' AND (deleted = 0 OR deleted IS NULL)",
            array($activityId)
        );
        $isJobActivity = ($adb->num_rows($jobCheckResult) > 0 && $adb->query_result($jobCheckResult, 0, 'cnt') > 0);
    }
    $reports = $reportManager->fetchActivityReportByActivityId($activityId, null, $isJobActivity);
    
    if (!empty($reports)) {
        foreach ($reports as $report) {
            // Obtener información del usuario que creó el reporte
            $userName = '-';
            $reportUserId = null;
            if ($report->getUserId() !== null) {
                $userSql = "SELECT CONCAT(first_name, ' ', last_name) as user_name 
                            FROM vtiger_users 
                            WHERE id = ?";
                $userResult = $adb->pquery($userSql, array($report->getUserId()));
                $userName = ($adb->num_rows($userResult) > 0) ? $adb->query_result($userResult, 0, 'user_name') : '-';
                $reportUserId = $report->getUserId();
            }
            
            // Verificar permisos de edición: el usuario puede editar si es el creador del reporte o es administrador
            $canEdit = false;
            $canDelete = false;
            if ($current_user) {
                // El usuario puede editar si es el creador del reporte
                if ($reportUserId !== null && $current_user->id == $reportUserId) {
                    $canEdit = true;
                    $canDelete = true; // El dueño puede eliminar
                }
                // O si es administrador
                if (isset($current_user->is_admin) && $current_user->is_admin == 'on') {
                    $canEdit = true;
                    $canDelete = true; // El admin puede eliminar
                }
            }
            
            // Obtener lista de evidencias (adjuntos) del reporte
            require_once('include/utils/AttachmentsUtils.class.php');
            $attachments = AttachmentsUtils::fetchActivityReportAttachments($adb, $report->getId());
            $evidenceList = array();
            
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $evidenceList[] = array(
                        'id' => $attachment['attachmentid'],
                        'name' => $attachment['name'],
                        'size' => $attachment['size'],
                        'uri' => $attachment['uri']
                    );
                }
            }
            
            // Obtener la fecha datetime del reporte y modificated_date
            $rawDateResult = $adb->pquery(
                "SELECT reportdate, modificated_date, activity_report_date FROM vtiger_activity_report WHERE activityreportid = ?",
                array($report->getId())
            );
            $rawReportDate = null;
            $rawModificatedDate = null;
            if ($adb->num_rows($rawDateResult) > 0) {
                $rawReportDate         = $adb->query_result($rawDateResult, 0, 'reportdate');
                $rawModificatedDate    = $adb->query_result($rawDateResult, 0, 'modificated_date');
                $rawActivityReportDate = $adb->query_result($rawDateResult, 0, 'activity_report_date');
            }
            DatabaseUtils::closeResult($rawDateResult);
            
            // Obtener formato de fecha del usuario (dd/mm/yyyy por defecto)
            $userDateFmt = isset($current_user->column_fields['date_format']) ? $current_user->column_fields['date_format'] : 'dd/mm/yyyy';
            $phpDateFmt  = str_replace(array('yyyy','mm','dd'), array('Y','m','d'), $userDateFmt);

            // Formatear reportdate (DATETIME) según formato del usuario
            $formattedReportDateTime = null;
            if (!empty($rawReportDate)) {
                $ts = strtotime($rawReportDate);
                if ($ts !== false) {
                    $formattedReportDateTime = date($phpDateFmt, $ts) . ' ' . date('H:i:s', $ts);
                }
            }
            // Formatear modificated_date (DATETIME) según formato del usuario
            $formattedModificatedDate = null;
            if (!empty($rawModificatedDate)) {
                $ts = strtotime($rawModificatedDate);
                if ($ts !== false) {
                    $formattedModificatedDate = date($phpDateFmt, $ts) . ' ' . date('H:i:s', $ts);
                }
            }
            // Formatear activity_report_date (DATE) según formato del usuario
            $formattedActivityReportDate = null;
            if (!empty($rawActivityReportDate) && $rawActivityReportDate !== '0000-00-00') {
                $ts = strtotime($rawActivityReportDate);
                if ($ts !== false) {
                    $formattedActivityReportDate = date($phpDateFmt, $ts);
                }
            }

            // Función helper para formatear números con separadores de miles y decimales
            $formatNumberWithSeparators = function($value, $decimals, $format) {
                if ($value === null || $value === '') return '0' . ($decimals > 0 ? ($format === 'EUROPEAN_FORMAT' ? ',' : '.') . str_repeat('0', $decimals) : '');
                
                $thousandsSep = ($format === 'EUROPEAN_FORMAT') ? '.' : ',';
                $decimalSep = ($format === 'EUROPEAN_FORMAT') ? ',' : '.';
                
                return number_format(floatval($value), $decimals, $decimalSep, $thousandsSep);
            };
            
            $numberFormat = isset($current_user->column_fields['numbering_format']) ? $current_user->column_fields['numbering_format'] : 'AMERICAN_FORMAT';

            $activityReports[] = array(
                'id' => $report->getId(),
                'activityreportid' => $report->getId(), // Agregar activityreportid para el enlace del lápiz
                'activityid' => $report->getActivityId(), // Agregar activityid
                'title' => $report->getTitle(), // Agregar title
                'progress' => $report->getProgress(), // Valor crudo para cálculos
                'progress_formatted' => $formatNumberWithSeparators($report->getProgress(), 0, $numberFormat), // Formateado para mostrar
                'reportdate' => $rawReportDate, // Fecha cruda ISO para date_format en Smarty
                'date' => $rawReportDate, // Fecha cruda ISO para date_format en Smarty
                'formatted_datetime' => $formattedReportDateTime, // Fecha y hora formateadas según idioma
                'modificated_date' => $formattedModificatedDate, // Fecha de última modificación
                'activity_report_date' => $formattedActivityReportDate,
                'user_name' => $userName,
                'report' => $report->getReport(),
                'duration_time' => $report->getTimeDuration(), // Agregar duration_time
                'duration' => $formatNumberWithSeparators($report->getTimeDuration(), 2, $numberFormat), // Formateado con separadores
                'actual_cost' => $report->getActualCost() !== null ? $report->getActualCost() : 0, // Agregar actual_cost
                'cost' => $formatNumberWithSeparators($report->getActualCost(), 2, $numberFormat), // Formateado con separadores
                'userid' => $reportUserId, // Agregar userid
                'evidences' => $evidenceList,
                'can_edit' => $canEdit,
                'can_delete' => $canDelete
            );
        }
    }
    
    // Obtener feedbacks de la tarea
    $activityFeedbacks = array();
    $feedbackManager = ActivityFeedbackManager::getInstance($adb);
    $feedbacks = $feedbackManager->fetchActivityFeedbackByActivity($activityId);
        
    if (!empty($feedbacks)) {
        foreach ($feedbacks as $feedback) {           
            // Verificar permisos de edición para feedbacks
            $canEditFeedback = false;
            if ($current_user) {
                // El usuario puede editar si es el creador del feedback
                if ($feedback->getUserId() !== null && $current_user->id == $feedback->getUserId()) {
                    $canEditFeedback = true;
                }
                // O si es administrador
                if (isset($current_user->is_admin) && $current_user->is_admin == 'on') {
                    $canEditFeedback = true;
                }
            }
            
            // Obtener el título del reporte asociado al feedback (si existe)
            $reportTitle = '';
            $reportSql = "SELECT ar.title 
                          FROM vtiger_activity_report ar
                          INNER JOIN vtiger_activity_report2feedback ar2f ON ar2f.activityreportid = ar.activityreportid
                          WHERE ar2f.activityfeedbackid = ?";
            $reportResult = $adb->pquery($reportSql, array($feedback->getId()));
            if ($adb->num_rows($reportResult) > 0) {
                $reportTitle = $adb->query_result($reportResult, 0, 'title');
            }
            
            $activityFeedbacks[] = array(
                'id' => $feedback->getId(),
                'activity_id' => $feedback->getActivityId(),
                'feedback' => $feedback->getFeedback(),
                'feedback_date' => $feedback->getFeedbackDate(),
                'title' => $feedback->getTitle(),
                'user_name' => $feedback->getUserName(),
                'user_avatar' => $feedback->getUserAvatar(),
                'user_id' => $feedback->getUserId(),
                'can_edit' => $canEditFeedback,
                'report_title' => $reportTitle
            );
        }
    }
    
    
    // Preparar datos para Smarty
    $userLanguage = (!empty($current_user) && is_object($current_user) && property_exists($current_user, 'language')) 
        ? $current_user->language 
        : 'es_es';
    $modStrings = return_module_language($userLanguage, 'Calendar');
    
    // Traducir valores de importancia, prioridad, tipo de actividad y estado
    if (!empty($taskData['importance'])) {
        $taskData['importance_translated'] = getTranslatedString($taskData['importance'], 'Calendar');
    }
    if (!empty($taskData['priority'])) {
        $taskData['priority_translated'] = getTranslatedString($taskData['priority'], 'Calendar');
    }
    if (!empty($taskData['activitytype'])) {
        $taskData['activitytype_translated'] = getTranslatedString($taskData['activitytype'], 'Calendar');
    }
    if (!empty($taskData['eventstatus'])) {
        $taskData['eventstatus_translated'] = getTranslatedString($taskData['eventstatus'], 'Calendar');
    }
    
    // Obtener formato de fecha del usuario
    $userDateFormat = 'dd/mm/yyyy'; // formato por defecto
    if (!empty($current_user) && is_object($current_user) && property_exists($current_user, 'column_fields')) {
        $dateFormatField = isset($current_user->column_fields['date_format']) ? $current_user->column_fields['date_format'] : '';
        if (!empty($dateFormatField)) {
            // Convertir formato de BD a formato de Smarty
            switch ($dateFormatField) {
                case 'yyyy-mm-dd':
                    $userDateFormat = '%Y-%m-%d';
                    break;
                case 'dd-mm-yyyy':
                    $userDateFormat = '%d-%m-%Y';
                    break;
                case 'mm-dd-yyyy':
                    $userDateFormat = '%m-%d-%Y';
                    break;
                case 'dd/mm/yyyy':
                    $userDateFormat = '%d/%m/%Y';
                    break;
                case 'mm/dd/yyyy':
                    $userDateFormat = '%m/%d/%Y';
                    break;
                case 'yyyy.mm.dd':
                    $userDateFormat = '%Y.%m.%d';
                    break;
                case 'dd.mm.yyyy':
                    $userDateFormat = '%d.%m.%Y';
                    break;
                default:
                    $userDateFormat = '%d/%m/%Y';
                    break;
            }
        }
    }
    
    $smarty = new vtigerCRM_Smarty();
    $smarty->assign('TASK_DATA', $taskData);
    $smarty->assign('ACTIVITY_REPORTS', $activityReports);
    $smarty->assign('ACTIVITY_FEEDBACKS', $activityFeedbacks);
    $smarty->assign('MOD', $modStrings);
    $smarty->assign('APP', $app_strings);
    $smarty->assign('IS_ADMIN', (isset($current_user->is_admin) && $current_user->is_admin == 'on'));
    $smarty->assign('NUMBERING_FORMAT', isset($current_user->column_fields['numbering_format']) ? $current_user->column_fields['numbering_format'] : 'AMERICAN_FORMAT');
    $smarty->assign('USER_DATE_FORMAT', $userDateFormat);
    $smarty->assign('IS_JOB_ACTIVITY', isset($isJobActivity) ? $isJobActivity : false);
        
    // Renderizar template
    $html = $smarty->fetch('modules/Calendar/TaskViewModal.tpl');
    
    // Enviar respuesta JSON
    echo json_encode(array(
        'success' => true,
        'html' => $html,
        'taskExists' => true
    ));
    
    exit;
}

// Handler para eliminar reporte de avance (soft delete)
if ($function === 'DELETE-ACTIVITY-REPORT') {
    ob_start();
    
    global $adb, $current_user;
    
    require_once('include/platzilla/Data/ActivityReportManager.php');
    require_once('include/platzilla/Utils/DatabaseUtils.php');
    
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    
    $reportId = isset($_REQUEST['reportid']) ? intval($_REQUEST['reportid']) : 0;
    $activityId = isset($_REQUEST['activityid']) ? intval($_REQUEST['activityid']) : 0;
    
    if (empty($reportId)) {
        echo json_encode(array('success' => false, 'error' => 'MISSING_REPORT_ID'));
        exit;
    }
    
    $reportManager = ActivityReportManager::getInstance($adb);
    
    // Verificar permisos: solo dueño o administrador pueden eliminar
    $canDelete = false;
    $report = $reportManager->fetchActivityReportById($reportId, $activityId);
    
    if ($report) {
        // Es el dueño del reporte
        if ($report->getUserId() == $current_user->id) {
            $canDelete = true;
        }
        // O es administrador
        if (isset($current_user->is_admin) && $current_user->is_admin == 'on') {
            $canDelete = true;
        }
    }
    
    if (!$canDelete) {
        echo json_encode(array(
            'success' => false, 
            'error' => getTranslatedString('LBL_REPORT_DELETE_PERMISSION_DENIED', 'Calendar')
        ));
        exit;
    }
    
    // Ejecutar soft delete
    try {
        $reportManager->softDeleteReport($reportId);
        
        echo json_encode(array(
            'success' => true,
            'message' => getTranslatedString('LBL_REPORT_DELETED_SUCCESS', 'Calendar')
        ));
    } catch (Exception $e) {
        echo json_encode(array(
            'success' => false,
            'error' => 'Error al eliminar: ' . $e->getMessage()
        ));
    }
    exit;
}

// Handler para subir evidencia a un reporte de avance Job (sin editar el reporte)
if ($function === 'UPLOAD-REPORT-EVIDENCE') {
    ob_start();
    
    global $adb, $current_user;
    
    require_once('include/utils/AttachmentsUtils.class.php');
    require_once('include/platzilla/Data/ActivityReportManager.php');
    require_once('include/platzilla/Utils/DatabaseUtils.php');
    require_once('include/utils/utils.php');
    
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    
    $reportId   = isset($_REQUEST['reportid'])   ? intval($_REQUEST['reportid'])   : 0;
    $activityId = isset($_REQUEST['activityid']) ? intval($_REQUEST['activityid']) : 0;
    
    if (empty($reportId) || empty($activityId)) {
        echo json_encode(array('success' => false, 'error' => 'MISSING_PARAMS'));
        exit;
    }
    
    if (empty($_FILES['evidence']) || $_FILES['evidence']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(array('success' => false, 'error' => 'NO_FILE_OR_UPLOAD_ERROR'));
        exit;
    }
    
    $badExtensions = array('php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'sh', 'js');
    
    $fileData = array(
        'name'     => $_FILES['evidence']['name'],
        'type'     => $_FILES['evidence']['type'],
        'tmp_name' => $_FILES['evidence']['tmp_name'],
    );
    
    $fileExt = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
    if (in_array($fileExt, $badExtensions)) {
        echo json_encode(array('success' => false, 'error' => 'FORBIDDEN_EXTENSION'));
        exit;
    }
    
    try {
        $now          = date('Y-m-d H:i:s');
        $ownerId      = $current_user->id;
        $fileName     = ltrim(basename(' ' . sanitizeUploadFileName($fileData['name'], $badExtensions)));
        $folderPath   = decideFilePath();
        $attachmentId = $adb->getUniqueID('vtiger_crmentity');
        $destPath     = "{$folderPath}{$attachmentId}_{$fileName}";
        
        if (!move_uploaded_file($fileData['tmp_name'], $destPath)) {
            echo json_encode(array('success' => false, 'error' => 'MOVE_FILE_FAILED'));
            exit;
        }
        
        $adb->pquery(
            'INSERT INTO vtiger_crmentity (crmid, smcreatorid, smownerid, setype, createdtime, modifiedtime) VALUES (?, ?, ?, ?, ?, ?)',
            array($attachmentId, $ownerId, $ownerId, 'Calendar Attachment', $now, $now)
        );
        $adb->pquery(
            'INSERT INTO vtiger_attachments (attachmentsid, name, type, path, fieldid) VALUES (?, ?, ?, ?, ?)',
            array($attachmentId, $fileName, $fileData['type'], $folderPath, null)
        );
        $adb->pquery(
            'INSERT INTO vtiger_seattachmentsrel (crmid, attachmentsid) VALUES (?, ?)',
            array($activityId, $attachmentId)
        );
        $adb->pquery(
            'INSERT INTO vtiger_activityreport2attachments (activityreportid, attachmentsid) VALUES (?, ?)',
            array($reportId, $attachmentId)
        );
        
        require_once('include/utils/PlatzillaUtils.class.php');
        $rootUri = PlatzillaUtils::getPlatzillaRootUri();
        
        echo json_encode(array(
            'success'      => true,
            'attachmentid' => $attachmentId,
            'name'         => $fileName,
            'uri'          => "{$rootUri}/{$destPath}",
        ));
    } catch (Exception $e) {
        echo json_encode(array('success' => false, 'error' => $e->getMessage()));
    }
    exit;
}

// Handler para eliminar evidencia de un reporte de avance Job
if ($function === 'DELETE-REPORT-EVIDENCE') {
    ob_start();
    
    global $adb, $current_user;
    
    require_once('include/utils/AttachmentsUtils.class.php');
    require_once('include/platzilla/Utils/DatabaseUtils.php');
    
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    
    $reportId     = isset($_REQUEST['reportid'])     ? intval($_REQUEST['reportid'])     : 0;
    $attachmentId = isset($_REQUEST['attachmentid']) ? intval($_REQUEST['attachmentid']) : 0;
    
    if (empty($reportId) || empty($attachmentId)) {
        echo json_encode(array('success' => false, 'error' => 'MISSING_PARAMS'));
        exit;
    }
    
    try {
        AttachmentsUtils::deleteActivityReportAttachment($adb, $reportId, $attachmentId);
        echo json_encode(array('success' => true));
    } catch (Exception $e) {
        echo json_encode(array('success' => false, 'error' => $e->getMessage()));
    }
    exit;
}

// Para otras peticiones AJAX, usar el handler común
require_once('include/Ajax/CommonAjax.php');
?>
