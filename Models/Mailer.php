<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require  __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require  __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require  __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

class Mailer {


    protected function __construct() {
        
    }

    public static function mail($email, $subject, $message){
        include(__DIR__ . '/../config.php');
        if($mailSmtpHost && $mailSmtpHost != ""){
            try {
                $mail = new PHPMailer(true);
                $mail->Host =$mailSmtpHost;
                $mail->Port = $mailPort;
                $mail->Username = $mailUsername;
                $mail->Password = $mailPAssword;
                $mail->setFrom($senderEmail, 'Sistema Prenotazioni teatro');
                $mail->addReplyTo($senderEmail);
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'ssl';
                $mail->IsSMTP();
                $mail->addAddress($email);
                $mail->isHTML(true);  
                $mail->Subject = $subject;
                $mail->Body    = $message;
                $mail->AltBody = $message;
                $mail->send();
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }else {
            mail($email, $subject, $message);
        }
    }


}
?>