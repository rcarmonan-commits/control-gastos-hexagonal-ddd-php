<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../Common/DependencyInjection.php';
require_once __DIR__ . '/Dto/CreateGastoRequest.php';
require_once __DIR__ . '/Dto/UpdateGastoRequest.php';
require_once __DIR__ . '/Mapper/GastoWebMapper.php';

final class GastoController
{
    private DependencyInjection $di;

    public function __construct(?DependencyInjection $di = null)
    {
        $this->di = $di ?? DependencyInjection::getInstance();
    }

    /**
     * @param array<string, mixed> $queryParams
     * @return GastoResponse[]
     */
    public function list(array $queryParams = array()): array
    {
        $query = GastoWebMapper::toGetAllQuery($queryParams);
        $models = $this->di->getAllGastosUseCase()->execute($query);
        return GastoWebMapper::toResponses($models);
    }

    public function getById(string $id): GastoResponse
    {
        $query = GastoWebMapper::toGetByIdQuery($id);
        $model = $this->di->getGastoByIdUseCase()->execute($query);
        return GastoWebMapper::toResponse($model);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function create(array $input): GastoResponse
    {
        $request = new CreateGastoRequest($input);
        $command = GastoWebMapper::toCreateCommand($request);
        $model = $this->di->createGastoUseCase()->execute($command);
        return GastoWebMapper::toResponse($model);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function update(array $input): GastoResponse
    {
        $request = new UpdateGastoRequest($input);
        $command = GastoWebMapper::toUpdateCommand($request);
        $model = $this->di->updateGastoUseCase()->execute($command);
        return GastoWebMapper::toResponse($model);
    }

    public function delete(string $id): void
    {
        $command = GastoWebMapper::toDeleteCommand($id);
        $this->di->deleteGastoUseCase()->execute($command);
    }
}
