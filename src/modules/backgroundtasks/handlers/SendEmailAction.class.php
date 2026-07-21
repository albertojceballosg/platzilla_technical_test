<?php
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');

	class SendEmailAction extends BackgroundTaskActionHandler {
		/** @var string */
		private $platform;

		public function __construct (PearDatabase $adb, Logger $logger = null, $platform = null) {
			if (empty ($platform)) {
				global $platPrincipal;
				require ('config.inc.php');
				$this->platform = $platPrincipal;
			} else {
				$this->platform = $platform;
			}
			parent::__construct ($adb, $logger);
		}

		/**
		 * @param BackgroundTaskAction $action
		 *
		 * @return null
		 * @throws Exception
		 */
		public function run ($action) {
			if (empty ($action)) {
				throw new Exception ('No se ha suministrado la acción');
			}

			$parameters = $action->getParameters ();
			if (empty ($parameters)) {
				throw new Exception ('No se han suministrado los parámetros de la acción');
			}

			$this->logger->emit ('INFO', 'Obteniendo parámetros');
			$requestedParameterNames = array ('language', 'recipients', 'templatename', 'variables');
			$parameterValues         = $this->getParameterValues ($parameters, $requestedParameterNames, true);
			if (is_string ($parameterValues ['recipients']) && (strpos ($parameterValues ['recipients'], ',') !== false)) {
				$parameterValues ['recipients'] = explode (',', $parameterValues ['recipients']);
			}
			$this->logger->emit ('INFO', 'Invocando al emailmanager');
			require_once ('modules/emailmanager/emailmanager.php');
			$status = emailmanager::getInstance ($this->adb, $this->platform)->addSender (
				'Platzilla',
				'no_reply@platzilla.com'
			)->send (
				$parameterValues ['recipients'],
				$parameterValues ['language'],
				$parameterValues ['templatename'],
				$parameterValues ['variables']
			);
			if ($status != emailmanager::STATUS_SENT) {
				throw new Exception ("Se ha presentado un error al enviar el correo: código {$status}");
			}
			$this->logger->emit ('INFO', 'Se ha enviado el correo');

			return null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $parameterConfiguration
		 * @param array $selectedParameterValues
		 *
		 * @return array|null
		 */
		public static function getDefaultOptions (PearDatabase $adb, $parameterConfiguration, $selectedParameterValues) {
			if (($parameterConfiguration ['parametername'] != 'variables') || (empty ($selectedParameterValues ['language'])) || (empty ($selectedParameterValues ['templatename']))) {
				return null;
			}
			require_once ('modules/emailmanager/lib/EmailManagerUtils.class.php');
			$variableNames = EmailManagerUtils::getTemplateVariableNames ($adb, $selectedParameterValues ['templatename'], $selectedParameterValues ['language']);
			if (!empty ($variableNames)) {
				$options = array ();
				foreach ($variableNames as $key => $value) {
					$options [ $key ] = array (
						'label'      => $value,
						'attributes' => null,
					);
				}
			} else {
				$options = null;
			}
			return $options;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return SendEmailAction
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}

	}
