<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/database/PearDatabase.php');
	require_once ('include/ComboUtil.php');
	require_once ('include/DatabaseUtil.php');
	require_once ('include/FormValidationUtil.php');
	require_once ('include/events/SqlResultIterator.inc');
	require_once ('include/fields/CurrencyField.php');
	require_once ('include/fields/DateTimeField.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/comunesTareas.php');
	require_once ('include/utils/DetailViewUtils.php');
	require_once ('include/utils/InventoryUtils.php');
	require_once ('include/utils/ListViewUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/SearchUtils.php');
	require_once ('modules/Settings/comunesRelaciones.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	require_once ('vtlib/Vtiger/Language.php');

// For Migration status.
	define ("MIG_CHARSET_PHP_UTF8_DB_UTF8", 1);
	define ("MIG_CHARSET_PHP_NONUTF8_DB_NONUTF8", 2);
	define ("MIG_CHARSET_PHP_NONUTF8_DB_UTF8", 3);
	define ("MIG_CHARSET_PHP_UTF8_DB_NONUTF8", 4);

// For Customview status.
	define ("CV_STATUS_DEFAULT", 0);
	define ("CV_STATUS_PRIVATE", 1);
	define ("CV_STATUS_PENDING", 2);
	define ("CV_STATUS_PUBLIC", 3);

// For Restoration.
	define ("RB_RECORD_DELETED", 'delete');
	define ("RB_RECORD_INSERTED", 'insert');
	define ("RB_RECORD_UPDATED", 'update');

// For detailview blocks
	define ("FIELDS_BLOCK", 0);
	define ("TODO_TASKS_BLOCK", 1);
	define ("PROGRESS_BAR_BLOCK", 2);
	
	/**
	 * @param string $site_URL
	 *
	 * @return null
	 */
	function setBugSnag ($site_URL) {
		if (empty ($site_URL) || true) {
			return null;
		}
		require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
		$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
		$bugsnag->setAppVersion('1.0.0');
		if ($site_URL == 'https://apphome.platzillatest.com/') {
			$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
		} else if ($site_URL == 'https://app.platzilla.com/') {
			$bugsnag->setReleaseStage('https://app.platzilla.com/');
		} else {
			$bugsnag->setReleaseStage($site_URL);
		}
		$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
		// Agregado por EB para integrar BUGSNAG - 20200512
	}

//used in module file
	function get_user_array ($add_blank = true, $status = "Active", $assigned_user = "", $private = "") {
		global $log;
		$log->debug ("Entering get_user_array(" . $add_blank . "," . $status . "," . $assigned_user . "," . $private . ") method ...");
		global $current_user, $current_user_parent_role_seq;
		//Se introduce la validacion que el username no sea vacío.
		$condicionUserName = 'length(user_name) > 0 AND';
		if (isset($current_user) && $current_user->id != '') {
			$local_user = clone $current_user;
			require ('user_privileges/user_privileges.php');
			require ('user_privileges/sharing_privileges.php');
		}
		static $user_array = null;
		$module = $_REQUEST['module'];

		if ($user_array == null) {
			require_once ('include/database/PearDatabase.php');
			$db          = PearDatabase::getInstance ();
			$temp_result = Array ();
			// Including deleted vtiger_users for now.
			if (empty($status)) {
				$query  = "SELECT id, user_name FROM vtiger_users";
				$params = array ();
			} else {
				if ($private == 'private') {
					$log->debug ("Sharing is Private. Only the current user should be listed");
					$query  = "select id as id,user_name as user_name,first_name,last_name from vtiger_users where id=? and status='Active' union select vtiger_user2role.userid as id,vtiger_users.user_name as user_name ,
							  vtiger_users.first_name as first_name ,vtiger_users.last_name as last_name
							  from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like ? and status='Active' union
							  select shareduserid as id,vtiger_users.user_name as user_name ,
							  vtiger_users.first_name as first_name ,vtiger_users.last_name as last_name  from vtiger_tmp_write_user_sharing_per inner join vtiger_users on vtiger_users.id=vtiger_tmp_write_user_sharing_per.shareduserid where $condicionUserName status='Active' and vtiger_tmp_write_user_sharing_per.userid=? and vtiger_tmp_write_user_sharing_per.tabid=?";
					$params = array ($current_user->id, $current_user_parent_role_seq . "::%", $current_user->id, getTabid ($module));
				} else {
					$log->debug ("Sharing is Public. All vtiger_users should be listed");
					$query  = "SELECT id, user_name,first_name,last_name from vtiger_users WHERE $condicionUserName status=?";
					$params = array ($status);
				}
			}
			if (!empty($assigned_user)) {
				$query .= " OR id=?";
				array_push ($params, $assigned_user);
			}

			$query .= " order by user_name ASC";

			$result = $db->pquery ($query, $params, true, "Error filling in user array: ");

			if ($add_blank == true) {
				// Add in a blank row
				$temp_result[''] = '';
			}

			// Get the id and the name.
			while ($row = $db->fetchByAssoc ($result)) {
				$temp_result[ $row['id'] ] = getFullNameFromArray ('Users', $row);
			}

			$user_array = &$temp_result;
		}

		$log->debug ("Exiting get_user_array method ...");

		return $user_array;
	}

	function get_group_array ($add_blank = true, $status = "Active", $assigned_user = "", $private = "") {
		global $log;
		$log->debug ("Entering get_user_array(" . $add_blank . "," . $status . "," . $assigned_user . "," . $private . ") method ...");
		global $current_user;
		if (isset($current_user) && $current_user->id != '') {
			$local_user = clone $current_user;
			require ('user_privileges/user_privileges.php');
			require ('user_privileges/sharing_privileges.php');
		}
		static $group_array = null;
		$module = $_REQUEST['module'];

		if ($group_array == null) {
			require_once ('include/database/PearDatabase.php');
			$db          = PearDatabase::getInstance ();
			$temp_result = Array ();
			// Including deleted vtiger_users for now.
			$log->debug ("Sharing is Public. All vtiger_users should be listed");
			$query  = "SELECT groupid, groupname FROM vtiger_groups";
			$params = array ();

			if ($private == 'private') {

				$query .= " WHERE groupid=?";
				$params = array ($current_user->id);

				if (count ($current_user_groups) != 0) {
					$query .= " OR vtiger_groups.groupid in (" . generateQuestionMarks ($current_user_groups) . ")";
					array_push ($params, $current_user_groups);
				}
				$log->debug ("Sharing is Private. Only the current user should be listed");
				$query .= " union select vtiger_group2role.groupid as groupid,vtiger_groups.groupname as groupname from vtiger_group2role inner join vtiger_groups on vtiger_groups.groupid=vtiger_group2role.groupid inner join vtiger_role on vtiger_role.roleid=vtiger_group2role.roleid where vtiger_role.parentrole like ?";
				array_push ($params, $current_user_parent_role_seq . "::%");

				if (count ($current_user_groups) != 0) {
					$query .= " union select vtiger_groups.groupid as groupid,vtiger_groups.groupname as groupname from vtiger_groups inner join vtiger_group2rs on vtiger_groups.groupid=vtiger_group2rs.groupid where vtiger_group2rs.roleandsubid in (" . generateQuestionMarks ($parent_roles) . ")";
					array_push ($params, $parent_roles);
				}

				$query .= " union select sharedgroupid as groupid,vtiger_groups.groupname as groupname from vtiger_tmp_write_group_sharing_per inner join vtiger_groups on vtiger_groups.groupid=vtiger_tmp_write_group_sharing_per.sharedgroupid where vtiger_tmp_write_group_sharing_per.userid=?";
				array_push ($params, $current_user->id);

				$query .= " and vtiger_tmp_write_group_sharing_per.tabid=?";
				array_push ($params, getTabid ($module));
			}
			$query .= " order by groupname ASC";

			$result = $db->pquery ($query, $params, true, "Error filling in user array: ");

			if ($add_blank == true) {
				// Add in a blank row
				$temp_result[''] = '';
			}

			// Get the id and the name.
			while ($row = $db->fetchByAssoc ($result)) {
				$temp_result[ $row['groupid'] ] = $row['groupname'];
			}

			$group_array = &$temp_result;
		}

		$log->debug ("Exiting get_user_array method ...");
		return $group_array;
	}

	/** This function retrieves an application language file and returns the array of strings included in the $app_list_strings var.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 * If you are using the current language, do not call this function unless you are loading it for the first time */
	function return_app_list_strings_language ($language) {
		global $log;
		$log->debug ("Entering return_app_list_strings_language(" . $language . ") method ...");
		global $app_list_strings, $default_language, $log, $translation_string_prefix;
		$temp_app_list_strings = $app_list_strings;
		$language_used         = $language;

		@include ("include/language/$language.lang.php");
		if (!isset($app_list_strings)) {
			$log->warn ("Unable to find the application language file for language: " . $language);
			require ("include/language/$default_language.lang.php");
			$language_used = $default_language;
		}

		if (!isset($app_list_strings)) {
			$log->fatal ("Unable to load the application language file for the selected language($language) or the default language($default_language)");
			$log->debug ("Exiting return_app_list_strings_language method ...");
			return null;
		}

		$return_value     = $app_list_strings;
		$app_list_strings = $temp_app_list_strings;

		$log->debug ("Exiting return_app_list_strings_language method ...");
		return $return_value;
	}

	/**
	 * Retrieve the app_currency_strings for the required language.
	 */
	function return_app_currency_strings_language ($language) {
		global $log;
		$log->debug ("Entering return_app_currency_strings_language(" . $language . ") method ...");
		global $app_currency_strings, $default_language, $log, $translation_string_prefix;
		// Backup the value first
		$temp_app_currency_strings = $app_currency_strings;
		@include ("include/language/$language.lang.php");
		if (!isset($app_currency_strings)) {
			$log->warn ("Unable to find the application language file for language: " . $language);
			require ("include/language/$default_language.lang.php");
			$language_used = $default_language;
		}
		if (!isset($app_currency_strings)) {
			$log->fatal ("Unable to load the application language file for the selected language($language) or the default language($default_language)");
			$log->debug ("Exiting return_app_currency_strings_language method ...");
			return null;
		}
		$return_value = $app_currency_strings;

		// Restore the value back
		$app_currency_strings = $temp_app_currency_strings;

		$log->debug ("Exiting return_app_currency_strings_language method ...");
		return $return_value;
	}

	/** This function retrieves an application language file and returns the array of strings included.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 * If you are using the current language, do not call this function unless you are loading it for the first time */
	function return_application_language ($language) {
		global $log;
		$log->debug ("Entering return_application_language(" . $language . ") method ...");
		global $app_strings, $default_language, $log, $translation_string_prefix;
		$temp_app_strings = $app_strings;
		$language_used    = $language;

		checkFileAccessForInclusion ("include/language/$language.lang.php");
		@include ("include/language/$language.lang.php");
		if (!isset($app_strings)) {
			$log->warn ("Unable to find the application language file for language: " . $language);
			require ("include/language/$default_language.lang.php");
			$language_used = $default_language;
		}

		if (!isset($app_strings)) {
			$log->fatal ("Unable to load the application language file for the selected language($language) or the default language($default_language)");
			$log->debug ("Exiting return_application_language method ...");
			return null;
		}

		// If we are in debug mode for translating, turn on the prefix now!
		if ($translation_string_prefix) {
			foreach ($app_strings as $entry_key => $entry_value) {
				$app_strings[ $entry_key ] = $language_used . ' ' . $entry_value;
			}
		}

		if (isset($_SESSION['plat'])) { //Se incluye el archivo de idioma de la peticion
			$file = $_SESSION['plat'] . "/include/language/$language.lang.php";
			if (file_exists ($file)) {
				@include ($file);
			}

			if (isset($app_strings_plat)) {
				foreach ($app_strings_plat as $clave => $valor) {
					$app_strings[ $clave ] = $valor;
				}
			}
		}

		$return_value = $app_strings;
		$app_strings  = $temp_app_strings;

		$log->debug ("Exiting return_application_language method ...");
		return $return_value;
	}

	/** This function retrieves a module's language file and returns the array of strings included.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 * If you are in the current module, do not call this function unless you are loading it for the first time */
	function return_module_language ($language, $module, $cache = true) {
		global $log;
		$log->debug ("Entering return_module_language(" . $language . "," . $module . ") method ...");
		global $mod_strings, $default_language, $log, $currentModule, $translation_string_prefix;
		static $cachedModuleStrings = array ();

		if ($cache) {
			if (isset($cachedModuleStrings) && isset($cachedModuleStrings[ $module ])) {
				if (!empty($cachedModuleStrings[ $module ])) {
					$log->debug ("Exiting return_module_language method ...");
					return $cachedModuleStrings[ $module ];
				}
			}
		}

		$temp_mod_strings = $mod_strings;
		$language_used    = $language;

		//if(!isset($mod_strings)) {
		@include ("modules/$module/language/$language.lang.php");
		if (!isset($mod_strings)) {
			$log->warn ("Unable to find the module language file for language: " . $language . " and module: " . $module);
			if ($default_language == 'en_us') {
				@include ("modules/$module/language/$default_language.lang.php");
				$language_used = $default_language;
			} else {
				@include ("modules/$module/language/$default_language.lang.php");
				if (!isset($mod_strings)) {
					@include ("modules/$module/language/en_us.lang.php");
					$language_used = 'en_us';
				} else {
					$language_used = $default_language;
				}
			}
		}
		//}

		@include ($_SESSION['plat'] . "/modules/$module/language/$language.lang.php"); //Se intenta primero con el personal de la plataforma

		if (!isset($mod_strings)) {
			$log->warn ("Unable to find the module language file for language: " . $language . " and module: " . $module);

			if ($default_language == 'en_us') {
				@include ($_SESSION['plat'] . "/modules/$module/language/$default_language.lang.php");
				$language_used = $default_language;
			} else {
				@include ($_SESSION['plat'] . "/modules/$module/language/$default_language.lang.php");
				if (!isset($mod_strings)) {
					@include ($_SESSION['plat'] . "/modules/$module/language/en_us.lang.php");
					$language_used = 'en_us';
				} else {
					$language_used = $default_language;
				}
			}
		}

		if (!isset($mod_strings)) {
			$log->fatal ("Unable to load the module($module) language file for the selected language($language) or the default language($default_language)");
			$log->debug ("Exiting return_module_language method ...");
			return null;
		}

		// If we are in debug mode for translating, turn on the prefix now!
		if ($translation_string_prefix) {
			foreach ($mod_strings as $entry_key => $entry_value) {
				$mod_strings[ $entry_key ] = $language_used . ' ' . $entry_value;
			}
		}

		$return_value = $mod_strings;
		$mod_strings  = $temp_mod_strings;

		$log->debug ("Exiting return_module_language method ...");
		$cachedModuleStrings[ $module ] = $return_value;
		return $return_value;
	}

	/*This function returns the mod_strings for the current language and the specified module
*/

	function return_specified_module_language ($language, $module) {
		global $log;
		global $default_language, $translation_string_prefix;

		@include ($_SESSION['plat'] . "/modules/$module/language/$language.lang.php");
		if (!isset($mod_strings)) {
			$log->warn ("Unable to find the module language file for language: " . $language . " and module: " . $module);
			@include ($_SESSION['plat'] . "/modules/$module/language/$default_language.lang.php");

			$language_used = $default_language;
		}

		if (!isset($mod_strings)) {
			@include ("modules/$module/language/$language.lang.php");
			if (!isset($mod_strings)) {
				$log->warn ("Unable to find the module language file for language: " . $language . " and module: " . $module);
				@include ("modules/$module/language/$default_language.lang.php");
				$language_used = $default_language;
			}
		}

		if (!isset($mod_strings)) {
			$log->fatal ("Unable to load the module($module) language file for the selected language($language) or the default language($default_language)");
			$log->debug ("Exiting return_module_language method ...");
			return null;
		}

		$return_value = $mod_strings;

		$log->debug ("Exiting return_module_language method ...");
		return $return_value;
	}

	/**
	 * Return an array of directory names.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 */
	function get_themes () {
		global $log;
		$log->debug ("Entering get_themes() method ...");
		if ($dir = @opendir ("./themes")) {
			while (($file = readdir ($dir)) !== false) {
				if ($file != ".." && $file != "." && $file != "CVS" && $file != "Attic" && $file != "akodarkgem" && $file != "bushtree" && $file != "coolblue" && $file != "Amazon" && $file != "busthree" && $file != "Aqua" && $file != "nature" && $file != "orange" && $file != "blue") {
					if (is_dir ("./themes/" . $file)) {
						if (!($file[0] == '.')) {
							// set the initial theme name to the filename
							$name = $file;

							// if there is a configuration class, load that.
							if (is_file ("./themes/$file/config.php")) {
								require_once ("./themes/$file/config.php");
							}

							if (is_file ("./themes/$file/style.css")) {
								$filelist[ $file ] = $name;
							}
						}
					}
				}
			}
			closedir ($dir);
		}

		ksort ($filelist);
		$log->debug ("Exiting get_themes method ...");
		return $filelist;
	}

	/** Function to set default varibles on to the global variable
	 *
	 * @param $defaults -- default values:: Type array
	 */
	function set_default_config (&$defaults) {
		global $log;
		$log->debug ("Entering set_default_config(" . $defaults . ") method ...");

		foreach ($defaults as $name => $value) {
			if (!isset($GLOBALS[ $name ])) {
				$GLOBALS[ $name ] = $value;
			}
		}
		$log->debug ("Exiting set_default_config method ...");
	}

	/** Function to convert the given string to html
	 *
	 * @param $string -- string:: Type string
	 * @param $ecnode -- boolean:: Type boolean
	 * @returns $string -- string:: Type string
	 *
	 */
	function to_html ($string, $encode = true) {
		global $default_charset;
		$search = '';

		ini_set ('memory_limit', '2048M');
		$action = isset ($_REQUEST ['action']) ? $_REQUEST ['action'] : null;
		if (isset($_REQUEST['search'])) {
			$search = $_REQUEST['search'];
		}

		$doconvert = false;

		if (!isset($_REQUEST['file'])) {
			$_REQUEST['file'] = '';
		}

		if (!isset ($_REQUEST ['module'])) {
			$_REQUEST ['module'] = '';
		}

		if (($_REQUEST ['module'] != 'Settings') && ($_REQUEST ['file'] != 'ListView') && ($_REQUEST ['module'] != 'Portal') && ($_REQUEST ['module'] != "Reports"))// && $_REQUEST['module'] != 'Emails')
		{
			$ajax_action = $_REQUEST['module'] . 'Ajax';
		}

		if (is_string ($string)) {
			if ($action != 'CustomView' && $action != 'Export' && $action != $ajax_action && $action != 'LeadConvertToEntities' && $action != 'CreatePDF' && $action != 'ConvertAsFAQ' && $_REQUEST['module'] != 'Dashboard' && $action != 'CreateSOPDF' && $action != 'SendPDFMail' && (!isset($_REQUEST['submode']))) {
				$doconvert = true;
			} else if ($search == true) {
				// Fix for tickets #4647, #4648. Conversion required in case of search results also.
				$doconvert = true;
			}
			if ($doconvert == true) {
				if (strtolower ($default_charset) == 'utf-8') {
					$string = htmlentities ($string, ENT_QUOTES, $default_charset);
				} else {
					$string = preg_replace (array ('/</', '/>/', '/"/'), array ('&lt;', '&gt;', '&quot;'), $string);
				}
			}
		}

		//$log->debug("Exiting to_html method ...");
		return $string;
	}

	/** Function to get the tablabel for a given id
	 *
	 * @param $tabid -- tab id:: Type integer
	 * @returns $string -- string:: Type string
	 */
	function getTabname ($tabid) {
		global $log;
		$log->debug ("Entering getTabname(" . $tabid . ") method ...");
		$log->info ("tab id is " . $tabid);
		global $adb;
		$sql     = "SELECT tablabel FROM vtiger_tab WHERE tabid=?";
		$result  = $adb->pquery ($sql, array ($tabid));
		$tabname = $adb->query_result ($result, 0, "tablabel");
		$log->debug ("Exiting getTabname method ...");
		return $tabname;
	}

	/** Function to get the tab module name for a given id
	 *
	 * @param $tabid -- tab id:: Type integer
	 * @returns $string -- string:: Type string
	 *
	 */
	function getTabModuleName ($tabid) {
		global $log;
		$log->debug ("Entering getTabModuleName(" . $tabid . ") method ...");

		// Lookup information in cache first
		$tabname = VTCacheUtils::lookupModulename ($tabid);
		if ($tabname === false) {
			/*if (file_exists('tabdata.php') && (filesize('tabdata.php') != 0)) {
			include('tabdata.php');*/

			//[ TT11181 ] Ajustes Menú Izquierdo (Módulos/APP´s) Producto Interno (Platzilla)
			//DM 22/06/2016
			// Se consulta por la variable en session
			if ((isset($_SESSION['authenticated_user_menu']['tabdata']) && count ($_SESSION['authenticated_user_menu']['tabdata']) > 0)) {

				$tab_info_array = $_SESSION['authenticated_user_menu']['tabdata']['tab_info_array'];

				$tabname = array_search ($tabid, $tab_info_array);

				if ($tabname == false) {
					global $adb;
					$sql     = "SELECT name FROM vtiger_tab WHERE tabid=?";
					$result  = $adb->pquery ($sql, array ($tabid));
					$tabname = $adb->query_result ($result, 0, "name");
				}

				// Update information to cache for re-use
				VTCacheUtils::updateTabidInfo ($tabid, $tabname);
			} else {
				$log->info ("tab id is " . $tabid);
				global $adb;
				$sql     = "SELECT name FROM vtiger_tab WHERE tabid=?";
				$result  = $adb->pquery ($sql, array ($tabid));
				$tabname = $adb->query_result ($result, 0, "name");

				// Update information to cache for re-use
				VTCacheUtils::updateTabidInfo ($tabid, $tabname);
			}
		}
		$log->debug ("Exiting getTabModuleName method ...");
		return $tabname;
	}

	/** Function to get column fields for a given module
	 *
	 * @param $module -- module:: Type string
	 * @returns $column_fld -- column field :: Type array
	 *
	 */
	function getColumnFields ($module) {
		global $log;
		$log->debug ("Entering getColumnFields(" . $module . ") method ...");
		$log->debug ("in getColumnFields " . $module);

		// Lookup in cache for information
		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module ($module);
		if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {//Si es una solicitud desde plataforma hija se obvia el cache
			$cachedModuleFields = false;
		}

		if ($cachedModuleFields === false) {
			global $adb;
			$tabid = getTabid ($module);
			if ($module == 'Calendar') {
				$tabid = array ('9', '16');
			}

			// Let us pick up all the fields first so that we can cache information
			$sql      = "SELECT tabid, fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata, presence
		FROM vtiger_field WHERE tabid IN (" . generateQuestionMarks ($tabid) . ")";
			$result   = $adb->pquery ($sql, array ($tabid));
			$noofrows = $adb->num_rows ($result);

			if ($noofrows) {
				while ($resultrow = $adb->fetch_array ($result)) {
					// Update information to cache for re-use
					VTCacheUtils::updateFieldInfo (
						$resultrow['tabid'], $resultrow['fieldname'], $resultrow['fieldid'],
						$resultrow['fieldlabel'], $resultrow['columnname'], $resultrow['tablename'],
						$resultrow['uitype'], $resultrow['typeofdata'], $resultrow['presence']
					);
				}
			}

			// For consistency get information from cache
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module ($module);
		}
		if ($module == 'Calendar') {
			$cachedEventsFields = VTCacheUtils::lookupFieldInfo_Module ('Events');
			if ($cachedModuleFields == false) {
				$cachedModuleFields = $cachedEventsFields;
			} else {
				$cachedModuleFields = array_merge (
					(is_array ($cachedModuleFields)) ? $cachedModuleFields : array (),
					(is_array ($cachedEventsFields)) ? $cachedEventsFields : array ()
				);
			}
		}

		$column_fld = array ();
		if ($cachedModuleFields) {
			foreach ($cachedModuleFields as $fieldinfo) {
				$column_fld[ $fieldinfo['fieldname'] ] = '';
			}
		}
		$log->debug ("Exiting getColumnFields method ...");
		return $column_fld;
	}

	/** Function to get a users's mail id
	 *
	 * @param $userid -- userid :: Type integer
	 * @returns $email -- email :: Type string
	 *
	 */
	function getUserEmail ($userid) {
		global $log;
		$log->debug ("Entering getUserEmail(" . $userid . ") method ...");
		$log->info ("in getUserEmail " . $userid);

		global $adb;
		if ($userid != '') {
			$sql    = "SELECT email1 FROM vtiger_users WHERE id=?";
			$result = $adb->pquery ($sql, array ($userid));
			$email  = $adb->query_result ($result, 0, "email1");
		}
		$log->debug ("Exiting getUserEmail method ...");
		return $email;
	}

//outlook security
	function getUserId_Ol ($username) {
		global $log;
		$log->debug ("Entering getUserId_Ol(" . $username . ") method ...");
		$log->info ("in getUserId_Ol " . $username);

		global $adb;
		$sql      = "SELECT id FROM vtiger_users WHERE user_name=?";
		$result   = $adb->pquery ($sql, array ($username));
		$num_rows = $adb->num_rows ($result);
		if ($num_rows > 0) {
			$user_id = $adb->query_result ($result, 0, "id");
		} else {
			$user_id = 0;
		}
		$log->debug ("Exiting getUserId_Ol method ...");
		return $user_id;
	}

//outlook security
	function getActionid ($action) {
		global $log;
		$log->debug ("Entering getActionid(" . $action . ") method ...");
		global $adb;
		$log->info ("get Actionid " . $action);
		$actionid = '';

		/*if(file_exists('tabdata.php') && (filesize('tabdata.php') != 0))
	{
		include('tabdata.php');
		$actionid= $action_id_array[$action];
	}*/

		//[ TT11181 ] Ajustes Menú Izquierdo (Módulos/APP´s) Producto Interno (Platzilla)
		//DM 28/06/2016
		//Leyendo de variable de session. No se escriben los archivos tabdata.php
		if (isset($_SESSION['authenticated_user_menu']['tabdata']) && isset($_SESSION['authenticated_user_menu']['tabdata']['action_id_array'])
			&& count ($_SESSION['authenticated_user_menu']['tabdata']['action_id_array'] > 0)
		) {

			$action_id_array = $_SESSION['authenticated_user_menu']['tabdata']['action_id_array'];
			$actionid        = $action_id_array[ $action ];
		} else {
			$query    = "SELECT * FROM vtiger_actionmapping WHERE actionname=?";
			$result   = $adb->pquery ($query, array ($action));
			$actionid = $adb->query_result ($result, 0, 'actionid');
		}
		$log->info ("action id selected is " . $actionid);
		$log->debug ("Exiting getActionid method ...");
		return $actionid;
	}

	/** Function to get a action for a given action id
	 *
	 * @param $action id -- action id :: Type integer
	 * @returns $actionname-- action name :: Type string
	 */
	function getActionname ($actionid) {
		global $log;
		$log->debug ("Entering getActionname(" . $actionid . ") method ...");
		global $adb;

		$actionname = '';

		/*if (file_exists('tabdata.php') && (filesize('tabdata.php') != 0))
	{
		include('tabdata.php');
		$actionname= $action_name_array[$actionid];
	}*/

		//[ TT11181 ] Ajustes Menú Izquierdo (Módulos/APP´s) Producto Interno (Platzilla)
		//DM 28/06/2016
		//Leyendo de variable de session. No se escriben los archivos tabdata.php
		if (isset($_SESSION['authenticated_user_menu']['tabdata']) && isset($_SESSION['authenticated_user_menu']['tabdata']['action_name_array'])
			&& count ($_SESSION['authenticated_user_menu']['tabdata']['action_name_array'] > 0)
		) {

			$action_name_array = $_SESSION['authenticated_user_menu']['tabdata']['action_name_array'];
			$actionname        = $action_name_array[ $actionid ];
		} else {

			$query      = "SELECT * FROM vtiger_actionmapping WHERE actionid=? AND securitycheck=0";
			$result     = $adb->pquery ($query, array ($actionid));
			$actionname = $adb->query_result ($result, 0, "actionname");
		}
		$log->debug ("Exiting getActionname method ...");
		return $actionname;
	}

	/** Function to get a user id or group id for a given entity
	 *
	 * @param $record -- entity id :: Type integer
	 * @returns $ownerArr -- owner id :: Type array
	 */
	function getRecordOwnerId ($record) {
		global $log;
		$log->debug ("Entering getRecordOwnerId(" . $record . ") method ...");
		global $adb;
		$ownerArr = Array ();
		$query    = "SELECT smownerid FROM vtiger_crmentity WHERE crmid = ?";
		$result   = $adb->pquery ($query, array ($record));
		if ($adb->num_rows ($result) > 0) {
			$ownerId    = $adb->query_result ($result, 0, 'smownerid');
			$sql_result = $adb->pquery ("SELECT count(*) AS count FROM vtiger_users WHERE id = ?", array ($ownerId));
			if ($adb->query_result ($sql_result, 0, 'count') > 0) {
				$ownerArr['Users'] = $ownerId;
			} else {
				$ownerArr['Groups'] = $ownerId;
			}
		}
		$log->debug ("Exiting getRecordOwnerId method ...");
		return $ownerArr;
	}

	/** Function to insert value to profile2field table
	 *
	 * @param $fld_module -- field module :: Type string
	 * @param $profileid -- profileid :: Type integer
	 * @returns $result -- result :: Type string
	 */
	function getProfile2FieldList ($fld_module, $profileid) {
		global $log;
		$log->debug ("Entering getProfile2FieldList(" . $fld_module . "," . $profileid . ") method ...");
		$log->info ("in getProfile2FieldList " . $fld_module . ' vtiger_profile id is  ' . $profileid);

		global $adb;
		$tabid = getTabid ($fld_module);

		$query  = "SELECT vtiger_profile2field.visible,vtiger_field.* FROM vtiger_profile2field INNER JOIN vtiger_field ON vtiger_field.fieldid=vtiger_profile2field.fieldid WHERE vtiger_profile2field.profileid=? AND vtiger_profile2field.tabid=? AND vtiger_field.presence IN (0,1,2)";
		$result = $adb->pquery ($query, array ($profileid, $tabid));
		$log->debug ("Exiting getProfile2FieldList method ...");
		return $result;
	}

	/** Function to insert value to profile2fieldPermissions table
	 *
	 * @param $fld_module -- field module :: Type string
	 * @param $profileid -- profileid :: Type integer
	 * @returns $return_data -- return_data :: Type string
	 */
	function getProfile2FieldPermissionList ($fld_module, $profileid) {
		global $log;
		$log->debug ("Entering getProfile2FieldPermissionList(" . $fld_module . "," . $profileid . ") method ...");
		$log->info ("in getProfile2FieldList " . $fld_module . ' vtiger_profile id is  ' . $profileid);

		// Cache information to re-use
		static $_module_fieldpermission_cache = array ();

		if (!isset($_module_fieldpermission_cache[ $fld_module ])) {
			$_module_fieldpermission_cache[ $fld_module ] = array ();
		}

		// Lookup cache first
		$return_data = VTCacheUtils::lookupProfile2FieldPermissionList ($fld_module, $profileid);

		if ($return_data === false) {

			$return_data = array ();

			global $adb;
			$tabid = getTabid ($fld_module);

			$query = "SELECT vtiger_profile2field.visible, vtiger_profile2field.readonly, vtiger_field.fieldlabel, vtiger_field.uitype,
			vtiger_field.fieldid, vtiger_field.displaytype, vtiger_field.typeofdata
			FROM vtiger_profile2field INNER JOIN vtiger_field ON vtiger_field.fieldid=vtiger_profile2field.fieldid
			WHERE vtiger_profile2field.profileid=? AND vtiger_profile2field.tabid=? AND vtiger_field.presence IN (0,2)";

			$qparams = array ($profileid, $tabid);
			$result  = $adb->pquery ($query, $qparams);

			for ($i = 0; $i < $adb->num_rows ($result); $i++) {
				$return_data[] = array (
					$adb->query_result ($result, $i, "fieldlabel"),
					$adb->query_result ($result, $i, "visible"), // From vtiger_profile2field.visible
					$adb->query_result ($result, $i, "uitype"),
					$adb->query_result ($result, $i, "readonly"),
					$adb->query_result ($result, $i, "fieldid"),
					$adb->query_result ($result, $i, "displaytype"),
					$adb->query_result ($result, $i, "typeofdata"),
				);
			}

			// Update information to cache for re-use
			VTCacheUtils::updateProfile2FieldPermissionList ($fld_module, $profileid, $return_data);
		}

		$log->debug ("Exiting getProfile2FieldPermissionList method ...");
		return $return_data;
	}

	/** Function to insert value to profile2fieldPermissions table
	 *
	 * @param $fld_module -- field module :: Type string
	 * @param $profileid -- profileid :: Type integer
	 * @returns $return_data -- return_data :: Type string
	 */
	function getProfile2ModuleFieldPermissionList ($fld_module, $profileid) {
		global $log;
		$log->debug ("Entering getProfile2ModuleFieldPermissionList(" . $fld_module . "," . $profileid . ") method ...");
		$log->info ("in getProfile2ModuleFieldList " . $fld_module . ' vtiger_profile id is  ' . $profileid);

		// Cache information to re-use
		static $_module_fieldpermission_cache = array ();

		if (!isset($_module_fieldpermission_cache[ $fld_module ])) {
			$_module_fieldpermission_cache[ $fld_module ] = array ();
		}

		$return_data = array ();

		global $adb;
		$tabid = getTabid ($fld_module);

		$query   = "SELECT vtiger_profile2tab.tabid, vtiger_profile2tab.permissions, vtiger_field.fieldlabel, vtiger_field.uitype,
		vtiger_field.fieldid, vtiger_field.displaytype, vtiger_field.typeofdata
		FROM vtiger_profile2tab INNER JOIN vtiger_field ON vtiger_field.tabid=vtiger_profile2tab.tabid
		WHERE vtiger_profile2tab.profileid=? AND vtiger_profile2tab.tabid=? AND vtiger_field.presence IN (0,2)";
		$qparams = array ($profileid, $tabid);
		$result  = $adb->pquery ($query, $qparams);

		for ($i = 0; $i < $adb->num_rows ($result); $i++) {
			$fieldid       = $adb->query_result ($result, $i, "fieldid");
			$checkentry    = $adb->pquery ("SELECT 1 FROM vtiger_profile2field WHERE profileid=? AND tabid=? AND fieldid =?", array ($profileid, $tabid, $fieldid));
			$visible_value = 0;
			$readOnlyValue = 0;
			if ($adb->num_rows ($checkentry) == 0) {
				$sql11 = "INSERT INTO vtiger_profile2field VALUES(?,?,?,?,?)";
				$adb->pquery ($sql11, array ($profileid, $tabid, $fieldid, $visible_value, $readOnlyValue));
			}

			$sql    = "SELECT vtiger_profile2field.visible, vtiger_profile2field.readonly FROM vtiger_profile2field WHERE fieldid=? AND tabid=? AND profileid=?";
			$params = array ($fieldid, $tabid, $profileid);
			$res    = $adb->pquery ($sql, $params);

			$return_data[] = array (
				$adb->query_result ($result, $i, "fieldlabel"),
				$adb->query_result ($res, 0, "visible"), // From vtiger_profile2field.visible
				$adb->query_result ($result, $i, "uitype"),
				$adb->query_result ($res, 0, "readonly"), // From vtiger_profile2field.readonly
				$adb->query_result ($result, $i, "fieldid"),
				$adb->query_result ($result, $i, "displaytype"),
				$adb->query_result ($result, $i, "typeofdata"),
			);
		}

		$log->debug ("Exiting getProfile2ModuleFieldPermissionList method ...");
		return $return_data;
	}

	/** Function to getProfile2allfieldsListinsert value to profile2fieldPermissions table
	 *
	 * @param $mod_array -- mod_array :: Type string
	 * @param $profileid -- profileid :: Type integer
	 * @returns $profilelist -- profilelist :: Type string
	 */
	function getProfile2AllFieldList ($mod_array, $profileid) {
		global $log;
		$log->debug ("Entering getProfile2AllFieldList(" . $mod_array . "," . $profileid . ") method ...");
		$log->info ("in getProfile2AllFieldList vtiger_profile id is " . $profileid);

		$profilelist = array ();
		for ($i = 0; $i < count ($mod_array); $i++) {
			$profilelist[ key ($mod_array) ] = getProfile2ModuleFieldPermissionList (key ($mod_array), $profileid);
			next ($mod_array);
		}
		$log->debug ("Exiting getProfile2AllFieldList method ...");
		return $profilelist;
	}

	/** Function to getdefaultfield organisation list for a given module
	 *
	 * @param $fld_module -- module name :: Type string
	 * @returns $result -- string :: Type object
	 */
	function getDefOrgFieldList ($fld_module) {
		global $log;
		$log->debug ("Entering getDefOrgFieldList(" . $fld_module . ") method ...");
		$log->info ("in getDefOrgFieldList " . $fld_module);

		global $adb;
		$tabid = getTabid ($fld_module);
		//Ojo el caparazon testtm no tiene la parte de presence
		$query   = "SELECT vtiger_def_org_field.visible,vtiger_field.* FROM vtiger_def_org_field INNER JOIN vtiger_field ON vtiger_field.fieldid=vtiger_def_org_field.fieldid WHERE vtiger_def_org_field.tabid=? AND vtiger_field.presence IN (0,2)";
		$qparams = array ($tabid);
		$result  = $adb->pquery ($query, $qparams);
		$log->debug ("Exiting getDefOrgFieldList method ...");
		return $result;
	}

	/** Function to getQuickCreate for a given tabid
	 *
	 * @param $tabid -- tab id :: Type string
	 * @param $actionid -- action id :: Type integer
	 * @returns $QuickCreateForm -- QuickCreateForm :: Type boolean
	 */
	function ChangeStatus ($status, $activityid, $activity_mode = '') {
		global $log;
		$log->debug ("Entering ChangeStatus(" . $status . "," . $activityid . "," . $activity_mode . "='') method ...");
		$log->info ("in ChangeStatus " . $status . ' vtiger_activityid is  ' . $activityid);

		global $adb;
		if ($activity_mode == 'Task') {
			$query = "UPDATE vtiger_activity SET status=? WHERE activityid = ?";
		} elseif ($activity_mode == 'Events') {
			$query = "UPDATE vtiger_activity SET eventstatus=? WHERE activityid = ?";
		}
		if ($query) {
			$adb->pquery ($query, array ($status, $activityid));
		}
		$log->debug ("Exiting ChangeStatus method ...");
	}

	/**     Function to get the vtiger_table name from 'field' vtiger_table for the input vtiger_field based on the module
	 *
	 * @param  : string $module - current module value
	 * @param  : string $fieldname - vtiger_fieldname to which we want the vtiger_tablename
	 *
	 * @return : string $tablename - vtiger_tablename in which $fieldname is a column, which is retrieved from 'field' vtiger_table per $module basis
	 */
	function getTableNameForField ($module, $fieldname) {
		global $log;
		$log->debug ("Entering getTableNameForField(" . $module . "," . $fieldname . ") method ...");
		global $adb;
		$tabid = getTabid ($module);
		//Asha
		if ($module == 'Calendar') {
			$tabid = array ('9', '16');
		}
		$sql = "SELECT tablename FROM vtiger_field WHERE tabid IN (" . generateQuestionMarks ($tabid) . ") AND vtiger_field.presence IN (0,2) AND columnname LIKE ?";
		$res = $adb->pquery ($sql, array ($tabid, '%' . $fieldname . '%'));

		$tablename = '';
		if ($adb->num_rows ($res) > 0) {
			$tablename = $adb->query_result ($res, 0, 'tablename');
		}

		$log->debug ("Exiting getTableNameForField method ...");
		return $tablename;
	}

	/** Function to get parent record owner
	 *
	 * @param $tabid -- tabid :: Type integer
	 * @param $parModId -- parent module id :: Type integer
	 * @param $record_id -- record id :: Type integer
	 * @returns $parentRecOwner -- parentRecOwner:: Type integer
	 */
	function getParentRecordOwner ($tabid, $parModId, $record_id) {
		global $log;
		$log->debug ("Entering getParentRecordOwner(" . $tabid . "," . $parModId . "," . $record_id . ") method ...");
		$parentRecOwner = Array ();
		$parentTabName  = getTabname ($parModId);
		$relTabName     = getTabname ($tabid);
		$fn_name        = "get" . $relTabName . "Related" . $parentTabName;
		$ent_id         = $fn_name($record_id);
		if ($ent_id != '') {
			$parentRecOwner = getRecordOwnerId ($ent_id);
		}
		$log->debug ("Exiting getParentRecordOwner method ...");
		return $parentRecOwner;
	}

	/**   Function to get the Graph and vtiger_table format for a particular date
	 * based upon the period
	 * Portions created by vtiger are Copyright (C) vtiger.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 */
	function Graph_n_table_format ($period_type, $date_value) {
		global $log;
		$log->debug ("Entering Graph_n_table_format(" . $period_type . "," . $date_value . ") method ...");
		$date_val = explode ("-", $date_value);
		if ($period_type == "month")   //to get the vtiger_table format dates
		{
			$table_format = date ("j", mktime (0, 0, 0, date ($date_val[1]), (date ($date_val[2])), date ($date_val[0])));
			$graph_format = date ("D", mktime (0, 0, 0, date ($date_val[1]), (date ($date_val[2])), date ($date_val[0])));
		} else if ($period_type == "week") {
			$table_format = date ("d/m", mktime (0, 0, 0, date ($date_val[1]), (date ($date_val[2])), date ($date_val[0])));
			$graph_format = date ("D", mktime (0, 0, 0, date ($date_val[1]), (date ($date_val[2])), date ($date_val[0])));
		} else if ($period_type == "yday") {
			$table_format = date ("j", mktime (0, 0, 0, date ($date_val[1]), (date ($date_val[2])), date ($date_val[0])));
			$graph_format = $table_format;
		}
		$values = array ($graph_format, $table_format);
		$log->debug ("Exiting Graph_n_table_format method ...");
		return $values;
	}

	/**
	 * Function to get user image for a given user
	 *
	 * @param integer $id -- user id :: Type integer
	 *
	 * @return string image_name -- image name:: Type string
	 */
	function getUserImageName ($id) {
		global $adb;
		$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE id=?', array ($id));
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return null;
		}

		$row = $adb->fetchByAssoc ($result, -1, false);
		return !empty ($row ['imagename']) ? "{$_SESSION ['plat']}/user_images/{$row ['imagename']}" : null;
	}

	/** Function to get all user images for displaying it in listview
	 * @returns $image_name -- image name:: Type array
	 */
	function getUserImageNames () {
		global $log;
		$log->debug ("Entering getUserImageNames() method ...");
		global $adb;
		$query      = "SELECT imagename FROM vtiger_users WHERE deleted=0";
		$result     = $adb->pquery ($query, array ());
		$image_name = array ();
		for ($i = 0; $i < $adb->num_rows ($result); $i++) {
			if ($adb->query_result ($result, $i, "imagename") != '') {
				$image_name[] = $adb->query_result ($result, $i, "imagename");
			}
		}
		$log->debug ("Inside getUserImageNames.");
		if (count ($image_name) > 0) {
			$log->debug ("Exiting getUserImageNames method ...");
			return $image_name;
		}
	}

	/** Function to check whether user has opted for internal mailer
	 * @returns $int_mailer -- int mailer:: Type boolean
	 */
	function useInternalMailer () {
		global $current_user, $adb;
		$result = $adb->pquery ('SELECT int_mailer FROM vtiger_mail_accounts WHERE user_id=?', array ($current_user->id));
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return 0;
		}
		return $adb->query_result ($result, 0, 'int_mailer');
	}

	/**
	 * the function is like unescape in javascript
	 * added by dingjianting on 2006-10-1 for picklist editor
	 */
	function utf8RawUrlDecode ($source) {
		global $default_charset;
		$decodedStr = "";
		$pos        = 0;
		$len        = strlen ($source);
		while ($pos < $len) {
			$charAt = substr ($source, $pos, 1);
			if ($charAt == '%') {
				$pos++;
				$charAt = substr ($source, $pos, 1);
				if ($charAt == 'u') {
					// we got a unicode character
					$pos++;
					$unicodeHexVal = substr ($source, $pos, 4);
					$unicode       = hexdec ($unicodeHexVal);
					$entity        = "&#" . $unicode . ';';
					$decodedStr .= utf8_encode ($entity);
					$pos += 4;
				} else {
					// we have an escaped ascii character
					$hexVal = substr ($source, $pos, 2);
					$decodedStr .= chr (hexdec ($hexVal));
					$pos += 2;
				}
			} else {
				$decodedStr .= $charAt;
				$pos++;
			}
		}
		if (strtolower ($default_charset) == 'utf-8') {
			return html_to_utf8 ($decodedStr);
		} else {
			return $decodedStr;
		}
		//return html_to_utf8($decodedStr);
	}

	/**
	 *simple HTML to UTF-8 conversion:
	 */
	function html_to_utf8 ($data) {
		return preg_replace ("/\\&\\#([0-9]{3,10})\\;/e", '_html_to_utf8("\\1")', $data);
	}

	function _html_to_utf8 ($data) {
		if ($data > 127) {
			$i = 5;
			while (($i--) > 0) {
				if ($data != ($a = $data % ($p = pow (64, $i)))) {
					$ret = chr (base_convert (str_pad (str_repeat (1, $i + 1), 8, "0"), 2, 10) + (($data - $a) / $p));
					for ($i; $i > 0; $i--) {
						$ret .= chr (128 + ((($data % pow (64, $i)) - ($data % ($p = pow (64, $i - 1)))) / $p));
					}
					break;
				}
			}
		} else {
			$ret = "&#$data;";
		}
		return $ret;
	}

