<?php
namespace NBOS\http;

class Curl {

	protected $response = '';       // Contains the cURL response for debug
	protected $response_header = '';
	protected $session;             // Contains the cURL handler for a session
	protected $url;                 // URL of the session
	protected $options = array();   // Populates curl_setopt_array
	protected $headers = array();   // Populates extra HTTP headers
    protected $http_method = 'get';
    public $format = null;
    public $mime_type = null;
	public $error_code;             // Error code returned as an int
	public $error_string;           // Error message returned as a string
	public $info = null;            // Returned after request (elapsed time, etc)
	private $log = null;
	public $last_info = null;
	public $last_response_header = null;

	function __construct($url = '')
	{
		$url AND $this->create($url);
		if(defined('CURL_DEBUG') && CURL_DEBUG === true && class_exists('\Monolog\Logger')){
			$this->log = new \Monolog\Logger('client_request');
			$log_file_path = dirname(dirname(__FILE__)).'/logs/'.date("Y-m-d").'api_requests.log';
			if(!file_exists(dirname(dirname(__FILE__)).'/logs/')){
				mkdir(dirname(dirname(__FILE__)).'/logs/');
			}
			if(!file_exists($log_file_path)){
				$fp = fopen($log_file_path, 'a');
				fclose($fp);
			}
			$this->log->pushHandler(new \Monolog\Handler\StreamHandler($log_file_path, \Monolog\Logger::INFO));
		}
	}

	public function __call($method, $arguments)
	{
		if (in_array($method, array('simple_get', 'simple_post', 'simple_put', 'simple_delete', 'simple_patch')))
		{
			// Take off the "simple_" and past get/post/put/delete/patch to _simple_call
			$verb = str_replace('simple_', '', $method);
			array_unshift($arguments, $verb);
			return call_user_func_array(array($this, '_simple_call'), $arguments);
		}
	}

	/* =================================================================================
	 * SIMPLE METHODS
	 * Using these methods you can make a quick and easy cURL call with one line.
	 * ================================================================================= */

	public function _simple_call($method, $url, $params = array(), $options = array())
	{
		// Get acts differently, as it doesnt accept parameters in the same way
		if ($method === 'get')
		{
			// If a URL is provided, create new session
			$this->create($url.($params ? '?'.http_build_query($params, NULL, '&') : ''));
		}

		else
		{
			// If a URL is provided, create new session
			$this->create($url);

			$this->{$method}($params);
		}

		// Add in the specific options provided
		$this->options($options);

		return $this->execute();
	}

	public function simple_ftp_get($url, $file_path, $username = '', $password = '')
	{
		// If there is no ftp:// or any protocol entered, add ftp://
		if ( ! preg_match('!^(ftp|sftp)://! i', $url))
		{
			$url = 'ftp://' . $url;
		}

		// Use an FTP login
		if ($username != '')
		{
			$auth_string = $username;

			if ($password != '')
			{
				$auth_string .= ':' . $password;
			}

			// Add the user auth string after the protocol
			$url = str_replace('://', '://' . $auth_string . '@', $url);
		}

		// Add the filepath
		$url .= $file_path;

		$this->option(CURLOPT_BINARYTRANSFER, TRUE);
		$this->option(CURLOPT_VERBOSE, TRUE);

		return $this->execute();
	}

	/* =================================================================================
	 * ADVANCED METHODS
	 * Use these methods to build up more complex queries
	 * ================================================================================= */

	public function post($params = array(), $options = array())
	{
		// If its an array (instead of a query string) then format it correctly
		if (is_array($params))
		{
            if($this->format == "json"){
                $params = json_encode($params);
            }else{
                if(!empty($_FILES)){
                    foreach($_FILES as $fileField => $fileVal){
                        $params[$fileField] = curl_file_create($fileVal['tmp_name'], $fileVal['type'], $fileVal['name']);
                    }
                }else{
					$params = http_build_query($params, NULL, '&');
				}
            }
		}

		// Add in the specific options provided
		$this->options($options);

		$this->http_method('post');

		$this->option(CURLOPT_POST, TRUE);
		$this->option(CURLOPT_POSTFIELDS, $params);
	}

