<?php
require_once('libs/Router.php');
require_once('app/controller/ApiControllerEquipos.php');
require_once('app/controller/ApiControllerJugadores.php');
require_once('app/controller/AuthController.php');


$router = new Router();

// GET http://localhost/api/noticias 
$router->addRoute('jugadores', 'GET', 'ApiControllerJugadores', 'getJugadores');
$router->addRoute('equipos', 'GET', 'ApiControllerEquipos', 'getEquipos');
$router->addRoute('jugadores/:ID', 'GET', 'ApiControllerJugadores', 'jugadorPorId');
$router->addRoute('equipos/:ID', 'GET', 'ApiControllerEquipos', 'equipoPorId');

$router->addRoute('jugadores', 'POST', 'ApiControllerJugadores', 'addJugador');
$router->addRoute('equipos', 'POST', 'ApiControllerEquipos', 'addEquipo');

$router->addRoute('jugadores/:ID', 'DELETE', 'ApiControllerJugadores', 'eliminarJugador');
$router->addRoute('equipos/:ID', 'DELETE', 'ApiControllerEquipos', 'eliminarEquipo');

$router->addRoute('jugadores/:ID', 'PUT', 'ApiControllerJugadores', 'updateJugador');
$router->addRoute('equipos/:ID', 'PUT', 'ApiControllerEquipos', 'updateEquipo');

//auth user
$router->addRoute('auth', 'POST', 'AuthController', 'login');



$router->route($_GET["resource"], $_SERVER['REQUEST_METHOD']);
