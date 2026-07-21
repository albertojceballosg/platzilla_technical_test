<?php
	
	interface ResourceInterface {
		
		/** set filed to retrieve the module factor */
		const MODULES_FACTOR_FIELD = array (
			'campaign_marketing' => 'overall_progress_perc',
			'orden_de_trabajo'   => 'overall_progress_perc',
			'proyectos'          => 'porcentaje_de_avance_genera',
		);
		
		/** available module to resource */
		const RESOURCE_MODULES = array (
			'campaign_marketing' => 'Campañas',
			'orden_de_trabajo'   => 'Trabajo',
			'proyectos'          => 'Proyecto',
		);
	}
