<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/SummaryReportManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/report_rails/lib/SummaryReportHelper.class.php');
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	
	global $adb, $app_strings, $current_module, $mod_strings;
	
	$function      = PlatzillaUtils::purify ($_REQUEST, 'function');
	$id = PlatzillaUtils::purify ($_REQUEST, 'record');
	
	if ($function == 'CHECK_SHARE_REPORT') {
		$masterReportId = PlatzillaUtils::purify ($_REQUEST, 'record');
		try {
			if(empty ($masterReportId)) {
				throw new Exception ('Reporte no identificado!');
			}
			$message = SummaryReportHelper::checkStatusReport ($masterReportId);
			
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $message));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'CREATE_MASTER_REPORT') {
		try {
			$idModal  = PlatzillaUtils::purify ($_REQUEST, 'id_modal');
			$action   = PlatzillaUtils::purify ($_REQUEST, 'report_action-' . $idModal);
			$agentId  = PlatzillaUtils::purify ($_REQUEST, 'report_agent');
			$instance = PlatzillaUtils::purify ($_REQUEST, 'report_instance');
			$report   = PlatzillaUtils::purify ($_REQUEST, 'master_report');
			$title    = PlatzillaUtils::purify ($_REQUEST, 'report_title');
			$week     = PlatzillaUtils::purify ($_REQUEST, 'report_week');
			if (empty ($week)) {
				throw new Exception (' ¡Semana no indentificada!');
			} else {
				$week = explode ('@', $week);
				if (count ($week) != 2) {
					throw new Exception ('¡Semana no indentificada!');
				}
			}
			if (empty ($agentId)) {
				throw new Exception ('¡Agente no indentificado!');
			} else {
				$agent = UsersHelper::getAgent ($adb, $agentId);
				if (empty ($agent)) {
					throw new Exception ('¡Agente no indentificado!');
				}
			}
			if (empty ($instance)) {
				throw new Exception (' ¡Instancia no identificada!');
			} else {
				$instance = explode (';', $instance);
				if (count ($instance) != 2) {
					throw new Exception ('¡Instancia no identificada!');
				}
			}
			if (($action == 'DUPLICATE_MASTER_REPORT') && (empty ($report))) {
					throw new Exception ('¡Reporte no indentificado!');
			} else {
				$reportPattern = SummaryReportHelper::getMasterReport ($report);
				if (empty ($reportPattern) && ($action == 'DUPLICATE_MASTER_REPORT')) {
					throw new Exception ('¡Reporte patron no encontrado!');
				}
			}
			
			if (empty ($title)) {
					$title = 'Reporte de ' . $agent->getName () . ' para la Instancia '.$instance[0] . 'Semana: ' . $week [0] . ' - ' . $week [1];
			}
			$masterReport = MasterWeeklyReport::getInstance ()
					->setAgentId ($agentId)
					->setCodeInstance ($instance [0])
					->setDateStart ($week [0])
					->setDescription ($title)
					->setDueDate ($week [1])
					->setId (null)
					->setMailInstance ($instance [1])
					->setStatus ('INACTIVE');
			$savedReport = SummaryReportManager::getInstance ($adb)->saveMasterReport ($masterReport);
			if ($action == 'DUPLICATE_MASTER_REPORT') {
				SummaryReportHelper::updateMasterReportStatus ($savedReport->getId (), $reportPattern->getReportOfStatus ());
				$performances = SummaryReportHelper::fetchPerformance ($reportPattern->getId ());
				foreach ($performances as $performance) {
						$performance->setPerformanceId (null);
						$performance->setReportId ($savedReport->getId ());
						SummaryReportHelper::savePerformance ($performance);
				}
				$agreements = SummaryReportHelper::fetchAgreements ($reportPattern->getId ());
				foreach ($agreements as $agreement) {
						$agreement->setAgreementId (null);
						$agreement->setReportId ($savedReport->getId ());
						$userIds   = (!empty($agreement->getUsersInvolved ())) ? array_column ($agreement->getUsersInvolved (), 'userid') : array();
						$agreement->setUsersInvolved ($userIds);
						SummaryReportHelper::saveAgreement ($agreement);
				}
			}
			
			$_SESSION ['flashmessage'] = array (
					'iserror' => false,
					'message' => ' ¡El reporte semanal, se ha creado con éxito! Continue ahora con los contenidos del informe',
			);
			header ('Location: index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings&master_report='.$savedReport->getId ());
			} catch (Exception $e) {
			$_SESSION ['flashmessage'] = array (
					'iserror' => true,
					'message' => $e->getMessage (),
			);
			header ("Location: index.php?module=report_rails&action=index&parenttab=Settings");
		}
		exit ();
	} else if ($function == 'DELETE_AGREEMENTS') {
		$agreementsId = PlatzillaUtils::purify ($_REQUEST, 'record');
		$masterReport  = PlatzillaUtils::purify ($_REQUEST, 'master_report');
		try {
			if(empty ($agreementsId)) {
				throw new Exception ('Acuerdo no identificado');
			}
			SummaryReportHelper::deleteReportByPart ('agreements', $agreementsId);
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' => ' ¡El acuerdo se ha eliminado con éxito!',
			);
			header ('Location: index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings&master_report='. $masterReport. '&tab=AGREEMENTS');
			} catch (Exception $e) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
			);
			header ("Location: index.php?module=report_rails&action=index&parenttab=Settings");
		}
		exit ();
	} else if ($function == 'DELETE_MASTER_REPORT') {
		$masterReportId  = PlatzillaUtils::purify ($_REQUEST, 'record');
		try {
			if(empty ($masterReportId)) {
				throw new Exception ('Reporte semanal no encontrado!');
			}
			SummaryReportHelper::deleteMasterReport ( $masterReportId);
			$_SESSION ['flashmessage'] = array (
					'iserror' => false,
					'message' => ' ¡El informe se ha eliminado con éxito!',
			);
			header ('Location: index.php?module=report_rails&action=index&parenttab=Settings');
		} catch (Exception $e) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
			);
			header ("Location: index.php?module=Settings&action=index&parenttab=Settings");
		}
		exit ();
	} else if ($function == 'DELETE_PERFORMANCE') {
		$performanceId = PlatzillaUtils::purify ($_REQUEST, 'record');
		$masterReport  = PlatzillaUtils::purify ($_REQUEST, 'master_report');
		try {
			if(empty ($performanceId)) {
				throw new Exception ('Index de desempeño no identificado');
			}
			SummaryReportHelper::deleteReportByPart ('performance', $performanceId);
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' => ' ¡El indice de rendimiento se ha eliminado con éxito!',
			);
			header ('Location: index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings&master_report='. $masterReport. '&tab=PERFORMANCE');
		} catch (Exception $e) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
			);
			header ("Location: index.php?module=report_rails&action=index&parenttab=Settings");
		}
		exit ();
	} else if ($function == 'GET_BOXSCORE_REPORT') {
		$masterReportId = PlatzillaUtils::purify ($_REQUEST, 'report_id');
		$randId         = PlatzillaUtils::purify ($_REQUEST, 'rand_id');
		$moduleName     = PlatzillaUtils::purify ($_REQUEST, 'module', $current_module);
		try {
			if (empty ($masterReportId)) {
				throw new Exception ('Reporte no identificado!');
			}
			$reportMaster = SummaryReportHelper::getMasterReport ($masterReportId);
			if (empty ($reportMaster)) {
				throw new Exception ('¡Reporte semanal no encontrado!');
			}
			$targetInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($reportMaster->getCodeInstance ());
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
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('PERIOD_TIME', null);
			$smarty->assign ('REPORT_ID', $masterReportId);
			$smarty->assign ('RAND_ID', $randId);
			$htmlOutput = $smarty->fetch ("modules/report_rails/BoxScoreReport.tpl");
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array ('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'GET_DATA_GRAPHICS') {
		$masterReportId = PlatzillaUtils::purify ($_REQUEST, 'report_id');
		$indicators     = PlatzillaUtils::purify ($_REQUEST, 'indicators');
		try {
			if (empty ($masterReportId)) {
				throw new Exception ('Reporte no identificado!');
			}
			if (empty ($indicators)) {
				throw new Exception ('Indicadores no identificados!');
			}
			$reportMaster = SummaryReportHelper::getMasterReport ($masterReportId);
			if (empty ($reportMaster)) {
				throw new Exception ('¡Reporte semanal no encontrado!');
			}
			$targetInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($reportMaster->getCodeInstance ());
			$descriptions = array ();
			$indicatorData = array ();
			foreach ($indicators as $indicator) {
				$dateFrom     = SummaryReportHelper::getIndicatorDateScale ($targetInstance, $indicator, $reportMaster->getDateStart ());
				$indicatorArr = SummaryReportHelper::fetchBoxScoreData ($targetInstance, $indicator, $dateFrom, $reportMaster->getDueDate ());
				if (!empty ($indicatorArr)) {
					$dataGraphics = array (
						array ('Fecha', 'Objetivo','Resultado'),
					);
					$descriptions [ $indicator ] = !empty($indicatorArr['description']) ? $indicatorArr['description'] : 'Sin descripción';
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
	} else if ($function == 'GET_INSTANCES') {
		$agentId = PlatzillaUtils::purify ($_REQUEST, 'agent');
		try {
			if (empty ($agentId)) {
				throw new Exception ('Agente no identificado');
			}
			
			$agent = UsersHelper::getAgent ($adb, $agentId);
			if (empty ($agent)) {
				throw new Exception ('Agente no encontrado');
			} else if (!count ($agent->getPlatformInstance ())) {
				throw new Exception ('Agente sin instancias asignadas');
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AGENT_INSTANCES', null);
			$smarty->assign ('INSTANCES', $agent->getPlatformInstance ());
			
			$htmlOutput = $smarty->fetch ("modules/report_rails/Objects/InstancesOption.tpl");
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
	} else if ($function == 'GET_PERIODS') {
		$data = PlatzillaUtils::purify ($_REQUEST, 'instance_data');
		try {
			if (empty ($data)) {
				throw new Exception ('Instancia no identifcada');
			}
			list ($codeInstance, $correo) =explode (';', $data);
			$targetInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($codeInstance);
			
			$firstDay    = WorkingDayUtils::getFirstDayWeek ($targetInstance);
			$offsetMonth = 3;
			$fromDate    = date ('Y-m-d', strtotime("{$firstDay} - 1 week"));
			$toDate      = date ('Y-m-d', strtotime ($fromDate . '+6 day'));
			$period      = "{$fromDate}@{$toDate}";
			
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
	} else if ($function == 'GET_UPCOMING_ACTIVITIES') {
 		$reportId = PlatzillaUtils::purify ($_REQUEST, 'report_id');
		$randId   = PlatzillaUtils::purify ($_REQUEST, 'rand_id');
		try {
			if (empty ($reportId)) {
				throw new Exception ('Reporte no identificado');
			}
			$isInstance   = !empty ($_SESSION ['platInstancia']);
			$dummy            = explode ('-', base64_decode ($reportId), 3);
			$upcomingActivity = SummaryReportHelper::fetchUpcomingReport ($adb, $reportId, $isInstance);
			$upcomingTab      = SummaryReportHelper::fetchUpcomingTab ($adb, $reportId, $isInstance);
			$moduleObjects    = array_column ($upcomingActivity['weeklyReports'], 'related_module', 'related_id');
			foreach ($moduleObjects as $relatedId => $relatedModule) {
				$totalWeeklyReport = count ($upcomingActivity['weeklyReports']);
				$indexWork         = null;
				$indexOther         = null;
				$totalAvance	   = 0;
				$isCalculated	   = false;
				for ($i = 0; $i < $totalWeeklyReport; $i++) {
					if (
						$relatedId != $upcomingActivity['weeklyReports'][$i]['related_id'] ||
						$relatedModule != $upcomingActivity['weeklyReports'][$i]['related_module']
					) {
						continue;
					}
					if (in_array ($relatedModule, array_keys (SummaryReportHelper::$SUMMARY_TAB))) {
						if (empty ($indexWork)) {
							$indexWork = $i;
							$upcomingActivity['weeklyReports'][$i]['execution_hours'] = floatval ($upcomingActivity['weeklyReports'][$i]['execution_hours']);
							$upcomingActivity['weeklyReports'][$i]['planned_hours']   = floatval ($upcomingActivity['weeklyReports'][$i]['planned_hours']);
						} else {
							$upcomingActivity['weeklyReports'][$indexWork]['execution_hours'] += floatval ($upcomingActivity['weeklyReports'][$i]['execution_hours']);
							$upcomingActivity['weeklyReports'][$indexWork]['planned_hours']   += floatval ($upcomingActivity['weeklyReports'][$i]['planned_hours']);
							unset ($upcomingActivity['weeklyReports'][$i]);
						}
						
					} else {
						if ($totalAvance == 0) {
							$isCalculated = true;
							$indexOther    = $i;
							$totalAvance = ((floatval ($upcomingActivity['weeklyReports'][$i]['advance_task']) / 100) * floatval ($upcomingActivity['weeklyReports'][$indexOther]['execution_hours']));
							$upcomingActivity['weeklyReports'][$indexOther]['execution_hours'] += floatval ($upcomingActivity['weeklyReports'][$i]['execution_hours']);
							$upcomingActivity['weeklyReports'][$indexOther]['planned_hours'] += floatval ($upcomingActivity['weeklyReports'][$i]['planned_hours']);
						} else {
							$upcomingActivity['weeklyReports'][$indexOther]['execution_hours'] += floatval ($upcomingActivity['weeklyReports'][$i]['execution_hours']);
							$upcomingActivity['weeklyReports'][$indexOther]['planned_hours']   += floatval ($upcomingActivity['weeklyReports'][$i]['planned_hours']);
							unset ($upcomingActivity['weeklyReports'][$i]);
						}
					}
				}
				if ($isCalculated) {
					$upcomingActivity['weeklyReports'][$indexOther]['advance_task'] = ($totalAvance / floatval ($upcomingActivity['weeklyReports'][$indexOther]['execution_hours'])) * 100;
					$upcomingActivity['weeklyReports'][$indexOther]['advance_task'] =  number_format ($upcomingActivity['weeklyReports'][$indexOther]['advance_task'], 2);
				}
				$upcomingActivity['weeklyReports'] = array_values ($upcomingActivity['weeklyReports']);
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ADB', $adb);
			$smarty->assign ('AFFAIRS', $upcomingTab ['affairs']);
			$smarty->assign ('AVAILABLE_REPORTS', SummaryReportHelper::availableWeeklyReport ($reportId, 'UPCOMING'));
			$smarty->assign ('CORRECTIVE_ACTIONS',$upcomingTab ['corrective_actions']);
			$smarty->assign ('INSTANCE_CODE', (!$isInstance && $dummy[0] != 'madre') ? $dummy[0] : null);
			$smarty->assign ('IS_INSTANCE', $isInstance);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('RAND_ID', $randId);
			$smarty->assign ('REPORT_ID', $reportId);
			$smarty->assign ('UPCOMING_ACTIVITIES',(!empty ($upcomingActivity)) ? $upcomingActivity['weeklyReports'] : null);
			$smarty->assign ('UPCOMING_TAB', $upcomingTab);
			$htmlOutput = $smarty->fetch ("modules/report_rails/UpcomingActivities.tpl");
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array ('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'GET_WEEKLY_REPORT') {
		$reportId = PlatzillaUtils::purify ($_REQUEST, 'report_id');
		$randId   = PlatzillaUtils::purify ($_REQUEST, 'rand_id');
		try {
			if (empty ($reportId)) {
				throw new Exception ('Reporte no identificado');
			}
			$isInstance    = !empty ($_SESSION ['platInstancia']);
			$dummy         = explode ('-', base64_decode ($reportId));
			$weeklyReport = SummaryReportHelper::fetchWeeklyReport ($adb, $reportId, $isInstance);
			if (isset ($weeklyReport['weeklyReports']) && count($weeklyReport['weeklyReports']) > 0) {
				$dataGraphics = array (
					array ('Objetos', '% Horas de ejecución', 'Horas planificadas', 'Tareas avanzadas'),
				);
				$moduleObjects = array_column ($weeklyReport['weeklyReports'], 'related_module', 'related_id');
				foreach ($moduleObjects as $relatedId => $relatedModule) {
					$totalWeeklyReport = count ($weeklyReport['weeklyReports']);
					$indexWork         = null;
					$indexOther         = null;
					$totalAvance	   = 0;
					$isCalculated	   = false;
					for ($i = 0; $i < $totalWeeklyReport; $i++) {
						if (
							$relatedId != $weeklyReport['weeklyReports'][$i]['related_id'] ||
							$relatedModule != $weeklyReport['weeklyReports'][$i]['related_module']
						) {
							continue;
						}
						if (in_array ($relatedModule, array_keys (SummaryReportHelper::$SUMMARY_TAB))) {
							if (empty ($indexWork)) {
								$indexWork = $i;
								$weeklyReport['weeklyReports'][$i]['execution_hours'] = floatval ($weeklyReport['weeklyReports'][$i]['execution_hours']);
								$weeklyReport['weeklyReports'][$i]['planned_hours']   = floatval ($weeklyReport['weeklyReports'][$i]['planned_hours']);
							} else {
								$weeklyReport['weeklyReports'][$indexWork]['execution_hours'] += floatval ($weeklyReport['weeklyReports'][$i]['execution_hours']);
								$weeklyReport['weeklyReports'][$indexWork]['planned_hours']   += floatval ($weeklyReport['weeklyReports'][$i]['planned_hours']);
								unset ($weeklyReport['weeklyReports'][$i]);
							}
							
						} else {
							if ($totalAvance == 0) {
								$isCalculated = true;
								$indexOther    = $i;
								$totalAvance = ((floatval ($weeklyReport['weeklyReports'][$i]['advance_task']) / 100) * floatval ($weeklyReport['weeklyReports'][$indexOther]['execution_hours']));
								$weeklyReport['weeklyReports'][$indexOther]['execution_hours'] += floatval ($weeklyReport['weeklyReports'][$i]['execution_hours']);
								$weeklyReport['weeklyReports'][$indexOther]['planned_hours'] += floatval ($weeklyReport['weeklyReports'][$i]['planned_hours']);
							} else {
								$weeklyReport['weeklyReports'][$indexOther]['execution_hours'] += floatval ($weeklyReport['weeklyReports'][$i]['execution_hours']);
								$weeklyReport['weeklyReports'][$indexOther]['planned_hours']   += floatval ($weeklyReport['weeklyReports'][$i]['planned_hours']);
								unset ($weeklyReport['weeklyReports'][$i]);
							}
						}
					}
					if ($isCalculated) {
						$weeklyReport['weeklyReports'][$indexOther]['advance_task'] = ($totalAvance / floatval ($weeklyReport['weeklyReports'][$indexOther]['execution_hours'])) * 100;
						$weeklyReport['weeklyReports'][$indexOther]['advance_task'] =  number_format ($weeklyReport['weeklyReports'][$indexOther]['advance_task'], 2);
					}
					$weeklyReport['weeklyReports'] = array_values ($weeklyReport['weeklyReports']);
				}
				
				foreach ($weeklyReport['weeklyReports'] as $report) {
					if (isset($report['subject']) && !empty($report['subject'])) {
						$objects [] = $report['subject'];
					} else {
						$objects [] = $report['task_subject'];
					}
					if ($report['planned_hours'] != 0) {
						$timePorc = (floatval ($report['execution_hours']) / floatval ($report['planned_hours'])) * 100;
					} else {
						$timePorc = floatval ($report['execution_hours']);
					}
					$objects [] = $timePorc;
					$objects []  = 100;
					$objects [] = floatval ($report ['advance_task']);
					$dataGraphics [] = $objects;
					unset($objects);
				}
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ACTION_PLAN', (!empty ($weeklyReport)) ? $weeklyReport['actionPlan'] : null);
			$smarty->assign ('ADB', $adb);
			$smarty->assign ('DATA_TABLE', (isset($dataGraphics)) ? json_encode ($dataGraphics) : null);
			$smarty->assign ('AVAILABLE_REPORTS', SummaryReportHelper::availableWeeklyReport ($reportId, 'ACTUAL'));
			$smarty->assign ('BUSINESS_INITIATIVES', (!empty ($weeklyReport)) ? $weeklyReport['businessInitiatives'] : null);
			$smarty->assign ('INSTANCE_CODE', (!$isInstance && $dummy[0] != 'madre') ? $dummy[0] : null);
			$smarty->assign ('IS_INSTANCE', $isInstance);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('PROJECT', (!empty ($weeklyReport)) ? $weeklyReport['project'] : null);
			$smarty->assign ('RAND_ID', $randId);
			$smarty->assign ('REPORT_ID', $reportId);
			$smarty->assign ('WEEKLY_REPORTS', (!empty ($weeklyReport)) ? $weeklyReport['weeklyReports'] : null);
			$htmlOutput = $smarty->fetch ("modules/report_rails/PlanningCompliance.tpl");
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array ('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'PUBLISHED_REPORT') {
		$reportId   = PlatzillaUtils::purify ($_REQUEST, 'report_id');
		$reportType = PlatzillaUtils::purify ($_REQUEST, 'report_type');
		try {
			if (empty ($reportId)) {
				throw new Exception ('Reporte no identificado');
			}
			if (empty ($reportType)) {
				throw new Exception ('Tipo de reporte no identificado');
			}
			
			$reportTypes = explode ('@', $reportType);
			SummaryReportHelper::publishReport ($reportId, $reportTypes);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array ('error' => 'OK'));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'SHARE_REPORT') {
		$masterReportId = PlatzillaUtils::purify ($_REQUEST, 'record');
		try {
			if(empty ($masterReportId)) {
				throw new Exception ('Reporte no identificado!');
			}
			SummaryReportHelper::shareReport ($masterReportId);
			
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
	} else if ($function == 'UPDATE_AGREEMENTS') {
		$agreementsId = PlatzillaUtils::purify ($_REQUEST, 'record');
		$status        = PlatzillaUtils::purify ($_REQUEST, 'row_status');
		$masterReport  = PlatzillaUtils::purify ($_REQUEST, 'master_report');
		try {
			if(empty ($agreementsId)) {
				throw new Exception ('acuerdo no identificado');
			} else if (empty ($status)) {
				throw new Exception ('Estado de desempeño no identificado');
			}
				
			$status = ($status == 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
			SummaryReportHelper::updateStatusReportByPart ('agreement', $status, $agreementsId);
					
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' => ' ¡El acuerdo se ha actualizado con éxito!',
			);
					header ('Location: index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings&master_report=' . $masterReport. '&tab=AGREEMENTS');
		} catch (Exception $e) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
			);
			header ("Location: index.php?module=report_rails&action=index&parenttab=Settings");
		}
		exit ();
	} else if ($function == 'UPDATE_MASTER_REPORT') {
		$masterReportId = PlatzillaUtils::purify ($_REQUEST, 'record');
		$status        = PlatzillaUtils::purify ($_REQUEST, 'row_status');
		try {
			if(empty ($masterReportId)) {
				throw new Exception ('Informe semanal no identificado');
			} else if (empty ($status)) {
				throw new Exception ('Estado de informe no identificado');
			}
				
			$status = ($status == 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
			SummaryReportHelper::updateStatusReportByPart ('master', $status, $masterReportId);
					
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' => ' ¡El informe semanal se ha actualizado con éxito!',
			);
			header ('Location: index.php?module=report_rails&action=index&parenttab=Settings');
		} catch (Exception $e) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
			);
			header ('index.php?module=Settings&action=index&parenttab=Settings');
		}
		exit ();
	} else if ($function == 'UPDATE_PERFORMANCE') {
		$performanceId = PlatzillaUtils::purify ($_REQUEST, 'record');
		$status        = PlatzillaUtils::purify ($_REQUEST, 'row_status');
		$masterReport  = PlatzillaUtils::purify ($_REQUEST, 'master_report');
		try {
			if(empty ($performanceId)) {
				throw new Exception ('Index de desempeño no identificado');
			} else if (empty ($status)) {
					throw new Exception ('Estado de desempeño no identificado');
			}
		
			$status = ($status == 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
			SummaryReportHelper::updateStatusReportByPart ('performance', $status, $performanceId);
				
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' => ' ¡El indice de rendimiento se ha actualizado con éxito!',
			);
			header ('Location: index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings&master_report=' . $masterReport. '&tab=PERFORMANCE');
		} catch (Exception $e) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
			);
			header ("Location: index.php?module=report_rails&action=index&parenttab=Settings");
		}
		exit ();
	}