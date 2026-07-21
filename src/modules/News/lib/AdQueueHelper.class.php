<?php
	require_once ('modules/News/lib/NewsUtils.php');

	class AdQueueHelper extends NewsUtils {

		/**
		 * @param boolean $headOnly
		 *
		 * @return AdQueue[]|null
		 * @throws Exception
		 */
		public function fetchAdQueus ($headOnly = false) {
			$result = self::$masterAdb->pquery ('SELECT * FROM vtiger_ad_queue adq  WHERE 1', array ());
			if (self::$masterAdb->num_rows ($result) > 0) {
				$adQueues = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$adQueues [] = AdQueue::getInstance()
						->setId ($row ['adqueueid'])
						->setName ($row ['name'])
						->setPeriod ($row ['period'])
						->setStatus ($row ['status'])
						->setDescription ($row ['description'])
						->setCreateDate ($row ['createdate'])
						->setInitDay ($row ['createdate'])
						->setNews (!($headOnly) ? $this->fetchNewsByAdQueue ($row ['adqueueid']) : null);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($adQueues)) ? $adQueues : null;
		}

		/**
		 * @param DateTime $date
		 * @param string $email
		 * @param string $adbName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function fetchNewsData ($date, $email, $adbName) {
			$whereClauses = array ();
			$arguments    = array ();
			if ($date != null) {
				$whereClauses [] = '(? BETWEEN startdatetime AND enddatetime) ';
				$today           = $date->format ('Y-m-d H:i:s');
				$arguments []    = $today;
			}

			if ($email != null) {
				$whereClauses [] = '(EXISTS (SELECT email FROM vtiger_news_sharing WHERE email=?))';
				$arguments []    = $email;
			}

			$whereClauses [] = '(categories!=?)';
			$arguments []   = 'PLATZILLA';
			$whereClause    = !empty ($whereClauses) ? 'WHERE ' . join (' AND ', $whereClauses) : '';

			$result = self::$masterAdb->pquery (
				"SELECT
					*
				FROM
					vtiger_news
				{$whereClause}
				ORDER BY
					newsid DESC",
				$arguments
			);
			if (self::$masterAdb->num_rows ($result) > 0) {
				$newsData = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$newsData [] = News::getInstance()
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
			} else {
				$newsData = array ();
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($today)) {
				$this->fetchNewsDataFromAdQueue($today, $email, $newsData, $adbName);
			}
			return (count ($newsData)) ? $newsData : null;
		}

		/**
		 * @param string $today
		 * @param string $email
		 * @param $newsData
		 *
		 * @throws Exception
		 */
		public  function fetchNewsDataFromAdQueue ($today, $email, &$newsData, $adbName) {
			$queues       = $this->fetchAdQueus (true);
			$dateRegister = null;
			if (!empty($adbName) && $adbName !== 'pg_crm_madre') {
				$dummy = explode('_', $adbName);
				$result = self::$masterAdb->pquery('SELECT registrationdate FROM vtiger_instances  WHERE code=?', array($dummy [2]));
				if (self::$masterAdb->num_rows ($result) > 0) {
					$row = self::$masterAdb->fetchByAssoc ($result, -1, false);
					$dateRegister = $row ['registrationdate'];
				}
			}

			if (!empty ($queues)) {
				foreach ($queues as $queue) {
					$this->searchNewsByAdQueue ($today, $email, $queue, $newsData, $dateRegister);
				}
			}
		}

		/**
		 * @param integer $queueId
		 *
		 * @return News[]|null
		 * @throws Exception
		 */
		public function fetchNewsByAdQueue ($queueId) {
			if (empty ($queueId)) {
				return null;
			}
			$result = self::$masterAdb->pquery (
				'SELECT ne.* 
					  FROM vtiger_news ne 
					  INNER JOIN vtiger_news2ad_queue nq ON nq.newsid = nq.newsid
					  WHERE nq.adqueueid=?
					  ORDER BY ne.newsid DESC',
				array ($queueId)
			);
			if (self::$masterAdb->num_rows ($result) > 0) {
				$row = self::$masterAdb->fetchByAssoc ($result, -1, false);
				$news[] = News::getInstance()
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
			return (isset ($news)) ? $news : null;
		}

		/**
		 * @param AdQueue $adQueue
		 *
		 * @return null|AdQueue
		 */
		public function saveAdQueue ($adQueue) {
			if (empty($adQueue) || !$adQueue instanceof AdQueue) {
				return null;
			}
			if (empty ($adQueue->getId ())) {
				self::$masterAdb->pquery (
					'INSERT INTO vtiger_ad_queue (name, description, period, status) VALUES (?, ?, ?, ?)',
					array ($adQueue->getName (), $adQueue->getDescription (), $adQueue->getPeriod (), $adQueue->getStatus())
				);
				$adQueue->setId (self::$masterAdb->getLastInsertID ());
			} else {
				self::$masterAdb->pquery (
					'UPDATE vtiger_ad_queue SET name=?, description=?, period=?, status=? WHERE adqueueid=?',
					array ($adQueue->getName (), $adQueue->getDescription (), $adQueue->getPeriod (), $adQueue->getStatus (), $adQueue->getId ())
				);
			}

			return $adQueue;
		}

		/**
		 * @return AdQueueHelper
		 */
		public static function getInstance () {
			return new self ();
		}

	}
