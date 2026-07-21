<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/ComboUtil.php');
	require_once ('include/platzilla/Objects/ApplicationInterface.php');
	require_once ('include/platzilla/Objects/ApplicationSubscriptionInterface.php');
	require_once ('include/platzilla/Managers/BlockManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/FieldInformationUtils.class.php');
	require_once ('vtlib/Vtiger/Block.php');
	require_once ('vtlib/Vtiger/Field.php');
	require_once ('vtlib/Vtiger/Module.php');

	/**
	 * Class LayoutBlockListHelper
	 *
	 * Clase Abstracta donde se definen las utilidades que brindan soporte a las funcionalidades del Editor de Campos
	 */
	abstract class LayoutBlockListHelper {

		const FIELD_GRID_VALUES    = 'vtiger_subfields_values';
		const FIELD_GRID_STRUCTURE = 'vtiger_subfields_special';
		
		const N0_IMPORT_FIELD = array (
			FieldInterface::UI_TYPE_ATTACHMENTS,
			FieldInterface::UI_TYPE_CALCULATED,
			FieldInterface::UI_TYPE_CALCULATED_LINK,
			FieldInterface::UI_TYPE_VIDEO,
			FieldInterface::UI_TYPE_CREATED_TIME,
			FieldInterface::UI_TYPE_IMAGE_DISPLAY,
			FieldInterface::UI_TYPE_IMAGE_REFERENCE,
			FieldInterface::UI_TYPE_MODULE_RECORDS,
			FieldInterface::UI_TYPE_IMAGE_DISPLAY,
			FieldInterface::UI_TYPE_CODE,
		);
		
		const DATE_FIELD_IMPORT = array(
			'CREATED-DATE' => 'Día de creación del nuevo registro',
			'NEXT-WEEK'    => 'Una semana después de crear el nuevo regsitro',
			'NEXT-MONTH'   => 'Un mes después de crear el nuevo regsitro',
		);
		
		/**
		 * Para obtener errores en los datos del bloque
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleId
		 * @param $moduleName
		 * @param $blockLabel
		 *
		 * @return null|string
		 */
		private static function getBlockDataError (PearDatabase $adb, $moduleId, $moduleName, $blockLabel) {
			if (strlen ($blockLabel) > 50) {
				return 'LENGTH_ERROR';
			}
			$result = $adb->pquery ('SELECT blocklabel FROM vtiger_blocks WHERE tabid=?', array ($moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			while ($row = $adb->fetchByAssoc ($result)) {
				if (strtolower ($blockLabel) == strtolower (getTranslatedString ($row ['blocklabel'], $moduleName))) {
					return 'yes';
				}
			}
			return null;
		}

		/**
		 * Para Obtener la secuencia del bloque
		 *
		 * @param \PearDatabase $adb
		 * @param $blockId
		 *
		 * @return int|null
		 */
		private static function getBlockSequence (PearDatabase $adb, $blockId) {
			$result = $adb->pquery ('SELECT sequence FROM vtiger_blocks WHERE blockid=?', array ($blockId));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row      = $adb->fetchByAssoc ($result);
				$sequence = intval ($row ['sequence']);
			} else {
				$sequence = null;
			}
			return $sequence;
		}

		/**
		 * Obtiene error en los datos de los campos personalizados
		 *
		 * @param \PearDatabase $adb
		 * @param $arguments
		 *
		 * @return null|string
		 */
		private static function getCustomFieldDataError (PearDatabase $adb, $arguments) {
			if (in_array ($arguments ['fieldtype'], array ('Picklist', 'MultiSelectCombo'))) {
				$result = $adb->pquery ('SHOW TABLES LIKE ?', array ("vtiger_{$arguments ['fieldname']}"));
				if (($result) && ($adb->num_rows ($result) > 0)) {
					return 'NAME_ERROR';
				}
			}
			return null;
		}

		/**
		 * Obtiene los campos para los filtros en las tareas automatizadas
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array
		 */
		private static function getFieldsInBackgroundTasksFilters (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT
					f.fieldname,
					d.taskname
				FROM
					vtiger_bgtasks_data_filters f
					INNER JOIN vtiger_bgtasks_data d ON d.taskid=f.taskid
				WHERE
					f.modulename=?',
				array ($moduleName)
			);
			$fields = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['fieldname'])) {
						$fields [ $row ['fieldname'] ]['backgroundtasksfilters'][] = $row ['taskname'];
					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fields;
		}

		/**
		 * Obtiene los campos para los parametros de las tareas ocultas
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array
		 */
		private static function getFieldsInBackgroundTasksParameters (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT
					p.parameterformula,
					d.taskname
				FROM
					vtiger_bgtasks_data_parameters p
					INNER JOIN vtiger_bgtasks_data d ON d.taskid=p.taskid
				WHERE
					d.modulename=? AND
					p.parametertype=?',
				array ($moduleName, 'SOURCE FIELD')
			);
			$fields = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['parameterformula'])) {
						$fields [ $row ['parameterformula'] ]['backgroundtasksparameters'][] = $row ['taskname'];
					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fields;
		}

		/**
		 * Obtiene los calculos asociados en el motor de calculos, para incorporar validacion del candadito
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array
		 */
		private static function getFieldsInCalculateSystem (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT 
					SUBSTRING_INDEX(SUBSTRING_INDEX(`firstelement`,".",-1),"@",1) as firstfield,
					SUBSTRING_INDEX(SUBSTRING_INDEX(`secondelement`,".",-1),"@",1) as secondfield,
					c.name
				FROM `vtiger_calculated_equation` e 
				INNER JOIN vtiger_calculated_system c ON c.equationid = e.calculated_equationid
				WHERE  
					c.modulename=? AND  
					(e.firstelemettype=? OR e.secondelementtype=?)',
				array ($moduleName, 'c', 'c')
			);
			$fields = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['firstfield']) && !is_numeric ($row ['firstfield'])) {
						$fields [$row ['firstfield']]['calculate_system'][] = $row ['name'];
					}
					if (!empty ($row ['secondfield']) && !is_numeric ($row ['secondfield'])) {
						$fields [$row ['secondfield']]['calculate_system'][] = $row ['name'];
					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fields;
		}

		/**
		 * Obtiene los campos involucrados en la vista calendario, para incorporar validacion del candadito
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array
		 */
		private static function getFieldsInCalendarViews (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT
					v.fromfieldname,
					v.titlefieldname,
					v.tofieldname,
					v.label
				FROM
					vtiger_calendarviews v
				WHERE
					v.modulename=?',
				array ($moduleName)
			);
			$fields = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['fromfieldname'])) {
						$fields [ $row ['fromfieldname'] ]['calendarviews'][] = $row ['label'];
					}
					if (!empty ($row ['titlefieldname'])) {
						$fields [ $row ['titlefieldname'] ]['calendarviews'][] = $row ['label'];
					}
					if (!empty ($row ['tofieldname'])) {
						$fields [ $row ['tofieldname'] ]['calendarviews'][] = $row ['label'];
					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fields;
		}

		/**
		 * Obtiene los campos con calculos para incorporar validacion del candadito
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array
		 */
		private static function getFieldsInCalculateFields (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT
					name,
					SUBSTRING_INDEX(`columnname`,".",-1) as columna,
					SUBSTRING_INDEX(SUBSTRING_INDEX(`relmodules`,".",2),".", -1) as tabla,
					SUBSTRING_INDEX(SUBSTRING_INDEX(`relmodules`,".",3),".", -1) as relfield
				FROM
					vtiger_calculated_fields
				WHERE
					modulename=? OR
					SUBSTRING_INDEX(`relmodules`,".",1)=?',
				array ($moduleName, $moduleName)
			);
			$fields = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['columna'])) {
						$fields [ $row ['columna'] ]['calculate_field'][] = $row ['name'];
					}
					if (
						!empty ($row ['relfield']) &&
						!empty ($row ['tabla']) &&
						$row ['relfield'] == self::FIELD_GRID_STRUCTURE
					) {
						$fields [ $row ['tabla'] ]['calculate_field'][] = $row ['name'];
					} else if (!empty ($row ['relfield']) && !empty ($row ['tabla'])) {
						$fields [ $row ['relfield'] ]['calculate_field'][] = $row ['name'];

					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fields;
		}

		/**
		 * Obtiene los campos para grafico de barra
		 *
		 * @param $fieldData
		 *
		 * @return null
		 */
		private static function getFieldInChartData ($fieldData) {
			$fieldOperation = null;
			foreach ($fieldData as $compoundField) {
				$dummy = explode ('.', $compoundField, 2);
				if ($dummy[ 0 ] == self::FIELD_GRID_VALUES) {
					$dummyField = explode ('@', $dummy[ 1 ], 2);
					$fieldOperation = $dummyField[ 0 ];
				} else {
					$fieldOperation = $dummy[ 1 ];
				}
				return $fieldOperation;
			}
		}

		/**
		 * Obtiene los campos asociados a graficos para incorporar validacion del candadito
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array
		 */
		private static function getFieldsInCharts (PearDatabase $adb, $moduleName) {
			$moduleName = '%' . trim ($moduleName) . '%';
			$result = $adb->query (
				"SELECT
					g.title,
					g.fieldoperation,
					g.fieldcompare,
					g.fieldgrouping
				FROM
					vtiger_graficos g
				WHERE
					g.fld_module LIKE '{$moduleName}'"
			);
			$fields = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['fieldoperation'])) {
						$fieldOperation = self::getFieldInChartData(json_decode ($row ['fieldoperation']));
						if (!empty ($fieldOperation) && !in_array($fieldOperation, array_keys($fields))) {
							$fields [ $fieldOperation ]['charts'][] = $row ['title'];
						}
					}
					if (!empty ($row ['fieldgrouping'])) {
						$fieldOperation = self::getFieldInChartData(array ($row ['fieldgrouping']));
						if (!empty ($fieldOperation) && !in_array($fieldOperation, array_keys($fields))) {
							$fields [ $fieldOperation ]['charts'][] = $row ['title'];
						}
					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fields;
		}

		/**
		 * Para obtener el nombre de entidad del campo
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array
		 */
		private static function getFieldsInEntityName (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT
					e.fieldname,
					t.tablabel
				FROM
					vtiger_entityname e
				INNER JOIN vtiger_tab t ON e.tabid = t.tabid
				WHERE
					e.modulename=?',
				array ($moduleName)
			);
			$fields = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['fieldname'])) {
						$fields [ $row ['fieldname'] ]['entityname'][] = $row ['tablabel'];
					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fields;
		}

		/**
		 * Para obtener los campos asociados a indicadores e incorporar validacion del candadito
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array
		 */
		private static function getFieldsInIndicators (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT 
					box_score,
					calculatedname
				FROM
					vtiger_box_score_data
				WHERE
					sourcemodule=?',
				array ($moduleName)
			);
			$fields = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['calculatedname'])) {
						$fields [ $row ['calculatedname'] ]['indicators'][] = $row ['box_score'];
					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fields;
		}

		/**
		 * Obtiene los campos que esten involucrados en reportes para incorporar validacion del candadito
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array
		 */
		private static function getFieldsInReports (PearDatabase $adb, $moduleName) {
			$moduleName = "{$moduleName}_%";
			$result = $adb->pquery (
				'SELECT DISTINCT 
					r.reportname,
					SUBSTRING_INDEX(SUBSTRING_INDEX(`columnname`,":",-2),":", 1) as fieldname,
					SUBSTRING_INDEX(SUBSTRING_INDEX(`columnname`,":",3),":", -1) as module_label
				FROM
					vtiger_selectcolumn rc
				INNER JOIN vtiger_report r ON r.queryid = rc.queryid
				WHERE
					SUBSTRING_INDEX(SUBSTRING_INDEX(`columnname`,":",3),":", -1) LIKE ?',
				array ($moduleName)
			);
			$fields = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['fieldname'])) {
						$fields [ $row ['fieldname'] ]['reports'][] = $row ['reportname'];
					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fields;
		}

		/**
		 * Mueve el bloque hacía abajo
		 *
		 * @param PearDatabase $adb
		 * @param integer $moduleId
		 * @param integer $blockId
		 * @param boolean $isInstance
		 */
		private static function moveBlockDown (PearDatabase $adb, $moduleId, $blockId, $isInstance) {
			$module = ModuleManager::getInstance ($adb)->fetchModuleById ($moduleId);
			if ((empty ($module)) || (!$module->getIsEntityType ())) {
				return;
			}

			$currentBlock = null;
			$nextBlock    = null;
			$blocks       = $module->getBlocks ();
			foreach ($blocks as $index => $block) {
				$currentBlockId = $block->getId ();
				if ($currentBlockId == $blockId) {
					$currentBlock = $block;
					$nextBlock    = isset ($blocks [ ($index + 1) ]) ? $blocks [ ($index + 1) ] : null;
					break;
				}
			}
			if ((empty ($currentBlock)) || (empty ($nextBlock))) {
				return;
			}

			$currentSequence = $currentBlock->getSequence ();
			$nextSequence    = $nextBlock->getSequence ();
			if ($currentSequence == $nextSequence) {
				$nextSequence++;
			}
			$bm = BlockManager::getInstance ($adb);
			$bm->updateBlockHeader ($currentBlock->setLocked ($isInstance)->setSequence ($nextSequence));
			$bm->updateBlockHeader ($nextBlock->setSequence ($currentSequence));
		}

		/**
		 * Mueve el bloque hacia arriba
		 *
		 * @param PearDatabase $adb
		 * @param integer $moduleId
		 * @param integer $blockId
		 * @param boolean $isInstance
		 */
		private static function moveBlockUp (PearDatabase $adb, $moduleId, $blockId, $isInstance) {
			$module = ModuleManager::getInstance ($adb)->fetchModuleById ($moduleId);
			if ((empty ($module)) || (!$module->getIsEntityType ())) {
				return;
			}

			$currentBlock  = null;
			$previousBlock = null;
			$blocks        = $module->getBlocks ();
			foreach ($blocks as $index => $block) {
				$currentBlockId = $block->getId ();
				if ($currentBlockId == $blockId) {
					$currentBlock  = $block;
					$previousBlock = isset ($blocks [ ($index - 1) ]) ? $blocks [ ($index - 1) ] : null;
					break;
				}
			}
			if ((empty ($currentBlock)) || (empty ($previousBlock))) {
				return;
			}

			$currentSequence  = $currentBlock->getSequence ();
			$previousSequence = $previousBlock->getSequence ();
			if ($currentSequence == $previousSequence) {
				$previousSequence--;
			}
			$bm = BlockManager::getInstance ($adb);
			$bm->updateBlockHeader ($currentBlock->setLocked ($isInstance)->setSequence ($previousSequence));
			$bm->updateBlockHeader ($previousBlock->setSequence ($currentSequence));
		}

		/**
		 * Mueve el campo hacia arriba o la derecha
		 *
		 * @param \PearDatabase $adb
		 * @param $whatToDo
		 * @param $blockId
		 * @param $fieldId
		 */
		private static function moveFieldDownOrRight (PearDatabase $adb, $whatToDo, $blockId, $fieldId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_field WHERE fieldid=? AND presence IN (0, 2)', array ($fieldId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$row             = $adb->fetchByAssoc ($result);
			$currentSequence = $row ['sequence'];
			if ($whatToDo == 'down') {
				$sql = 'SELECT * FROM vtiger_field WHERE sequence>? AND block=? AND presence IN (0, 2) ORDER BY sequence ASC LIMIT 1, 1';
			} else {
				$sql = 'SELECT * FROM vtiger_field WHERE sequence>? AND block=? AND presence IN (0, 2) ORDER BY sequence ASC LIMIT 0, 1';
			}
			$result = $adb->pquery ($sql, array ($currentSequence, $blockId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$row          = $adb->fetchByAssoc ($result);
			$nextFieldId  = $row ['blockid'];
			$nextSequence = $row ['sequence'];
			$adb->pquery ('UPDATE vtiger_field SET sequence=? WHERE fieldid=?', array ($nextSequence, $fieldId));
			$adb->pquery ('UPDATE vtiger_field SET sequence=? WHERE fieldid=?', array ($currentSequence, $nextFieldId));
		}

		/**
		 * Mueve el bloque hacia arriba o la izquierda
		 *
		 * @param \PearDatabase $adb
		 * @param $whatToDo
		 * @param $blockId
		 * @param $fieldId
		 */
		private static function moveFieldUpOrLeft (PearDatabase $adb, $whatToDo, $blockId, $fieldId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_field WHERE fieldid=? AND presence IN (0, 2)', array ($fieldId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$row             = $adb->fetchByAssoc ($result);
			$currentSequence = $row ['sequence'];
			if ($whatToDo == 'up') {
				$sql = 'SELECT * FROM vtiger_field WHERE sequence<? AND block=? AND presence IN (0, 2) ORDER BY sequence DESC LIMIT 1, 1';
			} else {
				$sql = 'SELECT * FROM vtiger_field WHERE sequence<? AND block=? AND presence IN (0, 2) ORDER BY sequence DESC LIMIT 0, 1';
			}
			$result           = $adb->pquery ($sql, array ($currentSequence, $blockId));
			$row              = $adb->fetchByAssoc ($result);
			$previousFieldId  = $row ['fieldid'];
			$previousSequence = $row ['sequence'];
			$adb->pquery ('UPDATE vtiger_field SET sequence=? WHERE fieldid=?', array ($previousSequence, $fieldId));
			$adb->pquery ('UPDATE vtiger_field SET sequence=? WHERE fieldid=?', array ($currentSequence, $previousFieldId));
		}

		/**
		 * Registra los campos en el bloque
		 *
		 * @param $blockId
		 * @param $blockLabel
		 * @param $fieldName
		 * @param $fieldUiType
		 */
		private static function registerBlockField ($blockId, $blockLabel, $fieldName, $fieldUiType) {
			if (!$fieldUiType) {
				return;
			}
			$field              = new Vtiger_Field ();
			$field->column      = ' ';
			$field->columntype  = '';
			$field->displaytype = 2;
			$field->label       = $blockLabel;
			$field->name        = $fieldName;
			$field->table       = '';
			$field->typeofdata  = '';
			$field->uitype      = $fieldUiType;
			$block              = new Vtiger_Block ();
			$block              = $block->getInstance ($blockId);
			$block->addField ($field);
		}

		/**
		 * Actualiza las listas relacionadas y las propiedades del bloque
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleId
		 * @param $blockId
		 * @param array $arguments
		 *
		 * @return array
		 */
		private static function updateRelatedListsAndBlockProperties (PearDatabase $adb, $moduleId, $blockId, array $arguments) {
			$fieldName   = '';
			$fieldUiType = null;
			if ($arguments ['blocktype'] == TODO_TASKS_BLOCK) {
				$result = $adb->pquery (
					'SELECT relation_id FROM vtiger_relatedlists WHERE tabid=? AND related_tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)',
					array ($moduleId, 'todotasks')
				);
				if (($result) || ($adb->num_rows ($result) > 0)) {
					$moduleOrigin    = Vtiger_Module::getInstance ($arguments ['fieldmodulename']);
					$relatedinstance = Vtiger_Module::getInstance ('todotasks');
					$function        = 'get_related_list';
					$moduleOrigin->setRelatedList ($relatedinstance, 'LBL_TODO_TASKS', array ('ADD'), $function);
					$relatedModuleId = $adb->getLastInsertID ();
					if ($relatedModuleId) {
						$adb->pquery ('INSERT INTO vtiger_relatedlists_properties (relation_id, modaledit) VALUES (?, 1)', array ($relatedModuleId));
					}
				}
				if ($arguments ['updateparentfield']) {
					$adb->pquery (
						'UPDATE vtiger_blocks_properties SET update_parentfield=?, oncomplete_value=?, onprogress_value=? WHERE blockid=?',
						array ($arguments ['updateparentfield'], $arguments ['oncompletevalue'], $arguments ['onprogressvalue'], $blockId)
					);
				}
				$fieldName   = 'todotasks';
				$fieldUiType = 100;
			} else if (($arguments ['blocktype'] == PROGRESS_BAR_BLOCK) && ($arguments ['relatedfieldname']) && ($arguments ['relatedmoduleid'])) {
				$adb->pquery (
					'UPDATE vtiger_blocks_properties SET relmodule=?, relfieldname=? WHERE blockid=?',
					array ($arguments ['relatedmoduleid'], $arguments ['relatedfieldname'], $blockId)
				);
				$fieldName   = "progress_bar_{$blockId}";
				$fieldUiType = 101;
			}
			return array (
				'fieldname'   => $fieldName,
				'fielduitype' => $fieldUiType,
			);
		}

		/**
		 * Añadir bloque
		 *
		 * @param \PearDatabase $adb
		 * @param array $arguments
		 * @param $isInstance
		 *
		 * @return null|string
		 */
		public static function addBlock (PearDatabase $adb, array $arguments, $isInstance) {
			$moduleId = getTabid ($arguments ['fieldmodulename']);
			$isCustom = 0;
			if (key_exists ('iscustom', $arguments)) {
				$isCustom = $arguments ['iscustom'];
			}
			$error = self::getBlockDataError ($adb, $moduleId, $arguments ['fieldmodulename'], $arguments ['blocklabel']);
			if ($error !== null) {
				return $error;
			}
			$currentSequence = self::getBlockSequence ($adb, $arguments ['previousblockid']);
			if ($currentSequence !== null) {
				$adb->pquery (
					'UPDATE vtiger_blocks SET sequence=sequence+1 WHERE tabid=? AND sequence>?',
					array ($moduleId, $currentSequence)
				);
			} else {
				$currentSequence = 1;
			}

			$block = Block::getInstance ()
				->setIsCustom ($isCustom)
				->setLabel ($arguments ['blocklabel'])
				->setLocked ($isInstance)
				->setModuleName ($arguments ['fieldmodulename'])
				->setSequence ($currentSequence + 1)
				->setShowTitle (BlockInterface::SHOW_TITLE_YES)
				->setVisibility (BlockInterface::VISIBILITY_VISIBLE);
			BlockManager::getInstance ($adb)->saveBlock ($block);
			$blockId = $block->getId ();
			if (!$blockId) {
				return null;
			}
			$result = $adb->pquery (
				'INSERT INTO vtiger_blocks_properties (blockid, blocktype) VALUES (?, ?)',
				array ($blockId, intval ($arguments ['blocktype']))
			);
			if (!$result) {
				return null;
			}
			$result      = self::updateRelatedListsAndBlockProperties ($adb, $moduleId, $blockId, $arguments);
			$fieldName   = $result ['fieldname'];
			$fieldUiType = $result ['fielduitype'];
			self::registerBlockField ($blockId, $arguments ['blocklabel'], $fieldName, $fieldUiType);
			return null;
		}

		/**
		 * Agregar campos cutomizados/personalizados
		 *
		 * @param \PearDatabase $adb
		 * @param array $arguments
		 * @param $isInstance
		 *
		 * @return null
		 * @throws \Exception
		 */
		public static function addCustomField (PearDatabase $adb, array $arguments, $isInstance) {
			if ((!is_numeric ($arguments ['blockid'])) || (!empty ($arguments ['fieldid']))) {
				return null;
			}

			$error = self::getCustomFieldDataError ($adb, $arguments);
			if ($error) {
				throw new Exception ($error);
			}
			$tableName = FieldInformationUtils::getTableName ($arguments ['fieldmodulename'], $arguments ['fieldname']);
			$field     = Field::getInstance ()
				->setBlockId ($arguments ['blockid'])
				->setCalculationName ($arguments ['calculationid'])
				->setColumnName ($arguments ['fieldname'])
				->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setLabel ($arguments ['fieldlabel'])
				->setLocked ($isInstance)
				->setMandatory (false)
				->setMassEditable (FieldInterface::MASS_EDITABLE_USER_DEFINED)
				->setModuleName ($arguments ['fieldmodulename'])
				->setName ($arguments ['fieldname'])
				->setPresence (FieldInterface::PRESENCE_USER_DEFINED)
				->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)
				->setReadOnly (FieldInterface::READ_WRITE)
				->setTableName ($tableName)
				->setUiType ($arguments ['fieldtype'], $arguments ['fieldlength'], $arguments ['fielddecimallength']);

			if (in_array ($arguments ['fieldtype'], array (FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_PICKLIST))) {
				$picklistValues = array ();
				foreach ($arguments ['fieldpicklistvalues'] as $fieldPicklistValue) {
					$picklistValues [] = PicklistValue::getInstance (true)->setLocked ($isInstance)->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ($fieldPicklistValue);
				}
				$picklist = Picklist::getInstance ()
					->setName ($arguments ['fieldname'])
					->setValues ($picklistValues);
				$field->setPicklist ($picklist);
			} else if (in_array ($arguments ['fieldtype'], array (FieldInterface::UI_TYPE_PIPELINE))) {
				$pipeline = Pipeline::getInstance ()
					->setFieldName ($arguments ['fieldname'])
					->setModuleName ($arguments ['fieldmodulename'])
					->setValues ($arguments ['fieldpicklistvalues']);
				$field->setPipeline ($pipeline);
			} else if ($arguments ['fieldtype'] == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
				$reference = FieldModuleReference::getInstance ()
					->setFieldName ($arguments ['fieldname'])
					->setModuleName ($arguments ['fieldmodulename'])
					->setReferencedModuleName ($arguments ['fieldrelatedmodule']);
				$field->setModuleReferences (array ($reference));
			} else if ($arguments ['fieldtype'] == FieldInterface::UI_TYPE_MODULE_RECORDS) {
				$reference = FieldModuleReference::getInstance ()
					->setFieldName ($arguments ['fieldname'])
					->setModuleName ($arguments ['fieldmodulename'])
					->setReferencedModuleName ($arguments ['fieldrelatedrecords']);
				$field->setModuleReferences (array ($reference));
			}
			FieldManager::getInstance ($adb)->saveField ($field);
		}

		/**
		 * Cambia el orden para bloques y campos del modulo
		 *
		 * @param \PearDatabase $adb
		 * @param $whatToDo
		 * @param $moduleId
		 * @param $blockId
		 * @param $fieldId
		 * @param $isInstance
		 */
		public static function changeOrder (PearDatabase $adb, $whatToDo, $moduleId, $blockId, $fieldId, $isInstance) {
			if ($whatToDo == 'block_down') {
				self::moveBlockDown ($adb, $moduleId, $blockId, $isInstance);
			} else if ($whatToDo == 'block_up') {
				self::moveBlockUp ($adb, $moduleId, $blockId, $isInstance);
			} else if (($whatToDo == 'down') || ($whatToDo == 'Right')) {
				self::moveFieldDownOrRight ($adb, $whatToDo, $blockId, $fieldId);
			} else if (($whatToDo == 'up') || ($whatToDo == 'Left')) {
				self::moveFieldUpOrLeft ($adb, $whatToDo, $blockId, $fieldId);
			} else if ($whatToDo == 'show') {
				$adb->pquery ("UPDATE vtiger_blocks SET display_status='1' WHERE blockid=?", array ($blockId));
			} else if ($whatToDo == 'hide') {
				$adb->pquery ("UPDATE vtiger_blocks SET display_status='0' WHERE blockid=?", array ($blockId));
			}
		}

		/**
		 * Para cambiar el orden de la listas relacionadas en el modulo
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleId
		 * @param $whatToDo
		 * @param $sequence
		 * @param $relationshipId
		 */
		public static function changeRelatedListOrder (PearDatabase $adb, $moduleId, $whatToDo, $sequence, $relationshipId) {
			if (empty ($whatToDo)) {
				return;
			}
			if ($whatToDo == 'move_up') {
				$result = $adb->pquery (
					'SELECT relation_id, sequence FROM vtiger_relatedlists WHERE sequence<? AND tabid=? ORDER BY sequence DESC LIMIT 0, 1',
					array ($sequence, $moduleId)
				);
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					return;
				}
				$row = $adb->fetchByAssoc ($result);
				$adb->pquery ('UPDATE vtiger_relatedlists SET sequence=? WHERE relation_id=? AND tabid=?', array ($row ['sequence'], $relationshipId, $moduleId));
				$adb->pquery ('UPDATE vtiger_relatedlists SET sequence=? WHERE relation_id=? AND tabid=?', array ($sequence, $row ['relation_id'], $moduleId));
			} else if ($whatToDo == 'move_down') {
				$result = $adb->pquery (
					'SELECT relation_id, sequence FROM vtiger_relatedlists WHERE sequence>? AND tabid=? ORDER BY sequence ASC LIMIT 0, 1',
					array ($sequence, $moduleId)
				);
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					return;
				}
				$row = $adb->fetchByAssoc ($result);
				$adb->pquery ('UPDATE vtiger_relatedlists SET sequence=? WHERE relation_id=? AND tabid=?', array ($row ['sequence'], $relationshipId, $moduleId));
				$adb->pquery ('UPDATE vtiger_relatedlists SET sequence=? WHERE relation_id=? AND tabid=?', array ($sequence, $row ['relation_id'], $moduleId));
			}
		}

		/**
		 * Para borrar bloque
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param integer $blockId
		 */
		public static function deleteBlock (PearDatabase $adb, $moduleName, $blockId) {
			$module = ModuleManager::getInstance ($adb)->fetchModule ($moduleName);
			if ((empty ($module)) || (!$module->getIsEntityType ())) {
				return;
			}

			$currentBlock = null;
			$blocks       = $module->getBlocks ();
			foreach ($blocks as $index => $block) {
				$currentBlockId = $block->getId ();
				if ($currentBlockId == $blockId) {
					$currentBlock = $block;
					unset ($blocks [ $index ]);
					break;
				}
			}

			$bm = BlockManager::getInstance ($adb);
			$bm->deleteBlock ($currentBlock);
			/** @var Block[] $blocks */
			$blocks = array_values ($blocks);
			foreach ($blocks as $index => $block) {
				$bm->updateBlockHeader ($block->setSequence ($index + 1));
			}
		}

		/**
		 * Para eliminar campos customizados/personalizados
		 *
		 * @param \PearDatabase $adb
		 * @param $fieldModuleName
		 * @param $fieldId
		 */
		public static function deleteCustomField (PearDatabase $adb, $fieldModuleName, $fieldId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_field WHERE fieldid=?', array ($fieldId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$row                  = $adb->fetchByAssoc ($result);
			$fieldType            = explode ('~', $row ['typeofdata']);
			$normalizedFieldLabel = str_replace (' ', '_', $row ['fieldlabel']);
			$adb->pquery ('DELETE FROM vtiger_field WHERE fieldid=? AND vtiger_field.presence IN (0, 2)', array ($fieldId));
			$adb->pquery ('DELETE FROM vtiger_profile2field WHERE fieldid=?', array ($fieldId));
			$adb->pquery ('DELETE FROM vtiger_def_org_field WHERE fieldid=?', array ($fieldId));
			if ($row ['tablename'] != 'vtiger_crmentity') {
				$result = $adb->query ("SHOW COLUMNS FROM {$row ['tablename']} LIKE '{$adb->sql_escape_string ($row ['columnname'])}'");
				if (($result) && ($adb->num_rows ($result) > 0)) {
					$adb->query ("ALTER TABLE {$row ['tablename']} DROP COLUMN {$adb->sql_escape_string ($row ['columnname'])}");
				}
			}
			$deleteColumnName = "{$row ['tablename']}:{$row ['columnname']}:{$row ['fieldname']}:{$fieldModuleName}_{$normalizedFieldLabel}:{$fieldType [0]}";
			$filterColumnName = "{$row ['tablename']}:{$row ['columnname']}:{$row ['fieldname']}:{$fieldModuleName}_{$normalizedFieldLabel}";
			$selectColumnName = "{$row ['tablename']}:{$row ['columnname']}:{$fieldModuleName}_{$normalizedFieldLabel}:{$row ['fieldname']}:{$fieldType [0]}";
			$reportColumnName = "{$row ['tablename']}:{$row ['columnname']}:{$normalizedFieldLabel}";
			// we have to remove the entries in customview and report related tables which have this field ($row ['columnname'])
			$adb->pquery ('DELETE FROM vtiger_cvcolumnlist WHERE columnname=?', array ($deleteColumnName));
			$adb->pquery ('DELETE FROM vtiger_cvstdfilter WHERE columnname=?', array ($filterColumnName));
			$adb->pquery ('DELETE FROM vtiger_cvadvfilter WHERE columnname=?', array ($deleteColumnName));
			$adb->pquery ('DELETE FROM vtiger_selectcolumn WHERE columnname=?', array ($selectColumnName));
			$adb->pquery ('DELETE FROM vtiger_relcriteria WHERE columnname=?', array ($selectColumnName));
			$adb->pquery ('DELETE FROM vtiger_reportsortcol WHERE columnname=?', array ($selectColumnName));
			$adb->pquery ('DELETE FROM vtiger_reportdatefilter WHERE datecolumnname=?', array ($filterColumnName));
			$adb->pquery ('DELETE FROM vtiger_reportsummary WHERE columnname LIKE ?', array ("%{$reportColumnName}%"));
			// HANDLE HERE - we have to remove the table for other picklist type values which are text area and multiselect combo box
			if ($fieldType [0] == 15) {
				$adb->query ("DROP TABLE vtiger_{$adb->sql_escape_string ($row ['columnname'])}");
			}
		}

		/**
		 * Obtiene el usser administrador
		 *
		 * @param \PearDatabase $adb
		 *
		 * @return array|string
		 */
		public static function getAdministrators (PearDatabase $adb) {
			$result = $adb->query ("SELECT concat(first_name, ' ', last_name, ' (', user_name, ')') AS admin FROM vtiger_users WHERE is_admin='on' AND status='Active'");
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return '';
			}
			$administrators = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$administrators [] = $row ['admin'];
			}
			return $administrators;
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param ModuleRelationship[] $relationships
		 * @param string $moduleName
		 *
		 * @return array/null
		 * @throws Exception
		 */
		public static function getAvailableFieldByRelatedList ($adb, $relationships, $moduleName) {
			if (!count ($relationships)) {
				return null;
			}
			$availableFields = array ();
			$fm              = FieldManager::getInstance ($adb);
			if (!empty ($moduleName)) {
				$availableFields[ $moduleName ] = $fm->fetchFieldHeaders ($moduleName);
			}
			foreach ($relationships as $relationship) {
				$availableFields[ $relationship->getRelatedModuleName ()] = $fm->fetchFieldHeaders ($relationship->getRelatedModuleName ());
				if (!empty ($relationship->getRelatedFields ())) {
					foreach ($relationship->getRelatedFields ()->getFieldImport () as $fieldName => $relatedImport) {
						if ($relatedImport[0] == 'LIST') {
							$thisField = $fm->fetchFieldByName ($relationship->getRelatedModuleName (), $fieldName, true);
							if (empty($thisField)) {
								continue;
							} else if ($thisField->getUiType () == FieldInterface::UI_TYPE_PICKLIST) {
								$pickList = PicklistManager::getInstance ($adb)->fetchPicklistByName ($fieldName, true);
								if (empty ($pickList)) {
									continue;
								}
								$smarty = new vtigerCRM_Smarty ();
								$smarty->assign ('PICKLIST_VALUES', $pickList);
								$smarty->assign ('VALUE', $relatedImport[1]);
								$availableFields[ $fieldName ] = $smarty->fetch ('utils/HTMLPickListOptions.tpl');
							} else {
								$pipeLine = PipelineManager::getInstance ($adb)->fetchPipeline ($relationship->getRelatedModuleName (), $fieldName);
								if (empty ($pipeLine)) {
									continue;
								}
								$smarty = new vtigerCRM_Smarty ();
								$smarty->assign ('PIPELINE_VALUES', $pipeLine->getValues ());
								$smarty->assign ('VALUE', $relatedImport[1]);
								$availableFields[ $fieldName ] = $smarty->fetch ('utils/HTMLPipelimeOptions.tpl');
							}
						}
					}
				}
			}
			return $availableFields;
		}
		
		/**
		 * Obtiene los reportes disponibles para el modulo
		 *
		 * @param \PearDatabase $adb
		 * @param $currentUser
		 * @param $moduleName
		 *
		 * @return integer
		 */
		public static function getAvailableReport (PearDatabase $adb, $currentUser, $moduleName) {
			$fieldEntries = PlatformUtils::getFieldListEntries ($adb, $currentUser, $moduleName);
			$result       = $adb->pquery ('SELECT reportavailable FROM vtiger_module_report WHERE tabid=?', array ($fieldEntries [0]['tabid']));
			$row          = ($result) && ($adb->num_rows ($result) > 0) ? $adb->fetchByAssoc ($result) : null;
			return $row ? $row ['reportavailable'] : 0;
		}

		/**
		 * Obtiene los campos personalizados de los modulos de soporte
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array
		 */
		public static function getCustomFieldSupportedModules (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				"SELECT t.name, t.tablabel FROM vtiger_tab t WHERE t.presence=0 AND t.isentitytype=1 AND t.name NOT IN ('Potentials', 'Events', 'instances', ?)",
				array ($moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$modules [] = array (
					'text'  => $row ['tablabel'],
					'value' => $row ['name'],
				);
			}
			usort (
				$modules,
				function ($moduleA, $moduleB) {
					return strcmp ($moduleA ['text'], $moduleB ['text']);
				}
			);
			return $modules;
		}

		/**
		 * Obtener la entidad del modulo
		 *
		 * @param \PearDatabase $adb
		 * @param $excludeModuleName
		 *
		 * @return array|null
		 */
		public static function getEntityModules (PearDatabase $adb, $excludeModuleName) {
			$result = $adb->pquery (
				'SELECT t.name, t.tablabel AS label FROM vtiger_tab t WHERE t.presence IN (0, 2) AND t.isentitytype=1 AND t.customized IN (0, 1) AND name<>?',
				array ($excludeModuleName)
			);
			if ($adb->num_rows ($result) > 0) {
				$modules = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['label'] = getTranslatedString ($row ['label'], $row ['name']);
					$modules [] = $row;
				}
				usort (
					$modules,
					function ($moduleA, $moduleB) {
						return strcmp ($moduleA ['label'], $moduleB ['label']);
					}
				);
			} else {
				$modules = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}

			return $modules;
		}

		/**
		 * Obtiene las aplicaciones activa por modulo
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 * @param null $instanceId
		 *
		 * @return array|null
		 */
		public static function getActiveApplicationsByModule (PearDatabase $adb, $moduleName, $instanceId = null) {
			$moduleId = getTabid ($moduleName);
			if (!empty ($instanceId)) {
				$result = $adb->pquery (
					'SELECT
						a.*
					FROM
						vtiger_config_applications a
						INNER JOIN vtiger_configapps_tab cat ON cat.config_applicationsid=a.config_applicationsid
						INNER JOIN vtiger_instanceapplications ia ON ia.applicationcode=a.app_code
						INNER JOIN vtiger_instances i ON i.code=ia.instancecode
					WHERE
						ia.status IN (?, ?) AND
						a.app_status=? AND
						cat.tabid=? AND
						i.instanceid=?',
					array (ApplicationSubscriptionInterface::STATUS_ACTIVE, ApplicationSubscriptionInterface::STATUS_SUBSCRIBED, ApplicationInterface::STATUS_ACTIVE, $moduleId, $instanceId)
				);
			} else {
				$result = $adb->pquery (
					'SELECT
						a.*
					FROM
						vtiger_config_applications a
						INNER JOIN vtiger_configapps_tab cat ON cat.config_applicationsid=a.config_applicationsid
					WHERE
						a.app_status=? AND
						cat.tabid=?',
					array (ApplicationInterface::STATUS_ACTIVE, $moduleId)
				);
			}
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$applications = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$applications [] = $row;
			}
			return $applications;
		}

		/**
		 * @param $adb
		 * @param string $moduleName
		 *
		 * @return Block|null
		 * @throws BlockException
		 * @throws FieldException
		 */
		public static function getCustomBlock ($adb, $moduleName) {
			if (empty ($moduleName)) {
				return null;
			}
			$bm             = BlockManager::getInstance ($adb);
			$blocks         = $bm->fetchBlocks ($moduleName);
			$hasCustomBlock = false;
			if (empty ($blocks)) {
				return null;
			}
			foreach ($blocks as $block) {
				if ($block->getLabel() == BlockInterface::CUSTOM_BLOCK) {
					return $block;
				}
			}

			if (!$hasCustomBlock) {
				return $bm->saveBlock (
					Block::getInstance ()
						->setId (null)
						->setDeleted (false)
						->setIsCustom (Block::IS_CUSTOM_YES)
						->setLabel (BlockInterface::CUSTOM_BLOCK)
						->setLocked (true)
						->setModuleName ($moduleName)
						->setSequence (null),
					$moduleName
				);
			}
		}

		/**
		 * Obtiene los bloques del modulo
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array|null
		 */
		public static function getModuleBlocks (PearDatabase $adb, $moduleName) {
			$moduleId = getTabid ($moduleName);
			$result   = $adb->pquery ('SELECT * FROM vtiger_blocks WHERE tabid=? ORDER BY sequence', array ($moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$blocks = array ();
			while ($row = $adb->fetch_array ($result)) {
				$blocks [] = $row;
			}
			return $blocks;
		}

		/**
		 * Obtiene el modulo de la lista relacionada
		 *
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array|null
		 */
		public static function getModuleRelatedLists (PearDatabase $adb, $moduleName) {
			$moduleId = getTabid ($moduleName);
			$result   = $adb->pquery (
				'SELECT
					rl.*,
					t.name AS relatedmodulename
				FROM
					vtiger_relatedlists rl
					INNER JOIN vtiger_tab t ON t.tabid=rl.related_tabid AND t.presence=0
				WHERE
					rl.tabid=?
				ORDER BY
					rl.sequence',
				array ($moduleId)
			);
			if ($adb->num_rows ($result) > 0) {
				$relatedLists = array ();
				while ($row = $adb->fetchByAssoc ($result)) {
					$relatedLists [] = array (
						'id'                => $row ['relation_id'],
						'actions'           => explode (',', $row ['actions']),
						'label'             => getTranslatedString ($row ['label'], $moduleName),
						'relatedmodulename' => $row ['relatedmodulename'],
						'sequence'          => intval ($row ['sequence']),
					);
				}
			} else {
				$relatedLists = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}

			return $relatedLists;
		}

		/**
		 * Obtiene los valores del campo tipo lista desplegable
		 *
		 * @param \PearDatabase $adb
		 * @param $picklistFieldName
		 *
		 * @return array
		 */
		public static function getPicklistValues (PearDatabase $adb, $picklistFieldName) {
			$result = $adb->query ("SELECT {$picklistFieldName}id, {$picklistFieldName} FROM vtiger_{$picklistFieldName}");
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$values = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$values [] = array (
					'text'  => html_entity_decode ($row [ $picklistFieldName ], ENT_QUOTES, 'UTF-8'),
					'value' => html_entity_decode ($row [ $picklistFieldName ], ENT_QUOTES, 'UTF-8'),
					'id'    => $row ["{$picklistFieldName}id"],
				);
			}
			return $values;
		}

		/**
		 * Obtener el id del campo seleccionado
		 *
		 * @param \PearDatabase $adb
		 * @param $columnName
		 * @param $parentFieldId
		 *
		 * @return null
		 */
		public static function getSelectedFieldId (PearDatabase $adb, $columnName, $parentFieldId) {
			if (!$columnName) {
				return $parentFieldId;
			}
			$result = $adb->pquery ('SELECT fieldid FROM vtiger_field WHERE columnname=?', array ($columnName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result);
			return $row ['fieldid'];
		}

		/**
		 * Cache estático para campos no modificables por módulo
		 * @var array
		 */
		private static $unmodifiableFieldsCache = array();

		/**
		 * Obtiene los campos que no se pueden modificar para el modulo
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array
		 */
		public static function getUnmodifiableFields (PearDatabase $adb, $moduleName) {
			// Verificar si ya existe en caché
			if (isset(self::$unmodifiableFieldsCache[$moduleName])) {
				return self::$unmodifiableFieldsCache[$moduleName];
			}

			$unmodifiableFields = self::getFieldsInBackgroundTasksFilters ($adb, $moduleName);
			$unmodifiableFields = array_merge ($unmodifiableFields, self::getFieldsInBackgroundTasksParameters ($adb, $moduleName));
			$unmodifiableFields = array_merge ($unmodifiableFields, self::getFieldsInCalendarViews ($adb, $moduleName));
			$unmodifiableFields = array_merge ($unmodifiableFields, self::getFieldsInCharts ($adb, $moduleName));
			$unmodifiableFields = array_merge ($unmodifiableFields, self::getFieldsInCalculateFields ($adb, $moduleName));
			$unmodifiableFields = array_merge ($unmodifiableFields, self::getFieldsInCalculateSystem ($adb, $moduleName));
			$unmodifiableFields = array_merge ($unmodifiableFields, self::getFieldsInReports ($adb, $moduleName));
			$unmodifiableFields = array_merge ($unmodifiableFields, self::getFieldsInIndicators ($adb, $moduleName));
			$unmodifiableFields = array_merge ($unmodifiableFields, self::getFieldsInEntityName ($adb, $moduleName));

			$result = array_filter ($unmodifiableFields);

			// Guardar en caché
			self::$unmodifiableFieldsCache[$moduleName] = $result;

			return $result;
		}

		/**
		 * Obtiene los modulos visibles
		 *
		 * @param \PearDatabase $adb
		 *
		 * @return array
		 */
		public static function getVisibleModules (PearDatabase $adb) {
			$result = $adb->query ('SELECT name, tablabel FROM vtiger_tab WHERE presence IN (0) AND customized IN (0, 1) ORDER BY name');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = array (
					'text'  => getTranslatedString ($row ['tablabel']),
					'value' => $row ['name'],
				);
			}
			return $modules;
		}

		/**
		 * Obtiene visibilidad del campo por perfil
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $profilesList
		 *
		 * @return array
		 */
		public static function getVisibilityFieldByProfiles ($adb, $moduleName, $profilesList) {
			if (!count ($profilesList)) {
				$profiles = $adb->sql_expr_datalist(array (1));
			} else {
				$profiles = $adb->sql_expr_datalist($profilesList);
			}

			$result = $adb->query (
				"SELECT
					p2f.fieldid,
					p2f.visible
				FROM
					vtiger_profile2field p2f
				INNER JOIN vtiger_tab t ON t.tabid = p2f.tabid
				WHERE
					t.name='{$moduleName}' AND 
					p2f.profileid IN {$profiles}"
			);
			$fields = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['fieldid'])) {
						$fields [ $row ['fieldid'] ] = $row ['visible'];
					}
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fields;
		}

	}
