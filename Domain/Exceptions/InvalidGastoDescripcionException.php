<?php

declare(strict_types=1);

class InvalidGastoDescripcionException extends InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('La descripcion del gasto no puede estar vacia.');
    }

    public static function becauseLengthIsInvalid(int $min, int $max): self
    {
        return new self(sprintf('La descripcion del gasto debe tener entre %d y %d caracteres.', $min, $max));
    }
}
