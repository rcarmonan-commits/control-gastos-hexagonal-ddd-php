<?php

declare(strict_types=1);

require_once __DIR__ . '/../Exceptions/InvalidUserStatusException.php';

class UserStatusEnum
{
    public const ACTIVE = 'ACTIVE';
    public const INACTIVE = 'INACTIVE';
    public const PENDING = 'PENDING';
    public const BLOCKED = 'BLOCKED';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array(
            self::ACTIVE,
            self::INACTIVE,
            self::PENDING,
            self::BLOCKED,
        );
    }

    public static function normalize(string $value): string
    {
        return strtoupper(trim($value));
    }

    public static function isValid(string $value): bool
    {
        return in_array(self::normalize($value), self::values(), true);
    }

    public static function ensureIsValid(string $value): void
    {
        if (!self::isValid($value)) {
            throw InvalidUserStatusException::becauseValueIsInvalid($value);
        }
    }
}
