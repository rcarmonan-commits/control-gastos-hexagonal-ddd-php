<?php

declare(strict_types=1);

require_once __DIR__ . '/../Exceptions/InvalidGastoDescripcionException.php';

final class GastoDescripcion
{
    private string $value;

    public function __construct(string $value)
    {
        $normalizedValue = preg_replace('/\s+/', ' ', trim($value));
        $normalizedValue = $normalizedValue !== null ? $normalizedValue : trim($value);

        if ($normalizedValue === '') {
            throw InvalidGastoDescripcionException::becauseValueIsEmpty();
        }

        $length = mb_strlen($normalizedValue);
        if ($length < 3 || $length > 500) {
            throw InvalidGastoDescripcionException::becauseLengthIsInvalid(3, 500);
        }

        $this->value = $normalizedValue;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(GastoDescripcion $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
