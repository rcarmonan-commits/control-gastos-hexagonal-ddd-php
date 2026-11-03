<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../Domain/Models/UserModel.php';
require_once __DIR__ . '/../../../../../Domain/ValueObjects/UserId.php';
require_once __DIR__ . '/../../../../../Domain/ValueObjects/UserName.php';
require_once __DIR__ . '/../../../../../Domain/ValueObjects/UserEmail.php';
require_once __DIR__ . '/../../../../../Domain/ValueObjects/UserPassword.php';
require_once __DIR__ . '/../../../../../Domain/Enums/UserRoleEnum.php';
require_once __DIR__ . '/../Entity/UserEntity.php';
require_once __DIR__ . '/../Dto/UserPersistenceDto.php';

final class UserPersistenceMapper
{
    public static function toEntity(UserModel $model): UserEntity
    {
        return new UserEntity(
            $model->id()->value(),
            $model->name()->value(),
            $model->email()->value(),
            $model->password()->value(),
            $model->role(),
            $model->status(),
            UserRoleEnum::toRoleId($model->role())
        );
    }

    public static function toDto(UserEntity $entity): UserPersistenceDto
    {
        return new UserPersistenceDto(
            $entity->getId(),
            $entity->getName(),
            $entity->getEmail(),
            $entity->getPassword(),
            $entity->getRole(),
            $entity->getStatus(),
            $entity->getRoleId()
        );
    }

    public static function toDomainModel(UserEntity $entity): UserModel
    {
        return new UserModel(
            new UserId($entity->getId()),
            new UserName($entity->getName()),
            new UserEmail($entity->getEmail()),
            new UserPassword($entity->getPassword()),
            $entity->getRole(),
            $entity->getStatus()
        );
    }
}
