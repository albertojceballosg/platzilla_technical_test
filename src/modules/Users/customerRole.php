<?php
	global $adb, $current_user, $gInstanciaEmpresaFacil;

	if ($gInstanciaEmpresaFacil)
		require_once('webservice.empresafacil.php');
	else
		require_once('webservice.time.php');
	
	$query = "UPDATE vtiger_users SET customerroleid = ? WHERE id = ?";
	
	$adb->pquery($query,array($_REQUEST['customerroleid'],$_REQUEST['id']));
	
	global $client;
	
	$focus = new Users;
	$focus->id = $_REQUEST['id'];
	$focus->retrieve_entity_info($_REQUEST['id'],'Users');
	
	$params = array('user_name' =>	 	_WEBSERVICE_USER,
					'user_password'=>	_WEBSERVICE_PASS,
					'version' => 		"5.4.0");
	$result = $client->call('authenticate_user', $params, $Server_Path, $Server_Path);
	$customerid = $result[0]['id'];
	$sessionid = $result[0]['sessionid'];
	
	$params = array('plat'	=> $_SESSION['plat'],
					'customerid'	=> $customerid,
					'sessionid'		=> $sessionid,
					);
	$accountid = $client->call('getAccountIdByPlatName', $params, $Server_Path, $Server_Path);
	$params = array('email'	=> $focus->column_fields['user_name'].'@'.$_SESSION['plat'].'.platzilla.com',
					'customerid'	=> $customerid,
					'sessionid'		=> $sessionid,
					);
	
	if (strstr($focus->column_fields['user_name'], '@'))
		$params['email'] = $focus->column_fields['user_name'];
	
	$userid = $client->call('getContactIdByEMail', $params, $Server_Path, $Server_Path);
	
	$fields = array('email'=>$focus->column_fields['user_name'].'@'.$_SESSION['plat'].'.platzilla.com',
						'lastname'=>$focus->column_fields['last_name'],
						'firstname'=>$focus->column_fields['first_name'],
						'account_id'=>$accountid,
						'portal' => 1,
						'support_start_date'=> date('Y-m-d'),
						'support_end_date'=> date('Y-m-d'),
						'roleid' => $_REQUEST['customerroleid'],
						'customer_language'=>$current_user->column_fields['language']);
	
	if (strstr($focus->column_fields['user_name'], '@'))
		$fields['email'] = $focus->column_fields['user_name'];
	
	$input_array = array(
				'module'		=> 'Contacts',
				'recordid'		=> $userid,
				'fields'		=> $fields,
			);
		
	$params = array('input_array'	=> $input_array,
					'customerid'	=> $customerid,
					'sessionid'		=> $sessionid,
					);
					
	$result = $client->call('saveRecord', $params, $Server_Path, $Server_Path);

	$userid = $result[0]['Contacts']['id'];
	
	if (!empty($userid)) {
		$_SESSION['customerid'] = $userid;
		$params = array('userid'	=> $userid,
					'customerid'	=> $customerid,
					'sessionid'		=> $sessionid,
					);
		$customerpass = $client->call('getCustomerPass', $params, $Server_Path, $Server_Path);
		
		$input_array = array(
				'userid'		=> $userid,
				'userpass'		=> $customerpass,
			);
			
		$query = "UPDATE vtiger_users SET customerid = ?, customerpass = ? WHERE id = ?";
		$adb->pquery($query,array($userid,$customerpass,$focus->id));		
	}
	
?>