(function (jQuery) {
	var VIMEO_BASE_URL              = 'https://vimeo.com/api/oembed.json',
		YOUTUBE_BASE_URL            = 'https://www.youtube.com/embed/',
		QUESTION_TYPE_SINGLE_CHOICE = 'SINGLE CHOICE',
		RESOURCE_TYPE_ATTACHMENT    = 'ATTACHMENT',
		RESOURCE_TYPE_URL           = 'URL',
		HAS_TEST                    = [],
		players                     = {},
        checkFeeInstance            = [],
		checkFeeNAInstance          = [],
        checkDesInstance            = [],
		checkExerciseInstance 	    = [],
        checkCourseInstance;

	var loadFileData = function (file, resource) {
		var reader = new FileReader ();
		reader.onload = function (evt) {
			resource.find ('.resource-data').val (evt.target.result);
		};
		reader.readAsDataURL (file);
	};

	var validateBasicProperties = function (form) {
		var field, value, fieldIndex = form.find ('#course-lesson-index');

		field = form.find ('#course-name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el nombre del curso');
			field.focus ();
			return false;
		}

		field = form.find ('#course-category');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona la categoría del curso');
			field.focus ();
			return false;
		}

		field = form.find ('#course-level');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el nivel del curso');
			field.focus ();
			return false;
		}

		field = form.find ('#course-target-audience');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona la audiencia del curso');
			field.focus ();
			return false;
		}

		field = form.find('#course-description');
		value = CourseUtils.checkCourseInstance.getData();
		//jQuery ('#cke_course-description').find ('iframe').contents ().find ('body').text ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce la descripción del curso');
			return false;
        } else if (value.trim ().length < 60) {
            alert ('La descripción del curso, parece estar vacía o es muy corta! introduce al menos 70 carácteres!');
        } else {
            field.val(value)
        }

		field = form.find ('#course-price');
		value = field.val ();
        if (((value === null) || (value === undefined) || (value.trim () === '') || (parseInt(value) <= 0)) && fieldIndex.val () !== '0') {
			alert ('Introduce el precio del curso');
			field.focus ();
			return false;
		} else if ((parseInt (value) > 0) && (fieldIndex.val () === '0')) {
            alert ('Seleccione pagar desde lección..');
            fieldIndex.focus ();
            return false;
		}

		field = form.find ('#course-status');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el status del curso');
			field.focus ();
			return false;
		}

        field = form.find ("input[name='imageType']");
        value = field.val ();
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            alert ('Selecciona una foto');
            field.focus ();
            return false;
        }

        field = form.find ('#video-course-type');
        value = field.val ();
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            alert ('Selecciona el tipo de video');
            field.focus ();
            return false;
        }

        field = form.find ('#video-course-url');
        value = field.val ();
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            alert ('Introduce la url del video');
            field.focus ();
            return false;
        }
        return true;
	};

	var validateAnswers = function (question, lessonsSection, lessonSelector) {
		var answers = question.find ('.answer'),
			answer, field, value, i, hasCorrectAnswers;

		if (answers.length === 0) {
			lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
			alert ('Agrega al menos una respuesta');
			return false;
		}

		hasCorrectAnswers = false;
		for (i = 0; i < answers.length; i += 1) {
			answer = jQuery (answers [ i ]);

			field = answer.find ('.answer-statement');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
				alert ('Introduce el planteamiento de la respuesta');
				field.focus ();
				return false;
			}

			field = answer.find ('.answer-correct');
			if (field.prop ('checked')) {
				hasCorrectAnswers = true;
			}
		}

		if (!hasCorrectAnswers) {
			lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
			alert ('Selecciona al menos una respuesta correcta');
			question.find ('.answer-statement').focus ();
			return false;
		}

		return true;
	};

	var validateQuestions = function (lesson) {
		var questions      = lesson.find ('.question'),
			lessonsSection = lesson.closest ('.lessons'),
			lessonSelector = '#' + lesson.closest ('.tab-pane').attr ('id').trim (),
			question, field, value, i;

		if (questions.length === 0) {
			lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
			alert ('Agrega al menos una pregunta');
			return false;
		}

		for (i = 0; i < questions.length; i += 1) {
			question = jQuery (questions [ i ]);

			field = question.find ('.question-type');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
				alert ('Selecciona el tipo de pregunta');
				field.focus ();
				return false;
			}

			field = question.find ('.question-statement');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
				alert ('Introduce el planteamiento de la pregunta');
				field.focus ();
				return false;
			}

			if (!validateAnswers (question, lessonsSection, lessonSelector)) {
				return false;
			}
		}

		return true;
	};

	var validateResources = function (lesson) {
		var resources      = lesson.find ('.resource'),
			lessonsSection = lesson.closest ('.lessons'),
			lessonSelector = '#' + lesson.closest ('.tab-pane').attr ('id').trim (),
			resource, resourceId, type, i, field, value;

		for (i = 0; i < resources.length; i += 1) {
			resource = jQuery (resources [ i ]);

			field = lesson.find ('.resource-name');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
				alert ('Introduce el nombre del recurso');
				field.focus ();
				return false;
			}

			field = lesson.find ('.resource-type');
			type = field.val ();
			if ((type === null) || (type === undefined) || (type.trim () === '')) {
				lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
				alert ('Selecciona el tipo de recurso');
				field.focus ();
				return false;
			}

			resourceId = resource.find ('.resource-id').val ();
			if ((type === RESOURCE_TYPE_ATTACHMENT) && ((resourceId === null) || (resourceId === undefined) || (resourceId.trim () === ''))) {
				field = lesson.find ('.resource-data');
				value = field.val ();
				if ((value === null) || (value === undefined) || (value.trim () === '')) {
					lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
					alert ('Agrega el adjunto del recurso');
					return false;
				}
			} else if (type === RESOURCE_TYPE_URL) {
				field = lesson.find ('.resource-url');
				value = field.val ();
				if ((value === null) || (value === undefined) || (value.trim () === '')) {
					lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
					alert ('Introduce el URL del recurso');
					field.focus ();
					return false;
				}
			}
		}
		return true;
	};

	var validateTest = function (lesson, index) {
		var test           = lesson.find ('.test'),
			lessonsSection = lesson.closest ('.lessons'),
			lessonSelector = '#' + lesson.closest ('.tab-pane').attr ('id').trim (),
			idQuestion     = jQuery ('#test-has-questions-' + index).attr ('data-idQuestion'),
			numQuestion    = jQuery ('#questions-' + idQuestion).children ().length,
			field, value;

		if (!HAS_TEST[ index ]) {
			return true;
		}

        field = test.find ('.test-total-questions');
        value = field.val ();
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            lessonsSection.find('.nav-tabs a[href="' + lessonSelector + '"]').tab('show');
            alert('Lessión ( ' + (index + 1) + ' ) Introduce el total de preguntas del examen');
            field.focus();
            return false;
        }else if (parseInt(value !== numQuestion)) {
            field.val (numQuestion.toString())
        }

		field = test.find ('.test-description');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
			alert ('Lessión ( ' + (index + 1)+ ' ) Introduce la descripción del examen');
			field.focus ();
			return false;
		}

		field = test.find ('.test-minimum-score');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
			alert ('Lessión ( ' + (index + 1)+ ' ) Introduce la puntuación mínima para aprobar el examen');
			field.focus ();
			return false;
		}

		return (validateQuestions (lesson));
	};

	var validateLessons = function (form) {
		var lessonsSection = form.find ('.lessons'),
			lessons, lesson, lessonSelector, i, idDummy, field, value, hasTest;

		lessons = form.find ('.lesson');
		if (lessons.length === 0) {
			alert ('Agrega al menos una lección');
			return false;
		}
        HAS_TEST = [];
		for (i = 0; i < lessons.length; i++) {
			lesson = jQuery (lessons [ i ]);
			lessonSelector = '#' + lesson.closest ('.tab-pane').attr ('id').trim ();
			hasTest        = parseInt (jQuery('#test-has-questions-' + i).val());
            HAS_TEST[ i ]  = ((hasTest === 1));
			field = lesson.find ('.lesson-name');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
				alert ('Introduce el nombre de la lección');
				field.focus ();
				return false;
			}

			field   = lesson.find ('.lesson-description');
            idDummy = field.attr('id').split('-');
            try {
                //value = jQuery ('#cke_' + field.attr ('id')).find ('iframe').contents ().find ('body').text ();
                value = CourseUtils.checkDesInstance[ idDummy[2] ].getData();
                if ((value === null) || (value === undefined) || (value.trim () === '')) {
                    lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
                    throw 'Introduce la descripción de la lección';
                } else if (value.trim ().length < 60) {
                    throw 'La descripción de una de las lecciones, parece estar vacía o es muy corta! introduce al menos 70 carácteres!';
                } else {
                    field.val(value)
                }

                field = lesson.find ('.test-feedback');
                value = CourseUtils.checkFeeInstance[ idDummy[2] ].getData();
                //value = field.val ();
                if (((value === null) || (value === undefined) || (value.trim () === '')) && HAS_TEST[ i ]) {
                    lessonsSection.find ('.nav-tabs a[href="' + lessonSelector + '"]').tab ('show');
                    throw 'Introduce el feedback del examen';
                } else if (value.trim ().length < 60 && HAS_TEST[ i ]) {
                    throw 'El Feedback de una de las lecciones, parece estar vacío o es muy corto! introduce al menos 70 carácteres!';
				} else {
                    field.val(value)
				}
            } catch (e) {
				alert(e);
				return false;
            }

			if ((!validateResources (lesson)) || (!validateTest (lesson, i))) {
				return false;
			}
		}
		return true;
	};

	var addAnswer = function (buttonElement) {
		var button             = jQuery (buttonElement),
			answersSection     = button.closest ('.question-answers-group').find ('.answers'),
			lessonId           = button.closest ('.tab-pane').data ('index'),
			questionId         = button.closest ('.question').data ('index'),
			answerTemplateHtml = jQuery ('#answer-template').html (),
			answer, dummy, index;

		dummy = answersSection.find ('.answer:last');
		index = dummy.length > 0 ? parseInt (dummy.data ('index')) + 1 : 0;

		answer = jQuery (answerTemplateHtml);
		answer.attr ('id', 'answer-' + lessonId + '-' + questionId + '-' + index).attr ('data-index', index);
		answer.find ('.answer-id').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][answers][' + index + '][answerid]');
		answer.find ('.answer-statement').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][answers][' + index + '][statement]');
		answer.find ('.answer-correct').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][answers][' + index + '][correct]');
		answersSection.append (answer);
	};

	var addAttachment = function (evt) {
		var targetElement   = evt.target ? evt.target : evt.srcElement,
			files           = targetElement.files,
			field           = jQuery (targetElement),
			dropZone        = field.closest ('.drop-zone'),
			maximumFileSize = field.attr ('data-maximum-file-size') ? field.attr ('data-maximum-file-size') : 10,
			file;

		if ((!files) || (!(files instanceof FileList)) || (files.length === 0)) {
			return;
		}

		file = files [ 0 ];
		if (file.size > (maximumFileSize * 1024 * 1024)) {
			alert ('El archivo suministrado supera el tamaño máximo permitido (' + maximumFileSize + ' MB)');
			return;
		}

		loadFileData (file, dropZone);
		dropZone.find ('.title').attr ('title', file.name + ' (' + (file.size / 1024).toFixed (2) + ' KB)').val (file.name + ' (' + (file.size / 1024).toFixed (2) + ' KB)')
	};

	var addLesson = function (buttonElement) {
		var button                = jQuery (buttonElement),
			lessonsSection        = button.closest ('.main-box').find ('.lessons'),
			lessonTemplateHtml    = jQuery ('#lesson-template').html (),
			lessonTabTemplateHtml = jQuery ('#lesson-tab-template').html (),
			lesson, lessonId, dummy, navTab, group, questions, questionId, answers, answerId, i, j;

		dummy    = lessonsSection.find ('.tab-pane:last');
		lessonId = dummy.length > 0 ? parseInt (dummy.data ('index')) + 1 : 0;
        console.log(dummy)
		console.log(lessonId)
		lesson = jQuery (lessonTemplateHtml);
		lesson.attr ('id', 'lesson-' + lessonId).attr ('data-index', lessonId);

		lesson.find ('.lesson-id').attr ('name', 'lessons[' + lessonId + '][lessonid]');

		lesson.find ('.video').attr ('id', 'video-' + lessonId);

		group = lesson.find ('.lesson-name-group');
		group.find ('label').attr ('for', 'lesson-name-' + lessonId);
		group.find ('.lesson-name').attr ('id', 'lesson-name-' + lessonId).attr ('name', 'lessons[' + lessonId + '][lessonname]');

		group = lesson.find ('.lesson-status-group');
		group.find ('label').attr ('for', 'lesson-status-' + lessonId);
		group.find ('select').attr ('id', 'lesson-status-' + lessonId).attr ('name', 'lessons[' + lessonId + '][lesson_status]');


		group = lesson.find ('.video-url-type-group');
        group.find ('label').attr ('for', 'video-type-' + lessonId);
        group.find ('.lesson-video-type').attr ('id', 'video-type-' + lessonId).attr ('name', 'lessons[' + lessonId + '][videotype]');

		group = lesson.find ('.video-url-group');
		group.find ('label').attr ('for', 'video-url-' + lessonId);
		group.find ('.lesson-video-url').attr ('id', 'video-url-' + lessonId).attr ('name', 'lessons[' + lessonId + '][videourl]');

		group = lesson.find ('.lesson-description-group');
		group.find ('label').attr ('for', 'lesson-description-' + lessonId);
		group.find ('.lesson-description').attr ('id', 'lesson-description-' + lessonId).attr ('name', 'lessons[' + lessonId + '][description]');

		group = lesson.find('.test-has-questions-group');
        group.find ('label').attr ('for', 'test-has-test-' + lessonId);
        group.find ('.lesson-has-test').attr ('id', 'test-has-test-' + lessonId).attr ('name', 'lessons[' + lessonId + '][hastest]');

		group = lesson.find ('.test-description-group');
		group.find ('label').attr ('for', 'test-description-' + lessonId);
		group.find ('.test-description').attr ('id', 'test-description-' + lessonId).attr ('name', 'lessons[' + lessonId + '][test][description]');

		group = lesson.find ('.test-feedback-group');
		group.find ('label').attr ('for', 'test-feedback-' + lessonId);
		group.find ('.test-feedback').attr ('id', 'test-feedback-' + lessonId).attr ('name', 'lessons[' + lessonId + '][test][feedback]');

		group = lesson.find ('.test-feedback_no_approved-group');
		group.find ('label').attr ('for', 'test-feedback_no_approved-' + lessonId);
		group.find ('.test-feedback').attr ('id', 'test-feedback_no_approved-' + lessonId).attr ('name', 'lessons[' + lessonId + '][test][feedback_no_approved]');

		group = lesson.find ('.test-minimum-score-group');
		group.find ('label').attr ('for', 'test-minimum-score-' + lessonId);
		group.find ('.test-minimum-score').attr ('id', 'test-minimum-score-' + lessonId).attr ('name', 'lessons[' + lessonId + '][test][minimumscore]');

		group = lesson.find ('.test-total-questions-group');
		group.find ('label').attr ('for', 'test-total-questions-' + lessonId);
		group.find ('.test-total-questions').attr ('id', 'test-total-questions-' + lessonId).attr ('name', 'lessons[' + lessonId + '][test][totalquestionspertest]');

		questions = lesson.find ('.question');
		for (i = 0; i < questions.length; i += 1) {
			dummy = jQuery (questions [ i ]);
			questionId = dummy.data ('index');

			dummy.find ('.question-id').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][questionid]');

			group = dummy.find ('.question-type-group');
			group.find ('label').attr ('for', 'question-type-' + lessonId + '-' + questionId);
			group.find ('.question-type').attr ('id', 'question-type-' + lessonId + '-' + questionId).attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][questiontype]');

			group = dummy.find ('.question-statement-group');
			group.find ('label').attr ('for', 'question-statement-' + lessonId + '-' + questionId);
			group.find ('.question-statement').attr ('id', 'test-total-questions-' + lessonId).attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][statement]');

			answers = dummy.find ('.answer');
			for (j = 0; j < answers.length; j += 1) {
				dummy = jQuery (answers [ j ]);
				answerId = dummy.data ('index');

				dummy.find ('.answer-id').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][answers][' + answerId + '][answerid]');
				dummy.find ('.answer-statement').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][answers][' + answerId + '][statement]');
				dummy.find ('.answer-correct').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][answers][' + answerId + '][correct]');
				dummy.find ('.answer-feedback').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][answers][' + answerId + '][feedback]');
			}
		}

		navTab = jQuery (lessonTabTemplateHtml);
		navTab.find ('a').attr ('href', '#lesson-' + lessonId).text (lessonId + 1);

		lessonsSection.find ('.nav-tabs').append (navTab).show ();
		lessonsSection.find ('.tab-content').append (lesson).show ();
		lessonsSection.find ('.nav-tabs a:last').tab ('show');
        CourseUtils.checkDesInstance[lessonId] = loadCkEditor ('lesson-description-' + lessonId);
        CourseUtils.checkFeeInstance[lessonId] = loadCkEditor ('test-feedback-' + lessonId);
		CourseUtils.checkFeeNAInstance[lessonId] = loadCkEditor ('test-feedback_no_approved-' + lessonId);
	};

	var addQuestion = function (buttonElement) {
		var button               = jQuery (buttonElement),
			questionsSection     = button.closest ('.test-questions').find ('.questions'),
			lessonId             = button.closest ('.tab-pane').data ('index'),
			questionTemplateHtml = jQuery ('#question-template').html (),
			question, questionId, dummy, group, answers, answerId, i;

		dummy = questionsSection.find ('.question:last');
		questionId = dummy.length > 0 ? parseInt (dummy.data ('index')) + 1 : 0;

		question = jQuery (questionTemplateHtml);
		question.attr ('id', 'question-' + lessonId + '-' + questionId).attr ('data-index', questionId);

		question.find ('.question-id').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][questionid]');

		group = question.find ('.question-type-group');
		group.find ('label').attr ('for', 'question-type-' + lessonId + '-' + questionId);
		group.find ('.question-type').attr ('id', 'question-type-' + lessonId + '-' + questionId).attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][questiontype]');

		group = question.find ('.question-statement-group');
		group.find ('label').attr ('for', 'question-statement-' + lessonId + '-' + questionId);
		group.find ('.question-statement').attr ('id', 'question-statement-' + lessonId + '-' + questionId).attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][statement]');

		answers = question.find ('.answer');
		for (i = 0; i < answers.length; i += 1) {
			dummy = jQuery (answers [ i ]);
			answerId = dummy.data ('index');
			dummy.find ('.answer-id').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][answers][' + answerId + '][answerid]');
			dummy.find ('.answer-statement').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][answers][' + answerId + '][statement]');
			dummy.find ('.answer-correct').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][answers][' + answerId + '][correct]');
			dummy.find ('.answer-feedback').attr ('name', 'lessons[' + lessonId + '][test][questions][' + questionId + '][answers][' + answerId + '][feedback]');
		}

		questionsSection.append (question);
	};

	var addResource = function (buttonElement, hasExercises) {
		var button           = jQuery (buttonElement),
			resourcesSection = button.closest ('.resources-group').find ('.resources'),
			resourceTemplate = jQuery ('#resource-template'),
			lessonId         = button.closest ('.tab-pane').data ('index'),
			resource, dummy, index;

		dummy = resourcesSection.find ('.resource:last');
		index = dummy.length > 0 ? parseInt (dummy.data ('index')) + 1 : 0;

		resource = jQuery (resourceTemplate.html ());
		resource.attr ('data-index', index);
		resource.find ('.resource-id').attr ('name', 'lessons[' + lessonId + '][resources][' + index + '][resourceid]');
		resource.find ('.resource-name').attr ('name', 'lessons[' + lessonId + '][resources][' + index + '][resourcename]');
		resource.find ('.resource-type').attr ('name', 'lessons[' + lessonId + '][resources][' + index + '][resourcetype]');
		resource.find ('.resource-url').attr ('name', 'lessons[' + lessonId + '][resources][' + index + '][url]');
		resource.find ('.resource-data').attr ('name', 'lessons[' + lessonId + '][resources][' + index + '][filedata]');

		resource.find ('.exercise-id').attr ('name', 'lessons[' + lessonId + '][resources][' + index + '][exerciseid]');
		resource.find ('.has_exercise-id').attr ('name', 'lessons[' + lessonId + '][resources][' + index + '][has_exercise]');

		resource.find ('.has_exercise-id').attr ('value', hasExercises);
		resourcesSection.append (resource);
	};

	var deleteAnswer = function (buttonElement) {
		var button = jQuery (buttonElement),
			answer = button.closest ('.answer');

		if (!confirm ('Esta acción eliminará la respuesta seleccionado. ¿Estás seguro?')) {
			return;
		}

		answer.remove ();
	};

	var deleteLesson = function (buttonElement) {
		var button         = jQuery (buttonElement),
			lessonsSection = button.closest ('.lessons'),
			tabSelector    = button.siblings ('a').attr ('href'),
			lessonId       = tabSelector.replace ('#lesson-', ''),
			videoId        = 'video-' + lessonId;

		if (!confirm ('Esta acción eliminará la lección seleccionada. ¿Estás seguro?')) {
			return;
		}

		if (players.hasOwnProperty (videoId)) {
			players [ videoId ].destroy ();
		}

		button.closest ('li').remove ();
		lessonsSection.find (tabSelector).remove ();
		lessonsSection.find ('.nav-tabs a:first').tab ('show');
	};

	var deleteQuestion = function (buttonElement) {
		var button   = jQuery (buttonElement),
			question = button.closest ('.question');

		if (!confirm ('Esta acción eliminará la pregunta seleccionada. ¿Estás seguro?')) {
			return;
		}

		question.remove ();
	};

	var deleteResource = function (buttonElement) {
		var button   = jQuery (buttonElement),
			resource = button.closest ('.resource');

		if (!confirm ('Esta acción eliminará el recurso seleccionado. ¿Estás seguro?')) {
			return;
		}

		resource.remove ();
	};

	var hasTest = function (obj, id, index) {
		var hasTest         = parseInt (jQuery(obj).val ()),
			questions       = jQuery ('#questions-' + id),
			btnAddQuestion  = jQuery ('#addquestion-' + id),
			testDescription = jQuery ('#test-description-' + index),
			feedBack        = CourseUtils.checkFeeInstance[ index ],
			feedBackNA      = CourseUtils.checkFeeNAInstance[ index ],
			score           = jQuery ('#test-minimum-score-' + index),
			totalTest       = jQuery ('#test-total-questions-' + index);

		if (hasTest === 0) {
            if (!confirm ('Esta acción eliminará todas las preguntas. ¿Estás seguro?')) {
                return;
            }
			questions.children ().each (function () {
                jQuery(this).remove();
            });
			btnAddQuestion.attr ('disabled', true);
			btnAddQuestion.removeClass ('btn-default').addClass('btn-danger');
            testDescription.val ('').attr ('readonly', true);
            feedBack.setData('');
			feedBackNA.setData('');
            score.val ('').attr ('readonly', true);
            totalTest.val ('').attr ('readonly', true);
            //HAS_TEST[index] = false;
		} else {
            btnAddQuestion.attr ('disabled', false);
            btnAddQuestion.removeClass ('btn-danger').addClass ('btn-default');
            testDescription.attr ('readonly', false);
            score.attr ('readonly', false);
            totalTest.attr ('readonly', false);
            //HAS_TEST[index] = true;
		}

    };

	var loadCkEditor = function (inputId) {
		var options = {
			contentsCss:   [ 'themes/centaurus/css/bootstrap/bootstrap.min.css' ],
			entities:      false,
			language:      'es',
			removePlugins: 'elementspath',
			toolbar:       [
				[ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript' ],
				[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ],
				[ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
				[ 'Link', 'Unlink', 'Anchor', '-', 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat', '-', 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'TextColor', 'BGColor' ],
				'/',
				[ 'Styles', 'Format', 'Font', 'FontSize', '-', 'EmailTemplateVariables', '-', 'Source' ]
			]
		};
		return CKEDITOR.replace (inputId, options);
	};

	var setAnswerCorrect = function (checkboxElement) {
		var checkbox     = jQuery (checkboxElement),
			questionType = checkbox.closest ('.question').find ('.question-type').val (),
			isChecked    = checkbox.prop ('checked'),
			answers, i;

		if ((!isChecked) || (questionType !== QUESTION_TYPE_SINGLE_CHOICE)) {
			return;
		}
		/*
		answers = checkbox.closest ('.answers').find ('.answer-correct');
		for (i = 0; i < answers.length; i += 1) {
			jQuery (answers [ i ]).prop ('checked', false);
		} */
		checkbox.prop ('checked', true);
	};

	var setQuestionType = function (selectElement) {
		var select         = jQuery (selectElement),
			answerCorrects = select.closest ('.question').find ('.answer-correct'),
			i;

		for (i = 0; i < answerCorrects.length; i += 1) {
			jQuery (answerCorrects [ i ]).prop ('checked', false);
		}
	};

	var setResourceType = function (selectElement) {
		var select   = jQuery (selectElement),
			resource = select.closest ('.resource'),
			type     = select.val (),
			dropZone;

		dropZone = resource.find ('.drop-zone');
		if (type === RESOURCE_TYPE_URL) {
			dropZone.find ('.resource-data').val ('');
			dropZone.find ('input[type="file"]').val ('');
			dropZone.prop ('disabled', true).hide ();
			resource.find ('.resource-url').prop ('disabled', false).show ();
		} else if (type === RESOURCE_TYPE_ATTACHMENT) {
			resource.find ('.resource-url').prop ('disabled', true).hide ();
			dropZone.prop ('disabled', false).show ();
		} else {
			dropZone.find ('.resource-data').val ('');
			dropZone.find ('input[type="file"]').val ('');
			dropZone.prop ('disabled', true).hide ();
			resource.find ('.resource-url').prop ('disabled', true).hide ();
		}
	};

	var selectVideo = function (obj) {
		var selected  = jQuery (obj),
			videoType = selected.val (),
            container = selected.closest ('.lesson'),
            videoUrl  = container.find ('input').eq(2),
			vimeo     = container.find ('.video'),
            videoId   = vimeo.attr('id'),
			youTube   = container.find ('.youtube-video');
		videoUrl.removeAttr('disabled');
        videoUrl.val ('');
		if(videoType === 'VIMEO') {
            vimeo.parent().removeClass('hide');
            youTube.parent().addClass('hide');
            youTube.attr ('src', '');
		} else if (videoType === 'YOUTUBE') {
            vimeo.parent().addClass('hide');
            if (players.hasOwnProperty(videoId)) {
                players [videoId].destroy();
            }
            youTube.parent().removeClass('hide');
		} else {
            videoUrl.attr('disabled',true);
            vimeo.parent().removeClass('hide');
            youTube.parent().addClass('hide');
		}
	};

	var showVideo = function (inputElement) {
		var input          = jQuery (inputElement),
			videoUrl       = input.val (),
			container      = input.closest ('.lesson'),
			videoContainer = container.find ('.video'),
			videoId        = videoContainer.attr ('id'),
			videoType      = container.find('select').eq(0).val(),
            youTube        = container.find ('.youtube-video');

		if (videoType === 'VIMEO') {
            if ((videoUrl === null) || (videoUrl === undefined) || (videoUrl.trim() === '')) {
                if (players.hasOwnProperty(videoId)) {
                    players [videoId].destroy();
                }
                return;
            } else if (videoUrl.indexOf('vimeo') === -1) {
                alert('La url incluida no parece una url VIMEO');
                return
            }
			jQuery.ajax (VIMEO_BASE_URL, {
				data:     'url=' + videoUrl,
				dataType: 'json',
				method:   'GET'
			}).done (function (data) {
				if ((data !== null) && (data.hasOwnProperty ('video_id')) && (data [ 'video_id' ] > 0)) {
					players [ videoId ] = new Vimeo.Player (videoId, {
						url: videoUrl
					});
				} else if (players.hasOwnProperty (videoId)) {
					alert ('El URL suministrado no corresponde a un video existente en VIMEO');
					players [ videoId ].destroy ();
				}
			}).fail (function () {
				if (players.hasOwnProperty (videoId)) {
					alert ('El URL suministrado no corresponde a un video existente en VIMEO');
					players [ videoId ].destroy ();
				}
			});
        } else if (videoType === 'YOUTUBE') {
            if ((videoUrl === null) || (videoUrl === undefined) || (videoUrl.trim() === '')) {
            	return
            } else if ((videoUrl.indexOf('youtube') === -1) && (videoUrl.indexOf('youtu.be') === -1)) {
            	alert('La url incluida no parece una url Youtube');
				return
			}
			var dummy = videoUrl.split('/');
            youTube.attr ('src', YOUTUBE_BASE_URL + dummy[ (dummy.length - 1)]);
        }
	};

    var showPreview = function (objFileInput, id) {
        var idImage = '#course-photo-' + id,
			fileId  = '#photo-' + id;

        if (objFileInput.files[0]) {
            var fileReader = new FileReader();
            fileReader.onload = function (e) {
                jQuery (idImage).attr ('src',e.target.result);
                jQuery (fileId).val (objFileInput.files[0].type)
            };
            fileReader.readAsDataURL(objFileInput.files[0]);
        }
    };

	var validateCourse = function (formElement) {
		var form = jQuery (formElement);

		return (validateBasicProperties (form)) && (validateLessons (form));
	};

	var validatePhotoSize = function (element, uploadSize) {
        if (jQuery(element).val() === '') {
            return true;
        }
        var fileSize = element.files[ 0 ].size;
        if (fileSize > uploadSize) {
            alert ('El tamaño del Archivo no debe ser superior a ' + uploadSize / (1024 * 1024) + 'MB');
            element.value = '';
        }
    };

	window.CourseUtils = {
		addAnswer:           addAnswer,
		addAttachment:       addAttachment,
		addLesson:           addLesson,
		addQuestion:         addQuestion,
		addResource:         addResource,
		deleteAnswer:        deleteAnswer,
		deleteLesson:        deleteLesson,
		deleteQuestion:      deleteQuestion,
		deleteResource:      deleteResource,
        hasTest:             hasTest,
		loadCkEditor:        loadCkEditor,
		setAnswerCorrect:    setAnswerCorrect,
		setQuestionType:     setQuestionType,
		setResourceType:     setResourceType,
        selectVideo:         selectVideo,
		showVideo:           showVideo,
        showPreview:         showPreview,
		validateCourse:      validateCourse,
        validatePhotoSize:   validatePhotoSize,
        checkCourseInstance: checkCourseInstance,
        checkFeeInstance:    checkFeeInstance,
        checkDesInstance:    checkDesInstance,
		checkExerciseInstance: checkExerciseInstance,
		checkFeeNAInstance:  checkFeeNAInstance

	};
} (jQuery));