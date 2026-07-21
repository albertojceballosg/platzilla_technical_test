<?php

	interface ReportColumnInterface {
		const SORT_ORDER_ASCENDING  = 'Ascending';
		const SORT_ORDER_DESCENDING = 'Descending';

		const TOTALS_OPERATION_AVERAGE = 'AVG';
		const TOTALS_OPERATION_MAXIMUM = 'MAX';
		const TOTALS_OPERATION_MINIMUM = 'MIN';
		const TOTALS_OPERATION_SUM     = 'SUM';
	}
