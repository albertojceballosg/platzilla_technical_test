<?php
	require_once ('include/platzilla/Objects/CalculationElement.php');
	require_once ('include/platzilla/Objects/CalculationSystem.php');
	class ScoringDataBox {

		/** @var integer */
		private $accountId;

		/** @var integer */
		private $blockScoreBoxId;

		/** @var string */
		private $boxScore;

		/** @var Field */
		private $calculatedField;
		
		/** @var string */
		private $calculatedName;
		
		/** @var CalculationSystem */
		private $calculatedSystem;
		
		/** @var string */
		private $calculatedSystemName;

		/** @var string */
		private $createdDate;
		
		/** @var string */
		private $dataRel;

		/** @var boolean */
		private $defaultPlatzilla;

		/** @var string */
		private $description;

		/** @var string */
		private $fulfillment;

		/** @var integer */
		private $id;

		/** @var boolean */
		private $isEditable;
		
		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;

		/** @var string */
		private $objective;

		/** @var string */
		private $queryKpi;

		/** @var string */
		private $queryKpiWeekly;

		/** @var integer */
		private $scoringBoxId;

		/** @var ScoreObjectivesBox[] */
		private $scoreObjectivesBoxes;

		/** @var string */
		private $sourceModule;
		
		/** @var string */
		private $status;
		
		/** @var array */
		public  $sharedOn = array ();
		
		/**
		 * @param ScoreObjectivesBox[] $scoreObjectives
		 *
		 * @return array|null
		 */
		private function copyScoreObjectivesBox ($scoreObjectives) {
			if(empty($scoreObjectives)) {
				return null;
			}
			$scoringObjectives = array ();
			foreach ($scoreObjectives as $scoringObjective) {
				$scoringObjectives [] = $scoringObjective->duplicate ($scoringObjective->getId());
			}
			return $scoringObjectives;
		}

		/**
		 * @param ScoreObjectivesBox[] $scoreObjectives
		 *
		 * @return array
		 */
		private function duplicateFromScoreObjectivesBox ($scoreObjectives) {
			$scoringObjectives = array ();
			foreach ($scoreObjectives as $scoringObjective) {
				$scoringObjectives [] = $scoringObjective->duplicate ($scoringObjective->getId ());
			}
			return $scoringObjectives;
		}

		/**
		 * @param ScoreObjectivesBox[] $theseScoreObjective
		 * @param ScoreObjectivesBox[] $thoseScoreObjective
		 *
		 * @return boolean
		 */
		private function isScoreObjectiveEqualTo ($theseScoreObjective, $thoseScoreObjective) {
			$totalScoreObjective = count ($theseScoreObjective);
			$equals              = false;
			if ($totalScoreObjective != count ($thoseScoreObjective)) {
				return false;
			}

			for ($k = 0; $k < $totalScoreObjective; $k++) {
				if ($theseScoreObjective [ $k ]->isEqualTo ($thoseScoreObjective [ $k ])) {
					$equals = true;
					$k      = ($totalScoreObjective + 1);
				}
			}
			return $equals;
		}

		/**
		 * @param ScoringDataBox $scoringData
		 */
		public function copyValuesFrom ($scoringData) {
			if ((empty ($scoringData)) || (!($scoringData instanceof ScoringDataBox))) {
				return;
			}
			$this->accountId            = $scoringData->getAccountId ();
			$this->boxScore             = $scoringData->getBoxScore ();
			$this->blockScoreBoxId      = $scoringData->getBlockScoreBoxId ();
			$this->calculatedName       = $scoringData->getCalculatedName ();
			$this->dataRel              = $scoringData->getDataRel ();
			$this->defaultPlatzilla     = $scoringData->isDefaultPlatzilla ();
			$this->description          = $scoringData->getDescription ();
			$this->fulfillment          = $scoringData->getFulfillment ();
			$this->id                   = $scoringData->getId ();
			$this->moduleName           = $scoringData->getModuleName ();
			$this->name                 = $scoringData->getName ();
			$this->objective            = $scoringData->getObjective ();
			$this->queryKpi             = $scoringData->getQueryKpi ();
			$this->queryKpiWeekly       = $scoringData->getQueryKpiWeekly ();
			$this->scoringBoxId         = $scoringData->getScoringBoxId ();
			$this->scoreObjectivesBoxes = $this->copyScoreObjectivesBox ($scoringData->getScoreObjectivesBoxes ());
			$this->sourceModule         = $scoringData->getSourceModule ();
		}

		/**
		 * @return ScoringDataBox
		 */
		public function duplicate ($newScoringDataId = null) {
			$object = new self ();
			return $object->setId ($newScoringDataId)
				->setAccountId ($this->accountId)
				->setBoxScore ($this->boxScore)
				->setBlockScoreBoxId ($this->blockScoreBoxId)
				->setCalculatedName ($this->calculatedName)
				->setDataRel ($this->dataRel)
				->setDescription ($this->description)
				->setFulfillment ($this->fulfillment)
				->setModuleName ($this->moduleName)
				->setName ($this->name)
				->setObjective ($this->objective)
				->setQueryKpi ($this->queryKpi)
				->setQueryKpiWeekly ($this->queryKpiWeekly)
				->setScoringBoxId ($this->scoringBoxId)
				->setScoreObjectivesBoxes ($this->duplicateFromScoreObjectivesBox ($this->scoreObjectivesBoxes))
				->setSourceModule ($this->sourceModule);
		}

		/**
		 * @return integer
		 */
		public function getAccountId () {
			return $this->accountId;
		}

		/**
		 * @return integer
		 */
		public function getBlockScoreBoxId () {
			return $this->blockScoreBoxId;
		}

		/**
		 * @return string
		 */
		public function getBoxScore () {
			return $this->boxScore;
		}
		
		/**
		 * @return Field
		 */
		public function getCalculatedField () {
			return $this->calculatedField;
		}
		
		/**
		 * @return string
		 */
		public function getCalculatedName () {
			return $this->calculatedName;
		}
		
		/**
		 * @return CalculationSystem
		 */
		public function getCalculatedSystem () {
			return $this->calculatedSystem;
		}
		
		/**
		 * @return string
		 */
		public function getCalculatedSystemName () {
			return $this->calculatedSystemName;
		}
		
		/**
		 * @return string
		 */
		public function getCreatedDate () {
			return $this->createdDate;
		}
		
		/**
		 * @return string
		 */
		public function getDataRel () {
			return $this->dataRel;
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
		public function getFulfillment () {
			return $this->fulfillment;
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return boolean
		 */
		public function isEditable () {
			return $this->isEditable;
		}
		
		/**
		 * @return string
		 */
		public function getModuleName() {
			return $this->moduleName;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return string
		 */
		public function getObjective () {
			return $this->objective;
		}

		/**
		 * @return string
		 */
		public function getQueryKpi () {
			return $this->queryKpi;
		}

		/**
		 * @return string
		 */
		public function getQueryKpiWeekly () {
			return $this->queryKpiWeekly;
		}

		/**
		 * @return integer
		 */
		public function getScoringBoxId() {
			return $this->scoringBoxId;
		}

		/**
		 * @return ScoreObjectivesBox[]
		 */
		public function getScoreObjectivesBoxes() {
			return $this->scoreObjectivesBoxes;
		}

		/**
		 * @return string
		 */
		public function getSourceModule () {
			return $this->sourceModule;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * @return boolean
		 */
		public function isDefaultPlatzilla () {
			return $this->defaultPlatzilla;
		}

		/**
		 * @param ScoringDataBox $scoringData
		 *
		 * @return boolean
		 */
		public function isEqualTo ($scoringData) {
			if (
				(empty ($scoringData)) ||
				(!($scoringData instanceof ScoringDataBox)) ||
				($this->accountId != $scoringData->getAccountId ()) ||
				($this->boxScore != $scoringData->getBoxScore ()) ||
				($this->blockScoreBoxId != $scoringData->getBlockScoreBoxId ()) ||
				($this->calculatedName != $scoringData->getCalculatedName ()) ||
				($this->dataRel != $scoringData->getDataRel ()) ||
				($this->defaultPlatzilla != $scoringData->isDefaultPlatzilla ()) ||
				($this->description != $scoringData->getDescription ()) ||
				($this->fulfillment != $scoringData->getFulfillment ()) ||
				($this->moduleName != $scoringData->getModuleName ()) ||
				($this->name != $scoringData->getName ()) ||
				($this->objective != $scoringData->getObjective ()) ||
				($this->queryKpi != $scoringData->getQueryKpi ()) ||
				($this->queryKpiWeekly != $scoringData->getQueryKpiWeekly ())||
				($this->scoringBoxId != $scoringData->getScoringBoxId()) ||
				($this->sourceModule != $scoringData->getSourceModule ()) ||
				$this->isScoreObjectiveEqualTo($this->scoreObjectivesBoxes, $scoringData->getScoreObjectivesBoxes ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @param integer $accountId
		 *
		 * @return ScoringDataBox
		 */
		public function setAccountId ($accountId) {
			if ((is_numeric ($accountId)) && ($accountId > 0) && (intval ($accountId) == $accountId)) {
				$this->accountId = $accountId;
			} else {
				$this->accountId = null;
			}
			return $this;
		}

		/**
		 * @param integer $blockScoreBoxId
		 *
		 * @return ScoringDataBox
		 */
		public function setBlockScoreBoxId ($blockScoreBoxId) {
			if ((is_numeric ($blockScoreBoxId)) && ($blockScoreBoxId > 0) && (intval ($blockScoreBoxId) == $blockScoreBoxId)) {
				$this->blockScoreBoxId = $blockScoreBoxId;
			} else {
				$this->blockScoreBoxId = null;
			}
			return $this;
		}

		/**
		 * @param string $boxScore
		 *
		 * @return ScoringDataBox
		 */
		public function setBoxScore ($boxScore) {
			if (is_scalar ($boxScore)) {
				$this->boxScore = $boxScore;
			} else {
				$this->boxScore = null;
			}
			return $this;
		}
		
		/**
		 * @param Field $calculatedField
		 *
		 * @return ScoringDataBox
		 */
		public function setCalculatedField ($calculatedField) {
			if (!($calculatedField instanceof Field)) {
				$this->calculatedField = null;
			} else {
				$this->calculatedField =  $calculatedField;
			}
			return $this;
		}
		
		/**
		 * @param string $calculatedName
		 *
		 * @return ScoringDataBox
		 */
		public function setCalculatedName($calculatedName) {
			if (is_scalar ($calculatedName)) {
				$this->calculatedName = $calculatedName;
			} else {
				$this->calculatedName = null;
			}
			return $this;
		}
		
		/**
		 * @param CalculationSystem $calculatedSystem
		 *
		 * @return ScoringDataBox
		 */
		public function setCalculatedSystem ($calculatedSystem) {
			if (!($calculatedSystem instanceof CalculationSystem)) {
				$this->calculatedSystem = null;
			} else {
				$this->calculatedSystem = $calculatedSystem;
			}
			return $this;
		}
		
		/**
		 * @param string $calculatedSystemName
		 *
		 * @return ScoringDataBox
		 */
		public function setCreatedDate ($createdDate) {
			$this->createdDate = $createdDate;
			return $this;
		}
		
		/**
		 * @param string $calculatedSystemName
		 */
		public function setCalculatedSystemName ($calculatedSystemName) {
			if (is_scalar ($calculatedSystemName)) {
				$this->calculatedSystemName = $calculatedSystemName;
			} else {
				$this->calculatedSystemName = null;
			}
			return $this;
		}
		
		/**
		 * @param string $dataRel
		 *
		 * @return ScoringDataBox
		 */
		public function setDataRel ($dataRel) {
			if (is_scalar ($dataRel)) {
				$this->dataRel = $dataRel;
			} else {
				$this->dataRel = null;
			}
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return ScoringDataBox
		 */
		public function setDescription ($description) {
			if (is_scalar ($description)) {
				$this->description = $description;
			} else {
				$this->description = null;
			}
			return $this;
		}

		/**
		 * @param boolean $defaultPlatzilla
		 *
		 * @return ScoringDataBox
		 */
		public function setDefaultPlatzilla ($defaultPlatzilla) {
			if (is_bool ($defaultPlatzilla)) {
				$this->defaultPlatzilla = $defaultPlatzilla;
			}
			return $this;
		}

		/**
		 * @param string $fulfillment
		 *
		 * @return ScoringDataBox
		 */
		public function setFulfillment ($fulfillment) {
			if (is_scalar ($fulfillment)) {
				$this->fulfillment = $fulfillment;
			} else {
				$this->fulfillment = null;
			}

			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return ScoringDataBox
		 */
		public function setId($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = $id;
			} else {
				$this->id = null;
			}
			return $this;
		}
		
		/**
		 * @param string $isEditable
		 *
		 * @return ScoringDataBox
		 */
		public function setIsEditable ($isEditable) {
			$this->isEditable = ($isEditable === 'YES');
			return $this;
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return ScoringDataBox
		 */
		public function setModuleName ($moduleName) {
			if (is_scalar ($moduleName)) {
				$this->moduleName = $moduleName;
			} else {
				$this->moduleName = null;
			}
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return ScoringDataBox
		 */
		public function setName ($name) {
			if (is_scalar ($name)) {
				$this->name = $name;
			} else {
				$this->name = null;
			}
			return $this;
		}

		/**
		 * @param string $objective
		 *
		 * @return ScoringDataBox
		 */
		public function setObjective ($objective) {
			if (is_scalar ($objective)) {
				$this->objective = $objective;
			} else {
				$this->objective = null;
			}
			return $this;
		}

		/**
		 * @param string $queryKpi
		 *
		 * @return ScoringDataBox
		 */
		public function setQueryKpi ($queryKpi) {
			if (is_scalar ($queryKpi)) {
				$this->queryKpi = $queryKpi;
			} else {
				$this->queryKpi = null;
			}
			return $this;
		}

		/**
		 * @param string $queryKpiWeekly
		 *
		 * @return ScoringDataBox
		 */
		public function setQueryKpiWeekly($queryKpiWeekly) {
			if (is_scalar ($queryKpiWeekly)) {
				$this->queryKpiWeekly = $queryKpiWeekly;
			} else {
				$this->queryKpiWeekly = null;
			}
			return $this;
		}

		/**
		 * @param integer $scoringBoxId
		 *
		 * @return ScoringDataBox
		 */
		public function setScoringBoxId($scoringBoxId) {
			if ((is_numeric ($scoringBoxId)) && ($scoringBoxId > 0) && (intval ($scoringBoxId) == $scoringBoxId)) {
				$this->scoringBoxId = $scoringBoxId;
			} else {
				$this->scoringBoxId = null;
			}
			return $this;
		}

		/**
		 * @param ScoreObjectivesBox[] $scoreObjectives
		 *
		 * @return ScoringDataBox
		 */
		public function setScoreObjectivesBoxes($scoreObjectives) {
			if (!is_array($scoreObjectives)) {
				return $this;
			}
			foreach ($scoreObjectives as $scoreObjective) {
				if (($scoreObjective == null) || ($scoreObjective instanceof ScoreObjectivesBox) && (!empty ($scoreObjective))) {
					$this->scoreObjectivesBoxes [] = $scoreObjective;
				}
			}
			return $this;
		}

		/**
		 * @param string $sourceModule
		 *
		 * @return ScoringDataBox
		 */
		public function setSourceModule ($sourceModule) {
			if (is_scalar ($sourceModule)) {
				$this->sourceModule = $sourceModule;
			} else {
				$this->sourceModule = null;
			}
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return ScoringDataBox
		 */
		public function setStatus ($status) {
			if (is_scalar ($status)) {
				$this->status = $status;
			} else {
				$this->status = null;
			}
			return $this;
		}
		
		/**
		 * @return ScoringDataBox
		 */
		public static function getInstance () {
			return new self ();
		}

	}
