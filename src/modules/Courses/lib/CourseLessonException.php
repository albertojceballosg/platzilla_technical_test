<?php

	class CourseLessonException extends Exception {
		const ERROR_COURSE_LESSON_DUPLICATE_NAME    = 'Ya existe una lección con ese nombre';
		const ERROR_COURSE_LESSON_EMPTY_COURSE_ID   = 'No se ha suministrado el curso al cual está asociada la lección';
		const ERROR_COURSE_LESSON_EMPTY_DESCRIPTION = 'No se ha suministrado la descripción de la lección';
		const ERROR_COURSE_LESSON_EMPTY_NAME        = 'No se ha suministrado el nombre de la lección';
		const ERROR_COURSE_LESSON_EMPTY_TEST        = 'No se ha suministrado el examen de la lección';
		const ERROR_COURSE_LESSON_INVALID_RESOURCE  = 'El recurso suministrado no es válido';
		const ERROR_COURSE_EMPTY_TEST_RESULTS       = 'Evaluación de la lección sin resultados';

	}
