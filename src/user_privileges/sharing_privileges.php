<?php

$sharingPrivilegies = false;
if (isset($_SESSION['plat'])) {
	if(is_dir($_SESSION['plat']."/user_privileges")) {
		require($_SESSION['plat'].'/user_privileges/sharing_privileges_' . $local_user->id . '.php');
		$sharingPrivilegies = true;
	}
}

if (!$sharingPrivilegies)
	require('user_privileges/sharing_privileges_' . $local_user->id . '.php');

?>