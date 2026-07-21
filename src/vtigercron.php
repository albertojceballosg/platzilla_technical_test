<?php
	/**
	 * Start the cron services configured.
	 */
	define ('ADODB_NEVER_PERSIST', true);
	error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
	ini_set ('display_errors', 1);

	$arg = $GLOBALS ['argv'];
	foreach ($arg as $argumento) {
		$vars                 = explode ('=', $argumento);
		$_REQUEST [ $vars [0] ] = $vars [1];
	}

	require_once ('include/utils/AdbManager.class.php');
	require_once ('vtlib/Vtiger/Cron.php');
	require_once ('config.inc.php');

	global $adb, $platPrincipal;

	if (!empty ($_REQUEST ['plat'])) {
		session_name ($_REQUEST ['plat']);
		$_SESSION ['plat'] = $_REQUEST ['plat'];
	} else {
		session_name ($platPrincipal);
		$_SESSION ['plat'] = $platPrincipal;
	}

	/** @var string $application_unique_key */
	if (PHP_SAPI === "cli" || (isset($_SESSION["authenticated_user_id"]) && isset($_SESSION["app_unique_key"]) && $_SESSION["app_unique_key"] == $application_unique_key)) {
		$adb = AdbManager::getInstance ()->getMasterAdb ();
		$cronTasks = false;
		if (isset($_REQUEST['service'])) {
			// Run specific service
			$cronTasks = array (Vtiger_Cron::getInstance ($_REQUEST['service']));
		} else {
			// Run all service
			$cronTasks = Vtiger_Cron::listAllActiveInstances ();
		}

		/** @var Vtiger_Cron $cronTask */
		foreach ($cronTasks as $cronTask) {
			echo "Ejecutando {$cronTask->getName ()}" . PHP_EOL;
			if (empty ($cronTask)) {
				continue;
			}

			try {
				$cronTask->setBulkMode (true);

				// Not ready to run yet?
				if (!$cronTask->isRunnable ()) {
					echo sprintf ("[INFO]: %s - not ready to run as the time to run again is not completed\n", $cronTask->getName ());
					continue;
				}

				// Timeout could happen if intermediate cron-tasks fails
				// and affect the next task. Which need to be handled in this cycle.
				if ($cronTask->hadTimedout ()) {
					echo sprintf ("[INFO]: %s - cron task had timedout as it is not completed last time it run- restarting\n", $cronTask->getName ());
				}

				// Mark the status - running
				$cronTask->markRunning ();

				checkFileAccess ($cronTask->getHandlerFile ());
				require_once $cronTask->getHandlerFile ();

				// Mark the status - finished
				$cronTask->markFinished ();
			} catch (Exception $e) {
				echo sprintf ("[ERROR]: %s - cron task execution throwed exception.\n", $cronTask->getName ());
				echo $e->getMessage ();
				echo "\n";
			}
		}
	} else {
		echo ("Access denied!");
	}
