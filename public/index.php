<?php

use Peach\Controllers\AttemptsController;
use Peach\Controllers\DatabaseController;
use Peach\Handlers\AttemptsHandler;
use Peach\Handlers\MainPageHandler;
use Peach\Http\HttpHandler;
use Peach\Repositories\RequestRepository;
use Peach\Visualizers\CaptchaVisualizer;

require __DIR__ . "/../vendor/autoload.php";

$database_controller = new DatabaseController();

$parsedUrl = parse_url($_SERVER["REQUEST_URI"]);
$body = file_get_contents('php://input');
$request_repository = new RequestRepository($_SERVER["REQUEST_METHOD"], $parsedUrl["path"], "", $body);

$http_handler = new HttpHandler($request_repository);
$http_handler->registerStandardHandlers($database_controller);

$http_handler->execute();