// Return Question mark
	function _questionify ($v) {
		return "?";
	}

	/**
	 * Function to generate question marks for a given list of items
	 */
	function generateQuestionMarks ($items_list) {
		// array_map will call the function specified in the first parameter for every element of the list in second parameter
		if (is_array ($items_list)) {
			return implode (",", array_map ("_questionify", $items_list));
		} else {
			return implode (",", array_map ("_questionify", explode (",", $items_list)));
		}
	}

	/**
	 * Function to find the UI type of a field based on the uitype id
	 */
	function is_uitype ($uitype, $reqtype) {
		$ui_type_arr = array (
			'_date_'       => array (5, 6, 23, 70),
			'_picklist_'   => array (15, 16, 52, 53, 54, 55, 59, 62, 63, 66, 68, 76, 77, 78, 80, 98, 101, 115, 357),
			'_users_list_' => array (52),
		);

		if ($ui_type_arr[ $reqtype ] != null) {
			if (in_array ($uitype, $ui_type_arr[ $reqtype ])) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Function to escape quotes
	 *
	 * @param $value - String in which single quotes have to be replaced.
	 *
	 * @return Input string with single quotes escaped.
	 */
	function escape_single_quotes ($value) {
		if (isset($value)) {
			$value = str_replace ("'", "\'", $value);
		}
		return $value;
	}

	/**
	 * Function to format the input value for SQL like clause.
	 *
	 * @param $str - Input string value to be formatted.
	 * @param $flag - By default set to 0 (Will look for cases %string%).
	 *                If set to 1 - Will look for cases %string.
	 *                If set to 2 - Will look for cases string%.
	 *
	 * @return String formatted as per the SQL like clause requirement
	 */
	function formatForSqlLike ($str, $flag = 0, $is_field = false) {
		global $adb;
		if (isset($str)) {
			if ($is_field == false) {
				$str = str_replace ('%', '\%', $str);
				$str = str_replace ('_', '\_', $str);
				if ($flag == 0) {
					$str = '%' . $str . '%';
				} elseif ($flag == 1) {
					$str = '%' . $str;
				} elseif ($flag == 2) {
					$str = $str . '%';
				}
			} else {
				if ($flag == 0) {
					$str = 'concat("%",' . $str . ',"%")';
				} elseif ($flag == 1) {
					$str = 'concat("%",' . $str . ')';
				} elseif ($flag == 2) {
					$str = 'concat(' . $str . ',"%")';
				}
			}
		}
		return $adb->sql_escape_string ($str);
	}

	/** Function to convert a given time string to Minutes */
	function ConvertToMinutes ($time_string) {
		$interval         = split (' ', $time_string);
		$interval_minutes = intval ($interval[0]);
		$interval_string  = strtolower ($interval[1]);
		if ($interval_string == 'hour' || $interval_string == 'hours') {
			$interval_minutes = $interval_minutes * 60;
		} elseif ($interval_string == 'day' || $interval_string == 'days') {
			$interval_minutes = $interval_minutes * 1440;
		}
		return $interval_minutes;
	}

	/** To get the converted record values which have to be display in duplicates merging tpl*/
	function getRecordValues ($id_array, $module) {
		global $adb, $current_user;
		global $app_strings;
		$value_pair = array ();
		$tabid      = getTabid ($module);
		$query      = "SELECT fieldname,fieldlabel,uitype FROM vtiger_field WHERE tabid=? AND fieldname  NOT IN ('createdtime','modifiedtime') AND vtiger_field.presence IN (0,2) AND uitype NOT IN('4')";
		$result     = $adb->pquery ($query, array ($tabid));
		$no_rows    = $adb->num_rows ($result);

		$focus = new $module();
		if (isset($id_array) && $id_array != '') {
			foreach ($id_array as $value_pair['disp_value']) {
				$focus->id = $value_pair['disp_value'];
				$focus->retrieve_entity_info ($value_pair['disp_value'], $module);
				$field_values[] = $focus->column_fields;
			}
		}

		$labl_array = array ();
		$value_pair = array ();
		$c          = 0;
		for ($i = 0; $i < $no_rows; $i++) {
			$fld_name  = $adb->query_result ($result, $i, "fieldname");
			$fld_label = $adb->query_result ($result, $i, "fieldlabel");
			$ui_type   = $adb->query_result ($result, $i, "uitype");

			if (getFieldVisibilityPermission ($module, $current_user->id, $fld_name, 'readwrite') == '0') {
				$fld_array []                      = $fld_name;
				$record_values[ $c ][ $fld_label ] = Array ();
				$ui_value[]                        = $ui_type;
				for ($j = 0; $j < count ($field_values); $j++) {

					if ($ui_type == 56) {
						if ($field_values[ $j ][ $fld_name ] == 0) {
							$value_pair['disp_value'] = $app_strings['no'];
						} else {
							$value_pair['disp_value'] = $app_strings['yes'];
						}
					} elseif ($ui_type == 53) {
						$owner_id                 = $field_values[ $j ][ $fld_name ];
						$ownername                = getOwnerName ($owner_id);
						$value_pair['disp_value'] = $ownername;
					} elseif ($ui_type == 75 || $ui_type == 81) {
						$vendor_id = $field_values[ $j ][ $fld_name ];
						if ($vendor_id != '') {
							$vendor_name = getVendorName ($vendor_id);
						}
						$value_pair['disp_value'] = $vendor_name;
					} elseif ($ui_type == 52) {
						$user_id                  = $field_values[ $j ][ $fld_name ];
						$user_name                = getUserFullName ($user_id);
						$value_pair['disp_value'] = $user_name;
					} elseif ($ui_type == 58) {
						$campaign_name = getCampaignName ($field_values[ $j ][ $fld_name ]);
						if ($campaign_name != '') {
							$value_pair['disp_value'] = $campaign_name;
						} else {
							$value_pair['disp_value'] = '';
						}
					} elseif ($ui_type == 10) {
						$value_pair['disp_value'] = getRecordInfoFromID ($field_values[ $j ][ $fld_name ]);
					} elseif ($ui_type == 5 || $ui_type == 6 || $ui_type == 23) {
						if ($field_values[ $j ][ $fld_name ] != '' && $field_values[ $j ][ $fld_name ]
																	  != '0000-00-00'
						) {
							$date                     = new DateTimeField($field_values[ $j ][ $fld_name ]);
							$value_pair['disp_value'] = $date->getDisplayDate ();
							if (strpos ($field_values[ $j ][ $fld_name ], ' ') > -1) {
								$value_pair['disp_value'] .= (' ' . $date->getDisplayTime ());
							}
						} elseif ($field_values[ $j ][ $fld_name ] == '0000-00-00') {
							$value_pair['disp_value'] = '';
						} else {
							$value_pair['disp_value'] = $field_values[ $j ][ $fld_name ];
						}
					} elseif ($ui_type == '71' || $ui_type == '72') {
						$currencyField = new CurrencyField($field_values[ $j ][ $fld_name ]);
						if ($ui_type == '72') {
							$value_pair['disp_value'] = $currencyField->getDisplayValue (null, true);
						} else {
							$value_pair['disp_value'] = $currencyField->getDisplayValue ();
						}
					} else {
						$value_pair['disp_value'] = $field_values[ $j ][ $fld_name ];
					}
					$value_pair['org_value'] = $field_values[ $j ][ $fld_name ];

					array_push ($record_values[ $c ][ $fld_label ], $value_pair);
				}
				$c++;
			}
		}
		$parent_array[0] = $record_values;
		$parent_array[1] = $fld_array;
		$parent_array[2] = $fld_array;
		return $parent_array;
	}

	/** Function to get a to find duplicates in a particular module*/
	function getDuplicateQuery ($module, $field_values, $ui_type_arr) {
		global $current_user;
		$tbl_col_fld = explode (",", $field_values);
		$i           = 0;
		foreach ($tbl_col_fld as $val) {
			list($tbl[ $i ], $cols[ $i ], $fields[ $i ]) = explode (".", $val);
			$tbl_cols[ $i ] = $tbl[ $i ] . "." . $cols[ $i ];
			$i++;
		}
		$table_cols    = implode (",", $tbl_cols);
		$sec_parameter = getSecParameterforMerge ($module);
		$modObj        = CRMEntity::getInstance ($module);
		if ($modObj != null && method_exists ($modObj, 'getDuplicatesQuery')) {
			$nquery = $modObj->getDuplicatesQuery ($module, $table_cols, $field_values, $ui_type_arr);
		}
		return $nquery;
	}

	/** Function to return the duplicate records data as a formatted array */
	function getDuplicateRecordsArr ($module) {
		global $adb, $app_strings, $list_max_entries_per_page, $theme;
		$field_values_array = getFieldValues ($module);
		$field_values       = $field_values_array['fieldnames_list'];
		$fld_arr            = $field_values_array['fieldnames_array'];
		$col_arr            = $field_values_array['columnnames_array'];
		$fld_labl_arr       = $field_values_array['fieldlabels_array'];
		$ui_type            = $field_values_array['fieldname_uitype'];

		$dup_query = getDuplicateQuery ($module, $field_values, $ui_type);
		// added for page navigation
		$dup_count_query = substr ($dup_query, stripos ($dup_query, 'FROM'), strlen ($dup_query));
		$dup_count_query = "SELECT count(*) as count " . $dup_count_query;
		$count_res       = $adb->query ($dup_count_query);
		$no_of_rows      = $adb->query_result ($count_res, 0, "count");

		if ($no_of_rows <= $list_max_entries_per_page) {
			$_SESSION[ 'dup_nav_start' . $module ] = 1;
		} else if (isset($_REQUEST["start"]) && $_REQUEST["start"] != "" && $_SESSION[ 'dup_nav_start' . $module ] != $_REQUEST["start"]) {
			$_SESSION[ 'dup_nav_start' . $module ] = ListViewSession::getRequestStartPage ();
		}
		$start            = ($_SESSION[ 'dup_nav_start' . $module ] != "") ? $_SESSION[ 'dup_nav_start' . $module ] : 1;
		$navigation_array = getNavigationValues ($start, $no_of_rows, $list_max_entries_per_page);
		$start_rec        = $navigation_array['start'];
		$end_rec          = $navigation_array['end_val'];
		$navigationOutput = getTableHeaderNavigation ($navigation_array, "", $module, "FindDuplicate", "");
		if ($start_rec == 0) {
			$limit_start_rec = 0;
		} else {
			$limit_start_rec = $start_rec - 1;
		}
		$dup_query .= " LIMIT $limit_start_rec, $list_max_entries_per_page";
		//ends

		$nresult = $adb->query ($dup_query);
		$no_rows = $adb->num_rows ($nresult);
		require_once ('modules/Vtiger/layout_utils.php');
		if ($no_rows == 0) {
			if ($_REQUEST['action'] == 'FindDuplicateRecords') {
				//echo "<br><br><center>".$app_strings['LBL_NO_DUPLICATE']." <a href='javascript:window.history.back()'>".$app_strings['LBL_GO_BACK'].".</a></center>";
				//die;
				echo "<link rel='stylesheet' type='text/css' href='themes/$theme/style.css'>";
				echo "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
				echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>

				<table border='0' cellpadding='5' cellspacing='0' width='98%'>
				<tbody><tr>
				<td rowspan='2' width='11%'><img src='" . vtiger_imageurl ('empty.jpg', $theme) . "' ></td>
				<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>$app_strings[LBL_NO_DUPLICATE]</span></td>
				</tr>
				<tr>
				<td class='small' align='right' nowrap='nowrap'>
				<a href='javascript:window.history.back();'>$app_strings[LBL_GO_BACK]</a><br>     </td>
				</tr>
				</tbody></table>
				</div>";
				echo "</td></tr></table>";
				exit();
			} else {
				echo "<br><br><table align='center' class='reportCreateBottom big' width='95%'><tr><td align='center'>" . $app_strings['LBL_NO_DUPLICATE'] . "</td></tr></table>";
				die;
			}
		}

		$rec_cnt = 0;
		$temp    = Array ();
		$sl_arr  = Array ();
		$grp     = "group0";
		$gcnt    = 0;
		$ii      = 0; //ii'th record in group
		while ($rec_cnt < $no_rows) {
			$result = $adb->fetchByAssoc ($nresult);
			//echo '<pre>';print_r($result);echo '</pre>';
			if ($rec_cnt != 0) {
				$sl_arr = array_slice ($result, 2);
				array_walk ($temp, 'lower_array');
				array_walk ($sl_arr, 'lower_array');
				$arr_diff = array_diff ($temp, $sl_arr);
				if (count ($arr_diff) > 0) {
					$gcnt++;
					$temp = $sl_arr;
					$ii   = 0;
				}
				$grp = "group" . $gcnt;
			}
			$fld_values[ $grp ][ $ii ]['recordid'] = $result['recordid'];
			for ($k = 0; $k < count ($col_arr); $k++) {
				if ($rec_cnt == 0) {
					$temp[ $fld_labl_arr[ $k ] ] = $result[ $col_arr[ $k ] ];
				}
				if ($ui_type[ $fld_arr[ $k ] ] == 56) {
					if ($result[ $col_arr[ $k ] ] == 0) {
						$result[ $col_arr[ $k ] ] = $app_strings['no'];
					} else {
						$result[ $col_arr[ $k ] ] = $app_strings['yes'];
					}
				}
				if ($ui_type[ $fld_arr[ $k ] ] == 75 || $ui_type[ $fld_arr[ $k ] ] == 81) {
					$vendor_id = $result[ $col_arr[ $k ] ];
					if ($vendor_id != '') {
						$vendor_name = getVendorName ($vendor_id);
					}
					$result[ $col_arr[ $k ] ] = $vendor_name;
				}
				if ($ui_type[ $fld_arr[ $k ] ] == 15 || $ui_type[ $fld_arr[ $k ] ] == 16) {
					$result[ $col_arr[ $k ] ] = getTranslatedString ($result[ $col_arr[ $k ] ], $module);
				}
				if ($ui_type[ $fld_arr[ $k ] ] == 33) {
					$fieldvalue               = explode (' |##| ', $result[ $col_arr[ $k ] ]);
					$result[ $col_arr[ $k ] ] = array ();
					foreach ($fieldvalue as $picklistValue) {
						$result[ $col_arr[ $k ] ][] = getTranslatedString ($picklistValue, $module);
					}
					$result[ $col_arr[ $k ] ] = implode (', ', $result[ $col_arr[ $k ] ]);
				}
				if ($ui_type[ $fld_arr[ $k ] ] == 68) {
					$parent_id = $result[ $col_arr[ $k ] ];
					if ($parent_id != '') {
						$parentname = getParentName ($parent_id);
					}

					$result[ $col_arr[ $k ] ] = $parentname;
				}
				if ($ui_type[ $fld_arr[ $k ] ] == 53 || $ui_type[ $fld_arr[ $k ] ] == 52) {
					if ($result[ $col_arr[ $k ] ] != '') {
						$owner = getOwnerName ($result[ $col_arr[ $k ] ]);
					}
					$result[ $col_arr[ $k ] ] = $owner;
				}
				if ($ui_type[ $fld_arr[ $k ] ] == 58) {
					$campaign_name = getCampaignName ($result[ $col_arr[ $k ] ]);
					if ($campaign_name != '') {
						$result[ $col_arr[ $k ] ] = $campaign_name;
					} else {
						$result[ $col_arr[ $k ] ] = '';
					}
				}
				/*uitype 10 handling*/
				if ($ui_type[ $fld_arr[ $k ] ] == 10) {
					$result[ $col_arr[ $k ] ] = getRecordInfoFromID ($result[ $col_arr[ $k ] ]);
				}
				if ($ui_type[ $fld_arr[ $k ] ] == 5 || $ui_type[ $fld_arr[ $k ] ] == 6 || $ui_type[ $fld_arr[ $k ] ] == 23) {
					if ($$result[ $col_arr[ $k ] ] != '' && $$result[ $col_arr[ $k ] ] != '0000-00-00') {
						$date  = new DateTimeField($$result[ $col_arr[ $k ] ]);
						$value = $date->getDisplayDate ();
						if (strpos ($$result[ $col_arr[ $k ] ], ' ') > -1) {
							$value .= (' ' . $date->getDisplayTime ());
						}
					} elseif ($$result[ $col_arr[ $k ] ] == '0000-00-00') {
						$value = '';
					} else {
						$value = $$result[ $col_arr[ $k ] ];
					}
					$result[ $col_arr[ $k ] ] = $value;
				}
				if ($ui_type[ $fld_arr[ $k ] ] == 71) {
					$result[ $col_arr[ $k ] ] = CurrencyField::convertToUserFormat ($result[ $col_arr[ $k ] ]);
				}
				if ($ui_type[ $fld_arr[ $k ] ] == 72) {
					$result[ $col_arr[ $k ] ] = CurrencyField::convertToUserFormat ($result[ $col_arr[ $k ] ], null, true);
				}

				$fld_values[ $grp ][ $ii ][ $fld_labl_arr[ $k ] ] = $result[ $col_arr[ $k ] ];
			}
			$fld_values[ $grp ][ $ii ]['Entity Type'] = $result['deleted'];
			$ii++;
			$rec_cnt++;
		}

		$gro = "group";
		for ($i = 0; $i < $no_rows; $i++) {
			$ii                = 0;
			$dis_group[]       = $fld_values[ $gro . $i ][ $ii ];
			$count_group[ $i ] = count ($fld_values[ $gro . $i ]);
			$ii++;
			$new_group[] = $dis_group[ $i ];
		}
		$fld_nam               = $new_group[0];
		$ret_arr[0]            = $fld_values;
		$ret_arr[1]            = $fld_nam;
		$ret_arr[2]            = $ui_type;
		$ret_arr["navigation"] = $navigationOutput;
		return $ret_arr;
	}

	/** Function to get on clause criteria for duplicate check queries */
	function get_on_clause ($field_list, $uitype_arr, $module) {
		$field_array = explode (",", $field_list);
		$ret_str     = '';
		$i           = 1;
		foreach ($field_array as $fld) {
			$sub_arr  = explode (".", $fld);
			$tbl_name = $sub_arr[0];
			$col_name = $sub_arr[1];
			$fld_name = $sub_arr[2];

			$ret_str .= " ifnull($tbl_name.$col_name,'null') = ifnull(temp.$col_name,'null')";

			if (count ($field_array) != $i) {
				$ret_str .= " and ";
			}
			$i++;
		}
		return $ret_str;
	}

	/** call back function to change the array values in to lower case */
	function lower_array (&$string) {
		$string = strtolower (trim ($string));
	}

	/** Function to get tablename, columnname, fieldname, fieldlabel and uitypes of fields of merge criteria for a particular module*/
	function getFieldValues ($module) {
		global $adb, $current_user;
		$fld_table_arr   = Array ();
		$special_fld_arr = Array ();
		$tabid           = getTabid ($module);

		$fieldname_query  = "SELECT fieldname,fieldlabel,uitype,tablename,columnname FROM vtiger_field WHERE fieldid IN
			(SELECT fieldid FROM vtiger_user2mergefields WHERE tabid=? AND userid=? AND visible = ?) AND vtiger_field.presence IN (0,2)";
		$fieldname_result = $adb->pquery ($fieldname_query, array ($tabid, $current_user->id, 1));

		$field_num_rows = $adb->num_rows ($fieldname_result);

		$fld_arr = array ();
		$col_arr = array ();
		for ($j = 0; $j < $field_num_rows; $j++) {
			$tablename   = $adb->query_result ($fieldname_result, $j, 'tablename');
			$column_name = $adb->query_result ($fieldname_result, $j, 'columnname');
			$field_name  = $adb->query_result ($fieldname_result, $j, 'fieldname');
			$field_lbl   = $adb->query_result ($fieldname_result, $j, 'fieldlabel');
			$ui_type     = $adb->query_result ($fieldname_result, $j, 'uitype');
			$table_col   = $tablename . "." . $column_name;
			if (getFieldVisibilityPermission ($module, $current_user->id, $field_name) == 0) {
				$fld_name = ($special_fld_arr[ $field_name ] != '') ? $special_fld_arr[ $field_name ] : $field_name;

				$fld_arr[] = $fld_name;
				$col_arr[] = $column_name;
				if ($fld_table_arr[ $table_col ] != '') {
					$table_col = $fld_table_arr[ $table_col ];
				}

				$field_values_array['fieldnames_list'][] = $table_col . "." . $fld_name;
				$fld_labl_arr[]                          = $field_lbl;
				$uitype[ $field_name ]                   = $ui_type;
			}
		}
		$field_values_array['fieldnames_list']   = implode (",", $field_values_array['fieldnames_list']);
		$field_values                            = implode (",", $fld_arr);
		$field_values_array['fieldnames']        = $field_values;
		$field_values_array["fieldnames_array"]  = $fld_arr;
		$field_values_array["columnnames_array"] = $col_arr;
		$field_values_array['fieldlabels_array'] = $fld_labl_arr;
		$field_values_array['fieldname_uitype']  = $uitype;

		return $field_values_array;
	}

	/** To get security parameter for a particular module -- By Pavani*/
	function getSecParameterforMerge ($module) {
		global $current_user;
		$tab_id        = getTabid ($module);
		$sec_parameter = "";
		$local_user    = clone $current_user;
		require ('user_privileges/user_privileges.php');
		require ('user_privileges/sharing_privileges.php');
		if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[ $tab_id ] == 3) {
			if ($module == "Vendors") {
				$sec_parameter = "";
			} else {
				$sec_parameter = getListViewSecurityParameter ($module);
			}
		}
		return $sec_parameter;
	}

// Update all the data refering to currency $old_cur to $new_cur
	function transferCurrency ($old_cur, $new_cur) {
		// Transfer User currency to new currency
		global $log, $adb, $current_user;
		$log->debug ("Entering function transferUserCurrency...");

		$sql = "UPDATE vtiger_users SET currency_id=? WHERE currency_id=?";
		$adb->pquery ($sql, array ($new_cur, $old_cur));

		$current_user->retrieve_entity_info ($current_user->id, "Users");
		$log->debug ("Exiting function transferUserCurrency...");
	}

//functions for settings page
	/**
	 * this function returns the blocks for the settings page
	 */
	function getSettingsBlocks () {
		global $adb, $current_user;
		if (!is_admin ($current_user)) {
			return array ();
		}
		if (!is_superadmin ($current_user)) {
			$whereClause = "WHERE label<>'LBL_ADMINISTRATION'";
		} else {
			$whereClause = '';
		}

		$result = $adb->query ("SELECT * FROM vtiger_settings_blocks {$whereClause} ORDER BY sequence");
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return array ();
		}

		$blocks = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$blocks [ $row ['blockid'] ] = $row ['label'];
		}
		return $blocks;
	}

	/**
	 * this function returns the fields for the settings page
	 */
	function getSettingsFields () {
		global $adb;
		$sql    = "SELECT * FROM vtiger_settings_field WHERE blockid!=? AND active=0 ORDER BY blockid,sequence";
		$result = $adb->pquery ($sql, array (getSettingsBlockId ('LBL_MODULE_MANAGER')));
		$count  = $adb->num_rows ($result);
		$fields = array ();

		if ($count > 0) {
			for ($i = 0; $i < $count; $i++) {
				$blockid     = $adb->query_result ($result, $i, "blockid");
				$iconpath    = $adb->query_result ($result, $i, "iconpath");
				$description = $adb->query_result ($result, $i, "description");
				$linkto      = $adb->query_result ($result, $i, "linkto");
				$action      = getPropertiesFromURL ($linkto, "action");
				$module      = getPropertiesFromURL ($linkto, "module");
				$name        = $adb->query_result ($result, $i, "name");

				$fields[ $blockid ][] = array ("icon" => $iconpath, "description" => $description, "link" => $linkto, "name" => $name, "action" => $action, "module" => $module);
			}

			//add blanks for 4-column layout
			foreach ($fields as $blockid => &$field) {
				if (count ($field) > 0 && count ($field) < 4) {
					for ($i = count ($field); $i < 4; $i++) {
						$field[ $i ] = array ();
					}
				}
			}
		}
		return $fields;
	}

	/**
	 * this function takes an url and returns the module name from it
	 */
	function getPropertiesFromURL ($url, $action) {
		$result = array ();
		preg_match ("/$action=([^&]+)/", $url, $result);
		return $result[1];
	}

