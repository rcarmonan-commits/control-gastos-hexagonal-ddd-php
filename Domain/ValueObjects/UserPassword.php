<?php

declare(strict_types=1);

require_once __DIR__ . '/../Exceptions/InvalidUserPasswordException.php';

class UserPassword
{
    private string $value;

    public function __construct(string $value)
    {
        $normalizedValue = trim($value);

        if ($normalizedValue === '') {
            throw InvalidUserPasswordException::becauseValueIsEmpty();
        }

        if (strlen($normalizedValue) < 8) {
            throw InvalidUserPasswordException::becauseLengthIsTooShort(8);
        }

        $this->value = $normalizedValue;
    }

    public static function fromPlainText(string $plainPassword): self
    {
        $instance = new self($plainPassword);
        if (!self::isAlreadyHashed($instance->value)) {
            return new self(password_hash($instance->value, PASSWORD_BCRYPT));
        }

        return $instance;
    }

    public static function isAlreadyHashed(string $value): bool
    {
        $info = password_get_info($value);
        return isset($info['algo']) && $info['algo'] !== null && $info['algo'] !== 0;
    }

    public function matches(string $plainCandidate): bool
    {
        if (self::isAlreadyHashed($this->value)) {
            return password_verify($plainCandidate, $this->value);
        }

        return hash_equals($this->value, trim($plainCandidate));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(UserPassword $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
