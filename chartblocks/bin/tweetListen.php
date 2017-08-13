<?php

chdir(__DIR__ . '/../');

require('vendor/autoload.php');
$config = require('config/local.php');

$track = array();
foreach ($config['twitter']['tracks'] as $trackDef) {
    foreach ($trackDef as $filter) {
        $track[] = '#' . $filter;
    }
}

// The OAuth credentials you received when registering your app at Twitter
define("TWITTER_CONSUMER_KEY", $config['twitter']['key']);
define("TWITTER_CONSUMER_SECRET", $config['twitter']['secret']);

// The OAuth data for the twitter account
define("OAUTH_TOKEN", $config['twitter']['token']);
define("OAUTH_SECRET", $config['twitter']['token_secret']);

$mongo = new \MongoClient();
$collection = $mongo->selectCollection($config['mongo']['database'], $config['mongo']['collection']);

$collection->ensureIndex(array('entities.hashtags.text' => 1));
$collection->ensureIndex(array('created_at' => 1));

// Start streaming
$sc = new \Php2014\TweetStream(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_FILTER);
$sc->setMongoCollection($collection);
$sc->setTrack($track);
$sc->consume();