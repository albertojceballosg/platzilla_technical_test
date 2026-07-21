<?php

	/**
	 * Class BoxScoreDataWeekly
	 *
	 * Esta clase hace referencia a los métodos que gestionan la tabla vtiger_box_score_data_weekly
	 */
	class BoxScoreDataWeekly {

		/** @var integer */
		private $accountId;

		/** @var integer */
		private $boxScoreDataId;

		/** @var integer */
		private $boxScoreId;

		/** @var string */
		private $createdDate;

		/** @var integer */
		private $id;

		/** @var string */
		private $value;

		/**
		 * Valida que el formato y datos de la fecha esten correctos
		 *
		 * @param string $date
		 * @param string $format
		 *
		 * @return boolean
		 */
		private function validateDate ($date, $format = 'Y-m-d') {
			$objectDate = DateTime::createFromFormat ($format, $date);
			return $objectDate && $objectDate->format ($format) == $date;
		}

		/**
		 * Obtiene el ID de la cuenta de la data del indicador
		 *
		 * @return integer
		 */
		public function getAccountId () {
			return $this->accountId;
		}

		/**
		 * Obtiene el ID del registro data asociado
		 *
		 * @return integer
		 */
		public function getBoxScoreDataId () {
			return $this->boxScoreDataId;
		}

		/**
		 * Obtiene el ID del indicador
		 *
		 * @return integer
		 */
		public function getBoxScoreId () {
			return $this->boxScoreId;
		}

		/**
		 * Obtiene la fecha de creación de la data semanal
		 *
		 * @return string
		 */
		public function getCreatedDate () {
			return $this->createdDate;
		}

		/**
		 * Obtiene el ID del registro de dato semanal actual
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getValue () {
			return $this->value;
		}

		/**
		 * Establece el ID de la cuenta del indicador
		 *
		 * @param integer $accountId
		 *
		 * @return BoxScoreDataWeekly
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
		 * Establece el ID de la Data asociada
		 *
		 * @param integer $boxScoreDataId
		 *
		 * @return BoxScoreDataWeekly
		 */
		public function setBoxScoreDataId ($boxScoreDataId) {
			if ((is_numeric ($boxScoreDataId)) && ($boxScoreDataId > 0) && (intval ($boxScoreDataId) == $boxScoreDataId)) {
				$this->boxScoreDataId = $boxScoreDataId;
			} else {
				$this->boxScoreDataId = null;
			}
			return $this;
		}

		/**
		 * @param integer $boxScoreId
		 *
		 * @return BoxScoreDataWeekly
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
		 * Establece la fecha de cración de la data semanal
		 *
		 * @param string $createdDate
		 *
		 * @return BoxScoreDataWeekly
		 */
		public function setCreatedDate ($createdDate) {
			if ($this->validateDate ($createdDate)) {
				$this->createdDate = $createdDate;
			} else {
				$this->createdDate = null;
			}
			return $this;
		}

		/**
		 * Establece el ID de la data semanal
		 *
		 * @param integer $id
		 *
		 * @return BoxScoreDataWeekly
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
		 * Establece el valor para el indicador
		 *
		 * @param string $value
		 *
		 * @return BoxScoreDataWeekly
		 */
		public function setValue ($value) {
			if (is_scalar ($value)) {
				$this->value = $value;
			} else {
				$this->value = null;
			}
			return $this;
		}

		/**
		 * Se obtiene un objeto BoxScoreDataWeekly con los atributos de la clase
		 *
		 * @return BoxScoreDataWeekly
		 */
		public static function getInstance () {
			return new self ();
		}
	}
