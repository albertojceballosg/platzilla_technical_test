<?php
	namespace Platzilla\MailManager\Type;

	class SecurityType {
		const PLAIN    = 'plain';
		const SSL      = 'ssl';
		const STARTTLS = 'starttls';

		/**
		 * @return string[]
		 */
		public static function getAll () {
			return array (self::PLAIN, self::SSL, self::STARTTLS);
		}

	}
