<?php

	class DiagnosticReportBuilderException extends Exception {
		const DIAGNOSTIC_REPORT_EMPTY_NAME             = 'Nombre del reporte no encontado';
		const DIAGNOSTIC_REPORT_HAS_NOT_QUESTIONNAIRE  = 'Cuestionario no encontrado';
		const ERROR_EXTENSION_NO_ALLOWED               = 'EXTENSION_NO_ALLOWED, PLACE_CHOOSE A PNG FILE';
		const ERROR_FILE_TOO_BIG                       = 'FILE_TOO_BIG, FILE SIZE MUST BE MAX 2MB';
	}