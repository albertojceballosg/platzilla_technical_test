<?php
	require_once ('include/platzilla/Objects/CalendarView.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Class CalendarViewManager
	 */
	class CalendarViewManager {
		/** @var CalendarViewManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param CalendarView $view
		 *
		 * @throws Exception
		 */
		private function createView ($view) {
			try {
				$this->adb->startTransaction ();
				$this->adb->pquery (
					'INSERT INTO vtiger_calendarviews (label, modulename, titlemodulename, titlefieldname, subtitlefieldname, frommodulename, fromfieldname, tomodulename, tofieldname, backgroundcolor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($view->getLabel (), $view->getModuleName (), $view->getTitleModuleName (), $view->getTitleFieldName (), $view->getSubTitle (),$view->getFromModuleName (), $view->getFromFieldName (), $view->getToModuleName (), $view->getToFieldName (), $view->getBackgroundColor ())
				);
				$viewId = $this->adb->getLastInsertID ();
				$view->setId ($viewId);
				$this->saveApplicationCodes ($view);
				$this->saveRules ($view);
				$this->adb->completeTransaction ();
			} catch (Exception $e) {
				$this->deleteView ($view);
				throw $e;
			}
		}

		/**
		 * @param integer $viewId
		 *
		 * @return string[]|null
		 */
		private function fetchApplicationCodes ($viewId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_calendarviews_applications WHERE calendarviewid=? ORDER BY applicationcode', array ($viewId));
			if ($this->adb->num_rows ($result) > 0) {
				$applicationCodes = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$applicationCodes [] = $row ['applicationcode'];
				}
			} else {
				$applicationCodes = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $applicationCodes;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return CalendarView[]
		 */
		private function fetchDeletedViews ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$views  = array ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('calendarview', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var CalendarView $view */
					$view = unserialize ($row ['serializedobject']);
					$view->setDeleted (true);
					$views [] = $view;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $views;
		}

		/**
		 * @param integer $viewId
		 *
		 * @return CalendarViewRule[]|null
		 */
		private function fetchRules ($viewId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_calendarviews_rules WHERE calendarviewid=? ORDER BY ruleid', array ($viewId));
			if ($this->adb->num_rows ($result) > 0) {
				$rules = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$rules [] = CalendarViewRule::getInstance ()
						->setBackgroundColor ($row ['backgroundcolor'])
						->setFieldName ($row ['fieldname'])
						->setId (intval ($row ['ruleid']))
						->setModuleName ($row ['modulename'])
						->setOperator ($row ['operator'])
						->setValue ($row ['value'])
						->setViewId ($viewId);
				}
			} else {
				$rules = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $rules;
		}

		/**
		 * @param CalendarView $view
		 */
		private function saveApplicationCodes ($view) {
			$applicationCodes          = $view->getApplicationCodes ();
			$viewId                    = $view->getId ();
			$processedApplicationCodes = array ();
			foreach ($applicationCodes as $applicationCode) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_calendarviews_applications WHERE calendarviewid=? AND applicationcode=?', array ($viewId, $applicationCode));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery ('INSERT INTO vtiger_calendarviews_applications (calendarviewid, applicationcode) VALUES (?, ?)', array ($viewId, $applicationCode));
				}
				DatabaseUtils::closeResult ($result);
				$result                       = null;
				$processedApplicationCodes [] = $applicationCode;
			}

			if (!empty ($processedApplicationCodes)) {
				$questionMarks = str_repeat ('?, ', (count ($processedApplicationCodes) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_calendarviews_applications WHERE calendarviewid=? AND applicationcode NOT IN ({$questionMarks})", array_merge (array ($viewId), $processedApplicationCodes));
			}
		}

		/**
		 * @param CalendarView $view
		 */
		private function saveRules ($view) {
			$rules = $view->getRules ();
			if (empty ($rules)) {
				return;
			}

			$viewId           = $view->getId ();
			$processedRuleIds = array ();
			foreach ($rules as $thisRules) {
				$idRule = $this->adb->getUniqueID ('vtiger_calendarviews_rules');
				foreach ($thisRules as $rule) {
					$ruleId = $rule->getId ();
					$rule->setViewId ($viewId);
					if (empty ($ruleId)) {
						$this->adb->pquery (
							'INSERT INTO vtiger_calendarviews_rules (ruleid, calendarviewid, modulename, fieldname, operator, value, joinrule, backgroundcolor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
							array($idRule, $viewId, $rule->getModuleName (), $rule->getFieldName (), $rule->getOperator (), $rule->getValue (), $rule->getJoinRule (), $rule->getBackgroundColor ())
						);
						$ruleId = $this->adb->getLastInsertID ();
					} else {
						$this->adb->pquery (
							'UPDATE vtiger_calendarviews_rules SET calendarviewid=?, modulename=?, fieldname=?, operator=?, value=?, joinrule=?, backgroundcolor=? WHERE ruleid=?',
							array($viewId, $rule->getModuleName (), $rule->getFieldName (), $rule->getOperator (), $rule->getValue (), $rule->getJoinRule (), $rule->getBackgroundColor (), $ruleId)
						);
					}
					$processedRuleIds [] = $ruleId;
				}
			}

			$questionMarks = str_repeat ('?, ', (count ($processedRuleIds) - 1)) . '?';
			$this->adb->pquery ("DELETE FROM vtiger_calendarviews_rules WHERE calendarviewid=? AND ruleid NOT IN ({$questionMarks})", array_merge (array ($viewId), $processedRuleIds));
		}

		/**
		 * @param CalendarView $view
		 */
		private function updateView ($view) {
			$this->adb->startTransaction ();
			$viewId = $view->getId ();
			$this->adb->pquery (
				'UPDATE vtiger_calendarviews SET backgroundcolor=?, fromfieldname=?, frommodulename=?, label=?, modulename=?, titlefieldname=?, subtitlefieldname=?, titlemodulename=?, tofieldname=?, tomodulename=? WHERE calendarviewid=?',
				array ($view->getBackgroundColor (), $view->getFromFieldName (), $view->getFromModuleName (), $view->getLabel (), $view->getModuleName (), $view->getTitleFieldName (), $view->getSubTitle (), $view->getTitleModuleName (), $view->getToFieldName (), $view->getToModuleName (), $viewId)
			);
			$this->saveApplicationCodes ($view);
			$this->saveRules ($view);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param CalendarView $view
		 *
		 * @throws CalendarViewException
		 * @throws CalendarViewRuleException
		 */
		private function validate ($view) {
			if ((empty ($view)) || (!($view instanceof CalendarView))) {
				throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_EMPTY);
			}

			$view->validate ();

			$moduleName = $view->getModuleName ();
			if (empty ($moduleName)) {
				throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_EMPTY_MODULE_NAME);
			}

			$result    = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			$totalRows = $this->adb->num_rows ($result);
			DatabaseUtils::closeResult ($result);
			$result = null;
			if ($totalRows == 0) {
				throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_INVALID_MODULE_NAME);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_calendarviews WHERE modulename=? AND label=?', array ($moduleName, $view->getLabel ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				DatabaseUtils::closeResult ($result);
				$result = null;
				$viewId = $view->getId ();
				if ((empty ($viewId)) || ($row ['calendarviewid'] != $viewId)) {
					throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_DUPLICATE_LABEL);
				}

				$this->validateViewField ($moduleName, $view->getTitleModuleName (), $view->getTitleFieldName ());
				$this->validateViewField ($moduleName, $view->getFromModuleName (), $view->getFromFieldName ());
				$this->validateViewField ($moduleName, $view->getToModuleName (), $view->getToFieldName ());
				$this->validateRules ($view);
			} else {
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
		}

		/**
		 * @param string $viewModuleName
		 * @param string $moduleName
		 * @param string $fieldName
		 * @param boolean $checkIfIsDateField
		 *
		 * @throws CalendarViewException
		 */
		private function validateViewField ($viewModuleName, $moduleName, $fieldName, $checkIfIsDateField = false) {
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return;
			}

			$result = $this->adb->pquery (
				'SELECT DISTINCT
					f.*
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
					LEFT JOIN vtiger_relatedlists rl ON rl.related_tabid=f.tabid
					LEFT JOIN vtiger_tab rlt ON rlt.tabid=rl.related_tabid
				WHERE
					(t.name=? AND f.fieldname=?) OR
					(rlt.name=? AND f.fieldname=?)',
				array ($viewModuleName, $fieldName, $moduleName, $fieldName)
			);
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_INVALID_FIELD_NAME);
			} else if ($checkIfIsDateField) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (!in_array ($row ['uitype'], array (FieldInterface::UI_TYPE_CREATED_TIME, FieldInterface::UI_TYPE_DATE, FieldInterface::UI_TYPE_DATETIME))) {
					throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_INVALID_DATE_FIELD);
				}
			} else {
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
		}

		/**
		 * @param CalendarView $view
		 *
		 * @throws CalendarViewRuleException
		 */
		private function validateRules ($view) {
			$rules = $view->getRules ();
			if (empty ($rules)) {
				return;
			}

			$viewModuleName = $view->getModuleName ();
			foreach ($rules as $thisRule) {
				foreach ($thisRule as $rule) {
					$rule->validate ();
					
					$moduleName = $rule->getModuleName ();
					$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array($moduleName));
					if ($this->adb->num_rows ($result) == 0) {
						$e = new CalendarViewRuleException (CalendarViewRuleException::ERROR_CALENDAR_VIEW_RULE_INVALID_MODULE_NAME);
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
					if (isset ($e)) {
						throw $e;
					}
					
					$fieldName = $rule->getFieldName ();
					$this->validateViewField ($viewModuleName, $moduleName, $fieldName);
				}
			}
		}

		/**
		 * @param CalendarView $view
		 */
		public function deleteView ($view) {
			if ((empty ($view)) || (!($view instanceof CalendarView))) {
				return;
			}

			$viewId = $view->getId ();
			if (empty ($viewId)) {
				return;
			}

			$moduleName = $view->getModuleName ();
			$identifier = $view->getId ();
			$this->adb->startTransaction ();
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('calendarview', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('calendarview', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($view)));
			}
			$this->adb->pquery ('DELETE FROM vtiger_calendarviews_applications WHERE calendarviewid=?', array ($view->getId ()));
			$this->adb->pquery ('DELETE FROM vtiger_calendarviews_rules WHERE calendarviewid=?', array ($view->getId ()));
			$this->adb->pquery ('DELETE FROM vtiger_calendarviews WHERE calendarviewid=?', array ($view->getId ()));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $moduleName
		 * @param boolean $ignoreLock
		 */
		public function deleteViews ($moduleName, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}

			$this->adb->startTransaction ();
			$this->adb->pquery ("DELETE FROM vtiger_calendarviews_applications WHERE calendarviewid IN (SELECT calendarviewid FROM vtiger_calendarviews WHERE modulename=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_calendarviews_rules WHERE calendarviewid IN (SELECT calendarviewid FROM vtiger_calendarviews WHERE modulename=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_calendarviews WHERE modulename=? {$whereClause}", array ($moduleName));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $moduleName
		 * @param string $viewLabel
		 *
		 * @return CalendarView|null
		 */
		public function fetchView ($moduleName, $viewLabel) {
			if ((empty ($moduleName)) || (empty ($viewLabel))) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_calendarviews WHERE modulename=? AND label=?', array ($moduleName, $viewLabel));
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$viewId = intval ($row ['calendarviewid']);
				$view   = CalendarView::getInstance ()
					->setApplicationCodes ($this->fetchApplicationCodes ($viewId))
					->setBackgroundColor ($row ['backgroundcolor'])
					->setFromFieldName ($row ['fromfieldname'])
					->setFromModuleName ($row ['frommodulename'])
					->setId ($viewId)
					->setLabel ($viewLabel)
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($moduleName)
					->setRules ($this->fetchRules ($viewId))
					->setSubTitle ($row ['subtitlefieldname'])
					->setTitleFieldName ($row ['titlefieldname'])
					->setTitleModuleName ($row ['titlemodulename'])
					->setToFieldName ($row ['tofieldname'])
					->setToModuleName ($row ['tomodulename']);
			} else {
				$view = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $view;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $includeDeleted
		 *
		 * @return CalendarView[]|null
		 */
		public function fetchViews ($moduleName, $includeDeleted = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_calendarviews WHERE modulename=?', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$views = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$viewId   = intval ($row ['calendarviewid']);
					$views [] = CalendarView::getInstance ()
						->setApplicationCodes ($this->fetchApplicationCodes ($viewId))
						->setBackgroundColor ($row ['backgroundcolor'])
						->setFromFieldName ($row ['fromfieldname'])
						->setFromModuleName ($row ['frommodulename'])
						->setId ($viewId)
						->setLabel ($row ['label'])
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($moduleName)
						->setRules ($this->fetchRules ($viewId))
						->setSubTitle ($row ['subtitlefieldname'])
						->setTitleFieldName ($row ['titlefieldname'])
						->setTitleModuleName ($row ['titlemodulename'])
						->setToFieldName ($row ['tofieldname'])
						->setToModuleName ($row ['tomodulename']);
				}
				if ($includeDeleted) {
					$deletedViews = $this->fetchDeletedViews ($moduleName);
				} else {
					$deletedViews = array ();
				}
				$views = array_merge ($views, $deletedViews);
			} else {
				$views = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $views;
		}

		/**
		 * @param CalendarView $view
		 * @param boolean $ignoreLock
		 *
		 * @return CalendarView
		 * @throws CalendarViewException
		 * @throws CalendarViewRuleException
		 */
		public function saveView ($view, $ignoreLock = true) {
			$this->validate ($view);

			$isDeleted = $view->isDeleted ();
			if ($isDeleted) {
				return $view;
			}

			$viewId = $view->getId ();
			if (!empty ($viewId)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_calendarviews v WHERE calendarviewid=?', array ($viewId));
			} else {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_calendarviews v WHERE label=?', array ($view->getLabel ()));
			}
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$viewId   = intval ($row ['calendarviewid']);
				$isLocked = $row ['locked'] == 1;
			} else {
				$viewId   = null;
				$isLocked = false;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if (empty ($viewId)) {
				$this->createView ($view);
			} else if (($ignoreLock) || (!$isLocked)) {
				$view->setId ($viewId);
				$this->updateView ($view);
			}

			return $view;
		}

		/**
		 * @param string $moduleName
		 * @param CalendarView[]|null $views
		 * @param boolean $ignoreLock
		 */
		public function saveViews ($moduleName, $views, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			} else if (empty ($views)) {
				$this->deleteViews ($moduleName, $ignoreLock);
				return;
			}

			$processedViewIds = array ();
			foreach ($views as $view) {
				$viewModuleName = $view->getModuleName ();
				if (empty ($viewModuleName)) {
					$view->setModuleName ($moduleName);
				}
				$this->saveView ($view, $ignoreLock);
				$processedViewIds [] = $view->getId ();
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}

			$questionMarks = str_repeat ('?, ', (count ($processedViewIds) - 1)) . '?';
			$this->adb->pquery (
				"DELETE FROM vtiger_calendarviews_applications WHERE calendarviewid IN (SELECT calendarviewid FROM vtiger_calendarviews WHERE modulename=? AND calendarviewid NOT IN ({$questionMarks}) {$whereClause})",
				array_merge (array ($moduleName), $processedViewIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_calendarviews_rules WHERE calendarviewid IN (SELECT calendarviewid FROM vtiger_calendarviews WHERE modulename=? AND calendarviewid NOT IN ({$questionMarks}) {$whereClause})",
				array_merge (array ($moduleName), $processedViewIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_calendarviews WHERE modulename=? AND calendarviewid NOT IN ({$questionMarks}) {$whereClause}",
				array_merge (array ($moduleName), $processedViewIds)
			);
		}

		/**
		 * @param CalendarView $view
		 *
		 * @throws CalendarViewException
		 */
		public function validateView ($view) {
			$this->validate ($view);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return CalendarViewManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
