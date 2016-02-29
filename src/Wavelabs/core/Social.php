<?php
namespace Wavelabs\core;

use Wavelabs\core\ApiBase;

class Social extends ApiBase{

    function __construct(){
        parent::__construct();
    }

    function facebookConnect($accessToken){
        $this->setClientTokenHeader();
        $this->last_response = $this->apiCall('post', API_HOST_URL . "api/identity/v0/auth/social/facebook/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
        return $this->last_response;
    }

    function googleConnect($accessToken){
        $this->setClientTokenHeader();
        $this->last_response = $this->apiCall('post', API_HOST_URL . "api/identity/v0/auth/social/googlePlus/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
        return $this->last_response;
    }

    function twitterConnect($accessToken){
        $this->setClientTokenHeader();
        $this->last_response = $this->apiCall('post', API_HOST_URL . "api/identity/v0/auth/social/twitter/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
        return $this->last_response;
    }

    function instagramConnect($accessToken){
        $this->setClientTokenHeader();
        $this->last_response = $this->apiCall('post', API_HOST_URL . "api/identity/v0/auth/social/instagram/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
        return $this->last_response;
    }

    function gitHubConnect($accessToken){
        $this->setClientTokenHeader();
        $this->last_response = $this->apiCall('post', API_HOST_URL . "api/identity/v0/auth/social/gitHub/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
        return $this->last_response;
    }

    function facebookLogin(){
        $this->setClientTokenHeader();
        $this->last_response = $this->apiCall('get', API_HOST_URL . "api/identity/v0/auth/social/facebook/login/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function googleLogin(){
        $this->setClientTokenHeader();
        $this->last_response = $this->rest->get(API_HOST_URL . "api/identity/v0/auth/social/googlePlus/login/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function twitterLogin(){
        $this->setClientTokenHeader();
        $this->last_response = $this->rest->get(API_HOST_URL . "api/identity/v0/auth/social/twitter/login/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function instagramLogin(){
        $this->setClientTokenHeader();
        $this->last_response = $this->rest->get(API_HOST_URL . "api/identity/v0/auth/social/instagram/login/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function gitHubLogin(){
        $this->setClientTokenHeader();
        $this->last_response = $this->rest->get(API_HOST_URL . "api/identity/v0/auth/social/gitHub/login/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }
}