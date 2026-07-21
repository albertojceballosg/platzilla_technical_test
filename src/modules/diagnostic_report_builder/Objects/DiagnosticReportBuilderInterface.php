<?php
	interface DiagnosticReportBuilderInterface {
        const BLOCKS_TYPE_CURRENT_STATUS    = 'CURRENT_STATUS';
        const BLOCKS_TYPE_INFORMATIVE_VIDEO = 'INFORMATIVE_VIDEO';
        const BUSINESS_TYPE                 = array (
            'SALES'         => 'Venta',
            'DISTRIBUTION'  => 'Distribución',
            'SERVICES'      => 'Servicios',
            'MANUFACTURING' => 'Fabricación',
            'MIXED'         => 'Mixta',
        );

		const ELEMENT_TYPE = array (
            'BUSINESS_PHASE'          => 'Etapa de avance de la empresa',
            'BUSINESS_TYPE'           => 'Tipo de empresa',
            'DYNAMIC_TEXT'            => 'Texto dinámico',
            'IMAGE'                   => 'Imagen',
            'IMAGE_CURRENT_STATUS'    => 'Imagen o mapa de la situación actual',
            'MANAGEMENT_LEVEL'        => 'Nivel de gestión',
            'PROSPECTUS_DATA'         => 'Datos del prospecto',
            'TARGET_CATEGORY'         => 'Categoría del destino',
            'VALUED_FUNCTIONS'        => 'Funciones valoradas en el cuestionario',
            'VIDEO'                   => 'Video',
		);
		
		const ELEMENT_TYPE_BUSINESS_PHASE       = 'BUSINESS_PHASE';
		const ELEMENT_TYPE_BUSINESS_TYPE        = 'BUSINESS_TYPE';
		const ELEMENT_TYPE_DIAGNOSTIC_DATA      = 'DIAGNOSTIC_DATA';
		const ELEMENT_TYPE_DYNAMIC_TEXT         = 'DYNAMIC_TEXT';
		const ELEMENT_TYPE_IMAGE                = 'IMAGE';
        const ELEMENT_IMPROVEMENT_OPPORTUNITY   = 'IMPROVEMENT_OPPORTUNITIES';
		const ELEMENT_TYPE_IMAGE_CURRENT_STATUS = 'IMAGE_CURRENT_STATUS';
		const ELEMENT_TYPE_PROSPECTUS_DATA      = 'PROSPECTUS_DATA';
		const ELEMENT_TYPE_SELECTED_TOPIC       = 'SELECTED-TOPIC';
		const ELEMENT_TYPE_TARGET_CATEGORY      = 'TARGET_CATEGORY';
        const ELEMENT_TYPE_MANAGEMENT_LEVEL     = 'MANAGEMENT_LEVEL';
        const ELEMENT_TYPE_VALUED_FUNCTIONS     = 'VALUED_FUNCTIONS';
		
		const IMAGE_CURRENT_STATUS = array(
			'BUSINESS_IDEA'      => 'Idea de negocio',
			'DESERT_CROSSING'    => 'Travesía del desierto',
			'UNSTABLE_GROWTH'    => 'Crecimiento inestable',
			'STABLE_GROWTH'      => 'Crecimiento estable',
			'OPTIMAL_OPERATION'  => 'Funcionamiento óptimo',
			//'EXPANSION_TRANSFER' => 'Expansión o traspaso',
		);
		const JOIN_CONDITIONS      = array ('AND' => 'Y', 'OR' => 'O');
		
		const PROSPECTUS_DATA      = array (
			'BUSINESS_NAME'     => 'Nombre de la empresa',
			'EMAIL_RESPONSE'    => 'Correo prospecto',
			'PROSPECT_EMAIL'    => 'Correo de instancia',
			'PROSPECT_NAME'     => 'Nombre del prospecto',
			'PROSPECT_PASSWORD' => 'Contraseña de instancia',
		);
		
		const REPORT_BLOCKS      = array (
            'DIAGNOSTIC_DATA'           => 'Datos del prospecto y la empresa',
			'CURRENT_SITUATION'	        => 'Texto explicativo de la situación actual',
			'CURRENT_STATUS'            => 'Imagen o mapa de la situación actual',
            'EXPLANATORY_DESTINATION'   => 'Texto explicativo de los destinos',
            'EXPLANATORY_OPPORTUNITIES' => 'Texto explicativo de las Oportunidades',
			'FUNCTION_RESULT'           => 'Resultados de funciones valoradas',
			'IMPROVEMENT_OPPORTUNITIES' => 'Oportunidades de mejoras',
			'INFORMATIVE_VIDEO'         => 'Video informativo',
		);
		
		const TAB_SECTION     = array (
			'CURRENT_STATUS'    => array (
				'COMPANY IDENTIFICATION'	=> 'Identificación de la empresa',
				'MAP CURRENT SITUATION'     => 'Imagen o mapa de la situación actual',
				'OPPORTUNITIES IMPROVEMENT' => '¿Cómo aprovechar las oportunidades de mejora?',
				'TAB CURRENT SITUATION'	    => 'Texto explicativo de la situación actual',
			),
			'EVALUATION'        => array ('EVALUATION' => 'Evaluación'),
			'OTHER_INFORMATION' => array (
				'BELIEFS'                 =>'Creencias',
				'CONSISTENCY_INFORMATION' =>'Consistencia de la información',
				'INDICATORS'              =>'Indicadores',
			),
		);
		const TOPICS_OPERATIONS = array (
            'COUNT' => 'Contar',
            'MAX'   => 'Máximo',
            'MIN'   => 'Mínino',
            'AVG'   => 'Promedio',
            'SUM'   => 'Suma',
        );
		const VALUED_FUNCTIONS = array (
			'COMMERCIAL_FUNCTION' => 'Función comercial',
			'TREASURY_CASHIER'    => 'Tesorería/Caja',
			'TEAM'                => 'Equipo',
			'FUNDAMENTALS'        => 'Fundamentos',
			'OPERATIONS'          => 'Operaciones',
			'RISKS'               => 'Riesgos',
		);
	}
