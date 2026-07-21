<?php

	interface PaymentInterface {
		const STATUS_CANCELLED = 'CANCELLED';
		const STATUS_PAST_DUE  = 'PAST DUE';
		const STATUS_PAID      = 'PAID';
		const STATUS_PENDING   = 'PENDING';
		const STATUS_REJECTED  = 'REJECTED';
		const STATUS_SUBMITTED = 'SUBMITTED';

		const TYPE_SUBSCRIPTION = 'SUBSCRIPTION';
		const TYPE_TRANSACTION  = 'TRANSACTION';

	}
