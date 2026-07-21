<?php
	require_once ('modules/News/Exceptions/AdQueueException.php');
	require_once ('modules/News/Objects/AdQueueInterface.php');

	class News implements AdQueueInterface {

		/** @var AdQueue */
		private $adQueue;

		/** @var string */
		private $categories;

		/** @var string */
		private $content;

		/** @var DateTime */
		private $createDate;

		/** @var string */
		protected $createDateFormat;

		/** @var string */
		private $dueDate;

		/** @var string */
		private $endDate;

		/** @var integer */
		private $id;

		/** @var string */
		private $initDay;

		/** @var string */
		private $startDate;

		/** @var array */
		private $sharing;

		/** @var string */
		private $status;

		/** @var string */
		private $title;

		/**
		 * @return AdQueue
		 */
		public function getAdQueue () {
			return $this->adQueue;
		}

		/**
		 * @return string
		 */
		public function getCategories () {
			return $this->categories;
		}

		/**
		 * @return string
		 */
		public function getContent () {
			return $this->content;
		}

		/**
		 * @return DateTime
		 */
		public function getCreateDate () {
			return $this->createDate;
		}

		/**
		 * @return string
		 */
		public function getCreateDateFormat () {
			return $this->createDateFormat;
		}

		/**
		 * @return string
		 */
		public function getDueDate () {
			return $this->dueDate;
		}

		/**
		 * @return string
		 */
		public function getEndDate () {
			return $this->endDate;
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
		public function getInitDay () {
			return $this->initDay;
		}

		/**
		 * @return string
		 */
		public function getStartDate () {
			return $this->startDate;
		}

		/**
		 * @return array
		 */
		public function getSharing () {
			return $this->sharing;
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
		public function getTitle () {
			return $this->title;
		}

		/**
		 * @param $adQueue
		 *
		 * @return News
		 */
		public function setAdQueue ($adQueue) {
			if ($adQueue instanceof AdQueue) {
				$this->adQueue = $adQueue;
			} else {
				$this->adQueue = null;
			}
			return $this;
		}

		/**
		 * @param $categories
		 *
		 * @return News
		 */
		public function setCategories ($categories) {
			$this->categories = $categories;
			return $this;
		}

		/**
		 * @param string $content
		 *
		 * @return News
		 */
		public function setContent ($content) {
			$this->content = $content;
			return $this;
		}

		/**
		 * @param $createDate
		 *
		 * @return News
		 */
		public function setCreateDate ($createDate) {
			if (!empty($createDate)) {
				$this->createDate = DateTime::createFromFormat ('Y-m-d H:i:s', $createDate);
			} else {
				$this->createDate = null;
			}
			$this->setCreateDateFormat ($createDate);
			return $this;
		}

		/**
		 * @param $creteDate
		 */
		public function setCreateDateFormat ($creteDate) {
			$format = '%A %d de %B - %Y, %H:%M:%S';
			if (!empty($creteDate)) {
				setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
				$this->createDateFormat = ucwords (utf8_encode(strftime ($format, strtotime ($creteDate))));
			} else {
				$this->createDateFormat = null;
			}
		}

		/**
		 * @param $dueDate
		 * @param null $format
		 *
		 * @return News
		 */
		public function setDueDate ($dueDate, $format = null) {
			$format = (empty ($format)) ? $format = '%A %d de %B - %Y, %H:%M:%S' : $format;
			if (!empty($dueDate)) {
				setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
				$this->dueDate = ucwords (utf8_encode(strftime ($format, strtotime ($dueDate))));
			} else {
				$this->dueDate = null;
			}
			return $this;
		}

		/**
		 * @param string $endDate
		 *
		 * @return News
		 */
		public function setEndDate ($endDate) {
			if (!empty($endDate)) {
				$this->endDate = $endDate;
			} else {
				$this->endDate = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return News
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $initDay
		 * @param null $format
		 *
		 * @return News
		 */
		public function setInitDay ($initDay, $format = null) {
			$format = (empty ($format)) ? $format = '%A %d de %B - %Y, %H:%M:%S' : $format;
			if (!empty($initDay)) {
				setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
				$this->initDay = ucwords (utf8_encode(strftime ($format, strtotime ($initDay))));
			} else {
				$this->initDay = null;
			}
			return $this;
		}

		/**
		 * @param string $startDate
		 *
		 * @return News
		 */
		public function setStartDate ($startDate) {
			if (!empty($startDate)) {
				$this->startDate = $startDate;
			} else {
				$this->startDate = null;
			}
			return $this;
		}

		/**
		 * @param array|null$sharing
		 *
		 * @return News
		 */
		public function setSharing ($sharing) {
			if (!empty ($sharing)) {
				$this->sharing = $sharing;
			} else {
				$this->sharing = null;
			}
			return $this;
		}

		/**
		 * @param string $status
		 *
		 * @return News
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}

		/**
		 * @param string $title
		 *
		 * @return News
		 */
		public function setTitle ($title) {
			$this->title = $title;
			return $this;
		}

		/**
		 * @return News
		 */
		public static function getInstance () {
			return new self ();
		}

	}
