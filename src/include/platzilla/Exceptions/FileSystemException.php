<?php

	class FileSystemException extends Exception {
		const ERROR_FILE_SYSTEM_UNABLE_TO_CREATE_FOLDER = 'ERROR_FILE_SYSTEM_UNABLE_TO_CREATE_FOLDER %s';

		public function __construct ($message, $values = null, Exception $previous = null) {
			if (!is_array ($values)) {
				$message = sprintf ($message, join (',', $values));
			} else if ((!empty ($values)) && (!is_object ($values))) {
				$message = sprintf ($message, $values);
			}
			parent::__construct ($message, null, $previous);
		}

	}
