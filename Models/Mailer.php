<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require  __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require  __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require  __DIR__ . '/../vendor/phpmailer/src/SMTP.php';
include_once(__DIR__ . '/../config.php');

class Mailer {

    private $sender_email;

    function custom_phpmailer_init($phpmailer) {
        $phpmailer->Host =$mailSmtpHost;
        $phpmailer->Port = $mailPort;
        $phpmailer->Username = $mailUsername;
        $phpmailer->Password = $mailPAssword;
        $this->senderEmail =$mailSender;

        $phpmailer->SMTPAuth = true;
        $phpmailer->SMTPSecure = 'ssl';
        $phpmailer->IsSMTP();
    }

    private function isConfigured(){
        return $phpmailer->Host && $phpmailer->Host != "";
    }

    function mail($email, $subject, $message){
        if($this->isConfigured()){
            $mail = new PHPMailer(true);
            $mail->setFrom($this->senderEmail, 'Sistema Prenotazioni teatro');
            $mail->addAddress($email);
            $mail->addReplyTo(this->senderEmail);
            $mail->isHTML(true);  
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = $message;
            $mail->send();
        }else {
            mail($email, $subject, $message);
        }
    }


}
?>