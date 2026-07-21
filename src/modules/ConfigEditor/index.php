<?php
	require_once (__DIR__ . '/Request.php');
	require_once (__DIR__ . '/Viewer.php');
	require_once (__DIR__ . '/ConfigFileReader.php');
	require_once (__DIR__ . '/config.php');

	class ConfigEditor_Controller {

		/**
		 * Get Viewer for displaying UI
		 */
		protected function getViewer () {
			return new ConfigEditor_Viewer();
		}

		/**
		 * Get Configuration file reader
		 */
		protected function getReader () {
			global $__ConfigEditor_Config;
			$configFile = $__ConfigEditor_Config['edit.filepath'];
			if (file_exists ($configFile)) {
				if (is_writeable ($configFile)) {
					return new ConfigFileReader (
						$configFile,
						$__ConfigEditor_Config['allow.editing.variables'], // What variables to view
						$__ConfigEditor_Config['allow.editing.variables']  // What variables to edit
					);
				} else {
					return null;
				}
			}
			return false;
		}

		/**
		 * Perform logged in user check and allow only administrators
		 */
		protected function authCheck () {
			global $current_user;
			if (is_admin ($current_user)) {
				return;
			}

			$viewer = $this->getViewer ();
			$viewer->display ('OperationNotPermitted.tpl');
			exit;
		}

		/**
		 * Core processing method
		 *
		 * @param ConfigEditor_Request $request
		 */
		public function process (ConfigEditor_Request $request) {
			$this->authCheck ();
			$type = $request->get ('type');
			if ($type == 'save') {
				$this->processSave ($request);
			} else {
				$this->processDefault ();
			}
		}

		/**
		 * Default action
		 */
		protected function processDefault () {
			global $currentModule;

			$configReader = $this->getReader ();
			$viewer       = $this->getViewer ();

			if ($configReader == null) {
				$viewer->assign ('WARNING', 'Configuration file is not writeable!');
			} else if ($configReader === false) {
				$viewer->assign ('WARNING', 'Configuration file not found!');
			} else {
				$viewer->assign ('CONFIGREADER', $configReader);
			}
			$viewer->display (vtlib_getModuleTemplate ($currentModule, 'index.tpl'));
		}

		/**
		 * Save action
		 *
		 * @param $request
		 */
		protected function processSave (ConfigEditor_Request $request) {
			$configReader = $this->getReader ();

			if ($configReader) {
				$reqvalues = $request->values ();
				foreach ($reqvalues as $k => $v) {
					if (preg_match ('/key_([^ ]+)/', $k, $m)) {
						$configReader->setVariableValue ($m[1], $v);
					}
				}
				$configReader->save ();
			}
			header ('Location: index.php?module=ConfigEditor&action=index');
		}

	}

	$request = new ConfigEditor_Request ($_REQUEST);
	$controller = new ConfigEditor_Controller();
	$controller->process ($request);
