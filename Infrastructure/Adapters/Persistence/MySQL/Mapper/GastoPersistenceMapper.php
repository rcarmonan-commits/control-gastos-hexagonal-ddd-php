<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../Domain/Models/GastoModel.php';
require_once __DIR__ . '/../../../../../Domain/ValueObjects/GastoId.php';
require_once __DIR__ . '/../../../../../Domain/ValueObjects/GastoFecha.php';
require_once __DIR__ . '/../../../../../Domain/ValueObjects/GastoMonto.php';
require_once __DIR__ . '/../../../../../Domain/ValueObjects/GastoLugar.php';
require_once __DIR__ . '/../../../../../Domain/ValueObjects/GastoDescripcion.php';
require_once __DIR__ . '/../../../../../Domain/ValueObjects/UserName.php';
require_once __DIR__ . '/../Entity/GastoEntity.php';
require_once __DIR__ . '/../Dto/GastoPersistenceDto.php';

final class GastoPersistenceMapper
{
    public static function toEntity(GastoModel $model): GastoEntity
    {
        return new GastoEntity(
            $model->id()->value(),
            $model->fecha()->value(),
            $model->valorTotalSinIVA()->value(),
            $model->ivaTotal()->value(),
            $model->valorTotalConIVA()->value(),
            $model->nombreUsuario()->value(),
            $model->lugar()->value(),
            $model->descripcion()->value()
        );
    }

    public static function toDto(GastoEntity $entity): GastoPersistenceDto
    {
        return new GastoPersistenceDto(
            $entity->getId(),
            $entity->getFecha(),
            $entity->getValorTotalSinIVA(),
            $entity->getIvaTotal(),
            $entity->getValorTotalConIVA(),
            $entity->getNombreUsuario(),
            $entity->getLugar(),
            $entity->getDescripcion()
        );
    }

    public static function toDomainModel(GastoEntity $entity): GastoModel
    {
        return new GastoModel(
            new GastoId($entity->getId()),
            new GastoFecha($entity->getFecha()),
            new GastoMonto($entity->getValorTotalSinIVA(), 'valorTotalSinIVA'),
            new GastoMonto($entity->getIvaTotal(), 'ivaTotal'),
            new GastoMonto($entity->getValorTotalConIVA(), 'valorTotalConIVA'),
            new UserName($entity->getNombreUsuario()),
            new GastoLugar($entity->getLugar()),
            new GastoDescripcion($entity->getDescripcion())
        );
    }
}
