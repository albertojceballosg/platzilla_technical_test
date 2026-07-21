<?php
	
	interface ManagementModeInterface {
		const ACTIONS_PROGRESS_MODULES   = array ('Calender' => 'Acciones', 'orden_de_trabajo' => 'Trabajos', 'proyecto' => 'Proyectos', 'por_proveedor' => 'Trabajos por proveedor');
		const ACTIONS_TABLE_HEADER       = array (
			'Accion'                  => array ('width' => '20', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Módulo/Registro'         => array ('width' => '20', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Asignado a'              => array ('width' => '10', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
			'Estado'                  => array ('width' => '10', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Situación'               => array ('width' => '10', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'fecha de<br>vencimiento' => array ('width' => '12', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Reportes'                => array ('width' => '10', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
			'Feedback'                => array ('width' => '10', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
			'Operaciones'                => array ('width' => '8', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '2' ),
		);
		const ACTIONS_TABLE_ROW        = array (
			'Accion'               => array ('description', array ('','subject')),
			'Módulo/Registro'      => array ('linkToDetailView', array ('','module_title', 'crmid', 'module_name', 'field')),
			'Asignado a'           => array ('showAvatar', array ('','imagename', 'username')),
			'Estado'               => array ('description', array ('','eventstatus')),
			'Situación'            => array ('description', array ('','combined_condition')),
			'Fecha de vencimiento' => array ('description', array ('','due_date')),
			'Reportes'             => array ('totalReport', array ('','reports', 'crmid', 'orden_de_trabajo')),
			'Feedback'             => array ('totalFeedback', array ('','feedbacks', 'crmid', 'orden_de_trabajo')),
			'Operaciones'             => array ('doReportAndFeedback', array ('module_name','crmid', 'activityid', 'orden_de_trabajo')),
		);
		const ISSUES_TABLE_HEADER        = array (
			'Código'       => array ('width' => '13', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Título'       => array ('width' => '30', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Prioridad'    => array ('width' => '12', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Fecha origen' => array ('width' => '12', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Tipo incidencia'  => array ('width' => '11', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Estado'       => array ('width' => '11', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Operaciones'     => array ('width' => '11', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
		);
		const ISSUES_TABLE_ROW           = array (
			'Codigo'    => array ('linkToDetailView', array ('','cod_incidencias', 'incidenciasid', 'incidencias','string')),
			'Titulo'    => array ('linkToDetailView', array ('','titulo', 'incidenciasid', 'incidencias','string')),
			'Prioridad' => array ('description', array ('','prioridad')),
			'Origen'    => array ('description', array ('','date_start')),
			'Tipo'      => array ('description', array ('','type_of_matter')),
			'Estado'    => array ('description', array ('','estado_incidencia')),
			'Operaciones'  => array ('HelpOnRecord', array ('','help_data', 'pedidosid', 'incidencias')),
		);
		const OPPORTUNITIES_TABLE_HEADER = array (
			'Código'         => array ('width' => '12', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Título'         => array ('width' => '30', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Tipo'           => array ('width' => '12', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Valor'          => array ('width' => '12', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Fecha creación' => array ('width' => '12', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Fase de venta'  => array ('width' => '12', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Operaciones'       => array ('width' => '10', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
		);
		const  OPPORTUNITIES_TABLE_ROW   = array (
			'Codigo'   => array ('linkToDetailView', array ('','cod_oportunidade', 'oportunidadesid', 'oportunidades','string')),
			'Titulo'   => array ('linkToDetailView', array ('','titulo', 'oportunidadesid', 'oportunidades','string')),
			'Tipo'     => array ('description', array ('','tipo_de_oportuni')),
			'Valor'    => array ('description', array ('','valor_oportunidad')),
			'Fecha'    => array ('description', array ('','fecha_oportunidad')),
			'Fase'     => array ('description', array ('','fase_de_venta')),
			'Operaciones' => array ('HelpOnRecord', array ('','help_data', 'oportunidadesid', 'oportunidades')),
		);
		const ORDERS_TABLE_HEADER        = array (
			'Código'                             => array ('width' => '12', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Título pedido'                      => array ('width' => '30', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'¿Qué o quién<br>origina el pedido?' => array ('width' => '20', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Prioridad'                          => array ('width' => '10', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Estado'                             => array ('width' => '8', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'fecha de<br>creación'               => array ('width' => '10', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Operaciones'                        => array ('width' => '10', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
		);
		const ORDERS_TABLE_ROW           = array (
			'Codigo'      => array ('linkToDetailView', array ('','cod_pedidos', 'pedidosid', 'pedidos','string')),
			'Titulo'      => array ('linkToDetailView', array ('','titulo', 'pedidosid', 'pedidos','string')),
			'Origen'      => array ('description', array ('','que_o_quien_origina_el_pedid')),
			'Prioridad'   => array ('description', array ('','prioridad_del_pe')),
			'Estado'      => array ('description', array ('','estado_del_pedid')),
			'Fecha'       => array ('description', array ('','date_start')),
			'Operaciones' => array ('HelpOnRecord', array ('','help_data', 'pedidosid', 'pedidos')),
		);
		const PROJECT_TABLE_HEADER       = array (
			'Proyecto'                => array ('width' => '30', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Asignado a'              => array ('width' => '15', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
			'Estado'                  => array ('width' => '15', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Porcentaje de<br>Avance' => array ('width' => '13', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'fecha de<br>Inicio'      => array ('width' => '13', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Operaciones'             => array ('width' => '14', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
		);
		const PROJECT_TABLE_ROW          = array (
			'Proyecto'             => array ('linkToDetailView', array ('','nombre', 'proyectosid', 'proyectos','string')),
			'Asignado a'           => array ('showAvatar', array ('','imagename', 'username')),
			'Estado'               => array ('description', array ('','etapa')),
			'(%) Avance'           => array ('description', array ('','porcentaje_de_avance_genera')),
			'Fecha de inicio'      => array ('description', array ('','date_start')),
			'Operaciones'          => array ('HelpOnRecord', array ('','help_data', 'crmid', 'proyectos')),
		);
		const TO_PROCESSED_MODULES       = array ('pedidos' =>'Pedidos', 'incidencias' => 'Incidencias', 'oportunidades' => 'Oportunidades');
		const WORK_TABLE_HEADER          = array (
			//'Proyecto'                => array ('width' => '16', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Tarea/actividad'         => array ('width' => '20', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Trabajo'                 => array ('width' => '18', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Asignado a'              => array ('width' => '8', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
			'Estado'                  => array ('width' => '10', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Situación'               => array ('width' => '10', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),	
			'fecha de<br>vencimiento' => array ('width' => '10', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Reportes'                => array ('width' => '8', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
			'Feedback'                => array ('width' => '8', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
			'Operaciones'             => array ('width' => '8', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '2' ),
		);
		const WORK_TABLE_ROW             = array (
			//'Proyecto'             => array ('linkToDetailView', array ('project_data','nombre', 'proyectosid', 'proyectos','string')),
			'Tarea/actividad'      => array ('description', array ('','subject')),
			'Trabajo'              => array ('linkToDetailView', array ('','titulo', 'orden_de_trabajoid', 'orden_de_trabajo','string')),
			'Asignado a'           => array ('showAvatar', array ('','imagename', 'username')),
			'Estado'               => array ('description', array ('','eventstatus')),
			'Situación'            => array ('description', array ('','combined_condition')),
			'Fecha de vencimiento' => array ('description', array ('','due_date')),
			'Reportes'             => array ('totalReport', array ('','reports', 'orden_de_trabajoid', 'orden_de_trabajo')),
			'Feedback'             => array ('totalFeedback', array ('','feedbacks', 'orden_de_trabajoid', 'orden_de_trabajo')),
			'Acciones1'            => array ('doReportAndFeedback', array ('','orden_de_trabajoid', 'activityid', 'orden_de_trabajo')),
			'Acciones2'            => array ('HelpOnRecord', array ('','help_data', 'orden_de_trabajoid', 'orden_de_trabajo')),
		);
		const SUPPLIER_WORK_TABLE_HEADER = array (
			'Tarea/actividad'         => array ('width' => '18', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Trabajo'                 => array ('width' => '18', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Proyecto'                => array ('width' => '16', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Proveedor'               => array ('width' => '14', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Estado'                  => array ('width' => '10', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'fecha de<br>vencimiento' => array ('width' => '12', 'text_align' => 'left', 'class' => 'text-center', 'colspan' => '' ),
			'Operaciones'             => array ('width' => '12', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '2' ),
		);
		const SUPPLIER_WORK_TABLE_ROW    = array (
			'Tarea/actividad'      => array ('description', array ('','subject')),
			'Trabajo'              => array ('linkToDetailView', array ('','titulo', 'orden_de_trabajoid', 'orden_de_trabajo','string')),
			'Proyecto'             => array ('linkToDetailView', array ('','project_name', 'proyectoid', 'proyectos','string')),
			'Proveedor'            => array ('linkToDetailView', array ('','supplier_name', 'proveedoresid', 'proveedores','string')),
			'Estado'               => array ('description', array ('','eventstatus')),
			'Fecha de vencimiento' => array ('description', array ('','due_date')),
			'Acciones1'            => array ('doReportAndFeedback', array ('','orden_de_trabajoid', 'activityid', 'orden_de_trabajo')),
			'Acciones2'            => array ('HelpOnRecord', array ('','help_data', 'orden_de_trabajoid', 'orden_de_trabajo')),
		);
	}