<?php

	/**
	 * Interface ViewInterface
	 *
	 * Donde se declaran las constantes que controlan si la vista es por defecto o no, se muestra el contador en el menu, y el estado de los filtros se configuran en la vista
	 */
	interface ViewInterface {
		const DEFAULT_NO  = 0;
		const DEFAULT_YES = 1;

		const SEARCH_NO  = 0;
		const SEARCH_YES = 1;

		const SHOW_COUNT_NO    = 0;
		const SHOW_COUNT_YES   = 1;
		const SHOW_ON_DESK_NO  = 0;
		const SHOW_ON_DESK_YES = 1;

		const STATUS_PUBLIC   = 0;
		const STATUS_PRIVATE  = 1;
		const STATUS_PENDING  = 2;
		const STATUS_APPROVED = 3;

	}
