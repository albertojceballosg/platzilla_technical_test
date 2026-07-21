<?php

	class CourseTestException extends Exception {
		const ERROR_COURSE_TEST_EMPTY_DESCRIPTION                = 'No se ha suministrado la descripción del examen';
		const ERROR_COURSE_TEST_EMPTY_LESSON_ID                  = 'No se ha suministrado la lección a la cual pertenece el examen';
		const ERROR_COURSE_TEST_EMPTY_MINIMUM_SCORE              = 'No se ha suministrado el porcentaje de aprobación del examen';
		const ERROR_COURSE_TEST_EMPTY_QUESTIONS                  = 'No se han suministrado las preguntas del examen';
		const ERROR_COURSE_TEST_EMPTY_TOTAL_QUESTIONS_PER_TEST   = 'No se ha suministrado la cantidad de preguntas por examen';
		const ERROR_COURSE_TEST_INVALID_QUESTION                 = 'La pregunta suministrada no es válida';
		const ERROR_COURSE_TEST_INVALID_TOTAL_QUESTIONS_PER_TEST = 'La cantidad de preguntas por examen no puede ser mayor a la cantidad de preguntas disponibles';

	}
