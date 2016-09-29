<?php
require 'bc-diapi.php';

// sample data

// json
$video_metadata   = '{"description": "An original nature video","custom_fields": {"subject": "Bird"},"tags": ["nature","bird"]}';
$poster_data      = '{"url": "http://solutions.brightcove.com/bcls/images/Great-Blue-Heron.png","height": 360,"width": 640}';
$thumbnail_data   = '{"url": "http://solutions.brightcove.com/bcls/images/great-blue-heron-thumbnail.png","height": 90,"width": 160}';
$text_tracks_data = '[{"url": "http://solutions.brightcove.com/bcls/assets/vtt/sample.vtt","srclang": "en","kind": "captions","label": "EN","default": true}]';
$callbacks_data   = '["http://solutions.brightcove.com/bcls/di-api/di-callbacks.php"]';

// pull request options
$pull_options = new stdClass();

$pull_options->account_id     = '57838016001';
$pull_options->client_id      = '553d4903-4547-435d-944c-2c8e2f6abc5d';
$pull_options->client_secret  = 'ENBQH6pHfJQub7oR0SGCn2Pu_W2SY5QsVw24fK-frXcE6hdTRnJO-0_LBmKZh15rVliIAiECAQF1yBYP_l90gQ';
$pull_options->pull_video_url = 'http://solutions.brightcove.com/bcls/assets/videos/Great_Blue_Heron.mp4';
$pull_options->video_name     = 'Great Blue Heron';
$pull_options->profile        = 'screencast-1280';
$pull_options->capture_images = false;
$pull_options->metadata       = json_decode($video_metadata);
$pull_options->poster         = json_decode($poster_data);
$pull_options->thumbnail      = json_decode($thumbnail_data);
$pull_options->text_tracks    = json_decode($text_tracks_data);
$pull_options->callbacks      = json_decode($callbacks_data);

// push request options
$push_options = new stdClass();

$push_options->account_id     = '57838016001';
$push_options->client_id      = '553d4903-4547-435d-944c-2c8e2f6abc5d';
$push_options->client_secret  = 'ENBQH6pHfJQub7oR0SGCn2Pu_W2SY5QsVw24fK-frXcE6hdTRnJO-0_LBmKZh15rVliIAiECAQF1yBYP_l90gQ';
$push_video_path              = '../assets/videos/Great_Blue_Heron.mp4';
$push_options->video_name     = 'Great Blue Heron';
$push_options->profile        = 'screencast-1280';
$push_options->capture_images = false;
$push_options->metadata       = json_decode($video_metadata);
$push_options->poster         = json_decode($poster_data);
$push_options->thumbnail      = json_decode($thumbnail_data);
$push_options->text_tracks    = json_decode($text_tracks_data);
$push_options->callbacks      = json_decode($callbacks_data);

$BCDI = new BCDIAPI($account_id, $client_id, $client_secret);
// var_dump($BCDI);

// add video via pull request

$new_video_status = $BCDI->add_video($pull_options);
var_dump($new_video_status);
?>
