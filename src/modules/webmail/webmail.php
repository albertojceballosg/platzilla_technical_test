<?php
	class webmail {

		public static function runPreUninstallTasks (PearDatabase $adb) {
			require_once ('modules/webmail/lib/WebmailInstaller.class.php');
			WebmailInstaller::getInstance ()->runPreUninstallTasks ($adb);
		}

		public static function runPostInstallTasks (PearDatabase $adb) {
			require_once ('modules/webmail/lib/WebmailInstaller.class.php');
			WebmailInstaller::getInstance ()->runPostInstallTasks ($adb);
		}

	}
