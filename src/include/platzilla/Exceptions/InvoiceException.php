<?php

	class InvoiceException extends Exception {
		const ERROR_INVOICE_EMPTY_ACCOUNT_ID    = 'No se ha suministrado el ID de la cuenta';
		const ERROR_INVOICE_EMPTY_CREATION_DATE = 'No se ha suministrado la fecha de creación';
		const ERROR_INVOICE_EMPTY_DUE_DATE      = 'No se ha suministrado la fecha de vencimiento';
		const ERROR_INVOICE_EMPTY_INSTANCE_CODE = 'No se ha suministrado el código de la instancia';
		const ERROR_INVOICE_EMPTY_ITEMS         = 'No se han suministrado los items';
		const ERROR_INVOICE_EMPTY_STATUS        = 'No se ha suministrado el status';
		const ERROR_INVOICE_EMPTY_SUBJECT       = 'No se ha suministrado el asunto';
		const ERROR_INVOICE_INVALID_ACCOUNT_ID  = 'El ID de la cuenta no está registrado';
		const ERROR_INVOICE_INVALID_ITEM        = 'El item suministrado no es válido';

	}
