<?php

declare(strict_types=1);

final class AuthenticateUserCommand
{
    private string $email;
    private string $password;

    public function __construct(string $email, string $password)
    {
        $this->email = trim($email);
        $this->password = trim($password);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
