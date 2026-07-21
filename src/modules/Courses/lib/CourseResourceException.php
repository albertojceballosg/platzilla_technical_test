<?php

	class CourseResourceException extends Exception {
		const ERROR_COURSE_RESOURCE_DUPLICATE_NAME      = 'Ya existe un recurso con ese nombre';
		const ERROR_COURSE_RESOURCE_EMPTY_FILE_CONTENTS = 'No se ha suministrado el archivo';
		const ERROR_COURSE_RESOURCE_EMPTY_LESSON_ID     = 'No se ha suministrado la lección a la cual está asociado el recurso';
		const ERROR_COURSE_RESOURCE_EMPTY_NAME          = 'No se ha suministrado el nombre del recurso';
		const ERROR_COURSE_RESOURCE_EMPTY_TYPE          = 'No se ha suministrado el tipo del recurso';
		const ERROR_COURSE_RESOURCE_EMPTY_URL           = 'No se ha suministrado el URL del recurso';

	}
