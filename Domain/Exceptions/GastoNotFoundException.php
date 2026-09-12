<?php

declare(strict_types=1);

class GastoNotFoundException extends DomainException
{
    public static function becauseIdWasNotFound(string $id): self
    {
        return new self('No se encontro ningun gasto registrado con el identificador: ' . $id);
    }
}
