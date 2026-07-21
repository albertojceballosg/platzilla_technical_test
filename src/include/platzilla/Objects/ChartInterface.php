<?php

	interface ChartInterface {
		const ADVANCED_NO       = 0;
		const ADVANCED_YES      = 1;
		const ADVANCED_BOXSCORE = 2;

		const DATE_GROUPING_DAILY     = 1;
		const DATE_GROUPING_WEEKLY    = 2;
		const DATE_GROUPING_MONTHLY   = 3;
		const DATE_GROUPING_QUARTERLY = 4;
		const DATE_GROUPING_BIANNUAL  = 5;
		const DATE_GROUPING_ANNUAL    = 6;

		const OPERATION_COUNT   = 1;
		const OPERATION_SUM     = 2;
		const OPERATION_AVERAGE = 3;

		const TYPE_AREA   = 'area';
		const TYPE_BARS   = 'bar';
		const TYPE_COLUMN = 'column';
		const TYPE_COMBO  = 'combo';
		const TYPE_DONUT  = 'donut';
		const TYPE_FUNNEL = 'funnel';
		const TYPE_LINE   = 'line';
		const TYPE_PIE    = 'pie';
		const TYPE_TABLE  = 'table';

	}
