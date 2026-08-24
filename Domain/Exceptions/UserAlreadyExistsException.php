<?php

declare(strict_types=1);

class UserAlreadyExistsException extends DomainException
{
    public static function becauseEmailAlreadyExists(string $email): self
    {
        return new self('Ya existe un usuario registrado con el correo: ' . $email);
    }

    public static function becauseIdAlreadyExists(string $id): self
    {
        return new self('Ya existe un usuario registrado con el identificador: ' . $id);
    }
}
