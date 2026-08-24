<?php

declare(strict_types=1);

class InvalidUserEmailException extends InvalidArgumentException
{
    public static function becauseFormatIsInvalid(string $email): self
    {
        return new self('El formato del correo electronico del usuario es invalido: ' . $email);
    }

    public static function becauseValueIsEmpty(): self
    {
        return new self('El correo electronico del usuario no puede estar vacio.');
    }
}
