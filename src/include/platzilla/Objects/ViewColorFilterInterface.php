<?php

	/**
	 * Interface ViewColorFilterInterface
	 *
	 * Donde se declaran las constantes que controlan los parametros para el filtro de color
	 */
	interface ViewColorFilterInterface {
		const COMPARATOR_AFTER             = 'a';
		const COMPARATOR_BEFORE            = 'b';
		const COMPARATOR_BETWEEN           = 'bw';
		const COMPARATOR_CONTAINS          = 'c';
		const COMPARATOR_DOES_NOT_CONTAIN  = 'k';
		const COMPARATOR_ENDS_WITH         = 'ew';
		const COMPARATOR_EQUALS            = 'e';
		const COMPARATOR_GREATER           = 'g';
		const COMPARATOR_GREATER_OR_EQUALS = 'h';
		const COMPARATOR_LESS              = 'l';
		const COMPARATOR_LESS_OR_EQUALS    = 'm';
		const COMPARATOR_NOT_EQUALS        = 'n';
		const COMPARATOR_STARTS_WITH       = 's';

		const OPERATOR_AND = 'and';
		const OPERATOR_OR  = 'or';

		const PERIOD_CURRENT_MONTH    = 'thismonth';
		const PERIOD_CURRENT_QUARTER  = 'thisfq';
		const PERIOD_CURRENT_WEEK     = 'thisweek';
		const PERIOD_CURRENT_YEAR     = 'thisfy';
		const PERIOD_CUSTOM           = 'custom';
		const PERIOD_LAST_7_DAYS      = 'last7days';
		const PERIOD_LAST_30_DAYS     = 'last30days';
		const PERIOD_LAST_60_DAYS     = 'last60days';
		const PERIOD_LAST_90_DAYS     = 'last90days';
		const PERIOD_LAST_120_DAYS    = 'last120days';
		const PERIOD_LAST_MONTH       = 'lastmonth';
		const PERIOD_LAST_WEEK        = 'lastweek';
		const PERIOD_NEXT_7_DAYS      = 'next7days';
		const PERIOD_NEXT_30_DAYS     = 'next30days';
		const PERIOD_NEXT_60_DAYS     = 'next60days';
		const PERIOD_NEXT_90_DAYS     = 'next90days';
		const PERIOD_NEXT_120_DAYS    = 'next120days';
		const PERIOD_NEXT_MONTH       = 'nextmonth';
		const PERIOD_NEXT_QUARTER     = 'nextfq';
		const PERIOD_NEXT_WEEK        = 'nextweek';
		const PERIOD_NEXT_YEAR        = 'nextfy';
		const PERIOD_PREVIOUS_QUARTER = 'prevfq';
		const PERIOD_PREVIOUS_YEAR    = 'prevfy';
		const PERIOD_TODAY            = 'today';
		const PERIOD_TOMORROW         = 'tomorrow';
		const PERIOD_YESTERDAY        = 'yesterday';
	}
