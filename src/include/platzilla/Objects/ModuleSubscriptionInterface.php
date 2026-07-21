<?php
	/*
	 * Donde se declaran los posibles estatus a tomar un modulo que un usuario puede dar de alta en una Aplicación
	 */
	interface ModuleSubscriptionInterface {
		const STATUS_ACTIVE     = 'Activo';
		const STATUS_INACTIVE   = 'Inactivo';
		const STATUS_SUBSCRIBED = 'Suscrito';

	}
