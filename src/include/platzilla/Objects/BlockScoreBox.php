<?php

	class BlockScoreBox {

		/** @var integer */
		private $blockNumber;

		/** @var integer */
		private $blockRel;

		/** @var string */
		private $colorBase;

		/** @var string */
		private $colorDegrade;

		/** @var integer */
		private $id;

		/** @var boolean */
		private $locked;

		/** @var integer */
		private $scoringBoxId;

		/** @var ScoringDataBox[] */
		private $scoringDataBoxes;

		/** @var integer */
		private $user;

		/**
		 * @param ScoringDataBox[] $scoreDataBoxes
		 *
		 * @return array|null
		 */
		private function copyScoringDataBox ($scoreDataBoxes) {
			if (empty($scoreDataBoxes)) {
				return null;
			}
			$scoringData = array ();
			foreach ($scoreDataBoxes as $scoreDataBox) {
				$scoringData [] = $scoreDataBox->duplicate ($scoreDataBox->getId());
			}
			return $scoringData;
		}

		/**
		 * @param ScoringDataBox[] $scoreDataBoxes
		 *
		 * @return array
		 */
		private function duplicateFromScoringDataBox ($scoreDataBoxes) {
			$scoringData = array ();
			foreach ($scoreDataBoxes as $scoreDataBox) {
				$scoringData [] = $scoreDataBox->duplicate ($scoreDataBox->getId ());
			}
			return $scoringData;
		}

		/**
		 * @param ScoringDataBox[] $theseScoringData
		 * @param ScoringDataBox[] $thoseScoringData
		 *
		 * @return boolean
		 */
		private function isScoringDataEqualTo ($theseScoringData, $thoseScoringData) {
			$totalScoringData = count ($theseScoringData);
			$equals              = false;
			if ($totalScoringData != count ($thoseScoringData)) {
				return false;
			}

			for ($k = 0; $k < $totalScoringData; $k++) {
				if ($theseScoringData [ $k ]->isEqualTo ($thoseScoringData [ $k ])) {
					$equals = true;
					$k      = ($totalScoringData + 1);
				}
			}
			return $equals;
		}

		/**
		 * @param BlockScoreBox $blockScore
		 */
		public function copyValuesFrom ($blockScore) {
			if ((empty ($blockScore)) || (!($blockScore instanceof BlockScoreBox))) {
				return;
			}
			$this->blockNumber      = $blockScore->getBlockNumber ();
			$this->blockRel         = $blockScore->getBlockRel ();
			$this->colorBase        = $blockScore->getColorBase ();
			$this->colorDegrade     = $blockScore->getColorDegrade ();
			$this->locked           = $blockScore->isLocked ();
			$this->scoringBoxId     = $blockScore->getScoringBoxId ();
			$this->scoringDataBoxes = $this->copyScoringDataBox ($blockScore->getScoringDataBoxes ());
			$this->user             = $blockScore->getUser ();
		}

		/**
		 * @param null|integer $newBlockScoreId
		 *
		 * @return BlockScoreBox
		 */
		public function duplicate ($newBlockScoreId = null) {
			$object = new self ();
			return $object->setId($newBlockScoreId)
				->setBlockNumber ($this->blockNumber)
				->setBlockRel ($this->blockRel)
				->setColorBase ($this->colorBase)
				->setColorDegrade ($this->colorDegrade)
				->setLocked ($this->locked)
				->setScoringBoxId ($this->scoringBoxId)
				->setScoringDataBoxes($this->duplicateFromScoringDataBox ($this->scoringDataBoxes))
				->setUser ($this->user);
		}

		/**
		 * @return integer
		 */
		public function getBlockNumber () {
			return $this->blockNumber;
		}

		/**
		 * @return integer
		 */
		public function getBlockRel () {
			return $this->blockRel;
		}

		/**
		 * @return string
		 */
		public function getColorBase () {
			return $this->colorBase;
		}

		/**
		 * @return string
		 */
		public function getColorDegrade () {
			return $this->colorDegrade;
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
		public function getScoringBoxId () {
			return $this->scoringBoxId;
		}

		/**
		 * @return ScoringDataBox[]
		 */
		public function getScoringDataBoxes () {
			return $this->scoringDataBoxes;
		}

		/**
		 * @return integer
		 */
		public function getUser () {
			return $this->user;
		}

		/**
		 * @param BlockScoreBox $blockScore
		 *
		 * @return boolean
		 */
		public function isEqualTo ($blockScore) {
			if (
				(empty ($blockScore)) ||
				($this->blockNumber != $blockScore->getBlockNumber ()) ||
				($this->blockRel != $blockScore->getBlockRel ()) ||
				($this->colorBase != $blockScore->getColorBase ()) ||
				($this->colorDegrade != $blockScore->getColorDegrade ()) ||
				($this->scoringBoxId != $blockScore->getScoringBoxId ()) ||
				$this->isScoringDataEqualTo($this->scoringDataBoxes, $blockScore->getScoringDataBoxes ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * @param integer $blockNumber
		 *
		 * @return BlockScoreBox
		 */
		public function setBlockNumber ($blockNumber) {
			if (is_numeric ($blockNumber)) {
				$this->blockNumber = $blockNumber;
			} else {
				$this->blockNumber = null;
			}
			return $this;
		}

		/**
		 * @param integer $blockRel
		 *
		 * @return BlockScoreBox
		 */
		public function setBlockRel ($blockRel) {
			if ((is_numeric ($blockRel)) && ($blockRel > 0) && (intval ($blockRel) == $blockRel)) {
				$this->blockRel = $blockRel;
			} else {
				$this->blockRel = null;
			}
			return $this;
		}

		/**
		 * @param string $colorBase
		 *
		 * @return BlockScoreBox
		 */
		public function setColorBase ($colorBase) {
			if (is_scalar ($colorBase)) {
				$this->colorBase = $colorBase;
			} else {
				$this->colorBase = null;
			}
			return $this;
		}

		/**
		 * @param string $colorDegrade
		 *
		 * @return BlockScoreBox
		 */
		public function setColorDegrade ($colorDegrade) {
			if (is_scalar ($colorDegrade)) {
				$this->colorDegrade = $colorDegrade;
			} else {
				$this->colorDegrade = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return BlockScoreBox
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
		 * @param boolean $locked
		 *
		 * @return BlockScoreBox
		 */
		public function setLocked ($locked) {
			if ($locked) {
				$this->locked = 1;
			} else {
				$this->locked = 0;
			}
			return $this;
		}

		/**
		 * @param integer $scoreBoxId
		 *
		 * @return BlockScoreBox
		 */
		public function setScoringBoxId ($scoreBoxId) {
			if ((is_numeric ($scoreBoxId)) && ($scoreBoxId > 0) && (intval ($scoreBoxId) == $scoreBoxId)) {
				$this->scoringBoxId = $scoreBoxId;
			} else {
				$this->scoringBoxId = null;
			}
			return $this;
		}

		/**
		 * @param ScoringDataBox[] $scoringDataBoxes
		 *
		 * @return BlockScoreBox
		 */
		public function setScoringDataBoxes ($scoringDataBoxes) {
			if (is_array ($scoringDataBoxes)) {
				foreach ($scoringDataBoxes as $scoringData) {
					if (($scoringData == null) || ($scoringData instanceof ScoringDataBox) && (!empty ($scoringData))) {
						$this->scoringDataBoxes [] = $scoringData;
					}
				}
			} else {
				$this->scoringDataBoxes = array();
			}
			return $this;
		}

		/**
		 * @param integer $user
		 *
		 * @return BlockScoreBox
		 */
		public function setUser ($user) {
			if ((is_numeric ($user)) && ($user > 0) && (intval ($user) == $user)) {
				$this->user = $user;
			} else {
				$this->user = null;
			}
			return $this;
		}

		/**
		 * @return BlockScoreBox
		 */
		public static function getInstance () {
			return new self ();
		}

	}
