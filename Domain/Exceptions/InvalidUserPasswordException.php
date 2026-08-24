<?php

declare(strict_types=1);

class InvalidUserPasswordException extends InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('La clave del usuario no puede estar vacia.');
    }

    public static function becauseLengthIsTooShort(int $minLength): self
    {
        return new self('La clave del usuario debe contener al menos ' . $minLength . ' caracteres.');
    }

    public static function becauseCredentialsAreInvalid(): self
    {
        return new self('Las credenciales de acceso proporcionadas no son validas.');
    }
}
