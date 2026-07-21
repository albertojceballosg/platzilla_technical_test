<?php
	namespace Platzilla\MailManager\Type;

	use Platzilla\MailManager\MailException;

	class SecurityTypeException extends MailException {
		const INVALID = 'No se ha suministrado un mecanismo de seguridad válido (SSL, STARTTLS, PLAIN)';

	}
