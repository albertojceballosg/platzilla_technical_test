<?php

	class Zip {

		/** @var integer  */
		public $compressionLevel;

		/** @var string  */
		public $directory;

		/** @var integer  */
		public $entries;

		/** @var integer  */
		public $fileNum;

		/** @var integer  */
		public $now;

		/** @var integer  */
		public $offSet;

		/** @var string  */
		public $zipData;

		public function __construct () {
			$this->now = time();
			$this->initData ();
		}

		/**
		 * @codingStandardsIgnoreStart
		 * Nota codesnifer detecta Function's cyclomatic complexity (12) exceeds, pero es imposible simplificar la funcion
		 * ya que todas las lineas son requeridas.
		 * @SuppressWarnings(PHPMD)
		 * @param string $fileName
		 * @param string $data
		 * @param boolean $setNime
		 */
		private function forceDownload ($fileName = '', $data = '', $setNime = false) {
			if ($fileName === '' || $data === '') {
				return;
			} else if (empty ($data)) {
				$fileSize = filesize ($fileName);
				if (!is_file($fileName) || $fileSize === false) {
					return;
				}

				$filePath = $fileName;
				$fileName = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $fileName));
				$fileName = end($fileName);
			} else {
				$fileSize = strlen ($data);
			}

			// Set the default MIME type to send
			$mime = 'application/octet-stream';

			$x = explode ('.', $fileName);
			$extension = end ($x);

			if ($setNime == true) {
				if (count ($x) === 1 || $extension === '') {
					return;
				}

				// Load the mime types
				$mimes = array ('zip' => array ('application/x-zip', 'application/zip', 'application/x-zip-compressed', 'application/s-compressed', 'multipart/x-zip'));
				if (isset ($mimes [$extension])) {
					$mime = is_array($mimes[$extension]) ? $mimes[$extension][0] : $mimes[$extension];
				}
			}

			if ((count($x) !== 1) && (isset($_SERVER['HTTP_USER_AGENT'])) && (preg_match ('/Android\s(1|2\.[01])/', $_SERVER['HTTP_USER_AGENT']))) {
				$x [(count($x) - 1)] = strtoupper ($extension);
				$fileName            = implode ('.', $x);
			}

			$fp = @fopen($filePath, 'rb');
			if (empty ($data) && $fp === false) {
				return;
			}

			// Clean output buffer
			if (ob_get_level() !== 0 && @ob_end_clean () === false) {
				@ob_clean();
			}

			// Generate the server headers
			header('Content-Type: '.$mime);
			header('Content-Disposition: attachment; filename="'.$fileName.'"');
			header('Expires: 0');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: '.$fileSize);
			header('Cache-Control: private, no-transform, no-store, must-revalidate');

			// If we have raw data - just dump it
			if (!empty ($data)) {
				exit ($data);
			}

			// Flush 1MB chunks of data
			while (!feof ($fp) && ($data = fread ($fp, 1048576)) !== false) {
				echo $data;
			}

			fclose ($fp);
			exit;
		}

		// @codingStandardsIgnoreEnd

		/**
		 * @codingStandardsIgnoreStart
		 * Nota: he definido el nombre del método para evitar conflictos
		 */
		private function initData () {
			$this->zipData          = '';
			$this->directory        = '';
			$this->entries          = 0;
			$this->fileNum          = 0;
			$this->offSet           = 0;
			$this->compressionLevel = 2;
		}

		// @codingStandardsIgnoreEnd

		/**
		 * @codingStandardsIgnoreStart
		 * Nota: he definido el nombre del método para evitar conflictos
		 * @param $dir
		 * @return array
		 */
		protected function _getModTime ($dir) {
			$date = file_exists ($dir) ? getdate(filemtime ($dir)) : getdate ($this->now);

			return array(
				'file_mtime' => ($date ['hours'] << 11) + ($date ['minutes'] << 5) + $date ['seconds'] / 2,
				'file_mdate' => (($date ['year'] - 1980) << 9) + ($date ['mon'] << 5) + $date ['mday']
			);
		}

		// @codingStandardsIgnoreEnd

		/**
		 *  @codingStandardsIgnoreStart
		 * Nota: he definido el nombre del método para evitar conflictos
		 * @param $dir
		 * @param $fileMtime
		 * @param $fileMdate
		 */
		protected function _addDir ($dir, $fileMtime, $fileMdate) {
			$dir = str_replace('\\', '/', $dir);

			$this->zipData .= "\x50\x4b\x03\x04\x0a\x00\x00\x00\x00\x00"
				.pack('v', $fileMtime)
				.pack('v', $fileMdate)
				.pack('V', 0) // crc32
				.pack('V', 0) // compressed filesize
				.pack('V', 0) // uncompressed filesize
				.pack('v', strlen($dir)) // length of pathname
				.pack('v', 0) // extra field length
				.$dir
				// below is "data descriptor" segment
				.pack('V', 0) // crc32
				.pack('V', 0) // compressed filesize
				.pack('V', 0); // uncompressed filesize

			$this->directory .= "\x50\x4b\x01\x02\x00\x00\x0a\x00\x00\x00\x00\x00"
				.pack('v', $fileMtime)
				.pack('v', $fileMdate)
				.pack('V',0) // crc32
				.pack('V',0) // compressed filesize
				.pack('V',0) // uncompressed filesize
				.pack('v', strlen($dir)) // length of pathname
				.pack('v', 0) // extra field length
				.pack('v', 0) // file comment length
				.pack('v', 0) // disk number start
				.pack('v', 0) // internal file attributes
				.pack('V', 16) // external file attributes - 'directory' bit set
				.pack('V', $this->offSet) // relative offSet of local header
				.$dir;

			$this->offSet = strlen($this->zipData);
			$this->entries++;
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @codingStandardsIgnoreStart
		 * Nota: he definido el nombre del método para evitar conflictos
		 *
		 * @param $filePath
		 * @param $data
		 * @param $fileMTime
		 * @param $fileMDate
		 */
		protected function _addData ($filePath, $data, $fileMTime, $fileMDate) {
			$filePath = str_replace('\\', '/', $filePath);

			$uncompressedSize = strlen ($data);
			$crvThirtyTwo   = crc32 ($data);
			$gzData         = substr (gzcompress ($data, $this->compressionLevel), 2, -4);
			$compressedSize = strlen ($gzData);

			$this->zipData .= "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00"
				.pack('v', $fileMTime)
				.pack('v', $fileMDate)
				.pack('V', $crvThirtyTwo)
				.pack('V', $compressedSize)
				.pack('V', $uncompressedSize)
				.pack('v', strlen ($filePath))
				.pack('v', 0)
				.$filePath
				.$gzData;

			$this->directory .= "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00"
				.pack('v', $fileMTime)
				.pack('v', $fileMDate)
				.pack('V', $crvThirtyTwo)
				.pack('V', $compressedSize)
				.pack('V', $uncompressedSize)
				.pack('v', strlen($filePath))
				.pack('v', 0)
				.pack('v', 0)
				.pack('v', 0)
				.pack('v', 0)
				.pack('V', 32)
				.pack('V', $this->offSet)
				.$filePath;

			$this->offSet = strlen($this->zipData);
			$this->entries++;
			$this->fileNum++;
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @param array $directory
		 */
		public function addDir ($directory) {
			foreach ($directory as $dir) {
				if ( ! preg_match ('|.+/$|', $dir)) {
					$dir .= '/';
				}
				$dirTime = $this->_getModTime($dir);
				$this->_addDir($dir, $dirTime['file_mtime'], $dirTime['file_mdate']);
			}
		}

		/**
		 * @codingStandardsIgnoreStart
		 * NOTA: CodeSniffer detecta Assignments must be the first block of code on a line
		 * pero en este caso se requiere la reevaluacion de la condicion
		 * @param $filePath
		 *
		 * @return boolean
		 */
		public function archive ($filePath) {
			$fp = @fopen($filePath, 'w+b');
			if (!$fp) {
				return false;
			}

			flock ($fp, LOCK_EX);

			for ($result = $written = 0, $data = $this->getZip(), $length = strlen($data); $written < $length; $written += $result) {
				$result = fwrite($fp, substr($data, $written));
				if ($result == false) {
					break;
				}
			}

			flock ($fp, LOCK_UN);
			fclose ($fp);

			return is_int ($result);
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @param string $filePath
		 * @param null $data
		 */
		public function addData($filePath, $data = null) {
			if (is_array ($filePath)) {
				foreach ($filePath as $path => $data) {
					$file_data = $this->_getModTime($path);
					$this->_addData($path, $data, $file_data['file_mtime'], $file_data['file_mdate']);
				}
			} else 	{
				$file_data = $this->_getModTime($filePath);
				$this->_addData($filePath, $data, $file_data['file_mtime'], $file_data['file_mdate']);
			}
		}

		/**
		 * @param $path
		 * @param boolean $archiveFilePath
		 *
		 * @return boolean
		 */
		public function readFile ($path, $archiveFilePath = false) {
			$data = file_get_contents ($path);
			if (file_exists ($path) && $data != false) {
				if (is_string ($archiveFilePath)) {
					$name = str_replace ('\\', '/', $archiveFilePath);
				} else {
					$name = str_replace ('\\', '/', $path);

					if ($archiveFilePath == false) {
						$name = preg_replace('|.*/(.+)|', '\\1', $name);
					}
				}

				$this->addData ($name, $data);
				return true;
			}

			return false;
		}

		/**
		 * @codingStandardsIgnoreStart
		 * @param $path
		 * @param boolean $preserveFilePath
		 * @param null $rootPath
		 *
		 * @return boolean
		 */
		public function readDir ($path, $preserveFilePath = true, $rootPath = null) {
			$path = rtrim ($path, '/\\').DIRECTORY_SEPARATOR;
			$fp   = @opendir ($path);
			if (!$fp) {
				return false;
			}

			// Set the original directory root for child dir's to use as relative
			if (empty ($rootPath)) {
				$rootPath = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, dirname($path)).DIRECTORY_SEPARATOR;
			}

			while (($file = readdir($fp)) !== false) {
				if ($file[0] === '.') {
					continue;
				}
				$data = file_get_contents ($path.$file);
				if (is_dir($path.$file)) {
					$this->readDir ($path.$file.DIRECTORY_SEPARATOR, $preserveFilePath, $rootPath);
				} else if ($data !== false) {
					$name = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path);
					if ($preserveFilePath === false) {
						$name = str_replace  ($rootPath, '', $name);
					}

					$this->addData ($name.$file, $data);
				}
			}
			closedir ($fp);
			return true;
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @codingStandardsIgnoreStart
		 * @return boolean|string
		 */
		public function getZip () {
			if ($this->entries === 0) {
				return false;
			}

			return $this->zipData
				.$this->directory."\x50\x4b\x05\x06\x00\x00\x00\x00"
				.pack('v', $this->entries)
				.pack('v', $this->entries)
				.pack('V', strlen($this->directory))
				.pack('V', strlen($this->zipData))
				."\x00\x00";
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @param string $fileName
		 */
		public function download ($fileName = 'backup') {
			if ( ! preg_match('|.+?\.zip$|', $fileName)) {
				$fileName .= '.zip';
			}
			$getZip     = $this->getZip();
			$zipContent =& $getZip;

			$this->forceDownload  ($fileName, $zipContent);
		}

		public function clearData () {
			$this->zipData   = '';
			$this->directory = '';
			$this->entries   = 0;
			$this->fileNum   = 0;
			$this->offSet    = 0;
			return $this;
		}

	}
