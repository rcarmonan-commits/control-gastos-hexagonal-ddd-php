<?php

declare(strict_types=1);

require_once __DIR__ . '/../Exceptions/InvalidGastoMontoException.php';

final class GastoMonto
{
    private float $value;
    private int $cents;

    public function __construct(float|int|string $value, string $fieldName = 'monto')
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || !is_numeric($trimmed)) {
                throw InvalidGastoMontoException::becauseValueIsNotNumeric($value, $fieldName);
            }
            $numeric = (float) $trimmed;
        } else {
            $numeric = (float) $value;
        }

        if (!is_finite($numeric)) {
            throw InvalidGastoMontoException::becauseValueIsNotNumeric((string) $value, $fieldName);
        }

        if ($numeric < 0.0) {
            throw InvalidGastoMontoException::becauseValueIsNegative($numeric, $fieldName);
        }

        $this->cents = (int) round($numeric * 100.0);
        $this->value = round($this->cents / 100.0, 2);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function add(GastoMonto $other, string $fieldName = 'monto'): self
    {
        $totalCents = $this->cents + $other->cents();
        return new self($totalCents / 100.0, $fieldName);
    }

    public function equals(GastoMonto $other): bool
    {
        return $this->cents === $other->cents();
    }

    public function formatted(): string
    {
        return number_format($this->value, 2, '.', '');
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
