<?php

	class ConfigFileRow {
		/** @var string $lineContent Actual line content */
		protected $lineContent;
		/** @var string $parsedVarName */
		protected $parsedVarName = false;
		/** @var string $parsedVarValue */
		protected $parsedVarValue = '';
		/** @var boolean $isValueString Is the variable of string type? */
		protected $isValueString = false;
		/** @var array $alltimeNoneditableVars Some variables which is never editable */
		protected static $alltimeNoneditableVars = array (
			"dbconfig['db_server']",
			'application_unique_key',
		);
		/** @var ConfigFileReader $parent Editable and Viewable variable names */
		protected $parent;
		/** @var boolean $isValueEditable Is the variable value editable? */
		protected $isValueEditable = false;
		/** @var string $variableRegex Regex to detect variable name and its safe value */
		public static $variableRegex = '/^[ \t]*\\$([^=]+)=([^;]+)/';
		/** @var string $variableUnSafeValueRegex Regex to detect support name,it doesnt allow any single quote,and special characters,it does allow only alpha numeric,utf8,.com */
		public static $variableUnSafeValueRegex = "/[\x{4e00}-\x{9fa5}[:print:]]+.*\-/u";

		public function __construct ($content, $parent) {
			$this->lineContent = $content;
			$this->parent      = $parent;
			$this->parse ();
		}

		/**
		 * Parse the content
		 */
		protected function parse () {
			if (preg_match (self::$variableRegex, $this->lineContent, $m)) {
				$this->parsedVarName  = trim ($m[1]);
				$this->parsedVarValue = trim ($m[2]);
				// Is variable string type?
				if (strpos ($this->parsedVarValue, "'") === 0 || strpos ($this->parsedVarValue, '"') === 0) {
					$this->isValueString  = true;
					$this->parsedVarValue = trim ($m[2], "'\" ");
				}
				if (!in_array ($this->parsedVarName, self::$alltimeNoneditableVars)) {
					$this->isValueEditable = true;
				} else {
					$this->isValueEditable = false;
				}
			}
		}

		/**
		 * Does the row represent variable?
		 */
		public function isVariable () {
			return ($this->parsedVarName !== false);
		}

		/**
		 * Is the variable viewable?
		 */
		public function isViewable () {
			if ($this->isVariable ()) {
				$editables = $this->parent->editables ();
				if (!empty($editables)) {
					return in_array ($this->parsedVarName, $this->parent->viewables ());
				} else {
					return true;
				}
			}
			return false;
		}

		/**
		 * Is the variable editable?
		 */
		public function isEditable () {
			if ($this->isVariable ()) {
				$editables = $this->parent->editables ();
				if (empty($editables)) {
					return $this->isValueEditable;
				}
				return ((in_array ($this->parsedVarName, $editables) !== false) && $this->isValueEditable);
			}
			return false;
		}

		/**
		 * Get variable name
		 */
		public function variableName () {
			return $this->parsedVarName;
		}

		public function matchesVariableName ($input) {
			$input = ltrim ($input, '$');
			return ($input == $this->parsedVarName);
		}

		/**
		 * Get variable value
		 */
		public function variableValue () {
			return $this->parsedVarValue;
		}

		/**
		 * Is the variable value string type?
		 */
		public function isValueString () {
			return $this->isValueString;
		}

		public function setVariableValue ($value) {
			if (preg_match (self::$variableUnSafeValueRegex, $value)) {
				return false;
			}
			// Should the value be restricted to a set?
			$meta = $this->meta ();
			if (isset($meta['values']) && is_array ($meta['values'])) {
				$allowedValues = array_keys ($meta['values']);
				if (!empty($allowedValues) && !in_array ($value, $allowedValues)) {
					return false;
				}
			}
			$this->parsedVarValue = $value;
			return true;
		}

		/**
		 * Get the meta information
		 */
		public function meta () {
			if ($this->isEditable ()) {
				return $this->parent->editables ($this->parsedVarName);
			}
			if ($this->isViewable ()) {
				return $this->parent->viewables ($this->parsedVarName);
			}
			return false;
		}

		/**
		 * String representation of the instance
		 */
		public function toString () {
			if ($this->isVariable ()) {
				$encloseWith = '';
				if ($this->isValueString ()) {
					$encloseWith = "'";
				}
				return sprintf ('\$%s = %s%s%s;', $this->parsedVarName, $encloseWith, $this->parsedVarValue, $encloseWith);
			}
			return $this->lineContent;
		}

	}