	public function put($params = array(), $options = array())
	{
		// If its an array (instead of a query string) then format it correctly
		if (is_array($params))
		{
            if($this->format == "json"){
                $params = json_encode($params);
            }else{
                //$params = http_build_query($params, NULL, '&');
            }
		}

		// Add in the specific options provided
		$this->options($options);

		$this->http_method('put');
		$this->option(CURLOPT_POSTFIELDS, $params);

		// Override method, I think this overrides $_POST with PUT data but... we'll see eh?
		$this->option(CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
	}
	
	public function patch($params = array(), $options = array())
	{
		// If its an array (instead of a query string) then format it correctly
		if (is_array($params))
		{
			$params = http_build_query($params, NULL, '&');
		}

		// Add in the specific options provided
		$this->options($options);

		$this->http_method('patch');
		$this->option(CURLOPT_POSTFIELDS, $params);

		// Override method, I think this overrides $_POST with PATCH data but... we'll see eh?
		$this->option(CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PATCH'));
	}

	public function delete($params, $options = array())
	{
		// If its an array (instead of a query string) then format it correctly
		if(is_array($params) && $this->format == "json"){
			$params = json_encode($params);
		}else if (is_array($params)){
			$params = http_build_query($params, NULL, '&');
		}

		// Add in the specific options provided
		$this->options($options);

		$this->http_method('delete');

		$this->option(CURLOPT_POSTFIELDS, $params);
	}

	public function set_cookies($params = array())
	{
		if (is_array($params))
		{
			$params = http_build_query($params, NULL, '&');
		}

		$this->option(CURLOPT_COOKIE, $params);
		return $this;
	}

	public function http_header($header, $content = NULL)
	{
		$this->headers[] = $content ? $header . ': ' . $content : $header;
		return $this;
	}

	public function http_method($method)
	{
        $this->http_method = $method;
		$this->options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
		return $this;
	}

	public function http_login($username = '', $password = '', $type = 'any')
	{
		$this->option(CURLOPT_HTTPAUTH, constant('CURLAUTH_' . strtoupper($type)));
		$this->option(CURLOPT_USERPWD, $username . ':' . $password);
		return $this;
	}

	public function proxy($url = '', $port = 80)
	{
		$this->option(CURLOPT_HTTPPROXYTUNNEL, TRUE);
		$this->option(CURLOPT_PROXY, $url . ':' . $port);
		return $this;
	}

	public function proxy_login($username = '', $password = '')
	{
		$this->option(CURLOPT_PROXYUSERPWD, $username . ':' . $password);
		return $this;
	}

	public function ssl($verify_peer = TRUE, $verify_host = 2, $path_to_cert = NULL)
	{
		if ($verify_peer)
		{
			$this->option(CURLOPT_SSL_VERIFYPEER, TRUE);
			$this->option(CURLOPT_SSL_VERIFYHOST, $verify_host);
			if (isset($path_to_cert)) {
				$path_to_cert = realpath($path_to_cert);
				$this->option(CURLOPT_CAINFO, $path_to_cert);
			}
		}
		else
		{
			$this->option(CURLOPT_SSL_VERIFYPEER, FALSE);
		}
		return $this;
	}

	public function options($options = array())
	{
		// Merge options in with the rest - done as array_merge() does not overwrite numeric keys
		foreach ($options as $option_code => $option_value)
		{
			$this->option($option_code, $option_value);
		}

		// Set all options provided
		curl_setopt_array($this->session, $this->options);

		return $this;
	}

	public function option($code, $value, $prefix = 'opt')
	{
		if (is_string($code) && !is_numeric($code))
		{
			$code = constant('CURL' . strtoupper($prefix) . '_' . strtoupper($code));
		}

		$this->options[$code] = $value;
		return $this;
	}

	// Start a session from a URL
	public function create($url)
	{
		$this->url = $url;
		$this->session = curl_init($this->url);

		return $this;
	}

	// End a session and return the results
	public function execute()
	{
		// Set two default options, and merge any extra ones in
		if ( ! isset($this->options[CURLOPT_TIMEOUT]))
		{
			$this->options[CURLOPT_TIMEOUT] = 30;
		}
		if ( ! isset($this->options[CURLOPT_RETURNTRANSFER]))
		{
			$this->options[CURLOPT_RETURNTRANSFER] = TRUE;
		}
		if ( ! isset($this->options[CURLOPT_FAILONERROR]))
		{
			$this->options[CURLOPT_FAILONERROR] = TRUE;
		}

		// Only set follow location if not running securely
		if ( ! ini_get('safe_mode') && ! ini_get('open_basedir'))
		{
			// Ok, follow location is not set already so lets set it to true
			if ( ! isset($this->options[CURLOPT_FOLLOWLOCATION]))
			{
				$this->options[CURLOPT_FOLLOWLOCATION] = TRUE;
			}
		}

		if ( ! empty($this->headers))
		{
			$this->option(CURLOPT_HTTPHEADER, $this->headers);
		}

		$this->options();
        curl_setopt($this->session, CURLINFO_HEADER_OUT, true);
		curl_setopt($this->session, CURLOPT_HEADER, 1);

		// Execute the request & and hide all output
		$this->response = curl_exec($this->session);
		if($this->response !== false){
			$this->info = curl_getinfo($this->session);
			// Split header and body response
			//$header_size = curl_getinfo($this->session, CURLINFO_HEADER_SIZE);
			//$this->response_header = substr($this->response, 0, $header_size);
			//$this->response = substr($this->response, $header_size);
			list($this->response_header, $this->response) = explode("\r\n\r\n", $this->response);

			if(defined('CURL_DEBUG') && $this->log !== null){
				$this->log->addInfo($this->url, [
					"Request" => $this->info,
					"Response" => $this->response
				]);
			}
		}else{
			if(defined('CURL_DEBUG') && $this->log !== null){
				$this->log->addError("Server not responding!", ["URL" => $this->url], ["message" => "test"]);
			}
		}

		// Request failed
		if ($this->response === FALSE)
		{
			$errno = curl_errno($this->session);
			$error = curl_error($this->session);

			curl_close($this->session);
			$this->set_defaults();

			$this->error_code = $errno;
			$this->error_string = $error;

			return FALSE;
		}
		// Request successful
		else
		{
			curl_close($this->session);
			$this->last_response = $this->response;
			$this->last_response_header = $this->response_header;
            $this->last_info = $this->info;
			$this->set_defaults();
			return $this->last_response;
		}
	}

	public function is_enabled()
	{
		return function_exists('curl_init');
	}

	public function debug_request()
	{
		return array(
			'url' => $this->url
		);
	}

	public function set_defaults()
	{
		$this->response = '';
		$this->headers = array();
		$this->options = array();
		$this->error_code = NULL;
		$this->error_string = '';
		$this->session = NULL;
	}

}

