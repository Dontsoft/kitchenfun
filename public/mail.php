<?php
use function GuzzleHttp\json_encode;
use function GuzzleHttp\json_decode;

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if (!defined('MAIL_PASSWORD') || strlen(MAIL_PASSWORD) <= 0 ||
!defined('MAIL_USERNAME') || strlen(MAIL_USERNAME) <= 0 ||
!defined('MAIL_SERVER') || strlen(MAIL_SERVER) <= 0 ||
!defined('MAIL_FROM') || strlen(MAIL_FROM) <= 0 ||
!defined('MAIL_FROM_MAIL') || strlen(MAIL_FROM_MAIL) <= 0) {
   http_response_code(500);
   echo json_encode((object)["message" => "Config missing"]);
   exit();
}

if (!isset($_POST["data"])) {
   http_response_code(400);
   echo json_encode((object)["message" => "Bad request"]);
   exit();
}

$data = json_decode($_POST["data"]);

if (!isset($data->reciever) || !isset($data->reciever->name) || !isset($data->reciever->mail) ||
   !isset($data->send_to_organizer) || !isset($data->organizer) || !is_array($data->organizer) ||
   !isset($data->title) || !isset($data->pretext) || !isset($data->starter) || !isset($data->main) ||
   !isset($data->dessert) || !isset($data->endtext)) {
      http_response_code(400);
      echo json_encode((object)["message" => "Parameter missing"]);
      exit();
}

$receiver_mail = $data->reciever->mail;
$receiver_name = $data->reciever->name;
$send_to_organizer = $data->send_to_organizer;
$organizer = $data->organizer;
$title = base64_decode($data->title);
$pretext = base64_decode($data->pretext);
$starter = base64_decode($data->starter);
$main = base64_decode($data->main);
$dessert = base64_decode($data->dessert);
$endtext = base64_decode($data->endtext);

try {
   $transport = (new Swift_SmtpTransport(MAIL_SERVER, MAIL_PORT))->setUsername(MAIL_USERNAME)->setPassword(MAIL_PASSWORD);
   $mailer = new Swift_Mailer($transport);
   $message = new Swift_Message();
   $message->setSubject('SWING.kitchenfun Infomail');
   $message->setFrom([MAIL_FROM_MAIL => MAIL_FROM]);
   $message->setTo([$receiver_mail => $receiver_name]);
   if ($send_to_organizer) {
      $message->setBcc($organizer);
   }
   $message->setReplyTo($organizer);
   $template = file_get_contents(__DIR__ . "/../template.html");
   $template = str_replace('%TITLE%', $title, $template);
   $template = str_replace('%TEXT%', $pretext, $template);
   $template = str_replace('%STARTER%', $starter, $template);
   $template = str_replace('%MAIN%', $main, $template);
   $template = str_replace('%DESSERT%', $dessert, $template);
   $template = str_replace('%END_TEXT%', $endtext, $template);
   $message->setBody($template, 'text/html');
   $result = $mailer->send($message);{

   }
   if (!$result) {
      http_response_code(500);
      echo json_encode((object)["message" => "Some error occured"]);
   } else {
      http_response_code(200);
      echo json_encode((object)["message" => "Success"]);
   }
} catch (Exception $e) {
   http_response_code(500);
   echo json_encode((object)["message" => $e->getMessage()]);
}