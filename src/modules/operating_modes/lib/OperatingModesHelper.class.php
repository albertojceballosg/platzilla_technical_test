<?php
	require_once ('include/QueryGenerator/QueryGenerator.php');
	require_once ('include/platzilla/Objects/UserInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/ProcessCasesUtils.class.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	require_once ('modules/News/lib/AdQueueHelper.class.php');
	require_once ('modules/Settings/lib/HowToHelper.class.php');
	require_once ('modules/daily_report/lib/DailyReportUtils.class.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/operating_modes/Objects/ModesContent.php');
	require_once ('modules/operating_modes/Objects/OperatingModes.php');
	require_once ('modules/operating_modes/Objects/TabTabs.php');
	require_once ('modules/operating_modes/lib/DirectionModeHelper.class.php');
	require_once ('modules/operating_modes/lib/ManagementModeHelper.class.php');
	require_once ('modules/process/Objects/ProcessStepInterface.php');
	require_once ('modules/process/lib/ProcessHelper.class.php');

	class OperatingModesHelper {
		
		const TASK_VIEW_STATUS = array (
			'Pendientes' => 'PENDING TASK',
			'Realizadas' => 'COMPLETED TASK',
		);
		
		/** @var PearDatabase */
		protected $masterAdb;

		/** @var integer|string */
		private static $transferValue;
		
		private static $tabName;
		
		public function __construct() {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 * @param boolean $canCreateRecords
		 * @param array $app_strings
		 * @param boolean $isInstance
		 * @param string|null $script
		 *
		 * @return mixed
		 * @throws Exception
		 */
		private static function getActivityTab ($adb, $current_user, $canCreateRecords, $app_strings, $isInstance, $script = null) {
			$smarty = new vtigerCRM_Smarty ();
			$tasksView = ViewManager::getInstance ($adb)->fetchView ('Calendar', 'PENDING TASK');
			if (empty($tasksView)) {
				$tasksView = DataViewUtils::fetchDefaultView ($adb, 'Calendar');
			}
			if (empty ($tasksView)) {
				throw new Exception ('La vista solicitada no se encuentra registrada');
			}
			
			$queryGenerator   = new QueryGenerator ('Calendar', $current_user);
			$queryGenerator->initForCustomViewById ($tasksView->getId());
			$queryGenerator->getQuery ();
			$conditionalWhere     = $queryGenerator->getConditionalWhere ();
			$tasksViewPermissions = DataViewUtils::fetchViewPermissions ($adb, $tasksView, $current_user);
			if ((!is_array ($tasksViewPermissions)) || (!in_array (DataViewUtils::PERMISSION_CAN_USE, $tasksViewPermissions))) {
				throw new Exception ('Acceso denegado');
			}
			$availableModules    = ModuleManager::getInstance ($adb)->fetchModulesByType (Module::TYPE_USER, true, $isInstance);
			$tasksViewData       = DataViewUtils::fetchViewData ($adb, $tasksView, $current_user, 1, null, $conditionalWhere, null, $script);
			$availableTaskView   = DataViewUtils::fetchAvailableViews ($adb, 'Calendar', $current_user);
			self::$transferValue = $tasksViewData ['totalNewTask'];
			$quickView           = array ();
            $totalRecords        = count($tasksViewData['records']);
            for ($k = 0; $k < $totalRecords; $k++) {
                $tasksViewData['records'][$k]['how_to'] =  HowToHelper::hasHowTo ($adb, 'Calendar', $tasksViewData ['records'][$k]['crmid'], 'DetailView_Task');
            }
			/* Suspendido por el momento
			foreach ($availableTaskView as $taskView) {
				if (in_array ($taskView->getName (), array_values (self::TASK_VIEW_STATUS))) {
					$key = array_search ($taskView->getName (), self::TASK_VIEW_STATUS);
					$quickView[ $key ] = $taskView->getId ();
				}
			}
			*/
			$availableSystemUsers =  UserManager::getInstance ($adb, null)->fetchUsers ();
			if ($current_user->is_admin == 'off') {
				$relateUserIds = DataViewUtils::getRelatedUserIds ($adb, $current_user);
				$totalUser = count ($availableSystemUsers);
				for ($k =0; $k < $totalUser; $k++) {
					if (!in_array ($availableSystemUsers[$k]->getId (), $relateUserIds)) {
						unset ($availableSystemUsers[$k]);
					}
				}
			}
			$smarty->assign ('APP', $app_strings);
			$smarty->assign ('ACTIVE_APPLICATIONS', null);
			$smarty->assign ('CAN_CREATE_RECORDS', $canCreateRecords);
			$smarty->assign ('AVAILABLE_TASKS_VIEWS', DataViewUtils::fetchAvailableViews ($adb, 'Calendar', $current_user));
			$smarty->assign ('AVAILABLE_SYSTEM_USERS', $availableSystemUsers);
			$smarty->assign ('AVAILABLE_MODULES', $availableModules);
			$smarty->assign ('AVAILABLE_USERS', DataViewUtils::getAvailableUser ($adb, $current_user));
			$smarty->assign ('AVAILABLE_GROUPS', DataViewUtils::getAvailableGroups($adb));
			$smarty->assign ('CATEGORIES', DataViewUtils::getAvailableTaskCategories ($adb, $current_user->id));
			$smarty->assign ('FLMODULE', $script);
			$smarty->assign ('KANBAN_LIST', null);
			$smarty->assign ('MODULE', 'Calendar');
			$smarty->assign ('QUICK_VIEW', (count ($quickView)) ? $quickView : null);
			$smarty->assign ('RELATED_MODULE', $script);
			$smarty->assign ('HAS_RELATED', ((count (explode (';',$script))) > 1) || empty ($script));
			$smarty->assign ('RETURN_ACTION', 'index');
			$smarty->assign ('RETURN_MODULE', 'Home');
			$smarty->assign ('RELATED_MODULES', DataViewUtils::getRelatedModule($adb));
			$smarty->assign ('TAB_GROUP', 'ACTIVITY');
			$smarty->assign ('TAB_HOME_ID', rand (1000, 150000));
			$smarty->assign ('TASKS_VIEW', $tasksView);
			$smarty->assign ('TASKS_VIEW_DATA', $tasksViewData);
			$smarty->assign ('TASKS_VIEW_PERMISSIONS', $tasksViewPermissions);
			$smarty->assign ('TOTAL_NEW_TASKS', self::$transferValue);
			$smarty->assign ('TAB_NAME',self::$tabName);
			return $smarty->fetch ('Home/TabsContents/Tasks.tpl');
		}

		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 *
		 * @return string|null
		 * @throws Exception
		 * @throws SmartyException
		 */
		private static function getBulletinBoardTab ($adb, $current_user) {
			$newsData = AdQueueHelper::getInstance()->fetchNewsData (date_create (), $current_user->user_name, $adb->dbName);
			if (empty($newsData)) {
				return null;
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('NEWS_CATEGORIES', AdQueue::AD_QUEUE_CATEGORIES);
			$smarty->assign ('NEWS_DATA', $newsData);
			return $smarty->fetch ('Home/TabsContents/News.tpl');
		}

		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 * @param boolean $isInstance
		 *
		 * @return string|null
		 * @throws Exception
		 * @throws SmartyException
		 */
		private static function getControlPanelTab ($adb, $current_user, $isInstance) {
			$allBoxScore = null;
			$type        = null;
			$userCharts = GraphicManager::getInstance ($adb)->fetchAllFavoriteGraphics ($current_user->id);

			// Tab panel de control, Box Score
			$myBoxScore = BoxScoreManager::getInstance ($adb)->fetchAllFavorites ($current_user->id);

			if (count ($userCharts) || (count ($myBoxScore))) {
				$categories = GraphUtils::getCategories ();
				foreach ($categories as $key => $category) {
					$categoryCatalg [ $key ] = array (
						'app_code' => $key,
						'app_name' => $category,
					);
				}
			}
			$smarty = new vtigerCRM_Smarty ();
			if (count ($userCharts)) {
				$favoriteCharts = array_column ($userCharts, 'graficoid');
				$objectDate     = new DateTime();
				$dateTo         = $objectDate->format ('Y-m-d');
				$objectDate     = new DateTime();
				$objectDate->modify ('-3 month');
				$dateFrom       = $objectDate->format ('Y-m-d');
				$dateFilter = array (
					'dateFrom' => $dateFrom,
					'dateTo'   => $dateTo,
				);
				// Obtener los gráficos básicos
				GraphicManager::getInstance($adb)->getBasicGraphics ($graphs, $isInstance, $categories, $dateFilter, $favoriteCharts);
				$graphsUtils = JSGraphicUtils::getInstance ($adb);

				$smarty->register_function ('loadGraphic', array(&$graphsUtils, 'fetchGoogleChartJs'));
				$smarty->assign ('ACTIVE_TAB', '');
				$smarty->assign ('APPLICATIONS', $categoryCatalg);
				$smarty->assign ('COLORS', array ('#2ecc71', '#e74c3c', '#f39c12', '#3fcfbb', '#626f70', '#8f44ad'));
				$smarty->assign ('FAVORITES', $favoriteCharts);
				$smarty->assign ('GRAPHS', $graphs);
				$smarty->assign ('IS_ADMIN', is_admin ($current_user));
				$smarty->assign ('IS_FAVORITES', true);
				$smarty->assign ('OPERATIONS', GraphUtils::getDefinedOperations ());
			} else {
				$graphs = null;
			}
			if (count ($myBoxScore)) {
				require_once ('modules/indicatorspanel/indicatorspanel.php');
				require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');

				$mod_strings      = return_module_language('es_es','indicatorspanel');
				$monthSearch      = date ('m');
				$favoriteBoxScore = array_column ($myBoxScore, 'boxscorename');
				$view             = 'Month';
				$n                = count ($categoryCatalg);
				if ($n > 0 && (!empty($categoryCatalg))) {
					$categoryCode = array_column ($categoryCatalg, 'app_code');
					$codeFirst    = $categoryCode[0];
					for ($i = 0; $i < $n; $i++) {
						$code = $categoryCode[ $i ];
						if ($code != 'all') {
							$bsDefault = IndicatorsPanelHelper::getIndicatorDefault ($adb, $code, $view);
							$record    = $bsDefault['boxscoreid'];
							$boxScore  = IndicatorsPanel::getInstance ($adb, $monthSearch, $record, null, null);
							$boxScore->loadData ($record, $monthSearch, $type, 0, $favoriteBoxScore);
							$blocks               = $boxScore->getBlocks ($record, $type);
							$calculations         = null;
							$allBoxScore[ $code ] = array ($boxScore, $blocks, $calculations, $record);
						}
					}
				}
				$categoryCatalg = (array ('all' => array ('config_applicationsid' => 0, 'app_code' => 'all', 'app_name' => $mod_strings['ALL_APLICATIONS'])) + $categoryCatalg);
				$smarty->assign ('APPLICATIONS', $categoryCatalg);
				$smarty->assign ('FAVORITES', $favoriteBoxScore);
				$smarty->assign ('IS_HOME', true);
				$smarty->assign ('MODSTRING', $mod_strings);
				$smarty->assign ('MODULE', 'indicatorspanel');
				$smarty->assign ('THEME', 'centaurus');
				$smarty->assign ('TAB_ACTIVE', null);
				$smarty->assign ('APPCODE', 'all');
				//assigning variables to editview boxscore
				$smarty->assign ('ALL_BOX_SCORE', $allBoxScore);
				$smarty->assign ('MONTH_SEARCH', $monthSearch);
				$smarty->assign ('VIEW_SEARCH', $view);
				$smarty->assign ('CODE_FIRST', $codeFirst);
				$smarty->assign ('YEAR_DATE', date ('Y'));
			}
			if (!empty ($graphs) || !empty($allBoxScore)) {
				return $smarty->fetch ('Home/TabsContents/graphicTab.tpl');
			}
			return null;
		}
		
		private static function getDailyMatrix ($adb, $current_user, $canCreateRecords, $app_strings, $isInstance, $script = null) {
			global $mod_strings;
			try {
				$tasksView = DataViewUtils::fetchView ($adb, 'Calendar', 'ALL');
				if (empty ($tasksView)) {
					throw new Exception ('La vista solicitada no se encuentra registrada');
				}
				$tasksViewPermissions = DataViewUtils::fetchViewPermissions ($adb, $tasksView, $current_user);
				if ((!is_array ($tasksViewPermissions)) || (!in_array (DataViewUtils::PERMISSION_CAN_USE, $tasksViewPermissions))) {
					throw new Exception ('Acceso denegado');
				}
				$users             = array ($current_user->id);
				$tasksData         = DataViewUtils::fetchTaskToMatrix ($adb, null, $users);
				$activitiesRecords = array();
				$priorityTranslate = array ('Alto' => 'High', 'Bajo' => 'Low');
				$totalRecords      = count ($tasksData);
				for ($k = 0; $k < $totalRecords; $k++) {
					$tasksViewData ['records'][ $k ]['invitee']        = DataViewUtils::fetchInviteesByActivity ($adb, $tasksData[ $k ]->getActivityId (), $current_user->id);
					$tasksViewData ['records'][ $k ]['str_date_start'] = $tasksData[ $k ]->getStartDate ();
					$tasksViewData ['records'][ $k ]['str_due_date']   = $tasksData[ $k ]->getDueDate ();
					
					$thisPriority = $tasksData[ $k ]->getPriority ();
					$tasksViewData ['records'][ $k ]['taskpriority']   = ((!empty ($thisPriority)) && (in_array($thisPriority, array ('Alto', 'Bajo')))) ? $thisPriority : 'Bajo';
					$tasksViewData ['records'][ $k ]['importance']     = $tasksData[ $k ]->getImportance ();
					$tasksViewData ['records'][ $k ]['progress']       = $tasksData[ $k ]->getProgress ();
					$tasksViewData ['records'][ $k ]['related_id']     = $tasksData[ $k ]->getRelatedId ();
					$tasksViewData ['records'][ $k ]['modulename']     = $tasksData[ $k ]->getModuleName ();
					$tasksViewData ['records'][ $k ]['tab_name']       = $tasksData[ $k ]->getRelatedModule ();
					$tasksViewData ['records'][ $k ]['estimated_time'] = $tasksData[ $k ]->getTimeDuration ();
					$tasksViewData ['records'][ $k ]['subject']        = $tasksData[ $k ]->getSubject ();
					$tasksViewData ['records'][ $k ]['planned_task']   = $tasksData[ $k ]->getActivityCondition ();
					$tasksViewData ['records'][ $k ]['description']    = $tasksData[ $k ]->getDescription ();
					
					$quadrant    = $priorityTranslate[$tasksViewData ['records'][ $k ]['taskpriority']] . '-' . $tasksData[ $k ]->getImportance ();
					$parameters  = "{$tasksViewData ['records'][ $k ]['activitytype']};{$priorityTranslate[$tasksViewData ['records'][ $k ]['taskpriority']]};{$tasksViewData ['records'][ $k ]['importance']};{$tasksData[ $k ]->getActivityId ()}";
					$tasksViewData ['records'][ $k ]['parameters'] = $parameters;
					$activitiesRecords[ $quadrant ][] = $tasksViewData ['records'][ $k ];
				}
				
				$quadrants = array ('High-HIGH', 'Low-HIGH', 'High-LOW', 'Low-LOW');
				$totalsQuadrants = array ();
				$totalsEstimated = array ();
				$totalTime       = 0;
				foreach ($quadrants as $quadrant) {
					$totalsQuadrants[] = count ($activitiesRecords[ $quadrant ]);
					$totalTask = 0;
					foreach ($activitiesRecords[ $quadrant ] as $taskItem) {
						if (empty ($taskItem ['estimated_time'])) {
							continue;
						}
						$totalTime += floatval ($taskItem ['estimated_time']);
						$totalTask++;
					}
					$totalsEstimated [] = $totalTask;
				}
				$totalsQuadrants[] = array_sum ($totalsQuadrants);
				$totalsEstimated[] = array_sum ($totalsEstimated);
				$totalHoursWorked  = DailyReportUtils::getTotalHoursWorked ($adb, null, $users);
				$reportTime        = DailyReportUtils::getActivityReportTotalTime ($adb, null, $users);
				$extraHours        = ($reportTime - $totalHoursWorked);
				$isOverTime        = false;
				$barMax            = 100;
				$barWidth          = 1;
				if (!empty($reportTime) && !empty($totalTime)) {
					$barWidth   = floor ((($reportTime * 100)/$totalTime));
					if (($barWidth > 100)) {
						$barMax     = $barWidth;
						$barWidth   = 100;
						$isOverTime = true;
						$overTime   = ($barMax - 100);
					}
				}
				$reportedDays      = DailyReportUtils::fetchDailyReportDateByUser ($adb, $current_user->id);
				$today             = date('Y-m-d');
				$yesterday         = date('Y-m-d',strtotime('-1 days'));
				$reportToDay       = "{$today}@{$current_user->id}";
				$reportToYesterday = "{$yesterday}@{$current_user->id}";
				$smarty = new vtigerCRM_Smarty ();
				$smarty->assign ('AVAILABLE_USERS', DataViewUtils::getAvailableUserAndAvatar ($adb, $current_user));
				$smarty->assign ('ADITIONAL_INFO',DailyReportUtils::fetchAdditionalInformation ($adb, null, $users));
				$smarty->assign ('ACHIEVEMENTS',DailyReportUtils::fetchAchievements ($adb, null, $users));
				$smarty->assign ('QUADRANTS', $quadrants);
				$smarty->assign ('TAB_HOME_ID', rand (1000, 10000));
				$smarty->assign ('TASKS_VIEW_DATA', $activitiesRecords);
				$smarty->assign ('TOTALS_QUADRANTS', $totalsQuadrants);
				$smarty->assign ('TOTALS_ESTIMATED', $totalsEstimated);
				$smarty->assign ('TOTAL_TIMES', $totalTime);
				$smarty->assign ('USER_NAME', ($current_user->first_name . ' ' . $current_user->last_name));
				$smarty->assign ('USERS', $users);
				$smarty->assign ('USER_ID', $current_user->id);
				$smarty->assign ('OVER_TIME', (isset ($overTime)) ? $overTime : 0);
				$smarty->assign ('PERIOD_DATES', NotificationPeriodUtils::getAvailablePeriods ());
				$smarty->assign ('PROGRESS_BAR_MAX', $barMax);
				$smarty->assign ('PROGRESS_BAR_WIDTH', $barWidth);
				$smarty->assign ('PROGRESS_BAR_OVER', $isOverTime);
				$smarty->assign ('MOD', $mod_strings);
				$smarty->assign ('REPORTED_HOURS', $reportTime);
				$smarty->assign ('REPORTED_DAYS', (is_array ($reportedDays)) ? join (';', $reportedDays) : null);
				$smarty->assign ('WORKED_HOURS', $totalHoursWorked);
				$smarty->assign ('EXTRA_HOURS', $extraHours);
				$smarty->assign ('PERIOD_SELECTED', 'today');
				$smarty->assign ('TODAY', $today);
				$smarty->assign ('YESTERDAY', $yesterday);
				$smarty->assign ('REPORT_TODAY', base64_encode ($reportToDay));
				$smarty->assign ('REPORT_YESTERDAY', base64_encode ($reportToYesterday));
				return $smarty->fetch ('Home/TabsContents/DailyMatriz.tpl');
			} catch (Exception $e) {
				$code   = $e->getCode ();
			}
		}
		
		/**
		 * @param $adb
		 *
		 * @return integer|null
		 */
		private static function getHomeView ($adb) {
			$result = $adb->pquery ('SELECT cvid FROM vtiger_customview WHERE viewname=?', array ('HOME_VIEW'));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['cvid'];
		}

		/**
		 * @param string $platPrincipal
		 *
		 * @return null
		 * @throws Exception
		 */
		private static function getMaterialsTab ($platPrincipal, $isInstance, $site_URL) {
			$fu    = FolderUtils::getInstance($platPrincipal);
			$menu  = $fu->getDocumentTabMenu ();
			$files = $fu->getLastDocuments ();
			if (empty($files)) {
				return null;
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign('DATA_MULTIPLIER', 10);
			$smarty->assign ('DEFAULT_PHOTO', FolderInterface::FILE_DEFAULT_IMAGE);
			$smarty->assign ('FILES', $files);
			$smarty->assign ('SITE_URL', $site_URL);
			$smarty->assign ('IS_INSTANCE', $isInstance);
			$smarty->assign ('MENU', $menu);
			$smarty->assign ('TAB_NAME', self::$tabName);
			return $smarty->fetch ('Home/TabsContents/Documents.tpl');
		}

		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 * @param boolean $isInstance
		 *
		 * @return string
		 * @throws Exception
		 * @throws SmartyException
		 */
		private static function getMessagesTab ($adb, $current_user, $isInstance) {
			$smarty           = new vtigerCRM_Smarty ();
			$availableModules = ModuleManager::getInstance ($adb)->fetchModulesByType (Module::TYPE_USER, true, $isInstance);
			usort (
				$availableModules,
				function (Module $moduleA, Module $moduleB) {
					return strcmp ($moduleA->getLabel (), $moduleB->getLabel ());
				}
			);
			$mailAccounts = WebmailUtils::fetchMailAccounts ($adb, $current_user->id, $isInstance);

			$filters = array (
				'from'   => date_format (date_sub (date_create (), date_interval_create_from_date_string ('7 days')), 'Y-m-d'),
				'status' => WebmailUtils::STATUS_ALL,
				'to'     => date_format (date_create (), 'Y-m-d'),
			);
			$emailsData          = WebmailUtils::fetchEmailsData ($adb, $current_user->id, $filters);
			self::$transferValue = WebmailUtils::getMailCountByStatus ($adb, $current_user->id);
			$smarty->assign ('AVAILABLE_MODULES', $availableModules);
			$smarty->assign ('MAIL_ACCOUNTS', (count ($mailAccounts)) ? $mailAccounts : null);
			$smarty->assign ('EMAILS_DATA', $emailsData);
			$smarty->assign ('TOTAL_UNREAD', self::$transferValue);
			return $smarty->fetch ('Home/TabsContents/Messages.tpl');
		}

		private static function getProcessCasesTab ($adb, $app_strings) {
			global $current_user, $mod_strings;
			$periodDates               = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ('thismonth');
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
			/** quality process */
			$availableProcess = ProcessHelper::fetchProcess ($adb);
			$processId        = null;
			if (!empty ($availableProcess) && isset($availableProcess[0]['processid'])) {
				$processId = $availableProcess[0]['processid'];
			}
			
			$processType         = ProcessHelper::fetchProcessTypes ($adb);
			$userList            = ($current_user->is_admin != 'on') ? array ($current_user->id) : null;
			$exeAccordingQuality = ProcessHelper::fetchExeAccordingQuality ($adb, $processId, $periodDates, $userList);
			$caseNumbers         = array_column ($exeAccordingQuality, 'case_number');
			$caseIds			 = array_column ($exeAccordingQuality, 'process_casesid');
			$stepName            = array_column ($exeAccordingQuality, 'step_name');
			$caseNumbers         = array_combine ($caseNumbers, $caseIds);
			$caseNumbers         = array_unique ($caseNumbers);
			$stepName            = array_unique ($stepName);
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ADB', $adb);
			$smarty->assign ('AVAILABLE_PROCESS', $availableProcess);
			$smarty->assign ('AVAILABLE_USERS', DataViewUtils::getAvailableUserAndAvatar ($adb, $current_user));
			$smarty->assign ('CASE_NUMBERS', $caseNumbers);
			$smarty->assign ('CONTROL_BANDS', ProcessCasesUtils::getControlBands ());
			$smarty->assign ('FINISHED_OUT_AVERAGE', $finishedOutOfAverage);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('PERIOD_DATES', NotificationPeriodUtils::getAvailablePeriods ());
			$smarty->assign ('PERIOD_SELECTED', 'thismonth');
			$smarty->assign ('PROCESS_ACCORDING_QUALITY', $exeAccordingQuality);
			$smarty->assign ('PROCESS_FINISHED', $behaviorOfProcessFinished);
			$smarty->assign ('PROCESS_ID', $processId);
			$smarty->assign ('PROCESS_TYPES', $processType);
			$smarty->assign ('PROCESS_UMFINISHED', $behaviorOfProcessUnfinished);
			$smarty->assign ('RESUMEN_PROCESSES', $resumenProcesses);
			$smarty->assign ('STEPS_COLOR_QUALITY', ProcessStepInterface::SCORING_MATRIX);
			$smarty->assign ('STEPS_NAME', $stepName);
			$smarty->assign ('TOTAL_CASE', $totalCase);
			$smarty->assign ('TOTAL_CASE_FINISHED', $totalCaseFinished);
			$smarty->assign ('TOTAL_CASE_UNFINISHED', $totalCaseUnfinished);
			$smarty->assign ('TOTAL_STEPS_NAME', count ($stepName));
			$smarty->assign ('UNFINISHED_OUT_AVERAGE', $unfinishedOutOfAverage);
			$smarty->assign ('USER_IDS', $current_user->id);
			$smarty->assign ('USER_NAME', ($current_user->first_name . ' ' . $current_user->last_name));
			return $smarty->fetch ('Home/TabsContents/ProcessPanel.tpl');
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return string|null
		 */
		private static function getProjectTab ($adb) {
			$isHomeTab  = true;
			$proyectTab = null;
			$moduleTab  = 'proyectos';
			$homeViewId = self::getHomeView ($adb);
			if (empty ($homeViewId)) {
				return null;
			}
			require_once ('modules/Home/ListView.php');
			return $proyectTab;
		}
		
		private static function getRecordTab ($adb, $view, $moduleTab) {
			if (empty ($view)) {
				return null;
			}
			$isHomeTab  = true;
			$proyectTab = null;
			$homeViewId = $view;
			require 'modules/Vtiger/ListView.php';
			return $proyectTab;
		}
		
		/**
		 * @param PearDatabase $masterAdb
		 * @param Users $current_user
		 * @param boolean $isInstance
		 *
		 * @return string
		 * @throws Exception
		 */
		private static function getTrainingTab ($masterAdb, $current_user, $isInstance, $adb) {
			global $current_user;
			self::$transferValue = CourseManager::getInstance ($masterAdb)->getTotalNewCourseByUser ($current_user->id);
			$courseData = CoursesHelper::fetchCoursesByTargetAudience ($masterAdb, $isInstance, $adb, $current_user->id);
			$categories = $courseData['category'];
			unset($courseData['category']);
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('COURSES_SERIES', $courseData);
			$smarty->assign ('CATEGORIES', $categories);
			$smarty->assign ('IS_INSTANCE', $isInstance);
			$smarty->assign ('COURSES_STATUS_COLOR', CoursesInterface::COURSE_STATUS_COLOR);
			$smarty->assign ('COURSES_STATUS_TITLE', CoursesInterface::COURSE_STATUS);
			$smarty->assign('TOTAL_NEW', self::$transferValue);
			$smarty->assign ('TAB_NAME', self::$tabName);
			return $smarty->fetch ('Home/TabsContents/Courses.tpl');
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $operationMode
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public function checkAvailableOperatingMode ($adb, $operationMode) {
			if (empty ($operationMode) || (!$adb instanceof PearDatabase)) {
				return false;
			} else if ($operationMode == 'MANAGEMENT_MODE') {
				return true;
			}
			
			$dummy  = explode ('_', $adb->dbName);
			$result = $this->masterAdb->pquery (
				'SELECT status FROM vtiger_instance2operating_modes WHERE instance_code = ? AND operatingmodename = ?',
				array ($dummy[2], $operationMode)
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				$status = $this->masterAdb->query_result ($result, 0, 'status');
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return isset ($status) && ($status == 'ENABLED');
		}
		
		/**
		 * @return null|OperatingModes[]
		 * @throws Exception
		 */
		public function fetchAvailableOperatingModes () {
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_operating_modes WHERE status=?', array ('ENABLED'));
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$operatingModes[] = OperatingModes::getInstance()
						->setId ($row ['operatingmodesid'])
						->setAttributes (json_decode ($row ['attributes'], true))
						->setLabel ($row ['label'])
						->setOperatingModeName ($row ['operatingmodename'])
						->setStatus ($row ['status'])
						->setTabTabs ($this->fetchTabTabs($row ['operatingmodename'], 'Home'));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($operatingModes)) ? $operatingModes : null;
		}

		/**
		 * @param string $name
		 *
		 * @return ModesContent|null
		 * @throws Exception
		 */
		public function fetchModesContent ($name) {
			if (empty($name)) {
				return null;
			}
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_modes_content WHERE name=?', array ($name));
			$numRows = $this->masterAdb->num_rows ($result);
			
			if ($numRows > 0) {
				$row = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$modesContent = ModesContent::getInstance()
					->setAction ($row ['action'])
					->setAttributes ($row ['attributes'])
					->setId ($row ['modescontentid'])
					->setLabel ($row ['label'])
					->setName ($row ['name'])
					->setScript ($row ['script']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$returnValue = (isset($modesContent)) ? $modesContent : null;
			return $returnValue;
		}

		/**
		 * @param string $operatingMode
		 * @param string $moduleName
		 * @param boolean $headerOnly
		 *
		 * @return OperatingModes|null
		 * @throws Exception
		 */
		public function fetchOperatingMode ($operatingMode, $moduleName, $headerOnly = false) {
			if (empty($operatingMode) || empty ($moduleName)) {
				return null;
			}

			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_operating_modes WHERE operatingmodename=? AND status=?', array ($operatingMode, 'ENABLED'));
			$numRows = $this->masterAdb->num_rows ($result);
			
			if ($numRows > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$tabTabs = (!$headerOnly) ? $this->fetchTabTabs($row ['operatingmodename'], $moduleName) : null;
					
					$operatingModes = OperatingModes::getInstance()
						->setId ($row ['operatingmodesid'])
						->setAttributes (json_decode ($row ['attributes'], true))
						->setLabel ($row ['label'])
						->setOperatingModeName ($row ['operatingmodename'])
						->setStatus ($row ['status'])
						->setTabTabs ($tabTabs);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$returnValue = (isset ($operatingModes)) ? $operatingModes : null;
			return $returnValue;
		}

		/**
		 * @param string $operationMode
		 * @param string $moduleName
		 *
		 * @return TabTabs[]|null
		 * @throws Exception
		 */
		public function fetchTabTabs ($operationMode, $moduleName) {
			if (empty($operationMode) || empty($moduleName)) {
				return null;
			}
			$result = $this->masterAdb->pquery (
				'SELECT vtiger_tab_tabs.*, vtiger_tab.tabid 
					  FROM vtiger_tab_tabs 
					  INNER JOIN vtiger_tab ON vtiger_tab.name = vtiger_tab_tabs.tabname AND vtiger_tab.presence=?  
					  WHERE operatingmodename=? AND
					  		tabname=? AND 
					  		vtiger_tab_tabs.presence=?
					  ORDER BY sequence',
				array (0, $operationMode, $moduleName, 1)
			);
			$numRows = $this->masterAdb->num_rows ($result);
			
			if ($numRows > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$modesContent = $this->fetchModesContent ($row ['modescontentname']);
					
					$tabTabs [] = TabTabs::getInstance()
						->setId ($row ['tabtabsid'])
						->setIconPath ($row ['iconpath'])
						->setModesContent($modesContent)
						->setModuleName ($row ['tabname'])
						->setOperatingModeName ($row ['operatingmodename'])
						->setPresence ($row ['presence'])
						->setSequence ($row ['sequence'])
						->setTabId ($row ['tabid']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$returnValue = (isset ($tabTabs)) ? $tabTabs : null;
			return $returnValue;
		}

		/**
		 * @param ModesContent $modesContent
		 * @param array $arguments
		 */
		public function fillTabsContent ($modesContent, $arguments) {
			if (empty ($modesContent->getAction ())) {
				$modesContent->setBufferOut(null);
				return;
			}
			self::$transferValue = 0;
			self::$tabName       = $modesContent->getName ();
			$arguments ['masterAdb'] = $this->masterAdb;
			$parameters              = array ();
			$attributes              = explode (',', $modesContent->getAttributes());
			foreach ($attributes as $key => $value) {
				if ($value == 'script') {
					$parameters [] = $modesContent->getScript ();
				} else {
					$parameters [] = $arguments [ $value ];
				}
			}
			
			$returnFunction = call_user_func_array ("{$modesContent->getAction ()}", $parameters);
			$modesContent->setBufferOut ($returnFunction);
			$modesContent->setValue (self::$transferValue);
		}
		
		/**
		 * @param array $tabRecords
		 * @param OperatingModes $operatingMode
		 *
		 * @throws Exception
		 */
		public function getTabsRecords ($tabRecords, $operatingMode) {
			if (empty ($tabRecords)) {
				return;
			}
			
			foreach ($operatingMode->getTabTabs () as $tabTab) {
				if ($tabTab->getModesContent()->getName () == 'RECORDS') {
					$theTab = $tabTab->duplicate ();
					break;
				}
			}
			$newTabs = array();
			$sequence = 1;
			foreach ($tabRecords as $moduleName => $cvId) {
				$label = getTabIdLabelByName ($moduleName);
				$newModesContent = clone $theTab->getModesContent ();
				$newModesContent->setLabel ($label);
				$newModesContent->setName ($moduleName);
				$theTab->setId ($cvId);
				$theTab->setSequence ($sequence);
				$theTab->setModesContent ($newModesContent);
				$newTabs [] = $theTab;
				$newModesContent= null;
				unset($newModesContent);
				$theTab = clone $theTab;
				$sequence++;
			}
			$operatingMode->setTabTabs (null);
			$operatingMode->setTabTabs ($newTabs);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return string
		 * @throws Exception
		 */
		public function getDefaultOperatingModeUser ($adb, $userId) {
			if (empty($userId)) {
				return 'FORMATIVE_MODE';
			}
			$result = $adb->pquery('SELECT default_operating, defaulthometab  FROM vtiger_users WHERE id=?', array ($userId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row = $this->masterAdb->fetchByAssoc ($result, -1, false);
				DatabaseUtils::closeResult ($result);
				$result = null;
				return array ($row ['default_operating'], $row ['defaulthometab']);
			}
			return array ('FORMATIVE_MODE', 'TRAINING');
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param integer $defaultOperating
		 *
		 * @return null|OperatingModes
		 * @throws Exception
		 */
		public function updateUserProfile (PearDatabase $adb, $userId, $defaultOperating) {
			if (empty ($defaultOperating)) {
				return null;
			}
			$defaultTab = UserInterface::HOME_TABS[ $defaultOperating ][0];
			$adb->pquery (
				'UPDATE vtiger_users SET default_operating=?, defaulthometab=? WHERE id=?',
				array ($defaultOperating, $defaultTab, $userId)
			);
			return $this->fetchOperatingMode ($defaultOperating, 'Home', true);
		}

		/**
		 * @return OperatingModesHelper
		 */
		public static function getInstance () {
			return new self ();
		}

	}
