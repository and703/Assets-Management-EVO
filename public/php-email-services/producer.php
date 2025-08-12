<?php
require 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

error_reporting(E_ALL);
ini_set('display_errors', 0);

$apikey = $_ENV['API_KEY'];

header('Content-Type: application/json');

if (!$apikey) {
    echo json_encode([
        "success" => false,
        "error" => "API key is not set"
    ]);

    exit;
}

$get_apikey = $_GET['apikey'] ?? '';
if ($get_apikey !== $apikey) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid API key"
    ]); 
    exit;
}

// Konfigurasi RabbitMQ
$connection = new AMQPStreamConnection(
    $_ENV['RABBITMQ_HOST'],
    $_ENV['RABBITMQ_PORT'],
    $_ENV['RABBITMQ_USER'],
    $_ENV['RABBITMQ_PASS']
);
$channel = $connection->channel();

$channel->queue_declare('email_queue', false, true, false, false);

// Ambil input dari request
$data = json_decode(file_get_contents("php://input"), true);

if (isset($_REQUEST['to']) && isset($_REQUEST['body'])  && isset($_REQUEST['subject'])) {
    // format multi pake (;) email@mail.com;email1@mail.com
    $data['to'] = $_REQUEST['to'];
    $data['body'] = $_REQUEST['body'];
    $data['subject'] = $_REQUEST['subject'];

    // optional
    $data['cc'] = (isset($_REQUEST['cc'])) ? $_REQUEST['cc'] : "";
    $data['bcc'] = (isset($_REQUEST['bcc'])) ? $_REQUEST['bcc'] : "";
    $data['reply'] = (isset($_REQUEST['reply'])) ? $_REQUEST['reply'] : "";

    $data['attachments'] = (isset($_REQUEST['attachments'])) ? $_REQUEST['attachments'] : "";
    // [
    //             'filename' => 'laporan.pdf',
    //             'url' => 'https://example.com/files/laporan.pdf',
    //             'mime' => 'application/pdf'
    //         ],
    //         [
    //             'filename' => 'gambar.png',
    //             'url' => 'https://example.com/files/gambar.png',
    //             'mime' => 'image/png'
    //         ]
}

if (!isset($data['to']) || !isset($data['body']) || !isset($data['subject'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Missing 'to' or 'subject' or 'body' parameter"
    ]);
    exit;
}

$emailData = json_encode([
    'to' => $data['to'],
    'subject' => $data['subject'],
    'body' => $data['body'],

    'cc' => (isset($data['cc'])) ? $data['cc'] : "",
    'bcc' => (isset($data['bcc'])) ? $data['bcc'] : "",
    'reply' => (isset($data['reply'])) ? $data['reply'] : "",
    'attachments' => (isset($data['attachments'])) ? $data['attachments'] : "",
]);

$msg = new AMQPMessage($emailData, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

$channel->basic_publish($msg, '', 'email_queue');

echo json_encode([
    "success" => true,
    "message" => "Email request added to queue"
]);

$channel->close();
$connection->close();
