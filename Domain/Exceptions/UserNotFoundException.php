<?php

declare(strict_types=1);

class UserNotFoundException extends DomainException
{
    public static function becauseIdWasNotFound(string $id): self
    {
        return new self('No se encontro ningun usuario con el identificador: ' . $id);
    }

    public static function becauseEmailWasNotFound(string $email): self
    {
        return new self('No se encontro ningun usuario con el correo: ' . $email);
    }
}
