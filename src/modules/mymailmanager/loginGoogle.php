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
 
 echo "entra en loginGoogle";
 global $site_URL,$adb;
 
 require_once 'include/google-api-php-client-master/src/Google/autoload.php';
 require_once 'include/google-api-php-client-master/src/Google/Service/Gmail.php';
// /************************************************
  // ATTENTION: Fill in these values! Make sure
  // the redirect URI is to this page, e.g:
  // http://localhost:8080/user-example.php
 // ************************************************/
$client_id = '325440880344-q8rse9bpd46b6lp9n9es8e15victokal.apps.googleusercontent.com';
$client_secret = 'l5O8bUxGlJSC27WpBDl56AVt';
 $redirect_uri = $site_URL.'index.php?action=index&module=mymailmanager';


 $client = new Google_Client();
 $client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setAccessType('offline');
// //$client->setDeveloperKey("AIzaSyCT40bq9NUmYuFgrkXQMR1CDfnI4FQPgl4");
 define('SCOPES', implode(' ', array(
   "https://www.googleapis.com/auth/gmail.modify")
 ));
 $client->setScopes(SCOPES);
 $client->setIncludeGrantedScopes(true);




// //$client->setScopes('email');

  // //$_SESSION['access_token'] = $client->getAccessToken();


// /************************************************
  // If we have a code back from the OAuth 2.0 flow,
  // we need to exchange that with the authenticate()
  // function. We store the resultant access token
  // bundle in the session, and redirect to ourself.
 // ************************************************/

  // //$_SESSION['access_token'] = $client->getAccessToken();

  // // Registrando calendario para el usuario



 if (isset($_GET['code'])) {
	 
	 echo "entro en if de code";
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
// // //$client = new Google_Client();
 $client->setApplicationName('devplatzilla-mymailmanager');
 $client->setAuthConfigFile('modules/mymailmanager/client_secret.json');
 $mensajes=array();
$service = new Google_Service_Gmail($client);
 
 





 $optParams = array();
                $optParams['maxResults'] = 20; // Return Only 5 Messages
                $optParams['labelIds'] = array('INBOX','CATEGORY_PERSONAL'); // Only show messages in Inbox
				
                $messages = $service->users_messages->listUsersMessages('me',$optParams);
                 $list = $messages->getMessages();
                 $messageId = $list[0]->getId(); // Grab first Message
					
				$inboxMessage=array();

                 $optParamsGet = array();
                 $optParamsGet['format'] = 'full'; // Display message in payload
				 //$optParamsGet['metadataHeaders']=array('From','Subject','To');
                $message = $service->users_messages->get('me',$messageId,$optParamsGet);
                $messagePayload = $message->getPayload();
                $headers = $message->getPayload()->getHeaders();
                $parts = $message->getPayload()->getParts();

                $body = $parts[0]['body'];
                $rawData = $body->data;
                $sanitizedData = strtr($rawData,'-_', '+/');
                $decodedMessage = base64_decode($sanitizedData);
				
				
				foreach($headers as $single) {

            if ($single->getName() == 'Subject') {

                $message_subject = $single->getValue();

            }

            else if ($single->getName() == 'Date') {

                $message_date = $single->getValue();
                $message_date = date('M jS Y h:i A', strtotime($message_date));
            }

            else if ($single->getName() == 'From') {

                $message_sender = $single->getValue();
                $message_sender = str_replace('"', '', $message_sender);
            }
        }


         // $inboxMessage[0] = [
            // 'messageId' => $message_id,
            // 'messageSnippet' => $snippet,
            // 'messageSubject' => $message_subject,
            // 'messageDate' => $message_date,
            // 'messageSender' => $message_sender
        // ];


  var_dump($decodedMessage);
echo $message_subject;
echo $message_date;
echo $message_sender;



 
  }

 
 if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
	 echo "aca entro que es if";
   $client->setAccessToken($_SESSION['access_token']);
  } else {
	 echo "manda a auntenticar";
    $authUrl = $client->createAuthUrl();
  }
  if (strpos($client_id, "googleusercontent") == false) {
	 echo "hizo exit";
   exit;
 }

  if (isset($authUrl)) {
	 echo "redirect";
    header('Location: ' .$authUrl);
 }

?>