<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../Common/DependencyInjection.php';
require_once __DIR__ . '/../UserController.php';
require_once __DIR__ . '/../GastoController.php';
require_once __DIR__ . '/../AuthController.php';

final class WebRoutes
{
    public static function dispatch(string $action): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        $di = DependencyInjection::getInstance();
        $userController = new UserController($di);
        $gastoController = new GastoController($di);
        $authController = new AuthController($di);
        $presentationDir = dirname(__DIR__, 2) . '/Presentation';

        switch ($action) {
            case 'login':
                require $presentationDir . '/Auth/login.php';
                return;

            case 'logout':
                $_SESSION = array();
                if (!headers_sent()) {
                    header('Location: ?action=login');
                }
                return;

            case 'users.list':
                require $presentationDir . '/Users/listar.php';
                return;

            case 'users.create':
                require $presentationDir . '/Users/agregar.php';
                return;

            case 'users.edit':
                require $presentationDir . '/Users/editar.php';
                return;

            case 'users.delete':
                require $presentationDir . '/Users/eliminar.php';
                return;

            case 'users.search':
                require $presentationDir . '/Users/buscar.php';
                return;

            case 'gastos.create':
                require $presentationDir . '/Gastos/agregar.php';
                return;

            case 'gastos.edit':
                require $presentationDir . '/Gastos/editar.php';
                return;

            case 'gastos.delete':
                require $presentationDir . '/Gastos/eliminar.php';
                return;

            case 'gastos.search':
                require $presentationDir . '/Gastos/buscar.php';
                return;

            case 'gastos.list':
            default:
                require $presentationDir . '/Gastos/listar.php';
                return;
        }
    }
}
