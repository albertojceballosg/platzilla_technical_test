<?php
	/**
	 * Interface ProfileInterface
	 *
	 * Donde se declaran las constantes que controlan las acciones de edicion y vista del perdil así como los permisos
	 */
	interface ProfileInterface {
		const ACTION_EDIT_ALL  = 2;
		const ACTION_VIEW_ALL  = 1;
		const PERMISSION_ALLOW = 1;
		const PERMISSION_DENY  = 0;

	}
