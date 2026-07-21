<?php
	namespace Platzilla\MailManager\Type;

	abstract class AuthenticationMethod {
		const OAUTH2              = 'oauth2';
		const PASSWORD_CLEAR_TEXT = 'password-cleartext';
		const PASSWORD_ENCRYPTED  = 'password-encrypted';
		const PLAIN               = 'plain';
		const SECURE              = 'secure';

		/**
		 * @return string[]
		 */
		public static function getAll () {
			return array (self::OAUTH2, self::PASSWORD_CLEAR_TEXT, self::PASSWORD_ENCRYPTED, self::PLAIN, self::SECURE);
		}

	}
