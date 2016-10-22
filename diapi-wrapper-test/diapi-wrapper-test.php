<?php
require '../dist/bc-diapi.php';


// sample data

// to ingest new video (pull-based)
$video_metadata = '{"name":"Great Blue Heron - DI Wrapper test","description": "An original nature video","tags": ["nature","bird"]}';
// pull ingest options
$pull_ingest_data = '{"profile": "BoltIngestProfile","capture-images": true,"text_tracks": [{"url": "http://solutions.brightcove.com/bcls/assets/vtt/sample.vtt","srclang": "en","kind": "captions","label": "EN","default": true}],"master": {"url": "http://solutions.brightcove.com/bcls/assets/videos/Great_Blue_Heron.mp4"},"callbacks": ["http://solutions.brightcove.com/bcls/di-api/di-callbacks.php"]}';

// push ingest data
$push_ingest_data = '{"profile": "BoltIngestProfile","capture-images": true,"callbacks": ["http://solutions.brightcove.com/bcls/di-api/di-callbacks.php"]}';

// for retranscode test
$retranscode_data = '{"profile": "BoltIngestProfile","capture-images": false,"master": { "use_archived_master": true },"callbacks": ["http://solutions.brightcove.com/bcls/di-api/di-callbacks.php"]}';

// for replace video test
$account_data = '{"client_secret": "h1dbPZCMFsloMCiXprlGDvdDR7QXtcw9alyocJ1ShDfLZ5QxqBqb9u_5gGcU6mlyA1PbbG6ABYS1FMDVE4JNDQ","client_id": "b10631d3-7597-4be8-b8b5-dce142f81006","account_id": "57838016001"}';

// for push-based ingest
$file_paths = '{"video": "../assets/Great-Blue-Heron.mp4"}';
$file_paths_full = '{"video": "../assets/Great-Blue-Heron.mp4","poster": "../assets/Great-Blue-Heron.png","thumbnail": "../assets/great-blue-heron-thumbnail.png"}';
$text_tracks = '[{"path": "../assets/sample.vtt", "srclang": "en","kind": "captions","label": "EN","default": true}]';


// data sets
$data_sets = new stdClass();
// pull request options
$data_sets->pull_options = new stdClass();
$data_sets->pull_options->video_options = $video_metadata;
$data_sets->pull_options->ingest_options = $pull_ingest_data;

// pull replace request options
$data_sets->pull_replace_options = new stdClass();
$data_sets->pull_replace_options->video_id = '5163084054001';
$data_sets->pull_replace_options->video_options = $video_metadata;
$data_sets->pull_replace_options->ingest_options = $pull_ingest_data;

// push replace request options
$data_sets->push_replace_options = new stdClass();
$data_sets->push_replace_options->video_id = '5163084054001';
$data_sets->push_replace_options->video_options = $video_metadata;
$data_sets->push_replace_options->ingest_options = $pull_ingest_data;

// retranscode request options
$data_sets->retranscode_options = new stdClass();
$data_sets->retranscode_options->video_id = '5163084054001';
$data_sets->retranscode_options->video_options = $video_metadata;
$data_sets->retranscode_options->ingest_options = $retranscode_data;

// push request options
$data_sets->push_options = new stdClass();
$data_sets->push_options->file_paths = $file_paths;
$data_sets->push_options->video_options = $video_metadata;
$data_sets->push_options->ingest_options = $push_ingest_data;
$data_sets->push_options->text_tracks = $text_tracks;

// instantiate the wrapper
$bcdi = new BCDIAPI($account_data);
// make a request - change data param to test other operations
$responses = $bcdi->ingest_request($push_options);
echo '<h3 style="font-family:sans-serif;">CMS Response (will be NULL except for new video additions)</h3>';
echo '<pre>'.json_encode($responses->cms, JSON_PRETTY_PRINT).'</pre>';
echo '<h3 style="font-family:sans-serif;">S3 Responses (will be empty for pull-based ingest)</h3>';
echo '<pre>'.json_encode($responses->s3, JSON_PRETTY_PRINT).'</pre>';
echo '<h3 style="font-family:sans-serif;">PUT File Responses (will be empty for pull-based ingest)</h3>';
echo '<pre>'.json_encode($responses->putFiles, JSON_PRETTY_PRINT).'</pre>';
echo '<h3 style="font-family:sans-serif;">DI Response</h3>';
echo '<pre>'.json_encode($responses->di, JSON_PRETTY_PRINT).'</pre>';
