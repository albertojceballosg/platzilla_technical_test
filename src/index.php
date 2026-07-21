<?php
	define ('ADODB_NEVER_PERSIST', true);
	ini_set ('display_errors', 1);
	if (isset ($_GET ['asopotamadre'])) {
		error_reporting (E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
	} else {
		error_reporting (E_ERROR);
	}

	global $entityDel;
	global $display;
	global $category;
	global $clientView;
	global $clientWeb;
	global $demoMode;
	global $bDlgModales;
	global $gWP;
	global $CALENDAR_DISPLAY, $WORLD_CLOCK_DISPLAY, $CALCULATOR_DISPLAY, $CHAT_DISPLAY, $CHECK_MOBILE;
	global $platPrincipal;

	require_once ('include/devicedetect.class.php');
	$CHECK_MOBILE  = deviceDetect::mobile_device_detect ();
	$lstPlatsFijas = array ('app' => 'madre');

	$clientView  = false;
	$bDlgModales = false;
	$gWP         = false;
	if (isset($lstPlatsFijas[ $_SERVER['HTTP_HOST'] ])) {
		$nameplat[] = $lstPlatsFijas[ $_SERVER['HTTP_HOST'] ];
	} else {
		$nameplat = explode ('.', $_SERVER['HTTP_HOST']);
		if (isset($lstPlatsFijas[ $nameplat[0] ])) {
			$nameplat[0] = $lstPlatsFijas[ $nameplat[0] ];
		}
	}
	if ((count ($nameplat) >= 1) && $nameplat[0] != 'madre') // Para el caso de la madre no se asigna instancia
	{
		$_REQUEST['plat'] = $nameplat[0];
	}

	if (isset($_REQUEST['VLK']) && $_REQUEST['VLK'] != '') {
		include 'include/security.php';

		$cadenaDesEncryptada = decrypt (($_REQUEST['VLK']), "estaeslaclave01EncryptadaDeTimeManagement");
		$unsecurityString    = unserialize ($cadenaDesEncryptada);

		list($_REQUEST['user_name'], $_REQUEST['user_password'], $themeClient) = explode ("|", $unsecurityString);
		$_REQUEST['user_name']     = unserialize ($_REQUEST['user_name']);
		$_REQUEST['user_password'] = unserialize ($_REQUEST['user_password']);
		$_REQUEST['module']        = 'Users';
		$_REQUEST['action']        = 'Authenticate';
		$_REQUEST['return_module'] = 'Users';
		$_REQUEST['return_action'] = 'Login';
		$_REQUEST['plat']          = 'marketing'; //Es la plataforma de gesti�n de clientes.
		$clientView                = true;
	}

	if (isset($_REQUEST['plat'])) {
		$plat = $_REQUEST['plat'];

		if (strstr ($plat, 'cliente-') || strstr ($plat, 'clienteweb-')) {
			session_name ($plat);
			$lstPlat          = explode ('-', $plat);
			$_REQUEST['plat'] = $lstPlat[1];
			$clientView       = true;

			if ($lstPlat[0] == 'clienteweb') {
				$clientWeb = true;
			}
		} else {
			session_name ($plat);
		}
	}
	session_start ();
	if (isset($_SESSION['authenticated_user_id_main'])) {
		$_SESSION['authenticated_user_id'] = $_SESSION['authenticated_user_id_main'];
		unset($_SESSION['authenticated_user_id_main']);
	}
	if (isset($_SESSION['plat_main'])) {
		$_SESSION['plat'] = $_SESSION['plat_main'];
		unset($_SESSION['plat_main']);
	}

	if (isset($_REQUEST['parentplat'])) {
		$_SESSION['parentplat'] = $_REQUEST['parentplat'];
	}
	if ($clientView && isset($themeClient) && $themeClient != '') {
		$_SESSION['vtiger_authenticated_user_theme_client'] = $themeClient;
	}

	# MA | 23-03-2016 | Nueva forma de seleccionar plataforma y base de datos
	# platInstancia viene de autenticación de usuarios
	if (isset($_SESSION['platInstancia']) && ($_SESSION['platInstancia'] != '')) {
		$_SESSION['plat'] = $_SESSION['platInstancia'];
	}

	$_SESSION['vtiger_authenticated_user_theme'] = 'centaurus';

	if (version_compare (phpversion (), '5.2.0') < 0) {
		insert_charset_header ();
		$serverPhpVersion = phpversion ();
		require_once ('phpversionfail.php');
		die();
	}

	require_once ('include/utils/utils.php');

	if (version_compare (phpversion (), '5.2.0') < 0) {
		eval('function clone  ($object) { return $object; }');
	}

	global $currentModule;
	function stripslashes_checkstrings ($value) {
		if (is_string ($value)) {
			return stripslashes ($value);
		}
		return $value;
	}

	if (get_magic_quotes_gpc () == 1) {
		$_REQUEST = array_map ("stripslashes_checkstrings", $_REQUEST);
		$_POST    = array_map ("stripslashes_checkstrings", $_POST);
		$_GET     = array_map ("stripslashes_checkstrings", $_GET);
	}

	// Allow for the session information to be passed via the URL for printing.
	if (isset($_REQUEST['PHPSESSID'])) {
		session_id ($_REQUEST['PHPSESSID']);
		//Setting the same session id to Forums as in CRM
		$sid = $_REQUEST['PHPSESSID'];
	}

	if (isset($_REQUEST['view'])) {
		//setcookie("view",$_REQUEST['view']);
		$view             = $_REQUEST["view"];
		$_SESSION["view"] = $view;
	}

	function insert_charset_header () {
		global $app_strings, $default_charset;
		$charset = $default_charset;

		if (isset($app_strings['LBL_CHARSET'])) {
			$charset = $app_strings['LBL_CHARSET'];
		}
		header ('Content-Type: text/html; charset=' . $charset);
	}

	insert_charset_header ();
	// Create or reestablish the current session
	//$_SESSION['KCFINDER']              = array ();
	//$_SESSION['KCFINDER']['disabled']  = false;
	//$_SESSION['KCFINDER']['uploadURL'] = "madre/uploads";
	//$_SESSION['KCFINDER']['uploadDir'] = "../madre/uploads";

	if (!is_file ('config.inc.php')) {
		header ("Location: install.php");
		exit();
	}

	require_once ('config.inc.php');
	if (!isset($dbconfig['db_hostname']) || $dbconfig['db_status'] == '_DB_STAT_') {
		header ("Location: install.php");
		exit();
	}

	// load up the config_override.php file.  This is used to provide default user settings
	if (is_file ('config_override.php')) {
		require_once ('config_override.php');
	}

	/**
	 * Check for vtiger installed version and codebase
	 */
	require_once ('include/utils/AdbManager.class.php');
	require_once ('vtigerversion.php');
	global $adb, $vtiger_current_version;
	$adb = new PearDatabase ();
	if (isset($_SESSION['VTIGER_DB_VERSION']) && isset($_SESSION['authenticated_user_id'])) {
		if (version_compare ($_SESSION['VTIGER_DB_VERSION'], $vtiger_current_version, '!=')) {
			unset($_SESSION['VTIGER_DB_VERSION']);
			echo "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
			echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>
			<table border='0' cellpadding='5' cellspacing='0' width='98%'>
			<tbody><tr>
			<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>Migration Incompleted 1.</span></td>
			</tr>
			<tr>
			<td class='small' align='right' nowrap='nowrap'>Please contact your system administrator.<br></td>
			</tr>
			</tbody></table>
			</div>";
			echo "</td></tr></table>";
			exit();
		}
	} else {
		$result    = $adb->query ("SELECT * FROM vtiger_version");
		$dbversion = $adb->query_result ($result, 0, 'current_version');
		if (version_compare ($dbversion, $vtiger_current_version, '=')) {
			$_SESSION['VTIGER_DB_VERSION'] = $dbversion;
		} else {
			echo "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
			echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>
			<table border='0' cellpadding='5' cellspacing='0' width='98%'>
			<tbody><tr>
			<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>Migration Incompleted 2.</span></td>
			</tr>
			<tr>
			<td class='small' align='right' nowrap='nowrap'>Please contact your system administrator.<br></td>
			</tr>
			</tbody></table>
			</div>";
			echo "</td></tr></table>";
			exit();
		}
	}
	// END

	$default_config_values = Array ("allow_exports" => "all", "upload_maxsize" => "3000000", "listview_max_textlength" => "40", "php_max_execution_time" => "0");

	set_default_config ($default_config_values);

	// Set the default timezone preferred by user
	global $default_timezone;
	if (isset($default_timezone) && function_exists ('date_default_timezone_set')) {
		@date_default_timezone_set ($default_timezone);
	}

	require_once ('include/logging.php');
	require_once ('modules/Users/Users.php');

	global $currentModule;

	$log = LoggerManager::getLogger ('index');

	global $seclog;
	$seclog = LoggerManager::getLogger ('SECURITY');

	if (isset($_REQUEST['PHPSESSID'])) {
		$log->debug ("****Starting for session " . $_REQUEST['PHPSESSID']);
	} else {
		$log->debug ("****Starting for new session");
	}

	// We use the REQUEST_URI later to construct dynamic URLs.  IIS does not pass this field
	// to prevent an error, if it is not set, we will assign it to ''
	if (!isset($_SERVER['REQUEST_URI'])) {
		$_SERVER['REQUEST_URI'] = '';
	}

	$action = '';
	if (isset($_REQUEST['action'])) {
		$action = $_REQUEST['action'];
	}
	if ($action == 'Export') {
		include ('include/utils/export.php');
	}
	if ($action == 'ExportAjax') {
		include ('include/utils/ExportAjax.php');
	}
	// vtlib customization: Module manager export
	if ($action == 'ModuleManagerExport') {
		include ('modules/Settings/ModuleManager/Export.php');
	}
	// END

	//Code added for 'Path Traversal/File Disclosure' security fix - Philip
	$is_module = false;
	$is_action = false;
	$is_plat   = false;
	if (isset ($_REQUEST['module']) && (trim ($_REQUEST['module']))) {
		if (isset($_SESSION['plat'])) {
			$module   = $_REQUEST['module'];
			$dir      = @scandir ($root_directory . $_SESSION['plat'] . "/modules");
			$temp_arr = Array ("CVS", "Attic");
			$res_arr  = @array_intersect ($dir, $temp_arr);
			if (count ($res_arr) == 0 && !preg_match ("/[\/.]/", $module)) {
				if (@in_array ($module, $dir)) {
					$is_module = true;
				}
			}
			$in_dir  = @scandir ($root_directory . $_SESSION['plat'] . "/modules/" . $module);
			$res_arr = @array_intersect ($in_dir, $temp_arr);
			if (count ($res_arr) == 0 && !preg_match ("/[\/.]/", $module)) {
				if (@in_array ($action . ".php", $in_dir)) {
					$is_action = true;
				}
			}
		}

		if (!$is_module || !$is_action) {
			$module   = $_REQUEST['module'];
			$dir      = @scandir ($root_directory . "modules");
			$temp_arr = Array ("CVS", "Attic");
			$res_arr  = @array_intersect ($dir, $temp_arr);
			if (count ($res_arr) == 0 && !preg_match ("/[\/.]/", $module)) {
				if (@in_array ($module, $dir)) {
					$is_module = true;
				}
			}
			$in_dir  = @scandir ($root_directory . "modules/" . $module);
			$res_arr = @array_intersect ($in_dir, $temp_arr);
			if (count ($res_arr) == 0 && !preg_match ("/[\/.]/", $module)) {
				if (@in_array ($action . ".php", $in_dir)) {
					$is_action = true;
				}
			}
		} else {
			$is_plat              = true;//El modulo es personalizado de la instancia de plataforma
			$_SESSION['dir_plat'] = $_SESSION['plat'] . '/';
		}

		if (!$is_module) {
			die("Module name is missing. Please check the module name.");
		}
		if (!$is_action) {
			die("Action name is missing. Please check the action name.");
		}
	}

	//Code added for 'Multiple SQL Injection Vulnerabilities & XSS issue' fixes - Philip
	if (isset($_REQUEST['record']) && !is_numeric ($_REQUEST['record']) && $_REQUEST['record'] != '') {
		die("An invalid record number specified to view details.");
	}

	// Check to see if there is an authenticated user in the session.
	$use_current_login = false;
	if (isset($_SESSION["authenticated_user_id"]) && (isset($_SESSION["app_unique_key"]) && $_SESSION["app_unique_key"] == $application_unique_key) && (!isset($_SESSION['briefing']))) {
		$use_current_login = true;
	}
	if (isset($_SESSION['plat'])) {
		$plat_default_module = getDefaultModule ();
		if (!empty($plat_default_module)) {
			$default_module = $plat_default_module;
		}
		if ($default_module == 'Tasks') {
			$default_module = 'Calendar';
			$default_action = 'ListView';
		} else {
			$defaction = obtenerValorVariable ('DEFAULT_ACTION', $default_module);
			if ($defaction) {
				$default_action = $defaction;
			}
		}

		$plat_default_theme = obtenerTemaDefecto ();
		if (!empty($plat_default_theme)) {
			$default_theme = $plat_default_theme;
		}
	}
	// Prevent loading Login again if there is an authenticated user in the session.
	if (isset($_SESSION["authenticated_user_id"]) && $module == 'Users' && $action == 'Login') {
		header ("Location: index.php?action=$default_action&module=$default_module");
	}

	if ($use_current_login) {
		//getting the internal_mailer flag
		if (!isset($_SESSION['internal_mailer'])) {
			$qry_res                     = $adb->pquery ("SELECT internal_mailer FROM vtiger_users WHERE id=?", array ($_SESSION["authenticated_user_id"]));
			$_SESSION['internal_mailer'] = $adb->query_result ($qry_res, 0, "internal_mailer");
		}
		$log->debug ("We have an authenticated user id: " . $_SESSION["authenticated_user_id"]);
	} else if (isset($action) && isset($module) && $action == "Authenticate" && $module == "Users") {
		$log->debug ("We are authenticating user now");
	} else {
		$lstActions = array ('signin', 'crearAplicacion', 'googleRegister', 'loginGoogle', 'pricing', 'checkDomain', 'checkMail', 'Logout', 'reset_password', 'change_password');
		// actions que requieren que el usuario no esté logueado
		$lstActionsStore    = array ('trial', 'register', 'begin', 'appsGrid', 'pricing', 'AddApplicationToCart', 'DeleteApplicationFromCart', 'CreateInstance', 'asignaCounterUsers');
		$lstActionsStore [] = 'invitation';
		$lstActionsStore [] = 'createInstanceFromInvitation';
		$lstActionsStore [] = 'BulletinBoard';
		$lstActionsStore [] = 'CreateFormativeInstance';
		$lstActionsStore [] = 'SendEmailPassword';
		$lstActionsStore [] = 'fetchSurvey';
		$lstActionsStore [] = 'saveSurvey';

		// si la sesión expiró e invocamos un action de módulo (que no sean login ni logout ni actions que requieren que el usuario no esté logueado) se redirecciona al login para continuar navegando
		if (
			(!empty ($_REQUEST['action'])) &&
			(!empty ($module)) &&
			(!in_array ($action, array_merge ($lstActions, $lstActionsStore, array ('Login', 'Logout'))))
		) {
			header ('Location: index.php');
			unset($_SESSION['briefing']);
			unset($_SESSION['plat']);
			exit ();
		}

		if ($_REQUEST['action'] != 'Logout' && $_REQUEST['action'] != 'Login') {
			$_SESSION['lastpage'] = $_SERVER['QUERY_STRING'];
		}
		$log->debug ("The current user does not have a session.  Going to the login page");
		if (($action == '' && $module == '') || ($module == 'Users' && ($action == 'Logout' || $action == 'Login'))) {
			$action = "Login";
			$module = "Users";
			include 'modules/Users/Login.php';
			unset($_SESSION['briefing']);
			unset($_SESSION['plat']);
			exit;
		} else {
			if ($module == 'Users' && (in_array ($action, $lstActions))) {
				//Acciones validas para usuarios anonimos
			} elseif ($module == 'store' && (in_array ($action, $lstActionsStore))) {
			} else {
				$postContent = getArticleActionModule ($module, $_REQUEST['action']);
				$actionreal  = $_REQUEST['action'];
				$action      = 'briefing';
				if (!empty($postContent)) {
					$gWP = true;
				}
			}

			$_SESSION['briefing']              = true;
			$_SESSION['esDemo']                = false;
			$_SESSION["authenticated_user_id"] = 1;
			$_SESSION['plat']                  = $platPrincipal;
			$theme                             = $_SESSION['vtiger_authenticated_user_theme'];
			$use_current_login                 = true;
		}
	}

	$log->debug ($_REQUEST);
	$skipHeaders       = false;
	$skipFooters       = false;
	$viewAttachment    = false;
	$skipSecurityCheck = false;

	//LCL 2013-04-02
	//Para peticiones ajax basta con indicar Ajax=true en el request
	if ((isset($_REQUEST['Ajax']) && ($_REQUEST['Ajax'] == 'true')) ||
		(isset($_REQUEST['Popup']) && ($_REQUEST['Popup'] == 'true'))
	) {
		$skipHeaders = true;
		$skipFooters = true;
	}

	if (isset($action) && isset($module)) {
		$log->info ("About to take action " . $action);
		$log->debug ("in $action");
		if (preg_match ("/^Save/", $action) ||
			preg_match ("/^createBoxScoreFromProjects/", $action) ||
			preg_match ("/^Delete/", $action) ||
			preg_match ("/^AppDelete/", $action) ||
			preg_match ("/^CatAppDelete/", $action) ||
			preg_match ("/^Choose/", $action) ||
			preg_match ("/^Popup/", $action) ||
			preg_match ("/^ChangePassword/", $action) ||
			preg_match ("/^Authenticate/", $action) ||
			preg_match ("/^Logout/", $action) ||
			preg_match ("/^add2db/", $action) ||
			preg_match ("/^result/", $action) ||
			preg_match ("/^LeadConvertToEntities/", $action) ||
			preg_match ("/^downloadfile/", $action) ||
			preg_match ("/^massdelete/", $action) ||
			preg_match ("/^updateLeadDBStatus/", $action) ||
			preg_match ("/^AddCustomFieldToDB/", $action) ||
			preg_match ("/^updateRole/", $action) ||
			preg_match ("/^UserInfoUtil/", $action) ||
			preg_match ("/^deleteRole/", $action) ||
			preg_match ("/^UpdateComboValues/", $action) ||
			preg_match ("/^fieldtypes/", $action) ||
			preg_match ("/^app_ins/", $action) ||
			preg_match ("/^minical/", $action) ||
			preg_match ("/^minitimer/", $action) ||
			preg_match ("/^app_del/", $action) ||
			preg_match ("/^send_mail/", $action) ||
			preg_match ("/^populatetemplate/", $action) ||
			preg_match ("/^TemplateMerge/", $action) ||
			preg_match ("/^testemailtemplateusage/", $action) ||
			preg_match ("/^saveemailtemplate/", $action) ||
			preg_match ("/^ProcessDuplicates/", $action) ||
			preg_match ("/^lastImport/", $action) ||
			preg_match ("/^lookupemailtemplate/", $action) ||
			preg_match ("/^deletewordtemplate/", $action) ||
			preg_match ("/^deleteemailtemplate/", $action) ||
			preg_match ("/^CurrencyDelete/", $action) ||
			preg_match ("/^deleteattachments/", $action) ||
			preg_match ("/^MassDeleteUsers/", $action) ||
			preg_match ("/^UpdateFieldLevelAccess/", $action) ||
			preg_match ("/^UpdateDefaultFieldLevelAccess/", $action) ||
			preg_match ("/^UpdateProfile/", $action) ||
			preg_match ("/^updateRelations/", $action) ||
			preg_match ("/^updateNotificationSchedulers/", $action) ||
			preg_match ("/^Star/", $action) ||
			preg_match ("/^addPbProductRelToDB/", $action) ||
			preg_match ("/^UpdateListPrice/", $action) ||
			preg_match ("/^PriceListPopup/", $action) ||
			preg_match ("/^SalesOrderPopup/", $action) ||
			preg_match ("/^CreatePDF/", $action) ||
			preg_match ("/^CreateSOPDF/", $action) ||
			preg_match ("/^redirect/", $action) ||
			preg_match ("/^webmail/", $action) ||
			preg_match ("/^left_main/", $action) ||
			preg_match ("/^delete_message/", $action) ||
			preg_match ("/^mime/", $action) ||
			preg_match ("/^move_messages/", $action) ||
			preg_match ("/^folders_create/", $action) ||
			preg_match ("/^imap_general/", $action) ||
			preg_match ("/^mime/", $action) ||
			preg_match ("/^download/", $action) ||
			preg_match ("/^about_us/", $action) ||
			preg_match ("/^SendMailAction/", $action) ||
			preg_match ("/^CreateXL/", $action) ||
			preg_match ("/^savetermsandconditions/", $action) ||
			preg_match ("/^home_rss/", $action) ||
			preg_match ("/^ConvertAsFAQ/", $action) ||
			preg_match ("/^Tickerdetail/", $action) ||
			preg_match ("/^" . $module . "Ajax/", $action) ||
			preg_match ("/^ActivityAjax/", $action) ||
			preg_match ("/^chat/", $action) ||
			preg_match ("/^vtchat/", $action) ||
			preg_match ("/^updateCalendarSharing/", $action) ||
			preg_match ("/^disable_sharing/", $action) ||
			preg_match ("/^HeadLines/", $action) ||
			preg_match ("/^TodoSave/", $action) ||
			preg_match ("/^RecalculateSharingRules/", $action) ||
			(preg_match ("/^body/", $action) && preg_match ("/^Webmails/", $module)) ||
			(preg_match ("/^dlAttachments/", $action) && preg_match ("/^Webmails/", $module)) ||
			(preg_match ("/^DetailView/", $action) && preg_match ("/^Webmails/", $module)) ||
			(preg_match ("/^play/", $action) && preg_match ("/^video/", $module)) ||
			preg_match ("/^savewordtemplate/", $action) ||
			preg_match ("/^mailmergedownloadfile/", $action) ||
			(preg_match ("/^Webmails/", $module) && preg_match ("/^get_img/", $action)) ||
			preg_match ("/^download/", $action) ||
			preg_match ("/^getListOfRecords/", $action) ||
			preg_match ("/^AddBlockFieldToDB/", $action) ||
			preg_match ("/^AddBlockToDB/", $action) ||
			preg_match ("/^MassEditSave/", $action) ||
			preg_match ("/^iCalExport/", $action) ||
			preg_match ("/^googleRegister/", $action) ||
			preg_match ("/^loginGoogle/", $action) ||
			preg_match ("/^crearAplicacion/", $action) ||
			preg_match ("/^BulletinBoard/", $action) ||
			preg_match ("/^CreateFormativeInstance/",$action) ||
			preg_match ("/^fetchSurvey/",$action) ||
			preg_match ("/^saveSurvey/",$action) ||
			preg_match ("/^CreateInstance/", $action) ||
			preg_match ("/^createInstanceFromInvitation/", $action) ||
			preg_match ("/^SendEmailPassword/", $action) ||
			preg_match ("/^codeverification/", $action) ||
			preg_match ("/^menuAjax/", $action) ||
			preg_match ("/^WidgetsDelete/", $action) ||
			preg_match ("/^AppDuplicator3/", $action) ||
			preg_match ("/^MassCreateSave/", $action) ||
			preg_match ("/^HelpSettingsDelete/", $action)

		) {
			$skipHeaders = true;
			//skip headers for all these invocations as they are mostly popups
			if (preg_match ("/^Popup/", $action) ||
				preg_match ("/^ChangePassword/", $action) ||
				//preg_match("/^Export/", $action) ||
				preg_match ("/^downloadfile/", $action) ||
				preg_match ("/^fieldtypes/", $action) ||
				preg_match ("/^lookupemailtemplate/", $action) ||
				preg_match ("/^about_us/", $action) ||
				preg_match ("/^home_rss/", $action) ||
				preg_match ("/^" . $module . "Ajax/", $action) ||
				preg_match ("/^chat/", $action) ||
				preg_match ("/^vtchat/", $action) ||
				preg_match ("/^massdelete/", $action) ||
				preg_match ("/^mailmergedownloadfile/", $action) || preg_match ("/^get_img/", $action) ||
				preg_match ("/^download/", $action) ||
				preg_match ("/^ProcessDuplicates/", $action) ||
				preg_match ("/^lastImport/", $action) ||
				preg_match ("/^massdelete/", $action) ||
				preg_match ("/^getListOfRecords/", $action) ||
				preg_match ("/^MassEditSave/", $action) ||
				preg_match ("/^play/", $action) ||
				preg_match ("/^iCalExport/", $action) ||
				preg_match ("/^googleRegister/", $action) ||
				preg_match ("/^loginGoogle/", $action) ||
				preg_match ("/^crearAplicacion/", $action) ||
				preg_match ("/^menuAjax/", $action) ||
				preg_match ("/^AppDuplicator3/", $action) ||
				preg_match ("/^MassCreateSave/", $action)
			) {
				$skipFooters = true;
			}
			//skip footers for all these invocations as they are mostly popups
			if (preg_match ("/^downloadfile/", $action)
				|| preg_match ("/^fieldtypes/", $action)
				|| preg_match ("/^mailmergedownloadfile/", $action)
				|| preg_match ("/^get_img/", $action)
				|| preg_match ("/^MergeFieldLeads/", $action)
				|| preg_match ("/^MergeFieldContacts/", $action)
				|| preg_match ("/^MergeFieldAccounts/", $action)
				|| preg_match ("/^MergeFieldProducts/", $action)
				|| preg_match ("/^MergeFieldHelpDesk/", $action)
				|| preg_match ("/^MergeFieldPotentials/", $action)
				|| preg_match ("/^MergeFieldVendors/", $action)
				|| preg_match ("/^dlAttachments/", $action)
				|| preg_match ("/^iCalExport/", $action)
			) {
				$viewAttachment = true;
			}
			if (($action == ' Delete ') && (!$entityDel)) {
				$skipHeaders = false;
			}
		}

		if ($action == 'Save') {
			header ("Expires: Mon, 20 Dec 1998 01:00:00 GMT");
			header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
		}

		if (($module == 'Users' || $module == 'Home' || $module == 'uploads') && $_REQUEST['parenttab'] != 'Settings') {
			$skipSecurityCheck = true;
		}

		if ($action == 'UnifiedSearch') {
			$currentModuleFile = $_SESSION['dir_plat'] . 'modules/Home/' . $action . '.php';
			if (!file_exists ($currentModuleFile)) {
				$currentModuleFile = 'modules/Home/' . $action . '.php';
			}
		} else {
			$currentModuleFile = $_SESSION['dir_plat'] . 'modules/' . $module . '/' . $action . '.php';
			if (!file_exists ($currentModuleFile)) {
				$currentModuleFile = 'modules/' . $module . '/' . $action . '.php';
			}
		}
		$currentModule = $module;
	} elseif (isset($module)) {

		$currentModule     = $module;
		$currentModuleFile = $_SESSION['dir_plat'] . $moduleDefaultFile[ $currentModule ];
		if (!file_exists ($currentModuleFile)) {
			$currentModuleFile = $moduleDefaultFile[ $currentModule ];
		}
	} else {
		// use $default_module and $default_action as set in config.php
		// Redirect to the correct module with the correct action.  We need the URI to include these fields.
		$current_user = new Users();
		$current_user->retrieveCurrentUserInfoFromFile ($_SESSION['authenticated_user_id']);
		$plat_default_module = getDefaultModule ();
		if (!empty($plat_default_module)) {
			$default_module = $plat_default_module;
		}
		if ($default_module == 'Tasks') {
			header ('Location: index.php?module=Calendar&action=ListView');
		} else {
			header ("Location: index.php?module={$default_module}&action={$default_action}");
		}
	}

	$log->info ("current page is $currentModuleFile");
	$log->info ("current module is $currentModule ");

	// for printing
	$module                    = (isset($_REQUEST['module'])) ? vtlib_purify ($_REQUEST['module']) : "";
	$action                    = (isset($_REQUEST['action'])) ? vtlib_purify ($_REQUEST['action']) : "";
	$record                    = (isset($_REQUEST['record'])) ? vtlib_purify ($_REQUEST['record']) : null;
	$lang_crm                  = (isset($_SESSION['authenticated_user_language'])) ? $_SESSION['authenticated_user_language'] : "";
	$GLOBALS['request_string'] = "&module=$module&action=$action&record=$record&lang_crm=$lang_crm";

	$current_user = new Users();

	if ($use_current_login) {
		//getting the current user info from flat file
		$result = $current_user->retrieveCurrentUserInfoFromFile ($_SESSION['authenticated_user_id']);
		if ($result == null) {
			session_destroy ();
			header ("Location: index.php?action=Login&module=Users");
		}

		$moduleList = getPermittedModuleNames ();

		foreach ($moduleList as $mod) {
			$moduleDefaultFile[ $mod ] = "modules/" . $currentModule . "/index.php";
		}

		//auditing
		if ((!in_array ($module, array ('', 'notification_center'))) && (!in_array ($action, array ('', 'notificationAjax')))) {
			$adb->pquery (
				'INSERT INTO vtiger_audit_trial (sessionid, userid, module, action, recordid, actiondate) VALUES (?, ?, ?, ?, ?, ?)',
				array (session_id (), $current_user->id, $module, $action, !empty ($record) ? $record : null, $adb->formatDate (date ('Y-m-d H:i:s'), true))
			);
		}

		$log->debug ('Current user is: ' . $current_user->user_name);
	}

	if (isset($_SESSION['vtiger_authenticated_user_theme']) && $_SESSION['vtiger_authenticated_user_theme'] != '') {
		$theme = $_SESSION['vtiger_authenticated_user_theme'];
	} else {
		if (!empty($current_user->theme)) {
			$theme = $current_user->theme;
		} else {
			$theme = $default_theme;
		}
	}

	if ($clientView && isset($_SESSION['vtiger_authenticated_user_theme_client'])) {
		$theme                                       = $_SESSION['vtiger_authenticated_user_theme_client'];
		$_SESSION['vtiger_authenticated_user_theme'] = $_SESSION['vtiger_authenticated_user_theme_client'];
	}

	$log->debug ('Current theme is: ' . $theme);

	//Used for current record focus
	$focus = "";

	// if the language is not set yet, then set it to the default language.
	if (isset($_SESSION['authenticated_user_language']) && $_SESSION['authenticated_user_language'] != '') {
		$current_language = $_SESSION['authenticated_user_language'];
	} else {
		if (!empty($current_user->language)) {
			$current_language = $current_user->language;
		} else {
			$current_language = $default_language;
		}
	}
	$log->debug ('current_language is: ' . $current_language);

	//set module and application string arrays based upon selected language
	$app_currency_strings = return_app_currency_strings_language ($current_language);
	$app_strings          = return_application_language ($current_language);
	$app_list_strings     = return_app_list_strings_language ($current_language);
	$mod_strings          = return_module_language ($current_language, $currentModule);

	//If DetailView, set focus to record passed in
	if ($action == "DetailView") {
		if (!isset($_REQUEST['record'])) {
			die("A record number must be specified to view details.");
		}

		// If we are going to a detail form, load up the record now.
		// Use the record to track the viewing.
		// todo - Have a record of modules and thier primary object names.
		//Getting the actual module
		switch ($currentModule) {
			case 'Webmails':
				//No need to create a webmail object here
				break;
			default:
				$focus = CRMEntity::getInstance ($currentModule);
				break;
		}

		if (isset($_REQUEST['record']) && $_REQUEST['record'] != '' && $_REQUEST["module"] != "Webmails" && $current_user->id != '') {
			// Only track a viewing if the record was retrieved.
			$focus->track_view ($current_user->id, $currentModule, $_REQUEST['record']);
		}
	}

	// set user, theme and language cookies so that login screen defaults to last values
	if (isset($_SESSION['authenticated_user_id'])) {
		$log->debug ("setting cookie ck_login_id_vtiger to " . $_SESSION['authenticated_user_id']);
		setcookie ('ck_login_id_vtiger', $_SESSION['authenticated_user_id']);
	}
	if (isset($_SESSION['vtiger_authenticated_user_theme'])) {
		$log->debug ("setting cookie ck_login_theme_vtiger to " . $_SESSION['vtiger_authenticated_user_theme']);
		setcookie ('ck_login_theme_vtiger', $_SESSION['vtiger_authenticated_user_theme']);
	}
	if (isset($_SESSION['authenticated_user_language'])) {
		$log->debug ("setting cookie ck_login_language_vtiger to " . $_SESSION['authenticated_user_language']);
		setcookie ('ck_login_language_vtiger', $_SESSION['authenticated_user_language']);
	}

	if ($_REQUEST['module'] == 'Documents' && $action == 'DownloadFile') {
		include ('modules/Documents/DownloadFile.php');
		exit;
	}

	//skip headers for popups, deleting, saving, importing and other actions
	if (!$skipHeaders) {
		$log->debug ("including headers");
		if ($use_current_login) {
			if (isset($_REQUEST['category']) && $_REQUEST['category'] != '') {
				$category = vtlib_purify ($_REQUEST['category']);
			} else {
				$category = getParentTabFromModule ($currentModule);
			}
			include ('modules/Vtiger/header.php');
		}

		if (isset($_SESSION['administrator_error'])) {
			// only print DB errors once otherwise they will still look broken after they are fixed.
			// Only print the errors for admin users.
			if (is_admin ($current_user)) {
				echo $_SESSION['administrator_error'];
			}
			unset($_SESSION['administrator_error']);
		}

		echo "<!-- startscrmprint -->";
		//Capa de mensajes y alertas.
		echo '<div id="Mensajes" class="calAddEvent layerPopup" style="display:none;position: absolute;top: 50%;left: 50%;width: 400 px;height: 100px;margin-top: -50px;margin-left: -200px;font-size:10pt;text-align:center;z-index:10000;">
			<div id="TextoMensajes" style="padding:20px">
			</div>
			<br/>
			<input type="button" class="crmbutton small cancel" onclick="cierraidUI(\'Mensajes\');" value="' . getTranslatedString ('LBL_CLOSE') . '">
		</div>';
	} else {
		$log->debug ("skipping headers");
	}

	//fetch the permission set from session and search it for the requisite data
	if (isset($_SESSION['vtiger_authenticated_user_theme']) && $_SESSION['vtiger_authenticated_user_theme'] != '') {
		$theme = $_SESSION['vtiger_authenticated_user_theme'];
	} else {
		if (!empty($current_user->theme)) {
			$theme = $current_user->theme;
		} else {
			$theme = $default_theme;
		}
	}

	if ($clientView) {
		$skipFooters = true;
	}

	//logging the security Information
	$seclog->debug ('########  Module -->  ' . $module . '  :: Action --> ' . $action . ' ::  UserID --> ' . $current_user->id . ' :: RecordID --> ' . $record . ' #######');
	if (!$skipSecurityCheck && $use_current_login) {
		require_once ('include/utils/UserInfoUtil.php');
		if (preg_match ('/Ajax/', $action)) {
			if ($_REQUEST['ajxaction'] == 'LOADRELATEDLIST') {
				$now_action = 'DetailView';
			} else {
				$now_action = vtlib_purify ($_REQUEST['Document']);
			}
		} else {
			$now_action = $action;
		}

		if (isset($_REQUEST['record']) && $_REQUEST['record'] != '') {
			$display = isPermitted ($module, $now_action, $_REQUEST['record']);
		} else if (($module != 'Settings') || (($now_action != 'fieldValidationsAjax') && ($now_action != 'sendEmail'))) {
			$display = isPermitted ($module, $now_action);
		} else {
			$display = 'yes';
		}
		$seclog->debug ('########### Pemitted ---> ' . $display . '  ##############');
	} else {
		$seclog->debug ('########### Pemitted ---> yes  ##############');
	}
	if ($display == "no") {
		echo "<link rel='stylesheet' type='text/css' href='themes/$theme/style.css'>";
		echo "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
		echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'><table border='0' cellpadding='5' cellspacing='0' width='98%'><tbody><tr><td rowspan='2' width='11%'><img src='" . vtiger_imageurl ('denied.gif', $theme) . "' ></td><td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>$app_strings[LBL_PERMISSION]</span></td></tr><tr><td class='small' align='right' nowrap='nowrap'><a href='javascript:window.history.back();'>$app_strings[LBL_GO_BACK]</a><br></td></tr></tbody></table></div>";
		echo "</td></tr></table>";
	} else if (($action != 'Modal') && (!vtlib_isModuleActive ($currentModule)) && (!isModuleRelatedPublic ($_REQUEST['srcmodule'], $_REQUEST['forfield'], $currentModule))) {
		if ($_SESSION['esInstancia'] == true) {
			include ('modules/store/modulonoactivo.php');
		} else {
			include ('modules/Settings/modulonoactivo.php');
		}
	} else {
		if (isset($_REQUEST['commonquery'])) {
			include ('CommonQuery.php');
		} else {
			if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
				$adbBak = clone $adb;
				unset($adb);
				$adb = conectaPlataformaHija ($_REQUEST['platdb'], DB_MAIN, 'plat_' . $_SESSION['plat']);
				if (!determinarPermisosModuloHijo ($_SESSION['plat_main'], $_REQUEST['module'], 'view')) {
					echo "Error accesando a modulo hijo";
				} else {
					include ($currentModuleFile);
				}
				unset($adb);
				$adb = clone $adbBak;
			} else {
				if (file_exists ($currentModuleFile)) {
					include ($currentModuleFile);
				}
				if (!empty($postContent)) {
					echo $postContent;
				}
			}
		}
	}

	//added to get the theme . This is a bad fix as we need to know where the problem lies yet
	if (isset($_SESSION['vtiger_authenticated_user_theme']) && $_SESSION['vtiger_authenticated_user_theme'] != '') {
		$theme = $_SESSION['vtiger_authenticated_user_theme'];
	} else {
		$theme = $default_theme;
	}

	if ((!$skipFooters) && ($action != "body") && ($action != $module . "Ajax") && ($action != "ActivityAjax")) {
		include ('modules/Vtiger/footer.php');
	}
