<?php

	interface BackgroundTaskParameterInterface {
		const PARAMETER_TYPE_CALCULATED_DATE      = 'CALCULATED DATE';
		const PARAMETER_TYPE_CUSTOM_SQL           = 'CUSTOM SQL';
		const PARAMETER_TYPE_JSON                 = 'JSON';
		const PARAMETER_TYPE_LITERAL              = 'LITERAL';
		const PARAMETER_TYPE_SOURCE_FIELD         = 'SOURCE FIELD';
		const PARAMETER_TYPE_RELATED_SOURCE_FIELD = 'RELATED SOURCE FIELD';
		const PARAMETER_TYPE_VARIABLE             = 'VARIABLE';

		const OPTION_TYPE_HANDLER = 'HANDLER';
		const OPTION_TYPE_JSON    = 'JSON';
		const OPTION_TYPE_LITERAL = 'LITERAL';
		const OPTION_TYPE_SQL     = 'SQL';

	}
