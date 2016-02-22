<?php
namespace Wavelabs\core;

use Wavelabs\core\ApiBase;

class Sample extends ApiBase{

    function __construct(){
        parent::__construct();
    }

    function about(){
        $this->last_response = $this->rest->get(API_BASE_URL . "api/v0/sample/about/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }


}