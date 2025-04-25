<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function send_email($to, $subject, $message) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Server Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'edumart.ucv@gmail.com'; // Replace with your email
        $mail->Password = 'advi gzmd rifj nrnt'; // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email Headers
        $mail->setFrom('edumart.ucv@gmail.com', 'EduMart');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $message;

        // Send Email
        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
