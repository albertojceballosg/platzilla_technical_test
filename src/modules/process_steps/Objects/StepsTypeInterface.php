<?php
	interface StepsTypeInterface {
		const EXCLUDED_MODULES = array(
			'almacenes',
			'answers',
			'daily_report',
			'diagnostic_report',
			'diagnostic_report_builder',
			'etapas_proyecto',
			'grid_view',
			'how_use',
			'materials',
			'model_action_plan',
			'operating_modes',
			'predefined_initiatives',
			'preloaded_tasks',
			'process',
			'process_steps',
			'questionnaire',
			'reportes',
			'systemalerts',
			'todotasks',
			'views_diagrams',
		);
		const PROCESS_MODULE_NAME      = 'process';
		const PROCESS_STEP_MODULE_NAME = 'process_steps';
		const PROCESS_TABLE_FIELD_NAME = 'process_steps_table';
		const PROPERTIES_TO_UPDATE     = array ('step_name=?', 'related_module=?', 'step_state=?', 'required_step=?');
		const STEPS_TYPE               = array (
			'ASSISTED'  => 'Asistido',
			'AUTOMATIC' => 'Automático',
			'MANUAL'    => 'Manual',
		);
		const STEPS_VIEW_END = array (
			'DetailView' => 'Ver registro',
			'EditView'   => 'Editar registro',
		);
	}
