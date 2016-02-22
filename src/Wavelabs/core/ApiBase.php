<?php
namespace Wavelabs\core;

use Wavelabs\http\Rest;

class ApiBase {

    protected $curl = null;
    protected $rest = null;
    protected $clientId = null;
    protected $clientSecret = null;
    protected $token = null;
    protected $clientToken = null;
    protected $member = null;
    protected $last_response = null;
    protected $last_http_code = null;
    public static $errors = [];
    public static $fields = [];
    public static $error = null;
    public static $message = null;


    function __construct(){
        defined('API_BASE_URL')   OR define('API_BASE_URL', "http://10.9.9.76:8080/starter-app-rest-grails/");
        defined('CLIENT_ID')      OR define('CLIENT_ID', "my-client");
        defined('CLIENT_SECRET')  OR define('CLIENT_SECRET', "my-secret");
        ApiBase::$fields = $_POST + $_GET;

        if(defined('CLIENT_ID') && defined('CLIENT_SECRET')){
            $this->setClient(CLIENT_ID, CLIENT_SECRET);
        }else if(defined('CLIENT_ID')){
            $this->setClient(CLIENT_ID);
        }
        $this->rest = new Rest();
        if(!empty($_SESSION['api_token'])){
            $this->token = $_SESSION['api_token'];
            $this->rest->setHttpHeader("Authorization", $this->token->token_type." ".$this->token->access_token);
        }
    }

    function apiCall($method, $url, $parems = null, $format = "json"){
        if(method_exists($this->rest, $method)){
            $this->last_response = $this->rest->{$method}($url, $parems, $format);
            $this->last_http_code = $this->rest->getLastHttpCode();
            self::$error = null;
            self::$message = null;
            if(isset($this->last_response->errors)){
                self::setErrors($this->last_response->errors);
            }else if(isset($this->last_response->error_description)){
                self::setError($this->last_response->error_description);
            }else if(isset($this->last_response->message)){
                if($this->last_http_code == 200){
                    self::setMessage($this->last_response->message);
                }else{
                    self::setError($this->last_response->message);
                }
            }
            self::__construct();
            return $this->last_response;
        }
        return false;
    }

    function getLastResponse(){
        return $this->last_response;
    }

    function getLastHttpCode(){
        return $this->last_http_code;
    }

    function setClient($clientId, $clientSecret = null){
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    function setToken($token){
        $_SESSION['api_token'] = $token;
        $this->token = $token;
    }

    function getToken(){
        return $this->token;
    }

    function resetToken(){
        unset($_SESSION['api_token']);
        $this->token = null;
    }

    function setClientToken($token){
        $_SESSION['api_client_token'] = $token;
        $this->clientToken = $token;
    }

    function getClientToken($new = false)
    {
        if($this->clientToken !== null){
            return $this->clientToken;
        }else if(!empty($_SESSION['api_client_token'])){
            $this->clientToken = $_SESSION['api_client_token'];
            return $this->clientToken;
        }else {
            $this->last_response = $this->apiCall("post", API_BASE_URL . "oauth/token", [
                "client_id" => "my-client",
                "client_secret" => "my-secret",
                "grant_type" => "client_credentials",
                "scope" => "oauth.client.r"
            ], "x-www-form-urlencoded");
            if(!empty($this->last_response->access_token)){
                $this->setClientToken($this->last_response);
            }
        }
        return $this->clientToken;
    }

    function resetClientToken(){
        unset($_SESSION['api_client_token']);
        $this->clientToken = null;
    }

    function setClientTokenHeader(){
        $this->getClientToken();
        if(isset($this->clientToken->access_token)){
            $this->rest->setHttpHeader("Authorization", $this->clientToken->token_type." ".$this->clientToken->access_token);
        }
    }

    public static function setErrors($errors = []){
        foreach($errors as $error){
            if(!empty($error->propertyName)){
                self::$errors[$error->propertyName] = $error->message;
            }
        }
    }

    public static function setError($error) {
        self::$error = $error;
    }

    public static function setMessage($message) {
        self::$message = $message;
    }

    public static function getErrors(){
        return self::$errors;
    }

    public static function getFormErrors(){
        return self::$errors;
    }

    public static function getError() {
        return self::$error;
    }

    public static function getMessage() {
        return self::$message;
    }

}