<?php
namespace NBOS\core;

use NBOS\core\ApiBase;

class User extends ApiBase{

    function __construct(){
        parent::__construct();
    }

    function get($member_id){
        return $this->apiCall("get", API_HOST_URL . "api/identity/v0/users/".$member_id."/");
    }

    function update($userData){
        $this->last_response = $this->apiCall("put", API_HOST_URL . "api/identity/v0/users/".$userData['id']."/", $userData);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function updateProfileImage($profileData){
        return $this->apiCall("post", API_HOST_URL . "api/media/v0/media/", $profileData, "form-data");
    }

    function getProfileImage($profile_id, $media_type = "original"){
        $this->last_response = $this->apiCall("get", API_HOST_URL . "api/media/v0/media/", [
            "id" => $profile_id,
            "mediafor" => "profile"
        ]);
        $this->last_http_code = $this->rest->getLastHttpCode();
        if(!empty($this->last_response->mediaFileDetailsList)){
            foreach($this->last_response->mediaFileDetailsList as $media){
                if($media->mediatype == $media_type){
                    return $media->mediapath;
                }
            }
        }
        return false;
    }


}