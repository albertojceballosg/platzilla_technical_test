<?php

	interface ReportScheduleInterface {
		const FORMAT_BOTH  = 'both';
		const FORMAT_EXCEL = 'xls';
		const FORMAT_PDF   = 'pdf';

		const FREQUENCY_BIWEEKLY = 4;
		const FREQUENCY_DAILY    = 2;
		const FREQUENCY_MONTHLY  = 5;
		const FREQUENCY_WEEKLY   = 3;
		const FREQUENCY_YEARLY   = 6;

		const MONTH_JANUARY   = 1;
		const MONTH_FEBRUARY  = 2;
		const MONTH_MARCH     = 3;
		const MONTH_APRIL     = 4;
		const MONTH_MAY       = 5;
		const MONTH_JUNE      = 6;
		const MONTH_JULY      = 7;
		const MONTH_AUGUST    = 8;
		const MONTH_SEPTEMBER = 9;
		const MONTH_OCTOBER   = 10;
		const MONTH_NOVEMBER  = 11;
		const MONTH_DECEMBER  = 12;

		const WEEKDAY_SUNDAY    = 0;
		const WEEKDAY_MONDAY    = 1;
		const WEEKDAY_TUESDAY   = 2;
		const WEEKDAY_WEDNESDAY = 3;
		const WEEKDAY_THURSDAY  = 4;
		const WEEKDAY_FRIDAY    = 5;
		const WEEKDAY_SATURDAY  = 6;

	}
