<?php
namespace Wavelabs\core;

use Wavelabs\core\ApiBase;

class Auth extends ApiBase{

    function __construct(){
        parent::__construct();
    }

    function signup($userData){
        $userData['clientId'] = $this->clientId;
        $this->last_response = $this->rest->post("users/signup/", $userData);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function login($username, $password){
        $this->last_response = $this->rest->post("auth/login/", [
            "clientId" => $this->clientId,
            "username" => $username,
            "password" => $password
        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        if(!empty($this->last_response->token)){
            $this->setToken($this->last_response->token);
        }
        return $this->last_response;
    }

    function changePassword($password, $newPassword){
        $this->rest->api_key($this->token->token_type." ".$this->token->access_token, "Authorization");
        $this->last_response = $this->rest->post("auth/changePassword/", [
            "password" => $password,
            "newPassword" => $newPassword
        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function forgotPassword($email){
        $this->last_response = $this->rest->post("auth/forgotPassword/", [
            "email" => $email
        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function resetPassword($resetToken){
        $this->last_response = $this->rest->post("auth/resetPassword/", [

        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function logout(){
        $this->resetToken();
        $this->last_response = $this->rest->get("auth/logout/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }
}