<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Check for image existence in themes orelse
 * use the common one.
 */
// Let us create cache to improve performance
if(!isset($__cache_vtiger_imagepath)) {
    $__cache_vtiger_imagepath = Array();
}
function vtiger_imageurl($imagename, $themename) {
	global $__cache_vtiger_imagepath;
	if($__cache_vtiger_imagepath[$imagename]) {
        $imagepath = $__cache_vtiger_imagepath[$imagename];
    } else {
		$imagepath = false;
		// Check in theme specific folder
        if(file_exists("themes/$themename/images/$imagename")) {
            $imagepath =  "themes/$themename/images/$imagename";
		} else if(file_exists("themes/images/$imagename")) {
			// Search in common image folder
			$imagepath = "themes/images/$imagename";
		} else {
			// Not found anywhere? Return whatever is sent
			$imagepath = $imagename;
		}
        $__cache_vtiger_imagepath[$imagename] = $imagepath;
    }
	return $imagepath;
}

/**
 * Get module name by id.
 */
function vtlib_getModuleNameById($tabid) {
	global $adb;
	$sqlresult = $adb->pquery("SELECT name FROM vtiger_tab WHERE tabid = ?",array($tabid));
	if($adb->num_rows($sqlresult)) return $adb->query_result($sqlresult, 0, 'name');
	return null;
}

/**
 * Cache the module active information for performance
 */
$__cache_module_activeinfo = Array();

/**
 * Fetch module active information at one shot, but return all the information fetched.
 */
function vtlib_prefetchModuleActiveInfo($force = true) {
	global $__cache_module_activeinfo;

	// Look up if cache has information
	$tabrows = VTCacheUtils::lookupAllTabsInfo();

	// Initialize from DB if cache information is not available or force flag is set
	if($tabrows === false || $force) {
		global $adb;
		$tabres = $adb->query("SELECT * FROM vtiger_tab");
		$tabrows = array();
		if($tabres) {
			while($tabresrow = $adb->fetch_array($tabres)) {
				$tabrows[] = $tabresrow;
				$__cache_module_activeinfo[$tabresrow['name']] = $tabresrow['presence'];
			}
			// Update cache for further re-use
			VTCacheUtils::updateAllTabsInfo($tabrows);
		}
	}

	return $tabrows;
}

/**
 * Check if module is set active (or enabled)
 */
function vtlib_isModuleActive($module) {
	global $adb, $__cache_module_activeinfo;

	if(in_array($module, vtlib_moduleAlwaysActive())){
		return true;
	}


	if(!isset($__cache_module_activeinfo[$module])) {
		$plat = '';
		if (isset($_SESSION['plat']))
			$plat = $_SESSION['plat'].'/';
		//include $plat.'tabdata.php';

		//[ TT11181 ] Ajustes Menú Izquierdo (Módulos/APP´s) Producto Interno (Platzilla)
		//DM 22/06/2016
		//leyendo de la variable de session creada
		$tab_info_array = $_SESSION['authenticated_user_menu']['tabdata']['tab_info_array'];

		//echo "<pre>tab info array ".print_r($tab_info_array ,true)." </pre>";

		$presence = isset($tab_info_array[$module])? 0: 1;
		$__cache_module_activeinfo[$module] = $presence;
	} else {
		$presence = $__cache_module_activeinfo[$module];
	}

	$active = false;
	if($presence != 1) $active = true;

	//echo "<pre>DEBUG 2 $module | presence $presence | return $active | plat $plat </pre>";

	return $active;
}

/**
 * Recreate user privileges files.
 */
function vtlib_RecreateUserPrivilegeFiles() {
	global $adb;
	$userres = $adb->query('SELECT id FROM vtiger_users WHERE deleted = 0');
	if($userres && $adb->num_rows($userres)) {
		while($userrow = $adb->fetch_array($userres)) {
			createUserPrivilegesfile($userrow['id']);
		}
	}
}

/**
 * Get list module names which are always active (cannot be disabled)
 */
