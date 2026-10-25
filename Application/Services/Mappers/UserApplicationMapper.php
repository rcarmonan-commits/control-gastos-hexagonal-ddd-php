<?php

declare(strict_types=1);

require_once __DIR__ . '/../Dto/Commands/CreateUserCommand.php';
require_once __DIR__ . '/../Dto/Commands/UpdateUserCommand.php';
require_once __DIR__ . '/../Dto/Commands/DeleteUserCommand.php';
require_once __DIR__ . '/../Dto/Queries/GetUserByIdQuery.php';
require_once __DIR__ . '/../Dto/Responses/UserApplicationResponse.php';

require_once __DIR__ . '/../../../Domain/Models/UserModel.php';
require_once __DIR__ . '/../../../Domain/ValueObjects/UserId.php';
require_once __DIR__ . '/../../../Domain/ValueObjects/UserName.php';
require_once __DIR__ . '/../../../Domain/ValueObjects/UserEmail.php';
require_once __DIR__ . '/../../../Domain/ValueObjects/UserPassword.php';
require_once __DIR__ . '/../../../Domain/Enums/UserRoleEnum.php';
require_once __DIR__ . '/../../../Domain/Enums/UserStatusEnum.php';

final class UserApplicationMapper
{
    public static function fromCreateCommandToModel(CreateUserCommand $command, ?string $generatedId = null): UserModel
    {
        $resolvedId = $command->getId() !== '' ? $command->getId() : ($generatedId ?? self::generateId());

        return new UserModel(
            new UserId($resolvedId),
            new UserName($command->getName()),
            new UserEmail($command->getEmail()),
            UserPassword::fromPlainText($command->getPassword()),
            $command->getRole(),
            UserStatusEnum::ACTIVE
        );
    }

    public static function fromUpdateCommandToModel(UpdateUserCommand $command, UserPassword $currentPassword): UserModel
    {
        $resolvedPassword = $command->getPassword() !== null
            ? UserPassword::fromPlainText($command->getPassword())
            : $currentPassword;

        return new UserModel(
            new UserId($command->getId()),
            new UserName($command->getName()),
            new UserEmail($command->getEmail()),
            $resolvedPassword,
            $command->getRole(),
            $command->getStatus()
        );
    }

    public static function fromGetUserByIdQueryToUserId(GetUserByIdQuery $query): UserId
    {
        return new UserId($query->getId());
    }

    public static function fromDeleteCommandToUserId(DeleteUserCommand $command): UserId
    {
        return new UserId($command->getId());
    }

    public static function fromModelToResponse(UserModel $user): UserApplicationResponse
    {
        return new UserApplicationResponse(
            $user->id()->value(),
            $user->name()->value(),
            $user->email()->value(),
            $user->role(),
            $user->status()
        );
    }

    /**
     * @return array<string, string>
     */
    public static function fromModelToArray(UserModel $user): array
    {
        return self::fromModelToResponse($user)->toArray();
    }

    /**
     * @param UserModel[] $users
     * @return array<int, array<string, string>>
     */
    public static function fromModelsToArray(array $users): array
    {
        $result = array();

        foreach ($users as $user) {
            $result[] = self::fromModelToArray($user);
        }

        return $result;
    }

    private static function generateId(): string
    {
        return 'USR-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }
}
