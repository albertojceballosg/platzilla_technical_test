<?php
	require_once ('include/platzilla/Objects/FieldInterface.php');

	class JSDetailViewEditableUtils {

		/** @var JSDetailViewEditableUtils|null */
		private static $INSTANCES = null;

		/** @var string */
		private $editableFunctions;

		/** @var string */
		private $footer;

		/** @var string */
		private $header;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
			$this->initialize ();
		}

		/**
		 * @param array $dataField
		 */
		private function getFieldEditableFunction ($dataField) {
			$editableOptions = '';
			if (in_array($dataField['ui'], array (FieldInterface::UI_TYPE_PICKLIST, FieldInterface::UI_TYPE_GLOBAL_PICKLIST))) {
				$source = $this->getOptionsList ($dataField['options']);
				if (!empty($source)) {
					$editableOptions = $source;
				}
			} else if (in_array($dataField['ui'], array (FieldInterface::UI_TYPE_OWNER))) {
				$source = $this->getUsersList ($dataField['options'][1]);
				if (!empty($source)) {
					$editableOptions = $source;
				}
			}
			
			// Agregar función display para campos numéricos (uitype=7)
			if (in_array($dataField['ui'], array (FieldInterface::UI_TYPE_NUMBER, FieldInterface::UI_TYPE_PERCENTAGE))) {
				$displayFunction = "display: function(value) {\n";
				$displayFunction .= "    // Verificar si el valor original era NULL usando el atributo data-is-null\n";
				$displayFunction .= "    var \$element = jQuery(this);\n";
				$displayFunction .= "    var isNull = \$element.data('is-null');\n";
				$displayFunction .= "    if (isNull === true || isNull === 'true') {\n";
				$displayFunction .= "        return '_';\n";
				$displayFunction .= "    }\n";
				$displayFunction .= "    if (value === null || value === '' || value === undefined) {\n";
				$displayFunction .= "        return '_';\n";
				$displayFunction .= "    }\n";
				$displayFunction .= "    // Convertir a número para evaluar (manejar formato europeo)\n";
				$displayFunction .= "    var cleanValue = String(value).replace(',', '.');\n";
				$displayFunction .= "    var numValue = parseFloat(cleanValue);\n";
				$displayFunction .= "    if (isNaN(numValue)) {\n";
				$displayFunction .= "        return '_';\n";
				$displayFunction .= "    }\n";
				$displayFunction .= "    // Si es 0, mostrar 0 con formato\n";
				$displayFunction .= "    if (numValue === 0) {\n";
				$displayFunction .= "        return typeof formatUserNumber === 'function' ? formatUserNumber(0, 2) : '0.00';\n";
				$displayFunction .= "    }\n";
				$displayFunction .= "    // Para valores positivos y negativos, mostrar el valor original ya formateado\n";
				$displayFunction .= "    return value;\n";
				$displayFunction .= "}";
				$editableOptions .= (!empty ($editableOptions)) ? ",\n{$displayFunction}" : $displayFunction;
			}
			
			if ($dataField['mandatory'] == 'M') {
				$validate  = 'validate: function(value) {' . "\n";
				$validate .= "if(jQuery.trim (value) == '') {\n";
				$validate .= "return 'Este campo  es requiredo';\n";
				$validate .= '}';
				if (in_array($dataField['ui'], array (FieldInterface::UI_TYPE_NUMBER, FieldInterface::UI_TYPE_PERCENTAGE))) {
					$validate .= "if(!jQuery.isNumeric(value)) {\n";
					$validate .= "return 'El valor debe ser numérico';\n";
					$validate .= '}';
				}
				$validate .= "\n}";
				$editableOptions .= (!empty ($editableOptions)) ? ",\n{$validate}" : $validate;
			} else if (in_array($dataField['ui'], array (FieldInterface::UI_TYPE_NUMBER, FieldInterface::UI_TYPE_PERCENTAGE))) {
				$validate  = 'validate: function(value) {' . "\n";
				$validate .= "if(!jQuery.isNumeric(value)) {\n";
				$validate .= "return 'El valor debe ser numérico';\n";
				$validate .= "}\n}";
				$editableOptions .= (!empty ($editableOptions)) ? ",\n{$validate}" : $validate;
			}
			$editableOptions = (!empty($editableOptions)) ? "{{$editableOptions}}" : '';
			$fieldName = json_encode($dataField['fldname']);
			$this->editableFunctions .= "jQuery ('#' + {$fieldName}).editable({$editableOptions});\n";
		}

		private function initialize () {
			$this->editableFunctions = '';
			$this->header            = '<script type="text/javascript">' . "\n";
			$this->header           .= 'jQuery (document).ready(function(){' . "\n";
			$this->header           .= "jQuery.fn.editable.defaults.mode = 'popup';" . "\n";
			$this->footer            = '});' . "\n";
			$this->footer           .= '</script>' . "\n";
		}

		/**
		 * @param array $options
		 *
		 * @return string
		 */
		private function getOptionsList ($options) {
			if (!count ($options)) {
				return '';
			}
			$source = "\n". 'source: [';
			foreach ($options as $option) {
				$value = json_encode($option[0]);
				$text = json_encode($option[1]);
				$source .= "{value: {$value}, text: {$text}},". "\n";
			}
			$source .= ']';
			return $source;
		}

		private function getUsersList ($options) {
			if (!count ($options)) {
				return '';
			}
			$source = "\n". 'source: [';
			foreach ($options as $userId => $option) {
				$userName = key($option);
				$value = json_encode($userId);
				$text = json_encode($userName);
				$source .= "{value: {$value}, text: {$text}},". "\n";
			}
			$source .= ']';
			return $source;
		}

		/**
		 * @param array $params
		 * @param Smarty $smarty
		 *
		 * @return string
		 */
		public function fetchEditableJs ($params, &$smarty) {
			if (!count ($params)) {
				return '';
			}
			$blocks = $params ['arrayBlocs'];
			$nonEditableFields = array (
				FieldInterface::UI_TYPE_ATTACHMENTS,
				FieldInterface::UI_TYPE_CALCULATED,
				FieldInterface::UI_TYPE_CALCULATED_LINK,
				FieldInterface::UI_TYPE_CODE,
				FieldInterface::UI_TYPE_CREATED_TIME,
				FieldInterface::UI_TYPE_GRID,
				FieldInterface::UI_TYPE_IMAGE_DISPLAY,
				FieldInterface::UI_TYPE_IMAGE_REFERENCE,
				FieldInterface::UI_TYPE_MODIFIED_BY,
				FieldInterface::UI_TYPE_MODULE_RECORDS,
				FieldInterface::UI_TYPE_MODULE_REFERENCE,
				FieldInterface::UI_TYPE_SUMMARY_ROW,
				FieldInterface::DATA_TYPE_CHECKBOX,
			);

			foreach ($blocks as $block) {
				foreach ($block as $details) {
					foreach ($details as $data) {
						if (in_array ($data[ 'ui' ], $nonEditableFields) || empty ($data[ 'fldname' ])) {
							continue;
						}
						$this->getFieldEditableFunction ($data);
					}
				}
			}
			return "\n" . $this->header . $this->editableFunctions . $this->footer;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return JSDetailViewEditableUtils
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
