
# Wavelabs PHP client App

Wavelabs PHP client App is the first easy, secure user management and authentication service for developers. This is the PHP Client App to easy integration of its features with any PHP language based application.


# Installation

You can install wavelabs-php-client-api via composer

## Via Composer

 wavelabs-php-client-api is available on packagist as the "wavelabs/php-client-api" package
 
 On your project root, install Composer
 ```php
   curl -sS https://getcomposer.org/installer | php
 ```
Configure the wavelabs-php-client-api dependency in your 'composer.json' file:
 ```json
 "require": {
     "wavelabs/php-client-api": "*"
 }
 ```
On your project root, install the the wavelabs-php-client-api with its dependencies:
 ```php
 php composer.phar install
 ```
You're ready to connect with wavelabs-php-client-api!
 
## Via github
 
 You can <a href="https://github.com/nbostech/wavelabs-php-client-api">download</a> from Github 
 
# Getting Started

 Include the wavelabs-php-client-api via composer autoloder 
 ```php
 inclide 'vendor/autoload.php';
 ```
 Or Include the wavelabs-php-client-api when downloaded from github 
 ```php
 include 'wavelabs-php-client-api-master/src/Wavelabs/Autoloader.php';
 ```

##Samples

1.  **User Registration** 
 ```php
 // Include autoloader
 require "vendor/autoload.php";
 
 // calling login service
 $response = $auth->signup([
     "username" => "demouser",
     "password" => "demopass",
     "email" => "demo@gmail.com",
     "firstName" => "first name",
     "lastName" => "last name"
 ]);
 // get service HTTP status code
 $http_code = $auth->getLastHttpCode();
 
 // if HTTP status is OK
 if($http_code == 200){
     // message from server
     echo "Token :".$response->token->access_token;
     echo "Member ID :".$response->member->id;
     echo "Email :".$response->member->email;
     echo "First Name :".$response->member->firstName;
 }else {
     //get Errors
     print_r(\Wavelabs\core\ApiBase::getErrors());
 }
 ```

2.  **User Login**
 ```php
 // Include autoloader
 require "vendor/autoload.php";
 
 $auth = new Wavelabs\core\Auth();
 
 // calling login service
 $response = $auth->login("sastrylal", "Admin@123");
 // get service HTTP status code
 $http_code = $auth->getLastHttpCode();
 
 // if HTTP status is OK
 if($http_code == 200){
     echo "Welcome to ". $response->member->firstName;
 }else {
     //get Errors 
     print_r(\Wavelabs\core\ApiBase::getErrors());
 }
 ```


