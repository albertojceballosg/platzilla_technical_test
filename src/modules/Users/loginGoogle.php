<?php
/*
 * Copyright 2011 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
global $site_URL;
 
require_once 'include/google-api-php-client-master/src/Google/autoload.php';
/************************************************
  ATTENTION: Fill in these values! Make sure
  the redirect URI is to this page, e.g:
  http://localhost:8080/user-example.php
 ************************************************/
$client_id = '968196546450-dabc8por92k9u5qv0dadbb60ftt58mop.apps.googleusercontent.com';
$client_secret = 'nZvP4I00arWOOSrGwS1xwyWA';
$redirect_uri = $site_URL.'index.php?module=Users&action=loginGoogle';

$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setScopes('profile email');

/************************************************
  If we have a code back from the OAuth 2.0 flow,
  we need to exchange that with the authenticate()
  function. We store the resultant access token
  bundle in the session, and redirect to ourself.
 ************************************************/
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}
/************************************************
  If we have an access token, we can make
  requests, else we generate an authentication URL.
 ************************************************/
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
} else {
  $authUrl = $client->createAuthUrl();
}
/************************************************
  If we're signed in we can go ahead and retrieve
  the ID token, which is part of the bundle of
  data that is exchange in the authenticate step
  - we only need to do a network call if we have
  to retrieve the Google certificate to verify it,
  and that can be cached.
 ************************************************/
if ($client->getAccessToken()) {
	$_SESSION['access_token'] = $client->getAccessToken();
	$token_data = $client->verifyIdToken()->getAttributes();

	//Send Client Request
	$objOAuthService = new Google_Service_Oauth2($client);
	$userData = $objOAuthService->userinfo->get();
 
	$adb = conectaPlataformaHija('platzilla');
	
	$sql = "SELECT code FROM vtiger_instanciaslogins INNER JOIN vtiger_instancias USING(instanciasid) 
			INNER JOIN vtiger_crmentity ON (crmid = vtiger_instancias.instanciasid AND deleted = 0)
			WHERE login = ?";
	$result = $adb->pquery($sql,array(to_html($userData->email)));

	if ($result && $adb->num_rows($result) > 0) {
		$plat = $adb->query_result($result,0,'code');
		$adb = conectaPlataformaHija($plat);
		$_SESSION['plat'] = $plat;
		
		$sql = "SELECT id FROM vtiger_users WHERE user_name = ?";
		$result = $adb->pquery($sql,array($userData->email));
		if ($result) {
			$row = $adb->fetchByAssoc($result);
			$_SESSION['authenticated_user_id'] = $row['id'];
		}
	
	  //Me conecto a esta plataforma

		$_SESSION['vtiger_authenticated_user_theme'] = 'centaurus';
		$_SESSION['app_unique_key'] = $application_unique_key;
		unset($_SESSION['briefing']);

		require_once('modules/Users/CreateUserPrivilegeFile.php');
		//Writing tab data in flat file
		create_tab_data_file();
		create_parenttab_data_file();

		createUserPrivilegesfile($_SESSION['authenticated_user_id']);
		createUserSharingPrivilegesfile($_SESSION['authenticated_user_id']);
	  
		header('Location: index.php?module=Home&action=index');
	} else {
		$_SESSION['login_error'] = $mod_strings['ERR_INVALID_PASSWORD'];
		
		// go back to the login screen.	
		// create an error message for the user.
		header("Location: index.php?module-Users-action-login");
	}
}

if (strpos($client_id, "googleusercontent") == false) {
  exit;
}

if (isset($authUrl)) {
  header('Location: ' .$authUrl);
}

?>