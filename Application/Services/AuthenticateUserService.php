<?php

declare(strict_types=1);

require_once __DIR__ . '/../Ports/In/AuthenticateUserUseCase.php';
require_once __DIR__ . '/../Ports/Out/GetUserByEmailPort.php';
require_once __DIR__ . '/../../Domain/ValueObjects/UserEmail.php';
require_once __DIR__ . '/../../Domain/Enums/UserStatusEnum.php';
require_once __DIR__ . '/../../Domain/Exceptions/InvalidUserPasswordException.php';
require_once __DIR__ . '/../../Domain/Exceptions/InvalidUserStatusException.php';

final class AuthenticateUserService implements AuthenticateUserUseCase
{
    private GetUserByEmailPort $getUserByEmailPort;

    public function __construct(GetUserByEmailPort $getUserByEmailPort)
    {
        $this->getUserByEmailPort = $getUserByEmailPort;
    }

    public function execute(AuthenticateUserCommand $command): UserModel
    {
        $email = new UserEmail($command->getEmail());
        $user = $this->getUserByEmailPort->getByEmail($email);

        if ($user === null || !$user->password()->matches($command->getPassword())) {
            throw InvalidUserPasswordException::becauseCredentialsAreInvalid();
        }

        if ($user->status() !== UserStatusEnum::ACTIVE) {
            throw InvalidUserStatusException::becauseValueIsInvalid($user->status());
        }

        return $user;
    }
}
