<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Common/DependencyInjection.php';
require_once __DIR__ . '/../Web/Controllers/GastoController.php';
require_once __DIR__ . '/ErrorHandler.php';

final class GastoRestController
{
    private GastoController $gastoController;

    public function __construct(?DependencyInjection $di = null)
    {
        $this->gastoController = new GastoController($di);
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
                    static fn(GastoResponse $r): array => $r->toArray(),
                    $this->gastoController->list($queryParams)
                );
                return array(
                    'status' => 200,
                    'body' => array('success' => true, 'count' => count($items), 'data' => $items),
                );
            }

            if ($upperMethod === 'GET' && $id !== null) {
                $gasto = $this->gastoController->getById($id);
                return array(
                    'status' => 200,
                    'body' => array('success' => true, 'data' => $gasto->toArray()),
                );
            }

            if ($upperMethod === 'POST') {
                $created = $this->gastoController->create($body);
                return array(
                    'status' => 201,
                    'body' => array('success' => true, 'message' => 'Gasto registrado exitosamente.', 'data' => $created->toArray()),
                );
            }

            if (($upperMethod === 'PUT' || $upperMethod === 'PATCH') && $id !== null) {
                $body['id'] = $id;
                $updated = $this->gastoController->update($body);
                return array(
                    'status' => 200,
                    'body' => array('success' => true, 'message' => 'Gasto actualizado exitosamente.', 'data' => $updated->toArray()),
                );
            }

            if ($upperMethod === 'DELETE' && $id !== null) {
                $this->gastoController->delete($id);
                return array(
                    'status' => 200,
                    'body' => array('success' => true, 'message' => 'Gasto eliminado exitosamente.', 'id' => $id),
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
