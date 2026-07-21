(function (jQuery) {
	/**
	 * Mapa de relaciones picklist -> pipeline.
	 * Formato:
	 *   {
	 *     motherPicklistField: {
	 *       pipelineField: {
	 *         motherValue: ['valorPermitido1', 'valorPermitido2', ...]
	 *       }
	 *     }
	 *   }
	 */
	var relationships = {};

	var setValue = function (buttonElement) {
		var button   = jQuery (buttonElement),
			buttons  = button.closest ('.pipeline-chart').find ('.pipeline-element'),
			position = button.data ('index'),
			i;

		for (i = 0; i < buttons.length; i += 1) {
			if (i <= position) {
				jQuery (buttons [ i ]).addClass ('selected');
			} else {
				jQuery (buttons [ i ]).removeClass ('selected');
			}
		}
		button.closest ('.field-container').find ('.pipeline-value').val (button.text ()).trigger ('change');
	};

	/**
	 * Obtiene el valor actual del campo picklist madre, soportando input/select
	 * y buscando por id o por name para mayor robustez.
	 */
	var getFieldValue = function (fieldName) {
		var element = jQuery ('#' + fieldName);
		if (element.length === 0) {
			element = jQuery ('[name="' + fieldName + '"]').first ();
		}
		return element.length ? element.val () : null;
	};

	/**
	 * Filtra los botones de un pipeline según el valor del picklist madre.
	 *
	 * @param {string} motherField    Nombre del campo picklist madre
	 * @param {string} pipelineField  Nombre del campo pipeline
	 * @param {string} motherValue    Valor actual del picklist madre
	 */
	var filterPipelineByPicklist = function (motherField, pipelineField, motherValue) {
		var container, buttons, allowedValues, hasRestriction;

		container = jQuery ('.pipeline-container[data-pipeline-field="' + pipelineField + '"]');
		if (container.length === 0) {
			return;
		}

		buttons        = container.find ('.pipeline-element');
		allowedValues  = null;
		hasRestriction = false;

		if (
			relationships &&
			relationships [ motherField ] &&
			relationships [ motherField ][ pipelineField ] &&
			(typeof motherValue !== 'undefined') && (motherValue !== null) && (motherValue !== '') &&
			relationships [ motherField ][ pipelineField ].hasOwnProperty (motherValue)
		) {
			allowedValues  = relationships [ motherField ][ pipelineField ][ motherValue ];
			hasRestriction = true;
		}

		buttons.each (function () {
			var btn         = jQuery (this),
				btnValue    = btn.data ('pipeline-value'),
				isAllowed;

			if (!hasRestriction) {
				// Sin restricción: mostrar todos los botones
				btn.show ().prop ('disabled', false);
				return;
			}

			isAllowed = (jQuery.inArray (btnValue, allowedValues) !== -1);
			if (isAllowed) {
				btn.show ().prop ('disabled', false);
			} else {
				btn.hide ().prop ('disabled', true).removeClass ('selected');
			}
		});

		// Recalcular ancho de botones visibles para mantener layout consistente
		var visibleButtons = buttons.filter (':visible'),
			visibleCount   = visibleButtons.length;
		if (visibleCount > 0) {
			visibleButtons.css ('width', 'calc((100% - 16px) / ' + visibleCount + ')');
		}

		// Si el valor actual del pipeline ya no es válido, limpiarlo
		var hiddenInput = container.find ('.pipeline-value'),
			currentVal  = hiddenInput.val ();
		if (hasRestriction && currentVal && (jQuery.inArray (currentVal, allowedValues) === -1)) {
			hiddenInput.val ('').trigger ('change');
			buttons.removeClass ('selected');
		}
	};

	/**
	 * Aplica filtros a todos los pipelines relacionados con un picklist madre
	 * tomando el valor actual del DOM.
	 */
	var applyFiltersForMother = function (motherField) {
		if ((!relationships) || (!relationships [ motherField ])) {
			return;
		}
		var motherValue = getFieldValue (motherField),
			pipelineField;
		for (pipelineField in relationships [ motherField ]) {
			if (relationships [ motherField ].hasOwnProperty (pipelineField)) {
				filterPipelineByPicklist (motherField, pipelineField, motherValue);
			}
		}
	};

	/**
	 * Inicializa los filtros: almacena el mapa de relaciones, asocia listeners
	 * a los picklists madre y aplica el filtro inicial según valores actuales.
	 *
	 * @param {object} relationshipsMap  Mapa de relaciones (ver estructura arriba)
	 */
	var initFilters = function (relationshipsMap) {
		if ((!relationshipsMap) || (typeof relationshipsMap !== 'object')) {
			return;
		}
		relationships = relationshipsMap;

		var motherField;
		for (motherField in relationships) {
			if (!relationships.hasOwnProperty (motherField)) {
				continue;
			}

			// Registrar listener (namespace para evitar duplicados al reinicializar)
			jQuery (document)
				.off ('change.pipelineFilter', '#' + motherField + ', [name="' + motherField + '"]')
				.on ('change.pipelineFilter', '#' + motherField + ', [name="' + motherField + '"]',
					(function (field) {
						return function () {
							applyFiltersForMother (field);
						};
					} (motherField))
				);

			// Aplicar filtro inicial con el valor ya presente en el formulario
			applyFiltersForMother (motherField);
		}
	};

	window.PipelineUtils = {
		setValue                : setValue,
		filterPipelineByPicklist: filterPipelineByPicklist,
		applyFiltersForMother   : applyFiltersForMother,
		initFilters             : initFilters
	};
} (jQuery));