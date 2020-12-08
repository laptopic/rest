<?php

namespace application\controllers;

use application\core\Controller;
use \Firebase\JWT\JWT;


class UserController extends Controller
{

    public function createAction(){
        $this->getHeaders();
        $data = json_decode(file_get_contents("php://input"), true);
        if($data = $this->validateDate($data)){
            $this->model->createUser($data);
            http_response_code(200);
            echo json_encode(array("message" => "User was created."));
        }else{
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create user."));
        }
    }

    public function loginAction(){
        $this->getHeaders();
        $data = json_decode(file_get_contents("php://input"), true);
        require 'application/config/core.php';

        $email = $data['email'];
        $row = $this->model->getUser($email);
        if($row && password_verify($data['password'], $row['password'])){

            $token = array(
                "iat" => $issued_at,
                "exp" => $expiration_time,
                "iss" => $issuer,
                "data" => $row
            );

            http_response_code(200);

            $jwt = JWT::encode($token, $key);
            echo json_encode(
                [
                    "message" => "Successful login.",
                    "jwt" => $jwt
                ]
            );

        }else{
            http_response_code(401);
            echo json_encode(array("message" => "Login failed."));
        }

    }

    public function validateDate($data){
        //полная валидация хороша в laravel или codeigniter проще перенести, тут я проверяю только два поля зря связался с чистым php))

        if(
            isset($data) &&
            !empty($data['fio']) &&
            !empty($data['email']) &&
            $this->validate_email($data['email']) &&
            !empty($data['password']) &&
            !empty($data['phone']) &&
            $this->validate_mobile($data['phone'])
        )
        {
            foreach($data as $key=>$value){
                $data[$key] = htmlspecialchars(strip_tags($value));
            }
            return $data;
        }else{
            return false;
        }
    }

    public function validate_mobile($phone)
    {
        return preg_match('/((8|\+7)-?)?\(?\d{3,5}\)?-?\d{1}-?\d{1}-?\d{1}-?\d{1}-?\d{1}((-?\d{1})?-?\d{1})?/', $phone);
    }

    public function validate_email($email)
    {
        return preg_match('/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}[0-9А-Яа-я]{1}))@([-0-9A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u', $email);
    }

    public function getHeaders(){
        header("Access-Control-Allow-Origin: http://localhost/rest-api-authentication-example/");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    }
}