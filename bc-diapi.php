<?php

/**
 * Brightcove PHP Dynamic Ingest API Wrapper 0.1.0 (October 2016)
 *
 * REFERENCES:
 *	 Website: http://opensource.brightcove.com
 *	 Source: http://github.com/brightcoveos
 *
 * AUTHORS:
 *	 Robert Crooks <rcrooks@brightcove.com>
 *
 * CONTRIBUTORS:
 *
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the “Software”),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, alter, merge, publish, distribute,
 * sublicense, and/or sell copies of the Software, and to permit persons to
 * whom the Software is furnished to do so, subject to the following conditions:
 *
 * 1. The permission granted herein does not extend to commercial use of
 * the Software by entities primarily engaged in providing online video and
 * related services.
 *
 * 2. THE SOFTWARE IS PROVIDED "AS IS", WITHOUT ANY WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, SUITABILITY, TITLE,
 * NONINFRINGEMENT, OR THAT THE SOFTWARE WILL BE ERROR FREE. IN NO EVENT
 * SHALL THE AUTHORS, CONTRIBUTORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY WHATSOEVER, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH
 * THE SOFTWARE OR THE USE, INABILITY TO USE, OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * 3. NONE OF THE AUTHORS, CONTRIBUTORS, NOR BRIGHTCOVE SHALL BE RESPONSIBLE
 * IN ANY MANNER FOR USE OF THE SOFTWARE.  THE SOFTWARE IS PROVIDED FOR YOUR
 * CONVENIENCE AND ANY USE IS SOLELY AT YOUR OWN RISK.  NO MAINTENANCE AND/OR
 * SUPPORT OF ANY KIND IS PROVIDED FOR THE SOFTWARE.
 */

class BCDIAPI
{
    const ERROR_ACCOUNT_ID_NOT_PROVIDED = 2;
    const ERROR_API_ERROR = 1;
    const ERROR_CLIENT_ID_NOT_PROVIDED = 9;
    const ERROR_CLIENT_SECRET_NOT_PROVIDED = 11;
    const ERROR_DEPRECATED = 99;
    const ERROR_DTO_DOES_NOT_EXIST = 12;
    const ERROR_INVALID_FILE_TYPE = 5;
    const ERROR_INVALID_JSON = 3;
    const ERROR_INVALID_PROPERTY = 4;
    const ERROR_INVALID_TYPE = 6;
    const ERROR_INVALID_UPLOAD_OPTION = 7;
    const ERROR_READ_API_TRANSACTION_FAILED = 8;
    const ERROR_SEARCH_TERMS_NOT_PROVIDED = 13;
    const ERROR_WRITE_API_TRANSACTION_FAILED = 10;


    protected $access_token = NULL;
    protected $account_id = NULL;
    protected $api_calls = 0;
    protected $bit32 = FALSE;
    protected $client_id = NULL;
    protected $client_secret = NULL;
    protected $cms_data = NULL;
    protected $current_request = NULL;
    protected $di_data = NULL:
    protected $di_suffix = '/ingest-requests';
    protected $file_name = NULL;
    protected $is_pull_request = TRUE;
    protected $job_id = NULL;
    protected $method = NULL;
    protected $parsed_data = array();
    protected $result_parsed = NULL;
    protected $show_notices = FALSE;
    protected $signed_url = NULL;
    protected $timeout_attempts = 100;
    protected $timeout_current = 0;
    protected $timeout_delay = 1;
    protected $timeout_retry = FALSE;
    protected $token_expires = time();
    protected $unsigned_url = NULL;
    protected $url = NULL;
    protected $url_cms = 'https://cms.api.brightcove.com/v1/accounts/';
    protected $url_di = 'https://ingest.api.brightcove.com/v1/accounts/';
    protected $url_oauth = 'https://oauth.brightcove.com/v3/access_token?grant_type=client_credentials';
    protected $video_id = NULL;

