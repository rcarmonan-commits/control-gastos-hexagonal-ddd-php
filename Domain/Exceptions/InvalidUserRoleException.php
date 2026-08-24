<?php

declare(strict_types=1);

class InvalidUserRoleException extends InvalidArgumentException
{
    public static function becauseValueIsInvalid(string $role): self
    {
        return new self('El rol de usuario proporcionado no es valido: ' . $role);
    }
}
