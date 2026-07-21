<?php
	require_once ('include/platzilla/Objects/ReportTemplate.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ReportTemplateManager {
		/** @var ReportTemplateManager[] */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return ReportTemplate[]
		 */
		private function fetchDeletedTemplates ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$templates = array ();
			$result    = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('reporttemplate', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$template = ReportTemplate::getInstance ()
						->setDeleted (true);
					$template->unserialize ($row ['serializedobject']);
					$templates [] = $template;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $templates;
		}

		/**
		 * @param ReportTemplate $template
		 *
		 * @throws ReportTemplateException
		 */
		private function validate ($template) {
			if ((empty ($template)) || (!($template instanceof ReportTemplate))) {
				throw new ReportTemplateException (ReportTemplateException::ERROR_REPORT_TEMPLATE_EMPTY);
			}

			$template->validate ();

			$moduleName = $template->getModuleName ();
			$result     = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new ReportTemplateException (ReportTemplateException::ERROR_REPORT_TEMPLATE_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param ReportTemplate $template
		 */
		public function deleteTemplate ($template) {
			if ((empty ($template)) || (!($template instanceof ReportTemplate))) {
				return;
			}

			$templateId = $template->getId ();
			if (empty ($templateId)) {
				return;
			}
			$moduleName = $template->getModuleName ();
			$identifier = $templateId;
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('reporttemplate', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('button', $moduleName, $identifier, date ('Y-m-d h:i:s'), $template->serialize ()));
			}
			$this->adb->pquery ('DELETE r2m FROM vtiger_report2module r2m INNER JOIN vtiger_tab t ON t.tabid=r2m.tabid AND t.name=? WHERE r2m.code_template=?', array ($moduleName, $templateId));
			$this->adb->pquery ('DELETE FROM vtiger_report_template WHERE id=?', array ($templateId));
		}

		/**
		 * @param string $moduleName
		 */
		public function deleteTemplates ($moduleName) {
			if (empty ($moduleName)) {
				return;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_report2module r2m INNER JOIN vtiger_tab t ON t.tabid=r2m.tabid AND t.name=?', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$templateCodes = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$templateCodes [] = $row ['code_template'];
				}
			} else {
				$templateCodes = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if (empty ($templateCodes)) {
				return;
			}

			$questionMarks = str_repeat ('?, ', (count ($templateCodes) - 1)) . '?';
			$this->adb->pquery ('DELETE r2m FROM vtiger_report2module r2m INNER JOIN vtiger_tab t ON t.tabid=r2m.tabid AND t.name=?', array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_report_template WHERE code IN ({$questionMarks})", $templateCodes);
		}

		/**
		 * @param integer $templateId
		 *
		 * @return null|ReportTemplate
		 */
		public function fetchTemplate ($templateId) {
			if (empty ($templateId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					rt.id,
					rt.code,
					rt.has_inventory,
					rt.name,
					r2m.active,
					t.name AS modulename
				FROM
					vtiger_report_template rt
					INNER JOIN vtiger_report2module r2m ON r2m.code_template=rt.code
					INNER JOIN vtiger_tab t ON t.tabid=r2m.tabid
				WHERE
					rt.id=?',
				array ($templateId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$template = ReportTemplate::getInstance ()
					->setId (intval ($row ['id']))
					->setActive ($row ['active'] == 1)
					->setCode ($row ['code'])
					->setHasInventory ($row ['has_inventory'] == 1)
					->setModuleName ($row ['modulename'])
					->setName ($row ['name']);
			} else {
				$template = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $template;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $includeDeleted
		 *
		 * @return ReportTemplate[]|null
		 */
		public function fetchTemplates ($moduleName, $includeDeleted = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$templates = array ();
			$result    = $this->adb->pquery (
				'SELECT
					rt.id,
					rt.code,
					rt.has_inventory,
					rt.name,
					r2m.active,
					t.name AS modulename
				FROM
					vtiger_report_template rt
					INNER JOIN vtiger_report2module r2m ON r2m.code_template=rt.code
					INNER JOIN vtiger_tab t ON t.tabid=r2m.tabid AND t.name=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$templates [] = ReportTemplate::getInstance ()
						->setId (intval ($row ['id']))
						->setActive ($row ['active'] == 1)
						->setCode ($row ['code'])
						->setHasInventory ($row ['has_inventory'] == 1)
						->setModuleName ($row ['modulename'])
						->setName ($row ['name']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$deletedTemplates = $includeDeleted ? $this->fetchDeletedTemplates ($moduleName) : array ();
			$templates        = array_merge ($templates, $deletedTemplates);
			return count ($templates) > 0 ? $templates : null;
		}

		/**
		 * @param ReportTemplate $template
		 *
		 * @return ReportTemplate
		 * @throws ReportTemplateException
		 */
		public function saveTemplate ($template) {
			$this->validate ($template);

			$isDeleted = $template->isDeleted ();
			if ($isDeleted) {
				return $template;
			}

			$templateId = $template->getId ();
			if (empty ($templateId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_report_template (has_inventory, code, name) VALUES (?, ?, ?)',
					array ($template->hasInventory (), $template->getCode (), $template->getName ())
				);
				$template->setId ($this->adb->getLastInsertID ());
				$this->adb->pquery (
					'INSERT INTO vtiger_report2module (tabid, code_template, active) SELECT t.tabid, ?, ? FROM vtiger_tab t WHERE t.name=?',
					array ($template->getCode (), $template->isActive (), $template->getModuleName ())
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_report_template SET has_inventory=?, code=?, name=? WHERE id=?',
					array ($template->hasInventory (), $template->getCode (), $template->getName (), $templateId)
				);
				$this->adb->pquery (
					'UPDATE vtiger_report2module SET active=? WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?) AND code_template=?',
					array ($template->isActive (), $template->getModuleName (), $template->getCode ())
				);
			}

			return $template;
		}

		/**
		 * @param string $moduleName
		 * @param ReportTemplate[] $templates
		 */
		public function saveTemplates ($moduleName, $templates) {
			if (empty ($moduleName)) {
				return;
			} else if (empty ($templates)) {
				$this->deleteTemplates ($moduleName);
				return;
			}

			$processedTemplateCodes = array ();
			foreach ($templates as $template) {
				$template->setModuleName ($moduleName);
				$this->saveTemplate ($template);
				$processedTemplateCodes [] = $template->getCode ();
			}

			if (empty ($processedTemplateCodes)) {
				return;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedTemplateCodes) - 1)) . '?';
			$result        = $this->adb->pquery (
				"SELECT r2m.* FROM vtiger_report2module r2m INNER JOIN vtiger_tab t ON t.tabid=r2m.tabid AND t.name=? WHERE r2m.code_template NOT IN ({$questionMarks})",
				array_merge (array ($moduleName), $processedTemplateCodes)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$unprocessedTemplateCodes = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$unprocessedTemplateCodes [] = $row ['code_template'];
				}
				$questionMarks = str_repeat ('?, ', (count ($unprocessedTemplateCodes) - 1)) . '?';
				$this->adb->pquery (
					"DELETE r2m FROM vtiger_report2module r2m INNER JOIN vtiger_tab t ON t.tabid=r2m.tabid AND t.name=? WHERE r2m.code_template IN ({$questionMarks})",
					array_merge (array ($moduleName), $unprocessedTemplateCodes)
				);
				$this->adb->pquery (
					"DELETE FROM vtiger_report_template WHERE code IN ({$questionMarks})",
					$unprocessedTemplateCodes
				);
			}
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ReportTemplateManager
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
