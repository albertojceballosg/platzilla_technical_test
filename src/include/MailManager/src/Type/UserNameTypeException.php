<?php
	namespace Platzilla\MailManager\Type;

	use Platzilla\MailManager\MailException;

	class UserNameTypeException extends MailException {
		const INVALID = 'No se ha suministrado un tipo de usuario válido (%EMAILADDRESS%, %EMAILLOCALPART%)';

	}
