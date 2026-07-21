<?php
	namespace Platzilla\MailManager\Provider;

	use Platzilla\MailManager\MailException;

	class GenericProviderException extends MailException {
		const EMPTY_HOST_NAME     = 'No se ha suministrado el nombre o la dirección del servidor de correo';
		const INVALID_PORT_NUMBER = 'No se ha suministrado el puerto del servidor o no es un número válido';

	}
