<?php

	global $root_directory;

	$fileName = "$root_directory/user_privileges/audit_trail.php";

	if ((!file_exists ($fileName)) || (!is_file ($fileName))) {
		return;
	}

	$fp     = fopen ($fileName, 'r+');
	$input  = '';
	$output = '';
	while (!feof ($fp)) {
		$input = fgets ($fp, 5200);
		list ($starter, $tmp) = explode (' = ', $input);
		if (($starter == '$audit_trail') && (stristr ($tmp, 'false'))) {
			$output .= "\$audit_trail = 'true';\n";
		} else if (($starter == '$audit_trail') && (stristr ($tmp, 'true'))) {
			$output .= "\$audit_trail = 'false';\n";
		} else {
			$output .= $input;
		}
	}
	fclose ($fp);

	$fp = fopen ($fileName, 'w');
	fputs ($fp, $output);
	fclose ($fp);
