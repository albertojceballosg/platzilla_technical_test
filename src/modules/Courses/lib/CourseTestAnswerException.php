<?php

	class CourseTestAnswerException extends Exception {
		const ERROR_COURSE_TEST_ANSWER_EMPTY_QUESTION_ID = 'No se ha suministrado la pregunta asociada a la respuesta';
		const ERROR_COURSE_TEST_ANSWER_EMPTY_STATEMENT   = 'No se ha suministrado el planteamiento de la respuesta';

	}
