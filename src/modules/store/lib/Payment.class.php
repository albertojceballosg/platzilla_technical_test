<?php

	class Payment {
		const STATUS_CANCELLED = 'CANCELLED';
		const STATUS_PAST_DUE  = 'PAST DUE';
		const STATUS_PAID      = 'PAID';
		const STATUS_PENDING   = 'PENDING';
		const STATUS_REJECTED  = 'REJECTED';
		const STATUS_SUBMITTED = 'SUBMITTED';

		const TYPE_SUBSCRIPTION = 'SUBSCRIPTION';
		const TYPE_TRANSACTION  = 'TRANSACTION';

		/** @var double */
		private $amount;

		/** @var string */
		private $description;

		/** @var DateTime */
		private $dueDate;

		/** @var string */
		private $gatewayId;

		/** @var string */
		private $instanceCode;

		/** @var string */
		private $lastErrorMessage;

		/** @var string */
		private $status;

		/** @var array */
		private $services;

		/** @var string */
		private $type;

		// Getters

		/**
		 * Obtiene la cantidad
		 *
		 * @return float
		 */
		public function getAmount () {
			return $this->amount;
		}

		/**
		 * Obtiene la descripción
		 *
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * Obtiene la fecha de vencimiento
		 *
		 * @return DateTime
		 */
		public function getDueDate () {
			return $this->dueDate;
		}

		/**
		 * Obtiene el ID del pago en la pasarela de pagos
		 *
		 * @return string
		 */
		public function getGatewayId () {
			return $this->gatewayId;
		}

		/**
		 * Obtiene el código de la instancia
		 *
		 * @return string
		 */
		public function getInstanceCode () {
			return $this->instanceCode;
		}

		/**
		 * Obtiene el último mensaje de error
		 *
		 * @return string
		 */
		public function getLastErrorMessage () {
			return $this->lastErrorMessage;
		}

		/**
		 * @codingStandardsIgnoreStart
		 * Obtiene la lista de servicios asociados al pago en el siguiente formato
		 *  array (
		 *      id del servicio => array (
		 *          'servicename'   => Nombre,
		 *          'description'   => Descripción,
		 *          'quantity'      => Cantidad,
		 *          'listprice'     => Precio de lista,
		 *          'taxpercentage' => Porcentaje de impuesto,
		 *          'tax'           => Monto de impuesto,
		 *          'finalprice'    => Precio final,
		 *      )
		 *  )
		 * @return array
		 * @codingStandardsIgnoreEnd
		 */
		public function getServices () {
			return $this->services;
		}

		/**
		 * Obtiene el status del pago
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Obtiene el tipo de pago
		 *
		 * @return string
		 */
		public function getType () {
			return $this->type;
		}

		// Setters

		/**
		 * Establece el monto del pago
		 *
		 * @param float $amount
		 *
		 * @return Payment
		 * @throws Exception Si el parámetro suministrado no es un número válido
		 */
		public function setAmount ($amount) {
			if ((!is_numeric ($amount)) || ($amount <= 0)) {
				throw new Exception ('La cantidad suministrada no es un número válido');
			}
			$this->amount = doubleval ($amount);
			return $this;
		}

		/**
		 * Establece la descripción
		 *
		 * @param string $description
		 *
		 * @return Payment
		 * @throws Exception Si el parámetro suministrado es vacío
		 */
		public function setDescription ($description) {
			if (empty ($description)) {
				throw new Exception ('No se ha suministrado la descripción');
			}
			$this->description = $description;
			return $this;
		}

		/**
		 * Establece la fecha de vencimiento
		 *
		 * @param string|DateTime $dueDate
		 *
		 * @return Payment
		 * @throws Exception Si el parámetro suministrado es vacío o no es una fecha válida
		 */
		public function setDueDate ($dueDate) {
			if (!isset ($dueDate)) {
				throw new Exception ('No se ha suministrado la fecha de vencimiento');
			} else if ((is_object ($dueDate)) && (!($dueDate instanceof DateTime)) && (!is_string ($dueDate))) {
				throw new Exception ('La fecha de vencimiento suministrada no es válida');
			}

			if (is_string ($dueDate)) {
				$this->dueDate = date_create ($dueDate);
			} else {
				$this->dueDate = $dueDate;
			}
			return $this;
		}

		/**
		 * Establece el ID del pago en la pasarela de pagos
		 *
		 * @param string $gatewayId
		 *
		 * @return Payment
		 * @throws Exception Si el parámetro suministrado no es un string
		 */
		public function setGatewayId ($gatewayId) {
			if ((isset ($gatewayId)) && (!is_string ($gatewayId))) {
				throw new Exception ('El identificador de la plataforma de pagos suministrado no es válido');
			}
			$this->gatewayId = $gatewayId;
			return $this;
		}

		/**
		 * Establece el código de la instancia
		 *
		 * @param string $instanceCode
		 *
		 * @return Payment
		 * @throws Exception Si el parámetro suministrado es vacío
		 */
		public function setInstanceCode ($instanceCode) {
			if (empty ($instanceCode)) {
				throw new Exception ('No se ha suministrado el código de la instancia');
			}
			$this->instanceCode = $instanceCode;
			return $this;
		}

		/**
		 * Establece el último mensaje de error
		 *
		 * @param string $lastErrorMessage
		 *
		 * @return Payment
		 */
		public function setLastErrorMessage ($lastErrorMessage) {
			$this->lastErrorMessage = $lastErrorMessage;
			return $this;
		}

		/**
		 * Establece el status
		 *
		 * @param string $status
		 *
		 * @return Payment
		 * @throws Exception Si el parámetro suministrado no es un status válido
		 */
		public function setStatus ($status) {
			$availableStatuses = self::getAvailableStatuses ();
			if (((isset ($status)) && (!is_string ($status))) || (!in_array ($status, $availableStatuses))) {
				throw new Exception ('El status suministrado no es válido');
			}
			$this->status = $status;
			return $this;
		}

		/**
		 * @codingStandardsIgnoreStart
		 * Establece la lista de servicios asociados al pago en el siguiente formato
		 *  array (
		 *      id del servicio => array (
		 *          'servicename'   => Nombre,
		 *          'description'   => Descripción,
		 *          'quantity'      => Cantidad,
		 *          'listprice'     => Precio de lista,
		 *          'taxpercentage' => Porcentaje de impuesto,
		 *          'tax'           => Monto de impuesto,
		 *          'finalprice'    => Precio final,
		 *      )
		 *  )
		 * @codingStandardsIgnoreEnd
		 *
		 * @param array $services
		 *
		 * @return Payment
		 * @throws Exception Si el parámetro suministrado no es un arreglo válido
		 */
		public function setServices ($services) {
			if ((!is_array ($services)) || (empty ($services))) {
				throw new Exception ('El valor suministrado de los servicios no es un arreglo o está vacío');
			}
			$this->services = $services;
			return $this;
		}

		/**
		 * Establece el tipo
		 *
		 * @param string $type
		 *
		 * @return Payment
		 * @throws Exception Si el parámetro suministrado no es un tipo válido
		 */
		public function setType ($type) {
			$availableTypes = self::getAvailableTypes ();
			if (((isset ($type)) && (!is_string ($type))) || (!in_array ($type, $availableTypes))) {
				throw new Exception ('El tipo suministrado no es válido');
			}
			$this->type = $type;
			return $this;
		}

		// Utils

		/**
		 * Determina si el pago está pendiente basado en el status
		 *
		 * @return boolean
		 */
		public function isPending () {
			return in_array ($this->status, array (self::STATUS_PENDING, self::STATUS_PAST_DUE, self::STATUS_REJECTED));
		}

		/**
		 * Valida que el objeto tenga todos los valores requeridos
		 *
		 * @throws Exception
		 */
		public function validate () {
			if (empty ($this->amount)) {
				throw new Exception ('No se ha suministrado la cantidad');
			} else if (empty ($this->description)) {
				throw new Exception ('No se ha suministrado la descripción');
			} else if (empty ($this->dueDate)) {
				throw new Exception ('No se ha suministrado la fecha de vencimiento');
			} else if (empty ($this->instanceCode)) {
				throw new Exception ('No se ha suministrado el código de la instancia');
			} else if (empty ($this->services)) {
				throw new Exception ('No se han suministrado los ID de los servicios');
			} else if (empty ($this->status)) {
				throw new Exception ('No se ha suministrado el status');
			} else if (empty ($this->type)) {
				throw new Exception ('No se ha suministrado el tipo');
			}
		}

		// Static utils

		/**
		 * Obtiene todos los status permitidos
		 *
		 * @return array
		 */
		public static function getAvailableStatuses () {
			return array (self::STATUS_CANCELLED, self::STATUS_PENDING, self::STATUS_PAID, self::STATUS_PAST_DUE, self::STATUS_REJECTED, self::STATUS_SUBMITTED);
		}

		/**
		 * Obtiene todos los tipos permitidos
		 *
		 * @return array
		 */
		public static function getAvailableTypes () {
			return array (self::TYPE_SUBSCRIPTION, self::TYPE_TRANSACTION);
		}

		/**
		 * Crea un nuevo objeto Payment. Útil para encadenar métodos
		 *
		 * @return Payment
		 */
		public static function getInstance () {
			return new self ();
		}

	}
