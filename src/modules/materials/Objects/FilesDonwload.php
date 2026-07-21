<?php
	require_once ('modules/materials/Exceptions/FolderException.php');
	require_once ('modules/materials/Objects/Document.php');
	require_once ('modules/materials/Objects/FolderInterface.php');
	class FilesDonwload implements FolderInterface {

		/** @var Document */
		private $document;

		/** @var string */
		private $lastTime;

		/** @var integer */
		private $userId;

		/**
		 * @param integer $original
		 *
		 * @return null|string
		 */
		private function timeSince ($original) {
			$chunks = array (
				array (60 * 60 * 24 * 365, 'año'),
				array (60 * 60 * 24 * 30, 'mes'),
				array (60 * 60 * 24 * 7, 'sem'),
				array (60 * 60 * 24, 'día'),
				array (60 * 60, 'h'),
				array (60, 'min'),
			);
			$today  = time ();
			$since  = ($today - intval ($original));
			if ($since < 0) {
				return null;
			}
			$j = count ($chunks);
			for ($i = 0; $i < $j; $i++) {
				$seconds = $chunks[ $i ][0];
				$name    = $chunks[ $i ][1];
				$count   = floor ($since / $seconds);
				if ($count != 0) {
					$print = ($count == 1) ? '1 ' . $name : (($name == 'mes') ? "$count {$name}es" : "$count {$name}s");
					if (($i + 1) < $j) {
						$secondsTwo = $chunks[ ($i + 1) ][0];
						$nameTwo    = $chunks[ ($i + 1) ][1];
						$countTwo   = floor (($since - ($seconds * $count)) / $secondsTwo);
						if ($countTwo != 0) {
							$print .= ($countTwo == 1) ? ', 1 ' . $nameTwo : ", $countTwo {$nameTwo}s";
						}
					}
					break;
				}
			}
			return isset ($print) ? $print : null;
		}

		/**
		 * @return Document
		 */
		public function getDocument () {
			return $this->document;
		}

		/**
		 * @return string
		 */
		public function getLastTime () {
			return $this->lastTime;
		}

		/**
		 * @return integer
		 */
		public function getUserId () {
			return $this->userId;
		}

		/**
		 * @param Document $document
		 *
		 * @return FilesDonwload
		 */
		public function setDocument ($document) {
			if($document instanceof Document) {
				$this->document = $document;
			} else {
				$this->document = null;
			}
			return $this;
		}

		/**
		 * @param string $lastTime
		 *
		 * @return FilesDonwload
		 */
		public function setLastTime ($lastTime) {
			if ($lastTime) {
				$this->lastTime = $this->timeSince ($lastTime);
			}
			return $this;
		}

		/**
		 * @param integer $userId
		 *
		 * @return FilesDonwload
		 */
		public function setUserId ($userId) {
			$this->userId = $userId;
			return $this;
		}

		/**
		 * @return FilesDonwload
		 */
		public static function getInstance () {
			return new self ();
		}

	}
