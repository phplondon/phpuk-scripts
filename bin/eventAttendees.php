<?php

chdir(__DIR__ . '/../');
$config = require('config/local.php');
require('src/EventBrite.php');

$authentication_tokens = array(
    'token' => $config['eventbrite']['oauthToken']
);

$eb = new EventBrite($authentication_tokens);
$endpoint = 'events/' . $config['eventbrite']['eventId'] . '/attendees/';
$result = $eb->DoRequest($endpoint, array('method' => 'POST'));

$data = array();

$thursday = strtotime('2017-08-17');
$friday = strtotime('2017-08-18');

//$checkIns = array(
//    strtotime('2013-02-21 07:00'),
//    strtotime('2013-02-21 08:00'),
//    strtotime('2013-02-21 09:15'),
//    strtotime('2013-02-21 10:00'),
//    strtotime('2013-02-21 11:45'),
//    strtotime('2013-02-22 07:00'),
//    strtotime('2013-02-22 08:00'),
//    strtotime('2013-02-22 09:30'),
//    strtotime('2013-02-22 10:00'),
//    strtotime('2013-02-22 11:00'),
//);

foreach ($result->attendees as $attendee) {
    if ($attendee->status != 'Attending') {
        continue;
    }
    
    $date = null;
    $floor = 0;

    foreach ($attendee->barcodes as $barcodeObject) {
        $barcode = $barcodeObject->barcode;
        if ((string) $barcode->status == 'used') {
            $date = strtotime($barcode->changed) + 8 * 3600;
            break;
        }
    }

    if ($date) {
        $floor = floor($date / 900) * 900;
    }
    
    foreach ($attendee->answers as $answer) {
        if ($answer->question_id = '14185194') {
            $gender = $answer->answer;
        }
    }

    $data[$attendee->id] = array(
        'lastname' => $attendee->profile->last_name,
        'firstname' => $attendee->profile->first_name,
        'company' => $attendee->profile->company,
        'title' => $attendee->profile->job_title,
        'gender' => empty($attendee->gender) ? 'n/a' : $attendee->gender,
        'checkedInThursday' => ($floor >= $thursday && $floor < $thursday + 86400) ? 1 : null,
        'checkedInFriday' => ($floor >= $friday && $floor < $friday + 86400) ? 1 : null,
        'checkedInAt' => date('H:i', $floor),
        'i' => 1
    );
}

ksort($data);

foreach ($data as $row) {
   $row = array_map(function($value) {
       return '"' . $value . '"';
   }, $row);

   echo implode(',', $row) . PHP_EOL;
}