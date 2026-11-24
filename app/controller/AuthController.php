<?php
require_once 'app/model/modelUsuarios.php';
require_once 'app/view/JSONView.php';
require_once 'helpers/Helpers.php';

class AuthController {


    private $model;  
    protected $authHelper;
    protected $view;   
    private $data;

    function __construct() {
        $this->authHelper = new Helpers();     
        $this->view = new JSONView();
        $this->data = file_get_contents("php://input");
        $this->model = new modelUsuarios(); 
    }

    function getData() {
        return json_decode($this->data);
    }
    

    function login() {       
        //PASO 1 obtenemos la autorizacion y la guardamos en una variable
        //PASO 2 al resultado del PASO 1 lo convertimos en arreglo explode(" ", $variable)
        //PASO 3 la variable[1] de ese arreglo de decodificamos usando base64_decode($variable[1]) y lo guardamos en otra variable;
        //PASO 4 aquí tenemos los datos del usuario

        //te devuelve el valor del encabezado de autorización HTTP
        $basic = $this->authHelper->getAuthHeaders(); // Darnos el header 'Authorization:' 'Basic: base64(usr:pass)'
        if(empty($basic)) {
            $this->view->response('No envió encabezados de autenticación.', 401);
            die();
        }
        //devuelve el valor del encabezado en la siguinte estructura "basic {valor codificado}"
        $basic = explode(" ", $basic);
        
        if($basic[0]!="Basic") {
            $this->view->response('Autenticación incorrecta.', 401);
            die();
        }
        
        $userpass = base64_decode($basic[1]); //base64_decode($basic[1]): se utiliza para decodificar datos codificados en base64.
        
        //$userpass tiene los datos del usuario de la siguiente forma webadmin:admin
        $userpass = explode(":", $userpass); // los separo con los "dos puntos"
        
        $name = $userpass[0];
        $pass = $userpass[1];
        $user = $this->model->existeUsuario($name);
        
        if($user && password_verify($pass, $user->password)){
            $userdata = [ "email" => $user->email, "role" => $user->role];
            $token = $this->authHelper->createToken($userdata);
            
            $response = [
                "status" => 200,
                "data" => $userdata,
                "token" => $token
            ];               
            $this->view->response($response, 200);
        }else{
            $response = [
                "status" => 404,
                "message" => "El usuario o contraseña son incorrectos."
            ];
            $this->view->response($response, 404);
        }
    }



}