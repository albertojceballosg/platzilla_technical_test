(function (jQuery) {
    var excludeFields  = ['module', 'action', 'record', 'return_action', 'return_module'],
        excludeElement = ['button', 'submit', 'undefined'],
        dataObjective  = [],
        dataKeyResults = [];

    // Private method
    var closeNotifyWizard = function () {
        if (wizard) {
            wizard.reset ().close ().trigger ('closed');
        }
    };

    var destroyWizard = function () {
        wizard = null;
       // window.location.reload ();
    };

    var setOkrs = function (card) {
        var dataForm     = jQuery ('#setp-1-section > .data-section'),
            companyType  = dataForm.find ('#companytype').val (),
            companyPhase = dataForm.find ('#companyphase').val (),
            arguments = {
                'module':       'okrs',
                'action':       'AjaxOkrUtils',
                'function':     'OKRS_CARD',
                'companytype':  companyType,
                'companyphase': companyPhase,
                'Ajax':         true
            };
        wizard.cards [ 'setp-2' ].el.find ('.data-section').empty ().append ('<div class="text-center">Cargando...</div>');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    wizard.cards [ 'setp-2' ].el.find ('.data-section').empty ().append (message.html);
                }
            }
            catch (e) {
                alert(e);
            }
        })

    };

    var submitWizard = function (wizard) {
        var field = jQuery ('#contents');
        field.val (checkInstance.getData ());
        jQuery.ajax ('index.php', {
            data:     wizard.serialize (),
            dataType: 'json',
            method:   'post'
        }).done (function () {
            wizard.submitSuccess ();
            wizard.hideButtons ();
        }).fail (function (jQueryResponse) {
            wizard.el.find ('.wizard-failure .message').text (jQueryResponse.responseJSON);
            wizard.submitFailure ();
            wizard.hideButtons ();
        });
    };

    var updateProgressBar = function () {
        var cards      = wizard.cards,
            activeCard = wizard.getActiveCard (),
            index      = 0,
            cardName;
        for (cardName in cards) {
            if (cardName === activeCard.name) {
                break;
            }
            index += 1;
        }
        wizard.updateProgressBar ((index * 100) / Object.keys (wizard.cards).length);
    };

    var validateSetpOne = function (card) {
        var dataForm               = jQuery ('#' + card.name + '-section > .data-section'),
            error                  = true,
            field, value, infoText = '',
            moduleField            = '',
            moduleValue            = '',
            selectedType           = '';

        jQuery ('span[id ^= sp-]').html ('');
        jQuery ('div[id ^= dv-]').removeClass ('has-error');

        field       = dataForm.find ('#companytype');
        value       = field.val ();
        moduleValue = value;
        moduleField = field;
        if ((value === null) || (value === undefined) || (value.length === 0)) {
            jQuery ('#sp-companytype').html ('Selecciona el tipo de empresa');
            jQuery ('#dv-companytype').addClass ('has-error');
            error = false;
        }

        field = dataForm.find ('#companyphase');
        value = field.val ();
        selectedType = value;
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            jQuery ('#sp-companyphase').html ('Selecciona la etapa de crecimiento');
            jQuery ('#dv-companyphase').addClass ('has-error');
            error = false;
        }

        return error
    };

    var validateSetpTwo = function (card) {
      return true;
    };

    //Public method
    var filterByArea = function (obj) {
        var area = jQuery(obj).val (),
            tbody       = jQuery('#objectives-table > tr');

        if (area !== '') {
            jQuery('tr[id ^= row-objetive-' + area + '-]').removeClass ('hide');
            tbody.each(function (i, elemnet) {
                var tr = jQuery (elemnet),
                    trId = tr.attr('id').split('-');
                if (trId[ 2 ] !== area) {
                    tr.addClass('hide')
                }
            })
        } else {
            tbody.each(function (i, elemnet) {
                jQuery (elemnet).removeClass ('hide');
            })
        }
    };

    var filterByObjective = function (obj) {
        var objectiveId = jQuery(obj).val (),
            tbody       = jQuery('#key-results-table > tr');

        if (objectiveId !== '') {
            jQuery('tr[id ^= row-key-' + objectiveId + '-]').removeClass ('hide');
            tbody.each(function (i, elemnet) {
                var tr = jQuery (elemnet),
                    trId = tr.attr('id').split('-');
                if (trId[ 2 ] !== objectiveId) {
                    tr.addClass('hide')
                }
            })
        } else {
            tbody.each(function (i, elemnet) {
                jQuery (elemnet).removeClass ('hide');
            })
        }
    };

    var keyResultData = function (data) {
        if (data !== '') {
            dataKeyResults = data;
            console.log(data)
        }
    };

    var objectiveData = function (data) {
        if (data !== '') {
            dataObjective = data;
        }
    };
    var openModalWizard = function (notificationId) {
        var template = jQuery ('#okrs-wizard-template');
        wizard = jQuery (template.html ()).wizard ({
            backdrop:   'static',
            showCancel: true,
            buttons:    {
                cancelText:     'Cancelar',
                nextText:       'Siguiente →',
                backText:       '← Atrás',
                submitText:     'Guardar',
                submittingText: 'Guardando...'
            },
            baseHeight: 0
        });

        if (notificationId) {
           /* wizard.cards [ 'start' ].el.closest ('form').find ('input[name="record"]').val (notificationId);
            wizard.cards [ 'start' ].el.find ('#new-notify-options').hide ().find ('input[name="wizardaction"]').prop ('disabled', true);
            wizard.cards [ 'start' ].el.find ('#existing-notify-options').show ().find ('input[name="wizardaction"]').prop ('disabled', false).first ().prop ('checked', true);
        */
        } else {
            /*
            wizard.cards [ 'start' ].el.closest ('form').find ('input[name="record"]').val ('');
            wizard.cards [ 'start' ].el.find ('#existing-notify-options').hide ().find ('input[name="wizardaction"]').prop ('disabled', true);
            wizard.cards [ 'start' ].el.find ('#new-notify-options').show ().find ('input[name="wizardaction"]').prop ('disabled', false).first ().prop ('checked', true);
            wizard.cards [ 'start' ].on ('validate', validateStart)
        */
        }

        wizard.cards [ 'setp-1' ].on ('validate', validateSetpOne);
            //.on ('selected', setExistingNotifyData);
         wizard.cards [ 'setp-2' ].on ('validate', validateSetpTwo)
             .on ('selected', setOkrs);
        /* wizard.cards [ 'setp-3' ].on ('validate', validateSetpThree)
             .on ('selected', setWindowsSize);*/
        wizard.on ('submit', submitWizard)
            .on ('closed', destroyWizard)
            .on ('incrementCard', updateProgressBar)
            .on ('decrementCard', updateProgressBar);
        wizard.show ();
        jQuery ('.wizard-modal .wizard-nav-item > .wizard-nav-link').on ('click', function (evt) {
            var link  = jQuery (this),
                links = link.closest ('.nav-list').find ('.wizard-nav-item'),
                i, requestedCardIndex, activeCardIndex;
            if ((!link.closest ('.wizard-nav-item').hasClass ('already-visited')) || (links.length === 0)) {
                return;
            }

            requestedCardIndex = null;
            for (i = 0; i < links.length; i += 1) {
                if (link.text () === jQuery (links [ i ]).text ()) {
                    requestedCardIndex = i;
                    break;
                }
            }

            evt.preventDefault ();
            evt.stopPropagation ();
            if (!requestedCardIndex) {
                return;
            }

            activeCardIndex = wizard.getActiveCard ().index;
            if (activeCardIndex > requestedCardIndex) {
                for (i = activeCardIndex; i > requestedCardIndex; i -= 1) {
                    wizard.decrementCard ();
                }
            } else if (activeCardIndex < requestedCardIndex) {
                for (i = activeCardIndex; i < requestedCardIndex; i += 1) {
                    wizard.incrementCard ();
                }
            }
        });
    };

    var selectedArea = function (obj, id) {
        var area       = jQuery(obj).val(),
            objectives = jQuery('#objectivesid-' + id),
            objOptions = jQuery ('option', objectives);

        objectives.val('');
        objOptions.each(function (i, element) {
            var optGroup = jQuery (jQuery (element).parent()[0]),
                value    = jQuery(element).val ();

            if (optGroup.attr('id') === area) {
                if (optGroup.hasClass('hide')) {
                    jQuery(element).parent().removeClass('hide')
                }
            } else if ((!optGroup.hasClass ('hide')) && (value !== '')) {
                jQuery(element).parent().addClass('hide')
            }
        })
    };

    var selectedKeyResult = function (obj, id) {
        var keyResultId = jQuery(obj).val(),
            description = jQuery ('#description-' + id),
            goalValue   = jQuery ('#goal-' + id),
            pager       = jQuery ('#pager-' + id);
        if (keyResultId !== null) {
            console.log(keyResultId.length);
            console.log(keyResultId);
            description.html(dataKeyResults[ keyResultId[0] ].description);
            goalValue.val(dataKeyResults[ keyResultId[0] ].value);
            if (keyResultId.length > 1) {
                pager.removeClass('hide')
            }
        } else {
            pager.addClass('hide');
            description.html('');
            goalValue.val('');
        }


    };
    var selectedObjective = function (obj, id) {
        var objectiveId = jQuery (obj).val (),
            dataRow     = jQuery ('#objective-data-' + id),
            howToDo     = jQuery ('#howtodo-' + id),
            frequecy    = jQuery ('#frequency-' + id),
            objectives  = jQuery('option:selected',obj),
            fieldSet    = jQuery (obj).closest ('fieldset'),
            keyResults  = fieldSet.find ('#keyresultsid-' + id),
            objOptions = [];

        if (objectiveId !== '') {
            howToDo.html(dataObjective[objectiveId].how_to_do);
            frequecy.val(dataObjective[objectiveId].frequency);
            dataRow.removeClass('hide')
        } else {
            howToDo.html('');
            frequecy.val('');
            dataRow.addClass('hide')
        }

        keyResults.find('option').each(function (i, element) {
            var keyResult    = jQuery(element),
                keyObjective = jQuery(element).attr('data-objective');
            keyResult.attr("selected",false);
            if (keyObjective === objectiveId) {
                keyResult.removeClass('hide')
            } else {
                keyResult.addClass('hide');
            }
        });

    };

    var setWizardAction = function (obj) {
        var radio          = jQuery (obj),
            action         = radio.val (),
            patternSection = radio.closest ('.wizard-input-section').find ('#notify-pattern');

        if (radio.attr ('id') === 'wizard-action-duplicate-from-pattern') {
            patternSection.find ('#notify-type').prop ('disabled', false);
            patternSection.find ('#notify-pattern-id').prop ('disabled', false);
            patternSection.show ();
        } else {
            patternSection.hide ();
            patternSection.find ('#notify-type').val ('').prop ('disabled', true);
            patternSection.find ('#notify-pattern-id').val ('').prop ('disabled', true);
        }
    };

    window.OKRsUtils = {
        filterByArea:      filterByArea,
        filterByObjective: filterByObjective,
        keyResultData:     keyResultData,
        objectiveData:     objectiveData,
        openModalWizard:   openModalWizard,
        selectedArea:      selectedArea,
        selectedKeyResult: selectedKeyResult,
        selectedObjective: selectedObjective,
        setWizardAction:   setWizardAction
    };

    jQuery (document).ready (function () {

    });
} (jQuery));