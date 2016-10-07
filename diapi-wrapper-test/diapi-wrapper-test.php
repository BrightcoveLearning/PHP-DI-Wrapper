<?php
require 'bc-diapi.php';

// sample data

// json
$video_metadata   = '{"name":"Great Blue Heron - DI Wrapper test","description": "An original nature video","custom_fields": {"subject": "Birds"},"tags": ["nature","bird"]}';
$pull_ingest_data = '{
    "profile": "BoltIngestProfile",
    "capture-images": false,
    "poster": {
        "url": "http://solutions.brightcove.com/bcls/images/Great-Blue-Heron.png",
        "height": 360,
        "width": 640
    },
    "thumbnail": {
        "url": "http://solutions.brightcove.com/bcls/images/great-blue-heron-thumbnail.png",
        "height": 90,
        "width": 160
    },
    "text_tracks": [
        {
            "url": "http://solutions.brightcove.com/bcls/assets/vtt/sample.vtt",
            "srclang": "en",
            "kind": "captions",
            "label": "EN",
            "default": true
        }
    ],
    "master": {
        "url": "http://solutions.brightcove.com/bcls/assets/videos/Great_Blue_Heron.mp4"
    },
    "callbacks": ["http://solutions.brightcove.com/bcls/di-api/di-callbacks.php"]
}';
$push_ingest_data = '{
    "profile": "BoltIngestProfile",
    "capture-images": false,
    "poster": {
        "url": "http://solutions.brightcove.com/bcls/images/Great-Blue-Heron.png",
        "height": 360,
        "width": 640
    },
    "thumbnail": {
        "url": "http://solutions.brightcove.com/bcls/images/great-blue-heron-thumbnail.png",
        "height": 90,
        "width": 160
    },
    "text_tracks": [
        {
            "url": "http://solutions.brightcove.com/bcls/assets/vtt/sample.vtt",
            "srclang": "en",
            "kind": "captions",
            "label": "EN",
            "default": true
        }
    ],
    "callbacks": ["http://solutions.brightcove.com/bcls/di-api/di-callbacks.php"]
}';
$account_data = '{
    "client_secret": "h1dbPZCMFsloMCiXprlGDvdDR7QXtcw9alyocJ1ShDfLZ5QxqBqb9u_5gGcU6mlyA1PbbG6ABYS1FMDVE4JNDQ",
    "client_id": "b10631d3-7597-4be8-b8b5-dce142f81006",
    "account_id": "57838016001"
}';
// pull request options
$pull_options = new stdClass();
$pull_options->video_options = $video_metadata;
$pull_options->ingest_options = $pull_ingest_data;
// push request options
$push_options = new stdClass();
$pull_options->video_options = $video_metadata;
$pull_options->ingest_options = $push_ingest_data;
$push_options->video_path = '../assets/videos/Great_Blue_Heron.mp4';

$BCDI = new BCDIAPI($account_data);

// add video via pull request

$responses = $BCDI->add_video($pull_options);
var_dump($responses);
?>
