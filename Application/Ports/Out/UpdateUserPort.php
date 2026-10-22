<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../Domain/Models/UserModel.php';

interface UpdateUserPort
{
    public function update(UserModel $user): UserModel;
}
