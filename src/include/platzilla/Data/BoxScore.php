<?php
	require_once ('include/platzilla/Data/BoxScoreData.php');
	require_once ('include/platzilla/Data/BoxScoreDataWeekly.php');

	/**
	 * Class BoxScore
	 *
	 * Esta clase hace referencia a los métodos que gestionan la tabla vtiger_boxscore
	 */
	class BoxScore {

		/** @var string */
		private $appCode;

		/** @var string */
		private $createdDate;

		/** @var BoxScoreData[] */
		private $dataScores;

		/** @var  boolean */
		private $default;

		/** @var string */
		private $description;

		/** @var integer */
		private $id;

		/** @var  string */
		private $fieldName;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $scale;

		/** @var  string */
		private $title;

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
		 * Obtiene el código de la aplicacion seleccionada para el indicador
		 *
		 * @return string
		 */
		public function getAppCode () {
			return $this->appCode;
		}

		/**
		 * Devuelve la fecha de creación del indicador
		 *
		 * @return string
		 */
		public function getCreatedDate () {
			return $this->createdDate;
		}

		/**
		 * Devuelve colección de objetos BoxScoreData
		 *
		 * @return BoxScoreData[]
		 */
		public function getDataScores() {
			return $this->dataScores;
		}

		/**
		 * Devuelve la descripción del indicador
		 *
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * Devuelve el nombre del campo usado como indicador
		 *
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * Devuelve el ID del indocador
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Devuelve el nombre del módulo usado por el indicador
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Obtiene el tipo de scala week/month
		 *
		 * @return string
		 */
		public function getScale () {
			return $this->scale;
		}

		/**
		 * Obtiene el título del indicador
		 *
		 * @return string
		 */
		public function getTitle () {
			return $this->title;
		}

		/**
		 * Devuelve la condición si el indicador es por defecto
		 *
		 * @return boolean
		 */
		public function isDefault () {
			return $this->default;
		}

		/**
		 * Establece el código del la aplicación
		 *
		 * @param string $appCode
		 *
		 * @return BoxScore
		 */
		public function setAppCode ($appCode) {
			if (is_scalar ($appCode)) {
				$this->appCode = $appCode;
			} else {
				$this->appCode = null;
			}
			return $this;
		}

		/**
		 * Establece la fecha de creación del indicador
		 *
		 * @param string $createdDate
		 *
		 * @return BoxScore
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
		 * Establece una colección de objetos BoxScoreData
		 *
		 * @param BoxScoreData[] $dataScores
		 *
		 * @return BoxScore
		 */
		public function setDataScores ($dataScores) {
			foreach ($dataScores as $score) {
				if (($score == null) || ($score instanceof BoxScoreData) && (!empty ($score))) {
					$this->dataScores [] = $score;
				}
			}
			return $this;
		}

		/**
		 * Establece un indicador como por defecto
		 *
		 * @param boolean $default
		 *
		 * @return BoxScore
		 */
		public function setDefault ($default) {
			if (is_bool ($default)) {
				$this->default = $default;
			}
			return $this;
		}

		/**
		 * Establece la descripción de un indicador
		 *
		 * @param string $description
		 *
		 * @return BoxScore
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
		 * Establece el nombre del campo usaudo en el indicador
		 *
		 * @param string $fieldName
		 *
		 * @return BoxScore
		 */
		public function setFieldName ($fieldName) {
			if (is_scalar ($fieldName)) {
				$this->fieldName = $fieldName;
			} else {
				$this->fieldName = null;
			}
			return $this;
		}

		/**
		 * Establece el ID del indicador
		 *
		 * @param integer $id
		 *
		 * @return BoxScore
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
		 * Establece el nombre del módulo seleccionado
		 *
		 * @param string $moduleName
		 *
		 * @return BoxScore
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
		 * Establece la escala para el indicador
		 *
		 * @param string $scale
		 *
		 * @return BoxScore
		 */
		public function setScale ($scale) {
			if (is_scalar ($scale)) {
				$this->scale = $scale;
			} else {
				$this->scale = null;
			}
			return $this;
		}

		/**
		 * Establece el título para el indoicador
		 *
		 * @param string $title
		 *
		 * @return BoxScore
		 */
		public function setTitle ($title) {
			if (is_scalar ($title)) {
				$this->title = $title;
			} else {
				$this->title = null;
			}
			return $this;
		}

		/**
		 * Se obtiene un objeto BoxScore con los atributos de la clase
		 *
		 * @return BoxScore
		 */
		public static function getInstance () {
			return new self ();
		}

	}
