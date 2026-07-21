<?php

	interface PricebookConditionInterface {
		const COMPARATOR_CONTAINS          = 'CONTAINS';
		const COMPARATOR_DOES_NOT_CONTAIN  = 'DOES_NOT_CONTAIN';
		const COMPARATOR_EQUALS            = 'EQUALS';
		const COMPARATOR_GREATER           = 'GREATER';
		const COMPARATOR_GREATER_OR_EQUALS = 'GREATER_OR_EQUALS';
		const COMPARATOR_LESS              = 'LESS';
		const COMPARATOR_LESS_OR_EQUALS    = 'LESS_OR_EQUALS';
		const COMPARATOR_NOT_EQUALS        = 'NOT_EQUALS';

		const OPERATOR_AND = 'AND';
		const OPERATOR_OR  = 'OR';

		const VARIABLE_TYPE_CUSTOMER_FIELD  = 'CUSTOMER_FIELD';
		const VARIABLE_TYPE_SYSTEM_VARIABLE = 'SYSTEM_VARIABLE';

	}
