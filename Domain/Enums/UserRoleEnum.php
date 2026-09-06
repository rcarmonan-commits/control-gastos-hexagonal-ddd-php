<?php

declare(strict_types=1);

require_once __DIR__ . '/../Exceptions/InvalidUserRoleException.php';

class UserRoleEnum
{
    public const ADMIN = 'ADMIN';
    public const MEMBER = 'MEMBER';
    public const REVIEWER = 'REVIEWER';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array(
            self::ADMIN,
            self::MEMBER,
            self::REVIEWER,
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
            throw InvalidUserRoleException::becauseValueIsInvalid($value);
        }
    }

    public static function toRoleId(string $role): int
    {
        $normalized = self::normalize($role);
        return match ($normalized) {
            self::ADMIN => 1,
            self::REVIEWER => 3,
            default => 2,
        };
    }
}