    /**
     * The constructor for the BCDIAPI class.
     * @access Public
     * @since 0.1.0
     * @param string [$account_id] The Video Cloud account id
     * @param string [$client_id] The read API token for the Brightcove account
     * @param string [$client_secret] The write API token for the Brightcove account
     */
    public function __construct($account_id = NULL, $client_id = NULL, $client_secret = NULL)
    {
        $this->account_id = $account_id;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->auth_string = "{$client_id}:{$client_secret}";
        $this->bit32 = ((string)'99999999999999' == (int)'99999999999999') ? FALSE : TRUE;
    }

    /**
     * Sets a property of the BCDIAPI class.
     * @access Public
     * @since 0.1.0
     * @param string [$key] The property to set
     * @param mixed [$value] The new value for the property
     * @return mixed The new value of the property
     */
    public function __set($key, $value)
    {
        if(isset($this->$key) || is_null($this->$key))
        {
            $this->$key = $value;
        } else {
            throw new BCDIAPIInvalidProperty($this, self::ERROR_INVALID_PROPERTY);
        }
    }

    /**
     * Retrieves a property of the BCDIAPI class.
     * @access Public
     * @since 0.1.0
     * @param string [$key] The property to retrieve
     * @return mixed The value of the property
     */
    public function __get($key)
    {
        if(isset($this->$key) || is_null($this->$key))
        {
            return $this->$key;
        } else {
            throw new BCDIAPIInvalidProperty($this, self::ERROR_INVALID_PROPERTY);
        }
    }

    /**
     * Adds media (videos, images, text tracks) to the account
     * @access Public
     * @since 0.1.0
     * @param string [$video_name] The video title (either here or in $video_metadata) default: video file name
     * @param object [$video_metadata] Metadata for the video - see [Dyanamic Ingest API reference](http://docs.brightcove.com/en/video-cloud/di-api/reference/versions/v1/index.html#api-Video-Create_Video_Object)
     * @param string [$video_url] URL for the video (for pull-based ingestion; required if $video_file is NULL)
     * @param string [$video_file] video file location (for pull-based ingestion; required if $video_file is NULL)
     * @param string [$profile] Name of the ingest profile to use - if NULL, default profile for the account will be used
     * @param boolean [$capture_images] Whether Video Cloud should capture images for the video still and thumbnail during trancoding - should be set to FALSE if the poster and thumbnail are provided
     * @param array [$poster] Video still information - if included, keys are: url (required0; height (optional); width (optional)
     * @param array [$thumbnail] thumbnail information - if included, keys are: url (required0; height (optional); width (optional)
     * @param array[] [$text_tracks] text tracks information - if included, each object in the array has keys: url (required), srclang (required); kind (optional); label (optional); default (optional)
     * @param string[] [$callbacks] array of callback URLs
     */
    public function add_video($video_name = NULL, $video_metadata = NULL, $video_url = NULL, $video_file = NULL, $profile = NULL, $capture_images = TRUE, $poster = NULL, $thumbnail = NULL, $text_tracks = NULL, $callbacks = NULL) {
        // get file name
        if (isset($video_url)) {
            $tmp = $video_url;
        } else if (isset($video_file)) {
            $is_pull_request = FALSE;
            $tmp = $video_file;
        }
        $file_name = array_pop(explode('/'), $tmp);

        // set up ingest request data
        $di_data = array(
            'master' => array(),
        );
        if (isset($profile)) {
            $di_data['profile'] = $profile;
        }
        if (isset($poster)) {
            $di_data['capture_images'] = FALSE;
            $di_data['poster'] = $poster;
        }
        if (isset($thumbnail)) {
            $di_data['thumbnail'] = $thumbnail;
        }
        if (isset($text_tracks)) {
            $di_data['text_tracks'] = $text_tracks;
        }
        if (isset($video_url)) {
            $di_data['master']['url'] = $video_url;
        }
        // set up CMS request data
        if (isset($video_metadata)) {
            $cms_data = $video_metadata;
        } else {
            $cms_data = array();
        }
        if (!isset($video_metadata->name)) {
            if (isset($video_name)) {
                $video_metadata['name'] = $video_name;
            } else {
                $video_metadata['name'] = $file_name;
            }
        }

        // data in place, make api requests
        $cms_response = json_decode($this->make_request('create_video', $cms_data));
        $video_id = $cms_response['id'];
        if ($is_pull_request) {
            $di_response = json_decode($this->make_request('ingest_video', $di_data))
            $job_id = $di_response['job_id'];
        } else {
            $s3_response = json_decode($this->make_request('get_s3urls', NULL));
            $signed_url = $s3_response['SignedUrl'];
            $unsigned_url = $s3_response['ApiRequestUrl'];
        }

    }

