<?php
	/** @var Users $current_user */
	global $current_user;

	$local_user = clone $current_user;

	require ('user_privileges/user_privileges.php');
	require ('user_privileges/sharing_privileges.php');
