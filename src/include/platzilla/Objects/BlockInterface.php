<?php
	/*
	 * Donde se declaran las constantes que controlan las acciones: Personalizar, mostrar titulo y visibilidad del bloque
	 */
	interface BlockInterface {
		const CUSTOM_BLOCK  = 'LBL_CUSTOM_BLOCK';

		const IS_CUSTOM_NO  = 0;
		const IS_CUSTOM_YES = 1;

		const SHOW_TITLE_NO  = 1;
		const SHOW_TITLE_YES = 0;

		const VISIBILITY_VISIBLE = 0;
		const VISIBILITY_HIDDEN  = 1;
	}
