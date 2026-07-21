<?php
	
	interface SummaryReportInterface {
		const AGREEMENTS_STATUS    = array ('ACTIVE' => 'Activo', 'INACTIVE' => 'Inactivo');
		const PERFORMANCES_ICON_PATH = array (
			'&#65;' => 'A',
			'&#66;' => 'B',
			'&#67;' => 'C',
			'&#68;' => 'D',
			'&#69;' => 'E',
			'&#70;' => 'F',
			'&#71;' => 'G',
			'&#72;' => 'H',
			'&#73;' => 'I',
			'&#74;' => 'J',
			'&#75;' => 'K',
			'&#76;' => 'L',
			'&#77;' => 'M',
			'&#78;' => 'N',
			'&#79;' => 'O',
		);
		const PERFORMANCES_STATUS    = array ('ACTIVE' => 'Activo', 'INACTIVE' => 'Inactivo');
		const REPORT_STATUS          = array (
			'DRAFT'            => 'Borrador',
			'UNDER_DISCUSSION' => 'En discusión',
			'APPROVED'         => 'Aprobado',
			'INACTIVE'         => 'Inactivo',
		);
		
	}
