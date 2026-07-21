<?php

	/**
	 * Interface GridFieldInterface
	 *
	 * Donde se declaran las constantes que controlan el tipo de dato de los campos que puede anexarse en los campos
	 * grid
	 */
	interface GridFieldInterface {
		const UI_TYPE_CHECKBOX         = 56;
		const UI_TYPE_DATE             = 5;
		const UI_TYPE_DATETIME         = 6;
		const UI_TYPE_MODULE_REFERENCE = 10;
		const UI_TYPE_NUMBER           = 7;
		const UI_TYPE_PERCENTAGE       = 9;
		const UI_TYPE_PICKLIST         = 15;
		const UI_TYPE_TEXT             = 1;
		const UI_TYPE_TEXTAREA         = 21;
		const UI_TYPE_URL              = 17;
		const UI_TYPE_GRID             = 2202;
		const UI_TYPE_SUMMARY          = 2203;
		const UI_TYPE_CALCULATED       = 2204;

	}