function vtlib_moduleAlwaysActive() {
	$modules = Array (
		'Administration', 'CustomView', 'Settings', 'Users', 'Migration',
		'Utilities', 'uploads', 'Import', 'System', 'com_vtiger_workflow', 'PickList',
		'Ondemand','store','notification_center',
	);
	return $modules;
}

/**
 * Toggle the module (enable/disable)
 */
function vtlib_toggleModuleAccess($module, $enable_disable) {
	global $adb, $__cache_module_activeinfo;

	include_once('vtlib/Vtiger/Module.php');

	$event_type = false;

	if($enable_disable === true) {
		$enable_disable = 0;
		$event_type = Vtiger_Module::EVENT_MODULE_ENABLED;
	} else if($enable_disable === false) {
		$enable_disable = 1;
		$event_type = Vtiger_Module::EVENT_MODULE_DISABLED;
	}

	$adb->pquery("UPDATE vtiger_tab set presence = ? WHERE name = ?", array($enable_disable,$module));

	$__cache_module_activeinfo[$module] = $enable_disable;

	create_tab_data_file();
	create_parenttab_data_file();

	// UserPrivilege file needs to be regenerated if module state is changed from
	// vtiger 5.1.0 onwards
	global $vtiger_current_version;
	if(version_compare($vtiger_current_version, '5.0.4', '>')) {
		vtlib_RecreateUserPrivilegeFiles();
	}

	Vtiger_Module::fireEvent($module, $event_type);
}

/**
 * Get list of module with current status which can be controlled.
 */
function vtlib_getToggleModuleInfo() {
	global $adb;

	$modinfo = Array();

	# Si es instancia
	if ( $_SESSION['platInstancia'] != '' ){

		$sql = "SELECT name, presence, customized, isentitytype, combinable,isplatzilla FROM vtiger_tab WHERE name NOT IN ('Users') AND presence IN (0,1) ORDER BY name";

	# Si es plataforma principal
	}else{

		$sql = "SELECT name, presence, customized, isentitytype, combinable,isplatzilla,
			(select count(*) from vtiger_configapps_tab cat where cat.tabid = t.tabid) as presenciaappsplatzilla,
			0 as presenciainstanciasclientes
			FROM vtiger_tab t
			WHERE name NOT IN ('Users') AND presence IN (0,1) ORDER BY name ";
	}


	$sqlresult = $adb->query($sql);
	$num_rows  = $adb->num_rows($sqlresult);
	for($idx = 0; $idx < $num_rows; ++$idx) {
		$module = $adb->query_result($sqlresult, $idx, 'name');
		$presence=$adb->query_result($sqlresult, $idx, 'presence');
		$customized=$adb->query_result($sqlresult, $idx, 'customized');
		$isentitytype=$adb->query_result($sqlresult, $idx, 'isentitytype');
		$combinable=$adb->query_result($sqlresult, $idx, 'combinable');
		$isplatzilla=$adb->query_result($sqlresult, $idx, 'isplatzilla');
		$presenciaappsplatzilla=$adb->query_result($sqlresult, $idx, 'presenciaappsplatzilla');
		$presenciainstanciasclientes=$adb->query_result($sqlresult, $idx, 'presenciainstanciasclientes');

		$plat = '';
		if (isset($_SESSION['plat']))
			$plat = $_SESSION['plat'].'/';

		$hassettings=file_exists($plat."modules/$module/Settings.php");

		if (!$hassettings)
			$hassettings=file_exists("modules/$module/Settings.php");

		$modinfo[$module] = Array( 'customized'=>$customized, 'presence'=>$presence, 'hassettings'=>$hassettings, 'isentitytype' => $isentitytype, 'combinable'=> $combinable, 'isplatzilla' => $isplatzilla, 'presenciaappsplatzilla' => $presenciaappsplatzilla, 'presenciainstanciasclientes' => $presenciainstanciasclientes );
	}
	return $modinfo;
}

