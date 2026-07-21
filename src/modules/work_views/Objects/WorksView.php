<?php
	require_once ('modules/work_views/Objects/WorksViewException.php');
	require_once ('modules/work_views/Objects/WorksViewInterface.php');
	class WorksView implements WorksViewInterface{
		
		/** @var integer */
		private $formUser;
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $view;
		
		/** @var string */
		private $viewStatus;
		
		/**
		 * @return integer
		 */
		public function getFormUser () {
			return $this->formUser;
		}
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return string
		 */
		public function getView () {
			return $this->view;
		}
		
		/**
		 * @return string
		 */
		public function getViewStatus () {
			return $this->viewStatus;
		}
		
		/**
		 * @param integer $formUser
		 *
		 * @return WorksView
		 */
		public function setFormUser ($formUser) {
			$this->formUser = $formUser;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return WorksView
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $view
		 *
		 * @return WorksView
		 */
		public function setView ($view) {
			if (in_array ($view, array_keys (WorksViewInterface::VIEWS))){
				$this->view = $view;
			} else {
				$this->view = null;
			}
			return $this;
		}
		
		/**
		 * @param string $viewStatus
		 *
		 * @return WorksView
		 */
		public function setViewStatus ($viewStatus) {
			if (in_array ($viewStatus, array_keys (WorksViewInterface::VIEWS_STATUS))){
				$this->viewStatus = $viewStatus;
			} else {
				$this->viewStatus = null;
			}
			return $this;
		}
		
		
		/**
		 * @throws WorksViewException
		 */
		public function validate () {
			if(empty($this->view)) {
				throw new WorksViewException(WorksViewException::VIEW_WORK_EMPTY);
			} else if (empty($this->viewStatus)) {
				throw new WorksViewException(WorksViewException::VIEW_STATUS_EMPTY);
			}
		}
		
		/**
		 * @return WorksView
		 */
		public static function getInstance () {
			return new self ();
		}
	}
