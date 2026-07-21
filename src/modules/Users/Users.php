<?php
	/*********************************************************************************
	 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
	 * ("License"); You may not use this file except in compliance with the
	 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
	 * Software distributed under the License is distributed on an  "AS IS"  basis,
	 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
	 * the specific language governing rights and limitations under the License.
	 * The Original Code is:  SugarCRM Open Source
	 * The Initial Developer of the Original Code is SugarCRM, Inc.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________.
	 ********************************************************************************/

	/*********************************************
	 * With modifications by
	 * Daniel Jabbour
	 * iWebPress Incorporated, www.iwebpress.com
	 * djabbour - a t - iwebpress - d o t - com
	 ********************************************/

	/*********************************************************************************
	 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Users/Users.php,v 1.10 2005/04/19 14:40:48 ray Exp $
	 * Description: TODO:  To be written.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 ********************************************************************************/

	require_once ('include/logging.php');
	require_once ('include/database/PearDatabase.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once 'data/CRMEntity.php';
	require_once ('modules/Calendar/Activity.php');
	require_once ('data/Tracker.php');
	require_once 'include/utils/CommonUtils.php';
	require_once 'include/Webservices/Utils.php';
	require_once ('modules/Users/UserTimeZonesArray.php');

// User is used to store customer information.
	/** Main class for the user module
	 *
	 */
	class Users extends CRMEntity {
		var $log;
		/**
		 * @var PearDatabase
		 */
		var $db;
		// Stored fields
		var $id;
		var $authenticated = false;
		var $error_string;
		var $is_admin;
		var $deleted;

		var $tab_name       = Array ('vtiger_users', 'vtiger_attachments', 'vtiger_user2role');
		var $tab_name_index = Array ('vtiger_users' => 'id', 'vtiger_attachments' => 'attachmentsid', 'vtiger_user2role' => 'userid');

		var $table_name  = "vtiger_users";
		var $table_index = 'id';

		// This is the list of fields that are in the lists.
		var $list_link_field = 'last_name';

		var $list_mode;
		var $popup_type;

		var $search_fields      = Array (
			'Name'   => Array ('vtiger_users' => 'last_name'),
			'Email'  => Array ('vtiger_users' => 'email1'),
			'Email2' => Array ('vtiger_users' => 'email2'),
		);
		var $search_fields_name = Array (
			'Name'   => 'last_name',
			'Email'  => 'email1',
			'Email2' => 'email2',
		);

		var $module_name = "Users";

		var $object_name     = "User";
		var $user_preferences;
		var $homeorder_array = array ('HDB', 'ALVT', 'PLVT', 'QLTQ', 'CVLVT', 'HLT', 'GRT', 'OLTSO', 'ILTI', 'MNL', 'OLTPO', 'LTFAQ', 'UA', 'PA');

		var $encodeFields = Array ("first_name", "last_name", "description");

		// This is used to retrieve related fields from form posts.
		var $additional_column_fields = Array ('reports_to_name');

		var $sortby_fields = Array ('status', 'email1', 'email2', 'phone_work', 'is_admin', 'user_name', 'last_name');

		// This is the list of vtiger_fields that are in the lists.
		var $list_fields      = Array (
			'First Name' => Array ('vtiger_users' => 'first_name'),
			'Last Name'  => Array ('vtiger_users' => 'last_name'),
			'Role Name'  => Array ('vtiger_user2role' => 'roleid'),
			'User Name'  => Array ('vtiger_users' => 'user_name'),
			'Status'     => Array ('vtiger_users' => 'status'),
			'Email'      => Array ('vtiger_users' => 'email1'),
			'Email2'     => Array ('vtiger_users' => 'email2'),
			'Admin'      => Array ('vtiger_users' => 'is_admin'),
			'Phone'      => Array ('vtiger_users' => 'phone_work'),
		);
		var $list_fields_name = Array (
			'Last Name'  => 'last_name',
			'First Name' => 'first_name',
			'Role Name'  => 'roleid',
			'User Name'  => 'user_name',
			'Status'     => 'status',
			'Email'      => 'email1',
			'Email2'     => 'email2',
			'Admin'      => 'is_admin',
			'Phone'      => 'phone_work',
		);

		//Default Fields for Email Templates -- Pavani
		var $emailTemplate_defaultFields = array ('first_name', 'last_name', 'title', 'department', 'phone_home', 'phone_mobile', 'signature', 'email1', 'email2', 'address_street', 'address_city', 'address_state', 'address_country', 'address_postalcode');

		var $popup_fields = array ('last_name');

		// This is the list of fields that are in the lists.
		var $default_order_by   = "user_name";
		var $default_sort_order = 'ASC';

		var $record_id;
		var $new_schema = true;

		var $DEFAULT_PASSWORD_CRYPT_TYPE; //'BLOWFISH', /* before PHP5.3*/ MD5;

		/** @var null|string */
		public $defaultOperating   = null;
		
		/** @var null|string */
		public $defaultHomeTab   = null;

		/** @var null|string */
		public $defaultOperationLabel = null;

		/** constructor function for the main user class
		 * instantiates the Logger class and PearDatabase Class
		 *
		 */

		function Users () {
			$this->log = LoggerManager::getLogger ('user');
			$this->log->debug ("Entering Users() method ...");
			$this->db                               = PearDatabase::getInstance ();
			$this->DEFAULT_PASSWORD_CRYPT_TYPE      = (version_compare (PHP_VERSION, '5.3.0') >= 0) ?
				'PHP5.3MD5' : 'MD5';
			$this->column_fields                    = getColumnFields ('Users');
			$this->column_fields['ccurrency_name']  = '';
			$this->column_fields['currency_code']   = '';
			$this->column_fields['currency_symbol'] = '';
			$this->column_fields['conv_rate']       = '';
			$this->log->debug ("Exiting Users() method ...");
		}

		// Mike Crowe Mod --------------------------------------------------------Default ordering for us
		/**
		 * Function to get sort order
		 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
		 */
		function getSortOrder () {
			global $log;
			$log->debug ("Entering getSortOrder() method ...");
			if (isset($_REQUEST['sorder'])) {
				$sorder = $this->db->sql_escape_string ($_REQUEST['sorder']);
			} else {
				$sorder = (($_SESSION['USERS_SORT_ORDER'] != '') ? ($_SESSION['USERS_SORT_ORDER']) : ($this->default_sort_order));
			}
			$log->debug ("Exiting getSortOrder method ...");
			return $sorder;
		}

		/**
		 * Function to get order by
		 * return string  $order_by    - fieldname(eg: 'subject')
		 */
		function getOrderBy () {
			global $log;
			$log->debug ("Entering getOrderBy() method ...");

			$use_default_order_by = '';
			if (PerformancePrefs::getBoolean ('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_order_by = $this->default_order_by;
			}

			if (isset($_REQUEST['order_by'])) {
				$order_by = $this->db->sql_escape_string ($_REQUEST['order_by']);
			} else {
				$order_by = (($_SESSION['USERS_ORDER_BY'] != '') ? ($_SESSION['USERS_ORDER_BY']) : ($use_default_order_by));
			}
			$log->debug ("Exiting getOrderBy method ...");
			return $order_by;
		}
		// Mike Crowe Mod --------------------------------------------------------

		/** Function to set the user preferences in the session
		 *
		 * @param $name -- name:: Type varchar
		 * @param $value -- value:: Type varchar
		 *
		 */
		function setPreference ($name, $value) {
			if (!isset($this->user_preferences)) {
				if (isset($_SESSION["USER_PREFERENCES"])) {
					$this->user_preferences = $_SESSION["USER_PREFERENCES"];
				} else {
					$this->user_preferences = array ();
				}
			}
			if (!array_key_exists ($name, $this->user_preferences) || $this->user_preferences[ $name ] != $value) {
				$this->log->debug ("Saving To Preferences:" . $name . "=" . $value);
				$this->user_preferences[ $name ] = $value;
				$this->savePreferecesToDB ();
			}
			$_SESSION[ $name ] = $value;
		}

		/** Function to save the user preferences to db
		 *
		 */

		function savePreferecesToDB () {
			$data   = base64_encode (serialize ($this->user_preferences));
			$query  = "UPDATE $this->table_name SET user_preferences=? where id=?";
			$result = $this->db->pquery ($query, array ($data, $this->id));
			$this->log->debug ("SAVING: PREFERENCES SIZE " . strlen ($data) . "ROWS AFFECTED WHILE UPDATING USER PREFERENCES:" . $this->db->getAffectedRowCount ($result));
			$_SESSION["USER_PREFERENCES"] = $this->user_preferences;
		}

		/** Function to load the user preferences from db
		 *
		 */
		function loadPreferencesFromDB ($value) {

			if (isset($value) && !empty($value)) {
				$this->log->debug ("LOADING :PREFERENCES SIZE " . strlen ($value));
				$this->user_preferences = unserialize (base64_decode ($value));
				$_SESSION               = array_merge ($this->user_preferences, $_SESSION);
				$this->log->debug ("Finished Loading");
				$_SESSION["USER_PREFERENCES"] = $this->user_preferences;
			}
		}

		/**
		 * @return string encrypted password for storage in DB and comparison against DB password.
		 *
		 * @param string $user_name - Must be non null and at least 2 characters
		 * @param string $user_password - Must be non null and at least 1 character.
		 *
		 * @desc Take an unencrypted username and password and return the encrypted password
		 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
		 * All Rights Reserved..
		 * Contributor(s): ______________________________________..
		 */
		function encrypt_password ($user_password, $crypt_type = '') {
			// encrypt the password.
			$salt = substr ($this->column_fields["user_name"], 0, 2);

			// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4923
			if ($crypt_type == '') {
				// Try to get the crypt_type which is in database for the user
				$crypt_type = $this->get_user_crypt_type ();
			}

			// For more details on salt format look at: http://in.php.net/crypt
			if ($crypt_type == 'MD5') {
				$salt = '$1$' . $salt . '$';
			} elseif ($crypt_type == 'BLOWFISH') {
				$salt = '$2$' . $salt . '$';
			} elseif ($crypt_type == 'PHP5.3MD5') {
				//only change salt for php 5.3 or higher version for backward
				//compactibility.
				//crypt API is lot stricter in taking the value for salt.
				$salt = '$1$' . str_pad ($salt, 9, '0');
			}

			$encrypted_password = crypt ($user_password, $salt);
			return $encrypted_password;
		}

		/** Function to authenticate the current user with the given password
		 *
		 * @param $password -- password::Type varchar
		 *
		 * @returns true if authenticated or false if not authenticated
		 */
		function authenticate_user ($password) {
			$usr_name = $this->column_fields["user_name"];

			$query  = "SELECT * from $this->table_name where user_name=? AND user_hash=?";
			$params = array ($usr_name, $password);
			$result = $this->db->requirePsSingleResult ($query, $params, false);

			if (empty($result)) {
				$this->log->fatal ("SECURITY: failed login by $usr_name");
				return false;
			}

			return true;
		}

		/** Function for validation check
		 *
		 */
		function validation_check ($validate, $md5, $alt = '') {
			$validate = base64_decode ($validate);
			if (file_exists ($validate) && $handle = fopen ($validate, 'rb', true)) {
				$buffer = fread ($handle, filesize ($validate));
				if (md5 ($buffer) == $md5 || (!empty($alt) && md5 ($buffer) == $alt)) {
					return 1;
				}
				return -1;
			} else {
				return -1;
			}
		}

		/** Function for authorization check
		 *
		 */
		function authorization_check ($validate, $authkey, $i) {
			$validate = base64_decode ($validate);
			$authkey  = base64_decode ($authkey);
			if (file_exists ($validate) && $handle = fopen ($validate, 'rb', true)) {
				$buffer = fread ($handle, filesize ($validate));
				if (substr_count ($buffer, $authkey) < $i) {
					return -1;
				}
			} else {
				return -1;
			}
		}

		/**
		 * Checks the config.php AUTHCFG value for login type and forks off to the proper module
		 *
		 * @param string $user_password - The password of the user to authenticate
		 *
		 * @return true if the user is authenticated, false otherwise
		 */
		function doLogin ($user_password, $loginToken = null, &$usr_name = null) {

			//echo "entrando doLogin ".$AUTHCFG['authType']." <br>";
			global $AUTHCFG;
			$usr_name = $this->column_fields["user_name"];

			switch (strtoupper ($AUTHCFG['authType'])) {
				case 'LDAP':
					$this->log->debug ("Using LDAP authentication");
					require_once ('modules/Users/authTypes/LDAP.php');
					$result = ldapAuthenticate ($this->column_fields["user_name"], $user_password);
					if ($result == null) {
						return false;
					} else {
						return true;
					}
					break;

				case 'AD':
					$this->log->debug ("Using Active Directory authentication");
					require_once ('modules/Users/authTypes/adLDAP.php');
					$adldap = new adLDAP();
					if ($adldap->authenticate ($this->column_fields["user_name"], $user_password)) {
						return true;
					} else {
						return false;
					}
					break;

				default:
					if ($loginToken) {
						$result = $this->db->requirePsSingleResult ("SELECT vtiger_logintoken.*, user_name FROM vtiger_logintoken INNER JOIN vtiger_users ON (vtiger_users.id = vtiger_logintoken.loggeduserid) WHERE gentime+60*60*duration_hrs > ? AND used=0 AND token=?", array (time (), $loginToken), false);

						if (empty($result)) {
							return false;
						}
						$usr_name = $this->db->query_result ($result, 0, 'user_name');
						$tokenid  = $this->db->query_result ($result, 0, 'id');

						$this->db->requirePsSingleResult ("UPDATE vtiger_logintoken SET used=1 WHERE id=?", array ($tokenid), false);

						return !empty($usr_name);
					}

					//echo "la bd actual es ". $this->db->getDataSourceName();

					$this->log->debug ("Using integrated/SQL authentication");
					$query  = "SELECT crypt_type FROM $this->table_name WHERE user_name=?";
					$result = $this->db->requirePsSingleResult ($query, array ($usr_name), false);
					if (empty($result)) {
						return false;
					}
					$crypt_type         = $this->db->query_result ($result, 0, 'crypt_type');
					$encrypted_password = $this->encrypt_password ($user_password, $crypt_type);
					$query              = "SELECT * from $this->table_name where user_name=? AND user_password=?";
//		var_dump($encrypted_password);
//		die();
					$result = $this->db->requirePsSingleResult ($query, array ($usr_name, $encrypted_password), false);

					$securityString   = serialize (serialize ($usr_name) . "|" . serialize ($user_password) . "|" . serialize ("0"));
					$cadenaEncryptada = $this->encrypt ($securityString, "estaeslaclave01EncryptadaDeEnacol");

					$_SESSION['VLK'] = $cadenaEncryptada;
					if (empty($result)) {
						unset($_SESSION['VLK']);
						return false;
					} else {
						return true;
					}
					break;
			}
			return false;
		}

		/**
		 * Load a user based on the user_name in $this
		 * @return -- this if load was successul and null if load failed.
		 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
		 * All Rights Reserved..
		 * Contributor(s): ______________________________________..
		 */
		function load_user ($user_password, $loginToken = null) {
			global $gInstanciaEmpresaFacil;

			$usr_name = $this->column_fields["user_name"];
			if (isset($_SESSION['loginattempts'])) {
				$_SESSION['loginattempts'] += 1;
			} else {
				$_SESSION['loginattempts'] = 1;
			}
			if ($_SESSION['loginattempts'] > 5) {
				$this->log->warn ("SECURITY: " . $usr_name . " has attempted to login " . $_SESSION['loginattempts'] . " times.");
			}
			$this->log->debug ("Starting user load for $usr_name");

			if ((!isset($this->column_fields["user_name"]) || $this->column_fields["user_name"] == "" || !isset($user_password) || $user_password == "") && empty($loginToken)) {
				return null;
			}

			$authCheck = false;
			$authCheck = $this->doLogin ($user_password, $loginToken, $usr_name);

			if (!$authCheck) {
				$this->log->warn ("User authentication for $usr_name failed");
				return null;
			}

			// Get the fields for the user
			$query  = "SELECT * from $this->table_name where user_name='$usr_name'";
			$result = $this->db->requireSingleResult ($query, false);

			$row                 = $this->db->fetchByAssoc ($result);
			$this->column_fields = $row;
			$this->id            = $row['id'];

			$user_hash = strtolower (md5 ($user_password));

			// If there is no user_hash is not present or is out of date, then create a new one.
			if ((!isset($row['user_hash']) || $row['user_hash'] != $user_hash) && empty($loginToken)) {
				$query = "UPDATE $this->table_name SET user_hash=? where id=?";
				$this->db->pquery ($query, array ($user_hash, $row['id']), true, "Error setting new hash for {$row['user_name']}: ");
			}
			$this->loadPreferencesFromDB ($row['user_preferences']);

			if ($row['status'] != "Inactive") {
				$this->authenticated = true;
			}

			//Se carga la informaci�n de la vista de cliente
			$query  = "SELECT customerid FROM vtiger_users WHERE id = ?";
			$result = $this->db->pquery ($query, array ($row['id']));

			if ($result) {
				$_SESSION['customerid'] = $this->db->query_result ($result, 0, 'customerid');
			}

			// EGC Permisos para soporte autom�tico (solo para admin)
			if (empty($_SESSION['customerid']) && $gInstanciaEmpresaFacil && $row['id'] == '1') {
				require_once 'include/utils/UserInfoUtil.php';
				$roles  = getSupportRoles ();
				$roleid = '';
				foreach ($roles as $role) {
					$roleid = $role['roleid'];
					if ($role['rolename'] == 'Customer') {
						break;
					}
				}
				if (!empty($roleid)) {
					$idbak                      = @$_REQUEST['id'];
					$_REQUEST['id']             = $row['id'];
					$_REQUEST['customerroleid'] = $roleid;
					require_once 'customerRole.php';
					if ($userid) {
						$_SESSION['customerid'] = $userid;
					}

					$_REQUEST['id'] = $idbak;
				}
			}

			unset($_SESSION['loginattempts']);
			return $this;
		}

		/**
		 * Get crypt type to use for password for the user.
		 * Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4923
		 */
		function get_user_crypt_type () {

			$crypt_res  = null;
			$crypt_type = $this->DEFAULT_PASSWORD_CRYPT_TYPE;

			// For backward compatability, we need to make sure to handle this case.
			global $adb;
			$table_cols = $adb->getColumnNames ("vtiger_users");
			if (!in_array ("crypt_type", $table_cols)) {
				return $crypt_type;
			}

			if (isset($this->id)) {
				// Get the type of crypt used on password before actual comparision
				$qcrypt_sql = "SELECT crypt_type from $this->table_name where id=?";
				$crypt_res  = $this->db->pquery ($qcrypt_sql, array ($this->id), true);
			} else if (isset($this->column_fields["user_name"])) {
				$qcrypt_sql = "SELECT crypt_type from $this->table_name where user_name=?";
				$crypt_res  = $this->db->pquery ($qcrypt_sql, array ($this->column_fields["user_name"]));
			} else {
				$crypt_type = $this->DEFAULT_PASSWORD_CRYPT_TYPE;
			}

			if ($crypt_res && $this->db->num_rows ($crypt_res)) {
				$crypt_row  = $this->db->fetchByAssoc ($crypt_res);
				$crypt_type = $crypt_row['crypt_type'];
			}
			return $crypt_type;
		}

		/**
		 * @param string $user name - Must be non null and at least 1 character.
		 * @param string $user_password - Must be non null and at least 1 character.
		 * @param string $new_password - Must be non null and at least 1 character.
		 *
		 * @return boolean - If passwords pass verification and query succeeds, return true, else return false.
		 * @desc Verify that the current password is correct and write the new password to the DB.
		 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
		 * All Rights Reserved..
		 * Contributor(s): ______________________________________..
		 */
		function change_password ($user_password, $new_password, $dieOnError = true, $iscontact = false) {

			$usr_name = $this->column_fields["user_name"];
			global $mod_strings;
			global $current_user;
			$this->log->debug ("Starting password change for $usr_name");

			$lstRoles = explode (',', obtenerValorVariable ('ROLES_PANEL_USERS', 'Users'));

			if (!isset($new_password) || $new_password == "") {
				$this->error_string = $mod_strings['ERR_PASSWORD_CHANGE_FAILED_1'] . $user_name . $mod_strings['ERR_PASSWORD_CHANGE_FAILED_2'];
				return false;
			}

			/*
        if ((!is_admin($current_user) && !in_array($current_user->column_fields['roleid'],$lstRoles) ) && !$iscontact) {
            $this->db->startTransaction();
            if(!$this->verifyPassword($user_password)) {
                $this->log->warn("Incorrect old password for $usr_name");
                $this->error_string = $mod_strings['ERR_PASSWORD_INCORRECT_OLD'];
                return false;
            }
            if($this->db->hasFailedTransaction()) {
                if($dieOnError) {
                    die("error verifying old transaction[".$this->db->database->ErrorNo()."] ".
                            $this->db->database->ErrorMsg());
                }
                return false;
            }
        }*/

			$user_hash = strtolower (md5 ($new_password));

			//set new password
			$crypt_type             = $this->DEFAULT_PASSWORD_CRYPT_TYPE;
			$encrypted_new_password = $this->encrypt_password ($new_password, $crypt_type);

			$query = "UPDATE $this->table_name SET user_password=?, confirm_password=?, user_hash=?, " .
					 "crypt_type=? where id=?";
			$this->db->startTransaction ();
			$this->db->pquery ($query, array (
				$encrypted_new_password, $encrypted_new_password,
				$user_hash, $crypt_type, $this->id,
			));
			if ($this->db->hasFailedTransaction ()) {
				if ($dieOnError) {
					die("error setting new password: [" . $this->db->database->ErrorNo () . "] " .
						$this->db->database->ErrorMsg ());
				}
				return false;
			}
			return true;
		}

		function de_cryption ($data) {
			require_once ('include/utils/encryption.php');
			$de_crypt = new Encryption();
			if (isset($data)) {
				$decrypted_password = $de_crypt->decrypt ($data);
			}
			return $decrypted_password;
		}

		function changepassword ($newpassword) {
			require_once ('include/utils/encryption.php');
			$en_crypt = new Encryption();
			if (isset($newpassword)) {
				$encrypted_password = $en_crypt->encrypt ($newpassword);
			}

			return $encrypted_password;
		}

		function verifyPassword ($password) {
			$query  = "SELECT user_name,user_password,crypt_type FROM {$this->table_name} WHERE id=?";
			$result = $this->db->pquery ($query, array ($this->id));
			$row    = $this->db->fetchByAssoc ($result);
			$this->log->debug ("select old password query: $query");
			$this->log->debug ("return result of $row");
			$encryptedPassword = $this->encrypt_password ($password, $row['crypt_type']);
			if ($encryptedPassword != $row['user_password']) {
				return false;
			}
			return true;
		}

		function is_authenticated () {
			return $this->authenticated;
		}

		/** gives the user id for the specified user name
		 *
		 * @param $user_name -- user name:: Type varchar
		 *
		 * @returns integer user id
		 */
		function retrieve_user_id ($user_name) {
			global $adb;
			$query  = "SELECT id FROM vtiger_users WHERE user_name=? AND deleted=0";
			$result = $adb->pquery ($query, array ($user_name));
			$userid = $adb->query_result ($result, 0, 'id');
			return $userid;
		}

		/**
		 * @return -- returns a list of all users in the system.
		 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
		 * All Rights Reserved..
		 * Contributor(s): ______________________________________..
		 */
		function verify_data () {
			$usr_name = $this->column_fields["user_name"];
			global $mod_strings;

			$query     = "SELECT user_name FROM vtiger_users WHERE user_name=? AND id<>? AND deleted=0";
			$result    = $this->db->pquery ($query, array ($usr_name, $this->id), true, "Error selecting possible duplicate users: ");
			$dup_users = $this->db->fetchByAssoc ($result);

			$query      = "SELECT user_name FROM vtiger_users WHERE is_admin = 'on' AND deleted=0";
			$result     = $this->db->pquery ($query, array (), true, "Error selecting possible duplicate vtiger_users: ");
			$last_admin = $this->db->fetchByAssoc ($result);

			$this->log->debug ("last admin length: " . count ($last_admin));
			$this->log->debug ($last_admin['user_name'] . " == " . $usr_name);

			$verified = true;
			if ($dup_users != null) {
				$this->error_string .= $mod_strings['ERR_USER_NAME_EXISTS_1'] . $usr_name . '' . $mod_strings['ERR_USER_NAME_EXISTS_2'];
				$verified = false;
			}
			if (!isset($_REQUEST['is_admin']) &&
				count ($last_admin) == 1 &&
				$last_admin['user_name'] == $usr_name
			) {
				$this->log->debug ("last admin length: " . count ($last_admin));

				$this->error_string .= $mod_strings['ERR_LAST_ADMIN_1'] . $usr_name . $mod_strings['ERR_LAST_ADMIN_2'];
				$verified = false;
			}

			return $verified;
		}

		/** Function to return the column name array
		 *
		 */

		function getColumnNames_User () {

			$mergeflds = array (
				"FIRSTNAME", "LASTNAME", "USERNAME", "SECONDARYEMAIL", "TITLE", "OFFICEPHONE", "DEPARTMENT",
				"MOBILE", "OTHERPHONE", "FAX", "EMAIL",
				"HOMEPHONE", "OTHEREMAIL", "PRIMARYADDRESS",
				"CITY", "STATE", "POSTALCODE", "COUNTRY",
			);
			return $mergeflds;
		}

		function fill_in_additional_list_fields () {
			$this->fill_in_additional_detail_fields ();
		}

		function fill_in_additional_detail_fields () {
			$query  = "SELECT u1.first_name, u1.last_name FROM vtiger_users u1, vtiger_users u2 WHERE u1.id = u2.reports_to_id AND u2.id = ? AND u1.deleted=0";
			$result = $this->db->pquery ($query, array ($this->id), true, "Error filling in additional detail vtiger_fields");

			$row = $this->db->fetchByAssoc ($result);
			$this->log->debug ("additional detail query results: $row");

			if ($row != null) {
				$this->reports_to_name = stripslashes (getFullNameFromArray ('Users', $row));
			} else {
				$this->reports_to_name = '';
			}
		}

		/** Function to get the current user information from the user_privileges file
		 *
		 * @param $userid -- user id:: Type integer
		 *
		 * @returns user info in $this->column_fields array:: Type array
		 *
		 */

		function retrieveCurrentUserInfoFromFile ($userid) {
			$local_user     = new Users();
			$local_user->id = $userid;
			require ('user_privileges/user_privileges.php');
			foreach ($this->column_fields as $field => $value_iter) {
				if (isset($user_info[ $field ])) {
					$this->$field                  = $user_info[ $field ];
					$this->column_fields[ $field ] = $user_info[ $field ];
				}
			}
			$this->id = $userid;
			return $this;
		}

		/** Function to save the user information into the database
		 *
		 * @param $module -- module name:: Type varchar
		 *
		 */
		function saveentity ($module) {
			global $current_user;//$adb added by raju for mass mailing
			$insertion_mode = $this->mode;
			if (empty($this->column_fields['time_zone'])) {
				$dbDefaultTimeZone                = DateTimeField::getDBTimeZone ();
				$this->column_fields['time_zone'] = $dbDefaultTimeZone;
				$this->time_zone                  = $dbDefaultTimeZone;
			}
			if (empty($this->column_fields['currency_id'])) {
				$this->column_fields['currency_id'] = CurrencyField::getDBCurrencyId ();
			}
			if (empty($this->column_fields['date_format'])) {
				$this->column_fields['date_format'] = 'yyyy-mm-dd';
			}

			$this->db->println ("TRANS saveentity starts $module");
			$this->db->startTransaction ();
			foreach ($this->tab_name as $table_name) {
				if ($table_name == 'vtiger_attachments') {
					$this->insertIntoAttachment ($this->id, $module);
				} else {
					$this->insertIntoEntityTable ($table_name, $module);
				}
			}
			require_once ('modules/Users/CreateUserPrivilegeFile.php');
			createUserPrivilegesfile ($this->id);
			unset($_SESSION['next_reminder_interval']);
			unset($_SESSION['next_reminder_time']);
			if ($insertion_mode != 'edit') {
				$this->createAccessKey ();
			}
			$this->db->completeTransaction ();
			$this->db->println ("TRANS saveentity ends");
		}

		function createAccessKey () {
			global $adb, $log;

			$log->info ("Entering Into function createAccessKey()");
			$updateQuery  = "UPDATE vtiger_users SET accesskey=? WHERE id=?";
			$insertResult = $adb->pquery ($updateQuery, array (vtws_generateRandomAccessKey (16), $this->id));
			$log->info ("Exiting function createAccessKey()");
		}

		/** Function to insert values in the specifed table for the specified module
		 *
		 * @param $table_name -- table name:: Type varchar
		 * @param $module -- module:: Type varchar
		 */
		function insertIntoEntityTable ($table_name, $module) {
			global $log;
			$log->info ("function insertIntoEntityTable " . $module . ' vtiger_table name ' . $table_name);
			global $adb, $current_user;
			$insertion_mode = $this->mode;
			//Checkin whether an entry is already is present in the vtiger_table to update
			if ($insertion_mode == 'edit') {
				$check_query  = "SELECT * FROM " . $table_name . " WHERE " . $this->tab_name_index[ $table_name ] . "=?";
				$check_result = $this->db->pquery ($check_query, array ($this->id));

				$num_rows = $this->db->num_rows ($check_result);

				if ($num_rows <= 0) {
					$insertion_mode = '';
				}
			}

			// We will set the crypt_type based on the insertion_mode
			$crypt_type = '';

			if ($insertion_mode == 'edit') {
				$update        = '';
				$update_params = array ();
				$tabid         = getTabid ($module);
				$sql           = "SELECT * FROM vtiger_field WHERE tabid=? AND tablename=? AND displaytype IN (1,3) AND vtiger_field.presence IN (0,2)";
				$params        = array ($tabid, $table_name);
			} else {
				$column = $this->tab_name_index[ $table_name ];
				if ($column == 'id' && $table_name == 'vtiger_users') {
					$currentuser_id = $this->db->getUniqueID ("vtiger_users") ? : 1;
					$this->id       = $currentuser_id;
				}
				$qparams = array ($this->id);
				$tabid   = getTabid ($module);
				$sql     = "SELECT * FROM vtiger_field WHERE tabid=? AND tablename=? AND displaytype IN (1,3,4) AND vtiger_field.presence IN (0,2)";
				$params  = array ($tabid, $table_name);

				$crypt_type = $this->DEFAULT_PASSWORD_CRYPT_TYPE;
			}

			$result   = $this->db->pquery ($sql, $params);
			$noofrows = $this->db->num_rows ($result);
			for ($i = 0; $i < $noofrows; $i++) {
				$fieldname  = $this->db->query_result ($result, $i, "fieldname");
				$columname  = $this->db->query_result ($result, $i, "columnname");
				$uitype     = $this->db->query_result ($result, $i, "uitype");
				$typeofdata = $adb->query_result ($result, $i, "typeofdata");

				$typeofdata_array = explode ("~", $typeofdata);
				$datatype         = $typeofdata_array[0];

				if (isset($this->column_fields[ $fieldname ])) {
					if ($uitype == 56) {
						if ($this->column_fields[ $fieldname ] === 'on' || $this->column_fields[ $fieldname ] == 1) {
							$fldvalue = 1;
						} else {
							$fldvalue = 0;
						}
					} elseif ($uitype == 15) {
						if ($this->column_fields[ $fieldname ] == $app_strings['LBL_NOT_ACCESSIBLE']) {
							//If the value in the request is Not Accessible for a picklist, the existing value will be replaced instead of Not Accessible value.
							$sql      = "select $columname from  $table_name where " . $this->tab_name_index[ $table_name ] . "=?";
							$res      = $adb->pquery ($sql, array ($this->id));
							$pick_val = $adb->query_result ($res, 0, $columname);
							$fldvalue = $pick_val;
						} else {
							$fldvalue = $this->column_fields[ $fieldname ];
						}
					} elseif ($uitype == 33) {
						if (is_array ($this->column_fields[ $fieldname ])) {
							$field_list = implode (' |##| ', $this->column_fields[ $fieldname ]);
						} else {
							$field_list = $this->column_fields[ $fieldname ];
						}
						$fldvalue = $field_list;
					} elseif ($uitype == 99) {
						$fldvalue = $this->encrypt_password ($this->column_fields[ $fieldname ], $crypt_type);
					} else {
						$fldvalue = $this->column_fields[ $fieldname ];
						$fldvalue = stripslashes ($fldvalue);
					}
					$fldvalue = from_html ($fldvalue, ($insertion_mode == 'edit') ? true : false);
				} else {
					$fldvalue = '';
				}
				if ($uitype == 31) {
					$themeList = get_themes ();
					if (!in_array ($fldvalue, $themeList) || $fldvalue == '') {
						global $default_theme;
						if (!empty($default_theme) && in_array ($default_theme, $themeList)) {
							$fldvalue = $default_theme;
						} else {
							$fldvalue = $themeList[0];
						}
					}
					if ($current_user->id == $this->id) {
						$_SESSION['vtiger_authenticated_user_theme'] = $fldvalue;
					}
				} elseif ($uitype == 32) {
					$languageList = Vtiger_Language::getAll ();
					$languageList = array_keys ($languageList);
					if (!in_array ($fldvalue, $languageList) || $fldvalue == '') {
						global $default_language;
						if (!empty($default_language) && in_array ($default_language, $languageList)) {
							$fldvalue = $default_language;
						} else {
							$fldvalue = $languageList[0];
						}
					}
					if ($current_user->id == $this->id) {
						$_SESSION['authenticated_user_language'] = $fldvalue;
					}
				}
				if ($fldvalue == '') {
					$fldvalue = $this->get_column_value ($columname, $fldvalue, $fieldname, $uitype, $datatype);
					//$fldvalue =null;
				}
				if ($insertion_mode == 'edit') {
					if ($i == 0) {
						$update = $columname . "=?";
					} else {
						$update .= ', ' . $columname . "=?";
					}
					array_push ($update_params, $fldvalue);
				} else {
					$column .= ", " . $columname;
					array_push ($qparams, $fldvalue);
				}
			}

			if ($insertion_mode == 'edit') {
				//Check done by Don. If update is empty the the query fails
				if (trim ($update) != '') {
					$sql1 = "update $table_name set $update where " . $this->tab_name_index[ $table_name ] . "=?";
					array_push ($update_params, $this->id);
					$this->db->pquery ($sql1, $update_params);
				}
			} else {
				// Set the crypt_type being used, to override the DB default constraint as it is not in vtiger_field
				if ($table_name == 'vtiger_users' && strpos ('crypt_type', $column) === false) {
					$column .= ', crypt_type';
					$qparams[] = $crypt_type;
				}
				// END
				$sql1 = "insert into $table_name ($column) values(" . generateQuestionMarks ($qparams) . ")";
				$this->db->pquery ($sql1, $qparams);
			}
		}

		/** Function to insert values into the attachment table
		 *
		 * @param $id -- entity id:: Type integer
		 * @param $module -- module:: Type varchar
		 */
		function insertIntoAttachment ($id, $module) {
			global $log;
			$log->debug ("Entering into insertIntoAttachment($id,$module) method.");

			foreach ($_FILES as $fileindex => $files) {
				if ($files['name'] != '' && $files['size'] > 0) {
					$files['original_name'] = vtlib_purify ($_REQUEST[ $fileindex . '_hidden' ]);
					$this->uploadAndSaveFile ($id, $module, $files);
				}
			}

			$log->debug ("Exiting from insertIntoAttachment($id,$module) method.");
		}

		/** Function to retreive the user info of the specifed user id The user info will be available in $this->column_fields array
		 *
		 * @param $record -- record id:: Type integer
		 * @param $module -- module:: Type varchar
		 */
		function retrieve_entity_info ($record, $module) {
			global $adb, $log;
			$log->debug ("Entering into retrieve_entity_info($record, $module) method.");

			if ($record == '') {
				$log->debug ("record is empty. returning null");
				return null;
			}

			$result = Array ();
			foreach ($this->tab_name_index as $table_name => $index) {
				$result[ $table_name ] = $adb->pquery ("SELECT * FROM " . $table_name . " WHERE " . $index . "=?", array ($record));
			}
			$tabid    = getTabid ($module);
			$sql1     = "SELECT * FROM vtiger_field WHERE tabid=? AND vtiger_field.presence IN (0,2)";
			$result1  = $adb->pquery ($sql1, array ($tabid));
			$noofrows = $adb->num_rows ($result1);
			for ($i = 0; $i < $noofrows; $i++) {
				$fieldcolname = $adb->query_result ($result1, $i, "columnname");
				$tablename    = $adb->query_result ($result1, $i, "tablename");
				$fieldname    = $adb->query_result ($result1, $i, "fieldname");

				$fld_value                         = $adb->query_result ($result[ $tablename ], 0, $fieldcolname);
				$this->column_fields[ $fieldname ] = $fld_value;
				$this->$fieldname                  = $fld_value;
			}
			$this->column_fields["record_id"]     = $record;
			$this->column_fields["record_module"] = $module;

			$currency_query  = "SELECT * FROM vtiger_currency_info WHERE id=? AND currency_status='Active' AND deleted=0";
			$currency_result = $adb->pquery ($currency_query, array ($this->column_fields["currency_id"]));
			if ($adb->num_rows ($currency_result) == 0) {
				$currency_query  = "SELECT * FROM vtiger_currency_info WHERE id =1";
				$currency_result = $adb->pquery ($currency_query, array ());
			}
			$currency_array = array ("$" => "&#36;", "&euro;" => "&#8364;", "&pound;" => "&#163;", "&yen;" => "&#165;");
			$ui_curr        = $currency_array[ $adb->query_result ($currency_result, 0, "currency_symbol") ];
			if ($ui_curr == "") {
				$ui_curr = $adb->query_result ($currency_result, 0, "currency_symbol");
			}
			$this->column_fields["currency_name"]   = $this->currency_name = $adb->query_result ($currency_result, 0, "currency_name");
			$this->column_fields["currency_code"]   = $this->currency_code = $adb->query_result ($currency_result, 0, "currency_code");
			$this->column_fields["currency_symbol"] = $this->currency_symbol = $ui_curr;
			$this->column_fields["conv_rate"]       = $this->conv_rate = $adb->query_result ($currency_result, 0, "conversion_rate");

			// TODO - This needs to be cleaned up once default values for fields are picked up in a cleaner way.
			// This is just a quick fix to ensure things doesn't start breaking when the user currency configuration is missing
			if ($this->column_fields['currency_grouping_pattern'] == ''
				&& $this->column_fields['currency_symbol_placement'] == ''
			) {

				$this->column_fields['currency_grouping_pattern']   = $this->currency_grouping_pattern = '123,456,789';
				$this->column_fields['currency_decimal_separator']  = $this->currency_decimal_separator = '.';
				$this->column_fields['currency_grouping_separator'] = $this->currency_grouping_separator = ',';
				$this->column_fields['currency_symbol_placement']   = $this->currency_symbol_placement = '$1.0';
			}

			// TODO - This needs to be cleaned up once default values for fields are picked up in a cleaner way.
			// This is just a quick fix to ensure things doesn't start breaking when the user currency configuration is missing
			if ($this->column_fields['currency_grouping_pattern'] == ''
				&& $this->column_fields['currency_symbol_placement'] == ''
			) {

				$this->column_fields['currency_grouping_pattern']   = $this->currency_grouping_pattern = '123,456,789';
				$this->column_fields['currency_decimal_separator']  = $this->currency_decimal_separator = '.';
				$this->column_fields['currency_grouping_separator'] = $this->currency_grouping_separator = ',';
				$this->column_fields['currency_symbol_placement']   = $this->currency_symbol_placement = '$1.0';
			}

			$this->id = $record;
			$log->debug ("Exit from retrieve_entity_info($record, $module) method.");

			return $this;
		}

		/** Function to upload the file to the server and add the file details in the attachments table
		 *
		 * @param $id -- user id:: Type varchar
		 * @param $module -- module name:: Type varchar
		 * @param $file_details -- file details array:: Type array
		 */
		function uploadAndSaveFile ($id, $module, $file_details) {
			global $log;
			$log->debug ("Entering into uploadAndSaveFile($id,$module,$file_details) method.");

			global $current_user;
			global $upload_badext;

			$date_var = date ('Y-m-d H:i:s');

			//to get the owner id
			$ownerid = $this->column_fields['assigned_user_id'];
			if (!isset($ownerid) || $ownerid == '') {
				$ownerid = $current_user->id;
			}

			$file    = $file_details['name'];
			$binFile = sanitizeUploadFileName ($file, $upload_badext);

			$filename     = ltrim (basename (" " . $binFile)); //allowed filename like UTF-8 characters
			$filetype     = $file_details['type'];
			$filesize     = $file_details['size'];
			$filetmp_name = $file_details['tmp_name'];

			$current_id = $this->db->getUniqueID ("vtiger_crmentity");

			//get the file path inwhich folder we want to upload the file
			$upload_file_path = decideFilePath ();
			//upload the file in server
			$upload_status = move_uploaded_file ($filetmp_name, $upload_file_path . $current_id . "_" . $binFile);

			$save_file = 'true';
			//only images are allowed for these modules
			if ($module == 'Users') {
				$save_file = validateImageFile ($file_details);
			}
			if ($save_file == 'true') {

				$sql1    = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES(?,?,?,?,?,?,?)";
				$params1 = array ($current_id, $current_user->id, $ownerid, $module . " Attachment", $this->column_fields['description'], $this->db->formatString ("vtiger_crmentity", "createdtime", $date_var), $this->db->formatDate ($date_var, true));
				$this->db->pquery ($sql1, $params1);

				$sql2    = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path) VALUES(?,?,?,?,?)";
				$params2 = array ($current_id, $filename, $this->column_fields['description'], $filetype, $upload_file_path);
				$result  = $this->db->pquery ($sql2, $params2);

				if ($id != '') {
					$delquery = 'DELETE FROM vtiger_salesmanattachmentsrel WHERE smid = ?';
					$this->db->pquery ($delquery, array ($id));
				}

				$sql3 = 'INSERT INTO vtiger_salesmanattachmentsrel VALUES(?,?)';
				$this->db->pquery ($sql3, array ($id, $current_id));

				//we should update the imagename in the users table
				$this->db->pquery ("UPDATE vtiger_users SET imagename=? WHERE id=?", array ($filename, $id));
			} else {
				$log->debug ("Skip the save attachment process.");
			}
			$log->debug ("Exiting from uploadAndSaveFile($id,$module,$file_details) method.");

			return;
		}

		/** Function to save the user information into the database
		 *
		 * @param $module -- module name:: Type varchar
		 *
		 */
		function save ($module_name) {
			global $log, $adb;
			//Save entity being called with the modulename as parameter
			$this->saveentity ($module_name);

			// Added for Reminder Popup support
			$query_prev_interval    = $adb->pquery ("SELECT reminder_interval FROM vtiger_users WHERE id=?",
				array ($this->id));
			$prev_reminder_interval = $adb->query_result ($query_prev_interval, 0, 'reminder_interval');

			//$focus->imagename = $image_upload_array['imagename'];
			//$this->saveHomeStuffOrder($this->id);
			// SaveTagCloudView($this->id);

			// Added for Reminder Popup support
			$this->resetReminderInterval ($prev_reminder_interval);
			//Creating the Privileges Flat File
			if (isset($this->column_fields['roleid'])) {
				updateUser2RoleMapping ($this->column_fields['roleid'], $this->id);
			}
			require_once ('modules/Users/CreateUserPrivilegeFile.php');
			createUserPrivilegesfile ($this->id);
			createUserSharingPrivilegesfile ($this->id);
		}

		/**
		 * gives the order in which the modules have to be displayed in the home page for the specified user id
		 *
		 * @param $id -- user id:: Type integer
		 *
		 * @returns the customized home page order in $return_array
		 */
		function getHomeStuffOrder ($id) {

			return; # la tabla vtiger_homestuff ya no se usa | MA 22-09-2016
			global $adb;
			if (!is_array ($this->homeorder_array)) {
				$this->homeorder_array = array (
					'UA', 'PA', 'ALVT', 'HDB', 'PLVT', 'QLTQ', 'CVLVT', 'HLT',
					'GRT', 'OLTSO', 'ILTI', 'MNL', 'OLTPO', 'LTFAQ',
				);
			}
			$return_array = Array ();
			$homeorder    = Array ();
			if ($id != '') {
				$qry = " SELECT DISTINCT(vtiger_homedefault.hometype) FROM vtiger_homedefault INNER JOIN vtiger_homestuff  ON vtiger_homestuff.stuffid=vtiger_homedefault.stuffid WHERE vtiger_homestuff.visible=0 AND vtiger_homestuff.userid=?";
				$res = $adb->pquery ($qry, array ($id));
				for ($q = 0; $q < $adb->num_rows ($res); $q++) {
					$homeorder[] = $adb->query_result ($res, $q, "hometype");
				}
				for ($i = 0; $i < count ($this->homeorder_array); $i++) {
					if (in_array ($this->homeorder_array[ $i ], $homeorder)) {
						$return_array[ $this->homeorder_array[ $i ] ] = $this->homeorder_array[ $i ];
					} else {
						$return_array[ $this->homeorder_array[ $i ] ] = '';
					}
				}
			} else {
				for ($i = 0; $i < count ($this->homeorder_array); $i++) {
					$return_array[ $this->homeorder_array[ $i ] ] = $this->homeorder_array[ $i ];
				}
			}
			return $return_array;
		}

		function getDefaultHomeModuleVisibility ($home_string, $inVal) {
			$homeModComptVisibility = 0;
			if ($inVal == 'postinstall') {
				if ($_REQUEST[ $home_string ] != '') {
					$homeModComptVisibility = 0;
				}
			}
			return $homeModComptVisibility;
		}

		function insertUserdetails ($inVal) {

			return; # la tabla vtiger_homestuff ya no se usa | MA 22-09-2016
			global $adb;
			$uid = $this->id;

			$s1         = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('ALVT', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s1, 1, 'Default', $uid, $visibility, 'Top Accounts'));

			$s2         = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('HDB', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s2, 2, 'Default', $uid, $visibility, 'Home Page Dashboard'));

			$s3         = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('PLVT', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s3, 3, 'Default', $uid, $visibility, 'Top Potentials'));

			$s4         = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('QLTQ', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s4, 4, 'Default', $uid, $visibility, 'Top Quotes'));

			$s5         = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('CVLVT', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s5, 5, 'Default', $uid, $visibility, 'Key Metrics'));

			$s6         = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('HLT', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s6, 6, 'Default', $uid, $visibility, 'Top Trouble Tickets'));

			$s7         = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('UA', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s7, 7, 'Default', $uid, $visibility, 'Upcoming Activities'));

			$s8         = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('GRT', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s8, 8, 'Default', $uid, $visibility, 'My Group Allocation'));

			$s9         = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('OLTSO', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s9, 9, 'Default', $uid, $visibility, 'Top Sales Orders'));

			$s10        = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('ILTI', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s10, 10, 'Default', $uid, $visibility, 'Top Invoices'));

			$s11        = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('MNL', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s11, 11, 'Default', $uid, $visibility, 'My New Leads'));

			$s12        = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('OLTPO', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s12, 12, 'Default', $uid, $visibility, 'Top Purchase Orders'));

			$s13        = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('PA', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s13, 13, 'Default', $uid, $visibility, 'Pending Activities'));;

			$s14        = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = $this->getDefaultHomeModuleVisibility ('LTFAQ', $inVal);
			$sql        = "INSERT INTO vtiger_homestuff VALUES(?,?,?,?,?,?)";
			$res        = $adb->pquery ($sql, array ($s14, 14, 'Default', $uid, $visibility, 'My Recent FAQs'));

			// Non-Default Home Page widget (no entry is requried in vtiger_homedefault below)
			$tc         = $adb->getUniqueID ("vtiger_homestuff");
			$visibility = 0;
			$sql        = "insert into vtiger_homestuff values($tc, 15, 'Tag Cloud', $uid, $visibility, 'Tag Cloud')";
			$adb->query ($sql);

			// Customization
			global $VtigerOndemandConfig;
			if (isset($VtigerOndemandConfig) && isset($VtigerOndemandConfig['DEFAULT_NOTEBOOK_WIDGET'])) {
				$defaultNoteBookWidgetInfo = $VtigerOndemandConfig['DEFAULT_NOTEBOOK_WIDGET'];
				$ntbkid                    = $adb->getUniqueID ("vtiger_homestuff");
				$visibility                = 0;
				$sql                       = "INSERT INTO vtiger_homestuff(stuffid, stuffsequence, stufftype, userid, visible, stufftitle) VALUES(?, ?, ?, ?, ?, ?)";
				$params                    = array ($ntbkid, 16, 'Notebook', $uid, $visibility, $defaultNoteBookWidgetInfo['title']);
				$adb->pquery ($sql, $params);

				$sql    = "INSERT INTO vtiger_notebook_contents(userid, notebookid, contents) VALUES(?,?,?)";
				$params = array ($uid, $ntbkid, $defaultNoteBookWidgetInfo['contents']);
				$adb->pquery ($sql, $params);
			}
			// END

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s1 . ",'ALVT',5,'Accounts')";
			$adb->query ($sql);

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s2 . ",'HDB',5,'Dashboard')";
			$adb->query ($sql);

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s3 . ",'PLVT',5,'Potentials')";
			$adb->query ($sql);

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s4 . ",'QLTQ',5,'Quotes')";
			$adb->query ($sql);

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s5 . ",'CVLVT',5,'NULL')";
			$adb->query ($sql);

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s6 . ",'HLT',5,'HelpDesk')";
			$adb->query ($sql);

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s7 . ",'UA',5,'Calendar')";
			$adb->pquery ($sql, array ());

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s8 . ",'GRT',5,'NULL')";
			$adb->query ($sql);

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s9 . ",'OLTSO',5,'SalesOrder')";
			$adb->query ($sql);

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s10 . ",'ILTI',5,'Invoice')";
			$adb->query ($sql);

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s11 . ",'MNL',5,'Leads')";
			$adb->query ($sql);

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s12 . ",'OLTPO',5,'PurchaseOrder')";
			$adb->query ($sql);

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s13 . ",'PA',5,'Calendar')";
			$adb->pquery ($sql, array ());

			$sql = "INSERT INTO vtiger_homedefault VALUES(" . $s14 . ",'LTFAQ',5,'Faq')";
			$adb->query ($sql);
		}

		/** function to save the order in which the modules have to be displayed in the home page for the specified user id
		 *
		 * @param $id -- user id:: Type integer
		 */
		function saveHomeStuffOrder ($id) {

			return; # la tabla vtiger_homestuff ya no se usa | MA 22-09-2016

			global $log, $adb;
			$log->debug ("Entering in function saveHomeOrder($id)");

			if ($this->mode == 'edit') {
				for ($i = 0; $i < count ($this->homeorder_array); $i++) {
					if ($_REQUEST[ $this->homeorder_array[ $i ] ] != '') {
						$save_array[] = $this->homeorder_array[ $i ];
						$qry          = " UPDATE vtiger_homestuff,vtiger_homedefault SET vtiger_homestuff.visible=0 WHERE vtiger_homestuff.stuffid=vtiger_homedefault.stuffid AND vtiger_homestuff.userid=" . $id . " AND vtiger_homedefault.hometype='" . $this->homeorder_array[ $i ] . "'";//To show the default Homestuff on the the Home Page
						$result       = $adb->query ($qry);
					} else {
						$qry    = "UPDATE vtiger_homestuff,vtiger_homedefault SET vtiger_homestuff.visible=1 WHERE vtiger_homestuff.stuffid=vtiger_homedefault.stuffid AND vtiger_homestuff.userid=" . $id . " AND vtiger_homedefault.hometype='" . $this->homeorder_array[ $i ] . "'";//To hide the default Homestuff on the the Home Page
						$result = $adb->query ($qry);
					}
				}
				if ($save_array != "") {
					$homeorder = implode (',', $save_array);
				}
			} else {
				$this->insertUserdetails ('postinstall');
			}
			$log->debug ("Exiting from function saveHomeOrder($id)");
		}

		/**
		 * Track the viewing of a detail record.  This leverages get_summary_text() which is object specific
		 * params $user_id - The user that is viewing the record.
		 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
		 * All Rights Reserved..
		 * Contributor(s): ______________________________________..
		 */
		function track_view ($user_id, $current_module, $id = '') {
			$this->log->debug ("About to call vtiger_tracker (user_id, module_name, item_id)($user_id, $current_module, $this->id)");

			$tracker = new Tracker();
			$tracker->track_view ($user_id, $current_module, $id, '');
		}

		/**
		 * Function to get the column value of a field
		 *
		 * @param $column_name -- Column name
		 * @param $input_value -- Input value for the column taken from the User
		 *
		 * @return Column value of the field.
		 */
		function get_column_value ($columname, $fldvalue, $fieldname, $uitype, $datatype) {
			if (is_uitype ($uitype, "_date_") && $fldvalue == '') {
				return null;
			}
			if ($datatype == 'I' || $datatype == 'N' || $datatype == 'NN') {
				return 0;
			}
			return $fldvalue;
		}

		/**
		 * Function to reset the Reminder Interval setup and update the time for next reminder interval
		 *
		 * @param $prev_reminder_interval -- Last Reminder Interval on which the reminder popup's were triggered.
		 */
		function resetReminderInterval ($prev_reminder_interval) {
			global $adb;
			if ($prev_reminder_interval != $this->column_fields['reminder_interval']) {
				unset($_SESSION['next_reminder_interval']);
				unset($_SESSION['next_reminder_time']);
				$set_reminder_next = date ('Y-m-d H:i');
				// NOTE date_entered has CURRENT_TIMESTAMP constraint, so we need to reset when updating the table
				$adb->pquery ("UPDATE vtiger_users SET reminder_next_time=?, date_entered=? WHERE id=?", array ($set_reminder_next, $this->column_fields['date_entered'], $this->id));
			}
		}

		function initSortByField ($module) {
			// Right now, we do not have any fields to be handled for Sorting in Users module. This is just a place holder as it is called from Popup.php
		}

		function filterInactiveFields ($module) {
			// TODO Nothing do right now
		}

		function deleteImage () {
			$sql1 = 'SELECT attachmentsid FROM vtiger_salesmanattachmentsrel WHERE smid = ?';
			$res1 = $this->db->pquery ($sql1, array ($this->id));
			if ($this->db->num_rows ($res1) > 0) {
				$attachmentId = $this->db->query_result ($res1, 0, 'attachmentsid');

				$sql2 = "DELETE FROM vtiger_crmentity WHERE crmid=? AND setype='Users Attachments'";
				$this->db->pquery ($sql2, array ($attachmentId));

				$sql3 = 'DELETE FROM vtiger_salesmanattachmentsrel WHERE smid=? AND attachmentsid=?';
				$this->db->pquery ($sql3, array ($this->id, $attachmentId));

				$sql2 = "UPDATE vtiger_users SET imagename='' WHERE id=?";
				$this->db->pquery ($sql2, array ($this->id));

				$sql4 = 'DELETE FROM vtiger_attachments WHERE attachmentsid=?';
				$this->db->pquery ($sql4, array ($attachmentId));
			}
		}

		/** Function to delete an entity with given Id */
		function trash ($module, $id) {
			global $log, $current_user;

			$this->mark_deleted ($id);
		}

		function transformOwnerShipAndDelete ($userId, $transformToUserId) {
			$adb = PearDatabase::getInstance ();

			$em = new VTEventsManager($adb);

			// Initialize Event trigger cache
			$em->initTriggerCache ();

			$entityData = VTEntityData::fromUserId ($adb, $userId);

			//set transform user id
			$entityData->set ('transformtouserid', $transformToUserId);

			$em->triggerEvent ("vtiger.entity.beforedelete", $entityData);

			vtws_transferOwnership ($userId, $transformToUserId);

			//delete from user vtiger_table;
			$sql = "DELETE FROM vtiger_users WHERE id=?";
			$adb->pquery ($sql, array ($userId));
		}

		/**
		 * This function should be overridden in each module.  It marks an item as deleted.
		 *
		 * @param <type> $id
		 */
		function mark_deleted ($id) {
			global $log, $current_user, $adb;
			$date_var = date ('Y-m-d H:i:s');
			$query    = "UPDATE vtiger_users SET status=?,date_modified=?,modified_user_id=? WHERE id=?";
			$adb->pquery ($query, array (
				'Inactive', $adb->formatDate ($date_var, true),
				$current_user->id, $id,
			), true, "Error marking record deleted: ");
		}

		/**
		 * Function to get the user if of the active admin user.
		 * @return Integer - Active Admin User ID
		 */
		public static function getActiveAdminId () {
			global $adb;
			$sql     = "SELECT id FROM vtiger_users WHERE is_admin='On' AND status='Active' LIMIT 1";
			$result  = $adb->pquery ($sql, array ());
			$adminId = 1;
			$it      = new SqlResultIterator($adb, $result);
			foreach ($it as $row) {
				$adminId = $row->id;
			}
			return $adminId;
		}

		/**
		 * Function to get the active admin user object
		 * @return Users - Active Admin User Instance
		 */
		public static function getActiveAdminUser () {
			$adminId = self::getActiveAdminId ();
			$user    = new Users();
			$user->retrieveCurrentUserInfoFromFile ($adminId);
			return $user;
		}

		//Integraci�n de VTiger con aplicativos terceros
		/**
		 * Integraci�n con VTiger
		 */
		public function registraUsuarioVTiger ($camposUsr) {
			$this->column_fields['user_name']          = $camposUsr['user_name'];
			$this->column_fields['user_password']      = $camposUsr['user_name'];
			$this->column_fields['first_name']         = $camposUsr['first_name'];
			$this->column_fields['last_name']          = $camposUsr['last_name'];
			$this->column_fields['is_admin']           = 'off';
			$this->column_fields['currency_id']        = 1;
			$this->column_fields['date_format']        = 'dd-mm-yyyy';
			$this->column_fields['email1']             = $camposUsr['email1'];
			$this->column_fields['status']             = 'Active';
			$this->column_fields['phone_work']         = $camposUsr['phone_work'];
			$this->column_fields['department']         = $camposUsr['department'];
			$this->column_fields['address_street']     = $camposUsr['address_street'];
			$this->column_fields['address_city']       = $camposUsr['address_city'];
			$this->column_fields['address_state']      = $camposUsr['address_state'];
			$this->column_fields['address_postalcode'] = $camposUsr['address_postalcode'];
			$this->column_fields['address_country']    = $camposUsr['address_country'];
			$this->column_fields['roleid']             = $camposUsr['roleid'];

			if ($result && $this->db->num_rows ($result)) {
				$row        = $this->db->fetchByAssoc ($result);
				$this->id   = $row['id'];
				$this->mode = 'edit';
			}
			$this->saveentity ("Users");//Se sincronizan los dem�s aplicativos
		}

		public function SincronizaVTigerLDAP ($adldap, $username, $userpass, $AUTENTICACION_USUARIOS_LDAP) {
			$userdata                                  = $adldap->user_info ($username, array ("*"));
			$this->column_fields['user_name']          = $username;
			$this->column_fields['user_password']      = $userpass;
			$this->column_fields['first_name']         = $userdata[0]['givenname'][0];
			$this->column_fields['last_name']          = $userdata[0]['sn'][0];
			$this->column_fields['roleid']             = 'H8';//Rol m�nimo de observador
			$this->column_fields['is_admin']           = 'off';
			$this->column_fields['currency_id']        = 1;
			$this->column_fields['date_format']        = 'dd-mm-yyyy';
			$this->column_fields['email1']             = $userdata[0]['mail'][0];
			$this->column_fields['status']             = 'Active';
			$this->column_fields['phone_work']         = $userdata[0]['telephonenumber'][0];
			$this->column_fields['department']         = $userdata[0]['physicaldeliveryofficename'][0];
			$this->column_fields['address_street']     = $userdata[0]['streetaddress'][0];
			$this->column_fields['address_city']       = $userdata[0]['l'][0];
			$this->column_fields['address_state']      = $userdata[0]['st'][0];
			$this->column_fields['address_postalcode'] = $userdata[0]['postalcode'][0];
			$this->column_fields['address_country']    = $userdata[0]['co'][0];

			$query  = "SELECT id from $this->table_name where user_name='$username'";
			$result = $this->db->query ($query);

			$roleVTiger = $this->obtenerRolVTigerAD ($adldap, $username);

			if (empty($roleVTiger)) {
				echo "Usuario con problemas en grupos del Active Directory - VTiger";
				die();
			} else {
				//$this->column_fields['roleid'] = $roleVTiger;
				$userdata[0]['roleVTiger'][0] = $roleVTiger;//
			}

			$this->registraUsuarioVTiger ($this->column_fields);

			//Se crea/actualiza el usuarios en el OrangeHRM
			$userg_id = $this->obtenerRolOrangeHRMAD ($adldap, $username);

			if (!$this->saveUserOrangeHRM ($adldap, $this->column_fields)) {
				echo $this->errorMsg;
				die();
			}

			//Se crea/actualiza el usuarios en el ProcessMaker
			if (!$this->saveUserProcessMaker ($adldap, $this->column_fields)) {
				echo $this->errorMsg;
				die();
			}

			//Se crea/actualiza el usuarios en el ProcessMaker
			if (!$this->saveUserDotProject ($adldap, $this->column_fields)) {
				echo $this->errorMsg;
				die();
			}

			return true;
		}

		public function obtenerRolesVTiger ($mayuscula = true) {
			global $log, $adb;
			$lstRoles = array ();

			$query = "SELECT roleid,rolename FROM `vtiger_role`";

			$result = $adb->query ($query);

			if ($result) {
				while ($row = $adb->fetch_array ($result)) {
					if ($mayuscula) {
						array_push ($lstRoles, array ($row["roleid"], strtoupper (html_entity_decode ($row["rolename"], ENT_COMPAT, "UTF-8"))));
					} else {
						array_push ($lstRoles, array ($row["roleid"], html_entity_decode ($row["rolename"], ENT_COMPAT, "UTF-8")));
					}
				}
			}
			return $lstRoles;
		}

		/*
     * Dado el nombre de usuario se contrasta los grupos asociados en el AD con
     * los roles existentes en el VTiger
     */
		public function obtenerRolVTigerAD ($adldap, $username) {
			$j         = 0;
			$lstGroups = $adldap->user_groups ($username, true);
			for ($k = 0; $k < count ($lstGroups); $k++) {
				$lstGroups[ $k ] = strtoupper ($lstGroups[ $k ]);
			}
			$lstRoles = $this->obtenerRolesVTiger ();
			$roleId   = '';

			for ($i = 0; $i < count ($lstRoles); $i++) {
				$rolename = 'VTIGER_' . $lstRoles[ $i ][1];
				if (in_array ($rolename, $lstGroups)) {
					$roleId = $lstRoles[ $i ][0];
					$j++;
				}
			}
			if ($j != 1) {
				return "";
			}//Tiene m�s de un rol asociado o no tiene rol asociado

			return $roleId;
		}

		/*
     * Integraci�n de login time
     */
		public function conectaIntranetLogin () {
			$bStatus = true;

			global $db_login;

			$dbLogin = mysql_connect ($db_login['db_server'] . $db_login['db_port'],
				$db_login['db_username'],
				$db_login['db_password'],
				true);

			if (!$dbLogin) {
				$this->errorMsg = "No se pudo conectar a la base de datos del Login";
				$bStatus        = false;
			}

			//Selecciono la db
			if ($bStatus && (!mysql_select_db ($db_login['db_name'], $dbLogin))) {
				$this->errorMsg = "No se pudo seleccionar a la base de datos del Login";
				$bStatus        = false;
			}

			if ($bStatus) {
				$bStatus = $dbLogin;
			}
			return $bStatus;
		}

		/**
		 * Integraci�n con HRM
		 */

		public function conectaBDHRM () {
			$bStatus = true;
			/*$dataDBOrangeHRM = array('server' => '127.0.0.1',
    						 'userdb' => 'root',
    						 'passdb' => 'perolito',
    						 'db' => 'plat_hrm_enacol');*/
			global $db_hrm;

			$dbOrangeHRM = mysql_connect ($db_hrm['dbhost'] . ':' . $db_hrm['dbport'],
				$db_hrm['dbuser'],
				$db_hrm['dbpass'],
				true);

			if (!$dbOrangeHRM) {
				$this->errorMsg = "No se pudo conectar a la base de datos del Orange HRM";
				$bStatus        = false;
			}

			//Selecciono la db
			if ($bStatus && (!mysql_select_db ($db_hrm['dbname'], $dbOrangeHRM))) {
				$this->errorMsg = "No se pudo seleccionar a la base de datos del Orange HRM";
				$bStatus        = false;
			}

			if ($bStatus) {
				$bStatus = $dbOrangeHRM;
			}
			return $bStatus;
		}

		public function saveUserOrangeHRM ($userg_id, $camposUsr, $adldap) {
			$bStatus = true;

			$dbOrangeHRM = $this->conectaBDHRM ();

			if ($dbOrangeHRM) {

				$query = "SELECT id FROM hs_hr_users WHERE user_name = '" . $camposUsr['user_name'] . "'";

				$result = mysql_query ($query, $dbOrangeHRM);

				if ($result) {
					$queryUsr     = '';
					$isadmin      = 'No';
					$date_entered = date ('Y-m-d');
					$created_by   = 'USR001';
					$status       = 'Enabled';
					$deleted      = '0';

					if ($userg_id === '') {
						echo "Usuario con problemas en grupos del Active Directory - HRM";
						die();
					}

					//if ($userg_id > -1) {
					if ($userg_id < 0) {
						$userg_id = 'NULL';
					}

					$row = mysql_fetch_array ($result);

					$emp_number = $this->obtenerNumeroEmpleadoOrangeHRM ($camposUsr['first_name'], $camposUsr['last_name'], $dbOrangeHRM);

					if ($row) {//Se actualiza el usuario
						if ($adldap) {
							$id       = $row['id'];
							$queryUsr = "UPDATE hs_hr_users SET user_name = '" . $camposUsr['user_name'] . "',
																user_password = '" . md5 ($camposUsr['user_password']) . "',
																first_name = '" . $camposUsr['first_name'] . "',
																last_name = '" . $camposUsr['last_name'] . "',
																emp_number = " . $emp_number . ",
																is_admin = '" . $isadmin . "'
											WHERE id = '" . $id . "'";
						}
					} else {//Se ingresa el empleado
						if (empty($emp_number)) {
							$id      = '1';
							$queryid = "SELECT last_id+1 AS newId FROM `hs_hr_unique_id` WHERE table_name = 'hs_hr_employee'";

							$resultId = mysql_query ($queryid, $dbOrangeHRM);
							if ($resultId) {
								$rowId      = mysql_fetch_array ($resultId);
								$emp_number = $rowId['newId'];
								$idEmployee = sprintf ("%04d", $rowId['newId']);
							}

							$queryUsr = "INSERT INTO hs_hr_employee (emp_number,employee_id,emp_lastname,emp_firstname)
										VALUES ('" . $emp_number . "','" . $idEmployee . "','" . $camposUsr['last_name'] . "','" . $camposUsr['first_name'] . "')";

							mysql_query ($queryUsr, $dbOrangeHRM);
						}

						if (!empty($emp_number)) {
							$queryid  = "UPDATE `hs_hr_unique_id` SET last_id=last_id+1 WHERE table_name = 'hs_hr_employee'";
							$resultId = mysql_query ($queryid, $dbOrangeHRM);

							$id      = 'USR001';
							$queryid = "SELECT last_id+1 AS newId FROM `hs_hr_unique_id` WHERE table_name = 'hs_hr_users'";

							$resultId = mysql_query ($queryid, $dbOrangeHRM);
							if ($resultId) {
								$rowId = mysql_fetch_array ($resultId);
								$id    = sprintf ("USR%03d", $rowId['newId']);
							}

							$queryUsr = "INSERT INTO hs_hr_users (id,user_name,user_password,first_name,last_name,is_admin,emp_number,date_entered,created_by,
																status,deleted,userg_id)
										VALUES ('" . $id . "','" . $camposUsr['user_name'] . "','" . md5 ($camposUsr['user_password']) . "','" . $camposUsr['first_name'] . "',
												'" . $camposUsr['last_name'] . "','" . $isadmin . "'," . $emp_number . ",'" . $date_entered . "','" . $created_by . "',
												'" . $status . "'," . $deleted . ",'" . $userg_id . "')";
						}
					}
					if (!mysql_query ($queryUsr, $dbOrangeHRM)) {
						$this->errorMsg = "Error ingresando/actualizando datos del usuario en Orange HRM";
						$bStatus        = false;
					} else {
						$queryid  = "UPDATE `hs_hr_unique_id` SET last_id=last_id+1 WHERE table_name = 'hs_hr_users'";
						$resultId = mysql_query ($queryid, $dbOrangeHRM);

						$dbLogin = $this->conectaIntranetLogin ();

						if ($dbLogin) {
							$queryid = "SELECT max(id_login)+1 AS id_login FROM `hrm`";

							$resultId = mysql_query ($queryid, $dbLogin);

							if ($resultId) {
								$rowId = mysql_fetch_array ($resultId);
								$id    = $rowId['id_login'];
							}

							$queryid  = "INSERT INTO hrm VALUES (" . $id . ",'" . $camposUsr['user_name'] . "','" .
										$this->encrypt ($camposUsr['user_password'], 'estaeslaclave01EncryptadaDeEnacol')
										. "')";
							$resultId = mysql_query ($queryid, $dbLogin);
						}
					}
					//}
				}
			}
			return $bStatus;
		}

		public function obtenerNumeroEmpleadoOrangeHRM ($firstname, $lastname, $dbOrangeHRM) {
			$query = "SELECT emp_number FROM `hs_hr_employee` WHERE emp_lastname LIKE '" . $lastname . "%'
    						AND emp_firstname LIKE '" . $firstname . "%' LIMIT 0,1";

			$result = mysql_query ($query, $dbOrangeHRM);

			if ($result) {
				if ($row = mysql_fetch_array ($result)) {
					return $row['emp_number'];
				}
			}
			return "";
		}

		public function obtenerRolesHRM ($dbOrangeHRM, $mayuscula = true) {
			$lstRoles = array ();

			if (!$dbOrangeHRM) {
				$dbOrangeHRM = $this->conectaBDHRM ();
			}

			$query = "SELECT userg_id,userg_name FROM `hs_hr_user_group`";

			$result = mysql_query ($query, $dbOrangeHRM);

			if ($result) {
				while ($row = mysql_fetch_array ($result)) {
					if ($mayuscula) {
						array_push ($lstRoles, array ($row["userg_id"], strtoupper (html_entity_decode ($row["userg_name"], ENT_COMPAT, "UTF-8"))));
					} else {
						array_push ($lstRoles, array ($row["userg_id"], html_entity_decode ($row["userg_name"], ENT_COMPAT, "UTF-8")));
					}
				}
			}
			if ($mayuscula) {
				array_push ($lstRoles, array (-1, "SIN PRIVILEGIOS"));
			} else {
				array_push ($lstRoles, array (-1, "Sin privilegios"));
			}

			return $lstRoles;
		}

		public function obtenerRolHRM ($username) {
			$lstRoles = array ();

			$dbOrangeHRM = $this->conectaBDHRM ();

			if ($dbOrangeHRM) {

				$query = "SELECT A.userg_id,A.userg_name FROM `hs_hr_user_group` A
						INNER JOIN hs_hr_users B ON (A.userg_id = B.userg_id)
						WHERE user_name = '" . $username . "'";

				$result = mysql_query ($query, $dbOrangeHRM);

				if ($result) {
					$row = mysql_fetch_array ($result);

					if ($row) {
						array_push ($lstRoles, array ($row["userg_id"], html_entity_decode ($row["userg_name"], ENT_COMPAT, "UTF-8")));
					} else {
						array_push ($lstRoles, array (-1, "Sin privilegios"));
					}
				}
			}

			return $lstRoles;
		}

		/*
     * Dado el nombre de usuario se contrasta los grupos asociados en el AD con
     * los roles existentes en el OrangeHRM
     */
		public function obtenerRolOrangeHRMAD ($adldap, $username) {
			$j           = 0;
			$lstGroups   = $adldap->user_groups ($username, true);
			$dbOrangeHRM = $this->conectaBDHRM ();

			for ($k = 0; $k < count ($lstGroups); $k++) {
				$lstGroups[ $k ] = strtoupper ($lstGroups[ $k ]);
			}
			$lstRoles = $this->obtenerRolesHRM ($dbOrangeHRM);
			$roleId   = '';

			for ($i = 0; $i < count ($lstRoles); $i++) {
				$rolename = 'HRM_' . $lstRoles[ $i ][1];
				if (in_array ($rolename, $lstGroups)) {
					$roleId = $lstRoles[ $i ][0];
					$j++;
				}
			}

			if ($j != 1) {
				return "";
			}//Tiene m�s de un rol asociado o no tiene rol asociado

			return $roleId;
		}

		/**
		 * Integraci�n con HRM - Vendas
		 */

		public function conectaBDHRMVendas () {
			$bStatus = true;
			global $db_hrm_vendas;

			$dbOrangeHRMVendas = mysql_connect ($db_hrm_vendas['dbhost'],
				$db_hrm_vendas['dbuser'],
				$db_hrm_vendas['dbpass'],
				true);

			if (!$dbOrangeHRMVendas) {
				$this->errorMsg = "No se pudo conectar a la base de datos del Orange HRM Vendas";
				$bStatus        = false;
			}

			//Selecciono la db
			if ($bStatus && (!mysql_select_db ($db_hrm_vendas['dbname'], $dbOrangeHRMVendas))) {
				$this->errorMsg = "No se pudo seleccionar a la base de datos del Orange HRM de Vendas";
				$bStatus        = false;
			}

			if ($bStatus) {
				$bStatus = $dbOrangeHRMVendas;
			}
			return $bStatus;
		}

		public function saveUserOrangeHRMVendas ($userg_id, $camposUsr, $adldap) {
			$bStatus = true;

			$dbOrangeHRMVendas = $this->conectaBDHRMVendas ();

			if ($dbOrangeHRMVendas) {

				$query = "SELECT id FROM hs_hr_users WHERE user_name = '" . $camposUsr['user_name'] . "'";

				$result = mysql_query ($query, $dbOrangeHRMVendas);

				if ($result) {
					$queryUsr     = '';
					$isadmin      = 'No';
					$date_entered = date ('Y-m-d');
					$created_by   = 'USR001';
					$status       = 'Enabled';
					$deleted      = '0';

					if ($userg_id === '') {
						echo "Usuario con problemas en grupos del Active Directory - HRM";
						die();
					}

					//if ($userg_id > -1) {
					if ($userg_id < 0) {
						$userg_id = 'NULL';
					}

					$row = mysql_fetch_array ($result);

					$emp_number = $this->obtenerNumeroEmpleadoOrangeHRMVendas ($camposUsr['first_name'], $camposUsr['last_name'], $dbOrangeHRMVendas);

					if ($row) {//Se actualiza el usuario
						if ($adldap) {
							$id       = $row['id'];
							$queryUsr = "UPDATE hs_hr_users SET user_name = '" . $camposUsr['user_name'] . "',
																user_password = '" . md5 ($camposUsr['user_password']) . "',
																first_name = '" . $camposUsr['first_name'] . "',
																last_name = '" . $camposUsr['last_name'] . "',
																emp_number = " . $emp_number . ",
																is_admin = '" . $isadmin . "'
											WHERE id = '" . $id . "'";
						}
					} else {//Se ingresa el empleado y/o el user
						if (empty($emp_number)) {
							$id      = '1';
							$queryid = "SELECT last_id+1 AS newId FROM `hs_hr_unique_id` WHERE table_name = 'hs_hr_employee'";

							$resultId = mysql_query ($queryid, $dbOrangeHRMVendas);
							if ($resultId) {
								$rowId      = mysql_fetch_array ($resultId);
								$emp_number = $rowId['newId'];
								$idEmployee = sprintf ("%04d", $rowId['newId']);
							}

							$queryUsr = "INSERT INTO hs_hr_employee (emp_number,employee_id,emp_lastname,emp_firstname)
										VALUES ('" . $emp_number . "','" . $idEmployee . "','" . $camposUsr['last_name'] . "','" . $camposUsr['first_name'] . "')";

							mysql_query ($queryUsr, $dbOrangeHRMVendas);
						}

						if (!empty($emp_number)) {
							$queryid  = "UPDATE `hs_hr_unique_id` SET last_id=last_id+1 WHERE table_name = 'hs_hr_employee'";
							$resultId = mysql_query ($queryid, $dbOrangeHRMVendas);

							$id      = 'USR001';
							$queryid = "SELECT last_id+1 AS newId FROM `hs_hr_unique_id` WHERE table_name = 'hs_hr_users'";

							$resultId = mysql_query ($queryid, $dbOrangeHRMVendas);
							if ($resultId) {
								$rowId = mysql_fetch_array ($resultId);
								$id    = sprintf ("USR%03d", $rowId['newId']);
							}

							$queryUsr = "INSERT INTO hs_hr_users (id,user_name,user_password,first_name,last_name,is_admin,emp_number,date_entered,created_by,
																status,deleted,userg_id)
										VALUES ('" . $id . "','" . $camposUsr['user_name'] . "','" . md5 ($camposUsr['user_password']) . "','" . $camposUsr['first_name'] . "',
												'" . $camposUsr['last_name'] . "','" . $isadmin . "'," . $emp_number . ",'" . $date_entered . "','" . $created_by . "',
												'" . $status . "'," . $deleted . ",'" . $userg_id . "')";
						}
					}
					if (!mysql_query ($queryUsr, $dbOrangeHRMVendas)) {
						$this->errorMsg = "Error ingresando/actualizando datos del usuario en Orange HRM";
						$bStatus        = false;
					} else {
						$queryid  = "UPDATE `hs_hr_unique_id` SET last_id=last_id+1 WHERE table_name = 'hs_hr_users'";
						$resultId = mysql_query ($queryid, $dbOrangeHRMVendas);

						$dbLogin = $this->conectaIntranetLogin ();

						if ($dbLogin) {
							$queryid = "SELECT max(id_login)+1 AS id_login FROM `hrm`";

							$resultId = mysql_query ($queryid, $dbLogin);

							if ($resultId) {
								$rowId = mysql_fetch_array ($resultId);
								$id    = $rowId['id_login'];
							}

							$queryid  = "INSERT INTO hrm VALUES (" . $id . ",'" . $camposUsr['user_name'] . "','" . $camposUsr['user_password'] . "')";
							$resultId = mysql_query ($queryid, $dbLogin);
						}
					}
					//}
				}
			}
			return $bStatus;
		}

		public function obtenerNumeroEmpleadoOrangeHRMVendas ($firstname, $lastname, $dbOrangeHRMVendas) {
			$query = "SELECT emp_number FROM `hs_hr_employee` WHERE emp_lastname LIKE '" . $lastname . "%'
    						AND emp_firstname LIKE '" . $firstname . "%' LIMIT 0,1";

			$result = mysql_query ($query, $dbOrangeHRMVendas);

			if ($result) {
				if ($row = mysql_fetch_array ($result)) {
					return $row['emp_number'];
				}
			}
			return "";
		}

		public function obtenerRolesHRMVendas ($dbOrangeHRMVendas, $mayuscula = true) {
			$lstRoles = array ();

			if (!$dbOrangeHRMVendas) {
				$dbOrangeHRMVendas = $this->conectaBDHRMVendas ();
			}

			$query = "SELECT userg_id,userg_name FROM `hs_hr_user_group`";

			$result = mysql_query ($query, $dbOrangeHRMVendas);

			if ($result) {
				while ($row = mysql_fetch_array ($result)) {
					if ($mayuscula) {
						array_push ($lstRoles, array ($row["userg_id"], strtoupper (html_entity_decode ($row["userg_name"], ENT_COMPAT, "UTF-8"))));
					} else {
						array_push ($lstRoles, array ($row["userg_id"], html_entity_decode ($row["userg_name"], ENT_COMPAT, "UTF-8")));
					}
				}
			}
			if ($mayuscula) {
				array_push ($lstRoles, array (-1, "SIN PRIVILEGIOS"));
			} else {
				array_push ($lstRoles, array (-1, "Sin privilegios"));
			}

			return $lstRoles;
		}

		public function obtenerRolHRMVendas ($username) {
			$lstRoles = array ();

			$dbOrangeHRM = $this->conectaBDHRMVendas ();

			$query = "SELECT A.userg_id,A.userg_name FROM `hs_hr_user_group` A
  					INNER JOIN hs_hr_users B ON (A.userg_id = B.userg_id)
  					WHERE user_name = '" . $username . "'";

			$result = mysql_query ($query, $dbOrangeHRMVendas);

			if ($result) {
				$row = mysql_fetch_array ($result);

				if ($row) {
					array_push ($lstRoles, array ($row["userg_id"], html_entity_decode ($row["userg_name"], ENT_COMPAT, "UTF-8")));
				} else {
					array_push ($lstRoles, array (-1, "Sin privilegios"));
				}
			}

			return $lstRoles;
		}

		/*
     * Dado el nombre de usuario se contrasta los grupos asociados en el AD con
     * los roles existentes en el OrangeHRM
     */
		public function obtenerRolOrangeHRMVendasAD ($adldap, $username) {
			$j                 = 0;
			$lstGroups         = $adldap->user_groups ($username, true);
			$dbOrangeHRMVendas = $this->conectaBDHRMVendas ();

			for ($k = 0; $k < count ($lstGroups); $k++) {
				$lstGroups[ $k ] = strtoupper ($lstGroups[ $k ]);
			}
			$lstRoles = $this->obtenerRolesHRMVendas ($dbOrangeHRMVendas);
			$roleId   = '';

			for ($i = 0; $i < count ($lstRoles); $i++) {
				$rolename = 'HRMVENDAS_' . $lstRoles[ $i ][1];
				if (in_array ($rolename, $lstGroups)) {
					$roleId = $lstRoles[ $i ][0];
					$j++;
				}
			}

			if ($j != 1) {
				return "";
			}//Tiene m�s de un rol asociado o no tiene rol asociado

			return $roleId;
		}

		/**
		 * Integraci�n con ProcessMaker
		 */

		public function conectaBDProcess () {
			$bStatus = true;

			/*$dataDBProcessMaker = array('server' => '127.0.0.1:3307',
    						 'userdb' => 'root',
    						 'passdb' => 'perolito',
    						 'db1' => 'rb_workflow',
    						 'db2' => 'wf_workflow');*/
			global $db_process;

			$dbProcessMaker = mysql_connect ($db_process['DB_RBAC_HOST'],
				$db_process['DB_RBAC_USER'],
				$db_process['DB_RBAC_PASS'],
				true);

			if (!$dbProcessMaker) {
				$this->errorMsg = "No se pudo conectar a la base de datos del ProcessMaker";
				$bStatus        = false;
			}

			//Selecciono la db
			if ($bStatus && (!mysql_select_db ($db_process['DB_RBAC_NAME'], $dbProcessMaker))) {
				$this->errorMsg = "No se pudo seleccionar a la base de datos de registro del ProcessMaker";
				$bStatus        = false;
			}

			if ($bStatus) {
				$bStatus = $dbProcessMaker;
			}
			return $bStatus;
		}

		public function conectaBDProcess2 () {
			$bStatus = true;

			/*$dataDBProcessMaker = array('server' => '127.0.0.1:3307',
    						 'userdb' => 'root',
    						 'passdb' => 'perolito',
    						 'db1' => 'rb_workflow',
    						 'db2' => 'wf_workflow');*/
			global $db_process;

			$dbProcessMaker2 = mysql_connect ($db_process['DB_HOST'],
				$db_process['DB_USER'],
				$db_process['DB_PASS'],
				true);

			if (!$dbProcessMaker2) {
				$this->errorMsg = "No se pudo conectar a la base de datos del ProcessMaker";
				$bStatus        = false;
			}

			//Selecciono la db
			if ($bStatus && (!mysql_select_db ($db_process['DB_NAME'], $dbProcessMaker2))) {
				$this->errorMsg = "No se pudo seleccionar a la base de datos de registro del ProcessMaker";
				$bStatus        = false;
			}

			if ($bStatus) {
				$bStatus = $dbProcessMaker2;
			}
			return $bStatus;
		}

		public function saveUserProcessMaker ($adldap, $camposUsr) {
			$bStatus         = true;
			$dbProcessMaker  = $this->conectaBDProcess ();
			$dbProcessMaker2 = $this->conectaBDProcess2 ();

			if ($dbProcessMaker) {
				$auto_source_uid = '';
				$query           = "SELECT AUTH_SOURCE_UID FROM `AUTHENTICATION_SOURCE` WHERE AUTH_SOURCE_PROVIDER = 'ldap'";

				$result = mysql_query ($query, $dbProcessMaker);

				if ($result) {
					$row = mysql_fetch_array ($result);

					if ($row) {
						$auto_source_uid = $row['AUTH_SOURCE_UID'];
					}
				} else {
					echo mysql_error ($dbProcessMaker);
				}

				if ($adldap && empty($auto_source_uid)) {
					$this->errorMsg = "No se pudo obtener el ID del ActiveDirectory configurado en ProcessMaker";
					$bStatus        = false;
				}

				if ($bStatus) {

					$query = "SELECT USR_UID AS id FROM USERS WHERE USR_USERNAME = '" . $camposUsr['user_name'] . "'";

					$result = mysql_query ($query, $dbProcessMaker2);

					if ($result) {
						$queryUsr     = '';
						$isadmin      = 'Yes';
						$USR_DUE_DATE = date ('Y');
						$USR_DUE_DATE++;
						$USR_DUE_DATE .= '-' . date ('m-d');
						$USR_CREATE_DATE = date ('Y-m-d H:i:s');
						$USR_UPDATE_DATE = date ('Y-m-d H:i:s');
						$USR_STATUS      = 1;

						if ($adldap) {
							$USR_AUTH_TYPE = 'ldap';
							$ROL_UID       = $this->obtenerRolProcessMakerAD ($adldap, $camposUsr['user_name'], $dbProcessMaker);
						} else {
							$USR_AUTH_TYPE = '';
							$ROL_UID       = '00000000000000000000000000000003'; //Se asigna criterios de operador
						}

						if (empty($ROL_UID)) {
							echo "Usuario con problemas en grupos del Active Directory - ProcessMaker";
							die();
						}

						$row = mysql_fetch_array ($result);

						if ($row) {//Se actualiza el usuario
							if ($adldap) {
								$id       = $row['id'];
								$queryUsr = "UPDATE USERS SET USR_USERNAME = '" . $camposUsr['user_name'] . "',
																	USR_PASSWORD = '" . md5 ($camposUsr['user_password']) . "',
																	USR_FIRSTNAME = '" . $camposUsr['first_name'] . "',
																	USR_LASTNAME = '" . $camposUsr['last_name'] . "',
																	USR_EMAIL = '" . $camposUsr['email1'] . "',
																	USR_STATUS = 'ACTIVE',
																	USR_ROLE = '" . $this->usrROLEPROCESSMAKER . "'

												WHERE USR_UID = '" . $id . "'";

								if (mysql_query ($queryUsr, $dbProcessMaker2)) {//ProcessMaker trabaja con 2 bases de datos. Se actualiza la otra bd
									$queryUsr = "UPDATE USERS_ROLES SET ROL_UID = '" . $ROL_UID . "'
												WHERE USR_UID = '" . $id . "'";

									mysql_query ($queryUsr, $dbProcessMaker);

									$queryUsr = "UPDATE USERS SET USR_USERNAME = '" . $camposUsr['user_name'] . "',
																	USR_PASSWORD = '" . md5 ($camposUsr['user_password']) . "',
																	USR_FIRSTNAME = '" . $camposUsr['first_name'] . "',
																	USR_LASTNAME = '" . $camposUsr['last_name'] . "',
																	USR_EMAIL = '" . $camposUsr['email1'] . "'

												WHERE USR_UID = '" . $id . "'";

									mysql_query ($queryUsr, $dbProcessMaker);
								} else {
									$this->errorMsg = "Error ingresando/actualizando datos del usuario en ProcessMaker";
									$bStatus        = false;
								}
							}
						} else {//Se ingresa el usuario
							$id      = '00000000000000000000000000000002';
							$queryid = "SELECT count(*)+1 AS id FROM `USERS`";

							$resultId = mysql_query ($queryid, $dbProcessMaker);
							if ($resultId) {
								$rowId = mysql_fetch_array ($resultId);
								$id    = sprintf ("%032d", $rowId['id']);
							}

							$queryUsr = "INSERT INTO USERS (USR_UID,USR_USERNAME,USR_PASSWORD,USR_FIRSTNAME,USR_LASTNAME,USR_EMAIL,USR_DUE_DATE,
														USR_CREATE_DATE,USR_UPDATE_DATE,USR_STATUS,USR_AUTH_TYPE,UID_AUTH_SOURCE)
										VALUES ('" . $id . "','" . $camposUsr['user_name'] . "','" . md5 ($camposUsr['user_password']) . "','" . $camposUsr['first_name'] . "',
												'" . $camposUsr['last_name'] . "','" . $camposUsr['email1'] . "','" . $USR_DUE_DATE . "','" . $USR_CREATE_DATE . "',
												'" . $USR_UPDATE_DATE . "'," . $USR_STATUS . ",'" . $USR_AUTH_TYPE . "','" . $auto_source_uid . "')";

							if (mysql_query ($queryUsr, $dbProcessMaker)) {//ProcessMaker trabaja con 2 bases de datos. Se actualiza la otra bd
								$USR_STATUS = 'ACTIVE';

								$queryUsr = "INSERT INTO USERS_ROLES (USR_UID,ROL_UID) VALUES ('" . $id . "','" . $ROL_UID . "')";

								mysql_query ($queryUsr, $dbProcessMaker);

								$queryUsr = "INSERT INTO USERS (USR_UID,USR_USERNAME,USR_PASSWORD,USR_FIRSTNAME,USR_LASTNAME,USR_EMAIL,USR_DUE_DATE,
														USR_CREATE_DATE,USR_UPDATE_DATE,USR_STATUS,USR_ROLE)
										VALUES ('" . $id . "','" . $camposUsr['user_name'] . "','" . md5 ($camposUsr['user_password']) . "','" . $camposUsr['first_name'] . "',
												'" . $camposUsr['last_name'] . "','" . $camposUsr['email1'] . "','" . $USR_DUE_DATE . "','" . $USR_CREATE_DATE . "',
												'" . $USR_UPDATE_DATE . "','" . $USR_STATUS . "','" . $this->usrROLEPROCESSMAKER . "')";

								mysql_query ($queryUsr, $dbProcessMaker2);

								$dbLogin = $this->conectaIntranetLogin ();

								if ($dbLogin) {
									$queryid = "SELECT max(id_login)+1 AS id_login FROM `processos`";

									$resultId = mysql_query ($queryid, $dbLogin);

									if ($resultId) {
										$rowId = mysql_fetch_array ($resultId);
										$id    = $rowId['id_login'];
									}

									$queryid  = "INSERT INTO processos VALUES (" . $id . ",'" . $camposUsr['user_name'] . "','" .
												$this->encrypt ($camposUsr['user_password'], 'estaeslaclave01EncryptadaDeEnacol')
												. "',NULL)";
									$resultId = mysql_query ($queryid, $dbLogin);
								}
							} else {
								$this->errorMsg = "Error ingresando/actualizando datos del usuario en ProcessMaker";
								$bStatus        = false;
							}
						}
					}
				}
			} else {
				die("No se pudo realizar la conexi�n a la bd del ProcessMaker");
			}
			return $bStatus;
		}

		public function obtenerRolesProcessMaker ($dbProcessMaker, $mayuscula = true) {
			$lstRoles = array ();

			if (!$dbProcessMaker) {
				$dbProcessMaker = $this->conectaBDProcess ();
			}

			$query = "SELECT ROL_UID,ROL_CODE FROM `roles` WHERE ROL_CODE LIKE 'PROCESS%'";

			$result = mysql_query ($query, $dbProcessMaker);

			if ($result) {
				while ($row = mysql_fetch_array ($result)) {
					array_push ($lstRoles, array ($row["ROL_UID"], strtoupper (html_entity_decode ($row["ROL_CODE"], ENT_COMPAT, "UTF-8"))));
				}
			}
			array_push ($lstRoles, array (-1, "SIN PRIVILEGIOS"));

			return $lstRoles;
		}

		public function obtenerRolPM ($username) {
			$lstRoles       = array ();
			$dbProcessMaker = $this->conectaBDProcess ();

			if ($dbProcessMaker) {
				$query = "SELECT A.ROL_UID, C.ROL_CODE FROM USERS_ROLES A INNER JOIN USERS B
						ON (A.USR_UID = B.USR_UID)
						INNER JOIN ROLES C
						ON (C.ROL_UID = A.ROL_UID) WHERE ROL_CODE != 'RBAC_ADMIN'
						AND B.USR_USERNAME = '" . $username . "'";

				$result = mysql_query ($query, $dbProcessMaker);

				if ($result) {
					$row = mysql_fetch_array ($result);
					if ($row) {
						array_push ($lstRoles, array ($row["ROL_UID"], strtoupper (html_entity_decode ($row["ROL_CODE"], ENT_COMPAT, "UTF-8"))));
					} else {
						array_push ($lstRoles, array ('-1', 'Sin privilegios'));
					}
				}
			}

			return $lstRoles;
		}

		/*
     * Dado el nombre de usuario se contrasta los grupos asociados en el AD con
     * los roles existentes en el ProcessMaker
     */
		public function obtenerRolProcessMakerAD ($adldap, $username, $dbProcessMaker) {
			$this->usrROLEPROCESSMAKER = '';
			$j                         = 0;
			$lstGroups                 = $adldap->user_groups ($username, true);
			for ($k = 0; $k < count ($lstGroups); $k++) {
				$lstGroups[ $k ] = strtoupper ($lstGroups[ $k ]);
			}
			$lstRoles = $this->obtenerRolesProcessMaker ($dbProcessMaker);
			$roleId   = '';

			for ($i = 0; $i < count ($lstRoles); $i++) {
				$rolename = $lstRoles[ $i ][1];
				if (in_array ($rolename, $lstGroups)) {
					$roleId                    = $lstRoles[ $i ][0];
					$this->usrROLEPROCESSMAKER = $lstRoles[ $i ][1];
					$j++;
				}
			}
			if ($j != 1) {
				return "";
			}//Tiene m�s de un rol asociado o no tiene rol asociado

			return $roleId;
		}

		/**
		 * Integraci�n con dotProject
		 */

		public function conectaBDDotProject () {
			$bStatus = true;

			/*
		$dataDBDotProject = array('server' => '127.0.0.1',
    						 'userdb' => 'root',
    						 'passdb' => 'perolito',
    						 'db' => 'intranet_dot');
    						 */
			global $db_dotProject;

			$dbDotProject = mysql_connect ($db_dotProject['dbhost'],
				$db_dotProject['dbuser'],
				$db_dotProject['dbpass'],
				true);

			if (!$dbDotProject) {
				$this->errorMsg = "No se pudo conectar a la base de datos del dotProject";
				$bStatus        = false;
			}

			//Selecciono la db
			if ($bStatus && (!mysql_select_db ($db_dotProject['dbname'], $dbDotProject))) {
				$this->errorMsg = "No se pudo seleccionar a la base de datos del dotProject";
				$bStatus        = false;
			}

			if ($bStatus) {
				$bStatus = $dbDotProject;
			}
			return $bStatus;
		}

		public function saveUserDotProject ($adldap, $camposUsr) {
			$bStatus = true;

			$dbDotProject = $this->conectaBDDotProject ();

			if ($dbDotProject) {
				$user_password    = md5 ($camposUsr['user_name']);
				$user_parent      = 0;
				$user_type        = 0;
				$user_company     = 0;
				$user_departament = 0;
				$user_owner       = 0;
				$user_signature   = '';

				$query = "SELECT user_id AS id FROM users WHERE user_username = '" . $camposUsr['user_name'] . "'";

				$result = mysql_query ($query, $dbDotProject);

				if ($result) {
					if ($adldap) {
						$rolId = $this->obtenerRolDotProjectAD ($adldap, $camposUsr['user_name'], $dbDotProject);
					} else {
						$rolId = '13';
					} //Se asigna el role de guest

					if (empty($rolId)) {
						echo "Usuario con problemas en grupos del Active Directory - DotProject";
						die();
					}

					$row = mysql_fetch_array ($result);

					if ($row) {//Se actualiza el usuario
						if ($adldap) {
							$id       = $row['id'];
							$queryUsr = "UPDATE users SET user_username = '" . $camposUsr['user_name'] . "',
																user_password = '" . md5 ($camposUsr['user_password']) . "'

											WHERE user_id = '" . $id . "'";

							if (mysql_query ($queryUsr, $dbDotProject)) {//Se actualizan los datos de contacto
								$queryUsr = "UPDATE contacts SET contact_first_name = '" . $camposUsr['first_name'] . "',
															 contact_last_name = '" . $camposUsr['last_name'] . "',
															 contact_company = 0,
															 contact_email = '" . $camposUsr['email1'] . "',
															 contact_icon = 'obj/contact'
											WHERE contact_id = '" . $id . "'";

								mysql_query ($queryUsr, $dbDotProject);

								if (mysql_query ($queryUsr, $dbDotProject)) {//Se actualizan los datos de contacto
									$query = "SELECT id FROM gacl_aro WHERE name = '" . $camposUsr['user_name'] . "'";

									$resultGroup = mysql_query ($query, $dbDotProject);

									if ($resultGroup) {
										$rowGroup   = mysql_fetch_array ($resultGroup);
										$queryGroup = "UPDATE gacl_groups_aro_map SET group_id = '" . $rolId . "' WHERE aro_id = '" . $rowGroup['id'] . "'";
										mysql_query ($queryGroup, $dbDotProject);
									}
								}
							} else {
								$this->errorMsg = "Error ingresando/actualizando datos del usuario en DotProject";
								$bStatus        = false;
							}
						}
					} else {//Se ingresa el usuario
						$id      = '1';
						$queryid = "SELECT max(user_id)+1 AS id FROM `users`";

						$resultId = mysql_query ($queryid, $dbDotProject);
						if ($resultId) {
							$rowId = mysql_fetch_array ($resultId);
							$id    = $rowId['id'];
						}

						$queryUsr = "INSERT INTO users (user_id,user_contact,user_username,user_password,user_parent,user_type,
													user_company,user_department,user_owner ,user_signature)

									VALUES ('" . $id . "','" . $id . "','" . $camposUsr['user_name'] . "','" . md5 ($camposUsr['user_password']) . "','" . $user_parent . "','" . $user_type . "',
	    									'" . $user_company . "','" . $user_departament . "','" . $user_owner . "','" . $user_signature . "')";

						if (mysql_query ($queryUsr, $dbDotProject)) {//Se crea la informaci�n del contacto
							$queryUsr = "INSERT INTO contacts (contact_id,contact_first_name,contact_last_name,contact_company,contact_email,contact_icon)
										VALUES ('" . $id . "','" . $camposUsr['first_name'] . "','" . $camposUsr['last_name'] . "',0,'" . $camposUsr['email1'] . "','obj/contact')";

							if (mysql_query ($queryUsr, $dbDotProject)) {
								$idAro   = '1';
								$queryid = "SELECT max(id)+1 AS id FROM `gacl_aro`";

								$resultId = mysql_query ($queryid, $dbDotProject);
								if ($resultId) {
									$rowId = mysql_fetch_array ($resultId);
									$idAro = $rowId['id'];
								}

								$queryUsr = "INSERT INTO gacl_aro (id,section_value,value,order_value,name,hidden)
											VALUES ('" . $idAro . "','user','" . $id . "',1,'" . $camposUsr['user_name'] . "',0)";

								if (mysql_query ($queryUsr, $dbDotProject)) {
									$queryUsr = "INSERT INTO gacl_groups_aro_map (group_id,aro_id)
											VALUES ('" . $rolId . "','" . $idAro . "')";

									mysql_query ($queryUsr, $dbDotProject);
								}

								$dbLogin = $this->conectaIntranetLogin ();

								if ($dbLogin) {
									$queryid = "SELECT max(id_login)+1 AS id_login FROM `dotproject`";

									$resultId = mysql_query ($queryid, $dbLogin);

									if ($resultId) {
										$rowId = mysql_fetch_array ($resultId);
										$id    = $rowId['id_login'];
									}

									$queryid  = "INSERT INTO dotproject VALUES (" . $id . ",'" . $camposUsr['user_name'] . "','" .
												$this->encrypt ($camposUsr['user_password'], 'estaeslaclave01EncryptadaDeEnacol')
												. "')";
									$resultId = mysql_query ($queryid, $dbLogin);
								}
							}
						} else {
							$this->errorMsg = "Error ingresando/actualizando datos del usuario en dotProject";
							$bStatus        = false;
						}
					}
				}
			}
			return $bStatus;
		}

		public function obtenerRolesDotProject ($dbDotProject, $mayuscula = true) {
			$lstRoles = array ();

			if (!$dbDotProject) {
				$dbDotProject = $this->conectaBDDotProject ();
			}

			$query = "SELECT id, value FROM `gacl_aro_groups`";

			$result = mysql_query ($query, $dbDotProject);

			if ($result) {
				while ($row = mysql_fetch_array ($result)) {
					if ($mayuscula) {
						array_push ($lstRoles, array ($row["id"], strtoupper (html_entity_decode ($row["value"], ENT_COMPAT, "UTF-8"))));
					} else {
						array_push ($lstRoles, array ($row["id"], html_entity_decode ($row["value"], ENT_COMPAT, "UTF-8")));
					}
				}
			}
			if ($mayuscula) {
				array_push ($lstRoles, array (-1, "SIN PRIVILEGIOS"));
			} else {
				array_push ($lstRoles, array (-1, "Sin privilegios"));
			}

			return $lstRoles;
		}

		public function obtenerRolDot ($username) {
			$lstRoles = array ();

			$dbDotProject = $this->conectaBDDotProject ();

			if ($dbDotProject) {
				$query  = "SELECT A.id, A.value
						FROM  `gacl_aro_groups` A
						INNER JOIN gacl_groups_aro_map B ON ( A.id = B.group_id )
						INNER JOIN gacl_aro C ON ( B.aro_id = C.id )
						INNER JOIN users D ON ( C.value = D.user_id
						AND C.section_value =  'user' )
						WHERE D.user_username = '" . $username . "'";
				$result = mysql_query ($query, $dbDotProject);

				if ($result) {
					$row = mysql_fetch_array ($result);
					if ($row) {
						array_push ($lstRoles, array ($row["id"], strtoupper (html_entity_decode ($row["value"], ENT_COMPAT, "UTF-8"))));
					} else {
						array_push ($lstRoles, array (-1, "SIN PRIVILEGIOS"));
					}
				}
			}

			return $lstRoles;
		}

		/*
     * Dado el nombre de usuario se contrasta los grupos asociados en el AD con
     * los roles existentes en el ProcessMaker
     */
		public function obtenerRolDotProjectAD ($adldap, $username, $dbDotProject) {
			$j         = 0;
			$lstGroups = $adldap->user_groups ($username, true);
			for ($k = 0; $k < count ($lstGroups); $k++) {
				$lstGroups[ $k ] = strtoupper ($lstGroups[ $k ]);
			}
			$lstRoles = $this->obtenerRolesDotProject ($dbDotProject);
			$roleId   = '';

			for ($i = 0; $i < count ($lstRoles); $i++) {
				$rolename = 'DOTPROJECT_' . $lstRoles[ $i ][1];
				if (in_array ($rolename, $lstGroups)) {
					$roleId = $lstRoles[ $i ][0];
					$j++;
				}
			}
			if ($j != 1) {
				return "";
			}//Tiene m�s de un rol asociado o no tiene rol asociado

			return $roleId;
		}

		public function creaGruposLDAP ($adldap) {
			$lstRoles = $this->obtenerRolesVTiger ();
			//Se sincronizan los grupos del VTiger con el active Directory
			for ($i = 0; $i < count ($lstRoles); $i++) {
				$rolename = 'VTIGER_' . $lstRoles[ $i ][1];
				$this->crearGruposActiveDirectory ($adldap, $rolename);
			}

			$dbOrangeHRM = $this->conectaBDHRM ();
			$lstRoles    = $this->obtenerRolesHRM ($dbOrangeHRM);

			for ($i = 0; $i < count ($lstRoles); $i++) {
				$rolename = 'HRM_' . $lstRoles[ $i ][1];
				$this->crearGruposActiveDirectory ($adldap, $rolename);
			}

			$dbProcessMaker = $this->conectaBDProcess ();
			$lstRoles       = $this->obtenerRolesProcessMaker ($dbProcessMaker);
			$roleId         = '';

			for ($i = 0; $i < count ($lstRoles); $i++) {
				$rolename = $lstRoles[ $i ][1];
				$this->crearGruposActiveDirectory ($adldap, $rolename);
			}

			$dbDotProject = $this->conectaBDDotProject ();
			$lstRoles     = $this->obtenerRolesDotProject ($dbDotProject);
			$roleId       = '';

			for ($i = 0; $i < count ($lstRoles); $i++) {
				$rolename = 'DOTPROJECT_' . $lstRoles[ $i ][1];
				$this->crearGruposActiveDirectory ($adldap, $rolename);
			}

			return;
		}

		public function crearGruposActiveDirectory ($adldap, $nameGroup) {
			$attributes = array (
				"group_name"  => $nameGroup,
				"description" => $nameGroup,
				"container"   => array (),
			);
			@$adldap->group_create ($attributes);
		}

		function encrypt ($string, $key) {
			$result = '';
			for ($i = 0; $i < strlen ($string); $i++) {
				$char    = substr ($string, $i, 1);
				$keychar = substr ($key, ($i % strlen ($key)) - 1, 1);
				$char    = chr (ord ($char) + ord ($keychar));
				$result .= $char;
			}
			return base64_encode ($result);
		}

		function decrypt ($string, $key) {
			$result = '';
			$string = base64_decode ($string);
			for ($i = 0; $i < strlen ($string); $i++) {
				$char    = substr ($string, $i, 1);
				$keychar = substr ($key, ($i % strlen ($key)) - 1, 1);
				$char    = chr (ord ($char) - ord ($keychar));
				$result .= $char;
			}
			return $result;
		}

		function esAdminCRM ($username) {
			global $adb;
			$query   = "SELECT is_admin FROM vtiger_users WHERE user_name='" . $username . "' AND deleted=0";
			$result  = $adb->query ($query);
			$isadmin = $adb->query_result ($result, 0, 'is_admin');
			if ($isadmin == 'on') {
				return true;
			}
			return false;
		}

		function esAdminHRM ($username) {
			$lstRol = $this->obtenerRolHRM ($username);

			if (strcasecmp ($lstRol[0][1], 'Admin') == 0) {
				return true;
			}
			return false;
		}

		function esAdminPM ($username) {
			$lstRol = $this->obtenerRolPM ($username);

			if (strcasecmp ($lstRol[0][1], 'PROCESSMAKER_ADMIN') == 0) {
				return true;
			}
			return false;
		}

		function esAdminDot ($username) {
			$lstRol = $this->obtenerRolDot ($username);

			if (strcasecmp ($lstRol[0][1], 'ADMIN') == 0) {
				return true;
			}
			return false;
		}

		function asignarRolAdminHRM ($username, $isadmin, $rolid) {
			$dbOrangeHRM = $this->conectaBDHRM ();

			if ($dbOrangeHRM) {

				$query = "SELECT id FROM hs_hr_users WHERE user_name = '" . $username . "'";

				$result = mysql_query ($query, $dbOrangeHRM);

				if ($result) {
					$row = mysql_fetch_array ($result);

					if ($row) {
						$id       = $row['id'];
						$queryUsr = "UPDATE hs_hr_users SET is_admin = '" . $isadmin . "', userg_id = '" . $rolid . "'
										WHERE id = '" . $id . "'";
						mysql_query ($queryUsr, $dbOrangeHRM);
					}
				}
			}
			return;
		}

		function asignarRolAdminPM ($username, $rolname, $rolid) {
			$dbProcessMaker  = $this->conectaBDProcess ();
			$dbProcessMaker2 = $this->conectaBDProcess2 ();

			if ($dbProcessMaker) {

				$query = "SELECT USR_UID AS id FROM USERS WHERE USR_USERNAME = '" . $username . "'";

				$result = mysql_query ($query, $dbProcessMaker2);

				if ($result) {
					$row = mysql_fetch_array ($result);

					if ($row) {
						$id       = $row['id'];
						$queryUsr = "UPDATE USERS SET USR_ROLE = '" . $rolname . "' WHERE USR_UID = '" . $id . "'";
						mysql_query ($queryUsr, $dbProcessMaker2);

						$queryUsr = "UPDATE USERS_ROLES SET ROL_UID = '" . $rolid . "' WHERE USR_UID = '" . $id . "'";
						mysql_query ($queryUsr, $dbProcessMaker);
					}
				}
			}
			return;
		}

		function asignarRolAdminDot ($username, $rolid) {
			$dbDotProject = $this->conectaBDDotProject ();

			if ($dbDotProject) {

				$query = "SELECT id AS id FROM gacl_aro WHERE name= '" . $username . "'";

				$result = mysql_query ($query, $dbDotProject);

				if ($result) {
					$row = mysql_fetch_array ($result);

					if ($row) {
						$id       = $row['id'];
						$queryUsr = "UPDATE gacl_groups_aro_map SET group_id = '" . $rolid . "'
										WHERE aro_id = '" . $id . "'";
						mysql_query ($queryUsr, $dbDotProject);
					}
				}
			}
			return;
		}

		/**
		 * Return Contact Id Associate to Current User
		 */
		function getContactId ($id = '') {
			global $adb, $plat_madre_empresafacil;

			if (!empty($this->contactid)) {
				return $this->contactid;
			}

			$user_name = $this->column_fields['user_name'];

			//Por el cambio de arquitectura se modifica su uso para que sea capaz de recuperar los datos de la bd central
			$adb = conectaPlataformaHija ($plat_madre_empresafacil);

			$result = $adb->pquery ("SELECT contactid FROM vtiger_users WHERE user_name = ?", array ($user_name));

			if ($result) {
				$contactid = $adb->query_result ($result, 0, 'contactid');
			}

			$adb = conectaPlataformaHija ($_SESSION['plat']);

			return $contactid;
		}

		function getUserByContactId ($contactid = '') {
			global $adb, $plat_madre_empresafacil;

			//Por el cambio de arquitectura se modifica su uso para que sea capaz de recuperar los datos de la bd central
			$adb = conectaPlataformaHija ($plat_madre_empresafacil);

			$result = $adb->pquery ("SELECT id FROM vtiger_users WHERE contactid = ?", array ($contactid));

			if ($result) {
				return $adb->query_result ($result, 0, 'id');
			}

			$adb = conectaPlataformaHija ($_SESSION['plat']);

			return;
		}

		function getHrmCategory ($userid) {
			global $adb;
			$result = $adb->pquery ("select hrm_categoriasid from vtiger_hrm_categorias where empleados like '$userid' or empleados like '$userid |%' or empleados like '%| $userid |%' or empleados like '%| $userid'");

			if ($result) {
				list($categoryid) = $adb->fetch_row ($result);
			}

			return $categoryid;
		}

		function getHrmCategoryName ($userid) {
			global $adb;
			$result = $adb->pquery ("select codigo, nombre from vtiger_hrm_categorias where empleados like '$userid' or empleados like '$userid |%' or empleados like '%| $userid |%' or empleados like '%| $userid'");

			$catname = '';

			if ($result && $adb->num_rows ($result) > 0) {
				list($codigo, $nombre) = $adb->fetch_row ($result);
				$catname = "$codigo $nombre";
			}

			return $catname;
		}

		function getInfoUserProfile ($userid) {
			global $adb;

			$query  = "SELECT *
            FROM `vtiger_users` u
            JOIN vtiger_user2role s2r ON ( s2r.userid = u.id )
            JOIN vtiger_role r ON ( r.roleid = s2r.roleid )
            WHERE u.id = $userid";
			$result = $adb->query ($query);
			$row    = $adb->fetchByAssoc ($result);

			$row['profileImage'] = getUserImageName ($row['id']);

			return $row;
		}

		function deletePermissionsInstance ($userid) {
			global $adb, $platPrincipal;

			$adbPrincipal = conectaPlataformaHija ($platPrincipal);

			$sql_email_usr = "SELECT email1, user_name FROM vtiger_users WHERE id = ?";
			$email         = $adb->query_result ($adb->pquery ($sql_email_usr, array ($userid)), 0, 'email1');
			$user_name     = $adb->query_result ($adb->pquery ($sql_email_usr, array ($userid)), 0, 'user_name');

			$sql_id_perfil = "SELECT profileid FROM vtiger_profile WHERE profilename = ?";
			$perfil_id     = $adb->query_result ($adb->pquery ($sql_id_perfil, array ($user_name)), 0, 'profileid');

			$sql = "DELETE FROM vtiger_instanceusers WHERE instancecode = ? AND username = ?";
			$adbPrincipal->pquery ($sql, array ($_SESSION['plat'], $email));

			$sql2 = "DELETE FROM vtiger_profile WHERE profileid = ?";
			$adb->pquery ($sql2, array ($perfil_id));

			$sql3 = "DELETE FROM vtiger_role WHERE roleid = ?";
			$adb->pquery ($sql3, array ($user_name));

			$sql4 = "DELETE FROM vtiger_role2profile WHERE roleid = ?";
			$adb->pquery ($sql4, array ($user_name));

			$adb->pquery ('DELETE FROM vtiger_profile2customview WHERE profileid=?', array ($perfil_id));

			$sql5 = "DELETE FROM vtiger_profile2globalpermissions WHERE profileid = ?";
			$adb->pquery ($sql5, array ($perfil_id));

			$sql6 = "DELETE FROM vtiger_profile2standardpermissions WHERE profileid = ?";
			$adb->pquery ($sql6, array ($perfil_id));

			$sql7 = "DELETE FROM vtiger_profile2utility WHERE profileid = ?";
			$adb->pquery ($sql7, array ($perfil_id));

			$sql8 = "DELETE FROM vtiger_profile2field WHERE profileid = ?";
			$adb->pquery ($sql8, array ($perfil_id));

			$sql9 = "DELETE FROM vtiger_profile2tab WHERE profileid = ?";
			$adb->pquery ($sql9, array ($perfil_id));

			$sql10 = "UPDATE vtiger_instances SET activeusers = activeusers - 1 WHERE code = ?";
			$adbPrincipal->pquery ($sql10, array ($_SESSION['plat']));
		}
		
		/**
		 * @param string $fieldName
		 *
		 * @return integer
		 * @throws Exception
		 */
		public function _getFieldPrecision ($fieldName) {
			if (empty ($fieldName)) {
				return 0;
			}
			$result = $this->db->pquery ('SELECT typeofdata FROM vtiger_field WHERE uitype IN (?,?,?) AND fieldname=? LIMIT 1',
				array (7, 9, 41, $fieldName)
			);
			
			if ($this->db->num_rows ($result) > 0) {
				$typeOfData = $this->db->fetchByAssoc ($result, -1, false);
				$dummy 	    = explode (',', $typeOfData ['typeofdata']);
			}
			$result = null;
			return (isset ($dummy)) ? intval ($dummy[1]) : 0;
		}

