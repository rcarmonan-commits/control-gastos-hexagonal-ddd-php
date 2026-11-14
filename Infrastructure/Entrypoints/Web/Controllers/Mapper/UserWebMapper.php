<?php

declare(strict_types=1);

require_once __DIR__ . '/../Dto/CreateUserRequest.php';
require_once __DIR__ . '/../Dto/UpdateUserRequest.php';
require_once __DIR__ . '/../Dto/UserResponse.php';
require_once __DIR__ . '/../../../../../Application/Services/Dto/Commands/CreateUserCommand.php';
require_once __DIR__ . '/../../../../../Application/Services/Dto/Commands/UpdateUserCommand.php';
require_once __DIR__ . '/../../../../../Application/Services/Dto/Commands/DeleteUserCommand.php';
require_once __DIR__ . '/../../../../../Application/Services/Dto/Queries/GetUserByIdQuery.php';
require_once __DIR__ . '/../../../../../Application/Services/Dto/Queries/GetAllUsersQuery.php';
require_once __DIR__ . '/../../../../../Domain/Models/UserModel.php';

final class UserWebMapper
{
    public static function toCreateCommand(CreateUserRequest $request): CreateUserCommand
    {
        return new CreateUserCommand(
            $request->id,
            $request->name,
            $request->email,
            $request->password,
            $request->role
        );
    }

    public static function toUpdateCommand(UpdateUserRequest $request): UpdateUserCommand
    {
        return new UpdateUserCommand(
            $request->id,
            $request->name,
            $request->email,
            $request->password,
            $request->role,
            $request->status
        );
    }

    public static function toDeleteCommand(string $id): DeleteUserCommand
    {
        return new DeleteUserCommand($id);
    }

    public static function toGetByIdQuery(string $id): GetUserByIdQuery
    {
        return new GetUserByIdQuery($id);
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    public static function toGetAllQuery(array $queryParams): GetAllUsersQuery
    {
        $search = isset($queryParams['q']) ? (string) $queryParams['q'] : null;
        $page = isset($queryParams['page']) ? (int) $queryParams['page'] : 1;
        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 50;

        return new GetAllUsersQuery($search, $page, $limit);
    }

    public static function toResponse(UserModel $model): UserResponse
    {
        return new UserResponse(
            $model->id()->value(),
            $model->name()->value(),
            $model->email()->value(),
            $model->role(),
            $model->status()
        );
    }

    /**
     * @param UserModel[] $models
     * @return UserResponse[]
     */
    public static function toResponses(array $models): array
    {
        $responses = array();
        foreach ($models as $model) {
            $responses[] = self::toResponse($model);
        }
        return $responses;
    }
}
