<?php
	require_once ('include/platzilla/Exceptions/FileSystemException.php');

	abstract class FileSystemUtils {

		public static function copyFolder ($sourceFolderPath, $targetFolderPath) {
			if ((!file_exists ($sourceFolderPath)) || (!is_dir ($sourceFolderPath))) {
				return;
			}
			if (!mkdir ($targetFolderPath, 0775, true)) {
				throw new FileSystemException (FileSystemException::ERROR_FILE_SYSTEM_UNABLE_TO_CREATE_FOLDER, $targetFolderPath);
			}
			$folder = opendir ($sourceFolderPath);
			while ($fileName = readdir ($folder)) {
				if (($fileName == '.') || ($fileName == '..')) {
					continue;
				}
				if (is_dir ("{$sourceFolderPath}/{$fileName}")) {
					self::copyFolder ($sourceFolderPath . DIRECTORY_SEPARATOR . $fileName, $targetFolderPath . DIRECTORY_SEPARATOR . $fileName);
				} else {
					copy ($sourceFolderPath . DIRECTORY_SEPARATOR . $fileName, $targetFolderPath . DIRECTORY_SEPARATOR . $fileName);
				}
			}
			closedir ($folder);
		}

		public static function deleteFiles ($filePaths) {
			if (empty ($filePaths)) {
				return;
			}
			foreach ($filePaths as $filePath) {
				if (file_exists ($filePath)) {
					unlink ($filePath);
				}
			}
		}

		public static function deleteFolder ($folderPath) {
			if (!file_exists ($folderPath)) {
				return true;
			}
			if (!is_dir ($folderPath)) {
				return unlink ($folderPath);
			}
			$folder = opendir ($folderPath);
			while ($fileName = readdir ($folder)) {
				if (($fileName == '.') || ($fileName == '..')) {
					continue;
				}
				if (!self::deleteFolder ($folderPath . DIRECTORY_SEPARATOR . $fileName)) {
					return false;
				}
			}
			return rmdir ($folderPath);
		}

	}
