<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../Application/Ports/Out/SaveUserPort.php';
require_once __DIR__ . '/../../../../../Application/Ports/Out/GetUserByIdPort.php';
require_once __DIR__ . '/../../../../../Application/Ports/Out/GetUserByEmailPort.php';
require_once __DIR__ . '/../../../../../Application/Ports/Out/FindUserByEmailPort.php';
require_once __DIR__ . '/../../../../../Application/Ports/Out/GetAllUsersPort.php';
require_once __DIR__ . '/../../../../../Application/Ports/Out/UpdateUserPort.php';
require_once __DIR__ . '/../../../../../Application/Ports/Out/DeleteUserPort.php';
require_once __DIR__ . '/../Config/Connection.php';
require_once __DIR__ . '/../Entity/UserEntity.php';
require_once __DIR__ . '/../Mapper/UserPersistenceMapper.php';

final class UserRepositoryMySQL implements
    SaveUserPort,
    GetUserByIdPort,
    GetUserByEmailPort,
    FindUserByEmailPort,
    GetAllUsersPort,
    UpdateUserPort,
    DeleteUserPort
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::getInstance();
    }

    public function save(UserModel $user): UserModel
    {
        $entity = UserPersistenceMapper::toEntity($user);

        $sql = 'INSERT INTO users (id, name, email, password, role, status, role_id)
                VALUES (:id, :name, :email, :password, :role, :status, :role_id)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(
            ':id' => $entity->getId(),
            ':name' => $entity->getName(),
            ':email' => $entity->getEmail(),
            ':password' => $entity->getPassword(),
            ':role' => $entity->getRole(),
            ':status' => $entity->getStatus(),
            ':role_id' => $entity->getRoleId(),
        ));

        return UserPersistenceMapper::toDomainModel($entity);
    }

    public function getById(UserId $userId): ?UserModel
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => $userId->value()));
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        return UserPersistenceMapper::toDomainModel(UserEntity::fromDatabaseRow($row));
    }

    public function getByEmail(UserEmail $email): ?UserModel
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
        $stmt->execute(array(':email' => $email->value()));
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        return UserPersistenceMapper::toDomainModel(UserEntity::fromDatabaseRow($row));
    }

    public function getAll(?string $search = null): array
    {
        if ($search !== null && trim($search) !== '') {
            $pattern = '%' . trim($search) . '%';
            $stmt = $this->pdo->prepare(
                'SELECT * FROM users
                 WHERE id LIKE :q OR name LIKE :q OR email LIKE :q OR role LIKE :q
                 ORDER BY created_at DESC, id ASC'
            );
            $stmt->execute(array(':q' => $pattern));
        } else {
            $stmt = $this->pdo->query('SELECT * FROM users ORDER BY created_at DESC, id ASC');
        }

        $rows = $stmt !== false ? $stmt->fetchAll() : array();
        $models = array();

        foreach ($rows as $row) {
            $models[] = UserPersistenceMapper::toDomainModel(UserEntity::fromDatabaseRow($row));
        }

        return $models;
    }

    public function update(UserModel $user): UserModel
    {
        $entity = UserPersistenceMapper::toEntity($user);

        $sql = 'UPDATE users
                SET name = :name,
                    email = :email,
                    password = :password,
                    role = :role,
                    status = :status,
                    role_id = :role_id
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(
            ':id' => $entity->getId(),
            ':name' => $entity->getName(),
            ':email' => $entity->getEmail(),
            ':password' => $entity->getPassword(),
            ':role' => $entity->getRole(),
            ':status' => $entity->getStatus(),
            ':role_id' => $entity->getRoleId(),
        ));

        return UserPersistenceMapper::toDomainModel($entity);
    }

    public function findByEmail(UserEmail $email): ?UserModel
    {
        return $this->getByEmail($email);
    }

    public function delete(UserId $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(array(':id' => $userId->value()));
    }
}
