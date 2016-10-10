<?php

require 'bc-diapi.php';

// sample data

// to ingest new video (pull-based)
$video_metadata = '{"name":"Great Blue Heron - DI Wrapper test","description": "An original nature video","custom_fields": {"subject": "Birds"},"tags": ["nature","bird"]}';

$ingest_data = '{"profile": "BoltIngestProfile","capture-images": false,"poster": {"url": "http://solutions.brightcove.com/bcls/images/Great-Blue-Heron.png","height": 360,"width": 640},"thumbnail": {"url": "http://solutions.brightcove.com/bcls/images/great-blue-heron-thumbnail.png","height": 90,"width": 160},"text_tracks": [{"url": "http://solutions.brightcove.com/bcls/assets/vtt/sample.vtt","srclang": "en","kind": "captions","label": "EN","default": true}],"master": {"url": "http://solutions.brightcove.com/bcls/assets/videos/Great_Blue_Heron.mp4"},"callbacks": ["http://solutions.brightcove.com/bcls/di-api/di-callbacks.php"]}';

// for retranscode test
$retranscode_data = '{"profile": "BoltIngestProfile","capture-images": false,"poster": {"url": "http://solutions.brightcove.com/bcls/images/Great-Blue-Heron.png","height": 360,"width": 640},"thumbnail": {"url": "http://solutions.brightcove.com/bcls/images/great-blue-heron-thumbnail.png","height": 90,"width": 160},"text_tracks": [{"url": "http://solutions.brightcove.com/bcls/assets/vtt/sample.vtt","srclang": "en","kind": "captions","label": "EN","default": true}],"master": { "use_archived_master": true },"callbacks": ["http://solutions.brightcove.com/bcls/di-api/di-callbacks.php"]}';

// for replace video test
$account_data = '{"client_secret": "h1dbPZCMFsloMCiXprlGDvdDR7QXtcw9alyocJ1ShDfLZ5QxqBqb9u_5gGcU6mlyA1PbbG6ABYS1FMDVE4JNDQ","client_id": "b10631d3-7597-4be8-b8b5-dce142f81006","account_id": "57838016001"}';

// for push-based ingest
$file_paths = '{"video": "../assets/videos/Great_Blue_Heron.mp4"}';
$file_paths_full = '{"video": "../assets/videos/Great_Blue_Heron.mp4","poster": "../assets/images/Great-Blue-Heron.png","thumbnail": "../assets/images/great-blue-heron-thumbnail.png","text_tracks": "../assets/vtt/sample.vtt"}';

// pull request options
$pull_options = new stdClass();
$pull_options->video_options = $video_metadata;
$pull_options->ingest_options = $ingest_data;

// replace request options
$replace_options = new stdClass();
$replace_options->video_id = '5163084054001';
$replace_options->video_options = $video_metadata;
$replace_options->ingest_options = $ingest_data;

// retranscode request options
$retranscode_options = new stdClass();
$retranscode_options->video_id = '5163084054001';
$retranscode_options->video_options = $video_metadata;
$retranscode_options->ingest_options = $retranscode_data;

// push request options
$push_options = new stdClass();
$push_options->file_paths = $file_paths;
$push_options->video_options = $video_metadata;
$push_options->ingest_options = $injest_data;

// instantiate the wrapper
$BCDI = new BCDIAPI($account_data);

// make a request - change data param to test other operations
$BCDI->ingest_request($push_options);
echo '<h3>CMS Response (will be NULL except for new video additions)</h3>';
var_dump($BCDI->responses->cms);
echo '<h3>DI Response</h3>';
var_dump($BCDI->responses->di);
