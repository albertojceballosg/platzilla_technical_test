<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once('vtlib/Vtiger/Utils.php');
include_once('vtlib/Vtiger/FieldBasic.php');

/**
 * Provides APIs to control vtiger CRM Field
 * @package vtlib
 */
class Vtiger_Field extends Vtiger_FieldBasic {

	/**
	 * Get unique picklist id to use
	 * @access private
	 */
	function __getPicklistUniqueId() {
		global $adb;
		return $adb->getUniqueID('vtiger_picklist');
	}

	/**
	 * Set values for picklist field (for all the roles)
	 * @param Array List of values to add.
	 */
	function setPicklistValues($values) {
		global $adb,$default_charset;

		// Non-Role based picklist values
		if($this->uitype == '16') {
			$this->setNoRolePicklistValues($values);
			return;
		}

		$picklist_table = 'vtiger_'.$this->name;
		$picklist_idcol = $this->name.'id';
		if(!Vtiger_Utils::CheckTable($picklist_table)) {
			Vtiger_Utils::CreateTable(
				$picklist_table,
				"($picklist_idcol INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				$this->name VARCHAR(200) NOT NULL,
				presence INT (1) NOT NULL DEFAULT 1,
				picklist_valueid INT NOT NULL DEFAULT 0)",
				true);
			$new_picklistid = $this->__getPicklistUniqueId();
			$adb->pquery("INSERT INTO vtiger_picklist (picklistid,name) VALUES(?,?)",Array($new_picklistid, $this->name));
			self::log("Creating table $picklist_table ... DONE");
		} else {
			$new_picklistid = $adb->query_result(
				$adb->pquery("SELECT picklistid FROM vtiger_picklist WHERE name=?", Array($this->name)), 0, 'picklistid');
		}

		$specialNameSpacedPicklists  = array(
			'opportunity_type'=>'opptypeid',
			'duration_minutes'=>'minutesid',
			'recurringtype'=>'recurringeventid'
		);

		// Fix Table ID column names
		$fieldName = (string)$this->name;
		if(in_array($fieldName.'_id', $adb->getColumnNames($picklist_table))) {
			$picklist_idcol = $fieldName.'_id';
		} elseif(array_key_exists($fieldName, $specialNameSpacedPicklists)) {
			$picklist_idcol = $specialNameSpacedPicklists[$fieldName];
		}
		// END

		// Add value to picklist now
		$sortid = 0; // TODO To be set per role
		include_once 'include/utils/CommonUtils.php';
		foreach($values as $value) {
			$value = htmlentities($value,ENT_QUOTES,$default_charset);
			$new_picklistvalueid = getUniquePicklistID();
			$presence = 1; // 0 - readonly, Refer function in include/ComboUtil.php
			$new_id = $adb->getUniqueID($picklist_table);
			$adb->setDieOnError(false);

			//Comentado para solucionar incidencia reportada en la creaci[on de campo multilista y por ende en campo Lista desde
			//ModuleManeger
			//[ TT11096 ] Fallas en Tipos de Campos - Creador de Módulos
			//DM 14/06/2016
			/*$adb->pquery("INSERT INTO $picklist_table($picklist_idcol, $this->name, presence, picklist_valueid) VALUES(?,?,?,?)",
				Array($new_id, html_entity_decode($value, NULL, 'UTF-8'), $presence, $new_picklistvalueid));*/
			$adb->pquery("INSERT INTO $picklist_table($picklist_idcol, $this->name, presence, picklist_valueid) VALUES(?,?,?,?)",
					Array($new_id, $value, $presence, $new_picklistvalueid));

			++$sortid;

			// Associate picklist values to all the role
			$adb->query("INSERT INTO vtiger_role2picklist(roleid, picklistvalueid, picklistid, sortid) SELECT roleid,
				$new_picklistvalueid, $new_picklistid, $sortid FROM vtiger_role");

			$adb->setDieOnError(true);
		}
	}

	/**
	 * Set values for picklist field (non-role based)
	 * @param Array List of values to add
	 *
	 * @internal Creates picklist base if it does not exists
	 * @access private
	 */
	function setNoRolePicklistValues($values) {
		global $adb;

		$picklist_table = 'vtiger_'.$this->name;
		$picklist_idcol = $this->name.'id';

		if(!Vtiger_Utils::CheckTable($picklist_table)) {
			Vtiger_Utils::CreateTable(
				$picklist_table,
				"($picklist_idcol INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				$this->name VARCHAR(200) NOT NULL,
				sortorderid INT(11),
				presence INT (11) NOT NULL DEFAULT 1)",
				true);
			self::log("Creating table $picklist_table ... DONE");
		}

