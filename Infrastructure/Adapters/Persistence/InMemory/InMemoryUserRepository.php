<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../Application/Ports/Out/SaveUserPort.php';
require_once __DIR__ . '/../../../../Application/Ports/Out/GetUserByIdPort.php';
require_once __DIR__ . '/../../../../Application/Ports/Out/GetUserByEmailPort.php';
require_once __DIR__ . '/../../../../Application/Ports/Out/GetAllUsersPort.php';
require_once __DIR__ . '/../../../../Application/Ports/Out/UpdateUserPort.php';
require_once __DIR__ . '/../../../../Application/Ports/Out/DeleteUserPort.php';
require_once __DIR__ . '/../../../../Domain/Enums/UserRoleEnum.php';
require_once __DIR__ . '/../../../../Domain/Enums/UserStatusEnum.php';

final class InMemoryUserRepository implements
    SaveUserPort,
    GetUserByIdPort,
    GetUserByEmailPort,
    GetAllUsersPort,
    UpdateUserPort,
    DeleteUserPort
{
    /** @var array<string, UserModel> */
    private array $items = array();

    private ?string $storageFile;

    public function __construct(?string $storageFile = null, bool $seedDefaults = true)
    {
        $this->storageFile = $storageFile;

        if ($this->storageFile !== null && is_file($this->storageFile)) {
            $this->loadFromDisk();
        } elseif ($seedDefaults) {
            $this->seed();
            $this->flushToDisk();
        }
    }

    public function save(UserModel $user): UserModel
    {
        $this->items[$user->id()->value()] = $user;
        $this->flushToDisk();
        return $user;
    }

    public function getById(UserId $userId): ?UserModel
    {
        return $this->items[$userId->value()] ?? null;
    }

    public function getByEmail(UserEmail $email): ?UserModel
    {
        foreach ($this->items as $user) {
            if ($user->email()->equals($email)) {
                return $user;
            }
        }
        return null;
    }

    public function getAll(?string $search = null): array
    {
        $values = array_values($this->items);
        if ($search === null || trim($search) === '') {
            return $values;
        }

        $needle = mb_strtolower(trim($search));
        $filtered = array();

        foreach ($values as $user) {
            $haystack = mb_strtolower(
                $user->id()->value() . ' ' .
                $user->name()->value() . ' ' .
                $user->email()->value() . ' ' .
                $user->role()
            );

            if (str_contains($haystack, $needle)) {
                $filtered[] = $user;
            }
        }

        return $filtered;
    }

    public function update(UserModel $user): UserModel
    {
        $this->items[$user->id()->value()] = $user;
        $this->flushToDisk();
        return $user;
    }

    public function delete(UserId $userId): void
    {
        unset($this->items[$userId->value()]);
        $this->flushToDisk();
    }

    private function seed(): void
    {
        $admin = new UserModel(
            new UserId('USR-001'),
            new UserName('Administrador'),
            new UserEmail('admin@crud.com'),
            UserPassword::fromPlainText('Developer123'),
            UserRoleEnum::ADMIN,
            UserStatusEnum::ACTIVE
        );

        $student = new UserModel(
            new UserId('USR-002'),
            new UserName('Rosary Carmona'),
            new UserEmail('rcarmonan@unicartagena.edu.co'),
            UserPassword::fromPlainText('Carmona2026*'),
            UserRoleEnum::MEMBER,
            UserStatusEnum::ACTIVE
        );

        $this->items[$admin->id()->value()] = $admin;
        $this->items[$student->id()->value()] = $student;
    }

    private function loadFromDisk(): void
    {
        if ($this->storageFile === null || !is_file($this->storageFile)) {
            return;
        }

        $raw = @file_get_contents($this->storageFile);
        if ($raw === false) {
            return;
        }

        $rows = json_decode($raw, true);
        if (!is_array($rows)) {
            return;
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $user = new UserModel(
                new UserId((string) ($row['id'] ?? '')),
                new UserName((string) ($row['name'] ?? '')),
                new UserEmail((string) ($row['email'] ?? '')),
                new UserPassword((string) ($row['password'] ?? '')),
                (string) ($row['role'] ?? UserRoleEnum::MEMBER),
                (string) ($row['status'] ?? UserStatusEnum::ACTIVE)
            );
            $this->items[$user->id()->value()] = $user;
        }
    }

    private function flushToDisk(): void
    {
        if ($this->storageFile === null) {
            return;
        }

        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $payload = array();
        foreach ($this->items as $user) {
            $payload[] = $user->toArray();
        }

        @file_put_contents($this->storageFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
}
