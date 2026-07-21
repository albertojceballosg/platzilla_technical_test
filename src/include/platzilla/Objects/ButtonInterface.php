<?php

	/**
	 * Interface ButtonInterface donde se declaran las constantes que controlan las vistas donde figuraran los custom buttons
	 */
	interface ButtonInterface {
		const LOCATION_ACTION_BUTTON = 'ActionButton';
		const LOCATION_DETAIL_VIEW   = 'DetailView';
		const LOCATION_EDIT_VIEW     = 'DetailView';
		const LOCATION_LIST_VIEW     = 'ListView';

		const TYPE_JAVASCRIPT = 'js';
		const TYPE_LINK       = 'link';

	}
