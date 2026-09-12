<?php

declare(strict_types=1);

class InvalidGastoMontoException extends InvalidArgumentException
{
    public static function becauseValueIsNegative(float $monto, string $campo): self
    {
        return new self(sprintf('El campo monetario "%s" no puede ser negativo (recibido: %.2f).', $campo, $monto));
    }

    public static function becauseValueIsNotNumeric(string $raw, string $campo): self
    {
        return new self(sprintf('El campo monetario "%s" debe ser un numero valido (recibido: %s).', $campo, $raw));
    }

    public static function becauseTotalConIvaIsInconsistent(
        float $sinIva,
        float $iva,
        float $conIvaRecibido
    ): self {
        $esperado = round($sinIva + $iva, 2);
        return new self(
            sprintf(
                'Invariante contable violada: valorTotalSinIVA (%.2f) + ivaTotal (%.2f) = %.2f, pero se recibio valorTotalConIVA = %.2f.',
                $sinIva,
                $iva,
                $esperado,
                $conIvaRecibido
            )
        );
    }
}
