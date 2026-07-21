    // Lista completa de iconos Font Awesome 4.1.0 (sin el prefijo 'fa-')
	// cargar DOM primero 
    const faIcons = [
      "adjust", "anchor", "archive", "area-chart", "arrows", "arrows-h", "arrows-v",
      "asterisk", "at", "automobile", "balance-scale", "ban", "bank", "bar-chart",
      "barcode", "bars", "beer", "bell", "bell-o", "bicycle", "binoculars", "birthday-cake",
      "bolt", "bomb", "book", "bookmark", "bookmark-o", "briefcase", "bug", "building",
      "building-o", "bullhorn", "bullseye", "bus", "cab", "calculator", "calendar",
      "camera", "camera-retro", "car", "caret-square-o-down", "caret-square-o-left",
      "caret-square-o-right", "caret-square-o-up", "cart-arrow-down", "cart-plus",
      "cc", "certificate", "check", "check-circle", "check-circle-o", "check-square",
      "check-square-o", "child", "circle", "circle-o", "circle-o-notch", "circle-thin",
      "clock-o", "clone", "cloud", "cloud-download", "cloud-upload", "code", "code-fork",
      "coffee", "cog", "cogs", "comment", "comment-o", "comments", "comments-o", "compass",
      "copyright", "credit-card", "crop", "crosshairs", "cube", "cubes", "cutlery",
      "dashboard", "database", "desktop", "diamond", "download", "edit", "ellipsis-h",
      "ellipsis-v", "envelope", "envelope-o", "envelope-square", "eraser", "exchange",
      "exclamation", "exclamation-circle", "exclamation-triangle", "external-link",
      "external-link-square", "eye", "eye-slash", "fax", "feed", "female", "fighter-jet",
      "file-archive-o", "file-audio-o", "file-code-o", "file-excel-o", "file-image-o",
      "file-movie-o", "file-o", "file-pdf-o", "file-photo-o", "file-picture-o",
      "file-powerpoint-o", "file-sound-o", "file-video-o", "file-word-o", "file-zip-o",
      "film", "filter", "fire", "fire-extinguisher", "flag", "flag-checkered",
      "flag-o", "flash", "flask", "folder", "folder-o", "folder-open", "folder-open-o",
      "frown-o", "futbol-o", "gamepad", "gavel", "gear", "gears", "gift", "glass",
      "globe", "graduation-cap", "group", "hand-grab-o", "hand-lizard-o", "hand-paper-o",
      "hand-peace-o", "hand-pointer-o", "hand-rock-o", "hand-scissors-o", "hand-spock-o",
      "hand-stop-o", "handshake-o", "hard-of-hearing", "hashtag", "hdd-o", "headphones",
      "heart", "heart-o", "heartbeat", "history", "home", "hospital-o", "hotel",
      "hourglass", "hourglass-1", "hourglass-2", "hourglass-3", "hourglass-end",
      "hourglass-half", "hourglass-o", "hourglass-start", "i-cursor", "id-badge",
      "id-card", "id-card-o", "image", "inbox", "industry", "info", "info-circle",
      "institution", "key", "keyboard-o", "language", "laptop", "leaf", "legal",
      "lemon-o", "level-down", "level-up", "life-ring", "lightbulb-o", "line-chart",
      "location-arrow", "lock", "low-vision", "magic", "magnet", "mail-forward",
      "mail-reply", "mail-reply-all", "male", "map", "map-marker", "map-o", "map-pin",
      "map-signs", "meh-o", "microphone", "microphone-slash", "minus", "minus-circle",
      "minus-square", "minus-square-o", "mobile", "mobile-phone", "money", "moon-o",
      "mortar-board", "motorcycle", "mouse-pointer", "music", "navicon", "newspaper-o",
      "object-group", "object-ungroup", "paint-brush", "paper-plane", "paper-plane-o",
      "paperclip", "paragraph", "paste", "pause", "paw", "pencil", "pencil-square",
      "pencil-square-o", "phone", "phone-square", "photo", "picture-o", "pie-chart",
      "plane", "plug", "plus", "plus-circle", "plus-square", "plus-square-o", "power-off",
      "print", "puzzle-piece", "qrcode", "question", "question-circle", "quote-left",
      "quote-right", "random", "recycle", "refresh", "registered", "remove", "reorder",
      "repeat", "reply", "reply-all", "retweet", "road", "rocket", "rss", "rss-square",
      "search", "search-minus", "search-plus", "send", "send-o", "server", "share",
      "share-alt", "share-alt-square", "share-square", "share-square-o", "shield",
      "ship", "shopping-cart", "sign-in", "sign-out", "signal", "sitemap", "sliders",
      "smile-o", "soccer-ball-o", "sort", "sort-alpha-asc", "sort-alpha-desc",
      "sort-amount-asc", "sort-amount-desc", "sort-asc", "sort-desc", "sort-numeric-asc",
      "sort-numeric-desc", "space-shuttle", "spinner", "spoon", "square", "square-o",
      "star", "star-half", "star-half-empty", "star-half-full", "star-half-o",
      "star-o", "sticky-note", "sticky-note-o", "street-view", "suitcase", "sun-o",
      "support", "tablet", "tachometer", "tag", "tags", "tasks", "taxi", "television",
      "terminal", "thumb-tack", "thumbs-down", "thumbs-o-down", "thumbs-o-up",
      "thumbs-up", "ticket", "times", "times-circle", "times-circle-o", "tint",
      "toggle-off", "toggle-on", "trademark", "trash", "trash-o", "tree", "trophy",
      "truck", "tty", "tv", "umbrella", "universal-access", "university", "unlock",
      "unlock-alt", "upload", "user", "user-circle", "user-circle-o", "user-o",
      "user-plus", "user-secret", "user-times", "users", "video-camera", "volume-down",
      "volume-off", "volume-up", "warning", "wheelchair", "wifi", "wrench", "youtube-play"
    ];
    function initFASelector() {
      const iconGrid = document.getElementById('icon-grid');
      const iconPreview = document.getElementById('icon-preview');
      const buttonPreview = document.getElementById('button-preview');
      const $iconGrid = jQuery('#icon-grid');
      const $iconPreview = jQuery('#icon-preview');
      const $buttonPreview = jQuery('#button-preview');
      const $faIcon = jQuery('#faIcon');

      if (!iconGrid || !iconPreview || !buttonPreview) {
        return; // No UI targets present on this page
      }

      function isIconSupported(name) {
        const test = document.createElement('i');
        test.className = `fa fa-${name}`;
        test.style.position = 'absolute';
        test.style.left = '-9999px';
        document.body.appendChild(test);
        const content = window.getComputedStyle(test, ':before').getPropertyValue('content');
        document.body.removeChild(test);
        return content && content !== 'none' && content !== 'normal' && content !== '""';
      }


    function loadIcons() {
      let firstSelected = false;
      faIcons.forEach((iconName) => {
        const name = (iconName || '').trim();
        if (!name || !isIconSupported(name)) return; // skip unsupported icons
        const div = document.createElement('div');
        div.className = 'icon-item';
        div.setAttribute('role', 'listitem');
        div.setAttribute('tabindex', '0');
        div.innerHTML = `<i class="fa fa-${name}"></i>`;

        if (!firstSelected) {
          div.classList.add('selected');
          $iconPreview.html(`<i class="fa fa-${name}"></i>`);
          $buttonPreview.html(`<i class="fa fa-${name}"></i> Botón ejemplo`);
          firstSelected = true;
        }
        
        $iconGrid.append(div);
      });
    }
    

      function selectIcon(element) {
        const $prevSelected = $iconGrid.find('.icon-item.selected');
        if ($prevSelected.length) $prevSelected.removeClass('selected');
        const $el = jQuery(element);
        $el.addClass('selected');
        const $iEl = $el.find('i').first();
        if (!$iEl.length) return;
        const iEl = $iEl.get(0);
        const faClass = (Array.from(iEl.classList).find(c => c.indexOf('fa-') === 0)) || 'fa-home';
        $iconPreview.html(`<i class="fa ${faClass}"></i>`);
        $buttonPreview.html(`<i class="fa ${faClass}"></i> Botón ejemplo`);
        $faIcon.val(faClass);
      }

      loadIcons();

      // jQuery handlers for icon selections
      $iconGrid.on('click', '.icon-item', function () {
        selectIcon(this);
      });
      $iconGrid.on('keydown', '.icon-item', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          selectIcon(this);
        }
      });

      // Hook style toggle group to use tooglebtnprev()
      // jQuery handler for .btn-group style toggle
      const $styleGroup = jQuery('.btn-group[data-toggle="buttons"]');
      if ($styleGroup.length) {
        $styleGroup.on('click', 'label, input[name="styleButton"]', function (e) {
          const $t = jQuery(e.target);
          const input = $t.is('input') ? $t[0] : $t.closest('label').find('input[name="styleButton"]')[0];
          if (input) {
            setTimeout(function(){ tooglebtnprev(input); }, 0);
          }
        });
        $styleGroup.find('input[name="styleButton"]').on('change', function () {
          tooglebtnprev(this);
        });
        const initial = $styleGroup.find('input[name="styleButton"]:checked')[0] || $styleGroup.find('label.active input[name="styleButton"]').first()[0];
        if (initial) { tooglebtnprev(initial); }

        // Extract Font Awesome icon class from the preview button

      }
    }
    
    function tooglebtnprev(element) {
      const $btn = jQuery('#button-preview');
      if (!$btn.length) return;
      const contextualClasses = ['btn-primary', 'btn-success', 'btn-info', 'btn-warning', 'btn-danger'];
      let newClass = null;
      const $label = jQuery(element).closest('label');
      if ($label.length) {
        newClass = contextualClasses.find(function(c){ return $label.hasClass(c); }) || null;
      }
      if (!newClass) {
        const val = String((element && element.value) || '').toLowerCase();
        const map = { primary: 'btn-primary', success: 'btn-success', info: 'btn-info', warning: 'btn-warning', danger: 'btn-danger' };
        newClass = map[val] || 'btn-primary';
      }
      $btn.removeClass(contextualClasses.join(' ')).addClass(newClass);
    }

