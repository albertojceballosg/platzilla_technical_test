<?php

	/**
	 * Smarty pluralize modifier plugin
	 *
	 * Note: This modifier plugin has been created only for Platzilla, we have to update manualy if any module name it changed
	 * Type: modifier
	 * Name: module_pluralize
	 * Purpose: convert to plural the modules´s name
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	function smarty_modifier_module_pluralize ($moduleName) {
		if (empty ($moduleName) || is_numeric ($moduleName) || !is_string ($moduleName)) {
			return $moduleName;
		}
		$module  = strtolower (sanitize ($moduleName));
		$modules = array (
			'articulo'         => 'Artículos',
			'asunto'           => 'Asuntos',
			'cliente'          => 'Clientes',
			'contacto'         => 'Contactos',
			'contrato'         => 'Contratos',
			'cotizacion'       => 'Cotizaciones',
			'colaborador'      => 'Colaboradores',
			'factura'          => 'Facturas',
			'gasto'            => 'Gastos',
			'oportunidad'      => 'Oportunidades',
			'pedido'           => 'Pedidos',
			'plan de servicio' => 'Planes de servicios',
			'prospecto'        => 'Prospectos',
			'proyecto'         => 'Proyectos',
			'venta'            => 'Ventas',
			'iniciativa'       => 'Iniciativas'
		);
		if (array_key_exists ($module, $modules)) {
			return $modules[ $module ];
		} else {
			return $moduleName;
		}
	}
