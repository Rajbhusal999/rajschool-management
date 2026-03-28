<?php
/**
 * Email Helper Function
 * Sends emails using PHPMailer with configured SMTP settings
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'includes/email_config.php';

/**
 * Send Email Function
 * 
 * @param string $to_email Recipient email address
 * @param string $to_name Recipient name
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @return array ['success' => bool, 'message' => string]
 */
function sendEmail($to_email, $to_name, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        $mail->SMTPDebug = SMTP_DEBUG;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body); // Plain text version

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"];
    }
}

/**
 * Send Verification Code Email
 * 
 * @param string $to_email School email
 * @param string $school_name School name
 * @param string $code Verification code
 * @return array ['success' => bool, 'message' => string]
 */
function sendVerificationCode($to_email, $school_name, $code)
{
    $subject = "Your Payment Verification Code - Smart विद्यालय";

    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
            .code-box { background: white; border: 2px dashed #667eea; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
            .code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 5px; }
            .footer { background: #1f2937; color: #9ca3af; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>Smart विद्यालय</h1>
                <p style='margin: 10px 0 0 0;'>School Management System</p>
            </div>
            <div class='content'>
                <h2 style='color: #1f2937;'>Hello, {$school_name}!</h2>
                <p>Your payment verification code has been generated. Please use this code to verify your payment and activate your subscription.</p>
                
                <div class='code-box'>
                    <p style='margin: 0; color: #6b7280; font-size: 14px;'>Your Verification Code</p>
                    <div class='code'>{$code}</div>
                </div>
                
                <p><strong>Important:</strong></p>
                <ul>
                    <li>This code is valid for 24 hours</li>
                    <li>Do not share this code with anyone</li>
                    <li>If you didn't request this code, please contact support</li>
                </ul>
                
                <p>To activate your subscription, please enter this code in your dashboard.</p>
                
                <p>If you have any questions, feel free to contact our support team.</p>
                
                <p>Best regards,<br><strong>Smart विद्यालय Team</strong></p>
            </div>
            <div class='footer'>
                <p>© 2026 Smart विद्यालय. All rights reserved.</p>
                <p>This is an automated email. Please do not reply to this message.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($to_email, $school_name, $subject, $body);
}

/**
 * Send Password Reset Email
 * 
 * @param string $to_email User email
 * @param string $school_name School name
 * @param string $reset_link Password reset link
 * @return array ['success' => bool, 'message' => string]
 */
function sendPasswordResetEmail($to_email, $school_name, $reset_link)
{
    $subject = "Password Reset Request - Smart विद्यालय";

    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { background: #1f2937; color: #9ca3af; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 10px 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin: 0;'>Smart विद्यालय</h1>
                <p style='margin: 10px 0 0 0;'>Password Reset Request</p>
            </div>
            <div class='content'>
                <h2 style='color: #1f2937;'>Hello, {$school_name}!</h2>
                <p>We received a request to reset your password. Click the button below to set a new password:</p>
                
                <div style='text-align: center;'>
                    <a href='{$reset_link}' class='button'>Reset Password</a>
                </div>
                
                <p>Or copy and paste this link into your browser:</p>
                <p style='background: #e5e7eb; padding: 10px; border-radius: 5px; word-break: break-all;'>{$reset_link}</p>
                
                <p><strong>Important:</strong></p>
                <ul>
                    <li>This link is valid for 1 hour</li>
                    <li>If you didn't request this, please ignore this email</li>
                    <li>Your password won't change until you create a new one</li>
                </ul>
                
                <p>Best regards,<br><strong>Smart विद्यालय Team</strong></p>
            </div>
            <div class='footer'>
                <p>© 2026 Smart विद्यालय. All rights reserved.</p>
                <p>This is an automated email. Please do not reply to this message.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($to_email, $school_name, $subject, $body);
}

/**
 * Send Custom Email
 * 
 * @param string $to_email Recipient email
 * @param string $to_name Recipient name
 * @param string $subject Email subject
 * @param string $html_body HTML email body
 * @return array ['success' => bool, 'message' => string]
 */
function sendCustomEmail($to_email, $to_name, $subject, $html_body)
{
    return sendEmail($to_email, $to_name, $subject, $html_body);
}
?>