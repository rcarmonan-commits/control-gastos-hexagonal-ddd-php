<?php

declare(strict_types=1);

require_once __DIR__ . '/../Dto/Commands/CreateGastoCommand.php';
require_once __DIR__ . '/../Dto/Commands/UpdateGastoCommand.php';
require_once __DIR__ . '/../Dto/Commands/DeleteGastoCommand.php';
require_once __DIR__ . '/../Dto/Queries/GetGastoByIdQuery.php';
require_once __DIR__ . '/../Dto/Responses/GastoApplicationResponse.php';

require_once __DIR__ . '/../../../Domain/Models/GastoModel.php';
require_once __DIR__ . '/../../../Domain/ValueObjects/GastoId.php';
require_once __DIR__ . '/../../../Domain/ValueObjects/GastoFecha.php';
require_once __DIR__ . '/../../../Domain/ValueObjects/GastoMonto.php';
require_once __DIR__ . '/../../../Domain/ValueObjects/GastoLugar.php';
require_once __DIR__ . '/../../../Domain/ValueObjects/GastoDescripcion.php';
require_once __DIR__ . '/../../../Domain/ValueObjects/UserName.php';

final class GastoApplicationMapper
{
    public static function fromCreateCommandToModel(CreateGastoCommand $command, ?string $generatedId = null): GastoModel
    {
        $resolvedId = $command->getId() !== '' ? $command->getId() : ($generatedId ?? self::generateId());
        $sinIva = new GastoMonto($command->getValorTotalSinIVA(), 'valorTotalSinIVA');
        $iva = new GastoMonto($command->getIvaTotal(), 'ivaTotal');
        $conIva = $command->getValorTotalConIVA() !== null
            ? new GastoMonto($command->getValorTotalConIVA(), 'valorTotalConIVA')
            : null;

        return GastoModel::create(
            new GastoId($resolvedId),
            new GastoFecha($command->getFecha()),
            $sinIva,
            $iva,
            $conIva,
            new UserName($command->getNombreUsuario()),
            new GastoLugar($command->getLugar()),
            new GastoDescripcion($command->getDescripcion())
        );
    }

    public static function fromUpdateCommandToModel(UpdateGastoCommand $command): GastoModel
    {
        $sinIva = new GastoMonto($command->getValorTotalSinIVA(), 'valorTotalSinIVA');
        $iva = new GastoMonto($command->getIvaTotal(), 'ivaTotal');
        $conIva = $command->getValorTotalConIVA() !== null
            ? new GastoMonto($command->getValorTotalConIVA(), 'valorTotalConIVA')
            : $sinIva->add($iva, 'valorTotalConIVA');

        return new GastoModel(
            new GastoId($command->getId()),
            new GastoFecha($command->getFecha()),
            $sinIva,
            $iva,
            $conIva,
            new UserName($command->getNombreUsuario()),
            new GastoLugar($command->getLugar()),
            new GastoDescripcion($command->getDescripcion())
        );
    }

    public static function fromGetGastoByIdQueryToGastoId(GetGastoByIdQuery $query): GastoId
    {
        return new GastoId($query->getId());
    }

    public static function fromDeleteCommandToGastoId(DeleteGastoCommand $command): GastoId
    {
        return new GastoId($command->getId());
    }

    public static function fromModelToResponse(GastoModel $gasto): GastoApplicationResponse
    {
        return new GastoApplicationResponse(
            $gasto->id()->value(),
            $gasto->fecha()->value(),
            $gasto->valorTotalSinIVA()->value(),
            $gasto->ivaTotal()->value(),
            $gasto->valorTotalConIVA()->value(),
            $gasto->nombreUsuario()->value(),
            $gasto->lugar()->value(),
            $gasto->descripcion()->value()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromModelToArray(GastoModel $gasto): array
    {
        return self::fromModelToResponse($gasto)->toArray();
    }

    /**
     * @param GastoModel[] $gastos
     * @return array<int, array<string, mixed>>
     */
    public static function fromModelsToArray(array $gastos): array
    {
        $result = array();

        foreach ($gastos as $gasto) {
            $result[] = self::fromModelToArray($gasto);
        }

        return $result;
    }

    private static function generateId(): string
    {
        return 'GAS-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }
}
