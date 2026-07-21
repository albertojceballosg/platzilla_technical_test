<?php
	require_once ('include/platzilla/Exceptions/ChartException.php');
	require_once ('include/platzilla/Objects/ChartInterface.php');

	class Chart implements ChartInterface {

		/** @var integer */
		private $id;

		/** @var integer */
		private $advanced;

		/** @var string[] */
		private $applicationCodes;

		/** @var boolean */
		private $compare;

		/** @var array */
		private $chartOptions;

		/** @var integer */
		private $dateGrouping;

		/** @var boolean */
		private $deleted;

		/** @var string */
		private $fieldGrid;

		/** @var string[] */
		private $fieldName;

		/** @var string */
		private $groupBy;

		/** @var boolean */
		private $locked;

		/** @var string[] */
		private $moduleName;

		/** @var string */
		private $name;

		/** @var array */
		private $operation;

		/** @var string[] */
		private $roleIds;

		/** @var string */
		private $sqlQuery;

		/** @var string */
		private $title;

		/** @var string */
		private $type;

		/** @var string */
		private $variables;

		/** @var string */
		private $fieldCompare;

		/** @var string */
		private $compareOperation;

		/**
		 * Chart constructor.
		 */
		public function __construct () {
			$this->advanced = self::ADVANCED_NO;
			$this->compare  = false;
			$this->deleted  = false;
			$this->locked   = false;
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return integer
		 */
		public function getAdvanced () {
			return $this->advanced;
		}

		/**
		 * @return string[]
		 */
		public function getApplicationCodes () {
			return $this->applicationCodes;
		}

		/**
		 * @return boolean
		 */
		public function getCompare () {
			return $this->compare;
		}

		/**
		 * @return array
		 */
		public function getChartOptions () {
			return $this->chartOptions;
		}

		/**
		 * @return integer
		 */
		public function getDateGrouping () {
			return $this->dateGrouping;
		}

		/**
		 * @return string
		 */
		public function getFieldGrid () {
			return $this->fieldGrid;
		}

		/**
		 * @return array
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * @return string
		 */
		public function getGroupBy () {
			return $this->groupBy;
		}

		/**
		 * @return array
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return array
		 */
		public function getOperation () {
			return $this->operation;
		}

		/**
		 * @return string[]
		 */
		public function getRoleIds () {
			return $this->roleIds;
		}

		/**
		 * @return string
		 */
		public function getSqlQuery () {
			return $this->sqlQuery;
		}

		/**
		 * @return string
		 */
		public function getTitle () {
			return $this->title;
		}

		/**
		 * @return string
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * @return string
		 */
		public function getVariables () {
			return $this->variables;
		}

		/**
		 * @return string
		 */
		public function getFieldCompare () {
			return $this->fieldCompare;
		}

		/**
		 * @return string
		 */
		public function getCompareOperation () {
			return $this->compareOperation;
		}

		/**
		 * @return boolean
		 */
		public function isDeleted () {
			return $this->deleted;
		}

		/**
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * @param integer $id
		 *
		 * @return Chart
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param integer $advanced
		 *
		 * @return Chart
		 */
		public function setAdvanced ($advanced) {
			if (in_array ($advanced, array (self::ADVANCED_BOXSCORE, self::ADVANCED_NO, self::ADVANCED_YES))) {
				$this->advanced = $advanced;
			}
			return $this;
		}

		/**
		 * @param string[] $applicationCodes
		 *
		 * @return Chart
		 */
		public function setApplicationCodes ($applicationCodes) {
			if (is_array ($applicationCodes)) {
				$this->applicationCodes = $applicationCodes;
			}
			return $this;
		}

		/**
		 * @param boolean $compare
		 *
		 * @return Chart
		 */
		public function setCompare ($compare) {
			if (is_bool ($compare)) {
				$this->compare = $compare;
			}
			return $this;
		}

		/**
		 * @param array $chartOptions
		 *
		 * @return Chart
		 */
		public function setChartOptions($chartOptions) {
			$this->chartOptions = $chartOptions;
			return $this;
		}

		/**
		 * @param integer $dateGrouping
		 *
		 * @return Chart
		 */
		public function setDateGrouping ($dateGrouping) {
			if (in_array ($dateGrouping, array (self::DATE_GROUPING_ANNUAL, self::DATE_GROUPING_BIANNUAL, self::DATE_GROUPING_DAILY, self::DATE_GROUPING_MONTHLY, self::DATE_GROUPING_QUARTERLY, self::DATE_GROUPING_WEEKLY))) {
				$this->dateGrouping = $dateGrouping;
			}
			return $this;
		}

		/**
		 * @param boolean $deleted
		 *
		 * @return Chart
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}

		/**
		 * @param string $fieldGrid
		 *
		 * @return Chart
		 */
		public function setFieldGrid ($fieldGrid) {
			$this->fieldGrid = $fieldGrid;
			return $this;
		}

		/**
		 * @param array $fieldName
		 *
		 * @return Chart
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * @param string $groupBy
		 *
		 * @return Chart
		 */
		public function setGroupBy ($groupBy) {
			$this->groupBy = $groupBy;
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return Chart
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * @param array $moduleName
		 *
		 * @return Chart
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return Chart;
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param array $operations
		 *
		 * @return Chart
		 */
		public function setOperation ($operations) {
			if (is_array($operations)) {
				foreach ($operations as $operation) {
					if (in_array($operation, array(self::OPERATION_AVERAGE, self::OPERATION_COUNT, self::OPERATION_SUM))) {
						$this->operation [] = $operation;
					}
				}
			} else {
				$this->operation = array ();
			}
			return $this;
		}

		/**
		 * @param string[] $roleIds
		 *
		 * @return Chart
		 */
		public function setRoleIds ($roleIds) {
			$this->roleIds = $roleIds;
			return $this;
		}

		/**
		 * @param string $sqlQuery
		 *
		 * @return Chart
		 */
		public function setSqlQuery ($sqlQuery) {
			$this->sqlQuery = $sqlQuery;
			return $this;
		}

		/**
		 * @param string $title
		 *
		 * @return Chart
		 */
		public function setTitle ($title) {
			$this->title = $title;
			return $this;
		}

		/**
		 * @param string $type
		 *
		 * @return Chart
		 */
		public function setType ($type) {
			if (in_array ($type, array (self::TYPE_AREA, self::TYPE_BARS, self::TYPE_COLUMN, self::TYPE_COMBO, self::TYPE_DONUT, self::TYPE_FUNNEL, self::TYPE_LINE, self::TYPE_PIE, self::TYPE_TABLE))) {
				$this->type = $type;
			}
			return $this;
		}

		/**
		 * @param string $variables
		 *
		 * @return Chart
		 */
		public function setVariables ($variables) {
			$this->variables = $variables;
			return $this;
		}

		/**
		 * @param string $fieldCompare
		 *
		 * @return Chart
		 */
		public function setFieldCompare ($fieldCompare) {
			$this->fieldCompare = $fieldCompare;
			return $this;
		}

		/**
		 * @param string $compareOperation
		 *
		 * @return Chart
		 */
		public function setCompareOperation ($compareOperation) {
			$this->compareOperation = $compareOperation;
			return $this;
		}

		/**
		 * @param Chart $chart
		 */
		public function copyValuesFrom ($chart) {
			if ((empty ($chart)) || (!($chart instanceof Chart))) {
				return;
			}

			$this->advanced         = $chart->getAdvanced ();
			$this->applicationCodes = $chart->getApplicationCodes ();
			$this->compare          = $chart->getCompare ();
			$this->chartOptions     = $chart->getChartOptions();
			$this->dateGrouping     = $chart->getDateGrouping ();
			$this->fieldGrid        = $chart->getFieldGrid ();
			$this->fieldName        = $chart->getFieldName ();
			$this->groupBy          = $chart->getGroupBy ();
			$this->moduleName       = $chart->getModuleName ();
			$this->name             = $chart->getName ();
			$this->operation        = $chart->getOperation ();
			$this->roleIds          = $chart->getRoleIds ();
			$this->sqlQuery         = $chart->getSqlQuery ();
			$this->title            = $chart->getTitle ();
			$this->type             = $chart->getType ();
			$this->variables        = $chart->getVariables ();
			$this->fieldCompare     = $chart->getFieldCompare ();
			$this->compareOperation = $chart->getCompareOperation ();
		}

		/**
		 * @param integer $newChartId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return Chart
		 * @throws ChartException
		 */
		public function duplicate ($newChartId, $oldCodeFieldName = null, $newCodeFieldName = null) {
			$this->validate ();

			$object = new self ();
			return $object->setId ($newChartId)
				->setAdvanced ($this->advanced)
				->setApplicationCodes ($this->applicationCodes)
				->setCompare ($this->compare)
				->setChartOptions ($this->chartOptions)
				->setDateGrouping ($this->dateGrouping)
				->setFieldGrid ($this->fieldGrid)
				->setFieldName ($this->fieldName != $oldCodeFieldName ? $this->fieldName : $newCodeFieldName)
				->setGroupBy ($this->groupBy != $oldCodeFieldName ? $this->groupBy : $newCodeFieldName)
				->setName ($this->name)
				->setModuleName ($this->moduleName)
				->setOperation ($this->operation)
				->setRoleIds ($this->roleIds)
				->setSqlQuery ($this->sqlQuery)
				->setTitle ($this->title)
				->setType ($this->type)
				->setVariables ($this->variables)
				->setFieldCompare ($this->fieldCompare)
				->setCompareOperation ($this->compareOperation);
		}

		/**
		 * @param Chart $chart
		 *
		 * @return boolean
		 */
		public function isEqualTo ($chart) {
			if (
				(empty ($chart)) ||
				(!($chart instanceof Chart)) ||
				($this->advanced != $chart->getAdvanced ()) ||
				($this->applicationCodes != $chart->getApplicationCodes ()) ||
				($this->compare != $chart->getCompare ()) ||
				(!empty (array_diff ($this->chartOptions, $chart->getChartOptions ()))) ||
				(!empty (array_diff ($chart->getChartOptions (), $this->chartOptions))) ||
				($this->dateGrouping != $chart->getDateGrouping ()) ||
				($this->fieldGrid != $chart->getFieldGrid ()) ||
				(!empty (array_diff ($this->fieldName,$chart->getFieldName ()))) ||
				(!empty (array_diff ($chart->getFieldName (), $this->fieldName))) ||
				($this->groupBy != $chart->getGroupBy ()) ||
				(!empty (array_diff ($this->moduleName, $chart->getModuleName ()))) ||
				(!empty (array_diff ($chart->getModuleName (), $this->moduleName))) ||
				($this->name != $chart->name) ||
				(!empty (array_diff ($this->operation, $chart->getOperation ()))) ||
				(!empty (array_diff ($chart->getOperation (), $this->operation))) ||
				($this->roleIds != $chart->getRoleIds ()) ||
				($this->sqlQuery != $chart->getSqlQuery ()) ||
				($this->title != $chart->getTitle ()) ||
				($this->type != $chart->getType ()) ||
				($this->variables != $chart->getVariables ()) ||
				($this->fieldCompare != $chart->getFieldCompare ()) ||
				($this->compareOperation != $chart->getCompareOperation ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws ChartException
		 */
		public function validate () {
			if ($this->deleted) {
				return;
			} else if (empty ($this->fieldName)) {
				throw new ChartException (ChartException::ERROR_CHART_EMPTY_FIELD_NAME);
			} else if (!isset ($this->operation)) {
				throw new ChartException (ChartException::ERROR_CHART_EMPTY_OPERATION);
			} else if (empty ($this->title)) {
				throw new ChartException (ChartException::ERROR_CHART_EMPTY_TITLE);
			} else if (!isset ($this->type)) {
				throw new ChartException (ChartException::ERROR_CHART_EMPTY_TYPE);
			} else if ((empty ($this->operation)) && (empty ($this->groupBy)) && empty($this->dateGrouping)) {
				throw new ChartException (ChartException::ERROR_CHART_EMPTY_GROUP_BY);
			}
		}

		/**
		 * @return Chart
		 */
		public static function getInstance () {
			return new self ();
		}

	}
