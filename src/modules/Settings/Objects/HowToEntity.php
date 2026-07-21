<?php
    require_once ('modules/Settings/Exceptions/HowToException.php');
    require_once ('modules/Settings/Objects/HowToInterface.php');
    
    class HowToEntity implements HowToInterface {
        
        /** @var integer */
        private $crmId;
        
        /** @var string */
        private $entityTitle;
        
        /** @var string */
        private $file;
        
        /** @var integer */
        private $howToId;
        
        /** @var integer */
        private $id;
        
        /** @var string */
        private $tabName;
    
        /**
         * @return integer
         */
        public function getCrmId () {
            return $this->crmId;
        }
    
        /**
         * @return string
         */
        public function getEntityTitle () {
            return $this->entityTitle;
        }
        
        /**
         * @return string
         */
        public function getFile () {
            return $this->file;
        }
        
        /**
         * @return integer
         */
        public function getHowToId () {
            return $this->howToId;
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
        public function getTabName () {
            return $this->tabName;
        }
    
        /**
         * @param integer $crmId
         *
         * @return HowToEntity
         */
        public function setCrmId ($crmId) {
            $this->crmId = $crmId;
            return $this;
        }
    
        /**
         * @param string $entityTitle
         * @return HowToEntity
         */
        public function setEntityTitle ($entityTitle) {
            $this->entityTitle = $entityTitle;
            return $this;
        }
        
        /**
         * @param string $file
         *
         * @return HowToEntity
         */
        public function setFile ($file) {
            $this->file = $file;
            return $this;
        }
        
        /**
         * @param integer $howToId
         *
         * @return HowToEntity
         */
        public function setHowToId ($howToId) {
            $this->howToId = $howToId;
            return $this;
        }
    
        /**
         * @param integer $id
         *
         * @return HowToEntity
         */
        public function setId ($id) {
            $this->id = $id;
            return $this;
        }
    
        /**
         * @param string $tabName
         */
        public function setTabName ($tabName) {
            $this->tabName = $tabName;
            return $this;
        }
    
        public function validate () {
            if (empty($this->crmId)) {
                throw new HowToException(HowToException::ERROR_EMPTY_CRM_ENTITY);
            }
            if (empty($this->file)) {
                throw new HowToException(HowToException::ERROR_FIELD_EMPTY);
            }
            if (empty($this->howToId)) {
                throw new HowToException(HowToException::ERROR_EMPTY_HOW_TO);
            }
            if (empty($this->tabName)) {
                throw new HowToException (HowToException::ERROR_EMPTY_TAB_NAME);
            }
        }
        
        /**
         * @return HowToEntity
         */
        public static function getInstance () {
            return new self ();
        }
        
    }