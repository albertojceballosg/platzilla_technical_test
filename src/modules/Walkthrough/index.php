<?php

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('IS_GUEST_USER', !empty ($_SESSION ['isGuestUser']));
	$smarty->assign ('IS_FIRST_CONNECTION', !empty ($_SESSION ['firstConnection']));
	$smarty->display ('modules/Walkthrough/index.tpl');
