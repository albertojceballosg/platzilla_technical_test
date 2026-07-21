<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('modules/News/Objects/AdQueue.php');
	require_once ('modules/News/Objects/News.php');

	abstract class NewsUtils {

		const RECORDS_PER_PAGE = 25;

		/** @var PearDatabase */
		static  $masterAdb;

		public function __construct() {
			self::$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		}

		/**
		 * @param $newsId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private static function fetchNewsSharingData ($newsId) {
			$result = self::$masterAdb->pquery ('SELECT * FROM vtiger_news_sharing WHERE newsid=?', array ($newsId));
			if (self::$masterAdb->num_rows ($result) > 0) {
				$sharingData = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$sharingData [] = $row;
				}
			} else {
				$sharingData = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $sharingData;
		}

		/**
		 * @param array $newsData
		 */
		private static function relatedToAdQueue ($newsData) {
			if ($newsData ['category'] == 'PLATZILLA') {
				return;
			}
			$newsId    = $newsData ['newsid'];
			$adQueueId = $newsData ['adQueueId'];
			self::$masterAdb->pquery ('INSERT INTO vtiger_news2ad_queue (newsid, adqueueid) VALUE (?, ?)', array ($newsId, $adQueueId));
		}

		/**
		 * @param array $newsData
		 */
		private static function saveNewsSharingData ($newsData) {
			$newsId = $newsData ['newsid'];
			foreach ($newsData ['sharing'] as $sharingData) {
				if ($sharingData ['USERS'] == '-ALL-') {
					self::$masterAdb->pquery (
						'INSERT INTO vtiger_news_sharing (newsid, userid, categories, email, entityid)
						SELECT ?, id, ?, user_name AS email, NULL FROM vtiger_users',
						array ($newsId, 'USERS')
					);
				} else if ($sharingData ['CUSTOMERS'] == '-ALL-CUSTOMERS-') {
					self::$masterAdb->pquery (
						'INSERT INTO vtiger_news_sharing (newsid, userid, categories, email, entityid)
						SELECT ?, NULL, ?, e_mail AS email, clientesid AS entityid FROM vtiger_clientes WHERE e_mail IS NOT NULL AND e_mail <> ?',
						array ($newsId, 'CUSTOMERS', '')
					);
				} else if ($sharingData ['PROVIDERS'] == '-ALL-PROVIDERS-') {
					self::$masterAdb->pquery (
						'INSERT INTO vtiger_news_sharing (newsid, userid, categories, email, entityid)
						SELECT ?, NULL, ?, email, proveedoresid AS entityid FROM vtiger_proveedores WHERE email IS NOT NULL AND email <> ?',
						array ($newsId, 'PROVIDERS', '')
					);
				} else {
					$category = array_keys ($sharingData);
					$dummy    = explode(';', $sharingData[ $category[0] ]);
					$userid   = ($category [0]  == 'USERS') ? $dummy [0] : null;
					$entityid = ($category [0] == 'USERS') ? null : $dummy [0];
					self::$masterAdb->pquery (
						'INSERT INTO vtiger_news_sharing (newsid, userid, categories, email, entityid) VALUES (?, ?, ?, ?, ?)',
						array ($newsId, $userid, $category[0], $dummy[1], $entityid, )
					);
				}
			}
		}

		/**
		 * @param integer $newsId
		 *
		 * @return AdQueue|null
		 * @throws Exception
		 */
		private static function searchAdQueueByNews ($newsId) {
			if (empty($newsId)) {
				return null;
			}
			$result = self::$masterAdb->pquery (
				'SELECT adqueueid FROM vtiger_news2ad_queue WHERE newsid=?', array ($newsId));
			if (self::$masterAdb->num_rows ($result) > 0) {
				$row = self::$masterAdb->fetchByAssoc ($result, -1, false);
				$adQueue = self::fetchAdQueueById ($row ['adqueueid']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($adQueue)) ? $adQueue : null;
		}

		/**
		 * @param $newsData
		 *
		 * @throws Exception
		 */
		private static function validateNewsSharingData ($newsData) {
			if (empty ($newsData ['sharing'])) {
				throw new Exception ('No has suministrado la información de compartir');
			} else {
				foreach ($newsData ['sharing'] as $sharingData) {
					if ((empty ($sharingData ['USERS'])) && (empty ($sharingData ['PROVIDERS'])) && (empty ($sharingData ['CUSTOMERS']))) {
						throw new Exception ('No has suministrado un usuario o un cliente o un proveedor');
					}
				}
			}
		}

		/**
		 * @param integer $queueId
		 *
		 * @return AdQueue|null
		 * @throws Exception
		 */
		public static function fetchAdQueueById ($queueId) {
			if (empty($queueId)) {
				return null;
			}
			$result = self::$masterAdb->pquery ('SELECT * FROM vtiger_ad_queue WHERE adqueueid=?', array ($queueId));
			if (self::$masterAdb->num_rows ($result) > 0) {
				$row = self::$masterAdb->fetchByAssoc ($result, -1, false);
				$adQueue = AdQueue::getInstance()
					->setId ($row ['adqueueid'])
					->setName ($row ['name'])
					->setPeriod ($row ['period'])
					->setStatus ($row ['status'])
					->setDescription ($row ['description'])
					->setCreateDate ($row ['createdate ']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($adQueue)) ? $adQueue : null;
		}

		/**
		 * @return array
		 */
		public static function AvailablePeriods () {
			return array (
				'1 DAY'  => AdQueueInterface::AD_QUEUE_PERIOD_24_HOURS,
				'2 DAY'  => AdQueueInterface::AD_QUEUE_PERIOD_48_HOURS,
				'3 DAY'  => AdQueueInterface::AD_QUEUE_PERIOD_72_HOURS,
				'15 DAY' => AdQueueInterface::AD_QUEUE_PERIOD_15_DAYS,
				'30 DAY' => AdQueueInterface::AD_QUEUE_PERIOD_30_DAYS
			);
		}

		/**
		 * @return array
		 */
		public static function AvailableStatus () {
			return array (AdQueueInterface::NEWS_AD_QUEUE_ENABLED, AdQueueInterface::NEWS_AD_QUEUE_DISABLED);
		}

		/**
		 * @param integer $queueId
		 */
		public static function deleteAdQueue ($queueId) {
			if (empty ($queueId)) {
				return;
			}
			self::$masterAdb->pquery ('DELETE FROM vtiger_ad_queue WHERE adqueueid=?', array ($queueId));
		}
		/**
		 * @param $newsId
		 */
		public static function deleteNewsData ($newsId) {
			if (empty ($newsId)) {
				return;
			}
			self::$masterAdb->pquery ('DELETE FROM vtiger_news_sharing WHERE newsid=?', array ($newsId));
			self::$masterAdb->pquery ('DELETE FROM vtiger_news WHERE newsid=?', array ($newsId));
		}

		/**
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchCustomersData () {
			$result = self::$masterAdb->pquery ('SELECT * FROM vtiger_clientes WHERE e_mail IS NOT NULL AND e_mail<>?', array (''));
			if (self::$masterAdb->num_rows ($result) > 0) {
				$customersData = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$customersData [] = array (
						'id'       => $row ['clientesid'],
						'email'    => $row ['e_mail'],
						'fullname' => $row ['alias'],
					);
				}
				usort ($customersData, function ($customerA, $customerB) {
					return strcmp ($customerA ['fullname'], $customerB ['fullname']);
				});
			} else {
				$customersData = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $customersData;
		}

		/**
		 * @param integer $newsId
		 *
		 * @return News|null
		 * @throws Exception
		 */
		public static function fetchNewsItemData ($newsId) {
			if (empty ($newsId)) {
				return null;
			}

			$result = self::$masterAdb->pquery (
				'SELECT
					n.*
				FROM
					vtiger_news n
				WHERE
					n.newsid=?',
				array ($newsId)
			);
			if (self::$masterAdb->num_rows ($result) > 0) {
				$row = self::$masterAdb->fetchByAssoc ($result, -1, false);
				$theNews = News::getInstance()
					->setAdQueue (self::searchAdQueueByNews ($row ['newsid']))
					->setId ($row ['newsid'])
					->setCategories ($row ['categories'])
					->setContent ($row ['content'])
					->setCreateDate ($row ['createdate'])
					->setDueDate ($row ['enddatetime'])
					->setEndDate ($row ['enddatetime'])
					->setInitDay ($row ['startdatetime'])
					->setStartDate ($row ['startdatetime'])
					->setStatus ($row ['status'])
					->setSharing (self::fetchNewsSharingData ($row ['newsid']))
					->setTitle ($row ['title']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($theNews)) ? $theNews : null;
		}

		/**
		 * @param DateTime $date
		 * @param integer $page
		 * @param integer $recordsPerPage
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchPagedNewsData ($date, $page, $recordsPerPage = self::RECORDS_PER_PAGE) {
			if (
				(($page == null) && ((!is_numeric ($page)) || ($page <= 0))) ||
				(($recordsPerPage == null) && ((!is_numeric ($recordsPerPage)) || ($recordsPerPage <= 0)))
			) {
				return null;
			}

			if ($page <= 0) {
				$page = 1;
			}
			if ($recordsPerPage <= 0) {
				$recordsPerPage = self::RECORDS_PER_PAGE;
			}

			$whereClauses = array ();
			$arguments    = array ();
			if ($date != null) {
				$whereClauses [] = 'startdatetime <= ? AND ? <= enddatetime';
				$arguments []    = $date->format ('Y-m-d H:i:s');
				$arguments []    = $date->format ('Y-m-d H:i:s');
				$arguments []    = $date->format ('Y-m-d H:i:s');
				$arguments []    = $date->format ('Y-m-d H:i:s');
			}
			$whereClause = !empty ($whereClauses) ? 'WHERE ' . join (' AND ', $whereClauses) : '';

			$startRecord = (($page - 1) * $recordsPerPage);
			$result      = self::$masterAdb->pquery (
				"SELECT
					*
				FROM
					vtiger_news
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_news {$whereClause}) AS total
				{$whereClause}
				LIMIT ?, ?",
				array_merge ($arguments, array ($startRecord, $recordsPerPage))
			);
			if (self::$masterAdb->num_rows ($result) > 0) {
				$totalRecords = 0;
				$newsData     = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$totalRecords    = ($page !== null) ? intval ($row ['__total_records__']) : ($row + 1);
					$newsData []     = News::getInstance()
						->setAdQueue (self::searchAdQueueByNews ($row ['newsid']))
						->setId ($row ['newsid'])
						->setCategories ($row ['categories'])
						->setContent ($row ['content'])
						->setCreateDate ($row ['createdate'])
						->setDueDate ($row ['enddatetime'])
						->setEndDate ($row ['enddatetime'])
						->setInitDay ($row ['startdatetime'])
						->setStartDate ($row ['startdatetime'])
						->setStatus ($row ['status'])
						->setSharing (null)
						->setTitle ($row ['title']);
				}
				$endRecord  = count ($newsData);
				$totalPages = ceil ($totalRecords / $recordsPerPage);
			} else {
				$newsData     = null;
				$totalRecords = 0;
				$endRecord    = 0;
				$totalPages   = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return array (
				'startRecord'  => $startRecord,
				'endRecord'    => $endRecord,
				'totalRecords' => $totalRecords,
				'page'         => intval ($page),
				'totalPages'   => $totalPages,
				'records'      => $newsData,
			);
		}

		/**
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchProvidersData () {
			$result = self::$masterAdb->pquery ('SELECT * FROM vtiger_proveedores WHERE email IS NOT NULL AND email<>?', array (''));
			if (self::$masterAdb->num_rows ($result) > 0) {
				$providersData = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$providersData [] = array (
						'id'       => $row ['proveedoresid'],
						'email'    => $row ['email'],
						'fullname' => $row ['alias'],
					);
				}
				usort ($providersData, function ($providerA, $providerB) {
					return strcmp ($providerA ['fullname'], $providerB ['fullname']);
				});
			} else {
				$providersData = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $providersData;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function fetchUsersData (PearDatabase $adb) {
			$users = UserManager::getInstance ($adb, null)->fetchUsers ();
			if (empty ($users)) {
				return null;
			}

			$usersData = array ();
			foreach ($users as $user) {
				$usersData [] = array (
					'id'       => $user->getId (),
					'email'    => $user->getUserName (),
					'fullname' => trim ("{$user->getFirstName ()} {$user->getLastName ()}"),
				);
			}

			return $usersData;
		}

		/**
		 * @param $newsData
		 *
		 * @throws Exception
		 */
		public static function saveNewsData ($newsData) {
			self::validateNewsData ($newsData);

			if (empty ($newsData ['newsid'])) {
				self::$masterAdb->pquery (
					'INSERT INTO vtiger_news (title, content, categories, startdatetime, enddatetime, status) VALUES (?, ?, ?, ?, ?, ?)',
					array ($newsData ['title'], $newsData ['content'], $newsData ['category'], $newsData ['startdatetime'], $newsData ['enddatetime'], $newsData ['status'])
				);
				$newsData ['newsid'] = self::$masterAdb->getLastInsertID ();
			} else {
				self::$masterAdb->pquery (
					'UPDATE vtiger_news SET title=?, content=?, categories=?, startdatetime=?, enddatetime=?, status=? WHERE newsid=?',
					array ($newsData ['title'], $newsData ['content'], $newsData ['category'], $newsData ['startdatetime'], $newsData ['enddatetime'], $newsData ['status'], $newsData ['newsid'])
				);
				self::$masterAdb->pquery ('DELETE FROM vtiger_news2ad_queue WHERE newsid=?', array ($newsData ['newsid']));
				self::$masterAdb->pquery ('DELETE FROM vtiger_news_sharing WHERE newsid=?', array ($newsData ['newsid']));
			}
			self::relatedToAdQueue ($newsData);
			self::saveNewsSharingData ($newsData);
		}

		/**
		 * @param string $date
		 * @param integer $email
		 * @param AdQueue $queue
		 * @param News[]$newsData
		 * @param string $dateRegister
		 *
		 * @throws Exception
		 */
		public static function searchNewsByAdQueue ($today, $email, $queue, &$newsData, $dateRegister) {
			if (empty ($email) || empty($queue)) {
				return;
			}
			$adQueueId = $queue->getId ();
			$period    = $queue->getPeriod ();
			$whereDate = "(ne.createdate BETWEEN DATE_SUB(?, INTERVAL {$period}) AND ?)";
			if (!empty ($dateRegister)) {
				$timeToday  = date_create ()->setTime (0, 0, 0);
				$sourceDate = date_create ($dateRegister)->setTime (0, 0, 0);
				$result     = intval ($sourceDate->diff ($timeToday)->format ('%a')) <= 45;
				$whereDate  = ($result) ? "('{$dateRegister}' BETWEEN DATE_SUB(?, INTERVAL {$period}) AND ?)" : $whereDate;
			}

			$result = self::$masterAdb->pquery (
				"SELECT 
						ne.* 
					  FROM 
					  	vtiger_news2ad_queue nq 
					  INNER JOIN vtiger_news ne ON ne.newsid = nq.newsid
					  WHERE 
					  	nq.adqueueid=? AND
					  	ne.categories=? AND
					  	ne.status=? AND
					  	(EXISTS (SELECT email FROM vtiger_news_sharing WHERE email=?)) AND
					  	{$whereDate}
					  ORDER BY ne.newsid DESC",
				array ($adQueueId, 'SUBSCRIPTION', 'ENABLED', $email, $today, $today)
			);
			if (self::$masterAdb->num_rows ($result) > 0) {
				$row = self::$masterAdb->fetchByAssoc ($result, -1, false);
				$newsData[] = News::getInstance()
					->setId ($row ['newsid'])
					->setCategories ($row ['categories'])
					->setContent ($row ['content'])
					->setCreateDate ($row ['createdate'])
					->setDueDate ($row ['enddatetime'])
					->setEndDate ($row ['enddatetime'])
					->setInitDay ($row ['startdatetime'])
					->setStartDate ($row ['startdatetime'])
					->setStatus ($row ['status'])
					->setSharing (null)
					->setTitle ($row ['title']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param array $adQueueIds
		 * @param string $email
		 * @param integer $userId
		 *
		 * @throws Exception
		 */
		public static function saveSharingByAdQueues ($adQueueIds, $email, $userId) {
			if (!is_array($adQueueIds) || empty($adQueueIds) || empty($email)) {
				throw new Exception ('Los datos de la suscripción estan incompletos!');
			}
			foreach ($adQueueIds as $adQueueId) {
				$result = self::$masterAdb->pquery('SELECT newsid FROM vtiger_news2ad_queue WHERE adqueueid=?', array($adQueueId));
				if (self::$masterAdb->num_rows($result) > 0) {
					while ($row = self::$masterAdb->fetchByAssoc($result, -1, false)) {
						self::saveNewsSharingData (array (
								'newsid' => $row ['newsid'],
								'sharing' => array (array ('CUSTOMERS' => $userId . ';' . $email)),
								'adQueueId' => $adQueueId,
							)
						);
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
				}
			}
		}

		/**
		 * @param array $newsData
		 *
		 * @throws Exception
		 */
		public static function validateNewsData ($newsData) {
			if ((empty ($newsData)) || (!is_array ($newsData))) {
				throw new Exception ('No has suministrado la información');
			} else if (empty ($newsData ['title'])) {
				throw new Exception ('No has suministrado el título');
			} else if (empty ($newsData ['content'])) {
				throw new Exception ('No has suministrado el contenido');
			} else {
				self::validateNewsSharingData ($newsData);
			}
		}

	}