    /**
     * Retrieves an access token if there is not a valid one already, and updates the token expiration
     * @since 0.1.0
     * @return string Access token
     */
    private function get_access_token() {
        if ($token_expires > time()) {
            $result_parsed = json_decode($this->make_request('get_token', NULL));
            $access_token = $result_parsed['access_token'];
            $token_expires = time() + $result_parsed['expires_in'];
        }
        return $access_token;
    }

    /**
     * Formats the request for any API requests and retrieves the data.
     * @access Private
     * @since 0.1.0
     * @param string [$call] The requested API method
     * @param mixed [$params] A key-value array of API parameters, or a single value that matches the default
     * @return object An object containing all API return data
     */
    private function make_request($call, $request_data = NULL)
    {
        $this->timeout_current = 0;

        if (isset($request_data)) {
                $data = json_decode($request_data);
            if (is_null($data)) {
                self::ERROR_INVALID_JSON);
            }
        }

        switch($call)
        {
            case 'get_token':
                $url = $url_oauth;
                $method = 'POST';
                $headers = array('Content-type: application/x-www-form-urlencoded');
                $data = array();
                $user_pwd = $this->$auth_sting;
                return $this->send_request($url, $method, $headers, $data, $user_pwd);
                break;
            case 'create_video':
                $url = $url_cms . $account_id . '/videos';
                $method = 'POST';
                $data = json_encode($request_data);
                $access_token = $this->get_access_token();
                $headers = array(
                    'Content-type: application/json',
                    'Authorization: Bearer ' . $access_token;
                );
                return $this->send_request($url, $method, $headers, $data, NULL);
                break;
            case 'get_s3urls':
                $url = $url_di . $account_id . '/videos/' . $video_id . $file_name;
                $method = GET;
                $data = array();
                $access_token = $this->get_access_token();
                $headers = array(
                    'Content-type: application/json',
                    'Authorization: Bearer ' . $access_token;
                );
                return $this->send_request($url, $method, $headers, $data, NULL)
                break;
            case 'put_video':
                $url = $signed_url;
                $method = PUT;
                $data = array();
                break;
            case 'ingest_video':
                $method = 'find_videos_by_ids';
                $default = 'video_ids';
                $get_item_count = FALSE;
                break;
            case 'get_status':
                $method = 'find_videos_by_ids_unfiltered';
                $default = 'video_ids';
                $get_item_count = FALSE;
                break;
            default:
                throw new BCDIAPIInvalidMethod($this, self::ERROR_INVALID_JSON);
                break;
        }


