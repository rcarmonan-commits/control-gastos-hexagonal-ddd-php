<?php

declare(strict_types=1);

class InvalidGastoIdException extends InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El identificador del gasto no puede estar vacio.');
    }
}
