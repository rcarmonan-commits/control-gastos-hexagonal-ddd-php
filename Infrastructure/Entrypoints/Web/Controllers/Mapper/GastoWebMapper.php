<?php

declare(strict_types=1);

require_once __DIR__ . '/../Dto/CreateGastoRequest.php';
require_once __DIR__ . '/../Dto/UpdateGastoRequest.php';
require_once __DIR__ . '/../Dto/GastoResponse.php';
require_once __DIR__ . '/../../../../../Application/Services/Dto/Commands/CreateGastoCommand.php';
require_once __DIR__ . '/../../../../../Application/Services/Dto/Commands/UpdateGastoCommand.php';
require_once __DIR__ . '/../../../../../Application/Services/Dto/Commands/DeleteGastoCommand.php';
require_once __DIR__ . '/../../../../../Application/Services/Dto/Queries/GetGastoByIdQuery.php';
require_once __DIR__ . '/../../../../../Application/Services/Dto/Queries/GetAllGastosQuery.php';
require_once __DIR__ . '/../../../../../Domain/Models/GastoModel.php';

final class GastoWebMapper
{
    public static function toCreateCommand(CreateGastoRequest $request): CreateGastoCommand
    {
        return new CreateGastoCommand(
            $request->id,
            $request->fecha,
            $request->valorTotalSinIVA,
            $request->ivaTotal,
            $request->valorTotalConIVA,
            $request->nombreUsuario,
            $request->lugar,
            $request->descripcion
        );
    }

    public static function toUpdateCommand(UpdateGastoRequest $request): UpdateGastoCommand
    {
        return new UpdateGastoCommand(
            $request->id,
            $request->fecha,
            $request->valorTotalSinIVA,
            $request->ivaTotal,
            $request->valorTotalConIVA,
            $request->nombreUsuario,
            $request->lugar,
            $request->descripcion
        );
    }

    public static function toDeleteCommand(string $id): DeleteGastoCommand
    {
        return new DeleteGastoCommand($id);
    }

    public static function toGetByIdQuery(string $id): GetGastoByIdQuery
    {
        return new GetGastoByIdQuery($id);
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    public static function toGetAllQuery(array $queryParams): GetAllGastosQuery
    {
        $search = isset($queryParams['q']) ? (string) $queryParams['q'] : null;
        $usuario = isset($queryParams['usuario']) ? (string) $queryParams['usuario'] : null;
        $page = isset($queryParams['page']) ? (int) $queryParams['page'] : 1;
        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 50;

        return new GetAllGastosQuery($search, $usuario, $page, $limit);
    }

    public static function toResponse(GastoModel $model): GastoResponse
    {
        return new GastoResponse(
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

    /**
     * @param GastoModel[] $models
     * @return GastoResponse[]
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
