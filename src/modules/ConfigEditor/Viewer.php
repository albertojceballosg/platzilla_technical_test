<?php
	require_once ('Smarty_setup.php');

	class ConfigEditor_Viewer extends vtigerCRM_Smarty {

		public function __construct () {
			parent::__construct ();
			global $app_strings, $mod_strings, $currentModule, $theme;

			$this->assign ('CUSTOM_MODULE', true);
			$this->assign ('APP', $app_strings);
			$this->assign ('MOD', $mod_strings);
			$this->assign ('MODULE', $currentModule);
			$this->assign ('SINGLE_MOD', 'SINGLE_' . $currentModule);
			$this->assign ('CATEGORY', 'Settings');
			$this->assign ('IMAGE_PATH', "themes/$theme/images/");
			$this->assign ('THEME', $theme);
		}

	}
