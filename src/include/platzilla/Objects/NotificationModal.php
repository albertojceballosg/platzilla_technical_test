<?php
	require_once ('include/platzilla/Exceptions/NotificationException.php');
	require_once ('include/platzilla/Objects/NotificationInterface.php');

	/**
	 * Class NotificationModal
	 *
	 * Esta clase hace referencia a la tabla vtiger_notifications_modal del módulo
	 */
	class NotificationModal implements NotificationInterface, Serializable {

		/** @var integer */
		private $id;

		/** @var string[] */
		private $buttonLinks;

		/** @var string */
		private $customButtons;

		/** @var string */
		private $exitText;

		/** @var string */
		private $inputText;

		/** @var string */
		private $moduleName;

		/**
		 * Copia un objeto NotificationModal a partir de otro objeto NotificationModal
		 *
		 * @param $notify
		 */
		public function copyValuesFrom ($notify) {
			if ((empty ($notify)) || (!($notify instanceof NotificationModal))) {
				return;
			}
			$this->buttonLinks   = $notify->getButtonLinks ();
			$this->customButtons = $notify->getCustomButton ();
			$this->exitText      = $notify->getExitText ();
			$this->inputText     = $notify->getInputText ();
			$this->moduleName    = $notify->getModuleName ();
		}

		/**
		 * Duplica un objeto NotificationModal
		 *
		 * @param integer $newNotificationId
		 *
		 * @return NotificationModal
		 */
		public function duplicate ($newNotificationId = null) {
			$object = new self ();
			return $object->setId ($newNotificationId)
				->setButtonLinks ($this->buttonLinks)
				->setCustomButton ($this->customButtons)
				->setExitText ($this->exitText)
				->setInputText ($this->inputText)
				->setModuleName ($this->moduleName);
		}

		/**
		 * Obtiene lista de botones personalizados asociados a la notifiación modal
		 *
		 * @return string[]
		 */
		public function getButtonLinks () {
			return $this->buttonLinks;
		}

		/**
		 * Obtiene el botón personalizado asociado
		 *
		 * @return string
		 */
		public function getCustomButton () {
			return $this->customButtons;
		}

		/**
		 * Obtiene el texto de salida de la notifiación modal
		 *
		 * @return string
		 */
		public function getExitText () {
			return $this->exitText;
		}

		/**
		 * Obtiene el ID de la notifiación modal
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Obtiene el texto de entrada de la notifiación modal
		 *
		 * @return string
		 */
		public function getInputText () {
			return $this->inputText;
		}

		/**
		 * Obtiene el módulo fuente de la notifiación modal
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Compara dos objetos NotificationModal
		 *
		 * @param NotificationModal $notify
		 *
		 * @return boolean
		 */
		public function isEqualTo ($notify) {
			if (
				(empty ($notify)) ||
				(!($notify instanceof NotificationModal)) ||
				($this->customButtons != $notify->getCustomButton ()) ||
				($this->exitText != $notify->getExitText ()) ||
				($this->inputText != $notify->getInputText ()) ||
				($this->moduleName != $notify->getModuleName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Devuelve una cadena serializada de los valores de las propiedades
		 *
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->id,
					$this->buttonLinks,
					$this->customButtons,
					$this->exitText,
					$this->inputText,
					$this->moduleName,
				)
			);
		}

		/**
		 * Establece el arreglo de botones personalizados
		 *
		 * @param string[] $links
		 *
		 * @return NotificationModal
		 */
		public function setButtonLinks ($links) {
			if (is_array ($links) && (!empty ($links))) {
				$this->buttonLinks = $links;
			} else {
				$this->buttonLinks = null;
			}
			return $this;
		}

		/**
		 * Establece los botones de la notifiación modal
		 *
		 * @param string $buttons
		 *
		 * @return NotificationModal
		 */
		public function setCustomButton ($buttons) {
			if (is_scalar ($buttons)) {
				$this->customButtons = $buttons;
			} else {
				$this->customButtons = null;
			}
			return $this;
		}

		/**
		 * Establece el texto de salida de una notifiación modal
		 *
		 * @param string $exitText
		 *
		 * @return NotificationModal
		 */
		public function setExitText ($exitText) {
			if (is_scalar ($exitText)) {
				$this->exitText = $exitText;
			} else {
				$this->exitText = null;
			}
			return $this;
		}

		/**
		 * Establece el ID del regsitro de una notifiación modal
		 *
		 * @param integer $id
		 *
		 * @return NotificationModal
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = $id;
			} else {
				$this->id = null;
			}
			return $this;
		}
		
		/**
		 * Establece el texto de salida de una notifiación modal
		 *
		 * @param string $inputText
		 *
		 * @return NotificationModal
		 */
		public function setInputText ($inputText) {
			if (is_scalar ($inputText)) {
				$this->inputText = $inputText;
			} else {
				$this->inputText = null;
			}
			return $this;
		}

		/**
		 * Establece el modulo fuente de la notificación modal
		 *
		 * @param string $moduleName
		 *
		 * @return NotificationModal
		 */
		public function setModuleName ($moduleName) {
			if (is_scalar ($moduleName)) {
				$this->moduleName = $moduleName;
			} else {
				$this->moduleName = null;
			}
			return $this;
		}

		/**
		 * Devuelve los valores de las propiedades a partir de una cadena serializada
		 *
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->buttonLinks,
				$this->customButtons,
				$this->exitText,
				$this->inputText,
				$this->moduleName,
				) = unserialize ($serialized);
		}

		/**
		 * Instanciación de la clase NotificationModal. Se obtiene un objeto NotificationModal con los atributos de la clase
		 *
		 * @return NotificationModal
		 */
		public static function getInstance () {
			return new self ();
		}

	}
