<?php
	require_once ('include/platzilla/Managers/DataSharingManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/Translator.class.php');

	abstract class DataSharingUtils {

		/**
		 * @param PearDatabase $adb
		 * @param integer $contactId
		 *
		 * @return null|string
		 */
		private static function fetchContactEmail (PearDatabase $adb, $contactId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_contactos WHERE contactosid=?', array ($contactId));
			if ($adb->num_rows ($result) > 0) {
				$row            = $adb->fetchByAssoc ($result, -1, false);
				$contactAddress = $row ['email'];
			} else {
				$contactAddress = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $contactAddress;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $customerId
		 *
		 * @return null|string
		 */
		private static function fetchCustomerEmail (PearDatabase $adb, $customerId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_clientes WHERE clientesid=?', array ($customerId));
			if ($adb->num_rows ($result) > 0) {
				$row           = $adb->fetchByAssoc ($result, -1, false);
				$customerEmail = $row ['e_mail'];
			} else {
				$customerEmail = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $customerEmail;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param string $moduleName
		 * @param integer $recordId
		 */
		public static function deleteSync (PearDatabase $adb, $instanceCode, $moduleName, $recordId) {
			DataSharingManager::getInstance ($adb)->deleteSync ($instanceCode, $moduleName, $recordId);
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Application[]|null
		 */
		public static function fetchAvailableApplicationsByModuleName ($moduleName) {
			$adb          = AdbManager::getInstance ()->getMasterAdb ();
			$applications = ApplicationManager::getInstance ($adb)->fetchApplicationHeadersByModuleName ($moduleName);
			if (empty ($applications)) {
				return null;
			}

			usort (
				$applications,
				function (Application $applicationA, Application $applicationB) {
					return strcmp ($applicationA->getName (), $applicationB->getName ());
				}
			);
			return $applications;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function fetchAvailableEntityModules (PearDatabase $adb) {
			$modules = ModuleManager::getInstance ($adb)->fetchModules (true);
			if (empty ($modules)) {
				return null;
			}

			$availableEntityModules = array ();
			foreach ($modules as $module) {
				if (
					($module->getIsEntityType ()) &&
					(in_array ($module->getPresence (), array (Module::PRESENCE_VISIBLE, Module::PRESENCE_USER_DEFINED))) &&
					(in_array ($module->getType (), array (Module::TYPE_ADMIN, Module::TYPE_USER)))
				) {
					$module->setLabel (Translator::translate ($module->getLabel (), $module->getName ()));
					$availableEntityModules [] = $module;
				}
			}
			usort (
				$availableEntityModules,
				function (Module $moduleA, Module $moduleB) {
					return strcmp ($moduleA->getLabel (), $moduleB->getLabel ());
				}
			);
			return !empty ($availableEntityModules) ? $availableEntityModules : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		public static function fetchAvailableFieldsData (PearDatabase $adb, $moduleName) {
			$fields = FieldManager::getInstance ($adb)->fetchFields ($moduleName);
			if (empty ($fields)) {
				return null;
			}

			$fieldsData = array ();
			foreach ($fields as $field) {
				$uiType    = $field->getUiType ();
				$fieldName = $field->getName ();
				if (in_array ($uiType, array (Field::UI_TYPE_GLOBAL_PICKLIST))) {
					$options = GlobalPicklistManager::getInstance ($adb)->fetchPicklistRawValues ($fieldName);
				} else if (in_array ($uiType, array (Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST))) {
					$options = PicklistManager::getInstance ($adb)->fetchPicklistRawValues ($fieldName);
				} else {
					$options = null;
				}
				$fieldsData [ $fieldName ] = array (
					'id'        => $field->getId (),
					'label'     => Translator::translate ($field->getLabel (), $moduleName),
					'mandatory' => $field->isMandatory (),
					'name'      => $field->getName (),
					'options'   => $options,
					'uitype'    => $uiType,
				);
			}
			usort (
				$fieldsData,
				function ($fieldA, $fieldB) {
					return strcmp ($fieldA ['label'], $fieldB ['label']);
				}
			);
			return $fieldsData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		public static function fetchAvailablePicklistValues (PearDatabase $adb, $moduleName) {
			if (empty ($moduleName)) {
				return null;
			}

			$picklistValues = array ();
			$fm             = FieldManager::getInstance ($adb);

			$fields = $fm->fetchFieldsByUiType ($moduleName, Field::UI_TYPE_GLOBAL_PICKLIST);
			if (!empty ($fields)) {
				$gpm = GlobalPicklistManager::getInstance ($adb);
				foreach ($fields as $field) {
					$picklistValues [ $field->getName () ] = $gpm->fetchPicklistRawValues ($field->getName ());
				}
			}

			$pm     = PicklistManager::getInstance ($adb);
			$fields = $fm->fetchFieldsByUiType ($moduleName, Field::UI_TYPE_MULTI_SELECT);
			if (!empty ($fields)) {
				foreach ($fields as $field) {
					$picklistValues [ $field->getName () ] = $pm->fetchPicklistRawValues ($field->getName ());
				}
			}

			$fields = $fm->fetchFieldsByUiType ($moduleName, Field::UI_TYPE_PICKLIST);
			if (!empty ($fields)) {
				foreach ($fields as $field) {
					$picklistValues [ $field->getName () ] = $pm->fetchPicklistRawValues ($field->getName ());
				}
			}

			return !empty ($picklistValues) ? $picklistValues : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		public static function fetchAvailableRules (PearDatabase $adb, $moduleName) {
			$rules = DataSharingManager::getInstance ($adb)->fetchRules ($moduleName);
			if (!empty ($rules)) {
				$availableRules = array ();
				foreach ($rules as $rule) {
					$availableRules [] = array ('id' => $rule->getId (), 'label' => $rule->getName ());
				}
			} else {
				$availableRules = null;
			}
			return $availableRules;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return null|User[]
		 */
		public static function fetchAvailableUsers (PearDatabase $adb) {
			return UserManager::getInstance ($adb, null)->fetchUsers ();
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $token
		 * @param string $sourceInstanceCode
		 *
		 * @return DataSharingRequest|null
		 */
		public static function fetchRequestByToken (PearDatabase $adb, $token, $sourceInstanceCode = null) {
			return DataSharingManager::getInstance ($adb)->fetchRequestByToken ($token, $sourceInstanceCode);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $ruleId
		 *
		 * @return DataSharingRule|null
		 */
		public static function fetchRule (PearDatabase $adb, $ruleId) {
			return DataSharingManager::getInstance ($adb)->fetchRuleById ($ruleId);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $keyword
		 * @param integer $page
		 * @param integer $rowsPerPage
		 *
		 * @return DataSharingRule[]
		 */
		public static function fetchRules (PearDatabase $adb, $keyword, $page, $rowsPerPage) {
			return DataSharingManager::getInstance ($adb)->searchRules ($keyword, $page, $rowsPerPage);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $syncId
		 *
		 * @return DataSharingSync|null
		 */
		public static function fetchSync (PearDatabase $adb, $syncId) {
			return DataSharingManager::getInstance ($adb)->fetchSync ($syncId);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param string $moduleName
		 * @param integer $recordId
		 *
		 * @return DataSharingSync[]|null
		 */
		public static function fetchSyncs (PearDatabase $adb, $instanceCode, $moduleName, $recordId = null) {
			return DataSharingManager::getInstance ($adb)->fetchSyncs ($instanceCode, $moduleName, $recordId);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param string $moduleName
		 * @param integer $recordId
		 *
		 * @return integer
		 */
		public static function fetchTotalSyncs (PearDatabase $adb, $instanceCode, $moduleName, $recordId = null) {
			return DataSharingManager::getInstance ($adb)->fetchTotalSyncs ($instanceCode, $moduleName, $recordId);
		}

		/**
		 * @param string $token
		 *
		 * @return string|null
		 */
		public static function fetchSourceInstanceCodeByToken ($token) {
			return DataSharingManager::fetchSourceInstanceCodeByToken ($token);
		}

		/**
		 * @param string $username
		 *
		 * @return null|string
		 */
		public static function fetchUserInstanceCode ($username) {
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$instance  = PlatformManager::getInstance ($masterAdb, null)->fetchInstanceByUserName ($username, true);
			return !empty ($instance) ? $instance->getCode () : null;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return boolean
		 */
		public static function hasAvailableContacts (PearDatabase $adb) {
			$module = ModuleManager::getInstance ($adb)->fetchModule ('contactos', true);
			if ((empty ($module)) || (!in_array ($module->getPresence (), array (Module::PRESENCE_VISIBLE, Module::PRESENCE_USER_DEFINED)))) {
				return false;
			}

			$result      = $adb->query (
				'SELECT
					c.*
				FROM
					vtiger_contactos c
					INNER JOIN vtiger_crmentity crme ON crme.crmid=c.contactosid AND crme.deleted=0
				LIMIT 1'
			);
			$hasContacts = ($adb->num_rows ($result) > 0);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $hasContacts;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return boolean
		 */
		public static function hasAvailableCustomers (PearDatabase $adb) {
			$module = ModuleManager::getInstance ($adb)->fetchModule ('clientes', true);
			if ((empty ($module)) || (!in_array ($module->getPresence (), array (Module::PRESENCE_VISIBLE, Module::PRESENCE_USER_DEFINED)))) {
				return false;
			}

			$result       = $adb->query (
				'SELECT
					c.*
				FROM
					vtiger_clientes c
					INNER JOIN vtiger_crmentity crme ON crme.crmid=c.clientesid AND crme.deleted=0
				LIMIT 1'
			);
			$hasCustomers = ($adb->num_rows ($result) > 0);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $hasCustomers;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return boolean
		 */
		public static function isModuleActive (PearDatabase $adb, $moduleName) {
			$module = ModuleManager::getInstance ($adb)->fetchModule ($moduleName, true);
			return (!empty ($module)) && (in_array ($module->getPresence (), array (Module::PRESENCE_USER_DEFINED, Module::PRESENCE_VISIBLE)));
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 *
		 * @throws DataSharingRequestException
		 * @throws Exception
		 */
		public static function sendRequest (PearDatabase $adb, $arguments) {
			if ((!is_array ($arguments)) || (empty ($arguments))) {
				throw new Exception ('No has suministrado los argumentos');
			} else if (empty ($arguments ['recipients'])) {
				throw new Exception ('No has suministrado los destinatarios');
			}

			$comments           = $arguments ['comments'];
			$moduleName         = $arguments ['modulename'];
			$recipients         = $arguments ['recipients'];
			$recipientType      = $arguments ['recipienttype'];
			$recordIds          = $arguments ['recordids'];
			$ruleId             = $arguments ['ruleid'];
			$sourceInstanceCode = $arguments ['sourceinstancecode'];
			$userId             = $arguments ['userid'];

			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$pm       = PlatformManager::getInstance ($masterAdb, null);
			$dsm      = DataSharingManager::getInstance ($adb);
			$requests = array ();
			foreach ($recipients as $recipient) {
				if ($recipientType == DataSharingRequest::RECIPIENT_TYPE_CONTACT) {
					$recipientAddress = self::fetchContactEmail ($adb, $recipient);
				} else if ($recipientType == DataSharingRequest::RECIPIENT_TYPE_CUSTOMER) {
					$recipientAddress = self::fetchCustomerEmail ($adb, $recipient);
				} else {
					$recipientAddress = trim ($recipient);
				}

				$targetInstance = $pm->fetchInstanceByUserName ($recipientAddress, true);
				if (!empty ($targetInstance)) {
					$targetInstanceCode = $targetInstance->getCode ();
				} else {
					$targetInstanceCode = null;
				}

				$request = DataSharingRequest::getInstance ()
					->setComments ($comments)
					->setCreatedBy (UserManager::getInstance ($adb, null)->fetchUserById ($userId))
					->setCreationDate (date_create ())
					->setModuleName ($moduleName)
					->setRecipientAddress ($recipientAddress)
					->setRecordIds ($recordIds)
					->setRuleId ($ruleId)
					->setSourceInstanceCode ($sourceInstanceCode)
					->setStatus (DataSharingRequest::STATUS_SENT)
					->setTargetInstanceCode ($targetInstanceCode);
				$dsm->validateRequest ($request);
				$requests [] = $request;
			}

			foreach ($requests as $request) {
				$dsm->sendRequest ($request);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param string $moduleName
		 * @param integer $recordId
		 */
		public static function synchronize (PearDatabase $adb, $instanceCode, $moduleName, $recordId) {
			DataSharingManager::getInstance ($adb)->synchronize ($instanceCode, $moduleName, $recordId);
		}

	}
