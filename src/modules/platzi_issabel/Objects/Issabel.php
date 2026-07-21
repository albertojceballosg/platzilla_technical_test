<?php
	require_once ('modules/platzi_issabel/Exceptions/PlatziIssabelException.php');
	require_once ('modules/platzi_issabel/Objects/PlatziIssabelInterface.php');
	
	class Issabel implements PlatziIssabelInterface {
		
		/** @var string */
		private $date;
		
		/** @var string */
		private $destination;
		
		/** @var string */
		private $duration;
		
		/** @var string */
		private $message;
		
		/** @var string */
		private $origin;
		
		/** @var string */
		private $time;
		
		/** @var integer */
		private $totalRecords;
		
		/** @var string */
		private $type;
		
		/** @var string */
		private $uniqueId;
		
		
		/**
		 * @param integer $sec
		 *
		 * @return string
		 */
		private function SecToHHMMSS($sec) {
		    $HH       = 0;
			$MM       = 0;
		    $segundos = $sec;
		
		    if ($segundos/3600 >= 1) {
				$HH       = (int)($segundos / 3600);
				$segundos = ($segundos % 3600);
			}
			if ($HH < 10) {
				$HH = "0$HH";
			};
		    if (($segundos / 60) >= 1) {
				$MM = (int)($segundos/60);
				$segundos = $segundos%60;
			}
			if ($MM < 10) {
				$MM = "0$MM";
			}
		    $SS = $segundos;
			if ($SS < 10) {
				$SS = "0$SS";
			}
		    return "$HH:$MM:$SS";
		}
		
		/**
		 * @return string
		 */
		public function getDate () {
			return $this->date;
		}
		
		/**
		 * @return string
		 */
		public function getDestination () {
			return $this->destination;
		}
		
		/**
		 * @return string
		 */
		public function getDuration () {
			return $this->duration;
		}
		
		/**
		 * @return string
		 */
		public function getMessage () {
			return $this->message;
		}
	
		/**
		 * @return string
		 */
		public function getOrigin () {
			return $this->origin;
		}
		
		/**
		 * @return string
		 */
		public function getTime () {
			return $this->time;
		}
		
		/**
		 * @return integer
		 */
		public function getTotalRecords () {
			return $this->totalRecords;
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
		public function getUniqueId () {
			return $this->uniqueId;
		}
		
		/**
		 * @param string $date
		 *
		 * @return Issabel
		 */
		public function setDate ($date) {
			$this->date =  date ('d M Y',strtotime($date));
			return $this;
		}
	
		/**
		 * @param string $destination
		 *
		 * @return Issabel
		 */
		public function setDestination ($destination) {
			$this->destination = !empty ($destination) ? $destination : '';
			return $this;
		}
		
		/**
		 * @param integer $duration
		 *
		 * @return Issabel
		 */
		public function setDuration ($duration) {
			$this->duration = $this->SecToHHMMSS ($duration);
			return $this;
		}
		
		/**
		 * @param string $message
		 *
		 * @return Issabel
		 */
		public function setMessage ($message) {
			$this->message = basename ($message);
			return $this;
		}
		
		/**
		 * @param array $row
		 *
		 * @return Issabel
		 */
		public function setOrigin ($row) {
			$src          = isset ($row['src']) ? $row ['src'] : '';
			$cnum         = isset ($row['cnum']) ? $row ['cnum'] : '';
			$this->origin = $src;
			if($cnum != $src) {
				$this->origin = $cnum;
			}
			return $this;
		}
	
		/**
		 * @param string $time
		 *
		 * @return Issabel
		 */
		public function setTime ($time) {
			$this->time = date ('H:i:s', strtotime($time));
			return $this;
		}
		
		/**
		 * @param integer $totalRecords
		 *
		 * @return Issabel
		 */
		public function setTotalRecords ($totalRecords) {
			$this->totalRecords = $totalRecords;
			return $this;
		}
		
		/**
		 * @param string $nameFile
		 *
		 * @return Issabel
		 */
		public function setType ($nameFile) {
			$nameFile = basename ($nameFile);
			if ($nameFile == 'deleted') {
				$this->type = 'Deleted';
			} else {
				switch ($nameFile [0]) {
					case 'O':  // FreePBX 2.8.1
					case 'o':  // FreePBX 2.11+
						$this->type = 'Outgoing';
						break;
					case 'g':  // FreePBX 2.8.1
					case 'r':  // FreePBX 2.11+
						$this->type = 'Group';
						break;
					case 'q':
						$this->type = 'Queue';
						break;
					default :
						$this->type = 'Incoming';
						break;
				}
			}
			return $this;
		}
		
		/**
		 * @param string $uniqueId
		 *
		 * @return Issabel
		 */
		public function setUniqueId ($uniqueId) {
			$this->uniqueId = $uniqueId;
			return $this;
		}
	
		/**
		 * @return Issabel
		 */
		public static function getInstance () {
			return new self ();
		}
	}