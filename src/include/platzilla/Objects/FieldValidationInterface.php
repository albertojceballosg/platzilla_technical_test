<?php
	/*
	 * Donde se declaran las constantes que controlan los atributos para los tipos de validaciones asociadas: Fecha, Numerico y Unicidad
	 */
	interface FieldValidationInterface {
		const VALIDATION_TYPE_DATE   = 'D';
		const VALIDATION_TYPE_NUMBER = 'N';
		const VALIDATION_TYPE_UNIQUE = 'U';

	}
