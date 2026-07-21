<?php
	require_once ('modules/Settings/Objects/HelpFieldInterface.php');
	require_once ('modules/Settings/Exceptions/HelpFieldException.php');
	
	class HelpField implements HelpFieldInterface {
		
		/** @var string */
		private $description;
		
		/** @var integer */
		private $fieldId;
		
		/** @var string */
		private $fieldLabel;
		
		/** @var string */
		private $fieldName;
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $image;
		
		/** @var string */
		private $isEditable;
		
		/** @var string */
		private $moduleLabel;
		
		/** @var string */
		private $moduleName;
		
		/** @var string */
		private $status;
		
		/** @var string */
		private $title;
		
		/** @var integer */
		private $uiType;
		
		/** @var string */
		private $urlVideo;
		
		/** @var string */
		private $videoType;
		
		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}
		
		/**
		 * @return integer
		 */
		public function getFieldId () {
			return $this->fieldId;
		}
		
		/**
		 * @return string
		 */
		public function getFieldLabel () {
			return $this->fieldLabel;
		}
		
		/**
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
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
		public function getImage () {
			return $this->image;
		}
		
		/**
		 * @return string
		 */
		public function getModuleLabel () {
			return $this->moduleLabel;
		}
		
		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @return string
		 */
		public function getTitle () {
			return $this->title;
		}
		
		/**
		 * @return integer
		 */
		public function getUiType () {
			return $this->uiType;
		}
		
		/**
		 * @return string
		 */
		public function getUrlVideo () {
			return $this->urlVideo;
		}
		
		/**
		 * @return string
		 */
		public function getVideoType () {
			return $this->videoType;
		}
		
		/**
		 * @return string
		 */
		public function isEditable () {
			return $this->isEditable;
		}
		
		/**
		 * @param string $description
		 *
		 * @return HelpField
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param integer $fieldId
		 *
		 * @return HelpField
		 */
		public function setFieldId ($fieldId) {
			$this->fieldId = $fieldId;
			return $this;
		}
		
		/**
		 * @param string $fieldLabel
		 *
		 * @return HelpField
		 */
		public function setFieldLabel ($fieldLabel) {
			$this->fieldLabel = $fieldLabel;
			return $this;
		}
		
		/**
		 * @param string $fieldName
		 *
		 * @return HelpField
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return HelpField
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $image
		 *
		 * @return HelpField
		 */
		public function setImage ($image) {
			$this->image = $image;
			return $this;
		}
		
		/**
		 * @param string $isEditable
		 *
		 * @return HelpField
		 */
		public function setIsEditable ($isEditable) {
			$this->isEditable = $isEditable;
			return $this;
		}
		
		/**
		 * @param string $moduleLabel
		 *
		 * @return HelpField
		 */
		public function setModuleLabel ($moduleLabel) {
			$this->moduleLabel = $moduleLabel;
			return  $this;
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return HelpField
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return  $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return HelpField
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param string $title
		 *
		 * @return HelpField
		 */
		public function setTitle ($title) {
			$this->title = $title;
			return $this;
		}
		
		/**
		 * @param integer $uiType
		 *
		 * @return HelpField
		 */
		public function setUiType ($uiType) {
			$this->uiType = $uiType;
			return $this;
		}
		
		/**
		 * @param string $urlVideo
		 *
		 * @return HelpField
		 */
		public function setUrlVideo ($urlVideo) {
			$this->urlVideo = $urlVideo;
			return $this;
		}
		
		/**
		 * @param string $videoType
		 *
		 * @return HelpField
		 */
		public function setVideoType ($videoType) {
			$this->videoType = $videoType;
			return $this;
		}
		
		/**
		 * @return HelpField
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
