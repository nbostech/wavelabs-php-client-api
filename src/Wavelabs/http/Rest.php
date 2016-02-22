<?php
namespace Wavelabs\http;

use Wavelabs\http\Curl;
use Wavelabs;

class Rest
{
    protected $curl = null;

    protected $supported_formats = array(
		'xml' 				=> 'application/xml',
		'json' 				=> 'application/json',
		'serialize' 		=> 'application/vnd.php.serialized',
		'php' 				=> 'text/plain',
    	'csv'				=> 'text/csv'
	);

    protected $auto_detect_formats = array(
		'application/xml' 	=> 'xml',
		'text/xml' 			=> 'xml',
		'application/json' 	=> 'json',
		'text/json' 		=> 'json',
		'text/csv' 			=> 'csv',
		'application/csv' 	=> 'csv',
    	'application/vnd.php.serialized' => 'serialize'
	);

	protected $rest_server;
	protected $format;
	protected $mime_type;
	
	protected $http_auth = null;
	protected $http_user = null;
	protected $http_pass = null;

    protected $response_string;
    private $last_http_code = null;
    private $last_response = null;
	protected $http_headers = [];

    const HTTP_OK = 200;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    function __construct($config = array())
    {
        $this->curl = new Curl();

		// If a URL was passed to the library
		empty($config) OR $this->initialize($config);
    }

	function __destruct()
	{
        $this->curl->set_defaults();
	}

    public function initialize($config)
    {
		$this->rest_server = @$config['server'];

		if (substr($this->rest_server, -1, 1) != '/')
		{
			$this->rest_server .= '/';
		}

		isset($config['http_auth']) && $this->http_auth = $config['http_auth'];
		isset($config['http_user']) && $this->http_user = $config['http_user'];
		isset($config['http_pass']) && $this->http_pass = $config['http_pass'];
    }


    public function get($uri, $params = array(), $format = NULL)
    {
        if ($params)
        {
        	$uri .= '?'.(is_array($params) ? http_build_query($params) : $params);
        }

    	return $this->_call('get', $uri, NULL, $format);
    }


    public function post($uri, $params = array(), $format = 'json')
    {
        return $this->_call('post', $uri, $params, $format);
    }


    public function put($uri, $params = array(), $format = 'json')
    {
        return $this->_call('put', $uri, $params, $format);
    }


    public function delete($uri, $params = array(), $format = NULL)
    {
        return $this->_call('delete', $uri, $params, $format);
    }

    public function api_key($key, $name = 'X-API-KEY')
	{
		$this->curl->http_header($name, $key);
	}

    public function language($lang)
	{
		if (is_array($lang))
		{
			$lang = implode(', ', $lang);
		}

		$this->curl->http_header('Accept-Language', $lang);
	}

    protected function _call($method, $uri, $params = array(), $format = NULL)
    {
    	if ($format !== NULL)
		{
			$this->format($format);
		}

		$this->_set_headers();

        // Initialize cURL session
        $this->curl->create($uri);

        // If authentication is enabled use it
        if ($this->http_auth != '' && $this->http_user != '')
        {
        	$this->curl->http_login($this->http_user, $this->http_pass, $this->http_auth);
        }

        // We still want the response even if there is an error code over 400
        $this->curl->option('failonerror', FALSE);

        // Call the correct method with parameters
        $this->curl->{$method}($params);

        // Execute and return the response from the REST server
        $response = $this->curl->execute();

        // Format
        $response = $this->_format_response($response);

        $this->last_http_code = isset($this->curl->last_info['http_code'])?$this->curl->last_info['http_code']:"";
        $this->last_response = $this->validateResponse($response);

        return $this->last_response;
    }


    // If a type is passed in that is not supported, use it as a mime type
    public function format($format)
	{
		if (array_key_exists($format, $this->supported_formats))
		{
			$this->format = $format;
			$this->mime_type = $this->supported_formats[$format];
		}else{
			$this->mime_type = $format;
		}
        $this->curl->format = $this->format;
        $this->curl->mime_type = $this->mime_type;

		return $this;
	}

