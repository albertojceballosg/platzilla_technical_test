<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/EntityUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/process/handlers/ProcessSteps.class.php');
	require_once ('modules/Settings/lib/TableFieldHelper.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	
	
	abstract class ProcessCasesUtils {
		
		const CONTROL_BANDS            = array ('IN_CONTROL' => '#02c102', 'AT_RISK' => '#ffff00', 'OUT_CONTROL' => '#ff0000');
		const DAY_NAMES                = array (
											'Sunday'    => 'Domingo',
											'Monday'    => 'Lunes',
											'Tuesday'   => 'Martes',
											'Wednesday' => 'Miércoles',
											'Thursday'  => 'Jueves',
											'Friday'     => 'Viernes',
											'Saturday'   => 'Sábado',
										);
		const HOURS_DAY          	   = 8;
		const LINE_WIDTH               = 420;
		const PROCESS_CASE_MODULE_NAME = 'process_cases';
		const PROCESS_MODULE_NAME      = 'process';
		const PROCESS_STEP_MODULE_NAME = 'process_steps';
		const PROCESS_TABLE_FIELD_NAME = 'process_steps_table';
		const QUALITY_VALUATION 	   = array ('Bueno', 'Regular', 'Malo');
		const STEP_STATUS              = array ('Aprobado', 'En construcción', 'Eliminado');
		
		
		
		/**
		 * @param PearDatabase $adb
		 * @param CRMEntity $entityCase
		 * @param string $module
		 * @param CRMEntity $entity
		 * @param array $processData
		 *
		 * @return void
		 * @throws Exception
		 */
		private static function buildEntity ($adb, &$entityCase, $module, $entity, $processData) {
			$today       = date ('Y-m-d');
			$thisMoment  = date ('H:i:s');
			$starDate    = $today . ' ' . $thisMoment;
			$moduleLabel = getTabIdLabelByName ($module);
			$stepData    = ProcessSteps::getInstance ($adb)->getStepProcess ($processData ['process_id'], $module, $processData ['step_id']);
			
			$entityCase->column_fields ['assigned_user_id'] = $entity->column_fields['assigned_user_id'];
			$entityCase->column_fields ['case_title']       = $moduleLabel;
			$entityCase->column_fields ['createdtime']      = $starDate;
			$entityCase->column_fields ['modifiedtime']     = $starDate;
			$entityCase->column_fields ['comment']          = '';
			$entityCase->column_fields ['module_name']      = $module;
			$entityCase->column_fields ['process']          = $processData ['process_id'];
			$entityCase->column_fields ['process_step']     = $stepData ['step_codeid'];
			$entityCase->column_fields ['error_rate']       = $stepData ['error_rate'];
			$entityCase->column_fields ['estimated_time']   = $stepData ['estimated_time'];
			$entityCase->column_fields ['star_step_time']   = $thisMoment;
			$entityCase->column_fields ['start_date']       = $today;
			$entityCase->column_fields ['start_step_date']  = $today;
			$entityCase->column_fields ['start_time']       = $thisMoment;
			$entityCase->column_fields ['step_name']        = $stepData ['step_name'];
			$entityCase->column_fields ['type_step']        = $stepData ['type_step'];
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $crmId
		 * @param string $moduleName
		 * @param string $tableName
		 *
		 * @return boolean
		 * @throws Exception
		 */
		private static function checkStepModule ($adb, $crmId, $moduleName, $setStepId = null) {
			if (empty ($moduleName) || empty ($crmId)) {
				return false;
			}
			$stepId = '';
			if (!empty ($setStepId)) {
				$stepId = " AND step_id !={$setStepId}";
			}
			$result = $adb->pquery (
				"SELECT
       					related_module
					FROM
				  		vtiger_process_at_steps
					WHERE
				  		processtfid =? AND
				  		related_module !=?
				  		{$stepId}",
					array ($crmId, '')
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result)) {
					if ($row ['related_module'] == $moduleName) {
						return true;
					}
				}
			}
			return false;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $periodDates
		 * @return void
		 */
		private static function createStateOfProcess ($adb, $periodDates) {
			$temporaryTable = self::getStatesTableName ();
			$allProcesses   = self::fetchDistinctProcess ($adb, null);
			if (empty ($allProcesses)) {
				return;
			}
			
			$adb->query (
				"CREATE TEMPORARY TABLE IF NOT EXISTS {$temporaryTable} (
						`processid` INT(19) NOT NULL,
						`total_process` DECIMAL(12,2) NULL DEFAULT '0',
						`sum_process` DECIMAL(12,2) NULL DEFAULT '0',
						`avg_process` DECIMAL(12,2) NULL DEFAULT '0',
						`stddev_process` DECIMAL(12,2) NULL DEFAULT '0',
						KEY `processid` (`processid`)
				) ENGINE=InnoDB AUTO_INCREMENT=1  DEFAULT CHARSET=utf8"
			);
			foreach ($allProcesses as $processId => $title) {
				$result = $adb->pquery (
					'SELECT
						COUNT(pc.`process`) AS total_process,
						SUM(pc.`step_exec_time`) AS sum_process,
						AVG(pc.`step_exec_time`) AS avg_process,
						STDDEV(pc.`step_exec_time`) AS stddev_process
					FROM
						`vtiger_process_cases` pc
					INNER JOIN vtiger_crmentity crm ON 	crm.crmid = pc.`process_casesid` AND crm.deleted = 0
					WHERE
						pc.process =? AND pc.`due_date` <= ?',
					array ($processId, $periodDates['enddate'])
				);
				if ($adb->num_rows ($result) > 0) {
					$process = $adb->fetchByAssoc ($result, -1, false);
					$adb->pquery (
						"INSERT INTO {$temporaryTable} (processid, total_process, sum_process, avg_process, stddev_process) VALUES (?, ?, ?, ?, ?)",
						array ($processId, $process['total_process'], $process['sum_process'], $process['avg_process'], $process['stddev_process'])
					);
				}
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $periodDates
		 *
		 * @return mixed|null
		 */
		private static function fetchCompleteCases ($adb, $periodDates) {
			$result = $adb->pquery (
				'SELECT DISTINCT(process_casesid)
				FROM
				    vtiger_process_cases pc
				INNER JOIN vtiger_crmentity crm ON
				    crm.crmid = pc.process_casesid AND crm.deleted = 0
				WHERE
					pc.case_number IS NOT NULL AND pc.case_number <> "" AND
				EXISTS(SELECT IF(COUNT(c.process) = (SELECT COUNT(*) FROM vtiger_process_at_steps WHERE processtfid = pc.process),1,NULL) FROM vtiger_process_cases c INNER JOIN vtiger_crmentity cr ON     cr.crmid = c.process_casesid AND cr.deleted = 0
				WHERE c.case_number = pc.case_number)  AND
				pc.finish_process= 1  AND
				pc.due_date <= ?',
				array ($periodDates['enddate'])
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$completeCases [] = $row['process_casesid'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($completeCases)) ? $completeCases : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		private static function fetchOpenCases ($adb, $moduleName) {
			if (empty ($moduleName)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT DISTINCT
				pc.case_number
				FROM
				    vtiger_process_cases pc
				INNER JOIN vtiger_crmentity ce ON ce.crmid = pc.process_casesid AND ce.deleted = 0
				WHERE
					pc.case_number IS NOT NULL AND
				    NOT EXISTS (
					    SELECT
					        *
					    FROM
					        vtiger_process_ft44508 pft
					    WHERE
					        pft.related_module=? AND
					        pft.step_codeid = pc.`process_step` AND
					        pft.processtfid = pc.process
					        
					)',
				array ($moduleName)
			);
			if ($adb->num_rows ($result) > 0) {
				$casesNumber = array ();
				while ($row = $adb->fetchByAssoc ($result)) {
					$casesNumber[] = $row;
				}
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($casesNumber)) ? $casesNumber : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $caseNumber
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		private static function getCaseDataByCode ($adb, $caseNumber, $moduleName, $crmId) {
			$processId = null;
			$result    = $adb->pquery (
				'SELECT
					pc.process,
       				pr.process_title,
       				ce.case_number AS crm_id
				FROM
					vtiger_process_cases pc
				INNER JOIN vtiger_crmentity ce ON ce.crmid = pc.process_casesid AND ce.deleted=?
				INNER JOIN vtiger_process pr ON pr.processid = pc.process
				WHERE
					pc.case_number=?
				LIMIT 1',
				array (0, $caseNumber)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result);
				$processId    = $row['process'];
				$processTitle = $row['process_title'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$steps  = ProcessSteps::getInstance ($adb)->getStepsByProcess ($processId, $caseNumber, $moduleName);
			if (!empty ($steps) && count ($steps) > 0) {
				$totalSteps                = count ($steps);
				$steps[0]['process_title'] = $processTitle;
				for ($i = 0; $i < $totalSteps; $i++) {
					$result = $adb->pquery (
						'SELECT
								pc.process_casesid,
								pc.case_title,
       							pc.start_step_date,
       							pc.start_time,
								pc.due_step_date,
       							pc.end_time,
       							pc.step_exec_time,
       							pc.case_number,
       							(SELECT crmid FROM  vtiger_crmentity crm WHERE crm.setype = pc.module_name AND crm.case_number = pc.case_number LIMIT 1) AS crm_id
							FROM
								vtiger_process_cases pc
							INNER JOIN vtiger_crmentity ce ON ce.crmid = pc.process_casesid AND ce.deleted=?
							WHERE
								pc.case_number=? AND
								pc.process_step=?',
						array (0, $caseNumber, $steps[$i]['step_id'])
					);
					if ($adb->num_rows ($result) > 0) {
						$row = $adb->fetchByAssoc ($result);
						$steps[$i]['case']= $row;
					} else {
						$steps[$i]['case']= null;
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
				}
			}
			return (isset ($steps)) ? $steps : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param Users $user
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		private static function getCaseInProcess ($adb, $user, $moduleName, $ignoreStepsDone = false) {
			if (empty($user) || !($user instanceof Users)) {
				return null;
			}
			
			if (is_admin ($user)) {
				$whereUser = '';
				$parameters = array (0, self::PROCESS_CASE_MODULE_NAME);
			} else {
				$userId    = $user->id;
				$whereUser  = 'AND (ce.smownerid= ? OR ce.smcreatorid= ?)';
				$parameters = array (0, self::PROCESS_CASE_MODULE_NAME, $userId, $userId);
			}
			$result = $adb->pquery (
				"SELECT
			       	p.process_casesid,
			       	p.case_title,
       				p.process,
       				p.process_step,
       				p.case_number
				FROM vtiger_process_cases p
				INNER JOIN vtiger_crmentity ce ON ce.crmid = p.process_casesid
				WHERE
					ce.deleted= ? AND
					ce.setype= ?
					{$whereUser}",
				$parameters
			);
			if ($adb->num_rows ($result) > 0) {
				$process = array ();
				while ($row = $adb->fetchByAssoc ($result)) {
					$stepId = (!$ignoreStepsDone) ? $row['process_step'] : null;
					$hasStep = self::checkStepModule ($adb, $row ['process'], $moduleName, $stepId);
					if ($hasStep) {
						$process [] = $row;
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($process)) ? $process : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $caseId
		 *
		 * @return mixed|null
		 */
		private static function getCaseNumber ($adb, $caseId) {
			$result = $adb->pquery (
				"SELECT
			       	case_number
				FROM vtiger_process_cases
				WHERE
					process_casesid= ?",
				array ($caseId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result);
				$caseNumber =  $row ['case_number'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($caseNumber)) ? $caseNumber : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $tabId
		 *
		 * @return mixed|null
		 */
		private static function getCodeFieldName ($adb, $tabId) {
			if (empty($tabId)) {
				return null;
			}
			$result = $adb->pquery ('SELECT fieldname FROM vtiger_field WHERE tabid=? AND uitype=? LIMIT 1', array ($tabId, 4));
			if ($adb->num_rows ($result) > 0) {
				$data = $adb->fetchByAssoc ($result, -1, false);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($data)) ? $data['fieldname'] : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer$numDay
		 * @param string $dayName
		 *
		 * @return integer
		 * @throws Exception
		 */
		private static function getHoursWorkedByDay ($adb, $numDay, $dayName) {
			if (empty ($numDay) || empty ($dayName)) {
				return 0;
			}
			$result = $adb->pquery ('SELECT working_hours FROM vtiger_working_days WHERE weekday LIKE ? LIMIT 1', array ($dayName));
			if ($adb->num_rows ($result) > 0) {
				$data = $adb->fetchByAssoc ($result, -1, false);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($data)) {
				return intval ($data['working_hours']);
			} else if ($numDay < 7) {
				return self::HOURS_DAY;
			}
			return 0;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param Users $user
		 * @param string $moduleName
		 *
		 * @retun array|null
		 * @throws exception
		 */
		private static function getProcess ($adb, $user, $moduleName) {
			if (empty($user) || !($user instanceof Users)) {
				return null;
			}
			
			$step_status = $adb->sql_expr_datalist (self::STEP_STATUS);
			
			if (is_admin ($user)) {
				$whereUser = '';
				$parameters = array (0, self::PROCESS_MODULE_NAME);
			} else {
				require ("{$_SESSION ['plat']}/user_privileges/user_privileges_{$user->id}.php");
				if (count ($current_user_groups)) {
					array_push ($current_user_groups, intval ($user->id));
					
					$dummy      = $adb->sql_expr_datalist ($current_user_groups);
					$whereUser  = "AND ce.smownerid IN{$dummy}";
					$parameters = array (0, self::PROCESS_MODULE_NAME);
				} else {
					$userId    = $user->id;
					$whereUser  = 'AND (ce.smownerid= ? OR ce.smcreatorid= ?)';
					$parameters = array (0, self::PROCESS_MODULE_NAME, $userId, $userId);
				}
			}
			
			$result = $adb->pquery (
				"SELECT
       					processid,
       					process_title
					FROM vtiger_process p
					INNER JOIN vtiger_crmentity ce ON ce.crmid = p.processid
					WHERE
				    	ce.deleted= ? AND
				    	ce.setype= ?
						{$whereUser}
						AND EXISTS (
							SELECT
							  step_id
							FROM vtiger_process_at_steps pt
							WHERE
							  processtfid=p.processid AND
					  		  step_state NOT IN {$step_status} AND
					  		  EXISTS (SELECT * FROM vtiger_step_type WHERE step_typeid=pt.step_id)
						)",
				$parameters
			);
			
			if ($adb->num_rows ($result) > 0) {
				$process = array ();
				while ($row = $adb->fetchByAssoc ($result)) {
					$hasStep = self::checkStepModule ($adb, $row ['processid'], $moduleName);
					if ($hasStep) {
						$process [] = $row;
					}
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($process) && count ($process)) ? $process : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $processId
		 * @param array $periodDates
		 *
		 * @return array|null
		 */
		private static function getStateOfProcess ($adb, $processId, $periodDates) {
			$compledCases = self::fetchCompleteCases ($adb, $periodDates);
			if (empty ($compledCases)) {
				return null;
			}
			
			$whereFullCase = "pc.process_casesid IN {$adb->sql_expr_datalist($compledCases)} AND";
			$result = $adb->pquery (
				"SELECT
					ROUND(COUNT(pc.process),4) AS total_process,
					ROUND(SUM(pc.step_exec_time),4) AS sum_process,
					ROUND(AVG(pc.step_exec_time),4) AS avg_process,
					ROUND(STDDEV(pc.step_exec_time),4) AS stddev_process
				FROM
					vtiger_process_cases pc
				INNER JOIN vtiger_crmentity crm ON 	crm.crmid = pc.process_casesid AND crm.deleted = 0
				WHERE
				      pc.case_number IS NOT NULL AND pc.case_number <> '' AND
				    {$whereFullCase}
					pc.process=?  AND pc.finish_process= ? AND pc.`due_date` <= ?",
				array ($processId, 1, $periodDates['enddate'])
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$process = array (
					'total_process'  => floatval ($row ['total_process']),
					'sum_process'    => floatval ($row ['sum_process']),
					'avg_process'    => floatval ($row ['avg_process']),
					'stddev_process' => floatval ($row ['stddev_process']),
				 );
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($process)) ? $process : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $stepId
		 * @param array $periodDates
		 *
		 * @return array|null
		 */
		private static function getStateOfSteps ($adb, $stepId, $periodDates) {
			$result = $adb->pquery (
				"SELECT
					COUNT(pc.process_step) AS total_process,
					SUM(pc.step_exec_time) AS sum_process,
					AVG(pc.step_exec_time) AS avg_process,
					STDDEV(pc.step_exec_time) AS stddev_process
				FROM
					vtiger_process_cases pc
				INNER JOIN vtiger_crmentity crm ON 	crm.crmid = pc.process_casesid AND crm.deleted = 0
				WHERE
					pc.process_step=? AND
					pc.due_date IS NOT NULL AND pc.due_date <> '' AND
					pc.due_date <= ?",
				array ($stepId, $periodDates['enddate'])
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$steps = array (
					'total_process'  => floatval ($row ['total_process']),
					'sum_process'    => floatval ($row ['sum_process']),
					'avg_process'    => floatval ($row ['avg_process']),
					'stddev_process' => floatval ($row ['stddev_process']),
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($steps)) ? $steps : null;
		}
		
		/**
		 * @param string $module
		 * @param array $processStepsTable
		 * @param integer|null $lastStep
		 *
		 * @return array|null
		 */
		private static function getStepProcess ($adb, $module, $processStepsTable, $lastStep) {
			if (empty ($module) ||  !is_array ($processStepsTable) || empty ($processStepsTable)) {
				return null;
			}
			
			foreach ($processStepsTable ['related_module'] as $key => $processStep) {
				if (($processStep  == $module) && ($processStepsTable ['step_codeid'] [$key] != $lastStep)) {
					$stepData ['step_codeid'] = intval ($processStepsTable ['step_codeid'] [$key]);;
					$stepData ['step_name']   = $processStepsTable ['step_name'] [$key];
					break;
				}
			}
			if (isset ($stepData)) {
				$stepData ['step_type'] = '';
				$entityStep  = CRMEntity::getInstance (self::PROCESS_STEP_MODULE_NAME);
				$entityStep->mode          = 'edit';
				$entityStep->id            = $stepData ['step_codeid'];
				$entityStep                = CRMEntity::getInstance (self::PROCESS_STEP_MODULE_NAME);
				$entityStep->column_fields = getColumnFields (self::PROCESS_STEP_MODULE_NAME);
				$entityStep->retrieve_entity_info ($stepData ['step_codeid'], self::PROCESS_STEP_MODULE_NAME);
				$stepData ['estimated_time'] = $entityStep->column_fields ['estimated_tim'];
				$stepData ['error_rate']     = $entityStep->column_fields ['error_rat'];
				unset($entityStep);
				$result = $adb->pquery (
					'SELECT step_type FROM vtiger_step_type WHERE step_typeid= ?',
					array ($stepData ['step_codeid'])
				);
				if ($adb->num_rows ($result) > 0) {
					$row = $adb->fetchByAssoc ($result);
					$stepData ['step_type'] = $row ['step_type'];
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				return $stepData;
			}
			return null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $processId
		 *
		 * @return array|null
		 */
		private static function getStepsByProcess ($adb, $processId) {
			if (empty ($processId)) {
				return null;
			}
			$results = $adb->pquery (
				"SELECT
						pt.*,
						st.step_task,
						st.step_comments,
			       		st.step_view,
						ps.step_description
					FROM vtiger_process_at_steps pt
					INNER JOIN vtiger_step_type st ON st.step_typeid = pt.step_id
					INNER JOIN vtiger_process_steps ps ON ps.process_stepsid = pt.step_id
					WHERE processtfid=?
					ORDER BY pt.sequence ASC",
				array ($processId)
			);
			
			if ($adb->num_rows ($results) > 0) {
				$steps = array ();
				while ($row = $adb->fetchByAssoc ($results)) {
					$steps [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($steps)) ? $steps : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $caseNum
		 *
		 * @return integer|null
		 */
		private static function getStepsByCase ($adb, $caseNum) {
			if (empty ($caseNum)) {
				return 0;
			}
			$result = $adb->pquery (
				'SELECT COUNT(*) AS total  FROM vtiger_crmentity WHERE deleted=? AND case_number=?',
				array (0, $caseNum)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result);
				$total = $row ['total'];
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($total)) ? $total : 0;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $idCase
		 * @param integer $idDoc
		 *
		 * @return boolean
		 */
		private static function isDocIncluded ($adb, $idCase, $idDoc) {
			$included = false;
			$result   = $adb->pquery (
				'SELECT cases2documentid FROM vtiger_process_cases2document WHERE process_casesid=? AND attachmentsid=?',
				array ($idCase, $idDoc)
			);
			if ($adb->num_rows ($result) > 0) {
				$included = true;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $included;
		}
		
		private static function sanitizeString ($string) {
			$string = html_entity_decode ($string, ENT_QUOTES, 'UTF-8');
			return $string;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $caseId
		 * @param string $stepName
		 * @param integer $crmId
		 * @param string $caseTitle
		 *
		 * @return void
		 */
		private static function setCaseNumber ($adb, $caseId, $caseNumber, $crmId, $caseTitle) {
			$caseNumber = (empty($caseNumber)) ? date ('Ymd') : $caseNumber;
			$adb->pquery (
				'UPDATE vtiger_process_cases SET case_number=?, case_title=? WHERE process_casesid=?',
				array ($caseNumber, $caseTitle, $caseId)
			);
			$adb->pquery (
				'UPDATE vtiger_crmentity SET case_number=? WHERE crmid=?',
				array ($caseNumber, $crmId)
			);
			$adb->pquery (
				'UPDATE vtiger_crmentity SET case_number=? WHERE crmid=?',
				array ($crmId, $caseId)
			);
			
		}
		
		/**
		 * @param integer $casesProcessId
		 *
		 * @return array|null
		 */
		private static function updateProcessCase ($adb, $casesProcessId, $crmId) {
			unset ($_REQUEST ['business_processes']);
			unset ($_REQUEST ['cases_process']);
			$today      = date ('Y-m-d');
			$thisMoment = date ('H:i:s');
			
			$entityCase  = CRMEntity::getInstance (self::PROCESS_CASE_MODULE_NAME);
			$entityCase->mode          = 'edit';
			$entityCase->id            = $casesProcessId;
			$entityCase->column_fields = getColumnFields (self::PROCESS_CASE_MODULE_NAME);
			$entityCase->retrieve_entity_info ($casesProcessId, self::PROCESS_CASE_MODULE_NAME);
			$startDate = $entityCase->column_fields ['start_date'] . ' ' . $entityCase->column_fields ['start_time'];
			$dueDate   = $today . ' ' . $thisMoment;
			$diff      = strtotime ($dueDate) - strtotime ($startDate);
			
			$entityCase->column_fields['due_date']       = $today;
			$entityCase->column_fields['due_step_date']  = $today;
			$entityCase->column_fields['end_step_time']  = $thisMoment;
			$entityCase->column_fields['end_time']       = $thisMoment;
			$entityCase->column_fields['step_exec_time'] = floatval ($diff / 3600);
			$processData ['process_id']                  = $entityCase->column_fields['process'];
			$processData ['step_id']                     = $entityCase->column_fields['process_step'];
			$processData ['case_id']                     = $casesProcessId;
			$entityCase->save (self::PROCESS_CASE_MODULE_NAME);
			$adb->pquery (
				'UPDATE vtiger_crmentity SET case_number=? WHERE crmid=?',
				array ($crmId, $entityCase->id)
			);
			
			unset($entityCase);
			return $processData;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $record
		 * @param string $caseTitle
		 * @param array $processData
		 * @param CRMEntity $entity
		 *
		 * @return void
		 * @throws WebServiceException
		 */
		private static function updateTitleProcessCase ($adb, $record, $caseTitle, $processData, $entity) {
			if (empty ($processData ['case_id'])) {
				$stepName   = StoreUtils::randomPassword (6, false);
				$caseNumber = date ('Ymd') . '-' . $stepName;
				
				$caseTitle = "Caso N° {$caseNumber} - {$caseTitle}";
				$message = '<b>Se ha creado el caso de proceso: </b>' . $caseTitle;
			} else {
				$caseNumber = self::getCaseNumber ($adb, $processData ['case_id']);
				$caseTitle = "Caso N° {$caseNumber} - {$caseTitle}";
				$message   = '<b>Se ha actualizado el caso de proceso: </b>' . $caseTitle;
			}
			self::setCaseNumber ($adb, $record, $caseNumber, $entity->id, $caseTitle);
			$_REQUEST['case_number']  = $caseNumber;
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' =>  $message,
			);
			unset ($caseEntity);
		}
		
		/**
		 * @param array $data
		 *
		 * @return integer
		 * @throws WebServiceException
		 */
		public static function createNewCase ($data) {
			$today      = date ('Y-m-d');
			$thisMoment = date ('H:i:s');
			$starDate   = $today . ' ' . $thisMoment;
			$entityCase                = CRMEntity::getInstance ('process_cases');
			$entityCase->column_fields = getColumnFields ('process_cases');
			$entityCase->mode          = 'create';
			$entityCase->id            = null;
			$entityCase->column_fields ['case_title']      = "Caso N° {$data['case_number']} - {$data ['moduleName']}";
			$entityCase->column_fields ['createdtime']     = $starDate;
			$entityCase->column_fields ['modifiedtime']    = $starDate;
			$entityCase->column_fields ['module_name']      = (($data ['moduleName'] == 'Manual')) ? '' : $data ['moduleName'];
			$entityCase->column_fields ['process']          = $data ['process_id'];
			$entityCase->column_fields ['process_step']     = $data ['step_id'];
			$entityCase->column_fields ['star_step_time']   = $thisMoment;
			$entityCase->column_fields ['start_date']       = $today;
			$entityCase->column_fields ['start_step_date']  = $today;
			$entityCase->column_fields ['start_time']       = $thisMoment;
			$entityCase->column_fields ['type_step']        = $data ['step_type'];;
			$entityCase->column_fields ['assigned_user_id'] = $data ['assigned_user_id'];
			$entityCase->column_fields ['case_number']      = $data ['case_number'];
			$entityCase->save ('process_cases');
			return $entityCase->id;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param Users $users
		 * @param string $moduleName
		 * @param string $caseNumber
		 *
		 * @return string|null
		 * @throws SmartyException
		 */
		public static function fetchAvailableProcess ($adb, $user, $moduleName, $caseNumber) {
			if (!empty ($caseNumber)) {
				return null;
			}
			$availableProcess = self::getProcess ($adb, $user, $moduleName);
			if (empty ($availableProcess)) {
				return null;
			}
			$availableCaesInProcess = self::getCaseInProcess ($adb, $user, $moduleName);
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_CASES_PROCESS', $availableCaesInProcess);
			$smarty->assign ('AVAILABLE_PROCESS', $availableProcess);
			$smarty->assign ('CASE_NUMBER', $caseNumber);
			return $smarty->fetch ("ProcessEditView.tpl");
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $periodDates
		 * @param string $state
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchBehaviorOfProcess ($adb, $periodDates, $state) {
			$allProcesses = self::fetchDistinctProcess ($adb, $periodDates);
			if (!empty ($allProcesses)) {
				foreach ($allProcesses as $processId => $title) {
					$stateOfProcess      = self::getStateOfProcess ($adb, $processId, $periodDates);
					if ($stateOfProcess ['avg_process'] == 0) {
						continue;
					}
					$bandControlPlus     = ($stateOfProcess ['avg_process'] + $stateOfProcess ['stddev_process']);
					$bandControlLess     = ($stateOfProcess ['avg_process'] - $stateOfProcess ['stddev_process']);
					$bandControlPlusPlus = ($stateOfProcess ['avg_process'] + (3 * $stateOfProcess ['stddev_process']));
					$bandControlLessLess = ($stateOfProcess ['avg_process'] - (3 * $stateOfProcess ['stddev_process']));
					$startDate           = $periodDates ['startdate'];
					$dueDate             = $periodDates ['enddate'];
					$where               = " process = {$processId} AND ";
					if ($state == 1) {
						$where .= "finish_process = 1 AND (
								(start_date BETWEEN '{$startDate}' AND '{$dueDate}') OR
								(due_date BETWEEN '{$startDate}' AND '{$dueDate}')
								)";
					} else if ($state == 0) {
						$where .= "finish_process != 1 AND (start_date >= '{$startDate}' AND start_date <= '{$dueDate}')";
					} else {
						continue;
					}
					$selectCase  = "CASE WHEN ROUND(SUM(step_exec_time),4) BETWEEN {$bandControlLess} AND {$bandControlPlus}  THEN 'IN_CONTROL' ";
					$selectCase .= " WHEN ROUND(SUM(step_exec_time),4) BETWEEN {$bandControlLessLess} AND {$bandControlPlusPlus} THEN 'AT_RISK' ";
					$selectCase .= " ELSE 'OUT_CONTROL' END AS rango";
					$result = $adb->query (
						"SELECT COUNT(*) as total, {$selectCase}
							FROM vtiger_process_cases
							INNER JOIN vtiger_crmentity crm ON 	crm.crmid = vtiger_process_cases.process_casesid AND crm.deleted = 0
							WHERE
							    vtiger_process_cases.case_number IS NOT NULL AND vtiger_process_cases.case_number <> '' AND
							    {$where}
							GROUP BY vtiger_process_cases.case_number"
					);
					if ($adb->num_rows ($result) > 0) {
						while ($row = $adb->fetchByAssoc ($result, -1, false)) {
							$behaviorData[] = $row;
						}
					}
					$states = array_unique (array_column ($behaviorData, 'rango'));
					foreach ($states as $state) {
						$total = 0;
						foreach ($behaviorData as $data) {
							if ($data ['rango'] == $state) {
								$total += 1;
							}
						}
						$behaviorProcess [] = array (
							'processId'   => $processId,
							'process'     => $title,
							'total'       => $total,
							'state'       => $state,
						);
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
					unset ($states);
				}
			}
			return (isset ($behaviorProcess)) ? $behaviorProcess : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $periodDates
		 * @param string $state
		 * @param integer $processId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchCasesInvolvedByProcess ($adb, $periodDates, $state, $processId) {
			$stateOfProcess      = self::getStateOfProcess ($adb, $processId, $periodDates);
			$bandControlPlus     = ($stateOfProcess ['avg_process'] + $stateOfProcess ['stddev_process']);
			$bandControlLess     = ($stateOfProcess ['avg_process'] - $stateOfProcess ['stddev_process']);
			$bandControlPlusPlus = ($stateOfProcess ['avg_process'] + (3 * $stateOfProcess ['stddev_process']));
			$bandControlLessLess = ($stateOfProcess ['avg_process'] - (3 * $stateOfProcess ['stddev_process']));
			$startDate           = $periodDates ['startdate'];
			$dueDate             = $periodDates ['enddate'];
			$where               = " process = {$processId} AND ";
			if ($state == 1) {
				$where .= "finish_process = 1 AND (
							(start_date BETWEEN '{$startDate}' AND '{$dueDate}') OR
							(due_date BETWEEN '{$startDate}' AND '{$dueDate}')
							)";
			} else if ($state == 0) {
				$where .= "finish_process != 1 AND (start_date >= '{$startDate}' AND start_date <= '{$dueDate}')";
			} else {
				return null;
			}
			
			$result = $adb->query (
				"SELECT
       					pc.process_casesid,
       					pc.cod_process_cases,
       					pc.case_title,
       					pc.step_exec_time,
                        ps.step_name,
       					ROUND(SUM(pc.step_exec_time),4) AS exec_time,
           				COUNT(*) AS total,
           				SUBSTRING_INDEX(pc.case_title,'-',2) AS sub_title
					FROM vtiger_process_cases pc
					INNER JOIN vtiger_crmentity crm ON 	crm.crmid = pc.process_casesid AND crm.deleted = 0
					INNER JOIN  vtiger_process_steps ps ON ps.process_stepsid = pc.process_step
					WHERE {$where}
					GROUP by pc.case_number, sub_title"
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$time         = floatval ($row ['exec_time']);
					$row['state'] = 'OUT_CONTROL';
					if ($time <= $bandControlPlus && $time >= ($bandControlLess)) {
						$row['state'] = 'IN_CONTROL';
					} else if ($time <= $bandControlPlusPlus && $time >= ($bandControlLessLess)) {
						$row['state'] = 'AT_RISK';
					}
					if (strlen($row['step_name']) > 20) {
						$row['step_name'] = substr ($row['step_name'], 0, 20) . '...';
					}
					
					$behaviorProcess [] = array (
						'casesid'   => $row ['process_casesid'],
						'cod'       => $row ['cod_process_cases'],
						'title'     => $row ['sub_title'],
						'time'      => number_format ($time, 2, ',', '.'),
						'state'     => $row['state'],
						'step_exec' => $row['total'],
						'step_name' => $row['step_name'],
					);
					usort ($behaviorProcess, function ($a, $b) {
						return $a ['state'] > $b ['state'];
					});
					$time = 0;
				}
				
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($behaviorProcess)) ? $behaviorProcess : array();
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array|null $periodDates
		 * @return array|null
		 */
		public static function fetchDistinctProcess ($adb, $periodDates = null) {
			$wherePeriod = '';
			if (!empty ($periodDates) && is_array ($periodDates)) {
				$startDate   = $periodDates ['startdate'];
				$dueDate     = $periodDates ['enddate'];
				$wherePeriod = "AND ((pc.start_date >= '{$startDate}' AND pc.start_date <= '{$dueDate}') OR (
					(pc.start_date <= '{$startDate}') AND (pc.due_date >= '{$startDate}' AND pc.due_date <= '{$dueDate}')
				))";
			}
			$result = $adb->query (
				'SELECT
    				DISTINCT(pc.process),
                	p.process_title
				FROM
				    vtiger_process_cases pc
				INNER JOIN vtiger_crmentity crm ON  crm.crmid = pc.process_casesid AND crm.deleted = 0
				INNER JOIN vtiger_process p ON p.processid = pc.process
				WHERE
				    pc.process IS NOT NULL AND pc.process != ""' . $wherePeriod
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$processes [$row['process']] = $row['process_title'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($processes)) ? $processes : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $starDate
		 * @param string $dueDate
		 * @param float $stepExecTime
		 * @param array $holidays
		 *
		 * @return float
		 * @throws Exception
		 */
		public static function fetchHoursWorkedByPeriod ($adb, $starDate, $dueDate, $stepExecTime, $holidays = array ()) {
			if (floatval ($stepExecTime) < 9) {
				return round (floatval ($stepExecTime),2);
			}
			setlocale (LC_TIME, 'es_ES.UTF-8');
			$periodInitDate = new DateTime ($starDate);
			$periodDueDate  = new DateTime ($dueDate);
			$periodDueDate->modify ('+1 day');
			
			$interval = new DateInterval('P1D');
			$period   = new DatePeriod ($periodInitDate, $interval, $periodDueDate);
			
			$workDays   = 0;
			$totalHours = 0;
			$daysName   = self::DAY_NAMES;
			foreach ($period as $myDate) {
				$timestamp = strtotime ($myDate->date);
				$dayOfWeek = strftime ('%A', $timestamp);
				$dayOfWeek = (isset($daysName [$dayOfWeek])) ? $daysName[$dayOfWeek] : $dayOfWeek;
				$hours     = self::getHoursWorkedByDay($adb, $myDate->format('N'), $dayOfWeek);
				if ($hours > 0 && !in_array($myDate->format('Y-m-d'), $holidays)) {
					$workDays++;
					$totalHours += $hours;
				}
			}
			$percentageWorkingTime = (($totalHours / ($workDays * 24)));
			return round ((floatval ($stepExecTime) * $percentageWorkingTime), 2);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string|null $caseNumber
		 * @param CRMEntity $entity
		 * @param Users $currentUser
		 * @param boolean $onlyView
		 *
		 * @return string|null
		 */
		public static function fetchCaseByCode ($adb, $caseNumber, $entity, $current_user, $onlyView = false) {
			if (!empty ($caseNumber)) {
				list ($date, $id, $moduleLabel) = explode ('-', $caseNumber);
				$caseId           = "{$date}-{$id}";
				$availableProcess = null;
				if ($onlyView) {
					$viewTemplate = 'Home/TabsContents/Objects/ProcessCaseView.tpl';
				} else {
					$viewTemplate = 'ProcessCaseDetailView.tpl';
				}
			} else {
				$viewTemplate     = 'ProcessDetailView.tpl';
				$availableProcess = self::getProcess (
					$adb, $current_user,
					$entity->column_fields ['record_module']
				);
				$caseId           = null;
				$moduleLabel      = null;
			}
			$numberOfSteps = 1;
			$spaceWidth    = 0;
			$openCases     = null;
			$caseProcess   = self::getCaseDataByCode ($adb, $caseId, $entity->column_fields ['record_module'], $entity->id);
			if (!empty ($caseProcess) && count ($caseProcess) > 1) {
				$numberOfSteps = (count ($caseProcess) - 1);
				$spaceWidth    = self::LINE_WIDTH / ($numberOfSteps);
			}
			if (empty ($caseProcess) && empty ($availableProcess)) {
				return null;
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_PROCESS', $availableProcess);
			$smarty->assign ('CASES_PROCESS', $caseProcess);
			$smarty->assign ('CASE_ID', $caseId);
			$smarty->assign ('CASE_NUMBER', $caseNumber);
			$smarty->assign ('ID', $entity->id);
			$smarty->assign ('IS_FINISH_PROCESS', self::isFinishProcess ($adb, $caseId));
			$smarty->assign ('LINE_WIDTH', self::LINE_WIDTH);
			$smarty->assign ('MODULE', $entity->column_fields ['record_module']);
			$smarty->assign ('MODULE_LABEL', $moduleLabel);
			$smarty->assign ('NUMBER_OF_STEPS', $numberOfSteps);
			$smarty->assign ('OPEN_CASES',isset($openCases) ? $openCases : null);
			$smarty->assign ('RECORD_ID', $entity->id);
			$smarty->assign ('RECORD_STEP', (!empty ($entity->case_number) && $caseNumber == $entity->case_number));
			$smarty->assign ('SPACE_WIDTH', $spaceWidth);
			$smarty->assign ('VIEW', null);
			return $smarty->fetch ($viewTemplate);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $mduleName
		 * @param integer $userId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchRecordsToJoinCase ($adb, $moduleName, $userId) {
			$entityData = EntityUtils::fetchModuleData ($adb, $moduleName);
			if (empty ($entityData) || count ($entityData) == 1 || empty ($userId)) {
				return null;
			}
			$cod = self::getCodeFieldName ($adb, $entityData['tabid']);
			if (empty ($cod)) {
				return null;
			}
			$cod = "{$cod} AS cod";
			
			$results = $adb->pquery (
				"SELECT
       					e.{$cod},
       					e.{$entityData['fieldname']} AS fieldname,
       					crm.crmid
					FROM
					   {$entityData['tablename']} e
					INNER JOIN vtiger_crmentity crm ON crm.crmid = e.{$entityData['entityidfield']}
					WHERE
					  crm.deleted = 0 AND
					  crm.setype=? AND
					  crm.case_number IS NULL AND
					  (crm.smcreatorid=? OR crm.smownerid=?)
					  ORDER BY
					      crm.crmid DESC
					  LIMIT 20",
				array ($moduleName, $userId, $userId)
			);
			
			if ($adb->num_rows ($results) > 0) {
				$entitys = array ();
				while ($row = $adb->fetchByAssoc ($results)) {
					$entitys [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($entitys)) ? $entitys : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $parameters
		 *
		 * @return array|mixed|null
		 * @throws Exception
		 */
		public static function getCaseDetails ($adb, $parameters) {
			if (empty ($parameters)) {
				return null;
			}
			$results = $adb->pquery (
				'SELECT
       				*,
       				crm.case_number AS crmid_module
				FROM vtiger_process_cases pc
				INNER JOIN vtiger_crmentity crm ON crm.crmid = pc.process_casesid
				WHERE
				    crm.deleted=0 AND
				 	pc.case_number=? AND
				    pc.process=? AND
				    pc.process_step=?',
				$parameters
			);
			if ($adb->num_rows ($results) > 0) {
				$row             = $adb->fetchByAssoc ($results);
				$row ['process'] = self::getProcessById ($adb, $row['process']);
				$row ['step']    = self::getStepProcessId ($adb, $row['process_step']);
				$caseDetails     = $row;
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($caseDetails)) ? $caseDetails : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $caseCode
		 * @param string $stepName
		 *
		 * @return integer|null
		 */
		public static function getCaseIdByStepName ($adb, $caseCode, $stepName) {
			if (empty ($caseCode) || empty ($stepName)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT
	   				pc.process_casesid
				FROM vtiger_process_cases pc
				INNER JOIN vtiger_process_steps ps ON ps.process_stepsid = pc.process_step
				WHERE
				    pc.case_number=?  AND
				    ps.step_name=?
				LIMIT 1',
				array ($caseCode, $stepName)
			);
			if ($adb->num_rows ($result) > 0) {
				$caseId = $adb->query_result ($result, 0, 'process_casesid');
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($caseId)) ? $caseId : null;
		}
		
		/**
		 * @return string[]
		 */
		public static function getControlBands () {
			return self::CONTROL_BANDS;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $stepId
		 * @param array $periodDates
		 * @param float $execTime
		 *
		 * @return string
		 */
		public static function getControlBandsBySteps ($adb, $stepId, $periodDates, $time) {
			$stateOfProcess = self::getStateOfSteps ($adb, $stepId, $periodDates);
			if (empty ($stateOfProcess)) {
				return 'OUT_CONTROL';
			}
			$bandControlPlus     = ($stateOfProcess ['avg_process'] + $stateOfProcess ['stddev_process']);
			$bandControlLess     = ($stateOfProcess ['avg_process'] - $stateOfProcess ['stddev_process']);
			$bandControlPlusPlus = ($stateOfProcess ['avg_process'] + (3 * $stateOfProcess ['stddev_process']));
			$bandControlLessLess = ($stateOfProcess ['avg_process'] - (3 * $stateOfProcess ['stddev_process']));
			$stepStatus          = 'OUT_CONTROL';
			if ($time <= $bandControlPlus && $time >= ($bandControlLess)) {
				$stepStatus = 'IN_CONTROL';
			} else if ($time <= $bandControlPlusPlus && $time >= ($bandControlLessLess)) {
				$stepStatus = 'AT_RISK';
			}
			return $stepStatus;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $processId
		 * @param array $periodDates
		 * @param array $data
		 *
		 * @return string[][]
		 */
		public static function getGraphicDataFromCases ($adb, $processId, $periodDates, $data) {
			$dataGraphics = array (
				array ('Caso', 'tiempo','Control sup ','Control inf','Control sup 3s','Control inf 3s'),
			);
			if (empty ($data)) {
				return $dataGraphics;
			}
			$stateOfProcess      = self::getStateOfProcess ($adb, $processId, $periodDates);
			$bandControlPlus     = ($stateOfProcess ['avg_process'] + $stateOfProcess ['stddev_process']);
			$bandControlLess     = ($stateOfProcess ['avg_process'] - $stateOfProcess ['stddev_process']);
			$bandControlPlusPlus = ($stateOfProcess ['avg_process'] + (3 * $stateOfProcess ['stddev_process']));
			$bandControlLessLess = ($stateOfProcess ['avg_process'] - (3 * $stateOfProcess ['stddev_process']));
			foreach ($data as $row) {
				$dataGraphics [] = array (
					(!empty($row ['title'])) ? $row ['title'] : 'COD.',
					floatval ($row ['time']),
					floatval ($bandControlPlus),
					floatval ($bandControlLess),
					floatval ($bandControlPlusPlus),
					floatval ($bandControlLessLess),
				);
			}
			return $dataGraphics;
		}
		
		/**
		 * @return string[]
		 */
		public static function getQualityValuation () {
			return self::QUALITY_VALUATION;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $processId
		 * @param array $periodDates
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchGraphicDataSteps ($adb, $processId, $periodDates) {
			$steps = self::getStepsByProcess ($adb, $processId);
			if (empty ($steps)) {
				return;
			}
			$stepCode            = null;
			$stepProcessed       = array ();
			$series              = array ('Paso','Control sup ','Control inf','Control sup 3s','Control inf 3s');
			$labels              = array ();
			
			foreach ($steps as $step) {
				if (in_array ($step ['step_id'], $stepProcessed)) {
					continue;
				}
				$stepCode = $step ['step_code'];
				$stepName = (!empty($step ['step_name'])) ? $step ['step_name'] :'Sin_Nombre';
				$stepName = self::sanitizeString ($stepName);
				$stepId   = $step ['step_id'];
				$result = $adb->pquery (
					'SELECT
				    p.processid,
				    p.process_title,
				    s.step_name,
				    pc.step_exec_time,
       				s.cod_process_steps,
       				pc.case_number,
                    SUBSTRING_INDEX(pc.case_title,"-",2) AS sub_title,
				    IFNULL(
				    ROUND(
				        (
				        SELECT  AVG(pc1.step_exec_time) FROM  vtiger_process_cases AS pc1
				        WHERE
				        	pc1.process = p.processid AND
				            pc1.process_step = pc.process_step AND
				            pc1.due_date IS NOT NULL AND pc1.due_date!= "" AND
				            pc1.start_date >=? AND start_date <=?
				    ), 4),0) AS avg_step,
				    IFNULL(
				    ROUND(
				        (
				        SELECT  STDDEV(pc1.step_exec_time) FROM vtiger_process_cases AS pc1
				        WHERE
				            pc1.process = p.processid AND
				            pc1.process_step = pc.process_step AND
				            pc1.due_date IS NOT NULL AND pc1.due_date!= "" AND
				            pc1.start_date >=? AND start_date <=?
				    ), 4),0) AS desvstd_step
				FROM
				    vtiger_process_cases AS pc
				INNER JOIN vtiger_crmentity AS crm ON crm.crmid = process_casesid AND crm.deleted = 0
				LEFT JOIN vtiger_process AS p ON p.processid = `process`
				LEFT JOIN vtiger_process_steps s ON s.process_stepsid = `process_step`
				WHERE
				    pc.case_number IS NOT NULL AND pc.case_number <> "" AND
				    pc.process_step=? AND
				    pc.due_date IS NOT NULL AND pc.due_date!= "" AND
				    pc.start_date >=? AND pc.start_date <=?
				ORDER BY
				    p.processid,
				    p.process_title,
				    s.step_name',
					array(
						$periodDates ['startdate'],
						$periodDates ['enddate'],
						$periodDates ['startdate'],
						$periodDates ['enddate'],
						$step ['step_id'],
						$periodDates ['startdate'],
						$periodDates ['enddate']
					)
				);
				$stepProcessed [] = $step ['step_id'];
				if ($adb->num_rows ($result) > 0) {
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						$index  = trim ($row['case_number']);
						if (!in_array ($index, $series)) {
							$series [] = $index;
							$labels [] = $index;
						}
						$theTime                                            = floatval ($row ['step_exec_time']);
						$stepExecTime[$index][$stepName] = round ((($theTime - $row['avg_step']) / $row['desvstd_step']), 2);
						$data[] = array (
							$stepName,
							1,
							-1,
							3,
							-3,
						);
					}
				}
			}
			if (isset ($data)) {
				$dataGraphics [] = $series;
				foreach ($labels as $label) {
					foreach ($data as $key => &$row) {
						$myStep = $row[0];
						if (!in_array ($myStep, array_keys ($stepExecTime[$label]))) {
							$value = null;
						} else {
							$value = $stepExecTime[$label][$myStep];
						}
						$row[] = $value;
					}
				}
				$stepFound = array();
				foreach ($data as $row) {
					$totalRow = array_sum (array_slice ($row, 5));
					if ($totalRow != 0 && !in_array ($row[0], $stepFound)) {;
						$dataGraphics [] = $row;
						$stepFound []    = $row[0];
					}
				}
			}
			return isset($dataGraphics) ? array('data' => $dataGraphics, 'series' => $labels) : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $caseNumber
		 *
		 * @return mixed|null
		 */
		public static function getLastCaseId ($adb, $caseNumber) {
			$results = $adb->pquery (
				'SELECT
	   				pc.process_casesid
				FROM vtiger_process_cases pc
				INNER JOIN vtiger_crmentity crm ON crm.crmid = pc.process_casesid
				WHERE
				    crm.deleted=0 AND
				 	pc.case_number=? AND
				    pc.module_name !=? AND
				    pc.module_name IS NOT NULL
				LIMIT 1',
				array ($caseNumber, '')
			);
			if ($adb->num_rows ($results) > 0) {
				$row = $adb->fetchByAssoc ($results);
				DatabaseUtils::closeResult ($results);
				$results = null;
				return $row['process_casesid'];
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $caseId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getProcessById ($adb, $processId) {
			if (empty ($processId)) {
				return null;
			}
			$results = $adb->pquery (
				'SELECT
       				p.*
				FROM vtiger_process p
				INNER JOIN vtiger_crmentity crm ON crm.crmid = p.processid
				WHERE
				    crm.deleted=? AND
				    p.processid=?',
				array (0, $processId)
			);
			if ($adb->num_rows ($results) > 0) {
				$row = $adb->fetchByAssoc ($results);
				$process = $row;
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($process)) ? $process : null;
		}
		
		/**
		 * @param array $processState
		 * @param string $state
		 *
		 * @return integer
		 */
		public static function getProcessOutOfAverage ($processState, $state = 'IN_CONTROL') {
			if (empty ($processState)) {
				return 0;
			}
			$total = 0;
			foreach ($processState as  $process) {
				if ($process ['state'] == $state) {
					continue;
				}
				$total += intval ($process ['total']);
			}
			return $total;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return string|null
		 * @throws Exception
		 */
		public static function getProcessTableSteps ($adb) {
			$tabId = getTabid (self::PROCESS_MODULE_NAME);
			return TableFieldHelper::getTableName ($adb, $tabId, self::PROCESS_TABLE_FIELD_NAME);
		}
		
		/**
		 * @return string
		 */
		public static function getStatesTableName () {
			return 'vtiger_states_'.session_id();
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $stepId
		 *
		 * @return array|mixed|null
		 * @throws Exception
		 */
		public static function getStepProcessId ($adb, $stepId) {
			if (empty ($stepId)) {
				return null;
			}
			$results = $adb->pquery (
				'SELECT
	   				ps.*,
       				st.*
				FROM vtiger_process_steps ps
				INNER JOIN vtiger_crmentity crm ON crm.crmid = ps.process_stepsid
				INNER JOIN vtiger_step_type st on st.step_typeid = ps.process_stepsid
				WHERE
				    crm.deleted=? AND
				    ps.process_stepsid=?',
				array (0, $stepId)
			);
			if ($adb->num_rows ($results) > 0) {
				$row = $adb->fetchByAssoc ($results);
				$step = $row;
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($step)) ? $step : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $processId
		 * @param string $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getStepToModule ($adb, $processId, $moduleName) {
			if (empty ($processId) || empty ($moduleName)) {
				return null;
			}
			$tableName = self::getProcessTableSteps ($adb);
			$results = $adb->pquery (
				"SELECT
				   *
				FROM
				    {$tableName} tn
				INNER JOIN vtiger_step_type st ON st.step_typeid = tn.step_codeid
				WHERE
				    tn.processtfid=? AND
				    tn.related_module=?
				LIMIT 1",
				array ($processId, $moduleName)
			);
			if ($adb->num_rows ($results) > 0) {
				$row = $adb->fetchByAssoc ($results);
				$step = $row;
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($step)) ? $step : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $periodDates
		 * @param integer|null $state
		 * @param integer|null $processId
		 *
		 * @return integer|array|mixed
		 * @throws Exception
		 */
		public static function getTotalCaseByFinishState ($adb, $periodDates, $state, $processId = null) {
			if (!count ($periodDates) || !is_array ($periodDates)) {
				return 0;
			}
			$startDate = $periodDates ['startdate'];
			$dueDate   = $periodDates ['enddate'];
			$where     = (!empty ($processId)) ? " process = {$processId} AND " : '';
			$groupBY   = 'GROUP by vtiger_process_cases.case_number';
			if ($state == 1) {
				$where .= "finish_process = 1 AND start_date <= '{$dueDate}' ";
			} else if ($state == 0) {
				$where .= "finish_process != 1 AND start_date <= '{$dueDate}' ";
			} else {
				$where .= "finish_process = 1 AND start_date <= '{$dueDate}'";
				$groupBY = '';
			}
			$result = $adb->query (
				"SELECT
       					COUNT(*) as total,
       					ROUND(SUM(step_exec_time),4) AS tota_sum,
                        ROUND(AVG(step_exec_time),4) AS avg,
    					ROUND(STDDEV(step_exec_time),4) AS stddev
					FROM vtiger_process_cases
					INNER JOIN vtiger_crmentity crm ON 	crm.crmid = vtiger_process_cases.process_casesid AND crm.deleted = 0
					WHERE
					    vtiger_process_cases.case_number IS NOT NULL AND vtiger_process_cases.case_number <> '' AND
					    {$where}
					    {$groupBY}"
			);
			$totalTime    = 0;
			$totalProcess = 0;
			if ($adb->num_rows ($result) > 0) {
				if ($state !=1 && $state != 0) {
					$row = $adb->fetchByAssoc ($result);
					$row ['avg']     = floatval ($row['avg']);
					$row ['stddev'] = floatval ($row['stddev']);
					return $row;
				}
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$totalTime    += floatval ($row['tota_sum']);
					$totalProcess += 1;
				}
				$result->Move(0);
				$avg    = ($totalTime/$totalProcess);
				$stdDev = 0;
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (floatval ($row ['tota_sum']) == 0) {
						continue;
					}
					$stdDev += floatval (abs(($row['tota_sum']) - $avg));
				}
				if ($stdDev > 0) {
					$stdDev = sqrt (pow ($stdDev, 2)/($totalProcess - 0));
				}
				if (!empty ($processId)) {
					$casesByFinishState ['total']    = $totalProcess;
					$casesByFinishState ['tota_sum'] = number_format ($totalTime, 2, ',', '.');
					$casesByFinishState ['avg']      = number_format ($avg, 2, ',', '.');
					$casesByFinishState ['stddev']   = number_format ($stdDev, 2, ',', '.');
				} else {
					$casesByFinishState ['total'] = $totalProcess;
				}
				
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($casesByFinishState)) ? $casesByFinishState : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $caseNumber
		 *
		 * @return boolean
		 */
		public static function isFinishProcess ($adb, $caseNumber) {
			if (empty ($caseNumber)) {
				return false;
			}
			$results = $adb->pquery (
				'SELECT
	   				pc.finish_process
				FROM vtiger_process_cases pc
				INNER JOIN vtiger_crmentity crm ON crm.crmid = pc.process_casesid
				WHERE
				    crm.deleted=0 AND
				 	pc.case_number=?
				LIMIT 1',
				array ($caseNumber)
			);
			if ($adb->num_rows ($results) > 0) {
				$row = $adb->fetchByAssoc ($results);
				$finishProcess = (intval ($row['finish_process']) == 1);
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($finishProcess)) ? $finishProcess : false;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $procesCaseId
		 * @param array $documentIds
		 *
		 * @return false|void
		 */
		public static function saveDocumentToCase ($adb, $processCaseId, $documentIds) {
			if (!count ($documentIds) || empty ($processCaseId)) {
				return false;
			}
			foreach ($documentIds as $documentId) {
				if (self::isDocIncluded ($adb, $processCaseId, $documentId)) {
					continue;
				}
				$adb->pquery (
					'INSERT INTO vtiger_process_cases2document (process_casesid, attachmentsid) VALUES (?,?)',
					array ($processCaseId, $documentId)
				);
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $module
		 * @param CRMEntity $entity
		 *
		 * @return void
		 */
		public static function saveProcessCase ($adb, $module, $entity) {
			if (
				(!isset ($_REQUEST ['business_processes']) || empty ($_REQUEST ['business_processes'])) &&
				(!isset ($_REQUEST ['cases_process']) || empty ($_REQUEST ['cases_process']))
			) {
				return null;
			}
			
			if (!empty ($_REQUEST ['cases_process'])) {
				$processData = self::updateProcessCase ($adb, $_REQUEST ['cases_process'], $entity->id);
			} else {
				$processData ['process_id'] =  $_REQUEST ['business_processes'];
				$processData ['step_id']    = null;
				$processData ['case_id']	= null;
			}
			
			unset ($_REQUEST ['business_processes']);
			unset ($_REQUEST ['cases_process']);
			$entityCase                = CRMEntity::getInstance (self::PROCESS_CASE_MODULE_NAME);
			$entityCase->column_fields = getColumnFields (self::PROCESS_CASE_MODULE_NAME);
			$entityCase->mode          = 'create';
			$entityCase->id            = null;
			self::buildEntity ($adb,$entityCase, $module, $entity, $processData);
			$caseTitle = $entityCase->column_fields ['case_title'];
			$entityCase->save (self::PROCESS_CASE_MODULE_NAME);
			$record   = $entityCase->id;
			unset ($entityCase);
			self::updateTitleProcessCase ($adb, $record, $caseTitle, $processData, $entity);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $data
		 *
		 * @return void
		 * @throws WebServiceException
		 */
		public static function setRecordToJoinCase ($adb, $data) {
			if (empty ($data['caseId']) || empty ($data['crmId']) || empty ($data['moduleName'])) {
				throw new Exception ('Imposible asignar registro en el caso por falta de datos');
			}
			$result = $adb->pquery('SELECT createdtime, smownerid FROM vtiger_crmentity WHERE crmid=?', array ($data['crmId']));
			if ($result && $adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result);
				list ($startDate, $startTime) = explode (' ', $row ['createdtime']);
				$ownerId                      = $row ['smownerid'];
			} else {
				throw new Exception ('Registro no encontrado');
			}
			list ($processId, $stepId) = explode ('@', $data['records']);
			$moduleLabel               = getTabIdLabelByName ($data['moduleName']);
			$caseTitle 			       = "Caso N° {$data['caseId']} - {$moduleLabel}";
			$entityCase = CRMEntity::getInstance (self::PROCESS_CASE_MODULE_NAME);
			$entityCase->column_fields = getColumnFields (self::PROCESS_CASE_MODULE_NAME);
			$entityCase->mode          = 'create';
			$entityCase->id            = null;
			$entityCase->column_fields ['assigned_user_id'] = $ownerId;
			$entityCase->column_fields ['case_title']        = $caseTitle;
			$entityCase->column_fields ['comment']           = '';
			$entityCase->column_fields ['module_name']       = $data['moduleName'];
			$entityCase->column_fields ['process']           = $processId;
			$entityCase->column_fields ['process_step']      = $stepId;
			$entityCase->column_fields ['star_step_time']    = $startTime;
			$entityCase->column_fields ['start_date']        = $startDate;
			$entityCase->column_fields ['start_step_date']   = $startDate;
			$entityCase->column_fields ['start_time']        = $startTime;
			$entityCase->save (self::PROCESS_CASE_MODULE_NAME);
			$record = $entityCase->id;
			$adb->pquery (
				'UPDATE vtiger_process_cases SET case_number=? WHERE process_casesid=?',
				array ($data['caseId'], $record)
			);
			$adb->pquery (
				'UPDATE vtiger_crmentity SET case_number=? WHERE crmid=?',
				array ($data['caseId'], $data['crmId'])
			);
			$adb->pquery (
				'UPDATE vtiger_crmentity SET case_number=? WHERE crmid=?',
				array ($data['crmId'], $record)
			);
		}
		
	}
