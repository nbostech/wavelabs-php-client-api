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

    function instagramConnect($accessToken){
        $this->last_response = $this->rest->post("auth/social/instagram/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function gitHubConnect($accessToken){
        $this->last_response = $this->rest->post("auth/social/gitHub/connect/", [
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

    function googleLogin(){
        $this->last_response = $this->rest->get("auth/social/googlePlus/login/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function twitterLogin(){
        $this->last_response = $this->rest->get("auth/social/twitter/login/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function instagramLogin(){
        $this->last_response = $this->rest->get("auth/social/instagram/login/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function gitHubLogin(){
        $this->last_response = $this->rest->get("auth/social/gitHub/login/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }
}