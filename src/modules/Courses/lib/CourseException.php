<?php

	class CourseException extends Exception {
		const ERROR_COURSE_DUPLICATE_NAME        = 'Ya existe un curso registrado con ese nombre';
		const ERROR_COURSE_EMPTY                 = 'No se ha suministrado un curso válido';
		const ERROR_COURSE_EMPTY_COURSE_ID	     = 'No se ha suministrado el ID del curso';
		const ERROR_COURSE_EMPTY_CATEGORY        = 'No se ha suministrado la categoría del curso';
		const ERROR_COURSE_EMPTY_DESCRIPTION     = 'No se ha suministrado la descripción del curso';
		const ERROR_COURSE_EMPTY_LESSONS         = 'No se han suministrado las lecciones del curso';
		const ERROR_COURSE_EMPTY_LEVEL           = 'No se ha suministrado el nivel del curso';
		const ERROR_COURSE_EMPTY_NAME            = 'No se ha suministrado el nombre del curso';
		const ERROR_COURSE_EMPTY_PRICE           = 'No se ha suministrado el precio del curso';
		const ERROR_COURSE_EMPTY_STATUS          = 'No se ha suministrado el status del curso';
		const ERROR_COURSE_EMPTY_TARGET_AUDIENCE = 'No se ha suministrado la audiencia del curso';
		const ERROR_COURSE_INVALID_CATEGORY      = 'La categoría suministrada no está registrada';
		const ERROR_COURSE_INVALID_LESSON        = 'La lección suministrada no es válida';
		const ERROR_COURSE_INVALID_USER          = 'No se ha suministrado el usuario del curso';
		const ERROR_FORUM_URL_INVALID            = 'Url del foro no es valida';
		const ERROR_LESSON_INVALID_USER          = 'No se ha suministrado el usuario de la lección';

	}
