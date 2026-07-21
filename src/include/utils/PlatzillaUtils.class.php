<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/VtlibUtils.php');

	abstract class PlatzillaUtils {

		private static function calculateNewImageDimensions ($oldWidth, $oldHeight, $newWidth, $newHeight) {
			$xRatio = ($newWidth / $oldWidth);
			$yRatio = ($newHeight / $oldHeight);
			if (($xRatio * $oldHeight) < $newHeight) {
				// si proporcion horizontal*alto mayor que el alto maximo, alto final es alto por la proporcion horizontal
				// es decir, le quitamos al ancho, la misma proporcion que le quitamos al alto
				$finalHeight = ceil ($xRatio * $oldHeight);
				$finalWidth  = $newWidth;
			} else {
				// Igual que antes pero a la inversa
				$finalWidth  = ceil ($yRatio * $oldWidth);
				$finalHeight = $newHeight;
			}
			return array ($finalWidth, $finalHeight);
		}

		private static function createImage ($imageFilePath, $type) {
			switch ($type) {
				case IMAGETYPE_GIF:
					$image = imagecreatefromgif ($imageFilePath);
					break;
				case IMAGETYPE_JPEG:
					$image = imagecreatefromjpeg ($imageFilePath);
					break;
				case IMAGETYPE_PNG:
					$image = imagecreatefrompng ($imageFilePath);
					break;
				default:
					return null;
			}
			return $image;
		}

		private static function getUriHost ($isSslUri) {
			global $site_URL;
			if (isset ($_SERVER ['SERVER_PORT'])) {
				$port = $_SERVER ['SERVER_PORT'];
				$port = ((!$isSslUri) && ($port == '80')) || (($isSslUri) && ($port == '443')) ? '' : ':' . $port;
				$host = (isset ($_SERVER ['HTTP_HOST']) ? $_SERVER ['HTTP_HOST'] . $port : null);
				$hostUri = $host ? $host : $_SERVER ['SERVER_NAME'] . $port;
			} else if (!empty ($site_URL)) {
				$dummy       = explode ('://', $site_URL);
				$hostUri = rtrim ($dummy [1], '/');
			} else {
				$hostUri = 'localhost';
			}
			return $hostUri;
		}

		private static function getUriProtocol ($isSslUri) {
			global $site_URL;
			if (isset ($_SERVER ['SERVER_PROTOCOL'])) {
				$sp       = !empty ($_SERVER ['SERVER_PROTOCOL']) ? strtolower ($_SERVER ['SERVER_PROTOCOL']) : 'HTTP/1.1';
				$protocol = substr ($sp, 0, strpos ($sp, '/')) . (($isSslUri) ? 's' : '');
			} else if (!empty ($site_URL)) {
				$sp = explode ('://', $site_URL);
				$protocol = $sp [0];
			} else {
				$protocol = 'https';
			}
			return $protocol;
		}

		private static function isSslUri () {
			global $site_URL;
			if (isset ($_SERVER ['HTTPS'])) {
				$isSslUri = ($_SERVER ['HTTPS'] == 'on');
			} else if (!empty ($site_URL)) {
				$dummy       = explode ('://', $site_URL);
				$isSslUri = strtolower ($dummy [0]) == 'https';
			} else {
				$isSslUri = false;
			}
			return $isSslUri;
		}

		private static function parseSize ($size) {
			$unit = preg_replace ('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
			$size = preg_replace ('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
			if ($unit) {
				// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
				return round ($size * pow (1024, stripos ('bkmgtpezy', $unit[0])));
			} else {
				return round ($size);
			}
		}

		private static function setImageTransparency ($oldImage, $newImage, $type) {
			if (!in_array ($type, array (IMAGETYPE_GIF, IMAGETYPE_PNG))) {
				return;
			}
			// transparency index
			$tid = imagecolortransparent ($oldImage);
			// default transparency color
			$tcol = array ('red' => 255, 'green' => 255, 'blue' => 255);
			if ($tid >= 0) {
				// get the colors for the transparency index
				$tcol = imagecolorsforindex ($oldImage, $tid);
			}
			$tid = imagecolorallocate ($newImage, $tcol['red'], $tcol['green'], $tcol['blue']);
			imagefill ($newImage, 0, 0, $tid);
			imagecolortransparent ($newImage, $tid);
		}

		public static function copyFolder ($sourceFolderPath, $targetFolderPath) {
			if ((!file_exists ($sourceFolderPath)) || (!is_dir ($sourceFolderPath))) {
				return;
			}
			if (!mkdir ($targetFolderPath, 0775, true)) {
				throw new Exception ("Imposible crear el directorio {$targetFolderPath}");
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

		public static function getCountries () {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->query ('SELECT codigo, pais FROM vtiger_paises ORDER BY pais');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$countries = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$countries [ $row ['codigo'] ] = $row ['pais'];
			}
			return $countries;
		}

		public static function getPlatzillaRootFolderPath () {
			return realpath (__DIR__ . '/../../');
		}

		public static function getPlatzillaRootUri () {
			$isSslUri      = self::isSslUri ();
			$protocol      = self::getUriProtocol ($isSslUri);
			$host          = self::getUriHost ($isSslUri);
			$hostURI       = rtrim ($protocol . '://' . $host, '/');
			$platzillaRoot = realpath (__DIR__ . '/../../');
			$docRoot       = !empty ($_SERVER ['DOCUMENT_ROOT']) ? rtrim ($_SERVER ['DOCUMENT_ROOT'], '/') : $platzillaRoot;
			$docRootURI    = strpos ($docRoot, $platzillaRoot) !== 0 ? rtrim (str_replace ($docRoot, '', $platzillaRoot), '/') : '';
			return "{$hostURI}{$docRootURI}";
		}

		public static function getDueDate ($serviceDate) {
			$oneMonthInterval = new DateInterval ('P1M');
			if (!empty ($serviceDate)) {
				$startServiceDate = date_create ($serviceDate);
			} else {
				$startServiceDate = date_create ('today')->add ($oneMonthInterval);
			}

			$today           = date_create ('today');
			$startServiceDay = intval ($startServiceDate->format ('d'));

			if (!checkdate ($today->format ('m'), $startServiceDay, $today->format ('Y'))) {
				$dueDate = date_create ($today->format ('Y-m-t'));
			} else {
				$dueDate = date_create ("{$today->format ('Y')}-{$today->format ('m')}-{$startServiceDay}");
			}
			if ($dueDate < $today) {
				$oneDayInterval = new DateInterval ('P1D');
				$dueDate = $dueDate->add ($oneMonthInterval)->sub ($oneDayInterval);
			}
			return $dueDate;
		}

		/**
		 * @param array $variable
		 * @param string $index
		 * @param mixed $returnValueIfNotSet
		 *
		 * @return mixed
		 */
		public static function purify ($variable, $index, $returnValueIfNotSet = null) {
			if (
				(!isset ($variable)) ||
				((is_array ($variable)) && ((empty ($index)) || (!isset ($variable [ $index ]))))
			) {
				return $returnValueIfNotSet;
			}
			return vtlib_purify ($variable [ $index ], (is_array ($variable [ $index ]) || ($variable [ $index ] != strip_tags ($variable [ $index ]))));
		}

		public static function resizeImage ($sourceImageFilePath, $targetImageFilePath, $maxWidth, $maxHeight) {
			if ((empty ($sourceImageFilePath)) || (!file_exists ($sourceImageFilePath))) {
				return null;
			}

			$type     = exif_imagetype ($sourceImageFilePath);
			$oldImage = self::createImage ($sourceImageFilePath, $type);
			if (!$oldImage) {
				return null;
			}

			list ($oldWidth, $oldHeight) = getimagesize ($sourceImageFilePath);
			if (($oldWidth <= $maxWidth) && ($oldHeight <= $maxHeight)) {
				return $sourceImageFilePath;
			}

			// Se calcula ancho y alto de la imagen final
			list ($newWidth, $newHeight) = self::calculateNewImageDimensions ($oldWidth, $oldHeight, $maxWidth, $maxHeight);

			// Creamos una imagen en blanco de tamaño $newWidth  por $newHeight .
			$newImage = imagecreatetruecolor ($newWidth, $newHeight);
			self::setImageTransparency ($oldImage, $newImage, $type);

			// Copiamos $oldImage sobre la imagen que acabamos de crear en blanco ($tmp)
			imagecopyresampled ($newImage, $oldImage, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

			// Se destruye variable $oldImage para liberar memoria
			imagedestroy ($oldImage);

			// Se crea la imagen final en el directorio indicado
			switch ($type) {
				case IMAGETYPE_GIF:
					imagegif ($newImage, $targetImageFilePath);
					break;
				case IMAGETYPE_JPEG:
					imagejpeg ($newImage, $targetImageFilePath, 100);
					break;
				case IMAGETYPE_PNG:
					imagepng ($newImage, $targetImageFilePath, 100);
					break;
				default:
					return null;
			}
			return $targetImageFilePath;
		}

		public static function getMaxFileSizeInMb () {
			static $maxSize = -1;

			if ($maxSize < 0) {
				// Start with post_max_size.
				$postMaxSize = self::parseSize (ini_get ('post_max_size'));
				if ($postMaxSize > 0) {
					$maxSize = $postMaxSize;
				}

				// If upload_max_size is less, then reduce. Except if upload_max_size is
				// zero, which indicates no limit.
				$maxUploadSize = self::parseSize (ini_get ('upload_max_filesize'));
				if (($maxUploadSize > 0) && ($maxUploadSize < $maxSize)) {
					$maxSize = $maxUploadSize;
				}
			}
			return ($maxSize / 1024 / 1024);
		}

	}
