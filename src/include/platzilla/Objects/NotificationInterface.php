<?php

	interface NotificationInterface {
		const ACTION_DANGER               = 'DANGER';
		const ACTION_INFO                 = 'INFO';
		const ACTION_SUCCESS              = 'SUCCESS';
		const ACTION_WARNING              = 'WARNING';
		const DETAIL_VIEW                 = 'DETAIL_VIEW';
		const EDIT_VIEW                   = 'EDIT VIEW';
		const EVENT_ALWAYS                = 'ALWAYS';
		const EVENT_EDIT_RECORD           = 'EDIT RECORD';
		const EVENT_FIRST_TIME            = 'FIRST TIME';
		const EVENT_CANCEL_RECORD         = 'CANCEL RECORD';
		const EVENT_CREATE_RECORD         = 'CREATE RECORD';
		const EVENT_RECORD_NO_CREATE      = 'RECORD_NO_CREATE';
		const EVENT_SAVE_RECORD           = 'SAVE RECORD';
		const EVENT_TOTAL_RECORDS_REACHED = 'TOTAL RECORDS REACHED';
		const EVENT_FROM_BACKGROUNDTASK   = 'FROM BACKGROUNDTASK';
		const FROM_SYSTEM                 = 'SYSTEM';
		const FROM_USERS                  = 'USERS';
		const LIST_VIEW                   = 'LIST_VIEW';
		const STATUS_ACTIVE               = 'ACTIVE';
		const STATUS_INACTIVE             = 'INACTIVE';
		const STYLE_ALERT                 = 'ALERT';
		const STYLE_MODAL                 = 'MODAL';
		const STYLE_NOTIFY                = 'NOTIFY';
		const TYPE_EMAIL                  = 'EMAIL';
		const TYPE_SCREEN                 = 'SCREEN';
	}
