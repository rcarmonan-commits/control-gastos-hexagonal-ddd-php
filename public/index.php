<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/Common/DependencyInjection.php';
require_once dirname(__DIR__) . '/Infrastructure/Entrypoints/Web/Controllers/Config/WebRoutes.php';
require_once dirname(__DIR__) . '/Infrastructure/Entrypoints/Web/Controllers/AuthController.php';
require_once dirname(__DIR__) . '/Infrastructure/Entrypoints/Rest/UserRestController.php';
require_once dirname(__DIR__) . '/Infrastructure/Entrypoints/Rest/GastoRestController.php';
require_once dirname(__DIR__) . '/Infrastructure/Entrypoints/Rest/ErrorHandler.php';

$rawUri = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$scriptDir = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
$requestUri = ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($rawUri, $scriptDir))
    ? (substr($rawUri, strlen($scriptDir)) ?: '/')
    : $rawUri;
$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

// Servir assets estaticos de la capa de Presentacion
if (isset($_GET['asset'])) {
    $asset = (string) $_GET['asset'];
    $presentationPath = dirname(__DIR__) . '/Infrastructure/Entrypoints/Web/Presentation';
    if ($asset === 'css') {
        header('Content-Type: text/css; charset=utf-8');
        readfile($presentationPath . '/css/styles.css');
        exit;
    }
    if ($asset === 'js') {
        header('Content-Type: application/javascript; charset=utf-8');
        readfile($presentationPath . '/js/validaciones.js');
        exit;
    }
}

// Documentacion OpenAPI JSON y visor Swagger UI
if ($requestUri === '/openapi.json' || $requestUri === '/api/openapi.json') {
    header('Content-Type: application/json; charset=utf-8');
    readfile(dirname(__DIR__) . '/Infrastructure/Entrypoints/Rest/Swagger/openapi.json');
    exit;
}

if ($requestUri === '/swagger') {
    $specUrl = ($scriptDir !== '' && $scriptDir !== '/' ? $scriptDir : '') . '/openapi.json';
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Swagger UI — Usuarios y Gastos</title>';
    echo '<link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css"></head><body>';
    echo '<div id="swagger-ui"></div>';
    echo '<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>';
    echo '<script>window.onload=function(){SwaggerUIBundle({url:' . json_encode($specUrl) . ',dom_id:"#swagger-ui"});};</script>';
    echo '</body></html>';
    exit;
}

// Endpoints RESTful JSON (/api/*)
if (str_starts_with($requestUri, '/api/')) {
    header('Content-Type: application/json; charset=utf-8');
    $di = DependencyInjection::getInstance();

    if ($requestUri === '/api/health') {
        echo json_encode(array(
            'success' => true,
            'service' => 'usuarios-gastos-hexagonal-ddd-cqrs',
            'persistenceDriver' => $di->activePersistenceDriver(),
            'taxReference' => $di->taxRestClient()->fetchCurrentIvaReference(),
            'recentDomainEvents' => $di->eventPublisher()->recentEvents(5),
        ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $rawBody = file_get_contents('php://input');
    $decodedBody = is_string($rawBody) && trim($rawBody) !== '' ? json_decode($rawBody, true) : array();
    $payload = is_array($decodedBody) ? $decodedBody : $_POST;

    if ($requestUri === '/api/auth/login' && $method === 'POST') {
        try {
            $authController = new AuthController($di);
            $logged = $authController->login(
                (string) ($payload['email'] ?? ''),
                (string) ($payload['password'] ?? '')
            );
            http_response_code(200);
            echo json_encode(array(
                'success' => true,
                'message' => 'Autenticacion exitosa.',
                'data' => $logged->toArray(),
            ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (Throwable $e) {
            ErrorHandler::sendJsonError($e);
        }
        exit;
    }

    if (preg_match('#^/api/users(?:/([^/]+))?/?$#', $requestUri, $matches) === 1) {
        $controller = new UserRestController($di);
        $id = isset($matches[1]) ? urldecode($matches[1]) : null;
        $response = $controller->handle($method, $id, $_GET, $payload);
        http_response_code($response['status']);
        echo json_encode($response['body'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if (preg_match('#^/api/gastos(?:/([^/]+))?/?$#', $requestUri, $matches) === 1) {
        $controller = new GastoRestController($di);
        $id = isset($matches[1]) ? urldecode($matches[1]) : null;
        $response = $controller->handle($method, $id, $_GET, $payload);
        http_response_code($response['status']);
        echo json_encode($response['body'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    http_response_code(404);
    echo json_encode(array(
        'success' => false,
        'error' => array('code' => 'ENDPOINT_NOT_FOUND', 'message' => 'Ruta API no encontrada: ' . $requestUri),
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Entrypoint Web MVC / Presentacion HTML
$action = isset($_GET['action']) ? (string) $_GET['action'] : 'gastos.list';
WebRoutes::dispatch($action);
