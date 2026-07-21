<?php

	/**
	 * Interface BackgroundTaskActionHandlerInterface
	 *
	 * Donde se ejecutan las acciones de la tarea oculta y se instancia la clase BackgroundTaskActionHandler
	 */
	interface BackgroundTaskActionHandlerInterface {

		/**
		 * @param BackgroundTaskAction $action
		 */
		public function run ($action);

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return BackgroundTaskActionHandlerInterface
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null);

	}
