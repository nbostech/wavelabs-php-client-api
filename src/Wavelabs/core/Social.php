<?php
namespace Wavelabs\core;

use Wavelabs\core\ApiBase;

class Social extends ApiBase{

    function __construct(){
        parent::__construct();
    }

    function facebookConnect($accessToken){
        return $this->rest->post("auth/social/facebook/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
    }

    function googleConnect($accessToken){
        return $this->rest->post("auth/social/googlePlus/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
    }

    function twitterConnect($accessToken){
        return $this->rest->post("auth/social/twitter/connect/", [
            "clientId" => $this->clientId,
            "accessToken" => $accessToken
        ]);
    }

    function facebookLogin(){
        return $this->rest->get("auth/social/facebook/login/");
    }
}