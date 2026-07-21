<?php

	interface DataSharingRequestInterface {
		const RECIPIENT_TYPE_CONTACT  = 'CONTACT';
		const RECIPIENT_TYPE_CUSTOMER = 'CUSTOMER';
		const RECIPIENT_TYPE_LITERAL  = 'LITERAL';

		const RULE_FULL    = 'FULL';
		const RULE_MINIMAL = 'MINIMAL';

		const STATUS_ACCEPTED = 'ACCEPTED';
		const STATUS_REJECTED = 'REJECTED';
		const STATUS_SENT     = 'SENT';

	}
