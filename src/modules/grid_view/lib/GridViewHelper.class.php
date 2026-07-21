<?php
	require_once ('include/platzilla/Data/ActivityReportManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('modules/grid_view/Objects/BoxTaskContent.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('include/ListView/RelatedListViewSession.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('include/utils/EntityCommentsUtils.class.php');

	/**
	 * Class GridViewHelper
	 *
	 * Clase abstracta donde se encuentra implementadas las utilidades que brindan soporte a las Vistas Cuadriculas
	 */
	abstract class GridViewHelper {

		/** @var array  */
		const INCLUDE_MODULE_NAMES = array (
			'almacenes',
			'articulos',
			'clientes',
			'contactos',
			'contratos_de_servicio',
			'equipamientos',
			'etapas_proyecto',
			'facturas',
			'gastos',
			'incidencias',
			'impuestos',
			'oportunidades',
			'orden_de_trabajo',
			'orden_de_venta',
			'pedidos',
			'presupuestos_cotizacion',
			'plan_de_mantenimiento',
			'potenciales_clientes',
			'proyectos',
			'proveedores',
			'campaigns',
			'campaign_marketing',
			'process_steps',
			'process',
			'nonconformities',
			'corrective_actions',
		);
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return boolean
		 */
		private static function checkForAvailableModule ($adb, $moduleName) {
			if (empty ($moduleName)) {
				return false;
			}
			$isAvailable = false;
			$modules = self::fetchAvailableModules ($adb);
			foreach ($modules as $module) {
				if ($module->getName () == $moduleName) {
					$isAvailable = true;
					break;
				}
				
			}
			return $isAvailable;
		}

		/**
		 * @param string $currentModule
		 * @param integer $recordId
		 * @param array $relatedData
		 * @param string $header
		 *
		 * @return string
		 */
		private static function getRelatedListCustomButtons ($currentModule, $recordId, $relatedData, $header) {
			if (empty($relatedData)) {
				return '';
			}
			$actionOption = (!empty($relatedData ['actions'])) ? $relatedData ['actions'] : $relatedData ['fieldName'];
			$urlHeader = urlencode ($header);
			return "<a href='index.php?module=grid_view&action=GridViewAjaxUtils&record={$recordId}&formodule={$currentModule}&relatedlist={$urlHeader}@{$relatedData['related_tabid']}@{$relatedData ['relationId']}@{$actionOption}&function=RELATED_INFO&Ajax=true'
			class='btn btn-success btn-circle'
			data-title='{$header}' data-width='950'
			data-toggle='lightbox' data-parent=''
			data-gallery='remoteload' class='link'>
			<i class='fa fa-eye fa-lg'></i></a>&nbsp;";
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $tabId
		 * @param string $moduleName
		 * @param $relatedId
		 *
		 * @return null|string
		 */
		private static function getRelatedListFormattedHeader ($adb, $tabId, $moduleName, $relatedId) {
			$relatedListResult = $adb->pquery ('SELECT * FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=?', array ($tabId, $relatedId));
			if (empty($relatedListResult)) {
				return null;
			}
			$relatedListRow = $adb->fetch_row ($relatedListResult);
			$header         = $relatedListRow ['label'];

			$formattedHeader = str_replace (' ', '', $header);
			return 'tbl_' . $moduleName . '_' . $formattedHeader;
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param integer $crmid
		 *
		 * @return null|string
		 * @throws Exception
		 */
		private static function getSeactivityRel ($adb, $crmid) {
			$results = $adb->run_query_allrecords ("SELECT activityid FROM vtiger_seactivityrel WHERE crmid={$crmid}");
			if (!empty($results)) {
				foreach ($results as $result) {
					$activities [] = $result ['activityid'];
				}
			}
			return (isset($activities)) ? $adb->sql_expr_datalist ($activities) : null;
		}

		/**
		 * Para crear la vista cuadricula por defecto para los modulos
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return GridView
		 * @throws Exception
		 * @throws GridViewException
		 */
		private static function createDefaultGridView ($adb, $moduleName = null) {
			$boxes         = array ('MESSAGES', 'TASKS', 'GUEST_REVIEWS', 'DOCUMENTS', 'CALENDAR', 'REPORT_ACTIVITY');
			$gridViewBoxes = array ();

			foreach ($boxes as $index => $boxType) {
				$gridViewBoxes [] = GridViewBox::getInstance()
					->setGridViewName ('DEFAULT_VIEW')
					->setBoxType ($boxType)
					->setPresence (1)
					->setSequence ($index);
			}
			$gvm = GridViewManager::getInstance ($adb);
			$gridView = $gvm->saveGridView (
				GridView::getInstance()
					->setId (null)
					->setGridViewBox ($gridViewBoxes)
					->setLabel ('default view')
					->setTabName ('Home')
					->setGridViewName ('DEFAULT_VIEW')
					->setStatus ('ENABLED')
					->setLocked (0)
			);
			if (!empty($moduleName) && ($gridView instanceof GridView)) {
				$gridView = $gvm->getDefaultGridView ($moduleName);
			}
			return $gridView;
		}

		/**
		 * Para cambiar el estatus de los cuadrantes de la vista cuadricula
		 *
		 * @param PearDatabase $adb
		 * @param string $gridViewName
		 * @param string $status
		 */
		public static function changeStatusToGridView ($adb, $gridViewName, $status) {
			if (empty ($status) || empty($gridViewName)) {
				return;
			}
			$adb->pquery ('UPDATE vtiger_grid_view SET gridviewstatus=? WHERE gridviewname=?', array ($status, $gridViewName));
		}

		/**
		 * Para eliminar cuadrantes de la vista cuadricula
		 *
		 * @param PearDatabase $adb
		 * @param string $gridViewName
		 *
		 * @throws Exception
		 */
		public static function deleteGridView ($adb, $gridViewName) {
			if (empty($gridViewName)) {
				throw new Exception ('Vista cuadricula no identificada');
			}
			$gwm      = GridViewManager::getInstance ($adb);
			$gridView = $gwm->deleteGridView($gwm->fetchGridView ($gridViewName));
			if (empty ($gridView)) {
				throw new Exception ('Vista cuadricula no identificada');
			}
		}

		/**
		 * Busca todas las vistas cuadriculas
		 *
		 * @param PearDatabase $adb
		 *
		 * @return GridView[]|null
		 * @throws Exception
		 */
		public static function fetchAllGridView ($adb) {
			return GridViewManager::getInstance ($adb)->fetchGridViewAll(true);
		}

		/**
		 * Para buscar los modulos disponibles para vista cuadriculas
		 *
		 * @param PearDatabase $adb
		 *
		 * @return Module[]|null
		 */
		public static function fetchAvailableModules ($adb) {
			$modules = ModuleManager::getInstance ($adb)->fetchModulesByType(Module::TYPE_USER, true);
			if (!empty($modules)) {
				$availableModules = array ();
				foreach ($modules as $module) {
					if ($module->getPresence () !== 0) {
						continue;
					}
					$availableModules [] = $module;
				}
			}
			return (isset($availableModules)) ? $availableModules : null;
		}

		/**
		 * Para buscar los cuadrantes disponibles de la vista cuadricula
		 *
		 * @param PearDatabase $adb
		 *
		 * @return BoxContent[]|null
		 * @throws Exception
		 */
		public static function fetchAvailableBoxes ($adb) {
			return GridViewManager::getInstance ($adb)->fetchAvailableBoxContent ();
		}

		/**
		 * Busca la lista de los calendarios para mostrar en la vista cuadricula
		 *
		 * @param PearDatabase $adb
		 * @param integer $record
		 *
		 * @return BoxTaskContent[]|null
		 * @throws Exception
		 */
		public static function fetchCalendarList ($adb, $record, $user) {
			if (empty($record)) {
				return null;
			}
			$filters = array ('Call', 'Meeting');
			$format = '%A %d de %B - %Y, %H:%M:%S';
			return self::fetchTaskList($adb, $record, $filters, $user, $format);
		}

		/**
		 * Busca las actividades reportadas en la vista cuadriculas
		 *
		 * @param PearDatabase $adb
		 * @param integer $record
		 * @param User $user
		 * @param integer $activityId [Opcional] ID de la actividad para filtrar reportes específicos
		 *
		 * @return ActivityReport[]|null
		 * @throws Exception
		 */
		public static function fetchActivityReport ($adb, $record, $user, $activityId = null) {
			// Si se proporciona activityId, filtrar reportes de esa tarea específica
			if (!empty($activityId)) {
				$reports = ActivityReportManager::getInstance($adb)->fetchActivityReportByActivityId ($activityId);
				
				// Si no es admin, filtrar por usuario
				if (!is_admin($user) && !empty($reports)) {
					$filteredReports = array();
					foreach ($reports as $report) {
						if ($report->getUserId() == $user->id) {
							$filteredReports[] = $report;
						}
					}
					return $filteredReports;
				}
				return $reports;
			} else {
				// Comportamiento original: todos los reportes de la entidad
				if (is_admin ($user)) {
					return ActivityReportManager::getInstance($adb)->fetchActivityReport ($record);
				} else {
					return ActivityReportManager::getInstance ($adb)->fetchActivityReportByUser ($record, $user->id);
				}
			}
		}

		/**
		 * Para buscar la vista cuadricula por modulo
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param integer $record
		 * @param string $platformName
		 * @param User $user
		 *
		 * @return GridView|null
		 * @throws Exception
		 */
		public static function fetchGridViewByModule ($adb, $moduleName, $record, $platformName, $user) {
			$gridView = GridViewManager::getInstance ($adb)->fetchGridViewByModule ($moduleName);

			if (empty ($gridView) && self::checkForAvailableModule ($adb, $moduleName)) {
				$gridView = GridViewManager::getInstance ($adb)->getDefaultGridView ($moduleName);
				$gridView = (empty ($gridView)) ? self::createDefaultGridView ($adb, $moduleName) : $gridView;
			}

			if (!empty ($gridView)) {
				$mainArguments = get_defined_vars ();
				foreach ($gridView->getGridViewBox () as $gridViewBox) {
					$action = $gridViewBox->getBoxContenet()->getAction();
					if (!empty ($action)) {
						$arguments  = array ();
						$attributes = explode (',', $gridViewBox->getBoxContenet()->getAttributes());
						foreach ($attributes as $value) {
							$arguments [] = $mainArguments[ $value ];
						}
						$returnFunction = call_user_func_array ("{$action}", $arguments);
						$gridViewBox->getBoxContenet()->setContent ($returnFunction);
					}
				}
			}
			return $gridView;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $boxName
		 * @param string $moduleName
		 * @param integer $record
		 * @param string $platformName
		 * @param User $user
		 *
		 * @return BoxContent|null
		 * @throws Exception
		 */
		public static function fetchGridViewByType ($adb, $boxName, $moduleName, $record, $platformName, $user) {
			$boxContent = GridViewManager::getInstance ($adb)->fetchBoxContent ($boxName);

			if (empty ($boxContent)) {
				throw new Exception ('Cuadricula no identificada!');
			}
			$mainArguments = get_defined_vars ();
			$action = $boxContent->getAction();
			if (!empty ($action)) {
				$arguments  = array ();
				$attributes = explode (',', $boxContent->getAttributes());
				foreach ($attributes as $value) {
					$arguments [] = $mainArguments[ $value ];
				}
				$returnFunction = call_user_func_array ("{$action}", $arguments);
				$boxContent->setContent ($returnFunction);
			}
			return $boxContent;
		}

		/**
		 * Para buscar la vista cuadricula por ID
		 *
		 * @param PearDatabase $adb
		 * @param integer $gridViewId
		 *
		 * @return GridView|null
		 * @throws Exception
		 */
		public static function fetchGridViewById ($adb, $gridViewId) {
			if (empty($gridViewId)) {
				return null;
			}
			return GridViewManager::getInstance ($adb)->fetchGridView ($gridViewId);
		}

		/**
		 * Para buscar los mensajes a mostrar en la vista cuadricula
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param integer $record
		 * @param User $user
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchMessages ($adb, $moduleName, $record, $user) {
			if (empty($record) || empty($moduleName)) {
				return null;
			}

			$searchParameter              = NotificationHelper::getInitialParameters ();
			$searchParameter ['recordId'] = $record;
			$searchParameter ['module']   = $moduleName;
			return NotificationHelper::searchParleyByWhere ($adb, $user, $searchParameter);
		}

		/**
		 * @param $parameters
		 *
		 * @return array|null
		 * @throws Exception
		 * @throws SmartyException
		 */
		public static function fetchRelatedList ($parameters) {
			if (empty ($parameters['relatedList']) || !is_array($parameters ['relatedList'])) {
				return null;
			}
			$currentModule = $parameters ['currentModule'];
			$currentTabId  = getTabid ($currentModule);
			$modObj        = $parameters ['entity'];
			$modObj->id    = $parameters ['recordId'];
			$recordId      = $parameters ['recordId'];
			$relatedCards  = array ();
			$theme_path    = "themes/{$parameters['theme']}/";
			$image_path    = $theme_path . 'images/';
			foreach ($parameters ['relatedList'] as $header => $relatedList) {
				if ((!empty ($relatedList['relationId'])) && ($relatedList['relationId'] > 0)) {
					try {
						$relationInfo    = getRelatedListInfoById ($relatedList['relationId']);
						$relatedModule   = $relatedList ['tabName']; //getTabModuleName ($relationInfo ['relatedTabId']);
						$function_name   = $relationInfo ['functionName'];
						$relatedListData = $modObj->$function_name (
							$recordId,
							$currentTabId,
							$relatedList ['related_tabid'],
							(!empty($relatedList ['actions'])) ? $relatedList ['actions'] : $relatedList ['fieldName'],
							false,
							$relatedList ['relationId'],
							true
						);
						
						$target = self::getRelatedListFormattedHeader ($parameters['adb'], $currentTabId, $currentModule, $relatedList ['related_tabid']);
						$customButton                      = self::getRelatedListCustomButtons ($currentModule, $recordId, $relatedList, $header);
						$customButton                     .= $relatedListData ['CUSTOM_BUTTON'];
						$navi                              = $relatedListData ['navigation'];
						$relatedListData ['CUSTOM_BUTTON'] = null;
						$relatedListData ['navigation']    = null;

						if (array_key_exists ('isModal', $parameters)) {
							$relatedListData['header'] = str_replace ($target, $target . '_modal', $relatedListData['header']);
							$navi                      = str_replace ($target, $target . '_modal', $navi);
							$target                    .= '_modal';
						}

						RelatedListViewSession::addRelatedModuleToSession ($relatedList ['relationId'], $header);
						$smarty = new vtigerCRM_Smarty ();
						$smarty->assign ('ACTION', $relatedList ['actions']);
						$smarty->assign ('MOD', $parameters ['mod_strings']);
						$smarty->assign ('APP', $parameters ['app_strings']);
						$smarty->assign ('THEME', $parameters ['theme']);
						$smarty->assign ('IMAGE_PATH', $image_path);
						$smarty->assign ('ID', $recordId);
						$smarty->assign ('MODULE', $currentModule);
						$smarty->assign ('RELATED_MODULE', $relatedModule);
						$smarty->assign ('RELATED_ID', $relatedList ['relationId']);
						$smarty->assign ('RELATED_TABID', $relatedList ['related_tabid']);
						$smarty->assign ('HEADER', $header);
						$smarty->assign ('RELATEDLISTDATA', $relatedListData);
						$smarty->assign ('TARGET', $target);
						$relatedCards [] = array (
							'currentModule' => $currentModule,
							'header'        => $header,
							'customButton'  => $customButton,
							'navi'          => $navi,
							'card'          => $smarty->fetch ('modules/grid_view/BoxContenets/RelatedListCardContents.tpl'),
						);

						if (is_array ($relatedListData)) {
							$smarty->assign ('RESET_COOKIE', $parameters ['resetCookie']);
						}
					} catch (Exception $e) {
						// Continuar con la siguiente lista relacionada en lugar de fallar completamente
						continue;
					}
				}
			}
			return (count ($relatedCards)) ? $relatedCards : null;
		}

		/**
		 * Para buscar la lista de las tareas a mostrar en la vista cuadricula
		 *
		 * @param PearDatabase $adb
		 * @param integer $record
		 * @param Users $user
		 * @param null|array $filter
		 * @param null|string $format
		 *
		 * @return BoxTaskContent[]|null
		 * @throws Exception
		 */
		public static function fetchTaskList ($adb, $record, $filter = null, $user,  $format = null) {
			if (empty ($record)) {
				return null;
			}
			
			$activities = self::getSeactivityRel ($adb, $record);
			if (empty ($activities)) {
				return null;
			}
			$where = " a.activityid IN{$activities} AND ";
			if (empty ($format)) {
				$format = '%A %d,<br>%B - %Y';
			}
			
			if (!empty ($filter) && is_array ($filter)) {
				$activityTypes = $adb->sql_expr_datalist ($filter);
				$where .= " a.activitytype IN{$activityTypes} AND";
			} else {
				$where .= " a.activitytype IN('Activity') AND ";
			}
			
			if (!empty($user) && !is_admin ($user)) {
				require ("{$_SESSION ['plat']}/user_privileges/user_privileges_{$user->id}.php");
			
				if (!empty($current_user_groups) && is_array($current_user_groups)) {
					$groups = $adb->sql_expr_datalist ($current_user_groups);
					$whereUser = "(crm.smcreatorid={$user->id} OR crm.smownerid IN{$groups}) AND ";
				} else {
					$whereUser = "crm.smcreatorid={$user->id} AND ";
				}
			}
			$sqlQuery = "SELECT DISTINCT
						a.*,
						u.first_name,
						u.last_name,
						crm.description,
						crm.smcreatorid,
						crm.smownerid,
						a.estimated_time_unit
  			  FROM
  				  vtiger_activity a
  			  INNER JOIN vtiger_crmentity crm ON crm.crmid = a.activityid
  			  INNER JOIN vtiger_users u ON u.id = crm.smcreatorid
  			  WHERE 
  			  	{$where}
  			  	{$whereUser}
  			  	a.eventstatus !=?
  			  ORDER BY 
  			  	a.date_start ASC";
			$result = $adb->pquery ($sqlQuery, array ('Held'));
			
			if ($adb->num_rows ($result) > 0) {
				$taskList = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$taskList [] = BoxTaskContent::getInstance ()
						->setActivityId ($row ['activityid'])
						->setActivityType ($row ['activitytype'])
						->setDateEnd ($row ['due_date'])
						->setDateSet($row ['date_start'], $row ['time_end'], $format)
						->setDateStart ($row ['date_start'])
						->setDescription ($row ['description'])
						->setDueDate ($row ['due_date'], $row ['time_end'], $format)
						->setEventStatus ($row ['eventstatus'])
						->setFirstName ($row ['first_name'])
						->setLastName ($row['last_name'])
						->setLocation ($row ['location'])
						->setPriority ($row ['priority'])
						->setProgress ($row ['progress'])
						->setSubject ($row['subject'])
						->setTimeEnd ($row ['time_end'])
						->setTimeStart ($row ['time_start'])
						->setEstimatedTimeUnit ($row ['estimated_time_unit']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($taskList)) ? $taskList : null;
		}

		/**
		 * Para obtener el nombre de la vista cuadricula
		 *
		 * @param string $label
		 *
		 * @return string
		 */
		public static function getGridViewName ($label) {
			$label = str_replace (
				array ('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
				array ('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
				$label
			);
			$label = str_replace (
				array ('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
				array ('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
				$label
			);
			$label = str_replace (
				array ('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
				array ('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
				$label
			);
			$label = str_replace (
				array ('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
				array ('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
				$label
			);
			$label = str_replace (
				array ('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
				array ('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
				$label
			);
			$label = str_replace (
				array ('ñ', 'Ñ', 'ç', 'Ç'),
				array ('n', 'N', 'c', 'C'),
				$label
			);
			$label = str_replace (
				array ('·', '$', '%', '&', '/', '(', ')', '?', '¡', '¿', '[', '^', ']', '+', '}', '{', '¨', '´', '>', '< ', ';', ',', ':', '.', ' )', ' '),
				'',
				$label
			);
			$label    = substr (trim (strtoupper ($label)), 0, 15);
			$randomId = rand (1000, 9999);
			return $label . $randomId;
		}

		/**
		 * @param array $parameters
		 *
		 * @return null|string
		 * @throws Exception
		 * @throws SmartyException
		 */
		public static function getRelatedListByRelatedModule($parameters) {
			if (empty ($parameters) || !is_array($parameters)) {
				return null;
			}

			$actions       = &$parameters ['actions'];
			$currentModule = $parameters ['currentModule'];
			$header        = $parameters ['header'];
			$modObj        = $parameters['entity'];
			$modObj->id    = $parameters['recordId'];
			$theme_path    = "themes/{$parameters['theme']}/";
			$image_path    = $theme_path . 'images/';
			$relationId     = $parameters ['relationId'];
			if ((!empty ($relationId)) && ($relationId > 0)) {
				$relationInfo  = getRelatedListInfoById ($relationId);
				$relatedModule = getTabModuleName ($relationInfo ['relatedTabId']);
				$function_name = $relationInfo ['functionName'];

				$relatedListData = $modObj->$function_name(
					$modObj->id,
					getTabid ($currentModule),
					$relationInfo['relatedTabId'],
					$actions,
					false,
					$relationId
				);

				$relatedListData ['CUSTOM_BUTTON'] = null;
				$relatedListData ['navigation']    = null;

				RelatedListViewSession::addRelatedModuleToSession ($relationId, $header);
				$smarty = new vtigerCRM_Smarty ();
				$smarty->assign ('MOD', $parameters ['app_strings']);
				$smarty->assign ('APP', $parameters ['app_strings']);
				$smarty->assign ('THEME', $parameters['theme']);
				$smarty->assign ('IMAGE_PATH', $image_path);
				$smarty->assign ('ID', $modObj->id);
				$smarty->assign ('MODULE', $currentModule);
				$smarty->assign ('RELATED_MODULE', $relatedModule);
				$smarty->assign ('HEADER', $header);
				$smarty->assign ('RELATEDLISTDATA', $relatedListData);
				return $smarty->fetch ('modules/grid_view/BoxContenets/RelatedListCardModule.tpl');
			}
			return null;
		}

		/**
		 * Establece la desactivacion de la ultima vista cuadricula
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 */
		public static function setDisabledLastActiveView ($adb, $moduleName) {
			if (empty ($moduleName)) {
				return;
			}
			$adb->pquery ('UPDATE vtiger_grid_view SET gridviewstatus=? WHERE tabname=?', array ('DISABLED', $moduleName));
		}

	}
