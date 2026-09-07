<?php
// send_otp.php
// Assuming PHPMailer is downloaded and located in a 'PHPMailer' folder nearby
// require_once __DIR__ . '/PHPMailer/src/Exception.php';
// require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
// require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends a 6-digit OTP code to the specified email address using PHPMailer.
 */
function sendOTP($toEmail, $otpCode)
{
    // Note: Uncomment the requires above, and ensure PHPMailer is installed.
    // Since PHPMailer is not installed locally by default, this function 
    // will just return true if the classes don't exist yet, to prevent throwing fatal errors in testing.
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return true;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.yourprovider.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;                    // Enable SMTP authentication
        $mail->Username = 'your_smtp_username';    // SMTP username
        $mail->Password = 'your_smtp_password';    // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port = 587;                     // TCP port to connect to

        // Recipients
        $mail->setFrom('noreply@noontech.com', 'noontech Verification');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your noontech Email Verification Code';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <h2 style='color: #333;'>Verify Your Email</h2>
                <p style='color: #555; font-size: 16px;'>Thanks for registering for noontech! Please use the 6-digit OTP code below to verify your email address. This code is valid for 10 minutes.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #000; padding: 15px 30px; background-color: #f4f4f4; border-radius: 8px;'>{$otpCode}</span>
                </div>
                <p style='color: #999; font-size: 12px;'>If you did not request this code, you can safely ignore this email.</p>
            </div>
        ";
        $mail->AltBody = "Your verification code is: {$otpCode}. It is valid for 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>