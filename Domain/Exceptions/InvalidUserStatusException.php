<?php

declare(strict_types=1);

class InvalidUserStatusException extends InvalidArgumentException
{
    public static function becauseValueIsInvalid(string $status): self
    {
        return new self('El estado de usuario proporcionado no es valido: ' . $status);
    }
}
