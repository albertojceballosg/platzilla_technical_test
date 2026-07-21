<?php
	require_once ('modules/proyectos/Objects/ProjectWorksInterface.php');
	require_once ('modules/proyectos/Objects/ProjectWorksException.php');
	
	class ProjectWorks implements ProjectWorksInterface	{
		
		/** @var integer */
		private $crmId;
		
		/** @var integer */
		private $crmIdJob;
		
		/** @var string */
		private $estimatedDueDate;
		
		/** @var integer */
		private $id;
		
		/** @var float */
		private $jobContributionFactor;
		
		/** @var string */
		private $jobName;
		
		/** @var float */
		private $percentageCompletion;
		
		/** @var float */
		private $projectProgress;
		
		/** @var float */
		private $workEstimatedCost;
		
		/** @var float */
		private $workEstimatedCostRaw;
		
		/** @var float */
		private $costWorkPerformed;
		
		/** @var float */
		private $costWorkPerformedRaw;
		
		/** @var integer */
		private $responsibleJob;
		
		/** @var string */
		private $responsibleJobName;
		
		/** @var string */
		private $stageName;
		
		/** @var integer */
		private $stageId;
		
		/** @var string */
		private $startDate;
		
		/** @var string */
		private $workSituation;
		
		/** @var array */
		private $summaryRow;
		
		/** @var string */
		private $summaryStr;
		
		/**
		 * @return integer
		 */
		public function getCrmId () {
			return $this->crmId;
		}
		
		/**
		 * @return integer
		 */
		public function getCrmIdJob () 	{
			return $this->crmIdJob;
		}
		
		/**
		 * @return string
		 */
		public function getEstimatedDueDate () {
			return $this->estimatedDueDate;
		}
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return float
		 */
		public function getJobContributionFactor () {
			return $this->jobContributionFactor;
		}
		
		/**
		 * @return string
		 */
		public function getJobName () {
			return $this->jobName;
		}
		
		/**
		 * @return float
		 */
		public function getPercentageCompletion () 	{
			return $this->percentageCompletion;
		}
		
		/**
		 * @return float
		 */
		public function getProjectProgress () {
			return $this->projectProgress;
		}
		
		/**
		 * @return float
		 */
		public function getWorkEstimatedCost () {
			return $this->workEstimatedCost;
		}
		
		/**
		 * @return float
		 */
		public function getWorkEstimatedCostRaw () {
			return $this->workEstimatedCostRaw;
		}
		
		/**
		 * @return float
		 */
		public function getCostWorkPerformed () {
			return $this->costWorkPerformed;
		}
		
		/**
		 * @return float
		 */
		public function getCostWorkPerformedRaw () {
			return $this->costWorkPerformedRaw;
		}
		
		/**
		 * @return integer
		 */
		public function getResponsibleJob () {
			return $this->responsibleJob;
		}
		
		/**
		 * @return string
		 */
		public function getResponsibleJobName () {
			return $this->responsibleJobName;
		}
		
		/**
		 * @return string
		 */
		public function getStageName () {
			return $this->stageName;
		}
		
		/**
		 * @return integer
		 */
		public function getStageId () {
			return $this->stageId;
		}
		
		/**
		 * @return string
		 */
		public function getStartDate () {
			return $this->startDate;
		}
		
		/**
		 * @return string
		 */
		public function getWorkSituation () {
			return $this->workSituation;
		}
		
		/**
		 * @return array
		 */
		public function getSummaryRow () {
			return $this->summaryRow;
		}
		
		/**
		 * @return string
		 */
		public function getSummaryStr () {
			return $this->summaryStr;
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return ProjectWorks
		 */
		public function setCrmId ($crmId) {
			$this->crmId = $crmId;
			return $this;
		}
		
		/**
		 * @param integer $crmIdJob
		 *
		 * @return ProjectWorks
		 */
		public function setCrmIdJob ($crmIdJob) {
			$this->crmIdJob = $crmIdJob;
			return $this;
		}
		
		/**
		 * @param $estimatedDueDate
		 *
		 * @return ProjectWorks
		 */
		public function setEstimatedDueDate ($estimatedDueDate) {
			$this->estimatedDueDate = $estimatedDueDate;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return ProjectWorks
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param float $jobContributionFactor
		 *
		 * @return ProjectWorks
		 */
		public function setJobContributionFactor ($jobContributionFactor) {
			$this->jobContributionFactor = $jobContributionFactor;
			return $this;
		}
		
		/**
		 * @param string $jobName
		 *
		 * @return ProjectWorks
		 */
		public function setJobName ($jobName) {
			$this->jobName = $jobName;
			return $this;
		}
		
		/**
		 * @param float $percentageCompletion
		 *
		 * @return ProjectWorks
		 */
		public function setPercentageCompletion ($percentageCompletion) {
			$this->percentageCompletion = $percentageCompletion;
			return $this;
		}
		
		/**
		 * @param float $projectProgress
		 *
		 * @return ProjectWorks
		 */
		public function setProjectProgress ($projectProgress) {
			$this->projectProgress = $projectProgress;
			return $this;
		}
		
		/**
		 * @param float $workEstimatedCost
		 *
		 * @return ProjectWorks
		 */
		public function setWorkEstimatedCost ($workEstimatedCost) {
			$this->workEstimatedCost = $workEstimatedCost;
			return $this;
		}
		
		/**
		 * @param float $workEstimatedCostRaw
		 *
		 * @return ProjectWorks
		 */
		public function setWorkEstimatedCostRaw ($workEstimatedCostRaw) {
			$this->workEstimatedCostRaw = $workEstimatedCostRaw;
			return $this;
		}
		
		/**
		 * @param float $costWorkPerformed
		 *
		 * @return ProjectWorks
		 */
		public function setCostWorkPerformed ($costWorkPerformed) {
			$this->costWorkPerformed = $costWorkPerformed;
			return $this;
		}
		
		/**
		 * @param float $costWorkPerformedRaw
		 *
		 * @return ProjectWorks
		 */
		public function setCostWorkPerformedRaw ($costWorkPerformedRaw) {
			$this->costWorkPerformedRaw = $costWorkPerformedRaw;
			return $this;
		}
		
		/**
		 * @param integer  $responsibleJob
		 *
		 * @return ProjectWorks
		 */
		public function setResponsibleJob ($responsibleJob) {
			$this->responsibleJob = $responsibleJob;
			return $this;
		}
		
		/**
		 * @param string $responsibleJobName
		 *
		 * @return ProjectWorks
		 */
		public function setResponsibleJobName ($responsibleJobName) {
			$this->responsibleJobName = $responsibleJobName;
			return $this;
		}
		
		/**
		 * @param integer $stageId
		 * @param stdClass[] $stages
		 *
		 * @return ProjectWorks
		 */
		public function setStageName ($stageId, $stages) {
			if (empty ($stages) || !is_array ($stages) || !count ($stages)) {
				$this->stageName = null;
			} else {
				foreach ($stages as $stage) {
					if ($stage->id == $stageId) {
						$this->stageName = $stage->stage;
					}
				}
			}
			return $this;
		}
		
		/**
		 * @param integer $stageId
		 *
		 * @return ProjectWorks
		 */
		public function setStageId ($stageId) {
			$this->stageId = $stageId;
			return $this;
		}
		
		/**
		 * @param string $startDate
		 *
		 * @return ProjectWorks
		 */
		public function setStartDate ($startDate) {
			$this->startDate = $startDate;
			return $this;
		}
		
		/**
		 * @param string $workSituation
		 *
		 * @return ProjectWorks
		 */
		public function setWorkSituation ($workSituation) {
			$this->workSituation = $workSituation;
			return $this;
		}
		
		/**
		 * @param string $summaryRow
		 *
		 * @return ProjectWorks
		 */
		public function setSummaryRow ($summaryRow) {
			if (!empty ($summaryRow)) {
				$this->summaryRow = json_decode ($summaryRow, true);
			} else {
				$this->summaryRow = array ();
			}
			return $this;
		}
		
		/**
		 * @param array $summaryStr
		 *
		 * @return ProjectWorks
		 */
		public function setSummaryStr ($summaryStr) {
			if (is_array ($summaryStr)) {
				$this->summaryStr = json_encode ($summaryStr);
			} else if (is_scalar ($summaryStr)) {
				$this->summaryStr = $summaryStr;
			} else {
				$this->summaryStr = null;
			}
			return $this;
		}
		
		/**
		 * @throws ProjectWorksException
		 */
		public function validate () {
			if(empty($this->crmId)) {
				throw new ProjectWorksException(ProjectWorksException::CRM_ID_PROJECT_EMPTY);
			} else if (empty($this->crmIdJob)) {
				throw new ProjectWorksException(ProjectWorksException::CRM_ID_WORK_EMPTY);
			}
		}
		
		/**
		 * @return ProjectWorks
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
