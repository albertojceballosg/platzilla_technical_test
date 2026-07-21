<?php
    require_once('include/platzilla/Data/ApplicationsManager.php');
	require_once('include/platzilla/Managers/BackgroundTaskManager.php');
    require_once('include/platzilla/Data/BoxScoreManager.php');
    require_once('include/platzilla/Data/GraphicManager.php');
    require_once('include/platzilla/Managers/PicklistManager.php');
    require_once('include/platzilla/Managers/PlatformSubscriptionManager.php');
    require_once('include/platzilla/Managers/UserManager.php');
    require_once('include/platzilla/Utils/JSGraphicUtils.php');
    require_once('include/utils/AdbManager.class.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once('include/utils/CommonUtils.php');
	require_once('include/utils/DataViewUtils.php');
	require_once('include/utils/GanttTaskUtils.class.php');
	require_once('include/utils/KanbanTaskUtils.class.php');
	require_once('include/utils/ProcessCasesUtils.class.php');
	require_once('include/utils/PlatzillaUtils.class.php');
	require_once('include/utils/utils.php');
	require_once('include/fields/DateTimeField.php');
	require_once('modules/Courses/lib/CoursesHelper.php');
	require_once('modules/grid_view/lib/GridViewHelper.class.php');
    require_once('modules/Home/lib/HomeUtils.class.php');
    require_once('modules/News/lib/NewsUtils.php');
    require_once('modules/notifications/lib/NotificationPeriodUtils.class.php');
    require_once('modules/notifications/lib/NotificationUtils.class.php');
    require_once('modules/notification_center/lib/NotificationHelper.class.php');
    require_once('modules/operating_modes/lib/OperatingModesHelper.class.php');
    require_once('modules/panelusuarios/lib/UsersHelper.class.php');
    require_once('modules/preloaded_tasks/lib/PrecreatedTaskUtils.class.php');
    require_once('modules/proyectos/handlers/taskToProject.class.php');
    require_once('modules/Settings/lib/HowToHelper.class.php');
    require_once('modules/store/lib/StoreUtils.class.php');
    require_once('modules/webmail/lib/WebmailUtils.class.php');
    require_once('modules/work_views/lib/WorksViewHelper.class.php');
    require_once('Smarty_setup.php');
    
    global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;
    
    setBugSnag($site_URL);
    $current_module = PlatzillaUtils::purify($_REQUEST, 'module');
    $function       = PlatzillaUtils::purify($_REQUEST, 'function');
    $moduleName     = PlatzillaUtils::purify($_REQUEST, 'flmodule');
    $isInstance     = !empty ($_SESSION ['platInstancia']);
    $masterAdb      = AdbManager::getInstance()->getMasterAdb();
    
    if ($function == 'CHANGE-DATES-JOB') {
        ob_start(); // Capturar cualquier salida de error
        try {
            $jobsData = PlatzillaUtils::purify($_POST, 'jobData', null);
            if (empty ($jobsData)) {
                throw new Exception ('No se recibieron datos del trabajo');
            }
            
            require_once('data/CrmEntityUtils.php');
            require_once('include/platzilla/Data/EntityHistoryManager.php');
            require_once('include/platzilla/Managers/ModuleManager.php');
            require_once('include/platzilla/Managers/FieldManager.php');
            
            $jobToProject = taskToProject::getInstance($adb);
            $jobModule = $jobToProject::JOB_RELATED_MODULE;
            $entity = CRMEntity::getInstance($jobModule);
            foreach ($jobsData as $job) {
                if (
                    empty ($job ['id']) ||
                    empty ($job ['start']) ||
                    empty ($job ['end'])
                ) {
                    continue;
                }
                $dummy = explode('@', $job['id']);
                $crmId = intval($dummy [0]);
                $tfId = intval($dummy [1]);
                $entity->id = $crmId;
                $entity->mode = 'edit';
                $entity->retrieve_entity_info($crmId, $jobModule);
                
                // Guardar datos antiguos para auditoría
                $jobName = $entity->column_fields['subject'];
                $oldStartDate = $entity->column_fields['fecha_prevista'];
                $oldEndDate = $entity->column_fields['fecha_estim_fin'];
                
                $entity->column_fields ['fecha_prevista'] = $job ['start'];
                $entity->save($jobModule);
                $jobToProject->updateDueDateJob($tfId, $crmId, $job ['end']);
                
                // Registrar auditoría en el PROYECTO relacionado
                try {
                    // Obtener el proyecto relacionado con este trabajo
                    $relResult = $adb->pquery('SELECT crmid FROM vtiger_project_works WHERE crmid_job = ?', array($crmId));
                    
                    if ($adb->num_rows($relResult) > 0) {
                        $projectId = $adb->query_result($relResult, 0, 'crmid');
                        
                        // Construir mensaje descriptivo del cambio
                        $changes = array();
                        if ($oldStartDate != $job['start']) {
                            $changes[] = "Inicio: {$oldStartDate} → {$job['start']}";
                        }
                        if ($oldEndDate != $job['end']) {
                            $changes[] = "Fin: {$oldEndDate} → {$job['end']}";
                        }
                        
                        // Si hay cambios, registrar en histórico del proyecto
                        if (!empty($changes)) {
                            $changeDescription = "Trabajo '{$jobName}' modificado desde Gantt: " . implode(', ', $changes);
                            
                            // Obtener tabid del módulo proyectos
                            $module = ModuleManager::getInstance($adb)->fetchModule('proyectos', true);
                            if (!empty($module)) {
                                $moduleId = $module->getId();
                                
                                // Buscar el campo task_project para asociar el histórico de trabajos
                                $fieldResult = $adb->pquery(
                                    "SELECT fieldid, uitype FROM vtiger_field WHERE tabid = ? AND fieldname = 'task_project' AND presence IN (0,2)",
                                    array($moduleId)
                                );
                                $fieldId = 0;
                                if ($adb->num_rows($fieldResult) > 0) {
                                    $fieldId = $adb->query_result($fieldResult, 0, 'fieldid');
                                }
                                
                                // Insertar directamente en vtiger_crmentityutils
                                $adb->pquery(
                                    'INSERT INTO vtiger_crmentityutils (module, field, oldvalue, newvalue, modifiedby, modifiedon, registryid, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                                    array(
                                        $moduleId,
                                        $fieldId,
                                        '(Cambio de trabajo desde Gantt)',
                                        $changeDescription,
                                        $current_user->id,
                                        1,
                                        $projectId,
                                        date('Y-m-d H:i:s')
                                    )
                                );
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("[GANTT-AUDIT-PROJECT] Error al registrar auditoría en proyecto: " . $e->getMessage());
                }
            }
            ob_end_clean(); // Limpiar buffer
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK'));
        } catch (Exception $e) {
            ob_end_clean(); // Limpiar buffer
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    } 
    else if ($function == 'CHANGE-DATES-TASK') {
        try {
            $tasksData = PlatzillaUtils::purify($_POST, 'taskData', null);
            
            if (empty($tasksData)) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            
            require_once('data/CrmEntityUtils.php');
            require_once('include/platzilla/Data/EntityHistoryManager.php');
            require_once('include/platzilla/Managers/ModuleManager.php');
            require_once('include/platzilla/Managers/FieldManager.php');
            
            $entity = CRMEntity::getInstance('Calendar');
            foreach ($tasksData as $task) {
                if (
                    empty ($task ['id']) ||
                    empty ($task ['start']) ||
                    empty ($task ['end']) ||
                    ! is_numeric($task ['id'])
                ) {
                    continue;
                }
                
                // Obtener datos antiguos para auditoría
                $entity->id = $task ['id'];
                $entity->mode = 'edit';
                $entity->retrieve_entity_info($task ['id'], 'Calendar');
                $oldData = $entity->column_fields;
                $taskSubject = $oldData['subject'];
                $oldDateStart = $oldData['date_start'];
                $oldDueDate = $oldData['due_date'];
                
                // Actualizar campos con nuevos valores
                $entity->column_fields ['date_start'] = $task ['start'];
                $entity->column_fields ['due_date'] = $task ['end'];
                
                // Guardar cambios (esto registra auditoría estándar)
                $entity->save('Calendar');
                
                // Registrar auditoría en el TRABAJO relacionado
                try {
                    // Obtener el trabajo relacionado con esta tarea
                    $relResult = $adb->pquery('SELECT crmid FROM vtiger_seactivityrel WHERE activityid = ?', array($task['id']));
                    
                    if ($adb->num_rows($relResult) > 0) {
                        $workId = $adb->query_result($relResult, 0, 'crmid');
                        
                        // Obtener el módulo del trabajo
                        $seTypeResult = $adb->pquery('SELECT setype FROM vtiger_crmentity WHERE crmid = ?', array($workId));
                        if ($adb->num_rows($seTypeResult) > 0) {
                            $workModule = $adb->query_result($seTypeResult, 0, 'setype');
                            
                            // Construir mensaje descriptivo del cambio
                            $changes = array();
                            if ($oldDateStart != $task['start']) {
                                $changes[] = "Inicio: {$oldDateStart} → {$task['start']}";
                            }
                            if ($oldDueDate != $task['end']) {
                                $changes[] = "Fin: {$oldDueDate} → {$task['end']}";
                            }
                            
                            // Si hay cambios, registrar en histórico
                            if (!empty($changes)) {
                                $changeDescription = "Tarea '{$taskSubject}' modificada desde Gantt: " . implode(', ', $changes);
                                
                                // Obtener tabid del módulo
                                $module = ModuleManager::getInstance($adb)->fetchModule($workModule, true);
                                if (!empty($module)) {
                                    $moduleId = $module->getId();
                                    
                                    // Buscar el campo task_work para asociar el histórico de tareas
                                    $fieldResult = $adb->pquery(
                                        "SELECT fieldid, uitype FROM vtiger_field WHERE tabid = ? AND fieldname = 'task_work' AND presence IN (0,2)",
                                        array($moduleId)
                                    );
                                    $fieldId = 0;
                                    if ($adb->num_rows($fieldResult) > 0) {
                                        $fieldId = $adb->query_result($fieldResult, 0, 'fieldid');
                                    }
                                    
                                    // Insertar directamente en vtiger_crmentityutils
                                    $adb->pquery(
                                        'INSERT INTO vtiger_crmentityutils (module, field, oldvalue, newvalue, modifiedby, modifiedon, registryid, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                                        array(
                                            $moduleId,
                                            $fieldId,
                                            '(Cambio de tarea desde Gantt)',
                                            $changeDescription,
                                            $current_user->id,
                                            1,
                                            $workId,
                                            date('Y-m-d H:i:s')
                                        )
                                    );
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("[GANTT-AUDIT-WORK] Error al registrar auditoría en trabajo: " . $e->getMessage());
                }
            }
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK'));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    } 
    else if ($function == 'CHANGE-ENTITY-OWNER') {
        try {
            $record = PlatzillaUtils::purify($_GET, 'record');
            $current_module = PlatzillaUtils::purify($_GET, 'module');
            if (empty ($record)) {
                throw new Exception ('Registro no encontrado');
            } else if (empty($moduleName)) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            
            $modalTitle = 'Asignar expediente';
            $focus = CRMEntity::getInstance($moduleName);
            $focus->id = $record;
            $focus->mode = 'edit';
            $focus->retrieve_entity_info($record, $moduleName);
            $listFieldsName = array_values($focus->list_fields_name);
            if (count($listFieldsName) >= 2) {
                $modalTitle = $focus->column_fields ['subject'];
            }
            $userSelected = $focus->column_fields['assigned_user_id'];
            $userList = str_replace('value=' . $userSelected . '>', 'value=' . $userSelected . ' selected="selected">', getUserslist(false));
            
            $smarty = new vtigerCRM_Smarty ();
            $smarty->assign('APP', $app_strings);
            $smarty->assign('ASSINGN_TYPE', 'U');
            $smarty->assign('CHANGE_OWNER', $userList);
            $smarty->assign('FLMODULE', $moduleName);
            $smarty->assign('MASS_EDIT', '0');
            $smarty->assign('MOD', $mod_strings);
            $smarty->assign('MODULE', $current_module);
            $smarty->assign('MODAL_TITLE', $modalTitle);
            $smarty->assign('MODE', 'edit');
            $smarty->assign('RECORD', $record);
            $smarty->assign('RETURN_ACTION', 'KANBA-SAVE');
        } catch (Exception $e) {
            $smarty->assign('MESSAGE', $e->getMessage());
            $smarty->assign('TYPE', 'ERROR');
        }
        $smarty->display('ChangeActivityOwnerModal.tpl');
    } 
    else if ($function == 'CHANGE-PROGRESS-JOB') {
        try {
            $job = PlatzillaUtils::purify($_POST, 'jobData', null);
            if (empty ($job)) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            
            if (
                empty ($job ['id']) ||
                empty ($job ['progress'])
            ) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            
            require_once('data/CrmEntityUtils.php');
            require_once('include/platzilla/Data/EntityHistoryManager.php');
            require_once('include/platzilla/Managers/ModuleManager.php');
            require_once('include/platzilla/Managers/FieldManager.php');
            
            $jobToProject = taskToProject::getInstance($adb);
            $dummy = explode('@', $job['id']);
            $crmId = intval($dummy [0]);
            $jobModule = $jobToProject::JOB_RELATED_MODULE;
            $entity = CRMEntity::getInstance($jobModule);
            $entity->id = $crmId;
            $entity->mode = 'edit';
            $entity->retrieve_entity_info($crmId, $jobModule);
            
            // Guardar datos antiguos para auditoría
            $jobName = $entity->column_fields['subject'];
            $oldProgress = $entity->column_fields['overall_progress_perc'];
            
            $entity->column_fields ['overall_progress_perc'] = $job ['progress'];
            $entity->save($jobModule);
            
            // Registrar auditoría en el PROYECTO relacionado
            try {
                // Obtener el proyecto relacionado con este trabajo
                $relResult = $adb->pquery('SELECT crmid FROM vtiger_project_works WHERE crmid_job = ?', array($crmId));
                
                if ($adb->num_rows($relResult) > 0) {
                    $projectId = $adb->query_result($relResult, 0, 'crmid');
                    
                    // Si hay cambio en el progreso, registrar en histórico del proyecto
                    if ($oldProgress != $job['progress']) {
                        $changeDescription = "Trabajo '{$jobName}' modificado desde Gantt: Progreso: {$oldProgress}% → {$job['progress']}%";
                        
                        // Obtener tabid del módulo proyectos
                        $module = ModuleManager::getInstance($adb)->fetchModule('proyectos', true);
                        if (!empty($module)) {
                            $moduleId = $module->getId();
                            
                            // Buscar el campo task_project para asociar el histórico de trabajos
                            $fieldResult = $adb->pquery(
                                "SELECT fieldid, uitype FROM vtiger_field WHERE tabid = ? AND fieldname = 'task_project' AND presence IN (0,2)",
                                array($moduleId)
                            );
                            $fieldId = 0;
                            if ($adb->num_rows($fieldResult) > 0) {
                                $fieldId = $adb->query_result($fieldResult, 0, 'fieldid');
                            }
                            
                            // Insertar directamente en vtiger_crmentityutils
                            $adb->pquery(
                                'INSERT INTO vtiger_crmentityutils (module, field, oldvalue, newvalue, modifiedby, modifiedon, registryid, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                                array(
                                    $moduleId,
                                    $fieldId,
                                    '(Cambio de trabajo desde Gantt)',
                                    $changeDescription,
                                    $current_user->id,
                                    1,
                                    $projectId,
                                    date('Y-m-d H:i:s')
                                )
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("[GANTT-AUDIT-PROJECT] Error al registrar auditoría en proyecto: " . $e->getMessage());
            }
            
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK'));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    } 
    else if ($function == 'CHANGE-PROGRESS-TASK') {
        try {
            $task = PlatzillaUtils::purify($_POST, 'taskData', null);
            if (empty ($task)) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            
            require_once('data/CrmEntityUtils.php');
            require_once('include/platzilla/Data/EntityHistoryManager.php');
            require_once('include/platzilla/Managers/ModuleManager.php');
            require_once('include/platzilla/Managers/FieldManager.php');
            
            $entity = CRMEntity::getInstance('Calendar');
            if (
                empty ($task ['id']) ||
                empty ($task ['progress']) ||
                ! is_numeric($task ['id'])
            ) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            
            // Obtener datos antiguos para auditoría
            $entity->id = $task ['id'];
            $entity->mode = 'edit';
            $entity->retrieve_entity_info($task ['id'], 'Calendar');
            $oldData = $entity->column_fields;
            $taskSubject = $oldData['subject'];
            $oldProgress = $oldData['progress'];
            
            // Actualizar progreso
            $entity->column_fields ['progress'] = $task ['progress'];
            
            // Guardar cambios (esto registra auditoría estándar)
            $entity->save('Calendar');
            
            // Registrar auditoría en el TRABAJO relacionado
            try {
                // Obtener el trabajo relacionado con esta tarea
                $relResult = $adb->pquery('SELECT crmid FROM vtiger_seactivityrel WHERE activityid = ?', array($task['id']));
                if ($adb->num_rows($relResult) > 0) {
                    $workId = $adb->query_result($relResult, 0, 'crmid');
                    
                    // Obtener el módulo del trabajo
                    $seTypeResult = $adb->pquery('SELECT setype FROM vtiger_crmentity WHERE crmid = ?', array($workId));
                    if ($adb->num_rows($seTypeResult) > 0) {
                        $workModule = $adb->query_result($seTypeResult, 0, 'setype');
                        
                        // Construir mensaje descriptivo del cambio
                        $changeDescription = "Tarea '{$taskSubject}' - Progreso modificado desde Gantt: {$oldProgress}% → {$task['progress']}%";
                        
                        // Obtener tabid del módulo
                        $module = ModuleManager::getInstance($adb)->fetchModule($workModule, true);
                        if (!empty($module)) {
                            $moduleId = $module->getId();
                            
                            // Buscar el campo task_work para asociar el histórico de tareas
                            $fieldResult = $adb->pquery(
                                "SELECT fieldid, uitype FROM vtiger_field WHERE tabid = ? AND fieldname = 'task_work' AND presence IN (0,2)",
                                array($moduleId)
                            );
                            $fieldId = 0;
                            if ($adb->num_rows($fieldResult) > 0) {
                                $fieldId = $adb->query_result($fieldResult, 0, 'fieldid');
                            }
                            
                            // Insertar directamente en vtiger_crmentityutils
                            $adb->pquery(
                                'INSERT INTO vtiger_crmentityutils (module, field, oldvalue, newvalue, modifiedby, modifiedon, registryid, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                                array(
                                    $moduleId,
                                    $fieldId,
                                    '(Cambio de progreso desde Gantt)',
                                    $changeDescription,
                                    $current_user->id,
                                    1,
                                    $workId,
                                    date('Y-m-d H:i:s')
                                )
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("[GANTT-AUDIT-WORK] Error al registrar auditoría de progreso en trabajo: " . $e->getMessage());
            }
            
            // Registrar reporte de actividad
            $activityReport = ActivityReport::getInstance()
                ->setId(null)
                ->setActivityId($task ['id'])
                ->setProgress(floatval($task ['progress']))
                ->setReport("Avance registrado vía diagrama Gantt. Valor inicial: {$task ['actProgress']}%.  Avance final: {$task ['progress']}%.")
                ->setTimeDuration(0.15)
                ->setTitle('Avance registrado a través del Gantt')
                ->setUserId($current_user->id);
            ActivityReportManager::getInstance($adb)->saveActivityReport($activityReport);
            
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK'));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    } 
	else if ($function == 'GET-PRECREATED-TASK-MODAL') {
        // Endpoint AJAX para servir la modal de tarea/acción bajo demanda
        $taskId = isset($_REQUEST['task_id']) ? $_REQUEST['task_id'] : '';
        $moduleName = isset($_REQUEST['module']) ? $_REQUEST['module'] : '';
        $smarty = new vtigerCRM_Smarty();
        $smarty->assign('idTaskDetailView', $taskId);
        $smarty->assign('FLMODULE', $moduleName);
        // Asignar AVAILABLE_MODULES, AREA_TASK y TASK_LIST
        require_once('modules/preloaded_tasks/lib/PrecreatedTaskUtils.class.php');
        require_once('modules/grid_view/lib/GridViewHelper.class.php');
        $preCreatedTaskUtils = new PrecreatedTaskUtils();
        $moduleObjects = GridViewHelper::fetchAvailableModules($adb);
        $areaTask = $preCreatedTaskUtils->fetchAreaActivity();
        $taskList = $preCreatedTaskUtils->fetchPreCreatedTask();

        // Convertir objetos Module a arrays para compatibilidad con template
        $availableModules = array();
        if (!empty($moduleObjects)) {
            foreach ($moduleObjects as $module) {
                // presence: 0 = visible, 1 = hidden
                $status = ($module->getPresence() == 0) ? 'VISIBLE' : 'HIDDEN';
                $availableModules[] = array(
                    'name' => $module->getName(),
                    'tabid' => $module->getId(),
                    'tablabel' => $module->getLabel(),
                    'status' => $status
                );
            }
        }

        $smarty->assign('AVAILABLE_MODULES', $availableModules);
        $smarty->assign('AREA_TASK', $areaTask);
        $smarty->assign('TASK_LIST', $taskList);

        // Renderiza y retorna solo el HTML de la modal
        //echo $smarty->fetch('centaurus/PrecreatedTaskActivity.tpl');
		echo $smarty->fetch('Smarty/templates/centaurus/PrecreatedTaskActivity.tpl');
        exit;
    } 
    else if ($function == 'CHANGE-STATUS-JOB') {
        try {
            $record = PlatzillaUtils::purify($_POST, 'record', null);
            $taskStatus = PlatzillaUtils::purify($_POST, 'status', null);
            if (empty ($record) || empty($taskStatus)) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            
            $entity = CRMEntity::getInstance($current_module);
            $entity->id = $record;
            $entity->mode = 'edit';
            $entity->retrieve_entity_info($record, $current_module);
            $entity->column_fields ['estado_de_la_orden'] = $taskStatus;
            $entity->save($current_module);
            
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK'));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    } 
    else if ($function == 'CHANGE-STATUS-TASK') {
        try {
            $taskId = PlatzillaUtils::purify($_POST, 'taskId', null);
            $taskStatus = PlatzillaUtils::purify($_POST, 'status', null);
            if (empty ($taskId) || empty($taskStatus)) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            $kanbanBlocks = array(
                'Planeado' => 'Planned',
                'Pospuesto' => 'Postponed',
                'Pendiente' => 'Not Held',
                'Realizado' => 'Held',
            );
            $entity = CRMEntity::getInstance('Calendar');
            $entity->id = $taskId;
            $entity->mode = 'edit';
            $entity->retrieve_entity_info($taskId, 'Calendar');
            $entityStatus = trim($entity->column_fields ['eventstatus']);
            $entityProgress = $entity->column_fields ['progress'];
            $entity->column_fields ['eventstatus'] = $kanbanBlocks[$taskStatus];
            $entity->save('Calendar');
            foreach ($kanbanBlocks as $key => $value) {
                if ($entityStatus == $value) {
                    $entityStatus = $key;
                    break;
                }
            }
            
            $activityReport = ActivityReport::getInstance()
                ->setId(null)
                ->setActivityId($taskId)
                ->setProgress(floatval($entityProgress))
                ->setReport("Cambio de estado desde el estado {$entityStatus} al estado {$taskStatus}")
                ->setTimeDuration(0.15)
                ->setTitle('Cambio de estado desde vista Kanban')
                ->setUserId($current_user->id);
            ActivityReportManager::getInstance($adb)->saveActivityReport($activityReport);
            
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK'));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    } 
    else if ($function == 'CLOSE-PROCESS-STEP') {
		try {
			$caseNumber = PlatzillaUtils::purify ($_REQUEST, 'caseId');
			$flModule   = PlatzillaUtils::purify ($_REQUEST, 'flModule', null);
			$record     = PlatzillaUtils::purify ($_REQUEST, 'recordModule', null);
			$records    = PlatzillaUtils::purify ($_REQUEST, 'recordsId');
				
			if (empty ($records)) {
				throw new Exception ('Registros de procesos no identificados');
			} else if (empty($caseNumber)) {
				throw new Exception ('Registro de caso no identificado');
			} else if (empty($flModule)) {
				throw new Exception ('Módulo de proceso no identificado');
			}
			list ($prosesId, $stepId, $actionStepId) = explode('@', $records);
			$caseDetails = ProcessCasesUtils::getCaseDetails ($adb, array ($caseNumber, $prosesId, $stepId));
			if (empty ($caseDetails)) {
				throw new Exception ('Registros de caso de proceso no encontrado');
			} else if ((floatval ($caseDetails ['step']['number_doc_required']) > 0) && (empty ($record))) {
				throw new Exception ('Imposible cargar documentos requeridos para el cerrar el paso, N° de registro no encontrado');
			}
			$myDocuments = AttachmentsUtils::fetchEntityAttachments($adb, $record);
			
			if ($caseDetails ['step']['step_type'] == 'AUTOMATIC' && !empty ($record)) {
				$backGroundTask = BackgroundTaskManager::getInstance ($adb)->fetchTaskById($caseDetails ['step']['step_task'], true);
				if (!empty ($backGroundTask) && $backGroundTask->getStatus() == 'ENABLED') {
					$taskName   = sha1 ($backGroundTask->getName());
					$linkAction = "/index.php?module=backgroundtasks&action=RunTask&Ajax=true&taskname={$taskName}&record={$record}&return_module={$current_module}&return_action=DetailView&return_record={$record}";
					$linkAction .= "&case_number={$caseNumber}&process_id={$prosesId}&step_id={$actionStepId}&user_id={$current_user->id}";
				}
			}
			
			$smarty     = new vtigerCRM_Smarty ();
			$smarty->assign ('CASE_DETAILS', $caseDetails);
			$smarty->assign ('CASE_NUMBER', $caseNumber);
			$smarty->assign ('DOCUMENTS', $myDocuments);
			$smarty->assign ('FL_MODULE', $flModule);
			$smarty->assign ('MODULE', $current_module);
			$smarty->assign ('RECORD', $record );
			$smarty->assign ('RECORDS', $records);
			$smarty->assign ('TASK_ACTION', isset ($linkAction) ? $linkAction : null);
			$smarty->assign ('TASK_NAME', isset ($linkAction) ? $backGroundTask->getName() : null);
			$smarty->assign ('TODAY', date ('Y-m-d'));
		} catch (Exception $e) {
			$smarty     = new vtigerCRM_Smarty ();
			$smarty->assign ('CASE_DETAILS', null);
			$smarty->assign ('MESSAGE', $e->getMessage());
		}
			$smarty->display ('modules/process_case/CloseProcessStep.tpl');
    } 
    else if ($function == 'DELETE-TASK-RECORD') {
        try {
            $record = PlatzillaUtils::purify($_POST, 'record');
            if (empty ($record)) {
                throw new Exception ('Registro no encontrado');
            } else if (empty($moduleName)) {
                throw new Exception ('Modulo no identificado');
            }
            
            $focus = CRMEntity::getInstance($moduleName);
            $focus->trash($moduleName, $record);
            if ($moduleName == 'Calendar') {
                $adb->pquery(
                    'DELETE FROM vtiger_seactivityrel WHERE activityid=?',
                    array($record)
                );
            }
            
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK'));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    } 
    else if ($function == 'GET-HOW-TO') {
        $smarty = new vtigerCRM_Smarty ();
        try {
            $record = PlatzillaUtils::purify($_REQUEST, 'record');
            if (empty ($record)) {
                throw new Exception ('Imposible encontar el HowTo');
            }
            $howTo = HowToHelper::fetchHowToById($adb, $record);
            $smarty->assign('HOW_TO', $howTo);
            $smarty->display('DetailViewHowTo.tpl');
        } catch (Exception $e) {
            $smarty->assign('IS_ERROR', true);
            $smarty->assign('LABEL', 'Volver');
            $smarty->assign('MESSAGE', $e->getMessage());
            $smarty->assign('TYPE', 'ERROR');
            $smarty->assign('HOW_TO', null);
            $smarty->display('DetailViewHowTo.tpl');
        }
    } 
    else if ($function == 'GET-STEP-MODULE-CRMID') {
		try {
			$caseNumber = PlatzillaUtils::purify($_REQUEST, 'caseid');
			$stepIds    = PlatzillaUtils::purify($_REQUEST, 'stepids');
			$stepType   = PlatzillaUtils::purify($_REQUEST, 'steptype');
			$crmId      = PlatzillaUtils::purify($_REQUEST, 'sequnce', null);
			
			list ($processId, $stepId) = explode ('@', $stepIds);
			if (empty ($caseNumber)) {
				throw new Exception ('Imposible encontar el proceso asociado');
			} else if (empty($moduleName)) {
				throw new Exception ('Modulo no identificado');
			}
			if (!empty ($processId) && !empty ($stepId)) {
				$case = ProcessCasesUtils::getCaseDetails ($adb, array ($caseNumber, $processId, $stepId));
				if (empty ($case)) {
					$dataCase = array (
						'case_number'      => $caseNumber,
						'process_id'       => $processId,
						'step_id'          => $stepId,
						'step_type'        => $stepType,
						'moduleName'       => $moduleName,
						'assigned_user_id' => $current_user->id
					);
					$caseId = ProcessCasesUtils::createNewCase ($dataCase);
				} else if (empty($crmId)) {
					$result = $adb->pquery(
						'SELECT case_number FROM vtiger_crmentity WHERE crmid=? AND deleted=?',
						array ($case['process_casesid'], 0)
					);
					if ($adb->num_rows ($result) > 0) {
						$row = $adb->fetchByAssoc ($result);
						$crmId =  $row ['case_number'];
					}
					
				}
			}
			if (empty ($crmId)) {
				throw new Exception ('Imposible encontar el registro asociado al proceso');
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			$html   = (isset ($crmId)) ? $crmId : '';
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK','html' => $html));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
    } 
    else if ($function == 'JOIN-PROCESS-CASE') {
		try {
			$record          = PlatzillaUtils::purify ($_REQUEST, 'record');
			$businessProcess = PlatzillaUtils::purify ($_REQUEST, 'business_processes');
				
			if (empty ($record)) {
				throw new Exception ('registro de entidad no encontrado');
			} else if (empty ($businessProcess)) {
				throw new Exception ('proceso de negocio no encontrado');
			}
		  
			$entity = CRMEntity::getInstance ($current_module);
			$entity->mode = 'edit';
			$entity->id   = $record;
			$entity->retrieve_entity_info ($record, $current_module);
		  
			ProcessCasesUtils::saveProcessCase ($adb, $current_module, $entity);
			$html = '';
			if (!empty ($_SESSION ['flashmessage'])) {
				$html = 'Se ha creado el caso de proceso: ' . $_REQUEST['case_number'];
			}
				
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK','html' => $html));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
    } 
    else if ($function == 'JOIN-STEP-CRMID') {
		try {
			$crmId        = PlatzillaUtils::purify($_REQUEST, 'crmid');
			$caseId       = PlatzillaUtils::purify($_REQUEST, 'caseid');
			$casesRecords = PlatzillaUtils::purify($_REQUEST, 'records');
			
			ProcessCasesUtils::setRecordToJoinCase (
				$adb,
				array (
					'crmId'      => $crmId,
					'caseId'     => $caseId,
					'records'    => $casesRecords,
					'moduleName' => $moduleName,
				)
			);
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
    } 
    else if ($function == 'SAVE-DOC-CLOSE-STEP') {
	    try {
			$caseNumber = PlatzillaUtils::purify ($_REQUEST, 'caseNumber');
			$caseId	    = PlatzillaUtils::purify ($_REQUEST, 'caseId');
			$record     = PlatzillaUtils::purify ($_REQUEST, 'recordModule', null);
			$records    = PlatzillaUtils::purify ($_REQUEST, 'recordsId');
			$documentId = PlatzillaUtils::purify ($_REQUEST, 'document_id');
			$stepNotes  = PlatzillaUtils::purify ($_REQUEST, 'step_notes');
			
			list ($prosesId, $stepId, $actionStepId) = explode('@', $records);
			$caseDetails = ProcessCasesUtils::getCaseDetails ($adb, array ($caseNumber, $prosesId, $stepId));
			
			$today      = date ('Y-m-d');
			$thisMoment = date ('H:i:s');
	  
			$entityCase                = CRMEntity::getInstance ('process_cases');
			$entityCase->mode          = 'edit';
			$entityCase->id            = $caseDetails ['process_casesid'];
			$entityCase->column_fields = getColumnFields ('process_cases');
			$entityCase->retrieve_entity_info ($caseDetails['process_casesid'], 'process_cases');
			if (
				(!empty ($entityCase->column_fields ['start_date']) && $entityCase->column_fields ['start_date'] != null) ||
				(!empty ($entityCase->column_fields ['start_time']) && $entityCase->column_fields ['start_time'] != null)
			) {
				$startDate = $entityCase->column_fields ['start_date'] . ' ' . $entityCase->column_fields ['start_time'];
			} else {
				$startDate = $caseDetails ['createdtime'];
				$dummy = explode (' ', $startDate);
				$entityCase->column_fields ['start_date'] = $dummy [0];
				$entityCase->column_fields ['start_time'] = $dummy [1];
				$caseDetails['start_date']                = $dummy [0];
				$caseDetails['start_time']                = $dummy [1];
			}
	  
			$dueDate = $today . ' ' . $thisMoment;
			$diff    = strtotime ($dueDate) - strtotime ($startDate);
	  
			$entityCase->column_fields['due_date']          = $today;
			$entityCase->column_fields['due_step_date']     = $today;
			$entityCase->column_fields['end_step_time']     = $thisMoment;
			$entityCase->column_fields['end_time']          = $thisMoment;
			$entityCase->column_fields['step_exec_time']    = floatval ($diff / 3600);
			$entityCase->column_fields['reason_valuation']  = 'Valoración de calidad asignada automáticamente';
			$entityCase->column_fields['quality_valuation'] = 'Bueno';
		    $entityCase->column_fields ['comment']          = $stepNotes;
			$entityCase->save ('process_cases');
			
			ProcessCasesUtils::saveDocumentToCase ($adb, $caseId, $documentId);
			
		    header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
    } 
    else if ($function == 'SAVE-ENTITY-OWNER') {
        try {
            $record = PlatzillaUtils::purify($_POST, 'record');
            $userId = PlatzillaUtils::purify($_POST, 'assigned_user_id');
            if (empty ($record)) {
                throw new Exception ('Registro no encontrado');
            } else if (empty($moduleName)) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            
            $focus = CRMEntity::getInstance($moduleName);
            $focus->id = $record;
            $focus->mode = 'edit';
            $focus->retrieve_entity_info($record, $moduleName);
            $focus->column_fields ['assigned_user_id'] = $userId;
            $focus->save($moduleName);
            
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK'));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    } 
    else if ($function == 'SAVE-QUALITY-ASSESSMENT') {
	    try {
			$record           = PlatzillaUtils::purify($_REQUEST, 'record');
			$qualityValuation = PlatzillaUtils::purify($_REQUEST, 'quality_valuation');
			$reasonValuation  = PlatzillaUtils::purify($_REQUEST, 'reason_valuation');
			$codStep		  = PlatzillaUtils::purify($_REQUEST, 'code_step');
			$relatedRecord    = PlatzillaUtils::purify($_REQUEST, 'related_record');
			
			if (empty ($record)) {
				throw new Exception ('Registro no encontrado');
			} else if (empty($current_module)) {
				throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
			}
			$entity       = CRMEntity::getInstance ('process_cases');
			$entity->id   = $record;
			$entity->mode = 'edit';
			$entity->retrieve_entity_info ($record, 'process_cases');
		    $entity->column_fields['reason_valuation']  = $reasonValuation;
			$entity->column_fields['quality_valuation'] = $qualityValuation;
		    $entity->save ('process_cases');
			if ($qualityValuation == 'Malo') {
				$affairsId     = null;
				$affairsEntity = CRMEntity::getInstance ('affairs');
				$result        = $adb->pquery (
					'SELECT affairsid FROM vtiger_process_cases WHERE process_casesid=?',
					array ($record)
				);
				if ($adb->num_rows($result) && $adb->num_rows($result) > 0) {
					$row       = $adb->fetchByAssoc ($result);
					$affairsId = $row ['affairsid'];
				}
				if (empty ($affairsId)) {
					$affairsEntity->mode = 'create';
					$affairsEntity->column_fields                       = getColumnFields ('affairs');
					$affairsEntity->column_fields['affair_title']       = 'Calidad mala en paso Nro ' . $codStep;
					$affairsEntity->column_fields['assigned_user_id']   = $current_user->id;
					$affairsEntity->column_fields['affair_description'] = $reasonValuation;
					$affairsEntity->column_fields['affair_date']        = date ('Y-m-d');
					$affairsEntity->column_fields['affair_nature']      = 'Ejecución de paso de proceso';
					$affairsEntity->column_fields['affair_priority']    = 'Normal';
					$affairsEntity->column_fields['affair_status']      = 'Nuevo';
					$affairsEntity->column_fields['process_cases']      = $record;
					$affairsEntity->save ('affairs');
					$adb->pquery (
						'UPDATE vtiger_process_cases SET affairsid=? WHERE process_casesid=?',
						array ($affairsEntity->id, $record)
					);
					if (!empty ($relatedRecord)) {
						$adb->pquery (
							'INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES (?, ?, ?, ?)',
							array($relatedRecord, $current_module, $affairsEntity->id, 'affairs')
						);
					}
				} else {
					$affairsEntity->mode                                = 'edit';
					$affairsEntity->id                                  = $affairsId;
					$affairsEntity->retrieve_entity_info ($affairsId, 'affairs');
					$affairsEntity->column_fields['affair_description'] = $reasonValuation;
					$affairsEntity->save ('affairs');
				}
			}
			
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
		
    } 
    else if ($function == 'SAVE-STEP-NOTES') {
		try {
			$record    = PlatzillaUtils::purify($_REQUEST, 'record');
			$stepType  = PlatzillaUtils::purify($_REQUEST, 'step_type');
			$stepNotes = PlatzillaUtils::purify($_REQUEST, 'step_notes');
			$startDate = PlatzillaUtils::purify($_REQUEST, 'date_start');
			$TimeStart = PlatzillaUtils::purify($_REQUEST, 'time_start');
			if (empty ($record)) {
				throw new Exception ('Registro no encontrado');
			} else if (empty($current_module)) {
				throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
			}
			$entity       = CRMEntity::getInstance ('process_cases');
			$entity->id   = $record;
			$entity->mode = 'edit';
			$entity->retrieve_entity_info ($record, 'process_cases');
			$entity->column_fields ['comment'] = $stepNotes;
			if ($stepType == 'MANUAL') {
				$entity->column_fields ['start_date'] = $startDate;
				$entity->column_fields ['start_time'] = $TimeStart;
			}
			$entity->save ('process_cases');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
    } 
    else if ($function == 'SELECT-MODULE-STEP') {
		try {
			$caseNumber = PlatzillaUtils::purify($_REQUEST, 'caseId');
			$records = PlatzillaUtils::purify($_REQUEST, 'records');
			if (empty ($caseNumber)) {
				throw new Exception ('Imposible encontar el proceso asociado');
			} else if (empty($moduleName)) {
				throw new Exception ('Modulo no identificado');
			}
			$entitys = ProcessCasesUtils::fetchRecordsToJoinCase ($adb, $moduleName, $current_user->id);
			
			$smarty     = new vtigerCRM_Smarty ();
			$smarty->assign ('ADB', $adb);
			$smarty->assign ('CASE_NUMBER', $caseNumber);
			$smarty->assign ('ENTITYS', $entitys);
			$smarty->assign ('FLMODULE', $moduleName);
			$smarty->assign ('MODULE', $current_module);
			$smarty->assign ('RECORDS', $records);
		} catch (Exception $e) {
			$smarty     = new vtigerCRM_Smarty ();
			$smarty->assign ('ENTITIES', null);
			$smarty->assign ('MESSAGE', $e->getMessage());
		}
		$smarty->display('modules/process_case/SelectRecordToStep.tpl');
    } 
    else if ($function == 'SET-QUALITY-ASSESSMENT') {
	    try {
			$records       = PlatzillaUtils::purify($_REQUEST, 'recordsId');
			$caseNumber    = PlatzillaUtils::purify($_REQUEST, 'caseNumbser');
			$caseType      = PlatzillaUtils::purify($_REQUEST, 'caseType', null);
			$relatedRecord = PlatzillaUtils::purify($_REQUEST, 'relatedRecord', null);
			if (empty ($records)) {
				throw new Exception ('Registros de procesos no identificados');
			} else if (empty($caseNumber)) {
				throw new Exception ('Registro de caso no identificado');
			}
			list ($prosesId, $stepId) = explode('@', $records);
	  
			$caseDetails = ProcessCasesUtils::getCaseDetails ($adb, array ($caseNumber, $prosesId, $stepId));
			if (empty ($caseDetails)) {
				throw new Exception ('Paso no iniciado o ha sido eliminado');
			}
			$smarty     = new vtigerCRM_Smarty ();
			$smarty->assign ('CASE_DETAILS', $caseDetails);
			$smarty->assign ('MODULE', $current_module);
		    $smarty->assign ('QUALITY_VALUATION', ProcessCasesUtils::getQualityValuation ());
		    $smarty->assign ('RELATED_RECORD', $relatedRecord);
			$smarty->assign ('THEME', $theme);
		} catch (Exception $e) {
			$smarty     = new vtigerCRM_Smarty ();
			$smarty->assign ('CASE_DETAILS', null);
			$smarty->assign ('MESSAGE', $e->getMessage());
		}
		$smarty->display('modules/process_case/qualityAssessmentEditView.tpl');
    } 
    else if ($function == 'STEP-EDIT-NOTES') {
	    try {
		    $records    = PlatzillaUtils::purify($_REQUEST, 'recordsId');
			$caseNumber = PlatzillaUtils::purify($_REQUEST, 'caseId');
			$caseType   = PlatzillaUtils::purify($_REQUEST, 'caseType', null);
			if (empty ($records)) {
				throw new Exception ('Registros de procesos no identificados');
			} else if (empty($caseNumber)) {
				throw new Exception ('Registro de caso no identificado');
			}
			list ($prosesId, $stepId) = explode('@', $records);
			
			$caseDetails = ProcessCasesUtils::getCaseDetails ($adb, array ($caseNumber, $prosesId, $stepId));
			if (empty ($caseDetails) && $caseType !== 'MANUAL') {
				throw new Exception ('Paso no iniciado o ha sido eliminado');
			} else if (empty ($caseDetails) && $caseType == 'MANUAL') {
				$dataCase = array (
					'case_number'      => $caseNumber,
					'process_id'       => $prosesId,
					'step_id'          => $stepId,
					'step_type'        => 'MANUAL',
					'moduleName'       => 'Manual',
					'assigned_user_id' => $current_user->id
				);
				$caseId = ProcessCasesUtils::createNewCase ($dataCase);
				$adb->pquery (
					'UPDATE vtiger_process_cases SET case_number=? WHERE process_casesid=?',
					array ($caseNumber, $caseId)
				);
				$caseDetails = ProcessCasesUtils::getCaseDetails ($adb, array ($caseNumber, $prosesId, $stepId));
				if (empty ($caseDetails)) {
					throw new Exception ('¡algo salio mal!');
				}
			}
			
		    $smarty     = new vtigerCRM_Smarty ();
			$smarty->assign ('CASE_DETAILS', $caseDetails);
			$smarty->assign ('MODULE', $current_module);
		    $smarty->assign ('THEME', $theme);
	    } catch (Exception $e) {
		    $smarty     = new vtigerCRM_Smarty ();
		    $smarty->assign ('CASE_DETAILS', null);
			$smarty->assign ('MESSAGE', $e->getMessage());
		}
	    $smarty->display('modules/process_case/StepEditViewNotes.tpl');
    } 
    else if ($function == 'VIEW-JOBS') {
        try {
            $crmId = PlatzillaUtils::purify($_REQUEST, 'record');
            if (empty ($moduleName) || $moduleName != 'proyectos') {
                throw new Exception ('Uoops! Algo salió mal, intente mas tarde');
            } else if (empty ($crmId)) {
                throw new Exception ('Registro no encontrado');
            }
            $gattView = false;
            $jobStatuses = null;
            $kanbanView = false;
            $relModule = taskToProject::JOB_RELATED_MODULE;
            $smarty = new vtigerCRM_Smarty ();
            $worksViews = WorksViewHelper::fetchWorksView($adb, $current_user->id);
            if (! empty($worksViews)) {
                foreach ($worksViews as $workView) {
                    if ($workView->getView() == 'GANTT' && $workView->getViewStatus() == 'VISIBLE') {
                        $gattView = true;
                    } else if ($workView->getView() == 'KANBAN' && $workView->getViewStatus() == 'VISIBLE') {
                        $kanbanView = true;
                    }
                }
            } else {
                $gattView = true;
            }
            // Obtener el campo identificador del módulo desde vtiger_entityname
            $entityNameQuery = "SELECT fieldname, fieldidentifier FROM vtiger_entityname 
                                WHERE modulename = ? AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)";
            $entityNameResult = $adb->pquery($entityNameQuery, array('proyectos', 'proyectos'));
            
            $identifierField = null;
            if ($adb->num_rows($entityNameResult) > 0) {
                $entityNameData = $adb->fetchByAssoc($entityNameResult);
                // Usar fieldidentifier si existe, sino usar fieldname
                if (!empty($entityNameData['fieldidentifier'])) {
                    $identifierField = $entityNameData['fieldidentifier'];
                } else if (!empty($entityNameData['fieldname'])) {
                    $identifierField = $entityNameData['fieldname'];
                }
            }
            
            // Obtener información del proyecto
            $projectEntity = CRMEntity::getInstance('proyectos');
            $projectEntity->retrieve_entity_info($crmId, 'proyectos');
            
            // Obtener el nombre usando el campo identificador o la etiqueta del módulo
            if (!empty($identifierField) && isset($projectEntity->column_fields[$identifierField])) {
                $projectName = $projectEntity->column_fields[$identifierField];
            } else {
                // Si no hay identificador, usar la etiqueta del módulo + ID
                $moduleLabel = getTabIdLabelByName('proyectos');
                $projectName = $moduleLabel . ' #' . $crmId;
            }
            
            if ($gattView || $kanbanView) {
                try {
                    $jobsProject = taskToProject::getInstance($adb)->fetchRelatedWork($crmId, $current_user, null);
                } catch (Exception $fetchEx) {
                    throw $fetchEx;
                }
            }
            if ($gattView && ! empty ($jobsProject)) {
                // NIVEL 1: Proyecto (nivel superior)
                // Obtener progreso del proyecto desde vtiger_proyectos.porcentaje_de_avance_genera
                // El valor en BD ya está multiplicado por 100 (16.16% = 16.16), solo convertir a entero
                $projectProgress = isset($projectEntity->column_fields['porcentaje_de_avance_genera']) 
                    ? intval(floatval($projectEntity->column_fields['porcentaje_de_avance_genera'])) 
                    : 0;
                $projectTask = new stdClass();
                $projectTask->id = "project-{$crmId}";
                $projectTask->name = html_entity_decode($projectName, ENT_QUOTES, 'UTF-8') . ' (PROYECTO)';
                $projectTask->start = null;
                $projectTask->end = null;
                $projectTask->progress = $projectProgress;
                $projectTask->dependencies = '';
                $projectTask->custom_class = 'task-level-1 task-project';
                $projectTask->totalGroup = 0;
                $projectTask->relModule = 'proyectos';
                $projectTask->actProgress = $projectProgress;
                $jobToGantt[] = (object)$projectTask;
                $projectIndex = count($jobToGantt) - 1;
                
                $ganttGroups = array();
                $dependencies = null;
                foreach ($jobsProject as $jobProject) {
                    // Normalizar fechas del trabajo al formato de base de datos (YYYY-mm-dd)
                    $rawStartDate = $jobProject->getStartDate();
                    $rawDueDate   = $jobProject->getEstimatedDueDate();

                    $normalizedStartDate = $rawStartDate;
                    $normalizedDueDate   = $rawDueDate;

                    // Si no viene ya en formato de BD, convertir desde el formato de usuario
                    if (!empty($rawStartDate) && !preg_match('/^\d{4}-\d{2}-\d{2}/', $rawStartDate)) {
                        $normalizedStartDate = DateTimeField::convertToDBFormat($rawStartDate, $current_user);
                    }
                    if (!empty($rawDueDate) && !preg_match('/^\d{4}-\d{2}-\d{2}/', $rawDueDate)) {
                        $normalizedDueDate = DateTimeField::convertToDBFormat($rawDueDate, $current_user);
                    }

                    $jobStarDate = !empty($normalizedStartDate) ? date_create($normalizedStartDate) : null;
                    $JobDueDate  = !empty($normalizedDueDate)   ? date_create($normalizedDueDate)   : null;

                    if (! count($ganttGroups) || ! in_array($jobProject->getStageName(), array_keys($ganttGroups))) {
                        $dependencies = null;
                    } else {
                        $lastDependence = $ganttGroups[$jobProject->getStageName()][0];
                        $dependencies = $ganttGroups[$jobProject->getStageName()][1];
                    }
                    
                    if (empty ($dependencies)) {
                        $dependencies = rand(5, 15);
                        $dependencies = "stage-{$dependencies}";
                        // NIVEL 2: Etapa
                        $stage = new stdClass ();
                        $stage->start = $normalizedStartDate;
                        $stage->end = null;
                        $stage->name = html_entity_decode($jobProject->getStageName(), ENT_QUOTES, 'UTF-8');
                        $stage->id = $dependencies;
                        $stage->progress = 0;
                        $stage->dependencies = "project-{$crmId}";  // Depende del proyecto
                        $stage->custom_class = 'task-level-2 task-stage';
                        $stage->totalGroup = 0;
                        $stage->relModule = $relModule;
                        $stage->actProgress = 0;
                        $jobToGantt [] = (object)$stage;
                        $lastDependence = end(array_keys($jobToGantt));
                        $ganttGroups [$jobProject->getStageName()] = array($lastDependence, $dependencies);
                    }
                    // NIVEL 3: Trabajo
                    $jobId = "{$jobProject->getCrmIdJob ()}@{$jobProject->getId ()}";
                    $job = new stdClass ();
                    $job->start = $normalizedStartDate;
                    $job->end = $normalizedDueDate;
                    $job->name = html_entity_decode($jobProject->getJobName(), ENT_QUOTES, 'UTF-8') . ' (TRABAJOS)';
                    $job->id = $jobId;
                    $job->progress = intval($jobProject->getPercentageCompletion());
                    $job->dependencies = $dependencies;  // Depende de la etapa
                    $job->custom_class = 'task-level-3 task-job';
                    $job->totalGroup = 0;
                    $job->relModule = $moduleName;
                    $job->actProgress = intval($jobProject->getPercentageCompletion());
                    $jobToGantt [] = (object)$job;
                    $jobIndex = count($jobToGantt) - 1;
                    
                    // NIVEL 4: Tareas del trabajo
                    $jobCrmId = $jobProject->getCrmIdJob();
                    $taskQuery = "SELECT activityid FROM vtiger_seactivityrel WHERE crmid = ?";
                    $taskResult = $adb->pquery($taskQuery, array($jobCrmId));
                    
                    if ($adb->num_rows($taskResult) > 0) {
                        while ($taskRow = $adb->fetchByAssoc($taskResult)) {
                            $activityId = $taskRow['activityid'];
                            
                            // Obtener datos de la tarea
                            $taskDetailQuery = "SELECT vtiger_activity.subject, vtiger_activity.date_start, 
                                                       vtiger_activity.due_date, vtiger_activity.progress 
                                                FROM vtiger_activity 
                                                WHERE vtiger_activity.activityid = ?";
                            $taskDetailResult = $adb->pquery($taskDetailQuery, array($activityId));
                            
                            if ($adb->num_rows($taskDetailResult) > 0) {
                                $taskDetail = $adb->fetchByAssoc($taskDetailResult);
                                
                                $task = new stdClass();
                                $task->id = $activityId;
                                $task->name = html_entity_decode($taskDetail['subject'], ENT_QUOTES, 'UTF-8') . ' (Tarea/Acción)';
                                $task->start = $taskDetail['date_start'];
                                $task->end = $taskDetail['due_date'];
                                $task->progress = intval($taskDetail['progress']);
                                $task->dependencies = $jobId;  // Depende del trabajo
                                $task->custom_class = 'task-level-4 task-item';
                                $task->totalGroup = 0;
                                $task->relModule = 'Calendar';
                                $task->actProgress = intval($taskDetail['progress']);
                                $jobToGantt[] = (object)$task;
                                
                                // Actualizar progreso y fechas del trabajo
                                $taskStartDate = date_create($taskDetail['date_start']);
                                $taskEndDate = date_create($taskDetail['due_date']);
                                
                                if (empty($jobToGantt[$jobIndex]->start) || 
                                    $taskStartDate < date_create($jobToGantt[$jobIndex]->start)) {
                                    $jobToGantt[$jobIndex]->start = $taskDetail['date_start'];
                                }
                                if (empty($jobToGantt[$jobIndex]->end) || 
                                    $taskEndDate > date_create($jobToGantt[$jobIndex]->end)) {
                                    $jobToGantt[$jobIndex]->end = $taskDetail['due_date'];
                                }
                                $jobToGantt[$jobIndex]->totalGroup++;
                            }
                        }
                    }
                    
                    if (isset ($jobToGantt [$lastDependence]) && $jobToGantt [$lastDependence] instanceof stdClass) {
                        $jobToGantt [$lastDependence]->progress += intval($jobProject->getProjectProgress());
                        $jobToGantt [$lastDependence]->actProgress += intval($jobProject->getProjectProgress());
                        $jobToGantt [$lastDependence]->totalGroup += 1;
                        if (!empty($jobToGantt[$lastDependence]->start) && $jobStarDate instanceof DateTime) {
                            $compareDate = new DateTime($jobToGantt[$lastDependence]->start);
                            if ($jobStarDate < $compareDate) {
                                $jobToGantt[$lastDependence]->start = $normalizedStartDate;
                            }
                        }

                        if (!empty($jobToGantt[$lastDependence]->end) && $JobDueDate instanceof DateTime) {
                            $compareDate = new DateTime($jobToGantt[$lastDependence]->end);
                            if ($JobDueDate > $compareDate) {
                                $jobToGantt[$lastDependence]->end = $normalizedDueDate;
                            }
                        }
                    }
                    
                    // Actualizar fechas y progreso del proyecto (nivel 1)
                    if ($jobStarDate instanceof DateTime) {
                        if (empty($jobToGantt[$projectIndex]->start) || 
                            $jobStarDate < date_create($jobToGantt[$projectIndex]->start)) {
                            $jobToGantt[$projectIndex]->start = $normalizedStartDate;
                        }
                    }
                    if ($JobDueDate instanceof DateTime) {
                        if (empty($jobToGantt[$projectIndex]->end) || 
                            $JobDueDate > date_create($jobToGantt[$projectIndex]->end)) {
                            $jobToGantt[$projectIndex]->end = $normalizedDueDate;
                        }
                    }
                    $jobToGantt[$projectIndex]->progress += intval($jobProject->getProjectProgress());
                    $jobToGantt[$projectIndex]->totalGroup++;
                }
                
                // Calcular progreso promedio del proyecto
                if ($jobToGantt[$projectIndex]->totalGroup > 0) {
                    $jobToGantt[$projectIndex]->progress = 
                        $jobToGantt[$projectIndex]->progress / $jobToGantt[$projectIndex]->totalGroup;
                }
            }
            
            if ($kanbanView && ! empty ($jobsProject)) {
                $jobStatuses = PicklistManager::getInstance($adb)->fetchPicklistRawValues('estado_de_la_orden', $isInstance);
                $jobEntity = CRMEntity::getInstance('orden_de_trabajo');
                foreach ($jobsProject as $jobProject) {
                    $jobEntity->id = $jobProject->getCrmIdJob();
                    $jobEntity->mode = 'edit';
                    $jobEntity->retrieve_entity_info($jobProject->getCrmIdJob(), 'orden_de_trabajo');
                    $smarty->assign('assignedUser', $jobProject->getResponsibleJob());
                    $smarty->assign('title', $jobProject->getJobName());
                    $smarty->assign('userAvatar', (! empty($jobProject->getResponsibleJob())) ? getUserImageName($jobProject->getResponsibleJob()) : null);
                    $kanbanTitle = $smarty->fetch('utils/kanbanTitle.tpl');

                    // Normalizar fechas para Kanban al formato de base de datos y luego formatear para mostrar
                    $rawStartDate = $jobProject->getStartDate();
                    $rawDueDate   = $jobProject->getEstimatedDueDate();
                    $normalizedStartDate = $rawStartDate;
                    $normalizedDueDate   = $rawDueDate;
                    if (!empty($rawStartDate) && !preg_match('/^\d{4}-\d{2}-\d{2}/', $rawStartDate)) {
                        $normalizedStartDate = DateTimeField::convertToDBFormat($rawStartDate, $current_user);
                    }
                    if (!empty($rawDueDate) && !preg_match('/^\d{4}-\d{2}-\d{2}/', $rawDueDate)) {
                        $normalizedDueDate = DateTimeField::convertToDBFormat($rawDueDate, $current_user);
                    }
                    $jobStarDate = !empty($normalizedStartDate) ? date_create($normalizedStartDate) : null;
                    $JobDueDate  = !empty($normalizedDueDate)   ? date_create($normalizedDueDate)   : null;

                    $smarty->assign('dateStart', ($jobStarDate instanceof DateTime) ? date_format($jobStarDate, 'd-m-Y g:ia') : null);
                    $smarty->assign('dueDate', ($JobDueDate instanceof DateTime) ? date_format($JobDueDate, 'd-m-Y g:ia') : null);
                    $smarty->assign('progress', intval($jobProject->getPercentageCompletion()));
                    $kanbanFooter = $smarty->fetch('utils/kanbanFooter.tpl');
                    
                    $kanban = new stdClass ();
                    $kanban->id = $jobProject->getCrmIdJob();
                    $kanban->title = $kanbanTitle;
                    $kanban->block = $jobEntity->column_fields ['estado_de_la_orden'];
                    $kanban->link = $jobProject->getCrmIdJob();
                    $kanban->link_text = $relModule;
                    $kanban->footer = $kanbanFooter;
                    $kanbanData [] = $kanban;
                }
            }
            
            $smarty->assign('HAS_GANTT', $gattView);
            $smarty->assign('HAS_KANBAN', $kanbanView);
            $smarty->assign('JOB_STATES', (! empty($jobStatuses)) ? json_encode($jobStatuses) : null);
            $smarty->assign('KANBAN_BLOCKS', (count($kanbanData)) ? json_encode($kanbanData) : null);
            $smarty->assign('RELATED_MODULE', $relModule);
            $smarty->assign('WORKS_GANTT', (count($jobToGantt) ? json_encode($jobToGantt, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) : null));
            $htmlOutput = $smarty->fetch('modules/proyectos/DetailViewJobActivity.tpl');
            // Limpiar cualquier output buffer anterior y BOM
            if (ob_get_length()) {
                ob_clean();
            }
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            $jsonOutput = json_encode(array('error' => 'OK', 'html' => $htmlOutput));
            // Eliminar BOM si está presente
            $jsonOutput = preg_replace('/^\xEF\xBB\xBF/', '', $jsonOutput);
            echo $jsonOutput;
        } catch (Exception $e) {
            if (ob_get_length()) {
                ob_clean();
            }
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            $jsonOutput = json_encode(array('error' => $e->getMessage()));
            $jsonOutput = preg_replace('/^\xEF\xBB\xBF/', '', $jsonOutput);
            echo $jsonOutput;
        }
    } 
    else if ($function == 'VIEW-TASK') {
        try {
            if ($isInstance) {
                if (! StoreUtils::isInstanceVerified($_SESSION ['platInstancia'])) {
                    throw new Exception ('Debes verificar tu cuenta', 400);
                }
                
                $psm = PlatformSubscriptionManager::getInstance($masterAdb);
                $subscription = $psm->fetchSubscription($_SESSION ['platInstancia']);
                if ((empty ($subscription)) || ($subscription->getStatus() == PlatformSubscription::STATUS_INACTIVE)) {
                    throw new Exception ('Tu suscripción se encuentra inactiva', 403);
                }
                
                $canCreateRecords = true;
            } else {
                $canCreateRecords = true;
            }
            
            if (empty ($moduleName)) {
                throw new Exception ('Módulo no encontrado');
            }

            $tabId = PlatzillaUtils::purify($_POST, 'tabid', null);
            $crmid = PlatzillaUtils::purify($_POST, 'record', null);
            $activities = array();
            $tasksViewData = null;
            require("{$_SESSION ['plat']}/user_privileges/user_privileges_{$current_user->id}.php");
            
            // Obtener información del trabajo (orden_de_trabajo) para nivel 1 del Gantt
            $workName = '';
            $workId = null;
            $workProgress = 0;
            if (!empty($crmid) && $moduleName == 'orden_de_trabajo') {
                $workResult = $adb->pquery(
                    "SELECT titulo, overall_progress_perc FROM vtiger_orden_de_trabajo 
                     INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_orden_de_trabajo.orden_de_trabajoid 
                     WHERE vtiger_orden_de_trabajo.orden_de_trabajoid = ? AND vtiger_crmentity.deleted = 0",
                    array($crmid)
                );
                if ($adb->num_rows($workResult) > 0) {
                    $workRow = $adb->fetchByAssoc($workResult);
                    $workName = $workRow['titulo'];
                    $workId = $crmid;
                    $workProgress = intval($workRow['overall_progress_perc']);
                }
            }
            
            if (! empty ($crmid)) {
                // Para proveedores, obtener tareas de vtiger_supplieractivityrel
                if ($moduleName == 'proveedores') {
                    $results = $adb->run_query_allrecords("SELECT activityid FROM vtiger_supplieractivityrel WHERE proveedoresid={$crmid}");
                } else {
                    $results = $adb->run_query_allrecords("SELECT activityid FROM vtiger_seactivityrel WHERE crmid={$crmid}");
                }
                if (! empty($results)) {
                    foreach ($results as $result) {
                        $activities [] = $result ['activityid'];
                    }
                }
                
                $tasksView = DataViewUtils::fetchView($adb, 'Calendar', 'ALL');
                //ALL PENDING TASK
                if (empty ($tasksView)) {
                    throw new Exception ('La vista solicitada no se encuentra registrada');
                }
                
                $queryGenerator = new QueryGenerator ('Calendar', $current_user);
                $queryGenerator->initForCustomViewById($tasksView->getId());
                $queryGenerator->getQuery();
                
                $conditionalWhere = $queryGenerator->getConditionalWhere();
                $tasksViewPermissions = DataViewUtils::fetchViewPermissions($adb, $tasksView, $current_user);
                if ((! is_array($tasksViewPermissions)) || (! in_array(DataViewUtils::PERMISSION_CAN_USE, $tasksViewPermissions))) {
                    throw new Exception ('Acceso denegado');
                }
                
                $tasksViewData = DataViewUtils::fetchViewData($adb, $tasksView, $current_user, null, 'vtiger_activity.date_start ASC', $conditionalWhere, null, $moduleName);
                $taskCategories = DataViewUtils::getAvailableTaskCategories($adb, $current_user->id);
                $taskCategoriesIds = array_values($taskCategories);
                $activitiesRecords = array();
                $totalRecords = count($tasksViewData ['records']);
                $processedActivities = array();
                $taskToGantt = array();
                $ganttGroups = array();
                $kanbanData = array();
                $kanbanBlocks = array(
                    'Planned' => 'Planeado',
                    'Postponed' => 'Pospuesto',
                    'Not Held' => 'Pendiente',
                    'Held' => 'Realizado',
                );
                $ganttTab = GanttTaskUtils::fetchGanttByModule($adb, $current_module);
                $kambanTab = KanbanTaskUtils::fetchKanbanByModule($adb, $current_module);
                $hasGantt = (! empty ($ganttTab)) ? $ganttTab ['detail_view'] : 0;
                $hasKanban = (! empty ($kambanTab)) ? $kambanTab ['detail_view'] : 1;
                $smarty = new vtigerCRM_Smarty ();
                
                // Para proveedores, construir Gantt con jerarquía Proyecto -> Trabajo -> Tarea
                if ($moduleName == 'proveedores') {
                    require_once('modules/proveedores/lib/SupplierGanttHelper.class.php');
                    $taskToGantt = SupplierGanttHelper::buildSupplierTasksGantt($adb, $crmid, $current_user);
                    $workTaskId = null; // No aplica para proveedores
                } else {
                    // Agregar el trabajo como nivel 1 del Gantt (si es orden_de_trabajo)
                    $workTaskId = null;
                    if (!empty($workName) && !empty($workId)) {
                        $workTaskId = "work-{$workId}";
                        $workTask = new stdClass();
                        $workTask->id = $workTaskId;
                        $workTask->name = $workName . ' (TRABAJO)';
                        $workTask->start = null; // Se calculará basándose en las tareas hijas
                        $workTask->end = null;
                        $workTask->progress = $workProgress;
                        $workTask->dependencies = '';
                        $workTask->custom_class = 'task-level-1 task-work';
                        $workTask->level = 1;
                        $workTask->totalGroup = 0;
                        $workTask->relModule = $moduleName;
                        $workTask->actProgress = $workProgress;
                        $taskToGantt[] = $workTask;
                    }
                }
                
                // Obtener eventstatus y priority de todas las actividades en una sola consulta
                $activityIds = array();
                for ($k = 0; $k < $totalRecords; $k++) {
                    $activityIds[] = $tasksViewData ['records'][$k]['crmid'];
                }
                
                // Consulta única para obtener eventstatus y priority de todas las actividades
                $activityStatusPriority = array();
                if (!empty($activityIds)) {
                    $placeholders = str_repeat('?,', count($activityIds) - 1) . '?';
                    $result = $adb->pquery(
                        "SELECT activityid, eventstatus, priority FROM vtiger_activity WHERE activityid IN ($placeholders)",
                        $activityIds
                    );
                    if ($adb->num_rows($result) > 0) {
                        while ($row = $adb->fetchByAssoc($result)) {
                            $activityStatusPriority[$row['activityid']] = array(
                                'eventstatus' => $row['eventstatus'],
                                'priority' => $row['priority']
                            );
                        }
                    }
                }
                
                // Procesar tareas directamente sin categorías (solo trabajo -> tareas)
                for ($k = 0; $k < $totalRecords; $k++) {
                    if ((! in_array($tasksViewData ['records'][$k]['crmid'], $activities))) {
                        continue;
                    } else if (in_array($tasksViewData ['records'][$k]['idactividad'], $processedActivities)) {
                        continue;
                    }
                    
                    // Obtener categoría para agrupar en activitiesRecords (para vista de lista)
                    $categoryId = $tasksViewData ['records'][$k]['categoryid'];
                    $categoryName = isset($taskCategories[$categoryId]) ? $taskCategories[$categoryId] : 'Sin categoría';
                    
                    $tasksViewData ['records'][$k]['invitee'] = DataViewUtils::fetchInviteesByActivity($adb, $tasksViewData ['records'][$k]['idactividad'], $current_user->id);
                    
                    if (($tasksViewData ['records'][$k]['activitytype'] == 'Assignment') || $tasksViewData ['records'][$k]['activitytype'] == 'Activity') {
                        $tasksViewData ['records'][$k]['str_date_start'] = $tasksViewData ['records'][$k]['date_start'];
                    } else {
                        $taskStarDate = date_create($tasksViewData ['records'][$k]['date_start'] . ' ' . $tasksViewData ['records'][$k]['time_start']);
                        $tasksViewData ['records'][$k]['str_date_start'] = date_format ($taskStarDate, 'd-m-Y  g:ia');
                    }
                    
                    $taskDueDate = date_create($tasksViewData ['records'][$k]['due_date'] . ' ' . $tasksViewData ['records'][$k]['time_end']);
                    $tasksViewData ['records'][$k]['str_due_date'] = date_format($taskDueDate, 'd-m-Y g:ia');
                    $tasksViewData ['records'][$k]['how_to']       = HowToHelper::hasHowTo ($adb, 'Calendar', $tasksViewData ['records'][$k]['crmid'], 'DetailView_Task');
                    
                    // Formatear números con preferencias del usuario
                    require_once('include/utils/NumberHelper.class.php');
                    $numberingHelper = NumberHelper::getInstance($adb, $current_user);
                    
                    $tasksViewData ['records'][$k]['estimated_time'] = $numberingHelper->setNumberFormat($tasksViewData ['records'][$k]['estimated_time'], 'estimated_time');
                    $tasksViewData ['records'][$k]['estimated_cost'] = $numberingHelper->setNumberFormat($tasksViewData ['records'][$k]['estimated_cost'], 'estimated_cost');
                    
                    // Asignar eventstatus y priority desde la consulta optimizada si faltan
                    $activityId = $tasksViewData ['records'][$k]['crmid'];
                    if (isset($activityStatusPriority[$activityId])) {
                        if (!isset($tasksViewData ['records'][$k]['eventstatus']) || empty($tasksViewData ['records'][$k]['eventstatus'])) {
                            $tasksViewData ['records'][$k]['eventstatus'] = $activityStatusPriority[$activityId]['eventstatus'];
                        }
                        if (!isset($tasksViewData ['records'][$k]['priority']) || empty($tasksViewData ['records'][$k]['priority'])) {
                            $tasksViewData ['records'][$k]['priority'] = $activityStatusPriority[$activityId]['priority'];
                        }
                    }
                    
                    $desc = isset($tasksViewData['records'][$k]['description']) ? trim($tasksViewData['records'][$k]['description']) : '';
                    if ($desc !== '' && stripos($desc, '<li') !== false && !preg_match('/^\s*<(ul|ol)[\s>]/i', $desc)) {
                        $tasksViewData['records'][$k]['description'] = '<ul>' . $desc . '</ul>';
                    }
                    $activitiesRecords[$categoryName][]            = $tasksViewData ['records'][$k];
                    
                    // Crear tarea para Gantt - depende directamente del trabajo (sin categoría intermedia)
                    // Para proveedores, el Gantt ya se construyó con SupplierGanttHelper
                    // Excluir tareas tipo "Job" del Gantt (pero mantenerlas en la tabla)
                    if ($moduleName != 'proveedores' && $tasksViewData ['records'][$k]['activitytype'] != 'Job') {
                        $task = new stdClass ();
                        $task->start = $tasksViewData ['records'][$k]['date_start'];
                        $task->end = $tasksViewData ['records'][$k]['due_date'];
                        $task->name = $tasksViewData ['records'][$k]['subject'] . ' (Tarea/Acción)';
                        $task->id = $tasksViewData ['records'][$k]['crmid'];
                        $task->progress = intval($tasksViewData ['records'][$k]['progress']);
                        // Las tareas dependen directamente del trabajo
                        $task->dependencies = !empty($workTaskId) ? $workTaskId : '';
                        $task->custom_class = 'task-level-4 task-item'; // Nivel 4 = Tarea
                        $task->level = 4;
                        $task->totalGroup = 0;
                        $task->relModule = $moduleName;
                        $task->actProgress = intval($tasksViewData ['records'][$k]['progress']);
                        $taskToGantt [] = (object)$task;
                    }
                    
                    $eventStatus = (! empty ($tasksViewData ['records'][$k]['eventstatus'])) ? $tasksViewData ['records'][$k]['eventstatus'] : 'Planned';
                    
                    $smarty->assign('assignedUser', $tasksViewData ['records'][$k]['assigned_user_id']);
                    $smarty->assign('title', $tasksViewData ['records'][$k]['subject']);
                    $smarty->assign('userAvatar', $tasksViewData ['records'][$k]['useravatar']);
                    $kanbanTitle = $smarty->fetch('utils/kanbanTitle.tpl');
                    
                    $smarty->assign('dateStart', date_format($taskStarDate, 'd-m-Y g:ia'));
                    $smarty->assign('dueDate', date_format($taskDueDate, 'd-m-Y g:ia'));
                    $smarty->assign('progress', intval($tasksViewData ['records'][$k]['progress']));
                    $kanbanFooter = $smarty->fetch('utils/kanbanFooter.tpl');
                    
                    $kanban = new stdClass ();
                    $kanban->id = $tasksViewData ['records'][$k]['crmid'];
                    $kanban->title = $kanbanTitle;
                    $kanban->block = $kanbanBlocks[$eventStatus];
                    $kanban->link = $tasksViewData ['records'][$k]['crmid'];
                    $kanban->link_text = $moduleName;
                    $kanban->footer = $kanbanFooter;
                    $kanbanData [] = $kanban;
                    $processedActivities [] = $tasksViewData ['records'][$k]['idactividad'];
                }
            }
            
            $result = $adb->query('SELECT * FROM vtiger_activitytype ORDER BY activitytype');
            if ($adb->num_rows($result) > 0) {
                $availableActivityTypes = array();
                while ($row = $adb->fetchByAssoc($result, -1, false)) {
                    $activityType = $row ['activitytype'];
					if ($activityType == 'Activity') {
						if ($moduleName == 'orden_de_trabajo') {
							$availableActivityTypes [$activityType] = $mod_strings [$activityType];
						}
					} else {
						$availableActivityTypes [$activityType] = $mod_strings [$activityType];
					}
                }
            } else {
                $availableActivityTypes = null;
            }
            if ($result instanceof ADORecordSet) {
                $result->Close();
                $result = null;
            }
            
            $preCreatedTask = new PrecreatedTaskUtils ();
            $availableModules = GridViewHelper::fetchAvailableModules($adb);
            if (empty($availableModules)) {
                throw new Exception ('No hay modulos disponibles');
            }
	        $objectDate = new DateTime();
	        $today      = $objectDate->format ('Y-m-d');
	        $objectDate = new DateTime();
	        $objectDate->modify ('+1 day');
	        $tomorrow   = $objectDate->format ('Y-m-d');
			
            $smarty->assign('APP', $app_strings);
            $smarty->assign('AREA_TASK', $preCreatedTask->fetchAreaActivity('ENABLED'));
            $smarty->assign('AVAILABLE_ACTIVITY_TYPES', $availableActivityTypes);
            $smarty->assign('AVAILABLE_EVENT_STATUSES', DataViewUtils::getAvailableEventStatuses($adb, $mod_strings));
            $smarty->assign('AVAILABLE_ESTIMATED_TIME_UNITS', getAvailableEstimatedTimeUnits());
            $smarty->assign('AVAILABLE_GROUPS', DataViewUtils::getAvailableGroups($adb));
            $smarty->assign('AVAILABLE_IMPORTANCE', DataViewUtils::getAvailableImportanceOfTasks());
            $smarty->assign('AVAILABLE_MODULES', $availableModules);
            $smarty->assign('AVAILABLE_SYSTEM_USERS', UserManager::getInstance($adb, null)->fetchUsers());
            $smarty->assign('AVAILABLE_TASKS_VIEWS', DataViewUtils::fetchAvailableViews($adb, 'Calendar', $current_user));
            $smarty->assign('AVAILABLE_TASK_PRIORITIES', DataViewUtils::getTaskPriorities($adb));
            $smarty->assign('AVAILABLE_USERS', DataViewUtils::getAvailableUserAndAvatar($adb, $current_user));
            $smarty->assign('CAN_CREATE_RECORDS', $canCreateRecords);
            $smarty->assign('CATEGORIES', $taskCategories);
            $smarty->assign('CURRENT_USER_ID', $current_user->id);
            $smarty->assign('CURRENT_USER_NAME', $current_user->first_name . ' ' . $current_user->last_name);
            $smarty->assign('FLMODULE', $moduleName);
            $smarty->assign('HAS_GANTT', (! empty ($ganttTab)) ? $ganttTab ['detail_view'] : 0);
            $smarty->assign('HAS_KANBAN', (! empty ($kambanTab)) ? $kambanTab ['detail_view'] : 1);
            $smarty->assign('HAS_RELATED', ((count(explode(';', $moduleName))) > 1) || empty ($moduleName));
            $smarty->assign('ID', $crmid);
            $smarty->assign('IS_ADMIN', is_admin($current_user));
            $smarty->assign('IS_INSTANCE', ! empty ($_SESSION ['platInstancia']));
            $smarty->assign('IS_MOTHER', empty ($_SESSION ['platInstancia']));
            $smarty->assign('KANBAN_BLOCKS', (count($kanbanData)) ? json_encode($kanbanData) : null);
            $smarty->assign('MOD', $mod_strings);
            $smarty->assign('RELATED_MODULE', $moduleName);
            $smarty->assign('RELATED_MODULES', DataViewUtils::getRelatedModule($adb));
            $smarty->assign('RETURN_ACTION', 'index');
            $smarty->assign('RETURN_MODULE', 'Home');
            $smarty->assign('ROOT_FOLDER_PATH', PlatzillaUtils::getPlatzillaRootFolderPath());
            $smarty->assign('TASKS_GANTT', (count($taskToGantt) ? json_encode($taskToGantt, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) : null));
            $smarty->assign('TASKS_VIEW', $tasksView);
            $smarty->assign('TASKS_VIEW_DATA', (count($activitiesRecords)) ? $activitiesRecords : null);
            $smarty->assign('TASKS_VIEW_PERMISSIONS', $tasksViewPermissions);
            $smarty->assign('TASK_LIST', $preCreatedTask->fetchPreCreatedTask('ENABLED'));
            $smarty->assign('THEME', $theme);
	        $smarty->assign ('TODAY', $today);
	        $smarty->assign ('TOMORROW', $tomorrow);
            
            // Formatear valores iniciales para el formulario de creación usando método estándar de Platzilla
            require_once('include/utils/NumberHelper.class.php');
            require_once('include/utils/CommonUtils.php');
            $numberingHelper = NumberHelper::getInstance($adb, $current_user);
            
            // Obtener tabid del módulo Calendar
            $calendarTabId = getTabid('Calendar');
            
            // Obtener información real de los campos usando método estándar de Platzilla
            $estimatedTimeFieldInfo = getFieldInformation($calendarTabId, 'estimated_time');
            $estimatedCostFieldInfo = getFieldInformation($calendarTabId, 'estimated_cost');
            
            // Usar uitypes reales de la base de datos
            // estimated_time es uitype=7 (campo numérico)
            // estimated_cost es uitype=71 (campo de moneda)
            $estimatedTimeUitype = $estimatedTimeFieldInfo ? $estimatedTimeFieldInfo['uitype'] : 7;
            $estimatedCostUitype = $estimatedCostFieldInfo ? $estimatedCostFieldInfo['uitype'] : 71;
            
            // Formatear valores según preferencias del usuario y uitypes
            $formattedTime = $numberingHelper->setNumberFormat('0.5', $estimatedTimeUitype);
            $formattedCost = $numberingHelper->setNumberFormat('0.00', $estimatedCostUitype);
            
            $smarty->assign('DEFAULT_ESTIMATED_TIME', $formattedTime);
            $smarty->assign('DEFAULT_ESTIMATED_COST', $formattedCost);
            $smarty->assign('DEFAULT_ESTIMATED_TIME_UNIT', 'Hora');
            
            // PASAR FORMATO NUMÉRICO AL JAVASCRIPT
            $userNumberFormat = $numberingHelper->getNumberFormat();
            $smarty->assign('USER_NUMBER_FORMAT', $userNumberFormat);
            
            $smarty->assign('TOTAL_NEW_TASKS', $tasksViewData ['totalNewTask']);
            $htmlOutput = $smarty->fetch('DetailViewTaskActivity.tpl');
            // Limpiar cualquier output buffer anterior y BOM
            if (ob_get_length()) {
                ob_clean();
            }
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            $jsonOutput = json_encode(array('error' => 'OK', 'html' => $htmlOutput));
            // Eliminar BOM si está presente
            $jsonOutput = preg_replace('/^\xEF\xBB\xBF/', '', $jsonOutput);
            echo $jsonOutput;
        } catch (Exception $e) {
            if (ob_get_length()) {
                ob_clean();
            }
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            $jsonOutput = json_encode(array('error' => $e->getMessage()));
            $jsonOutput = preg_replace('/^\xEF\xBB\xBF/', '', $jsonOutput);
            echo $jsonOutput;
        }
    } 
    else if ($function == 'VIEW-TASK-MODAL') {
        // Capturar cualquier output no deseado
        ob_start();
        
        // Log para depuración
        
        require_once('include/fields/DateTimeField.php');
        require_once('include/utils/NumberHelper.class.php');
        require_once('include/platzilla/Utils/DatabaseUtils.php');
        require_once('include/utils/CommonUtils.php');
        
        // Limpiar cualquier output capturado
        ob_clean();
        
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
            $modStrings = return_module_language($current_user->language, 'Calendar');
            $smarty = new vtigerCRM_Smarty();
            $smarty->assign('ERROR_MESSAGE', 'La tarea solicitada no existe');
            $smarty->assign('MOD', $modStrings);
            $smarty->assign('APP', $app_strings);
            $html = $smarty->fetch('modules/Calendar/TaskViewModal_Error.tpl');
            echo json_encode(array('success' => true, 'html' => $html, 'taskExists' => false));
            exit;
        }
        
        $taskData = $adb->fetchByAssoc($checkResult, -1, false);
        DatabaseUtils::closeResult($checkResult);
        
        if ($taskData['deleted'] == 1) {
            $modStrings = return_module_language($current_user->language, 'Calendar');
            $smarty = new vtigerCRM_Smarty();
            $smarty->assign('ERROR_MESSAGE', 'La tarea solicitada no existe');
            $smarty->assign('MOD', $modStrings);
            $smarty->assign('APP', $app_strings);
            $html = $smarty->fetch('modules/Calendar/TaskViewModal_Error.tpl');
            echo json_encode(array('success' => true, 'html' => $html, 'taskExists' => false));
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
        
        // Preparar datos para el template
        $numberingHelper = NumberHelper::getInstance($adb, $current_user);
        $modStrings = return_module_language($current_user->language, 'Calendar');
        
        // Formatear fechas
        $dateTimeField = new DateTimeField($taskData['date_start'] . ' ' . $taskData['time_start']);
        $taskData['formatted_start_date'] = $dateTimeField->getDisplayDate();
        $taskData['formatted_start_time'] = $dateTimeField->getDisplayTime();
        
        $startDateDisplay = $taskData['formatted_start_date'];
        $dueDateDisplay = '';
        if (!empty($taskData['due_date']) && $taskData['due_date'] !== '0000-00-00') {
            $dueDateField = new DateTimeField($taskData['due_date']);
            $taskData['formatted_due_date'] = $dueDateField->getDisplayDate();
            $dueDateDisplay = $taskData['formatted_due_date'];
        }
        
        // Formatear números
        $taskData['formatted_estimated_time'] = $numberingHelper->setNumberFormat($taskData['estimated_time'], 'estimated_time');
        $taskData['formatted_estimated_cost'] = $numberingHelper->setNumberFormat($taskData['estimated_cost'], 'estimated_cost');
        $taskData['formatted_progress'] = $numberingHelper->setNumberFormat($taskData['progress'], 'progress');
        
        $estimatedTime = $taskData['formatted_estimated_time'];
        $estimatedCost = $taskData['formatted_estimated_cost'];
        $progress = $taskData['formatted_progress'];
        
        // Obtener nombre del usuario asignado
        $assignedUserName = '';
        if (!empty($taskData['smownerid'])) {
            $userResult = $adb->pquery("SELECT CONCAT(first_name, ' ', last_name) as owner_name FROM vtiger_users WHERE id = ?", array($taskData['smownerid']));
            if ($adb->num_rows($userResult) > 0) {
                $taskData['owner_name'] = $adb->query_result($userResult, 0, 'owner_name');
                $assignedUserName = $taskData['owner_name'];
            }
            DatabaseUtils::closeResult($userResult);
        }
        
        // Obtener datos de informes de avance (activity reports)
        $actualData = array(
            'total_duration' => 0,
            'total_cost' => 0,
            'min_date' => '',
            'max_date' => '',
            'has_reports' => false
        );
        
        // Incluir reportes de la tarea actual Y de la tarea tipo 'Job' (reporte global de trabajo)
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
                $formattedRatio = $numberingHelper->setNumberFormat($indicators['duration_ratio'], null);
                $indicators['duration_ratio_display'] = $formattedRatio . '%';
                $indicators['duration_over_budget'] = $indicators['duration_ratio'] > 100;
            }
            
            // Calcular proporción de costo
            $estimatedCost = floatval($taskData['estimated_cost']);
            if ($estimatedCost > 0) {
                $indicators['cost_ratio'] = ($actualData['total_cost'] / $estimatedCost) * 100;
                $formattedCostRatio = $numberingHelper->setNumberFormat($indicators['cost_ratio'], null);
                $indicators['cost_ratio_display'] = $formattedCostRatio . '%';
                $indicators['cost_over_budget'] = $indicators['cost_ratio'] > 100;
            }
        }
        
        // Agregar datos calculados a taskData
        $taskData['actual_data'] = $actualData;
        $taskData['indicators'] = $indicators;
        
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
        
        // Preparar datos para el template
        $taskViewData = array(
            'activityid' => $taskData['activityid'],
            'subject' => $taskData['subject'],
            'description' => $taskData['description'],
            'activitytype' => isset($modStrings[$taskData['activitytype']]) ? $modStrings[$taskData['activitytype']] : $taskData['activitytype'],
            'activitytype_translated' => isset($taskData['activitytype_translated']) ? $taskData['activitytype_translated'] : '',
            'date_start' => $startDateDisplay,
            'due_date' => $dueDateDisplay,
            'formatted_start_date' => $taskData['formatted_start_date'],
            'formatted_start_time' => $taskData['formatted_start_time'],
            'formatted_due_date' => isset($taskData['formatted_due_date']) ? $taskData['formatted_due_date'] : '',
            'time_start' => $taskData['time_start'],
            'duration_hours' => $taskData['duration_hours'],
            'estimated_time' => $estimatedTime,
            'estimated_time_unit' => $taskData['estimated_time_unit'],
            'formatted_estimated_time' => $taskData['formatted_estimated_time'],
            'estimated_cost' => $estimatedCost,
            'formatted_estimated_cost' => $taskData['formatted_estimated_cost'],
            'progress' => $progress,
            'formatted_progress' => $taskData['formatted_progress'],
            'eventstatus' => $taskData['eventstatus'],
            'eventstatus_translated' => isset($taskData['eventstatus_translated']) ? $taskData['eventstatus_translated'] : '',
            'priority' => $taskData['priority'],
            'priority_translated' => isset($taskData['priority_translated']) ? $taskData['priority_translated'] : '',
            'importance' => isset($modStrings[$taskData['importance']]) ? $modStrings[$taskData['importance']] : $taskData['importance'],
            'importance_translated' => isset($taskData['importance_translated']) ? $taskData['importance_translated'] : '',
            'location' => $taskData['location'],
            'show_in_matrix' => $taskData['show_in_matrix'],
            'combined_condition' => $taskData['combined_condition'],
            'estimated_progress' => $taskData['estimated_progress'],
            'progress_ratio' => $taskData['progress_ratio'],
            'related_to' => $taskData['related_to'],
            'related_id' => $taskData['related_id'],
            'related_module' => $taskData['related_module'],
            'assigned_user' => $assignedUserName,
            'owner_name' => isset($taskData['owner_name']) ? $taskData['owner_name'] : $assignedUserName,
            'supplier_name' => $taskData['supplier_name'],
            'actual_data' => $actualData,
            'indicators' => $indicators
        );
        
        // Cargar reportes de actividad para esta tarea
        require_once('modules/grid_view/lib/GridViewHelper.class.php');
        require_once('include/platzilla/Data/ActivityFeedbackManager.php');
        require_once('include/utils/AttachmentsUtils.class.php');
        
        $activityReports = GridViewHelper::fetchActivityReport($adb, $taskData['related_id'], $current_user, $activityId);
        
        // Convertir objetos ActivityReport a arrays para Smarty
        $reportsForSmarty = array();
        if (is_array($activityReports) && count($activityReports) > 0) {
            foreach ($activityReports as $index => $report) {
                // Obtener información del usuario que creó el reporte
                $userName = '-';
                $reportUserId = null;
                if ($report->getUserId() !== null) {
                    $userSql = "SELECT CONCAT(first_name, ' ', last_name) as user_name FROM vtiger_users WHERE id = ?";
                    $userResult = $adb->pquery($userSql, array($report->getUserId()));
                    $userName = ($adb->num_rows($userResult) > 0) ? $adb->query_result($userResult, 0, 'user_name') : '-';
                    $reportUserId = $report->getUserId();
                }
                
                // Verificar permisos de edición: el usuario puede editar si es el creador del reporte o es administrador
                $canEdit = false;
                if ($current_user) {
                    if ($reportUserId !== null && $current_user->id == $reportUserId) {
                        $canEdit = true;
                    }
                    if (isset($current_user->is_admin) && $current_user->is_admin == 'on') {
                        $canEdit = true;
                    }
                }
                
                // Obtener lista de evidencias (adjuntos) del reporte
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
                
                // Convertir a array para Smarty
                $reportArray = array(
                    'id' => $report->getId(),
                    'activityreportid' => $report->getId(),
                    'activityid' => $report->getActivityId(),
                    'title' => $report->getTitle(),
                    'progress' => $report->getProgress(),
                    'report' => $report->getReport(),
                    'reportdate' => $report->getReportDate(),
                    'date' => $report->getReportDate(),
                    'user_name' => $userName,
                    'duration_time' => $report->getTimeDuration(),
                    'duration' => $report->getTimeDuration(),
                    'actual_cost' => $report->getActualCost() !== null ? $report->getActualCost() : 0,
                    'cost' => $report->getActualCost() !== null ? $report->getActualCost() : 0,
                    'userid' => $reportUserId,
                    'evidences' => $evidenceList,
                    'can_edit' => $canEdit
                );
                
                $reportsForSmarty[] = $reportArray;
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
                    if ($feedback->getUserId() !== null && $current_user->id == $feedback->getUserId()) {
                        $canEditFeedback = true;
                    }
                    if (isset($current_user->is_admin) && $current_user->is_admin == 'on') {
                        $canEditFeedback = true;
                    }
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
                    'can_edit' => $canEditFeedback
                );
            }
        }
        
        $smarty = new vtigerCRM_Smarty();
        $smarty->assign('TASK_DATA', $taskViewData);
        $smarty->assign('ACTIVITY_REPORTS', $reportsForSmarty);
        $smarty->assign('ACTIVITY_FEEDBACKS', $activityFeedbacks);
        $smarty->assign('MOD', $modStrings);
        $smarty->assign('APP', $app_strings);
        $html = $smarty->fetch('modules/Calendar/TaskViewModal.tpl');
        
        echo json_encode(array('success' => true, 'html' => $html, 'taskExists' => true));
        exit;
    }
    else if ($function == 'VIEW-TASK-RECORD') {
        try {
            $record = PlatzillaUtils::purify($_GET, 'record');
            $current_module = PlatzillaUtils::purify($_GET, 'module');
            if (empty ($record)) {
                throw new Exception ('Registro no encontrado');
            } else if (empty($moduleName)) {
                throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
            }
            
            $focus = CRMEntity::getInstance($moduleName);
            $focus->id = $record;
            $focus->mode = 'edit';
            $focus->retrieve_entity_info($record, $moduleName);
            
            if ($moduleName == 'Calendar') {
                $htmlTemplate = 'DetailViewTaskRecord.tpl';
            } else {
                $htmlTemplate = 'modules/proyectos/job_project/DetailViewJobRecord.tpl';
            }
            $smarty = new vtigerCRM_Smarty ();
            $smarty->assign('MOD', return_module_language($current_language, $moduleName));
            $smarty->assign('TASK', $focus->column_fields);
            $smarty->display($htmlTemplate);
        } catch (Exception $e) {
            $smarty = new vtigerCRM_Smarty ();
            $smarty->assign('MESSAGE', $e->getMessage());
            $smarty->assign('TYPE', 'ERROR');
            $smarty->assign('TASK', null);
            $smarty->display('DetailViewTaskRecord.tpl');
        }
    }
    exit();
