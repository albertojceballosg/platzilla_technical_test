<?php

	require_once ('modules/News/Exceptions/AdQueueException.php');
	require_once ('modules/News/Objects/AdQueueInterface.php');

	class AdQueue implements AdQueueInterface {

		/** @var DateTime  */
		private $createDate;

		/** @var string */
		private $description;

		/** @var integer[] */
		private $clientIds;

		/** @var integer */
		private $id;

		/** @var string */
		private $initDay;

		/** @var string */
		private $name;

		/** @var News[] */
		private $news;

		/** @var  string */
		private $period;

		/** @var string */
		private $status;

		/**
		 * @return DateTime
		 */
		public function getCreateDate () {
			return $this->createDate;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return integer[]
		 */
		public function getClientIds () {
			return $this->clientIds;
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
		public function getName () {
			return $this->name;
		}

		/**
		 * @return News[]
		 */
		public function getNews () {
			return $this->news;
		}

		/**
		 * @return string
		 */
		public function getPeriod () {
			return $this->period;
		}

		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * @param DateTime $createDate
		 *
		 * @return AdQueue
		 */
		public function setCreateDate ($createDate) {
			if (!empty($createDate)) {
				$this->createDate = DateTime::createFromFormat ('Y-m-d H:i:s', $createDate);
			} else {
				$this->createDate = null;
			}
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return AdQueue
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param integer[] $clientIds
		 *
		 * @return AdQueue
		 */
		public function setClientIds ($clientIds) {
			if (count ($clientIds)) {
				foreach ($clientIds as $id) {
					if (!is_integer($id) && $id <= 0) {
						continue;
					}
					$this->clientIds [] = $id;
				}
			} else {
				$this->clientIds = array ();
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return AdQueue
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string$initDay
		 * @param null|string $format
		 *
		 * @return AdQueue
		 */
		public function setInitDay ($initDay, $format = null) {
			$format = (empty ($format)) ? $format = '%A %d de %B - %Y, %H:%M:%S' : $format;
			if (!empty($initDay)) {
				setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
				$this->initDay = ucwords (mb_convert_encoding(strftime ($format, strtotime ($initDay)), 'UTF-8', 'ISO-8859-1'));
			} else {
				$this->initDay = $initDay;
			}
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return AdQueue
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param News[] $news
		 *
		 * @return AdQueue
		 */
		public function setNews ($news) {
			if (count ($news)) {
				foreach ($news as $thisNews) {
					if (! $thisNews instanceof News) {
						continue;
					}
					$this->news [] = $thisNews;
				}
			} else {
				$this->news = null;
			}
			return $this;
		}

		/**
		 * @param string $period
		 *
		 * @return AdQueue
		 */
		public function setPeriod ($period) {
			$this->period = $period;
			return $this;
		}

		/**
		 * @param string $status
		 *
		 * @return AdQueue
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}

		/**
		 * @return AdQueue
		 */
		public static function getInstance () {
			return new self();
		}

	}
