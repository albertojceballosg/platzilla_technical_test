<?php
	namespace Platzilla\MailManager\Utils;

	use Platzilla\MailManager\MailException;

	abstract class MailUtils {
		const ENCRYPTION_METHOD = 'DES-EDE3-CBC';

		/**
		 * @param integer $length
		 *
		 * @return null|string
		 */
		private static function generateRandomBytes ($length) {
			// Use PHP7 true random generator
			if (function_exists ('random_bytes')) {
				// random_bytes() can throw an Error/TypeError/Exception in some cases
				try {
					$randomBytes = random_bytes ($length);
				} catch (\Throwable $ignored) {
					$randomBytes = null;
				}
			} else {
				$randomBytes = null;
			}

			if (!$randomBytes) {
				$randomBytes = openssl_random_pseudo_bytes ($length);
			}

			return $randomBytes;
		}

		/**
		 * @param string $encryptedPassword
		 * @param string $key
		 *
		 * @return string
		 */
		public static function decrypt ($encryptedPassword, $key) {
			$cipher  = base64_decode ($encryptedPassword);
			$options = defined ('OPENSSL_RAW_DATA') ? OPENSSL_RAW_DATA : true;
			$ivSize  = openssl_cipher_iv_length (self::ENCRYPTION_METHOD);
			$iv      = substr ($cipher, 0, $ivSize);
			// session corruption? (#1485970)
			if (strlen ($iv) < $ivSize) {
				return '';
			}
			$cipher = substr ($cipher, $ivSize);
			return openssl_decrypt ($cipher, self::ENCRYPTION_METHOD, $key, $options, $iv);
		}

		/**
		 * @param string $plainPassword
		 * @param string $key
		 *
		 * @return string
		 */
		public static function encrypt ($plainPassword, $key) {
			if (empty ($plainPassword)) {
				return '';
			}

			$options = defined ('OPENSSL_RAW_DATA') ? OPENSSL_RAW_DATA : true;
			$iv      = self::generateRandomBytes (openssl_cipher_iv_length (self::ENCRYPTION_METHOD));
			return base64_encode ($iv . openssl_encrypt ($plainPassword, self::ENCRYPTION_METHOD, $key, $options, $iv));
		}

	}
