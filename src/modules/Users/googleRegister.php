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
$redirect_uri = $site_URL.'index.php?module=Users&action=googleRegister';

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
  
	$content = file_get_contents($userData->picture);
	$temp_file = tempnam(sys_get_temp_dir(), 'img');
	$fp = fopen($temp_file, 'wb');
	fwrite($fp, $content);
	fclose($fp);
	$_REQUEST['email'] = $userData->email;
	$_FILES['logo']['tmp_name'] = $temp_file;
	$_REQUEST['company'] = $userData->name;
	$_REQUEST['password'] = 'admin';
  
	include_once 'modules/Users/crearAplicacion.php';
}

if (strpos($client_id, "googleusercontent") == false) {
  exit;
}

if (isset($authUrl)) {
  header('Location: ' .$authUrl);
}

?>