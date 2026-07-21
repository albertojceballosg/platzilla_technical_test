<?php
//require_once('../lists/admin/PHPMailer/PHPMailerAutoload.php');
require_once ('include/phpmailer/PHPMailerAutoload.php');
//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

$mail             = new PHPMailer();

$body             = file_get_contents('contents.html');
$body             = eregi_replace("[\]",'',$body);

$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host       = "mail.platzilla.com"; // SMTP server
$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
                                           // 1 = errors and messages
                                           // 2 = messages only
$mail->SMTPAuth   = false;                  // enable SMTP authentication
$mail->SMTPSecure = "tls";                 // sets the prefix to the servier
$mail->Host       = "mail.platzilla.com";      // sets GMAIL as the SMTP server
$mail->Port       = 587;                   // set the SMTP port for the GMAIL server
$mail->Username   = "no_reply@platzilla.com";  // GMAIL username
$mail->Password   = "23n2AeLny5";            // GMAIL password

$mail->SetFrom('no_reply@platzilla.com', 'Platzilla');

$mail->AddReplyTo('avergara@timemanagement.es', 'Alfredo Vergara');

$mail->Subject    = "PHPMailer Test Subject via smtp, basic";

$mail->AltBody    = "Para ver el mensaje, por vafor utilice un visualizador de correo compatible con HTML!"; // optional, comment out and test

$mail->MsgHTML($body);

$address = "alenverme@gmail.com";
$mail->AddAddress($address, "Alfredo Vergara");

//$mail->AddAttachment("images/phpmailer.gif");      // attachment
//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}
?>   