        $response = $this->send_request($url, $headers, $method, $data);
    }



    /**
     * Shares a media asset with the selected accounts.
     * @access Public
     * @since 0.1.0
     * @param string [$type] The type of object to check
     * @param int [$id] The ID of the media asset
     * @param array [$account_ids] An array of account IDs
     * @param bool [$accept] Whether the share should be auto accepted
     * @param bool [$force] Whether the share should overwrite existing copies of the media
     * @return array The new media asset IDs
     */
    public function shareMedia($type = 'video', $id, $account_ids, $accept = FALSE, $force = FALSE)
    {
        if(!isset($id))
        {
            throw new BCDIAPIIdNotProvided($this, self::ERROR_ID_NOT_PROVIDED);
        }

        if(!is_array($account_ids))
        {
            $account_ids = array($account_ids);
        }

        $request = array();
        $post = array();
        $params = array();

        $params['token'] = $this->client_secret;
        $params['sharee_account_ids'] = $account_ids;

        if($accept)
        {
            $params['auto_accept'] = 'TRUE';
        } else {
            $params['auto_accept'] = 'FALSE';
        }

        if($force)
        {
            $params['force_reshare'] = 'TRUE';
        } else {
            $params['force_reshare'] = 'FALSE';
        }

        if(strtolower($type) == 'video')
        {
            $params['video_id'] = $id;
            $post['method'] = 'share_video';
        } else {
            throw new BCDIAPIInvalidType($this, self::ERROR_INVALID_TYPE);
        }

        $post['params'] = $params;

        $request['json'] = json_encode($post) . "\n";

        return $this->putData($request)->result;
    }


    /**
     * Converts milliseconds to formatted time or seconds.
     * @access Public
     * @since 0.2.1
     * @param int [$ms] The length of the media asset in milliseconds
     * @param bool [$seconds] Whether to return only seconds
     * @return mixed The formatted length or total seconds of the media asset
     */
    public function convertTime($ms, $seconds = FALSE)
    {
        $total_seconds = ($ms / 1000);

        if($seconds)
        {
            return $total_seconds;
        } else {
            $time = '';

            $value = array(
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0
            );

            if($total_seconds >= 3600)
            {
                $value['hours'] = floor($total_seconds / 3600);
                $total_seconds = $total_seconds % 3600;

                $time .= $value['hours'] . ':';
            }

            if($total_seconds >= 60)
            {
                $value['minutes'] = floor($total_seconds / 60);
                $total_seconds = $total_seconds % 60;

                $time .= $value['minutes'] . ':';
            } else {
                $time .= '0:';
            }

            $value['seconds'] = floor($total_seconds);

            if($value['seconds'] < 10)
            {
                $value['seconds'] = '0' . $value['seconds'];
            }

            $time .= $value['seconds'];

            return $time;
        }
    }



    /**
     * Formats a media asset name to be search-engine friendly.
     * @access Public
     * @since 0.2.1
     * @param string [$name] The asset name
     * @return string The SEF asset name
     */
    public function sef($name)
    {
        $accent_match = array('Â', 'Ã', 'Ä', 'À', 'Á', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
        $accent_replace = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'B', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');

        $name = str_replace($accent_match, $accent_replace, $name);
        $name = preg_replace('/[^a-zA-Z0-9\s]+/', '', $name);
        $name = preg_replace('/\s/', '-', $name);

        return $name;
    }

    /**
     * Appends API parameters onto API request URL.
     * @access Private
     * @since 0.1.0
     * @param string [$method] The requested API method
     * @param array [$params] A key-value array of API parameters
     * @param string [$default] The default API parameter if only 1 provided
     * @return string The complete API request URL
     */
    protected function appendParams($method, $params = NULL, $default = NULL)
    {
        $url = $this->getUrl('read') . 'token=' . $this->client_id . '&command=' . $method;

        if(isset($params))
        {
            if(isset($default))
            {
                $url .= '&' . $default . '=' . urlencode($params);
            } else {
                foreach($params as $option => $value)
                {
                    $url .= '&' . $option . '=' . urlencode($value);
                }
            }
        }

        return $url;
    }

    /**
     * Retrieves API data from provided URL.
     * @access Private
     * @since 0.1.0
     * @param string [$url] The complete API request URL
     * @param string [$method] The HTTP method for the request
     * @param array [$headers] The HTTP headers to send with the request
     * @param string [$data_string] A JSON string containing the request body (if any) to send with the request
     * @return object An object containing all API return data
     */
    protected function send_request($url = NULL, )
    {
        if(class_exists('BCDIAPICache'))
        {
            $cache = BCDIAPICache::get($url);

            if($cache !== FALSE)
            {
                $response_object = json_decode($cache);

                if(isset($response_object->items))
                {
                    $data = $response_object->items;
                } else {
                    $data = $response_object;
                }


                return $data;
            }
        }

        $this->timeout_current++;

        if(!isset($this->client_id))
        {
            throw new BCDIAPITokenError($this, self::ERROR_CLIENT_ID_NOT_PROVIDED);
        }

        $response = $this->curlRequest($url, TRUE);

        if($response && $response != 'NULL')
        {
            $response_object = json_decode(preg_replace('/[[:cntrl:]]/u', '', $response));;

            if(isset($response_object->error))
            {
                if($this->timeout_retry && $response_object->code == 103 && $this->timeout_current < $this->timeout_attempts)
                {
                    if($this->timeout_delay > 0)
                    {
                        if($this->timeout_delay < 1)
                        {
                            usleep($this->timeout_delay * 1000000);
                        } else {
                            sleep($this->timeout_delay);
                        }
                    }

                    return $this->send_request($url);
                } else {
                    throw new BCDIAPIApiError($this, self::ERROR_API_ERROR, $response_object);
                }
            } else {
                if(class_exists('BCDIAPICache'))
                {
                    $cache = BCDIAPICache::set($url, $response_object);
                }

                if(isset($response_object->items))
                {
                    $data = $response_object->items;
                } else {
                    $data = $response_object;
                }

                $this->page_number = isset($response_object->page_number) ? $response_object->page_number : NULL;
                $this->page_size = isset($response_object->page_size) ? $response_object->page_size : NULL;
                $this->total_count = isset($response_object->total_count) ? $response_object->total_count : NULL;

                return $data;
            }
        } else {
            throw new BCDIAPIApiError($this, self::ERROR_API_ERROR);
        }
    }

    /**
     * Sends data to the API.
     * @access Private
     * @since 0.1.0
     * @param array [$request] The data to send
     * @param bool [$return_json] Whether we should return any data or not
     * @return object An object containing all API return data
     */
    protected function putData($request, $return_json = TRUE)
    {
        if(!isset($this->client_secret))
        {
            throw new BCDIAPITokenError($this, self::ERROR_CLIENT_SECRET_NOT_PROVIDED);
        }

        $response = $this->curlRequest($request, FALSE);

        if($return_json)
        {
            $response_object = json_decode(preg_replace('/[[:cntrl:]]/', '', $response));

            if(!isset($response_object->result))
            {
                throw new BCDIAPIApiError($this, self::ERROR_API_ERROR, $response_object);
            }

            return $response_object;
        }
    }

    /**
     * Makes a cURL request.
     * @access Private
     * @since 0.1.0
     * @param mixed [$request] URL to fetch or the data to send via POST
     * @param boolean [$get_request] If false, send POST params
     * @return void
     */
    protected function curlRequest($request, $get_request = FALSE)
    {
        $curl = curl_init();

        if($get_request)
        {
            curl_setopt($curl, CURLOPT_URL, $request);
        } else {
            curl_setopt($curl, CURLOPT_URL, $this->getUrl('write'));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);

        $this->api_calls++;

        $curl_error = NULL;

        if(curl_errno($curl))
        {
            $curl_error = curl_error($curl);
        }

        curl_close($curl);

        if($curl_error !== NULL)
        {
            if($get_request)
            {
                throw new BCDIAPITransactionError($this, self::ERROR_READ_API_TRANSACTION_FAILED, $curl_error);
            } else {
                throw new BCDIAPITransactionError($this, self::ERROR_WRITE_API_TRANSACTION_FAILED, $curl_error);
            }
        }

        return $this->bit32clean($response);
    }

    /**
     * Cleans the response for 32-bit machine compliance.
     * @access Private
     * @since 0.1.0
     * @param string [$response] The response from a cURL request
     * @return string The cleansed string if using a 32-bit machine.
     */
    protected function bit32Clean($response)
    {
        if($this->bit32)
        {
            $response = preg_replace('/(?:((?:":\s*)(?:\[\s*)?|(?:\[\s*)|(?:\,\s*))+(\d{10,}))/', '\1"\2"', $response);
        }

        return $response;
    }

    /**
     * Determines if provided type is valid
     * @access Private
     * @since 0.1.0
     * @param string [$type] The type
     */
    protected function validType($type)
    {
        if(!in_array(strtolower($type), $this->valid_types))
        {
            throw new BCDIAPIInvalidType($this, self::ERROR_INVALID_TYPE);
        } else {
            return TRUE;
        }
    }

    /**
     * Returns the JavaScript version of the player embed code.
     * @access Public
     * @since 0.2.2
     * @deprecated 1.2.0
     * @return string The embed code
     */
    public function embed($a = NULL, $b = NULL, $c = NULL, $d = NULL, $e = NULL)
    {
        throw new BCDIAPIDeprecated($this, self::ERROR_DEPRECATED);

        return FALSE;
    }

    /**
     * Converts an error code into a textual representation.
     * @access public
     * @since 0.1.0
     * @param int [$error_code] The code number of an error
     * @return string The error text
     */
    public function getErrorAsString($error_code)
    {
        switch($error_code)
        {
            case self::ERROR_API_ERROR:
                return 'API error';
                break;
            case self::ERROR_DTO_DOES_NOT_EXIST:
                return 'The requested object does not exist';
                break;
            case self::ERROR_ID_NOT_PROVIDED:
                return 'ID not provided';
                break;
            case self::ERROR_INVALID_FILE_TYPE:
                return 'Unsupported file type';
                break;
            case self::ERROR_INVALID_JSON:
                return 'Requested method not found';
                break;
            case self::ERROR_INVALID_PROPERTY:
                return 'Requested property not found';
                break;
            case self::ERROR_INVALID_TYPE:
                return 'Type not specified';
                break;
            case self::ERROR_INVALID_UPLOAD_OPTION:
                return 'An invalid media upload parameter has been set';
                break;
            case self::ERROR_READ_API_TRANSACTION_FAILED:
                return 'Read API transaction failed';
                break;
            case self::ERROR_CLIENT_ID_NOT_PROVIDED:
                return 'Client id not provided';
                break;
            case self::ERROR_SEARCH_TERMS_NOT_PROVIDED:
                return 'Search terms not provided';
                break;
            case self::ERROR_WRITE_API_TRANSACTION_FAILED:
                return 'Write API transaction failed';
                break;
            case self::ERROR_CLIENT_SECRET_NOT_PROVIDED:
                return 'Write token not provided';
                break;
            case self::ERROR_DEPRECATED:
                return 'Access to this method or property has been deprecated';
                break;
        }
    }
}

