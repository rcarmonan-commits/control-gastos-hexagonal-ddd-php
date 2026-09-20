<?php

declare(strict_types=1);

require_once __DIR__ . '/../Exceptions/InvalidGastoIdException.php';

final class GastoId
{
    private string $value;

    public function __construct(string $value)
    {
        $normalizedValue = trim($value);

        if ($normalizedValue === '') {
            throw InvalidGastoIdException::becauseValueIsEmpty();
        }

        $this->value = $normalizedValue;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(GastoId $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
