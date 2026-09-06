<?php

declare(strict_types=1);

class InvalidUserNameException extends InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El nombre del usuario no puede estar vacio.');
    }

    public static function becauseLengthIsTooShort(int $minLength): self
    {
        return new self('El nombre del usuario debe contener al menos ' . $minLength . ' caracteres.');
    }

    public static function becauseLengthIsTooLong(int $maxLength): self
    {
        return new self('El nombre del usuario no puede superar los ' . $maxLength . ' caracteres.');
    }
}