jQuery(function(){ initFASelector(); });



(function (jQuery)
{
	// Private variables
	var fLabels = [];
	var hfLabels = [];
	var typeofdata = [];
    var cloneGroup = '';
    var moduleData = '';

	// Private methods

	var onAjaxFailureHandler = function (jQueryResponse) {
		alert ('Se ha presentado un error. Intenta más tarde');
	};

	var onGetColumnsSuccessHandler = function (responseText) {
        moduleData = responseText;
		var obj;
		if (totalFilterGroup > 1) {
			obj = jQuery('#group-' + (totalFilterGroup - 1));
		} else {
			obj = '';
		}
		setFieldsOptions(responseText, obj);
	};

	var setFieldsOptions = function (responseText, obj) {
    var fieldsSelect,
        fields = JSON.parse(responseText);
    if ((fields === null) || (fields === undefined)) {
        return;
    }

    // Select the first select (fields dropdown) either globally (first group)
    // or within the provided group container (subsequent groups)
    if (obj === '') {
        fieldsSelect = jQuery('#fieldFilter');
    } else {
        fieldsSelect = obj.find('select').eq(0);
    }

    // Reset and add default option
    if (fieldsSelect && fieldsSelect.length) {
        fieldsSelect.empty();
        fieldsSelect.append(
            jQuery('<option>', { value: '', text: '-Ninguno-' })
        );
    }

    jQuery.each(
        fields,
        function (i, field) {
            if ((field === null) || (field === undefined) || (!(field instanceof Object)) || (jQuery.isEmptyObject(field))) {
                return;
            }
            // Populate the fields dropdown with available fields and their data types
            fieldsSelect.append(
                jQuery('<option>', {
                    value: field.fieldname,
                    text: field.label
                }).attr('data-type', field.typeofdata)
            );
        }
    );
};



    var validateForm = function () {
        jQuery('span[id ^= cb-]').html('');
        jQuery('div[id ^= cb-dv-]').removeClass('has-error');
        var field, isValidate = true;
        if (!jQuery ('#title').val ()) {
            jQuery('#cb-title').html('Especifique el título del botón');
            jQuery('#cb-dv-title').addClass('has-error');
            isValidate = false;
        }
        if (!jQuery ('#description').val ()) {
            jQuery('#cb-description').html('Escriba una descripción para el botón');
            jQuery('#cb-dv-description').addClass('has-error');
            isValidate = false;
        }
        if (!jQuery('#faIcon').val()) {
            alert('Seleccione un icono para el botón');
            isValidate = false;
        }
        // Validate styleButton selection is not null
        if (jQuery('input[name="styleButton"]:checked').length === 0) {
            alert('Seleccione un estilo para el botón');
            isValidate = false;
        }
        if (!jQuery ('#modulo').val ()) {
            jQuery('#cb-modulo').html('Seleccione el módulo en el que aparecerá el botón');
            jQuery('#cb-dv-modulo').addClass('has-error');
            isValidate = false;
        }
        field = jQuery ('#type');
        if ((field.val () === 'js') && (!jQuery ('#clickaction').val ())) {
            jQuery('#cb-clickaction').html('Indica la acción Javascript que ejecutará el botón');
            jQuery('#cb-dv-clickaction').addClass('has-error');
            isValidate = false;
        } else if ((field.val () === 'link') && (!jQuery ('#linkaction').val ())) {
            jQuery('#cb-linkaction').html('Indica el URL al cual te llevará el botón');
            jQuery('#cb-dv-linkaction').addClass('has-error');
            isValidate = false;
        } else if ((field.val () === 'backgroundtask') && (!jQuery ('#backgroundtaskaction').val ())) {
            jQuery('#cb-backgroundtask').html('Selecciona la tarea en segundo plano que ejecutará el botón');
            jQuery('#cb-dv-backgroundtask').addClass('has-error');
            isValidate = false;
        }
        jQuery('li[id ^= row-]').each(function(index,item){

            jQuery(item).find('span').eq(0).html('');
            jQuery(item).find('span').eq(1).html('');
            jQuery(item).find('span').eq(2).html('');

			if(jQuery(item).find('select').eq(0).val() == '') {
                jQuery(item).find('span').eq(0).html('La variable es requerida');
                isValidate = false;
			}

            if(jQuery(item).find('select').eq(1).val() == '') {
                jQuery(item).find('span').eq(1).html('El Operador es requerido');
                isValidate = false;
            }

            if(jQuery(item).find('input').eq(0).val() == '') {
                idfield = jQuery(item).find('input').eq(0).attr('id')
                jQuery(item).find('span').eq(2).html('El valor es requerido');
                isValidate = false;
            }
        });

        return isValidate;
    };

    
	// Public methods
    var addFilterGroup = function (obj) {
        var module       = jQuery ('#modulo');
        if(module.val() == '') {
            module.parent().addClass('has-error');
            module.parent().find ('.help-block').html('Selecciona el modulo');
            return false;
		}

		var conditionGroups = jQuery('.action-bar'),
			conditionGroupTemplate = jQuery(jQuery('#condition-group-template').html().replace(/__GROUP_ID__/g, totalFilterGroup)),
			conditionTemplate = jQuery('#condition-template').html().replace(/__GROUP_ID__/g, totalFilterGroup); //.replace(/__CONDITION_ID__/g, -1)
            conditionGroupTemplate.find('.conditions').append(conditionTemplate);
		conditionGroups.before(conditionGroupTemplate);
		totalFilterGroup += 1;
		totalFilterRow += 1;
		jQuery(obj).attr('data-group',totalFilterGroup );
		if(moduleData === '') {
			getCustomButtonField(module);
			if(totalFilterGroup > 1) {
                jQuery('#group-'+(totalFilterGroup-2)).find('.operator').removeClass ('hidden').removeAttr ('disabled');
			}
		} else {
			jQuery('#group-'+(totalFilterGroup-2)).find('.operator').removeClass ('hidden').removeAttr ('disabled');
			setFieldsOptions(moduleData,jQuery ('#group-'+(totalFilterGroup - 1)))
		}
    };

	var blockTypeButton = function () {
        var type = jQuery ('#type').val (),
            action;
        if (type == 'js') {
            jQuery ('#linkaction').val ('').prop ('disabled', true);
            jQuery ('#backgroundtaskaction').val ('').prop ('disabled', true);
            jQuery ('#clickaction').prop ('disabled', false);
        } else if (type == 'link') {
            jQuery ('#backgroundtaskaction').val ('').prop ('disabled', true);
            jQuery ('#clickaction').val ('').prop ('disabled', true);
            jQuery ('#linkaction').prop ('disabled', false);
        } else {
            jQuery ('#clickaction').val ('').prop ('disabled', true);
            jQuery ('#linkaction').val ('').prop ('disabled', true);
            jQuery ('#backgroundtaskaction').prop ('disabled', false);
        }
    };

    var eraseFilterValue = function (obj) {
		var elementRow = '';
		elementRow = jQuery (obj).parent ();
		elementRow.find ('input').eq (0).val ('');
		return false;
	};

	var eraseFilterRow = function (obj) {
		var prevElementRow, thisRow, thisId, lastRowId,
			infoTexto = '¿Esás seguro de borrar la condición seleccionada?';
		var r = confirm (infoTexto);
		if (r == true) {
            thisRow = jQuery (obj).parent ().parent ().parent ();
            lastRowId = thisRow.parent().find('li:last-child').attr('id');
            thisId    = thisRow.attr('id');
            prevElementRow = thisRow.prev ();
            if(thisId == lastRowId) {
                prevElementRow.find('select').eq(2).addClass('hidden').attr('disabled', 'disabled');
            }
            thisRow.remove ()
		}
	};

	var eraseFilterGroup = function(obj) {
        var elementGroup, thisGroup,idGroup, lastGroup
            infoTexto = '¿Esás seguro de borrar el grupo de condiciones seleccionado?';
        thisGroup = jQuery (obj).parent ().parent ().parent ().parent ();
        idGroup = thisGroup.attr('id');
        var r = confirm (infoTexto);
        if (r == true) {
            lastGroup = jQuery('div.filter_goup').last().attr('id');
            if(idGroup == lastGroup) {
                thisGroup.prev().find('.operator').addClass ('hidden').attr ('disabled', 'disabled');
                totalFilterGroup -= 1;
            }
            thisGroup.remove ();
        }
	};

	var setFilterRow = function (obj) {
		var elementRow, newElementRow, numRow, fieldSelect, totalRow;
		elementRow = jQuery (obj).parent ().parent ().parent().find('li:last-child');
		newElementRow = elementRow.clone ().attr('id','row-'+totalFilterRow);
        elementRow.find ('select').eq(2).removeClass ('hidden').removeAttr ('disabled');
        newElementRow.find ('button').eq (0).removeClass ('hidden');
        newElementRow.find ('select').eq (0).val('');
        newElementRow.find ('select').eq (1).val('');
        newElementRow.find ('input').eq (0).val('');
		newElementRow.appendTo (elementRow.parent ());
        totalFilterRow += 1;
	};

	var getCustomButtonField = function (moduleSelect) {
		var module        = jQuery (moduleSelect),
			moduleName    = module.val (),
			form          = module.closest ('form'),
			params;
		if ((moduleName === null) || (moduleName === undefined) || (moduleName.trim () === '')) {
			return;
		}

		params = [
			'module=Settings',
			'action=CustomButtonActions',
			'function=getColumns',
			'Ajax=true',
			'fld_module=' + encodeURIComponent (moduleName)
		];
		jQuery.ajax (
			'index.php',
			{
				data:     params.join ('&'),
				dataType: 'text',
				method:   'post'
			}
		).done (onGetColumnsSuccessHandler).fail (onAjaxFailureHandler);
	};

	var getTypoOfOperation = function (obj) {
		var selectedType   = '',
			dataType       = '',
			operatorSource = jQuery ('#opcolumnTwo'),
			operators      = [ 'Conteo', 'Suma', 'Promedio' ],
			numOperations  = 0,
			trRow          = jQuery ('.graphcis-oper-two');
		selectedType = jQuery (obj).val ();
		dataType = jQuery (obj).children ('option:selected').attr ('data-type');

		operatorSource.empty ();
		if (selectedType != '') {
			if (jQuery.inArray (dataType, [ 'N', 'NN' ]) !== -1) {
				numOperations = 3;
			} else {
				numOperations = 1;
			}
			for (var i = 0; i < numOperations; i++) {
				operatorSource.append (
					jQuery (
						'<option>',
						{
							value: (i + 1),
							text:  operators[ i ]
						}
					)
				);
			}
			trRow.removeClass ('hide');
		} else {
			trRow.addClass ('hide');
		}
	};


	var getViewsAvailable =  function (obj) {
		var view = jQuery(obj).val (),
			buttonType = jQuery('#type');

		if (view === 'ActionButton') {
			buttonType.val ('backgroundtask');
            jQuery ('#type option:not(:selected)').attr ('disabled', true);
		} else {
            jQuery ('#type option:not(:selected)').attr ('disabled', false);
		}

    };

	var setHelpToField = function (obj) {
		var elementRow       = '',
			selectedOperator = '';
		selectedOperator = jQuery (obj).val ();
		elementRow = jQuery (obj).parent ().parent ();
		elementRow.find ('input').eq (0).val ('');
        elementRow.find ('input').eq (0).attr('readonly', false);
        if ((selectedOperator === 'in') || (selectedOperator === 'inn')) {
            elementRow.find ('input').eq (0).val(hfLabels[ selectedOperator ]);
            elementRow.find ('input').eq (0).attr('readonly', true);
        } else {
            elementRow.find ('input').eq (0).attr ('placeholder', hfLabels[ selectedOperator ]);
        }
	};

	var setFilterOperators = function (obj)
	{
		var filterRow    = '',
			selectedType = '',
			thisOperator = '',
			thisInput    = '';
		selectedType = jQuery (obj).children ('option:selected').attr ('data-type');
		filterRow = jQuery (obj).parent ().parent ();
		thisOperator = filterRow.find ('select').eq (1);
		thisInput = filterRow.find ('input').eq(0);
        thisInput.val('');
		        if (selectedType != null && selectedType.length != 0) {
            if (jQuery.inArray (selectedType, [ 'T', 'D', 'DT' ]) !== -1) {
                filterRow.find ('.is-date').removeClass('hide');
                thisInput.attr('readonly', true);
                thisInput.datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
            } else {
                filterRow.find ('.is-date').addClass('hide');
                thisInput.attr ('readonly', false);
                thisInput.datepicker ('remove');
            }
            var ops = typeofdata[ selectedType ];
            if (ops != null) {
                thisOperator.empty ();
                jQuery (thisOperator).append (
                    jQuery (
                        '<option>',
                        {
                            value: '',
                            text:  '-Ninguno-'
                        }
                    )
                );
                for (var i = 0; i < ops.length; i++) {
                    var label = fLabels[ ops[ i ] ];
                    if (label == null) {
                        continue;
                    }
                    jQuery (thisOperator).append (
                        jQuery (
                            '<option>',
                            {
                                value: ops[ i ],
                                text:  label
                            }
                        )
                    );

                }
            }
        } else {
            if (selectedType == '') {
                if (thisOperator.children('option').length === 0) {
                    thisOperator.append(
                        jQuery('<option>', { value: '', text: '-Ninguno-' })
                    );
                }
                thisOperator.prop('selectedIndex', 0);
            }
        }
    };

    var validateRepeatData = function () {
    	if(! validateForm()) {
            jQuery("html, body").animate({ scrollTop: 0}, 800);
    		return false;
		}
        var linkaction = jQuery ('#linkaction').val (),
            params;

        if (linkaction != '') {
            linkaction = linkaction.replace (/[?]/g, "|");
            linkaction = linkaction.replace (/[&]/g, "!");
        }


        params = [
            'module=Settings',
            'action=SettingsAjax',
            'file=validateSaveEditCustomButtons',
            'validation=norepeatnamebuttonEdit',
            'modulo=' + encodeURIComponent (jQuery ('#modulo').val ()),
            'title=' + encodeURIComponent (jQuery ('#title').val ()),
            'clickaction=' + encodeURIComponent (jQuery ('#clickaction').val ()),
            'linkaction=' + encodeURIComponent (linkaction),
            'type=' + encodeURIComponent (jQuery ('#type').val ())
        ];
        jQuery.ajax ('index.php', {
            data:     params.join ('&'),
            dataType: 'text',
            method:   'post'
        }).done (function (response) {
            if (response === 'repeated') {
                alert ('El bot\u00F3n que desea actualizar ya se encuentra registrado');
                return false
            } else {
                jQuery ('#SaveEditCustomButtons').submit ();
                return true;
            }
        });
    };

	window.CBUtils = {
		addFilterGroup:            addFilterGroup,
        blockTypeButton:		   blockTypeButton,
		setFilterRow:              setFilterRow,
        eraseFilterGroup:		   eraseFilterGroup,
		eraseFilterValue:          eraseFilterValue,
		eraseFilterRow:            eraseFilterRow,
		getCustomButtonField:      getCustomButtonField,
		getTypoOfOperation:        getTypoOfOperation,
        getViewsAvailable:         getViewsAvailable,
		setHelpToField:            setHelpToField,
		setFilterOperators:        setFilterOperators,
        validateRepeatData:		   validateRepeatData
	};
    if (typeof alert_arr !== "undefined") {
        fLabels[ 'l' ] = alert_arr.LESS_THAN;
        fLabels[ 'g' ] = alert_arr.GREATER_THAN;
        fLabels[ 'm' ] = alert_arr.LESS_OR_EQUALS;
    }

	var onDocumentReadyHandler = function ()
	{
		if (typeof alert_arr !== "undefined") {
			fLabels[ 'e' ] = alert_arr.EQUALS;
			fLabels[ 'n' ] = alert_arr.NOT_EQUALS_TO;
			fLabels[ 's' ] = alert_arr.STARTS_WITH;
			fLabels[ 'ew' ] = alert_arr.ENDS_WITH;
			fLabels[ 'c' ] = alert_arr.CONTAINS;
			fLabels[ 'k' ] = alert_arr.DOES_NOT_CONTAINS;
			fLabels[ 'h' ] = alert_arr.GREATER_OR_EQUALS;
			fLabels[ 'bw' ] = alert_arr.BETWEEN;
			fLabels[ 'b' ] = alert_arr.BEFORE;
			fLabels[ 'a' ] = alert_arr.AFTER;
            fLabels[ 'in' ] = 'es nulo';
            fLabels[ 'inn' ] = 'no es nulo';
			hfLabels[ 'e' ] = 'texto o valor para comparar';
			hfLabels[ 'n' ] = 'texto o valor para comparar';
			hfLabels[ 's' ] = 'Comienza con el texto?';
			hfLabels[ 'ew' ] = 'Termina con el texto?';
			hfLabels[ 'c' ] = 'Contiene el texto?';
			hfLabels[ 'k' ] = 'No contiene el texto?';
			hfLabels[ 'l' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'g' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'm' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'h' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'bw' ] = 'inferior,superior o fechas: aaaa-mm-dd,aaaa-mm-dd';
			hfLabels[ 'b' ] = 'antes de aaaa-mm-dd';
			hfLabels[ 'a' ] = 'despues de aaaa-mm-dd';
            hfLabels[ 'in' ] = 'NULL';
            hfLabels[ 'inn' ] = 'NOT NULL';
		} else {
            // Fallback labels when alert_arr is unavailable
            fLabels[ 'e' ] = 'Igual a';
            fLabels[ 'n' ] = 'Diferente de';
            fLabels[ 's' ] = 'Comienza con';
            fLabels[ 'ew' ] = 'Termina con';
            fLabels[ 'c' ] = 'Contiene';
            fLabels[ 'k' ] = 'No contiene';
            fLabels[ 'h' ] = 'Mayor o igual que';
            fLabels[ 'bw' ] = 'Entre';
            fLabels[ 'b' ] = 'Antes de';
            fLabels[ 'a' ] = 'Después de';
            fLabels[ 'in' ] = 'es nulo';
            fLabels[ 'inn' ] = 'no es nulo';
            hfLabels[ 'e' ] = 'texto o valor para comparar';
            hfLabels[ 'n' ] = 'texto o valor para comparar';
            hfLabels[ 's' ] = 'Comienza con el texto?';
            hfLabels[ 'ew' ] = 'Termina con el texto?';
            hfLabels[ 'c' ] = 'Contiene el texto?';
            hfLabels[ 'k' ] = 'No contiene el texto?';
            hfLabels[ 'l' ] = 'Valor o aaaa-mm-dd si es fecha';
            hfLabels[ 'g' ] = 'Valor o aaaa-mm-dd si es fecha';
            hfLabels[ 'm' ] = 'Valor o aaaa-mm-dd si es fecha';
            hfLabels[ 'h' ] = 'Valor o aaaa-mm-dd si es fecha';
            hfLabels[ 'bw' ] = 'inferior,superior o fechas: aaaa-mm-dd,aaaa-mm-dd';
            hfLabels[ 'b' ] = 'antes de aaaa-mm-dd';
            hfLabels[ 'a' ] = 'despues de aaaa-mm-dd';
            hfLabels[ 'in' ] = 'NULL';
            hfLabels[ 'inn' ] = 'NOT NULL';
		}
        typeofdata[ 'V' ] = [ 'e', 'n', 's', 'ew', 'c', 'k', 'in', 'inn' ];
		typeofdata[ 'N' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'in', 'inn' ];
		typeofdata[ 'T' ] = [ 'e', 'b', 'a', 'in', 'inn' ];
		typeofdata[ 'I' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'in', 'inn' ];
		typeofdata[ 'C' ] = [ 'e', 'n', 'in', 'inn' ];
		typeofdata[ 'D' ] = [ 'e', 'b', 'a', 'in', 'inn' ];
		typeofdata[ 'DT' ] = [ 'e', 'b', 'a', 'in', 'inn' ];
		typeofdata[ 'NN' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'in', 'inn' ];
		typeofdata[ 'E' ] = [ 'e', 'n', 's', 'ew', 'c', 'k', 'in', 'inn' ];

        if(jQuery (".condition-groups").find (".condition-group").length > 0) {
        	var dataType = '',filterRow,thisInput;
            jQuery('li[id ^= row-]').each(function(index,item){
                dataType = jQuery(item).find('select').eq(0).children ('option:selected').attr ('data-type');
                thisInput = jQuery(item).find('input').eq(0);
                if (jQuery.inArray (dataType, [ 'T', 'D', 'DT' ]) !== -1) {
                    filterRow = jQuery(item).find('select').eq(0).parent().parent();
                    filterRow.find ('.is-date').removeClass('hide');
                    thisInput.attr('readonly', true);
                    thisInput.datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
                }

            });
        }
        getViewsAvailable (jQuery('#vista'));
	};
	jQuery (document).ready (onDocumentReadyHandler);
}(jQuery));
