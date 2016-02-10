<?php
namespace Wavelabs\core;

use Wavelabs\core\ApiBase;

class Social extends ApiBase{

    function __construct(){
        parent::__construct();
    }

    function facebookConnect($accessToken){
        $this->last_response = $this->rest->post("auth/social/facebook/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function googleConnect($accessToken){
        $this->last_response = $this->rest->post("auth/social/googlePlus/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function twitterConnect($accessToken){
        $this->last_response = $this->rest->post("auth/social/twitter/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function facebookLogin(){
        $this->last_response = $this->rest->get("auth/social/facebook/login/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }
}