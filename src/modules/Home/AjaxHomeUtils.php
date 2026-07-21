<?php
    require_once('include/platzilla/Data/ApplicationsManager.php');
    require_once('include/platzilla/Data/BoxScoreManager.php');
    require_once('include/platzilla/Data/GraphicManager.php');
	require_once('include/platzilla/Managers/FieldManager.php');
    require_once('include/platzilla/Managers/PlatformSubscriptionManager.php');
    require_once('include/platzilla/Managers/UserManager.php');
    require_once('include/platzilla/Utils/JSGraphicUtils.php');
    require_once('include/utils/AdbManager.class.php');
    require_once('include/utils/DataViewUtils.php');
	require_once('include/utils/ProcessCasesUtils.class.php');
    require_once('include/utils/PlatzillaUtils.class.php');
    require_once('modules/Calendar/Activity.php');
    require_once('modules/Courses/lib/CoursesHelper.php');
    require_once('modules/daily_report/lib/DailyReportUtils.class.php');
    require_once('modules/Home/lib/HomeUtils.class.php');
    require_once('modules/News/lib/NewsUtils.php');
    require_once('modules/notifications/lib/NotificationPeriodUtils.class.php');
    require_once('modules/notifications/lib/NotificationUtils.class.php');
    require_once('modules/notification_center/lib/NotificationHelper.class.php');
	require_once('modules/operating_modes/lib/DirectionModeHelper.class.php');
    require_once('modules/operating_modes/lib/OperatingModesHelper.class.php');
    require_once('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once('modules/process_cases/handlers/SummaryProcessCase.class.php');
    require_once('modules/Settings/lib/HowToHelper.class.php');
    require_once('modules/store/lib/StoreUtils.class.php');
    require_once('modules/webmail/lib/WebmailUtils.class.php');
    require_once('Smarty_setup.php');
    
    global $adb, $app_strings, $current_user, $current_module, $current_language, $mod_strings, $site_URL, $theme;
    
    setBugSnag($site_URL);
    
    $function = PlatzillaUtils::purify($_REQUEST, 'function');
    $moduleName = PlatzillaUtils::purify($_REQUEST, 'flmodule');
    $homeTabId = PlatzillaUtils::purify($_POST, 'hometabid', null);
    $isInstance = ! empty ($_SESSION ['platInstancia']);
    
    if ($function == 'CHECK-DAILY-REPORT') {
        $date = PlatzillaUtils::purify($_POST, 'date');
        try {
            if (empty ($date)) {
                throw new Exception ('La fecha es requerida!');
            }
            $reportData = DailyReportUtils::checkDailyReportByDate($adb, $date, $current_user->id);
            if (empty($reportData)) {
                $outputArray = array('status' => 'Create', 'form' => 'create', 'crmid' => '');
            } else {
                $outputArray = array(
                    'status' => $reportData ['status'],
                    'crmid' => $reportData ['crmid'],
                    'form' => 'edit',
                );
            }
            
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK', 'html' => $outputArray));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    } else if ($function == 'DAILY-MATRIX-SEARCH') {
        $periodTask = PlatzillaUtils::purify($_POST, 'periodtask');
        $startDate = PlatzillaUtils::purify($_POST, 'datestart', null);
        $dueDate = PlatzillaUtils::purify($_POST, 'duedate', null);
        $inviteesId = PlatzillaUtils::purify($_POST, 'inviteesid');
        try {
            $tasksView = DataViewUtils::fetchView ($adb, 'Calendar', 'ALL');
            if (empty ($tasksView)) {
                throw new Exception ('La vista solicitada no se encuentra registrada');
            } else if (empty ($periodTask)) {
                throw new Exception ('Periodo de fechas no identificado!');
            } else if (empty ($inviteesId)) {
                throw new Exception ('usuario(s) no identificado(s)!');
            }
            
            if ($periodTask == 'custom') {
                $periodDates ['startdate'] = $startDate;
                $periodDates ['enddate'] = $dueDate;
            } else {
                $periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate($periodTask);
                $dateIni = $periodDates ['startdate'];
                $dateEnd = $periodDates ['enddate'];
            }
            $users = explode(',', $inviteesId);
            $conditionalWhere = " ((DATE(vtiger_activity.date_start) BETWEEN '{$dateIni}' AND '{$dateEnd}') AND (vtiger_crmentity.smcreatorid IN ({$inviteesId})) AND (vtiger_activity.eventstatus != 'Held') AND (vtiger_activity.show_in_matrix = 'YES'))";
            
            $tasksViewPermissions = DataViewUtils::fetchViewPermissions($adb, $tasksView, $current_user);
            if ((! is_array($tasksViewPermissions)) || (! in_array(DataViewUtils::PERMISSION_CAN_USE, $tasksViewPermissions))) {
                throw new Exception ('Acceso denegado');
            }
            $tasksData = DataViewUtils::fetchTaskToMatrix($adb, $periodDates, $users);
            $activitiesRecords = array();
            $priorityTranslate = array('Alto' => 'High', 'Bajo' => 'Low');
            $totalRecords = count($tasksData);
            for ($k = 0; $k < $totalRecords; $k++) {
                $tasksViewData ['records'][$k]['invitee'] = DataViewUtils::fetchInviteesByActivity($adb, $tasksData[$k]->getActivityId(), $current_user->id);
                $tasksViewData ['records'][$k]['str_date_start'] = $tasksData[$k]->getStartDate();
                $tasksViewData ['records'][$k]['str_due_date'] = $tasksData[$k]->getDueDate();
                
                $thisPriority = $tasksData[$k]->getPriority();
                $tasksViewData ['records'][$k]['taskpriority'] = ((! empty ($thisPriority)) && (in_array($thisPriority, array('Alto', 'Bajo')))) ? $thisPriority : 'Bajo';
                $tasksViewData ['records'][$k]['importance'] = $tasksData[$k]->getImportance();
                $tasksViewData ['records'][$k]['progress'] = $tasksData[$k]->getProgress();
                $tasksViewData ['records'][$k]['related_id'] = $tasksData[$k]->getRelatedId();
                $tasksViewData ['records'][$k]['modulename'] = $tasksData[$k]->getModuleName();
                $tasksViewData ['records'][$k]['tab_name'] = $tasksData[$k]->getRelatedModule();
                $tasksViewData ['records'][$k]['estimated_time'] = $tasksData[$k]->getTimeDuration();
                $tasksViewData ['records'][$k]['subject'] = $tasksData[$k]->getSubject();
                $tasksViewData ['records'][$k]['planned_task'] = $tasksData[$k]->getActivityCondition();
                $tasksViewData ['records'][$k]['description'] = $tasksData[$k]->getDescription();
                
                $quadrant = $priorityTranslate[$tasksViewData ['records'][$k]['taskpriority']] . '-' . $tasksData[$k]->getImportance();
                $parameters = "{$tasksViewData ['records'][ $k ]['activitytype']};{$priorityTranslate[$tasksViewData ['records'][ $k ]['taskpriority']]};{$tasksViewData ['records'][ $k ]['importance']};{$tasksData[ $k ]->getActivityId ()}";
                $tasksViewData ['records'][$k]['parameters'] = $parameters;
                $activitiesRecords[$quadrant][] = $tasksViewData ['records'][$k];
            }
            $quadrants = array('High-HIGH', 'Low-HIGH', 'High-LOW', 'Low-LOW');
            $totalsQuadrants = array();
            $totalsEstimated = array();
            $totalTime = 0;
            foreach ($quadrants as $key => $quadrant) {
                $totalsQuadrants[] = count($activitiesRecords[$quadrant]);
                $totalTask = 0;
                foreach ($activitiesRecords[$quadrant] as $taskItem) {
                    if (empty ($taskItem ['estimated_time'])) {
                        continue;
                    }
                    $totalTime += floatval($taskItem ['estimated_time']);
                    $totalTask++;
                }
                $totalsEstimated [] = $totalTask;
            }
            $totalsQuadrants [] = array_sum($totalsQuadrants);
            $totalsEstimated [] = array_sum($totalsEstimated);
            $totalHoursWorked = DailyReportUtils::getTotalHoursWorked($adb, $periodDates, $users);
            $reportTime = DailyReportUtils::getActivityReportTotalTime($adb, $periodDates, $users);
            $extraHours = ($reportTime - $totalHoursWorked);
            $isOverTime = false;
            $barMax = 100;
            $barWidth = 1;
            if (! empty($reportTime) && ! empty($totalTime)) {
                $barWidth = floor((($reportTime * 100) / $totalTime));
                if (($barWidth > 100)) {
                    $barMax = $barWidth;
                    $barWidth = 100;
                    $overTime = ($barMax - 100);
                    $isOverTime = true;
                }
            }
            
            $smarty = new vtigerCRM_Smarty ();
            $smarty->assign('ADITIONAL_INFO', DailyReportUtils::fetchAdditionalInformation($adb, $periodDates, $users));
            $smarty->assign('ACHIEVEMENTS', DailyReportUtils::fetchAchievements($adb, $periodDates, $users));
            $smarty->assign('QUADRANTS', $quadrants);
            $smarty->assign('MOD', $mod_strings);
            $smarty->assign('TAB_HOME_ID', $homeTabId);
            $smarty->assign('TASKS_VIEW_DATA', $activitiesRecords);
            $smarty->assign('TOTALS_QUADRANTS', $totalsQuadrants);
            $smarty->assign('TOTALS_ESTIMATED', $totalsEstimated);
            $smarty->assign('TOTAL_TIMES', $totalTime);
            $smarty->assign('OVER_TIME', (isset ($overTime)) ? $overTime : 0);
            $smarty->assign('PROGRESS_BAR_MAX', $barMax);
            $smarty->assign('PROGRESS_BAR_WIDTH', $barWidth);
            $smarty->assign('PROGRESS_BAR_OVER', $isOverTime);
            $smarty->assign('REPORTED_HOURS', $reportTime);
            $smarty->assign('WORKED_HOURS', $totalHoursWorked);
            $smarty->assign('EXTRA_HOURS', $extraHours);
            $htmlOutput = $smarty->fetch('Home/TabsContents/SearchDailyMatriz.tpl');
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    } else if ($function == 'FETCH-ACTIVITY-WIZARD') {
        $recordId = PlatzillaUtils::purify($_POST, 'record');
        try {
            $tasksView = DataViewUtils::fetchView($adb, 'Calendar', 'ALL');
            if (empty ($recordId)) {
                throw new Exception ('Actividad no registrada!');
            }
            
            $upgradeable = array('subject', 'description', 'location', 'eventstatus', 'assigned_user_id', 'date_start', 'time_start', 'due_date', 'time_end', 'categoryid', 'taskpriority');
            $focus = CRMEntity::getInstance('Calendar');
            $focus->id = $recordId;
            $focus->mode = 'edit';
            
            $priorityTranslate = array('Alto' => 'High', 'Bajo' => 'Low');
            $focus->retrieve_entity_info($recordId, 'Calendar');
            $outputArray = array();
            foreach ($focus->column_fields as $key => $value) {
                if (in_array($key, $upgradeable)) {
                    if ($key == 'date_start') {
                        $outputArray ['startdate'] = $value;
                    } else if ($key == 'time_start') {
                        $outputArray ['starttime'] = $value;
                    } else if ($key == 'due_date') {
                        $outputArray ['enddate'] = $value;
                    } else if ($key == 'time_end') {
                        $outputArray ['endtime'] = $value;
                    } else if ($key == 'taskpriority') {
                        $outputArray ['taskpriority'] = $priorityTranslate [$value];
                    } else {
                        $outputArray [$key] = $value;
                    }
                }
            }
            $invitees = DataViewUtils::fetchInviteesByActivity($adb, $recordId, $focus->column_fields['assigned_user_id']);
            $outputArray['invitees'] = $invitees ['userId'];
            
            
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK', 'html' => $outputArray));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    } else if ($function == 'FETCH-WEEKLY-REPORT') {
		try {
			$periodTime   = PlatzillaUtils::purify($_POST, 'selectedWeek');
			$instanceCode = PlatzillaUtils::purify ($_REQUEST, 'report_instance', null);
			
			$isInstance = !empty ($_SESSION ['platInstancia']);
			if ($isInstance && empty ($instanceCode)) {
				$dummy        = explode ('_', $adb->dbName);
				$instanceCode = $dummy [2];
			}
			$summaryReport     = DirectionModeHelper:: getWeeklyStatusReport ($adb, $current_user, $periodTime, $isInstance, $instanceCode);
			$performanceReport = DirectionModeHelper::getWeeklyContext ($adb, $current_user, $periodTime, $isInstance, $instanceCode);
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'summary' => $summaryReport, 'performance' => $performanceReport));
		 
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		        
		}
    } else if ($function == 'GET-HOW-TO') {
        $smarty = new vtigerCRM_Smarty ();
        try {
            $record = PlatzillaUtils::purify ($_REQUEST, 'record');
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
    } else if ($function == 'GET-PROCESS-CASE') {
		try {
			$caseNumber = PlatzillaUtils::purify ($_POST, 'case_number');
			$stepName   = PlatzillaUtils::purify ($_POST, 'step_name');
			if (empty ($caseNumber) || empty ($stepName)) {
				throw new Exception ('Imposible encontrar el caso');
			}
			$processCaseId = ProcessCasesUtils::getCaseIdByStepName ($adb, $caseNumber, $stepName);
			if (empty ($processCaseId)) {
				throw new Exception ('Caso no encontrado');
			}
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $processCaseId));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
    } else if ($function == 'GET-PROCESS-STEPS') {
	    try {
		    $caseId        = PlatzillaUtils::purify ($_POST, 'case');
		    $dueDate       = PlatzillaUtils::purify ($_POST, 'toDate', null);
			$homeTabId	   = PlatzillaUtils::purify ($_POST, 'hometabid');
			$periodProcess = PlatzillaUtils::purify ($_POST, 'period');
		    $processId      = PlatzillaUtils::purify ($_POST, 'processId');
			$startDate     = PlatzillaUtils::purify ($_POST, 'fromDate', null);
			if ($periodProcess == 'custom') {
				$periodDates ['startdate'] = $startDate;
				$periodDates ['enddate']   = $dueDate;
			} else {
				$periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodProcess);
			}
			$processSteps = SummaryProcessCase::getInstance ($adb)->run ($caseId, 'HomeView', $current_user);
			$caseDetails  = $processSteps['caseDetails'];
			$totalCases   = count ($caseDetails);
		    $moduleName   = null;
			for ($i = 0; $i < $totalCases; $i++) {
				$stepId   = $caseDetails [$i]['process_step'];
				$execTime = floatval ($caseDetails [$i]['step_exec_time']);
				$caseDetails [$i]['step_state'] = ProcessCasesUtils::getControlBandsBySteps ($adb, $stepId, $periodDates, $execTime);
				if (empty($moduleName)) {
					$moduleName = $caseDetails [$i]['step']['step_type_module'];
				}
			}
			$caseNumber = $processSteps['parameters']['case_number'];
			if (!empty ($caseNumber) && !empty ($moduleName)) {
				$result = $adb->pquery (
					'SELECT crmid FROM vtiger_crmentity WHERE setype=? AND deleted=? AND case_number=?',
					array($moduleName, 0, $caseNumber)
				);
				$crmId = $adb->query_result ($result, 0, "crmid");
				$entity = CRMEntity::getInstance ($moduleName);
				$entity->id = $crmId;
				$processDisplay = ProcessCasesUtils::fetchCaseByCode ($adb, $caseNumber, $entity, $current_user, true);
			}
		    $finishedCaseDetail   = ProcessCasesUtils::fetchCasesInvolvedByProcess ($adb, $periodDates, 1, $processId);
		    $unfinishedCaseDetail = ProcessCasesUtils::fetchCasesInvolvedByProcess ($adb, $periodDates, 0, $processId);
			$allCases             = array_merge ($finishedCaseDetail, $unfinishedCaseDetail);
		    $graphic              = ProcessCasesUtils::fetchGraphicDataSteps ($adb, $processId, $periodDates);
			
		    $smarty = new vtigerCRM_Smarty ();
		    $smarty->assign ('ADB', $adb);
		    $smarty->assign ('ALL_CASES', $allCases);
		    $smarty->assign ('CASE_DETAILS', $caseDetails);
		    $smarty->assign ('CASE_ID', $caseId);
		    $smarty->assign ('CASE_NUMBER', $caseNumber);
		    $smarty->assign ('CONTROL_BANDS', ProcessCasesUtils::getControlBands ());
		    $smarty->assign ('HOME_TAB_ID', $homeTabId);
		    $smarty->assign ('PROCESS_CASE', isset($processDisplay) ? $processDisplay : null);
			$smarty->assign ('SERIES_LABEL', !empty($graphic) ? $graphic['series'] : null);
		    $smarty->assign ('STEPS_GRAPH', !empty($graphic) ? json_encode ($graphic['data']) : null);
		    $smarty->assign ('STEPS_GRAPH2', !empty($graphic) ? $graphic['data'] : null);
		    $smarty->assign ('STEPS_TYPE', SummaryProcessCase::STEPS_TYPE);
			$htmlOutput = $smarty->fetch('Home/TabsContents/TableProcessSteps.tpl');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
		    echo json_encode (array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage()));
		}
    } else if ($function == 'GET_BOXSCORE_REPORT') {
        $codeInstance = PlatzillaUtils::purify ($_REQUEST, 'report_id');
        $randId       = PlatzillaUtils::purify ($_REQUEST, 'rand_id');
	    $moduleName   = PlatzillaUtils::purify ($_REQUEST, 'module', $current_module);
	    $PeriodTime   = PlatzillaUtils::purify ($_REQUEST, 'period');
	    try {
		    if (empty ($codeInstance)) {
		        throw new Exception ('Instancia no identificada!');
		    }
		    
		    $targetInstance     = AdbManager::getInstance ()->getTargetInstanceAdb ($codeInstance);
		    $excludedCategories = array ('Marco','Infraestructura','Actividades','Revision','Control','Mejoras');
		    $categories         = IndicatorsPanelHelper::getCategories ($excludedCategories);
		    $categories ['KR']  = 'KR';
		    foreach ($categories as $key => $category) {
			    $indicators = SummaryReportHelper::fetchBoxScoreRails ($targetInstance, $key);
			    $categoryCatalg [ $key ] = array (
			    'app_code'          => "{$key}-{$randId}",
			    'indicators'        => $indicators['objectives'],
			    'indicators_script' => (!empty($indicators['indicators'])) ? json_encode ($indicators['indicators']) : '',
			    'app_name'          => $category,
		    );
		        unset($indicators);
		    }
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('CATEGORIES', $categoryCatalg);
			$smarty->assign ('FLMODULE', $moduleName);
			$smarty->assign ('MOD', return_module_language($current_language, 'report_rails'));
			$smarty->assign ('PERIOD_TIME', $PeriodTime);
			$smarty->assign ('REPORT_ID', $codeInstance);
			$smarty->assign ('RAND_ID', $randId);
			$htmlOutput = $smarty->fetch ("modules/report_rails/BoxScoreReport.tpl");
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array ('error' => 'OK', 'html' => $htmlOutput));
	    } catch (Exception $e) {
		    header ('Access-Control-Allow-Origin: *');
		    header ('HTTP/1.1 200 OK');
		    header ('Content-Type: application/json; charset=utf-8');
		    echo json_encode (array('error' => $e->getMessage ()));
	    }
    } else if ($function == 'GET_DATA_GRAPHICS') {
	    $codeInstance = PlatzillaUtils::purify ($_REQUEST, 'report_id');
	    $indicators   = PlatzillaUtils::purify ($_REQUEST, 'indicators');
	    $periodTime   = PlatzillaUtils::purify ($_REQUEST, 'period');
	    try {
		    if (empty ($codeInstance)) {
		        throw new Exception ('Instancia no identificada');
		    }
		    if (empty ($indicators)) {
		        throw new Exception ('Indicadores no identificados!');
		    }
		    if (empty ($periodTime)) {
		        throw new Exception ('Periodo no identificado!');
		    }
		    list ($fromDate, $toDate) = explode ('@', $periodTime);
		    $targetInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($codeInstance);
		    $descriptions = array ();
		    $indicatorData = array ();
		    foreach ($indicators as $indicator) {
			    $dateFrom     = SummaryReportHelper::getIndicatorDateScale ($targetInstance, $indicator, $fromDate);
			    $indicatorArr = SummaryReportHelper::fetchBoxScoreData ($targetInstance, $indicator, $dateFrom, $toDate);
			    if (!empty ($indicatorArr)) {
				    $dataGraphics = array (
				        array ('Fecha', 'Objetivo','Resultado'),
				    );
				    $descriptions [ $indicator ] = !empty($indicatorArr['box_score']) ? $indicatorArr['box_score'] : '¡No se encontró el nombre del indicador!';
				    if (count($indicatorArr['objectives']) && count($indicatorArr['data_weekly'])) {
					    $totalData = count($indicatorArr['objectives']);
					    for ($k = 0; $k < $totalData; $k++) {
						    if (isset ($indicatorArr['data_weekly'][$k])) {
							    $objects [] = $indicatorArr['data_weekly'][$k]['date'];
							    $objects [] = floatval ($indicatorArr['objectives'][$k]['objective']);
						    } else {
							    $objects [] = 'N/A';
							    $objects [] = 0;
						    }
						    if (isset($indicatorArr['data_weekly'][$k])) {
						        $objects [] = floatval ($indicatorArr['data_weekly'][$k]['value']);
						    } else {
						        $objects [] = 0;
						    }
						    $dataGraphics [] = $objects;
						    unset ($objects);
					    }
				    } else {
				        $dataGraphics [] = array ('N/A', 0, 0);
				    }
				    $indicatorData [ $indicator ] = $dataGraphics;
				    unset ($dataGraphics);
			    }
		    }
		    header ('Access-Control-Allow-Origin: *');
		    header ('HTTP/1.1 200 OK');
		    header ('Content-Type: application/json; charset=utf-8');
		    echo json_encode (array ('error' => 'OK', 'html' => $indicatorData, 'name' => $descriptions));
	    } catch (Exception $e) {
		    header('Access-Control-Allow-Origin: *');
		    header('HTTP/1.1 200 OK');
		    header('Content-Type: application/json; charset=utf-8');
		    echo json_encode(array('error' => $e->getMessage()));
	    }
    } else if ($function == 'GET_PERIODS') {
		$data = PlatzillaUtils::purify ($_REQUEST, 'instance_data');
		try {
			if (empty ($data)) {
				throw new Exception ('Instancia no identifcada');
			}
			list ($codeInstance, $correo) = explode (';', $data);
			
			$availableReports = HomeUtils::fetchAvailableWeeklyReport ($adb, $codeInstance, $isInstance, '');
			if (!empty($availableReports)) {
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode (array('error' => 'OK', 'html' => $availableReports));
				exit();
			}
			
			$targetInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($codeInstance);
			$firstDay       = WorkingDayUtils::getFirstDayWeek ($targetInstance);
			$offsetMonth    = 3;
			$fromDate       = date ('Y-m-d', strtotime("{$firstDay} - 1 week"));
			$toDate         = date ('Y-m-d', strtotime ($fromDate . '+6 day'));
			$period         = "{$fromDate}@{$toDate}";
   
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('FIRST_DAY', $firstDay);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('OFFSET_MONTH', $offsetMonth);
			$smarty->assign ('SELECTED_WEEK', $period);
			$htmlOutput = $smarty->fetch ("modules/report_rails/Objects/PeriodsOptions.tpl");
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
    			$_SESSION ['flashmessage'] = array (
    				'iserror' => true,
    				'message' => $e->getMessage (),
    			);
    			header ("Location: index.php?module=report_rails&action=index&parenttab=Settings");
		}
    } else if ($function == 'PROCESS-DETAIL-VIEW') {
	    try {
			$dueDate        = PlatzillaUtils::purify ($_POST, 'toDate', null);
			$homeTabId	    = PlatzillaUtils::purify ($_POST, 'hometabid');
		    $periodProcess  = PlatzillaUtils::purify ($_POST, 'period');
		    $processId      = PlatzillaUtils::purify ($_POST, 'process');
			$startDate      = PlatzillaUtils::purify ($_POST, 'fromDate', null);
			if ($periodProcess == 'custom') {
				$periodDates ['startdate'] = $startDate;
				$periodDates ['enddate']   = $dueDate;
			} else {
				$periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodProcess);
			}
			$processData          = ProcessCasesUtils::getProcessById ($adb, $processId);
		    $finishedCase         = ProcessCasesUtils::getTotalCaseByFinishState ($adb, $periodDates, 1, $processId);
		    $unfinishedCase       = ProcessCasesUtils::getTotalCaseByFinishState ($adb, $periodDates, 0, $processId);
		    $finishedCaseDetail   = ProcessCasesUtils::fetchCasesInvolvedByProcess ($adb, $periodDates, 1, $processId);
			$finishedCaseGraph    = ProcessCasesUtils::getGraphicDataFromCases ($adb, $processId, $periodDates, $finishedCaseDetail);
		    $unfinishedCaseDetail = ProcessCasesUtils::fetchCasesInvolvedByProcess ($adb, $periodDates, 0, $processId);
		    $unfinishedCaseGraph  = ProcessCasesUtils::getGraphicDataFromCases ($adb, $processId, $periodDates, $unfinishedCaseDetail);
		    
		    $smarty = new vtigerCRM_Smarty ();
		    $smarty->assign ('CONTROL_BANDS', ProcessCasesUtils::getControlBands ());
		    $smarty->assign ('FINISHED_CASE_DETAIL', $finishedCaseDetail);
		    $smarty->assign ('FINISHED_CASE_GRAPH', json_encode ($finishedCaseGraph));
		    $smarty->assign ('HOME_TAB_ID', $homeTabId);
		    $smarty->assign ('MOD', $mod_strings);
		    $smarty->assign ('PROCESS_DATA', $processData);
		    $smarty->assign ('SUMMAY_FINISHED_CASE', $finishedCase);
		    $smarty->assign ('SUMMAY_UNFINISHED_CASE', $unfinishedCase);
		    $smarty->assign ('UNFINISHED_CASE_DETAIL', $unfinishedCaseDetail);
		    $smarty->assign ('UNFINISHED_CASE_GRAPH', json_encode ($unfinishedCaseGraph));
		    $htmlOutput = $smarty->fetch('Home/TabsContents/ProcessDetailView.tpl');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
		    echo json_encode (array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage()));
		}
    } else if ($function == 'PROCESS-PANEL-SEARCH') {
	    try {
		    $dueDate       = PlatzillaUtils::purify ($_POST, 'duedate', null);
			$homeTabId	   = PlatzillaUtils::purify ($_POST, 'hometabid');
		    $periodProcess = PlatzillaUtils::purify ($_POST, 'periodtask');
		    $startDate     = PlatzillaUtils::purify ($_POST, 'datestart', null);
		    if ($periodProcess == 'custom') {
			    $periodDates ['startdate'] = $startDate;
			    $periodDates ['enddate']   = $dueDate;
			} else {
				$periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodProcess);
			}
		    $processInPeriod           = ProcessCasesUtils::fetchDistinctProcess ($adb, $periodDates);
		    /** Finished  Process */
		    $behaviorOfProcessFinished = ProcessCasesUtils::fetchBehaviorOfProcess ($adb, $periodDates, 1);
			$totalCaseFinished         = ProcessCasesUtils::getProcessOutOfAverage ($behaviorOfProcessFinished, 'ALL');
			$finishedOutOfAverage      = ProcessCasesUtils::getProcessOutOfAverage ($behaviorOfProcessFinished);
			/** Unfinished Process */
		    $behaviorOfProcessUnfinished = ProcessCasesUtils::fetchBehaviorOfProcess ($adb, $periodDates, 0);
			$totalCaseUnfinished         = ProcessCasesUtils::getProcessOutOfAverage ($behaviorOfProcessUnfinished, 'ALL');
			$unfinishedOutOfAverage      = ProcessCasesUtils::getProcessOutOfAverage ($behaviorOfProcessUnfinished);
			$resumenProcesses            = count ($processInPeriod);
			$totalCase                   = $totalCaseFinished + $totalCaseUnfinished;
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('CONTROL_BANDS', ProcessCasesUtils::getControlBands ());
			$smarty->assign ('FINISHED_OUT_AVERAGE', $finishedOutOfAverage);
		    $smarty->assign ('HOME_TAB_ID', $homeTabId);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('PROCESS_FINISHED', $behaviorOfProcessFinished);
			$smarty->assign ('PROCESS_UMFINISHED', $behaviorOfProcessUnfinished);
			$smarty->assign ('RESUMEN_PROCESSES', $resumenProcesses);
			$smarty->assign ('TOTAL_CASE', $totalCase);
			$smarty->assign ('TOTAL_CASE_FINISHED', $totalCaseFinished);
			$smarty->assign ('TOTAL_CASE_UNFINISHED', $totalCaseUnfinished);
			$smarty->assign ('UNFINISHED_OUT_AVERAGE', $unfinishedOutOfAverage);
		    $htmlOutput = $smarty->fetch('Home/TabsContents/Objects/PanelContent.tpl');
		    header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
		    header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage()));
		}
    } else if ($function == 'PROCESS-QUALITY-SEARCH') {
	    try {
			$dueDate       = PlatzillaUtils::purify ($_POST, 'duedate', null);
			$homeTabId	   = PlatzillaUtils::purify ($_POST, 'hometabid');
			$periodProcess = PlatzillaUtils::purify ($_POST, 'periodtask');
			$processId	   = PlatzillaUtils::purify ($_POST, 'quality_process');
			$startDate     = PlatzillaUtils::purify ($_POST, 'datestart', null);
			$users		   = PlatzillaUtils::purify ($_POST, 'users');
			if (empty($users)) {
				$userList = array ($current_user->id);
			} else {
				$userList = array_filter (explode (',', $users));
			}
			
			if ($periodProcess == 'custom') {
				$periodDates ['startdate'] = $startDate;
				$periodDates ['enddate']   = $dueDate;
			} else {
				$periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodProcess);
			}
			
			$processInPeriod = ProcessCasesUtils::fetchDistinctProcess ($adb, $periodDates);
			/** Quality & time  Process */
		    $userList            = array_filter (explode (',', $users));
		    $exeAccordingQuality = ProcessHelper::fetchExeAccordingQuality ($adb, $processId, $periodDates, $userList);
		    $caseNumbers         = array_column ($exeAccordingQuality, 'case_number');
		    $caseIds			 = array_column ($exeAccordingQuality, 'process_casesid');
		    $stepName            = array_column ($exeAccordingQuality, 'step_name');
		    $caseNumbers         = array_combine ($caseNumbers, $caseIds);
		    $caseNumbers         = array_unique ($caseNumbers);
		    $stepName            = array_unique ($stepName);;
		    
	  
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ADB', $adb);
			$smarty->assign ('AVAILABLE_USERS', DataViewUtils::getAvailableUserAndAvatar ($adb, $current_user));
			$smarty->assign ('CASE_NUMBERS', $caseNumbers);
			$smarty->assign ('CONTROL_BANDS', ProcessCasesUtils::getControlBands ());
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('PROCESS_ACCORDING_QUALITY', $exeAccordingQuality);
			$smarty->assign ('STEPS_COLOR_QUALITY', ProcessStepInterface::SCORING_MATRIX);
			$smarty->assign ('STEPS_NAME', $stepName);
		    $smarty->assign ('TOTAL_STEPS_NAME', count ($stepName));
			$smarty->assign ('USER_IDS', $current_user->id);
			$userFirstName = isset($current_user->first_name) ? $current_user->first_name : (method_exists($current_user, 'getFirstName') ? $current_user->getFirstName() : null);
			$userLastName  = isset($current_user->last_name) ? $current_user->last_name : (method_exists($current_user, 'getLastName') ? $current_user->getLastName() : null);
			$userFullName  = trim(trim((string)$userFirstName) . ' ' . trim((string)$userLastName));
			$smarty->assign ('USER_NAME', !empty($userFullName) ? $userFullName : (isset($current_user->user_name) ? $current_user->user_name : ''));
			$htmlOutput = $smarty->fetch('Home/TabsContents/Objects/QualityContent.tpl');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage()));
		}
    } else if ($function == 'SELECTED-PROCESS-CASES') {
		try {
			$smarty = new vtigerCRM_Smarty ();
			$htmlOutput = $smarty->fetch('Home/TabsContents/Objects/PanelContent.tpl');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage()));
		}
    } else if ($function == 'SHOW_AGREEMENT') {
	    try {
		    $codeInstance = PlatzillaUtils::purify($_REQUEST, 'code');
		    $record       = PlatzillaUtils::purify($_REQUEST, 'record_id');
		    $smarty = new vtigerCRM_Smarty ();
			if (empty($codeInstance) || empty($record) || empty($moduleName)) {
				throw new Exception('No se ha especificado la instancia o el registro');
			}
			if (!$isInstance) {
				$masterAdb = $adb;
				$adb       = AdbManager::getInstance ()->getTargetInstanceAdb ($codeInstance);
			}
			$entity = CRMEntity::getInstance($moduleName);
		    $entity->retrieve_entity_info($record, $moduleName);
			$fields     = FieldManager::getInstance ($adb)->fetchFields ($moduleName);
		    $fieldLabel = array();
			foreach ($fields as $field) {
				$fieldLabel [$field->getName ()] = $field->getLabel ();
			}
			$adb = $masterAdb;
			$smarty->assign('DATA_FIELD', $entity->column_fields);
		    $smarty->assign('DATA_FIELD_LABELS', $fieldLabel);
			$smarty->assign ('EXCLUDE_FIELDS', array('assigned_user_id','modifiedtime', 'createdtime', 'record_id','record_module'));
		    $smarty->display('Home/WeeklyReport/AgreementExecDetailView.tpl');
        } catch (Exception $e) {
		    $smarty->assign ('MESSAGE', $e->getMessage ());
		    $smarty->assign('DATA_FIELD', null);
		    $smarty->display ('Home/WeeklyReport/AgreementExecDetailView.tpl');
	    }
    } else if ($function == 'VIEW-TASK') {
        try {
            if (empty ($moduleName)) {
                throw new Exception ('Módulo no encontrado');
            }
            $tasksView = ViewManager::getInstance($adb)->fetchView('Calendar', 'PENDING TASK');
            if (empty($tasksView)) {
                $tasksView = DataViewUtils::fetchDefaultView($adb, 'Calendar');
            }
            if (empty ($tasksView)) {
                throw new Exception ('La vista solicitada no se encuentra registrada');
            }
            $masterAdb = AdbManager::getInstance()->getMasterAdb();
            $isInstance = ! empty ($_SESSION ['platInstancia']);
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
            $queryGenerator = new QueryGenerator ('Calendar', $current_user);
            $queryGenerator->initForCustomViewById($tasksView->getId());
            $queryGenerator->getQuery();
            $conditionalWhere = $queryGenerator->getConditionalWhere();
            $tasksViewPermissions = DataViewUtils::fetchViewPermissions($adb, $tasksView, $current_user);
            if ((! is_array($tasksViewPermissions)) || (! in_array(DataViewUtils::PERMISSION_CAN_USE, $tasksViewPermissions))) {
                throw new Exception ('Acceso denegado');
            }
            $availableModules = ModuleManager::getInstance($adb)->fetchModulesByType(Module::TYPE_USER, true, $isInstance);
            $tasksViewData = DataViewUtils::fetchViewData($adb, $tasksView, $current_user, 1, null, $conditionalWhere, null, $moduleName);
            $availableTaskView = DataViewUtils::fetchAvailableViews($adb, 'Calendar', $current_user);
            $quickView = array();
            
            $smarty = new vtigerCRM_Smarty ();
            $smarty->assign('APP', $app_strings);
            $smarty->assign('CAN_CREATE_RECORDS', $canCreateRecords);
            $smarty->assign('AVAILABLE_TASKS_VIEWS', DataViewUtils::fetchAvailableViews($adb, 'Calendar', $current_user));
            $smarty->assign('AVAILABLE_SYSTEM_USERS', UserManager::getInstance($adb, null)->fetchUsers());
            $smarty->assign('AVAILABLE_MODULES', $availableModules);
            $smarty->assign('AVAILABLE_USERS', DataViewUtils::getAvailableUser($adb, $current_user));
            $smarty->assign('AVAILABLE_GROUPS', DataViewUtils::getAvailableGroups($adb));
            $smarty->assign('QUICK_VIEW', (count($quickView)) ? $quickView : null);
            $smarty->assign('RELATED_MODULE', $moduleName);
            $smarty->assign('HAS_RELATED', ((count(explode(';', $moduleName))) > 1) || empty ($moduleName));
            $smarty->assign('RETURN_ACTION', 'index');
            $smarty->assign('RETURN_MODULE', 'Home');
            $smarty->assign('RELATED_MODULES', DataViewUtils::getRelatedModule($adb));
            $smarty->assign('FLMODULE', $moduleName);
            $smarty->assign('TAB_HOME_ID', $homeTabId);
            $smarty->assign('TASKS_VIEW', $tasksView);
            $smarty->assign('TASKS_VIEW_DATA', $tasksViewData);
            $smarty->assign('TASKS_VIEW_PERMISSIONS', $tasksViewPermissions);
            $smarty->assign('TOTAL_NEW_TASKS', $tasksViewData ['totalNewTask']);
            $smarty->assign('APP', $app_strings);
            $smarty->assign('CAN_CREATE_RECORDS', $canCreateRecords);
            $smarty->assign('DEFAULT_OPERATING', $current_user->defaultOperating);
            $smarty->assign('IS_ADMIN', is_admin($current_user));
            $smarty->assign('IS_INSTANCE', ! empty ($_SESSION ['platInstancia']));
            $smarty->assign('ROOT_FOLDER_PATH', PlatzillaUtils::getPlatzillaRootFolderPath());
            $smarty->assign('IS_MOTHER', empty ($_SESSION ['platInstancia']));
            $smarty->assign('SELECTED_TAB', $selectedTab);
            $smarty->assign('TAB_GROUP', 'record');
            $smarty->assign('THEME', $theme);
            $smarty->assign('OPERATING_MODES', $operatingMode);
            
            $htmlOutput = $smarty->fetch('Home/TabsContents/Tasks.tpl');
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
        } catch (Exception $e) {
            header('Access-Control-Allow-Origin: *');
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('error' => $e->getMessage()));
        }
    }
    exit();