//=============TT11394] AJUSTES CONFIGURACION Y MI CUENTA PLATZILLA - PARTE 1 Keyla Rodríguez 28/10/2016
		function escribeProfile ($current_user) {
			require_once ('Smarty_setup.php');
			require_once 'include/utils/utils.php';
			global $mod_strings;
			global $app_strings;
			global $app_list_strings;

			global $theme, $adb;

			$theme_path = "themes/" . $theme . "/";
			$image_path = $theme_path . "images/";

			$oGetUserGroups = new GetUserGroups();
			$oGetUserGroups->getAllUserGroups ($current_user->id);

			$smarty = new vtigerCRM_Smarty;
			if (is_admin ($current_user) || $_REQUEST['record'] == $current_user->id || in_array ($current_user->column_fields['roleid'], $lstRoles)) {
				$buttons = "<a href='index.php?module=Users&action=EditView&record=" . $current_user->id . "'>
                            <button type='button' class='btn btn-primary'>" . $app_strings['LBL_EDIT_BUTTON_LABEL'] . "</button>
                        </a>";
				$smarty->assign ('EDIT_BUTTON', $buttons);

				$buttons = '<button type="button" data-modal="modal-1" class="md-trigger mrg-b-lg btn btn-success">' . $mod_strings['LBL_CHANGE_PASSWORD_BUTTON_LABEL'] . '</button>';

				$smarty->assign ('CHANGE_PW_BUTTON', $buttons);
				if ((isset ($lstRoles)) && (is_array ($lstRoles)) && (in_array ($current_user->column_fields ['roleid'], $lstRoles))) {
					$smarty->assign ('IS_ROLESUP', 'true');
				}
			}

			//echo "<pre> ".print_r(getBlocksLabel('Users',"detail_view",$current_user->column_fields,'LBL_USERLOGIN_ROLE'),true)."</pre>";
			$smarty->assign ("APP", $app_strings);
			$smarty->assign ("MODULE", 'Settings');
			$smarty->assign ("CATEGORY", 'Settings');
			$smarty->assign ("MOD", $mod_strings);
			$smarty->assign ("THEME", $theme);
			$smarty->assign ("IMAGE_PATH", $image_path);
			$smarty->assign ("IMAGES", "themes/images/");
			$smarty->assign ("BLOCKS", getSettingsBlocks ());
			$smarty->assign ("BLOCKS_ID", getSettingsBlockId ('LBL_PREFERENCIAS'));
			$smarty->assign ("BLOCKS_USER_INFO", getBlocksLabel ('Users', "detail_view", $current_user->column_fields, 'LBL_USERLOGIN_ROLE'));
			$smarty->assign ("BLOCKS_IMAGE_USER", getBlocksLabel ('Users', "detail_view", $current_user->column_fields, 'LBL_USER_IMAGE_INFORMATION'));
			$smarty->assign ("BLOCKS_MORE_INFO", getBlocksLabel ('Users', "detail_view", $current_user->column_fields, 'LBL_ADDRESS_INFORMATION'));
			$smarty->assign ("GROUP_COUNT", count ($oGetUserGroups->user_groups));
			$smarty->assign ("BLOCKS_CURRENCY_SETTINGS", getBlocksLabel ('Users', "detail_view", $current_user->column_fields, 'LBL_CURRENCY_CONFIGURATION'));
			$smarty->assign ("BLOCKS_ADVANCED_CONFIGURE", getBlocksLabel ('Users', "detail_view", $current_user->column_fields, 'LBL_USER_ADV_OPTIONS'));
			if (is_admin ($current_user)) {
				$smarty->assign ("IS_ADMIN", true);
			} else {
				$smarty->assign ("IS_ADMIN", false);
			}

			$smarty->assign ("ID", $current_user->id);
			$smarty->assign ("FIELDS", getSettingsFields ());
			$smarty->assign ("NUMBER_OF_COLUMNS", 4); //this is the number of columns in the settings page

			$smarty->assign ("CURRENT_USER_IMAGE", getUserImageName ($current_user->id));

			return $smarty->fetch ("Home/ProfileUser.tpl");
		}

		/**
		 * Get list view query (send more WHERE clause condition if required)
		 *
		 * @param string $moduleName
		 * @param string $additionalWhereClause
		 *
		 * @return string
		 */
		public function getListQuery ($moduleName, $additionalWhereClause = '') {
			$sql = "SELECT
						vtiger_users.id,
						vtiger_users.user_name,
						vtiger_users.first_name,
						vtiger_users.last_name,
						vtiger_users.email1,
						vtiger_users.phone_mobile,
						vtiger_users.phone_work,
						vtiger_users.is_admin,
						vtiger_users.status,
						vtiger_users.email2,
						vtiger_user2role.roleid AS roleid,
						vtiger_role.depth AS depth
					FROM
						vtiger_users
						INNER JOIN vtiger_user2role ON vtiger_users.id=vtiger_user2role.userid
						INNER JOIN vtiger_role ON vtiger_user2role.roleid=vtiger_role.roleid
					WHERE
						vtiger_users.deleted=0
						{$additionalWhereClause}";
			return trim (preg_replace ('/\s+/S', ' ', $sql));
		}
	}
