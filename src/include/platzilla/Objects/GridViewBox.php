<?php
	require_once ('include/platzilla/Objects/BoxContent.php');

	/**
	 * Class GridViewBox
	 *
	 * En esta clase se define el objeto Cuadro de la Vista Cuadricula, el cual hace referencia a los cuadros, que conforman la Vista Cuadricula, que se pueden configurar en Platzilla, para los modulos
	 */
	class GridViewBox implements GridViewInterface {

		/** @var BoxContent */
		private $boxContenet;

		/** @var string */
		private $boxType;

		/** @var string */
		private $gridViewName;

		/** @var integer */
		private $id;

		/** @var integer */
		private $presence;

		/** @var integer */
		private $sequence;

		/**
		 * Para copiar los valores de un cuadro de la vista cuadricula a otro
		 *
		 * @param GridViewBox $gridViewBox
		 */
		public function copyValuesFrom ($gridViewBox) {
			if ((empty ($gridViewBox)) || (!($gridViewBox instanceof GridViewBox))) {
				return;
			}
			$this->id           = $gridViewBox->getId ();
			$this->boxType      = $gridViewBox->getBoxType ();
			$this->gridViewName = $gridViewBox->getGridViewName ();
			$this->presence     = $gridViewBox->getPresence ();
			$this->sequence     = $gridViewBox->getSequence ();
		}

		/**
		 * Para duplicar cuadros de la vista cuadricula
		 *
		 * @param null $gridViewBoxIs
		 *
		 * @return \GridViewBox
		 */
		public function duplicate ($gridViewBoxIs = null) {
			$object = new self ();
			return $object->setId ($gridViewBoxIs)
				->setBoxType ($this->boxType)
				->setGridViewName ($this->gridViewName)
				->setPresence ($this->presence)
				->setSequence ($this->sequence);
		}

		/**
		 * Para obtener el contenido del cuadro de la vista cuadricula
		 *
		 * @return BoxContent
		 */
		public function getBoxContenet () {
			return $this->boxContenet;
		}

		/**
		 * Obtiene el tipo de cuadro para la vista cuadricula
		 *
		 * @return string
		 */
		public function getBoxType () {
			return $this->boxType;
		}

		/**
		 * Obtiene el nombre de la vista cuadricula del objeto cuadro
		 *
		 * @return string
		 */
		public function getGridViewName () {
			return $this->gridViewName;
		}

		/**
		 * Obtiene el Id de la vista cuadricula del objeto cuadro
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener la presencia del cuadro en la vista cuadricula
		 *
		 * @return integer
		 */
		public function getPresence () {
			return $this->presence;
		}

		/**
		 * Para obtener la secuencia del cuadro de la vista cuadricula
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para comparar si 2 cuadros de la vista cuadricula son iguales
		 *
		 * @param GridViewBox $gridViewBox
		 *
		 * @return boolean
		 */
		public function isEqualTo ($gridViewBox) {
			if (
				(empty ($gridViewBox)) ||
				($this->boxType != $gridViewBox->getBoxType ()) ||
				($this->gridViewName != $gridViewBox->getGridViewName ()) ||
				($this->sequence != $gridViewBox->getSequence ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Establece el contenido de la caja de la vista cuadricula
		 *
		 * @param BoxContent $boxContenet
		 *
		 * @return GridViewBox
		 */
		public function setBoxContenet ($boxContenet) {
			if ($boxContenet instanceof BoxContent) {
				$this->boxContenet = $boxContenet;
			} else {
				$this->boxContenet = null;
			}
			return $this;
		}

		/**
		 * Establece el tipo de caja para la vista cuadricula
		 *
		 * @param string $boxType
		 *
		 * @return GridViewBox
		 */
		public function setBoxType ($boxType) {
			$this->boxType = $boxType;
			return $this;
		}

		/**
		 * Establece el nombre de la vista cuadricula donde pertenece el cuadro
		 *
		 * @param string $gridViewName
		 *
		 * @return GridViewBox
		 */
		public function setGridViewName ($gridViewName) {
			$this->gridViewName = $gridViewName;
			return $this;
		}

		/**
		 * Establece el Id del cuadro de la vista cuadricula
		 *
		 * @param integer $id
		 *
		 * @return GridViewBox
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece la presencia del cuadro para la vista cuadricula
		 *
		 * @param integer $presence
		 *
		 * @return GridViewBox
		 */
		public function setPresence ($presence) {
			$this->presence = $presence;
			return $this;
		}

		/**
		 * Establece la secuencia del cuadro para la vista cuadricula
		 *
		 * @param integer $sequence
		 *
		 * @return GridViewBox
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * Valida que los atributos del objeto Cuadro de la Vista Cuadricula, estén correctamente definidos
		 *
		 * @throws GridViewException
		 */
		public function validate () {
			if (empty ($this->gridViewName)) {
				throw new GridViewException (GridViewException::ERROR_GRID_VIEW_EMPTY_NAME);
			}
		}

		/**
		 * Instanciación de la clase Cuadro de la Vista Cuadricula. Se obtiene un objeto Cuadro de la Vista Cuadricula con los atributos de la clase
		 *
		 * @return GridViewBox
		 */
		public static function getInstance () {
			return new self ();
		}

	}
