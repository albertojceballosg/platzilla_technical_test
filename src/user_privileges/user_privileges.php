<?php
	/** @var Users $local_user */

	$userPrivilegies = false;
	if ((isset ($_SESSION ['plat'])) && (is_dir ("{$_SESSION ['plat']}/user_privileges"))) {
		require ("{$_SESSION ['plat']}/user_privileges/user_privileges_{$local_user->id}.php");
		$userPrivilegies = true;
	}

	if (!$userPrivilegies) {
		require ("user_privileges/user_privileges_{$local_user->id}.php");
	}
