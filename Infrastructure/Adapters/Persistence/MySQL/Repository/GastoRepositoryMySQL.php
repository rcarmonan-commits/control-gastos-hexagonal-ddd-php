<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../Application/Ports/Out/SaveGastoPort.php';
require_once __DIR__ . '/../../../../../Application/Ports/Out/GetGastoByIdPort.php';
require_once __DIR__ . '/../../../../../Application/Ports/Out/GetAllGastosPort.php';
require_once __DIR__ . '/../../../../../Application/Ports/Out/UpdateGastoPort.php';
require_once __DIR__ . '/../../../../../Application/Ports/Out/DeleteGastoPort.php';
require_once __DIR__ . '/../Config/Connection.php';
require_once __DIR__ . '/../Entity/GastoEntity.php';
require_once __DIR__ . '/../Mapper/GastoPersistenceMapper.php';

final class GastoRepositoryMySQL implements
    SaveGastoPort,
    GetGastoByIdPort,
    GetAllGastosPort,
    UpdateGastoPort,
    DeleteGastoPort
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::getInstance();
    }

    public function save(GastoModel $gasto): GastoModel
    {
        $entity = GastoPersistenceMapper::toEntity($gasto);

        $sql = 'INSERT INTO gastos (
                    id, fecha, valor_total_sin_iva, iva_total, valor_total_con_iva,
                    nombre_usuario, lugar, descripcion
                ) VALUES (
                    :id, :fecha, :sin_iva, :iva, :con_iva,
                    :nombre_usuario, :lugar, :descripcion
                )';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(
            ':id' => $entity->getId(),
            ':fecha' => $entity->getFecha(),
            ':sin_iva' => $entity->getValorTotalSinIVA(),
            ':iva' => $entity->getIvaTotal(),
            ':con_iva' => $entity->getValorTotalConIVA(),
            ':nombre_usuario' => $entity->getNombreUsuario(),
            ':lugar' => $entity->getLugar(),
            ':descripcion' => $entity->getDescripcion(),
        ));

        return GastoPersistenceMapper::toDomainModel($entity);
    }

    public function getById(GastoId $gastoId): ?GastoModel
    {
        $stmt = $this->pdo->prepare('SELECT * FROM gastos WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => $gastoId->value()));
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        return GastoPersistenceMapper::toDomainModel(GastoEntity::fromDatabaseRow($row));
    }

    public function getAll(?string $search = null, ?string $nombreUsuario = null): array
    {
        $conditions = array();
        $params = array();

        if ($search !== null && trim($search) !== '') {
            $conditions[] = '(id LIKE :q OR lugar LIKE :q OR descripcion LIKE :q OR nombre_usuario LIKE :q OR CAST(fecha AS CHAR) LIKE :q)';
            $params[':q'] = '%' . trim($search) . '%';
        }

        if ($nombreUsuario !== null && trim($nombreUsuario) !== '') {
            $conditions[] = 'LOWER(nombre_usuario) = LOWER(:usuario)';
            $params[':usuario'] = trim($nombreUsuario);
        }

        $sql = 'SELECT * FROM gastos';
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY fecha DESC, id ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $models = array();
        foreach ($rows as $row) {
            $models[] = GastoPersistenceMapper::toDomainModel(GastoEntity::fromDatabaseRow($row));
        }

        return $models;
    }

    public function update(GastoModel $gasto): GastoModel
    {
        $entity = GastoPersistenceMapper::toEntity($gasto);

        $sql = 'UPDATE gastos
                SET fecha = :fecha,
                    valor_total_sin_iva = :sin_iva,
                    iva_total = :iva,
                    valor_total_con_iva = :con_iva,
                    nombre_usuario = :nombre_usuario,
                    lugar = :lugar,
                    descripcion = :descripcion
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(
            ':id' => $entity->getId(),
            ':fecha' => $entity->getFecha(),
            ':sin_iva' => $entity->getValorTotalSinIVA(),
            ':iva' => $entity->getIvaTotal(),
            ':con_iva' => $entity->getValorTotalConIVA(),
            ':nombre_usuario' => $entity->getNombreUsuario(),
            ':lugar' => $entity->getLugar(),
            ':descripcion' => $entity->getDescripcion(),
        ));

        return GastoPersistenceMapper::toDomainModel($entity);
    }

    public function delete(GastoId $gastoId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM gastos WHERE id = :id');
        $stmt->execute(array(':id' => $gastoId->value()));
    }
}
