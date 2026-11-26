<?php
require_once 'app/view/JSONView.php';
require_once 'app/model/modelJugadores.php';
require_once 'helpers/Helpers.php';

class ApiControllerJugadores
{

    private $view;
    private $data;
    private $model;
    private $authHelper;
    public function __construct()
    {
        $this->model = new modelJugadores();
        $this->view = new JSONView();
        $this->authHelper = new Helpers();
        // $this->data = file_get_contents("php://input");
    }

    function obtenerDatosJugador()
    {
        if (!empty($_POST) || !empty($_FILES)) {
            $data = $_POST;
            if (!empty($_FILES['imagen'])) {
                $data['imagen'] = $_FILES['imagen'];
            }
            return (object) $data;
        }
    }
    function obtenerNuevosDatosJugador()
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body);
        return $data;
    }

    public function resolverImagen($imagen)
    {
        // Si llega como archivo desde form-data ($_FILES['imagen'])
        if (is_array($imagen) && !empty($imagen['tmp_name'])) {
            $nombre = uniqid() . '_' . basename($imagen['name']);
            $ruta = 'imagenes/jugadores/' . $nombre;

            if (move_uploaded_file($imagen['tmp_name'], $ruta)) {
                return $ruta; // string
            }

            return null;
        }

        // Si llega como texto/URL (desde JSON raw o campo Text en Postman)
        if (is_string($imagen) && $imagen !== '') {
            return $imagen;
        }

        return null;
    }

    public function getJugadores()
    { //debo poner el token  
        $user = $this->authHelper->currentUser();
        if ($user) {

            try {
                // Obtener todas las tareas del modelo

                $jugadores = $this->model->all();

                if ($jugadores) {
                    $response = [
                        "status" => 200,
                        "data" => $jugadores
                    ];
                    // Si hay tareas, devolverlas con un código 200 (éxito)
                    $this->view->response($response, 200);
                } else
                    // Si no hay tareas, devolver un mensaje con un código 404 (no encontrado)
                    $this->view->response("No hay jugadores en la base de datos", 404);
            } catch (Exception $e) {
                // En caso de error del servidor, devolver un mensaje con un código 500 (error del servidor)
                $this->view->response("Error de servidor", 500);
            }
        } else {
            $this->view->response("Sin autorizacion", 401);
        }
    }

    public function jugadorPorId($params = null)
    {
        $user = $this->authHelper->currentUser();
        if ($user) {
            $id = $params[':ID'];

            try {
                // Obtiene una tarea del modelo
                $jugador = $this->model->findJugadorById($id);
                // Si existe la tarea, la retorna con un código 200 (éxito)
                if ($jugador) {
                    $response = [
                        "status" => 200,
                        "message" => $jugador
                    ];
                    $this->view->response($response, 200);
                } else {
                    $response = [
                        "status" => 404,
                        "message" => "No existe la jugador en la base de datos."
                    ];
                    $this->view->response($response, 404);
                }
            } catch (Exception $e) {
                // En caso de error del servidor, devolver un mensaje con un código 500 (error del servidor)
                $this->view->response("Error de servidor", 500);
            }
        } else {
            $this->view->response("Sin autorizacion", 401);
        }
    }



    public function addJugador()
    {
        $user = $this->authHelper->currentUser();
        if ($user) {
            $jugadorNuevo = $this->obtenerDatosJugador();
            $imagen = $this->resolverImagen($jugadorNuevo->imagen ?? null);


            $lastId = $this->model->saveJugador(
                $jugadorNuevo->equipo,
                $jugadorNuevo->fecha_nacimiento,
                $jugadorNuevo->nombre,
                $jugadorNuevo->apellido,
                $jugadorNuevo->sueldo,
                $jugadorNuevo->posicion,
                $imagen
            );

            if ($lastId) {
                $this->view->response("El jugador se insertó correctamente con id: $lastId", 201);
            } else {
                $this->view->response("No se pudo insertar correctamente el jugador", 404);
            }
        } else {
            $this->view->response("Sin autorizacion", 401);
        }
    }

    public function updateJugador($params = null)
    {
        $user = $this->authHelper->currentUser();
        if ($user) {
            $id = $params[':ID'];

            $JugadorNuevo = $this->obtenerNuevosDatosJugador();

            $imagen = $this->resolverImagen($JugadorNuevo->imagen ?? null);

            //var_dump($JugadorNuevo);die();

            $lastId = $this->model->updateJugador(
                $JugadorNuevo->id_equipo,
                $JugadorNuevo->fecha_nacimiento,
                $JugadorNuevo->nombre,
                $JugadorNuevo->apellido,
                $JugadorNuevo->sueldo,
                $JugadorNuevo->posicion,
                $imagen,
                $id
            );
            if ($id) {
                $this->view->response("El jugador se edito correctamente con id: $id", 200);
            } else {
                $this->view->response("No se pudo editar correctamente el jugador", 404);
            }
        } else {
            $this->view->response("Sin autorizacion", 401);
        }
    }


   function filtrarJugadores()
    {
        $user = $this->authHelper->currentUser();
        if ($user) {


            try {
                // Obtiene una tarea del modelo
                $nombre = isset($_GET['nombre']) ? $_GET['nombre'] : null;
                $orden = isset($_GET['orden']) ? $_GET['orden'] : 'ASC';
                $jugadores = $this->model->filtro($nombre, $orden);

                // Si existe la tarea, la retorna con un código 200 (éxito)
                if ($jugadores) {
                    $response = [
                        "status" => 200,
                        "message" => $jugadores
                    ];
                    $this->view->response($response, 200);
                } else {
                    $response = [
                        "status" => 404,
                        "message" => "No existe la jugadores en la base de datos."
                    ];
                    $this->view->response($response, 404);
                }
            } catch (Exception $e) {
                // En caso de error del servidor, devolver un mensaje con un código 500 (error del servidor)
                $this->view->response("Error de servidor", 500);
            }
        } else {
            $this->view->response("Sin autorizacion", 401);
        }
    }


    public function eliminarJugador($params = null)
    {
        $user = $this->authHelper->currentUser();
        if ($user) {
            $id = $params[':ID'];
            $Jugador = $this->model->findJugadorById($id);
            if ($Jugador) {
                $this->model->deleteJugador($id);

                $this->view->response("Jugador $id, eliminada", 200);
            } else {
                $this->view->response("Jugador $id, no encontrada", 404);
            }
        } else {
            $this->view->response("Sin autorizacion", 401);
        }
    }
}
