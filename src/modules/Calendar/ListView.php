<?php
	require_once ('Smarty_setup.php');
	require_once ('include/QueryGenerator/QueryGenerator.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $adb, $app_strings, $current_user, $currentModule;

	$isAjaxRequest   = isset ($_GET ['Ajax']);
	$filters         = PlatzillaUtils::purify ($_GET, 'filters');
	$profileIds      = PlatzillaUtils::purify ($_GET, 'profileids');
	$kanbanViewId    = PlatzillaUtils::purify ($_GET, 'kviewid');
	$kanbanFieldName = PlatzillaUtils::purify ($_GET, 'kfieldname');
	$page            = PlatzillaUtils::purify ($_GET, 'page');
	$relatedModule   = PlatzillaUtils::purify ($_GET, 'relmodule', null);
	$idTab           = PlatzillaUtils::purify ($_GET, 'idTab', null);
	$selectedTab     = PlatzillaUtils::purify ($_GET, 'selectedtab', null);
	$sortBy          = PlatzillaUtils::purify ($_GET, 'sortby');
	$sortOrder       = PlatzillaUtils::purify ($_GET, 'sortorder');
	$viewId          = PlatzillaUtils::purify ($_GET, 'viewid');
	$profileIds      = !empty ($profileIds) ? explode (',', $profileIds) : null;
	$viewType        = !empty ($kanbanViewId) ? 'KANBAN' : 'REGULAR';

	try {
		if (!empty ($_SESSION ['platInstancia'])) {
			if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
				throw new Exception ('Debes verificar tu cuenta', 400);
			}

			$masterAdb    = AdbManager::getInstance ()->getMasterAdb ();
			$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
			$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
			if ((empty ($subscription)) || ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE)) {
				throw new Exception ('Tu suscripción se encuentra inactiva', 403);
			}

			$applications     = PlatformUtils::getApplicationsByUserRole ($adb, $current_user->column_fields ['roleid'], $currentModule);
			$canCreateRecords = true;
		} else {
			$applications     = PlatformUtils::getApplicationsByModuleName ($adb, $currentModule);
			$canCreateRecords = true;
		}
		// Custom View
		$customView = new CustomView ($currentModule);
		if ($viewType == 'REGULAR') {
			if (!empty ($viewId)) {
				$queryGenerator = new QueryGenerator ($currentModule, $current_user);
				$queryGenerator->initForCustomViewById ($viewId);
				$listQuery        = $queryGenerator->getQuery ();
				$conditionalWhere = $queryGenerator->getConditionalWhere ();
				$view             = DataViewUtils::fetchViewById ($adb, $currentModule, $viewId);
			} else {
				$view = DataViewUtils::fetchDefaultView ($adb, $currentModule);
			}

			if (empty ($view)) {
				throw new Exception ('La vista solicitada no se encuentra registrada');
			}

			$viewPermissions = DataViewUtils::fetchViewPermissions ($adb, $view, $current_user);
			if ((!is_array ($viewPermissions)) || (!in_array (DataViewUtils::PERMISSION_CAN_USE, $viewPermissions))) {
				throw new Exception ('Acceso denegado');
			}

			$orderBy = (!empty ($sortBy)) && (!empty ($sortOrder)) ? array ($sortBy => $sortOrder) : null;
			if (!empty ($filters)) {
				$filters        = explode (';', $filters);
				$searchCriteria = array ();
				foreach ($filters as $filter) {
					if (strpos ($filter, '>=') !== false) {
						$operator = '>=';
					} else if (strpos ($filter, '<=') !== false) {
						$operator = '<=';
					} else {
						$operator = '=';
					}
					list ($fieldName, $fieldValue) = explode ($operator, $filter);
					$searchCriteria [] = array ($fieldName, $operator, $fieldValue);
				}
			} else {
				$searchCriteria = null;
			}
			$viewData = DataViewUtils::fetchViewData ($adb, $view, $current_user, $page, $orderBy, $conditionalWhere, $searchCriteria, $relatedModule);

			$arguments     = array (
				'module'   => $currentModule,
				'user'     => $current_user,
				'view'     => Notification::LIST_VIEW,
				'style'    => Notification::STYLE_NOTIFY,
				'recordId' => 0,
				'platform' => $_SESSION ['plat'],
			);
			$notifications = NotificationUtils::fetchApplicableOnScreenNotifications ($adb, $arguments);

			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ACTIVE_APPLICATIONS', $applications);
			$smarty->assign ('ALLOW_MASS_ACTIONS', false);
			$smarty->assign ('APP', $app_strings);
			$smarty->assign ('APPLICATION_VIEWS_ENABLED', PlatformUtils::areApplicationViewsEnabled ($adb));
			$smarty->assign ('AVAILABLE_VIEWS', DataViewUtils::fetchAvailableViews ($adb, $currentModule, $current_user));
			$smarty->assign ('CAN_CREATE_RECORDS', $canCreateRecords);
			$smarty->assign ('IS_ADMIN', is_admin ($current_user));
			$smarty->assign ('IS_RELATED_TO_CALENDAR', DataViewUtils::isRelatedToCalendar ($adb, $currentModule));
			$smarty->assign ('idActivity', $idTab);
			$smarty->assign ('MODULE', $currentModule);
			$smarty->assign ('NOTIFICATIONS', $notifications);
			$smarty->assign ('PROFILE_IDS', $profileIds);
			$smarty->assign ('RELATED_MODULE', $relatedModule);
			$smarty->assign ('TAB_NAME', $selectedTab);
			$smarty->assign ('RETURN_MODULE', ((!empty ($selectedTab) && $searchCriteria !== 'undefined')? 'Home' : 'Calendar'));
			$smarty->assign ('RETURN_ACTION', ((!empty ($selectedTab) && $searchCriteria !== 'undefined')? 'index' : 'ListView'));
			$smarty->assign ('HAS_RELATED', ((count (explode (';',$relatedModule))) > 1) || empty ($relatedModule));
			$smarty->assign ('ROOT_FOLDER_PATH', PlatzillaUtils::getPlatzillaRootFolderPath ());
			$smarty->assign ('VIEW', $view);
			$smarty->assign ('VIEW_DATA', $viewData);
			$smarty->assign ('VIEW_PERMISSIONS', $viewPermissions);
			$smarty->assign ('VIEW_TYPE', $viewType);
			if (isset ($_SESSION ['flashmessage'])) {
				$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
				$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
				unset ($_SESSION ['flashmessage']);
			}
			if (!$isAjaxRequest) {
				$smarty->display ('modules/Calendar/ListView.tpl');
			} else {
				$smarty->display ('modules/Calendar/ListViewEntries.tpl');
			}
		} else {
			// TODO: Vistas Kanban
		}
	} catch (Exception $e) {
		$code   = $e->getCode ();
		$smarty = new vtigerCRM_Smarty ();
		if ($code === 400) {
			$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta');
			$smarty->display ('instanciaUnverified.tpl');
		} else if ($code === 403) {
			$smarty->assign ('LABEL', 'Tu suscripción');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('URL', 'index.php?module=Home&action=index');
			$smarty->display ('Message.tpl');
		} else {
			$smarty->assign ('LABEL', 'Inicio');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('URL', 'index.php');
			$smarty->display ('Message.tpl');
		}
	}
