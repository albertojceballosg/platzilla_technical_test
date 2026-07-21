<?php
	require_once ('include/platzilla/Exceptions/SummaryReportException.php');
	require_once ('include/platzilla/Objects/SummaryReportInterface.php');
	
	class MasterWeeklyReport implements SummaryReportInterface 	{
		/** @var Agents */
		private	$agent;
		
		/** @var integer */
		private	$agentId;
		
		/** @var string */
		private	$codeInstance;
		
		/** @var string */
		private	$dateStart;
		
		/** @var string */
		private	$description;
		
		/** @var string */
		private	$dueDate;
		
		/** @var integer */
		private	$id;
		
		/** @var string */
		private	$mailInstance;
		
		/** @var string */
		private	$status;
		
		/** @var string */
		private $reportOfStatus;
		
		/** @var string */
		private $upcomingReportId;
		
		/** @var string */
		private $weeklyReportId;
		
		/**
		 * @return Agents
		 */
		public function getAgent () {
			return $this->agent;
		}
		
		/**
		 * @return integer
		 */
		public function getAgentId () {
			return $this->agentId;
		}
		
		/**
		 * @return string
		 */
		public function getCodeInstance () {
			return $this->codeInstance;
		}
		
		/**
		 * @return string
		 */
		public function getDateStart () {
			return $this->dateStart;
		}
		
		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}
		
		/**
		 * @return string
		 */
		public function getDueDate () {
			return $this->dueDate;
		}
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return string
		 */
		public function getMailInstance () {
			return $this->mailInstance;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @return string
		 */
		public function getReportOfStatus () {
			return $this->reportOfStatus;
		}
		
		/**
		 * @return string
		 */
		public function getUpcomingReportId () {
			return $this->upcomingReportId;
		}
		
		/**
		 * @return string
		 */
		public function getWeeklyReportId () {
			return $this->weeklyReportId;
		}
		
		/**
		 * @param Agents|null $agent
		 *
		 * @return MasterWeeklyReport
		 */
		public function setAgent ($agent) {
			if (!empty($agent)) {
				$this->agent = $agent;
			}
			return $this;
		}
		
		/**
		 * @param integer $agentId
		 *
		 * @return MasterWeeklyReport
		 */
		public function setAgentId ($agentId) {
			$this->agentId = $agentId;
			return $this;
		}
		
		/**
		 * @param string $codeInstance
		 *
		 * @return MasterWeeklyReport
		 */
		public function setCodeInstance ($codeInstance) {
			$this->codeInstance = $codeInstance;
			return $this;
		}
		
		/**
		 * @param string $dateStart
		 *
		 * @return MasterWeeklyReport
		 */
		public function setDateStart ($dateStart) {
			$this->dateStart = $dateStart;
			return $this;
		}
		
		/**
		 * @param string $description
		 *
		 * @return MasterWeeklyReport
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param string $dueDate
		 *
		 * @return MasterWeeklyReport
		 */
		public function setDueDate ($dueDate) {
			$this->dueDate = $dueDate;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return MasterWeeklyReport
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $mailInstance
		 *
		 * @return MasterWeeklyReport
		 */
		public function setMailInstance ($mailInstance) {
			$this->mailInstance = $mailInstance;
			return $this;
		}
		
		/**
		 * @param integer $status
		 *
		 * @return MasterWeeklyReport
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param string $reportOfStatus
		 *
		 * @return MasterWeeklyReport
		 */
		public function setReportOfStatus ($reportOfStatus) {
			$this->reportOfStatus = $reportOfStatus;
			return $this;
		}
		
		/**
		 * @param $upcomingReportId
		 *
		 * @return MasterWeeklyReport
		 */
		public function setUpcomingReportId ($upcomingReportId) {
			$this->upcomingReportId = $upcomingReportId;
			return $this;
		}
		
		/**
		 * @param $weeklyReportId
		 *
		 * @return MasterWeeklyReport
		 */
		public function setWeeklyReportId ($weeklyReportId) {
			$this->weeklyReportId = $weeklyReportId;
			return $this;
		}
		
		/**
		 * @return MasterWeeklyReport
		 */
		public static function getInstance () {
					return new self ();
		}
		
	}
