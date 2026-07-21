<?php
	/**
	 * Donde se declaran las constantes que controlan los atributos del campo: Tipos de Campos,  valor por pantalla, edicion en masa, visibilidad, creacion rapida
	 * permiso de lectura/escritura, y el uiType en BD para cada tipo de campo
	 */
	interface FieldInterface {
		const DATA_TYPE_CHECKBOX        = 'C';
		const DATA_TYPE_DATE            = 'D';
		const DATA_TYPE_DATETIME        = 'DT';
		const DATA_TYPE_EMAIL           = 'E';
		const DATA_TYPE_GRID            = 'X';
		const DATA_TYPE_INTEGER         = 'I';
		const DATA_TYPE_NEGATIVE_NUMBER = 'NN';
		const DATA_TYPE_NUMBER          = 'N';
		const DATA_TYPE_PASSWORD        = 'P';
		const DATA_TYPE_TIME            = 'T';
		const DATA_TYPE_VARCHAR         = 'V';

		const DISPLAY_TYPE_NOWHERE          = 0;
		const DISPLAY_TYPE_ALL              = 1;
		const DISPLAY_TYPE_DETAIL_VIEW_ONLY = 2;
		const DISPLAY_TYPE_LIST_VIEW_ONLY   = 3;
		const DISPLAY_TYPE_PASSWORD         = 4;

		const GENERATED_TYPE_EXISTING = 1;
		const GENERATED_TYPE_CUSTOM   = 2;

		const MASS_EDITABLE_DISABLED     = 0;
		const MASS_EDITABLE_ENABLED      = 1;
		const MASS_EDITABLE_USER_DEFINED = 2;

		const PRESENCE_ALWAYS_HIDDEN = -1;
		const PRESENCE_VISIBLE       = 0;
		const PRESENCE_HIDDEN        = 1;
		const PRESENCE_USER_DEFINED  = 2;

		const QUICK_CREATE_ENABLED  = 0;
		const QUICK_CREATE_DISABLED = 1;
		const QUICK_CREATE_UNKNOWN  = 2;

		const READ_ONLY  = 0;
		const READ_WRITE = 1;

		const UI_TYPE_ATTACHMENTS      = 4096;
		const UI_TYPE_CALCULATED       = 2204;
		const UI_TYPE_CALCULATED_LINK  = 2206;
		const UI_TYPE_CHECKBOX         = 56;
		const UI_TYPE_CODE             = 4;
		const UI_TYPE_CREATED_TIME     = 70;
		const UI_TYPE_CURRENCY         = 71;
		const UI_TYPE_DATE             = 5;
		const UI_TYPE_DATETIME         = 6;
		const UI_TYPE_EMAIL            = 13;
		const UI_TYPE_GLOBAL_PICKLIST  = 16;
		const UI_TYPE_GRID             = 2202;
		const UI_TYPE_IMAGE_DISPLAY    = 258;
		const UI_TYPE_IMAGE_REFERENCE  = 257;
		const UI_TYPE_MODIFIED_BY      = 52;
		const UI_TYPE_MODULE_RECORDS   = 404;
		const UI_TYPE_MODULE_REFERENCE = 10;
		const UI_TYPE_MULTI_SELECT     = 33;
		const UI_TYPE_NUMBER           = 7;
		const UI_TYPE_OWNER            = 53;
		const UI_TYPE_PERCENTAGE       = 9;
		const UI_TYPE_PHONE            = 11;
		const UI_TYPE_PICKLIST         = 15;
		const UI_TYPE_PIPELINE         = 8192;
		const UI_TYPE_SKYPE            = 85;
		const UI_TYPE_SUMMARY_ROW      = 2203;
		const UI_TYPE_OPERATION_ROW    = 2207;
		const UI_TYPE_TABLE_FIELD      = 2208;
		const UI_TYPE_TEXT             = 1;
		const UI_TYPE_TEXTAREA         = 21;
		const UI_TYPE_TIME             = 14;
		const UI_TYPE_URL              = 17;
		const UI_TYPE_VIDEO            = 5006;
		const UI_TYPE_APP              = 5010;
	}
