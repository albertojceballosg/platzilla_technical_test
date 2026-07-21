<?php
	namespace Platzilla\MailManager\Type;

	abstract class UserNameType {
		const EMAIL_ADDRESS = '%emailaddress%';
		const LOCAL_PART    = '%emaillocalpart%';

		/**
		 * @return string[]
		 */
		public static function getAll () {
			return array (self::EMAIL_ADDRESS, self::LOCAL_PART);
		}

	}