//functions for settings page end

	/* Function to get the name of the Field which is used for Module Specific Sequence Numbering, if any
 * @param module String - Module label
 * return Array - Field name and label are returned */
	function getModuleSequenceField ($module) {
		global $adb, $log;
		$log->debug ("Entering function getModuleSequenceFieldName ($module)...");
		$field = null;

		if (!empty($module)) {

			// First look at the cached information
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module ($module);

			if ($cachedModuleFields === false) {
				//uitype 4 points to Module Numbering Field
				$seqColRes = $adb->pquery ("SELECT fieldname, fieldlabel, columnname FROM vtiger_field WHERE uitype=? AND tabid=? AND vtiger_field.presence IN (0,2)", array ('4', getTabid ($module)));
				if ($adb->num_rows ($seqColRes) > 0) {
					$fieldname  = $adb->query_result ($seqColRes, 0, 'fieldname');
					$columnname = $adb->query_result ($seqColRes, 0, 'columnname');
					$fieldlabel = $adb->query_result ($seqColRes, 0, 'fieldlabel');

					$field           = array ();
					$field['name']   = $fieldname;
					$field['column'] = $columnname;
					$field['label']  = $fieldlabel;
				}
			} else {

				foreach ($cachedModuleFields as $fieldinfo) {
					if ($fieldinfo['uitype'] == '4') {
						$field = array ();

						$field['name']   = $fieldinfo['fieldname'];
						$field['column'] = $fieldinfo['columnname'];
						$field['label']  = $fieldinfo['fieldlabel'];

						break;
					}
				}
			}
		}

		$log->debug ("Exiting getModuleSequenceFieldName...");
		return $field;
	}

	/* Function to get the Result of all the field ids allowed for Duplicates merging for specified tab/module (tabid) */
	function getFieldsResultForMerge ($tabid) {
		global $log, $adb;
		$log->debug ("Entering getFieldsResultForMerge(" . $tabid . ") method ...");

		$nonmergable_tabids = array (29);

		if (in_array ($tabid, $nonmergable_tabids)) {
			return null;
		}

		// List of Fields not allowed for Duplicates Merging based on the module (tabid) [tabid to fields mapping]
		$nonmergable_field_tab = Array (
			4  => array ('portal', 'imagename'),
			13 => array ('update_log', 'filename', 'comments'),
		);

		$nonmergable_displaytypes = Array (4);
		$nonmergable_uitypes      = Array ('70', '69', '4');

		$sql    = "SELECT fieldid,typeofdata FROM vtiger_field WHERE tabid = ? AND vtiger_field.presence IN (0,2) AND block IS NOT NULL";
		$params = array ($tabid);

		$where = '';

		if (isset($nonmergable_field_tab[ $tabid ]) && count ($nonmergable_field_tab[ $tabid ]) > 0) {
			$where .= " AND fieldname NOT IN (" . generateQuestionMarks ($nonmergable_field_tab[ $tabid ]) . ")";
			array_push ($params, $nonmergable_field_tab[ $tabid ]);
		}

		if (count ($nonmergable_displaytypes) > 0) {
			$where .= " AND displaytype NOT IN (" . generateQuestionMarks ($nonmergable_displaytypes) . ")";
			array_push ($params, $nonmergable_displaytypes);
		}
		if (count ($nonmergable_uitypes) > 0) {
			$where .= " AND uitype NOT IN ( " . generateQuestionMarks ($nonmergable_uitypes) . ")";
			array_push ($params, $nonmergable_uitypes);
		}

		if (trim ($where) != '') {
			$sql .= $where;
		}

		$res = $adb->pquery ($sql, $params);
		$log->debug ("Exiting getFieldsResultForMerge method ...");
		return $res;
	}

	/* Function to get the related tables data
 * @param - $module - Primary module name
 * @param - $secmodule - Secondary module name
 * return Array $rel_array tables and fields to be compared are sent
 * */
	function getRelationTables ($module, $secmodule) {
		global $adb;
		$primary_obj   = CRMEntity::getInstance ($module);
		$secondary_obj = CRMEntity::getInstance ($secmodule);

		$ui10_query = $adb->pquery ("SELECT vtiger_field.tabid AS tabid,vtiger_field.tablename AS tablename, vtiger_field.columnname AS columnname FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE (vtiger_fieldmodulerel.module=? AND vtiger_fieldmodulerel.relmodule=?) OR (vtiger_fieldmodulerel.module=? AND vtiger_fieldmodulerel.relmodule=?)", array ($module, $secmodule, $secmodule, $module));
		if ($adb->num_rows ($ui10_query) > 0) {
			$ui10_tablename  = $adb->query_result ($ui10_query, 0, 'tablename');
			$ui10_columnname = $adb->query_result ($ui10_query, 0, 'columnname');
			$ui10_tabid      = $adb->query_result ($ui10_query, 0, 'tabid');

			if ($primary_obj->table_name == $ui10_tablename) {
				$reltables = array ($ui10_tablename => array ("" . $primary_obj->table_index . "", "$ui10_columnname"));
			} else if ($secondary_obj->table_name == $ui10_tablename) {
				$reltables = array ($ui10_tablename => array ("$ui10_columnname", "" . $secondary_obj->table_index . ""), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
			} else {
				if (isset($secondary_obj->tab_name_index[ $ui10_tablename ])) {
					$rel_field = $secondary_obj->tab_name_index[ $ui10_tablename ];
					$reltables = array ($ui10_tablename => array ("$ui10_columnname", "$rel_field"), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
				} else {
					$rel_field = $primary_obj->tab_name_index[ $ui10_tablename ];
					$reltables = array ($ui10_tablename => array ("$rel_field", "$ui10_columnname"), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
				}
			}
		} else {
			if (method_exists ($primary_obj, setRelationTables)) {
				$reltables = $primary_obj->setRelationTables ($secmodule);
			} else {
				$reltables = '';
			}
		}
		if (is_array ($reltables) && !empty($reltables)) {
			$rel_array = $reltables;
		} else {
			$rel_array = array ("vtiger_crmentityrel" => array ("crmid", "relcrmid"), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
		}
		return $rel_array;
	}

	/**
	 * This function returns no value but handles the delete functionality of each entity.
	 * Input Parameter are $module - module name, $return_module - return module name, $focus - module object, $record - entity id, $return_id - return entity id.
	 */
	function DeleteEntity ($module, $return_module, $focus, $record, $return_id) {
		global $log;
		$log->debug ("Entering DeleteEntity method ($module, $return_module, $record, $return_id)");

		if ($module != $return_module && !empty($return_module) && !empty($return_id)) {
			$focus->unlinkRelationship ($record, $return_module, $return_id);
			$focus->trackUnLinkedInfo ($return_module, $return_id, $module, $record);
		} else {
			$focus->trash ($module, $record);
		}
		$log->debug ("Exiting DeleteEntity method ...");
	}

	/**
	 * Function to related two records of different entity types
	 */
	function relateEntities ($focus, $sourceModule, $sourceRecordId, $destinationModule, $destinationRecordIds) {
		if (!is_array ($destinationRecordIds)) {
			$destinationRecordIds = Array ($destinationRecordIds);
		}
		foreach ($destinationRecordIds as $destinationRecordId) {
			$focus->save_related_module ($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordId);
			$focus->trackLinkedInfo ($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordId);
		}
	}

	/* To get modules list for which work flow and field formulas is permitted*/
	function com_vtGetModules ($adb) {
		$sql     = "SELECT DISTINCT vtiger_field.tabid, name
		FROM vtiger_field
		INNER JOIN vtiger_tab
			ON vtiger_field.tabid=vtiger_tab.tabid
		WHERE vtiger_field.tabid NOT IN(9,10,16,15,8,29) AND vtiger_tab.presence = 0 AND vtiger_tab.isentitytype=1";
		$it      = new SqlResultIterator($adb, $adb->query ($sql));
		$modules = array ();
		foreach ($it as $row) {
			if (isPermitted ($row->name, 'index') == "yes") {
				$modules[ $row->name ] = getTranslatedString ($row->name);
			}
		}
		return $modules;
	}

	/**
	 * this function accepts an ID and returns the entity value for that id
	 *
	 * @param integer $id - the crmid of the record
	 *
	 * @return string $data - the entity name for the id
	 */
	function getRecordInfoFromID ($id) {
		global $adb;
		$data   = array ();
		$sql    = "SELECT setype FROM vtiger_crmentity WHERE crmid=?";
		$result = $adb->pquery ($sql, array ($id));
		if ($adb->num_rows ($result) > 0) {
			$setype = $adb->query_result ($result, 0, "setype");
			$data   = getEntityName ($setype, $id);
		}
		$data = array_values ($data);
		$data = $data[0];
		return $data;
	}

	/**
	 * Function to check if a given record exists (not deleted)
	 *
	 * @param integer $recordId - record id
	 */
	function isRecordExists ($recordId) {
		global $adb;
		$query  = "SELECT crmid FROM vtiger_crmentity WHERE crmid=? AND deleted=0";
		$result = $adb->pquery ($query, array ($recordId));
		if ($adb->num_rows ($result)) {
			return true;
		}
		return false;
	}

	/** Function to set date values compatible to database (YY_MM_DD)
	 *
	 * @param $value -- value :: Type string
	 * @returns $insert_date -- insert_date :: Type string
	 */
	function getValidDBInsertDateValue ($value) {
		global $log, $current_user;
		$log->debug ("Entering getValidDBInsertDateValue(" . $value . ") method ...");
		$value = trim ($value);
		
		// Si el valor está vacío, retornar vacío
		if (empty($value)) {
			return '';
		}
		
		// Si ya está en formato YYYY-MM-DD, retornarlo directamente
		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
			return $value;
		}
		
		$delim = array ('/', '.');
		foreach ($delim as $delimiter) {
			$x = strpos ($value, $delimiter);
			if ($x === false) {
				continue;
			} else {
				$value = str_replace ($delimiter, '-', $value);
				break;
			}
		}
		
		$parts = explode ('-', $value);
		if (count($parts) !== 3) {
			return '';
		}
		
		// Obtener el formato de fecha del usuario
		$userDateFormat = isset($current_user->date_format) ? $current_user->date_format : 'yyyy-mm-dd';
		
		// Parsear según el formato del usuario (soporta - y /)
		if ($userDateFormat === 'dd-mm-yyyy' || $userDateFormat === 'dd/mm/yyyy') {
			$d = $parts[0];
			$m = $parts[1];
			$y = $parts[2];
		} else if ($userDateFormat === 'mm-dd-yyyy' || $userDateFormat === 'mm/dd/yyyy') {
			$m = $parts[0];
			$d = $parts[1];
			$y = $parts[2];
		} else {
			// yyyy-mm-dd o yyyy/mm/dd (formato por defecto y formato BD)
			$y = $parts[0];
			$m = $parts[1];
			$d = $parts[2];
		}
		if (strlen ($d) == 1) {
			$d = '0' . $d;
		}
		if (strlen ($m) == 1) {
			$m = '0' . $m;
		}

		// Construir fecha en formato BD (yyyy-mm-dd)
		$insert_date = $y . '-' . $m . '-' . $d;

		if (preg_match ("/^[0-9]{2,4}[-][0-1]{1,2}?[0-9]{1,2}[-][0-3]{1,2}?[0-9]{1,2}$/", $insert_date) == 0) {
			return '';
		}

		$log->debug ("Exiting getValidDBInsertDateValue method ...");
		return $insert_date;
	}

	function getValidDBInsertDateTimeValue ($value) {
		$value     = trim ($value);
		$valueList = explode (' ', $value);
		if (count ($valueList) == 2) {
			$dbDateValue = getValidDBInsertDateValue ($valueList[0]);
			$dbTimeValue = $valueList[1];
			if (!empty($dbTimeValue) && strpos ($dbTimeValue, ':') === false) {
				$dbTimeValue = $dbTimeValue . ':';
			}
			$timeValueLength = strlen ($dbTimeValue);
			if (!empty($dbTimeValue) && strrpos ($dbTimeValue, ':') == ($timeValueLength - 1)) {
				$dbTimeValue = $dbTimeValue . '00';
			}
			try {
				$dateTime = new DateTimeField($dbDateValue . ' ' . $dbTimeValue);
				return $dateTime->getDBInsertDateTimeValue ();
			} catch (Exception $ex) {
				return '';
			}
		} elseif (count ($valueList == 1)) {
			return getValidDBInsertDateValue ($value);
		}
	}

	/** Function to sanitize the upload file name when the file name is detected to have bad extensions
	 *
	 * @param String -- $fileName - File name to be sanitized
	 *
	 * @return String - Sanitized file name
	 */
	function sanitizeUploadFileName ($fileName, $badFileExtensions) {
		$fileName = preg_replace ('/\s+/', '_', $fileName);//replace space with _ in filename
		$fileName = rtrim ($fileName, '\\/<>?*:"<>|');

		$fileNameParts        = explode (".", $fileName);
		$countOfFileNameParts = count ($fileNameParts);
		$badExtensionFound    = false;

		for ($i = 0; $i < $countOfFileNameParts; ++$i) {
			$partOfFileName = $fileNameParts[ $i ];
			if (in_array (strtolower ($partOfFileName), $badFileExtensions)) {
				$badExtensionFound   = true;
				$fileNameParts[ $i ] = $partOfFileName . 'file';
			}
		}

		$newFileName = implode (".", $fileNameParts);

		if ($badExtensionFound) {
			$newFileName .= ".txt";
		}
		return $newFileName;
	}

	function getSelectedRecords ($input, $module, $idstring, $excludedRecords) {
		global $current_user, $adb;

		if ($idstring == 'relatedListSelectAll') {
			$recordid        = vtlib_purify ($input['recordid']);
			$storearray      = array ();
			$excludedRecords = explode (';', $excludedRecords);
			$storearray      = array_diff ($storearray, $excludedRecords);
		} else if ($module == 'Documents') {

			if ($input['selectallmode'] == 'true') {
				$result     = getSelectAllQuery ($input, $module);
				$storearray = array ();
				$focus      = CRMEntity::getInstance ($module);

				for ($i = 0; $i < $adb->num_rows ($result); $i++) {
					$storearray[] = $adb->query_result ($result, $i, $focus->table_index);
				}

				$excludedRecords = explode (';', $excludedRecords);
				$storearray      = array_diff ($storearray, $excludedRecords);
				if ($idstring != 'all') {
					$storearray = array_merge ($storearray, explode (';', $idstring));
				}
				$storearray = array_unique ($storearray);
			} else {
				$storearray = explode (";", $idstring);
			}
		} elseif ($idstring == 'all') {

			$result     = getSelectAllQuery ($input, $module);
			$storearray = array ();
			$focus      = CRMEntity::getInstance ($module);

			for ($i = 0; $i < $adb->num_rows ($result); $i++) {
				$storearray[] = $adb->query_result ($result, $i, $focus->table_index);
			}

			$excludedRecords = explode (';', $excludedRecords);
			$storearray      = array_diff ($storearray, $excludedRecords);
		} else {
			$storearray = explode (";", $idstring);
		}

		return $storearray;
	}

	function getSelectAllQuery ($input, $module) {
		global $adb, $current_user;

		$viewid = vtlib_purify ($input['viewname']);

		if ($module == "Calendar") {
			$listquery   = getListQuery ($module);
			$oCustomView = new CustomView($module);
			$query       = $oCustomView->getModifiedCvListQuery ($viewid, $listquery, $module);
			$where       = '';
			if ($input['query'] == 'true') {
				list($where, $ustring) = split ("#@@#", getWhereCondition ($module, $input));
				if (isset($where) && $where != '') {
					$query .= " AND " . $where;
				}
			}
		} else {
			$queryGenerator = new QueryGenerator($module, $current_user);
			$queryGenerator->initForCustomViewById ($viewid);

			if ($input['query'] == 'true') {
				$queryGenerator->addUserSearchConditions ($input);
			}

			$queryGenerator->setFields (array ('id'));
			$query = $queryGenerator->getQuery ();

			if ($module == 'Documents') {
				$folderid = vtlib_purify ($input['folderidstring']);
				$folderid = str_replace (';', ',', $folderid);
				$query .= " AND vtiger_notes.folderid in (" . $folderid . ")";
			}
		}

		$result = $adb->pquery ($query, array ());
		return $result;
	}

	//Funciones para el registro de campos tipo grid
	/**
	 * @param $module
	 * @param string $fieldid
	 *
	 * @return array
	 */
	function obtieneListaCamposCampoGrid ($module, $fieldid = '') {
		global $adb, $current_user;
		$lstCampos = array ();

		$profileList = getCurrentUserProfileList ();
		if (empty($profileList) && $current_user->is_admin != 'on') {
			return $lstCampos;
		}

		$condicionFieldid = '';
		if (!empty($fieldid)) {
			$condicionFieldid = " AND A.fieldid = " . $fieldid;
		}

		// no encontrÃ© permisologÃ­a sobre estos campos, tuve que insertar en la tabla vtiger_def_org_field, y luego actualizar los permisos al perfil
		// igualmente la presencia no debe ser -1 en vtiger_field para que pueda aparecer en la permisologÃ­a de los perfiles
		$query    = "SELECT DISTINCT A.fieldid,A.fieldname,A.fieldlabel FROM vtiger_field A
				INNER JOIN vtiger_tab B ON (A.tabid = B.tabid)
				LEFT JOIN vtiger_profile2field
				ON vtiger_profile2field.fieldid = A.fieldid
				LEFT JOIN vtiger_def_org_field
				ON vtiger_def_org_field.fieldid = A.fieldid
				WHERE A.uitype = 2202 AND B.name = ? $condicionFieldid";
		$params[] = $module;
		if ($current_user->is_admin != 'on' && 0) {    // EGC este campo aÃºn no maneja permisos al ser creados por defecto
			$query .= "AND vtiger_profile2field.visible = 0
				AND vtiger_profile2field.profileid IN (" . generateQuestionMarks ($profileList) . ")
  				AND vtiger_def_org_field.visible = 0 ORDER BY sequence";
			$params[] = $profileList;
		}

		$result = $adb->pquery ($query, $params);
		if ($result) {
			while ($row = $adb->fetch_array ($result)) {
				$lstCampos[] = $row;
			}
		}

		return $lstCampos;
	}

	/**
	 * @param integer $fieldid
	 * @param boolean $bEditView
	 * @param boolean $swGrid
	 *
	 * @return array $lstSubCampos
	 */
	function obtieneListaSubCamposCampoGrid ($fieldid, $bEditView = true, $swGrid = true) {
		global $adb;
		$lstSubCampos = array ();
		if ($swGrid) {
			$withGrid   = 'AND A.uitype != ?';
			$parameters = array ($fieldid, 2202);
		} else {
			$withGrid   = '';
			$parameters = array ($fieldid);
		}
		$query  = "SELECT A.* FROM vtiger_subfields_special A
				INNER JOIN vtiger_field B ON (A.fieldid = B.fieldid)
						WHERE A.fieldid = ? {$withGrid}  ORDER BY sequence";
		$result = $adb->pquery ($query, $parameters);
		if ($result) {
			$numRows = $adb->num_rows($result);
			
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$subCampo                    = $row;
				$subCampo['ancho']           = $row ['length'];
				$subCampo['valor']           = $row ['values'];
				$subCampo['posiblesvalores'] = is_array (json_decode ($row ['values'])) ? join (',', json_decode ($row ['values'])) : json_decode ($row ['values']);
				$lstSubCampos[]              = $subCampo;
			}
		}
		if ($bEditView) {
			$lstSubCampos[] = array ('ancho' => 10, 'name' => 'Acciones', 'uitype' => 99, 'valor' => '', 'posiblesvalores' => array (''));
		}

		$totalSubcampos = count($lstSubCampos);
		return $lstSubCampos;
	}

	function getSelectField (PearDatabase $adb, $fieldName) {
		$resultsArray = array ();
		$sql          = 'SELECT columnname FROM vtiger_field WHERE `fieldname` = ?';
		$results      = $adb->pquery ($sql, array ($fieldName));
		$numOfRows    = $adb->num_rows ($results);
		if ($numOfRows > 0) {
			$columnaName = $adb->query_result ($results, 0, 'columnname');
			if ((!empty($columnaName)) && ($columnaName != null)) {
				$sql     = "SELECT {$columnaName} FROM  vtiger_{$columnaName}";
				$results = $adb->query ($sql);
				while ($row = $adb->fetch_array ($results)) {
					$resultsArray[ $row[ $columnaName ] ] = $row[ $columnaName ];
				}
				return $resultsArray;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function getFieldGridValue ($subfieldsid, $recordId = '') {
		global $adb;
		if (!empty($recordId)) {
			$sql     = 'SELECT field_values FROM vtiger_subfields_values WHERE subfieldsid = ? AND modulecfid = ?';
			$results = $adb->pquery ($sql, array ($subfieldsid, $recordId));
		} else {
			$sql     = 'SELECT field_values FROM vtiger_subfields_values
							INNER JOIN vtiger_crmentity ON  vtiger_crmentity.crmid = vtiger_subfields_values.modulecfid
							WHERE vtiger_crmentity.deleted = 0 AND subfieldsid = ?
							ORDER BY subfieldsvaluesid DESC  LIMIT 1';
			$results = $adb->pquery ($sql, array ($subfieldsid));
		}
		$numOfRows = $adb->num_rows ($results);
		if ($numOfRows > 0) {
			$fieldValue = $adb->query_result ($results, 0, 'field_values');
			if ((!empty($fieldValue)) && ($fieldValue != null)) {
				return $fieldValue;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function organizeRelatedList (&$dataField) {
		$totalDataField = count ($dataField);
		for ($k = 1; $k < $totalDataField; $k++) {
			if (!is_array ($dataField[ $k ]) && ($k < count ($dataField))) {
				array_splice ($dataField, ($k - 1), 1);
			}
		}
		$totalDataField = count ($dataField);
		$dataGroup = $dataField[0];
		$numGrupo  = 0;
		for ($k = 1; $k < $totalDataField; $k++) {
			if (is_array ($dataField[ $k ])) {
				$listValue = array_values ($dataField[ $k ]);
				$dataGroup .= '@' . $listValue[0];
			} else {
				$dataField[ $numGrupo ] = $dataGroup;
				$dataGroup              = $dataField[ $k ];
				$numGrupo++;
			}
		}
		$dataField[ $numGrupo ] = $dataGroup;
		$dataField              = array_slice ($dataField, 0, ($numGrupo + 1));
	}

	/**
	 * Function to return block name
	 *
	 * @param integer $blockid
	 *
	 * @return string Block Name
	 */
	function getBlockName ($blockid) {
		global $adb;
		if (empty ($blockid)) {
			return '';
		}

		$result = $adb->pquery ('SELECT blocklabel FROM vtiger_blocks WHERE blockid=?', array ($blockid));
		if ($adb->num_rows ($result)) {
			$blockname = $adb->query_result ($result, 0, 'blocklabel');
		} else {
			$blockname = '';
		}
		return $blockname;
	}

	/**
	 *
	 * @param string $module
	 * @param integer $uitype
	 *
	 * @return array|boolean
	 */
	function getModulesWithGridFields ($module, $uitype) {
		global $adb;
		$specialInner   = '';
		$groupCondition = '';
		if ($uitype == '2202') {
			$results = $adb->pquery ('SHOW TABLES LIKE ?', array ('vtiger_subfields_special'));
			if (!$adb->num_rows ($results)) {
				return false;
			}
			$specialInner   = ' INNER JOIN vtiger_subfields_special ON  vtiger_subfields_special.fieldid = vtiger_field.fieldid';
			$groupCondition = ' GROUP BY vtiger_subfields_special.fieldid';
		}
		$sql          = "SELECT vtiger_field.fieldid, vtiger_field.fieldlabel, vtiger_tab.tablabel, vtiger_field.fieldname, vtiger_tab.name FROM vtiger_field
							INNER JOIN vtiger_tab ON  vtiger_tab.tabid = vtiger_field.tabid
							{$specialInner}
							WHERE vtiger_field.uitype = ?
							AND  vtiger_tab.name != ?
							AND vtiger_field.tabid not in(9,10,16,15,8,29)
							AND vtiger_tab.presence = 0
							AND vtiger_tab.isentitytype=1
							{$groupCondition}
							ORDER BY vtiger_tab.name ASC";
		$results      = $adb->pquery ($sql, array ($uitype, $module));
		$resultsArray = array ();
		$numOfRows    = $adb->num_rows ($results);
		if ($numOfRows > 0) {
			while ($row = $adb->fetchByAssoc ($results)) {
				$resultsArray[] = $row;
			}
			return $resultsArray;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param integer $fieldid
	 *
	 * @return array|boolean
	 */
	function getSpeciaFieldIds ($fieldid) {
		global $adb;
		$specialFieldList = array ();
		$sql              = 'SELECT * FROM vtiger_subfields_special WHERE  uitype = ? AND fieldid= ?';
		$results          = $adb->pquery ($sql, array (2202, $fieldid));
		$numOfRows        = $adb->num_rows ($results);
		if ($numOfRows > 0) {
			if ($results) {
				while ($row = $adb->fetch_array ($results)) {
					$specialFieldList[] = $row;
				}
				return $specialFieldList;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @param integer $fieldid
	 * @param integer $recordid
	 * @param array $dataFields
	 */
	function updateFieldGridValues ($fieldid, $recordid, $dataFields) {
		global $adb, $current_user, $upload_badext;
		
		$lstCampos = obtieneListaSubCamposCampoGrid ($fieldid);
		$sql       = 'INSERT INTO `vtiger_subfields_values` (`modulecfid`, `subfieldsid`, `field_values`) VALUES (?, ?, ?);';
		foreach ($lstCampos as $row) {
			if (!in_array ($row ['name'], array_keys ($dataFields))) {
				continue;
			}
			if (!empty ($row['subfieldsid'])) {
				$sqlDel = 'DELETE FROM vtiger_subfields_values WHERE subfieldsid = ? AND modulecfid=? ';
				$adb->pquery ($sqlDel, array ($row['subfieldsid'],$recordid));
				unset ($sqlDel);
			}
			if ((key_exists ('subfieldsid', $row)) && ($row['name'] != 'Acciones')) {
				$dataField = vtlib_purify ($dataFields [ $row['name'] ]);
				
				if ((is_array ($dataField)) && ($row['uitype'] != 2203)) {
					array_shift ($dataField);
				}
				if ($row['uitype'] == 10) {
					$postDelimiter = strpos ($row['relmodule'], '@');
					if ($postDelimiter !== false) {
						if (is_array ($dataField)) {
							array_shift ($dataField);
							array_pop ($dataField);
						}
						organizeRelatedList ($dataField);
					}
				} else if ($row['uitype'] == 4096) {
					array_shift ($dataField);
					$dataFieldToUpload = array ();
					$dataFieldToSave   = array ();
					$totalAttachment   = count ($_REQUEST [ $row['name'] ]);
					$registredDocs     = AttachmentsUtils::getAttachmentsNames ($adb, $recordid, $row['subfieldsid']);
					for ($k = 0; $k < $totalAttachment; $k += 2) {
						$uploadDocs = true;
						list($tableRow, $fieleName) = explode ('@', $_REQUEST [ $row['name'] ][ ($k + 1) ]['filename']);
						$dataFieldToSave [] = array (
							'data'     => $_REQUEST [ $row['name'] ][ $k ]['data'],
							'filename' => $fieleName,
							'tableRow' => $tableRow,
						);
						if (is_array ($registredDocs) && in_array ($fieleName, $registredDocs)) {
							$uploadDocs = false;
						}

						if ($uploadDocs) {
							$dataFieldToUpload[] = array (
								'data'     => $_REQUEST [ $row['name'] ][ $k ]['data'],
								'filename' => $fieleName,
								'tableRow' => $tableRow,
								'isGrid'   => true,
							);
						}
					}

					AttachmentsUtils::saveAttachments ($adb, $recordid, $row['relmodule'], $row['subfieldsid'], $current_user->id, $dataFieldToUpload, $upload_badext);
					unset($dataField);
					$dataField = $dataFieldToSave;
				} else if (in_array($row['uitype'], array(7, 9, 71, 72, 2204))) {
					// Campos numéricos (incluyendo calculados) - convertir del formato del usuario al formato BD
					if (is_array($dataField)) {
						require_once('include/utils/NumberHelper.class.php');
						$numberHelper = NumberHelper::getInstance($adb, $current_user);
						foreach ($dataField as $idx => $numVal) {
							// Permitir el valor 0 (empty() retorna true para "0")
							if ($numVal !== null && $numVal !== '') {
								$dataField[$idx] = $numberHelper->setSaveNumberFormat($numVal);
							}
						}
					}
				}

				$parameter = array (
					$recordid,
					$row['subfieldsid'],
					base64_encode (serialize ($dataField)),
				);
				$adb->pquery ($sql, $parameter);
			}
		}
	}

	/**
	 * @param array $specialValuesInv
	 * @param array $dataValuesInv
	 *
	 * @return array
	 */
	function getRowDataGrid ($specialValuesInv, $dataValuesInv) {
		$totalFieldByRow       = count ($dataValuesInv);
		$totalSpecialValuesInv = count ($specialValuesInv);
		$totalRowLoop          = ($totalFieldByRow > $totalSpecialValuesInv) ? $totalFieldByRow : $totalSpecialValuesInv;
		$dataGrid              = array ();
		for ($k = 0; $k < $totalRowLoop; $k++) {
			if (key_exists ($k, $specialValuesInv)) {
				foreach ($specialValuesInv[ $k ] as $field) {
					$rowGrid[] = $field;
				}
			} else {
				foreach ($specialValuesInv[0] as $field) {
					$rowGrid[] = '';
				}
			}
			unset($field);
			if (key_exists ($k, $dataValuesInv)) {
				foreach ($dataValuesInv[ $k ] as $field) {
					$rowGrid[] = $field;
				}
			}
			$dataGrid[] = $rowGrid;
			unset($rowGrid);
		}
		return $dataGrid;
	}

	/**
	 *
	 * @param array $dataValues
	 *
	 * @return array
	 */
	function changeColumnsToRow ($dataValues) {
		$column = count ($dataValues);
		$row    = count ($dataValues[0]);
		for ($c = 0; $c < $column; $c++) {
			for ($r = 0; $r < $row; $r++) {
				$dataValuesInv[ $r ][] = $dataValues[ $c ][ $r ];
			}
		}
		return $dataValuesInv;
	}

	/**
	 *
	 * @param array $listSpecialField
	 * @param array $lstSubCampos
	 * @param array $listIds
	 */
	function addSpecialFieldToGridField (&$listSpecialField, &$lstSubCampos, $listIds) {
		for ($j = count ($listSpecialField); $j >= 0; $j--) {
			if (in_array ($listSpecialField[ $j ]['subfieldsid'], $listIds)) {
				$listSpecialField[ $j ]['uitype'] = 2202;
				array_unshift ($lstSubCampos, $listSpecialField[ $j ]);
			}
		}
	}

	/**
	 *
	 * @param array $actionField
	 * @param integer $idField
	 *
	 * @return string
	 */
	function updateFieldName ($actionField, $idField) {
		$arryActionField = unserialize (base64_decode ($actionField));
		if ((is_array ($arryActionField)) && (!empty ($arryActionField))) {
			foreach ($arryActionField as $key => $value) {
				if (empty($value)) {
					continue;
				}
				$arryActionField[ $key ] = $value . '_' . $idField;
			}
		}
		return json_encode ($arryActionField);
	}

	/**
	 *
	 * @param array $lstSubCampos
	 */
	function searchByActionInGrid (&$lstSubCampos) {
		$totalSubCampos = count ($lstSubCampos);
		for ($k = 0; $k < $totalSubCampos; $k++) {
			if ($lstSubCampos[ $k ]['uitype'] == 15) {
				$lstSubCampos[ $k ]['values']                = unserialize (base64_decode ($lstSubCampos[ $k ]['values']));
				$lstSubCampos[ $k ]['valor']                 = unserialize (base64_decode ($lstSubCampos[ $k ]['valor']));
				$lstSubCampos[ $k ]['values']['Seleccionar'] = 'Seleccionar';
				asort ($lstSubCampos[ $k ]['values']);
				if ($lstSubCampos[ $k ]['action_field'] != 'null') {
					$lstSubCampos[ $k ]['action_field'] = updateFieldName ($lstSubCampos[ $k ]['action_field'], $lstSubCampos[ $k ]['fieldid']);
				}
			} else if ($lstSubCampos[ $k ]['uitype'] == 56) {
				if ($lstSubCampos[ $k ]['action_field'] != 'null') {
					$lstSubCampos[ $k ]['action_field'] = updateFieldName ($lstSubCampos[ $k ]['action_field'], $lstSubCampos[ $k ]['fieldid']);
				}
			} else if ($lstSubCampos[ $k ]['uitype'] == 10) {
				if ($lstSubCampos[ $k ]['action_field'] != 'null') {
					$lstSubCampos[ $k ]['action_field'] = json_encode (unserialize (base64_decode ($lstSubCampos[ $k ]['action_field'])));
				}
			}
		}
	}

	/**
	 *
	 * @param $lstSubCampos
	 */
	function searchByFilterInGrid (&$lstSubCampos) {
		$totalSubCampos = count ($lstSubCampos);
		for ($k = 0; $k < $totalSubCampos; $k++) {
			if (($lstSubCampos[ $k ]['filter_field'] != 'NULL' || $lstSubCampos[ $k ]['filter_field'] != 'null') && !empty($lstSubCampos[ $k ]['filter_field'])) {
				$arrayFilter                        = unserialize (base64_decode ($lstSubCampos[ $k ]['filter_field']));
				$lstSubCampos[ $k ]['filter_field'] = json_encode ($arrayFilter);
			}
		}
	}

	/**
	 *
	 * @param array $lstSubCampos
	 *
	 * @return integer
	 * @throws Exception
	 */
	function searchByImportedColumns (&$lstSubCampos) {
		global $adb;
		$totalSubCampos = count ($lstSubCampos);
		$numberOfRows   = 0;
		for ($k = 0; $k < $totalSubCampos; $k++) {
			if ($lstSubCampos[ $k ]['uitype'] == 2202) {
				if (($lstSubCampos[ $k ]['data_field'] != 'NULL' || $lstSubCampos[ $k ]['data_field'] != 'null') && !empty($lstSubCampos[ $k ]['data_field'])) {
					$arrayData = explode ('@', $lstSubCampos[ $k ]['data_field']);
					$sql       = 'SELECT field_values FROM vtiger_subfields_values WHERE  modulecfid = ? AND subfieldsid= ?';
					$results   = $adb->pquery ($sql, $arrayData);
					$numOfRows = $adb->num_rows ($results);
					if ($numOfRows > 0) {
						$fieldValues                      = $adb->query_result ($results, 0, 'field_values');
						$lstSubCampos[ $k ]['data_field'] = unserialize (base64_decode ($fieldValues));
						if ($numberOfRows < count ($lstSubCampos[ $k ]['data_field'])) {
							$numberOfRows = count ($lstSubCampos[ $k ]['data_field']);
						}
					}
				}
			}
		}
		return $numberOfRows;
	}

	/**
	 * @param array $lstSubCampos
	 */
	function searchBySummaryRow (&$lstSubCampos) {
		global $adb;
		$subCampoIndex  = 0;
		$totalSubCampos = count ($lstSubCampos);
		for ($k = 0; $k < $totalSubCampos; $k++) {
			if ($lstSubCampos[ $k ]['uitype'] == 2203) {
				$fieldName = isset($lstSubCampos[$k]['name']) ? $lstSubCampos[$k]['name'] : 'N/A';
				$dataField = $lstSubCampos[ $k ]['data_field'];
				
				$arraySummary  = unserialize (base64_decode ($lstSubCampos[ $k ]['data_field']));
				$subCampoIndex = $k;
				
				if ($arraySummary) {
					foreach ($arraySummary as $index => $summaryItem) {
						$action = isset($summaryItem['action']) ? $summaryItem['action'] : 'N/A';
						$calculatedId = isset($summaryItem['calculatedId']) ? $summaryItem['calculatedId'] : 'N/A';
					}
				} 
			}
		}

		if (isset($arraySummary)) {
			require_once ('modules/calculated_fields/CalculatedFields.class.php');
			$platform        = $_SESSION ['plat'];
			$objectCalulated = new CalculatedFieldsUtils ($adb, $platform);
			$summaryIndex    = 0;
			foreach ($arraySummary as $row) {
				$action = isset($row['action']) ? $row['action'] : 'N/A';
				$originalCalculatedId = isset($row['calculatedId']) ? $row['calculatedId'] : 'N/A';
				
				if ($row['action'] == 'sys') {
					$calculatedResult = $objectCalulated->getCaculateSystemById ($row['calculatedId']);
					$arraySummary[ $summaryIndex ]['calculatedId'] = $calculatedResult;
				} 
				$summaryIndex++;
			}
			$lstSubCampos[ $subCampoIndex ]['data_field'] = $arraySummary;
		} 
	}

	/**
	 *
	 * @param array $lstSubCampos
	 * @param array $dataValuesInv
	 */
	function completeValues ($lstSubCampos, &$dataValuesInv) {
		$totalSubCampos = count ($lstSubCampos);
		for ($k = 0; $k < $totalSubCampos; $k++) {
			if ($lstSubCampos[ $k ]['uitype'] == 2202) {
				$totalDataInv = count ($dataValuesInv);
				for ($j = 0; $j < $totalDataInv; $j++) {
					array_splice ($dataValuesInv[ $j ], $k, 0, '');
				}
			}
		}
	}

	/**
	 *
	 * @param array $lstSubCampos
	 * @param integer $id
	 * @param vtigerCRM_Smarty $smarty
	 *
	 * @return array
	 */
	function getDataFromId (&$lstSubCampos, $id, &$smarty) {
		global $adb;
		$totalListSubCampos = count ($lstSubCampos);
		for ($k = 0; $k < $totalListSubCampos; $k++) {
			if ($lstSubCampos[ $k ]['uitype'] != '2202') {
				if (key_exists ('subfieldsid', $lstSubCampos[ $k ])) {
					$subfieldsId = $lstSubCampos[ $k ]['subfieldsid'];
					$fieldName = isset($lstSubCampos[$k]['name']) ? $lstSubCampos[$k]['name'] : 'N/A';
					$uitype = $lstSubCampos[ $k ]['uitype'];				
					$valueOfField = getFieldGridValue ($lstSubCampos[ $k ]['subfieldsid'], $id);
					if ($valueOfField) {
						if ($lstSubCampos[ $k ]['uitype'] == '2203') {
							$summaryValues = unserialize (base64_decode ($valueOfField));
							
							if ($summaryValues) {
								foreach ($summaryValues as $index => $summaryValue) {
									$summaryInfo = is_array($summaryValue) ? json_encode($summaryValue) : $summaryValue;
								}
								
								// Crear mapeo correcto basado en la configuración del campo summary
								$summaryConfig = isset($lstSubCampos[$k]['data_field']) ? unserialize(base64_decode($lstSubCampos[$k]['data_field'])) : null;
								if ($summaryConfig && is_array($summaryConfig)) {
									$mappedSummaryValues = array();
									
									// Inicializar NumberHelper para formateo numérico
									global $current_user, $adb;
									require_once('include/utils/NumberHelper.class.php');
									$numberHelper = NumberHelper::getInstance($adb, $current_user);
									
									foreach ($summaryConfig as $configIndex => $config) {
										if (isset($summaryValues[$configIndex])) {
											$rawValue = $summaryValues[$configIndex];
											$fieldName = isset($config['field']) ? $config['field'] : 'N/A';
											
											// Formatear valor según el tipo de campo referenciado
											if ($rawValue != 0 && is_numeric($rawValue)) {
												// Buscar el uitype del campo referenciado
												$referencedUitype = null;
												foreach ($lstSubCampos as $subField) {
													if ($subField['name'] == $fieldName) {
														$referencedUitype = $subField['uitype'];
														break;
													}
												}
												
												if ($referencedUitype == 71) {
													// Campo de moneda - usar CurrencyField
													require_once('include/fields/CurrencyField.php');
													$currencyField = new CurrencyField($rawValue);
													$formattedValue = $currencyField->getDisplayValue();
												} elseif ($referencedUitype == 72) {
													// Campo de moneda de inventario - usar CurrencyField
													require_once('include/fields/CurrencyField.php');
													$currencyField = new CurrencyField($rawValue);
													$formattedValue = $currencyField->getDisplayValue(null, true);
												} elseif (in_array($referencedUitype, array(7, 9, 2204))) {
													// Campos numéricos, porcentajes y calculados - usar NumberHelper
													$formattedValue = $numberHelper->setNumberFormat($rawValue, $fieldName);
												} else {
													// Otros tipos - sin formateo
													$formattedValue = $rawValue;
												}
											} else {
												$formattedValue = $rawValue;
											}
											
											$mappedSummaryValues[$configIndex] = $formattedValue;
										} else {
											$mappedSummaryValues[$configIndex] = $numberHelper->getDefaultValue();
										}
									}
									$smarty->assign ('summaryValues', $mappedSummaryValues);
								} else {
									$smarty->assign ('summaryValues', $summaryValues);
								}
							} 
						} else if ($lstSubCampos[ $k ]['uitype'] == '4096') {
							$attachValues = unserialize (base64_decode ($valueOfField));
							$smarty->assign ('ATTACHMENTS_DATA', $attachValues);
							$smarty->assign ('ATTACHMENTS', AttachmentsUtils::fetchAttachmentsFromGrid ($adb, $id, $lstSubCampos[ $k ]['subfieldsid']));
							$dataValues[] = array ();
						} else if ($lstSubCampos[ $k ]['uitype'] == '5') {
							// Campo fecha - convertir de formato BD al formato del usuario
							$dateValues = unserialize (base64_decode ($valueOfField));
							if (is_array($dateValues)) {
								foreach ($dateValues as $idx => $dateVal) {
									if (!empty($dateVal) && $dateVal !== '0000-00-00') {
										$dateValues[$idx] = DateTimeField::convertToUserFormat($dateVal);
									}
								}
							}
							$dataValues[] = $dateValues;
						} else if (in_array($lstSubCampos[ $k ]['uitype'], array('7', '9', '71', '72', '2204'))) {
							// Campos numéricos y calculados - mantener en formato BD, el template formateará
							$dataValues[] = unserialize (base64_decode ($valueOfField));
						} else {
							$dataValues[] = unserialize (base64_decode ($valueOfField));
						}
					} else {
						$dataValues[] = array ();
					}
				}
			}
		}
		$dataValuesInv = changeColumnsToRow ($dataValues);
		if (count ($dataValuesInv) != count ($lstSubCampos)) {
			completeValues ($lstSubCampos, $dataValuesInv);
		}
		unset($dataValues);
		return $dataValuesInv;
	}

	/**
	 *
	 * @param string $module
	 * @param integer $id
	 * @param boolean $swDetailView
	 * @param array|null $originData
	 *
	 * @return array
	 * @throws Exception
	 * @throws SmartyException
	 */
	function escribeCamposGrid ($module, $id, $swDetailView, $originData = null) {
		global $adb;
		$id          = (!isset($id)) ? '' : $id;
		$orgCurrency = HomeUtils::getOrganizationCurrency ($adb);
		$currency    = (!empty($orgCurrency)) ? $orgCurrency['currency_symbol'] : '';
		$smarty      = new vtigerCRM_Smarty();
		$lstCampos = obtieneListaCamposCampoGrid ($module);
		$bufferOut = array ();
		$totalList = count ($lstCampos);
		for ($i = 0; $i < $totalList; $i++) {
			$gridFieldName = $lstCampos[$i]['fieldname'];
			$gridFieldId = $lstCampos[$i]['fieldid'];
			
			$lstSubCampos = obtieneListaSubCamposCampoGrid ($lstCampos[ $i ]['fieldid'], true, false);
			$totalSubCampos = count($lstSubCampos);
			if (!empty($id)) {
				$dataValuesInv = getDataFromId ($lstSubCampos, $id, $smarty);
			} else if (!empty($originData) && is_array ($originData)) {
				$originCampos = obtieneListaCamposCampoGrid ($originData['module']);
				if (($lstCampos[ $i ]['fieldname'] == $originData['destination_field']) && ($originCampos[ $i ]['fieldname'] == $originData['origin_field'])) {
					$originSubCampos = obtieneListaSubCamposCampoGrid ($originCampos[ $i ]['fieldid'], true, false);
					$dataValuesInv   = getDataFromId ($originSubCampos, $originData['record'], $smarty);
					$originData      = null;
				}
			}
			searchByActionInGrid ($lstSubCampos);
			searchByFilterInGrid ($lstSubCampos);
			searchBySummaryRow ($lstSubCampos);
			$numberOfRows = searchByImportedColumns ($lstSubCampos);
			if (isset($dataValuesInv)) {
				$smarty->assign ('dataValues', $dataValuesInv);
			} else {
				// Ensure dataValues is always set to avoid hiding the grid in DetailView
				$smarty->assign ('dataValues', array());
			}
			
			// Pasar información del usuario para formateo de números
			global $current_user;
			$smarty->assign('current_user', $current_user);
			// Set currency on invoice table
			if ($module == 'facturas') {
				$totalSubField = count($lstSubCampos);
				for ($n = 0; $n < $totalSubField; $n++) {
					if (in_array($lstSubCampos[ $n ]['label'], array('Precio', 'Subtotal', 'Total', 'TOTALES'))) {
						$lstSubCampos[ $n ]['label']  .= ' (' . $currency . ')';
					}
				}
			}

			// Calcular anchos proporcionales
			$totalWidth = 0;
			$totalSubFields = count($lstSubCampos);
			
			
			// Calcular suma total de anchos
			for ($j = 0; $j < $totalSubFields; $j++) {
				$fieldName = isset($lstSubCampos[$j]['name']) ? $lstSubCampos[$j]['name'] : 'N/A';
				$fieldLabel = isset($lstSubCampos[$j]['label']) ? $lstSubCampos[$j]['label'] : 'N/A';
				$uitype = $lstSubCampos[$j]['uitype'];
				$dbLength = isset($lstSubCampos[$j]['length']) ? $lstSubCampos[$j]['length'] : 'N/A';
				
				if ($lstSubCampos[$j]['uitype'] == 99) {
					// Columna de eliminar: ancho fijo de 10
					$fieldWidth = 10;
					$totalWidth += $fieldWidth;
				} elseif ($lstSubCampos[$j]['uitype'] == 4096) {
					// Columna de adjuntos: ancho fijo de 15
					$fieldWidth = 15;
					$totalWidth += $fieldWidth;
				} elseif ($lstSubCampos[$j]['uitype'] == 10) {
					// Campos de referencia a módulo: usar ancho específico o por defecto
					if (!empty($lstSubCampos[$j]['length']) && $lstSubCampos[$j]['length'] > 0) {
						$fieldWidth = $lstSubCampos[$j]['length'];
					} else {
						$fieldWidth = 40; // Ancho por defecto para uitype 10
					}
					$totalWidth += $fieldWidth;
				} elseif (!empty($lstSubCampos[$j]['length']) && $lstSubCampos[$j]['length'] > 0) {
					// Usar ancho de la base de datos
					$fieldWidth = $lstSubCampos[$j]['length'];
					$totalWidth += $fieldWidth;
				} else {
					// Anchos por defecto específicos según uitype
					switch ($lstSubCampos[$j]['uitype']) {
						case 7:  // Campos numéricos
							$fieldWidth = 15;
							break;
						case 9:  // Campos de porcentaje
							$fieldWidth = 12;
							break;
						case 2204: // Campos calculados
							$fieldWidth = 18;
							break;
						case 2203: // Campos de fila resumen
							$fieldWidth = 18;
							break;
						default:
							$fieldWidth = 20; // Ancho por defecto general
							break;
					}
					$totalWidth += $fieldWidth;
				}
			}
			
			
			// Calcular porcentajes proporcionales
			
			for ($j = 0; $j < $totalSubFields; $j++) {
				$fieldName = isset($lstSubCampos[$j]['name']) ? $lstSubCampos[$j]['name'] : 'N/A';
				$fieldLabel = isset($lstSubCampos[$j]['label']) ? $lstSubCampos[$j]['label'] : 'N/A';
				$uitype = $lstSubCampos[$j]['uitype'];
				$fieldWidth = 0;
				
				if ($lstSubCampos[$j]['uitype'] == 99) {
					$fieldWidth = 10;
				} elseif ($lstSubCampos[$j]['uitype'] == 4096) {
					$fieldWidth = 10;
				} elseif ($lstSubCampos[$j]['uitype'] == 10) {
					// Campos de referencia a módulo: usar ancho específico o por defecto
					if (!empty($lstSubCampos[$j]['length']) && $lstSubCampos[$j]['length'] > 0) {
						$fieldWidth = $lstSubCampos[$j]['length'];
					} else {
						$fieldWidth = 40; // Ancho por defecto para uitype 10
					}
				} elseif (!empty($lstSubCampos[$j]['length']) && $lstSubCampos[$j]['length'] > 0) {
					$fieldWidth = $lstSubCampos[$j]['length'];
				} else {
					// Anchos por defecto específicos según uitype
					switch ($lstSubCampos[$j]['uitype']) {
						case 7:  // Campos numéricos
							$fieldWidth = 15;
							break;
						case 9:  // Campos de porcentaje
							$fieldWidth = 12;
							break;
						case 2204: // Campos calculados
							$fieldWidth = 18;
							break;
						case 2203: // Campos de fila resumen
							$fieldWidth = 18;
							break;
						default:
							$fieldWidth = 20; // Ancho por defecto general
							break;
					}
				}
				
				// Calcular porcentaje proporcional: (ancho del campo / suma total) * 100
				$proportionalWidth = round(($fieldWidth / $totalWidth) * 100, 2);
				$lstSubCampos[$j]['proportional_width'] = $proportionalWidth;
			}
			

			$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
			$smarty->assign ('swDetailView', $swDetailView);
			$smarty->assign ('fieldname', $lstCampos[ $i ]['fieldname']);
			$smarty->assign ('fieldlabel', $lstCampos[ $i ]['fieldlabel']);
			$smarty->assign ('lstSubCampos', $lstSubCampos);
			$smarty->assign ('numSubCampos', count ($lstSubCampos));
			$smarty->assign ('numberOfRows', $numberOfRows);
			$bufferOut[ $lstCampos[ $i ]['fieldid'] ] = $smarty->fetch ('Settings/GridTable.tpl');
			$smarty->assign ('dataValues', '');
			unset($dataValuesInv);
		}
		return $bufferOut;
	}

	/**
	 *
	 * @param  PearDatabase $adb
	 * @param stdClass $current_user
	 * @param integer $fieldid
	 *
	 * @return string
	 */
	function upDateGridDefaultValues ($adb, $current_user, $fieldid) {
		$lstCampos = obtieneListaSubCamposCampoGrid ($fieldid, false);
		$returnMsn = '';
		$sgl       = 'UPDATE `vtiger_subfields_special` SET `defaultvalue` = ?
		                WHERE `subfieldsid` = ? AND `fieldid` = ? ';
		foreach ($lstCampos as $row) {
			if ((key_exists ('subfieldsid', $row)) && ($row['name'] != 'Acciones')) {
				$dataField = (isset ($_REQUEST [ $row['name'] ])) && ($_REQUEST [ $row['name'] ] != '') ? vtlib_purify ($_REQUEST [ $row['name'] ]) : '';
				if (!empty($dataField)) {
					if ($row['uitype'] == 5 || $row['uitype'] == 6 || $row['uitype'] == 23) {
						if (isset($current_user->date_format)) {
							$fldvalue = getValidDBInsertDateValue ($dataField[0]);
						} else {
							$fldvalue = $dataField[0];
						}
					} else {
						$fldvalue = $dataField[0];
					}
					$parameter = array (
						$fldvalue,
						$row['subfieldsid'],
						$fieldid,
					);
					$adb->pquery ($sgl, $parameter);
					$returnMsn .= $row['label'] . ' ha sido Actualizado <br />';
				}
			}
		}
		return $returnMsn;
	}

	/**
	 * @param PearDatabase $adb
	 * @param integer $userId
	 *
	 * @return string
	 */
	function getDefaultModuleByUserId (PearDatabase $adb, $userId) {
		$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE id=?', array ($userId));
		if ($adb->num_rows ($result) > 0) {
			$row = $adb->fetchByAssoc ($result, -1, false);
			if (!empty ($row ['default_module'])) {
				$defaultModule = $row ['default_module'];
			} else {
				$defaultModule = null;
			}
		} else {
			$defaultModule = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $defaultModule;
	}

	/**
	 * @param PearDatabase $adb
	 * @param integer $userId
	 *
	 * @return string
	 */
	function getDefaultModuleByUserRole (PearDatabase $adb, $userId) {
		$result = $adb->pquery ('SELECT r.* FROM vtiger_role r INNER JOIN vtiger_user2role u2r ON u2r.roleid=r.roleid AND u2r.userid=?', array ($userId));
		if ($adb->num_rows ($result) > 0) {
			$row    = $adb->fetchByAssoc ($result, -1, false);
			$roleId = $row ['roleid'];
		} else {
			$roleId = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		if (empty ($roleId)) {
			return null;
		}

		$result = $adb->pquery ('SELECT * FROM vtiger_role WHERE roleid=?', array ($roleId));
		if ($adb->num_rows ($result) > 0) {
			$row = $adb->fetchByAssoc ($result, -1, false);
			if (!empty ($row ['default_module'])) {
				$defaultModule = $row ['default_module'];
			} else {
				$defaultModule = null;
			}
		} else {
			$defaultModule = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $defaultModule;
	}

	/**
	 * @param PearDatabase $adb
	 *
	 * @return string
	 */
	function getDefaultModuleByOrganization (PearDatabase $adb) {
		$result = $adb->query ('SELECT * FROM vtiger_organizationdetails LIMIT 1');
		if ($adb->num_rows ($result) > 0) {
			$row = $adb->fetchByAssoc ($result, -1, false);
			if (!empty ($row ['default_module'])) {
				$defaultModule = $row ['default_module'];
			} else {
				$defaultModule = null;
			}
		} else {
			$defaultModule = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $defaultModule;
	}

	/**
	 * @return string
	 */
	function getDefaultModule () {
		global $adb;

		$userId = $_SESSION ['authenticated_user_id'];
		if (empty ($userId)) {
			return '';
		}

		$defaultModule = getDefaultModuleByUserId ($adb, $userId);
		if (!empty ($defaultModule)) {
			return $defaultModule;
		}

		$defaultModule = getDefaultModuleByUserRole ($adb, $userId);
		if (!empty ($defaultModule)) {
			return $defaultModule;
		}

		$defaultModule = getDefaultModuleByOrganization ($adb);
		if (!empty ($defaultModule)) {
			return $defaultModule;
		} else {
			return 'Walkthrough';
		}
	}

	/**
	 * @return string
	 */
	function obtenerTemaDefecto () {
		global $adb;

		$result = $adb->query ('SELECT theme FROM vtiger_organizationdetails');
		if ($adb->num_rows ($result) > 0) {
			$row   = $adb->fetchByAssoc ($result, -1, false);
			$theme = $row ['theme'];
		} else {
			$theme = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $theme;
	}

	/**
	 * @param string $module
	 *
	 * @return integer
	 */
	function determinarFiltroListasModulo ($module) {
		global $adb;

		$result = $adb->pquery ('SELECT permite_filtros_listas FROM vtiger_tab WHERE name=?', array ($module));
		if ($adb->num_rows ($result) > 0) {
			$row    = $adb->fetchByAssoc ($result, -1, false);
			$allows = $row ['permite_filtros_listas'];
		} else {
			$allows = 0;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $allows;
	}

	/**
	 * @return array
	 */
	function getVistasDisponiblesParaBotonesPersonalizados () {
		return array (
			array ('name' => 'DetailView', 'label' => 'Detalle de registros'),
			array ('name' => 'ListView', 'label' => 'Listas de registros'),
		);
	}

	/**
	 * @return array
	 */
	function getTiposDisponiblesParaBotonesPersonalizados () {
		return array (
			array ('name' => 'js', 'label' => 'JavaScript'),
			array ('name' => 'link', 'label' => 'Enlace'),
			array ('name' => 'backgroundtask', 'label' => 'Tarea en segundo plano'),
		);
	}

	/**
	 * @param $variable
	 * @param $module
	 *
	 * @return string
	 */
	function obtenerValorVariable ($variable, $module) {
		global $adb;

		if (!existeTabla ('vtiger_variables')) {
			return null;
		}

		$result = $adb->pquery (
			'SELECT
				value
			FROM
				vtiger_variables A
				INNER JOIN vtiger_tab B ON A.tabid=B.tabid
			WHERE
				varname=? AND name=?',
			array ($variable, $module)
		);
		if ($result && $adb->num_rows ($result) == 1) {
			$row   = $adb->fetchByAssoc ($result, -1, false);
			$value = $row ['value'];
		} else {
			$value = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $value;
	}

	/**
	 * @param $variable
	 * @param $valor
	 * @param $module
	 */
	function setValueVariable ($variable, $valor, $module) {
		global $adb;

		$tabid  = getTabid ($module);
		$result = $adb->pquery (
			'SELECT
				variableid
			FROM
				vtiger_variables A
				INNER JOIN vtiger_tab B ON A.tabid=B.tabid
			WHERE
				varname=? AND
				name=?',
			array ($variable, $module)
		);
		if ($result) {
			$row        = $adb->fetchByAssoc ($result);
			$variableId = $row ['variableid'];
		} else {
			$variableId = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		if (empty ($variableId)) {
			$query = 'INSERT INTO vtiger_variables VALUES (NULL, ?, ?, ?)';
			$adb->pquery ($query, array ($tabid, $variable, $valor));
		} else {
			$query = 'UPDATE vtiger_variables SET value=? WHERE tabid=? AND varname=?';
			$adb->pquery ($query, array ($valor, $tabid, $variable));
		}
	}

	/**
	 * @param $id
	 * @param $relmodule
	 *
	 * @return mixed|null|string
	 * @throws Exception
	 */
	function getRecordRelatedList ($id, $relmodule) {
		global $adb;

		$result = $adb->pquery (
			'SELECT
				COUNT(*) AS cantidad
			FROM
				vtiger_crmentityrel
				INNER JOIN vtiger_crmentity ON vtiger_crmentityrel.relcrmid=vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0
			WHERE
				vtiger_crmentityrel.crmid=? AND
				vtiger_crmentityrel.relmodule=?',
			array ($id, $relmodule)
		);
		if ($adb->num_rows ($result) > 0) {
			$qty = $adb->query_result ($result, 0, 'cantidad');
		} else {
			$qty = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $qty;
	}

	/**
	 * @param $focus
	 * @param $module
	 * @param $id
	 * @param $relmodule
	 * @param $fieldvalue
	 * @param $relfield
	 */
	function saveRecordRelatedListAutomatic ($focus, $module, $id, $relmodule, $fieldvalue, $relfield) {
		global $adb;

		$relids = array ();
		$where  = " AND {$relfield}='{$fieldvalue}'";
		$sql    = getListQuery ($relmodule, $where);
		$result = $adb->query ($sql);
		while ($row = $adb->fetchByAssoc ($result)) {
			$relids[] = $row['crmid'];
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		relateEntities ($focus, $module, $id, $relmodule, $relids);
	}

	/**
	 * @param string $module
	 *
	 * @return array
	 */
	function getRelatedListaAutomatic ($module) {
		global $adb;

		$lst    = array ();
		$tabid  = getTabid ($module);
		$result = $adb->pquery (
			'SELECT
				vtiger_tab.name,
				relfield
			FROM
				vtiger_relatedlists
				INNER JOIN vtiger_tab ON vtiger_relatedlists.related_tabid=vtiger_tab.tabid
			WHERE
				vtiger_relatedlists.tabid=? AND
				vtiger_relatedlists.presence IN (0, 2) AND
				vtiger_relatedlists.actions LIKE ?',
			array ($tabid, '%ADD_AUTOMATIC%')
		);
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			list($field, $relfield) = explode ('||', $row ['relfield']);
			$lst [] = array (
				'relmodule' => $row ['name'],
				'field'     => $field,
				'relfield'  => $relfield,
			);
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $lst;
	}

	/**
	 * Writes a javascript just once
	 *
	 * @param string $js
	 * @param boolean $returnInBuffer
	 *
	 * @return string
	 */
	function writeJsOnce ($js, $returnInBuffer = false) {
		static $writedList = array ();
		$output = '';

		if (!in_array ($js, $writedList)) {
			if (!$returnInBuffer) {
				echo "<script src=\"include/js/{$js}.js\"></script>";
			} else {
				$output = "<script src=\"include/js/{$js}.js\"></script>";
			}
			$writedList[] = $js;
		}

		return $output;
	}

	/**
	 * Gets the related list property given in $property
	 *
	 * @param integer $relationid
	 * @param string $property
	 *
	 * @return string
	 */
	function getRelatedListProperty ($relationid, $property) {
		global $adb;
		$result = $adb->query ("SHOW COLUMNS FROM vtiger_relatedlists_properties LIKE '$property'");
		if ($adb->num_rows ($result) > 0) {
			list ($value) = $adb->fetch_row ($adb->pquery ("select $property from vtiger_relatedlists_properties where relation_id=?", array ($relationid)));
			$returnValue = $value;
		} else {
			$returnValue = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $returnValue;
	}

	/**
	 * @return string
	 */
	function getArticleActionModule () {
		return '';
	}

	/**
	 * @return string
	 */
	function getMenuQuickCreate ($filterOption = array ()) {
		$createLabel  = getTranslatedString ('LBL_CREATE_BUTTON_LABEL');
		$hiddenMenu   = array (4);
		$hiddenModule = array ('platzi_issabel');
		$header       = getHeaderArray ($hiddenMenu);
		if (count ($header)) {
			$items   = array ();
			/** @note no is it necessary to remove the last option  */
			//array_pop ($header);
			foreach ($header as $detail) {
				if ((!in_array($detail['id'], $filterOption)) ) {
					continue;
				}
				$items [] = "<li style=\"padding: 0 4px\"><h6>{$detail ['name']}</h6></li>";
				foreach ($detail['elementos'] as $detailDos) {
					if ((in_array($detailDos['name'], $hiddenModule)) || empty ($detailDos['name'])) {
						continue;
					}
					$items [] = "<li ><a class=\"module-fast-create-item\" href=\"index.php?module={$detailDos ['name']}&action=EditView&return_action=DetailView&parenttab=Administraci%C3%B3n&mode=create\"><i class=\"fa fa-plus-circle\"></i>{$createLabel} {$detailDos ['label']}</a></li>";
				}
			}
		} else {
			$items = '';
		}
		$items = join ('', $items);
		return "<ul class=\"dropdown-menu platzilla-fast-create\" style=\"max-height: 90vh; overflow-x: hidden; overflow-y: auto;\">{$items}</ul>";
	}

	/**
	 * @param PearDatabase $adb
	 *
	 * @return array
	 */
	function getModuleActive ($adb) {
		$result = $adb->query ('SELECT name FROM vtiger_tab WHERE presence=0');

		if ($result) {
			$lst = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$lst[] = $row;
			}
		} else {
			$lst = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $lst;
	}

	/**
	 * To retreive the vtiger_tab permissions of the specifed user from the various vtiger_profiles associated with the user
	 *
	 * @param integer $userId The User Id
	 *
	 * @return array user global permission  array in the following format: $tabPerrArray=(tabid1=>permission, tabid2=>permission);
	 */
	function getUserTabsPermissions ($userId) {
		global $adb;

		$profArr  = getUserProfile ($userId);
		$result   = $adb->pquery (
			'SELECT
				t.tabid,
				t.name,
				t.tablabel,
				ptt.parenttab_label
			FROM
				vtiger_profile2tab pt
				INNER JOIN vtiger_tab t ON t.tabid=pt.tabid
				INNER JOIN vtiger_parenttabrel ptr ON ptr.tabid=pt.tabid
				INNER JOIN vtiger_parenttab ptt ON ptt.parenttabid=ptr.parenttabid
			WHERE
				pt.profileid=? AND
				t.presence IN (0,2)
			GROUP BY
				pt.tabid
			ORDER BY
				ptr.parenttabid ASC',
			array ($profArr)
		);
		$num_rows = $adb->num_rows ($result);
		if ($num_rows > 0) {
			$ret = array ();
			while ($r = $adb->fetchByAssoc ($result)) {
				$ret [] = $r;
			}
		} else {
			$ret = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $ret;
	}

	/**
	 * Default (generic) function to handle the related list for the module.
	 * NOTE: Vtiger_Module::setRelatedList sets reference to this function in vtiger_relatedlists table
	 * if function name is not explicitly specified.
	 *
	 * @param $cur_tab_id
	 * @param $rel_tab_id
	 *
	 * @return string
	 */
	function getQueryRelatedlist ($cur_tab_id, $rel_tab_id) {
		global $adb, $currentModule;

		$related_module = vtlib_getModuleNameById ($rel_tab_id);
		$modulebegin    = vtlib_getModuleNameById ($cur_tab_id);
		$other          = CRMEntity::getInstance ($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars ($currentModule, $this);
		vtlib_setup_modulevars ($related_module, $other);

		$query       = "SELECT vtiger_crmentity.*, {$other->table_name}.*";
		$userNameSql = getSqlForNameInDisplayFormat (
			array ('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'),
			'Users'
		);
		$query .= ", CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN {$userNameSql} ELSE vtiger_groups.groupname END AS user_name";

		$more_relation = '';
		if (!empty($other->related_tables)) {
			foreach ($other->related_tables as $tname => $relmap) {
				$query .= ", {$tname}.*";

				// Setup the default JOIN conditions if not specified
				if (empty($relmap[1])) {
					$relmap[1] = $other->table_name;
				}
				if (empty($relmap[2])) {
					$relmap[2] = $relmap[0];
				}
				$more_relation .= " LEFT JOIN {$tname} ON {$tname}.{$relmap[0]}={$relmap[1]}.{$relmap[2]}";
			}
		}

		$query .= " FROM {$other->table_name}";
		$query .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid={$other->table_name}.{$other->table_index}";
		if ($related_module == 'Documents' && $other->table_name == 'vtiger_notes') {
			$query .= ' INNER JOIN vtiger_senotesrel ON vtiger_senotesrel.notesid=vtiger_notes.notesid';
		} else {
			$query .= ' INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid=vtiger_crmentity.crmid OR vtiger_crmentityrel.crmid=vtiger_crmentity.crmid)';
		}
		$query .= $more_relation;
		$query .= ' LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid';
		$query .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid';
		if (($related_module == 'Documents') && ($other->table_name == 'vtiger_notes')) {
			$query .= " WHERE vtiger_crmentity.deleted=0 AND vtiger_senotesrel.crmid>0 AND vtiger_crmentityrel.relmodule='{$related_module}' AND vtiger_crmentityrel.module='{$modulebegin}'";
		} else {
			$query .= " WHERE vtiger_crmentity.deleted=0 AND (vtiger_crmentityrel.crmid>0 OR vtiger_crmentityrel.relcrmid>0) AND vtiger_crmentityrel.relmodule='{$related_module}' AND vtiger_crmentityrel.module='{$modulebegin}'";
		}

		//Se une los datos de los campos 10 para
		$result = $adb->pquery (
			'SELECT
				vtiger_field.fieldid,
				tablename,
				columnname
			FROM
				vtiger_fieldmodulerel
				INNER JOIN vtiger_field ON vtiger_fieldmodulerel.fieldid=vtiger_field.fieldid
			WHERE
				module=? AND
				relmodule=?',
			array ($related_module, $modulebegin)
		);
		while ($row = $adb->fetchByAssoc ($result)) {
			$othertables        = '';
			$othermore_relation = '';
			if (!empty($other->related_tables)) {
				foreach ($other->related_tables as $tname => $relmap) {
					$othertables .= ", {$tname}.*";
					// Setup the default JOIN conditions if not specified
					if (empty($relmap[1])) {
						$relmap[1] = $other->table_name;
					}
					if (empty($relmap[2])) {
						$relmap[2] = $relmap[0];
					}
					$othermore_relation .= " LEFT JOIN {$tname} ON {$tname}.{$relmap[0]}={$relmap[1]}.{$relmap[2]}";
				}
			}

			$query .= " UNION
						SELECT
							vtiger_crmentity.*,
							{$row ['tablename']}.*,
							CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN {$userNameSql} ELSE vtiger_groups.groupname END AS user_name
							{$othertables}
						FROM
							vtiger_crmentity
							INNER JOIN {$row ['tablename']} ON vtiger_crmentity.crmid={$row ['tablename']}.{$other->tab_name_index [$row['tablename']]} AND deleted=0
							LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
							LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
							{$othermore_relation}";
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $query;
	}

	/**
	 * @param $modulename
	 *
	 * @return mixed|null|string
	 * @throws Exception
	 */
	/**
	 * Obtiene el título del módulo desde la base de datos (vtiger_tab.tablabel)
	 * Si no se encuentra en la BD, usa el archivo de idioma como fallback
	 * 
	 * @param string $modulename - Nombre del módulo
	 * @return string - Título del módulo desde vtiger_tab.tab_label o archivo de idioma
	 */
	function getTabIdLabelByName ($modulename) {
		global $adb;

		$result = $adb->pquery ('SELECT tablabel FROM vtiger_tab WHERE name=?', array ($modulename));
		if ($result && $adb->num_rows($result) > 0) {
			$label = $adb->query_result ($result, 0, 'tablabel');
		} else {
			// Fallback: usar traducción de archivos de idioma
			$label = getTranslatedString($modulename, $modulename);
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $label;
	}

	/**
	 * Obtiene el título del módulo desde la base de datos
	 * Función wrapper que proporciona una interfaz clara y semántica
	 * 
	 * @param string $modulename - Nombre del módulo
	 * @return string - Título del módulo
	 */
	function getModuleTitleFromDB($modulename) {
		return getTabIdLabelByName($modulename);
	}

	/**
	 * Obtiene el Hash de Intercom para el usuario actual, conectado en Platzilla
	 * Funcion Agregada por AV para integrar el Chat Intercom de Soporte - 20180606
	 *
	 * @param $currentUserId
	 *
	 * @return string
	 */
	 /* DESACTIVADO EL HELPCRUNCH EL 21/11/2021 - AV */
	/*function getHashHmacHelpCrunch ($currentUserId) {
		$hashHmac = hash_hmac (
			'sha256', // hash function
			$currentUserId, // user's id
			'JS2ehL8zuEwSrD_z1dtogf7I__-fCpl-44q33j4F' // secret key (keep safe!)
		);
		return $hashHmac;
	}*/
	/* DESACTIVADO EL HELPCRUNCH EL 21/11/2021 - AV */
