<?php
require_once ('include/phpmailer/class.phpmailer.php');
require_once ('include/phpmailer/class.smtp.php');
try {
				$mailer           = new PHPMailer ();
				$mailer->CharSet  = 'UTF-8';
				$mailer->Encoding = 'quoted-printable';
				$mailer->IsHTML (true);
				$mailer->IsSMTP ();
				$mailer->SMTPSecure  = 'tls';
				$mailer->SMTPAuth    = true;
				$mailer->Host        = 'mail.platzilla.com';
				$mailer->SMTPDebug  = 2;
				$mailer->Username    = 'no_reply@platzilla.com';
				$mailer->Password    = '23n2AeLny5';
				$mailer->Port        = 587;
				$mailer->SMTPOptions = array (
					'ssl' => array (
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true
					)
				);

				$mailer->From     = 'no_reply@platzilla.com';
				$mailer->FromName = 'Platzilla';
				$recipient = 'testplatzilla21@gmail.com';
				$mailer->AddAddress ($recipient, 'David Polo');
				$mailer->Subject = 'Reunión el dia Viernes - 20181219!!!';
				$body             = file_get_contents('contents.html');
				$body             = eregi_replace("[\]",'',$body);
				$mailer->Body    = $body;
				$result = $mailer->send ();
				if (!$result) {
					$lastError = $mailer->ErrorInfo;
				}
			} catch (Exception $e) {
				$lastError = $e->getMessage ();
				$result          = false;
			}
?>   