<?php
global $calpath;
global $app_strings,$mod_strings;
global $theme;
global $log;
global $current_user;

$theme_path='themes/'.$theme.'/';
$image_path=$theme_path.'images/';
require_once ('include/utils/NumberHelper.class.php');
require_once('modules/Reports/SanitizeUtils.php');
require_once ('include/utils/GridFieldUtils.class.php');
require_once('include/database/PearDatabase.php');
require_once('data/CRMEntity.php');
require_once('modules/Reports/Reports.php');
require_once('modules/Reports/ReportUtils.php');
require_once('vtlib/Vtiger/Module.php');
require_once 'include/tcpdf/tcpdf.php';


// --- Clase personalizada para encabezado PDF ---
class CustomPDF extends TCPDF {
    public $orgName = '';
    public $reportTitle = '';
    public $reportDesc = '';
    public $fechaHora = '';
    public $columnHeaderHTML = '';
    public $tableWidth = 0;
    public function Header() {
        $this->SetFont(PDF_FONT_NAME_MAIN, '', 12);
        $this->Cell(0, 7, $this->orgName, 0, 0, 'L', 0, '', 0, false, 'T', 'M');
        $this->SetTextColor(0,0,0); // Negro
        $this->SetFont(PDF_FONT_NAME_MAIN, '', 10);
        $this->Cell(0, 7, $this->fechaHora, 0, 1, 'R', 0, '', 0, false, 'T', 'M');
        $this->SetFont(PDF_FONT_NAME_MAIN, 'B', 13);
        $this->Cell(0, 7, $this->reportTitle, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        if (!empty($this->reportDesc)) {
            $this->SetFont(PDF_FONT_NAME_MAIN, '', 10);
            $this->Cell(0, 7, $this->reportDesc, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        }
        $this->Ln(2);
        $this->Line($this->GetX(), $this->GetY(), $this->getPageWidth()-$this->GetX(), $this->GetY());
        $this->Ln(2);
        
        // Renderizar encabezado de columnas si está definido
        if (!empty($this->columnHeaderHTML) && $this->tableWidth > 0) {
            $this->SetFont('Arial', '', 9);
            $headerTable = '<table cellpadding="2" border="0" width="'. $this->tableWidth .'">';
            $headerTable .= '<tr>'.$this->columnHeaderHTML.'</tr>';
            $headerTable .= '</table>';
            $this->writeHTML($headerTable, true, false, false, false, '');
            // Agregar espacio después del encabezado de columnas para evitar solapamiento
            $this->Ln(3);
        }
    }
}


// @codingStandardsIgnoreStart

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ReportRun extends CRMEntity {
	// @codingStandardsIgnoreEnd

	/**
	 * @var string
	 */
	public $primarymodule;

	public $secondarymodule;
	public $orderbylistsql;
	public $orderbylistcolumns;

	public $selectcolumns;
	public $groupbylist;
	public $reporttype;
	public $reportname;
	public $totallist;

	// @codingStandardsIgnoreStart
	public $_groupinglist  = false;
	public $_columnslist    = false;
	public $_stdfilterlist = false;
	public $_columnstotallist = false;
	public $_advfiltersql = false;
	// @codingStandardsIgnoreEnd

	/**
	 * @var array
	 */
	public $append_currency_symbol_to_value = array(
		'Products_Unit_Price',
		'Services_Price',
		'Invoice_Total',
		'Invoice_Sub_Total',
		'Invoice_S&H_Amount',
		'Invoice_Discount_Amount',
		'Invoice_Adjustment',
		'Quotes_Total',
		'Quotes_Sub_Total',
		'Quotes_S&H_Amount',
		'Quotes_Discount_Amount',
		'Quotes_Adjustment',
		'SalesOrder_Total',
		'SalesOrder_Sub_Total',
		'SalesOrder_S&H_Amount',
		'SalesOrder_Discount_Amount',
		'SalesOrder_Adjustment',
		'PurchaseOrder_Total',
		'PurchaseOrder_Sub_Total',
		'PurchaseOrder_S&H_Amount',
		'PurchaseOrder_Discount_Amount',
		'PurchaseOrder_Adjustment',
		'Invoice_Tax',
	);

	public $ui10_fields = array();
	public $ui101_fields = array();
	public $groupByTimeParent = array(
		'Quarter'=>array('Year'),
		'Month'=>array('Year'),
	);

	/**
	 * @param PearDatabase $adb
	 * @param string $gridColname
	 * @param string $temporaryTable
	 */
	private function createTempGridValues ($adb, $gridColname, $temporaryTable){
		$adb->query (
			"CREATE TEMPORARY TABLE IF NOT EXISTS `{$temporaryTable}` (
					`recordid` INT(19) NOT NULL,
					`{$gridColname}` DECIMAL(12,2) NULL DEFAULT '0'
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8"
		);

	}

	/**
	 * @param PearDatabase $adb
	 * @param string $colname
	 * @param string $module_field
	 * @param string $temporaryTable
	 * @return null|string
	 * @throws Exception
	 *  2025-07-15- GGC -  Esta función se dejó de usar, porque ya no se usa una tabla temporal 
	 *  para los totales de los REports, sino que se hace uso de la tabla vtiger_grid_summary_<campo grid>
	 */
	 /*
	private function getGridTempTable ($adb, $colname, $module_field, $temporaryTable) {
		list ($gridColname, $gridName) = explode('@', $colname);
		list($module, $mainTable) = explode('@',$module_field,2);
		$result = $adb->pquery('SELECT crmid FROM vtiger_crmentity WHERE setype=? AND deleted = 0', array ($module));
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return null;
		}
		$this->createTempGridValues($adb, $gridColname, $temporaryTable);
		while($row = $adb->fetch_array($result)) {
			$gridRow = GridFieldUtils::getGridValues($adb,$module,$gridName, $row['crmid'], true);
			$gridRowValue = floatval($gridRow['summary'][ $gridColname ]);
			$adb->pquery ("INSERT INTO {$temporaryTable} (recordid, {$gridColname}) VALUES (?, ?)", array ($row['crmid'], $gridRowValue));
		}
		return "{$temporaryTable}:{$gridColname}:{$module}_{$gridColname}:V";
	}
	*/

    /**
     * Obtiene el nombre de columna real de la tabla de totales grid y construye el string de columna de reporte.
     *
     * @param PearDatabase $adb
     * @param string $colname Nombre lógico de columna grid (ej: subtotal@articulos_a_facturar)
     * @param string $module_field Campo de módulo relacionado (ej: Potentials@vtiger_potential)
     * @param string $temporaryTable Nombre de la tabla summary grid (ej: vtiger_grid_summary_articulos_a_facturar)
     * @return string Cadena de columna en formato tabla:columna:...
     */
    private function getGridColumTotal($adb, $colname, $module_field, $temporaryTable) {
		// Obtener el nombre de columna a Buscar
		$columnaB = explode('@',$colname);
		$colgrid = $columnaB[0];
        // Obtener columnas de la tabla summary
        $columns = array();
        $result = $adb->pquery("SHOW COLUMNS FROM $temporaryTable", array());
        if (!$result || $adb->num_rows($result) == 0) {
            return "$temporaryTable:$colname:$module_field:$colname:N";
        }
        while ($row = $adb->fetchByAssoc($result)) {
            $columns[] = $row['field'];
        }
        // Buscar la columna que corresponde a $colname
        $colMatch = null;
        foreach ($columns as $col) {
            // Coincidencia exacta: el nombre de la columna debe empezar con el nombre del grid seguido de un guion bajo y terminar en número
            if (preg_match('/^' . preg_quote($colgrid, '/') . '_[0-9]+$/', $col)) {
                $colMatch = $col;
                break;
            }
        }
		if (!$colMatch) {
            // Devuelve igualmente la cadena
            return "$temporaryTable:$colname:$module_field:$colname:N";
        }

        // Buscar etiqueta en vtiger_subfields_special de forma segura
        $fieldlabel = $colMatch; // Valor por defecto
        $sqlaux = "SELECT label FROM vtiger_subfields_special WHERE name = ? LIMIT 1";
        $labelAux = $adb->pquery($sqlaux, array($colMatch));
        if ($adb->num_rows($labelAux) > 0) {
            $rowlabel = $adb->fetchByAssoc($labelAux);
            if (!empty($rowlabel['label'])) {
                $fieldlabel = $rowlabel['label'];
            }
        }
		return "$temporaryTable:$colMatch:$fieldlabel:$colMatch:N";
    }

    /**hasGridColumn
     * Determina y construye cláusulas JOIN o devuelve información relacionada con columnas grid en el reporte.
     *
     * Esta función analiza el array interno $this->_columnslist para identificar columnas que requieren un JOIN especial,
     * especialmente aquellas provenientes de campos tipo grid (tablas vtiger_grid_summary_* o temporales vtiger_gridvalues_*).
     * Se asegura de no duplicar los INNER JOIN cuando existen múltiples subcampos del mismo grid en el reporte.
     *
     * @param string $type Tipo de operación a realizar:
     *   - 'join': Devuelve la(s) cláusula(s) INNER JOIN necesarias para los campos grid.
     *   - '{columna}': Devuelve la clave de columna correspondiente si coincide con el identificador solicitado.
     * @return string Cláusula(s) JOIN para SQL o identificador de columna grid según el tipo solicitado.
     *
     * Ejemplo de uso:
     *   $joins = $this->hasGridColumn('join');
     *   $colKey = $this->hasGridColumn('subtotal@articulos_a_facturar');
     *
     * Consideraciones:
     * - Evita duplicar INNER JOIN para la misma tabla grid summary.
     * - Para columnas vtiger_gridvalues_* simplemente las ignora en el JOIN, pues son temporales.
     * - Es fundamental para la correcta construcción del SELECT y FROM en reportes con campos grid.
	 * Modificado por GGC / 2025-07-17 
     */
    private function hasGridColumn ($type){
		$joinClause = '';
		if(!count ($this->_columnslist)) {
			return $joinClause;
		}

		foreach ($this->_columnslist as $key => $value) {
			if (strpos($key, 'vtiger_subfields_values') === false) {
				continue;
			} else {
				$key_e = explode(":",$key);
				if ($key_e[0] === 'vtiger_subfields_values' ) {
					$dummy = explode(':', $key);
					$nombrecampoX = explode('@', $dummy [1]);
					$table_summary = "vtiger_grid_summary_" . $nombrecampoX[1];
					// Cambiar a LEFT JOIN para incluir registros sin artículos
					$joinClauseX = "LEFT JOIN {$table_summary} ON {$table_summary}.recordid = vtiger_crmentity.crmid ";
					if (strpos($joinClause, $joinClauseX) == false) {
						$joinClause .= " ". $joinClauseX;
					}
				}
			}
		}
		return $joinClause;
	}

    /** @noinspection PhpInconsistentReturnPointsInspection */
    /**
     * Function to set reportid,primarymodule,secondarymodule,reporttype,reportname, for given reportid
     * This function accepts the $reportid as argument
     * It sets reportid,primarymodule,secondarymodule,reporttype,reportname for the given reportid
     * @param $reportid
     * @return ReportRun
     */
    public function ReportRun($reportid) {
		// @codingStandardsIgnoreEnd
		$oReport = new Reports($reportid);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->reportid = $reportid;
		$this->primarymodule = $oReport->primodule;
		$this->secondarymodule = $oReport->secmodule;
		$this->reporttype = $oReport->reporttype;
		$this->reportname = $oReport->reportname;;
	}

	// @codingStandardsIgnoreStart
	/**
	 * Function to get the columns for the reportid
	 * This function accepts the $reportid and $outputformat (optional)

	 * @param $reportid
	 * @param string $outputformat

	 * @return mixed

	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function getQueryColumnsList($reportid, $outputformat = '') {
		// @codingStandardsIgnoreEnd
		// Have we initialized information already?

		if($this->_columnslist !== false) {
            return $this->_columnslist;
        }
        global $adb;
		$ssql= 'SELECT vtiger_selectcolumn.* FROM vtiger_selectcolumn WHERE queryid in (SELECT queryid FROM vtiger_selectquery where queryid =?) ORDER BY vtiger_selectcolumn.columnindex';

        // Obtener la lista de columnas seleccionadas por el usuario
        $selectedColumnsList = [];
        $resultPreview = $adb->pquery($ssql, array($reportid));

        while($rowPreview = $adb->fetch_array($resultPreview)) {
            $columnname = $rowPreview['columnname'];
            $selectedColumnsList[] = $columnname;
        }
        // Procesamiento de columnas seleccionadas
        $columnslist = array();
        //$alreadyAdded = array();
        foreach ($selectedColumnsList as $fieldcolname) {
            // Descomponer la columna
			$colDef = "";
            $selectedfields = explode(":", $fieldcolname);
            if (count($selectedfields) < 5) {
                continue;
            }
            $tablename = $selectedfields[0];
            $colname = $selectedfields[1];
            $fieldlabel = $selectedfields[2];
            $fieldname = $selectedfields[3];
            $typeofdata = $selectedfields[4];

            // Simulación de chequeo de permisos (ajustar según lógica real)
            $has_permission = true;
            // Aquí debería ir la lógica real de permisos, visibilidad, etc.
            // Por ejemplo:
            // if (!isFieldPermitted($tablename, $fieldname)) { ... }
            // if ($typeofdata == 'X') { ... }
            // Ejemplo de filtro por tipo:
            if ($typeofdata == 'X') {
                continue;
            }
            // Ejemplo de filtro por permisos (simulado)
            if (!$has_permission) {
                continue;
            }
            // Si pasa todos los filtros, agregar
			if ($tablename == "vtiger_subfields_values") {
				$aux = explode('@',$fieldlabel);
				$module_field1 = $aux[0];
				$aux1= explode('@',$colname);
				$temporaryTable = "vtiger_grid_summary_". $aux1[1];

				$cadena = $this->getGridColumTotal($adb, $colname, $module_field1, $temporaryTable);
				$cadena1 = explode(':',$cadena);
				$colDef = "$cadena1[0].$cadena1[1] AS '$cadena1[2]'";
			} else if (strpos($tablename, 'vtiger_users') === 0) {
				$tablename = "vtiger_users";
				$colDef = "$tablename.$colname AS '$fieldlabel'";
			} else if (strpos($tablename, "vtiger_crmentity") !== false){
					$tablename= "vtiger_crmentity";	
					$colDef = "$tablename.$colname AS '$fieldlabel'";					
			} else { $colDef = "$tablename.$colname AS '$fieldlabel'";}
			
			if (!isset($columnslist[$fieldcolname])) {
				$columnslist[$fieldcolname] = $colDef;
			}
        }

		global /** @noinspection PhpUnusedLocalVariableInspection */
		$modules;
		global $log,$current_user,$current_language;

		$result = $adb->pquery($ssql, array($reportid));
		$permitted_fields = array();

		while($columnslistrow = $adb->fetch_array($result)) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			$fieldname      = '';
			//$temporaryTable = 'vtiger_gridvalues_';
			$cadena = $columnslistrow[2];
			$partes = explode('@', $cadena);
			$partes1 = explode(':',$partes[1]);
			$campogrid = $partes1[0];
			$temporaryTable = "vtiger_grid_summary_". $campogrid;
			$fieldcolname = $columnslistrow['columnname'];
			/** @noinspection PhpUnusedLocalVariableInspection */
			list($tablename,$colname,$module_field,$fieldname,$single) = explode(':',$fieldcolname);
			
			// Obtiene datos desde las tablas inteligentes (antes campos grid)
			if ($tablename == 'vtiger_subfields_values') {
				//2025-07-11  Tomar totales de la tabla de totales grid del campo correspondiente				
				$fieldcolname = $this->getGridColumTotal ($adb, $colname, $module_field, $temporaryTable);
				list($tablename,$colname,$module_field,$fieldname,$single) = explode(':',$fieldcolname);				
			}

			list($module,$field) = explode('_',$module_field,2);
			$inventory_fields = array('quantity','listprice','serviceid','productid','discount','comment');
			$inventory_modules = array('SalesOrder','Quotes','PurchaseOrder','Invoice');
			/** @noinspection PhpUnusedLocalVariableInspection */
			$local_user = clone $current_user;
			require('user_privileges/user_privileges.php');
			/** @noinspection PhpUndefinedVariableInspection */
			if (count($permitted_fields[$module]) == 0 && $is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
				$permitted_fields[$module] = $this->getaccesfield($module);
			}

			if (in_array($module,$inventory_modules)) {
				$permitted_fields = array_merge($permitted_fields,$inventory_fields);
			}
			$selectedfields = explode(':',$fieldcolname);
			/** @noinspection PhpUndefinedVariableInspection */
			if($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && !in_array($selectedfields[3], $permitted_fields[$module])) {
				//user has no access to this field, skip it.
				continue;
			}
			$concatSql = getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0].'.first_name', 'last_name' => $selectedfields[0].'.last_name'), 'Users');
			$querycolumns = $this->getEscapedColumns($selectedfields, array_column($result ? $adb->fetch_array($result) : [], 'columnname'));

			if(isset($module) && $module!='') {
				/** @noinspection PhpUnusedLocalVariableInspection */
				$mod_strings = return_module_language($current_language,$module);
			}

			$fieldlabel = trim(preg_replace('/$module/',' ',$selectedfields[2],1));
			$mod_arr=explode('_',$fieldlabel);
			$fieldlabel = trim(str_replace('_',' ',$fieldlabel));
			//modified code to support i18n issue
			$fld_arr = explode(' ',$fieldlabel);
			if(($mod_arr[0] == '')) {
				$mod = $module;
				$mod_lbl = getTranslatedString($module,$module); //module
			} else {
				$mod = $mod_arr[0];
				array_shift($fld_arr);
				$mod_lbl = getTranslatedString($fld_arr[0],$mod); //module
			}
			$fld_lbl_str = implode(' ',$fld_arr);
			$fld_lbl = getTranslatedString($fld_lbl_str,$module); //fieldlabel
			$fieldlabel = $fld_lbl;

			if(($selectedfields[0] == 'vtiger_usersRel1') && ($selectedfields[1] == 'user_name') && ($selectedfields[2] == 'Quotes_Inventory_Manager')) {
				//$columnslist[$fieldcolname] = "trim( $concatSql ) as ".$module.'_Inventory_Manager';
				continue;
			}

			if((CheckFieldPermission($fieldname,$mod) != 'true' && $colname!='crmid' && (!in_array($fieldname,$inventory_fields) && in_array($module,$inventory_modules))) || empty($fieldname)) {
				continue;
			} else {
				/** @noinspection PhpUndefinedFieldInspection */
				$this->labelMapping[$selectedfields[2]] = str_replace(' ','_',$fieldlabel);
				$header_label = $selectedfields[2];

				if($querycolumns == '') {
					if($selectedfields[4] == 'C') {
						$field_label_data = explode('_',$selectedfields[2]);
						$module= $field_label_data[0];
						if($module!=$this->primarymodule) {
						  /*$columnslist[$fieldcolname] = 'case when ('.$selectedfields[0].'.'.$selectedfields[1]."='1')then 'yes' else case when (vtiger_crmentity$module.crmid !='') then 'no' else '-' end end as '$selectedfields[2]'";*/
							$columnslist[$fieldcolname] = 'case when ('.$selectedfields[0].'.'.$selectedfields[1]."='1')then 'yes' else case when (vtiger_crmentity.crmid !='') then 'no' else '-' end end as '$selectedfields[2]'";
						} else {
							$columnslist[$fieldcolname] = 'case when ('.$selectedfields[0].'.'.$selectedfields[1]."='1')then 'yes' else case when (vtiger_crmentity.crmid !='') then 'no' else '-' end end as '$selectedfields[2]'";
						}
					} else if($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'status') {
						$columnslist[$fieldcolname] = " case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end as Calendar_Status";
					} else if($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
						$columnslist[$fieldcolname] = "cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME) as Calendar_Start_Date_and_Time";
					} else if(stristr($selectedfields[0],'vtiger_users') && ($selectedfields[1] == 'user_name')) {
					// Siempre usa el alias correcto para usuarios asignados
						$columnslist[$fieldcolname] = "vtiger_users.user_name as '".$header_label."'";
					} else if(stristr($selectedfields[0],'vtiger_crmentity') && ($selectedfields[1] == 'modifiedby')) {
						$concatSql = getSqlForNameInDisplayFormat(array('last_name' => 'vtiger_lastModifiedBy'.$module.'.last_name', 'first_name' => 'vtiger_lastModifiedBy'.$module.'.first_name'), 'Users');
						$columnslist[$fieldcolname] = "trim($concatSql) as $header_label";
					} else if($selectedfields[0] == 'vtiger_crmentity'.$this->primarymodule) {
						$columnslist[$fieldcolname] = 'vtiger_crmentity.'.$selectedfields[1]." AS '".$header_label."'";
					} else if(in_array($selectedfields[2], $this->append_currency_symbol_to_value) && stristr($selectedfields[1],'cf_') == false) {
						$columnslist[$fieldcolname] = 'concat('.$selectedfields[0].".currency_id,'::',".$selectedfields[0].'.'.$selectedfields[1].") as '" . $header_label ."'";
					} else if($selectedfields[0] == 'vtiger_notes' && ($selectedfields[1] == 'filelocationtype' || $selectedfields[1] == 'filesize' || $selectedfields[1] == 'folderid' || $selectedfields[1]=='filestatus')) {
						if($selectedfields[1] == 'filelocationtype') {
							$columnslist[$fieldcolname] = 'case '.$selectedfields[0].'.'.$selectedfields[1]." when 'I' then 'Internal' when 'E' then 'External' else '-' end as '$selectedfields[2]'";
						} else if($selectedfields[1] == 'folderid') {
							$columnslist[$fieldcolname] = "vtiger_attachmentsfolder.foldername as '$selectedfields[2]'";
						} else if($selectedfields[1] == 'filestatus') {
							$columnslist[$fieldcolname] = 'case '.$selectedfields[0].'.'.$selectedfields[1]." when '1' then 'yes' when '0' then 'no' else '-' end as '$selectedfields[2]'";
						} else if($selectedfields[1] == 'filesize') {
							$columnslist[$fieldcolname] = 'case '.$selectedfields[0].'.'.$selectedfields[1]." when '' then '-' else concat(".$selectedfields[0].'.'.$selectedfields[1]."/1024,'  ','KB') end as '$selectedfields[2]'";
						}
					} else if(stristr($selectedfields[1],'cf_')==true && stripos($selectedfields[1],'cf_')==0) {
						$columnslist[$fieldcolname] = $selectedfields[0].'.'.$selectedfields[1]." AS '".$adb->sql_escape_string(decode_html($header_label))."'";

					} else if($selectedfields[0] == $temporaryTable) {
						//$columnslist[$fieldcolname] = $selectedfields[0].'.'.$selectedfields[1]." AS '".$adb->sql_escape_string(decode_html($selectedfields[1]))."'";
					} else {
						//$columnslist[$fieldcolname] = $selectedfields[0].'.'.$selectedfields[1]." AS '".$header_label."'";
					}
				} else {
					//$columnslist[$fieldcolname] = $querycolumns;
				}
			}
		}
		if (in_array($outputformat, ['HTML', 'PDF'])) {
			$columnslist['vtiger_crmentity:crmid:LBL_ACTION:crmid:I'] = 'vtiger_crmentity.crmid AS "LBL_ACTION"';
		}
		// Save the information
		/** @noinspection PhpUndefinedVariableInspection */
		$this->_columnslist = $columnslist;

		$log->info('ReportRun :: Successfully returned getQueryColumnsList'.$reportid);
		return $columnslist;
	}

	/**
	 * Function to get field columns based on profile
	 *  @ param $module : Type string
	 *  returns permitted fields in array format

	 * @param $module

	 * @return array
	 */
	public function getaccesfield($module) {
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $current_user;
		global $adb;
		$access_fields = array();

		$profileList = getCurrentUserProfileList();
		// @codingStandardsIgnoreStart
		$query = 'select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid';
		// @codingStandardsIgnoreEnd
		$params = array();
		if($module == 'Calendar') {
			if (count($profileList) > 0) {
				$query .= ' where vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0
                                and vtiger_field.presence IN (0,2) and vtiger_profile2field.profileid in ('. generateQuestionMarks($profileList) .') group by vtiger_field.fieldid order by block,sequence';
				array_push($params, $profileList);
			} else {
				$query .= ' where vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0
                                and vtiger_field.presence IN (0,2) group by vtiger_field.fieldid order by block,sequence';
			}
		} else {
			array_push($params, $module);
			if (count($profileList) > 0) {
				$query .= ' where vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?)) and vtiger_field.displaytype in (1,2,3,5) and vtiger_profile2field.visible=0
                                and vtiger_field.presence IN (0,2) and vtiger_def_org_field.visible=0 and vtiger_profile2field.profileid in ('. generateQuestionMarks($profileList) .') group by vtiger_field.fieldid order by block,sequence';
				array_push($params, $profileList);
			} else {
				$query .= ' where vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?)) and vtiger_field.displaytype in (1,2,3,5) and vtiger_profile2field.visible=0
                                and vtiger_field.presence IN (0,2) and vtiger_def_org_field.visible=0 group by vtiger_field.fieldid order by block,sequence';
			}
		}
		$result = $adb->pquery($query, $params);

		while($collistrow = $adb->fetch_array($result)) {
			$access_fields[] = $collistrow['fieldname'];
		}
		// added to include ticketid for Reports module in select columnlist for all users
		if($module == 'HelpDesk') {
			$access_fields[] = 'ticketid';
		}
		return $access_fields;
	}

	/**
	 * Function to get Escapedcolumns for the field in case of multiple parents
	 *  @ param $selectedfields : Type array
	 *  returns the case query for the escaped columns

	 * @param $selectedfields

	 * @return string
	 */
	public function getEscapedColumns($selectedfields, $selectedColumnsList = null) {
		$tableName = $selectedfields[0];
		$columnName = $selectedfields[1];
		$moduleFieldLabel = $selectedfields[2];
		$fieldName = $selectedfields[3];
		list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
		$fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);

		// Solo construir CASE si el campo está en la lista seleccionada (si se provee)
		if (is_array($selectedColumnsList)) {
			// Formato: tabla:columna:modulo_etiqueta:fieldname:tipo
			$columnKey = implode(':', $selectedfields);
			if (!in_array($columnKey, $selectedColumnsList)) {
				return '';
			}
		}

		if($moduleName == 'ModComments' && $fieldName == 'creator') {
			$concatSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_usersModComments.first_name', 'last_name' => 'vtiger_usersModComments.last_name'), 'Users');
			$queryColumn = "trim(case when (vtiger_usersModComments.user_name not like '' and vtiger_crmentity.crmid!='') then $concatSql end) as 'ModComments_Creator'";
		} else if(($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype']))
			&& $fieldInfo['uitype'] != '52' && $fieldInfo['uitype'] != '53'
		) {
			$fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
			if(count($fieldSqlColumns) > 0) {
				$queryColumn = "(CASE WHEN $tableName.$columnName NOT LIKE '' THEN (CASE";
				foreach($fieldSqlColumns as $columnSql) {
					$queryColumn .= " WHEN $columnSql NOT LIKE '' THEN $columnSql";
				}
				$queryColumn .= " ELSE '' END) ELSE '' END) AS '$moduleFieldLabel'";
			}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		return $queryColumn;
	}

	/**
	 * Function to get selectedcolumns for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the query of columnlist for the selected columns

	 * @param $reportid

	 * @return string
	 */
	public function getSelectedColumnsList($reportid) {
		global $adb;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$modules;
		global $log;
		$sSQL = '';

		$ssql = 'select vtiger_selectcolumn.* from vtiger_report inner join vtiger_selectquery on vtiger_selectquery.queryid = vtiger_report.queryid';
		$ssql .= ' left join vtiger_selectcolumn on vtiger_selectcolumn.queryid = vtiger_selectquery.queryid where vtiger_report.reportid = ? ';
		$ssql .= ' order by vtiger_selectcolumn.columnindex';

		$result = $adb->pquery($ssql, array($reportid));
		$noofrows = $adb->num_rows($result);

		if ($this->orderbylistsql != '') {
			$sSQL .= $this->orderbylistsql.', ';
		}

		for($i=0; $i<$noofrows; $i++) {
			$fieldcolname = $adb->query_result($result,$i,'columnname');
			$ordercolumnsequal = true;
			if($fieldcolname != '') {
				$countOrderByListColumns = count($this->orderbylistcolumns);
				for($j=0; $j<$countOrderByListColumns; $j++) {
					if($this->orderbylistcolumns[$j] == $fieldcolname) {
						$ordercolumnsequal = false;
						break;
					} else {
						$ordercolumnsequal = true;
					}
				}
				if($ordercolumnsequal) {
					$selectedfields = explode(':',$fieldcolname);
					if($selectedfields[0] == 'vtiger_crmentity'.$this->primarymodule) {
						$selectedfields[0] = 'vtiger_crmentity';
					}
					$sSQLList[] = $selectedfields[0].'.'.$selectedfields[1]." '".$selectedfields[2]."'";
				}
			}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		$sSQL .= implode(',',$sSQLList);

		$log->info('ReportRun :: Successfully returned getSelectedColumnsList'.$reportid);
		return $sSQL;
	}

	// @codingStandardsIgnoreStart
	/**
	 * Function to get advanced comparator in query form for the given Comparator and value
	 *  @ param $comparator : Type String
	 *  @ param $value : Type String
	 *  returns the check query for the comparator

	 * @param $comparator
	 * @param $value
	 * @param string $datatype

	 * @return string

	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function getAdvComparator($comparator, $value, $datatype = '') {
		// @codingStandardsIgnoreEnd
		global $log;
		global $adb;
		global $default_charset;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$ogReport;
		$value=html_entity_decode(trim($value),ENT_QUOTES,$default_charset);
		$value_len = strlen($value);
		$is_field = false;
		if($value_len > 1 && $value[0]=='$' && $value[($value_len-1)]=='$') {
			$temp = str_replace('$','',$value);
			$is_field = true;
		}
		if($datatype=='C') {
			$value = str_replace('yes','1',str_replace('no','0',$value));
		}

		if($is_field==true) {
			/** @noinspection PhpUndefinedVariableInspection */
			$value = $this->getFilterComparedField($temp);
		}
		if($comparator == 'e') {
			if(trim($value) == 'NULL') {
				$rtvalue = ' is NULL';
			} else if(trim($value) != '') {
				$rtvalue = ' = '.$adb->quote($value);
			} else if(trim($value) == '' && $datatype == 'V') {
				$rtvalue = ' = '.$adb->quote($value);
			} else
			{
				$rtvalue = ' is NULL';
			}
		}
		if($comparator == 'n') {
			if(trim($value) == 'NULL') {
				$rtvalue = ' is NOT NULL';
			} else if(trim($value) != '') {
				$rtvalue = ' <> '.$adb->quote($value);
			} else if(trim($value) == '' && $datatype == 'V') {
				$rtvalue = ' <> '.$adb->quote($value);
			} else
			{
				$rtvalue = ' is NOT NULL';
			}
		}
		if($comparator == 's') {
			$rtvalue = " like '". formatForSqlLike($value, 2,$is_field) ."'";
		}
		if($comparator == 'ew') {
			$rtvalue = " like '". formatForSqlLike($value, 1,$is_field) ."'";
		}
		if($comparator == 'c') {
			$rtvalue = " like '". formatForSqlLike($value,0,$is_field) ."'";
		}
		if($comparator == 'k') {
			$rtvalue = " not like '". formatForSqlLike($value,0,$is_field) ."'";
		}
		if($comparator == 'l') {
			$rtvalue = ' < '.$adb->quote($value);
		}
		if($comparator == 'g') {
			$rtvalue = ' > '.$adb->quote($value);
		}
		if($comparator == 'm') {
			$rtvalue = ' <= '.$adb->quote($value);
		}
		if($comparator == 'h') {
			$rtvalue = ' >= '.$adb->quote($value);
		}
		if($comparator == 'b') {
			$rtvalue = ' < '.$adb->quote($value);
		}
		if($comparator == 'a') {
			$rtvalue = ' > '.$adb->quote($value);
		}
		if($is_field==true) {
			/** @noinspection PhpUndefinedVariableInspection */
			$rtvalue = str_replace("'",'',$rtvalue);
			$rtvalue = str_replace('\\','',$rtvalue);
		}
		$log->info('ReportRun :: Successfully returned getAdvComparator');
		/** @noinspection PhpUndefinedVariableInspection */
		return $rtvalue;
	}

	/**
	 * Function to get field that is to be compared in query form for the given Comparator and field
	 *  @ param $field : field
	 *  returns the value for the comparator

	 * @param $field

	 * @return string
	 */
	public function getFilterComparedField($field) {
		global $adb;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$ogReport;
		$field = explode('#',$field);
		$module = $field[0];
		$fieldname = trim($field[1]);
		$tabid = getTabId($module);
		$field_query = $adb->pquery('SELECT tablename,columnname,typeofdata,fieldname,uitype FROM vtiger_field WHERE tabid = ? AND fieldname= ?',array($tabid, $fieldname));
		$fieldtablename = $adb->query_result($field_query,0,'tablename');
		$fieldcolname = $adb->query_result($field_query,0,'columnname');
		$typeofdata = $adb->query_result($field_query,0,'typeofdata');
		/** @noinspection PhpUnusedLocalVariableInspection */
		$fieldtypeofdata=ChangeTypeOfData_Filter($fieldtablename,$fieldcolname,$typeofdata[0]);
		$uitype = $adb->query_result($field_query,0,'uitype');
		if($uitype == 68 || $uitype == 59) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			$fieldtypeofdata = 'V';
		}
		if($fieldtablename == 'vtiger_crmentity') {
			$fieldtablename = $fieldtablename.$module;
		}
		if($fieldname == 'assigned_user_id') {
			$fieldtablename = 'vtiger_users'.$module;
			$fieldcolname = 'user_name';
		}
		if($fieldtablename == 'vtiger_crmentity' && $fieldname == 'modifiedby') {
			$fieldtablename = 'vtiger_lastModifiedBy'.$module;
			$fieldcolname = 'user_name';
		}
		if($fieldname == 'assigned_user_id1') {
			$fieldtablename = 'vtiger_usersRel1';
			$fieldcolname = 'user_name';
		}
		$value = $fieldtablename.'.'.$fieldcolname;
		return $value;
	}

	/**
	 * Function to get the advanced filter columns for the reportid

	 * @param $reportid

	 * @return array
	 */
	public function getAdvFilterList($reportid) {
		global $adb;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$log;
		$advft_criteria = array();

		$sql = 'SELECT * FROM vtiger_relcriteria_grouping WHERE queryid = ? ORDER BY groupid';
		$groupsresult = $adb->pquery($sql, array($reportid));

		$i = 1;
		$j = 0;
		while($relcriteriagroup = $adb->fetch_array($groupsresult)) {
			$groupId = $relcriteriagroup['groupid'];
			$groupCondition = $relcriteriagroup['group_condition'];

			$ssql = 'select vtiger_relcriteria.* from vtiger_report
						inner join vtiger_relcriteria on vtiger_relcriteria.queryid = vtiger_report.queryid
						left join vtiger_relcriteria_grouping on vtiger_relcriteria.queryid = vtiger_relcriteria_grouping.queryid
								and vtiger_relcriteria.groupid = vtiger_relcriteria_grouping.groupid';
			$ssql.= ' where vtiger_report.reportid = ? AND vtiger_relcriteria.groupid = ? order by vtiger_relcriteria.columnindex';

			$result = $adb->pquery($ssql, array($reportid, $groupId));
			$noOfColumns = $adb->num_rows($result);
			if($noOfColumns <= 0) {
				continue;
			}
			while($relcriteriarow = $adb->fetch_array($result)) {
				/** @noinspection PhpUnusedLocalVariableInspection */
				$columnIndex = $relcriteriarow['columnindex'];
				$criteria = array();
				$fieldcolname = explode(':',$relcriteriarow['columnname']);

				if ($fieldcolname [0] == 'vtiger_subfields_values') {
					$gridColumn = explode('@', $fieldcolname [1]);
					$relcriteriarow['columnname'] = $this->hasGridColumn($gridColumn [0]);
				}

				$criteria['columnname'] = html_entity_decode($relcriteriarow['columnname']);
				$criteria['comparator'] = $relcriteriarow['comparator'];
				$advfilterval = $relcriteriarow['value'];
				/** @noinspection PhpUnusedLocalVariableInspection */
				$criteria['value'] = $advfilterval;
				$criteria['column_condition'] = $relcriteriarow['column_condition'];
				$advft_criteria[$i]['columns'][$j] = $criteria;
				$advft_criteria[$i]['condition'] = $groupCondition;

			}
			if(!empty($advft_criteria[$i]['columns'][($j-1)]['column_condition'])) {
				$advft_criteria[$i]['columns'][($j-1)]['column_condition'] = '';
			}
			$i++;
		}
		// Clear the condition (and/or) for last group, if any.
		if(!empty($advft_criteria[($i-1)]['condition'])) {
			$advft_criteria[($i-1)]['condition'] = '';
		}
		return $advft_criteria;
	}

	// @codingStandardsIgnoreStart
	/**
	 * @param $advfilterlist

	 * @return string

	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function generateAdvFilterSql($advfilterlist) {
		// @codingStandardsIgnoreEnd
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$adb;

		$advfiltersql = '';

		foreach($advfilterlist as $groupindex => $groupinfo) {
			$groupcondition = $groupinfo['condition'];
			$groupcolumns = $groupinfo['columns'];

			if(count($groupcolumns) > 0) {
				$advfiltergroupsql = '';
				foreach($groupcolumns as $columnindex => $columninfo) {
					$fieldcolname = $columninfo['columnname'];
					$comparator = $columninfo['comparator'];
					$value = $columninfo['value'];
					$columncondition = $columninfo['column_condition'];

					if($fieldcolname != '' && $comparator != '') {
						$selectedfields = explode(':',$fieldcolname);
						$moduleFieldLabel = $selectedfields[2];
						list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
						$fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);
						$concatSql = getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0].'.first_name', 'last_name' => $selectedfields[0].'.last_name'), 'Users');
						// Added to handle the crmentity table name for Primary module
						if($selectedfields[0] == 'vtiger_crmentity'.$this->primarymodule) {
							$selectedfields[0] = 'vtiger_crmentity';
						}
						//Added to handle yes or no for checkbox  field in reports advance filters. -shahul
						if($selectedfields[4] == 'C') {
							if(strcasecmp(trim($value),'yes')==0) {
								$value='1';
							}
							if(strcasecmp(trim($value),'no')==0) {
								$value='0';
							}
						}
						$valuearray = explode(';',trim($value));
						$datatype = (isset($selectedfields[4])) ? $selectedfields[4] : '';
						if(isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {
							/** @noinspection PhpUnusedLocalVariableInspection */
							$advcolumnsql = '';
							$countValueArray = count($valuearray);
							for($n=0; $n<$countValueArray; $n++) {
								if(($selectedfields[0] == 'vtiger_users'.$this->primarymodule || $selectedfields[0] == 'vtiger_users'.$this->secondarymodule) && $selectedfields[1] == 'user_name') {
									$module_from_tablename = str_replace('vtiger_users','',$selectedfields[0]);
									$advcolsql[] = " trim($concatSql)".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype).' or vtiger_groups'.$module_from_tablename.'.groupname '.$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
								} else if($selectedfields[1] == 'status') {//when you use comma seperated values.
									if($selectedfields[2] == 'Calendar_Status') {
										$advcolsql[] = "(case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end)".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
									} else if($selectedfields[2] == 'HelpDesk_Status') {
										$advcolsql[] = 'vtiger_troubletickets.status'.$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
									}
								} else if($selectedfields[1] == 'description') {//when you use comma seperated values.
									if($selectedfields[0]=='vtiger_crmentity'.$this->primarymodule) {
										$advcolsql[] = 'vtiger_crmentity.description'.$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
									} else {
										$advcolsql[] = $selectedfields[0].'.'.$selectedfields[1].$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
									}
								} else if($selectedfields[2] == 'Quotes_Inventory_Manager') {
									$advcolsql[] = ("trim($concatSql)".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype));
								} else {
									$advcolsql[] = $selectedfields[0].'.'.$selectedfields[1].$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
								}
							}
							//If negative logic filter ('not equal to', 'does not contain') is used, 'and' condition should be applied instead of 'or'
							if($comparator == 'n' || $comparator == 'k') {
								/** @noinspection PhpUndefinedVariableInspection */
								$advcolumnsql = implode(' and ',$advcolsql);
							} else {
								/** @noinspection PhpUndefinedVariableInspection */
								$advcolumnsql = implode(' or ',$advcolsql);
							}
							$fieldvalue = ' ('.$advcolumnsql.') ';
						} else if(($selectedfields[0] == 'vtiger_users'.$this->primarymodule || $selectedfields[0] == 'vtiger_users'.$this->secondarymodule) && $selectedfields[1] == 'user_name') {
							$module_from_tablename = str_replace('vtiger_users','',$selectedfields[0]);
							$fieldvalue = ' trim(case when ('.$selectedfields[0].".last_name NOT LIKE '') then ".$concatSql.' else vtiger_groups'.$module_from_tablename.'.groupname end) '.$this->getAdvComparator($comparator,trim($value),$datatype);
						} else if($comparator == 'bw' && count($valuearray) == 2) {
							if($selectedfields[0] == 'vtiger_crmentity'.$this->primarymodule) {
								// @codingStandardsIgnoreStart
								$fieldvalue = '('.'vtiger_crmentity.'.$selectedfields[1]." between '".trim($valuearray[0])."' and '".trim($valuearray[1])."')";
								// @codingStandardsIgnoreEnd
							} else {
								$fieldvalue = '('.$selectedfields[0].'.'.$selectedfields[1]." between '".trim($valuearray[0])."' and '".trim($valuearray[1])."')";
							}
						} else if($selectedfields[0] == 'vtiger_crmentity'.$this->primarymodule) {
							$fieldvalue = 'vtiger_crmentity.'.$selectedfields[1].' '.$this->getAdvComparator($comparator,trim($value),$datatype);
						} else if($selectedfields[2] == 'Quotes_Inventory_Manager') {
							$fieldvalue = ("trim($concatSql)" . $this->getAdvComparator($comparator,trim($value),$datatype));
						} else if($selectedfields[1]=='modifiedby') {
							$module_from_tablename = str_replace('vtiger_crmentity','',$selectedfields[0]);
							if($module_from_tablename != '') {
								$tableName = 'vtiger_lastModifiedBy'.$module_from_tablename;
							} else {
								$tableName = 'vtiger_lastModifiedBy'.$this->primarymodule;
							}
							$fieldvalue = getSqlForNameInDisplayFormat(array('last_name' => "$tableName.last_name", 'first_name' => "$tableName.first_name"), 'Users').
								$this->getAdvComparator($comparator,trim($value),$datatype);
						} else if($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'status') {
							$fieldvalue = "(case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end)".$this->getAdvComparator($comparator,trim($value),$datatype);
						} else if($comparator == 'e' && (trim($value) == 'NULL' || trim($value) == '')) {
							$fieldvalue = '('.$selectedfields[0].'.'.$selectedfields[1].' IS NULL OR '.$selectedfields[0].'.'.$selectedfields[1]." = '')";
						} else if($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype'])) {
							$comparatorValue = $this->getAdvComparator($comparator,trim($value),$datatype);
							$fieldSqls = array();
							$fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
							foreach($fieldSqlColumns as $columnSql) {
								$fieldSqls[] = $columnSql.$comparatorValue;
							}
							$fieldvalue = ' ('. implode(' OR ', $fieldSqls).') ';
						} else {
							$fieldvalue = $selectedfields[0].'.'.$selectedfields[1].$this->getAdvComparator($comparator,trim($value),$datatype);
						}

						/** @noinspection PhpUndefinedVariableInspection */
						$advfiltergroupsql .= $fieldvalue;
						if(!empty($columncondition)) {
							$advfiltergroupsql .= ' '.$columncondition.' ';
						}
					}
				}

				if (trim($advfiltergroupsql) != '') {
					$advfiltergroupsql = "( $advfiltergroupsql ) ";
					if(!empty($groupcondition)) {
						$advfiltergroupsql .= ' '. $groupcondition . ' ';
					}

					$advfiltersql .= $advfiltergroupsql;
				}
			}
		}
		if (trim($advfiltersql) != '') {
			$advfiltersql = '('.$advfiltersql.')';
		}

		return $advfiltersql;
	}

	public function getAdvFilterSql($reportid) {
		// Have we initialized information already?
		if($this->_advfiltersql !== false) {
			return $this->_advfiltersql;
		}
		global $log;


		if (!empty($_REQUEST['advft_criteria'])) {
			require_once 'include/Zend/Json.php';
			/** @noinspection PhpUndefinedClassInspection */
			$json = new Zend_Json();
			$advft_criteria = $_REQUEST['advft_criteria'];
			if(!empty($advft_criteria)) {
				$advft_criteria = $json->decode($advft_criteria);
			}
			$advft_criteria_groups = $_REQUEST['advft_criteria_groups'];
			if(!empty($advft_criteria_groups)) {
				$advft_criteria_groups = $json->decode($advft_criteria_groups);
			}
			$advfiltersql = $this->RunTimeAdvFilter($advft_criteria,$advft_criteria_groups);
		} else {
			$advfilterlist = $this->getAdvFilterList($reportid);
			$advfiltersql = $this->generateAdvFilterSql($advfilterlist);
		}

		// Save the information
		$this->_advfiltersql = $advfiltersql;

		$log->info('ReportRun :: Successfully returned getAdvFilterSql'.$reportid);
		return $advfiltersql;
	}

	// @codingStandardsIgnoreStart
	/**
	 * Function to get the Standard filter columns for the reportid

	 * @param $reportid

	 * @return array

	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function getStdFilterList($reportid) {
		// @codingStandardsIgnoreEnd
		// Have we initialized information already?
		if($this->_stdfilterlist !== false) {
			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return $this->_stdfilterlist;
		}

		global $adb, $log;
		$stdfilterlist = array();

		$stdfiltersql = 'select vtiger_reportdatefilter.* from vtiger_report';
		$stdfiltersql .= ' inner join vtiger_reportdatefilter on vtiger_report.reportid = vtiger_reportdatefilter.datefilterid';
		$stdfiltersql .= ' where vtiger_report.reportid = ?';

		$result = $adb->pquery($stdfiltersql, array($reportid));
		$stdfilterrow = $adb->fetch_array($result);
		if(isset($stdfilterrow)) {
			$fieldcolname = $stdfilterrow['datecolumnname'];
			$datefilter = $stdfilterrow['datefilter'];
			$startdate = $stdfilterrow['startdate'];
			$enddate = $stdfilterrow['enddate'];

			if($fieldcolname != 'none') {
				$selectedfields = explode(':',$fieldcolname);
				if($selectedfields[0] == 'vtiger_crmentity'.$this->primarymodule) {
					$selectedfields[0] = 'vtiger_crmentity';
				}

				$moduleFieldLabel = $selectedfields[3];
				list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
				$fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);
				$typeOfData = $fieldInfo['typeofdata'];
				/** @noinspection PhpUnusedLocalVariableInspection */
				list($type, $typeOtherInfo) = explode('~', $typeOfData, 2);

				if($datefilter != 'custom') {
					$startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
					$startdate = $startenddate[0];
					$enddate = $startenddate[1];
				}

				if($startdate != '0000-00-00' && $enddate != '0000-00-00' && $startdate != '' && $enddate != '' && $selectedfields[0] != '' && $selectedfields[1] != '') {
					/** @noinspection PhpParamsInspection */
					$startDateTime = new DateTimeField($startdate.' '. date('H:i:s'));
					$userStartDate = $startDateTime->getDisplayDate();
					if($type == 'DT') {
						$userStartDate = $userStartDate.' 00:00:00';
					}
					$startDateTime = getValidDBInsertDateTimeValue($userStartDate);

					/** @noinspection PhpParamsInspection */
					$endDateTime = new DateTimeField($enddate.' '. date('H:i:s'));
					$userEndDate = $endDateTime->getDisplayDate();
					if($type == 'DT') {
						$userEndDate = $userEndDate.' 23:59:00';
					}
					$endDateTime = getValidDBInsertDateTimeValue($userEndDate);

					if ($selectedfields[1] == 'birthday') {
						$tableColumnSql = 'DATE_FORMAT('.$selectedfields[0].'.'.$selectedfields[1].", '%m%d')";
						$startDateTime = "DATE_FORMAT('$startDateTime', '%m%d')";
						$endDateTime = "DATE_FORMAT('$endDateTime', '%m%d')";
					} else {
						if($selectedfields[0] == 'vtiger_activity' && ($selectedfields[1] == 'date_start' || $selectedfields[1] == 'due_date')) {
							/** @noinspection PhpUnusedLocalVariableInspection */
							$tableColumnSql = '';
							if($selectedfields[1] == 'date_start') {
								$tableColumnSql = "CAST((CONCAT(date_start,' ',time_start)) AS DATETIME)";
							} else {
								$tableColumnSql = "CAST((CONCAT(due_date,' ',time_end)) AS DATETIME)";
							}
						} else {
							$tableColumnSql = $selectedfields[0].'.'.$selectedfields[1];
						}
						$startDateTime = "'$startDateTime'";
						$endDateTime = "'$endDateTime'";
					}

					$stdfilterlist[$fieldcolname] = $tableColumnSql.' between '.$startDateTime.' and '.$endDateTime;
				}
			}
		}
		// Save the information
		$this->_stdfilterlist = $stdfilterlist;

		$log->info('ReportRun :: Successfully returned getStdFilterList'.$reportid);
		return $stdfilterlist;
	}

	// @codingStandardsIgnoreStart
	/**
	 * Function to get the RunTime filter columns for the given $filtercolumn,$filter,$startdate,$enddate
	 *  @ param $filtercolumn : Type String
	 *  @ param $filter : Type String
	 *  @ param $startdate: Type String
	 *  @ param $enddate : Type String

	 * @param $filtercolumn
	 * @param $filter
	 * @param $startdate
	 * @param $enddate

	 * @return mixed
	 */
	public function RunTimeFilter($filtercolumn, $filter, $startdate, $enddate) {
		// @codingStandardsIgnoreEnd
		if($filtercolumn != 'none') {
			$selectedfields = explode(':',$filtercolumn);
			if($selectedfields[0] == 'vtiger_crmentity'.$this->primarymodule) {
				$selectedfields[0] = 'vtiger_crmentity';
			}
			if($filter == 'custom') {
				if($startdate != '0000-00-00' && $enddate != '0000-00-00' && $startdate != '' && $enddate != '' && $selectedfields[0] != '' && $selectedfields[1] != '') {
					$stdfilterlist[$filtercolumn] = $selectedfields[0].'.'.$selectedfields[1]." between '".$startdate." 00:00:00' and '".$enddate." 23:59:00'";
				}
			} else {
				if($startdate != '' && $enddate != '') {
					$startenddate = $this->getStandarFiltersStartAndEndDate($filter);
					if($startenddate[0] != '' && $startenddate[1] != '' && $selectedfields[0] != '' && $selectedfields[1] != '') {
						$stdfilterlist[$filtercolumn] = $selectedfields[0].'.'.$selectedfields[1]." between '".$startenddate[0]." 00:00:00' and '".$startenddate[1]." 23:59:00'";
					}
				}
			}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		return $stdfilterlist;
	}

	// @codingStandardsIgnoreStart
	/**
	 * Function to get the RunTime Advanced filter conditions
	 *  @ param $advft_criteria : Type Array
	 *  @ param $advft_criteria_groups : Type Array

	 * @param $advft_criteria
	 * @param $advft_criteria_groups

	 * @return string

	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function RunTimeAdvFilter($advft_criteria, $advft_criteria_groups) {
		// @codingStandardsIgnoreEnd
		$adb = PearDatabase::getInstance();

		$advfilterlist = array();

		if(!empty($advft_criteria)) {
			foreach($advft_criteria as $column_index => $column_condition) {
				if(empty($column_condition)) {
					continue;
				}

				$adv_filter_column = $column_condition['columnname'];
				$adv_filter_comparator = $column_condition['comparator'];
				$adv_filter_value = $column_condition['value'];
				$adv_filter_column_condition = $column_condition['columncondition'];
				$adv_filter_groupid = $column_condition['groupid'];

				$column_info = explode(':',$adv_filter_column);

				$moduleFieldLabel = $column_info[2];
				/** @noinspection PhpUnusedLocalVariableInspection */
				$fieldName = $column_info[3];
				list($module, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
				$fieldInfo = getFieldByReportLabel($module, $fieldLabel);
				$fieldType = null;
				if(!empty($fieldInfo)) {
					$field = WebserviceField::fromArray($adb, $fieldInfo);
					$fieldType = $field->getFieldDataType();
				}

				if($fieldType == 'currency') {
					// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
					/** @noinspection PhpUndefinedVariableInspection */
					if($field->getUIType() == '72') {
						$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value, null, true);
					} else {
						$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value);
					}
				}

				$temp_val = explode(',',$adv_filter_value);
				if(($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' )) {
					$val = array();
					$countTempVal = count($temp_val);
					for($x=0; $x<$countTempVal; $x++) {
						if($column_info[4] == 'D') {
							/** @noinspection PhpParamsInspection */
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDBInsertDateValue();
						} else if($column_info[4] == 'DT') {
							/** @noinspection PhpParamsInspection */
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDBInsertDateTimeValue();
						} else {
							/** @noinspection PhpParamsInspection */
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDBInsertTimeValue();
						}
					}
					$adv_filter_value = implode(',',$val);
				}
				$criteria = array();
				$criteria['columnname'] = $adv_filter_column;
				$criteria['comparator'] = $adv_filter_comparator;
				$criteria['value'] = $adv_filter_value;
				$criteria['column_condition'] = $adv_filter_column_condition;

				$advfilterlist[$adv_filter_groupid]['columns'][] = $criteria;
			}

			foreach($advft_criteria_groups as $group_index => $group_condition_info) {
				if(empty($group_condition_info)) {
					continue;
				}
				if(empty($advfilterlist[$group_index])) {
					continue;
				}
				$advfilterlist[$group_index]['condition'] = $group_condition_info['groupcondition'];
				$noOfGroupColumns = count($advfilterlist[$group_index]['columns']);
				if(!empty($advfilterlist[$group_index]['columns'][($noOfGroupColumns-1)]['column_condition'])) {
					$advfilterlist[$group_index]['columns'][($noOfGroupColumns-1)]['column_condition'] = '';
				}
			}
			$noOfGroups = count($advfilterlist);
			if(!empty($advfilterlist[$noOfGroups]['condition'])) {
				$advfilterlist[$noOfGroups]['condition'] = '';
			}

			$advfiltersql = $this->generateAdvFilterSql($advfilterlist);
		}
		/** @noinspection PhpUndefinedVariableInspection */
		return $advfiltersql;
	}

	/**
	 * Function to get standardfilter for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the query of columnlist for the selected columns

	 * @param $reportid

	 * @return string
	 */
	public function getStandardCriterialSql($reportid) {
		global $adb;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$modules;
		global $log;
		$sSQL = '';

		$sreportstdfiltersql = 'SELECT vtiger_reportdatefilter.* FROM vtiger_report';
		$sreportstdfiltersql .= ' INNER JOIN vtiger_reportdatefilter ON vtiger_report.reportid = vtiger_reportdatefilter.datefilterid';
		$sreportstdfiltersql .= ' WHERE vtiger_report.reportid = ?';

		$result = $adb->pquery($sreportstdfiltersql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for($i=0; $i<$noofrows; $i++) {
			$fieldcolname = $adb->query_result($result,$i,'datecolumnname');
			$datefilter = $adb->query_result($result,$i,'datefilter');
			$startdate = $adb->query_result($result,$i,'startdate');
			$enddate = $adb->query_result($result,$i,'enddate');

			if($fieldcolname != 'none') {
				$selectedfields = explode(':',$fieldcolname);
				if($selectedfields[0] == 'vtiger_crmentity'.$this->primarymodule) {
					$selectedfields[0] = 'vtiger_crmentity';
				}
				if($datefilter == 'custom') {
					if($startdate != '0000-00-00' && $enddate != '0000-00-00' && $selectedfields[0] != '' && $selectedfields[1] != ''
						&& $startdate != '' && $enddate != ''
					) {
						/** @noinspection PhpParamsInspection */
						$startDateTime = new DateTimeField($startdate.' '. date('H:i:s'));
						$startdate = $startDateTime->getDisplayDate();
						/** @noinspection PhpParamsInspection */
						$endDateTime = new DateTimeField($enddate.' '. date('H:i:s'));
						$enddate = $endDateTime->getDisplayDate();

						$sSQL .= $selectedfields[0].'.'.$selectedfields[1]." between '".$startdate."' and '".$enddate."'";
					}
				} else {
					$startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
					/** @noinspection PhpParamsInspection */
					$startDateTime = new DateTimeField($startenddate[0].' '. date('H:i:s'));
					$startdate = $startDateTime->getDisplayDate();
					/** @noinspection PhpParamsInspection */
					$endDateTime = new DateTimeField($startenddate[1].' '. date('H:i:s'));
					$enddate = $endDateTime->getDisplayDate();

					if($startenddate[0] != '' && $startenddate[1] != '' && $selectedfields[0] != '' && $selectedfields[1] != '') {
						$sSQL .= $selectedfields[0].'.'.$selectedfields[1]." between '".$startdate."' and '".$enddate."'";
					}
				}
			}
		}
		$log->info('ReportRun :: Successfully returned getStandardCriterialSql'.$reportid);
		return $sSQL;
	}

	// @codingStandardsIgnoreStart
	/**
	 * Function to get standardfilter startdate and enddate for the given type
	 *  @ param $type : Type String
	 *  returns the $datevalue Array in the given format
	 *  $datevalue = Array(0=>$startdate,1=>$enddate)

	 * @param $type

	 * @return mixed

	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function getStandarFiltersStartAndEndDate($type) {
		$today = date('Y-m-d',mktime(0, 0, 0, date('m'), date('d'), date('Y')));
		$tomorrow  = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')+1), date('Y')));
		$yesterday  = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')-1), date('Y')));

		$currentmonth0 = date('Y-m-d',mktime(0, 0, 0, date('m'), '01',   date('Y')));
		$currentmonth1 = date('Y-m-t');
		$lastmonth0 = date('Y-m-d',mktime(0, 0, 0, (date('m')-1), '01',   date('Y')));
		$lastmonth1 = date('Y-m-t', strtotime('-1 Month'));
		$nextmonth0 = date('Y-m-d',mktime(0, 0, 0, (date('m')+1), '01',   date('Y')));
		$nextmonth1 = date('Y-m-t', strtotime('+1 Month'));

		$lastweek0 = date('Y-m-d',strtotime('-2 week Sunday'));
		$lastweek1 = date('Y-m-d',strtotime('-1 week Saturday'));

		$thisweek0 = date('Y-m-d',strtotime('-1 week Sunday'));
		$thisweek1 = date('Y-m-d',strtotime('this Saturday'));

		$nextweek0 = date('Y-m-d',strtotime('this Sunday'));
		$nextweek1 = date('Y-m-d',strtotime('+1 week Saturday'));

		$next7days = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')+6), date('Y')));
		$next30days = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')+29), date('Y')));
		$next60days = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')+59), date('Y')));
		$next90days = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')+89), date('Y')));
		$next120days = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')+119), date('Y')));

		$last7days = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')-6), date('Y')));
		$last30days = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')-29), date('Y')));
		$last60days = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')-59), date('Y')));
		$last90days = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')-89), date('Y')));
		$last120days = date('Y-m-d',mktime(0, 0, 0, date('m'), (date('d')-119), date('Y')));

		$currentFY0 = date('Y-m-d',mktime(0, 0, 0, '01', '01',   date('Y')));
		$currentFY1 = date('Y-m-t',mktime(0, 0, 0, '12', date('d'),   date('Y')));
		$lastFY0 = date('Y-m-d',mktime(0, 0, 0, '01', '01',   (date('Y')-1)));
		$lastFY1 = date('Y-m-t', mktime(0, 0, 0, '12', date('d'), (date('Y')-1)));
		$nextFY0 = date('Y-m-d',mktime(0, 0, 0, '01', '01',   (date('Y')+1)));
		$nextFY1 = date('Y-m-t', mktime(0, 0, 0, '12', date('d'), (date('Y')+1)));

		if(date('m') <= 3) {
			$cFq = date('Y-m-d',mktime(0, 0, 0, '01','01',date('Y')));
			$cFq1 = date('Y-m-d',mktime(0, 0, 0, '03','31',date('Y')));
			$nFq = date('Y-m-d',mktime(0, 0, 0, '04','01',date('Y')));
			$nFq1 = date('Y-m-d',mktime(0, 0, 0, '06','30',date('Y')));
			$pFq = date('Y-m-d',mktime(0, 0, 0, '10','01',(date('Y')-1)));
			$pFq1 = date('Y-m-d',mktime(0, 0, 0, '12','31',(date('Y')-1)));
		} else if(date('m') > 3 && date('m') <= 6) {
			$pFq = date('Y-m-d',mktime(0, 0, 0, '01','01',date('Y')));
			$pFq1 = date('Y-m-d',mktime(0, 0, 0, '03','31',date('Y')));
			$cFq = date('Y-m-d',mktime(0, 0, 0, '04','01',date('Y')));
			$cFq1 = date('Y-m-d',mktime(0, 0, 0, '06','30',date('Y')));
			$nFq = date('Y-m-d',mktime(0, 0, 0, '07','01',date('Y')));
			$nFq1 = date('Y-m-d',mktime(0, 0, 0, '09','30',date('Y')));
		} else if(date('m') > 6 && date('m') <= 9) {
			$nFq = date('Y-m-d',mktime(0, 0, 0, '10','01',date('Y')));
			$nFq1 = date('Y-m-d',mktime(0, 0, 0, '12','31',date('Y')));
			$pFq = date('Y-m-d',mktime(0, 0, 0, '04','01',date('Y')));
			$pFq1 = date('Y-m-d',mktime(0, 0, 0, '06','30',date('Y')));
			$cFq = date('Y-m-d',mktime(0, 0, 0, '07','01',date('Y')));
			$cFq1 = date('Y-m-d',mktime(0, 0, 0, '09','30',date('Y')));
		} else if(date('m') > 9 && date('m') <= 12) {
			$nFq = date('Y-m-d',mktime(0, 0, 0, '01','01',(date('Y')+1)));
			$nFq1 = date('Y-m-d',mktime(0, 0, 0, '03','31',(date('Y')+1)));
			$pFq = date('Y-m-d',mktime(0, 0, 0, '07','01',date('Y')));
			$pFq1 = date('Y-m-d',mktime(0, 0, 0, '09','30',date('Y')));
			$cFq = date('Y-m-d',mktime(0, 0, 0, '10','01',date('Y')));
			$cFq1 = date('Y-m-d',mktime(0, 0, 0, '12','31',date('Y')));
		}

		if($type == 'today') {
			$datevalue[0] = $today;
			$datevalue[1] = $today;
		} else if($type == 'yesterday') {
			$datevalue[0] = $yesterday;
			$datevalue[1] = $yesterday;
		} else if($type == 'tomorrow') {
			$datevalue[0] = $tomorrow;
			$datevalue[1] = $tomorrow;
		} else if($type == 'thisweek') {
			$datevalue[0] = $thisweek0;
			$datevalue[1] = $thisweek1;
		} else if($type == 'lastweek') {
			$datevalue[0] = $lastweek0;
			$datevalue[1] = $lastweek1;
		} else if($type == 'nextweek') {
			$datevalue[0] = $nextweek0;
			$datevalue[1] = $nextweek1;
		} else if($type == 'thismonth') {
			$datevalue[0] =$currentmonth0;
			$datevalue[1] = $currentmonth1;
		} else if($type == 'lastmonth') {
			$datevalue[0] = $lastmonth0;
			$datevalue[1] = $lastmonth1;
		} else if($type == 'nextmonth') {
			$datevalue[0] = $nextmonth0;
			$datevalue[1] = $nextmonth1;
		} else if($type == 'next7days') {
			$datevalue[0] = $today;
			$datevalue[1] = $next7days;
		} else if($type == 'next30days') {
			$datevalue[0] =$today;
			$datevalue[1] =$next30days;
		} else if($type == 'next60days') {
			$datevalue[0] = $today;
			$datevalue[1] = $next60days;
		} else if($type == 'next90days') {
			$datevalue[0] = $today;
			$datevalue[1] = $next90days;
		} else if($type == 'next120days') {
			$datevalue[0] = $today;
			$datevalue[1] = $next120days;
		} else if($type == 'last7days') {
			$datevalue[0] = $last7days;
			$datevalue[1] = $today;
		} else if($type == 'last30days') {
			$datevalue[0] = $last30days;
			$datevalue[1] = $today;
		} else if($type == 'last60days') {
			$datevalue[0] = $last60days;
			$datevalue[1] = $today;
		} else if($type == 'last90days') {
			$datevalue[0] = $last90days;
			$datevalue[1] = $today;
		} else if($type == 'last120days') {
			$datevalue[0] = $last120days;
			$datevalue[1] = $today;
		} else if($type == 'thisfy') {
			$datevalue[0] = $currentFY0;
			$datevalue[1] = $currentFY1;
		} else if($type == 'prevfy') {
			$datevalue[0] = $lastFY0;
			$datevalue[1] = $lastFY1;
		} else if($type == 'nextfy') {
			$datevalue[0] = $nextFY0;
			$datevalue[1] = $nextFY1;
		} else if($type == 'nextfq') {
			/** @noinspection PhpUndefinedVariableInspection */
			$datevalue[0] = $nFq;
			/** @noinspection PhpUndefinedVariableInspection */
			$datevalue[1] = $nFq1;
		} else if($type == 'prevfq') {
			/** @noinspection PhpUndefinedVariableInspection */
			$datevalue[0] = $pFq;
			/** @noinspection PhpUndefinedVariableInspection */
			$datevalue[1] = $pFq1;
		} else if($type == 'thisfq') {
			/** @noinspection PhpUndefinedVariableInspection */
			$datevalue[0] = $cFq;
			/** @noinspection PhpUndefinedVariableInspection */
			$datevalue[1] = $cFq1;
		} else
		{
			$datevalue[0] = '';
			$datevalue[1] = '';
		}
		return $datevalue;
	}
	// @codingStandardsIgnoreEnd

	// @codingStandardsIgnoreStart
	/**
	 * Function to get getGroupingList for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the $grouplist Array in the following format
	 * This function also sets the return value in the class variable $this->groupbylist

	 * @param $reportid

	 * @return array

	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	/**
	 * Obtiene la lista de agrupamientos (GROUP BY) configurados para el reporte indicado.
	 * Devuelve un array asociativo fieldcolname => SQL para usar en GROUP BY.
	 * También guarda el resultado en $this->_groupinglist para cache interno.
	 */
	public function getGroupingList($reportid) {
		// @codingStandardsIgnoreEnd
		global $adb;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$modules;
		global $log;

		// Have we initialized information already?
		// Si ya existe la lista de agrupamientos en caché, la retorna directamente
		if($this->_groupinglist !== false) {
			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return $this->_groupinglist;
		}

		// Construye la consulta SQL para obtener columnas de ordenamiento y agrupamiento definidas en el reporte
		$sreportsortsql = ' SELECT vtiger_reportsortcol.*, vtiger_reportgroupbycolumn.* FROM vtiger_report';
		$sreportsortsql .= ' INNER JOIN vtiger_reportsortcol ON vtiger_report.reportid = vtiger_reportsortcol.reportid AND vtiger_reportsortcol.columnname <> "none"';
		$sreportsortsql .= ' LEFT JOIN vtiger_reportgroupbycolumn ON (vtiger_report.reportid = vtiger_reportgroupbycolumn.reportid AND vtiger_reportsortcol.sortcolid = vtiger_reportgroupbycolumn.sortid)';
		$sreportsortsql .= ' WHERE vtiger_report.reportid =? AND vtiger_reportsortcol.columnname IN (SELECT columnname from vtiger_selectcolumn WHERE queryid=?) ORDER BY vtiger_reportsortcol.sortcolid';

		// Ejecuta la consulta preparada con el id del reporte
		$result = $adb->pquery($sreportsortsql, array($reportid, $reportid));
		// Array donde se almacenarán los campos y expresiones de agrupamiento
		$grouplist = array();

		// Itera sobre cada columna de ordenamiento/agrupamiento encontrada
		while($reportsortrow = $adb->fetch_array($result)) {			
			// fieldcolname tiene el formato tabla:columna:modulo_campo:fieldname:tipo
			$fieldcolname = $reportsortrow['columnname'];
			/** @noinspection PhpUnusedLocalVariableInspection */
			list($tablename,$colname,$module_field,$fieldname,$single) = explode(':',$fieldcolname);
			// Obtiene el sentido de orden (ASC/DESC) definido para la columna
			$sortorder = strtoupper($reportsortrow['sortorder']);
			// Si el orden no es válido, se ignora
			if ($sortorder !== 'ASC' && $sortorder !== 'DESC') {
				$sortorder = '';
			}

			if($fieldcolname != 'none') {
				$selectedfields = explode(':',$fieldcolname);

				if($selectedfields[0] == 'vtiger_crmentity'.$this->primarymodule) {
					$selectedfields[0] = 'vtiger_crmentity';
				}
				// Si es un campo personalizado (custom field), decodifica el nombre

				if(stripos($selectedfields[1],'cf_')==0 && stristr($selectedfields[1],'cf_')==true) {
					$sqlvalue = $selectedfields[0].".".$selectedfields[1]; // tabla.campo
				} else {
					//$sqlvalue = self::replaceSpecialChar($selectedfields[2]);
					$sqlvalue = $selectedfields[0].".".$selectedfields[1]; // tabla.campo
				}

				// Si hay orden definido, lo agrega a la expresión SQL
				if ($sortorder !== '') {
					$sqlvalue .= ' ' . $sortorder;
				}
				// MONOLITHIC phase 6 customization

				// Si es un campo de tipo fecha y tiene criterio de agrupamiento por fecha, lo procesa especialmente
				if($selectedfields[4]=='D' && strtolower($reportsortrow['dategroupbycriteria'])!='none') {
					$sqlvalueF = $selectedfields[0].".".$selectedfields[1]; // tabla.campo
					/*
					$groupField = $module_field; // Campo de agrupación temporal
					$groupCriteria = $reportsortrow['dategroupbycriteria']; // Criterio temporal (ej: mes, año, etc.)
					if(in_array($groupCriteria,array_keys($this->groupByTimeParent))) {
						$parentCriteria = $this->groupByTimeParent[$groupCriteria];
						foreach($parentCriteria as $criteria){
							$groupByCondition[]=$this->GetTimeCriteriaCondition($criteria, $groupField).' '.$sortorder;
						}
					}
					$groupByCondition[] =$this->GetTimeCriteriaCondition($groupCriteria, $groupField).' '.$sortorder; 
					*/
					//Criterio principal
					$groupByCondition[]= $selectedfields[0] .".". $selectedfields[1] .' '.$sortorder;
					// Une todos los criterios de agrupamiento por fecha
					$sqlvalue = implode(', ',$groupByCondition);					
				}
				$grouplist[$fieldcolname] = $sqlvalue;
				// Determina el módulo base a partir del nombre del campo
				$temp = explode('_',$selectedfields[2],2);
				$module = $temp[0];
				// Si el usuario tiene permiso sobre el campo, mantiene la expresión SQL
				if(CheckFieldPermission($fieldname,$module) == 'true') {
					$grouplist[$fieldcolname] = $sqlvalue;
				} else {
					// Si no tiene permiso, usa solo tabla.columna
				}
			}
		}

		// Save the information
		$this->_groupinglist = $grouplist;

		$log->info('ReportRun :: Successfully returned getGroupingList'.$reportid);
		// Devuelve el array de agrupamientos para el reporte
		return $grouplist;
	}

	/**
	 * Function to replace special characters
	 *  @ param $selectedfield : type string
	 *  this returns the string for grouplist

	 * @param $selectedfield

	 * @return mixed|string
	 */
	public function replaceSpecialChar($selectedfield) {
		$selectedfield = decode_html(decode_html($selectedfield));
		preg_match('/&/', $selectedfield, $matches);
		if(!empty($matches)) {
			$selectedfield = str_replace('&', 'and',($selectedfield));
		}
		return $selectedfield;
	}

	/**
	 * Function to get the selectedorderbylist for the given reportid
	 *  @ param $reportid : type integer
	 *  this returns the columns query for the sortorder columns
	 *  this function also sets the return value in the class variable $this->orderbylistsql

	 * @param $reportid

	 * @return string
	 */
	public function getSelectedOrderbyList($reportid) {
		global $adb;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$modules;
		global $log;
		$n = 0;
		$sSQL = '';

		$sreportsortsql = 'SELECT vtiger_reportsortcol.* FROM vtiger_report';
		$sreportsortsql .= ' INNER JOIN vtiger_reportsortcol ON vtiger_report.reportid = vtiger_reportsortcol.reportid';
		$sreportsortsql .= ' WHERE vtiger_report.reportid =? ORDER BY vtiger_reportsortcol.sortcolid';

		$result = $adb->pquery($sreportsortsql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for($i=0; $i<$noofrows; $i++) {
			$fieldcolname = $adb->query_result($result,$i,'columnname');
			$sortorder = $adb->query_result($result,$i,'sortorder');

			if($sortorder == 'Ascending') {
				$sortorder = 'ASC';
			} else if($sortorder == 'Descending') {
				$sortorder = 'DESC';
			}

			if($fieldcolname != 'none') {
				$this->orderbylistcolumns[] = $fieldcolname;
				$n++;
				$selectedfields = explode(':',$fieldcolname);
				if($n > 1) {
					$sSQL .= ', ';
					$this->orderbylistsql .= ', ';
				}
				if($selectedfields[0] == 'vtiger_crmentity'.$this->primarymodule) {
					$selectedfields[0] = 'vtiger_crmentity';
				}
				$sSQL .= $selectedfields[0].'.'.$selectedfields[1].' '.$sortorder;
				$this->orderbylistsql .= $selectedfields[0].'.'.$selectedfields[1].' '.$selectedfields[2];
			}
		}
		$log->info('ReportRun :: Successfully returned getSelectedOrderbyList'.$reportid);
		return $sSQL;
	}

	/**
	 * Function to get secondary Module for the given Primary module and secondary module
	 *  @ param $module : type String
	 *  @ param $secmodule : type String
	 * this returns join query for the given secondary module

	 * @param $module
	 * @param $secmodule

	 * @return string
	 */
	public function getRelatedModulesQuery($module, $secmodule) {
    global $log, $current_user;
    $query = '';
    // --- INICIO OPTIMIZACIÓN: solo módulos secundarios realmente usados ---
    if ($secmodule != '') {
        // Obtener la lista de módulos secundarios posibles
        $secondaryModules = array();
        if (!empty($this->secondarymodule)) {
            $secondaryModules = explode(':', $this->secondarymodule);
        }
       // Elimina duplicados por si acaso
		$secondaryModules = array_unique($secondaryModules);
        
		// Genera los JOINs para todos los módulos secundarios, sin filtrar por columnas
		foreach ($secondaryModules as $key => $value) {
			if (empty($value)) continue;
            $foc = CRMEntity::getInstance($value);
			$query .= ' ' . $foc->generateReportsSecQuery($module, $value, $secondaryModules);
            // FIX: No aplicar filtros de permisos a módulos secundarios
            // Los filtros de permisos solo deben aplicarse al módulo principal
            // Los módulos secundarios ya están filtrados por LEFT JOIN y deleted=0
            // $query .= getNonAdminAccessControlQuery($value, $current_user);
        }
    }
    // --- FIN OPTIMIZACIÓN ---
    $log->info('ReportRun :: Successfully returned getRelatedModulesQuery ' . $query);
    return $query;
}


	// @codingStandardsIgnoreStart
	/**
	 * Function to get report query for the given module
	 *  @ param $module : type String
	 *  this returns join query for the given module

	 * @param $module
	 * @param string $type

	 * @return string

	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function getReportsQuery($module, $type = '', $allowedRelatedModules = null) {
        $startQueryTime = microtime(true);
		// @codingStandardsIgnoreEnd
		global $log, $current_user, $adb;
		$secondary_module ="'";
		$secondary_module .= str_replace(':',"','",$this->secondarymodule);
		/** @noinspection PhpUnusedLocalVariableInspection */
		$secondary_module .="'";
		$query = '';

		/** @noinspection PhpUnusedLocalVariableInspection */
		$modulesActive = array();
		$modulesActive = getModuleActive($adb);
		if($module == 'Calendar') {
			$query = 'from vtiger_activity
                inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid
                left join vtiger_activitycf on vtiger_activitycf.activityid = vtiger_crmentity.crmid
                left join vtiger_groups as vtiger_groupsCalendar on vtiger_groupsCalendar.groupid = vtiger_crmentity.smownerid
                left join vtiger_users on (vtiger_users.id = vtiger_crmentity.smownerid OR vtiger_users.id = vtiger_crmentity.modifiedby)
                left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                left join vtiger_seactivityrel on vtiger_seactivityrel.activityid = vtiger_activity.activityid AND vtiger_seactivityrel.crmid in (select crmid from vtiger_crmentity as crm where deleted=0)
                left join vtiger_activity_reminder on vtiger_activity_reminder.activity_id = vtiger_activity.activityid
                left join vtiger_recurringevents on vtiger_recurringevents.activityid = vtiger_activity.activityid';
			$query .=' left join vtiger_users as vtiger_lastModifiedByCalendar on vtiger_lastModifiedByCalendar.id = vtiger_crmentity.modifiedby '
                .$this->getRelatedModulesQuery($module,$this->secondarymodule).
				getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				WHERE vtiger_crmentity.deleted=0 and (vtiger_activity.activitytype != 'Emails')";
		} else {
			if($module!='') {
				$focus = CRMEntity::getInstance($module);
				
				// LOG: Verificar permisos antes de construir query
				$local_user = clone $current_user;
				require('user_privileges/user_privileges.php');

				$query = $focus->generateReportsQuery($module, $allowedRelatedModules)
					.$this->getRelatedModulesQuery($module,$this->secondarymodule)
					.getNonAdminAccessControlQuery($this->primarymodule,$current_user)
					.$this->hasGridColumn ('join').
					' WHERE vtiger_crmentity.deleted=0';
			}
		}

	return $query;
	}
	
	// @codingStandardsIgnoreStart
	/**
	 * Function to get query for the given reportid,filterlist,type
	 *  @ param $reportid : Type integer
	 *  @ param $filtersql : Type Array
	 *  @ param $module : Type String
	 *  this returns join query for the report

	 * @param $reportid
	 * @param $filtersql
	 * @param string $type
	 * @param boolean

	 * @return string

	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function sGetSQLforReport($reportid, $filtersql, $type = '', $chartReport = false) {
		// @codingStandardsIgnoreEnd
		global $log;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$current_user;
		$columnlist = $this->getQueryColumnsList($reportid,$type);
		$groupslist = $this->getGroupingList($reportid);
		$groupTimeList = $this->getGroupByTimeList($reportid);
		$stdfilterlist = $this->getStdFilterList($reportid);
		$columnstotallist = $this->getColumnsTotal($reportid);
		$advfiltersql = $this->getAdvFilterSql($reportid);
		$this->totallist = $columnstotallist;
		$stdfiltersql = '';
		$wheresql = '';
		$columnstotalsql = '';
		$selectedcolumns = '';
		$groupsquery = '';
		/** @noinspection PhpUnusedLocalVariableInspection */
		$tab_id = getTabid($this->primarymodule);
		// Fix for ticket #4915.
		$selectlist = $columnlist;
		// columns list
        if(isset($selectlist)) {
            // --- INICIO OPTIMIZACIÓN REPORTES SUMMARY ---
            if ($this->reporttype == 'summary' && !empty($groupslist)) {
                // Función auxiliar para extraer tabla.columna de los distintos formatos
                $extractTableColumn = function($item) {
                    // Si es formato SQL tipo vtiger_tabla.columna AS 'alias'
                    if (preg_match("/^([a-zA-Z0-9_]+\.[a-zA-Z0-9_]+)/", $item, $matches)) {
                        return strtolower($matches[1]);
                    }
                    // Si es formato vtiger:tabla:columna:... o Tabla:columna:...
                    $parts = explode(':', $item);
                    if (count($parts) >= 2) {
                        $tabla = strtolower(str_replace('vtiger_', '', $parts[0]));
                        $columna = strtolower($parts[1]);
                        return $tabla.'.'.$columna;
                    }
                    return strtolower($item);
                };

                // Normalizar groupslist y columnstotallist a tabla.columna
				$i = 0;
				foreach ($groupslist as $clave => $valor){
					list($tablecolname,$colname, $collabelgroup, $resto)= explode(':',$clave,4);
					$grouplist_norm[$i] = $tablecolname . ".". $colname;
					$i= $i+1;
				};
				$i = 0;
				foreach ($columnstotallist as $clave => $valor){
					list($resto1,$tablecolname,$colname,$asEtiqueta1,$resto)= explode(':',$clave,5);
					list($colOperacion,$asEtiqueta, $resto) = explode("'",$valor,3);
					$columnstotallist_norm[$i][0] = $tablecolname . ".". $colname;
					$columnstotallist_norm[$i][1] = $colOperacion . " AS '". $asEtiqueta1 . "'";
					$columnstotallist_norm[$i][2] = $asEtiqueta1;
					$i= $i+1;
				};
                $selectedcolumns_arr = array();
				$k=0;
                foreach ($selectlist as $col) {
                    $col_norm = $extractTableColumn($col);
                    // Si la columna está en $groupslist (agrupación), la dejamos tal cual (clave, valor o normalizada)
                    $es_group = false;
                    // Verificar normalizado
                    if (in_array($col_norm, $grouplist_norm)) {
                        $es_group = true;
                    } else {
                        // Verificar contra los valores y claves originales de $groupslist
                        foreach ($groupslist as $k => $v) {
                            if ($col_norm == strtolower($v) || $col_norm == strtolower($k) || $col == $v || $col == $k) {
                                $es_group = true;
                                break;
                            }
                        }
                    }
                    if ($es_group) {
						// Si la columna está en el group by, siempre se muestra tal cual.
                        $selectedcolumns_arr[] = $col;
                        continue;
                    }

                    // Si la columna está en $columnstotallist, usamos la operación indicada en columnstotallist_norm
					$encontrado = false;
					foreach ($columnstotallist_norm as $fila) {
                        if ($col_norm == $fila[0]) {
							$selectedcolumns_arr[] = $fila[1]; // Agrega cada operación (SUM, AVG, etc.)
							$encontrado = true;
							// No hacemos break, permitimos múltiples agregaciones para la misma columna
											}										
										}
                    if ($encontrado) continue;
                   
                    // Si es la columna de acción, la sustituimos por null
                    if (trim(strtolower($col)) == "vtiger_crmentity.crmid as 'lbl_action'" || trim(strtolower($col)) == 'vtiger_crmentity.crmid as "lbl_action"') {
						$selectedcolumns_arr[] = "count(*) AS 'LBL_RECORD_COUNT'";
						if ($chartReport = 'HTML') {
                        	$selectedcolumns_arr[] = "null AS 'LBL_ACTION'";
						}
                    } else {
                        // Si la columna está en el group by, dejarla tal cual
                        if (in_array($col_norm, $groupslist_norm)) {
                            $selectedcolumns_arr[] = $col;
                        } else {
                            // --- NUEVA LÓGICA DE SUMARIZACIÓN AUTOMÁTICA ---
                            // Buscar el tipo de dato de la columna en $columnlist (key)
                            $tipoDato = null;
                            $alias = null;
                            foreach($columnlist as $key => $value) {
                                $coltype = explode(':', $key);
                                // $coltype[0]=tabla, [1]=columna, [2]=etiqueta, [3]=fieldname, [4]=tipo
                                $columnaQ = explode("AS", $value);
                                if (isset($columnaQ[0]) && trim($columnaQ[0]) == $col_norm) {
                                    $tipoDato = isset($coltype[4]) ? $coltype[4] : null;
                                    // Extraer alias
                                    $alias = isset($columnaQ[1]) ? trim($columnaQ[1], " ' ") : (isset($coltype[2]) ? $coltype[2] : $col_norm);
                                    break;
                                }
                            }
                            // Si no se encontró tipo, intentar heurística para grid
                            if ($tipoDato === null && strpos($col_norm, 'vtiger_grid_summary_') === 0) {
                                $tipoDato = 'N'; // asumir numérico para grid summary
                            }
                            if ($tipoDato === 'N' || $tipoDato === 'NN' || $tipoDato === 'real' || $tipoDato === 'decimal' || $tipoDato === 'float' || $tipoDato === 'double' || $tipoDato === 'int' || $tipoDato === '7' || $tipoDato === '71' || $tipoDato === '72') {
                                // Numérico: SUM
                                $selectedcolumns_arr[] = "SUM(" . $col_norm . ") AS '" . $alias . "'";
                            } else {
                                // No numérico: COUNT
                                $selectedcolumns_arr[] = "COUNT(" . $col_norm . ") AS '" . $alias . "'";
                            }
                            // No agregar la columna original sin función
                        }
                    }
                }
                $selectedcolumns = implode(', ', $selectedcolumns_arr);
                if($chartReport == true) {
                    $selectedcolumns .= ", count(*) AS 'groupby_count'";
                }
            } else {
                // Reporte tabulado tradicional

                $selectedcolumns = implode(', ',$selectlist);
                if($chartReport == true) {
                    $selectedcolumns .= ", count(*) AS 'groupby_count'";
                }
            }
            // --- FIN OPTIMIZACIÓN REPORTES SUMMARY ---
        }
		// groups list
		if(isset($groupslist)) {
			$groupsquery = implode(', ',$groupslist);
		}

		if(isset($groupTimeList)) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			$groupTimeQuery = implode(', ',$groupTimeList);
		}

		// standard list
		if(isset($stdfilterlist)) {
			$stdfiltersql = implode(', ',$stdfilterlist);
		}

		// columns to total list
		if(isset($columnstotallist)) {
			/** @noinspection PhpParamsInspection */
			$columnstotalsql = implode(', ',$columnstotallist);
		}

		if($stdfiltersql != '') {
			$wheresql = ' and '.$stdfiltersql;
		}

		if(isset($filtersql) && $filtersql !== false) {
			$advfiltersql = $filtersql;
		}

		if($advfiltersql != '') {
			$wheresql .= ' and '.$advfiltersql;
		}

		// --- INICIO OPTIMIZACIÓN DE MÓDULOS RELACIONADOS ---
		// 1. Obtener todos los módulos secundarios seleccionados
		$allSecondaryModules = array();
		if (!empty($this->secondarymodule)) {
			$allSecondaryModules = explode(':', $this->secondarymodule);
		} else {
			// Fallback: obtener de la base de datos
			$sql_a = "SELECT secondarymodules AS secondarymodules FROM vtiger_reportmodules WHERE reportmodulesid = ?";
			global $adb;
			$result = $adb->pquery($sql_a, array($reportid));
			if ($result && $adb->num_rows($result) > 0) {
				$secondarymodules_str = $adb->query_result($result, 0, 'secondarymodules');
				if (!empty($secondarymodules_str)) {
					$allSecondaryModules = explode(':', $secondarymodules_str);
				}
			}
		}

		// 2. Recolectar módulos usados en columnas, filtros y agrupamientos
		$usedRelatedModules = array();
		// a) Columnas seleccionadas
		if (!empty($columnlist) && is_array($columnlist)) {
			foreach ($columnlist as $col) {
				$parts = explode(':', $col);
				$mod = $parts[0];
				if ($mod !== $this->primarymodule && in_array($mod, $allSecondaryModules) && !in_array($mod, $usedRelatedModules)) {
					$usedRelatedModules[] = $mod;
				}
			}
		}
		// b) Columnas de filtros avanzados
		if (!empty($advfiltersql) && is_array($this->getAdvFilterList($reportid))) {
			foreach ($this->getAdvFilterList($reportid) as $filter) {
				if (!empty($filter['columnname'])) {
					$parts = explode(':', $filter['columnname']);
					$mod = $parts[0];
					if ($mod !== $this->primarymodule && in_array($mod, $allSecondaryModules) && !in_array($mod, $usedRelatedModules)) {
						$usedRelatedModules[] = $mod;
					}
				}
			}
		}
		// c) Columnas de agrupamiento
		if (!empty($groupslist) && is_array($groupslist)) {
			foreach ($groupslist as $col) {
				$parts = explode(':', $col);
				$mod = $parts[0];
				if ($mod !== $this->primarymodule && in_array($mod, $allSecondaryModules) && !in_array($mod, $usedRelatedModules)) {
					$usedRelatedModules[] = $mod;
				}
			}
		}
		// d) Columnas de totales (summary)
		if (!empty($columnstotallist) && is_array($columnstotallist)) {
			$gg=0;
			foreach ($columnstotallist as $col) {
				$parts = explode(':', $col);
				$mod = $parts[0];
				if ($mod !== $this->primarymodule && in_array($mod, $allSecondaryModules) && !in_array($mod, $usedRelatedModules)) {
					$usedRelatedModules[] = $mod;
				}
			}
		}

		// --- FIN OPTIMIZACIÓN DE MÓDULOS RELACIONADOS ---
		$reportquery = $this->getReportsQuery($this->primarymodule, $type, $usedRelatedModules);

		// If we don't have access to any columns, let us select one column and limit result to shown we have not results
		// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad
		$allColumnsRestricted = false;

		if($type == 'COLUMNSTOTOTAL') {
			if($columnstotalsql != '') {
				$reportquery = 'select '.$columnstotalsql.' '.$reportquery.' '.$wheresql;
			}
		} else {
			if($selectedcolumns == '') {
					// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad

					$selectedcolumns = "''"; // "''" to get blank column name
					$allColumnsRestricted = true;
			}
			if (in_array ( $this->primarymodule, array('Invoice','Quotes','SalesOrder','PurchaseOrder'))) {
				$selectedcolumns = ' distinct '. $selectedcolumns;
			}

			$reportquery = 'SELECT '.$selectedcolumns.' '.$reportquery.' '.$wheresql;
		}
		//2025-07-21 GGC Eliminado lo siguiente por no hacer falta y agregar una condición inválida.
		//$reportquery = listQueryNonAdminChange($reportquery, $this->primarymodule);

		// Si hay columnas de agrupamiento y no es un total de columnas, agregamos GROUP BY y ORDER BY
		if(trim($groupsquery) != '' && $type !== 'COLUMNSTOTOTAL') {
			$reportquery .= ' GROUP BY '.$groupsquery;                 
			$reportquery .= ' ORDER BY '.$groupsquery;
		}

		// Prasad: No columns selected so limit the number of rows directly.
		if($allColumnsRestricted) {
			$reportquery .= ' limit 0';
		}

		preg_match('/&amp;/', $reportquery, $matches);
		if(!empty($matches)) {
			$report=str_replace('&amp;', '&', $reportquery);
			$reportquery = $this->replaceSpecialChar($report);
		}
		$log->info('ReportRun :: Successfully returned sGetSQLforReport'.$reportid);
		return $reportquery;
	}

	// @codingStandardsIgnoreStart
	/** @noinspection PhpInconsistentReturnPointsInspection */
	/**
	 * Function to get the report output in HTML,PDF,TOTAL,PRINT,PRINTTOTAL formats depends on the argument $outputformat

	 * @param $outputformat
	 * @param $filtersql
	 * @param boolean

	 * @return null|string

	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function GenerateReport($outputformat, $filtersql, $directOutput = false, $reportwidth = null) {
		// @codingStandardsIgnoreEnd
		global $adb,$current_user,$php_max_execution_time;
		
// --- Sincronización robusta de numbering_format del usuario antes de instanciar NumberHelper ---
		if (!empty($current_user->column_fields['numbering_format'])) {
			$current_user->numbering_format = $current_user->column_fields['numbering_format'];
		}

		if (!isset($current_user->numbering_format) || empty($current_user->numbering_format)) {
			$resultNF = $adb->pquery('SELECT numbering_format FROM vtiger_users WHERE id=? LIMIT 1', array($current_user->id));
			if ($adb->num_rows($resultNF) > 0) {
				$nfRow = $adb->fetchByAssoc($resultNF, -1, false);
				$current_user->numbering_format = $nfRow['numbering_format'];
			} else {
				$current_user->numbering_format = 'AMERICAN_FORMAT'; // fallback
		}

		if (isset($resultNF)) { unset($resultNF); }
		}
// --- Fin sincronización numbering_format ---

	    $numberingHelper = new NumberHelper($adb, $current_user); // Siempre instancia con el usuario actualizado	
		global $modules,$app_strings;
		global $mod_strings;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$current_language;$theNumberFormat = $current_user->numbering_format;
		//$current_user->numbering_format = 'EUROPEAN_FORMAT';
		$numberingHelper = NumberHelper::getInstance ($adb, $current_user);
		/** @noinspection PhpUnusedLocalVariableInspection */
		$local_user = clone $current_user;
		$secondvalue = '';
		$thirdvalue = '';
		require('user_privileges/user_privileges.php');
		$modules_selected = array();
		$modules_selected[] = $this->primarymodule;

		if(!empty($this->secondarymodule)) {		
			$sec_modules = explode(':',$this->secondarymodule);
			$countSecModules = count($sec_modules);
			for($i=0; $i<$countSecModules; $i++){
				$modules_selected[] = $sec_modules[$i];
			}
		}

		// Update Reference fields list list
		$referencefieldres = $adb->pquery('SELECT tabid, fieldlabel, uitype from vtiger_field WHERE uitype in (10,101)', array());
		
		if($referencefieldres) {
			foreach($referencefieldres as $referencefieldrow) {
				$uiType = $referencefieldrow['uitype'];
				$modprefixedlabel = getTabModuleName($referencefieldrow['tabid']).' '.$referencefieldrow['fieldlabel'];
				$modprefixedlabel = str_replace(' ','_',$modprefixedlabel);

				if($uiType == 10 && !in_array($modprefixedlabel, $this->ui10_fields)) {
					$this->ui10_fields[] = $modprefixedlabel;
				} else if($uiType == 101 && !in_array($modprefixedlabel, $this->ui101_fields)) {
					$this->ui101_fields[] = $modprefixedlabel;
				}
			}
		}

		if($outputformat == 'HTML') {
			/** @noinspection PhpUndefinedFieldInspection */
			$sSQL = $this->sGetSQLforReport($this->reportid,$filtersql,$outputformat);
			
			
			$result = $adb->query($sSQL);
			/** @noinspection PhpUndefinedMethodInspection */
			$error_msg = $adb->database->ErrorMsg();
			if(!$result && $error_msg!='') {
				
				// Performance Optimization: If direct output is requried
				if($directOutput) {
					/** @noinspection PhpUndefinedVariableInspection */
					echo getTranslatedString('LBL_REPORT_GENERATION_FAILED', $currentModule) . '<br>' . $error_msg;
					$error_msg = false;
				}
				// END
				return $error_msg;
			}

			// Performance Optimization: If direct output is required
			if($directOutput) {
				echo '<table cellpadding="5" cellspacing="0" align="center" class="table"><tr>';
			}
			// END

			/** @noinspection PhpUndefinedVariableInspection */
			if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
				$picklistarray = $this->getAccessPickListValues();
			}
			if($result) {
				$y=$adb->num_fields($result);
				$arrayHeaders = array();
				$arrayType = array();
				for ($x=0; $x<$y; $x++)
				{
					$fld = $adb->field_name($result, $x);
					if ($fld->name == 'groupby_count') continue;
					$arrayType[] = $fld->type;
					if (in_array($this->getLstringforReportHeaders($fld->name, $fld), $arrayHeaders)) {
						$headerLabel = str_replace('_',' ',$fld->name);
						$arrayHeaders[] = $headerLabel;
					} else
					{
						$headerLabel = str_replace('_',' ',$this->getLstringforReportHeaders($fld->name, $fld));
						if($fld->name == 'ACTION' || $fld->name == 'LBL_ACTION' ) {
							$headerLabel = getTranslatedString('LBL_ACTION','Reports');
						} else if( $fld->name == 'LBL_RECORD_COUNT') {
							$headerLabel = getTranslatedString('LBL_RECORD_COUNT','Reports');
						}
						$arrayHeaders[] = $headerLabel;
					}

					// STRING TRANSLATION starts
					$mod_name = explode(' ',$headerLabel,2);
					$moduleLabel ='';
					if(in_array($mod_name[0],$modules_selected)) {
						$moduleLabel = getTranslatedString($mod_name[0],$mod_name[0]);
					}
					if(!empty($this->secondarymodule)) {
						if($moduleLabel!='') {
							$headerLabel_tmp = $moduleLabel.' '.getTranslatedString($mod_name[1],$mod_name[0]);
						} else {
							$headerLabel_tmp = getTranslatedString($mod_name[0].' '.$mod_name[1]);
						}
					} else {
						if($moduleLabel!='') {
							$headerLabel_tmp = getTranslatedString($mod_name[1],$mod_name[0]);
						} else {
							$headerLabel_tmp = getTranslatedString($mod_name[0].' '.$mod_name[1]);
						}
					}
					if($headerLabel == $headerLabel_tmp) {
						$headerLabel = getTranslatedString($headerLabel_tmp);
					} else {
						$headerLabel = $headerLabel_tmp;
					}
					// STRING TRANSLATION ends
					/** @noinspection PhpUndefinedVariableInspection */
					// Detectar si la columna es numérica y alinear a la derecha el encabezado
					if (isset($arrayType[$x]) && ($arrayType[$x] == 'real' || $arrayType[$x] == 'decimal' || $arrayType[$x] == 'float' || $arrayType[$x] == 'double' || $arrayType[$x] == 'int')) {
						$header .= "<td style='text-align:right;font-size:1.05em;font-weight:400'>".$headerLabel."</td>";
					} else {
						$header .= "<td style='text-align:left;font-size:1.05em;font-weight:400'>".$headerLabel."</td>";
					}
					// Performance Optimization: If direct output is required
					if($directOutput) {
						echo $header;
						$header = '';
					}
					// END
				}

				// Performance Optimization: If direct output is required
				if($directOutput) {
					echo '</tr><tr>';
				}
				// END

				$noofrows = $adb->num_rows($result);
				$custom_field_values = $adb->fetch_array($result);
				/** @noinspection PhpUndefinedFieldInspection */
				$groupslist = $this->getGroupingList($this->reportid);
				$column_definitions = $adb->getFieldsDefinition($result);

				$ndo=0;
				$htmlRecordCount = 0;
				do {
					$ndo++;
					$htmlRecordCount++;
					$arraylists = array();
					if(count($groupslist) == 1) {
						$newvalue = $custom_field_values[0];
					} else if(count($groupslist) == 2) {
						$newvalue = $custom_field_values[0];
						$snewvalue = $custom_field_values[1];
					} else if(count($groupslist) == 3) {
						$newvalue = $custom_field_values[0];
						$snewvalue = $custom_field_values[1];
						$tnewvalue = $custom_field_values[2];
					}
					/** @noinspection PhpUndefinedVariableInspection */
					if($newvalue == '') {
						$newvalue = '-';
					}

					/** @noinspection PhpUndefinedVariableInspection */
					if($snewvalue == '') {
						$snewvalue = '-';
					}

					/** @noinspection PhpUndefinedVariableInspection */
					if($tnewvalue == '') {
						$tnewvalue = '-';
					}

					/** @noinspection PhpUndefinedVariableInspection */
					$valtemplate .= '<tr>';

					// Performance Optimization
					if($directOutput) {
						echo $valtemplate;
						$valtemplate = '';
					}
					// END

					for ($i=0; $i<$y; $i++) {
						$fld = $adb->field_name($result, $i);
						if ($fld->name == 'groupby_count') continue;
						/** @noinspection PhpUnusedLocalVariableInspection */
						$fld_type = $column_definitions[$i]->type;
						/** @noinspection PhpUndefinedVariableInspection */
						if (isset($fld_type) && ($fld_type == 'real' || $fld_type == 'decimal' || $fld_type == 'float' || 	$fld_type == 'double' || $fld_type == 'int')) {
							$vstyle = ' style="text-align:right" ';
						} else {
							$vstyle = ' style="text-align:left" ';
						}
							
						$fieldvalue = getReportFieldValue(
							$this,
							$picklistarray,
							$fld,
							$custom_field_values,
							$i
						);

						// check for Roll based pick list
						/** @noinspection PhpUnusedLocalVariableInspection */
						$temp_val= $fld->name;

						if($fieldvalue == '') {
							$fieldvalue = '-';
						} else if($fld->name == 'LBL_ACTION' && $fieldvalue != '-') {
							$fieldvalue = "<a href='index.php?module={$this->primarymodule}&action=DetailView&record={$fieldvalue}' target='_blank'>".getTranslatedString('LBL_VIEW_DETAILS').'</a>';
						}
						
						// Guardar valor en arraylists para logging posterior
						$arraylists[$fld->name] = $fieldvalue;

						/** @noinspection PhpUndefinedVariableInspection */
						if(($lastvalue == $fieldvalue) && $this->reporttype == 'summary') {
							if($this->reporttype == 'summary') {							
								$valtemplate .= "<td class='rptEmptyGrp'" .$vstyle .">&nbsp;</td>";
							} else {
								$valtemplate .= "<td class='rptData' " .$vstyle .">".getTranslatedString($fieldvalue).'</td>';
							}
						} else if(($secondvalue === $fieldvalue) && $this->reporttype == 'summary') {
							if($lastvalue === $newvalue) {
								$valtemplate .= "<td class='rptEmptyGrp' " .$vstyle .">&nbsp;</td>";
							} else {
								$valtemplate .= "<td class='rptGrpHead' " .$vstyle .">".getTranslatedString($fieldvalue).'</td>';
							}
						} else if(($thirdvalue === $fieldvalue) && $this->reporttype == 'summary') {
							if($secondvalue === $snewvalue) {
								$valtemplate .= "<td class='rptEmptyGrp' " .$vstyle .">&nbsp;</td>";
							} else {
								$valtemplate .= "<td class='rptGrpHead' " .$vstyle .">".getTranslatedString($fieldvalue).'</td>';
							}
						} else {
							if($this->reporttype == 'tabular') {
								if (is_numeric ($fieldvalue) && ( $fld->type == 'real' || $fld_type == 'real' || $fld_type == 'decimal' || $fld_type == 'float' || 	$fld_type == 'double' || $fld_type == 'int') ) {
									$fieldvalue = $numberingHelper->setNumberFormat ($fieldvalue, $fld->name);
									$valtemplate .= " <td  class='rptData' " .$vstyle .">".$fieldvalue.'</td>';
								} else {
									$valtemplate .= "<td  class='rptData' " .$vstyle .">" . getTranslatedString ($fieldvalue) . '</td>';
								}
							} else if($this->reporttype == 'summary') {
								// Formateo numérico centralizado para informes resumen
								if (is_numeric ($fieldvalue) && $fld->type == 'real') {
									$fieldvalue = $numberingHelper->setNumberFormat($fieldvalue, $fld->name);
								}
								$valtemplate .= "<td class='rptData' " .$vstyle .">".getTranslatedString($fieldvalue).'</td>';
							} else {
								$valtemplate .= "<td class='rptData' " .$vstyle .">".getTranslatedString($fieldvalue).'</td>';
							}
						}

						// Performance Optimization: If direct output is required
						if($directOutput) {
							echo $valtemplate;
							$valtemplate = '';
						}
						// END
					}

					$valtemplate .= '</tr>';

					// Performance Optimization: If direct output is required
					if($directOutput) {
						echo $valtemplate;
						$valtemplate = '';
					}
					// END

					$lastvalue = $newvalue;
					$secondvalue = $snewvalue;
					$thirdvalue = $tnewvalue;
					$arr_val[] = $arraylists;
					set_time_limit($php_max_execution_time);
				} while($custom_field_values = $adb->fetch_array($result));

				// Performance Optimization
				if($directOutput) {
					echo '</tr></table>';
					/** @noinspection JSJQueryEfficiency */
					echo "<script type='text/javascript' id='__reportrun_directoutput_recordcount_script'>";
					/** @noinspection JSUnresolvedVariable */
					echo "if($('_reportrun_total')) $('_reportrun_total').innerHTML=$noofrows;</script>";
				} else {
					/** @noinspection PhpUndefinedVariableInspection */
					$sHTML ='<table cellpadding="5" cellspacing="0" align="center" class="table">
					<tr>'.
						$header
						.'<!-- BEGIN values -->
					<tr>'.
						$valtemplate
						.'</tr>
					</table>';
				}
				/** @noinspection PhpUndefinedVariableInspection */
				// --- INICIO: Añadir tabla de totales para reportes tabulados ---
				if ($this->reporttype == 'tabular' && !empty($this->totallist)) {
					$totalHTML = $this->GenerateReport('TOTALHTML', $filtersql);
					if (!empty($totalHTML)) {
						$sHTML .= '<br>' . $totalHTML;
					}
				}
				// --- FIN: Añadir tabla de totales para tabulados ---
				$return_data[] = $sHTML;
				$return_data[] = $noofrows;
				$return_data[] = $sSQL;
				/** @noinspection PhpIncompatibleReturnTypeInspection */
				return $return_data;
			}
		} 
		else if($outputformat == 'PDF') {
			/** @noinspection PhpUndefinedFieldInspection */
			$sSQL = $this->sGetSQLforReport($this->reportid,$filtersql,$outputformat);

			$result = $adb->query($sSQL);
			/** @noinspection PhpUndefinedVariableInspection */
			if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
				$picklistarray = $this->getAccessPickListValues();
			}

			if($result) {
                $headerFieldInfo = $this->getUnifiedReportHeadersAndFields($result, $adb, $modules_selected, $this->secondarymodule);
                // Filtrar columnas que no deben mostrarse en PDF
                $omitFields = array('LBL_ACTION', 'ACTION', 'groupby_count');
                $filteredHeaders = array();
                $filteredFields = array();
                foreach ($headerFieldInfo['fields'] as $idx => $fieldname) {
                    if (!in_array($fieldname, $omitFields)) {
                        $filteredFields[] = $fieldname;
                        $filteredHeaders[] = $headerFieldInfo['headers'][$idx];
						$filteredHeaders1[$fieldname] = $headerFieldInfo['headers'][$idx];
                    }
                }
				$y=$adb->num_fields($result);
				/** @noinspection PhpUnusedLocalVariableInspection */
				$noofrows = $adb->num_rows($result);
				$custom_field_values = $adb->fetch_array($result);
				$column_definitions = $adb->getFieldsDefinition($result);

				$recordCount = 0;
				do {
					$recordCount++;
					$arraylists = array();
					for ($i=0; $i<$y; $i++) {
						$fld = $adb->field_name($result, $i);
						if (in_array($fld->name,$omitFields) ) continue;
						/** @noinspection PhpUnusedLocalVariableInspection */
						$fld_type = $column_definitions[$i]->type;
						list($module, $fieldLabel) = explode('_', $fld->name, 2);
						$fieldInfo = getFieldByReportLabel($module, $fieldLabel);
						$fieldType = null;
						if(!empty($fieldInfo)) {
							$field = WebserviceField::fromArray($adb, $fieldInfo);
							/** @noinspection PhpUnusedLocalVariableInspection */
							$fieldType = $field->getFieldDataType();
						}
						$translatedLabel = isset($filteredHeaders1[$fld->name]) ? $filteredHeaders1[$fld->name] : $fieldInfo->fieldlabel;
						// STRING TRANSLATION starts

						$moduleLabel ='';
						if(in_array($module,$modules_selected)) {
							$moduleLabel = getTranslatedString($module,$module);
						}
						$headerLabel = $translatedLabel;
						if(!empty($this->secondarymodule)) {
							if($moduleLabel != '') {
								$headerLabel = $translatedLabel;
							}
						}
						// Check for role based pick list
						/** @noinspection PhpUnusedLocalVariableInspection */
						$temp_val= $fld->name;
						/** @noinspection PhpUndefinedVariableInspection */
						$fieldvalue = getReportFieldValue(
							$this,
							$picklistarray,
							$fld,
							$custom_field_values,
							$i
						);
						if ($fld_type == "real" || $fld_type == "int" ){ 
							$fieldvalue1 = $numberingHelper->setNumberFormat ($fieldvalue, $fld->name);
							$arraylists[$headerLabel] = $fieldvalue1;
						} else {	
						$arraylists[$headerLabel] = $fieldvalue;
					}
					}
					$arr_val[] = $arraylists;
					set_time_limit($php_max_execution_time);
				} while($custom_field_values = $adb->fetch_array($result));

				/** @noinspection PhpIncompatibleReturnTypeInspection */
				return $arr_val;
			}
		} 
		else if($outputformat == 'TOTALXLS') {
			$escapedchars = array('_SUM','_AVG','_MIN','_MAX');
			$totalpdf=array();
			/** @noinspection PhpUndefinedFieldInspection */
			$sSQL = $this->sGetSQLforReport($this->reportid,$filtersql,'COLUMNSTOTOTAL');
			if(isset($this->totallist)) {
				if($sSQL != '') {
					$result = $adb->query($sSQL);
					$y=$adb->num_fields($result);
					$custom_field_values = $adb->fetch_array($result);

					foreach($this->totallist as $key => $value)
					{
						$fieldlist = explode(':',$key);
						$mod_query = $adb->pquery('SELECT distinct(tabid) as tabid, uitype as uitype from vtiger_field where tablename = ? and columnname=?',array($fieldlist[1], $fieldlist[2]));
						if($adb->num_rows($mod_query)>0) {
							$module_name = getTabName($adb->query_result($mod_query,0,'tabid'));
							$fieldlabel = trim(str_replace($escapedchars,' ',$fieldlist[3]));
							$fieldlabel = str_replace('_', ' ', $fieldlabel);
							if($module_name) {
								$field = getTranslatedString($module_name,$module_name).' '.getTranslatedString($fieldlabel,$module_name);
							} else {
								$field = getTranslatedString($fieldlabel);
							}
						}
						/** @noinspection PhpUndefinedVariableInspection */
						$uitype_arr[str_replace($escapedchars,'',$module_name.'_'.$fieldlist[3])] = $adb->query_result($mod_query,0,'uitype');
						/** @noinspection PhpUndefinedVariableInspection */
						$totclmnflds[str_replace($escapedchars,'',$module_name.'_'.$fieldlist[3])] = $field;
					}
					for($i =0; $i<$y; $i++) {
						$fld = $adb->field_name($result, $i);
						$keyhdr[$fld->name] = $custom_field_values[$i];
					}

					$rowcount=0;
					/** @noinspection PhpUndefinedVariableInspection */
					foreach($totclmnflds as $key => $value) {
						$col_header = trim(str_replace($modules,' ',$value));
						$fld_name_1 = $this->primarymodule . '_' . trim($value);
						$fld_name_2 = $this->secondarymodule . '_' . trim($value);
						/** @noinspection PhpUndefinedVariableInspection */
						if($uitype_arr[$key] == 7 || $uitype_arr[$key] == 71 || $uitype_arr[$key] == 72 || in_array($fld_name_1,$this->append_currency_symbol_to_value) || in_array($fld_name_2,$this->append_currency_symbol_to_value)) {
							/** @noinspection PhpUnusedLocalVariableInspection */
							$col_header .= ' ('.$app_strings['LBL_IN'].' '.$current_user->currency_symbol.')';
							$convert_price = true;
						} else{
							$convert_price = false;
						}
						$value = trim($key);
						$arraykey = $value.'_SUM';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else if($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey]);
							} else {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey], null, true);
							}
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						} else {
							$totalpdf[$rowcount][$arraykey] = '';
						}

						$arraykey = $value.'_AVG';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else if($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey]);
							} else {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey], null, true);
							}
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						} else {
							$totalpdf[$rowcount][$arraykey] = '';
						}

						$arraykey = $value.'_MIN';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else if($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey]);
							} else {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey], null, true);
							}
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						} else {
							$totalpdf[$rowcount][$arraykey] = '';
						}

						$arraykey = $value.'_MAX';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else if($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey]);
							} else {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey], null, true);
							}
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						} else {
							$totalpdf[$rowcount][$arraykey] = '';
						}
						$rowcount++;
					}
				}
			}
			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return $totalpdf;
		} 
		else if($outputformat == 'TOTALHTML') {
			$escapedchars = array('_SUM','_AVG','_MIN','_MAX');
			/** @noinspection PhpUndefinedFieldInspection */
			$sSQL = $this->sGetSQLforReport($this->reportid,$filtersql,'COLUMNSTOTOTAL');

			if(isset($this->totallist)) {
				if($sSQL != '') {
					$result = $adb->query($sSQL);
					$y=$adb->num_fields($result);
					$custom_field_values = $adb->fetch_array($result);
					/** @noinspection PhpUndefinedConstantInspection */
					/** @noinspection PhpUndefinedVariableInspection */
					$coltotalhtml .= "<table align='center' width='100%' cellpadding='3' cellspacing='0' border='0' class='table'><tr><td width='28%'>".$mod_strings[Totals]."</td><td width='18%'>".$mod_strings[SUM]."</td><td width='18%'>".$mod_strings[AVG]."</td><td width='18%'>".$mod_strings[MIN]."</td><td width='18%'>".$mod_strings[MAX]."</td></tr>";

					// Performation Optimization: If Direct output is desired
					if($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END

					foreach($this->totallist as $key => $value) {
						$fieldlist = explode(':',$key);
						$mod_query = $adb->pquery('SELECT distinct(tabid) as tabid, uitype as uitype from vtiger_field where tablename = ? and columnname=?',array($fieldlist[1], $fieldlist[2]));
						if($adb->num_rows($mod_query)>0) {
							$module_name = getTabName($adb->query_result($mod_query,0,'tabid'));
							$fieldlabel = trim(str_replace($escapedchars,' ',$fieldlist[3]));
							$fieldlabel = str_replace('_', ' ', $fieldlabel);
							if($module_name) {
								$field = getTranslatedString($module_name, $module_name).' '.getTranslatedString($fieldlabel,$module_name);
							} else {
								$field = getTranslatedString($fieldlabel);
							}
						}
						/** @noinspection PhpUndefinedVariableInspection */
						$uitype_arr[str_replace($escapedchars,'',$module_name.'_'.$fieldlist[3])] = $adb->query_result($mod_query,0,'uitype');
						/** @noinspection PhpUndefinedVariableInspection */
						$totclmnflds[str_replace($escapedchars,'',$module_name.'_'.$fieldlist[3])] = $field;
					}
					for($i =0; $i<$y; $i++) {
						$fld = $adb->field_name($result, $i);
						$keyhdr[$fld->name] = $custom_field_values[$i];
					}

					/** @noinspection PhpUndefinedVariableInspection */
					foreach($totclmnflds as $key => $value) {
						$coltotalhtml .= '<tr class="rptGrpHead" valign=top>';
						$col_header = trim(str_replace($modules,' ',$value));
						$fld_name_1 = $this->primarymodule . '_' . trim($value);
						$fld_name_2 = $this->secondarymodule . '_' . trim($value);
						/** @noinspection PhpUndefinedVariableInspection */
						if($uitype_arr[$key]==7 || $uitype_arr[$key]==71 || $uitype_arr[$key] == 72 || in_array($fld_name_1,$this->append_currency_symbol_to_value) || in_array($fld_name_2,$this->append_currency_symbol_to_value)) {
							$col_header .= ' ('.$app_strings['LBL_IN'].' '.$current_user->currency_symbol.')';
							$convert_price = true;
						} else{
							$convert_price = false;
						}
						$coltotalhtml .= '<td width="28%" class="rptData">'. $col_header .'</td>';
						$value = trim($key);
						$arraykey = $value.'_SUM';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else if($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey]);
							} else {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey], null, true);
							}
							$coltotalhtml .= '<td width="18%" class="rptTotal">'.$conv_value.'</td>';
						} else {
							$coltotalhtml .= '<td width="18%" class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $value.'_AVG';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else if($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey]);
							} else {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey], null, true);
							}
							$coltotalhtml .= '<td width="18%" class="rptTotal">'.$conv_value.'</td>';
						} else {
							$coltotalhtml .= '<td width="18%" class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $value.'_MIN';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else if($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey]);
							} else {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey], null, true);
							}
							$coltotalhtml .= '<td width="18%" class="rptTotal">'.$conv_value.'</td>';
						} else {
							$coltotalhtml .= '<td width="18%" class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $value.'_MAX';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else if($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey]);
							} else {
								$conv_value = CurrencyField::convertToUserFormat ($keyhdr[$arraykey], null, true);
							}
							$coltotalhtml .= '<td width="18%" class="rptTotal">'.$conv_value.'</td>';
						} else {
							$coltotalhtml .= '<td width="18%" class="rptTotal">&nbsp;</td>';
						}

						$coltotalhtml .= '<tr>';

						// Performation Optimization: If Direct output is desired
						if($directOutput) {
							echo $coltotalhtml;
							$coltotalhtml = '';
						}
						// END
					}

					$coltotalhtml .= '</table>';

					// Performation Optimization: If Direct output is desired
					if($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END
				}
			}
			/** @noinspection PhpUndefinedVariableInspection */
			return $coltotalhtml;
		} 
		else if($outputformat == 'PDF' || $outputformat == 'PRINT') {
			/** @noinspection PhpUndefinedFieldInspection */
			$sSQL = $this->sGetSQLforReport($this->reportid,$filtersql,$outputformat);
			
			
			$result = $adb->query($sSQL);
			/** @noinspection PhpUndefinedVariableInspection */
			if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
				$picklistarray = $this->getAccessPickListValues();
			}

			if($result) {
				$y=$adb->num_fields($result);
				$arrayHeaders = array();
				for ($x=0; $x<$y; $x++) {
					$fld = $adb->field_name($result, $x);
					if(in_array($this->getLstringforReportHeaders($fld->name), $arrayHeaders)) {
						$headerLabel = str_replace('_',' ',$fld->name);
						$arrayHeaders[] = $headerLabel;
					} else {
						$headerLabel = str_replace($modules,' ',$this->getLstringforReportHeaders($fld->name));
						$arrayHeaders[] = $headerLabel;
					}
					// STRING TRANSLATION starts

					$mod_name = explode(' ',$headerLabel,2);
					$moduleLabel ='';
					if(in_array($mod_name[0],$modules_selected)) {
						$moduleLabel = getTranslatedString($mod_name[0],$mod_name[0]);
					}

					if(!empty($this->secondarymodule)) {
						if($moduleLabel!='') {
							$headerLabel_tmp = $moduleLabel.' '.getTranslatedString($mod_name[1],$mod_name[0]);
						} else {
							$headerLabel_tmp = getTranslatedString($mod_name[0].' '.$mod_name[1]);
						}
					} else {
						if($moduleLabel!='') {
							$headerLabel_tmp = getTranslatedString($mod_name[1],$mod_name[0]);
						} else {
							$headerLabel_tmp = getTranslatedString($mod_name[0].' '.$mod_name[1]);
						}
					}
					if($headerLabel == $headerLabel_tmp) {
						$headerLabel = getTranslatedString($headerLabel_tmp);
					} else {
						$headerLabel = $headerLabel_tmp;
					}
					// STRING TRANSLATION ends

					/** @noinspection PhpUndefinedVariableInspection */
					$header .= '<th style="font_size:1.05em;font-weight:400; text-align:center">'.$headerLabel.'</th>';
				}
				$noofrows = $adb->num_rows($result);
				$custom_field_values = $adb->fetch_array($result);
				/** @noinspection PhpUndefinedFieldInspection */
				$groupslist = $this->getGroupingList($this->reportid);

				$column_definitions = $adb->getFieldsDefinition($result);

				do {
					$arraylists = array();
					if(count($groupslist) == 1) {
						$newvalue = $custom_field_values[0];
					} else if(count($groupslist) == 2) {
						$newvalue = $custom_field_values[0];
						$snewvalue = $custom_field_values[1];
					} else if(count($groupslist) == 3) {
						$newvalue = $custom_field_values[0];
						$snewvalue = $custom_field_values[1];
						$tnewvalue = $custom_field_values[2];
					}

					/** @noinspection PhpUndefinedVariableInspection */
					if($newvalue == '') {
						$newvalue = '-';
					}

					/** @noinspection PhpUndefinedVariableInspection */
					if($snewvalue == '') {
						$snewvalue = '-';
					}

					/** @noinspection PhpUndefinedVariableInspection */
					if($tnewvalue == '') {
						$tnewvalue = '-';
					}

					/** @noinspection PhpUndefinedVariableInspection */
					$valtemplate .= '<tr>';

					for ($i=0; $i<$y; $i++) {
						$fld = $adb->field_name($result, $i);
						/** @noinspection PhpUnusedLocalVariableInspection */
						$fld_type = $column_definitions[$i]->type;
						/** @noinspection PhpUndefinedVariableInspection */
						$fieldvalue = getReportFieldValue(
							$this,
							$picklistarray,
							$fld,
							$custom_field_values,
							$i
						);
						// --- Formateo numérico consistente para PDF ---
						// Detectar si el campo es numérico (uitype 7, 9, 71, 72, tipo decimal/float/int)
						$uitype = isset($fld->uitype) ? $fld->uitype : null;
						$is_numeric = false;
						if (in_array($uitype, array(7, 9, 71, 72)) || in_array(strtolower($fld_type), array('decimal','float','double','real','int','numeric','currency'))) {
							$is_numeric = true;
						}
						if (($outputformat == 'PDF') && $is_numeric && is_numeric($fieldvalue)) {
							// Formatear usando NumberHelper según configuración del usuario SOLO para PDF
							$fieldvalue = $numberingHelper->setNumberFormat($fieldvalue, isset($fld->name) ? $fld->name : null);
						}
						if(($lastvalue == $fieldvalue) && $this->reporttype == 'summary') {
							if($this->reporttype == 'summary') {
								$valtemplate .= "<td style='border-top:1px dotted #FFFFFF;'>&nbsp;</td>";
							} else {
								$valtemplate .= '<td>'.$fieldvalue.'</td>';
							}
						} else if(($secondvalue == $fieldvalue) && $this->reporttype == 'summary') {
							if($lastvalue == $newvalue) {
								$valtemplate .= "<td style='border-top:1px dotted #FFFFFF;'>&nbsp;</td>";
							} else {
								$valtemplate .= '<td>'.$fieldvalue.'</td>';
							}
						} else if(($thirdvalue == $fieldvalue) && $this->reporttype == 'summary') {
							if($secondvalue == $snewvalue) {
								$valtemplate .= "<td style='border-top:1px dotted #FFFFFF;'>&nbsp;</td>";
							} else {
								$valtemplate .= '<td>'.$fieldvalue.'</td>';
							}
						} else {
							if($this->reporttype == 'tabular') {
								$valtemplate .= '<td>'.$fieldvalue.'</td>';
							} else {
								$valtemplate .= '<td>'.$fieldvalue.'</td>';
							}
						}
					}
					$valtemplate .= '</tr>';
					$lastvalue = $newvalue;
					$secondvalue = $snewvalue;
					$thirdvalue = $tnewvalue;
					$arr_val[] = $arraylists;
					set_time_limit($php_max_execution_time);
				} while($custom_field_values = $adb->fetch_array($result));

				/** @noinspection PhpUndefinedVariableInspection */
				$sHTML = '<tr>'.$header.'</tr>'.$valtemplate;
				$return_data[] = $sHTML;
				$return_data[] = $noofrows;
				/** @noinspection PhpIncompatibleReturnTypeInspection */
				return $return_data;
			}
		} 
		else if($outputformat == 'PRINT_TOTAL') {
			$escapedchars = array('_SUM','_AVG','_MIN','_MAX');
			/** @noinspection PhpUndefinedFieldInspection */
			$sSQL = $this->sGetSQLforReport($this->reportid,$filtersql,'COLUMNSTOTOTAL');
			if(isset($this->totallist)) {
				if($sSQL != '') {
					$result = $adb->query($sSQL);
					$y=$adb->num_fields($result);
					$custom_field_values = $adb->fetch_array($result);

					/** @noinspection PhpUndefinedVariableInspection */

					$reportwidth27= round($reportwidth * 0.27, 0);
					$reportwidth17= round($reportwidth * 0.17, 0);
					$coltotalhtml .= "<br /><table align='center' cellpadding='3' cellspacing='0' border='1' class='printReport' width='". ($reportwidth*0.98). "'>
					<tr><td width='". $reportwidth27 ."'>".$mod_strings['Totals']."</td>
					<td width='". $reportwidth17 ."'><b>".$mod_strings['SUM']."</b></td>
					<td width='". $reportwidth17 ."'><b>".$mod_strings['AVG']."</b></td>
					<td width='". $reportwidth17 ."'><b>".$mod_strings['MIN']."</b></td>
					<td width='". $reportwidth17 ."'><b>".$mod_strings['MAX'].'</b></td></tr>';
					// Performation Optimization: If Direct output is desired
					if($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END

					foreach($this->totallist as $key => $value) {
						$fieldlist = explode(':',$key);
						$mod_query = $adb->pquery('SELECT distinct(tabid) as tabid, uitype as uitype from vtiger_field where tablename = ? and columnname=?',array($fieldlist[1], $fieldlist[2]));
						if($adb->num_rows($mod_query)>0) {
							$module_name = getTabName($adb->query_result($mod_query,0,'tabid'));
							$fieldlabel = trim(str_replace($escapedchars,' ',$fieldlist[3]));
							$fieldlabel = str_replace('_', ' ', $fieldlabel);
							if($module_name) {
								$field = getTranslatedString($module_name, $module_name).' '.getTranslatedString($fieldlabel,$module_name);
							} else {
								$field = getTranslatedString($fieldlabel);
							}
						}
						/** @noinspection PhpUndefinedVariableInspection */
						$uitype_arr[str_replace($escapedchars,'',$module_name.'_'.$fieldlist[3])] = $adb->query_result($mod_query,0,'uitype');
						/** @noinspection PhpUndefinedVariableInspection */
						$totclmnflds[str_replace($escapedchars,'',$module_name.'_'.$fieldlist[3])] = $field;
					}

					for($i =0; $i<$y; $i++) {
						$fld = $adb->field_name($result, $i);
						$keyhdr[$fld->name] = $custom_field_values[$i];
					}
					/** @noinspection PhpUndefinedVariableInspection */
					foreach($totclmnflds as $key => $value) {
						$coltotalhtml .= '<tr class="rptGrpHead">';
						$col_header = getTranslatedString(trim(str_replace($modules,' ',$value)));
						$fld_name_1 = $this->primarymodule . '_' . trim($value);
						$fld_name_2 = $this->secondarymodule . '_' . trim($value);
						/** @noinspection PhpUndefinedVariableInspection */
						if($uitype_arr[$key]==7 || $uitype_arr[$key]==71 || $uitype_arr[$key] == 72 || in_array($fld_name_1,$this->append_currency_symbol_to_value) || in_array($fld_name_2,$this->append_currency_symbol_to_value)) {
							$col_header .= ' ('.$app_strings['LBL_IN'].' '.$current_user->currency_symbol.')';
							$convert_price = true;
						} else {
							$convert_price = false;
						}
						$coltotalhtml .= '<td width="'. $reportwidth27 .'" class="rptData" style="width:'. $reportwidth27. 'px">'. $col_header .'</td>';
						$value = trim($key);
						$arraykey = trim($value).'_SUM';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else {
								$conv_value = $numberingHelper->setNumberFormat ($keyhdr[$arraykey], "TOTAL_SUM");								
							}
							$coltotalhtml .= "<td class='rptTotal' width='". $reportwidth17 ."' style='width:". $reportwidth17 ."'>".$conv_value.'</td>';
						} else {
							$coltotalhtml .= "<td class='rptTotal' width='". $reportwidth17 ."' style='width:". $reportwidth17 ."'>&nbsp;</td>";
						}

						$arraykey = trim($value).'_AVG';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else {
								$conv_value = $numberingHelper->setNumberFormat ($keyhdr[$arraykey], "TOTAL_AVG");
							}
							$coltotalhtml .= "<td class='rptTotal' width='". $reportwidth17 ."' style='width:". $reportwidth17 ."px'>".$conv_value.'</td>';
						} else {
							$coltotalhtml .= "<td class='rptTotal' width='". $reportwidth17 ."' style='width:". $reportwidth17 ."px'>&nbsp;</td>";
						}

						$arraykey = trim($value).'_MIN';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else {
								$conv_value = $numberingHelper->setNumberFormat ($keyhdr[$arraykey], "TOTAL_MIN");
							}
							$coltotalhtml .= "<td class='rptTotal' width='". $reportwidth17 ."' style='width:". $reportwidth17 ."px'>".$conv_value.'</td>';
						} else {
							$coltotalhtml .= "<td class='rptTotal' width='". $reportwidth17 ."' style='width:". $reportwidth17 ."px'>&nbsp;</td>";
						}

						$arraykey = trim($value).'_MAX';
						if(isset($keyhdr[$arraykey])) {
							if ($_REQUEST['action'] == 'CreateXL') {
								$conv_value = round($keyhdr[$arraykey],2);
							} else {
								$conv_value = $numberingHelper->setNumberFormat ($keyhdr[$arraykey], "TOTAL_MAX");
							}
							$coltotalhtml .= "<td class='rptTotal' width='". $reportwidth17 ."' style='width:". $reportwidth17 ."px'>".$conv_value.'</td>';
						} else {
							$coltotalhtml .= "<td class='rptTotal' width='". $reportwidth17 ."' style='width:". $reportwidth17 ."px'>&nbsp;</td>";
						}

						$coltotalhtml .= '</tr>';

						// Performation Optimization: If Direct output is desired
						if($directOutput) {
							echo $coltotalhtml;
							$coltotalhtml = '';
						}
						// END
					}

					$coltotalhtml .= '</table>';
					// Performation Optimization: If Direct output is desired
					if($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END
				}
			}
			/** @noinspection PhpUndefinedVariableInspection */
			$current_user->numbering_format = $theNumberFormat;
			return $coltotalhtml;
		}
	}

	// @codingStandardsIgnoreStart
	/**
	 * New

	 * @param $reportid

	 * @return boolean

	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function getColumnsTotal($reportid) {
		// @codingStandardsIgnoreEnd
		// Have we initialized it already?
		if($this->_columnstotallist !== false) {
			return $this->_columnstotallist;
		}

		global $adb;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$modules;
		global $log;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$current_user;

		$query = 'select * from vtiger_reportmodules where reportmodulesid =?';
		$res = $adb->pquery($query, array($reportid));
		$modrow = $adb->fetch_array($res);
		$premod = $modrow['primarymodule'];
		$secmod = $modrow['secondarymodules'];
		$coltotalsql = 'select vtiger_reportsummary.* from vtiger_report';
		$coltotalsql .= ' inner join vtiger_reportsummary on vtiger_report.reportid = vtiger_reportsummary.reportsummaryid';
		$coltotalsql .= ' where vtiger_report.reportid =?';

		$result = $adb->pquery($coltotalsql, array($reportid));

		while($coltotalrow = $adb->fetch_array($result)) {
			$fieldcolname = $coltotalrow['columnname'];
			if($fieldcolname != 'none') {
				$fieldlist = explode(':',$fieldcolname);
				$field_tablename = $fieldlist[1];
				$field_columnname = $fieldlist[2];

				$mod_query = $adb->pquery('SELECT distinct(tabid) as tabid from vtiger_field where tablename = ? and columnname=?',array($fieldlist[1], $fieldlist[2]));
				if($adb->num_rows($mod_query)>0) {
					$module_name = getTabName($adb->query_result($mod_query,0,'tabid'));
					/** @noinspection PhpUnusedLocalVariableInspection */
					$fieldlabel = trim($fieldlist[3]);
					if($module_name) {
						$field_columnalias = $module_name.'_'.$fieldlist[3];
					} else {
						$field_columnalias = $module_name.'_'.$fieldlist[3];
					}
				}
				$field_permitted = false;
				if(CheckColumnPermission($field_tablename,$field_columnname,$premod) != 'false') {
					$field_permitted = true;
				} else {
					$mod = explode(':',$secmod);
					foreach($mod as $key){
						if(CheckColumnPermission($field_tablename,$field_columnname,$key) != 'false') {
							$field_permitted=true;
						}
					}
				}
				if($field_permitted == true) {
					$field = $field_tablename.'.'.$field_columnname;
					if(($field_tablename == 'vtiger_invoice' || $field_tablename == 'vtiger_quotes' || $field_tablename == 'vtiger_purchaseorder' || $field_tablename == 'vtiger_salesorder') && ($field_columnname == 'total' || $field_columnname == 'subtotal' || $field_columnname == 'discount_amount' || $field_columnname == 's_h_amount')) {
						$field = " $field_tablename.$field_columnname/$field_tablename.conversion_rate ";
					}
					if($fieldlist[4] == 2) {
						/** @noinspection PhpUndefinedVariableInspection */
						$stdfilterlist[$fieldcolname] = "SUM($field) '".$field_columnalias."'";
					}
					if($fieldlist[4] == 3) {
						/** @noinspection PhpUndefinedVariableInspection */
						$stdfilterlist[$fieldcolname] = "AVG($field) '".$field_columnalias."'";
					}
					if($fieldlist[4] == 4) {
						/** @noinspection PhpUndefinedVariableInspection */
						$stdfilterlist[$fieldcolname] = "MIN($field) '".$field_columnalias."'";
					}
					if($fieldlist[4] == 5) {
						/** @noinspection PhpUndefinedVariableInspection */
						$stdfilterlist[$fieldcolname] = "MAX($field) '".$field_columnalias."'";
					}
				}
			}
		}
		// Save the information
		/** @noinspection PhpUndefinedVariableInspection */
		$this->_columnstotallist = $stdfilterlist;

		$log->info('ReportRun :: Successfully returned getColumnsTotal'.$reportid);
		return $stdfilterlist;
	}

	/**
	 * Function to get query for the columns to total for the given reportid
	 *  @ param $reportid : Type integer
	 *  This returns columnstoTotal query for the reportid

	 * @param $reportid

	 * @return string
	 */
	public function getColumnsToTotalColumns($reportid) {
		global $adb;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$modules;
		global $log;
		$sSQL = '';

		$sreportstdfiltersql = 'SELECT vtiger_reportsummary.* FROM vtiger_report';
		$sreportstdfiltersql .= ' INNER JOIN vtiger_reportsummary ON vtiger_report.reportid = vtiger_reportsummary.reportsummaryid';
		$sreportstdfiltersql .= ' WHERE vtiger_report.reportid =?';

		$result = $adb->pquery($sreportstdfiltersql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for($i=0; $i<$noofrows; $i++) {
			$fieldcolname = $adb->query_result($result,$i,'columnname');

			if($fieldcolname != 'none') {
				$fieldlist = explode(':',$fieldcolname);
				if($fieldlist[4] == 2) {
					$sSQLList[] = 'sum('.$fieldlist[1].'.'.$fieldlist[2].') '.$fieldlist[3].'_SUM';
				}
				if($fieldlist[4] == 3) {
					$sSQLList[] = 'avg('.$fieldlist[1].'.'.$fieldlist[2].') '.$fieldlist[3].'_AVG';
				}
				if($fieldlist[4] == 4) {
					$sSQLList[] = 'min('.$fieldlist[1].'.'.$fieldlist[2].') '.$fieldlist[3].'_MIN';
				}
				if($fieldlist[4] == 5) {
					$sSQLList[] = 'max('.$fieldlist[1].'.'.$fieldlist[2].') '.$fieldlist[3].'_MAX';
				}
			}
		}
		if(isset($sSQLList)) {
			$sSQL = implode(',',$sSQLList);
		}
		$log->info('ReportRun :: Successfully returned getColumnsToTotalColumns'.$reportid);
		return $sSQL;
	}

	/**
	 * Function to convert the Report Header Names into i18n

	 * @param $fldname: Type Varchar
	 * @ FIED $field
	 *
	 * @return string
	 **/
	public function getLstringforReportHeaders($fldname, $field = null) {
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$modules;
		global /** @noinspection PhpUnusedLocalVariableInspection */
		$current_language;
		global $current_user;
		global $app_strings;
		if (!empty ($field)) {
			if (!empty($field->table) && ($field->table != 'LBL_ACTION')) {
				$theTable = explode('_', $field->table);
				array_shift($theTable);
				$rep_header = ltrim($fldname);
				$rep_header = decode_html($rep_header);
				$labelInfo = explode('_', $rep_header);
				$fieldLabel = decode_html(implode(' ', array_diff($labelInfo, $theTable)));
				return $fieldLabel;
			}
		}
		$rep_header = ltrim($fldname);
		$rep_header = decode_html($rep_header);
		$labelInfo = explode('_', $rep_header);
		$rep_module = $labelInfo[0];
		/** @noinspection PhpUndefinedFieldInspection */
		if(is_array($this->labelMapping) && !empty($this->labelMapping[$rep_header])) {
			/** @noinspection PhpUndefinedFieldInspection */
			$rep_header = $this->labelMapping[$rep_header];
		} else {
			if($rep_module == 'LBL') {
				$rep_module = '';
			}
			array_shift($labelInfo);
			$fieldLabel = decode_html(implode('_',$labelInfo));
			/** @noinspection PhpUnusedLocalVariableInspection */
			$rep_header_temp = preg_replace('/\s+/','_',$fieldLabel);
			$rep_header = "$rep_module $fieldLabel";
		}
		$curr_symb = '';
		$fieldLabel = ltrim(str_replace($rep_module, '', $rep_header), '_');
		$fieldInfo = getFieldByReportLabel($rep_module, $fieldLabel);

		if($fieldInfo['uitype'] == '71') {
			$curr_symb = ' ('.$app_strings['LBL_IN'].' '.$current_user->currency_symbol.')';
		}
		$rep_header .=$curr_symb;

		return $rep_header;
	}

	// @codingStandardsIgnoreStart
	/**
	 * Function to get picklist value array based on profile
	 * returns permitted fields in array format

	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function getAccessPickListValues() {
		// @codingStandardsIgnoreEnd
		global $adb;
		global $current_user;
		$id = array(getTabid($this->primarymodule));
		if($this->secondarymodule != '') {
			array_push($id, getTabid($this->secondarymodule));
		}
		$query = 'select fieldname,columnname,fieldid,fieldlabel,tabid,uitype from vtiger_field where tabid in('. generateQuestionMarks($id) .') and uitype in (15,33,55)'; //and columnname in (?)';
		$result = $adb->pquery($query, $id);
		$roleid=$current_user->roleid;
		$subrole = getRoleSubordinates($roleid);
		/** @noinspection PhpParamsInspection */
		if(count($subrole)> 0) {
			$roleids = $subrole;
			/** @noinspection PhpParamsInspection */
			array_push($roleids, $roleid);
		} else {
			$roleids = $roleid;
		}

		$temp_status = array();
		$numRowsResult = $adb->num_rows($result);
		for($i=0; $i < $numRowsResult; $i++) {
			$fieldname = $adb->query_result($result,$i,'fieldname');
			$fieldlabel = $adb->query_result($result,$i,'fieldlabel');
			$tabid = $adb->query_result($result,$i,'tabid');
			$uitype = $adb->query_result($result,$i,'uitype');

			// @codingStandardsIgnoreStart
			$fieldlabel1 = str_replace(' ','_',$fieldlabel);
			$keyvalue = getTabModuleName($tabid).'_'.$fieldlabel1;
			// @codingStandardsIgnoreEnd
			$fieldvalues = array();
			/** @noinspection PhpParamsInspection */
			if (count($roleids) > 1) {
				/** @noinspection SqlResolve */
				/** @noinspection PhpParamsInspection */
				$mulsel="select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid in (\"". implode($roleids,'","') ."\") and picklistid in (select picklistid from vtiger_$fieldname) order by sortid asc";
			} else {
				$mulsel="select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid ='".$roleid."' and picklistid in (select picklistid from vtiger_$fieldname) order by sortid asc";
			}
			if($fieldname != 'firstname') {
				$mulselresult = $adb->query($mulsel);
			}
			$numRowsMulSelResult = $adb->num_rows($mulselresult);
			for($j=0; $j < $numRowsMulSelResult; $j++) {
				$fldvalue = $adb->query_result($mulselresult,$j,$fieldname);
				if(in_array($fldvalue,$fieldvalues)) {
					continue;
				}
				$fieldvalues[] = $fldvalue;
			}
			$field_count = count($fieldvalues);
			if( $uitype == 15 && $field_count > 0 && ($fieldname == 'taskstatus' || $fieldname == 'eventstatus')) {
				$temp_count =count($temp_status[$keyvalue]);
				if($temp_count > 0) {
					for($t=0; $t < $field_count; $t++) {
						$temp_status[$keyvalue][($temp_count+$t)] = $fieldvalues[$t];
					}
					$fieldvalues = $temp_status[$keyvalue];
				} else {
					$temp_status[$keyvalue] = $fieldvalues;
				}
			}

			if($uitype == 33) {
				$fieldlists[1][$keyvalue] = $fieldvalues;
			} else if($uitype == 55 && $fieldname == 'salutationtype') {
				$fieldlists[$keyvalue] = $fieldvalues;
			} else if($uitype == 15) {
				$fieldlists[$keyvalue] = $fieldvalues;
			}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		return $fieldlists;
	}

		public function Header() {
			$this->SetFont(PDF_FONT_NAME_MAIN, '', 12);
			$this->Cell(0, 7, $this->orgName, 0, 0, 'L', 0, '', 0, false, 'T', 'M');
			$this->SetTextColor(0,0,0); // Negro
			$this->SetFont(PDF_FONT_NAME_MAIN, '', 10);
			$this->Cell(0, 7, $this->fechaHora, 0, 1, 'R', 0, '', 0, false, 'T', 'M');
			$this->SetFont(PDF_FONT_NAME_MAIN, 'B', 13);
			$this->Cell(0, 7, $this->reportTitle, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
			if (!empty($this->reportDesc)) {
				$this->SetFont(PDF_FONT_NAME_MAIN, '', 10);
				$this->Cell(0, 7, $this->reportDesc, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
			}
			$this->Ln(2);
			$this->Line($this->GetX(), $this->GetY(), $this->getPageWidth()-$this->GetX(), $this->GetY());
			$this->Ln(2);
		}
	// @codingStandardsIgnoreStart
	/**
	 * getReportPDF

	 * @param $filterlist

	 * @return object

	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function getReportPDF($filterlist = false) {
		// @codingStandardsIgnoreEnd

		$arr_val = $this->GenerateReport('PDF',$filterlist, false, null);

        // --- Instanciación de NumberHelper para formateo numérico consistente ---
        require_once('include/utils/NumberHelper.class.php');
        global $adb, $current_user;
        $numberingHelper = NumberHelper::getInstance($adb, $current_user);
        // --- Fin instanciación ---
		if(isset($arr_val)) {
			foreach($arr_val as $wkey => $warray_value) {
				foreach($warray_value as $whd => $wvalue) {
					if(strlen($wvalue) < strlen($whd)) {
						$w_inner_array[] = strlen($whd);
					} else {
						$w_inner_array[] = strlen($wvalue);
					}
				}
				/** @noinspection PhpUndefinedVariableInspection */
				$warr_val[] = $w_inner_array;
				unset($w_inner_array);
			}

			/** @noinspection PhpUndefinedVariableInspection */
			foreach($warr_val[0] as $fkey => $fvalue) {
				foreach($warr_val as $wkey => $wvalue) {
					$f_inner_array[] = $warr_val[$wkey][$fkey];
				}
				sort($f_inner_array,1);
				$farr_val[] = $f_inner_array;
				unset($f_inner_array);
			}

			/** @noinspection PhpUndefinedVariableInspection */
			foreach($farr_val as $skkey => $skvalue) {
				/** @noinspection PhpParamsInspection */
				$colName = array_keys($arr_val[0])[$skkey];
				
				$type = isset($header_to_type[$colName]) ? $header_to_type[$colName] : '';
				$maxLen = $skvalue[(count($arr_val)-1)];
				// Detectar si es numérico aunque no tenga tipo explícito
				//$isNumeric = in_array(strtolower($type), ['real','decimal','float','double','int','N', 'NN']);
				if($maxLen == 1) {
					$width = ($maxLen * 1.25);
				} else {
					$width = (($maxLen * 1.25) + 10);
				}
				
				// Aplicar ancho mínimo general de 35
				if ($width < 35) {
					$width = 35;
				}
				// Limitar ancho máximo a 200
				if ($width > 200) {
					$width = 200;
			}
				
				$col_width[] = $width;
			}
			// --- Transformar anchos  ---
			$total_width = array_sum($col_width);
			
			// IMPORTANTE: Agregar 30px para la columna de numeración (#) que se agregará después
			// Esto asegura que el cálculo de tamaño de página considere esta columna adicional
			$total_width = $total_width + 30;
			
			if ($total_width <=  494) 
				{$total_width_new = 551; $page_orientation = 'P'; $page_size='A4';} //A4
			
			else if ($total_width > 494 && $total_width <= 533) 
				{$total_width_new = 551; $page_orientation = 'P'; $page_size='A4';} //A4 			
			else if ($total_width > 533 && $total_width <= 698) 
				{$total_width_new = 780; $page_orientation = 'L'; $page_size='A4';} //A4 
			
			else if ($total_width > 698 && $total_width <= 754) 
				{$total_width_new = 780; $page_orientation = 'P'; $page_size='A3';} //A3 			
			else if ($total_width > 754 && $total_width <= 987) 
				{$total_width_new = 1102; $page_orientation = 'L'; $page_size='A3';} //A3 
			
			else if ($total_width > 987 && $total_width <= 1067) 
				{$total_width_new = 1102; $page_orientation = 'P'; $page_size='A2';} //A2
			else if ($total_width > 1067 && $total_width <= 1396) 
				{$total_width_new = 1559; $page_orientation = 'L'; $page_size='A2';} //A2
			
			else if ($total_width > 1396 && $total_width <= 1509) 
				{$total_width_new = 1559; $page_orientation = 'P'; $page_size='A1';} //A1
			else if ($total_width > 1509 && $total_width <= 1974) 
				{$total_width_new = 2205; $page_orientation = 'L'; $page_size='A1';} //A1
			
			else if ($total_width > 1974  && $total_width <= 2136) 
				{$total_width_new = 2205; $page_orientation = 'P'; $page_size='A0';} //A0
			else if ($total_width > 2136  && $total_width <= 2794) 
				{$total_width_new = 3121; $page_orientation = 'L'; $page_size='A0';} //A0
			
			else if ($total_width > 2794  && $total_width <= 3020)
				{$total_width_new = 3121; $page_orientation = 'P'; $page_size='2A0';} //2A0
			else if ($total_width > 3020  && $total_width <= 3953)
				{$total_width_new = 4415; $page_orientation = 'L'; $page_size='2A0';} //2A0
			
			else if ($total_width > 3953 && $total_width <= 4272)
				{$total_width_new = 4415; $page_orientation = 'P'; $page_size='4A0';} //4A0
			
			else{$total_width_new = 6242; $page_orientation = 'L'; $page_size='4A0';} //4A0
			
			$factorwidth = $total_width_new / $total_width ;
			if ($total_width > 0) {
				foreach ($col_width as $idx => $width) {
					$widthaux= round($width * $factorwidth , 2);
					if ($widthaux < 35) $widthaux = 35;
					$col_width[$idx] = $widthaux;
				}
			}	

			$new_col_width = array_sum($col_width);
			// Nota: Los 30px de la columna de numeración ya están incluidos en el cálculo de $total_width
			// --- Fin transformación a porcentaje ---

			// Agregar columna de numeración en el encabezado
			$headerHTML .= '<td cellpadding="1" valign="middle" bgcolor="#dddddd" align="center" width="30" height="1"><b>#</b></td> ';
			
			$count = 0;
			foreach($arr_val[0] as $key => $value) {
				/** @noinspection PhpUndefinedVariableInspection */
				$headerHTML .= '<td cellpadding="1" valign="middle" bgcolor="#dddddd" align="center" width='.$col_width[$count].' height="1" ><b>'.$key.'</b></td> ';
				$count++;
			}

			// --- Obtener tipos de columna para formateo numérico ---
			// --- Mapeo encabezado visible <-> tipo de campo para formateo numérico ---
			$header_to_type = array();
			$columnslist = $this->getQueryColumnsList($this->reportid, 'PDF');
			foreach ($columnslist as $fieldcolname => $colDef) {
				$selectedfields = explode(":", $fieldcolname);
				if (count($selectedfields) >= 5) {
					// Extraer el alias (nombre visible en el PDF) del SQL generado (AS '...')
					if (preg_match("/AS '([^']+)'/", $colDef, $m)) {
						$visible = $m[1];
						$type = strtolower($selectedfields[4]);
						$header_to_type[$visible] = $type;
					}
				}
			}

			// --- Fin mapeo encabezado-tipo ---

			// Preparar filas de datos en array para renderizado por bloques
			$pdfRecordCount = 0;
			$dataRows = array();
			foreach($arr_val as $key => $array_value) {
				$pdfRecordCount++;
				// Agregar columna de numeración al inicio de cada fila
				$valueHTML = '<td width="30" align="center" bgcolor="#f5f5f5"><b>'.$pdfRecordCount.'</b></td>';
				$count = 0;
				foreach($array_value as $hd => $value) {
					$type = isset($header_to_type[$hd]) ? $header_to_type[$hd] : '';
					// Formatear solo si es numérico y tipo adecuado
					if (is_numeric($value) && in_array($type, ['real', 'decimal', 'float', 'double', 'int', 'n'])) {
						if (isset($numberingHelper) && $numberingHelper !== null) {
							$value = $numberingHelper->setNumberFormat($value, $hd);
						}
					}
					// Sanitizar HTML problemático para TCPDF - ELIMINAR TODO EL HTML
					if (is_string($value) && strlen($value) > 0) {
						// Si contiene cualquier etiqueta HTML, eliminarla completamente
						if (strpos($value, '<') !== false && strpos($value, '>') !== false) {
							// Extraer solo el texto plano, eliminando TODO el HTML
							$value = strip_tags($value);
							// Reemplazar múltiples espacios/saltos de línea por uno solo
							$value = preg_replace('/\s+/', ' ', $value);
							// Limitar longitud para evitar celdas muy grandes
							if (strlen($value) > 250) {
								$value = substr($value, 0, 247) . '...';
							}
							// Trim espacios
							$value = trim($value);
						}
					}
					$valueHTML .= '<td width='.$col_width[$count].'>'.$value.'</td>';
					$count++;
				}
				$dataRows[] = '<tr>'.$valueHTML.'</tr>';
			}
		}
		$total_width95= $new_col_width * 0.98;
		$totalpdf = $this->GenerateReport('PRINT_TOTAL',$filterlist,false, $total_width95 );

	// Nota: Ya no construimos $html gigante (comentado para evitar error con $dataHTML)

		/** @noinspection PhpUndefinedVariableInspection */
		$columnlength = $new_col_width;


		if($columnlength > 4756) {
			/** @noinspection HtmlDeprecatedTag */
			die('<br><br><center>'.$app_strings['LBL_PDF']." <a href='javascript:window.history.back()'>".$app_strings['LBL_GO_BACK'].'.</a></center>');
		}
		$pdf = new CustomPDF($page_orientation,'mm',$page_size,true);

		// Obtener nombre de la empresa desde vtiger_organizationdetails
        $orgName = '';
        $resultOrg = $adb->pquery("SELECT organizationname FROM vtiger_organizationdetails LIMIT 1");
        if ($resultOrg && $adb->num_rows($resultOrg) > 0) {
            $rowOrg = $adb->fetchByAssoc($resultOrg);
            $orgName = $rowOrg['organizationname'];
        }
        // Título del reporte
        $reportTitle = isset($this->reportname) ? decode_html($this->reportname) : '';
        // Descripción del reporte (opcional)
        $reportDesc = isset($this->reportdescription) ? decode_html($this->reportdescription) : '';
        // Fecha y hora actual
        $fechaHora = date('Y-m-d H:i');

        // Asignar propiedades de encabezado
        $pdf->orgName = $orgName;
        $pdf->reportTitle = $reportTitle;
        $pdf->reportDesc = $reportDesc;
        $pdf->fechaHora = $fechaHora;
        // Asignar encabezado de columnas y ancho de tabla para que se repita en cada página
        $pdf->columnHeaderHTML = $headerHTML;
        $pdf->tableWidth = $new_col_width;
		
		$pdf->SetMargins(12, 40, 12); // Margen superior aumentado para el encabezado de columnas + espacio adicional
		$pdf->SetHeaderMargin(5);
        //$pdf->SetMargins(10, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetAutoPageBreak(true, 30); // Margen inferior más grande para saltos más tempranos
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		/** @noinspection PhpUndefinedVariableInspection */
		$pdf->setLanguageArray($l);
		$pdf->AddPage();
		
		// El título y encabezado de columnas ya se renderizan en Header()
		$pdf->SetFont('Arial','',9);
	
	// Renderizar tabla de datos SIN encabezado (el encabezado está en el Header de cada página)
	if (isset($dataRows) && count($dataRows) > 0) {
		$totalRows = count($dataRows);
		
		// Construir tabla completa SOLO con datos (sin encabezado)
		$fullTableHTML = '<table cellpadding="2" border="0" width="'. $new_col_width .'">';
		$fullTableHTML .= '<tbody>';
		$fullTableHTML .= implode('', $dataRows);
		$fullTableHTML .= '</tbody></table>';
		
		// Renderizar tabla completa (TCPDF manejará saltos de página)
		$pdf->writeHTML($fullTableHTML, true, false, true, false, '');
		
		if (!empty($totalpdf)) {
			$pdf->Ln(2);
			$totalHTML = '<table cellpadding="2" border="1" width="'. $new_col_width .'" style="border-color:#6e6e6e; border-style:solid; border-width:1px">';
			// Ajustar colspan para incluir la columna de numeración (+1)
			$totalHTML .= '<tr><td colspan="'. ($count + 1).'"  width="'. ($new_col_width*0.99) .'"><center>'.$totalpdf.'</center></td></tr>';
			$totalHTML .= '</table>';
			$pdf->writeHTML($totalHTML, true, false, true, false, '');
		}
	}
		return $pdf;
	}

    /**
     * Obtiene los encabezados y campos (ordenados) a partir del resultado de un query de reporte.
     * Devuelve un array con:
     *   - headers: array de etiquetas amigables (ej: Horas estimadas AVG)
     *   - fields: array de nombres de campo tal como vienen del query
     * Uso: Llamar tras ejecutar el query, pasando el resource $result y el objeto $adb.
     */
    public function getUnifiedReportHeadersAndFields($result, $adb, $modules_selected = array(), $secondarymodule = '') {
        $y = $adb->num_fields($result);
        $headers = array();
        $fields = array();
        for ($x = 0; $x < $y; $x++) {
            $fld = $adb->field_name($result, $x);
            $fieldname = $fld->name;
            $fields[] = $fieldname;
            $headerLabel = str_replace('_', ' ', $fieldname);
            // Si es acción o contador, traducir
            if ($fieldname == 'ACTION' || $fieldname == 'LBL_ACTION') {
                $headerLabel = getTranslatedString('LBL_ACTION', 'Reports');
            } elseif ($fieldname == 'LBL_RECORD_COUNT') {
                $headerLabel = getTranslatedString('LBL_RECORD_COUNT', 'Reports');
            }
            // Si es campo de módulo secundario, anteponer módulo
            $mod_name = explode(' ', $headerLabel, 2);
            $moduleLabel = '';
            if (isset($modules_selected[0]) && in_array($mod_name[0], $modules_selected)) {
                $moduleLabel = getTranslatedString($mod_name[0], $mod_name[0]);
            }
            if (!empty($secondarymodule)) {
                if ($moduleLabel != '') {
                    $headerLabel = $moduleLabel . ' ' . getTranslatedString(isset($mod_name[1]) ? $mod_name[1] : '', $mod_name[0]);
                } else {
                    $headerLabel = getTranslatedString($headerLabel);
                }
            } else {
                if ($moduleLabel != '') {
                    $headerLabel = getTranslatedString(isset($mod_name[1]) ? $mod_name[1] : '', $mod_name[0]);
                } else {
                    $headerLabel = getTranslatedString($headerLabel);
                }
            }
            $headers[] = $headerLabel;
        }
        return array('headers' => $headers, 'fields' => $fields);
	}

	public function writeReportToExcelFile($fileName, $filterlist = '') {
		global $currentModule, $current_language;
		$mod_strings = return_module_language($current_language, $currentModule);

		require_once('include/php_writeexcel/class.writeexcel_workbook.inc.php');
		require_once('include/php_writeexcel/class.writeexcel_worksheet.inc.php');

		$workbook = new writeexcel_workbook($fileName);
		$worksheet = $workbook->addworksheet();

		// Set the column width for columns 1, 2, 3 and 4
		$worksheet->set_column(0, 3, 25);

		// Create a format for the column headings
		$header = $workbook->addformat();
		$header->set_bold();
		$header->set_size(12);
		$header->set_color('blue');

		$arr_val = $this->GenerateReport('PDF',$filterlist, false, null);
		$totalxls = $this->GenerateReport('TOTALXLS',$filterlist, false, null);

		if(isset($arr_val)) {
			foreach($arr_val[0] as $key => $value) {
				/** @noinspection PhpUndefinedVariableInspection */
				$worksheet->write(0, $count, html_entity_decode($key), $header);
				$count++;
			}
			$rowcount=1;
			foreach($arr_val as $key => $array_value) {
				$dcount = 0;
				foreach($array_value as $hdr => $value) {
					$value = decode_html($value);
					$worksheet->write(($key+1), $dcount, mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8'));
					$dcount++;
				}
				$rowcount++;
			}

			$rowcount++;
			$count=0;
			if(is_array($totalxls[0])) {
				foreach($totalxls[0] as $key => $value) {
					$chdr=substr($key,-3,3);
					$translated_str = in_array($chdr,array_keys($mod_strings)) ? $mod_strings[$chdr] : $key;
					$worksheet->write($rowcount, $count, $translated_str);
					$count++;
				}
			}
			$rowcount++;
			foreach($totalxls as $key => $array_value) {
				$dcount = 0;
				foreach($array_value as $hdr => $value) {
					$value = decode_html($value);
					$worksheet->write(($key+$rowcount), $dcount, mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8'));
					$dcount++;
				}
			}
		}
		$workbook->close();
	}

	public function getGroupByTimeList($reportId) {
		global $adb;
		$groupByTimeQuery = 'SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?';
		$groupByTimeRes = $adb->pquery($groupByTimeQuery,array($reportId));
		$num_rows = $adb->num_rows($groupByTimeRes);
		for($i=0; $i<$num_rows; $i++){
			$sortColName = $adb->query_result($groupByTimeRes, $i,'sortcolname');
			/** @noinspection PhpUnusedLocalVariableInspection */
			list($tablename,$colname,$module_field,$fieldname,$single) = explode(':',$sortColName);
			$groupField = $module_field;
			$groupCriteria = $adb->query_result($groupByTimeRes, $i,'dategroupbycriteria');
			if(in_array($groupCriteria,array_keys($this->groupByTimeParent))) {
				$parentCriteria = $this->groupByTimeParent[$groupCriteria];
				foreach($parentCriteria as $criteria){
					$groupByCondition[]=$this->GetTimeCriteriaCondition($criteria, $groupField);
				}
			}
			$groupByCondition[] = $this->GetTimeCriteriaCondition($groupCriteria, $groupField);
		}
		/** @noinspection PhpUndefinedVariableInspection */
		return $groupByCondition;
	}

	// @codingStandardsIgnoreStart
	public function GetTimeCriteriaCondition($criteria, $dateField) {
		// @codingStandardsIgnoreEnd
		$condition = '';
		if(strtolower($criteria)=='year') {
			$condition = "DATE_FORMAT($dateField, '%Y' )";
		} else if (strtolower($criteria)=='month') {
			$condition = "CEIL(DATE_FORMAT($dateField,'%m')%13)";
		} else if(strtolower($criteria)=='quarter') {
			$condition = "CEIL(DATE_FORMAT($dateField,'%m')/3)";
		}
		return $condition;
	}

	// @codingStandardsIgnoreStart
	/**
	 * @param $reportid

	 * @return string

	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function GetFirstSortByField($reportid) {
		// @codingStandardsIgnoreEnd
		global $adb;
		$groupByField ='';
		$sortFieldQuery = "SELECT * FROM vtiger_reportsortcol
                            LEFT JOIN vtiger_reportgroupbycolumn ON (vtiger_reportsortcol.sortcolid = vtiger_reportgroupbycolumn.sortid and vtiger_reportsortcol.reportid = vtiger_reportgroupbycolumn.reportid)
                            WHERE columnname!='none' and vtiger_reportsortcol.reportid=? ORDER By sortcolid";
		$sortFieldResult= $adb->pquery($sortFieldQuery,array($reportid));
		if($adb->num_rows($sortFieldResult)>0) {
			$fieldcolname = $adb->query_result($sortFieldResult,0,'columnname');
			list($tablename,$colname,$module_field,$fieldname,$typeOfData) = explode(':',$fieldcolname);
			/** @noinspection PhpUnusedLocalVariableInspection */
			list($modulename,$fieldlabel) = explode('_', $module_field, 2);
			$groupByField = $module_field;
			if($typeOfData == 'D') {
				$groupCriteria = $adb->query_result($sortFieldResult,0,'dategroupbycriteria');
				if(strtolower($groupCriteria)!='none') {
					if(in_array($groupCriteria,array_keys($this->groupByTimeParent))) {
						$parentCriteria = $this->groupByTimeParent[$groupCriteria];
						foreach($parentCriteria as $criteria){
							$groupByCondition[]=$this->GetTimeCriteriaCondition($criteria, $groupByField);
						}
					}
					$groupByCondition[] = $this->GetTimeCriteriaCondition($groupCriteria, $groupByField);
					$groupByField = implode(', ',$groupByCondition);
				}
			} else if(CheckFieldPermission($fieldname,$modulename) != 'true') {
				$groupByField = $tablename.'.'.$colname;
			}
		}
		return $groupByField;
	}

	// @codingStandardsIgnoreStart
	/**
	 * @param $moduleName
	 * @param $fieldInfo

	 * @return array

	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function getReferenceFieldColumnList($moduleName, $fieldInfo) {
		// @codingStandardsIgnoreEnd
		$adb = PearDatabase::getInstance();

		$columnsSqlList = array();

		$fieldInstance = WebserviceField::fromArray($adb, $fieldInfo);
		$referenceModuleList = $fieldInstance->getReferenceList();

		$reportSecondaryModules = explode(':', $this->secondarymodule);

		if($moduleName != $this->primarymodule && in_array($this->primarymodule, $referenceModuleList)) {
			$entityTableFieldNames = getEntityFieldNames($this->primarymodule);
			$entityTableName = $entityTableFieldNames['tablename'];
			$entityFieldNames = $entityTableFieldNames['fieldname'];

			$columnList = array();
			if(is_array($entityFieldNames)) {
				foreach ($entityFieldNames as $entityColumnName) {
					$columnList["$entityColumnName"] = "$entityTableName.$entityColumnName";
				}
			} else {
				$columnList[] = "$entityTableName.$entityFieldNames";
			}
			if(count($columnList) > 1) {
				$columnSql = getSqlForNameInDisplayFormat($columnList, $this->primarymodule);
			} else {
				$columnSql = implode('', $columnList);
			}
			$columnsSqlList[] = $columnSql;
		} else {
			foreach($referenceModuleList as $referenceModule) {
				$entityTableFieldNames = getEntityFieldNames($referenceModule);
				$entityTableName = $entityTableFieldNames['tablename'];
				$entityFieldNames = $entityTableFieldNames['fieldname'];

				if (in_array($referenceModule, $reportSecondaryModules)) {
					$referenceTableName = $entityTableName;
				} else if (in_array($moduleName, $reportSecondaryModules)) {
					$referenceTableName = "{$entityTableName}Rel$moduleName";
				} else {
					$referenceTableName = "{$entityTableName}Rel{$moduleName}{$fieldInstance->getFieldId()}";
				}

				$columnList = array();
				if(is_array($entityFieldNames)) {
					foreach ($entityFieldNames as $entityColumnName) {
						$columnList["$entityColumnName"] = "$referenceTableName.$entityColumnName";
					}
				} else {
					$columnList[] = "$referenceTableName.$entityFieldNames";
				}
				if(count($columnList) > 1) {
					$columnSql = getSqlForNameInDisplayFormat($columnList, $referenceModule);
				} else {
					$columnSql = implode('', $columnList);
				}
				if ($referenceModule == 'DocumentFolders' && $fieldInstance->getFieldName() == 'folderid') {
					$columnSql = 'vtiger_attachmentsfolder.foldername';
				}
				if ($referenceModule == 'Currency' && $fieldInstance->getFieldName() == 'currency_id') {
					$columnSql = "vtiger_currency_info$moduleName.currency_name";
				}
				$columnsSqlList[] = $columnSql;
			}
		}
		return $columnsSqlList;
	}

}

