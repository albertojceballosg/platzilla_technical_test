<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/emailmanager/lib/EmailManagerUtils.class.php');

	class emailmanager {
		const STATUS_CONNECTION_REFUSED = 'CONNECTION REFUSED';
		const STATUS_INVALID_LANGUAGE   = 'INVALID LANGUAGE';
		const STATUS_INVALID_RECIPIENTS = 'INVALID RECIPIENTS';
		const STATUS_INVALID_SENDER     = 'INVALID SENDER';
		const STATUS_INVALID_TEMPLATE   = 'INVALID TEMPLATE';
		const STATUS_REJECTED           = 'REJECTED';
		const STATUS_SENT               = 'SENT';
		const STATUS_UNKNOWN            = 'UNKNOWN';

		private $adb;
		private $platform;
		private $lastError = null;

		private $host;
		private $port;
		private $username;
		private $password;
		private $security;
		private $senderFullName     = '';
		private $senderEmailAddress = '';

		public function __construct (PearDatabase $adb = null, $platform = null) {
			require ('config.inc.php');
			global $mailServer;

			$this->adb      = $adb;
			$this->platform = $platform;
			$this->host     = $mailServer ['host'];
			$this->port     = $mailServer ['port'];
			$this->username = $mailServer ['username'];
			$this->password = $mailServer ['password'];
			$this->security = $mailServer ['security'];
		}

		private function buildHtmlEmail ($body, $addDefaultHeader, $addDefaultFooter) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ADD_DEFAULT_HEADER', $addDefaultHeader);
			$smarty->assign ('ADD_DEFAULT_FOOTER', $addDefaultFooter);
			$smarty->assign ('BODY', $body);
			return $smarty->fetch ('modules/emailmanager/Email.tpl');
		}

		private function getAttachmentData ($attachment, $templateId, $platzillaRootFolderPath) {
			if (is_array ($attachment)) {
				if (file_exists ("{$platzillaRootFolderPath}/{$attachment ['path']}")) {
					$attachmentFilePath = $attachment ['path'];
					$attachmentFileName = $attachment ['file'];
				} else {
					$attachmentFilePath = "{$attachment ['path']}/{$attachment ['file']}";
					$attachmentFileName = $attachment ['file'];
				}
			} else if (file_exists ("{$platzillaRootFolderPath}/{$attachment}")) {
				$attachmentFilePath = $attachment;
				$attachmentFileName = $attachment;
			} else {
				$attachmentFilePath = EmailManagerUtils::getTemplateAttachmentFilePath ($this->adb, $templateId, $attachment, $this->platform);
				$attachmentFileName = $attachment;
			}
			return array (
				'filepath' => $attachmentFilePath,
				'filename' => $attachmentFileName,
			);
		}

		private function getValidRecipients ($recipients) {
			if (((!is_array ($recipients)) && (!filter_var ($recipients, FILTER_VALIDATE_EMAIL))) || ((is_array ($recipients)) && (count ($recipients) == 0))) {
				throw new Exception (self::STATUS_INVALID_RECIPIENTS);
			}

			$validRecipients = array ();
			if (is_array ($recipients)) {
				foreach ($recipients as $recipient) {
					$recipient = trim ($recipient);
					if (filter_var ($recipient, FILTER_VALIDATE_EMAIL)) {
						$validRecipients [] = $recipient;
					}
				}
			} else {
				$validRecipients = array (trim ($recipients));
			}

			if (count ($validRecipients) == 0) {
				throw new Exception (self::STATUS_INVALID_RECIPIENTS);
			}
			return $validRecipients;
		}

		private function sendEmail ($templateId, $recipients, $subject, $body, array $attachments = null) {
			require_once ('include/phpmailer/class.phpmailer.php');
			require_once ('include/phpmailer/class.smtp.php');

			try {
				$mailer           = new PHPMailer ();
				$mailer->CharSet  = 'UTF-8';
				$mailer->Encoding = 'quoted-printable';
				$mailer->IsHTML (true);
				$mailer->IsSMTP ();
				$mailer->SMTPSecure  = $this->security;
				$mailer->SMTPAuth    = $this->security ? true : false;
				$mailer->Host        = $this->host;
				$mailer->Username    = $this->username;
				$mailer->Password    = $this->password;
				$mailer->Port        = $this->port;
				$mailer->SMTPOptions = array (
					'ssl' => array (
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true
					)
				);

				$mailer->From     = $this->senderEmailAddress;
				$mailer->FromName = $this->senderFullName;
				if (is_array ($recipients)) {
					foreach ($recipients as $recipient) {
						$mailer->AddAddress ($recipient);
					}
				} else {
					$mailer->AddAddress ($recipients);
				}
				$mailer->Subject = $subject;
				$mailer->Body    = $body;
				if ($attachments) {
					$platzillaRootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
					foreach ($attachments as $attachment) {
						$attachmentData = $this->getAttachmentData ($attachment, $templateId, $platzillaRootFolderPath);
						if (file_exists ("{$platzillaRootFolderPath}/{$attachmentData ['filepath']}")) {
							$mailer->AddAttachment ("{$platzillaRootFolderPath}/{$attachmentData ['filepath']}", $attachmentData ['filename']);
						}
					}
				}
				$result = $mailer->send ();
				if (!$result) {
					$this->lastError = $mailer->ErrorInfo;
				}
			} catch (Exception $e) {
				$this->lastError = $e->getMessage ();
				$result          = false;
			}
			return $result;
		}

		private function substituteVariables ($text, array $variables = null) {
			if ((!$text) || (!$variables)) {
				return $text;
			}
			$text   = html_entity_decode ($text, ENT_QUOTES, 'UTF-8');
			$result = preg_match_all ('/<var>(.*?)<\/var>/', $text, $matches);
			if (($result === 0) || ($result === false)) {
				return $text;
			}

			$substitutedText = $text;
			foreach ($matches [1] as $index => $match) {
				if (isset ($variables [ $match ])) {
					$substitution = $variables [ $match ];
				} else {
					$substitution = '';
				}
				$substitutedText = str_replace ($matches [0][ $index ], $substitution, $substitutedText);
			}
			return $substitutedText;
		}

		private function validateLanguage ($language) {
			$availableLanguages = EmailManagerUtils::getAvailableLanguages ();
			if (!in_array ($language, $availableLanguages)) {
				throw new Exception (self::STATUS_INVALID_LANGUAGE);
			}
		}

		private function validateSender () {
			if ((!$this->senderFullName) || (!$this->senderEmailAddress) || (!filter_var ($this->senderEmailAddress, FILTER_VALIDATE_EMAIL))) {
				throw new Exception (self::STATUS_INVALID_SENDER);
			}
		}

		private function validateTemplate ($template) {
			if (empty ($template)) {
				throw new Exception (self::STATUS_INVALID_TEMPLATE);
			}
		}

		public function addSender ($fullName, $emailAddress) {
			$this->senderFullName     = $fullName;
			$this->senderEmailAddress = $emailAddress;
			return $this;
		}

		public function send ($recipients, $language, $templateName, array $variables = null, array $attachments = null) {
			$subject          = null;
			$body             = null;
			$validRecipients  = array ();
			$emailAttachments = null;
			try {
				$this->validateLanguage ($language);
				$this->validateSender ();
				$validRecipients = $this->getValidRecipients ($recipients);
				$template        = EmailManagerUtils::getTemplateByNameAndLanguage ($this->adb, $templateName, $language, $this->platform);
				$this->validateTemplate ($template);
				$subject          = $this->substituteVariables ($template ['subject'], $variables);
				$body             = $this->buildHtmlEmail (
					$this->substituteVariables ($template ['body'], $variables),
					(!!$template ['adddefaultheader']),
					(!!$template ['adddefaultfooter'])
				);
				$emailAttachments = array_merge (
					!empty ($template ['attachments']) ? $template ['attachments'] : array (),
					!empty ($attachments) ? $attachments : array ()
				);
				$result           = $this->sendEmail ($template ['templateid'], $validRecipients, $subject, $body, $emailAttachments);
				$status           = $result ? self::STATUS_SENT : self::STATUS_REJECTED;
			} catch (Exception $e) {
				$status          = self::STATUS_REJECTED;
				$this->lastError = $e->getMessage ();
			}

			EmailManagerUtils::registerEmailHistory (
				$this->adb,
				array (
					'templatename' => $templateName,
					'language'     => $language,
					'from'         => "{$this->senderFullName} <{$this->senderEmailAddress}>",
					'to'           => join (', ', $validRecipients),
					'subject'      => $subject,
					'body'         => $body,
					'status'       => $status,
					'attachments'  => $emailAttachments,
					'errormessage' => $this->lastError,
				)
			);
			$this->lastError = null;
			return $status;
		}

		public static function getAvailableStatuses () {
			return array (
				self::STATUS_SENT,
				self::STATUS_REJECTED,
				self::STATUS_CONNECTION_REFUSED,
				self::STATUS_INVALID_LANGUAGE,
				self::STATUS_INVALID_RECIPIENTS,
				self::STATUS_INVALID_SENDER,
				self::STATUS_INVALID_TEMPLATE,
				self::STATUS_UNKNOWN,
			);
		}

		public static function getInstance (PearDatabase $adb = null, $platform = null) {
			return new self ($adb, $platform);
		}

		public static function runPreUninstallTasks (PearDatabase $adb) {
			require_once ('modules/emailmanager/lib/EmailManagerInstaller.class.php');
			EmailManagerInstaller::getInstance ()->runPreUninstallTasks ($adb);
		}

		public static function runPostInstallTasks (PearDatabase $adb) {
			require_once ('modules/emailmanager/lib/EmailManagerInstaller.class.php');
			EmailManagerInstaller::getInstance ()->runPostInstallTasks ($adb);
		}

	}
