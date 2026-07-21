<?php
	require_once ('include/utils/Pagination.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/platzi_issabel/Exceptions/PlatziIssabelException.php');
	require_once ('modules/platzi_issabel/Objects/Issabel.php');
	require_once ('modules/platzi_issabel/Objects/PlatziIssabelInterface.php');
	
	class PlatziIssabel implements PlatziIssabelInterface {
		
		/** @var string */
		private $configFile;
		
		/** @var integer  */
		private  $recordPerPage = 15;
		
		/** @var integer  */
		private  $startRecord   = 0;
		
		/**
		 * @param string $platform
		 */
		public function __construct ($platform) {
			if (!is_dir (__DIR__ . "/../../../{$platform}/issabel")) {
				mkdir (__DIR__ . "/../../../{$platform}/issabel", 0777, true);
			}
			$rootFolderPath   = PlatzillaUtils::getPlatzillaRootFolderPath ();
			$this->configFile = "{$rootFolderPath}/{$platform}/issabel/IssabelConfig.class.php";;
		}
		
		/**
		 * @param string $recordingFile
		 *
		 * @return array|null
		 */
		private function checkRecordingPath ($recordingFile) {
			if (empty ($recordingFile)) {
				return null;
			}
			require_once ($this->configFile);
			$result ['mimetype'] = NULL;
			$result ['deleted']  = ($recordingFile == 'deleted');
			if (!$result ['deleted']) {
				$dummy               = explode ('/', $recordingFile, 5);
				$result ['fullpath'] = IssabelConfig::BASEDIR_RECORDINGS . $dummy [4];
			} else {
				$result ['fullpath'] = null;
			}
			if (!empty ($result ['fullpath'])) {
				$regs = NULL;
				if (preg_match ('/\.(\S{3})$/', $result ['fullpath'], $regs)) {
					if (in_array ($regs[1], array_keys (IssabelConfig::LIST_EXTENSIONS))) {
						$result['mimetype'] = IssabelConfig::LIST_EXTENSIONS [ $regs[1] ];
					}
				}
			}
			return $result;
		}
		
		/**
		 * @return mysqli|void
		 * @throws PlatziIssabelException
		 */
		private function getConnection () {
			if (!file_exists ($this->configFile)) {
				throw new PlatziIssabelException (PlatziIssabelException::ERROR_CONFIG_FILE_NOT_FOUND);
			}
			require_once ($this->configFile);
			$connector = mysqli_connect  (
				IssabelConfig::HOST,
				IssabelConfig::DB_USER,
				IssabelConfig::DB_PASSWORD
				) or die (PlatziIssabelException::ERROR_ESTABLISHING_CONNECTION);
			mysqli_select_db (
				$connector,
				self::DB_NAME
			) or die(PlatziIssabelException::DATABASE_CONNECTION_ERROR . mysqli_error ($connector));
			
			@mysqli_query ($connector, "SET NAMES 'utf8'");
			return $connector;
		}
		
		/**
		 * @param integer $totalRows
		 * @param integer $id
		 *
		 * @return Pagination
		 */
		public function configPaginator ($totalRows, $id) {
			$paginator       = Pagination::getInstance();
			$paginatorConfig = array (
				'totalRows'       => $totalRows,
				'perPage'         => $this->recordPerPage,
				'numLinks'        => 5,
				'attributes'      => array('class' => 'linkPag', 'onclick' => "PlatziIssabelUtils.goToPage (event, this, '{$id}');"),
				'firstTagOpen'    => "<li class='Pages'>",
				'firstTagClose'   => '</li>',
				'lastTagOpen'     => "<li class='Pages'>",
				'lastTagClose'    => '</li>',
				'currentTagOpen'  => "<li class='Pages'><a href='#'><strong>",
				'currentTagClose' => '</strong></a></li>',
				'numTagOpen'      => "<li class='Pages'>",
				'numTagClose'     => '</li>',
				'prevTagOpen'     => "<li class='Pages'>",
				'prevTagClose'    => '</li>',
				'nextTagOpen'     => "<li class='Pages'>",
				'nextTagClose'    => '</li>',
			);
			$paginator->initialize ($paginatorConfig);
			return $paginator;
		}
		
		/**
		 * @param array|null$where
		 * @param integer|null$page
		 *
		 * @return Issabel[]|void|null
		 * @throws PlatziIssabelException
		 */
		public function fetchIssabelMonitoring ($where, $page) {
			$connector      = $this->getConnection ();
			$thisWhere      = '';
			$starRecord     = (isset ($page)) ? $page : $this->startRecord;
			$recordsPerPage = $this->recordPerPage;
			if (is_array ($where)  &&  count ($where) > 0) {
				foreach ($where as $join => $conditions) {
					foreach ($conditions as $key => $value) {
						if (empty ($thisWhere)) {
							$thisWhere = "WHERE ({$key}{$value})";
						} else {
							$thisWhere .= " {$join} ({$key}{$value}) ";
						}
					}
				}
			}
			$query     = "SELECT *
						FROM cdr
						CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM cdr {$thisWhere}) AS total
						{$thisWhere}
						LIMIT {$starRecord}, {$recordsPerPage}";
			$result    = mysqli_query (
				$connector,
				$query
			) or die (PlatziIssabelException::DATABASE_CONNECTION_ERROR . mysqli_error ($connector));
			if (mysqli_num_rows ($result) > 0) {
				while ($row = mysqli_fetch_assoc ($result)) {
					$issabelMonitoring[] = Issabel::getInstance ()
						->setDate ($row['calldate'])
						->setDestination ($row['dst'])
						->setDuration ($row['duration'])
						->setMessage ($row['recordingfile'])
						->setOrigin ($row)
						->setTime ($row['calldate'])
						->setTotalRecords (intval ($row['__total_records__']))
						->setType ($row['recordingfile'])
						->setUniqueId ($row['uniqueid']);
				}
			}
			mysqli_close ($connector);
			return isset ($issabelMonitoring) ? $issabelMonitoring : null;
		}
	
		/**
		 * @param string $uniqueId
		 *
		 * @return array|void
		 * @throws PlatziIssabelException
		 */
		public function getAudioByUniqueId ($monitorId) {
			if (empty ($monitorId)) {
				throw new PlatformException (PlatziIssabelException::ERROR_MONITOR_ID_EMPTY);
			}
			$connector = $this->getConnection ();
			$query     = "SELECT recordingfile FROM cdr WHERE uniqueid = '{$monitorId}'";
			$result    = mysqli_query (
				$connector,
				$query
			) or die (PlatziIssabelException::DATABASE_CONNECTION_ERROR . mysqli_error ($connector));
			if (mysqli_num_rows ($result) > 0) {
				$row   = mysqli_fetch_assoc ($result);
				$audio = $this->checkRecordingPath ($row ['recordingfile']);
			}
			mysqli_close ($connector);
			return isset ($audio) ? $audio : null;
		}
		
		/**
		 * @param string $monitorId
		 *
		 * @return Issabel|null
		 * @throws PlatziIssabelException
		 */
		public function getMonitorByUniqueId ($monitorId) {
			if (empty ($monitorId)) {
				throw new PlatformException (PlatziIssabelException::ERROR_MONITOR_ID_EMPTY);
			}
			$connector = $this->getConnection ();
			$query     = "SELECT * FROM cdr WHERE uniqueid = '{$monitorId}'";
			$result    = mysqli_query (
				$connector,
				$query
			) or die (PlatziIssabelException::DATABASE_CONNECTION_ERROR . mysqli_error ($connector));
			if (mysqli_num_rows ($result) > 0) {
				$row               = mysqli_fetch_assoc ($result);
				$issabelMonitoring = Issabel::getInstance ()
					->setDate ($row['calldate'])
					->setDestination ($row['dst'])
					->setDuration ($row['duration'])
					->setMessage ($row['recordingfile'])
					->setOrigin ($row)
					->setTime ($row['calldate'])
					->setType ($row['recordingfile'])
					->setUniqueId ($row['uniqueid']);
				}
			mysqli_close ($connector);
			return isset ($issabelMonitoring) ? $issabelMonitoring : null;
		}
		
		/**
		 * @param string $monitorId
		 *
		 * @return array|void
		 * @throws PlatziIssabelException
		 */
		public function getRecordPerPage () {
			return $this->recordPerPage;
		}
		
		public function getStartRecord () {
			return $this->startRecord;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return PlatziIssabel
		 */
		public static function getInstance ($platform) {
			return new self ($platform);
		}
	}