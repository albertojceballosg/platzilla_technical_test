<?php
    
    interface HowToInterface {
        const HOW_TO_FILES = array (
			'DetailView'      => 'Vista detallada',
			'EditView'        => 'Vista de edición',
			//'ListView'        => 'Vista lista',
			'DetailView_Task' => 'Tareas en vistas de detalles'
        );
        const HOW_TO_FIELD_TYPE_VIDEO = array ('VIMEO', 'YOUTUBE');
        const YOUTUBE_BASE_URL        = 'https://www.youtube.com/embed/';
        const HOW_TO_STATUS           = array ('ENABLED' => 'Activo', 'DISABLED' => 'Deshabilitado');
        
        
    }
