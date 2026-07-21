<?php
	require_once ('include/platzilla/Objects/ApplicationInterface.php');
	require_once ('include/platzilla/Objects/ApplicationSubscriptionInterface.php');
	require_once ('include/platzilla/Managers/InvoiceManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	abstract class HomeUtils {

		private static function getUserIdsByRoleWhereClause (PearDatabase $adb, $currentUser) {
			$userIds = array ();
			$result  = $adb->pquery (
				'SELECT
					u2r.userid
				FROM
					vtiger_user2role u2r
					LEFT JOIN vtiger_role r ON r.roleid=u2r.roleid
				WHERE
					r.parentrole LIKE ?',
				array ("{$currentUser->roleid}%")
			);
			if ($adb->num_rows ($result) == 0) {
				return " AND a.userid='{$currentUser->id}'";
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				if ((is_admin ($currentUser)) || ($row ['userid'] != 1)) {
					$userIds [] = $row ['userid'];
				}
			}
			if (!empty ($userIds)) {
				$dummy = join ("','", $userIds);
				return " AND a.userid IN ('{$dummy}')";
			} else {
				return " AND a.userid='{$currentUser->id}'";
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $codeInstance
		 * @param boolean $isInstance
		 * @param string $week
		 *
		 * @return string|null
		 */
		public static function fetchAvailableWeeklyReport ($adb, $codeInstance, $isInstance, $week) {
			if (empty($codeInstance)) {
				return null;
			}
			if ($isInstance) {
				$where = "master_status = 'ACTIVE'";
			} else {
				$where = 1;
			}
			$result   = $adb->pquery (
				"SELECT
       				date_start,
       				due_date
				FROM vtiger_master_summary_report
				WHERE {$where} AND
					instance_code=?
				ORDER BY masterreportid DESC",
				array ($codeInstance)
			);
			if ($adb->num_rows ($result) > 0) {
				$htmlOutput   = '';
				$dayOfWeekEn  = array ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
				$dayOfWeekEs  = array ('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$formDateDisplay = date ('l - Y-m-d', strtotime ($row['date_start']));
					$toDateDisplay   = date ('l - Y-m-d',  strtotime ($row['due_date']));
					$daysDisplay     =  str_replace ($dayOfWeekEn, $dayOfWeekEs, $formDateDisplay . ' - ' . $toDateDisplay);
					$daysValue       = $row['date_start'] . '@' . $row['due_date'];
					$selectedDays    = ($week == $daysValue) ? 'selected="selected"' : '';
					$htmlOutput     .= "<option value=". $daysValue ." $selectedDays>".$daysDisplay."</option>";
				}
			}
			return (isset ($htmlOutput)) ? $htmlOutput : null;
		}
		
		/**
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableCountries () {
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$result    = $masterAdb->query ('SELECT * FROM vtiger_pais ORDER BY pais');
			if ($masterAdb->num_rows ($result) > 0) {
				$countries = array ();
				while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
					$countries [] = $row;
				}
			} else {
				$countries = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				unset ($result);
			}
			return $countries;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $platInstance
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAppCatalog ($adb, $platInstance) {
			if (!empty ($platInstance)) {
				$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
				$instanceDatabaseName = "pg_crm_{$platInstance}";
				$result               = $masterAdb->pquery (
					"SELECT
				ica.config_applicationsid,
				ica.app_code,
				ica.app_name
			FROM
				vtiger_instanceapplications ia
				INNER JOIN vtiger_instances i ON i.code=ia.instancecode
				INNER JOIN vtiger_config_applications mca ON mca.app_code=ia.applicationcode
				INNER JOIN {$instanceDatabaseName}.vtiger_config_applications ica ON ica.app_code=mca.app_code AND ica.app_status='Activa'
			WHERE
				ia.status IN (?, ?) AND
				i.code=?",
					array (ApplicationSubscriptionInterface::STATUS_ACTIVE, ApplicationSubscriptionInterface::STATUS_SUBSCRIBED, $platInstance)
				);
			} else {
				$result = $adb->pquery ('SELECT config_applicationsid, app_code, app_name FROM vtiger_config_applications WHERE app_status=?', array (ApplicationInterface::STATUS_ACTIVE));
			}
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$applications     = array ();
				$applicationCodes = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['modules'] = ReportUtils::getApplicationModules ($adb, $row ['config_applicationsid']);
					$applications []     = $row;
					$applicationCodes [] = $row ['app_code'];
				}
			}
			return (isset($applications)) ? array ('applications' => $applications, 'applicationCodes' => $applicationCodes) : null;
		}
		
		/**
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableCurrencies () {
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$result    = $masterAdb->query ('SELECT * FROM vtiger_currencies ORDER BY currency_name');
			if ($masterAdb->num_rows ($result) > 0) {
				$currencies = array ();
				while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
					$currencies [] = $row;
				}
			} else {
				$currencies = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				unset ($result);
			}
			return $currencies;
		}

		public static function getAvailableDefaultModules (PearDatabase $adb) {
			$availableModuleNames = array ('Calendar', 'Documents', 'formacion_cursos', 'Home');
			$questionMarks        = str_repeat ('?, ', (count ($availableModuleNames) - 1)) . '?';
			$result               = $adb->pquery ("SELECT * FROM vtiger_tab WHERE presence=0 AND name IN ({$questionMarks})", $availableModuleNames);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ('Home' => getTranslatedString ('Home', 'Home'));
			}

			$defaultModuleNames = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$defaultModuleNames [ $row ['name'] ] = getTranslatedString ($row ['name'], $row ['name']);
			}
			$defaultModuleNames ['Tasks'] = 'Tareas';
			return $defaultModuleNames;
		}

		public static function getCustomerId ($instanceCode) {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery ('SELECT * FROM vtiger_instances i WHERE i.code=?', array ($instanceCode));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return intval ($row ['accountid']);
		}

		public static function getCustomer ($instanceCode) {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery (
				'SELECT
					c.*,
					i.name AS companyname
				FROM
					vtiger_clientes c
					INNER JOIN vtiger_instances i ON i.accountid=c.clientesid
				WHERE
					i.code=?',
				array ($instanceCode)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			return $adb->fetchByAssoc ($result, -1, false);
		}

		/* DESACTIVADO EL HELPCRUNCH EL 21/11/2021 - AV */
		/*public static function getHelpCrunchCustomerData ($instanceCode) {
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$result    = $masterAdb->pquery (
				'SELECT
					i.accountid,
					i.registrationdate,
					i.servicestartdate,
					ibp.planname
				FROM
					vtiger_instances i
					INNER JOIN vtiger_instancebillingplans ibp ON ibp.planid=i.billingplanid
				WHERE
					i.code=?',
				array ($instanceCode)
			);
			if ($masterAdb->num_rows ($result) > 0) {
				$data = $masterAdb->fetchByAssoc ($result, -1, false);
			} else {
				$data = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				unset ($result);
			}
			return $data;
		}*/
		/* DESACTIVADO EL HELPCRUNCH EL 21/11/2021 - AV */

		/**
		 * @param string $instanceCode
		 * @param integer $invoiceId
		 *
		 * @return Invoice|null
		 */
		public static function getInvoice ($instanceCode, $invoiceId) {
			if ((empty ($instanceCode)) || (empty ($invoiceId))) {
				return null;
			}

			$invoice = InvoiceManager::getInstance ()->fetchInvoice ($invoiceId, $instanceCode);
			if (empty ($invoice)) {
				return null;
			}

			$customerId = self::getCustomerId ($instanceCode);
			if ((empty ($customerId)) || ($customerId != $invoice->getAccountId ())) {
				return null;
			}

			return $invoice;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param boolean $isInstance
		 *
		 * @return array|null
		 */
		public static function getLastWeeklyReport ($adb, $isInstance) {
			if ($isInstance) {
				$where = "master_status = 'ACTIVE'";
			} else {
				$where = 1;
			}
			$result   = $adb->query ("SELECT * FROM vtiger_master_summary_report WHERE {$where} ORDER BY masterreportid DESC LIMIT 1");
			if ($adb->num_rows ($result) > 0) {
				return $adb->fetchByAssoc ($result, -1, false);
			}
			return null;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function getOrganizationCurrency (PearDatabase $adb) {
			$result = $adb->query ('SELECT * FROM vtiger_currency_info LIMIT 1');
			if ($adb->num_rows ($result) > 0) {
				$currency = $adb->fetchByAssoc ($result, -1, false);
			} else {
				$currency = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $currency;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $platform
		 *
		 * @return array|null
		 */
		public static function getOrganizationDetails (PearDatabase $adb, $platform) {
			$result = $adb->query ('SELECT * FROM vtiger_organizationdetails LIMIT 1');
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				if ((!empty ($row ['logoname'])) && (file_exists ("{$platform}/{$row ['logoname']}")) && (is_file ("{$platform}/{$row ['logoname']}"))) {
					$row ['organization_logopath'] = $platform;
				} else {
					$row ['organization_logopath'] = 'test/logo';
					$row ['logoname']              = 'platzi.png';
				}
				$details = $row;
			} else {
				$details = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $details;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param $currentUser
		 * @param $startDateTime
		 * @param $endDateTime
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getAllActivity (PearDatabase $adb, $currentUser, $startDateTime, $endDateTime) {
			$sqlUser     = self::getUserIdsByRoleWhereClause ($adb, $currentUser);
			$queryResult = $adb->query (
				"SELECT
					a.module,
					a.action,
					a.recordid,
					MAX(a.actiondate) AS action_date,
					t.tablabel,
					en.tablename,
					en.fieldname,
					en.entityidfield,
					usr.id,
					usr.last_name
				FROM
					vtiger_audit_trial a
					LEFT JOIN vtiger_tab t ON t.name=a.module
					LEFT JOIN vtiger_entityname en ON en.modulename=a.module
					LEFT JOIN vtiger_users AS usr ON a.userid=usr.id
				WHERE
					t.name NOT IN ('Tooltip', 'Home') AND
					a.action IN ('DetailView', 'Save', 'Delete', 'CalendarAjax', 'EditGraph', 'EditView') AND
					CAST(actiondate AS DATETIME)>='{$startDateTime}' AND
					CAST(actiondate as DATETIME)<='{$endDateTime}'
					{$sqlUser}
				GROUP BY
					action,
					recordid
				ORDER BY
					actiondate DESC"
			);
			$history     = array ();
			$noofrows    = $adb->num_rows ($queryResult);
			if ($noofrows > 0) {
				$row = $adb->fetchByAssoc ($queryResult);
				while ($row) {
					$userImagen = getUserImageName ($row['id']);
					if ($userImagen) {
						$row['imagename'] = $userImagen;
					} else {
						$row['imagename'] = '';
					}
					$campo = str_replace (',', ",' ',", $row['fieldname']);
					if (($campo != '') && (isset($row['tablename'])) && (isset($row['entityidfield'])) && (isset($row['recordid'])) && ($row['recordid'] != '')) {
						$entity      = "SELECT CONCAT({$campo}) c FROM {$row['tablename']} WHERE {$row['entityidfield']}={$row['recordid']} LIMIT 1";
						$queryEntity = $adb->query ($entity);
						if ($adb->num_rows ($queryEntity) > 0) {
							$row['label_entity'] = $adb->query_result ($queryEntity, 0, 'c');
							if ($row['action'] == 'DetailView') {
								$row['tipo'] = 'Nuevo';
							} else if ($row['action'] == 'Delete') {
								$row['tipo'] = 'Eliminado';
							} else {
								$row['tipo'] = 'Modificado';
							}
						} else {
							$row['label_entity'] = '';
							$row['tipo']         = 'Modificado';
						}
					} else {
						$row['label_entity'] = '';
						$row['tipo']         = 'Modificado';
					}
					$fileToChck = "./modules/{$row ['module']}/ListView.php";
					if (file_exists ($fileToChck)) {
						$row['url_module'] = "index.php?action=ListView&module={$row ['module']}&parenttab=";
					} else {
						$row['url_module'] = "index.php?module={$row ['module']}&action=index";
					}
					$history[] = $row;
					$row       = $adb->fetchByAssoc ($queryResult);
				}
			}
			return $history;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @throws Exception
		 * @return null|array
		 */
		public static function getCustomViewOnDesk ($adb) {
			$result = $adb->query ('SELECT cvid, entitytype FROM vtiger_customview WHERE deskview=1 GROUP BY entitytype');
			if ($adb->num_rows ($result) > 0) {
				$customView = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (count ($customView)) {
						$customView = array_merge ($customView, array ($row['entitytype'] => $row['cvid']));
					} else {
						$customView = array ($row['entitytype'] => $row['cvid']);
					}
				}
			} else {
				$customView = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $customView;
		}
		
	}
