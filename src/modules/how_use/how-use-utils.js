(function (jQuery) {
    var excludeFields  = ['module', 'action', 'record', 'return_action', 'return_module'],
        excludeElement = ['button', 'submit', 'undefined'],
        rowFields      = [
            'listviewtab[]',
            'views[0][]',
            'views[1][]',
            'views[2][]',
            'views[3][]',
            'views[4][]',
            'views[5][]',
            'views[6][]',
            'defaultview[]'
        ],
        viewRowsDiv    = jQuery ('#views-rows'),
        totalTab       = 0,
        deleteRow      = [],
        views          = [];

    /* Private method */
    var autoSelected = function(views, defaults, i){
        var filters, n, options, relatedIds, rowTemplate;

        rowTemplate = jQuery ('#how-use-row-' + i);
        filters     = rowTemplate.find('select').eq (1);
        relatedIds  = rowTemplate.find('select').eq (2);
        options     = filters.find('option');
        n           = options.length;
        if (n === 0) {
            return;
        }
        relatedIds.empty();
        for (k = 0; k < n; k += 1) {
           var option = jQuery (options [ k ]);
            if (jQuery.inArray(option.val (), views) !== -1) {
                option.attr('selected', true);
                relatedIds.append (jQuery ('<option></option>').text (option.text ()).val (option.val ()).attr ('selected', (option.val () == defaults)));
            } else {
                option.attr('selected', false);
            }

        }
    };

    var getFiltersOptions = function (tabViews, views, isAsync) {
        var forModule  = jQuery('#formodule').val(),
            html       = '',
            arguments = {
                'module':    'how_use',
                'action':    'HowToUseAjaxUtils',
                'function':  tabViews,
                'formodule': forModule,
                'Ajax':       true
            };
        if (isAsync) {
            jQuery.ajax ('index.php', {
                async: false,
                type: 'POST',
                data: arguments
            }).done( function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if(message.error !== 'OK') {
                        throw message.error;
                    } else {
                        views.append(jQuery(message.html));
                    }
                }
                catch (e) {
                    alert(e);
                }
            });
        } else {
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        views.append(jQuery(message.html));
                    }
                }
                catch (e) {
                    alert(e);
                }
            })
        }
    };

    var getHowToUseOptions = function (selectObj,module) {
        selectObj.find('option').each(function (index, element) {
            var theOption = jQuery(element);
            if (theOption.attr('data-module') === module) {
                if (theOption.hasClass('hide')) {
                    theOption.removeClass('hide')
                }
            } else {
                theOption.addClass('hide')
            }
        })
    };

    var isMasterSelected = function (idMaster) {
        var totalFound = 0;
        viewRowsDiv.find ('div[id ^= how-use-row-]').each(function (i, elemnet) {
            if(idMaster === jQuery(this).find('select').eq(0).val()) {
                totalFound += 1;
            }
        });
        return (totalFound > 1);
    };

    /* Public method */
    var addRowProfile = function (obj) {
        var button = jQuery (obj),
            profileRows = jQuery('#profile-rows'),
            rows   = profileRows.find ('div[id ^= profile-use-row-]'),
            numRow = (rows.length - 1),
            lastRowId = jQuery (rows[numRow]).attr ('id').split('-'),
            rowProfile;

        rowProfile = jQuery('#profile-use-template').html().replace (/__ID__/g, (parseInt (lastRowId [3]) + 1));
        profileRows.append(jQuery(rowProfile));
    };

    var addRowView = function (obj) {
        var button  = jQuery (obj),
            numRow  = viewRowsDiv.find ('div[id ^= how-use-row-]').length,
            rowView;

        if ((numRow + 1) < totalTab) {
            if (deleteRow.length === 0) {
                rowView = jQuery('#how-use-view-template').html().replace (/__ID__/g, numRow);
            } else {
                rowView = jQuery('#how-use-view-template').html().replace (/__ID__/g, deleteRow[0]);
                deleteRow.splice(0,1);
            }
            viewRowsDiv.append(jQuery(rowView));
        } else {
            alert ('Upoos! no hay mas pestañas!')
        }
    };

    var activateProfieForm = function (obj) {
        var objForm = jQuery ('#subcription-profile-form'),
            btn     = jQuery(obj);
        if (! validateProfileForm (objForm)) {
            return false
        }
        btn.prop('disabled', true);
        var arguments = objForm.serialize ();
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert (message.html);
                    btn.prop('disabled', false);
                }
            }
            catch (e) {
                alert(e);
                btn.prop('disabled', false);
            }
        });

    };

    var selectedModule = function (obj) {
        viewRowsDiv.find ('div[id ^= how-use-row-]').each(function (i, elemnet) {
            var index   = jQuery(this).attr('id').substr(12);
            if (index === '0') {
                jQuery(this).find ('select').eq(0).val('');
                jQuery(this).find ('select').eq(1).empty();
                jQuery(this).find ('select').eq(2).empty();
            } else {
                jQuery(this).parent().remove()
            }
        })
    };

    var searchProfile = function (obj) {
        if (jQuery (obj).val () === '') {
            return;
        }
        var type      = jQuery ('#profile_type').val (),
            sector    = jQuery ('#profile_sector').val (),
            phase     = jQuery ('#profile_phase').val (),
            profile   = jQuery ('#profile_profile'),
            help      = jQuery ('#profile-supcrition-help'),
            arguments = {
                'module':    'how_use',
                'action':    'HowToUseAjaxUtils',
                'function':  'PROFILE_USE',
                'type':       type,
                'sector':     sector,
                'phase':      phase,
                'Ajax':       true
            };
        if ((sector === '') || (phase === '') || (type === '')) {
            return
        }
        profile.empty ();
        profile.append(jQuery('<option value="" selected>Cargando perfiles...</option>'));
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    profile.empty();
                    profile.append (jQuery (message.html));
                    help.html (message.help)
                }
            }
            catch (e) {
                alert(e);
            }
        })
    };

    var selectProfile = function (obj) {
        var profile = jQuery (obj),
            help    = jQuery ('#profile-supcrition-help');

        help.find('p').each(function (index, element) {
            jQuery(element).addClass('hide');
        });
        if (profile.val () === '') {
            help.addClass('hide');
            return
        }

        help.removeClass('hide');
        jQuery ('#' + profile.find ('option:selected').attr('data-code')).removeClass('hide');
    };

    var selectedProfileTab = function (obj) {
        var module       = jQuery(obj).val(),
            id           = jQuery (obj).parent().attr('id'),
            profileRows = jQuery('#profile-rows'),
            rows         = profileRows.find ('div[id ^= profile-use-row-]'),
            row          = jQuery(obj).parent().parent(),
            howToUse     = row.find ('select').eq (1),
            isFound      = false;
        rows.each(function (index, element) {
            var thisModule = jQuery(element).find('select').eq(0);
            if ((thisModule.parent().attr('id') !== id) && (thisModule.val() === module)) {
                isFound = true;
            }
        });
        if (isFound) {
            alert('Upoos! el Modulo: '+ module + ' ya ha sido seleccionado');
            jQuery(obj).val('');
            module = '';
        }
        howToUse.val ('');
        getHowToUseOptions (howToUse, module);
    };

    var selectedSector = function (obj) {
        var sector = jQuery (obj).val (),
            type   = jQuery ('#profile_type > option');

        type.each(function (index, element) {
            var theOption = jQuery(element);
            theOption.addClass('hide')
        });

        type.each(function (index, element) {
            var theOption = jQuery(element);
            if (jQuery.inArray (theOption.attr('data-sector'),sector) !== -1) {
                theOption.removeClass('hide')
            }

        })
    };

    var deleteRowProfile = function (obj) {
        var button  = jQuery (obj),
            thisRow = button.parent().parent();
        thisRow.parent ().remove ();
    };

    var deleteRowView = function (obj) {
        var button  = jQuery (obj),
            thisRow = button.parent().parent(),
            index   = thisRow.attr('id').substr(12);

        if (deleteRow.indexOf (index) === -1) {
            deleteRow.push (index);
        }
        thisRow.parent ().remove ();
    };

    var filterModule = function (obj) {
      var module = jQuery(obj).val (),
          tbody = jQuery('#how-use-table > tr');

      if (module !== '') {
          jQuery('tr[id ^= row-' + module+ '-]').removeClass ('hide');
          tbody.each(function (i, elemnet) {
            var tr = jQuery (elemnet),
                trId = tr.attr('id').split('-');
            if (trId[ 1 ] !== module) {
                tr.addClass('hide')
            }
          })
      } else {
          tbody.each(function (i, elemnet) {
              jQuery (elemnet).removeClass ('hide');
          })
      }
    };

    var reload = function (rows) {
        var myRows   = JSON.parse (JSON.stringify (rows)),
            views    = myRows.views,
            defaults = myRows.defaults,
            rowTemplate, filters;
        for (var i = 0; i < myRows.tabViews.length; i++) {
            var tabView = myRows.tabViews[ i ];
            if(i === 0) {
                rowTemplate = jQuery('#how-use-row-0');
            } else {
                rowTemplate = jQuery ('#how-use-view-template').html().replace (/__ID__/g, i);
                viewRowsDiv.append (rowTemplate);
                rowTemplate = jQuery ('#how-use-row-' + i);
            }
            rowTemplate.find('select').eq(0).val (tabView);
            filters = rowTemplate.find('select').eq (1);
            getFiltersOptions (tabView, filters, true);
            autoSelected (views[i][tabView], defaults[i], i)
        }
    };

    var reloadProfile = function (prifileRows) {
        var myRows      = JSON.parse (JSON.stringify (prifileRows)),
            profileRows = jQuery('#profile-rows'),
            numRow      = 0;
        jQuery.each(myRows, function (i, val) {
            if (numRow === 0){
                profileRows.find('select').eq(0).val (i);
                var howUse = profileRows.find('select').eq(1);
                getHowToUseOptions (howUse, i);
                howUse.val (val)
            } else {
                var rowProfile = jQuery('#profile-use-template').html().replace(/__ID__/g, (numRow)),
                    theRow, module, howUse;
                profileRows.append (jQuery (rowProfile));
                theRow = jQuery ('#profile-use-row-' + numRow);
                module = theRow.find ('select').eq (0);
                howUse = theRow.find ('select').eq (1);
                module.val(i);
                getHowToUseOptions (howUse, i);
                howUse.val(val);
            }
            numRow++;
        });
    };

    var selectedTab = function (obj) {
            var masterView = jQuery(obj),
                tabId      = masterView.val (),
                forModule  = jQuery('#formodule').val(),
                views      = masterView.parent().parent().find('select').eq(1),
                defaultView = masterView.parent().parent().find ('select').eq (2);
        isMasterSelected (tabId);
            if (forModule === '') {
                alert('Upoos! no ha seleccionado el módulo');
                masterView.val('');
            } else if (isMasterSelected (tabId)) {
                masterView.val('');
                views.empty();
                defaultView.empty();
                alert ('Upoos! ya has incluido esta pestaña');
            } else {
                views.empty();
                defaultView.empty();
                getFiltersOptions (tabId, views, false);
            }

    };

    var selectedViews = function (obj) {
        var row         = jQuery (obj).parent().parent(),
            views       = row.find ('select').eq (1),
            defaultView = row.find ('select').eq (2),
            $options    = views.find ('option:selected'),
            $option, i, n;

        n = $options.length;
        if (n === 0) {
            return;
        }
        defaultView.empty();
        for (i = 0; i < n; i += 1) {
            $option = jQuery ($options [ i ]);
            defaultView.append (jQuery ('<option></option>').text ($option.text ()).val ($option.val ()));
        }
    };

    var validateForm = function (objForm) {
        var form        = jQuery (objForm),
            formElement = jQuery("form[name='" + form.attr ('name') +"'] :input"),
            isValidate  = true,
            totalTabs   = 0,
            isValidateRow,
            field, operationValue, value;
        jQuery('span[id ^= hu-]').html('');
        jQuery('div[id ^= hu-div-]').removeClass ('has-error');
        //girdviewbox
        formElement.map(function (index, elm) {
            var element = jQuery(elm),
                elementTitle = element.attr('title'),
                elementName  = element.attr ('name'),
                value = element.val();
            if (jQuery.inArray(elementName, rowFields) !== -1) {
                isValidateRow = true;
                if (Array.isArray(value)){
                    if (value.length === 0) {
                        isValidate    = false;
                        isValidateRow = false;
                    }
                } else if (value === null || value.trim() === '') {
                    isValidate    = false;
                    isValidateRow = false;
                }
                if (!isValidateRow) {
                    element.parent().addClass('has-error');
                    if (element.parent().find('.help-block').length) {
                        element.parent().find('.help-block').html(elementTitle + ' requerido!');
                    } else {
                        element.parent().parent().find('.help-block').html(elementTitle + ' requerido!');
                    }
                }
            } else if ((jQuery.inArray(elm.type, excludeElement) === -1) && (jQuery.inArray(elementName, excludeFields) === -1) && elementTitle !== '' && elementTitle !== undefined) {
                if ((value === null) || (value === undefined) || (value.trim() === '')) {
                    element.parent().addClass('has-error');
                    if (element.parent().find('.help-block').length) {
                        element.parent().find('.help-block').html(elementTitle + ' requerido!');
                    } else {
                        element.parent().parent().find('.help-block').html(elementTitle + ' requerido!');
                    }
                    isValidate = false;
                }
            }
        });
        return isValidate;
    };

    var validateProfileForm = function (objForm) {
        var form        = jQuery (objForm),
            formElement = jQuery("form[name='" + form.attr ('name') +"'] :input"),
            isValidate  = true,
            totalTabs   = 0,
            isValidateRow,
            field, operationValue, value;
        jQuery('span[id ^= pu-]').html('');
        jQuery('div[id ^= pu-div-]').removeClass ('has-error');
        formElement.map(function (index, elm) {
            var element = jQuery(elm),
                elementTitle = element.attr('title'),
                elementName  = element.attr ('name'),
                value = element.val();
            if (jQuery.inArray(elementName, rowFields) !== -1) {
                isValidateRow = true;
                if (Array.isArray(value)){
                    if (value.length === 0) {
                        isValidate    = false;
                        isValidateRow = false;
                    }
                } else if (value === null || value.trim() === '') {
                    isValidate    = false;
                    isValidateRow = false;
                }
                if (!isValidateRow) {
                    element.parent().addClass('has-error');
                    if (element.parent().find('.help-block').length) {
                        element.parent().find('.help-block').html(elementTitle + ' requerido!');
                    } else {
                        element.parent().parent().find('.help-block').html(elementTitle + ' requerido!');
                    }
                }
            } else if ((jQuery.inArray(elm.type, excludeElement) === -1) && (jQuery.inArray(elementName, excludeFields) === -1) && elementTitle !== '' && elementTitle !== undefined) {
                if (jQuery.isArray(value)) {
                    if(value.length === 0) {
                        element.parent().addClass('has-error');
                        if (element.parent().find('.help-block').length) {
                            element.parent().find('.help-block').html(elementTitle + ' requerido!');
                        } else {
                            element.parent().parent().find('.help-block').html(elementTitle + ' requerido!');
                        }
                        isValidate = false;
                    }
                } else if ((value === null) || (value === undefined) || (value.trim() === '')) {
                    element.parent().addClass('has-error');
                    if (element.parent().find('.help-block').length) {
                        element.parent().find('.help-block').html(elementTitle + ' requerido!');
                    } else {
                        element.parent().parent().find('.help-block').html(elementTitle + ' requerido!');
                    }
                    isValidate = false;
                }
            }
        });
        return isValidate;
    };

    window.HowToUseUtils = {
        addRowProfile:       addRowProfile,
        addRowView:          addRowView,
        activateProfieForm:  activateProfieForm,
        deleteRowProfile:    deleteRowProfile,
        deleteRowView:       deleteRowView,
        filterModule:        filterModule,
        reload:              reload,
        reloadProfile:       reloadProfile,
        selectedModule:      selectedModule,
        searchProfile:       searchProfile,
        selectProfile:       selectProfile,
        selectedProfileTab:  selectedProfileTab,
        selectedSector:      selectedSector,
        selectedTab:         selectedTab,
        selectedViews:       selectedViews,
        validateForm:        validateForm,
        validateProfileForm: validateProfileForm
    };

    jQuery (document).ready (function () {
        totalTab = jQuery ('#master_view option').length;
    });
} (jQuery));