class BCDIAPIException extends Exception
{
    /**
     * The constructor for the BCDIAPIException class
     * @access Public
     * @since 0.1.0
     * @param object [$obj] A pointer to the BCDIAPI class
     * @param int [$error_code] The error code
     * @param string [$raw_error] Any additional error information
     */
    public function __construct(BCDIAPI $obj, $error_code, $raw_error = NULL)
    {
        $error = $obj->getErrorAsString($error_code);

        if(isset($raw_error))
        {
            if(isset($raw_error->error) && isset($raw_error->error->message) && isset($raw_error->error->code))
            {
                $raw_error = $raw_error->error;
            }

            $error .= "'\n";
            $error .= (isset($raw_error->message) && isset($raw_error->code)) ? '== ' . $raw_error->message . ' (' . $raw_error->code . ') ==' . "\n" : '';
            $error .= isset($raw_error->errors[0]) ? '== ' . $raw_error->errors[0]->error . ' (' . $raw_error->errors[0]->code . ') ==' . "\n" : '';
        }

        parent::__construct($error, $error_code);
    }
}

class BCDIAPIApiError extends BCDIAPIException{}
class BCDIAPIDeprecated extends BCDIAPIException{}
class BCDIAPIDtoDoesNotExist extends BCDIAPIException{}
class BCDIAPIIdNotProvided extends BCDIAPIException{}
class BCDIAPIInvalidFileType extends BCDIAPIException{}
class BCDIAPIInvalidMethod extends BCDIAPIException{}
class BCDIAPIInvalidProperty extends BCDIAPIException{}
class BCDIAPIInvalidType extends BCDIAPIException{}
class BCDIAPIInvalidUploadOption extends BCDIAPIException{}
class BCDIAPISearchTermsNotProvided extends BCDIAPIException{}
class BCDIAPITokenError extends BCDIAPIException{}
class BCDIAPITransactionError extends BCDIAPIException{}

?>
