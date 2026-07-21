<?php
	
	interface PartOfWorkInterface {
		const PART_WORK_PDF_HEADER = array (
			//' - '         => array ('width' => '16', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Trabajo'     => array ('width' => '17', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Descripción' => array ('width' => '25', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Tareas'      => array ('width' => '25', 'text_align' => 'left;padding-left:60px', 'class' => 'text-center', 'colspan' => '' ),
			'Responsable' => array ('width' => '8', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Cliente'     => array ('width' => '17', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Prioridad'   => array ('width' => '8', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
		);
		const PART_WORK_PDF_ROW    = array (
			//' - '         => array ('linkToDetailView', array ('','titulo', 'orden_de_trabajoid', 'orden_de_trabajo')),
			'Trabajo'     => array ('linkToDetailView', array ('','titulo', 'entity_id', 'orden_de_trabajo')),
			'Descripción' => array ('description', array ('','descripcion')),
			'Tareas'      => array ('showList', 'entity_id', array ('Calendar', 'activityid', 'subject', 'date_start')),
			'Responsable' => array ('description', array ('','username')),
			'Cliente'     => array ('listOfFields', array('client', 'address', 'phone')),
			'Prioridad'   => array ('description', array ('','work_priority')),
		);
		
		const PART_WORK_TABLE_HEADER = array (
			' - '         => array ('width' => '2', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
			'Trabajo'     => array ('width' => '17', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Descripción' => array ('width' => '21', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Tareas'      => array ('width' => '21', 'text_align' => 'left;padding-left:60px', 'class' => 'text-center', 'colspan' => '' ),
			'Responsable' => array ('width' => '8', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Cliente'     => array ('width' => '17', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Prioridad'   => array ('width' => '8', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
			'Acciones'    => array ('width' => '6', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
		);
		const PART_WORK_TABLE_ROW    = array (
			' - '         => array ('linkToShowModal', array ('','titulo', 'entity_id', 'orden_de_trabajo', 'tab_name')),
			'Trabajo'     => array ('linkToDetailView', array ('','titulo', 'entity_id', 'orden_de_trabajo')),
			'Descripción' => array ('description', array ('','descripcion')),
			'Tareas'      => array ('showList', 'entity_id', array ('Calendar', 'activityid', 'subject','date_start')),
			'Responsable' => array ('description', array ('','username')),
			'Cliente'     => array ('listOfFields', array('client', 'address', 'phone')),
			'Prioridad'   => array ('description', array ('','work_priority')),
			'Acciones'    => array ('doReportAndFeedback', array ('','orden_de_trabajoid', 'activityid', 'orden_de_trabajo')),
		);
		
	}
