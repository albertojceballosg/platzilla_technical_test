<?php
	require_once (__DIR__ . '/ConfigFileRow.php');

	class ConfigFileReader {
		/** @var array $rows Each line is treated as configuration row */
		protected $rows;
		/** @var string $filepath Path to configuration file */
		protected $filepath;
		/** @var int $rowIndex Iteration support for rows */
		protected $rowIndex;
		/** @var array */
		protected $viewables;
		/** @var array */
		protected $editables;

		public function __construct ($path, $viewables = array (), $editables = array ()) {
			$this->filepath  = $path;
			$this->viewables = $viewables;
			$this->editables = $editables;
			$this->read ();
		}

		/**
		 * Read and parse the configuration file contents.
		 */
		protected function read () {
			$fileContent    = trim (file_get_contents ($this->filepath));
			$pattern        = '/\$([^=]+)=([^;]+);/';
			$matches        = null;
			$matchesFound   = preg_match_all ($pattern, $fileContent, $matches);
			$configContents = array ();
			if ($matchesFound) {
				$configContents = $matches[0];
			}
			$this->rows = array ();
			foreach ($configContents as $configLine) {
				$this->rows[] = new ConfigFileRow($configLine, $this);
			}
			$this->rowIndex = -1;
			unset($fileContent);
		}

		/**
		 * Save the rows back to configuration.
		 */
		public function save () {
			$fileContent = trim (file_get_contents ($this->filepath));
			if ($this->rows) {
				$fp       = fopen ($this->filepath, 'w');
				$rowcount = count ($this->rows);
				for ($index = 0; $index < $rowcount; ++$index) {
					/** @var ConfigFileRow $row */
					$row = $this->rows[ $index ];
					if ($row->isEditable ()) {
						$variableName = $row->variableName ();
						$pattern      = '/\$' . $variableName . '[\s]+=([^;]+);/';
						$replacement  = $row->toString ();
						$fileContent  = preg_replace ($pattern, $replacement, $fileContent);
					}
				}
				fwrite ($fp, $fileContent);
				fclose ($fp);
			}
		}

		public function editables ($key = false) {
			if ($key === false) {
				return array_keys ($this->editables);
			}
			return $this->editables[ $key ];
		}

		public function viewables ($key = false) {
			if ($key === false) {
				return array_keys ($this->viewables);
			}
			return $this->viewables[ $key ];
		}

		public function setVariableValue ($name, $value) {
			if (!$this->rows) {
				return null;
			}
			/** @var ConfigFileRow $row */
			foreach ($this->rows as $row) {
				if (!$row->matchesVariableName ($name)) {
					continue;
				}
				if ($name == 'upload_maxsize') {
					return $row->setVariableValue ($value * 1000000);
				} else {
					return $row->setVariableValue ($value);
				}
			}
			return null;
		}

		/**
		 * Get all the rows
		 */
		public function getAll () {
			return $this->rows;
		}

		/**
		 * Has next row to read?
		 */
		public function next () {
			if ($this->rowIndex++ < count ($this->rows)) {
				return true;
			}
			return false;
		}

		/**
		 * Get the current row during iteration (please call next() before this)
		 */
		public function get () {
			return $this->rows[ $this->rowIndex ];
		}

		/**
		 * Rewind the iteration
		 */
		public function rewind () {
			$this->rowIndex = 0;
		}

	}
