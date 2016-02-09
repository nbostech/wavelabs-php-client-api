<?php
namespace Wavelabs\core;

use Wavelabs\core\ApiBase;

class Auth extends ApiBase{

    function __construct(){
        parent::__construct();
    }

    function signup($userData){
        $userData['clientId'] = $this->clientId;
        return $this->rest->post("users/signup/", $userData);
    }

    function login($username, $password){
        $this->last_response = $this->rest->post("auth/login/", [
            "clientId" => $this->clientId,
            "username" => $username,
            "password" => $password
        ]);
        $this->http_code = $this->rest->getLastHttpCode();
        if(!empty($this->last_response->token)){
            $this->setToken($this->last_response->token);
        }
        return $this->last_response;
    }

    function changePassword($password, $newPassword){
        $this->rest->api_key($this->token->token_type." ".$this->token->access_token, "Authorization");
        return $this->rest->post("auth/changePassword/", [
            "password" => $password,
            "newPassword" => $newPassword
        ]);
    }

    function forgotPassword($email){
        return $this->rest->post("auth/forgotPassword/", [
            "email" => $email
        ]);
    }

    function resetPassword($resetToken){
        return $this->rest->post("auth/resetPassword/", [

        ]);
    }

    function logout(){
        $this->resetToken();
        return $this->rest->get("auth/logout/");
    }
}