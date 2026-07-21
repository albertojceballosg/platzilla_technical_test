<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	class platzi_issabel extends CRMEntity {
		public $db;
		public $log;
		
		public function __construct () {
			global $log, $currentModule;
			$this->db   = PearDatabase::getInstance ();
			$this->log  = $log;
		}
		
		/**
		 * @param $currentModule
		 *
		 * @return void
		 * @throws Exception
		 */
		public function checkModuleSubscription ($currentModule) {
			if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
				$smarty = new vtigerCRM_Smarty ();
				$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta!');
				$smarty->display ('instanciaUnverified.tpl');
				exit ();
			}
			$masterAdb    = AdbManager::getInstance ()->getMasterAdb ();
			$subscription = null;
			try {
				$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
				$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
				if ((empty ($subscription)) || ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE)) {
					throw new Exception ('Tu suscripción se encuentra inactiva');
				}
				
			} catch (Exception $e) {
				$smarty = new vtigerCRM_Smarty ();
				$smarty->assign ('LABEL', 'Tu suscripción');
				$smarty->assign ('MESSAGE', $e->getMessage ());
				$smarty->assign ('TYPE', 'ERROR');
				$smarty->assign ('URL', 'index.php?module=Home&action=ViewSubscriptionDetails&tab=subscription');
				$smarty->display ('Message.tpl');
				exit ();
			}
		}
	}
