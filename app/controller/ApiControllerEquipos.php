<?php
require_once 'app/view/JSONView.php';
require_once('app/model/modelEquipos.php');
require_once 'helpers/Helpers.php';

class ApiControllerEquipos
{
    private $model;
    private $data;
    private $view;
    private $authHelper;


    function __construct()
    {
        $this->model = new modelEquipos;
        $this->view = new JSONView();
        $this->authHelper = new Helpers();
    }

    function obtenerDatosEquipo()
    {
        // $data = new stdClass();
        // $data->equipo = $_POST['equipo'] ?? null;
        // $data->fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
        // $data->nombre = $_POST['nombre'] ?? null;
        // $data->apellido = $_POST['apellido'] ?? null;
        // $data->sueldo = $_POST['sueldo'] ?? null;
        // $data->posicion = $_POST['posicion'] ?? null;
        // $data->imagen = $_FILES['imagen']['name'] ?? null;
        // return $data;
        if (!empty($_POST) || !empty($_FILES)) {
            $data = $_POST;

            if (!empty($_FILES['imagen'])) {
                $data['imagen'] = $_FILES['imagen'];
            }

            return (object) $data;
        }
    }
    function obtenerNuevosDatos()
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
            $ruta = 'imagenes/equipos/' . $nombre;

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

    function getEquipos()
    {
        $user = $this->authHelper->currentUser();
        if ($user) {
            try {
                // Obtener todas las tareas del modelo

                $equipos = $this->model->all();

                if ($equipos) {
                    $response = [
                        "status" => 200,
                        "data" => $equipos
                    ];
                    // Si hay tareas, devolverlas con un código 200 (éxito)
                    $this->view->response($response, 200);
                } else
                    // Si no hay tareas, devolver un mensaje con un código 404 (no encontrado)
                    $this->view->response("No hay equipos en la base de datos", 404);
            } catch (Exception $e) {
                // En caso de error del servidor, devolver un mensaje con un código 500 (error del servidor)
                $this->view->response("Error de servidor", 500);
            }
        } else {
            $this->view->response("Sin autorizacion", 401);
        }
    }

    public function equipoPorId($params = null)
    {
        $user = $this->authHelper->currentUser();
        if ($user) {
            $id = $params[':ID'];

            try {
                // Obtiene una tarea del modelo
                $equipo = $this->model->findEquipoById($id);
                // Si existe la tarea, la retorna con un código 200 (éxito)
                if ($equipo) {
                    $response = [
                        "status" => 200,
                        "message" => $equipo
                    ];
                    $this->view->response($response, 200);
                } else {
                    $response = [
                        "status" => 404,
                        "message" => "No existe la equipo en la base de datos."
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

    public function addEquipo()
    {
        $user = $this->authHelper->currentUser();
        if ($user) {
            $equipoNuevo = $this->obtenerDatosEquipo();
            $imagen = $this->resolverImagen($equipoNuevo->imagen ?? null);

            //var_dump($equipoNuevo);die();

            $lastId = $this->model->saveEquipo(
                $equipoNuevo->nombre,
                $equipoNuevo->fundacion,
                $equipoNuevo->pais,
                $imagen
            );


            if ($lastId) {
                $this->view->response("El equipo se insertó correctamente con id: $lastId", 200);
            } else {
                $this->view->response("No se pudo insertar correctamente el jugador", 404);
            }
        } else {
            $this->view->response("Sin autorizacion", 401);
        }
    }

    public function updateEquipo($params = null)
    {
        $user = $this->authHelper->currentUser();
        if ($user) {
            $id = $params[':ID'];

            $equipoNuevo = $this->obtenerNuevosDatos();
            
            $imagen = $this->resolverImagen($equipoNuevo->imagen ?? null);

            //var_dump($equipoNuevo);die();

            $lastId = $this->model->updateEquipo(
                $id,
                $equipoNuevo->nombre,
                $equipoNuevo->fundacion,
                $equipoNuevo->pais,
                $imagen
            );


            if ($lastId) {
                $this->view->response("El equipo se edito correctamente con id: $id", 200);
            } else {
                $this->view->response("No se pudo editar correctamente el equipo", 404);
            }
        } else {
            $this->view->response("Sin autorizacion", 401);
        }
    }




    function eliminarEquipo($params = null)
    {
        $user = $this->authHelper->currentUser();
        if ($user) {
            $id = $params[':ID'];
            $equipo = $this->model->findEquipoById($id);
            if ($equipo) {
                $this->model->deleteEquipo($id);

                $this->view->response("Equipo $id, eliminada", 200);
            } else {
                $this->view->response("Equipo $id, no encontrada", 404);
            }
        } else {
            $this->view->response("Sin autorizacion", 401);
        }
    }
}
