<?php
	require_once ('modules/materials/lib/FoldersHelper.php');

	class FolderUtils extends FoldersHelper {

		private $lastDocuments = 10;

		/**
		 * @param Folder $folder
		 *
		 * @return mixed
		 */
		private function getFolderTree ($folder, $idCategory) {
			foreach ($folder->getFiles() as $file) {
				if (!empty($folder->getFiles ())) {
					$items [] = array(
						'id'   => "1_{$idCategory}_{$folder->getId()}_{$file->getId()}",
						'text' => $file->getName(),
					);
				}
			}
			return (isset($items)) ? $items : null;
		}

		/**
		 * @return array|null
		 * @throws Exception
		 */
		public function getLastDocuments () {
			$result = self::$masterAdb->pquery (
				'SELECT 
						f2f.*,
						f.name AS foldername
					  FROM 
					  	vtiger_folder2files f2f 
					  INNER JOIN vtiger_folders f ON f.foldersid = f2f.foldersid
					  WHERE 1
					  ORDER BY 
					  	createtime DESC 
					  LIMIT ?',
				array ($this->lastDocuments)
			);
			if (self::$masterAdb->num_rows ($result) > 0) {
				$documents = array ();
				while ($row = self::$masterAdb->fetchByAssoc ($result, -1, false)) {
					$documents[] = Document::getInstance()
						->setCreateDate ($row ['craetedate'])
						->setCreateTime ($row ['createtime'])
						->setType ($row ['type'])
						->setDescription ($row ['description'])
						->setFolderId ($row ['foldersid'])
						->setFolderName (self::$platForm .'/materials/' . self::sanitizeString ($row ['foldername']))
						->setId ($row ['filesid'])
						->setName ($row ['name'])
						->setPublicName ($row['publicname'])
						->setPhoto ($row ['photo'])
						->setPhotoType($row ['imagetype'])
						->setRelatedFiles (null)
						->setUrl ($row ['url']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($documents)) ? $documents : null;
		}

		/**
		 * @return null|string
		 * @throws Exception
		 */
		public function getDocumentTabMenu () {
			$categories = $this->fetchCategories(false, false, true);
			if (empty ($categories)) {
				return null;
			}
			$keyIndex = '1';
			$menu = array (
				'id'       => $keyIndex,
				'text'     => 'Documentos, Ebooks, Videos y enlaces a interesantes atículos',
				'expanded' => true,
			);

			foreach ($categories as $category) {
				if (empty ($category->getFolders())) {
					continue;
				}
				foreach ($category->getFolders() as $folder) {
					$items     = $this->getFolderTree ($folder, $category->getId ());
					if (empty($items)) {
						continue;
					}
					$submenu[] = array (
						'id'    => "1_{$category->getId ()}_{$folder->getId ()}",
						'text'  => $folder->getName (),
						'items' => $items,
					);
					unset ($items);
				}
				$menu ['items'][] = array (
					'id'       => "{$keyIndex}_{$category->getId ()}",
					'text'     => $category->getName (),
					'expanded' => true,
					'items'    => $submenu,
				);
				unset ($submenu);
			}
			return json_encode($menu);
		}

		/**
		 * @param PearDatabase $adb
		 * @param $userId
		 *
		 * @return FilesDonwload[]|null
		 * @throws Exception
		 */
		public function fetchDownloadedFile ($adb, $userId) {
			if (empty ($userId) || !is_numeric ($userId)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT * FROM 	vtiger_files_donwload  WHERE donwloadby=?',
				array($userId)
			);
			if ($adb->num_rows ($result) > 0) {
				$documents = array();
				$row = $adb->fetchByAssoc ($result, -1, false);
				$documents [] = FilesDonwload::getInstance()
					->setDocument (self::fetchDocumentById ($row['filesid'], true))
					->setLastTime ($row['donwloadon'])
					->setUserId ($row['donwloadby']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($documents)) ? $documents : null;
		}

		/**
		 * @return null
		 * @throws Exception
		 */
		public function fetchFoldersMenu () {
			$foldres = self::fetchFolders();
			if (empty ($foldres)) {
				return null;
			}
			$menu = array (
				'id'       => '0',
				'text'     => 'Documentos, Ebooks, Videos y enlaces a interesantes atículos',
				'expanded' => true,
			);
			foreach ($foldres as $folder) {
				if (!empty($folder->getFiles ())) {
					$expanded = true;
					foreach ($folder->getFiles() as $file) {
						$items [] = array (
							'id'   => $file->getId(),
							'text' => $file->getName (),
						);
					}
				} else {
					$expanded = false;
					$items = '';
				}
				$menu ['items'][] = array (
					'id'       => $folder->getId (),
					'text'     => $folder->getName (),
					'expanded' => $expanded,
					'items'    => $items,
				);
				unset ($items);
			}

			return json_encode($menu);

		}

		/**
		 * @param integer $fileId
		 * @param integer $numViewed
		 */
		public function updateViewed ($fileId, $numViewed) {
			if (empty($fileId) || empty($numViewed) || !is_numeric($fileId) || !is_numeric($numViewed)) {
				return;
			}
			self::$masterAdb->pquery (
				'UPDATE vtiger_folder2files SET viewed=? WHERE filesid=?',
				array ($numViewed, $fileId)
			);
		}

		/**
		 * @param string $platform
		 *
		 * @return FolderUtils
		 */
		public static function getInstance ($platform) {
			return new self ($platform);
		}

	}
