<?php

	interface ReportInterface {
		const STATUS_CUSTOMIZED = 'CUSTOM';
		const STATUS_SAVED      = 'SAVED';

		const TYPE_SUMMARY = 'summary';
		const TYPE_TABULAR = 'tabular';

		const VISIBILITY_PRIVATE = 'Private';
		const VISIBILITY_PUBLIC  = 'Public';
		const VISIBILITY_SHARED  = 'Shared';

	}
