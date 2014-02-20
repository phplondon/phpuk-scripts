<?php

chdir(__DIR__ . '/../');

require('vendor/autoload.php');
$config = require('config/local.php');


$tempData = file_get_contents($config['temperatureFile']);

/* The file should look something like 
 * 
 * aa 01 4b 46 7f ff 06 10 84 : crc=84 YES
 * aa 01 4b 46 7f ff 06 10 84 t=26625
 * 
 * using preg_match to grab the t=TEMP
 */

$matches = array();
preg_match('/t\=([0-9]+)/', $tempData, $matches);


if (isset($matches[1])) {

    $temp = (string) (((float) $matches[1]) / 1000);

    $client = new ChartBlocks\Client(array(
        'token' => $config['chartblocks']['token'],
        'secret' => $config['chartblocks']['secret'],
    ));

    $dataSet = $client->getRepository('dataSet')->findById($config['chartblocks']['temperatureSetId']);

    $row = $dataSet->createRow();
    $row->getCell(1)->setValue(time());
    $row->getCell(2)->setValue($temp);
    $row->save();
}
