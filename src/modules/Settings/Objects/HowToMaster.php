<?php
    require_once ('modules/Settings/Exceptions/HowToException.php');
    require_once ('modules/Settings/Objects/HowToEntity.php');
    require_once ('modules/Settings/Objects/HowToInterface.php');
    
    class HowToMaster implements HowToInterface  {
        
        /** @var HowToEntity[] */
        private $entity;
        
        /** @var string */
        private $html;
        
        /** @var integer */
        private $id;
    
        /** @var string */
        private $image;
    
        /** @var string */
        private $status;
        
        /** @var string */
        private $title;
        
        /** @var string */
        private $url;
        
        /** @var string */
        private $video;
        
        /** @var string */
        private $videoType;
    
        /**
         * @param string $url
         *
         * @return boolean
         */
        private function validateUrl ($url) {
            $path        = parse_url ($url, PHP_URL_PATH);
            $encodedPath = array_map ('urlencode', explode ('/', $path));
            $url         = str_replace ($path, implode ('/', $encodedPath), $url);
            return filter_var ($url, FILTER_VALIDATE_URL) ? true : false;
        }
        /**
         * @return HowToEntity[]
         */
        public function getEntity () {
            return $this->entity;
        }
    
        /**
         * @return string
         */
        public function getHtml () {
            return $this->html;
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
         * @return string
         */
        public function getUrl () {
            return $this->url;
        }
    
        /**
         * @return string
         */
        public function getVideo () {
            return $this->video;
        }
    
        /**
         * @return string
         */
        public function getVideoType () {
            return $this->videoType;
        }
        
        /**
         * @param HowToEntity[] $entity
         *
         * @return HowToMaster
         */
        public function setEntity ($entity) {
            $this->entity = $entity;
            return $this;
        }
    
        /**
         * @param $html
         *
         * @return HowToMaster
         */
        public function setHtml ($html) {
            $this->html = $html;
            return $this;
        }
    
        /**
         * @param integer $id
         *
         * @return HowToMaster
         */
        public function setId ($id) {
            $this->id = $id;
            return $this;
        }
    
        /**
         * @param string $image
         *
         * @return HowToMaster
         */
        public function setImage ($image) {
            $this->image = $image;
            return $this;
        }
    
        /**
         * @param string $status
         *
         * @return HowToMaster
         */
        public function setStatus ($status) {
            if (in_array ($status, array_keys (self::HOW_TO_STATUS))) {
                $this->status = $status;
            } else {
                $this->status = self::HOW_TO_STATUS [1];
            }
            return $this;
        }
    
        /**
         * @param string $title
         *
         * @return HowToMaster
         */
        public function setTitle ($title) {
            $this->title = $title;
            return $this;
        }
    
        /**
         * @param string $url
         *
         * @return HowToMaster
         */
        public function setUrl ($url) {
            $this->url = $url;
            return $this;
        }
    
        /**
         * @param string $video
         *
         * @return HowToMaster
         */
        public function setVideo ($video) {
            $this->video = $video;
            return $this;
        }
    
        /**
         * @param string $videoType
         *
         * @return HowToMaster
         */
        public function setVideoType ($videoType) {
            if (in_array($videoType, self::HOW_TO_FIELD_TYPE_VIDEO)) {
                $this->videoType = $videoType;
            } else {
                $this->videoType = null;
            }
            return $this;
        }
        
        /**
         * @return void
         * @throws HowToException
         */
        public function validate () {
            if (!empty ($this->video)) {
                if (!$this->validateUrl ($this->video)) {
                    throw new HowToException (HowToException::ERROR_WRONG_VIDEO_URL);
                }
                if (empty ($this->videoType)) {
                    throw new HowToException (HowToException::ERROR_EMPTY_VIDEO_TYPE);
                }
            }
            if (empty($this->title)) {
                throw new HowToException (HowToException::ERROR_EMPTY_TITLE);
            }
        }
        
        /**
         * @return HowToMaster
         */
        public static function getInstance () {
            return new self ();
        }
        
    }
