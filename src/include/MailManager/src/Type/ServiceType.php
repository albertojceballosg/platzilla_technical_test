<?php
	namespace Platzilla\MailManager\Type;

	abstract class ServiceType {
		const IMAP = 'imap';
		const POP3 = 'pop3';
		const SMTP = 'smtp';

		const IMAP_DEFAULT_PORT  = 143;
		const IMAP_SSL_PORT      = 993;
		const IMAP_STARTTLS_PORT = 143;
		const POP3_DEFAULT_PORT  = 110;
		const POP3_SSL_PORT      = 995;
		const SMTP_DEFAULT_PORT  = 25;
		const SMTP_SSL_PORT      = 465;
		const SMTP_STARTTLS_PORT = 587;

		/**
		 * @return string[]
		 */
		public static function getAll () {
			return array (self::IMAP, self::POP3, self::SMTP);
		}

	}
