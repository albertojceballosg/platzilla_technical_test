<?php

	interface CoursesInterface {
		const COURSE_EVALUATION = array ('PASS' => 'Aprobada', 'TO PASS' => 'Por aprobar');
		const COURSE_STATUS     = array (
			'IN_PROGRESS' => 'En realización',
			'MADE'        => 'Realizado',
			'NOT_STARTED' => 'No iniciado',
		);
		const COURSE_STATUS_COLOR = array (
			'IN_PROGRESS' => '#0073c2',
			'MADE'        => '#03af50',
			'NOT_STARTED' => '#d4d4d7',
		);
		const COURSE_TARGET_AUDIENCE = array (
			'Emprendedor',
			'Microempresario',
			'Emprendedor pyme',
			'Miembro de una PYME en crecimiento',
			'Miembro de una empresa grande',
			'Fases del emprendimiento',
			'Evaluación de ideas de negocio',
			'Síntomas de mala gestión',
			'Procesos correctos y perfectos',
			'Implantación de procesos',
			'Plan de marketing',
			'Plan de ventas',
			'Gestión comercial',
			'Diagnóstico empresarial con el índice ideal',
		);
		const COURSE_TYPE_VIDEO     = array ('VIMEO', 'YOUTUBE');
		const LESSON_EVALUATION     = array ('PASS' => 'Aprobada', 'TO PASS' => 'Por aprobar');
		const LESSON_PUBLISH_STATUS = array (0 => 'No publicada', 1 => 'Publicada');
		const LESSON_STATUS     = array (
			'LESSON_NOT_VISITED'             => 'Lección no accedida',
			'LESSON_VISITED'                 => 'Lección visitada previamente sin hacer evaluación',
			'LESSON_ASSESSED_BUT_NOT_PASSED' => 'Lección evaluada pero no aprobada',
			'LESSON_TEST_PASSED'             => 'Evaluación aprobada, ejercicio práctico pendiente',
			'LESSON_PASSED'                  => 'Lección aprobada',
		);
		const LESSON_STATUS_COLOR = array (
			'LESSON_NOT_VISITED'             => '#7E8F7E',
			'LESSON_VISITED'                 => '#01B9FE',
			'LESSON_ASSESSED_BUT_NOT_PASSED' => '#46617F',
			'LESSON_TEST_PASSED'             => '#46617F',
			'LESSON_PASSED'                  => '#2ECC71',
		);
		// Estados específicos para evaluaciones
		const EVALUATION_STATUS = array (
			'TEST_NOT_PASSED' => 'Evaluación no aprobada',
			'TEST_PASSED'     => 'Evaluación aprobada',
		);
		const EVALUATION_STATUS_COLOR = array (
			'TEST_NOT_PASSED' => '#46617F',
			'TEST_PASSED'     => '#2ECC71',
		);
		// Estados específicos para ejercicios prácticos
		const EXERCISE_STATUS = array (
			'EXERCISE_VISITED' => 'Ejercicio visitado',
			'EXERCISE_DONE'   => 'Ejercicio completado',
			'EXERCISE_PASSED' => 'Ejercicio aprobado'
		);
		const EXERCISE_STATUS_COLOR = array (
			'EXERCISE_VISITED' => '#46617F',
			'EXERCISE_DONE'   => '#2ECC71',
			'EXERCISE_PASSED' => '#2ECC71'
		);
		const EXERCISE_BUTTON_COLOR = array (
			'NO_ACCESS'      => '#7E8F7E',  // Gris - Cuando hay evaluación pendiente
			'READY'          => '#01B9FE',  // Azul - Cuando está listo para realizar o no hay evaluación
			'EXERCISE_VISITED' => '#01B9FE', // Azul - Cuando el ejercicio ha sido visitado
			'EXERCISE_DONE'    => '#2ECC71'  // Verde - Cuando el ejercicio está completado
		);
		const QUESTION_EVALUATION  = array ('PASSED' => 'Lograda', 'TO_BE_PASSED' => 'No lograda');
		const YOUTUBE_BASE_URL     = 'https://www.youtube.com/embed/';
		const UI_COLORS = array(
            'TEXT_WHITE' => '#FFFFFF',
            'TEXT_LIGHT_GRAY' => '#f2f3f2',
            'BORDER_GRAY' => '#EAEAEA',
            'BACKGROUND_LIGHT_GRAY' => '#e8e8e8',
            'BORDER_DARK' => '#DDDDDD',
            'ICON_INFO' => '#17a2b8',
            'ERROR' => '#FF0000'
        );

        const FILE_ICONS = array(
            'PDF' => array('class' => 'fa-file-pdf-o', 'color' => self::UI_COLORS['ICON_INFO']),
            'TEXT' => array('class' => 'fa-file-text-o', 'color' => self::UI_COLORS['ICON_INFO']),
            'IMAGE' => array('class' => 'fa-file-image-o', 'color' => self::UI_COLORS['ICON_INFO']),
            'WORD' => array('class' => 'fa-file-word-o', 'color' => self::UI_COLORS['ICON_INFO']),
            'EXCEL' => array('class' => 'fa-file-excel-o', 'color' => self::UI_COLORS['ICON_INFO']),
            'VIDEO' => array('class' => 'fa-file-video-o', 'color' => self::UI_COLORS['ICON_INFO']),
            'DEFAULT' => array('class' => 'fa-file-o', 'color' => self::UI_COLORS['ICON_INFO'])
        );
	}
