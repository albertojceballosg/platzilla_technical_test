<?php
	require_once ('include/utils/PlatzillaUtils.class.php');

	abstract class SystemVariables {

		const DEFAULT_MAIL = 'notificaciones@platzilla.com';
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return string;
		 */
		private static function getAssociatedAccount (PearDatabase $adb, $userId) {
			$emails = self::DEFAULT_MAIL;
			$result   = $adb->pquery ('SELECT  emailaddress FROM vtiger_webmail_accounts WHERE userid=?', array ($userId));
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$arrayEmail [] = $row ['emailaddress'];
				}
				if (((count ($arrayEmail) == 1) && (in_array ($emails, $arrayEmail)))) {
					$arrayEmail [] = PlatzillaUtils::purify ($_SESSION, 'authenticated_user_email');
					$emails   = join (',', $arrayEmail);
				} else {
					$emails   = join (',', $arrayEmail);
				}
			} else {
				$emails .= ',' . PlatzillaUtils::purify ($_SESSION, 'authenticated_user_email');
			}

			return $emails;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $dataSourceValues
		 *
		 * @return string|null
		 */
		private static function getRecordOwnerEmail (PearDatabase $adb, $dataSourceValues) {
			if ((empty ($dataSourceValues)) || (!isset ($dataSourceValues ['assigned_user_id']))) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE id=?', array ($dataSourceValues ['assigned_user_id']));
			if ($adb->num_rows ($result) > 0) {
				$row   = $adb->fetchByAssoc ($result, -1, false);
				$email = $row ['email1'];
			} else {
				$email = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $email;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $dataSourceValues
		 *
		 * @return string|null
		 */
		private static function getRecordOwnerFullName (PearDatabase $adb, $dataSourceValues) {
			if ((empty ($dataSourceValues)) || (!isset ($dataSourceValues ['assigned_user_id']))) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE id=?', array ($dataSourceValues ['assigned_user_id']));
			if ($adb->num_rows ($result) > 0) {
				$row      = $adb->fetchByAssoc ($result, -1, false);
				$fullName = trim ("{$row ['first_name']} {$row ['last_name']}");
			} else {
				$fullName = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fullName;
		}

		/**
		 * @return array
		 */
		public static function getAvailableVariables () {
			$variables = array (
				'ASSIGNED_USER_EMAIL'    => 'Dirección de correo del dueño del registro',
				'ASSIGNED_USER_FULLNAME' => 'Nombre y apellidos del dueño del registro',
				'ASSIGNED_USER_ID'       => 'Usuario dueño del registro',
				'CURRENT_USER_EMAIL'     => 'Dirección de correo del usuario actual',
				'USER_ASSOCIATED_EMAIL'  => 'Dirección de correo asociada del usuario actual',
				'CURRENT_USER_FULLNAME'  => 'Nombre y apellidos del usuario actual',
				'CURRENT_USER_ID'        => 'Usuario actual',
				'CURRENT_USER_INSTANCE'  => 'Instancia del usuario actual',
				'CURRENT_USER_PLATFORM'  => 'Plataforma del usuario actual',
				'IP_ADDRESS'             => 'Dirección IP del usuario actual',
				'NOW'                    => 'Fecha y hora actual (yyyy-mm-dd hh:mm:ss)',
				'PLATZILLA_ROOT_URI'     => 'Carpeta de instalación de Platzilla',
				'PLATZILLA_URL'          => 'URL de Platzilla',
				'RECORD_ID'              => 'ID del registro que se está procesando',
				'TODAY'                  => 'Fecha actual (yyyy-mm-dd)',
			);
			asort ($variables);
			return $variables;
		}

		/**
		 * @return array
		 */
		public static function getAvailableVariableTypes () {
			$variables = array (
				'ASSIGNED_USER_EMAIL'    => 'EMAIL',
				'ASSIGNED_USER_FULLNAME' => 'TEXT',
				'ASSIGNED_USER_ID'       => 'USER',
				'CURRENT_USER_EMAIL'     => 'EMAIL',
				'CURRENT_USER_FULLNAME'  => 'TEXT',
				'CURRENT_USER_ID'        => 'USER',
				'CURRENT_USER_INSTANCE'  => 'SYSTEM',
				'CURRENT_USER_PLATFORM'  => 'SYSTEM',
				'IP_ADDRESS'             => 'SYSTEM',
				'NOW'                    => 'DATE',
				'PLATZILLA_ROOT_URI'     => 'SYSTEM',
				'PLATZILLA_URL'          => 'SYSTEM',
				'RECORD_ID'              => 'RECORD',
				'TODAY'                  => 'DATE',
				'USER_ASSOCIATED_EMAIL'  => 'EMAIL',
			);
			asort ($variables);
			return $variables;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $dataSourceValues
		 *
		 * @return array
		 */
		public static function getAvailableVariableValues (PearDatabase $adb, $dataSourceValues) {
			global $site_URL, $current_user;
			return array (
				'ASSIGNED_USER_EMAIL'    => self::getRecordOwnerEmail ($adb, $dataSourceValues),
				'ASSIGNED_USER_FULLNAME' => self::getRecordOwnerFullName ($adb, $dataSourceValues),
				'ASSIGNED_USER_ID'       => $dataSourceValues ['assigned_user_id'],
				'CURRENT_USER_EMAIL'     => PlatzillaUtils::purify ($_SESSION, 'authenticated_user_email'),
				'CURRENT_USER_FULLNAME'  => PlatzillaUtils::purify ($_SESSION, 'authenticated_user_fullname'),
				'CURRENT_USER_ID'        => PlatzillaUtils::purify ($_SESSION, 'authenticated_user_id'),
				'CURRENT_USER_INSTANCE'  => PlatzillaUtils::purify ($_SESSION, 'platInstancia'),
				'CURRENT_USER_PLATFORM'  => PlatzillaUtils::purify ($_SESSION, 'plat'),
				'IP_ADDRESS'             => $_SERVER ['REMOTE_ADDR'],
				'NOW'                    => date ('Y-m-d H:i:s'),
				'PLATZILLA_ROOT_URI'     => PlatzillaUtils::getPlatzillaRootUri (),
				'PLATZILLA_URL'          => $site_URL,
				'RECORD_ID'              => $dataSourceValues ['record_id'],
				'TODAY'                  => date ('Y-m-d'),
				'USER_ASSOCIATED_EMAIL'  => self::getAssociatedAccount ($adb, $current_user->id),
			);
		}

		/**
		 * @param $variable
		 *
		 * @return string
		 */
		public static function getLabel ($variable) {
			if (empty ($variable)) {
				return null;
			}

			$availableVariables = self::getAvailableVariables ();
			if (!in_array ($variable, array_keys ($availableVariables))) {
				return null;
			} else {
				return $availableVariables [ $variable ];
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $variable
		 * @param array $dataSourceValues
		 *
		 * @return string
		 */
		public static function getValue (PearDatabase $adb, $variable, $dataSourceValues) {
			if (empty ($variable)) {
				return null;
			}

			$availableVariables = self::getAvailableVariableValues ($adb, $dataSourceValues);
			if (!in_array ($variable, array_keys ($availableVariables))) {
				return null;
			} else {
				return $availableVariables [ $variable ];
			}
		}

	}
