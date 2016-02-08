<?php
namespace Wavelabs\core;

use Wavelabs\http\Rest;

class ApiBase {

    protected $curl = null;
    protected $rest = null;
    protected $clientId = CLIENT_ID;
    protected $clientSecret = CLIENT_SECRET;
    protected $token = null;

    function __construct(){
        $this->rest = new Rest([
            "server" => API_URL
        ]);
        if(!empty($_SESSION['api_token'])){
            $this->token = $_SESSION['api_token'];
            $this->rest->api_key($this->token->token_type." ".$this->token->access_token, "Authorization");
        }
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

}