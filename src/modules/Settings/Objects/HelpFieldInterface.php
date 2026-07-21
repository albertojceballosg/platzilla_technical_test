<?php

	interface HelpFieldInterface {
		const YOUTUBE_BASE_URL = 'https://www.youtube.com/embed/';
	}
	
	// Arrays constantes no soportados en interfaces en PHP 5.6
	// Se mueven a clase abstracta
	abstract class HelpFieldConstants {
		public static $HELP_FIELD_EDITABLE   = array ('YES' => 'Si', 'NOT' => 'No');
		public static $HELP_FIELD_STATUS     = array ('ENABLED' => 'Activo', 'DISABLED' => 'Deshabilitado');
		public static $HELP_FIELD_TYPE_VIDEO = array ('VIMEO', 'YOUTUBE');
	}
