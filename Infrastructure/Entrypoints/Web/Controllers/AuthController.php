<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../Common/DependencyInjection.php';
require_once __DIR__ . '/../../../../Application/Services/Dto/Commands/AuthenticateUserCommand.php';
require_once __DIR__ . '/Mapper/UserWebMapper.php';

final class AuthController
{
    private DependencyInjection $di;

    public function __construct(?DependencyInjection $di = null)
    {
        $this->di = $di ?? DependencyInjection::getInstance();
    }

    public function login(string $email, string $password): UserResponse
    {
        $command = new AuthenticateUserCommand($email, $password);
        $user = $this->di->authenticateUserUseCase()->execute($command);
        return UserWebMapper::toResponse($user);
    }
}