		// Add value to picklist now
		$sortid = 1;
		foreach($values as $value) {
			$presence = 1; // 0 - readonly, Refer function in include/ComboUtil.php
			$new_id = $adb->getUniqueId($picklist_table);
			$adb->pquery("INSERT INTO $picklist_table($picklist_idcol, $this->name, sortorderid, presence) VALUES(?,?,?,?)",
				Array($new_id, $value, $sortid, $presence));

			$sortid = $sortid+1;
		}
	}

	/**
	 * Set relation between field and modules (UIType 10)
	 * @param Array List of module names
	 */
	function setRelatedModules($moduleNames) {

		// We need to create core table to capture the relation between the field and modules.
		if(!Vtiger_Utils::CheckTable('vtiger_fieldmodulerel')) {
			Vtiger_Utils::CreateTable(
				'vtiger_fieldmodulerel',
				'(fieldid INT NOT NULL, module VARCHAR(100) NOT NULL, relmodule VARCHAR(100) NOT NULL, status VARCHAR(10), sequence INT)',
				true
			);
		}
		// END

		global $adb;
		foreach($moduleNames as $relmodule) {
			$checkres = $adb->pquery('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=? AND relmodule=?',
				Array($this->id, $this->getModuleName(), $relmodule));

			// If relation already exist continue
			if($adb->num_rows($checkres)) continue;

			$adb->pquery('INSERT INTO vtiger_fieldmodulerel(fieldid, module, relmodule) VALUES(?,?,?)',
				Array($this->id, $this->getModuleName(), $relmodule));

			self::log("Setting $this->name relation with $relmodule ... DONE");
		}
		return true;
	}

	/**
	 * Remove relation between the field and modules (UIType 10)
	 * @param Array List of module names
	 */
	function unsetRelatedModules($moduleNames) {
		global $adb;
		foreach($moduleNames as $relmodule) {
			$adb->pquery('DELETE FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=? AND relmodule = ?',
				Array($this->id, $this->getModuleName(), $relmodule));

			Vtiger_Utils::Log("Unsetting $this->name relation with $relmodule ... DONE");
		}
		return true;
	}

	/**
	 * Get Vtiger_Field instance by fieldid or fieldname
	 * @param mixed fieldid or fieldname
	 * @param Vtiger_Module Instance of the module if fieldname is used
	 */
	static function getInstance($value, $moduleInstance=false) {
		global $adb;
		$instance = false;

		$query = false;
		$queryParams = false;
		if(Vtiger_Utils::isNumber($value)) {
			$query = "SELECT * FROM vtiger_field WHERE fieldid=?";
			$queryParams = Array($value);
		} else {
			$query = "SELECT * FROM vtiger_field WHERE fieldname=? AND tabid=?";
			$queryParams = Array($value, $moduleInstance->id);
		}
		$result = $adb->pquery($query, $queryParams);
		if($adb->num_rows($result)) {
			$instance = new self();
			$instance->initialize($adb->fetch_array($result), $moduleInstance);
		}
		return $instance;
	}

	/**
	 * Get Vtiger_Field instances related to block
	 * @param Vtiger_Block Instnace of block to use
	 * @param Vtiger_Module Instance of module to which block is associated
	 */
	 static function getAllForBlock($blockInstance, $moduleInstance=false) {
		global $adb;
		$instances = false;

		$query = false;
		$queryParams = false;
		if($moduleInstance) {
			$query = "SELECT * FROM vtiger_field WHERE block=? AND tabid=?";
			$queryParams = Array($blockInstance->id, $moduleInstance->id);
		} else {
			$query = "SELECT * FROM vtiger_field WHERE block=?";
			$queryParams = Array($blockInstance->id);
		}
		$result = $adb->pquery($query, $queryParams);
		for($index = 0; $index < $adb->num_rows($result); ++$index) {
			$instance = new self();
			$instance->initialize($adb->fetch_array($result), $moduleInstance, $blockInstance);
			$instances[] = $instance;
		}
		return $instances;
	}

	/**
	 * Get Vtiger_Field instances related to module
	 * @param Vtiger_Module Instance of module to use
	 */
	static function getAllForModule($moduleInstance) {
		global $adb;
		$instances = false;

		$query = "SELECT * FROM vtiger_field WHERE tabid=?";
		$queryParams = Array($moduleInstance->id);

		$result = $adb->pquery($query, $queryParams);
		for($index = 0; $index < $adb->num_rows($result); ++$index) {
			$instance = new self();
			$instance->initialize($adb->fetch_array($result), $moduleInstance);
			$instances[] = $instance;
		}
		return $instances;
	}

	/**
	 * Delete fields associated with the module
	 * @param Vtiger_Module Instance of module
	 * @access private
	 */
	static function deleteForModule($moduleInstance) {
		global $adb;
		$adb->pquery("DELETE FROM vtiger_field WHERE tabid=?", Array($moduleInstance->id));
		self::log("Deleting fields of the module ... DONE");
	}


	/* Function to set the Sequence string and sequence number starting value */
	function setModuleSeqNumber($mode, $module, $req_str = '', $req_no = '') {
		global $adb;
		//when we configure the invoice number in Settings this will be used
		if ($mode == "configure" && $req_no != '') {
			$check = $adb->pquery("select cur_id from vtiger_modentity_num where semodule=? and prefix = ?", array($module, $req_str));
			if ($adb->num_rows($check) == 0) {
				$numid = $adb->getUniqueId("vtiger_modentity_num");
				$active = $adb->pquery("select num_id from vtiger_modentity_num where semodule=? and active=1", array($module));
				$adb->pquery("UPDATE vtiger_modentity_num SET active=0 where num_id=?", array($adb->query_result($active, 0, 'num_id')));

				$adb->pquery("INSERT into vtiger_modentity_num values(?,?,?,?,?,?)", array($numid, $module, $req_str, $req_no, $req_no, 1));
				return true;
			} else if ($adb->num_rows($check) != 0) {
				$num_check = $adb->query_result($check, 0, 'cur_id');
				if ($req_no < $num_check) {
					return false;
				} else {
					$adb->pquery("UPDATE vtiger_modentity_num SET active=0 where active=1 and semodule=?", array($module));
					$adb->pquery("UPDATE vtiger_modentity_num SET cur_id=?, active = 1 where prefix=? and semodule=?", array($req_no, $req_str, $module));
					return true;
				}
			}
		} else if ($mode == "increment" || $mode == "decrement") {
			$bSequence = false;

			$this->crmid_sequence = $this->column_fields[obtenerValorVariable('field_sequence',$module)];
			if (isset($this->crmid_sequence) && !empty($this->crmid_sequence)) {
				//It checks if there own numbering for the record associated
				$check = $adb->pquery("select cur_id,prefix from vtiger_modentity_num where semodule=? and active = 1 and crmid = ?", array($module,$this->crmid_sequence));


				if ($adb->num_rows($check) == 1) {
					$prefix = $adb->query_result($check, 0, 'prefix');
					$curid = $adb->query_result($check, 0, 'cur_id');
					$bSequence = true;
				}
			}
			if (!$bSequence) {
				//when we save new invoice we will increment the invoice id and write
				$check = $adb->pquery("select cur_id,prefix from vtiger_modentity_num where semodule=? and active = 1", array($module));
				$prefix = $adb->query_result($check, 0, 'prefix');
				$curid = $adb->query_result($check, 0, 'cur_id');
			}
			//Se actualizan los datos de los campos según la siguiente secuencia

			foreach ($this->column_fields as $campo => $valor) {
				if (getUItype($module,$campo) == 5 || getUItype($module,$campo) == 6 || getUItype($module,$campo) == 23) {
					$valor = DateTimeField::convertToDBFormat($valor);
					$prefix = str_replace('$YEAR_'.$campo.'$',substr($valor,0,4),$prefix);
					$prefix = str_replace('$MONTH_'.$campo.'$',substr($valor,5,2),$prefix);
					$prefix = str_replace('$DAY_'.$campo.'$',substr($valor,8,2),$prefix);
					$prefix = str_replace('$'.$campo.'$',$valor,$prefix);
				} else {
					// EGC David pidió substring de un campo, el formato sería $campo|x$
					// donde x es la cantidad de caracteres a extraer,
					// ej: $timetravel|1$ extrae M o T del campo que indica el turno del viaje en tuninha
					if (preg_match('/\$'.$campo.'\|(.?)\$/', $prefix, $matches)) {
						$prefix = str_replace($matches[0],  substr($valor, 0, $matches[1]),$prefix);
					} else
						$prefix = str_replace('$'.$campo.'$',$valor,$prefix);
				}
			}

			$prefix = str_replace('$YEAR$',date("Y"),$prefix);
			$prefix = str_replace('$MONTH$',date("m"),$prefix);
			$prefix = str_replace('$DAY$',date("d"),$prefix);

			if (strstr($prefix,'$CURID$')) {
				$prefix = str_replace('$CURID$',$curid,$prefix);
				$prev_inv_no = $prefix;
			} else {
				$prev_inv_no = $prefix . $curid;
			}


			$strip = strlen($curid) - strlen($curid + 1);
			if ($strip < 0)
				$strip = 0;
			$temp = str_repeat("0", $strip);
			if ($mode == "increment")
				$req_no.= $temp . ($curid + 1);
			else	// EGC casos donde no se quiere incrementar el contador, ej: devolución de billetes en viajes.
				$req_no.= $temp . ($curid - 1);

			$adb->pquery("UPDATE vtiger_modentity_num SET cur_id=? where cur_id=? and active=1 AND semodule=?", array($req_no, $curid, $module));
			return decode_html($prev_inv_no);
		}
	}

}
?>
