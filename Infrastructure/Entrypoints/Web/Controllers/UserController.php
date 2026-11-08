<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../Common/DependencyInjection.php';
require_once __DIR__ . '/Dto/CreateUserRequest.php';
require_once __DIR__ . '/Dto/UpdateUserRequest.php';
require_once __DIR__ . '/Mapper/UserWebMapper.php';

final class UserController
{
    private DependencyInjection $di;

    public function __construct(?DependencyInjection $di = null)
    {
        $this->di = $di ?? DependencyInjection::getInstance();
    }

    /**
     * @param array<string, mixed> $queryParams
     * @return UserResponse[]
     */
    public function list(array $queryParams = array()): array
    {
        $query = UserWebMapper::toGetAllQuery($queryParams);
        $models = $this->di->getAllUsersUseCase()->execute($query);
        return UserWebMapper::toResponses($models);
    }

    public function getById(string $id): UserResponse
    {
        $query = UserWebMapper::toGetByIdQuery($id);
        $model = $this->di->getUserByIdUseCase()->execute($query);
        return UserWebMapper::toResponse($model);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function create(array $input): UserResponse
    {
        $request = new CreateUserRequest($input);
        $command = UserWebMapper::toCreateCommand($request);
        $model = $this->di->createUserUseCase()->execute($command);
        return UserWebMapper::toResponse($model);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function update(array $input): UserResponse
    {
        $request = new UpdateUserRequest($input);
        $command = UserWebMapper::toUpdateCommand($request);
        $model = $this->di->updateUserUseCase()->execute($command);
        return UserWebMapper::toResponse($model);
    }

    public function delete(string $id): void
    {
        $command = UserWebMapper::toDeleteCommand($id);
        $this->di->deleteUserUseCase()->execute($command);
    }
}
