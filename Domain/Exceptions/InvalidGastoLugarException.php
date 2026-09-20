<?php

declare(strict_types=1);

class InvalidGastoLugarException extends InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El lugar donde se realizo el gasto no puede estar vacio.');
    }

    public static function becauseLengthIsInvalid(int $min, int $max): self
    {
        return new self(sprintf('El lugar del gasto debe tener entre %d y %d caracteres.', $min, $max));
    }
}
