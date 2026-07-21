<?php

	/**
	 * Interface BackgroundTaskInterface
	 *
	 * Donde se declaran las constantes que controlan los estatus de la tarea oculta (antes o despues, si son tareas del tipo usuario o del sistema, si está habilitada o deshabilitada y
	 * si es programada o a eventos, manual la tarea oculta)
	 */
	interface BackgroundTaskInterface {
		const EVENT_INSTANT_AFTER  = 'AFTER';
		const EVENT_INSTANT_BEFORE = 'BEFORE';

		const SCOPE_SYSTEM = 'SYSTEM';
		const SCOPE_USER   = 'USER';

		const STATUS_DISABLED = 'DISABLED';
		const STATUS_ENABLED  = 'ENABLED';

		const TRIGGER_DAILY_SCHEDULE = 'DAILY SCHEDULE';
		const TRIGGER_EVENT          = 'EVENT';
		const TRIGGER_MANUAL         = 'MANUAL';
		const TRIGGER_TIMED_SCHEDULE = 'TIMED SCHEDULE';

	}
