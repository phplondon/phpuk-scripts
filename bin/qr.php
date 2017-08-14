<?php

chdir(__DIR__ . '/../');

use JeroenDesloovere\VCard\VCard;

$config = require('config/local.php');
require('src/EventBrite.php');
require('src/VCard/VCard.php');
require('src/VCard/VCardException.php');
require('src/VCard/VCardParser.php');
require('src/Behat/Transliterator.php');
require('src/phpqrcode/phpqrcode.php');

$authentication_tokens = array(
    'token' => $config['eventbrite']['oauthToken']
);

$eb = new EventBrite($authentication_tokens);
$endpoint = 'events/' . $config['eventbrite']['eventId'] . '/attendees/';
$params = array('method' => 'GET');
$result = $eb->DoRequest($endpoint, $params);
$cont = $result->pagination->continuation;

while ($cont) {
    $params['continuation'] = $cont;
    $r = $eb->DoRequest($endpoint, $params);
    $result->attendees = array_merge($result->attendees, $r->attendees);
    $cont = $r->pagination->continuation;
}

echo "Generating " . count($result->attendees) . " codes";

foreach ($result->attendees as $attendee) {
    $barcode = null;
    foreach ($attendee->barcodes as $barcodeObject) {
        $barcode = $barcodeObject->barcode;
    }

    // define vcard
    $vcard = new VCard();

    // add personal data
    $vcard->addName($attendee->profile->last_name, $attendee->profile->first_name);

    // add work data
    $vcard->addCompany($attendee->profile->company);
    $vcard->addJobtitle($attendee->profile->job_title);
    $vcard->addEmail($attendee->profile->email);

    $numberFilename = __DIR__ . '/../qr/' . $attendee->id . '_number.png';
    echo 'Generating QR number for ' . $attendee->id . ' ' . $barcode . PHP_EOL;
    QRcode::png($barcode, $numberFilename, 'L', 30, 4);

    $vcardFilename = __DIR__ . '/../qr/' . $attendee->id . '_vcard.png';
    echo 'Generating QR vcard for ' . $attendee->id . ' ' . $barcode . PHP_EOL;
    QRcode::png($vcard->buildVCard(), $vcardFilename, 'L', 30, 4);
}