	// Return HTTP status code
	public function status()
	{
		return $this->info('http_code');
	}

	// Return curl info by specified key, or whole array
	public function info($key = null)
	{
		return $key === null ? $this->curl->info : @$this->curl->info[$key];
	}

	// Set custom options
	public function option($code, $value)
	{
		$this->curl->option($code, $value);
	}

	protected function _set_headers()
	{
		//$this->curl->http_header('Accept: '.$this->mime_type);
        if(!empty($this->mime_type) && in_array($this->mime_type, $this->supported_formats) ){
            $this->curl->http_header('Content-Type: '.$this->mime_type);
        }else if(!empty($this->mime_type) && $this->mime_type == "x-www-form-urlencoded"){
			$this->mime_type = "json";
			$this->format = "json";
            $this->curl->http_header('Content-Type: application/x-www-form-urlencoded');
        }else{
			$this->mime_type = "json";
			$this->format = "json";
			$this->curl->http_header('Content-Type: multipart/form-data');
		}
		if(!empty($this->http_headers)){
			foreach($this->http_headers as $key => $val){
				$this->curl->http_header($key.': '.$val);
			}
		}
	}

	protected function _format_response($response)
	{
		$this->response_string =& $response;
		// It is a supported format, so just run its formatting method
		if (array_key_exists($this->format, $this->supported_formats))
		{
			return $this->{"_".$this->format}($response);
		}
		exit;
		// Find out what format the data was returned in
		$returned_mime = @$this->curl->info['content_type'];

		// If they sent through more than just mime, stip it off
		if (strpos($returned_mime, ';'))
		{
			list($returned_mime)=explode(';', $returned_mime);
		}

		$returned_mime = trim($returned_mime);

		if (array_key_exists($returned_mime, $this->auto_detect_formats))
		{
			return $this->{'_'.$this->auto_detect_formats[$returned_mime]}($response);
		}

		return $response;
	}


    // Format XML for output
    protected function _xml($string)
    {
    	return $string ? (array) simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA) : array();
    }

    // Format HTML for output
    // This function is DODGY! Not perfect CSV support but works with my REST_Controller
    protected function _csv($string)
    {
		$data = array();

		// Splits
		$rows = explode("\n", trim($string));
		$headings = explode(',', array_shift($rows));
		foreach( $rows as $row )
		{
			// The substr removes " from start and end
			$data_fields = explode('","', trim(substr($row, 1, -1)));

			if (count($data_fields) == count($headings))
			{
				$data[] = array_combine($headings, $data_fields);
			}

		}

		return $data;
    }

    // Encode as JSON
    protected function _json($string)
    {
    	return json_decode(trim($string));
    }

    // Encode as Serialized array
    protected function _serialize($string)
    {
    	return unserialize(trim($string));
    }

    // Encode raw PHP
    protected function _php($string)
    {
    	$string = trim($string);
    	$populated = array();
    	eval("\$populated = \"$string\";");
    	return $populated;
    }

    public function validateResponse($response){
        Wavelabs\core\ApiBase::$error = null;
        Wavelabs\core\ApiBase::$message = null;
        if(isset($response->errors)){
            Wavelabs\core\ApiBase::setErrors($response->errors);
        }else if(isset($response->error_description)){
            Wavelabs\core\ApiBase::setError($response->error_description);
        }else if(isset($response->message)){
			if($this->last_http_code == 200){
				Wavelabs\core\ApiBase::setMessage($response->message);
			}else{
				Wavelabs\core\ApiBase::setError($response->message);
			}
        }
        return $response;
    }

    public function getLastHttpCode(){
        return $this->last_http_code;
    }

    public function getLastResponse(){
        return $this->last_response;
    }

	public function setHttpHeader($key, $val){
		$this->http_headers[$key] = $val;
	}

}
