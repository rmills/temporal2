<?php

/**
 * This is a wrapper for PHPMailer
 * http://code.google.com/a/apache-extras.org/p/phpmailer/
 * 
 * ENABLE_MAIL must be set to true in your config.inc.php
 */

namespace Module;

class Sendsmtp extends Module {

    /**
     * ENABLE_MAIL must be set to true in your config.inc.php
     * 
     * @param string $to 
     * @param string $subject
     * @param string $body
     * @param array $attachments
     * @return boolean
     */
    public static function send($to, $subject, $body, $attachments = array()) {
        if (ENABLE_MAIL) {
            $mail = new \PHPMailer();

            //$body             = preg_replace('/[\]/','',$body);

            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->Host = MAIL_HOST; // SMTP server
            $mail->SMTPDebug = MAIL_SMTP_DEBUG;                     // enables SMTP debug information (for testing)
            // 1 = errors and messages
            // 2 = messages only
            $mail->SMTPAuth = MAIL_SMTP_AUTH;                  // enable SMTP authentication
            $mail->Host = MAIL_HOST; // sets the SMTP server
            $mail->Port = MAIL_PORT;                    // set the SMTP port for the GMAIL server
            $mail->Username = MAIL_USERNAME; // SMTP account username
            $mail->Password = MAIL_PASSWORD;        // SMTP account password
            $mail->SMTPSecure = MAIL_SECURE;

            $mail->SetFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);

            $mail->Subject = $subject;

            //$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

            $mail->MsgHTML($body);
            $mail->AddAddress($to);

            foreach ($attachments as $v) {
                $mail->AddAttachment($v);      // attachment
            }

            if (!$mail->Send()) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

}
