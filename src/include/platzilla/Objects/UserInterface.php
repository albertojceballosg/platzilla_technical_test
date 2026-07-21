<?php

	/**
	 * Interface UserInterface
	 *
	 * Donde se declaran las constantes que controlan el estatus del usuario (activo o desactivo)
	 */
	interface UserInterface {
		const STATUS_ACTIVE   = 'Active';
		const STATUS_INACTIVE = 'Inactive';
		const OPERATING_MODO  = array ('MANAGEMENT_MODE', 'FORMATIVE_MODE', 'DIRECTION_MODE');
		const HOME_TABS       = array (
			'MANAGEMENT_MODE' => array ('ACTIVITY', 'MESSAGES', 'CONTROL_PANEL'),
			'FORMATIVE_MODE'  => array ('TRAINING', 'MATERIALS'),
		);

	}
