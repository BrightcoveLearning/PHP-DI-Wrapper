# PHP Wrapper for Brightcove Dynamic Ingest

## About


This project provides a starting point for integrating the Brightcove Dynamic Ingest API into your application. It provides simple ways to add and update videos and associated media using either pull-based ingest or source file upload.

## Requirements

PHP version 5.2 or greater.

## Installation

1. Download, clone, or fork this repository.
2. Copy the **dist** folder to your local or remote web server - *note: for the wrapper to function properly, you must make a physical copy, not a symlink*

___

## Examples


### Instantiation

This example shows how to instantiate, or start, the BCDIAPI PHP class. The argument, a JSON string, is required, and must take the form shown here. The recommended permissions for your credentials are:

![DI Credentials](sample/assets/di-credentials-permissions.png "DI Credentials")

#### Sample code

    // Include the BCDIAPI SDK
    require('bc-diapi.php');

    // account information
    $account_data = '{
        "account_id": "YOUR_ACCOUNT_ID",
        "client_id": "YOUR_CLIENT_ID",
        "client_secret": "YOUR_CLIENT_SECRET"
    }';

    // Instantiate the class, passing it the account information
    $bc = new BCDIAPI($account_data);

The parameters for the constructor are:

    * [JSON string] $account_data

### Ingest request

There is one method to handle ingest requests of all types - only the input data varies for the 5 types of requests:

* ingest a new video (pull-based)
* ingest a new video (using source file upload)
* replace a video (pull-based)
* replace video (using source file upload)
* retrancode a video

#### Method

    $BCDIAPI->ingest_request($ingest_options)

The parameters for the method are:

    [object] $ingest_options
        [JSON string] $ingest_options->video_options a JSON string corresponding to the request body for the CMS API request - **required for new videos**
        [JSON string] $ingest_options->ingest_options a JSON string corresponding to the request body for the Dynamic Ingest API request - **required**
        [JSON string] $ingest_options->file_paths a JSON string containing paths to the video, poster, and/or thumbnail files **required** *for source file upload requests only* - see the examples below for the structure
        [JSON string] $ingest_options->text_tracks a JSON string containing paths and other parameters for text tracks *for source file upload requests only* - see the examples below for the structure

Notes:

1. For the `video_options`, see the [API reference](http://docs.brightcove.com/en/video-cloud/di-api/reference/versions/v1/index.html#api-Video-Create_Video_Object) - for new videos, minimal JSON would be `{"name": "My Video Title"}`
2. For the `ingest_options`, see the [API reference](http://docs.brightcove.com/en/video-cloud/di-api/reference/versions/v1/index.html#api-Ingest-Ingest_Media_Asset) and the examples below

## Error Handling

(*work-in-progress - not implemented yet!*)

This example shows how to utilize the built-in error handling in BCDIAPI.

    // Create a try/catch
    try {
        // Make our API call
        $video = $bc->find('find_video_by_id', 123456789);
    } catch(Exception $error) {
        // Handle our error
        echo $error;
        die();
    }

* * *

### Errors

(*work-in-progress - not implemented yet!*)

BCDIAPIApiError
--------------
This is the most generic error returned from BCDIAPI as it is thrown whenever the API returns unexpected data, or an error. The API return data will be included in the error to help you diagnose the problem.

BCDIAPIDeprecated
----------------
The requested item is no longer supported by Brightcove and/or BCDIAPI. Stop using this method as early as possible, as the item could be removed in any future release.

BCDIAPIDtoDoesNotExist
---------------------
The specified asset does not exist in the Brightcove system. Ensure you're using the correct ID.

BCDIAPIIdNotProvided
-------------------
An ID has not been passed to the method (usually a "delete" or "share" function). Include the ID parameter to resolve the error.

BCDIAPIInvalidFileType
---------------------
The file being passed to the function is not supported. Try another file type to resolve the error.

BCDIAPIInvalidMethod
-------------------
The "find" method being requested is not supported by BCDIAPI, or does not exist in the Brightcove API. Remove the method call and check both the BCDIAPI and Brightcove API documentation.

BCDIAPIInvalidProperty
---------------------
The BCDIAPI property you are trying to set or retrieve does not exist. Check the BCDIAPI documentation.

BCDIAPIInvalidType
-----------------
The DTO type (video, playlist, image, etc) you specified is not allowed for the method. Check both the BCDIAPI and Brightcove API documentation.

BCDIAPISearchTermsNotProvided
----------------------------
Please specify one or more search parameters. Verify you are passing the parameters in an array.

BCDIAPITokenError
----------------
The read or write token you provided is not recognized by Brightcove. Verify you are using the correct token.

BCDIAPITransactionError
----------------------
The API could not be accessed, or the API did not return any data. Verify the server has cURL installed, enabled, and able to retrieve remote data. Verify the Brightcove API is currently available.
