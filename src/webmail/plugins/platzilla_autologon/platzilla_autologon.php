<?php

	class platzilla_autologon extends rcube_plugin {
		public $task = 'login';

		public function init () {
			$this->add_hook ('startup', array ($this, 'startup'));
			$this->add_hook ('authenticate', array ($this, 'authenticate'));
		}

		public function startup ($arguments) {
			if (empty ($_SESSION ['user_id'])) {
				$arguments ['action'] = 'login';
			}
			if ((!empty ($_POST)) && (!empty ($_POST ['incoming']))) {
				if (empty ($_POST ['incoming']['securitytype'])) {
					$incomingProtocol = '';
				} else if (strtolower ($_POST ['incoming']['securitytype']) == 'ssl') {
					$incomingProtocol = 'ssl://';
				} else if (strtolower ($_POST ['incoming']['securitytype']) == 'tls') {
					$incomingProtocol = 'tls://';
				} else {
					$incomingProtocol = '';
				}
				$incomingHost = "{$incomingProtocol}{$_POST ['incoming']['hostname']}";

				if (empty ($_POST ['outgoing']['securitytype'])) {
					$outgoingProtocol = '';
				} else if (strtolower ($_POST ['outgoing']['securitytype']) == 'ssl') {
					$outgoingProtocol = 'ssl://';
				} else if (strtolower ($_POST ['outgoing']['securitytype']) == 'tls') {
					$outgoingProtocol = 'tls://';
				} else {
					$outgoingProtocol = '';
				}
				$outgoingHost = "{$outgoingProtocol}{$_POST ['outgoing']['hostname']}";

				$rcube = rcube::get_instance ();
				$rcube->config->set ('default_host', $incomingHost);
				$rcube->config->set ('default_port', intval ($_POST ['incoming']['port']));
				$rcube->config->set ('imap_auth_type', $_POST ['incoming']['authenticationmethod']);
				$rcube->config->set ('smtp_server', $outgoingHost);
				$rcube->config->set ('smtp_port', intval ($_POST ['outgoing']['port']));
				$rcube->config->set ('smtp_user', $_POST ['user']['username']);
				$rcube->config->set ('smtp_pass', $_POST ['user']['password']);
				$rcube->config->set ('smtp_auth_type', $_POST ['outgoing']['authenticationmethod']);
			}
			return $arguments;
		}

		public function authenticate ($arguments) {
			if (!empty ($_POST ['user'])) {
				$arguments ['user']        = $_POST ['user']['username'];
				$arguments ['pass']        = $_POST ['user']['password'];
				$arguments ['cookiecheck'] = true;
			}

			return $arguments;
		}

	}
