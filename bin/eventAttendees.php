<?php

chdir(__DIR__ . '/../');

require('vendor/autoload.php');
$config = require('config/local.php');

$authentication_tokens = array(
    'app_key' => $config['eventbrite']['appKey'],
    'user_key' => $config['eventbrite']['userKey']
);

$eb = new Php2014\EventBrite($authentication_tokens);
$result = $eb->event_list_attendees(array(
    'id' => $config['eventbrite']['eventId'],
    'status' => 'attending',
        ));

$data = array();
foreach ($result->attendees as $object) {
    $attendee = $object->attendee;
    $data[$attendee->id] = array(
        'lastname' => $attendee->last_name,
        'country' => empty($attendee->work_country_code) ? 'n/a' : $attendee->work_country_code,
        'company' => empty($attendee->company) ? 'n/a' : $attendee->company,
        'age' => empty($attendee->age) ? 'n/a' : $attendee->age,
        'gender' => empty($attendee->gender) ? 'n/a' : $attendee->gender,
        'checkedInAt' => null,
        'i' => 1
    );
}

ksort($data);

$cb = new ChartBlocks\Client(array(
    'token' => $config['chartblocks']['token'],
    'secret' => $config['chartblocks']['secret'],
        ));

$set = $cb->getRepository('dataSet')->findById($config['chartblocks']['attendeeSetId']);

$r = 2;
$rowSet = new \ChartBlocks\DataSet\RowSetDynamic($set);
foreach ($data as $person) {
    $row = new \ChartBlocks\DataSet\Row($set, array('row' => $r));

    $c = 1;
    foreach ($person as $key => $value) {
        $row->setCell($c, $value);
        $c++;
    }

    $rowSet->addRow($row);
    $r++;
}

$rowSet->save();
