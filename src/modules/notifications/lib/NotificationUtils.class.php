<?php
require_once('include/platzilla/Data/ApplicationsManager.php');
require_once('include/platzilla/Managers/BackgroundTaskManager.php');
require_once('include/platzilla/Managers/NotificationManager.php');
require_once('include/platzilla/Objects/FieldInterface.php');
require_once('include/platzilla/Utils/DatabaseUtils.php');
require_once('include/utils/AdbManager.class.php');
require_once('include/utils/PlatformUtils.class.php');
require_once('include/utils/PlatzillaUtils.class.php');
require_once('log4php/LoggerManager.php');
require_once('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
require_once('modules/notifications/lib/NotificationPeriodUtils.class.php');

/**
 * Class NotificationUtils
 *
 * Contiene métodos que dan soporte a las funcionalidades del módulo
 */
abstract class NotificationUtils
{

	/**
	 * Verifica si se ha de mostrar la notificación
	 *
	 * @param PearDatabase $adb
	 * @param Notification $notification
	 * @param integer $recordId
	 * @param integer|null $userId
	 * @param string|null $platform
	 *
	 * @return boolean
	 */
	private static function isVisibility(PearDatabase $adb, $notification, $recordId, $userId = null, $platform = null)
	{
		if (!$notification instanceof Notification) {
			return false;
		}
		$visibility     = true;
		$whereUser      = '';
		
		// Get sqlFilter, regenerate from advancedFilter if needed
		$sqlFilter = $notification->getFilter()->getSqlFilter();
		$advancedFilter = $notification->getFilter()->getAdvancedFilter();
		
		// If sqlFilter is empty but advancedFilter exists, regenerate sqlFilter
		if (empty($sqlFilter) && !empty($advancedFilter)) {
			$advancedFilterData = is_string($advancedFilter) ? json_decode($advancedFilter, true) : $advancedFilter;
			if (!empty($advancedFilterData) && is_array($advancedFilterData)) {
				$sqlFilter = self::getSqlFilter($adb, $advancedFilterData);
			}
		}
		
		// Clean sqlFilter: handle cases where it's stored as JSON string or already decoded
		if (!empty($sqlFilter)) {
			if (is_string($sqlFilter)) {
				// Try to decode JSON first (handles cases where sqlFilter is stored as JSON string)
				$decoded = json_decode($sqlFilter, true);
				if ($decoded !== null && is_string($decoded)) {
					// Successfully decoded from JSON
					$sqlFilter = $decoded;
				} else {
					// Not valid JSON or already decoded, just clean HTML entities
					$sqlFilter = str_replace('&quot;', '"', $sqlFilter);
					// Remove surrounding quotes if present
					$sqlFilter = trim($sqlFilter, '"');
				}
			}
		}
		
		$where          = (!empty($sqlFilter)) ? $sqlFilter : 1;
		$moduleName     = $notification->getFilter()->getModuleFilter();
		$filterPeriod   = $notification->getFilter()->getFilterPeriod();
		$standardFilter = null;
		if ($filterPeriod !== 'custom') {
			$standardFilter = NotificationPeriodUtils::getStandarFiltersStartAndEndDate($filterPeriod);
		} else {
			$standardFilterRaw = $notification->getFilter()->getStandardFilter();
			if (!empty($standardFilterRaw)) {
				$standardFilterJson = (is_array($standardFilterRaw)) ? json_encode($standardFilterRaw) : str_replace('&quot;', '"', $standardFilterRaw);
				$standardFilter = json_decode($standardFilterJson, true);
			}
		}
		$fieldToPeriod  = $notification->getFilter()->getColumnPeriod();
		$logger         = null;
		if (!empty($platform)) {
			$logger = new Logger('notifications', array('appender' => array('File' => "{$platform}/logs/notificationslogs/notify_{$notification->getId()}.log")));
			$logger->emit('DEBUG', "isVisibility start: module={$moduleName}, record={$recordId}, user=" . (($userId !== null) ? $userId : 'null'));
			$logger->emit('DEBUG', "sqlFilter raw: " . var_export($notification->getFilter()->getSqlFilter(), true));
			$logger->emit('DEBUG', "sqlFilter cleaned: " . var_export($sqlFilter, true));
			$logger->emit('DEBUG', "where clause: " . var_export($where, true));
		}

		// For Users module (global notifications), period filters should NOT be applied
		// because vtiger_users doesn't have createdtime/modifiedtime columns
		// Period filters are only relevant for entity modules (Contacts, Accounts, etc.)
		if (!empty($standardFilter['startdate']) && !empty($standardFilter['enddate']) && !empty($fieldToPeriod) && $moduleName !== 'Users') {
			$where = " ({$where})  AND (DATE({$fieldToPeriod}) BETWEEN STR_TO_DATE('{$standardFilter['startdate']}','%Y-%m-%d') AND STR_TO_DATE('{$standardFilter['enddate']}','%Y-%m-%d'))";
			if ($logger instanceof Logger) {
				$logger->emit('DEBUG', "Standard filter range: field={$fieldToPeriod}, start={$standardFilter['startdate']}, end={$standardFilter['enddate']}");
			}
		} elseif ($moduleName === 'Users' && !empty($standardFilter['startdate'])) {
			if ($logger instanceof Logger) {
				$logger->emit('DEBUG', "SKIP period filter for Users module (vtiger_users doesn't support createdtime filters)");
			}
		}
		
		// Special handling for Users module (global notifications)
		if ($moduleName === 'Users') {
			// Replace tq. with vtiger_users. in sqlfilter for Users module
			$whereBefore = $where;
			$where = str_replace('tq.', 'vtiger_users.', $where);
			// Replace crm. with vtiger_users. in sqlfilter for Users module (if any)
			$where = str_replace('crm.', 'vtiger_users.', $where);
			
			if ($logger instanceof Logger) {
				$logger->emit('DEBUG', "Users module detected, sqlfilter before: {$whereBefore}");
				$logger->emit('DEBUG', "Users module detected, sqlfilter after: {$where}");
			}
			
			// For Users module, query vtiger_users directly (no CRM entity)
			// Only query if where is not just "1" (which means no filter)
			if ($where !== '1') {
				// IMPORTANT: For Users module with advanced filters, we need to check if the CURRENT USER matches the filter
				// We need to get the current user ID from the context
				// Since userId is passed as parameter, we should use it, but if it's null, we need to get it from the session
				$currentUserId = $userId;
				if (empty($currentUserId)) {
					// Try to get current user from session or global context
					global $current_user;
					if (!empty($current_user) && !empty($current_user->id)) {
						$currentUserId = $current_user->id;
					}
				}
				
				if (!empty($currentUserId)) {
					// Add condition to check if current user matches the filter
					$query = "SELECT * FROM vtiger_users WHERE id = {$currentUserId} AND ({$where})";
					if ($logger instanceof Logger) {
						$logger->emit('DEBUG', "Current user ID: {$currentUserId}");
						$logger->emit('DEBUG', "Executing query: {$query}");
					}
				} else {
					// If we can't get current user ID, fall back to checking if any user matches (old behavior)
					$query = "SELECT * FROM vtiger_users WHERE {$where}";
					if ($logger instanceof Logger) {
						$logger->emit('DEBUG', "WARNING: No current user ID available, checking if any user matches filter");
						$logger->emit('DEBUG', "Executing query: {$query}");
					}
				}
				
				$result = $adb->query($query);
				$visibility = (($result) && ($adb->num_rows($result) > 0));
				if ($logger instanceof Logger) {
					$logger->emit('DEBUG', 'Query result: ' . ($visibility ? 'matched rows' : 'no rows'));
					if ($result) {
						$logger->emit('DEBUG', 'Number of rows: ' . $adb->num_rows($result));
					}
				}
			} else {
				// No filter, show to all users
				$visibility = true;
				if ($logger instanceof Logger) {
					$logger->emit('DEBUG', 'No filter (where=1), showing to all users');
				}
			}
		} else {
			// Standard CRM entity handling
			if (!empty($userId)) {
				$whereUser = "crm.smownerid={$userId} AND";
				if ($logger instanceof Logger) {
					$logger->emit('DEBUG', "User filter clause: {$whereUser}");
				}
			}
			$entity = PlatformUtils::getCrmEntity($adb, $moduleName);

			if (!empty($entity)) {
				$where .= (($notification->getView() == Notification::DETAIL_VIEW || $notification->getView() == Notification::EDIT_VIEW) && $recordId) ? " AND ({$entity->table_index} = {$recordId})" : '';
				if ($logger instanceof Logger) {
					$logger->emit('DEBUG', "Final WHERE clause: {$where}");
				}

				$result     = $adb->query(
					"SELECT *
						FROM
						{$entity->table_name} tq
					  	INNER JOIN vtiger_crmentity crm ON crm.crmid = tq.{$entity->table_index}
						WHERE
						{$whereUser}
						 crm.deleted = 0
						AND
						 {$where}"
				);
				$visibility = (($result) && ($adb->num_rows($result) > 0));
				if ($logger instanceof Logger) {
					$logger->emit('DEBUG', 'Query result: ' . ($visibility ? 'matched rows' : 'no rows'));
				}
			}
		}

		return $visibility;
	}

	/**
	 * Escribe el log de la notificación
	 *
	 * @param $platform
	 */
	private static function setLogDir($platform)
	{
		$platzillaRootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath();
		if (!is_dir("{$platzillaRootFolderPath}/{$platform}/logs/notificationslogs")) {
			mkdir("{$platzillaRootFolderPath}/{$platform}/logs/notificationslogs", 0777, true);
		}
	}

	/**
	 * @param PearDatabase$adb
	 * @param NotificationFilter $notify
	 */
	private static function resetRecordNotify($adb, $notify)
	{
		$adb->pquery('UPDATE vtiger_notifications_filters SET recordid=? WHERE notificationid=?', array(null, $notify->getId()));
	}

	/**
	 * Cambia el estatus de una notificación
	 *
	 * @param PearDatabase $adb
	 * @param integer $notificationId
	 * @param boolean $isLocked
	 * @param stdClass $platform
	 *
	 * @return string
	 * @throws Exception
	 * @throws NotificationException
	 */
	public static function changeStatusNotification(PearDatabase $adb, $notificationId, $isLocked, $platform)
	{
		$nm     = NotificationManager::getInstance($adb);
		$notify = $nm->fetchNotification($notificationId);

		if (empty($notify)) {
			throw new Exception('No se encuentra registrada la notificacion con el ID suministrado');
		}

		self::setLogDir($platform);

		if ($notify->getStatus() === Notification::STATUS_ACTIVE) {
			$notify->setStatus(Notification::STATUS_INACTIVE);
			$message = 'La notificación ha sido deshabilitada';
		} else {
			$notify->setStatus(Notification::STATUS_ACTIVE);
			$message = 'La notificación ha sido habilitada';
		}

		$notify->setLocked($isLocked);
		$nm->saveNotification($notify);
		$logger = new Logger('notifications', array('appender' => array('File' => "{$platform}/logs/notificationslogs/notify_{$notify->getId()}.log")));
		$logger->emit('INFO', $message);
		return $message;
	}

	/**
	 * Duplica una notificación
	 *
	 * @param PearDatabase $adb
	 * @param integer $notificationId
	 *
	 * @return Notification
	 * @throws Exception
	 */
	public static function duplicateNotification(PearDatabase $adb, $notificationId)
	{
		$notify = NotificationManager::getInstance($adb)->fetchNotification($notificationId);
		if (empty($notify)) {
			throw new Exception('No se encuentra registrada la notificacion con el ID suministrado');
		}

		return $notify->duplicate()->setName(null)->setDescription(null);
	}

	/**
	 * Devuelve una colección de objetos Notification con todos sus atributos
	 *
	 * @param PearDatabase $adb
	 * @param array $data
	 *
	 * @return Notification[]|null
	 * @throws Exception
	 */
	public static function fetchApplicableOnScreenNotifications(PearDatabase $adb, $data)
	{
		$view       = $data['view'];
		$moduleName = $data['module'];
		$user       = $data['user'];
		$style      = $data['style'];
		$recordId   = $data['recordId'];
		$platform   = $data['platform'];
		$mode       = isset($data['mode']) ? $data['mode'] : null;

		self::setLogDir($platform);
		$results            = array();
		$contextLogger      = new Logger('notifications', array('appender' => array('File' => "{$platform}/logs/notificationslogs/notify_context.log")));
		$contextLogger->emit('DEBUG', "fetchApplicableOnScreenNotifications invoked: view={$view}, module={$moduleName}, style={$style}, record={$recordId}, user={$user->id}, mode={$mode}");
		
		// Determine event type based on mode (same logic as fetchApplicableOnScreenNotificationsModal)
		$eventType = null;
		if ($mode == 'save') {
			$eventType = Notification::EVENT_SAVE_RECORD;
		} else if ($mode == 'edit') {
			$eventType = Notification::EVENT_EDIT_RECORD;
		} else if ($mode == 'cancel') {
			$eventType = Notification::EVENT_CANCEL_RECORD;
		} else if ($mode == 'create') {
			$eventType = Notification::EVENT_CREATE_RECORD;
		}
		
		$notificationsGroup = NotificationManager::getInstance($adb)->fetchApplicableNotifications($view, $moduleName, Notification::TYPE_SCREEN, $user, $style);
		if (empty($notificationsGroup)) {
			$contextLogger->emit('DEBUG', 'No notifications fetched for current context');
			return $results;
		}
		
		$contextLogger->emit('DEBUG', 'Notifications fetched: count=' . count($notificationsGroup));
	foreach ($notificationsGroup as $n) {
		$contextLogger->emit('DEBUG', "- Notification in array: ID={$n->getId()}, event={$n->getEvent()}, view={$n->getView()}");
	}

	foreach ($notificationsGroup as $notify) {
		// Skip MODAL notifications with FROM_BACKGROUNDTASK event - they are only triggered explicitly from background tasks
		// ALERT and NOTIFY notifications with FROM_BACKGROUNDTASK event are allowed to display normally
		if ($notify->getEvent() === Notification::EVENT_FROM_BACKGROUNDTASK && $notify->getStyle() === Notification::STYLE_MODAL) {
			$contextLogger->emit('DEBUG', "Notification {$notify->getId()} skipped: FROM_BACKGROUNDTASK event with MODAL style (only triggered explicitly from background tasks)");
			continue;
		}
		
		// Filter by event type if mode is specified
		// IMPORTANT: ALWAYS and FROM_BACKGROUNDTASK events should never be filtered out by mode
		if ($eventType !== null && $notify->getEvent() !== $eventType && $notify->getEvent() !== Notification::EVENT_ALWAYS && $notify->getEvent() !== Notification::EVENT_FROM_BACKGROUNDTASK) {
			$contextLogger->emit('DEBUG', "Notification {$notify->getId()} skipped: event mismatch (expected={$eventType}, got={$notify->getEvent()})");
			continue;
		}
			$isNotifyVisibility = true;
			$users              = $notify->getFilter()->getUsersFilter();
			$visibilityReason   = 'Visibility granted';
			$logger             = new Logger('notifications', array('appender' => array('File' => "{$platform}/logs/notificationslogs/notify_{$notify->getId()}.log")));
			$logger->emit('DEBUG', "Evaluating notify {$notify->getId()}: event={$notify->getEvent()}, view={$notify->getView()}, users=" . implode(',', $users));

			if ((in_array($user->id, $users)) || (in_array('0', $users))) {
				// Check if user is in the filter
				$hasAdvancedFilter = !empty($notify->getFilter()->getSqlFilter()) || (!empty($notify->getFilter()->getAdvancedFilter()));
				$hasStandardFilter = !empty($notify->getFilter()->getStandardFilter()) && !empty($notify->getFilter()->getColumnPeriod());
				
				if ($notify->getFilter()->getRecordId() == $recordId) {
					// Specific record filter
					$isNotifyVisibility = true;
					if ($style == 'MODAL') {
						$notify->setEvent('FIRST TIME');
					}
					self::resetRecordNotify($adb, $notify->getFilter());
				} else if ($hasAdvancedFilter || $hasStandardFilter) {
					// Advanced filter or standard filter: must evaluate SQL filter
					// Even if "all users" (usersFilter contains '0'), advanced filters must be applied
					// IMPORTANT: Pass the current user ID so the filter can check if THIS user matches
					$isNotifyVisibility = self::isVisibility($adb, $notify, $recordId, $user->id, $platform);
					if ($isNotifyVisibility) {
						$visibilityReason = 'Filters matched';
					} else {
						$visibilityReason = 'Filters returned no rows';
					}
					$isNotifyVisibility = ($notify->getEvent() !== Notification::EVENT_RECORD_NO_CREATE) ? $isNotifyVisibility : !$isNotifyVisibility;
				} else {
					// No advanced filter and no standard filter: show to all users in the filter
					$isNotifyVisibility = true;
					$visibilityReason = 'No filters, showing to all users in filter';
				}
			} else {
				$isNotifyVisibility = false;
				$visibilityReason   = 'Current user not included in users filter';
			}

			if ($isNotifyVisibility) {
				$logger->emit('INFO', "({$style}) {$notify->getName()}: se ha activado en {$moduleName}");
				$logger->emit('DEBUG', "Filtro evaluado: {$notify->getFilter()->getSqlFilter()} / Record {$recordId}");
				$results[] = $notify;
				if ($notify->getSendByEmail() == $notify::STATUS_ACTIVE) {
					BackgroundTasksRunner::getInstance($adb, $platform)->runManuallyTriggeredTask('[SYS] - Enviar notificacion', $notify->getId());
				}
			} else {
				$logger->emit('DEBUG', "Notification skipped for user {$user->id}. Reason: {$visibilityReason}. SQL: {$notify->getFilter()->getSqlFilter()} / Record {$recordId}");
			}
			unset($users);
		}


		unset($notificationsGroup);
		$contextLogger->emit('DEBUG', "fetchApplicableOnScreenNotifications returning: count=" . count($results));
		return $results;
	}

	/**
	 * Devuelve el ID/NULL si hay la Notificación modal disponibles
	 *
	 * @param PearDatabase $adb
	 * @param array $data
	 *
	 * @return integer|null
	 * @throws Exception
	 */
	public static function fetchApplicableOnScreenNotificationsModal(PearDatabase $adb, $data)
	{
		$eventType          = '';
		$firstTimeEvent     = false;
		$found              = false;
		$moduleName         = $data['module'];
		$notificationFound  = null;
		$user               = isset($data['user']) ? $data['user'] : null;
		$view               = $data['view'];
		$platform           = isset($data['platform']) ? $data['platform'] : '';
		$modalLogger        = new Logger('notifications', array('appender' => array('File' => "{$platform}/logs/notificationslogs/notify_context.log")));
		$modalLogger->emit('DEBUG', "fetchApplicableOnScreenNotificationsModal invoked: view={$view}, module={$moduleName}, mode={$data['mode']}, record=" . (isset($data['recordId']) ? $data['recordId'] : 'null'));
		
		// Validate required data
		if ($user === null || !is_object($user) || !isset($user->id)) {
			$modalLogger->emit('ERROR', 'Invalid user object in data array');
			return null;
		}

		// FIRST: Check for pending modal from GET parameter (fallback if session fails)
		// This is a backup mechanism in case session_write_close() doesn't persist data
		$modalLogger->emit('DEBUG', 'Checking GET parameters: ' . json_encode(array('pending_modal_id' => isset($_GET['pending_modal_id']) ? $_GET['pending_modal_id'] : 'not set', 'REQUEST_URI' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'not set')));
		if (isset($_GET['pending_modal_id']) && !empty($_GET['pending_modal_id'])) {
			$pendingModalId = intval($_GET['pending_modal_id']);
			$modalLogger->emit('DEBUG', 'GET pending_modal_id found: raw=' . $_GET['pending_modal_id'] . ', intval=' . $pendingModalId);
			if ($pendingModalId > 0) {
				$modalLogger->emit('DEBUG', 'Pending modal found in GET parameter: id=' . $pendingModalId);
				// Store in session for future requests
				if (!isset($_SESSION['pending_notification_modal'])) {
					$_SESSION['pending_notification_modal'] = array();
				}
				if (!isset($_SESSION['pending_notification_modal'][$user->id])) {
					$_SESSION['pending_notification_modal'][$user->id] = array();
				}
				if (!isset($_SESSION['pending_notification_modal'][$user->id][$moduleName])) {
					$_SESSION['pending_notification_modal'][$user->id][$moduleName] = array();
				}
				if (isset($data['recordId']) && !empty($data['recordId'])) {
					$_SESSION['pending_notification_modal'][$user->id][$moduleName][$data['recordId']] = array('id' => $pendingModalId, 'ts' => time());
				}
				return $pendingModalId;
			}
		}

		// SECOND: Check for pending modal from previous save (before filtering by event type)
		// This allows SAVE_RECORD notifications to show even when mode is 'edit' after a save
		if (isset($data['recordId']) && !empty($data['recordId'])) {
			$recordId = $data['recordId'];
			$modalLogger->emit('DEBUG', 'Checking pending modal: record=' . $recordId . ', user=' . $user->id . ', module=' . $moduleName . ', session_id=' . session_id());
			// Log complete session structure for debugging
			if (isset($_SESSION['pending_notification_modal'])) {
				$modalLogger->emit('DEBUG', 'Session pending_notification_modal exists. Structure: ' . json_encode(array_keys($_SESSION['pending_notification_modal'])));
			} else {
				$modalLogger->emit('DEBUG', 'Session pending_notification_modal does not exist. Available session keys: ' . json_encode(array_keys($_SESSION)));
			}
			if (isset($_SESSION['pending_notification_modal'])) {
				$modalLogger->emit('DEBUG', 'Session pending_notification_modal exists');
				if (isset($_SESSION['pending_notification_modal'][$user->id])) {
					$modalLogger->emit('DEBUG', 'Session pending_notification_modal[' . $user->id . '] exists');
					if (isset($_SESSION['pending_notification_modal'][$user->id][$moduleName])) {
						$modalLogger->emit('DEBUG', 'Session pending_notification_modal[' . $user->id . '][' . $moduleName . '] exists');
						if (isset($_SESSION['pending_notification_modal'][$user->id][$moduleName][$recordId])) {
							$pending = $_SESSION['pending_notification_modal'][$user->id][$moduleName][$recordId];
							$modalLogger->emit('DEBUG', 'Pending modal found: id=' . $pending['id'] . ', ts=' . (isset($pending['ts']) ? $pending['ts'] : 'N/A'));
							// Clean expired pending modals (older than 5 minutes)
							if (isset($pending['ts']) && (time() - $pending['ts']) > 300) {
								unset($_SESSION['pending_notification_modal'][$user->id][$moduleName][$recordId]);
								$modalLogger->emit('DEBUG', 'Pending modal expired: id=' . $pending['id'] . ', module=' . $moduleName . ', record=' . $recordId);
							} else {
								unset($_SESSION['pending_notification_modal'][$user->id][$moduleName][$recordId]);
								$modalLogger->emit('DEBUG', 'Pending modal consumed: id=' . $pending['id'] . ', module=' . $moduleName . ', record=' . $recordId . ', user=' . $user->id);
								return $pending['id'];
							}
						} else {
							$modalLogger->emit('DEBUG', 'No pending modal for record=' . $recordId . ' in session[' . $user->id . '][' . $moduleName . ']');
							if (!empty($_SESSION['pending_notification_modal'][$user->id][$moduleName])) {
								$availableRecords = array_keys($_SESSION['pending_notification_modal'][$user->id][$moduleName]);
								$modalLogger->emit('DEBUG', 'Available pending modals for this module: ' . implode(', ', $availableRecords));
							}
						}
					} else {
						$modalLogger->emit('DEBUG', 'No pending modal module=' . $moduleName . ' in session[' . $user->id . ']');
					}
				} else {
					$modalLogger->emit('DEBUG', 'No pending modal user=' . $user->id . ' in session');
				}
			} else {
				$modalLogger->emit('DEBUG', 'Session pending_notification_modal does not exist');
			}
		} else {
			$modalLogger->emit('DEBUG', 'No recordId provided in data array');
		}

		// SECOND: Fetch notifications and filter by event type
		$notificationsGroup = self::fetchApplicableOnScreenNotifications($adb, $data);

		// Determine event type based on mode
		$mode = isset($data['mode']) ? $data['mode'] : '';
		if ($mode == 'save') {
			$eventType = Notification::EVENT_SAVE_RECORD;
		} else if ($mode == 'edit') {
			$eventType = Notification::EVENT_EDIT_RECORD;
		} else if ($mode == 'cancel') {
			$eventType = Notification::EVENT_CANCEL_RECORD;
		} else if ($mode == 'create') {
			$eventType = Notification::EVENT_CREATE_RECORD;
		} else {
			// Default to EDIT_RECORD if mode is not specified
			$eventType = Notification::EVENT_EDIT_RECORD;
		}
		
		$modalLogger->emit('DEBUG', "Modal expected event={$eventType}, notifications fetched=" . count($notificationsGroup));

		foreach ($notificationsGroup as $notification) {
			if ($found) {
				continue;
			} else if (
				(($notification->getEvent() == $eventType) ||
				($notification->getEvent() == Notification::EVENT_ALWAYS) ||
				(($notification->getEvent() == Notification::EVENT_FIRST_TIME) && $notification->getView() == $view)) &&
				(($notification->getFilter()->getModuleFilter() == $moduleName) ||
				($notification->getFilter()->getModuleFilter() == 'Users'))
			) {
				$notificationFound = $notification;
				$found          = true;
				$firstTimeEvent = ($notification->getEvent() == Notification::EVENT_FIRST_TIME);
			} else {
				$modalLogger->emit(
					'DEBUG',
					'Modal skipped: id=' . $notification->getId() .
					', event=' . $notification->getEvent() .
					', view=' . $notification->getView() .
					', module=' . $notification->getFilter()->getModuleFilter()
				);
			}
		}

		// THIRD: If we found a modal during SAVE, persist it for the next request (post-redirect delivery)
		if (($notificationFound) && ($data['mode'] == 'save') && !empty($data['recordId'])) {
			if (!isset($_SESSION['pending_notification_modal'])) {
				$_SESSION['pending_notification_modal'] = array();
			}
			if (!isset($_SESSION['pending_notification_modal'][$user->id])) {
				$_SESSION['pending_notification_modal'][$user->id] = array();
			}
			if (!isset($_SESSION['pending_notification_modal'][$user->id][$moduleName])) {
				$_SESSION['pending_notification_modal'][$user->id][$moduleName] = array();
			}
			$_SESSION['pending_notification_modal'][$user->id][$moduleName][$data['recordId']] = array('id' => $notificationFound->getId(), 'ts' => time());
			// Force session write immediately by accessing the variable
			// This ensures PHP marks the session as modified and will write it
			$sessionTest = $_SESSION['pending_notification_modal'][$user->id][$moduleName][$data['recordId']];
			// Force session data to be written by touching the session variable
			// This is critical: PHP only writes session data if it detects changes
			// By accessing the nested array, we ensure PHP marks the session as dirty
			$dummy = $_SESSION['pending_notification_modal'][$user->id][$moduleName][$data['recordId']]['id'];
			$modalLogger->emit('DEBUG', 'Pending modal set: id=' . $notificationFound->getId() . ', module=' . $moduleName . ', record=' . $data['recordId'] . ', user=' . $user->id . ', session_id=' . session_id());
			// Log session contents for debugging
			if (isset($_SESSION['pending_notification_modal'])) {
				$modalLogger->emit('DEBUG', 'Session pending_notification_modal structure: ' . json_encode(array_keys($_SESSION['pending_notification_modal'])));
				// Verify the pending modal was actually stored
				if (isset($_SESSION['pending_notification_modal'][$user->id][$moduleName][$data['recordId']])) {
					$stored = $_SESSION['pending_notification_modal'][$user->id][$moduleName][$data['recordId']];
					$modalLogger->emit('DEBUG', 'Verified pending modal in session: id=' . $stored['id'] . ', ts=' . $stored['ts']);
				} else {
					$modalLogger->emit('ERROR', 'Pending modal NOT found in session after setting!');
				}
			}
		}
		if ($firstTimeEvent) {
			NotificationManager::getInstance($adb)->disableNotification($notificationFound, $user);
		}
		$modalLogger->emit('DEBUG', ($notificationFound) ? "Modal candidate found: id={$notificationFound->getId()}, event={$notificationFound->getEvent()}, view={$notificationFound->getView()}" : 'No modal notification matched current context');

		return ($notificationFound) ? $notificationFound->getId() : null;
	}

	/**
	 * Devuelve el arreglo de acciones disponibles
	 *
	 * @return string[]
	 */
	public static function getAvailableActions()
	{
		return array(
			Notification::ACTION_DANGER,
			Notification::ACTION_INFO,
			Notification::ACTION_SUCCESS,
			Notification::ACTION_WARNING,
		);
	}

	/**
	 * Devuelve un arreglo de modulos disponibles
	 *
	 * @param PearDatabase $adb
	 *
	 * @return array|null
	 * @throws Exception
	 */
	public static function fetchAvailableEntityModules(PearDatabase $adb)
	{
		$result = $adb->query('SELECT t.* FROM vtiger_tab t WHERE (t.presence IN (0, 2) AND t.isentitytype=1)');
		if ((!$result) || ($adb->num_rows($result) == 0)) {
			return null;
		}

		$modules = array();
		while ($row = $adb->fetchByAssoc($result, -1, false)) {
			$row['tablabel'] = getTranslatedString($row['tablabel'], $row['name']);
			$modules[]       = $row;
		}
		usort(
			$modules,
			function ($moduleA, $moduleB) {
				return strcmp($moduleA['tablabel'], $moduleB['tablabel']);
			}
		);
		array_unshift($modules, array('name' => 'Home', 'tablabel' => '(Portada)'));
		return $modules;
	}

	/**
	 * Devuelve un arreglo de eventos disponibles
	 *
	 * @return string[]
	 */
	public static function getAvailableEvents()
	{
		return array(
			Notification::EVENT_ALWAYS,
			Notification::EVENT_RECORD_NO_CREATE,
			Notification::EVENT_CANCEL_RECORD,
			Notification::EVENT_CREATE_RECORD,
			Notification::EVENT_EDIT_RECORD,
			Notification::EVENT_FIRST_TIME,
			Notification::EVENT_SAVE_RECORD,
			Notification::EVENT_TOTAL_RECORDS_REACHED,
			Notification::EVENT_FROM_BACKGROUNDTASK,
		);
	}

	/**
	 * Devuelve lista de botones con acción link disponibles
	 *
	 * @param PearDatabase $adb
	 * @param $isInstance
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function fetchCustomButtons(PearDatabase $adb, $isInstance)
	{
		if ($isInstance) {
			$result = $adb->query('SELECT * FROM vtiger_tab WHERE presence IN (0, 2)');
			if (($result) && ($adb->num_rows($result) > 0)) {
				$moduleNames = array();
				while ($row = $adb->fetchByAssoc($result, -1, false)) {
					$moduleNames[] = $row['name'];
				}
				$questionMarks = str_repeat('?, ', (count($moduleNames) - 1)) . '?';
				$result = $adb->pquery("SELECT * FROM vtiger_custombuttons WHERE module IN ({$questionMarks}) AND active=1 AND type='link'", $moduleNames);
			} else {
				$result = null;
			}
		} else {
			$result = $adb->query('SELECT * FROM vtiger_custombuttons WHERE active=1  AND type="link"');
		}
		$customButtons = array();
		if (($result) && ($adb->num_rows($result) > 0)) {
			while ($row = $adb->fetchByAssoc($result)) {
				$customButtons[] = $row;
			}
		}
		DatabaseUtils::closeResult($result);
		$result = null;
		return $customButtons;
	}

	/**
	 * Devuelve la estructura de datos de un botón personalizado dado su ID
	 *
	 * @param PearDatabase $adb
	 * @param array $buttonIds
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function fetchCustomButtonsData(PearDatabase $adb, $buttonIds)
	{
		$data = array();
		foreach ($buttonIds as $id) {
			if (empty($id) || $id == null) {
				continue;
			}
			$result = $adb->pquery('SELECT style, label, link, action, module FROM vtiger_custombuttons WHERE custombuttonid=?', array($id));
			if (($result) && ($adb->num_rows($result) > 0)) {
				while ($row = $adb->fetchByAssoc($result)) {
					$data[] = $row;
				}
			}
		}
		DatabaseUtils::closeResult($result);
		$result = null;
		return $data;
	}

	/**
	 * Obtiene un colección de objetos Notification
	 *
	 * @param PearDatabase $adb
	 * @param string $keyword
	 * @param integer $page
	 * @param integer $rowsPerPage
	 * @param string $scope
	 *
	 * @return Notification[]
	 * @throws Exception
	 */
	public static function fetchNotifications(PearDatabase $adb, $keyword, $page, $rowsPerPage, $scope)
	{
		return NotificationManager::getInstance($adb)->searchNotifications($keyword, $page, $rowsPerPage, $scope);
	}

	/**
	 * Obtiene un objeto Notification dado su ID
	 *
	 * @param PearDatabase $adb
	 * @param integer $notificationId
	 *
	 * @return Notification|null
	 * @throws Exception
	 */
	public static function fetchNotification(PearDatabase $adb, $notificationId)
	{
		return NotificationManager::getInstance($adb)->fetchNotification($notificationId);
	}

	/**
	 * @param PearDatabase $adb
	 * @param string $moduleName
	 * @param User $current_user
	 *
	 * @return null|BackgroundTask[]
	 */
	public static function getAutomatedActivities($adb, $moduleName, $current_user)
	{
		if (empty($moduleName)) {
			return null;
		}

		$activities            = array();
		$modules               = array();
		$availableApplications = ApplicationsManager::getInstance($adb)->getAvailableApplications($current_user);
		foreach ($availableApplications as $application) {
			if (in_array($moduleName, $application->getModules())) {
				if (!count($modules)) {
					$modules = $application->getModules();
				} else {
					$modules = array_merge($modules, $application->getModules());
				}
			}
		}

		foreach (array_unique($modules) as $module) {
			$tasks = BackgroundTaskManager::getInstance($adb)->fetchTasks($module, 'USER', false, false);
			if (empty($tasks)) {
				continue;
			}
			if (!count($activities)) {
				$activities = $tasks;
			} else {
				$activities = array_merge($activities, $tasks);
			}
		}
		return (count($activities)) ? $activities : null;
	}

	/**
	 * @param PearDatabase $adb
	 * @param BackgroundTask[] $tasks
	 *
	 * @return array|null
	 */
	public static function getAvailableCategories($adb, $tasks)
	{
		if (empty($tasks)) {
			return null;
		}
		$categories          = array();
		$availableCategories = BackgroundTaskManager::getInstance($adb)->fetchAvailableCategories();
		foreach ($availableCategories as $category) {
			foreach ($tasks as $task) {
				if ($task->getCategory() == $category['categoryname']) {
					$categories[] = $category;
					break;
				}
			}
		}
		return (count($categories)) ? $categories : null;
	}

	/**
	 * Obtiene el arreglo de clientes de las notificaciones
	 *
	 * @return string[]
	 */
	public static function getAvailableFrom()
	{
		return array(Notification::FROM_SYSTEM, Notification::FROM_USERS);
	}

	/**
	 * Devuelve un arreglo de estatus disponibles
	 *
	 * @return string[]
	 */
	public static function getAvailableStatuses()
	{
		return array(Notification::STATUS_ACTIVE, Notification::STATUS_INACTIVE);
	}

	/**
	 * Devuelve un arreglo de estilos de notificaciones disponibles
	 *
	 * @return string[]
	 */
	public static function getAvailableStyle()
	{
		return array(Notification::STYLE_ALERT, Notification::STYLE_MODAL, Notification::STYLE_NOTIFY);
	}

	/**
	 * Devuelve un arreglo de tipos de notificaciones disponibles
	 *
	 * @return string[]
	 */
	public static function getAvailableTypes()
	{
		return array(Notification::TYPE_EMAIL, Notification::TYPE_SCREEN);
	}

	/**
	 * Devuelve un arreglo de vistas disponibles para notificaciones
	 *
	 * @return string[]
	 */
	public static function getAvailableViews()
	{
		return array(
			Notification::DETAIL_VIEW,
			Notification::EDIT_VIEW,
			Notification::LIST_VIEW,
			'HOME_VIEW'
		);
	}

	/**
	 * Obtiene la estructura de la formula para el filtro de una notificación
	 *
	 * @param string $fields
	 * @param string $values
	 * @param string $operators
	 *
	 * @return string
	 */
	private static function getEquation($fields, $values, $operators)
	{
		$equation   = '';
		$typeofdata = array(
			'V'  => array('e' => ' LIKE "@"', 'n' => ' NOT LIKE "@"', 's' => ' LIKE "@%"', 'ew' => ' LIKE "%@"', 'c' => ' LIKE "%@%"', 'k' => ' NOT LIKE "%@%"'),
			'N'  => array('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= '),
			'T'  => array('e' => ' DATE( * ) = DATE( "@" )', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' DATE( * ) < DATE( "@" )', 'a' => ' DATE( * ) > DATE( "@" )'),
			'I'  => array('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= '),
			'C'  => array('e' => ' = ', 'n' => ' != '),
			'D'  => array('e' => ' DATE( * ) = DATE( "@" )', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' DATE( * ) < DATE( "@" )', 'a' => ' DATE( * ) > DATE( "@" )'),
			'DT' => array('e' => ' DATE( * ) = DATE( "@" )', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' DATE( * ) < DATE( "@" )', 'a' => ' DATE( * )  > DATE( "@" )'),
			'NN' => array('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= '),
			'E'  => array('e' => ' LIKE "@"', 'n' => ' NOT LIKE "@"', 's' => ' LIKE "@%"', 'ew' => ' LIKE "%@"', 'c' => ' LIKE "%@%"', 'k' => ' NOT LIKE "%@%"'),
		);

		list($fieldType, $fieldName, $tablaAlias) = explode('@', $fields);
		$fieldName = $tablaAlias . $fieldName;
		
		// Extract decimal precision from fieldType if present (e.g., NN~O~10,2)
		$decimalPrecision = null;
		if (strpos($fieldType, '~') !== false) {
			$typeParts = explode('~', $fieldType);
			$baseType = $typeParts[0];
			if (count($typeParts) >= 3 && strpos($typeParts[2], ',') !== false) {
				// Format is like NN~O~10,2 where 10,2 is precision
				$decimalPrecision = $typeParts[2];
				// Override fieldType to use base type for operator lookup
				$fieldType = $baseType;
			}
		}
		list($min, $max) = explode(',', $values);

		// Translate field values for specific fields (e.g., status: 'Activo' -> 'Active', 'Inactivo' -> 'Inactive')
		// This is necessary because the database stores values in English but the UI may use Spanish
		$min = self::translateFieldValue($fieldName, $min);
		if (!empty($max)) {
			$max = self::translateFieldValue($fieldName, $max);
		}

		$operated = $typeofdata[$fieldType][$operators];

		$posValue = strripos($operated, '@');
		if ($posValue !== false) {
			$operated = str_replace('@', $min, $operated);
			if (!empty($max)) {
				$operated = str_replace('_', $max, $operated);
			}
		}
		$posField = strripos($operated, '*');
		if ($posField !== false) {
			$equation .= str_replace('*', $fieldName, $operated);
		} else if ($posValue === false) {
			// For DECIMAL fields, cast the value to ensure proper comparison
			if ($decimalPrecision !== null && !empty($min)) {
				// Extract precision parts (e.g., "10,2" -> precision=10, scale=2)
				list($precision, $scale) = explode(',', $decimalPrecision);
				// Cast value to DECIMAL for accurate comparison
				$equation .= $fieldName . $operated . 'CAST(' . $min . ' AS DECIMAL(' . $precision . ',' . $scale . '))';
			} else {
				$equation .= $fieldName . $operated . $min;
			}
		} else {
			$equation .= $fieldName . $operated;
		}
		return $equation;
	}

	/**
	 * Translates field values from Spanish (UI) to English (database)
	 * 
	 * This method handles translation of specific field values that are displayed
	 * in Spanish in the UI but stored in English in the database. For example,
	 * the "status" field may display "Activo" in the UI but is stored as "Active"
	 * in the database.
	 *
	 * @param string $fieldName The name of the field (e.g., "status", "vtiger_users.status")
	 * @param string $value The value to translate (e.g., "Activo", "Inactivo")
	 * @return string The translated value (e.g., "Active", "Inactive") or original value if no translation needed
	 */
	private static function translateFieldValue($fieldName, $value)
	{
		// If value is empty, return as is
		if (empty($value)) {
			return $value;
		}

		// Extract field name without table alias (e.g., "vtiger_users.status" -> "status")
		$fieldNameOnly = $fieldName;
		if (strpos($fieldName, '.') !== false) {
			$parts = explode('.', $fieldName);
			$fieldNameOnly = end($parts);
		}

		// Translation mappings for specific fields
		// Format: 'fieldname' => array('spanish_value' => 'english_value')
		$translations = array(
			'status' => array(
				'Activo' => 'Active',
				'Inactivo' => 'Inactive',
				'activo' => 'Active',
				'inactivo' => 'Inactive',
			),
		);

		// Check if this field has translations defined
		if (isset($translations[$fieldNameOnly])) {
			$fieldTranslations = $translations[$fieldNameOnly];
			// Check if the value needs translation
			if (isset($fieldTranslations[$value])) {
				return $fieldTranslations[$value];
			}
		}

		// No translation needed, return original value
		return $value;
	}

	/**
	 * Obtiene los campos que de un módulo dado su nombre
	 *
	 * @param PearDatabase $adb
	 * @param string $moduleName
	 *
	 * @return array|null
	 * @throws Exception
	 */
	public static function getColumnsByModule(PearDatabase $adb, $moduleName)
	{
		$presenceValues = array(FieldInterface::PRESENCE_VISIBLE, FieldInterface::PRESENCE_USER_DEFINED);
		$sqlData        = array($moduleName);
		$uitypeValues   = array(
			FieldInterface::UI_TYPE_MODULE_REFERENCE,
			FieldInterface::UI_TYPE_PHONE,
			FieldInterface::UI_TYPE_EMAIL,
			FieldInterface::UI_TYPE_URL,
			FieldInterface::UI_TYPE_IMAGE_REFERENCE,
			FieldInterface::UI_TYPE_GRID,
			FieldInterface::UI_TYPE_MODIFIED_BY,
		);

		$sqlValues = array_merge($presenceValues, $uitypeValues, $sqlData);

		$result = $adb->pquery(
			'SELECT
					f.fieldid,
					f.fieldname,
					f.fieldlabel,
					f.tablename,
					f.uitype,
					f.typeofdata,
					f.helpinfo
				FROM
					vtiger_field f
					INNER JOIN vtiger_blocks b ON f.block=b.blockid AND b.visible=0 AND b.display_status=1
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					f.presence IN (?, ?) AND
					f.uitype NOT IN (?, ?, ?, ?, ?, ?, ?) AND
					t.name=?',
			$sqlValues
		);
		if ((!$result) || ($adb->num_rows($result) == 0)) {
			return null;
		}

		$columns = array();
		while ($row = $adb->fetchByAssoc($result, -1, false)) {
			$fieldtype  = explode('~', $row['typeofdata']);
			// Clean and translate helpinfo
			$helpinfo = '';
			if (!empty($row['helpinfo'])) {
				// Decode HTML entities first
				$helpinfo = html_entity_decode($row['helpinfo'], ENT_QUOTES, 'UTF-8');
				// Strip HTML tags to get plain text
				$helpinfo = strip_tags($helpinfo);
				// Try to translate using field-specific key: HELPINFO_<fieldname>_<module>
				// Example: HELPINFO_currency_symbol_placement_Users
				$helpinfoKey = 'HELPINFO_' . strtoupper($row['fieldname']) . '_' . strtoupper($moduleName);
				$translatedHelpinfo = getTranslatedString($helpinfoKey, $moduleName);
				// If translation key exists and is different from the key itself, use it
				// Otherwise, try to translate the original text directly
				if ($translatedHelpinfo != $helpinfoKey) {
					$helpinfo = $translatedHelpinfo;
				} else {
					// Try to translate the original text (might work if it's already a translation key)
					$helpinfo = getTranslatedString($helpinfo, $moduleName);
				}
				// Clean up extra whitespace
				$helpinfo = trim(preg_replace('/\s+/', ' ', $helpinfo));
			}
			$columns[] = array(
				'fieldname'  => $row['fieldname'],
				'label'      => html_entity_decode(getTranslatedString($row['fieldlabel'], $moduleName), ENT_QUOTES, 'UTF-8'),
				'tablename'  => $row['tablename'],
				'uitype'     => $row['uitype'],
				'typeofdata' => $fieldtype[0],
				'fieldid'    => $row['fieldid'],
				'helpinfo'   => $helpinfo,
				'module'     => $moduleName, // Add module name to identify source
			);
		}
		usort(
			$columns,
			function ($columnA, $columnB) {
				return strcmp($columnA['label'], $columnB['label']);
			}
		);
		return $columns;
	}

	/**
	 * Obtiene la relacion campo - tabla dado el tipo de campo
	 *
	 * @param array $fields
	 * @param array $fieldData
	 */
	public static function getFieldDataType(&$fields, $fieldData)
	{
		$totalFields = count($fields);
		foreach ($fieldData as $field) {
			for ($k = 0; $k < $totalFields; $k++) {
				if ($fields[$k] == $field['fieldname']) {
					$fields[$k] = $field['typeofdata'] . '@' . $fields[$k];
					if ($field['uitype'] == FieldInterface::UI_TYPE_CREATED_TIME) {
						$fields[$k] .= '@crm.';
					} else {
						$fields[$k] .= '@tq.';
					}
				}
			}
		}
	}

	/**
	 * Obtiene el sql del filtro para notificaciones
	 *
	 * @param PearDatabase $adb
	 * @param array $filterData
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function getSqlFilter(PearDatabase $adb, $filterData)
	{
		$fields       = $filterData['filterField'];
		$operators    = $filterData['filterOperator'];
		$values       = $filterData['filterValue'];
		$joins        = $filterData['filterJoin'];
		$groupJoins   = $filterData['filterGroupJoin'];
		$moduleFilter = $filterData['moduleFilter'];
		$grupoIndex   = $filterData['indexGrupo'];

		$fieldData = self::getColumnsByModule($adb, $moduleFilter);
		self::getFieldDataType($fields, $fieldData);

		$totalOperations = count($fields);
		$totalGroup      = count($groupJoins);
		$myGroup         = $grupoIndex[0];
		$nextOper        = 0;
		$equation        = '( ';
		$indexGroup      = 0;
		$indexJoin       = 0;

		if ($totalOperations > 0) {
			for ($op = 0; $op < $totalOperations; $op++) {
				$equation .= self::getEquation($fields[$op], $values[$op], $operators[$op]);

				$nextOper = ($nextOper < $totalOperations) ? ($nextOper + 1) : $op;

				if ($grupoIndex[$nextOper] != $myGroup) {
					$myGroup = $grupoIndex[$nextOper];
					if ($op < ($totalOperations - 1)) {
						$equation .= ' )';
						if ($indexGroup == 0) {
							$equation = '( ' . $equation;
						}
						$equation = $equation . ' ) ' . $groupJoins[$indexGroup] . ' ( ( ';
						$indexGroup++;
					} else if ($totalGroup > 0) {
						$equation = $equation . ' ))';
					} else {
						$equation = $equation . ' )';
					}
				} else {
					if ($op < ($totalOperations - 1)) {
						$equation = $equation . ' ) ' . $joins[$indexJoin] . ' ( ';
						$indexJoin++;
					} else {
						if ($indexGroup == 0) {
							$equation = $equation . ' ) ';
						} else {
							$equation = $equation . ' ) )';
						}
					}
				}
			}
			return $equation;
		} else {
			return '';
		}
	}

	/**
	 * Obtiene el tipo de operador dado el tipo de campo
	 *
	 * @return array
	 */
	public static function getTypeOfData()
	{
		return array(
			'V'  => array('e' => 'igual', 'n' => 'no igual a', 's' => 'empieza con', 'ew' => 'termina con', 'c' => 'contiene', 'k' => 'no contiene'),
			'N'  => array('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual'),
			'T'  => array('e' => 'igual', 'b' => 'antes', 'a' => 'después'),
			'I'  => array('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual'),
			'C'  => array('e' => 'igual', 'n' => 'no igual a'),
			'D'  => array('e' => 'igual', 'b' => 'antes', 'a' => 'después'),
			'DT' => array('e' => 'igual', 'b' => 'antes', 'a' => 'después'),
			'NN' => array('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual'),
			'E'  => array('e' => 'igual', 'n' => 'no igual a', 's' => 'empieza con', 'ew' => 'termina con', 'c' => 'contiene', 'k' => 'no contiene'),
		);
	}

	/**
	 * Obtiene arraglo de usuarios disponibles
	 *
	 * @param PearDatabase $adb
	 *
	 * @return array|null
	 * @throws Exception
	 */
	public static function getUsers(PearDatabase $adb)
	{
		$result = $adb->query(
			"SELECT
					u.*,
					u2r.roleid,
					r.rolename,
					DATE_FORMAT(u.date_entered,'%d/%m/%Y') AS date_entered
				FROM
					vtiger_users u
					INNER JOIN vtiger_user2role u2r ON u2r.userid=u.id
					INNER JOIN vtiger_role r ON u2r.roleid=r.roleid
				ORDER BY
					u.id"
		);
		if ((!$result) || ($adb->num_rows($result) == 0)) {
			return null;
		}

		$users = array();
		while ($row = $adb->fetchByAssoc($result)) {
			$row['profileimage'] = getUserImageName($row['id']);
			$users[]             = $row;
		}
		return $users;
	}

	/**
	 * Obtiene colección de objetos Notification tipo alartas habilitados
	 *
	 * @param PearDatabase $adb
	 * @param array $data
	 * @param Users $user
	 *
	 * @return Notification[]|null
	 * @throws Exception
	 */
	public static function searchAvailableAlerts(PearDatabase $adb, $data, $user)
	{
		$notificationsGroup = NotificationManager::getInstance($adb)->searchAlerts($data['period'], $user);
		$recordId           = 0;
		$results            = array();
		$user               = $data['user'];
		$platform           = $data['platform'];
		$showNotifications  = $data['show_notifications'];

		if (empty($notificationsGroup)) {
			return $results;
		}

		foreach ($notificationsGroup as $notify) {
			$isNotifyVisibility = true;
			$users              = $notify->getFilter()->getUsersFilter();

			if ((in_array($user->id, $users)) || (in_array('0', $users))) {
				if (!empty($notify->getFilter()->getSqlFilter()) || (!empty($notify->getFilter()->getStandardFilter()) && !empty($notify->getFilter()->getColumnPeriod()))) {
					$isNotifyVisibility = self::isVisibility($adb, $notify, $recordId, $user->id);
					if ($isNotifyVisibility) {
						$isNotifyVisibility = ($notify->getEvent() !== Notification::EVENT_RECORD_NO_CREATE) ? true : false;
					} else {
						$isNotifyVisibility = ($notify->getEvent() === Notification::EVENT_RECORD_NO_CREATE) ? true : false;
					}
				}
			} else {
				$isNotifyVisibility = false;
			}

			if ($isNotifyVisibility) {
				$results[] = $notify;
				if ($notify->getSendByEmail() == $notify::STATUS_ACTIVE && $showNotifications == 'on') {
					BackgroundTasksRunner::getInstance($adb, $platform)->runManuallyTriggeredTask('[SYS] - Enviar notificacion', $notify->getId());
				}
			}
			unset($users);
		}

		unset($notificationsGroup);
		return $results;
	}
	
}
