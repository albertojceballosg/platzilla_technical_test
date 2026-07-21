<?php
	require_once ('include/platzilla/Objects/GridViewBox.php');

	/**
	 * Class GridView
	 *
	 * En esta clase se define el objeto Vista Cuadricula, el cual hace referencia a las Vistas tipo Cuadricula, que se pueden configurar en Platzilla, para los modulos
	 */
	class GridView implements GridViewInterface {

		/** @var datetime */
		private $createDate;

		/** @var GridViewBox[] */
		private $gridViewBox;

		/** @var string */
		private $gridViewName;

		/** @var integer */
		private $id;

		/** @var string */
		private $label;

		/** @var integer */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $position;

		/** @var string */
		private $status;

		/** @var string */
		private $tabName;

		/**
		 * Para copiar cuadros de la vista cuadricula
		 *
		 * @param GridViewBox[] $gridViewBoxes
		 *
		 * @return GridViewBox[]|null
		 */
		private function copyGridViewBoxes ($gridViewBoxes) {
			if (empty($gridViewBoxes)) {
				return null;
			}
			$boxes = array();
			foreach ($gridViewBoxes as $gridViewBox) {
				if (empty($gridViewBox) || !$gridViewBox instanceof GridViewBox) {
					continue;
				}
				$boxes [] = $gridViewBox->duplicate ();
			}
			return (count ($boxes)) ? $boxes : null;
		}

		/**
		 * Para copiar los valores desde una vista cuadricula a otra
		 *
		 * @param GridView $gridView
		 */
		public function copyValuesFrom ($gridView) {
			if ((empty ($gridView)) || (!($gridView instanceof GridView))) {
				return;
			}
			$this->id           = $gridView->getId ();
			$this->gridViewBox  = $this->copyGridViewBoxes ($gridView->getGridViewBox());
			$this->createDate   = $gridView->getCreateDate ();
			$this->gridViewName = $gridView->getGridViewName();
			$this->label        = $gridView->getLabel ();
			$this->locked       = $gridView->getLocked ();
			$this->moduleName   = $gridView->getModuleName ();
			$this->position     = $gridView->getPosition ();
			$this->status       = $gridView->getStatus();
		}

		/**
		 * Para duplicar desde las cajas de las vista cuadricula
		 *
		 * @param GridViewBox[] $theseGridViewBoxes
		 *
		 * @return GridViewBox[]
		 */
		private function duplicateFromGridBoxes ($theseGridViewBoxes) {
			$gridViewBoxes = array ();
			foreach ($theseGridViewBoxes as $thisGridViewBoxes) {
				$gridViewBoxes [] = $thisGridViewBoxes->duplicate ();
			}
			return $gridViewBoxes;
		}

		/**
		 * Compara si dos vistas cuadriculas son iguales
		 *
		 * @param $theseGridViewBoxes
		 * @param $thoseGridViewBoxes
		 *
		 * @return boolean
		 */
		private function isGridViewBoxEqualTo ($theseGridViewBoxes, $thoseGridViewBoxes) {
			$totalGridViewBoxes = count ($theseGridViewBoxes);
			$equals          = true;
			if ($totalGridViewBoxes != count ($thoseGridViewBoxes)) {
				return false;
			}

			for ($k = 0; $k < $totalGridViewBoxes; $k++) {
				if (!$theseGridViewBoxes [ $k ]->isEqualTo ($thoseGridViewBoxes [ $k ])) {
					$equals = false;
				}
			}
			return $equals;
		}

		/**
		 * Para duplicar una vista cuadricula
		 *
		 * @param null $gridViewId
		 *
		 * @return \GridView
		 */
		public function duplicate ($gridViewId = null) {
			$object = new self ();
			return $object->setId ($gridViewId)
				->setCreateDate ($this->createDate)
				->setGridViewBox ($this->duplicateFromGridBoxes ($this->gridViewBox))
				->setGridViewName ($this->gridViewName)
				->setLabel ($this->label)
				->setLocked ($this->locked)
				->setModuleName ($this->moduleName)
				->setPosition ($this->position)
				->setStatus ($this->status)
				->setTabName ($this->tabName);
		}

		/**
		 * Obtiene la fecha de creación de la vista cuadricula
		 *
		 * @return datetime
		 */
		public function getCreateDate () {
			return $this->createDate;
		}

		/**
		 * Obtiene las cajas que conforman la vista cuadricula
		 *
		 * @return GridViewBox[]
		 */
		public function getGridViewBox () {
			return $this->gridViewBox;
		}

		/**
		 * Obtiene el nombre de la vista cuadricula
		 *
		 * @return string
		 */
		public function getGridViewName () {
			return $this->gridViewName;
		}

		/**
		 * Obtiene el Id de la vista cuadricula
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Obtiene la etiqueta de la vista cuadricula
		 *
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}

		/**
		 * Obtiene el nombre del modulo para configurar la vista cuadricula
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return string
		 */
		public function getPosition () {
			return $this->position;
		}

		/**
		 * Para obtener el Status de la vista cuadricula
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Para obtener el nombre de la pestaña de la vista cuadricula
		 *
		 * @return string
		 */
		public function getTabName () {
			return $this->tabName;
		}

		/**
		 * @return integer
		 */
		public function getLocked () {
			return $this->locked;
		}

		/**
		 * Para comparar si 2 vistas cuadriculas son iguales
		 *
		 * @param GridView $gridView
		 *
		 * @return boolean
		 */
		public function isEqualTo ($gridView) {
			if (
				(empty ($gridView)) ||
				($this->isGridViewBoxEqualTo ($this->gridViewBox, $gridView->getGridViewBox ())) ||
				($this->gridViewName != $gridView->getGridViewName ()) ||
				($this->label != $gridView->getLabel ()) ||
				($this->moduleName != $gridView->getModuleName ()) ||
				($this->tabName != $gridView->getTabName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Establece la fecha de creación para la vista cuadricula
		 *
		 * @param datetime $createDate
		 *
		 * @return GridView
		 */
		public function setCreateDate ($createDate) {
			$this->createDate = $createDate;
			return $this;
		}

		/**
		 * Para establecer cuadros de la vista cuadricula
		 *
		 * @param GridViewBox[] $gridViewBoxes
		 *
		 * @return GridView
		 */
		public function setGridViewBox ($gridViewBoxes) {
			foreach ($gridViewBoxes as $gridViewBox) {
				if (!$gridViewBox instanceof GridViewBox) {
					continue;
				}
				$this->gridViewBox[] = $gridViewBox;
			}
			return $this;
		}

		/**
		 * Para establecer el nombre de la vista cuadricula
		 *
		 * @param string $gridViewName
		 *
		 * @return GridView
		 */
		public function setGridViewName ($gridViewName) {
			$this->gridViewName = $gridViewName;
			return $this;
		}

		/**
		 * Establece el ID de la vista cuadricula
		 *
		 * @param integer $id
		 *
		 * @return GridView
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece la etiqueta para la vista cuadricula
		 *
		 * @param string $label
		 *
		 * @return GridView;
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}
		
		/**
		 * Establece el nombre del modulo para la vista cuadricula
		 *
		 * @param integer $moduleName
		 *
		 * @return GridView
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param string $position
		 *
		 * @return GridView
		 */
		public function setPosition ($position) {
			$availablePosition = array_keys (GridViewInterface::GRID_VIEW_POSITION);
			if (in_array ($position, $availablePosition)) {
				$this->position = $position;
			} else {
				$this->position = $availablePosition[ 1 ];
			}
			return $this;
		}

		/**
		 * Para establecer el status de la vista cuadricula
		 *
		 * @param string $status
		 *
		 * @return GridView
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}

		/**
		 * Para establecer el nombre de la pestaña de la vista cuadricula
		 *
		 * @param string $tabName
		 *
		 * @return GridView
		 */
		public function setTabName($tabName) {
			$this->tabName = $tabName;
			return $this;
		}

		/**
		 * @param integer $locked
		 *
		 * @return GridView
		 */
		public function setLocked ($locked) {
			$this->locked = $locked;
			return $this;
		}

		/**
		 * Valida que los atributos establecidos para el objeto Vista Cuadricula, esten definidos correctamente
		 *
		 * @throws GridViewException
		 */
		public function validate () {
			if (empty ($this->gridViewName)) {
				throw new GridViewException (GridViewException::ERROR_GRID_VIEW_EMPTY_NAME);
			} else if (empty ($this->tabName)) {
				throw new GridViewException (GridViewException::ERROR_GRID_VIEW_EMPTY_MODULE_NAME);
			}
		}

		/**
		 * Instanciación de la clase Vista Cuadricula. Se obtiene un objeto Vista Cuadricula con los atributos de la clase
		 *
		 * @return GridView
		 */
		public static function getInstance () {
			return new self ();
		}

	}