/**
 * Get list of module with current status which can be controlled custom function.
 */
/*[ TT11223 ] Migrar funcionalidad “Administrador de Módulos” a un módulo simple
 * DM 25/07/2016
*/
function vtlib_getToggleModuleInfoCustom() {
	global $adb, $current_user;

	$modinfo = Array();

	/*
	$query = "SELECT t.name, t.presence, t.customized, t.isentitytype, t.combinable,t.isplatzilla
			  FROM vtiger_tab t
			  INNER JOIN vtiger_gestionmodule gm on gm.tabid = t.tabid
			  WHERE t.name NOT IN ('Users') AND t.presence IN (0,1) AND gm.userid = '".$current_user->id."'
			   ORDER BY name";
	*/


	# Si es la plataforma principal
	if ( $_SESSION['platInstancia'] != '' ){

		$query = "SELECT t.name, t.presence, t.customized, t.isentitytype, t.combinable,t.isplatzilla
			  FROM vtiger_tab t
			  WHERE t.name NOT IN ('Users') AND t.presence IN (0,1) and isplatzilla = 0
			  ORDER BY name";

	}else{

		$query = "SELECT t.name, t.presence, t.customized, t.isentitytype, t.combinable,t.isplatzilla
			  FROM vtiger_tab t
			  WHERE t.name NOT IN ('Users') AND t.presence IN (0,1)
			  ORDER BY name";
	}



	$sqlresult = $adb->query($query);
	$num_rows  = $adb->num_rows($sqlresult);
	for($idx = 0; $idx < $num_rows; ++$idx) {
		$module = $adb->query_result($sqlresult, $idx, 'name');
		$presence=$adb->query_result($sqlresult, $idx, 'presence');
		$customized=$adb->query_result($sqlresult, $idx, 'customized');
		$isentitytype=$adb->query_result($sqlresult, $idx, 'isentitytype');
		$combinable=$adb->query_result($sqlresult, $idx, 'combinable');
		$isplatzilla=$adb->query_result($sqlresult, $idx, 'isplatzilla');

		$plat = '';
		if (isset($_SESSION['plat']))
			$plat = $_SESSION['plat'].'/';

		$hassettings=file_exists($plat."modules/$module/Settings.php");

		if (!$hassettings)
			$hassettings=file_exists("modules/$module/Settings.php");

		$modinfo[$module] = Array( 'customized'=>$customized, 'presence'=>$presence, 'hassettings'=>$hassettings, 'isentitytype' => $isentitytype, 'combinable'=> $combinable,'isplatzilla'=> $isplatzilla );
	}
	return $modinfo;
}



/**
 * Get list of language and its current status.
 */
function vtlib_getToggleLanguageInfo() {
	global $adb;

	// The table might not exists!
	$old_dieOnError = $adb->dieOnError;
	$adb->dieOnError = false;

	$langinfo = Array();
	$sqlresult = $adb->query("SELECT * FROM vtiger_language");
	if($sqlresult) {
		for($idx = 0; $idx < $adb->num_rows($sqlresult); ++$idx) {
			$row = $adb->fetch_array($sqlresult);
			$langinfo[$row['prefix']] = Array( 'label'=>$row['label'], 'active'=>$row['active'] );
		}
	}
	$adb->dieOnError = $old_dieOnError;
	return $langinfo;
}

/**
 * Toggle the language (enable/disable)
 */
function vtlib_toggleLanguageAccess($langprefix, $enable_disable) {
	global $adb;

	// The table might not exists!
	$old_dieOnError = $adb->dieOnError;
	$adb->dieOnError = false;

	if($enable_disable === true) $enable_disable = 1;
	else if($enable_disable === false) $enable_disable = 0;

	$adb->pquery('UPDATE vtiger_language set active = ? WHERE prefix = ?', Array($enable_disable, $langprefix));

	$adb->dieOnError = $old_dieOnError;
}

/**
 * Setup mandatory (requried) module variable values in the module class.
 */
