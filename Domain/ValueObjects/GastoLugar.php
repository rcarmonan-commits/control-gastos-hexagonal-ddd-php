<?php

declare(strict_types=1);

require_once __DIR__ . '/../Exceptions/InvalidGastoLugarException.php';

final class GastoLugar
{
    private string $value;

    public function __construct(string $value)
    {
        $normalizedValue = preg_replace('/\s+/', ' ', trim($value));
        $normalizedValue = $normalizedValue !== null ? $normalizedValue : trim($value);

        if ($normalizedValue === '') {
            throw InvalidGastoLugarException::becauseValueIsEmpty();
        }

        $length = mb_strlen($normalizedValue);
        if ($length < 2 || $length > 150) {
            throw InvalidGastoLugarException::becauseLengthIsInvalid(2, 150);
        }

        $this->value = $normalizedValue;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(GastoLugar $other): bool
    {
        return mb_strtolower($this->value) === mb_strtolower($other->value());
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
