<?php
namespace NBOS\core;

use NBOS\core\ApiBase;

class Auth extends ApiBase
{

    function __construct()
    {
        parent::__construct();
    }

    function signup($userData)
    {
        $this->setClientTokenHeader();
        $userData['clientId'] = $this->clientId;
        $this->last_response = $this->apiCall("post", API_HOST_URL . "api/identity/v0/users/signup", $userData);
        if (!empty($this->last_response->token)) {
            $this->setToken($this->last_response->token);
        }
        return $this->last_response;
    }

    function login($username, $password)
    {
        $this->setClientTokenHeader();
        $this->last_response = $this->apiCall("post", API_HOST_URL . "api/identity/v0/auth/login/", [
            "clientId" => $this->clientId,
            "username" => $username,
            "password" => $password
        ]);
        if (!empty($this->last_response->token)) {
            $this->setToken($this->last_response->token);
        }
        return $this->last_response;
    }

    function changePassword($password, $newPassword)
    {
        $this->rest->api_key($this->token->token_type . " " . $this->token->access_token, "Authorization");
        $this->last_response = $this->rest->post(API_HOST_URL . "api/identity/v0/auth/changePassword/", [
            "password" => $password,
            "newPassword" => $newPassword
        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function forgotPassword($email)
    {
        $this->setClientTokenHeader();
        $this->last_response = $this->rest->post(API_HOST_URL . "api/identity/v0/auth/forgotPassword/", [
            "email" => $email
        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function resetPassword($resetToken)
    {
        $this->last_response = $this->rest->post(API_HOST_URL . "api/identity/v0/auth/resetPassword/", [

        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function logout()
    {
        $response = $this->apiCall("get", API_HOST_URL . "api/identity/v0/auth/logout/");
        $this->resetToken();
        $this->resetClientToken();
        $_SESSION = [];
        return $response;
    }
}