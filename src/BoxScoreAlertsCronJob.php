<?php

ini_set('display_errors', 1);
ini_set("error_reporting", true);
//error_reporting(E_ALL & E_NOTICE & E_WARNING & E_DEPRECATED & E_STRICT);
error_reporting(E_ERROR);
global $current_user;
require ('config.inc.php');


require_once('data/CRMEntity.php');
//require_once('include/utils/UserInfoUtil.php');
require_once('include/utils/ADBManager.class.php');


if (!$current_user) {
	$current_user = CRMEntity::getInstance ('Users');
	$current_user->retrieve_entity_info (1, 'Users');
}

echo "<pre>el current user es ".$current_user->id."</pre>";

require_once('include/utils/comunesSixSigma.php');



// Alerts to create noconformity
getAlertDataForNC();

// Alerts to send email
getAlertDataForSendEmail();

// Alerts to create noconformity and to send email
getAlertDataForNCandSendEmail();

?>
