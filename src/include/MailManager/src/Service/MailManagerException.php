<?php
	namespace Platzilla\MailManager\Service;

	use Platzilla\MailManager\MailException;

	class MailManagerException extends MailException {
		const FOLDER_NOT_FOUND           = 'Imposible obtener la carpeta solicitada';
		const NOT_CONNECTED              = 'No se ha establecido una conexión al servidor';
		const INVALID_ACCOUNT            = 'La cuenta suministrada no es válida';
		const INVALID_PROVIDER           = 'La configuración del proveedor no es válida';
		const TOKEN_EXPIRED              = 'El token ha expirado';
		const UNABLE_TO_CONNECT          = 'Imposible conectarse al servidor de correo';
		const UNABLE_TO_GET_OAUTH2_TOKEN = 'Imposible obtener autorización del proveedor de correos';

	}
