<?php
	require_once ('include/platzilla/Exceptions/NotificationException.php');
	require_once ('include/platzilla/Objects/NotificationFilter.php');
	require_once ('include/platzilla/Objects/NotificationInterface.php');
	require_once ('include/platzilla/Objects/NotificationModal.php');

	/**
	 * Class Notification
	 *
	 * Esta clase hace referencia a la tabla vtiger_notifications del módulo
	 */
	class Notification implements NotificationInterface, Serializable {
		/** @var integer */
		private $id;

		/** @var string */
		private $action;

		/** @var string */
		private $contents;

		/** @var string */
		private $createdTime;

		/** @var string */
		private $description;

		/** @var string */
		private $event;

		/** @var string */
		private $eventParameter;

		/** @var NotificationFilter */
		private $filter;

		/** @var boolean */
		private $locked;

		/** @var NotificationModal */
		private $modal;

		/** @var string[] */
		private $moduleNames;

		/** @var string */
		private $name;

		/** @var string */
		private $readyToSend;

		/** @var string */
		private $sendByEmail;

		/** @var string */
		private $scope;

		/** @var string */
		private $status;

		/** @var string */
		private $style;

		/** @var string */
		private $type;

		/** @var string */
		private $view;

		/** @var string */
		public $timeSince;

		/**
		 * Obtiene el ID de la notificación
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Copia un objeto Notification desde otro objeto Notification
		 *
		 * @param $notify
		 */
		public function copyValuesFrom ($notify) {
			if ((empty ($notify)) || (!($notify instanceof Notification))) {
				return;
			}
			$this->action         = $notify->getAction ();
			$this->sendByEmail    = $notify->getSendByEmail ();
			$this->scope          = $notify->getScope ();
			$this->contents       = $notify->getContents ();
			$this->description    = $notify->getDescription ();
			$this->event          = $notify->getEvent ();
			$this->eventParameter = $notify->getEventParameter ();
			$this->filter         = $notify->filter->copyValuesFrom ($notify->getFilter ());
			$this->locked         = $notify->isLocked ();
			$this->modal          = ($this->modal) ? $notify->modal->copyValuesFrom($notify->getModal ()) : null;
			$this->moduleNames    = $notify->getModuleNames ();
			$this->name           = $notify->getName ();
			$this->status         = $notify->getStatus ();
			$this->style          = $notify->getStyle ();
			$this->type           = $notify->getType ();
			$this->view           = $notify->getView ();
		}

		/**
		 * Duplica un objeto Notification
		 *
		 * @param integer $newNotificationId
		 *
		 * @return Notification
		 */
		public function duplicate ($newNotificationId = null) {
			$object = new self ();
			return $object->setId ($newNotificationId)
				->setAction ($this->action)
				->setSendByEmail ($this->sendByEmail)
				->setScope ($this->scope)
				->setContents ($this->contents)
				->setDescription ($this->description)
				->setEvent ($this->event)
				->setEventParameter ($this->eventParameter)
				->setFilter ($this->filter->duplicate ($newNotificationId))
				->setLocked ($this->locked)
				->setModal (($this->modal) ? $this->modal->duplicate ($newNotificationId) : null)
				->setModuleNames ($this->moduleNames)
				->setName ($this->name)
				->setStatus ($this->status)
				->setStyle ($this->style)
				->setType ($this->type)
				->setView ($this->view);
		}

		/**
		 * Obtiene la accion de una notificación
		 *
		 * @return string
		 */
		public function getAction () {
			return $this->action;
		}

		/**
		 * Obtiene el contenido de una notificación
		 *
		 * @return string
		 */
		public function getContents () {
			return $this->contents;
		}

		/**
		 * Obtiene la fecha de creación de una notifiación
		 *
		 * @return string
		 */
		public function getCreatedTime () {
			return $this->createdTime;
		}

		/**
		 * Obtiene la descripción de una notifiación
		 *
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * Obtiene el evento de una notifiación
		 *
		 * @return string
		 */
		public function getEvent () {
			return $this->event;
		}

		/**
		 * Obtiene los parámetros requeridos para que se ejecute un evento
		 *
		 * @return string
		 */
		public function getEventParameter () {
			return $this->eventParameter;
		}

		/**
		 * Obtiene un objeto NotificationFilter
		 *
		 * @return NotificationFilter
		 */
		public function getFilter () {
			return $this->filter;
		}

		/**
		 * Obtiene un objeto NotificationModal
		 *
		 * @return NotificationModal
		 */
		public function getModal () {
			return $this->modal;
		}

		/**
		 * Obtiene el nombre del módulo fuente de una notifiación
		 *
		 * @return string[]
		 */
		public function getModuleNames () {
			return $this->moduleNames;
		}

		/**
		 * Obtien el nombre de una notificación
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return string
		 */
		public function getReadyToSend() {
			return $this->readyToSend;
		}

		/**
		 * Obtiene el estatus de envio al panel de mensaje
		 *
		 * @return string
		 */
		public function getSendByEmail () {
			return $this->sendByEmail;
		}

		/**
		 * Obtiene el alcance de una notifiación en terminos del sistema
		 *
		 * @return string
		 */
		public function getScope () {
			return $this->scope;
		}

		/**
		 * Obtiene el estatus de una notificación
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Obtiene el estilo de una notificación
		 *
		 * @return string
		 */
		public function getStyle () {
			return $this->style;
		}

		/**
		 * Obtien el tipo de notificación
		 *
		 * @return string
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * Obtiene la vista donde se presentará la notifiación
		 *
		 * @return string
		 */
		public function getView () {
			return $this->view;
		}

		/**
		 * Compara dos Objetos Notification
		 *
		 * @param Notification $notify
		 *
		 * @return boolean
		 */
		public function isEqualTo ($notify) {
			if (
				(empty ($notify)) ||
				(!($notify instanceof Notification)) ||
				($this->action != $notify->getAction ()) ||
				($this->sendByEmail != $notify->getSendByEmail ()) ||
				($this->scope != $notify->getScope ()) ||
				($this->contents != $notify->getContents ()) ||
				($this->description != $notify->getDescription ()) ||
				($this->event != $notify->getEvent ()) ||
				($this->eventParameter != $notify->getEventParameter ()) ||
				(!$this->filter->isEqualTo ($notify->getFilter ())) ||
				(!$this->modal->isEqualTo($notify->getModal ())) ||
				($this->moduleNames != $notify->getModuleNames ()) ||
				($this->name != $notify->getName ()) ||
				($this->status != $notify->getStatus ()) ||
				($this->style != $notify->getStyle ()) ||
				($this->type != $notify->getType ()) ||
				($this->view != $notify->getView ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Verifica si una notifiación esta bloqueada
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Establece el ID de una notifiación
		 *
		 * @param integer $id
		 *
		 * @return Notification
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
		 * Establece la acción de una notifiación
		 *
		 * @param string $action
		 *
		 * @return Notification
		 */
		public function setAction ($action) {
			if (in_array (
				$action,
				array (
					self::ACTION_SUCCESS,
					self::ACTION_INFO,
					self::ACTION_WARNING,
					self::ACTION_DANGER,
				)
			)
			) {
				$this->action = $action;
			} else {
				$this->action = null;
			}
			return $this;
		}

		/**
		 * Establece el contenido de una notifiación
		 *
		 * @param string $contents
		 *
		 * @return Notification
		 */
		public function setContents ($contents) {
			if (is_scalar ($contents)) {
				$this->contents = $contents;
			} else {
				$this->contents = null;
			}
			return $this;
		}

		/**
		 * Establece la fecha de creación de la notifiación
		 *
		 * @param string $date
		 *
		 * @return Notification
		 */
		public function setCreatedTime ($date) {
			if (is_numeric ($date)) {
				$this->createdTime = $date;
			} else {
				$this->createdTime = null;
			}
			return $this;
		}

		/**
		 * Establece la descripción de la notificación
		 *
		 * @param string $description
		 *
		 * @return Notification
		 */
		public function setDescription ($description) {
			if (is_scalar ($description)) {
				$this->description = $description;
			} else {
				$this->description = null;
			}
			return $this;
		}

		/**
		 * Establece el evento que disparará la notifiación
		 *
		 * @param string $event
		 *
		 * @return Notification
		 */
		public function setEvent ($event) {
			if (in_array (
				$event,
				array (
					self::EVENT_ALWAYS,
					self::EVENT_EDIT_RECORD,
					self::EVENT_FIRST_TIME,
					self::EVENT_CANCEL_RECORD,
					self::EVENT_CREATE_RECORD,
					self::EVENT_RECORD_NO_CREATE,
					self::EVENT_SAVE_RECORD,
					self::EVENT_TOTAL_RECORDS_REACHED,
					self::EVENT_FROM_BACKGROUNDTASK,
				)
			)
			) {
				$this->event = $event;
			} else {
				$this->event = null;
			}
			return $this;
		}

		/**
		 * Establece los parámetros del evento de la notifiación
		 *
		 * @param string $eventParameter
		 *
		 * @return Notification
		 */
		public function setEventParameter ($eventParameter) {
			if (is_scalar ($eventParameter)) {
				$this->eventParameter = $eventParameter;
			} else {
				$this->eventParameter = null;
			}
			return $this;
		}

		/**
		 * Establece el objeto NotificationFilter de una notifiación
		 *
		 * @param NotificationFilter $filter
		 *
		 * @return Notification
		 */
		public function setFilter ($filter) {
			if (($filter == null) || ($filter instanceof NotificationFilter) && (!empty ($filter))) {
				$this->filter = $filter;
			}
			return $this;
		}

		/**
		 * Establece el bloqueo de una notifiación
		 *
		 * @param boolean $locked
		 *
		 * @return Notification
		 */
		public function setLocked ($locked) {
			if ($locked) {
				$this->locked = 1;
			} else {
				$this->locked = 0;
			}
			return $this;
		}

		/**
		 * Establece el objeto NotificationModal de una notifiación
		 *
		 * @param NotificationModal $modal
		 *
		 * @return Notification
		 */
		public function setModal ($modal) {
			if (($modal == null) || ($modal instanceof NotificationModal) && (!empty ($modal))) {
				$this->modal = $modal;
			}
			return $this;
		}

		/**
		 * Establece el módulo fuente de una notifiación
		 *
		 * @param string[] $moduleNames
		 *
		 * @return Notification
		 */
		public function setModuleNames ($moduleNames) {
			if ((is_array ($moduleNames)) && (!empty ($moduleNames))) {
				$this->moduleNames = $moduleNames;
			} else {
				$this->moduleNames = null;
			}
			return $this;
		}

		/**
		 * Establece el nombre de la notifiación
		 *
		 * @param string $name
		 *
		 * @return Notification
		 */
		public function setName ($name) {
			if (is_scalar ($name)) {
				$this->name = $name;
			} else {
				$this->name = null;
			}
			return $this;
		}

		/**
		 * @param string $readyToSend
		 *
		 * @return Notification
		 */
		public function setReadyToSend ($readyToSend) {
			if (in_array($readyToSend, array(self::STATUS_ACTIVE, self::STATUS_INACTIVE))) {
				$this->readyToSend = $readyToSend;
			} else {
				$this->readyToSend = self::STATUS_INACTIVE;
			}
			return $this;
		}

		/**
		 * Establece el estatus de envio al panel de mensajes
		 *
		 * @param string $sendByEmail
		 *
		 * @return Notification
		 */
		public function setSendByEmail ($sendByEmail) {
			if (in_array($sendByEmail, array(self::STATUS_ACTIVE, self::STATUS_INACTIVE))) {
				$this->sendByEmail = $sendByEmail;
			} else {
				$this->sendByEmail = self::STATUS_INACTIVE;
			}
			return $this;
		}

		/**
		 * Establece el alcance de una notifiación
		 *
		 * @param string $scope
		 *
		 * @return Notification
		 */
		public function setScope ($scope) {
			if (in_array ($scope, array (self::FROM_SYSTEM, self::FROM_USERS))) {
				$this->scope = $scope;
			} else {
				$this->scope = null;
			}
			return $this;
		}

		/**
		 * Establece el estatus de una notifiación
		 *
		 * @param string $status
		 *
		 * @return Notification
		 */
		public function setStatus ($status) {
			if (in_array ($status, array (self::STATUS_ACTIVE, self::STATUS_INACTIVE))) {
				$this->status = $status;
			} else {
				$this->status = null;
			}
			return $this;
		}

		/**
		 * Establece el estilo de una notifiación
		 *
		 * @param string $style
		 *
		 * @return Notification
		 */
		public function setStyle ($style) {
			if (in_array ($style, array (self::STYLE_ALERT, self::STYLE_MODAL, self::STYLE_NOTIFY))) {
				$this->style = $style;
			} else {
				$this->style = null;
			}
			return $this;
		}

		/**
		 * Establece el tipo de notifiación
		 *
		 * @param string $type
		 *
		 * @return Notification
		 */
		public function setType ($type) {
			if (in_array ($type, array (self::TYPE_EMAIL, self::TYPE_SCREEN))) {
				$this->type = $type;
			} else {
				$this->type = null;
			}
			return $this;
		}

		/**
		 * Establece la vista donde se mostrará la notifiación
		 *
		 * @param string $view
		 *
		 * @return Notification
		 */
		public function setView ($view) {
			if (in_array ($view, array (self::DETAIL_VIEW, self::EDIT_VIEW, self::LIST_VIEW))) {
				$this->view = $view;
			} else {
				$this->view = null;
			}
			return $this;
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
					$this->action,
					$this->sendByEmail,
					$this->scope,
					$this->contents,
					$this->description,
					$this->event,
					$this->eventParameter,
					$this->filter,
					$this->modal,
					$this->moduleNames,
					$this->name,
					$this->status,
					$this->style,
					$this->type,
					$this->view,
				)
			);
		}

		// @codingStandardsIgnoreStart
		/**
		 * Valida los datos de la notificación
		 *
		 * @throws NotificationException
		 */
		public function validate () {
			if (empty ($this->action)) {
				throw new NotificationException (NotificationException::ERROR_NOTIFICATION_EMPTY_ACTION);
			} else if (empty ($this->contents)) {
				throw new NotificationException (NotificationException::ERROR_NOTIFICATION_EMPTY_CONTENTS);
			} else if (empty ($this->event)) {
				throw new NotificationException (NotificationException::ERROR_NOTIFICATION_EMPTY_EVENT);
			} else if ((in_array ($this->event, array (self::EVENT_TOTAL_RECORDS_REACHED))) && ($this->eventParameter === null)) {
				throw new NotificationException (NotificationException::ERROR_NOTIFICATION_EMPTY_EVENT_PARAMETER);
			} else if (empty ($this->scope)) {
				throw new NotificationException (NotificationException::ERROR_NOTIFICATION_EMPTY_FROM);
			} else if (empty ($this->name)) {
				throw new NotificationException (NotificationException::ERROR_NOTIFICATION_EMPTY_NAME);
			} else if (empty ($this->status)) {
				throw new NotificationException (NotificationException::ERROR_NOTIFICATION_EMPTY_STATUS);
			} else if (empty ($this->style)) {
				throw new NotificationException (NotificationException::ERROR_NOTIFICATION_EMPTY_STYLE);
			} else if ($this->style === self::STYLE_NOTIFY) {
				if (empty ($this->moduleNames)) {
					throw new NotificationException (NotificationException::ERROR_NOTIFICATION_EMPTY_MODULE_NAMES);
				}
				foreach ($this->moduleNames as $moduleName) {
					if (!is_scalar ($moduleName)) {
						throw new NotificationException (NotificationException::ERROR_NOTIFICATION_INVALID_MODULE_NAME);
					}
				}
			} else if (empty ($this->type)) {
				throw new NotificationException (NotificationException::ERROR_NOTIFICATION_EMPTY_TYPE);
			}
		}
		// @codingStandardsIgnoreEnd

		/**
		 * Devuelve los valores de las propiedades a partir de una cadena serializada
		 *
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->action,
				$this->sendByEmail,
				$this->scope,
				$this->contents,
				$this->description,
				$this->event,
				$this->eventParameter,
				$this->filter,
				$this->modal,
				$this->moduleNames,
				$this->name,
				$this->status,
				$this->style,
				$this->type,
				$this->view,
				) = unserialize ($serialized);
		}

		/**
		 * Instanciación de la clase Notification. Se obtiene un objeto Notification con los atributos de la clase
		 *
		 * @return Notification
		 */
		public static function getInstance () {
			return new self ();
		}

	}
