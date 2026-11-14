<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Common/DependencyInjection.php';
require_once __DIR__ . '/../Web/Controllers/UserController.php';
require_once __DIR__ . '/ErrorHandler.php';

final class UserRestController
{
    private UserController $userController;

    public function __construct(?DependencyInjection $di = null)
    {
        $this->userController = new UserController($di);
    }

    /**
     * @param array<string, mixed> $queryParams
     * @param array<string, mixed> $body
     * @return array{status: int, body: array<string, mixed>}
     */
    public function handle(string $method, ?string $id, array $queryParams = array(), array $body = array()): array
    {
        try {
            $upperMethod = strtoupper($method);

            if ($upperMethod === 'GET' && ($id === null || $id === '')) {
                $items = array_map(
                    static fn(UserResponse $r): array => $r->toArray(),
                    $this->userController->list($queryParams)
                );
                return array(
                    'status' => 200,
                    'body' => array('success' => true, 'count' => count($items), 'data' => $items),
                );
            }

            if ($upperMethod === 'GET' && $id !== null) {
                $user = $this->userController->getById($id);
                return array(
                    'status' => 200,
                    'body' => array('success' => true, 'data' => $user->toArray()),
                );
            }

            if ($upperMethod === 'POST') {
                $created = $this->userController->create($body);
                return array(
                    'status' => 201,
                    'body' => array('success' => true, 'message' => 'Usuario creado exitosamente.', 'data' => $created->toArray()),
                );
            }

            if (($upperMethod === 'PUT' || $upperMethod === 'PATCH') && $id !== null) {
                $body['id'] = $id;
                $updated = $this->userController->update($body);
                return array(
                    'status' => 200,
                    'body' => array('success' => true, 'message' => 'Usuario actualizado exitosamente.', 'data' => $updated->toArray()),
                );
            }

            if ($upperMethod === 'DELETE' && $id !== null) {
                $this->userController->delete($id);
                return array(
                    'status' => 200,
                    'body' => array('success' => true, 'message' => 'Usuario eliminado exitosamente.', 'id' => $id),
                );
            }

            return array(
                'status' => 405,
                'body' => array('success' => false, 'error' => array('code' => 'METHOD_NOT_ALLOWED', 'message' => 'Operacion HTTP no soportada.')),
            );
        } catch (Throwable $e) {
            return ErrorHandler::toHttpPayload($e);
        }
    }
}
