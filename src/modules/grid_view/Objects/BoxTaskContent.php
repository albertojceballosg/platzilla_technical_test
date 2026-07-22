<?php
	require_once ('include/platzilla/Exceptions/GridViewException.php');
	require_once ('include/platzilla/Objects/GridViewInterface.php');

	/**
	 * Class BoxTaskContent
	 *
	 * Clase donde encuentran implementadas las tareas (actividades) de uso exclusivo de las Vista Cuadricula
	 */
	class BoxTaskContent implements GridViewInterface {

		/** @var integer */
		private $activityId;

		/** @var string */
		private $activityType;

		/** @var DateTime */
		private $dateEnd;

		/** @var string */
		private $dateSet;

		/** @var  DateTime */
		private $dateStart;

		/** @var string */
		private $description;

		/** @var string */
		private $dueDate;

		/** @var string */
		private $eventStatus;

		/** @var string */
		private $firstName;

		/** @var boolean */
		private $isLate;

		/** @var string */
		private $lastName;

		/** @var string */
		private $location;

		/** @var string */
		private $priority;

		/** @var float */
		private $progress;

		/** @var string */
		private $subject;

		/** @var datetime */
		private $timeStart;

		/** @var datetime */
		private $timeEnd;

		/** @var string */
		private $estimatedTimeUnit;

		/**
		 * Obtiene el Id de la actividad
		 *
		 * @return integer
		 */
		public function getActivityId () {
			return $this->activityId;
		}

		/**
		 * Obtiene el tipo de actividad
		 *
		 * @return string
		 */
		public function getActivityType () {
			return $this->activityType;
		}

		/**
		 * Obtiene la fecha de fin de la actividad
		 *
		 * @return datetime
		 */
		public function getDateEnd () {
			return $this->dateEnd;
		}

		/**
		 * Obtiene la fecha establecida para la actividad
		 *
		 * @return string
		 */
		public function getDateSet () {
			return $this->dateSet;
		}

		/**
		 * Obtiene la fecha de inicio de la actividad
		 *
		 * @return datetime
		 */
		public function getDateStart () {
			return $this->dateStart;
		}

		/**
		 * Obtiene la descripción de la actividad
		 *
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * Obtiene la fecha de vencimiento de la actividad
		 *
		 * @return string
		 */
		public function getDueDate () {
			return $this->dueDate;
		}

		/**
		 * Obtiene el estatus de la actividad/evento
		 *
		 * @return string
		 */
		public function getEventStatus () {
			return $this->eventStatus;
		}

		/**
		 * Obtiene el primer nombre de la actividad
		 *
		 * @return string
		 */
		public function getFirstName () {
			return $this->firstName;
		}

		/**
		 * @return boolean
		 */
		public function isLate () {
			return $this->isLate;
		}

		/**
		 * Obtiene el ultimo nombre de la actividad
		 *
		 * @return string
		 */
		public function getLastName () {
			return $this->lastName;
		}

		/**
		 * Obtiene la localizacion de la actividad
		 *
		 * @return string
		 */
		public function getLocation () {
			return $this->location;
		}

		/**
		 * Obtiene la priorización de la actividad
		 *
		 * @return string
		 */
		public function getPriority () {
			return $this->priority;
		}

		/**
		 * Obtiene el progreso de la actividad
		 *
		 * @return float
		 */
		public function getProgress () {
			return $this->progress;
		}

		/**
		 * Obtiene el asunto de la actividad
		 *
		 * @return string
		 */
		public function getSubject () {
			return $this->subject;
		}

		/**
		 * Obtiene la hora de fin de la actividad
		 *
		 * @return datetime
		 */
		public function getTimeEnd () {
			return $this->timeEnd;
		}

		/**
		 * Obtiene la hora de inicio de la actividad
		 *
		 * @return datetime
		 */
		public function getTimeStart () {
			return $this->timeStart;
		}

		/**
		 * Obtiene la unidad de tiempo estimada
		 *
		 * @return string
		 */
		public function getEstimatedTimeUnit () {
			return $this->estimatedTimeUnit;
		}

		/**
		 * Establece el Id para la actividad
		 *
		 * @param integer $activityId
		 *
		 * @return BoxTaskContent
		 */
		public function setActivityId ($activityId) {
			$this->activityId = $activityId;
			return $this;
		}

		/**
		 * Establece el tipo de actividad
		 *
		 * @param string $activityType
		 *
		 * @return BoxTaskContent
		 */
		public function setActivityType ($activityType) {
			$this->activityType = $activityType;
			return $this;
		}

		/**
		 * Esteblece la fecha de finalización de la actividad
		 *
		 * @param string $dateEnd
		 *
		 * @return BoxTaskContent
		 */
		public function setDateEnd ($dateEnd) {
			if (!empty($dateEnd)) {
				$this->dateEnd = DateTime::createFromFormat('Y-m-d', $dateEnd);
			} else {
				$this->dateEnd = null;
			}
			return $this;
		}

		/**
		 * Establece la fecha de la actividad
		 *
		 * @param string$dateStart
		 * @param string $timeStart
		 * @param string $format
		 *
		 * @return BoxTaskContent
		 */
		public function setDateSet ($dateStart, $timeStart, $format) {
			if (!empty($dateStart) && !empty($timeStart)) {
				setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
				$this->dateSet = ucwords(mb_convert_encoding(strftime ($format, strtotime ($dateStart . ' '. $timeStart)), 'UTF-8', 'ISO-8859-1'));
			} else {
				$this->dateSet = null;
			}
			return $this;
		}

		/**
		 * Establece la fecha de inicio para la actividad
		 *
		 * @param string $dateStart
		 *
		 * @return BoxTaskContent
		 */
		public function setDateStart ($dateStart) {
			if (!empty($dateStart)) {
				$this->dateStart = DateTime::createFromFormat ('Y-m-d', $dateStart);
				$this->setIsLate ();
			} else {
				$this->dateStart = null;
			}
			return $this;
		}

		/**
		 * Establece la descripción de la actividad
		 *
		 * @param string $description
		 *
		 * @return BoxTaskContent
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * Establece la fecha de vencimiento para la actividad
		 *
		 * @param string $dueDate
		 * @param string $timeEnd
		 * @param string $format
		 *
		 * @return BoxTaskContent
		 */
		public function setDueDate ($dueDate, $timeEnd, $format) {
			if (!empty($dueDate) && !empty($timeEnd)) {
				setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
				$this->dueDate = ucwords(mb_convert_encoding(strftime ($format, strtotime ($dueDate . ' '. $timeEnd)), 'UTF-8', 'ISO-8859-1'));
			} else {
				$this->dueDate = null;
			}
			return $this;
		}

		/**
		 * Establece el estatus para las actividades/eventos
		 *
		 * @param string $eventStatus
		 *
		 * @return  BoxTaskContent
		 */
		public function setEventStatus ($eventStatus) {
			$this->eventStatus = $eventStatus;
			return $this;
		}

		/**
		 * Establece el nombre de la actividad
		 *
		 * @param string $firstName
		 *
		 * @return BoxTaskContent
		 */
		public function setFirstName ($firstName) {
			$this->firstName = $firstName;
			return $this;
		}

		/**
		 * @return BoxTaskContent
		 */
		public function setIsLate () {
			if (! empty($this->dateStart)) {
				$today = new DateTime('now');
				$this->isLate = ($today < $this->dateStart);
			} else {
				$this->isLate = false;
			}
			return $this;
		}

		/**
		 * Establece el segundo nombre para la actividad
		 *
		 * @param string $lastName
		 *
		 * @return BoxTaskContent
		 */
		public function setLastName ($lastName) {
			$this->lastName = $lastName;
			return $this;
		}

		/**
		 * Establece la localizacion para la actividad
		 *
		 * @param string $location
		 *
		 * @return  BoxTaskContent
		 */
		public function setLocation ($location) {
			$this->location = $location;
			return $this;
		}

		/**
		 * Establece la prioridad para la actividad
		 *
		 * @param string $priority
		 *
		 * @return  BoxTaskContent
		 */
		public function setPriority ($priority) {
			$this->priority = $priority;
			return $this;
		}

		/**
		 * Establece el progreso de la actividad
		 *
		 * @param float $progress
		 *
		 * @return  BoxTaskContent
		 */
		public function setProgress ($progress) {
			$this->progress = $progress;
			return $this;
		}

		/**
		 * Establece el asunto de la actividad
		 *
		 * @param string $subject
		 *
		 * @return  BoxTaskContent
		 */
		public function setSubject ($subject) {
			$this->subject = $subject;
			return $this;
		}

		/**
		 * Establece el fin del tiempo de la actividad
		 *
		 * @param string $timeEnd
		 *
		 * @return  BoxTaskContent
		 */
		public function setTimeEnd ($timeEnd) {
			if (!empty($timeEnd)) {
				$this->timeEnd = DateTime::createFromFormat ('H:i:s', $timeEnd);
			} else {
				$this->timeEnd = null;
			}
			return $this;
		}

		/**
		 * Establece la hora de inicio de la actividad
		 *
		 * @param string $timeStart
		 *
		 * @return  BoxTaskContent
		 */
		public function setTimeStart ($timeStart) {
			if (!empty($timeStart)) {
				$this->timeStart = DateTime::createFromFormat ('H:i:s', $timeStart);
			} else {
				$this->timeStart = null;
			}
			return $this;
		}

		/**
		 * Establece la unidad de tiempo estimada
		 *
		 * @param string $estimatedTimeUnit
		 *
		 * @return  BoxTaskContent
		 */
		public function setEstimatedTimeUnit ($estimatedTimeUnit) {
			$this->estimatedTimeUnit = $estimatedTimeUnit;
			return $this;
		}

		/**
		 * Instanciación de la clase BoxTaskContent. Se obtiene un objeto BoxTaskContent con los atributos de la clase
		 *
		 * @return BoxTaskContent
		 */
		public static function getInstance () {
			return new self ();
		}

	}
