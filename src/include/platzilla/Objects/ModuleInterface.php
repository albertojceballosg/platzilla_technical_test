<?php

	interface ModuleInterface {
		const PRESENCE_ALWAYS_HIDDEN = -1;
		const PRESENCE_VISIBLE       = 0;
		const PRESENCE_HIDDEN        = 1;
		const PRESENCE_USER_DEFINED  = 2;

		const TYPE_ADMIN = 0;
		const TYPE_USER  = 1;
		const TYPE_TOOL  = 2;

	}
