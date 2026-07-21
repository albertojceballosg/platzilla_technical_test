<?php
	namespace Platzilla\MailManager\Provider;

	use Platzilla\MailManager\Type\ServiceType;

	class ImapProvider extends GenericProvider {

		public function __construct ($hostName, $port, $securityType, $authenticationMethod, $userNameType) {
			parent::__construct ($hostName, $port, $securityType, $authenticationMethod, ServiceType::IMAP, $userNameType);
		}

	}
