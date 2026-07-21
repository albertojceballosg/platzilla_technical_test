<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

	require_once ('Smarty_setup.php');

	class Import_UI_Viewer {
		private $parameters = array();
		
		/**
		 * @param string $key
		 * @param mixed $value
		 *
		 * @return void
		 */
		public function assign ($key, $value) {
			$this->parameters[$key] = $value;
		}
		
		/**
		 * @return vtigerCRM_Smarty
		 */
		public  function viewController() {
			global $theme;
			$themePath = "themes/".$theme."/";
			$imagePath = $themePath."images/";
			$smarty    = new vtigerCRM_Smarty();
			
			foreach ($this->parameters as $k => $v) {
				$smarty->assign($k, $v);
			}
	
			$smarty->assign ('MODULE', 'Import');
			$smarty->assign ('THEME', $theme);
			$smarty->assign ('IMAGE_PATH', $imagePath);
	
			return $smarty;
		}
		
		/**
		 * @param string $templateName
		 * @param string $moduleName
		 *
		 * @return void
		 */
		public function display ($templateName, $moduleName = '') {
			$smarty = $this->viewController();
			if (empty ($moduleName)) {
				$moduleName = 'Import';
			}
			$smarty->display (vtlib_getModuleTemplate ($moduleName, $templateName));
		}
		
		/**
		 * @param string $templateName
		 * @param string $moduleName
		 *
		 * @return false|string
		 * @throws SmartyException
		 */
		public  function fetch($templateName, $moduleName='') {
			$smarty = $this->viewController();
			if (empty ($moduleName)) {
				$moduleName = 'Import';
			}
			return $smarty->fetch (vtlib_getModuleTemplate($moduleName, $templateName));
		}
		
	}