<?php

declare(strict_types=1);

require_once __DIR__ . '/../Exceptions/InvalidGastoFechaException.php';

final class GastoFecha
{
    private string $value;

    public function __construct(string $value)
    {
        $normalizedValue = trim($value);

        if ($normalizedValue === '') {
            throw InvalidGastoFechaException::becauseValueIsEmpty();
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalizedValue);
        $errors = DateTimeImmutable::getLastErrors();
        $hasErrors = is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);

        if ($parsed === false || $hasErrors || $parsed->format('Y-m-d') !== $normalizedValue) {
            throw InvalidGastoFechaException::becauseFormatIsInvalid($normalizedValue);
        }

        $this->value = $normalizedValue;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(GastoFecha $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
