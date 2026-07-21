<?php
	namespace Platzilla\MailManager\Type;

	use Platzilla\MailManager\MailException;

	class ServiceTypeException extends MailException {
		const INVALID = 'No se ha suministrado un servicio válido (IMAP, POP3, SMTP)';

	}
