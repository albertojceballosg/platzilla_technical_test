<?php
	require_once ('include/platzilla/Objects/Notification.php');
	require_once ('include/platzilla/Objects/NotificationFilter.php');
	require_once ('include/platzilla/Objects/NotificationModal.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Class NotificationManager
	 *
	 * Gestiona  los datos desde y hacia las base de datos de las notificaciones
	 */
	class NotificationManager {
		/** @var NotificationManager */
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * Constructor
		 *
		 * @param PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * Devuelve los dato del boton personalizado según su ID
		 *
		 * @param array $buttonIds
		 *
		 * @return array
		 * @throws Exception
		 */
		private function fetchCustomButtonsLinks ($buttonIds) {
			$links = array ();
			foreach ($buttonIds as $id) {
				if (empty($id) || $id == null) {
					continue;
				}
				$result = $this->adb->pquery ('SELECT link FROM vtiger_custombuttons WHERE custombuttonid=?', array ($id));
				if ($this->adb->num_rows ($result) > 0) {
					while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
						$links [] = $row ['link'];
					}
				}
			}

			return $links;
		}

		/**
		 * Devuelve arreglo de IDs de los botones personalizados de un módulo dado
		 *
		 * @param array $links
		 * @param string $moduleName
		 *
		 * @return array
		 * @throws Exception
		 */
		private function fetchCustomButtonsIds ($links, $moduleName) {
			$custombuttonIds = array ();
			foreach ($links as $link) {
				if (empty($link) || $link == null) {
					continue;
				}
				$result = $this->adb->pquery ('SELECT custombuttonid FROM vtiger_custombuttons WHERE module=? AND  link=?', array ($moduleName, $link));
				if ($this->adb->num_rows ($result) > 0) {
					while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
						$custombuttonIds [] = $row ['custombuttonid'];
					}
				}
			}

			return $custombuttonIds;
		}

		/**
		 * Obtiene un objeto Notificación dado un arreglo de datos de un registro
		 *
		 * @param  array $notifications
		 * @param array $row
		 *
		 * @throws Exception
		 */
		private function getNotificationRecord (&$notifications, $row) {
			$filter = NotificationFilter::getInstance ()
				->setId(intval ($row ['notificationid']))
				->setAdvancedFilter ($row ['advancedfilter'])
				->setColumnPeriod ($row ['columnperiod'])
				->setFilterPeriod ($row ['filterperiod'])
				->setModuleFilter ($row ['modulefilter'])
				->setRecordId ($row ['recordid'])
				->setSqlFilter (json_decode (str_replace ('&quot;', '"', $row ['sqlfilter'])))
				->setStandardFilter ($row ['standardfilter'])
				->setUsersFilter (json_decode (str_replace ('&quot;', '"', $row ['usersfilter'])));

			$modal = NotificationModal::getInstance()
				->setId (intval ($row ['notificationid']))
				->setButtonLinks((!empty($row ['custombuttons'])) ? $this->fetchCustomButtonsLinks(json_decode(str_replace ('&quot;', '"',  $row ['custombuttons']))) : null)
				->setCustomButton ($row ['custombuttons'])
				->setExitText ($row ['exittext'])
				->setInputText ($row ['inputtext'])
				->setModuleName ($row ['module']);

			$notifications [] = Notification::getInstance ()
				->setId (intval ($row ['notificationid']))
				->setAction ($row ['action'])
				->setScope ($row ['scope'])
				->setContents (stripslashes ($row['contents']))
				->setCreatedTime ($row['createdtime'])
				->setDescription ($row ['description'])
				->setEvent ($row ['event'])
				->setEventParameter ($row ['eventparameter'])
				->setFilter ($filter)
				->setModal (($row ['module']) ? $modal : null)
				->setModuleNames (json_decode (str_replace ('&quot;', '"', $row ['modulenames'])))
				->setName ($row ['name'])
				->setSendByEmail ($row ['sendbyemail'])
				->setStatus ($row ['status'])
				->setStyle ($row ['style'])
				->setType ($row ['type'])
				->setView ($row ['view']);
		}

		/**
		 * Verifica si un objeto Notifiation aplica para ser activada
		 *
		 * @param array $data
		 * @param string $moduleName
		 *
		 * @return boolean
		 * @throws Exception
		 */
		private function isApplicable ($data, $moduleName) {
			if (empty ($data ['event'])) {
				return false;
			} else if (in_array (
				$data ['event'],
				array (
					Notification::EVENT_ALWAYS,
					Notification::EVENT_EDIT_RECORD,
					Notification::EVENT_CANCEL_RECORD,
					Notification::EVENT_CREATE_RECORD,
					Notification::EVENT_SAVE_RECORD,
					Notification::EVENT_RECORD_NO_CREATE,
				)
			)
			) {
				return true;
			}

			$event = $data ['event'];
			switch ($event) {
				case Notification::EVENT_TOTAL_RECORDS_REACHED:
					$result = $this->adb->pquery ('SELECT COUNT(*) AS total FROM vtiger_crmentity WHERE setype=? AND deleted=0', array ($moduleName));
					if ($this->adb->num_rows ($result) > 0) {
						$row          = $this->adb->fetchByAssoc ($result, -1, false);
						$totalRecords = intval ($row ['total']);
					} else {
						$totalRecords = 0;
					}
					DatabaseUtils::closeResult ($result);
					$isApplicable = ($totalRecords >= intval ($data ['eventparameter']));
					$result       = null;
					break;
				case Notification::EVENT_FIRST_TIME:
					$result = $this->adb->pquery ('SELECT COUNT(*) AS total FROM vtiger_crmentity WHERE setype=? ', array ($moduleName));
					if ($this->adb->num_rows ($result) > 0) {
						$row          = $this->adb->fetchByAssoc ($result, -1, false);
						$totalRecords = intval ($row ['total']);
					} else {
						$totalRecords = 0;
					}
					DatabaseUtils::closeResult ($result);
					$isApplicable = ($totalRecords == 1);
					$result       = null;
					break;
				default:
					$isApplicable = false;
					break;
			}
			return $isApplicable;
		}

		/**
		 * Establece como habilitada una notificación
		 *
		 * @param Notification $notification
		 * @param Users $user
		 */
		public function enableNotification ($notification, $user) {
			if ((empty ($notification)) || (!($notification instanceof Notification))) {
				return;
			}

			$this->adb->pquery ('DELETE FROM vtiger_notifications_disabled WHERE notificationid=? AND disabledby=?', array ($notification->getId (), $user->id));
		}

		/**
		 * Elimina una notificación
		 *
		 * @param Notification $notification
		 */
		public function deleteNotification ($notification) {
			if ((empty ($notification)) || (!($notification instanceof Notification))) {
				return;
			}

			$this->adb->pquery ('DELETE FROM vtiger_notifications WHERE notificationid=?', array ($notification->getId ()));
			$this->adb->pquery ('DELETE FROM vtiger_notifications_filters WHERE notificationid=?', array ($notification->getId ()));
			$this->adb->pquery ('DELETE FROM vtiger_notifications_modal WHERE notificationid=?', array ($notification->getId ()));
		}

		/**
		 * Elimina una colección de notifiaciones
		 *
		 * @param string $moduleName
		 * @param boolean $ignoreLock
		 */
		public function deleteNotifications ($moduleName, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			}

			if (!$ignoreLock) {
				$whereClause = 'AND n.locked=0';
			} else {
				$whereClause = '';
			}

			$this->adb->pquery (
				"DELETE
						nf.*,
						 n.*,
						nm.*
					  FROM
					  	vtiger_notifications_filters nf
					  INNER JOIN vtiger_notifications n ON n.notificationid = nf.notificationid
					  INNER JOIN vtiger_notifications_modal nm ON nm.notificationid = nf.notificationid
					  WHERE
					  	nf.modulefilter=?
					  	{$whereClause}",
				array ($moduleName)
			);
		}

		/**
		 * Deshabilita una notifiación
		 *
		 * @param Notification $notification
		 * @param Users $user
		 */
		public function disableNotification ($notification, $user) {
			if ((empty ($notification)) || (!($notification instanceof Notification))) {
				return;
			}

			$this->adb->pquery (
				'INSERT INTO vtiger_notifications_disabled (notificationid, disabledon, disabledby) VALUES (?, ?, ?)',
				array ($notification->getId (), date ('Y-m-d H:i:s'), $user->id)
			);
		}

		/**
		 * Devuelve una colección de objetos Notification habilitados para ser activados
		 *
		 * @param string $view
		 * @param string $moduleName
		 * @param string $type
		 * @param Users $user
		 * @param $style
		 *
		 * @return Notification[]|null
		 * @throws Exception
		 */
		public function fetchApplicableNotifications ($view, $moduleName, $type, $user, $style) {
			if ((empty ($view)) || (empty ($moduleName)) || (empty ($type)) || (empty ($style))) {
				return null;
			}

			$inView = ($style == Notification::STYLE_NOTIFY) ? "n.view= '{$view}' AND" : '';
			$result = $this->adb->pquery (
				"SELECT
					*
				FROM
					vtiger_notifications n
					LEFT JOIN vtiger_notifications_modal nm ON nm.notificationid = n.notificationid
					LEFT JOIN vtiger_notifications_filters fg ON fg.notificationid = n.notificationid
				WHERE
					n.status=? AND
					n.type=? AND
					n.style=? AND
					{$inView}
					NOT EXISTS (SELECT notificationid FROM vtiger_notifications_disabled WHERE notificationid=n.notificationid AND fg.recordid IS NULL AND disabledby=?)",
				array (Notification::STATUS_ACTIVE, $type, $style, $user->id)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$notifications = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$groupOfModules = json_decode (str_replace ('&quot;', '"', $row['modulenames']));

					// Handle null or "null" modulenames (global notifications)
					$isGlobal = false;
					if (empty($groupOfModules) || $groupOfModules === null || (is_string($row['modulenames']) && strtolower($row['modulenames']) === 'null')) {
						$isGlobal = true;
					} else if (is_array($groupOfModules) && in_array('Users', $groupOfModules)) {
						$isGlobal = true;
					}
					// ALERTs must respect modulenames like MODAL and NOTIFY
					// Remove the special condition ($style == Notification::STYLE_ALERT) that bypassed modulenames
					$isApplicable = $this->isApplicable ($row, $moduleName);
					if ((($isGlobal) || (is_array($groupOfModules) && in_array ($moduleName, $groupOfModules))) && $isApplicable) {
						$this->getNotificationRecord ($notifications, $row);
					}
				}
			} else {
				$notifications = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty ($notifications) ? $notifications : null;
		}

		/**
		 * Devuelve un objeto Notification basado en el ID de la notificación
		 *
		 * @param integer $notificationId
		 *
		 * @return Notification|null
		 * @throws Exception
		 */
		public function fetchNotification ($notificationId) {
			if (empty ($notificationId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
						*
					  FROM
						vtiger_notifications n
					  	LEFT JOIN vtiger_notifications_filters fg ON fg.notificationid = n.notificationid
					  	LEFT JOIN vtiger_notifications_modal nm ON nm.notificationid = n.notificationid
					  WHERE
					  	n.notificationid=?',
				array ($notificationId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$filter = NotificationFilter::getInstance ()
					->setId (intval ($row ['notificationid']))
					->setAdvancedFilter ($row['advancedfilter'])
					->setColumnPeriod ($row ['columnperiod'])
					->setFilterPeriod ($row ['filterperiod'])
					->setModuleFilter ($row ['modulefilter'])
					->setSqlFilter (json_decode (str_replace ('&quot;', '"', $row['sqlfilter'])))
					->setStandardFilter ($row ['standardfilter'])
					->setUsersFilter (json_decode (str_replace ('&quot;', '"', $row['usersfilter'])));

				$modal = NotificationModal::getInstance()
					->setId (intval ($row ['notificationid']))
					->setCustomButton ($row ['custombuttons'])
					->setExitText ($row ['exittext'])
					->setInputText ($row ['inputtext'])
					->setModuleName ($row ['module']);

				$notification = Notification::getInstance ()
					->setId (intval ($row ['notificationid']))
					->setAction ($row ['action'])
					->setScope ($row ['scope'])
					->setContents (stripslashes ($row['contents']))
					->setCreatedTime ($row['createdtime'])
					->setDescription ($row ['description'])
					->setEvent ($row ['event'])
					->setEventParameter ($row ['eventparameter'])
					->setId (intval ($row ['notificationid']))
					->setFilter ($filter)
					->setModal (($row ['module']) ? $modal : null)
					->setModuleNames (json_decode (str_replace ('&quot;', '"', $row['modulenames'])))
					->setName ($row ['name'])
					->setSendByEmail ($row ['sendbyemail'])
					->setStatus ($row ['status'])
					->setStyle ($row ['style'])
					->setType ($row ['type'])
					->setView ($row ['view']);
			} else {
				$notification = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $notification;
		}

		/**
		 * Devuelve una colección de objetos Notification basado en el módulo fuente
		 *
		 * @param string $moduleName
		 * @param boolean $status
		 *
		 * @return Notification[]|null
		 * @throws Exception
		 */
		public function fetchNotifications ($moduleName, $status = true) {
			$statusNotify = ($status) ? 'ACTIVE' : 'INACTIVE';
			$result       = $this->adb->pquery (
				'SELECT
					*
				FROM
					vtiger_notifications n
					LEFT JOIN vtiger_notifications_modal nm ON nm.notificationid = n.notificationid
					LEFT JOIN vtiger_notifications_filters nf ON nf.notificationid = n.notificationid
				WHERE
					nf.modulefilter=? AND
					n.status=?',
				array ($moduleName, $statusNotify)
			);

			if ($this->adb->num_rows ($result) > 0) {
				$notifications = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$this->getNotificationRecord ($notifications, $row);
				}
			} else {
				$notifications = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $notifications;
		}

		/**
		 * Guarda un objeto Notification
		 *
		 * @param Notification $notification
		 *
		 * @return Notification|null
		 * @throws NotificationException
		 * @throws Exception
		 */
		public function saveNotification ($notification) {
			if ((empty ($notification)) || (!($notification instanceof Notification))) {
				return null;
			}

			$notification->validate ();

			$notificationId = $notification->getId ();
			if (!empty ($notificationId)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_notifications WHERE notificationid=?', array ($notificationId));
				if ($this->adb->num_rows ($result) > 0) {
					$row            = $this->adb->fetchByAssoc ($result, -1, false);
					$notificationId = intval ($row ['notificationid']);
				} else {
					$notificationId = null;
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			} else {
				$notificationId = null;
			}

			$notificationsValues = array (
				'name'           => $notification->getName (),
				'type'           => $notification->getType (),
				'description'    => $notification->getDescription (),
				'event'          => $notification->getEvent (),
				'eventparameter' => $notification->getEventParameter (),
				'scope'          => $notification->getScope (),
				'view'           => $notification->getView (),
				'modulenames'    => json_encode ($notification->getModuleNames (), (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)),
				'locked'         => $notification->isLocked (),
				'style'          => $notification->getStyle (),
				'action'         => $notification->getAction (),
				'contents'       => addslashes ($notification->getContents ()),
				'status'         => $notification->getStatus (),
				'createdtime'    => $notification->getCreatedTime (),
				'sendbyemail'    => $notification->getSendByEmail (),
			);

			if ($notification->getFilter ()) {
				$notification->getFilter ()->validate ();
				$filterValues = array (
					'columnperiod'   => $notification->getFilter ()->getColumnPeriod (),
					'filterperiod'   => $notification->getFilter ()->getFilterPeriod (),
					'standardfilter' => $notification->getFilter ()->getStandardFilter (),
					'advancedfilter' => $notification->getFilter ()->getAdvancedFilter (),
					'sqlfilter'      => $notification->getFilter ()->getSqlFilter (),
					'modulefilter'   => $notification->getFilter ()->getModuleFilter (),
					'usersfilter'    => json_encode ($notification->getFilter ()->getUsersFilter (), (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)),
				);
			}

			if (empty ($notificationId)) {
				$sql = 'INSERT INTO vtiger_notifications  (' . implode (', ', array_keys ($notificationsValues)) . ') VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
				$this->adb->pquery ($sql, array_values ($notificationsValues));
				$notification->setId (intval ($this->adb->getLastInsertID ()));

				if (isset($filterValues)) {
					$filterValues['notificationid'] = $notification->getId ();
					$sql                            = 'INSERT INTO vtiger_notifications_filters  (' . implode (', ', array_keys ($filterValues)) . ') VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
					$this->adb->pquery ($sql, array_values ($filterValues));
				}
				if ($modal = $notification->getModal ()) {
					$this->adb->pquery (
						'INSERT INTO vtiger_notifications_modal (notificationid, custombuttons, module, inputtext, exittext) VALUES (?, ?, ?, ?, ?)',
						array ($notification->getId (), $modal->getCustomButton (), $modal->getModuleName (), $modal->getInputText(), $modal->getExitText ())
					);
				}
			} else {
				$sql = 'UPDATE vtiger_notifications SET ' . implode ('=?, ', array_keys ($notificationsValues)) . '=? WHERE notificationid=' . $notificationId;
				$this->adb->pquery ($sql, array_values ($notificationsValues));
				if (isset($filterValues)) {
					$sql = 'UPDATE vtiger_notifications_filters SET ' . implode ('=?, ', array_keys ($filterValues)) . '=? WHERE notificationid=' . $notificationId;
					$this->adb->pquery ($sql, array_values ($filterValues));
				}
				if ($modal = $notification->getModal ()) {
					$this->adb->pquery (
						'UPDATE vtiger_notifications_modal SET custombuttons=?, module=?, inputtext=?, exittext=? WHERE notificationid=?',
						array ($modal->getCustomButton (), $modal->getModuleName (), $modal->getInputText(), $modal->getExitText (), $notificationId)
					);
				}
				$this->adb->pquery ('DELETE FROM vtiger_notifications_disabled WHERE notificationid=?', array ($notificationId));
			}
			return $notification;
		}

		/**
		 * Guarda una coleccón de objetos Notification
		 *
		 * @param string $moduleName
		 * @param Notification[] $notifications
		 * @param boolean $ignoreLock
		 *
		 * @throws NotificationException
		 * @throws Exception
		 */
		public function saveNotifications ($moduleName, $notifications, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			} else if (empty ($notifications)) {
				$this->deleteNotifications ($moduleName, $ignoreLock);
				return;
			}

			$processedNotificationIds = array ();
			foreach ($notifications as $notification) {
				$notification->setLocked (false);
				$notification->setCreatedTime (time ());

				if ($motificationModal = $notification->getModal ()) {
					$customButonsId = (!empty ($motificationModal->getButtonLinks ())) ? $this->fetchCustomButtonsIds ($motificationModal->getButtonLinks (), $motificationModal->getModuleName ()) : null;
					$motificationModal->setCustomButton (($customButonsId) ? json_encode ($customButonsId) : null);
				}
				$this->saveNotification ($notification);
				$processedNotificationIds [] = $this->adb->getLastInsertID ();
			}

			if (!$ignoreLock) {
				$whereClause = 'AND n.locked=0';
			} else {
				$whereClause = '';
			}
			$questionMarks = str_repeat ('?, ', (count ($processedNotificationIds) - 1)) . '?';
			$this->adb->pquery (
				"DELETE
					nf.*,
					 n.*,
				    nm.*
				FROM
					vtiger_notifications_filters nf
					INNER JOIN vtiger_notifications n ON n.notificationid = nf.notificationid
					INNER JOIN vtiger_notifications_modal nm ON nm.notificationid = n.notificationid
				WHERE
					nf.modulefilter = '{$moduleName}' AND
					n.notificationid NOT IN ({$questionMarks})
				{$whereClause}",
				$processedNotificationIds
			);
		}

		/**
		 * Busca las alerta basado en un periodo
		 *
		 * @param array $serchPeriod
		 * @param Users $user
		 *
		 * @return Notification[]|null
		 * @throws Exception
		 */
		public function searchAlerts ($serchPeriod, $user) {
			if (empty ($serchPeriod['startdate']) && empty ($serchPeriod['enddate'])) {
				return null;
			}

			$result = $this->adb->pquery (
				"SELECT
					*
				FROM
					vtiger_notifications n
					LEFT JOIN vtiger_notifications_filters fg ON fg.notificationid = n.notificationid
				WHERE
					n.status=? AND
					n.type=? AND
					n.style=? AND
				   (DATE(fg.createddate) BETWEEN STR_TO_DATE('{$serchPeriod['startdate']}','%Y-%m-%d') AND STR_TO_DATE('{$serchPeriod['enddate']}','%Y-%m-%d'))
				   AND NOT EXISTS (SELECT notificationid FROM vtiger_notifications_disabled WHERE notificationid=n.notificationid AND disabledby=?)",
				array (Notification::STATUS_ACTIVE, Notification::TYPE_SCREEN, Notification::STYLE_ALERT, $user->id)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$notifications = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$this->getNotificationRecord ($notifications, $row);
				}
			} else {
				$notifications = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty ($notifications) ? $notifications : null;
		}

		/**
		 * Busca notificaciones por palabra clave o por paginas
		 *
		 * @param null|string $keyword
		 * @param null|integer $page
		 * @param null|integer $recordsPerPage
		 * @param string $scope
		 *
		 * @return array
		 * @throws Exception
		 */
		public function searchNotifications ($keyword = null, $page = null, $recordsPerPage = null, $scope = '') {
			$whereClauses = array ();
			$arguments    = array ();
			$totalRecords = 0;
			$records      = null;
			$endRecord    = 0;
			$totalPages   = 0;
			$startRecord  = 0;
			$limitClause  = '';
			if (!empty ($keyword)) {
				$whereClauses [] = 'name LIKE ?';
				$arguments []    = "%{$keyword}%";
				$arguments []    = "%{$keyword}%";
			}

			if (!empty($scope)) {
				$whereClauses [] = 'scope LIKE ?';
				$arguments []    = $scope;
				$arguments []    = $scope;
			}

			$whereClause = !empty ($whereClauses) ? 'WHERE ' . join (' AND ', $whereClauses) : '';

			if ((!empty ($recordsPerPage)) && (is_numeric ($recordsPerPage))) {
				$startRecord = (!empty ($page)) && ($page > 0) ? (($page - 1) * $recordsPerPage) : 0;
				$limit       = $recordsPerPage;
				$limitClause = "LIMIT {$startRecord}, {$limit}";
			}

			$result = $this->adb->pquery (
				"SELECT
					*
				FROM
					vtiger_notifications
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_notifications {$whereClause}) AS total
				{$whereClause}
				ORDER BY
					name
				{$limitClause}",
				$arguments
			);
			if ($this->adb->num_rows ($result) > 0) {
				$startRecord++;
				$totalRecords = null;
				$records      = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$totalRecords = intval ($row ['__total_records__']);
					$records []   = Notification::getInstance ()
						->setId (intval ($row ['notificationid']))
						->setAction ($row ['action'])
						->setScope ($row ['scope'])
						->setContents (stripslashes ($row['contents']))
						->setCreatedTime ($row['createdtime'])
						->setDescription ($row ['description'])
						->setEvent ($row ['event'])
						->setEventParameter ($row ['eventparameter'])
						->setFilter (null)
						->setModuleNames (json_decode (str_replace ('&quot;', '"', $row['modulenames'])))
						->setName ($row ['name'])
						->setSendByEmail ($row ['sendbyemail'])
						->setStatus ($row ['status'])
						->setStyle ($row ['style'])
						->setType ($row ['type'])
						->setView ($row ['view']);
				}
				$endRecord  = count ($records);
				$totalPages = ceil ($totalRecords / $recordsPerPage);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return array (
				'startRecord'  => $startRecord,
				'endRecord'    => $endRecord,
				'totalRecords' => $totalRecords,
				'page'         => empty ($page) ? 1 : intval ($page),
				'totalPages'   => $totalPages,
				'records'      => $records,
			);
		}

		/**
		 * Se obtiene un objeto NotificationManager con los atributos de la clase
		 *
		 * @param PearDatabase $adb
		 *
		 * @return NotificationManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = array ();
			}
			if (!isset (self::$INSTANCE [ $adb->dbName ])) {
				self::$INSTANCE[ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCE [ $adb->dbName ];
		}

	}
