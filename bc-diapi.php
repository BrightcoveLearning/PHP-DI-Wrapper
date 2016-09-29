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
    const ERROR_CLIENT_CREDENTIALS_NOT_PROVIDED = 9;
    const ERROR_CLIENT_SECRET_NOT_PROVIDED = 11;
    const ERROR_DEPRECATED = 99;
    const ERROR_DTO_DOES_NOT_EXIST = 12;
    const ERROR_INVALID_FILE_TYPE = 5;
    const ERROR_INVALID_JSON = 3;
    const ERROR_INVALID_PROPERTY = 4;
    const ERROR_INVALID_CALL = 6;
    const ERROR_INVALID_UPLOAD_OPTION = 7;
    const ERROR_READ_API_TRANSACTION_FAILED = 8;
    const ERROR_SEARCH_TERMS_NOT_PROVIDED = 13;
    const ERROR_WRITE_API_TRANSACTION_FAILED = 10;


    protected $access_token = null;
    protected $account_id = null;
    protected $bit32 = false;
    protected $client_id = null;
    protected $client_secret = null;
    protected $cms_data = null;
    protected $current_request = null;
    protected $di_data = null;
    protected $di_suffix = '/ingest-requests';
    protected $file_name = null;
    protected $is_pull_request = true;
    protected $job_id = null;
    protected $job_status = null;
    protected $options = null;
    protected $parsed_data = array();
    protected $result_parsed = null;
    protected $show_notices = false;
    protected $signed_url = null;
    protected $timeout_attempts = 100;
    protected $timeout_current = 0;
    protected $timeout_delay = 1;
    protected $timeout_retry = false;
    protected $token_expires = null;
    protected $unsigned_url = null;
    protected $url_cms = 'https://cms.api.brightcove.com/v1/accounts/';
    protected $url_di = 'https://ingest.api.brightcove.com/v1/accounts/';
    protected $url_oauth = 'https://oauth.brightcove.com/v3/access_token?grant_type=client_credentials';
    protected $video_id = null;

    /**
     * The constructor for the BCDIAPI class.
     * @access Public
     * @since 0.1.0
     * @param string [$account_id] The Video Cloud account id (required)
     * @param string [$client_id] The read API token for the Brightcove account (required)
     * @param string [$client_secret] The write API token for the Brightcove account (required)
     */
    public function __construct($account_id = null, $client_id = null, $client_secret = null)
    {
        $this->account_id = $account_id;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->auth_string = "{$client_id}:{$client_secret}";
        $this->bit32 = ((string)'99999999999999' == (int)'99999999999999') ? false : true;
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
     * @param string [$video_url] URL for the video (for pull-based ingestion; required if $video_file is null)
     * @param string [$video_file] video file location (for pull-based ingestion; required if $video_file is null)
     * @param string [$profile] Name of the ingest profile to use - if null, default profile for the account will be used
     * @param boolean [$capture_images] Whether Video Cloud should capture images for the video still and thumbnail during trancoding - should be set to false if the poster and thumbnail are provided
     * @param array [$poster] Video still information - if included, keys are: url (required0; height (optional); width (optional)
     * @param array [$thumbnail] thumbnail information - if included, keys are: url (required0; height (optional); width (optional)
     * @param array[] [$text_tracks] text tracks information - if included, each object in the array has keys: url (required), srclang (required); kind (optional); label (optional); default (optional)
     * @param string[] [$callbacks] array of callback URLs (optional)
     * @return object status of the ingest
     */
    public function add_video($video_name = null, $video_metadata = null, $video_url = null, $video_file = null, $profile = null, $capture_images = true, $poster = null, $thumbnail = null, $text_tracks = null, $callbacks = null) {
        // get file name
        if (isset($video_url)) {
            $tmp = $video_url;
        } else if (isset($video_file)) {
            $this->is_pull_request = false;
            $tmp = $video_file;
        }
        $this->file_name = urlencode(array_pop(explode('/'), $tmp));

        // set up ingest request data
        $this->di_data = array(
            'master' => array(),
        );
        if (isset($profile)) {
            $this->di_data['profile'] = $profile;
        }
        if (isset($poster)) {
            $this->di_data['capture_images'] = false;
            $this->di_data['poster'] = $poster;
        }
        if (isset($thumbnail)) {
            $this->di_data['thumbnail'] = $thumbnail;
        }
        if (isset($text_tracks)) {
            $this->di_data['text_tracks'] = $text_tracks;
        }
        if (isset($video_url)) {
            $this->di_data['master']['url'] = $video_url;
        }
        // set up CMS request data
        if (isset($video_metadata)) {
            $this->cms_data = $video_metadata;
        } else {
            $this->cms_data = array();
        }
        if (!isset($video_metadata->name)) {
            if (isset($video_name)) {
                $this->cms_data['name'] = $video_name;
            } else {
                $this->cms_data['name'] = $this->file_name;
            }
        }

        // data in place, make api requests
        $cms_response = json_decode($this->make_request('create_video', $this->cms_data));
        $this->video_id = $cms_response['id'];
        if ($this->is_pull_request) {
            $di_response = json_decode($this->make_request('ingest_video', $this->di_data));
            $this->job_id = $di_response['job_id'];
            return json_decode($this->make_request('get_status', null));
        } else {
            $s3_response = json_decode($this->make_request('get_s3urls', null));
            $this->signed_url = $s3_response['SignedUrl'];
            $this->unsigned_url = $s3_response['ApiRequestUrl'];
        }

    }

    /**
     * Retrieves an access token if there is not a valid one already, and updates the token expiration
     * @since 0.1.0
     * @return string Access token
     */
    private function get_access_token() {
        if (isset($this->token_expires)) {
            if ($this->token_expires > time()) {
                $result_parsed = json_decode($this->make_request('get_token', null));
                $this->access_token = $result_parsed['access_token'];
                $this->token_expires = time() + $result_parsed['expires_in'];
            }
        } else {
            $result_parsed = json_decode($this->make_request('get_token', null));
            $this->access_token = $result_parsed['access_token'];
            $this->token_expires = time() + $result_parsed['expires_in'];
        }
        return $this->access_token;
    }

    /**
     * Formats the request for any API requests and retrieves the data.
     * @access Private
     * @since 0.1.0
     * @param string [$call] The requested API method
     * @param mixed [$params] A key-value array of API parameters, or a single value that matches the default
     * @return object An object containing all API return data
     */
    private function make_request($call, $request_data = null) {
        $this->timeout_current = 0;
        $options = array();

        if (isset($request_data)) {
            if (is_null(json_decode($request_data))) {
                $data = array();
            }
            $data = $request_data;
        } else {
            $data = array();
        }

        switch($call)
        {
            case 'get_token':
                $options['url'] = $this->url_oauth;
                $options['method'] = 'POST';
                $options['headers'] = array('Content-type: application/x-www-form-urlencoded');
                $options['user_pwd'] = $this->$auth_sting;
                return $this->send_request($url, $options);
                break;
            case 'create_video':
                $options['url'] = $this->url_cms . $this->account_id . '/videos';
                $options['method'] = 'POST';
                $this->$access_token = $this->get_access_token();
                $options['headers'] = array(
                    'Content-type: application/json',
                    'Authorization: Bearer ' . $access_token
                );
                return $this->send_request($url, $options);
                break;
            case 'get_s3urls':
                $options['url'] = $this->url_di . $this->account_id . '/videos/' . $this->video_id . '/upload-urls/' . $this->file_name;
                $options['method'] = 'GET';
                $this->access_token = $this->get_access_token();
                $options['headers'] = array(
                    'Content-type: application/json',
                    'Authorization: Bearer ' . $this->access_token
                );
                return $this->send_request($url, $options);
                break;
            case 'put_video':
                $options['url'] = $this->signed_url;
                $options['method'] = 'PUT';
                break;
            case 'ingest_video':
                $options['url'] = $this->url_di . $this->account_id . '/videos/' . $this->video_id . '/ingest-requests';
                $options['method'] = 'POST';
                $this->access_token = $this->get_access_token();
                $options['headers'] = array(
                'Content-type: application/json',
                'Authorization: Bearer ' . $this->access_token
                );
                return $this->send_request($options);
                break;
            case 'get_status':
                $options['url'] = $this->url_di . $this->account_id . '/videos/' . $this->video_id . '/ingest_jobs/' . $this->job_id;
                $options['method'] = 'GET';
                $this->access_token = $this->get_access_token();
                $options['headers'] = array(
                'Content-type: application/json',
                'Authorization: Bearer ' . $this->access_token
                );
                return $this->send_request($url, $options);
                break;
            default:
                throw new BCDIAPIInvalidMethod($this, self::ERROR_INVALID_CALL);
                break;
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
     * Retrieves API data from provided URL.
     * @access Private
     * @since 0.1.0
     * @param string [$url] The complete API request URL
     * @param string [$method] The HTTP method for the request
     * @param array [$headers] The HTTP headers to send with the request
     * @param string [$data_string] A JSON string containing the request body (if any) to send with the request
     * @return object An object containing all API return data
     */
    protected function send_request($options = null) {



        // $this->timeout_current++;

        if(!isset($this->client_id) || !isset($this->client_secret)) {
            throw new BCDIAPITokenError($this, self::ERROR_CLIENT_CREDENTIALS_NOT_PROVIDED);
        }

        $response = $this->curlRequest($options);

        if($response && $response != 'null')
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
                if(isset($response_object->items))
                {
                    $data = $response_object->items;
                } else {
                    $data = $response_object;
                }

                $this->page_number = isset($response_object->page_number) ? $response_object->page_number : null;
                $this->page_size = isset($response_object->page_size) ? $response_object->page_size : null;
                $this->total_count = isset($response_object->total_count) ? $response_object->total_count : null;

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
    protected function putData($request, $return_json = true)
    {

        $response = $this->curlRequest($request, false);

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
    protected function curlRequest($options) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $options['url']);
        if (isset($options['headers'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $options['headers']);
        }
        if ($options['method'] === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $options['data']);
            if (isset($options['user_pwd'])) {
                curl_setopt($curl, CURLOPT_USERPWD, $options['user_pwd']);
            }
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);

        // $this->api_calls++;

        $curl_error = null;

        if(curl_errno($curl))
        {
            $curl_error = curl_error($curl);
        }

        curl_close($curl);

        if($curl_error !== null)
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
            return true;
        }
    }

    /**
     * Returns the JavaScript version of the player embed code.
     * @access Public
     * @since 0.2.2
     * @deprecated 1.2.0
     * @return string The embed code
     */
    public function embed($a = null, $b = null, $c = null, $d = null, $e = null)
    {
        throw new BCDIAPIDeprecated($this, self::ERROR_DEPRECATED);

        return false;
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
            case self::ERROR_CLIENT_CREDENTIALS_NOT_PROVIDED:
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
    public function __construct(BCDIAPI $obj, $error_code, $raw_error = null)
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
