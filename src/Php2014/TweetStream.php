<?php

namespace Php2014;

use OauthPhirehose;
use MongoCollection;

class TweetStream extends OauthPhirehose {

    protected $mongoCollection;
    protected $trackName;

    /**
     * Enqueue each status
     *
     * @param string $status
     */
    public function enqueueStatus($status) {
        $json = json_decode($status);
        if ($json) {
            $json->created_at = new \MongoDate(strtotime($json->created_at));
            foreach ($json->entities->hashtags as $hashtag) {
                $hashtag->text = strtolower($hashtag->text);
            }

            $this->getMongoCollection()->insert($json);
        }
    }

    /**
     * 
     * @param \MongoCollection $collection
     * @return \TweetStream
     */
    public function setMongoCollection(MongoCollection $collection) {
        $this->mongoCollection = $collection;
        return $this;
    }

    /**
     * 
     * @return \MongoCollection
     */
    public function getMongoCollection() {
        return $this->mongoCollection;
    }

}
