<?php
require 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Konfigurasi RabbitMQ
$connection = new AMQPStreamConnection(
    $_ENV['RABBITMQ_HOST'],
    $_ENV['RABBITMQ_PORT'],
    $_ENV['RABBITMQ_USER'],
    $_ENV['RABBITMQ_PASS']
);
$channel = $connection->channel();

$channel->queue_declare('email_queue', false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) {
    $data = json_decode($msg->body, true);
    if (!isset($data['to']) || !isset($data['subject']) || !isset($data['body'])) {
        echo " [x] Invalid message format\n";
        return;
    }

    $to = $data['to'];
    $cc = $data['cc'];
    $bcc = $data['bcc'];
    $reply = $data['reply'];

    // echo " [✓] Email to {$to}\n";
    // echo " [✓] Email to {$cc}\n";
    // echo " [✓] Email to {$bcc}\n";
    // echo " [✓] Email to {$reply}\n";

    $subject = $data['subject'];
    $body = $data['body'];

    $mail = new PHPMailer(true);

    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Set email
        $mail->setFrom($_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);
        // $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $to_email = explode(";", $to);
        if (count($to_email) > 0) {
            foreach ($to_email as $email) {
                $mail->addAddress($email);
            }
        }

        $cc_email = explode(";", $cc);
        if (count($cc_email) > 0) {
            foreach ($cc_email as $email) {
                if ($email != "") {
                    $mail->addCC($email);
                }
            }
        }

        $bcc_email = explode(";", $bcc);
        if (count($bcc_email) > 0) {
            foreach ($bcc_email as $email) {
                if ($email != "") {
                    $mail->addBCC($email);
                }
            }
        }

        $reply_email = explode(";", $reply);
        if (count($reply_email) > 0) {
            foreach ($reply_email as $email) {
                if ($email != "") {
                    $mail->addReplyTo($email);
                }
            }
        }

        // Tambahkan lampiran
        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                if (!empty($attachment['url']) && !empty($attachment['filename'])) {
                    $fileContent = @file_get_contents($attachment['url']);  // gunakan @ untuk suppress warning

                    if ($fileContent !== false) {
                        $mail->addStringAttachment(
                            $fileContent,
                            $attachment['filename'],
                            'base64',
                            $attachment['mime'] ?? 'application/octet-stream'
                        );
                    } else {
                        error_log("Gagal unduh attachment dari URL: " . $attachment['url']);
                    }
                }
            }
        }

        $mail->send();
        echo " [✓] Email sent to {$to}\n";
    } catch (Exception $e) {
        echo " [x] Failed to send email: {$mail->ErrorInfo}\n";
    }

    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_consume('email_queue', '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