function vtlib_setup_modulevars($module, $focus) {

	$checkfor = Array('table_name', 'table_index', 'related_tables', 'popup_fields', 'IsCustomModule');
	foreach($checkfor as $check) {
		if(!isset($focus->$check)) $focus->$check = __vtlib_get_modulevar_value($module, $check);
	}
}
function __vtlib_get_modulevar_value($module, $varname) {
	$mod_var_mapping =
		Array(
			'Documents'=>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_notes',
				'table_index'=> 'notesid',
			),
		);
	return $mod_var_mapping[$module][$varname];
}

/**
 * Convert given text input to singular.
 */
function vtlib_tosingular($text) {
	$lastpos = strripos($text, 's');
	if($lastpos == strlen($text)-1)
		return substr($text, 0, -1);
	return $text;
}

/**
 * Get picklist values that is accessible by all roles.
 */
function vtlib_getPicklistValues_AccessibleToAll($field_columnname) {
	global $adb;

	$columnname =  $adb->sql_escape_string($field_columnname);
	$tablename = "vtiger_$columnname";

	// Gather all the roles (except H1 which is organization role)
	$roleres = $adb->query("SELECT roleid FROM vtiger_role WHERE roleid != 'H1'");
	$roleresCount= $adb->num_rows($roleres);
	$allroles = Array();
	if($roleresCount) {
		for($index = 0; $index < $roleresCount; ++$index)
			$allroles[] = $adb->query_result($roleres, $index, 'roleid');
	}
	sort($allroles);

	// Get all the picklist values associated to roles (except H1 - organization role).
	$picklistres = $adb->query(
		"SELECT $columnname as pickvalue, roleid FROM $tablename
		INNER JOIN vtiger_role2picklist ON $tablename.picklist_valueid=vtiger_role2picklist.picklistvalueid
		WHERE roleid != 'H1'");

	$picklistresCount = $adb->num_rows($picklistres);

	$picklistval_roles = Array();
	if($picklistresCount) {
		for($index = 0; $index < $picklistresCount; ++$index) {
			$picklistval = $adb->query_result($picklistres, $index, 'pickvalue');
			$pickvalroleid=$adb->query_result($picklistres, $index, 'roleid');
			$picklistval_roles[$picklistval][] = $pickvalroleid;
		}
	}
	// Collect picklist value which is associated to all the roles.
	$allrolevalues = Array();
	foreach($picklistval_roles as $picklistval => $pickvalroles) {
		sort($pickvalroles);
		$diff = array_diff($pickvalroles,$allroles);
		if(empty($diff)) $allrolevalues[] = $picklistval;
	}

	return $allrolevalues;
}

/**
 * Get all picklist values for a non-standard picklist type.
 */
function vtlib_getPicklistValues($field_columnname) {
	global $adb;

	$columnname =  $adb->sql_escape_string($field_columnname);
	$tablename = "vtiger_$columnname";

	$picklistres = $adb->query("SELECT $columnname as pickvalue FROM $tablename");

	$picklistresCount = $adb->num_rows($picklistres);

	$picklistvalues = Array();
	if($picklistresCount) {
		for($index = 0; $index < $picklistresCount; ++$index) {
			$picklistvalues[] = $adb->query_result($picklistres, $index, 'pickvalue');
		}
	}
	return $picklistvalues;
}

/**
 * Check for custom module by its name.
 */
function vtlib_isCustomModule($moduleName) {
	if(file_exists($_SESSION['plat']."/modules/$module/$modName.php")) {
		$moduleFile = $_SESSION['plat']."/modules/$module/$modName.php";
	} else {
		$moduleFile = "modules/$module/$modName.php";
	}
	if(file_exists($moduleFile)) {
		if(function_exists('checkFileAccessForInclusion')) {
			checkFileAccessForInclusion($moduleFile);
		}
		include_once($moduleFile);
		$focus = new $moduleName();
		return (isset($focus->IsCustomModule) && $focus->IsCustomModule);
	}
	return false;
}

/**
 * Get module specific smarty template path.
 */
function vtlib_getModuleTemplate($module, $templateName) {
	return ("modules/$module/$templateName");
}

/**
 * Check if give path is writeable.
 */
function vtlib_isWriteable($path) {
	if(is_dir($path)) {
		return vtlib_isDirWriteable($path);
	} else {
		return is_writable($path);
	}
}

/**
 * Check if given directory is writeable.
 * NOTE: The check is made by trying to create a random file in the directory.
 */
function vtlib_isDirWriteable($dirpath) {
	if(is_dir($dirpath)) {
		do {
			$tmpfile = 'vtiger' . time() . '-' . rand(1,1000) . '.tmp';
			// Continue the loop unless we find a name that does not exists already.
			$usefilename = "$dirpath/$tmpfile";
			if(!file_exists($usefilename)) break;
		} while(true);
		$fh = @fopen($usefilename,'a');
		if($fh) {
			fclose($fh);
			unlink($usefilename);
			return true;
		}
	}
	return false;
}

/** HTML Purifier global instance */
$__htmlpurifier_instance = false;
/**
 * Purify (Cleanup) malicious snippets of code from the input
 *
 * @param String $value
 * @param Boolean $ignore Skip cleaning of the input
 * @return String
 */
function vtlib_purify($input, $ignore=false) {
	global $__htmlpurifier_instance, $root_directory, $default_charset;

	$use_charset = $default_charset;
	$use_root_directory = $root_directory;

	$value = $input;
	if(!$ignore) {
		// Initialize the instance if it has not yet done
		if($__htmlpurifier_instance == false) {
			if(empty($use_charset)) $use_charset = 'UTF-8';
			if(empty($use_root_directory)) $use_root_directory = dirname(__FILE__) . '/../..';
			
			// Normalizar el path eliminando slashes duplicados
			$use_root_directory = rtrim($use_root_directory, '/\\');

			include_once ('include/htmlpurifier/library/HTMLPurifier.auto.php');

			$config = HTMLPurifier_Config::createDefault();
	    	$config->set('Core', 'Encoding', $use_charset);
	    	$config->set('Cache', 'SerializerPath', "$use_root_directory/test/vtlib");

			$__htmlpurifier_instance = new HTMLPurifier($config);
		}
		if($__htmlpurifier_instance) {
			// Composite type
			if (is_array($input)) {
				$value = array();
				foreach ($input as $k => $v) {
					$value[$k] = vtlib_purify($v, $ignore);
				}
			} else { // Simple type
				$value = $__htmlpurifier_instance->purify($input);
			}
		}
	}
	$value = str_replace('&amp;','&',$value);
	return $value;
}

/**
 * Process the UI Widget requested
 * @param Vtiger_Link $widgetLinkInfo
 * @param Current Smarty Context $context
 * @return
 */
function vtlib_process_widget($widgetLinkInfo, $context = false) {
	if (preg_match("/^block:\/\/(.*)/", $widgetLinkInfo->linkurl, $matches)) {
		list($widgetControllerClass, $widgetControllerClassFile) = explode(':', $matches[1]);
		if (!class_exists($widgetControllerClass)) {
			checkFileAccessForInclusion($widgetControllerClassFile);
			include_once $widgetControllerClassFile;
		}
		if (class_exists($widgetControllerClass)) {
			$widgetControllerInstance = new $widgetControllerClass;
			$widgetInstance = $widgetControllerInstance->getWidget($widgetLinkInfo->linklabel);
			if ($widgetInstance) {
				return $widgetInstance->process($context);
			}
		}
	}
	return "";
}

function vtlib_module_icon($modulename){
	if($modulename == 'Events'){
		return "modules/Calendar/Events.png";
	}
	if(file_exists("modules/$modulename/$modulename.png")){
		return "modules/$modulename/$modulename.png";
	}
	return "modules/Vtiger/Vtiger.png";
}

?>
