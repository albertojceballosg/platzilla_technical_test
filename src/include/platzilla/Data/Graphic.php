<?php
	require_once ('include/platzilla/Exceptions/ChartException.php');
	require_once ('include/platzilla/Objects/ChartInterface.php');

	class Graphic implements ChartInterface {

		/** @var integer */
		private $id;

		/** @var integer */
		private $advanced;

		/** @var string[] */
		private $applicationCodes;

		/** @var boolean */
		private $compare;

		/** @var integer */
		private $dateGrouping;

		/** @var boolean */
		private $deleted;

		/** @var string */
		private $fieldGrid;

		/** @var string|array */
		private $fieldName;

		/** @var string|array */
		private $graphicOptions;

		/** @var string */
		private $groupBy;

		/** @var boolean */
		private $locked;

		/** @var string|array */
		private $moduleName;

		/** @var string|array */
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
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * @return string
		 */
		public function getGraphicOptions () {
			return $this->graphicOptions;
		}

		/**
		 * @return string
		 */
		public function getGroupBy () {
			return $this->groupBy;
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return integer
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
		 * @return Graphic
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param integer $advanced
		 *
		 * @return Graphic
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
		 * @return Graphic
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
		 * @return Graphic
		 */
		public function setCompare ($compare) {
			if (is_bool ($compare)) {
				$this->compare = $compare;
			}
			return $this;
		}

		/**
		 * @param integer $dateGrouping
		 *
		 * @return Graphic
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
		 * @return Graphic
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
		 * @return Graphic
		 */
		public function setFieldGrid ($fieldGrid) {
			$this->fieldGrid = $fieldGrid;
			return $this;
		}

		/**
		 * @param string|array $fieldName
		 *
		 * @return Graphic
		 */
		public function setFieldName ($fieldName) {
			if (is_array ($fieldName)) {
				$this->fieldName = json_encode ($fieldName);
			} else {
				$this->fieldName = $fieldName;
			}
			return $this;
		}

		/**
		 * @param array|string $graphicOptions
		 *
		 * @return Graphic
		 */
		public function setGraphicOptions($graphicOptions) {
			if (is_array ($graphicOptions)) {
				$this->graphicOptions = json_encode ($graphicOptions, JSON_FORCE_OBJECT);
			} else {
				$this->graphicOptions = $graphicOptions;
			}
			return $this;
		}

		/**
		 * @param string $groupBy
		 *
		 * @return Graphic
		 */
		public function setGroupBy ($groupBy) {
			$this->groupBy = $groupBy;
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return Graphic
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * @param string|array $moduleName
		 *
		 * @return Graphic
		 */
		public function setModuleName ($moduleName) {
			if (is_array ($moduleName)) {
				$this->moduleName = json_encode ($moduleName);
			} else {
				$this->moduleName = $moduleName;
			}
			return $this;
		}

		/**
		 * @param string|array $operation
		 *
		 * @return Graphic
		 */
		public function setOperation ($operation) {
			if (is_array($operation)) {
				$this->operation = json_encode ($operation);
			} else {
				$this->operation = $operation;
			}
			return $this;
		}

		/**
		 * @param string[] $roleIds
		 *
		 * @return Graphic
		 */
		public function setRoleIds ($roleIds) {
			$this->roleIds = $roleIds;
			return $this;
		}

		/**
		 * @param string $sqlQuery
		 *
		 * @return Graphic
		 */
		public function setSqlQuery ($sqlQuery) {
			$this->sqlQuery = $sqlQuery;
			return $this;
		}

		/**
		 * @param string $title
		 *
		 * @return Graphic
		 */
		public function setTitle ($title) {
			$this->title = $title;
			return $this;
		}

		/**
		 * @param string $type
		 *
		 * @return Graphic
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
		 * @return Graphic
		 */
		public function setVariables ($variables) {
			$this->variables = $variables;
			return $this;
		}

		/**
		 * @param string $fieldCompare
		 *
		 * @return Graphic
		 */
		public function setFieldCompare ($fieldCompare) {
			$this->fieldCompare = $fieldCompare;
			return $this;
		}

		/**
		 * @param string $compareOperation
		 *
		 * @return Graphic
		 */
		public function setCompareOperation ($compareOperation) {
			$this->compareOperation = $compareOperation;
			return $this;
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
			} else if ((in_array ($this->operation, array (self::OPERATION_AVERAGE, self::OPERATION_SUM))) && (empty ($this->groupBy))) {
				throw new ChartException (ChartException::ERROR_CHART_EMPTY_GROUP_BY);
			}
		}

		/**
		 * @return Graphic
		 */
		public static function getInstance () {
			return new self ();
		}

	}
