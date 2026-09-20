<?php

declare(strict_types=1);

class InvalidGastoFechaException extends InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('La fecha del gasto no puede estar vacia.');
    }

    public static function becauseFormatIsInvalid(string $fecha): self
    {
        return new self('La fecha del gasto debe tener formato valido YYYY-MM-DD: ' . $fecha);
    }
}
