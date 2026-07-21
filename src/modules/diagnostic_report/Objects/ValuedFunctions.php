<?php

    class ValuedFunctions {

        /** @var string */
        private $barColor;

        /** @var integer */
        private $crmId;

        /** @var string */
        private $description;

        /** @var integer */
        private $functionId;

        /** @var string */
        private $functionLabel;

        /** @var string */
        private $functionName;

        /** @var integer */
        private $functionValue;
        
        /** @var string */
        private $question;

        /** @var string */
        private $surveyCod;

        /**
         * @return string
         */
        public function getBarColor () {
            return $this->barColor;
        }

        /**
         * @return integer
         */
        public function getCrmId () {
            return $this->crmId;
        }

        /**
         * @return string
         */
        public function getDescription () {
            return $this->description;
        }

        /**
         * @return integer
         */
        public function getFunctionId () {
            return $this->functionId;
        }

        /**
         * @return string
         */
        public function getFunctionLabel() {
            return $this->functionLabel;
        }

        /**
         * @return string
         */
        public function getFunctionName () {
            return $this->functionName;
        }

        /**
         * @return integer
         */
        public function getFunctionValue () {
            return $this->functionValue;
        }
    
        /**
         * @return string
         */
        public function getQuestion () {
            return $this->question;
        }
        
        /**
         * @return string
         */
        public function getSurveyCod () {
            return $this->surveyCod;
        }

        /**
         * @param integer $functionValue
         *
         * @return ValuedFunctions
         */
        public function setBarColor($functionValue) {
            $value = intval ($functionValue);
            $barColor = '#cc0000';
            if ($value > 10 && $value <= 25) {
                $barColor = '#ff0000';
            } else if ($value > 25 && $value <= 45) {
                $barColor = '#FFA500';
            } else if ($value > 45 && $value <= 65) {
                $barColor = '#ffff00';
            } else if ($value > 65 && $value <= 85) {
                $barColor = '#86c222';
            } else if ($value > 85) {
                $barColor = '#008000';
            }
            $this->barColor = $barColor;
            return $this;
        }


        /**
         * @param integer $crmId
         *
         * @return ValuedFunctions
         */
        public function setCrmId ($crmId) {
            $this->crmId = $crmId;
            return $this;
        }

        /**
         * @param string $description
         *
         * @return ValuedFunctions
         */
        public function setDescription ($description) {
            $this->description = $description;
            return $this;
        }

        /**
         * @param string $functionId
         *
         * @return ValuedFunctions
         */
        public function setFunctionId ($functionId) {
            $this->functionId = $functionId;
            return $this;
        }

        /**
         * @param string $functionLabel
         *
         * @return ValuedFunctions
         */
        public function setFunctionLabel ($functionLabel) {
            $this->functionLabel = $functionLabel;
            return $this;
        }

        /**
         * @param string $functionName
         *
         * @return ValuedFunctions
         */
        public function setFunctionName ($functionName) {
            $this->functionName = $functionName;
            return $this;
        }

        /**
         * @param integer $functionValue
         *
         * @return ValuedFunctions
         */
        public function setFunctionValue ($functionValue) {
            $this->functionValue = $functionValue;
            return $this;
        }
    
        /**
         * @param $question
         *
         * @return ValuedFunctions
         */
        public function setQuestion ($question) {
            $this->question = $question;
            return $this;
        }
        
        /**
         * @param string $surveyCod
         *
         * @return ValuedFunctions
         */
        public function setSurveyCod ($surveyCod) {
            $this->surveyCod = $surveyCod;
            return $this;
        }

        /**
         * @return ValuedFunctions
         */
        public static function getInstance () {
            return new self();
        }
    }
