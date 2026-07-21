<?php

	/**
	 * @param $string
	 *
	 * @return mixed
	 */
	function sanitize ($string) {
		$string = str_replace (
			array ('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
			array ('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
			$string
		);
		$string = str_replace (
			array ('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
			array ('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
			$string
		);
		$string = str_replace (
			array ('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
			array ('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
			$string
		);
		$string = str_replace (
			array ('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
			array ('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
			$string
		);
		$string = str_replace (
			array ('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
			array ('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
			$string
		);
		$string = str_replace (
			array ('ñ', 'Ñ', 'ç', 'Ç'),
			array ('n', 'N', 'c', 'C'),
			$string
		);
		return $string;
	}

	/**
	 * Smarty singularize modifier plugin
	 *
	 * Note: This modifier plugin has been created only for Platzilla, we have to update manualy if any module name it changed
	 * Type: modifier
	 * Name: module_sigularize
	 * Purpose: convert to singulr the modules´s name
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	function smarty_modifier_module_singularize ($moduleName) {
		if (empty ($moduleName) || is_numeric ($moduleName) || !is_string ($moduleName)) {
			return $moduleName;
		}
		$module  = strtolower (sanitize ($moduleName));
		$modules = array (
			'articulos'           => 'Artículo',
			'asuntos'             => 'Asunto',
			'clientes'            => 'Cliente',
			'contactos'           => 'Contacto',
			'contratos'           => 'Contrato',
			'cotizaciones'        => 'Cotización',
			'colaboradores'       => 'Colaborador',
			'facturas'            => 'Factura',
			'gastos'              => 'Gasto',
			'oportunidades'       => 'Oportunidad',
			'pedidos'             => 'Pedido',
			'planes de servicios' => 'Plan de servicio',
			'prospectos'          => 'Prospecto',
			'proyectos'           => 'Proyecto',
			'ventas'              => 'Venta',
			'inciativas'          => 'Iniciativas'
		);
		if (array_key_exists ($module, $modules)) {
			return $modules[ $module ];
		} else {
			return $moduleName;
		}
	}
