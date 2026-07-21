<?php
	require_once ('Smarty_setup.php');

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('IS_ERROR', false);
	$smarty->assign ('MESSAGE', 'Sentimos que te vayas... :-( mándanos una carta o una postal a donde quieras que estés..., y regresa algún día. En cualquier caso, te deseamos el mayor de los éxitos en tu empresa, negocio o emprendimiento');
	$smarty->display ('SubscriptionCancelled.tpl');
