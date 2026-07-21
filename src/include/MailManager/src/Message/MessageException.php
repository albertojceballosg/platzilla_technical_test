<?php
	namespace Platzilla\MailManager\Message;

	use Platzilla\MailManager\MailException;

	class MessageException extends MailException {
		const INVALID_ACCOUNT_HOLDER_EMAIL_ADDRESS = 'No se ha suministrado la dirección de correo del dueño de la cuenta';
		const INVALID_DATE                         = 'No se ha suministrado una fecha válida';
		const INVALID_FROM                         = 'No se ha suministrado un remitente válido';
		const INVALID_RECIPIENTS                   = 'No se ha suministrado al menos un destinatario válido';
		const INVALID_RECIPIENT                    = 'Al menos un destinatario no es válido';
		const INVALID_UID                          = 'No se ha suministrado un UID válido';

	}
