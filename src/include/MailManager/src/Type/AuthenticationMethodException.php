<?php
	namespace Platzilla\MailManager\Type;

	use Platzilla\MailManager\MailException;

	class AuthenticationMethodException extends MailException {
		const INVALID = 'No se ha suministrado un mecanismo de autenticación válido (OAUTH2, PASSWORD-CLEARTEXT, PASSWORD-ENCRYPTED, PLAIN, SECURE)';

	}
