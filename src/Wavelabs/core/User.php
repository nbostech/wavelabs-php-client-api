<?php
namespace Wavelabs\core;

use Wavelabs\core\ApiBase;

class User extends ApiBase{

    function __construct(){
        parent::__construct();
    }

    function get($member_id){
        $this->last_response = $this->rest->get("users/".$member_id."/");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function update($userData){
        $this->last_response = $this->rest->put("users/".$userData['id']."/", $userData);
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function updateProfileImage($profileData){
        $this->last_response = $this->rest->post("media/", $profileData, "form-data");
        $this->last_http_code = $this->rest->getLastHttpCode();
        return $this->last_response;
    }

    function getProfileImage($profile_id, $media_type = "original"){
        $this->last_response = $this->rest->get("media/", [
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