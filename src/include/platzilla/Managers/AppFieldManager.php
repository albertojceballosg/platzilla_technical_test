<?php
	require_once ('include/platzilla/Objects/AppField.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	
	class AppFieldManager {
		
		/** @var AppFieldManager[]|null */
		private static $INSTANCES = null;
		
		/** @var PearDatabase */
		private $adb;
		
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}
		
		/**
		 * @param $appFieldName
		 *
		 * @return AppField|null
		 * @throws Exception
		 */
		public function fetchAppFieldByName ($appFieldName, $moduleName) {
			if (empty($appFieldName)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_application_fields WHERE applicationname=? AND tabname=? AND satuts=?', array ($appFieldName, $moduleName, 'ENABLED'));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$fieldApp = AppField::getInstance ()
					->setApplicationName ($row ['applicationname'])
					->setHandlerClass ($row ['handlerclass'])
					->setHandlerMethod ($row ['handlermethod'])
					->setId ($row ['applicationfieldsid'])
					->setTabName ($row ['tabname'])
					->setStatus ($row ['satuts']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($fieldApp)) ? $fieldApp : null;
		}
		
		/**
		 * @param Field $field
		 *
		 * @throws AppFieldException
		 */
		public function saveAppField ($field) {
			if ($field->getUiType () != Field::UI_TYPE_APP) {
				$field->setAppField (null);
			}
			$result = $this->adb->pquery (
				'SELECT * FROM vtiger_application_fields f INNER JOIN vtiger_tab t ON t.name =f.tabname AND t.name=? WHERE f.applicationname=?',
				array ($field->getModuleName (), $field->getName ())
			);
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				DatabaseUtils::closeResult ($result);
				throw new AppFieldException (AppFieldException::ERROR_APPLICATION_FIELD_DUPLICATE);
			}
			$handlerData = $field->getAppField ();
			if (empty ($handlerData['class'])) {
				throw new AppFieldException (AppFieldException::ERROR_APPLICATION_FIELD_HANDLE_CLASS);
			}
			if (empty($handlerData['method'])) {
				throw new AppFieldException (AppFieldException::ERROR_APPLICATION_FIELD_HANDLE_METHOD);
			}
			$this->adb->pquery (
				'INSERT INTO vtiger_application_fields (applicationname, tabname, handlerclass, handlermethod) VALUES (?, ?, ?, ?)',
				array ($field->getName (), $field->getModuleName (), $handlerData['class'], $handlerData['method'])
			);
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return AppFieldManager
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
