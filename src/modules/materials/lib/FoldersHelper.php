<?php
	require_once ('modules/materials/Objects/Category.php');
	require_once ('modules/materials/Objects/Document.php');
	require_once ('modules/materials/Objects/FilesDonwload.php');
	require_once ('modules/materials/Objects/Folder.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/ImageUtils.class.php');

	abstract class FoldersHelper {

		const IMAGEN_TYPE   = array('png', 'jpg', 'jpeg', 'gif');
		const IMAGEN_WIDTH  = 224;
		const IMAGEN_HEIGHT = 277;

		/** @var PearDatabase */
		public static  $masterAdb;

		/** @var string */
		public static $rootDirectory;

		public static $platForm;

		public function __construct ($platform) {
			self::$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			self::$platForm  = $platform;
			if (!is_dir (__DIR__ . "/../../../{$platform}/materials")) {
				mkdir (__DIR__ . "/../../../{$platform}/materials", 0777, true);
			}
			self::$rootDirectory = __DIR__ . "/../../../{$platform}/materials";
		}

		/**
		 * @param string $url
		 */
		private static function meakeDirectory ($url) {
			if (!is_dir ($url)) {
				mkdir ($url, 0777, true);
			}
		}

		/**
		 * @param integer $fileId
		 *
		 * @return array
		 * @throws Exception
		 */
		private static function getRelatedDocuments ($fileId) {
			if (empty($fileId) || !is_numeric($fileId)) {
				return array ();
			}

			$result = self::$masterAdb->pquery ('SELECT relatedfileid FROM vtiger_file2files WHERE mainfileid=?', array($fileId));
			if (self::$masterAdb->num_rows ($result) > 0) {
				$documents = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$documents [] = $row ['relatedfileid'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($documents)) ? $documents : array ();
		}

		/**
		 * @param Document $document
		 */
		private static function saveRealtedFiles ($document) {
			if (!count($document->getRelatedFiles ())) {
				return;
			}
			foreach ($document->getRelatedFiles () as $realtedFile) {
				self::$masterAdb->pquery (
					'INSERT INTO vtiger_file2files (mainfileid, relatedfileid) VALUES (?, ?)',
					array($document->getId(), $realtedFile)
				);
				self::$masterAdb->pquery (
					'UPDATE vtiger_folder2files SET locked=? WHERE filesid=?',
					array (1, $realtedFile)
				);
			}
		}

		/**
		 * @param integer $folderId
		 *
		 * @return array
		 */
		private static function scanFromTable ($folderId) {
			self::$masterAdb->pquery ('DELETE FROM vtiger_folder2files WHERE type=? AND locked=? AND foldersid=?', array ('UPLOADED', 0, $folderId));
			$results = self::$masterAdb->run_query_allrecords ("SELECT publicname FROM vtiger_folder2files WHERE  type='UPLOADED'");
			return array_column ($results, 'publicname');
		}

		/**
		 * @param string $folderDir
		 * @param integer $folderId
		 *
		 * @throws Exception
		 */
		private static function scanDirectories ($folderDir, $folderId) {
			if (empty($folderDir) || !is_dir (self::$rootDirectory . '/' . $folderDir)) {
				return;
			}
			$results            = self::scanFromTable ($folderId);
			$folderDir          = self::$rootDirectory . '/' . $folderDir;
			$invisibleFileNames = array ('.', '..', '.htaccess', '.htpasswd');
			$dirContent         = scandir ($folderDir, SCANDIR_SORT_DESCENDING);
			foreach ($dirContent as $content) {
				if (in_array ($content, $invisibleFileNames) || in_array ($content, $results) || empty ($content)) {
					continue;
				}
				$path  = $folderDir . '/' . $content;
				$dummy = pathinfo ($path);
				if (is_file ($path) && is_readable ($path) && (!in_array ($dummy['extension'], array ('png', 'jpg', 'jpeg', 'gif')))) {
					$uploadTime = intval (filemtime ($path));
					$uploadDate = date ('Y-m-d H:i:s', $uploadTime);
					self::saveFile (
						Document::getInstance ()
							->setFolderId ($folderId)
							->setCreateDate ($uploadDate)
							->setCreateTime ($uploadTime)
							->setFeatured (null)
							->setType ('UPLOADED')
							->setName ($dummy ['basename'])
							->setPhoto (null)
							->setPhotoType (null)
							->setPublicName ($content)
							->setUrl ($dummy ['dirname'])
					);
				}
			}
		}

		/**
		 * @param integer $categoryId
		 */
		public static function deleteCategory ($categoryId) {
			if (!empty ($categoryId) && is_numeric ($categoryId)) {
				self::$masterAdb->pquery ('DELETE FROM vtiger_folders_categories WHERE categoryid=?', array($categoryId));
				self::$masterAdb->pquery ('DELETE FROM vtiger_folder2category WHERE categoryid=?', array($categoryId));
			}
		}

		/**
		 * @param integer $fileId
		 *
		 * @throws Exception
		 */
		public static function deleteDocument ($fileId) {
			$file = self::fetchDocumentById ($fileId);
			if (empty($file)) {
				throw new Exception ('Documento no identificado');
			}
			if ($file->getType() == 'UPLOADED') {
				$fileToDelete = "{$file->getUrl()}/{$file->getPublicName()}";
				$isDeleted    = unlink ($fileToDelete);
				if (!$isDeleted) {
					throw new Exception ('Imposible eliminar el documento');
				}
			}
			self::$masterAdb->pquery ('DELETE FROM vtiger_folder2files WHERE filesid=?', array ($file->getId ()));
			self::$masterAdb->pquery ('DELETE FROM vtiger_file2files WHERE mainfileid=? OR relatedfileid=?', array ($file->getId (), $file->getId ()));
		}

		/**
		 * @param integer $folderId
		 *
		 * @throws Exception
		 */
		public static function deleteFolder ($folderId) {
			$folder = self::fetchFolderById ($folderId);
			if (empty($folder)) {
				throw new Exception ('Carpeta no identificada');
			}
			array_map ('unlink', array_filter ((array) glob ("{$folder->getUrl ()}/*")));
			rmdir ($folder->getUrl ());
			self::$masterAdb->pquery ('DELETE FROM vtiger_folder2files WHERE foldersid=?', array ($folder->getId ()));
			self::$masterAdb->pquery ('DELETE FROM vtiger_folders WHERE foldersid=?', array ($folder->getId ()));
		}

		/**
		 * @param boolean $headOnly
		 * @param boolean $withOutDocuments
		 * @param boolean $enabled
		 *
		 * @return Category[]|null
		 * @throws Exception
		 */
		public static function fetchCategories ($headOnly = false, $withOutDocuments = false, $enabled = false) {
			$where = (!$enabled) ? 1 : 'status="ENABLED"';
			$result = self::$masterAdb->query ("SELECT * FROM vtiger_folders_categories WHERE {$where}");
			if (self::$masterAdb->num_rows ($result) > 0) {
				$categoriesData = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$categoriesData [] = Category::getInstance()
						->setCreateDate ($row ['createdate'])
						->setDescription ($row ['description'])
						->setFolders ((!$headOnly) ? self::fetchFoldersByCategory ($row ['categoryid'], $withOutDocuments) : null)
						->setId ($row ['categoryid'])
						->setStatus ($row ['status'])
						->setName ($row ['name']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($categoriesData)) ? $categoriesData : null;
		}

		/**
		 * @param integer $categoryId
		 * @param boolean $headOnly
		 * @param boolean $withDocuments
		 *
		 * @return Category|null
		 * @throws Exception
		 */
		public static function fetchCategoryById ($categoryId, $headOnly = false, $withDocuments = false) {
			if (empty($categoryId)) {
				return null;
			}
			$result = self::$masterAdb->pquery ('SELECT * FROM vtiger_folders_categories WHERE categoryid=?', array ($categoryId));
			if (self::$masterAdb->num_rows ($result) > 0) {
				$row = self::$masterAdb->fetchByAssoc ($result, -1, false);
				$categoriesData = Category::getInstance()
					->setCreateDate ($row ['createdate'])
					->setDescription ($row ['description'])
					->setFolders ((!$headOnly) ? self::fetchFoldersByCategory ($row ['categoryid'], $withDocuments) : null)
					->setId ($row ['categoryid'])
					->setStatus ($row ['status'])
					->setName ($row ['name']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($categoriesData)) ? $categoriesData : null;
		}

		/**
		 * @param $folderAddress
		 * @param $folderId
		 *
		 * @return Document[]|null
		 * @throws Exception
		 */
		public static function fetchDocuments ($folderAddress, $folderId) {
			if (!empty ($folderAddress)) {
				$folderAddress = self::sanitizeString ($folderAddress);
				self::scanDirectories ($folderAddress, $folderId);
			}

			$result = self::$masterAdb->pquery ('SELECT * FROM vtiger_folder2files WHERE foldersid=?', array ($folderId));
			if (self::$masterAdb->num_rows ($result) > 0) {
				$documents = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$documents [] = Document::getInstance()
						->setCreateDate ($row ['craetedate'])
						->setCreateTime ($row ['createtime'])
						->setDescription ($row ['description'])
						->setType ($row ['type'])
						->setFeatured ($row ['featured'])
						->setFolderId ($row ['foldersid'])
						->setId ($row ['filesid'])
						->setName ($row ['name'])
						->setPublicName ($row ['publicname'])
						->setUrl ($row ['url'])
						->setUrlPublic ($row ['url_public'])
						->setUrlBlog (($row ['featured'] == 'ENABLED') ? $row ['filesid'] : null)
						->setViewed ($row ['viewed']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($documents)) ? $documents : null;
		}

		/**
		 * @param integer $fileId
		 *
		 * @return Document|null
		 * @throws Exception
		 */
		public static function fetchDocumentById ($fileId, $headOnly = false) {
			if (empty ($fileId) || !is_numeric($fileId)) {
				return null;
			}
			$result = self::$masterAdb->pquery (
				'SELECT 
						f2f.*,
						f.name AS foldername
					  FROM 
					  	vtiger_folder2files f2f 
					  INNER JOIN vtiger_folders f ON f.foldersid = f2f.foldersid
					  WHERE 
					  	filesid=?',
				array($fileId)
			);
			if (self::$masterAdb->num_rows ($result) > 0) {
				$row = self::$masterAdb->fetchByAssoc ($result, -1, false);
				$document = Document::getInstance()
					->setCreateDate ($row ['craetedate'])
					->setCreateTime ($row ['createtime'])
					->setType ($row ['type'])
					->setDescription ($row ['description'])
					->setFeatured ($row ['featured'])
					->setFolderId ($row ['foldersid'])
					->setFolderName (self::$platForm .'/materials/' . self::sanitizeString($row ['foldername']))
					->setId ($row ['filesid'])
					->setName ($row ['name'])
					->setPublicName ($row['publicname'])
					->setPhoto ($row ['photo'])
					->setPhotoType ($row ['imagetype'])
					->setRelatedFiles (!($headOnly) ? self::getRelatedDocuments ($row ['filesid']) : null)
					->setUrl ($row ['url'])
					->setUrlPublic ($row ['url_public'])
					->setViewed ($row ['viewed']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($document)) ? $document : null;
		}

		/**
		 * @param boolean $headOnly
		 *
		 * @return Folder[]|null
		 * @throws Exception
		 */
		public static function fetchFolders ($headOnly = false) {
			$result = self::$masterAdb->query ('SELECT * FROM vtiger_folders WHERE 1');
			if (self::$masterAdb->num_rows ($result) > 0) {
				$foldersData = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$foldersData [] = Folder::getInstance()
						->setCreateDate ($row ['createdate'])
						->setCreateTime ($row ['createtime'])
						->setDescription ($row ['description'])
						->setFiles ((!$headOnly) ? self::fetchDocuments ($row ['name'], $row ['foldersid']) : null)
						->setId ($row ['foldersid'])
						->setStatus ($row ['status'])
						->setName ($row ['name'])
						->setFolderName (self::$platForm .'/materials/' . self::sanitizeString ($row ['name']))
						->setPhoto ($row ['photo'])
						->setUrl ($row ['url'])
						->setVideo ($row ['video']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($foldersData)) ? $foldersData : null;
		}

		/**
		 * @param integer $categoryId
		 * @param boolean $headOnly
		 *
		 * @return Folder[]|null
		 * @throws Exception
		 */
		public static function fetchFoldersByCategory ($categoryId, $headOnly = false) {
			if (empty ($categoryId) || !is_numeric ($categoryId)) {
				return null;
			}
			$result = self::$masterAdb->pquery (
				'SELECT 
						f.* 
					  FROM 
					  	vtiger_folders f
					  INNER JOIN vtiger_folder2category fc ON f.foldersid = fc.foldersid
					  WHERE fc.categoryid=?',
				array($categoryId)
			);
			if (self::$masterAdb->num_rows ($result) > 0) {
				$foldersData = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$foldersData[] = Folder::getInstance()
						->setCreateDate ($row ['createdate'])
						->setCreateTime ($row ['createtime'])
						->setDescription ($row ['description'])
						->setFiles ((!$headOnly) ? self::fetchDocuments ($row ['name'], $row ['foldersid']) : null)
						->setId ($row ['foldersid'])
						->setStatus ($row ['status'])
						->setName ($row ['name'])
						->setFolderName (self::$platForm .'/materials/' . self::sanitizeString($row ['name']))
						->setUrl ($row ['url'])
						->setPhoto ($row ['photo'])
						->setVideo ($row ['video']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($foldersData)) ? $foldersData : null;
		}

		/**
		 * @param integer $folderId
		 * @param boolean $headOnly
		 *
		 * @return Folder|null
		 * @throws Exception
		 */
		public static function fetchFolderById ($folderId, $headOnly = false) {
			if (empty ($folderId) || !is_numeric ($folderId)) {
				return null;
			}
			$result = self::$masterAdb->pquery ('SELECT * FROM vtiger_folders WHERE foldersid=?', array($folderId));
			if (self::$masterAdb->num_rows ($result) > 0) {
				$row = self::$masterAdb->fetchByAssoc ($result, -1, false);
				$foldersData = Folder::getInstance()
					->setCreateDate ($row ['createdate'])
					->setCreateTime ($row ['createtime'])
					->setDescription ($row ['description'])
					->setFiles ((!$headOnly) ? self::fetchDocuments ($row ['name'], $row ['foldersid']) : null)
					->setId ($row ['foldersid'])
					->setStatus ($row ['status'])
					->setName ($row ['name'])
					->setFolderName (self::$platForm .'/materials/' . self::sanitizeString($row ['name']))
					->setUrl ($row ['url'])
					->setPhoto ($row ['photo'])
					->setVideo ($row ['video']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($foldersData)) ? $foldersData : null;
		}

		/**
		 * @param $uploadMax
		 *
		 * @return null|string
		 * @throws Exception
		 */
		public static function getImageEBook ($uploadMax) {
			if(!isset ($_FILES['imagePhoto'])) {
				return null;
			} else if (empty ($_FILES ['imagePhoto']['name'])) {
				return null;
			}

			$fileSize = $_FILES ['imagePhoto']['size'];
			$fileTmp  = $_FILES ['imagePhoto']['tmp_name'];
			$fileExt  = strtolower (end (explode ('.',$_FILES ['imagePhoto']['name'])));

			if(!in_array($fileExt, self::IMAGEN_TYPE)) {
				throw new Exception (FolderException::ERROR_EXTENSION_NO_ALLOWED);
			}
			if($fileSize > $uploadMax) {
				throw new Exception(FolderException::ERROR_FILE_TOO_BIG);
			}

			$idPhoto = rand ();
			$fileExt = '.' . $fileExt;

			move_uploaded_file ($fileTmp,'Image/Source_' . $idPhoto . $fileExt);

			$config                   = array();
			$config ['imageLibrary']  = 'gd2';
			$config ['sourceImage']   = 'Image/Source_' . $idPhoto . $fileExt;
			$config ['createThumb']   = false;
			$config ['maintainRatio'] = true;
			$config ['width']         = self::IMAGEN_WIDTH;
			$config ['height']        = self::IMAGEN_HEIGHT;

			$imagLibrary = new ImageUtils ($config);

			$resizeStatus = $imagLibrary->resize();
			if ($resizeStatus) {
				$data = file_get_contents('Image/Source_' . $idPhoto . $fileExt);
				$data = base64_encode ($data);
				unlink ('Image/Source_' . $idPhoto . $fileExt);
			}
			return (isset($data)) ? $data : null;
		}

		/**
		 * @param $string
		 *
		 * @return string
		 */
		public static function sanitizeString ($string) {
			$string = trim($string);
			$string = str_replace (
				array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
				array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
				$string
			);
			$string = str_replace (
				array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
				array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
				$string
			);
			$string = str_replace (
				array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
				array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
				$string
			);
			$string = str_replace (
				array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
				array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
				$string
			);
			$string = str_replace (
				array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
				array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
				$string
			);
			$string = str_replace (
				array('ñ', 'Ñ', 'ç', 'Ç'),
				array('n', 'N', 'c', 'C'),
				$string
			);
			$string = str_replace (
				array( '·', '$', '%', '&', '/', '(', ')', '?', "'", '¡', '¿', '[', '^', '<code>', ']', '+', '}', '{', '¨', '´', '>', '< ', ';', ',', ':', '.'),
				'',
				$string
			);
			$string = str_replace (' ', '_', $string);
			return strtolower ($string);
		}

		/**
		 * @param Category $category
		 *
		 * @throws Exception
		 */
		public static function saveCategory ($category) {
			if (empty($category) && (!$category instanceof Category)) {
				throw new Exception (FolderException::ERROR_CREATE_EMPTY_OBJECT_CATEGORY);
			} else if (empty ($category->getName())) {
				throw new Exception(FolderException::ERROR_CREATE_EMPTY_CATEGORY_NAME);
			}

			if (empty($category->getId ())) {
				self::$masterAdb->pquery (
					'INSERT INTO vtiger_folders_categories (name, description, status) VALUES (?, ?, ?)',
					array ($category->getName (), $category->getDescription (), $category->getStatus())
				);
			} else {
				self::$masterAdb->pquery (
					'UPDATE vtiger_folders_categories SET name=?, description=?, status=? WHERE categoryid=?',
					array ($category->getName (), $category->getDescription (),$category->getStatus(),$category->getId ())
				);
			}
		}

		/**
		 * @param Document $document
		 *
		 * @throws Exception
		 */
		public static function saveFile ($document) {
			if (empty($document) && (!$document instanceof Document)) {
				throw new Exception (FolderException::ERROR_CREATE_EMPTY_OBJECT_FILE);
			}
			self::validateFile ($document);
			if (empty ($document->getId ())) {
				$createDate = ($document->getType() == 'UPLOADED') ? $document->getCreateDate()->format ('Y-m-d H:m:s') : date ('Y-m-d H:m:s');
				$creatTime  = ($document->getType() == 'UPLOADED') ? $document->getRealCreateTime() : time ();
				self::$masterAdb->pquery (
					'INSERT INTO vtiger_folder2files (foldersid, name, publicname, photo,  	imagetype, type, url, url_public, description, featured, createtime, craetedate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($document->getFolderId (), $document->getName (), $document->getPublicName (), $document->getPhoto (), $document->getPhotoType (), $document->getType (), $document->getUrl (), $document->getUrlPublic (), $document->getDescription (), $document->getFeatured(), $creatTime, $createDate)
				);
			} else {
				$locked = ($document->getType() == 'UPLOADED') ? 1 : 0;
				self::$masterAdb->pquery (
					'UPDATE vtiger_folder2files SET foldersid=?, name=?, publicname=?, photo=?, imagetype=?, url=?, url_public=?, description=?, featured=?, locked=? WHERE filesid=?',
					array ($document->getFolderId (), $document->getName(), $document->getPublicName (), $document->getPhoto (), $document->getPhotoType (), $document->getUrl (), $document->getUrlPublic (),$document->getDescription (), $document->getFeatured(), $locked, $document->getId ())
				);
				self::$masterAdb->pquery ('DELETE FROM vtiger_file2files WHERE mainfileid=?', array ($document->getId ()));
			}
			self::saveRealtedFiles ($document);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $fileId
		 * @param integer $userId
		 */
		public static function setDownloadedFile ($adb, $fileId, $userId) {
			$lastTime = time();
			$isDownloaded = $adb->run_query_allrecords ("SELECT * FROM vtiger_files_donwload WHERE filesid={$fileId} AND donwloadby={$userId}");
			if (!empty ($isDownloaded)) {
				return;
			} else {
				$adb->run_insert_data ('vtiger_files_donwload', array ('filesid' => $fileId, 'donwloadon' => $lastTime, 'donwloadby' => $userId));
			}
		}

		/**
		 * @param integer|string $folder
		 * @param integer $uploadMax
		 * @param string $type
		 *
		 * @return string|null
		 * @throws Exception
		 */
		public static function uploadPhoto ($folder, $uploadMax, $type = 'FILE') {
			if(!isset ($_FILES['imagePhoto'])) {
				return null;
			} else if (empty ($_FILES ['imagePhoto']['name'])) {
				return null;
			}
			if ($type == 'FILE') {
				$folder   = self::fetchFolderById ($folder, true);
				$url      = $folder->getUrl();
			} else {
				$folderName = self::sanitizeString ($folder);
				$url        = self::$rootDirectory . '/' . $folderName;

				self::meakeDirectory ($url);
			}

			$fileName = $_FILES ['imagePhoto']['name'];
			$fileSize = $_FILES ['imagePhoto']['size'];
			$fileTmp  = $_FILES ['imagePhoto']['tmp_name'];
			$fileExt  = strtolower (end (explode ('.',$_FILES ['imagePhoto']['name'])));

			if(('.' . $fileExt) != self::IMAGEN_TYPE) {
				throw new Exception (FolderException::ERROR_EXTENSION_NO_ALLOWED);
			}
			if($fileSize > $uploadMax) {
				throw new Exception(FolderException::ERROR_FILE_TOO_BIG);
			}

			$fileName = self::sanitizeString (str_replace(('.' . $fileExt), '', $fileName)) . '.' . $fileExt;
			$isUpload = move_uploaded_file ($fileTmp,$url . '/' . $fileName);

			return ($isUpload) ? $fileName : null;
		}

		/**
		 * @param Folder $folder
		 *
		 * @throws Exception
		 */
		public static function saveFolder ($folder) {
			if (empty($folder) && (!$folder instanceof Folder)) {
				throw new Exception (FolderException::ERROR_CREATE_EMPTY_OBJECT_FOLDER);
			}

			if (empty ($folder->getId ())) {
				self::validateFolder ($folder);
				$folderName = self::sanitizeString ($folder->getName ());
				$folderUrl  = self::$rootDirectory . '/' . $folderName;
				self::$masterAdb->pquery (
					'INSERT INTO vtiger_folders (name, description, video, photo, url, createtime, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
					array ($folder->getName (), $folder->getDescription (), $folder->getVideo (), $folder->getPhoto (), $folderUrl, time(), $folder->getStatus())
				);
				$folder->setId (self::$masterAdb->getLastInsertID());
				self::meakeDirectory ($folderUrl);
			} else {
				self::$masterAdb->pquery (
					'UPDATE vtiger_folders SET description=?, video=?, photo=?, status=? WHERE foldersid=?',
					array ($folder->getDescription (), $folder->getVideo (), $folder->getPhoto (), $folder->getStatus(),$folder->getId ())
				);
				self::$masterAdb->pquery ('DELETE FROM vtiger_folder2category WHERE foldersid=?', array ($folder->getId ()));
			}
			if (!empty($folder->getCategory())) {
				self::$masterAdb->pquery (
					'INSERT INTO vtiger_folder2category (categoryid, foldersid) VALUES (?, ?)',
					array ($folder->getCategory(), $folder->getId ())
				);
			}
		}

		/**
		 * @param Document $document
		 *
		 * @throws Exception
		 */
		public static function validateFile ($document) {
			if (empty ($document->getName ())) {
				throw new Exception (FolderException::ERROR_CREATE_EMPTY_FILE_NAME);
			} else if (empty($document->getUrl())) {
				throw new Exception (FolderException::ERROR_CREATE_EMPTY_FILE_URL);
			} else if (empty($document->getFolderId())) {
				throw new Exception (FolderException::ERROR_CREATE_EMPTY_FOLDER_ID);
			} else if (!empty($document->getUrlPublic ())) {
				if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$document->getUrlPublic ())) {
					throw new Exception (FolderException::ERROR_FILE_PUBLIC_URL);
				}
			}
		}

		/**
		 * @param Folder $folder
		 *
		 * @throws Exception
		 */
		public static function validateFolder ($folder) {
			if (empty ($folder->getName ())) {
				throw new Exception (FolderException::ERROR_CREATE_EMPTY_FOLDER_NAME);
			} else if (is_dir (self::$rootDirectory . '/' . self::sanitizeString ($folder->getName ())) && empty ($folder->getPhoto())) {
				throw new Exception (FolderException::ERROR_CREATE_DUPLICATE_FOLDER);
			}
		}

	}
