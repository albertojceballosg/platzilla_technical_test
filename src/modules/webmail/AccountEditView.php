<?php
	require_once ('include/MailManager/PlatzillaMailManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	use League\OAuth2\Client\Token\AccessToken;
	use Platzilla\MailManager\Account\GenericAccount;
	use Platzilla\MailManager\MailException;
	use Platzilla\MailManager\Type\AuthenticationMethod;

	global $adb, $current_user, $webMailClient;

	$emailAddress = PlatzillaUtils::purify ($_GET, 'emailaddress');
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'AccountListView');
	$returnModule = PlatzillaUtils::purify ($_GET, 'return_module', 'webmail');

	try {
		if (isset ($_SESSION ['oauth2mailaccount'])) {
			$accountData = json_decode ($_SESSION ['oauth2mailaccount'], true);
			if (isset ($_SESSION ['oauth2user'])) {
				$accountData ['emailaddress'] = $_SESSION ['oauth2user'];
				unset ($_SESSION ['oauth2user']);
			}

			if (isset ($_SESSION ['oauth2token'])) {
				$accountData ['accesstoken'] = json_decode ($_SESSION ['oauth2token'], true);
				unset ($_SESSION ['oauth2token']);
			}
			$account = WebmailUtils::deserializeMailAccount ($accountData);

			if (isset ($_SESSION ['oauth2hostname'])) {
				unset ($_SESSION ['oauth2hostname']);
			}

			if (isset ($_SESSION ['oauth2redirectto'])) {
				unset ($_SESSION ['oauth2redirectto']);
			}

			unset ($_SESSION ['oauth2mailaccount']);
		} else if ((isset ($emailAddress)) && (!isset ($_GET ['reset']))) {
			$account = WebmailUtils::fetchMailAccount ($adb, $emailAddress, $current_user->id);
		} else {
			$account = null;
		}
	} catch (MailException $ignored) {
		// Do nothing
		$account = null;
	}

	if ($account instanceof GenericAccount) {
		$accessToken          = $account->getAccessToken ();
		$provider             = $account->getProvider ();
		$authenticationMethod = $provider->getIncomingAuthenticationMethod ();
		try {
			if ($authenticationMethod != AuthenticationMethod::OAUTH2) {
				$folders = WebmailUtils::getMailAccountSubscribedFolders ($account, $webMailClient ['encryptionkey']);
			} else if (($accessToken instanceof AccessToken) && ($accessToken->hasExpired ())) {
				$accessToken = OAuth2Utils::refreshToken ($adb, $provider->getIncomingHostName (), $accessToken);
				if (!($accessToken instanceof \League\OAuth2\Client\Token\AccessTokenInterface)) {
					throw new Exception ('Imposible obtener un token de acceso');
				}
				$account->setAccessToken ($accessToken);
				$folders = WebmailUtils::getMailAccountSubscribedFolders ($account, $webMailClient ['encryptionkey']);
			} else if (!empty ($accessToken)) {
				$folders = WebmailUtils::getMailAccountSubscribedFolders ($account, $webMailClient ['encryptionkey']);
			} else {
				$folders = null;
			}
		} catch (Exception $e) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
			);
			$folders                   = null;
			$account->setAccessToken (null);
		}
	} else {
		$folders = null;
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('ACCOUNT', $account);
	$smarty->assign ('AVAILABLE_FOLDERS', $folders);
	$smarty->assign ('CURRENT_USER_EMAIL_ADDRESS', $current_user->column_fields ['email1']);
	$smarty->assign ('RETURN_ACTION', $returnAction);
	$smarty->assign ('RETURN_MODULE', $returnModule);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/webmail/AccountEditView.tpl');
