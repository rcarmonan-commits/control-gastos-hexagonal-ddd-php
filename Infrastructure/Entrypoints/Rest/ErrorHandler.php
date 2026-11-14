<?php

declare(strict_types=1);

final class ErrorHandler
{
    /**
     * Traduce excepciones del Dominio y Aplicacion a codigos HTTP semanticos.
     *
     * @return array{status: int, body: array<string, mixed>}
     */
    public static function toHttpPayload(Throwable $throwable): array
    {
        $status = 500;
        $code = 'INTERNAL_SERVER_ERROR';

        if (
            $throwable instanceof UserNotFoundException ||
            $throwable instanceof GastoNotFoundException
        ) {
            $status = 404;
            $code = 'RESOURCE_NOT_FOUND';
        } elseif ($throwable instanceof UserAlreadyExistsException) {
            $status = 409;
            $code = 'RESOURCE_CONFLICT';
        } elseif ($throwable instanceof InvalidUserPasswordException && str_contains($throwable->getMessage(), 'credenciales')) {
            $status = 401;
            $code = 'UNAUTHORIZED';
        } elseif ($throwable instanceof InvalidArgumentException || $throwable instanceof DomainException) {
            $status = 422;
            $code = 'DOMAIN_VALIDATION_ERROR';
        }

        return array(
            'status' => $status,
            'body' => array(
                'success' => false,
                'error' => array(
                    'code' => $code,
                    'type' => get_class($throwable),
                    'message' => $throwable->getMessage(),
                ),
            ),
        );
    }

    public static function sendJsonError(Throwable $throwable): void
    {
        $mapped = self::toHttpPayload($throwable);
        if (!headers_sent()) {
            http_response_code($mapped['status']);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($mapped['body'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
