<?php
	require_once ('include/utils/utils.php');

	function deleteReport ($iddelete) {
		global $adb;

		$adb->pquery ("DELETE FROM vtiger_report2module WHERE (id=?);", array ($iddelete));
	}

	function getReportsAllList ($PAGEACTUAL) {
		global $adb;

		$QUERY = "SELECT t.id templateid,
		t.has_inventory,
		t.code,
		t.name,
		r.id  reportid,
		ta.name namemodule,
		ta.tablabel module,
		ta.tabid,
		r.active
	FROM vtiger_report_template t
	INNER JOIN vtiger_report2module r ON r.code_template = t.code
	INNER JOIN vtiger_tab ta ON ta.tabid = r.tabid
	ORDER BY ta.tablabel ASC";

		$LIMIT      = '';
		$TOTALxPAGE = 25;
		$TOTALTOTAL = 0;
		$TOTALPAGES = 1;
		$query2     = $adb->pquery ($QUERY, array ());
		$TOTALTOTAL = $adb->num_rows ($query2);

		if ($TOTALTOTAL > $TOTALxPAGE) {
			$TOTALPAGES = ceil ($TOTALTOTAL / $TOTALxPAGE);
			$NROINICIAL = ($PAGEACTUAL - 1) * $TOTALxPAGE;
			$LIMIT      = "LIMIT $NROINICIAL,$TOTALxPAGE";
		}

		$templates = array ();
		$result    = $adb->pquery ("$QUERY $LIMIT", array (), true);
		while ($row = $adb->fetch_row ($result)) {
			$templates[] = $row;
		}

		return $templates;
	}

	function getReport ($idedit, $idduplicate) {
		global $adb;

		$template = array ();
		if ($idedit or $idduplicate) {
			$wheretemplates = '';
			if ($idedit) {
				$wheretemplates .= ($wheretemplates ? 'AND' : 'WHERE') . "(T.id = $idedit)";
			} elseif ($idduplicate) {
				$wheretemplates .= ($wheretemplates ? 'AND' : 'WHERE') . "(T.id = $idduplicate)";
			}

			if ($wheretemplates) {
				$result   = $adb->pquery ("SELECT T.*
					FROM vtiger_report2module T
					$wheretemplates", array ());
				$template = $adb->fetch_row ($result);
			}
		}

		return $template;
	}

	function getTemplateAll () {
		global $adb;

		$eventos = array ();
		$result  = $adb->pquery ("SELECT * FROM vtiger_report_template ORDER BY code ", array ());
		while ($row = $adb->fetch_row ($result)) {
			$eventos[] = $row;
		}

		return $eventos;
	}

	function getTemplateList ($PAGEACTUAL) {
		global $adb;

		$QUERY = "SELECT t.id templateid,
		t.has_inventory,
		t.code,
		t.name
	FROM vtiger_report_template t
	ORDER BY t.id DESC";

		$LIMIT      = '';
		$TOTALxPAGE = 25;
		$TOTALTOTAL = 0;
		$TOTALPAGES = 1;
		$query2     = $adb->pquery ($QUERY, array ());
		$TOTALTOTAL = $adb->num_rows ($query2);

		if ($TOTALTOTAL > $TOTALxPAGE) {
			$TOTALPAGES = ceil ($TOTALTOTAL / $TOTALxPAGE);
			$NROINICIAL = ($PAGEACTUAL - 1) * $TOTALxPAGE;
			$LIMIT      = "LIMIT $NROINICIAL,$TOTALxPAGE";
		}

		$templates = array ();
		$result    = $adb->pquery ("$QUERY $LIMIT", array (), true);
		while ($row = $adb->fetch_row ($result)) {
			$templates[] = $row;
		}

		return $templates;
	}

	function getTemplate ($idedit, $idduplicate) {
		global $adb;

		$template = array ();
		if ($idedit or $idduplicate) {
			$wheretemplates = '';
			if ($idedit) {
				$wheretemplates .= ($wheretemplates ? 'AND' : 'WHERE') . "(T.id = $idedit)";
			} elseif ($idduplicate) {
				$wheretemplates .= ($wheretemplates ? 'AND' : 'WHERE') . "(T.id = $idduplicate)";
			}

			if ($wheretemplates) {
				$result   = $adb->pquery ("SELECT T.*
					FROM vtiger_report_template T
					$wheretemplates", array ());
				$template = $adb->fetch_row ($result);
			}
		}

		return $template;
	}

	function deleteTemplate ($iddelete) {
		global $adb;

		$adb->pquery ("DELETE FROM vtiger_report_template WHERE (id=?);", array ($iddelete));
	}

	function sanitizeString ($string) {
		$string = str_replace (
			array ('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
			array ('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
			$string
		);
		$string = str_replace (
			array ('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
			array ('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
			$string
		);
		$string = str_replace (
			array ('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
			array ('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
			$string
		);
		$string = str_replace (
			array ('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
			array ('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
			$string
		);
		$string = str_replace (
			array ('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
			array ('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
			$string
		);
		$string = str_replace (
			array ('ñ', 'Ñ', 'ç', 'Ç'),
			array ('n', 'N', 'c', 'C'),
			$string
		);
		return $string;
	}
