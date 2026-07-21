<?php
	namespace Platzilla\MailManager\Message;

	class Attachment {
		/** @var string */
		private $data;

		/** @var string */
		private $fileName;

		/** @var string */
		private $folderPath;

		/** @var string */
		private $mimeType;

		/**
		 * @return string
		 */
		public function getData () {
			return $this->data;
		}

		/**
		 * @return string
		 */
		public function getFileName () {
			return $this->fileName;
		}

		/**
		 * @return string
		 */
		public function getFolderPath () {
			return $this->folderPath;
		}

		/**
		 * @return string
		 */
		public function getMimeType () {
			return $this->mimeType;
		}

		/**
		 * @param string $data
		 *
		 * @return Attachment
		 */
		public function setData ($data) {
			$this->data = $data;
			return $this;
		}

		/**
		 * @param string $fileName
		 *
		 * @return Attachment
		 */
		public function setFileName ($fileName) {
			$this->fileName = $fileName;
			return $this;
		}

		/**
		 * @param string $folderPath
		 *
		 * @return Attachment
		 */
		public function setFolderPath ($folderPath) {
			$this->folderPath = $folderPath;
			return $this;
		}

		/**
		 * @param string $mimeType
		 *
		 * @return Attachment
		 */
		public function setMimeType ($mimeType) {
			$this->mimeType = $mimeType;
			return $this;
		}

		/**
		 * @return Attachment
		 */
		public static function getInstance () {
			return new self ();
		}

	}
