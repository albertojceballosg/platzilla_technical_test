<?php

	class InvoiceItemException extends Exception {
		const ERROR_INVOICE_ITEM_EMPTY_ID       = 'No se ha suministrado el ID del item';
		const ERROR_INVOICE_ITEM_EMPTY_PRICE    = 'No se ha suministrado el precio';
		const ERROR_INVOICE_ITEM_EMPTY_SEQUENCE = 'No se ha suministrado el número de secuencia';

	}
