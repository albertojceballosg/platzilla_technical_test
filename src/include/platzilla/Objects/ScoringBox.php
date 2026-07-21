<?php
	require_once ('include/platzilla/Objects/ScoringBoxInterface.php');
	class ScoringBox implements ScoringBoxInterface {

		/** @var string */
		private $appCode;

		/** @var BlockScoreBox[] */
		private $blockScoreBoxes;

		/** @var string */
		private $createdDate;

		/** @var boolean */
		private $default;

		/** @var string */
		private $description;

		/** @var integer */
		private $id;

		/** @var string */
		private $scale;

		/** @var string */
		private $title;

		/**
		 * @param BlockScoreBox[] $blockScoreBoxes
		 *
		 * @return array|null
		 */
		private function copyBlockScoreBoxes ($blockScoreBoxes) {
			if (empty($blockScoreBoxes)) {
				return null;
			}
			$blockScoring = array ();
			foreach ($blockScoreBoxes as $blockScoreBox) {
				if (empty ($blockScoreBox)) {
					continue;
				}
				$blockScoring [] = $blockScoreBox->duplicate ();
			}
			return $blockScoring;
		}

		/**
		 * @param BlockScoreBox[] $blockScoreBoxes
		 *
		 * @return array
		 */
		private function duplicateFromBlockScoreBoxes ($blockScoreBoxes) {
			$blockScoring = array ();
			foreach ($blockScoreBoxes as $blockScoreBox) {
				$blockScoring [] = $blockScoreBox->duplicate ($blockScoreBox->getId ());
			}
			return $blockScoring;
		}

		/**
		 * @param BlockScoreBox[] $theseBlockScore
		 * @param BlockScoreBox[] $thoseBlockScore
		 *
		 * @return boolean
		 */
		private function isScoreBoxEqualTo ($theseBlockScore, $thoseBlockScore) {
			$totalBlockScore = count ($theseBlockScore);
			$equals          = false;
			if ($totalBlockScore != count ($thoseBlockScore)) {
				return false;
			}

			for ($k = 0; $k < $totalBlockScore; $k++) {
				if ($theseBlockScore [ $k ]->isEqualTo ($thoseBlockScore [ $k ])) {
					$equals = true;
					$k      = ($totalBlockScore + 1);
				}
			}
			return $equals;
		}

		/**
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
		 * @param ScoringBox $score
		 */
		public function copyValuesFrom ($score) {
			if ((empty ($score)) || (!($score instanceof ScoringBox))) {
				return;
			}
			$this->appCode         = $score->getAppCode ();
			$this->blockScoreBoxes = $this->copyBlockScoreBoxes ($score->getBlockScoreBoxes ());
			$this->createdDate     = $score->getCreatedDate ();
			$this->default         = $score->isDefault ();
			$this->description     = $score->getDescription ();
			$this->id              = $score->getId();
			$this->scale           = $score->getScale ();
			$this->title           = $score->getTitle ();
		}

		/**
		 * @param null|integer $newScoreId
		 *
		 * @return ScoringBox
		 */
		public function duplicate ($newScoreId = null) {
			$object = new self ();
			return $object->setId ($newScoreId)
				->setAppCode ($this->appCode)
				->setBlockScoreBoxes ($this->duplicateFromBlockScoreBoxes ($this->blockScoreBoxes))
				->setCreatedDate ($this->createdDate)
				->setDefault ($this->default)
				->setDescription ($this->description)
				->setScale ($this->scale)
				->setTitle ($this->title);
		}

		/**
		 * @return string
		 */
		public function getAppCode () {
			return $this->appCode;
		}

		/**
		 * @return BlockScoreBox[]
		 */
		public function getBlockScoreBoxes () {
			return $this->blockScoreBoxes;
		}

		/**
		 * @return string
		 */
		public function getCreatedDate() {
			return $this->createdDate;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
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
		public function getScale () {
			return $this->scale;
		}

		/**
		 * @return string
		 */
		public function getTitle () {
			return $this->title;
		}

		/**
		 * @return boolean
		 */
		public function isDefault () {
			return $this->default;
		}

		/**
		 * @param ScoringBox $score
		 *
		 * @return boolean
		 */
		public function isEqualTo ($score) {
			if (
				(empty ($score)) ||
				($this->appCode != $score->getAppCode ()) ||
				($this->createdDate != $score->getCreatedDate ()) ||
				($this->default != $score->isDefault ()) ||
				($this->description != $score->getDescription ()) ||
				($this->scale != $score->getScale ()) ||
				($this->title != $score->getTitle ()) ||
				$this->isScoreBoxEqualTo($this->blockScoreBoxes, $score->getBlockScoreBoxes())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @param string $appCode
		 *
		 * @return ScoringBox
		 */
		public function setAppCode($appCode) {
			if (is_scalar ($appCode)) {
				$this->appCode = $appCode;
			} else {
				$this->appCode = null;
			}
			return $this;
		}

		/**
		 * @param BlockScoreBox[] $blockScoreBoxes
		 *
		 * @return ScoringBox
		 */
		public function setBlockScoreBoxes ($blockScoreBoxes) {
			if ($blockScoreBoxes == null) {
				$this->blockScoreBoxes [] = $blockScoreBoxes;
				return $this;
			}
			foreach ($blockScoreBoxes as $blockScore) {
				if (($blockScore == null) || ($blockScore instanceof BlockScoreBox) && (!empty ($blockScore))) {
					$this->blockScoreBoxes [] = $blockScore;
				}
			}
			return $this;
		}

		/**
		 * @param string $createdDate
		 *
		 * @return ScoringBox
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
		 * @param boolean $default
		 *
		 * @return ScoringBox
		 */
		public function setDefault ($default) {
			if (is_bool ($default)) {
				$this->default = $default;
			}
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return ScoringBox
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
		 * @param integer $id
		 *
		 * @return ScoringBox
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
		 * @param string $scale
		 *
		 * @return ScoringBox
		 */
		public function setScale ($scale) {
			if (in_array ($scale, array (self::SCALE_MONTH, self::SCALE_WEEK))) {
				$this->scale = $scale;
			} else {
				$this->scale = null;
			}
			return $this;
		}

		/**
		 * @param string $title
		 *
		 * @return ScoringBox
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
		 * @return ScoringBox
		 */
		public static function getInstance () {
			return new self ();
		}

	}
