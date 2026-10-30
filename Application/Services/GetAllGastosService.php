<?php

declare(strict_types=1);

require_once __DIR__ . '/../Ports/In/GetAllGastosUseCase.php';
require_once __DIR__ . '/../Ports/Out/GetAllGastosPort.php';
require_once __DIR__ . '/../Ports/Out/CachePort.php';

final class GetAllGastosService implements GetAllGastosUseCase
{
    private GetAllGastosPort $getAllGastosPort;
    private ?CachePort $cachePort;

    public function __construct(GetAllGastosPort $getAllGastosPort, ?CachePort $cachePort = null)
    {
        $this->getAllGastosPort = $getAllGastosPort;
        $this->cachePort = $cachePort;
    }

    public function execute(GetAllGastosQuery $query): array
    {
        $cacheKey = 'gastos:list:' . md5(
            ($query->getSearch() ?? '') . ':' .
            ($query->getNombreUsuario() ?? '') . ':' .
            $query->getPage() . ':' .
            $query->getLimit()
        );

        if ($this->cachePort !== null) {
            $cached = $this->cachePort->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $all = $this->getAllGastosPort->getAll($query->getSearch(), $query->getNombreUsuario());
        $offset = ($query->getPage() - 1) * $query->getLimit();
        $sliced = array_slice($all, $offset, $query->getLimit());

        if ($this->cachePort !== null) {
            $this->cachePort->set($cacheKey, $sliced, 60);
        }

        return $sliced;
    }
}
