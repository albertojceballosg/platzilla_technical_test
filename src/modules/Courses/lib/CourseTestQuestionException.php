<?php

	class CourseTestQuestionException extends Exception {
		const ERROR_COURSE_TEST_QUESTION_EMPTY_ANSWERS         = 'No se han suministrado las respuestas a la pregunta';
		const ERROR_COURSE_TEST_QUESTION_EMPTY_CORRECT_ANSWERS = 'No se ha suministrado ninguna respuesta correcta a la pregunta';
		const ERROR_COURSE_TEST_QUESTION_EMPTY_STATEMENT       = 'No se ha suministrado la pregunta';
		const ERROR_COURSE_TEST_QUESTION_EMPTY_TEST_ID         = 'No se ha suministrado el examen al cual está asociado la pregunta';
		const ERROR_COURSE_TEST_QUESTION_EMPTY_TYPE            = 'No se ha suministrado el tipo de pregunta';
		const ERROR_COURSE_TEST_QUESTION_INVALID_ANSWER        = 'La respuesta suministrada no es válida';

	}
