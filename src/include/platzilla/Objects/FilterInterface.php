<?php

	interface FilterInterface {
		const COMPARATOR_CONTAINS          = 'CONTAINS';
		const COMPARATOR_DAYS_AFTER        = 'DAYS_AFTER';
		const COMPARATOR_DAYS_AFTER_EXACT  = 'DAYS_AFTER_EXACT';
		const COMPARATOR_DAYS_BEFORE       = 'DAYS_BEFORE';
		const COMPARATOR_DAYS_BEFORE_EXACT = 'DAYS_BEFORE_EXACT';
		const COMPARATOR_DOES_NOT_CONTAIN  = 'DOES_NOT_CONTAIN';
		const COMPARATOR_ENDS_WITH         = 'ENDS_WITH';
		const COMPARATOR_EQUALS            = 'EQUALS';
		const COMPARATOR_GREATER           = 'GREATER';
		const COMPARATOR_GREATER_OR_EQUALS = 'GREATER_OR_EQUALS';
		const COMPARATOR_LESS              = 'LESS';
		const COMPARATOR_LESS_OR_EQUALS    = 'LESS_OR_EQUALS';
		const COMPARATOR_NOT_EQUALS        = 'NOT_EQUALS';
		const COMPARATOR_STARTS_WITH       = 'STARTS_WITH';

		const OPERATOR_AND = 'AND';
		const OPERATOR_OR  = 'OR';

		/**
		 * @return Filter
		 */
		public static function getInstance ();

	}
