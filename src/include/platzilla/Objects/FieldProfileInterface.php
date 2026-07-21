<?php

	/**
	 * Interface FieldProfileInterface
	 *
	 * Donde se declaran las constantes que controlan los atributos de lectura, escritura, visibilidad y oculto del perfil del campo
	 */
	interface FieldProfileInterface {
		const READ_ONLY  = 1;
		const READ_WRITE = 0;

		const VISIBILITY_VISIBLE = 0;
		const VISIBILITY_HIDDEN  = 1;

	}
