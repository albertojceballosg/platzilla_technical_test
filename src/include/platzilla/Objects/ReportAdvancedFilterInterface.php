<?php

	interface ReportAdvancedFilterInterface {
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
		const COMPARATOR_NOT_EQUAL         = 'n';
		const COMPARATOR_NOT_EQUALS        = 'ne';
		const COMPARATOR_STARTS_WITH       = 's';

		const OPERATOR_AND = 'and';
		const OPERATOR_OR  = 'or';

	}
