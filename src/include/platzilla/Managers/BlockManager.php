<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Objects/Block.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class BlockManager {
		/** @var BlockManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param FieldManager $fm
		 * @param Block $block
		 * @param integer[] $processedFieldIds
		 * @param boolean $ignoreLock
		 */
		private function deleteUnprocessedFields (FieldManager $fm, Block $block, $processedFieldIds, $ignoreLock) {
			if (empty ($processedFieldIds)) {
				return;
			}

			if ((!$ignoreLock)) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}
			$questionMarks = str_repeat ('?, ', (count ($processedFieldIds) - 1)) . '?';
			$result        = $this->adb->pquery (
				"SELECT * FROM vtiger_field WHERE block=? AND fieldid NOT IN ({$questionMarks}) {$whereClause}",
				array_merge (array ($block->getId ()), $processedFieldIds)
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$field = $fm->fetchFieldById ($row ['fieldid']);
					$fm->deleteField ($field);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Block[]
		 */
		private function fetchDeletedBlocks ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$blocks = array ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('block', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var Block $block */
					$block = unserialize ($row ['serializedobject']);
					$block->setDeleted (true);
					$blocks [] = $block;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $blocks;
		}

		/**
		 * @param Block $block
		 *
		 * @return integer
		 */
		private function getSequenceNumber ($block) {
			$sequence = isset ($block) ? $block->getSequence () : null;
			if ((!empty ($sequence)) && (is_numeric ($sequence))) {
				$sequence = intval ($block->getSequence ());
			} else {
				$result = $this->adb->pquery (
					'SELECT MAX(b.sequence) AS maxsequence FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=?',
					array ($block->getModuleName ())
				);
				if ($this->adb->num_rows ($result) == 0) {
					$sequence = 1;
				} else {
					$row      = $this->adb->fetchByAssoc ($result, -1, false);
					$sequence = (intval ($row ['maxsequence']) + 1);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return $sequence;
		}

		/**
		 * @param Block $block
		 * @param string|null $moduleTableName
		 * @param boolean $ignoreLock
		 */
		private function saveFields ($block, $moduleTableName = null, $ignoreLock = true) {
			if ((empty ($block)) || (!($block instanceof Block))) {
				return;
			}

			$fields = isset ($block) ? $block->getFields () : null;
			if (empty ($fields)) {
				return;
			}

			$fm                = FieldManager::getInstance ($this->adb);
			$processedFieldIds = array ();
			foreach ($fields as $field) {
				$moduleName = $field->getModuleName ();
				$tableName  = $field->getTableName ();
				$field->setBlockId ($block->getId ());
				if (empty ($moduleName)) {
					$field->setModuleName ($block->getModuleName ());
				}
				if (empty ($tableName)) {
					$field->setTableName ($moduleTableName);
				}
				$fm->saveField ($field, $ignoreLock);
				$processedFieldIds [] = $field->getId ();
			}

			$this->deleteUnprocessedFields ($fm, $block, $processedFieldIds, $ignoreLock);
		}

		/**
		 * @param Block $block
		 *
		 * @throws BlockException
		 * @throws FieldException
		 */
		private function validate ($block) {
			if ((empty ($block)) || (!($block instanceof Block))) {
				throw new BlockException (BlockException::ERROR_BLOCK_EMPTY);
			}

			$block->validate ();

			$moduleName = $block->getModuleName ();
			if (empty ($moduleName)) {
				throw new BlockException (BlockException::ERROR_BLOCK_EMPTY_MODULE_NAME);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new BlockException (BlockException::ERROR_BLOCK_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$fields = $block->getFields ();
			if (empty ($fields)) {
				return;
			}

			foreach ($fields as $field) {
				try {
					FieldManager::getInstance ($this->adb)->validateField ($field);
				} catch (FieldException $fe) {
					$blockId = $block->getId ();
					if ((empty ($blockId)) && ($fe->getMessage () != FieldException::ERROR_FIELD_EMPTY_BLOCK_ID)) {
						throw $fe;
					}
				}
			}
		}

		/**
		 * @param Block $block
		 *
		 * @throws BlockException
		 * @throws FieldException
		 */
		private function validateHeader ($block) {
			if ((empty ($block)) || (!($block instanceof Block))) {
				throw new BlockException (BlockException::ERROR_BLOCK_EMPTY);
			}

			$block->validate ();

			$moduleName = $block->getModuleName ();
			if (empty ($moduleName)) {
				throw new BlockException (BlockException::ERROR_BLOCK_EMPTY_MODULE_NAME);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new BlockException (BlockException::ERROR_BLOCK_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param Block $block
		 */
		public function deleteBlock ($block) {
			if ((empty ($block)) || (!($block instanceof Block))) {
				return;
			}

			$blockId = $block->getId ();
			if (empty ($blockId)) {
				return;
			}

			$moduleName = $block->getModuleName ();
			$identifier = $blockId;
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('block', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('block', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($block)));
			}
			FieldManager::getInstance ($this->adb)->deleteFieldsByBlockId ($blockId);
			$this->adb->pquery ('DELETE FROM vtiger_blocks WHERE blockid=?', array ($blockId));
		}

		/**
		 * @param string $moduleName
		 * @param boolean $ignoreLock
		 */
		public function deleteBlocks ($moduleName, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			}

			if (!$ignoreLock) {
				$whereClause = 'WHERE b.locked=0';
			} else {
				$whereClause = '';
			}

			$result = $this->adb->pquery ("SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=? {$whereClause}", array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$fm = FieldManager::getInstance ($this->adb);
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$blockId = $row ['blockid'];
					$fm->deleteFieldsByBlockId ($blockId);
					$this->adb->pquery ('DELETE FROM vtiger_blocks WHERE blockid=?', array ($blockId));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param integer $blockId
		 * @param boolean $headersOnly
		 *
		 * @return Block|null
		 */
		public function fetchBlock ($blockId, $headersOnly = false) {
			if (empty ($blockId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT b.*, t.name AS modulename FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid WHERE b.blockid=?',
				array ($blockId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row   = $this->adb->fetchByAssoc ($result, -1, false);
				$block = Block::getInstance ()
					->setId (intval ($blockId))
					->setFields (!$headersOnly ? FieldManager::getInstance ($this->adb)->fetchFieldsByBlockId ($blockId) : null)
					->setIsCustom (intval ($row ['iscustom']))
					->setLabel ($row ['blocklabel'])
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($row ['modulename'])
					->setSequence (intval ($row ['sequence']))
					->setShowTitle (intval ($row ['show_title']))
					->setVisibility (intval ($row ['visible']), intval ($row ['create_view']), intval ($row ['detail_view']), intval ($row ['edit_view']));
			} else {
				$block = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $block;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $includeDeleted
		 *
		 * @return Block[]|null
		 */
		public function fetchBlocks ($moduleName, $includeDeleted = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=? ORDER BY b.sequence',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$fm     = FieldManager::getInstance ($this->adb);
				$blocks = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$blocks [] = Block::getInstance ()
						->setId (intval ($row ['blockid']))
						->setFields ($fm->fetchFieldsByBlockId ($row ['blockid'], $includeDeleted))
						->setIsCustom (intval ($row ['iscustom']))
						->setLabel ($row ['blocklabel'])
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($moduleName)
						->setSequence (intval ($row ['sequence']))
						->setShowTitle (intval ($row ['show_title']))
						->setVisibility (intval ($row ['visible']), intval ($row ['create_view']), intval ($row ['detail_view']), intval ($row ['edit_view']));
				}

				if ($includeDeleted) {
					$deletedBlocks = $this->fetchDeletedBlocks ($moduleName);
				} else {
					$deletedBlocks = array ();
				}
				$blocks = (count ($deletedBlocks)) ? array_merge ($blocks, $deletedBlocks) : $blocks;
			} else {
				$blocks = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $blocks;
		}

		/**
		 * @param Block $block
		 * @param string|null $moduleTableName
		 * @param boolean $ignoreLock
		 *
		 * @return Block
		 * @throws BlockException
		 * @throws FieldException
		 */
		public function saveBlock ($block, $moduleTableName = null, $ignoreLock = true) {
			$this->validate ($block);

			$isDeleted = $block->isDeleted ();
			if ($isDeleted) {
				return $block;
			}

			$blockId  = $block->getId ();
			$sequence = $this->getSequenceNumber ($block);
			$this->adb->startTransaction ();
			if (!empty ($blockId)) {
				$result = $this->adb->pquery (
					'SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=? WHERE b.blockid=?',
					array ($block->getModuleName (), $block->getId ())
				);
				if (($result) && ($this->adb->num_rows ($result) > 0)) {
					$row      = $this->adb->fetchByAssoc ($result, -1, false);
					$isLocked = ($row ['locked'] == 1);
					$moduleId = intval ($row ['tabid']);
				} else {
					$isLocked = false;
					$blockId  = null;
					$moduleId = null;
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			} else {
				$isLocked = false;
				$blockId  = null;
				$moduleId = null;
			}

			if (empty ($blockId)) {
				$blockId = $this->adb->getUniqueID ('vtiger_blocks');
				$this->adb->pquery (
					'INSERT INTO vtiger_blocks (blockid, tabid, blocklabel, sequence, show_title, visible, create_view, detail_view, edit_view, display_status, iscustom, locked)
						SELECT ?, t.tabid, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? FROM vtiger_tab t WHERE t.name=?',
					array ($blockId, $block->getLabel (), $sequence, $block->getShowTitle (), $block->getVisibility (), $block->getVisibilityInCreateView (), $block->getVisibilityInDetailView (), $block->getVisibilityInEditView (), $block->getDisplayStatus (), $block->getIsCustom (), $block->isLocked (), $block->getModuleName ())
				);
				$block->setId ($blockId);
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->adb->pquery (
					'UPDATE vtiger_blocks SET blocklabel=?, sequence=?, show_title=?, visible=?, create_view=?, detail_view=?, edit_view=?, display_status=?, iscustom=?, locked=? WHERE blockid=? AND tabid=?',
					array ($block->getLabel (), $sequence, $block->getShowTitle (), $block->getVisibility (), $block->getVisibilityInCreateView (), $block->getVisibilityInDetailView (), $block->getVisibilityInEditView (), $block->getDisplayStatus (), $block->getIsCustom (), $block->isLocked (), $blockId, $moduleId)
				);
			}

			$this->saveFields ($block, $moduleTableName, $ignoreLock);
			$this->adb->completeTransaction ();

			return $block;
		}

		/**
		 * @param string $moduleName
		 * @param Block[]|null $blocks
		 * @param string|null $moduleTableName
		 * @param boolean $ignoreLock
		 */
		public function saveBlocks ($moduleName, $blocks, $moduleTableName = null, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			} else if (empty ($blocks)) {
				$this->deleteBlocks ($moduleName, $ignoreLock);
				return;
			}

			$processedBlockIds = array ();
			foreach ($blocks as $block) {
				if ($block->isDeleted ()) {
					continue;
				}

				$block->setModuleName ($moduleName);
				$this->saveBlock ($block, $moduleTableName, $ignoreLock);
				$processedBlockIds [] = $block->getId ();
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}

			$questionMarks = str_repeat ('?, ', (count ($processedBlockIds) - 1)) . '?';
			$result        = $this->adb->pquery (
				"SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=? WHERE b.blockid NOT IN ({$questionMarks}) {$whereClause}",
				array_merge (array ($moduleName), $processedBlockIds)
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$this->deleteBlock (Block::getInstance ()->setId (intval ($row ['blockid'])));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param Block $block
		 *
		 * @return Block
		 * @throws BlockException
		 * @throws FieldException
		 */
		public function updateBlockHeader ($block) {
			$this->validateHeader ($block);

			$blockId   = $block->getId ();
			$isDeleted = $block->isDeleted ();
			if (($isDeleted) || (empty ($blockId))) {
				return $block;
			}

			$result = $this->adb->pquery (
				'SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=? WHERE b.blockid=?',
				array ($block->getModuleName (), $block->getId ())
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$moduleId = intval ($row ['tabid']);
				$isLocked = $block->isLocked ();
				$sequence = $this->getSequenceNumber ($block);
				$this->adb->pquery (
					'UPDATE vtiger_blocks SET blocklabel=?, sequence=?, show_title=?, visible=?, create_view=?, detail_view=?, edit_view=?, display_status=?, iscustom=?, locked=? WHERE blockid=? AND tabid=?',
					array ($block->getLabel (), $sequence, $block->getShowTitle (), $block->getVisibility (), $block->getVisibilityInCreateView (), $block->getVisibilityInDetailView (), $block->getVisibilityInEditView (), $block->getDisplayStatus (), $block->getIsCustom (), $isLocked, $blockId, $moduleId)
				);
				$this->adb->completeTransaction ();
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $block;
		}

		/**
		 * @param Block $block
		 *
		 * @throws BlockException
		 */
		public function validateBlock ($block) {
			$this->validate ($block);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return BlockManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
