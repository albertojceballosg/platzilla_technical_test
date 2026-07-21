<?php
	require_once ('include/platzilla/Objects/Application.php');

	/**
	 * Class Platform
	 *
	 * En esta clase se define el objeto "Plataforma" el cual hace referencia a la "instancia Madre", con sus atributos definidos.
	 **/
	class Platform {
		/** @var Application[] */
		private $applications;

		/** @var Module[] */
		private $modules;

		/** @var ModuleRelationship[] */
		private $moduleRelationships;
		
		/** @var Systemalerts[] */
		private $SystemAlerts;
		
		/**
		 * Para obtener el listado de aplicaciones disponibles
		 *
		 * @return Application[]
		 */
		public function getApplications () {
			return $this->applications;
		}

		/**
		 * Para obtener el lista de los módulos disponibles
		 *
		 * @return Module[]
		 */
		public function getModules () {
			return $this->modules;
		}

		/**
		 * Para obtener el listado de los módulos relacionados
		 *
		 * @return ModuleRelationship[]
		 */
		public function getModuleRelationships () {
			return $this->moduleRelationships;
		}
		
		/**
		 * @return Systemalerts[]
		 */
		public function getSystemAlerts () {
			return $this->SystemAlerts;
		}
		
		/**
		 * Establece las aplicaciones que lleva una instancia
		 *
		 * @param Application[] $applications
		 *
		 * @return Platform
		 */
		public function setApplications ($applications) {
			$this->applications = $applications;
			return $this;
		}

		/**
		 * Establece los módulos que lleva una instancia
		 *
		 * @param Module[] $modules
		 *
		 * @return Platform
		 */
		public function setModules ($modules) {
			$this->modules = $modules;
			return $this;
		}

		/**
		 * Establece los módulos relacionados que lleva una instancia
		 *
		 * @param ModuleRelationship[] $moduleRelationships
		 *
		 * @return Platform
		 */
		public function setModuleRelationships ($moduleRelationships) {
			$this->moduleRelationships = $moduleRelationships;
			return $this;
		}
		
		/**
		 * @param Systemalerts[] $SystemAlerts
		 * @return Platform
		 */
		public function setSystemAlerts ($SystemAlerts) {
			$this->SystemAlerts = $SystemAlerts;
			return $this;
		}
		
		/**
		 * Valida que la instancia no tenga módulos vacios
		 *
		 * @throws PlatformException
		 */
		public function validate () {
			if (empty ($this->modules)) {
				throw new PlatformException (PlatformException::ERROR_PLATFORM_EMPTY_MODULES);
			}
		}

		/**
		 * Instanciación de la clase Platform. Se obtiene un objeto Platform con los atributos de la clase
		 *
		 * @return Platform
		 */
		public static function getInstance () {
			return new self ();
		}

	}
