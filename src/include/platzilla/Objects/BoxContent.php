<?php
	require_once ('include/platzilla/Exceptions/GridViewException.php');
	require_once ('include/platzilla/Objects/GridViewInterface.php');

	/**
	 * Class BoxContent
	 *
	 * En esta clase se define el objeto BoxContent, el cual hace referencia a las actividades que son de uso exclusivo para la Vista Cuadricula
	 */
	class BoxContent implements GridViewInterface {

		/** @var string */
		private $action;

		/** @var string */
		private $attributes;

		/** @var BoxTaskContent[] */
		private $content;

		/** @var integer */
		private $id;

		/** @var string */
		private $label;

		/** @var string */
		private $name;

		/** @var string */
		private $script;

		/**
		 * Obtiene la acción para el objeto BoxContent
		 *
		 * @return string
		 */
		public function getAction () {
			return $this->action;
		}

		/**
		 * Obtiene los atributos del objeto BoxContent
		 *
		 * @return string
		 */
		public function getAttributes () {
			return $this->attributes;
		}

		/**
		 * Obtiene el contenido para el objeto BoxContent
		 *
		 * @return BoxTaskContent[]
		 */
		public function getContent () {
			return $this->content;
		}

		/**
		 * Obtiene el Id del objeto BoxContent
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Obtiene la etiqueta para el objeto BoxContent
		 *
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}

		/**
		 * Obtiene el nombre para el objeto BoxContent
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener el guion para el objeto BoxContent
		 *
		 * @return string
		 */
		public function getScript () {
			return $this->script;
		}

		/**
		 * Establece la accion para al objeto BoxContent
		 *
		 * @param string $action
		 *
		 * @return BoxContent
		 */
		public function setAction ($action) {
			$this->action = $action;
			return $this;
		}

		/**
		 * Establece los atributos para el objeto BoxContent
		 *
		 * @param string $attributes
		 *
		 * @return BoxContent
		 */
		public function setAttributes ($attributes) {
			$this->attributes = $attributes;
			return $this;
		}

		/**
		 * Establece el contenido para el objeto BoxContent
		 *
		 * @param BoxTaskContent[] $content
		 *
		 * @return BoxContent
		 */
		public function setContent ($content) {
			$this->content = $content;
			return $this;
		}

		/**
		 * Establece el Id para el objeto BoxContent
		 *
		 * @param integer $id
		 *
		 * @return BoxContent
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece la etiqueta para el objeto BoxContent
		 *
		 * @param integer $label
		 *
		 * @return BoxContent
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * Establece el nombre para el objeto BoxContent
		 *
		 * @param string $name
		 *
		 * @return BoxContent
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece secuencia para el objeto BoxContent
		 *
		 * @param string $script
		 *
		 * @return BoxContent
		 */
		public function setScript ($script) {
			$this->script = $script;
			return $this;
		}

		/**
		 * Valida que los atributos establecidos para el objeto BoxContent, esten definidos correctamente
		 *
		 * @throws GridViewException
		 */
		public function validate () {
			if (empty ($this->name)) {
				throw new GridViewException (GridViewException::ERROR_GRID_VIEW_EMPTY_BOX_NAME);
			} else if (empty ($this->label)) {
				throw new GridViewException (GridViewException::ERROR_GRID_VIEW_EMPTY_BOX_LABEL);
			}
		}

		/**
		 * Instanciación de la clase BoxContent. Se obtiene un objeto BoxContent con los atributos de la clase
		 *
		 * @return BoxContent
		 */
		public static function getInstance () {
			return new self ();
		}

	}
