<?php
	require_once('modules/Reports/Reports.php');
	require_once('modules/Reports/ReportRun.php');
	require_once('include/Zend/Json.php');
	require_once('include/database/PearDatabase.php');

	class VTScheduledReport extends Reports {

	public $db;
	public $user;
	public $scheduledTime;
	public $isScheduled = false;
	public $scheduledInterval = null;
	public $scheduledFormat = null;
	public $scheduledRecipients = null;
	static public $scheduledHourly = 1;
	static public $scheduledDaily = 2;
	static public $scheduledWeekly = 3;
	static public $scheduledBiweekly = 4;
	static public $scheduledMonthly = 5;
	static public $scheduledAnnually = 6;

	/**
	 * VTScheduledReport constructor.

	 * @param string $adb
	 * @param $user
	 * @param string $reportid
	 */
	public function __construct($adb, $user, $reportid = '') {
		$this->db	= $adb;
		$this->user = $user;
		$this->id	= $reportid;
		parent::__construct($reportid);
	}

	public function getReportScheduleInfo() {
		global $adb;

		if(!empty($this->id)) {
			$cachedInfo = VTCacheUtils::lookupReport_ScheduledInfo($this->user->id, $this->id);

			if($cachedInfo == false) {
				$result = $adb->pquery('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array($this->id));

				if($adb->num_rows($result) > 0) {
					$reportScheduleInfo = $adb->raw_query_result_rowdata($result, 0);

					/** @noinspection PhpUndefinedClassInspection */
					$scheduledInterval = (!empty($reportScheduleInfo['schedule'])) ? Zend_Json::decode($reportScheduleInfo['schedule']) : array();
					/** @noinspection PhpUndefinedClassInspection */
					$scheduledRecipients = (!empty($reportScheduleInfo['recipients'])) ? Zend_Json::decode($reportScheduleInfo['recipients']) : array();

					VTCacheUtils::updateReport_ScheduledInfo($this->user->id, $this->id, true, $reportScheduleInfo['format'], $scheduledInterval, $scheduledRecipients, $reportScheduleInfo['next_trigger_time']);

					$cachedInfo = VTCacheUtils::lookupReport_ScheduledInfo($this->user->id, $this->id);
				}
			}
			if($cachedInfo) {
				$this->isScheduled			= $cachedInfo['isScheduled'];
				$this->scheduledFormat		= $cachedInfo['scheduledFormat'];
				$this->scheduledInterval	= $cachedInfo['scheduledInterval'];
				$this->scheduledRecipients	= $cachedInfo['scheduledRecipients'];
				$this->scheduledTime		= $cachedInfo['scheduledTime'];
				return true;
			}
		}
		return false;
	}

	public function getRecipientEmails() {
		$recipientsInfo = $this->scheduledRecipients;
		$recipientsList = array();
		$recipientsList = self::auxGetRecipientEmailsPartOne($recipientsInfo, $recipientsList);
		$recipientsEmails = array();
		if(!empty($recipientsList) && count($recipientsList) > 0) {
			foreach($recipientsList as $userId) {
				$userName = getUserFullName($userId);
				$userEmail = getUserEmail($userId);
				if(!in_array($userEmail, $recipientsEmails)) {
					$recipientsEmails[$userName] = $userEmail;
				}
			}
		}
		return $recipientsEmails;
	}

	public function auxGetRecipientEmailsPartOne($auxRecipientsInfo, $auxRecipientsList) {
		if(!empty($auxRecipientsInfo)) {
			if(!empty($auxRecipientsInfo['users'])) {
				$auxRecipientsList = array_merge($auxRecipientsList, $auxRecipientsInfo['users']);
			}

			if(!empty($auxRecipientsInfo['roles'])) {
				foreach($auxRecipientsInfo['roles'] as $roleId) {
					$roleUsers = getRoleUsers($roleId);
					foreach($roleUsers as $userId => $userName) {
						array_push($auxRecipientsList, $userId);
					}
				}
			}

			if(!empty($auxRecipientsInfo['rs'])) {
				foreach($auxRecipientsInfo['rs'] as $roleId) {
					$users = getRoleAndSubordinateUsers($roleId);
					foreach($users as $userId => $userName) {
						array_push($auxRecipientsList, $userId);
					}
				}
			}
			$auxRecipientsList = self::auxGetRecipientEmailsPartTwo($auxRecipientsList);
		}
		return $auxRecipientsList;
	}

	public function auxGetRecipientEmailsPartTwo($auxRecipientsList) {
		if(!empty($auxRecipientsInfo['groups'])) {
			require_once('include/utils/GetGroupUsers.php');
			foreach($auxRecipientsInfo['groups'] as $groupId) {
				$userGroups = new GetGroupUsers();
				$userGroups->getAllUsersInGroup($groupId);
				$auxRecipientsList = array_merge($auxRecipientsList, $userGroups->group_users);
			}
		}
		return $auxRecipientsList;
	}

	public function sendEmail() {
		global $currentModule;
		require_once 'vtlib/Vtiger/Mailer.php';

		$vtigerMailer = new Vtiger_Mailer();

		$recipientEmails = $this->getRecipientEmails();
		foreach($recipientEmails as $name => $email) {
			$vtigerMailer->AddAddress($email, $name);
		}

		$currentTime = date('Y-m-d H:i:s');
		$subject = $this->reportname .' - '. $currentTime .' ('. DateTimeField::getDBTimeZone() .')';

		$contents = getTranslatedString('LBL_AUTO_GENERATED_REPORT_EMAIL', $currentModule) .'<br/><br/>';
		$contents .= '<b>'.getTranslatedString('LBL_REPORT_NAME', $currentModule) .' :</b> '. $this->reportname .'<br/>';
		$contents .= '<b>'.getTranslatedString('LBL_DESCRIPTION', $currentModule) .' :</b><br/>'. $this->reportdescription .'<br/><br/>';

		$vtigerMailer->Subject = $subject;
		$vtigerMailer->Body    = $contents;
		$vtigerMailer->ContentType = 'text/html';

		$baseFileName = preg_replace('/[^a-zA-Z0-9_-\s]/', '', $this->reportname).'_'. preg_replace('/[^a-zA-Z0-9_-\s]/', '', $currentTime);

		$oReportRun = new ReportRun($this->id);
		$reportFormat = $this->scheduledFormat;
		$attachments = array();

		if($reportFormat == 'pdf' || $reportFormat == 'both') {
			$fileName = $baseFileName.'.pdf';
			$filePath = 'storage/'.$fileName;
			$attachments[$fileName] = $filePath;
			$pdf = $oReportRun->getReportPdf();
			$pdf->Output($filePath,'F');
		}
		if ($reportFormat == 'excel' || $reportFormat == 'both') {
			$fileName = $baseFileName.'.xls';
			$filePath = 'storage/'.$fileName;
			$attachments[$fileName] = $filePath;
			$oReportRun->writeReportToExcelFile($filePath);
		}

		foreach($attachments as $attachmentName => $path) {
			$vtigerMailer->AddAttachment($path, $attachmentName);
		}

		$vtigerMailer->Send(true);

		foreach($attachments as $attachmentName => $path) {
			unlink($path);
		}
	}

	public function getNextTriggerTime() {
		$scheduleInfo = $this->scheduledInterval;
		$scheduleType		= $scheduleInfo['scheduletype'];
		$scheduledDayOfWeek = $scheduleInfo['day'];
		$scheduledTime		= $scheduleInfo['time'];
		if(empty($scheduledTime)) {
			$scheduledTime = '10:00';
		} else if(stripos(':', $scheduledTime) === false) {
			$scheduledTime = $scheduledTime .':00';
		}

		if($scheduleType == self::$scheduledHourly) {
			return date('Y-m-d H:i:s',strtotime('+1 hour'));
		}
		if($scheduleType == self::$scheduledDaily) {
			return date('Y-m-d H:i:s',strtotime('+ 1 day '.$scheduledTime));
		}
		if($scheduleType == self::$scheduledWeekly) {
			$weekDays = array('0' => 'Sunday','1' => 'Monday','2' => 'Tuesday','3' => 'Wednesday','4' => 'Thursday','5' => 'Friday','6' => 'Saturday');

			if(date('w',time()) == $scheduledDayOfWeek) {
				return date('Y-m-d H:i:s',strtotime('+1 week '.$scheduledTime));
			} else {
				return date('Y-m-d H:i:s',strtotime($weekDays[$scheduledDayOfWeek].' '.$scheduledTime));
			}
		}
		if($scheduleType == self::$scheduledBiweekly) {
			$weekDays = array('0' => 'Sunday','1' => 'Monday','2' => 'Tuesday','3' => 'Wednesday','4' => 'Thursday','5' => 'Friday','6' => 'Saturday');
			if(date('w',time()) == $scheduledDayOfWeek) {
				return date('Y-m-d H:i:s',strtotime('+2 weeks '.$scheduledTime));
			} else {
				return date('Y-m-d H:i:s',strtotime($weekDays[$scheduledDayOfWeek].' '.$scheduledTime));
			}
		}
		self::auxGetNextTriggerTime($scheduleType, $scheduleInfo, $scheduledTime);
		// Return default null value
		return null;
	}

	public function auxGetNextTriggerTime($auxScheduleType, $auxScheduleInfo, $auxScheduledTime) {
		$scheduledMonth		= $auxScheduleInfo['month'];
		$scheduledDayOfMonth= $auxScheduleInfo['date'];
		if($auxScheduleType == self::$scheduledMonthly) {
			$currentTime = time();
			$currentDayOfMonth = date('j',$currentTime);

			if($scheduledDayOfMonth == $currentDayOfMonth) {
				return date('Y-m-d H:i:s',strtotime('+1 month '.$auxScheduledTime));
			} else {
				$monthInFullText = date('F',$currentTime);
				$yearFullNumberic = date('Y',$currentTime);
				if($scheduledDayOfMonth < $currentDayOfMonth) {
					$nextMonth = date('Y-m-d H:i:s',strtotime('next month'));
					$monthInFullText = date('F',strtotime($nextMonth));
			    }
				return date('Y-m-d H:i:s',strtotime($scheduledDayOfMonth.' '.$monthInFullText.' '.$yearFullNumberic.' '.$auxScheduledTime));
			}
		}
		if($auxScheduleType == self::$scheduledAnnually) {
			$months = array(
				0=>'January',
				1=>'February',
				2=>'March',
				3=>'April',
				4=>'May',
				5=>'June',
				6=>'July',
				7=>'August',
				8=>'September',
				9=>'October',
				10=>'November',
				11=>'December'
			);
			$currentTime = time();
			$currentMonth = date('n',$currentTime);
			if(($scheduledMonth + 1) == $currentMonth) {
			return date('Y-m-d H:i:s',strtotime('+1 year '.$auxScheduledTime));
			} else {
			$monthInFullText = $months[$scheduledMonth];
			$yearFullNumberic = date('Y',$currentTime);
			if(($scheduledMonth + 1) < $currentMonth) {
					$nextMonth = date('Y-m-d H:i:s',strtotime('next year'));
					$yearFullNumberic = date('Y',strtotime($nextMonth));
			}
			return date('Y-m-d H:i:s',strtotime($scheduledDayOfMonth.' '.$monthInFullText.' '.$yearFullNumberic.' '.$auxScheduledTime));
			}
		}
		return null;
	}

	public function updateNextTriggerTime() {
		global $adb;
		$nextTriggerTime = $this->getNextTriggerTime();
		$adb->pquery('UPDATE vtiger_scheduled_reports SET next_trigger_time=? WHERE reportid=?', array($nextTriggerTime, $this->id));
	}

	public static function generateRecipientOption($type, $value, $name = '') {
		$optionValue = '';
		$optionName = '';
		switch($type) {
			case 'users':
		if(empty($name)) {
					$name = getUserFullName($value);
		}
		$optionName = 'User::'.addslashes(decode_html($name));
		$optionValue = 'users::'.$value;
				break;
			case 'groups':
		if(empty($name)) {
					$groupInfo = getGroupName($value);
					$name = $groupInfo[0];
		}
		$optionName = 'Group::'.addslashes(decode_html($name));
		$optionValue = 'groups::'.$value;
				break;
			case 'roles':
		if(empty($name)) {
					$name = getRoleName ($value);
		}
		$optionName = 'Roles::'.addslashes(decode_html($name));
		$optionValue = 'roles::'.$value;
				break;
			case 'rs':
		if(empty($name)) {
					$name = getRoleName ($value);
		}
		$optionName = 'RoleAndSubordinates::'.addslashes(decode_html($name));
		$optionValue = 'rs::'.$value;
				break;
			default:
			// empty
				break;
		}
		return '<option value="'.$optionValue.'">'.$optionName.'</option>';
	}

	public function getSelectedRecipientsHtml() {
		$selectedRecipientsHTML = '';
		if(!empty($this->scheduledRecipients)) {
			foreach($this->scheduledRecipients as $recipientType => $recipients) {
				foreach($recipients as $recipientId) {
					$selectedRecipientsHTML .= self::generateRecipientOption($recipientType, $recipientId);
				}
			}
		}
		return $selectedRecipientsHTML;
	}

	public static function getAvailableUsersHtml() {
		$userDetails = getAllUserName();
		$usersHTML = '<select id="availableRecipients" name="availableRecipients" multiple size="10" class="small crmFormList">';
		foreach($userDetails as $userId => $userName) {
			$usersHTML .= self::generateRecipientOption('users', $userId, $userName);
		}
		$usersHTML .= '</select>';
		return $usersHTML;
	}

	public static function getAvailableGroupsHtml() {
		$grpDetails = getAllGroupName();
		$groupsHTML = '<select id="availableRecipients" name="availableRecipients" multiple size="10" class="small crmFormList">';
		foreach($grpDetails as $groupId => $groupName) {
			$groupsHTML .= self::generateRecipientOption('groups', $groupId, $groupName);
		}
		$groupsHTML .= '</select>';
		return $groupsHTML;
	}

	public static function getAvailableRolesHtml() {
		$roleDetails = getAllRoleDetails();
		$rolesHTML = '<select id="availableRecipients" name="availableRecipients" multiple size="10" class="small crmFormList">';
		foreach($roleDetails as $roleId => $roleInfo) {
			$rolesHTML .= self::generateRecipientOption('roles', $roleId, $roleInfo[0]);
		}
		$rolesHTML .= '</select>';
		return $rolesHTML;
	}

	public static function getAvailableRolesAndSubordinatesHtml() {
		$roleDetails = getAllRoleDetails();
		$rolesAndSubHTML = '<select id="availableRecipients" name="availableRecipients" multiple size="10" class="small crmFormList">';
		foreach($roleDetails as $roleId => $roleInfo) {
			$rolesAndSubHTML .= self::generateRecipientOption('rs', $roleId, $roleInfo[0]);
		}
		$rolesAndSubHTML .= '</select>';
		return $rolesAndSubHTML;
	}

	public static function getScheduledReports($user) {
		global $adb;
		$currentTime = date('Y-m-d H:i:s');
		$result = $adb->pquery(
			"SELECT * FROM vtiger_scheduled_reports WHERE next_trigger_time = '' || next_trigger_time <= ?",
			array($currentTime)
		);

		$scheduledReports = array();
		$noOfScheduledReports = $adb->num_rows($result);
		for($i=0; $i<$noOfScheduledReports; ++$i) {
			$reportScheduleInfo = $adb->raw_query_result_rowdata($result, $i);

			/** @noinspection PhpUndefinedClassInspection */
			$scheduledInterval = (!empty($reportScheduleInfo['schedule'])) ? Zend_Json::decode($reportScheduleInfo['schedule']) : array();
			/** @noinspection PhpUndefinedClassInspection */
			$scheduledRecipients = (!empty($reportScheduleInfo['recipients'])) ? Zend_Json::decode($reportScheduleInfo['recipients']) : array();

			$vtScheduledReport = new VTScheduledReport($adb, $user, $reportScheduleInfo['reportid']);
			$vtScheduledReport->isScheduled			= true;
			$vtScheduledReport->scheduledFormat		= $reportScheduleInfo['format'];
			$vtScheduledReport->scheduledInterval	= $scheduledInterval;
			$vtScheduledReport->scheduledRecipients = $scheduledRecipients;
			$vtScheduledReport->scheduledTime		= $reportScheduleInfo['next_trigger_time'];

			$scheduledReports[] = $vtScheduledReport;
		}
		return $scheduledReports;
	}

	public static function runScheduledReports() {
		require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
		$util = new VTWorkflowUtils();
		$adminUser = $util->adminUser();

		global $currentModule, $current_language;
		if(empty($currentModule)) {
			$currentModule = 'Reports';
		}
		if(empty($current_language)) {
			$current_language = 'en_us';
		}

		$scheduledReports = self::getScheduledReports($adminUser);
		foreach($scheduledReports as $scheduledReport) {
			$scheduledReport->sendEmail();
			$scheduledReport->updateNextTriggerTime();
		}
		$util->revertUser();
	}

	}
