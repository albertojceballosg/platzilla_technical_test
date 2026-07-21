<?php

	/**
	 * Class BoxScoreData
	 *
	 * Esta clase hace referencia a los métodos que gestionan la tabla vtiger_box_score_data
	 */
	class BoxScoreData {

		/** @var integer */
		private $accountId;

		/** @var string */
		private $boxScore;

		/** @var integer */
		private $boxScoreId;

		/** @var  string */
		private $calculatedName;

		/** @var integer */
		private $dataRel;

		/** @var BoxScoreDataWeekly[] */
		private $dataScoresWeekly;

		/** @var  boolean */
		private $defaultPlatzilla;

		/** @var string */
		private $description;

		/** @var string */
		private $fulfillment;

		/** @var integer */
		private $id;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $objective;

		/** @var string */
		private $queryKpi;

		/** @var string */
		private $queryKpiWeekly;

		/** @var string */
		private $sourceModule;

		/** @var  integer*/
		private $type;

		/**
		 * Obtiene el ID de la cuenta de la data del indicador
		 *
		 * @return integer
		 */
		public function getAccountId () {
			return $this->accountId;
		}

		/**
		 * Obtiene el nombre del campo con cálculo asociado al indicador
		 *
		 * @return string
		 */
		public function getCalculatedName () {
			return $this->calculatedName;
		}

		/**
		 * Devuelve el ID del indicador
		 *
		 * @return integer
		 */
		public function getBoxScoreId () {
			return $this->boxScoreId;
		}

		/**
		 * Devuelve el nombre del indicador
		 *
		 * @return string
		 */
		public function getBoxScore () {
			return $this->boxScore;
		}

		/**
		 * Obtiene el ID de la data asociada a la escala
		 *
		 * @return integer
		 */
		public function getDataRel() {
			return $this->dataRel;
		}

		/**
		 * Obtiene colección de objeto BoxScoreDataWeekly
		 *
		 * @return BoxScoreDataWeekly[]
		 */
		public function getDataScoresWeekly () {
			return $this->dataScoresWeekly;
		}

		/**
		 * Obtiene la descripción del indicador
		 *
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * Obtiene el valor para completar el objetivo de comparación
		 *
		 * @return string
		 */
		public function getFulfillment () {
			return $this->fulfillment;
		}

		/**
		 * Devuelve el ID del registro de Data
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Devueleve el nombre del módulo
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Obtiene el valor objetivo del indicador
		 *
		 * @return string
		 */
		public function getObjective() {
			return $this->objective;
		}

		/**
		 * Devuelve el sql del KPI mensual
		 *
		 * @return string
		 */
		public function getQueryKpi () {
			return $this->queryKpi;
		}

		/**
		 * Devuelve el sql del KPI semanal
		 *
		 * @return string
		 */
		public function getQueryKpiWeekly () {
			return $this->queryKpiWeekly;
		}

		/**
		 * Obtiene el nombre del módulo fuente para campos con cálculo
		 *
		 * @return string
		 */
		public function getSourceModule () {
			return $this->sourceModule;
		}

		/**
		 * Obtiene el tipo de indicador
		 *
		 * @return integer
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * Devuelve 1/0 si es una aplicación por defecto de Platzilla
		 *
		 * @return boolean
		 */
		public function isDefaultPlatzilla () {
			return $this->defaultPlatzilla;
		}

		/**
		 * Establece el ID de cuenta del indicador
		 *
		 * @param integer $accountId
		 *
		 * @return BoxScoreData
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
		 * Establece el nombre del indicador
		 *
		 * @param string $boxScore
		 *
		 * @return BoxScoreData
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
		 * Establece el ID del indicador
		 *
		 * @param integer $boxScoreId
		 *
		 * @return BoxScoreData
		 */
		public function setBoxScoreId ($boxScoreId) {
			if ((is_numeric ($boxScoreId)) && ($boxScoreId > 0) && (intval ($boxScoreId) == $boxScoreId)) {
				$this->boxScoreId = $boxScoreId;
			} else {
				$this->boxScoreId = null;
			}
			return $this;
		}

		/**
		 * Establece el nombre del campo con cálculo asociado al indicador
		 *
		 * @param string $calculatedName
		 *
		 * @return BoxScoreData
		 */
		public function setCalculatedName ($calculatedName) {
			if (is_scalar ($calculatedName)) {
				$this->calculatedName = $calculatedName;
			} else {
				$this->calculatedName = null;
			}
			return $this;
		}

		/**
		 * Establece el ID Rel
		 *
		 * @param integer $dataRel
		 *
		 * @return BoxScoreData
		 */
		public function setDataRel ($dataRel) {
			if ((is_numeric ($dataRel) && ($dataRel > 0)) && (intval ($dataRel) == $dataRel)) {
				$this->dataRel = $dataRel;
			} else {
				$this->dataRel = null;
			}
			return $this;
		}

		/**
		 * Establece la colección de objetos BoxScoreDataWeekly asociada a la data del indicador
		 *
		 * @param BoxScoreDataWeekly[] $dataScoresWeekly
		 *
		 * @return BoxScoreData
		 */
		public function setDataScoresWeekly ($dataScoresWeekly) {
			foreach ($dataScoresWeekly as $score) {
				if (($score == null) || ($score instanceof BoxScoreDataWeekly) && (!empty ($score))) {
					$this->dataScoresWeekly [] = $score;
				}
			}
			return $this;
		}

		/**
		 * Establece la condición de indicador por defecto Platzilla
		 *
		 * @param boolean $defaultPlatzilla
		 *
		 * @return BoxScoreData
		 */
		public function setDefaultPlatzilla ($defaultPlatzilla) {
			if (is_bool ($defaultPlatzilla)) {
				$this->defaultPlatzilla = $defaultPlatzilla;
			}
			return $this;
		}

		/**
		 * Establece la descripción del indicador
		 *
		 * @param string $description
		 *
		 * @return BoxScoreData
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
		 * Establece el valor de relleno para el valor objetivo
		 *
		 * @param string $fulfillment
		 *
		 * @return BoxScoreData
		 */
		public function setFulfillment($fulfillment) {
			if (is_scalar ($fulfillment)) {
				$this->fulfillment = $fulfillment;
			} else {
				$this->fulfillment = null;
			}
			return $this;
		}

		/**
		 * Establece el ID para el indicador
		 *
		 * @param integer $id
		 *
		 * @return BoxScoreData
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = $id;
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * Establece el nombre del modulo del indicador
		 *
		 * @param string $moduleName
		 *
		 * @return BoxScoreData
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
		 * Establece el valor objetivo
		 *
		 * @param string $objective
		 *
		 * @return BoxScoreData;
		 */
		public function setObjective($objective) {
			if (is_scalar ($objective)) {
				$this->objective = $objective;
			} else {
				$this->objective = null;
			}
			return $this;
		}

		/**
		 * Establece el sql del KIP para data mensual
		 *
		 * @param string $queryKpi
		 *
		 * @return BoxScoreData
		 */
		public function setQueryKpi($queryKpi) {
			if (is_scalar ($queryKpi)) {
				$this->queryKpi = $queryKpi;
			} else {
				$this->queryKpi = null;
			}
			return $this;
		}

		/**
		 * Establece el sql del KIP para data semanal
		 *
		 * @param string $queryKpiWeekly
		 *
		 * @return BoxScoreData
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
		 * Establece el modulo fuente para el campo con cálculo asociado al indicador
		 *
		 * @param string $sourceModule
		 *
		 * @return BoxScoreData
		 */
		public function setSourceModule($sourceModule) {
			if (is_scalar ($sourceModule)) {
				$this->sourceModule = $sourceModule;
			} else {
				$this->sourceModule = null;
			}
			return $this;
		}

		/**
		 * Establece el tipo de indicador
		 *
		 * @param integer $type
		 *
		 * @return BoxScoreData
		 */
		public function setType ($type) {
			if ((is_numeric ($type)) && ($type > 0) && (intval ($type) == $type)) {
				$this->type = $type;
			} else {
				$this->accountId = null;
			}
			return $this;
		}

		/**
		 * Se obtiene un objeto BoxScoreData con los atributos de la clase
		 *
		 * @return BoxScoreData
		 */
		public static function getInstance () {
			return new self ();
		}

	}
