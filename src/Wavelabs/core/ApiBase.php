<?php
namespace Wavelabs\core;

use Wavelabs\http\Rest;

class ApiBase {

    protected $curl = null;
    protected $rest = null;
    protected $clientId = null;
    protected $clientSecret = null;
    protected $token = null;
    protected $member = null;
    protected $last_response = null;
    protected $last_http_code = null;
    public static $errors = [];
    public static $fields = [];
    public static $error = null;
    public static $message = null;


    function __construct(){
        defined('API_URL')        OR define('API_URL', "http://starterapp.com:8080/starter-app-rest-grails/api/v0/");
        defined('CLIENT_ID')      OR define('CLIENT_ID', "my-client");
        defined('CLIENT_SECRET')  OR define('CLIENT_SECRET', "");
        ApiBase::$fields = $_POST + $_GET;

        if(defined('CLIENT_ID') && defined('CLIENT_SECRET')){
            $this->setClient(CLIENT_ID, CLIENT_SECRET);
        }else if(defined('CLIENT_ID')){
            $this->setClient(CLIENT_ID);
        }
        $this->rest = new Rest([
            "server" => API_URL
        ]);
        if(!empty($_SESSION['api_token'])){
            $this->token = $_SESSION['api_token'];
            $this->rest->api_key($this->token->token_type." ".$this->token->access_token, "Authorization");
        }
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

    function resetToken(){
        unset($_SESSION['api_token']);
        $this->token = null;
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