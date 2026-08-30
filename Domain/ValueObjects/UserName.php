<?php

declare(strict_types=1);

require_once __DIR__ . '/../Exceptions/InvalidUserNameException.php';

class UserName
{
    private string $value;

    public function __construct(string $value)
    {
        $normalizedValue = preg_replace('/\s+/', ' ', trim($value));
        $normalizedValue = $normalizedValue !== null ? $normalizedValue : trim($value);

        if ($normalizedValue === '') {
            throw InvalidUserNameException::becauseValueIsEmpty();
        }

        if (mb_strlen($normalizedValue) < 3) {
            throw InvalidUserNameException::becauseLengthIsTooShort(3);
        }

        if (mb_strlen($normalizedValue) > 100) {
            throw InvalidUserNameException::becauseLengthIsTooLong(100);
        }

        $this->value = $normalizedValue;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(UserName $other): bool
    {
        return mb_strtolower($this->value) === mb_strtolower($other->value());
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
