(function (jQuery) {
    // Private variables
    var questionSequence = 0,
        lookingFeedBack  = false,
        lastVisited      = 0,
        lastVisitedSeq   = 0;

// Private functions
    var activeTabForm = function (idQuestion, step, from) {
        var mainDivs = jQuery ('.main');
        mainDivs.each(function (index, div) {
            var mainDiv = jQuery(div);
            mainDiv.removeClass('active')
        });
        if (idQuestion !== '' && from === 'next') {
            jQuery('#main-' + idQuestion).addClass('active').attr('relate-step', step)
        }else if (idQuestion !== '' && from !== 'next') {
            jQuery ('#main-' + idQuestion).addClass('active')
        } else {
            jQuery(mainDivs[step]).addClass('active')

        }
    };

    var activeTabHelp = function () {
        var helpTabText  = jQuery('.help-tab-text'),
            helpTabVideo = jQuery('.help-tab-video'),
            mainDivs     = jQuery ('.main'),
            helpText, helpVideo,groupTitle,
            myTab        = 0;
        mainDivs.each(function (index, div) {
            var mainDiv = jQuery(div), dummy;
            if (mainDiv.hasClass('active')) {
                dummy = mainDiv.attr('id').split('-');
                myTab = dummy[1];
            }

        });
        helpText     = jQuery('#survey-feedback-' + myTab);
        helpVideo    = jQuery ('#survey-feedback-v-' + myTab);
        groupTitle   = jQuery ('#survey-feedback-g-' + myTab);
        helpTabText.each(function (index, div) {
            var myhelpDiv = jQuery(div);
            if (!myhelpDiv.hasClass('hide')) {
                myhelpDiv.addClass('hide');
            }
        });
        helpTabVideo.each(function (index, div) {
            var myhelpDiv = jQuery(div);
            if (!myhelpDiv.hasClass('hide')) {
                myhelpDiv.addClass('hide');
            }
        });
        helpText.removeClass('hide');
        helpVideo.removeClass('hide');
        groupTitle.removeClass('hide');
    };

    var progress = function () {
        var progressBar = jQuery ('.steps li'),
            mainDivs     = jQuery ('.main'),
            myTab        = 0;
        mainDivs.each(function (index, div) {
            var mainDiv = jQuery(div);
            if (mainDiv.hasClass('active')) {
                myTab = index;
            }
        });
        progressBar.each(function (index, theLi) {
            var bar = jQuery(theLi);
            if (index <= myTab) {
                bar.addClass('li-active');
                console.log('listo pase');
            } else {
                bar.removeClass('li-active');
            }
        });
    };

    var validateForm = function () {
        var validate          = true,
            isCheck           = false,
            hasCheckBox       = false,
            activeTab         = jQuery (".main.active"),
            validateInputs    = jQuery (".main.active input"),
            validatetextareas = jQuery (".main.active textarea"),
            validateSelect    = jQuery ('.main.active select');

        validateInputs.map (function(index, elm) {
            var element = jQuery (elm),
                type    = elm.type,
                name    = elm.name,
                value   = element.val ();
            element.removeClass('warning');
            if (element.hasClass('required')) {
                if (type === 'radio') {
                    element.parent().find ('label').removeClass('warning');
                    element.closest ("tr").find ('.chekbox-label').removeClass('warning');
					element.closest ("span").find ('.chekbox-label').removeClass('warning');
                   if(!jQuery ("input[name='" + name + "']").is(':checked')) {
                       element.closest("tr").find ('.chekbox-label').addClass('warning');
					   element.parent().find ('span').addClass ('warning');
                       element.parent().find ('label').addClass ('warning');
                       validate = false;
                   }
                } else if (type === 'checkbox') {
                    hasCheckBox = true;

                    if(jQuery ("input[name='" + name + "']").is(':checked')) {
                        isCheck = true;
                    }
                } else if ((value === null) || (value === undefined) || (value.trim() === '')) {
                    element.addClass('warning');
                    validate = false;
                }
            }
        });
        validateSelect.map (function(index, elm) {
            var element = jQuery (elm),
                value   = element.val ();
            element.removeClass('warning');
            if (element.hasClass('required')) {
                if ((value === null) || (value === undefined) || (value.trim() === '')) {
                    element.addClass('warning');
                    validate = false;
                }
            }
        });
        validatetextareas.map (function(index, elm) {
            var element = jQuery (elm),
                value   = element.val ();
            element.removeClass('warning');
            if (element.hasClass('required')) {
                if ((value === null) || (value === undefined) || (value.trim() === '')) {
                    element.addClass('warning');
                    validate = false;
                }
            }
        });
        if (hasCheckBox) {
            activeTab.find('.check-survey-requiered').addClass('warning');
            validate = false;
            if (isCheck) {
                activeTab.find('.check-survey-requiered').removeClass('warning');
                validate = true;
            }
        }
        return validate;
    };

    //public method
    var checkEmail = function (obj) {
        var email = jQuery(obj),
            emailSurvey = jQuery ('#platzilla_email_survey'),
            errorMessage = jQuery ('#email-error-message'),
            arguments = {
                'module':   'store',
                'flmodule': 'store',
                'action':   'ajaxSurveyUtils',
                'function': 'CHECK-EMAIL',
                'email':     email.val(),
                'Ajax':     'true'
            },
            re    = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        if (email.val() === null || email.val() === undefined || email.val().trim() === '') {
            email.val('');
            errorMessage.html ('Por favor ingrese su correo electrónico');
            return false;
        } else if (!re.test(email.val())) {
            email.val('');
            errorMessage.html ('Por favor ingrese un correo electrónico válido');
            return false;
        } else {
            jQuery.ajax({
              type: "POST",
              url: "index.php",
              data: arguments,
              async: false,
              success: function(data) {
                  try {
                      var message = JSON.parse(JSON.stringify(data));
                      if (message.error !== 'OK') {
                          alert ( 'Este email ' + email.val() + ' ya estás registrado en Platzilla!');
                          email.val('');
                      } else {
                          errorMessage.html ('');
                          emailSurvey.val (email.val());
                      }
                  } catch (e) {
                      alert(e);
                  }
              },
              error: function() {
                  alert('Error al validar el correo electrónico');
              }
            });
        }
    }

    var nextStep = function (obj, step, idQuestion) {
        var myStep       = parseInt(step),
            askingForClass = jQuery ('.asking-for-' + idQuestion),
            divData      = jQuery ('#response-' + idQuestion),
            divQuestions = jQuery ('#response-question-' + idQuestion),
            dataNav      = divData.find ('.data-question'),
            hasFeedBack  = dataNav.attr ('data-feed-back'),
            stepObjetive = dataNav.attr ('data-next-step'),
            helpMs       = divData.find('.help-block');
        if (!validateForm()) {
            return false;
        }

        lastVisited = idQuestion;
        if (hasFeedBack === 'yes' && !lookingFeedBack) {
            divQuestions.addClass('hide');
            askingForClass.addClass('hide');
            divData.removeClass('hide');
            lookingFeedBack = true;
            helpMs.html('Clic nuevamente en próximo para continuar')
        } else if(hasFeedBack === undefined || stepObjetive === '') {
            activeTabForm ('', myStep, 'seq');
            progress ();
            lookingFeedBack = false;
            divData.addClass('hide');
            divQuestions.removeClass('hide');
            askingForClass.removeClass('hide');
            helpMs.html('');
            activeTabHelp();
        } else {
            activeTabForm (stepObjetive, idQuestion, 'next');
            progress ();
            lookingFeedBack = false;
            divData.addClass('hide');
            divQuestions.removeClass('hide');
            helpMs.html('');
            activeTabHelp();
        }
    };

    var startSurvey = function () {
        var presentation = jQuery ('#presentation-card'),
            surveyCard   = jQuery ('#questionnaire-car');

        presentation.addClass('hide');
        surveyCard.removeClass('hide');
    };

    var prevStep = function (obj, step, idQuestion) {
        var myStep        = parseInt(step),
            myRelatedStep = (lastVisited) ? lastVisited : jQuery ('#main-' + idQuestion).attr('relate-step');

        lastVisited = 0;
        activeTabForm (myRelatedStep, myStep, 'prev');
        progress ();
        activeTabHelp ();
    };

    var sendSurvey = function (obj, step, id) {
        if (!validateForm()) {
            return false;
        }
        var myStep = parseInt(step),
            btn       = jQuery (obj),
            feddBack  = jQuery ('#survey-save-response'),
            loading   = jQuery ('#survey-save-response-loading'),
            steps     = jQuery ('.steps'),
            userName  = jQuery ('#user_name').val(),
            shownName = jQuery ('#shown_name'),
            arguments = jQuery ('#survey-' + id).serialize();

        btn.attr('disabled','disabled');
        steps.addClass('d-none');
        activeTabForm (myStep, 0, 'seq');
        shownName.html(userName);
        loading.removeClass('hide');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    steps.addClass('d-none');
                    activeTabForm (myStep, 0, 'seq');
                    if (message.url !== '') {
                        location.href = message.url
                    } else {
                        feddBack.html(message.html)
                    }

                }
            }
            catch (e) {
                alert(e);
            }
        });
    };

    window.SurveyUtils = {
        checkEmail:  checkEmail,
        nextStep:    nextStep,
        startSurvey: startSurvey,
        prevStep:    prevStep,
        sendSurvey:  sendSurvey

    };
} (jQuery));