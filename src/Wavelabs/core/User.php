<?php
namespace Wavelabs\core;

use Wavelabs\core\ApiBase;

class User extends ApiBase{

    function __construct(){
        parent::__construct();
    }

    function update($userData){
        return $this->rest->put("users/".$userData['id']."/", $userData);
    }

    function updateProfileImage($file_field_name, $profileData){
        if(!empty($_FILES[$file_field_name])){
            $profileData["file"] = '@' . $_FILES[$file_field_name]['tmp_name'].';filename=' . $_FILES[$file_field_name]['name']. ';type='.$_FILES[$file_field_name]['type'];
        }
        return $this->rest->post("media/", $profileData, "form-data");
    }

    function getProfileImage($profile_id, $media_type = "original"){
        $response = $this->rest->get("media/", [
            "id" => $profile_id,
            "mediafor" => "profile"
        ]);
        if(!empty($response->mediaFileDetailsList)){
            foreach($response->mediaFileDetailsList as $media){
                if($media->mediatype == $media_type){
                    return $media->mediapath;
                }
            }
        }
        return false;
    }


}