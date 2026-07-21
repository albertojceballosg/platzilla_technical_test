<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://www.opensource.org/licenses/MIT
 */

//LEONARDO DICE (2014-06-05): 
//Me parece que esto deberia ir a un include, porque se usa también en el index.php 
$lstPlatsFijas = array('plat.tuninha.com'=>'fusion',
						'gestoria-facil'=>'gf',
						'outsourcing-facil'=>'of',
						'formacion-facil'=>'ff',
					);
 
 if (strstr($_SERVER['HTTP_HOST'],'platzilla.com')) {
	$nameplat = explode('.',$_SERVER['HTTP_HOST']);
	$_REQUEST['plat'] = $nameplat[0];
}
if (isset($lstPlatsFijas[$nameplat[0]]) && $nameplat[0]) {
	$nameplat[0] = $lstPlatsFijas[$nameplat[0]];
	$_REQUEST['plat'] = $nameplat[0];
}
if (isset($_REQUEST['plat'])) {
	$plat = $_REQUEST['plat'];
	session_name($plat);
}

session_start();
require('UploadHandler.php');
$upload_handler = new UploadHandler();
