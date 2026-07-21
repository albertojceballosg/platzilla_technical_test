<?php

	interface AdQueueInterface {
		const AD_QUEUE_CATEGORIES         = array ('PLATZILLA' => 'Anuncios del sistema', 'SUBSCRIPTION' => 'Anuncios de suscripción');
		const AD_QUEUE_PERIOD_24_HOURS    = '24_HOURS';
		const AD_QUEUE_PERIOD_48_HOURS    = '48_HOURS';
		const AD_QUEUE_PERIOD_72_HOURS    = '72_HOURS';
		const AD_QUEUE_PERIOD_15_DAYS     = '15_DAYS';
		const AD_QUEUE_PERIOD_30_DAYS     = '30_DAYS';
		const CLIENT_AD_QUEUE_PUBLISHED   = 'PUBLISHED';
		const CLIENT_AD_QUEUE_UNPUBLISHED = 'UNPUBLISHED';
		const NEWS_AD_QUEUE_ENABLED       = 'ENABLED';
		const NEWS_AD_QUEUE_DISABLED      = 'DISABLED';
	}
